<?php
function get_side_menu()
{
    $subject =isset($_GET) && isset($_GET["subject"]) ? $_GET["subject"] : "home"; ?>
<div id="sidebar-menu" class="part-10">
    <ul class="menu flex-display direction-column" role="navigation">
        <?php
        $menu = wp_get_nav_menu_object( get_nav_menu_locations()[ 'main_menu' ] );
        $menuitems = wp_get_nav_menu_items( $menu->term_id, array( 'order' => 'DESC' ) );
        $count = 0;
        $submenu = false;
        foreach($menuitems as $item ){
            $name = $item->description;
            if(is_agent() && ($name=="agents" || $name=="suppliers" || $name=="collection"||$name=="lists") ||
                is_supplier() && $name !="collection")continue;
            $url = $item->url."?subject=".$name;
            if ( !$item->menu_item_parent ){
                $parent_id = $item->ID;?>
                <li class="pointer flex-display direction-column center  <?=($subject == $name ? 'selected':'') ?> ">
                    <div class="flex-display space-between align-center">
                        <?php echo get_svg($name) ?>
                        <a class="not-link" href="<?= esc_url($url)?>"><?=esc_html($item->title)?></a>
                    </div>
            <?php }
            else{
                if($name=="collection"){
                    $url .= "&payed=true";
                }
                else {
                    $url .= "&action=new";
                }
            }
            //לבדוק מה סוכן יכול להוסיף חדש (הזמנה? מה עוד)
            ?>
            <?php if ( $parent_id == $item->menu_item_parent && (is_manager() || is_agent() && $name=="orders"
                || is_supplier() && $name=="collection")){ ?>
                <?php if ( !$submenu ){ $submenu = true; ?>
                    <ul class="sub-menu font-15">
                <?php } ?>
                <li class="pointer flex-display start align-center">
                    <a class="not-link" href="<?= esc_url($url)?>"><?=esc_html($item->title)?></a></li>
                    <?php if ( $menuitems[ $count + 1 ]->menu_item_parent != $parent_id && $submenu ){ ?>
                        </ul>
                        <?php $submenu = false;
                    }
           if ( $menuitems[ $count + 1 ]->menu_item_parent != $parent_id ){ ?>
                </li>
                <?php $submenu = false;
           } ?>
           <?php $count++;
           }
        }?>
    </ul>
</div>
<?php }
function option_if_set($set,$option){
    return  (isset($set[$option]) ? "$option=\"".$set[$option]."\"" : "" );
}

add_action('wp_ajax_get_list_ajax', 'get_list_ajax');
function get_list_ajax(){
    //write_log ("get_list_ajax " );
    $table_name = $_POST['table_name'];
    $selected_value = isset($_POST['selected_value'])?$_POST['selected_value']:null;
    $filter="";
    if(isset($_POST['filter'])){
        $filter = $_POST['filter'];
    }
   $options=$table=null;
    $format =  isset($_POST["format"])? $_POST["format"] :"options";
    switch ($format) {
        case  'table':
            //write_log ("table_display ");
            $table = lists_table_rows ($table_name, '');
            $title = BOUTIQUE_LISTS[$table_name]["title"];
            $options = array();
            $options["title"] = $title;
            //write_log ("rows " . $table);
            break;
        case 'array':
            $result = get_list($table_name,$filter,true);
            break;
        case 'options':
            $options = build_select_options($table_name, $selected_value, array("filter"=>$filter));
            //write_log ("options" . $options);
            break;
    }
    echo json_encode (array("options" => $options ,"tableData"=>$table,"array"=>$result));
    die();
}

