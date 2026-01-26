<?php /* Template Name: archive */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
?>
<?php get_header();?>
<?php
if(!isset($_GET["subject"]) || !is_manager() && $_GET["subject"] == "clients") return;
$table_name = $_GET["subject"];
$query = get_page_query($table_name);
   // print_r ("aa ".$query."<br>");
$result = run_query ($query);
$fields_arr  = BOUTIQUE_TABLES[$table_name];
  //  write_log("cols  ".json_encode( $fields_arr["columns"]));
?>
<section class="page">
    <?php
    if($table_name == "lists"){   ?>
    <h1 class="page-title  font-30 bold">רשימות</h1>
        <select><?php
            foreach (BOUTIQUE_LISTS as $B_LIST){
               echo "<option>".$B_LIST->title."</option>";
            }
            ?>

        </select>
     <?php
    }
    ?>

    <div class="archive-actions flex-display end">
        <?php //get_svg ("clients","new",false,"class-name"); ?>
        <a href="<?php echo 'single?subject='.$table_name.'&action=new' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                <circle cx="30" cy="30" r="29.5" fill="#1A7870" stroke="white"/>
                <line x1="30" y1="20" x2="30" y2="42" stroke="white" stroke-width="2"/>
                <line x1="41" y1="31" x2="19" y2="31" stroke="white" stroke-width="2"/>
            </svg>
        </a>

    </div>
    <?php
    $user_meta = get_user_meta( get_current_user_id(), "products_view", true);
    if($table_name == "products" && $user_meta == "gallery"){
        view_catalog_gallery($result);
    }
    else{
    ?>
    <table name="" class="archive-table">
        <thead><tr class="gold">
            <?php
            foreach($fields_arr["columns"] as $column){
                ?>
                <th><?= $column["label"]?></th>
            <?php } ?>
            <th></th>
        </tr></thead>
        <?php foreach($result as $row){
            echo get_tr_data($table_name,$row ,"id");
        }?>
    </table>
    <?php } ?>
</section>
<?php
function get_tr_data($page_name, $data, $id_column){
    //error_log ("add_tr_data");
    global $actions_icons;
    $page_info = BOUTIQUE_TABLES[$page_name];
    $row = is_array ($data)? $data[0]:$data;
    //error_log ('row '.json_encode ($row));
    $html='<tr class="border-dark-gray" data-id="'.$row->id.'">';
//        <td data-id="checkbox" class="td-checkbox"><input type="checkbox" class="checkbox-row" value="'.$row->$id_column.'" id=""/></td>';
    foreach($page_info["columns"] as $column) {
        $field = isset($column['join_table']) ? substr($column['join_table'], 0, -1)  . "_" . $column['join_value']: $column["field_name"];
        $list = isset($column['table_name'])? constant($column['table_name']):null;

        if($field != $id_column){
            if(isset($column['type']) && $column['type']=="user_data") {
                //write_log ('fiel ' . $field);
               // write_log ('row ' . json_encode ($row));
                $user_field = $column["user_field"];
                $column_value = empty($row->$field) ? '' : get_userdata ($row->$field)->$user_field;

            }
            //else if($column['type']=="action"){
                //$column_value = '<button  class="action bg-lightblue" name="'.$column['field_name'].'" onclick="action_func(this)"><i class="'.$actions_icons[$column['field_name']].'"></i><span>פעולה</span></button>';
            //}
        else{
                $column_value = isset($column['list_name']) && isset($list[$row->$field])?$list[$row->$field]: $row->$field;
            }
            $html .='<td>'. $column_value.'</td>';
        }
    }

    $html .='<td class="flex-display space-around">';
    $html .='<a href="single?subject='.$_GET["subject"].'&action=edit&id='.$row->id.'">
<svg class="edit-row" xmlns="http://www.w3.org/2000/svg" width="24" height="23" viewBox="0 0 24 23" fill="none">
    <path d="M7 16.3041L11.413 16.2898L21.045 7.14726C21.423 6.78501 21.631 6.30393 21.631 5.79218C21.631 5.28043 21.423 4.79934 21.045 4.43709L19.459 2.91717C18.703 2.19267 17.384 2.19651 16.634 2.9143L7 12.0587V16.3041ZM18.045 4.27226L19.634 5.7893L18.037 7.30538L16.451 5.78643L18.045 4.27226ZM9 12.858L15.03 7.13384L16.616 8.65376L10.587 14.376L9 14.3808V12.858Z" fill="#E2B252"/>
    <path d="M5 20.125H19C20.103 20.125 21 19.2654 21 18.2083V9.9015L19 11.8182V18.2083H8.158C8.132 18.2083 8.105 18.2179 8.079 18.2179C8.046 18.2179 8.013 18.2093 7.979 18.2083H5V4.79167H11.847L13.847 2.875H5C3.897 2.875 3 3.73462 3 4.79167V18.2083C3 19.2654 3.897 20.125 5 20.125Z" fill="#E2B252"/>
</svg>
</a>';
    $html .='<svg class="remove-row pointer"  xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
<path d="M4.16663 7H20.8333M10.4166 11V17M14.5833 11V17M5.20829 7L6.24996 19C6.24996 19.5304 6.46945 20.0391 6.86015 20.4142C7.25085 20.7893 7.78076 21 8.33329 21H16.6666C17.2192 21 17.7491 20.7893 18.1398 20.4142C18.5305 20.0391 18.75 19.5304 18.75 19L19.7916 7M9.37496 7V4C9.37496 3.73478 9.48471 3.48043 9.68006 3.29289C9.87541 3.10536 10.1404 3 10.4166 3H14.5833C14.8596 3 15.1245 3.10536 15.3199 3.29289C15.5152 3.48043 15.625 3.73478 15.625 4V7" stroke="#E2B252" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>';
    $html .='</td>';
    //$html .='<td></td>';
    if(isset($page_info["actions"]) && is_array($page_info["actions"])) {
        $html .='<td class="flex-display space-around">';
        foreach($page_info["actions"] as $action) {
            $html .='<button  class="action bg-lightblue" name="'.$action.'" onclick="action_func(this)">               
                 <i class="'. $actions_icons[$action].'"></i><span>פעולה</span></button>';
        }
        $html .='</td>';
    }
    $html .='</tr>';
    //error_log ("add_tr_data enf ".$html);
    return $html;
}
?>
