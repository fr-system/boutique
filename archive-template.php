<?php /* Template Name: archive */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
?>
<?php get_header();?>
<?php
if(!isset($_GET["subject"]) || !is_manager() && $_GET["subject"] == "clients") return;
$table_name = $_GET["subject"];
$page_info  = BOUTIQUE_TABLES[$table_name];
  //  write_log("cols  ".json_encode( $page_info["columns"]));
?>
<section class="page" data-single="<?php echo $page_info['single']?>">
    <?php
        $filter_field = null;
        $filter_value = null;
        $add_text = "";
        $lastKey = array_key_last($_GET);
        if ($lastKey != "subject") {
            $filter_field = $lastKey;
            $filter_value = $_GET[$lastKey];

            global $wpdb;
            $query = "SELECT name from {$wpdb->prefix}clients WHERE " .$filter_field."=".$filter_value;
            $result = run_query ($query);
            $add_text =" של ". $result[0]->name;
            $filter_field = "client_id";
        }


        $archive_actions = view_archive_actions($table_name,false,$add_text,$filter_value);
        echo $archive_actions;

    $table_name = $_GET["subject"];
    $result = get_page_data($table_name,$filter_field,$filter_value);
    $user_meta = get_user_meta( get_current_user_id(), "products_view", true);
    if($table_name == "products" && $user_meta == "gallery"){
        $catalog_gallery = view_catalog_gallery($result,array("table_name"=>"products"));
        echo $catalog_gallery;
    }
    else{
    ?>
    <table name="" class="archive-table">
        <thead><tr class="tr-head gold">
            <?php
            if($table_name == "products"){?>
                <th></th>
            <?php }
            foreach($page_info["columns"] as $column){
                if(isset($column["hidden"]) || !isset($column["label"]) || !empty($add_text) && $column["field_name"]== "client_id"){continue;}
                ?>
                <th><?= $column["label"]?></th>
            <?php } ?>
            <th></th>
            <?php
            if(isset($page_info["actions"])){?>
                <th></th>
            <?php } ?>

        </tr></thead>
        <?php foreach($result as $row){
            echo get_tr_data($table_name,$row ,"id",$add_text);
        }?>
    </table>
    <?php } ?>