function lists_table_rows($list_name)
{
    $fields_list = BOUTIQUE_LISTS[$list_name];
    //write_log("build_table_rows list name " .$list_name);
    $list = get_list($list_name,'',true);
   // write_log("list " .json_encode($list));
    $column_name =  $fields_list["columns"][0]["field_name"];
    $rows = '<tr class="tr-head gold"><th data-column-name ="'.$column_name.'">'.$fields_list["single"].'</th>';
    foreach (array_slice($fields_list["columns"],1) as $column) {
        $rows .= '<th data-column-name ="'.$column["field_name"].'" data-column-type ="'.$column["widget"].'" data-table ="'.$column["join_table"].'">'
            .$column['label'].'</th>';
    }
    $rows .= '</tr>';
    foreach ($list as $row) {
        $rows .= '<tr data-id="'. $row->id .'">';
        foreach ($fields_list["columns"] as $column) {
            if (isset($column['join_value'])) {
                $field = $column['join_value'];
            } else {
                $field = $column["field_name"];
            }
            $rows .= '<td>' . $row->$field . '</td>';
        }
        //?subject=' . $list_name . '&action=edit&id=' . $row->id . '
        $rows .= '<td class="td-action"><a class="has-tooltip" data-tooltip="עדכון ' . $fields_list['single'] . '" data-bs-toggle="modal" href="#edit-list" role="button" data-action="edit"> 
                        <svg class="edit-row" xmlns="http://www.w3.org/2000/svg" width="24" height="23" viewBox="0 0 24 23" fill="none">
                            <path d="M7 16.3041L11.413 16.2898L21.045 7.14726C21.423 6.78501 21.631 6.30393 21.631 5.79218C21.631 5.28043 21.423 4.79934 21.045 4.43709L19.459 2.91717C18.703 2.19267 17.384 2.19651 16.634 2.9143L7 12.0587V16.3041ZM18.045 4.27226L19.634 5.7893L18.037 7.30538L16.451 5.78643L18.045 4.27226ZM9 12.858L15.03 7.13384L16.616 8.65376L10.587 14.376L9 14.3808V12.858Z" fill="#E2B252"/>
                            <path d="M5 20.125H19C20.103 20.125 21 19.2654 21 18.2083V9.9015L19 11.8182V18.2083H8.158C8.132 18.2083 8.105 18.2179 8.079 18.2179C8.046 18.2179 8.013 18.2093 7.979 18.2083H5V4.79167H11.847L13.847 2.875H5C3.897 2.875 3 3.73462 3 4.79167V18.2083C3 19.2654 3.897 20.125 5 20.125Z" fill="#E2B252"/>
                        </svg></a>
                  </td>';
        $rows .='<td class="td-action"><a data-bs-toggle="modal" class="has-tooltip"  data-tooltip="מחיקת ' . $fields_list['single'] . '" href="#bout-massage" role="button" data-action="remove">
                    <svg class="remove-row"  xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
                    <path d="M4.16663 7H20.8333M10.4166 11V17M14.5833 11V17M5.20829 7L6.24996 19C6.24996 19.5304 6.46945 20.0391 6.86015 20.4142C7.25085 20.7893 7.78076 21 8.33329 21H16.6666C17.2192 21 17.7491 20.7893 18.1398 20.4142C18.5305 20.0391 18.75 19.5304 18.75 19L19.7916 7M9.37496 7V4C9.37496 3.73478 9.48471 3.48043 9.68006 3.29289C9.87541 3.10536 10.1404 3 10.4166 3H14.5833C14.8596 3 15.1245 3.10536 15.3199 3.29289C15.5152 3.48043 15.625 3.73478 15.625 4V7" stroke="#E2B252" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg></a>
                </td>';
        $rows .= '</tr>';
    }
    //write_log("rows " .$rows);
    return $rows;
}



