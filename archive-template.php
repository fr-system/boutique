<?php /* Template Name: archive */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
?>
<?php get_header();?>
<?php
if(!isset($_GET["subject"])) return;
    $table_name= $_GET["subject"];
    $query = get_page_query($table_name);
   // print_r ("aa ".$query."<br>");
    $result =run_query ($query);

    $fields_arr  = BOUTIQUE_TABLES[$table_name];
  //  write_log("cols  ".json_encode( $fields_arr["columns"]));
?>
<section class="page">
     <h1 class="page-title  font-40 bold"><?= $fields_arr["title"]; ?></h1>
    <div class="archive-actions flex-display">
        <button class="add"><?= get_svg ("new_client");?> </button>
    </div>
    <table name="" class="archive-table">
        <thead><tr class="gold">
            <?php
            foreach($fields_arr["columns"] as $column){
                ?>
                <th><?= $column["label"]?></th>
            <?php } ?>
        </tr></thead>
        <?php foreach($result as $row){
            echo get_tr_data($table_name,$row ,"id");
        }?>
    </table>
</section>

<?php
function get_tr_data($page_name, $data, $id_column){
    //error_log ("add_tr_data");
    global $actions_icons;
    $page_info = BOUTIQUE_TABLES[$page_name];
    $row = is_array ($data)? $data[0]:$data;
    //error_log ('row '.json_encode ($row));
    $html='<tr class="border-dark-gray">';
//        <td data-id="checkbox" class="td-checkbox"><input type="checkbox" class="checkbox-row" value="'.$row->$id_column.'" id=""/></td>';
    foreach($page_info["columns"] as $column) {

        $field = isset($column['join_table']) ? substr($column['join_table'], 0, -1)  . "_" . $column['join_value'] : $column["field_name"];
        $list = isset($column['list_name']) ? constant ($column['list_name']) : null;

        if($field != $id_column){
            if($column['type']=="user_data"){
                write_log ('fiel '.$field);
                write_log ('row '.json_encode ($row));
                $user_field =$column["user_field"];
                $column_value = get_userdata($row->$field)->$user_field;
            }
            else if($column['type']=="action"){
                $column_value = '<button  class="action bg-lightblue" name="'.$column['field_name'].'" onclick="action_func(this)"><i class="'.$actions_icons[$column['field_name']].'"></i><span>פעולה</span></button>';
            }else{
                $column_value = isset($column['list_name']) && isset($list[$row->$field])?$list[$row->$field]: $row->$field;
            }
            $html .='<td>'. $column_value.'</td>';
        }
    }
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