</section>
<?php
function get_tr_data($table_name, $data, $id_column,$add_text){
    //error_log ("add_tr_data");
    global $actions_icons;
    $page_info = BOUTIQUE_TABLES[$table_name];
    $row = is_array ($data)? $data[0]:$data;
    //write_log('row '.json_encode ($row));
    //$html='<tr class="border-dark-gray" data-id="'.$row->id.'">';
    $backgraund_class = ($table_name == "orders" && $row->done ? "order-confirm" : "");

    $html='<tr data-id="'.$row->id.'" class="'.$backgraund_class.'">';
//        <td data-id="checkbox" class="td-checkbox"><input type="checkbox" class="checkbox-row" value="'.$row->$id_column.'" id=""/></td>';
    if($table_name == "products"){
        $html.='<td>'.($row->image_id ? '<img class="" src="'.wp_get_attachment_url($row->image_id) .'" /></div>':'') .'</td>';
    }

    foreach($page_info["columns"] as $column) {
        if (!isset($column['field_name']) || $column["field_name"]== "client_id" && !empty($add_text)) {
            continue;
        }

        $field = isset($column['join_table']) ? substr($column['join_table'], 0, -1) . "_" . $column['join_value'] : $column["field_name"];
        $list = isset($column['table_name']) ? constant($column['table_name']) : null;

        if ($field != $id_column && !isset($column["hidden"]) && isset($column["label"])) {
            $column_value = get_column_value($column,$row,$field,$list);
            //else if($column['type']=="action"){
            //$column_value = '<button  class="action bg-lightblue" name="'.$column['field_name'].'" onclick="action_func(this)"><i class="'.$actions_icons[$column['field_name']].'"></i><span>פעולה</span></button>';
            //}
            //else {

            //}
            $html .= '<td >' . $column_value . '</td>';
        }
    }
    if(isset($page_info["actions"])) {
        foreach ($page_info["actions"] as $action) {
            $html .= '<td ><a class="button background-white dark-green bold font-18" href="/archive?subject=' . $action . '&id=' . $row->id . '">' . BOUTIQUE_TABLES[$action]["title"] . '</a></td>';
        }
    }
//write_log("row ".json_encode($row));
    if($table_name != "orders" || $row->done == 0) {
        $html .= '<td><a href="single?subject=' . $table_name . '&action=edit&id=' . $row->id . '">
                        <svg class="edit-row" xmlns="http://www.w3.org/2000/svg" width="24" height="23" viewBox="0 0 24 23" fill="none">
                            <path d="M7 16.3041L11.413 16.2898L21.045 7.14726C21.423 6.78501 21.631 6.30393 21.631 5.79218C21.631 5.28043 21.423 4.79934 21.045 4.43709L19.459 2.91717C18.703 2.19267 17.384 2.19651 16.634 2.9143L7 12.0587V16.3041ZM18.045 4.27226L19.634 5.7893L18.037 7.30538L16.451 5.78643L18.045 4.27226ZM9 12.858L15.03 7.13384L16.616 8.65376L10.587 14.376L9 14.3808V12.858Z" fill="#E2B252"/>
                            <path d="M5 20.125H19C20.103 20.125 21 19.2654 21 18.2083V9.9015L19 11.8182V18.2083H8.158C8.132 18.2083 8.105 18.2179 8.079 18.2179C8.046 18.2179 8.013 18.2093 7.979 18.2083H5V4.79167H11.847L13.847 2.875H5C3.897 2.875 3 3.73462 3 4.79167V18.2083C3 19.2654 3.897 20.125 5 20.125Z" fill="#E2B252"/>
                        </svg></a>
                  </td>';
    }
    if($table_name == "orders" && $row->done == 1) {
        $html .= '<td><a href="single?subject=' . $table_name . '&action=readonly&id=' . $row->id . '">
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
                        <path d="M12.5 5C5.93017 5 2.74267 10.683 2.17704 11.808C2.14681 11.8678 2.1311 11.9335 2.1311 12C2.1311 12.0665 2.14681 12.1322 2.17704 12.192C2.74163 13.317 5.92913 19 12.5 19C19.0708 19 22.2573 13.317 22.8229 12.192C22.8531 12.1322 22.8688 12.0665 22.8688 12C22.8688 11.9335 22.8531 11.8678 22.8229 11.808C22.2583 10.683 19.0708 5 12.5 5Z" stroke="#E2B252" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12.5 15C14.2259 15 15.625 13.6569 15.625 12C15.625 10.3431 14.2259 9 12.5 9C10.7741 9 9.375 10.3431 9.375 12C9.375 13.6569 10.7741 15 12.5 15Z" stroke="#E2B252" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        </a>
                  </td>';
    }
    $html .='<td >';
    $html .='<svg class="remove-row"  xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
<path d="M4.16663 7H20.8333M10.4166 11V17M14.5833 11V17M5.20829 7L6.24996 19C6.24996 19.5304 6.46945 20.0391 6.86015 20.4142C7.25085 20.7893 7.78076 21 8.33329 21H16.6666C17.2192 21 17.7491 20.7893 18.1398 20.4142C18.5305 20.0391 18.75 19.5304 18.75 19L19.7916 7M9.37496 7V4C9.37496 3.73478 9.48471 3.48043 9.68006 3.29289C9.87541 3.10536 10.1404 3 10.4166 3H14.5833C14.8596 3 15.1245 3.10536 15.3199 3.29289C15.5152 3.48043 15.625 3.73478 15.625 4V7" stroke="#E2B252" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>';
    $html .='</td>';
    //$html .='<td></td>';
    /*if(isset($page_info["actions"]) && is_array($page_info["actions"])) {
        $html .='<td class="flex-display space-around">';
        foreach($page_info["actions"] as $action) {
            $html .='<button  class="action bg-lightblue" name="'.$action.'" onclick="action_func(this)">               
                 <i class="'. $actions_icons[$action].'"></i><span>פעולה</span></button>';
        }
        $html .='</td>';
    }*/
    $html .='</tr>';
    //error_log ("add_tr_data enf ".$html);
    return $html;
}
?>