function get_column_value($column,$row,$field,$list,$key)
{
    //write_log("column ".json_encode($column)." key ".$key);
    $column_value = "";
    switch ($column["widget"]) {

        case "select":
            if (isset($column["options"])) {
                $field_id = $row->$field;
                $results = array_filter($column["options"], function ($option) use ($field_id) {
                    return $option["value"] == $field_id;
                });
                if (count($results) > 0) {
                    $column_value = array_pop($results)["text"];
                }
            } else {
                $column_value = $row->$field;
            }
            break;
        case "radio":
            $column_value = '<div class="flex-display center align-center" style="color: ' . $column["values"][$row->$field]["color"] . '"><div class="dot" style="background-color: ' . $column["values"][$row->$field]["color"] . '"></div>&nbsp;' . $column["values"][$row->$field]["label"] . '</div>';

            break;
        case "status":
            $column_value = '<span class="pointer ellipse ' . $column["values"][$row->$field]["class"] . '">
                                   ' . $column["values"][$row->$field]["label"] . '
                                </span>';
            break;
        case "date":
            if ($row->$field) {
                $timestamp = strtotime($row->$field); // המרת התאריך לאטימות זמן
                $column_value = date('d/m/Y', $timestamp);
            }
            break;
        case "datetime-local":
            if ($row->$field) {
                $timestamp = strtotime($row->$field); // המרת התאריך לאטימות זמן
                $column_value = date('d/m/Y H:i', $timestamp);
            }
            break;
        case "file":
            if ($row->$field) {
                $column_value = '<a href="' . wp_get_attachment_url($row->$field) . '" target="_blank">' . basename(get_attached_file($row->$field)) . '</a>';
            }
            break;
        case "image":
            if ($row->$field) {
                $column_value = wp_get_attachment_image($row->$field, 'full');
            }
            break;
        default:
            if (isset($column["type"]) && $column["type"] == "user") {
                $column_value = empty($row->$field) ? '' : get_userdata($row->$field)->display_name;
            } else {
                $column_value = isset($column['list_name']) && isset($list[$row->$field]) ? $list[$row->$field] : $row->$field;
                if (!empty($column_value) && isset($column["un_apostrophe"])) {
                    $column_value .= " ₪";
                }
            }

            if(isset($column["create_input"])){
                $column_value = '<input type="'.$column["widget"].'" name="rows[' . $key . '][' . $field . ']" value="' . $column_value . '"/>';
            }
            break;
    }

    return $column_value;

}

add_action('wp_ajax_on_order_confirmation', 'on_order_confirmation');
function on_order_confirmation(){
    global  $wpdb;
    if(isset($_POST['order_id'])){
        $order_id = $_POST['order_id'];
        $query = "UPDATE ".$wpdb->prefix."orders SET done = 1, user_confirms = ".get_current_user_id()."
                  WHERE id = ".$order_id;
        //run_query ($query);//זה עובד טוב פשוט חבל כל הזמן שיאשר ויפריע לבדיקות!!!!
        //add_notice( 'order_confirmation' ,"ההזמנה אושרה נשלח מייל ללקוח ולספקים" );

        /*echo json_encode(array(
            'status' => 'success',
            'redirect' => $_POST["previous_page"],
        ));
        die();*/

        $filters= array(array("filter_field" => "id", "filter_value"=>$order_id));
        $order_confirmation = get_data_table("orders",$filters)[0];

        $filters=array(array("filter_field" => "id", "filter_value"=>$order_confirmation->client_id));

        //שליחת מייל ללקוח על ההזמנה שאושרה
        $client = get_data_table("clients",$filters)[0];

        $body = "הזמנה מתאריך ".date('d/m/Y',strtotime ( $order_confirmation->order_date)) ."<br><br>";

        $filters=array(array("filter_field" => "order_id", "filter_value"=>$order_id));

        $products = get_data_table("order_products",$filters);
        $order_supplier = array();
        //write_log("client ".json_encode($client));
        foreach ($products as $product){
            if(!is_array($order_supplier[$product->supplier_id])){
                $order_supplier[$product->supplier_id] = array();
            }
            $order_supplier[$product->supplier_id][]=$product;
            $body .= $product->name." כמות: ".$product->count." מחיר ".$product->price;
            if(!empty($product->bonus)){
                $body .= " כמות בקבוקים/ארגזים בונוס ".$product->bonus;
            }
            if(!empty($product->discount_percent)){
                $body .= " קיבלת הנחה של ".$product->discount_percent."%";
            }
            $body .= "<br>";
        }
        $body.= "<br>הערות: ".$order_confirmation->notes;
        //write_log("to ".$client->email." body ".$body);
        //send_mail($client->email,"סיכום הזמנתך מס. ".$order_id,$body);


        //שליחת מייל לכל ספק על ההזמנה בשבילו
        foreach ($order_supplier as $key=>$order){
            $filters=array(array("filter_field" => "id", "filter_value"=>$key));
            $supplier = get_data_table("suppliers",$filters)[0];

            $body = "נא לספק ללקוח  ".$client->name." את המוצרים הבאים:<br>";
            foreach ($order as $product){

                $body .= $product->name." כמות: ".$product->count+$product->bonus." מחיר ";
                $body .= "<br>";
            }
            //write_log("to ".$supplier->email." body ".$body);
            //send_mail($supplier->email,"סיכום הזמנה מס. ".$order_id,$body);
        }

        wp_send_json([
            'status' => 'success',
            "message" => "ההזמנה אושרה נשלח מייל ללקוח ולספקים",
            'redirect' => isset($_POST["previous_page"]) ? $_POST["previous_page"]:'',
        ]);
    }
}

function get_favorite_products($client_id)
{
    global  $wpdb;
    $query = "SELECT p.* FROM ".$wpdb->prefix."order_products op
                JOIN ".$wpdb->prefix."products p ON p.id = op.product_id
                WHERE order_id 
              IN (SELECT id FROM ".$wpdb->prefix."orders WHERE client_id = ".$client_id." 
               ORDER BY order_date DESC
                )
            GROUP BY p.id";//LIMIT 3
    //write_log("query ".$query);
    $products = run_query ($query);
    return $products;
    //write_log("favorite: ".json_encode($products));
}
function update_client_price_modal() {
    ?>
<form class="modal fade site_form" id="update_client_price"  tabindex='-1' role="dialog" data-success='getTableAjaxData' data-failed='show_error_messages'>
    <input type="hidden" name="form_func" value="save_single_data" />
    <input type="hidden" name="table_name" value="products_clients" />
    <input type="hidden" name="product_id" value="" />
    <input type="hidden" name="id" value="" />
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title grow" >מחיר מיחוד ללקוח מסוים</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="סגור">
                    </button>
                </div>
                <div class="modal-body border-dark-gray padding-20 flex-display direction-column margin-20">
                    <span class="input-label flex-display align-center">
                        <label class="bold" for="client_id">לקוח:</label>
                        <select id="client_id" name="client_id"  class=" font-17 grow">
                            <?php echo build_select_options ("clients");?>
                        </select>
                    </span>
                    <span class="input-label flex-display align-center">
                        <label class="bold" for="client_price">מחיר:</label>
                        <input type="text" id="client_price" name="client_price"  data-a-sign="₪"/>
                    </span>
                </div>
                <div class="modal-footer">
                    <button type="post" class="save background-gold bold font-18">שמור</button>
                    <button type="button" data-bs-dismiss="modal" class="cancel button background-white gold bold font-18">בטל</button>

                </div>
            </div>
        </div>
</form>
<?php
}

function payment_modal(){
    ?>
    <form class="modal fade site_form" id="payment_modal" data-success="updateRowSuccess"  tabindex='-1' role="dialog">
        <input type="hidden" name="form_func" value="save_single_data">
        <input type="hidden" name="id" value="">
        <input type="hidden" name="table_name" value="collection">
        <div class="modal-dialog" role="document">

            <div class="modal-content">
                <div class="modal-header flex-display">
                    <h3 class="modal-title grow" >תשלום חשבונית מס.<span class="bill-num"></span></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="סגור">
                    </button>
                </div>
                <div class="modal-body">
                    <div class="modal-body border-dark-gray padding-20 flex-display direction-column margin-20">
                        <span class="input-label flex-display align-center">
                            <label class="bold" for="payment_date">תאריך תשלום:</label>
                            <input type="date" name="payment_date" value="">
                        </span>
                        <span class="input-label flex-display align-center">
                            <label class="bold" for="payment_type">אופן תשלום:</label>
                            <select id="payment_type" name="payment_type"  class=" font-17 grow">
                                <?php echo build_select_options("collection",null,array("filter"=>null,"options"=>true,"field_name"=>"payment_type")) ?>
                            </select>
                        </span>
                        <span class="input-label flex-display align-center">
                            <label class="bold" for="check_number">מס. צ'ק:</label>
                            <input type="text" name="check_number" value="">
                        </span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="ok background-gold bold font-18">אישור</button>
                    <button type="button" class="background-white gold" data-bs-dismiss="modal">ביטול</button>

                </div>
            </div>
        </div>
    </form>
<?php
}
function upload_file($field_name)
{
    //צריך לשמור את הקובץ שהגיע ואם לא הגיע קובץ לכאורה צריך למחוק את הקובץ ואת הקישור להזמנה למשל
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    if (!empty($_FILES) && $_FILES["file_".$field_name]["size"] > 0) {
        $file_id = media_handle_upload("file_".$field_name, 0);
        return $file_id;
    }

    return null;
}
function get_field($table_name, $field_name)
{
    if (array_key_exists($table_name, BOUTIQUE_LISTS)) {
        $page_info = BOUTIQUE_LISTS[$table_name]["columns"];
    }
    else if (array_key_exists($table_name, BOUTIQUE_TABLES)) {
        $page_info = BOUTIQUE_TABLES[$table_name]["columns"];
    }

    $field = array_filter($page_info, function ($field_row) use ($field_name) {
        return isset($field_row["field_name"]) && $field_row["field_name"] == $field_name;
    });
    return count($field) > 0 ? array_pop($field) : null;
}
function is_needed_apostrophe($widget,$un_apostrophe)
{
    if($un_apostrophe)return "";
    $widgets = array("text","date","datetime-local","textarea","email");
    if(in_array($widget, $widgets)){
        return "'";
    }
    return "";
}


add_action('wp_ajax_get_obligation_client', 'get_obligation_client');
function get_obligation_client()
{
    $filters = array();
    $filters[]=array("filter_field" => "id", "filter_value" => $_POST["client_id"]);
    $client = get_data_table("clients", $filters)[0];

    $filters = array();
    $filters[]=array("filter_field" => "client_id", "filter_value" => $_POST["client_id"]);
    $filters[]=array("filter_field" => "payment_date", "filter_type" => "null");
    $result = get_data_table("collection", $filters);
    //write_log("eres ".json_encode($result));
    $obligo = 0;
    foreach ($result as $row){

        $obligo+=$row->obligation;
    }

    //write_log("obligo ".$obligo);
    echo json_encode (array("obligation" => $obligo > $client->obligo));
    die();

}
add_action('wp_ajax_sent_to_manager', 'sent_to_manager');
function sent_to_manager()
{
    $filters = array(array("filter_field" => "id", "filter_value"=>$_POST["id"]));
    $order = get_data_table("orders",$filters)[0];

    $filters = array(array("filter_field" => "id", "filter_value"=>$order->client_id));
    $client = get_data_table("clients",$filters)[0];

    $body = "ללקוח " . $client->name."<br>". "יש חריגה מתשלום יש לו חוב בסכום של: "  .  $client->obligo.
        "<br>"."ותקרת החוב שלו היא:" .  $client->obligo;
    send_mail(get_option('admin_email'),"בקשה לאישור הזמנה חדשה ללקוח: " .$client->name,$body);

    add_notice( 'sent_to_manager' ,"נשלח מייל למנהל לאישור ההזמנה" );
}

function send_late_bills($attr)
{
    write_log("send_who_needs_pay_today");
    $filters = array();
    $filters[] = array("filter_field" => "payment_date", "filter_type" => "null");
    $filters[] = array("filter_field" => "doc_type","filter_value"=>"1");
    $result = get_data_table("collection", $filters);
    //write_log("eres ".json_encode($result));
    $late_pay = "";
    $count = 0;
    foreach ($result as $row) {
        $client = get_data_table("clients", array(array("filter_field" => "client_id", "filter_value" => $row->client_id)));

        if ($attr["type"] == "daily" && $row->payment_until === date('Y-m-d') ||//לבדוק אם היום מוצ"ש ???
            $attr["type"] == "weekly" && $row->payment_until < date('Y-m-d')) {
            $late_pay = "חשבונית מספר " . $row->doc_number . " נקלטה בתאריך" . $row->date .
                " ללקוח " . $client->name . "על סך של " . $row->obligation . "₪ <br>";
            $count++;
        }
    }

    if($count > 0) {
        if ($attr["type"] == "daily") {
            $body = "להלן רשימת החשבוניות שהיו צריכים לשלם אותן היום ועדיין לא שולמו  <br>";
            $subject = "חשבוניות שלא שולמו היום";
        } else if ($attr["type"] == "weekly") {
            $body = "להלן רשימת החשבוניות עדיין לא שולמו<br>";
            $subject = "סיכום שבועי לחשבוניות שלא שולמו";
        }
        $body .= $late_pay;


        send_mail(get_option('admin_email'), $subject, $body);
    }
}
function get_payment_until($payment_term_id,$date)
{
    $lastDayOfMonth = date('Y-m-t', strtotime($date));

    switch ($payment_term_id) {
        case 2://שוטף+60"
            $newDate = date('Y-m-d', strtotime($lastDayOfMonth . ' +60 days'));
            break;
        case 3://שוטף+90"
            $newDate = date('Y-m-d', strtotime($lastDayOfMonth . ' +90 days'));
            break;
        case  1: //מזומן
        default:
            $newDate =$date;
    }
    return $newDate;
}

add_action('wp_ajax_checking_duplicates', 'checking_duplicates');
function checking_duplicates()
{
    $client = get_data_table("clients", array(
            array("filter_field" => "id", "filter_value" => $_POST["client_id"],"filter_type"=>"!="),
            array("filter_field" => "BnNumber", "filter_value" => $_POST["BnNumber"])
    ));

    if(count($client)>0){
        wp_send_json([
            'status' => 'failed',
            'msg' => 'קיים לקוח עם מספר ח_פ כזה',
            'dupple' => true
        ]);
    }
    else{
        wp_send_json(['status' => 'success','msg' => ""]);
    }
}

function get_data_to_export($table_name,$file_type)
{
    $page_info = BOUTIQUE_TABLES[$table_name];
    $filters = array();
    write_log ('id '.json_encode ($_GET["ids"]));
    if(isset($_GET["ids"])){
        $filters[]=array("filter_field"=>"id","filter_value"=>$_GET["ids"],"filter_type" => "array");
    }
    $list = get_data_table ($table_name,$filters);

    $fname = $page_info["title"];
    $headers = [];

    foreach ($page_info["columns"] as $column) {
        if (!isset($column['field_name']) || !isset($column["label"]) || $file_type == "pdf" && isset($column["hide_in_table"])) { continue; }

        $headers[$column["label"]] = get_column_type ($column["widget"]);
    }

    $data = [];
    foreach ($list as $item) {
        $row = [];
        foreach ($page_info["columns"] as $column) {
            if (!isset($column['field_name']) || !isset($column["label"]) || $file_type == "pdf" && isset($column["hide_in_table"]) ) continue;
            $field = isset($column['join_table']) ? substr($column['join_table'], 0, -1) . "_" . $column['join_value'] : $column["field_name"];

            $column_value = get_value($column, $item, $field);
            $row[] = $column_value;

        }
        $data[] = $row;
    }

    return array("headers"=>$headers,"data"=>$data);

}

function  get_column_type($widget)
{
    switch ($widget){
        case "number":
            return "integer";
        case "date":
            return "date";
        default:
            return "string";
    }
}

function get_value($column,$row,$field)
{		$column_value = "";
    switch ($column["widget"]) {
        case "select":
            $column_value = $row->$field;
            break;
        case "radio":
        case "status":
            $column_value = $column["values"][$row->$field]["label"];
            break;
        case "date":
        case "datetime-local":
            if ($row->$field) {
                $timestamp = strtotime($row->$field); // המרת התאריך לאטימות זמן
                $format = 'd/m/Y';
                if ($column["widget"] == "datetime-local") {
                    $format .= " H:i";
                }
                $column_value = date($format, $timestamp);
            }
            break;
        default:
            $column_value = isset($column['list_name']) && isset($list[$row->$field]) ? $list[$row->$field] : $row->$field;
            break;
    }

    return $column_value;
}
?>