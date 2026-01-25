<?php
function run_query($query, $type="")
{
    global $wpdb;

    if ($type == "execute") {
        $result = $wpdb->query($query);
    } else {
        $result = $wpdb->get_results($query);
    }

    //error_log("result ". json_encode($result));
    return $result;
}
add_action('wp_ajax_build_query_boutique', 'build_query_boutique');
function build_query_boutique()
{
    $table_name = $_POST["table_name"];
    global $wpdb;
    $update = array();
    $fields = array();
    $values = array();
    if(isset($_POST["remove"])){}
    else {
        foreach ($_POST as $key => $value) {
            $field = get_field($table_name, $key);
            if ($field != null) {
                if (!empty($value) && isset($field["un_apostrophe"])) {
                    write_log("val " . PHP_FLOAT_MAX);
                    write_log("val2 " . str_replace("₪", "", $value));

                    $value = floatval(str_replace("₪", "", $value));
                }

                $apostrophe = is_needed_apostrophe($field["widget"], isset($field["un_apostrophe"]));
                array_push($fields, $key);
                array_push($values, (empty($value) ? "NULL" : $apostrophe . $value . $apostrophe));
                array_push($update, $key . " = " . (empty($value) ? "NULL" : $apostrophe . $value . $apostrophe));
            }
        }
    }

    if($_POST["id"]) {
        if (isset($_POST["remove"])) {//remove
            $query = "DELETE FROM {$wpdb->prefix}". $table_name;
        } else {//"update"
            $query = "UPDATE {$wpdb->prefix}" . $table_name . " set " . implode(",", $update);
        }
        $query .= " where id = " . $_POST["id"];
    }
    else{//new

        /*if($table_name == "tasks"){
            $fields.= " date_write, assignor_id"." ,";
            $values .= " NOW(), ". get_current_user_id() ." ,";
        }*/

        $query = "INSERT INTO {$wpdb->prefix}".$table_name." (".implode(",", $fields)." ) select " . implode(",", $values);
    }
    write_log(" query " . $query);
    $ok = run_query($query, "execute");
    write_log("ok run_query " . $ok);
    //return  $ok;
    echo json_encode( array(
        'status'   => 'success',
        //'redirect' => $_POST["previous_page"],
    ) );
    die();

}

function get_field($table_name, $field_name)
{
    $fields = BOUTIQUE_TABLES[$table_name]["columns"];
    $field = array_filter($fields, function ($field_row) use ($field_name) {
        return $field_row["field_name"] == $field_name;
    });

    return count($field) > 0 ? array_pop($field) : null;
}

function get_page_query($table_name,$field_filter=null ,$filter_value=null)
{
    global $wpdb;
    $columns = BOUTIQUE_TABLES[$table_name]["columns"];
    $join = "";

    $query = "SELECT ".$wpdb->prefix.$table_name.".id, ";
    foreach ($columns as $column) {
       // if (/*$column["type"] == "action" ||*/isset($column["type"]) && $column["type"] == "user_data" && !isset($column['join_table'])) continue;
        $query .=$wpdb->prefix.$table_name.".". $column["field_name"] . ", ";
        if (isset($column['join_table'])) {
            $query .= $wpdb->prefix.$column['join_table'] . "." . $column['join_value'] . " AS ".substr($column['join_table'], 0, -1)  . "_" . $column['join_value'].", ";
            $join .= " LEFT JOIN " . $wpdb->prefix.$column['join_table'] . " ON " .$wpdb->prefix. $table_name . "." . $column["field_name"] . " = " . $wpdb->prefix.$column['join_table'] . ".id";
        }
    }

    $query = substr($query,0,-2);
    //write_log("f1 ".$field_filter);
    $query .= " FROM ".$wpdb->prefix.$table_name. $join;
    if($field_filter=!null && $filter_value!=null){

        $field = get_field($table_name, $field_filter);
        if($field != null){
            $apostrophe = is_needed_apostrophe($field["widget"],isset($field["un_apostrophe"]));
            //write_log("f2 ".$field_filter);
            $query.=" WHERE ".$field_filter." = ".$apostrophe.$filter_value.$apostrophe;
        }
    }
//    if($filter_value!= 0){
//        $query .= " WHERE ".get_id_column_in_page($page_name)." = ".$filter_value;
//    }
    write_log ('select '.$query);
    return $query ;
}

function is_needed_apostrophe($widget,$un_apostrophe)
{
    if($un_apostrophe)return "";
    $widgets = array("text","date","textarea","email");
    if(in_array($widget, $widgets)){
        return "'";
    }
    return "";
}

function get_fields_list($table_name)
{
    switch($table_name) {
        case "agents":
            break;
        default:

    }
    return array();
}

function build_options($table_name,$value=null,$filter=null)
{
    $fields_list = BOUTIQUE_TABLES[$table_name];
    if(isset($fields_list["data-field"])) {
        $field = $fields_list["data-field"];
    }
    $list = get_list($table_name,$filter);
    //write_log("list " .json_encode($fields_list));
    $options = '<option value=""></option>';
    foreach ($list as $row) {
        //$options .= '<option '.(isset($fields_list["data-field"])?'data-field="'.$row[$fields_list["data-field"]].'"':'').' value="' . $row->value . '"' . (!empty($value) && in_array($row->value, $value) ? 'selected' : '') . '>' . $row->text . '</option>';
        $data_field = "";
        if(isset($fields_list["data-field"])){
            $data_field =' data-field="'.$row->$field.'"';
        }
        $options .= '<option '.$data_field.' value="' . $row->value . '"' . (!empty($value)&& is_array($value) && in_array($row->value, $value) ? 'selected' : '') . '>' . $row->text . '</option>';
    }
    //write_log("options" .$options);
    return $options;
}

function get_list($table_name,$filter){
    global $wpdb;
    $fields_list = BOUTIQUE_TABLES[$table_name];
    $field_name = $fields_list["columns"][0]["field_name"];
    $fields=array();

    $query = "SELECT id as value, ".$field_name." as text";
    if(isset($fields_list["data-field"])) {
        $query .= ", ".$fields_list["data-field"];
    }
    $query .= " FROM ".$wpdb->prefix.$table_name;
    /*if(isset($fields_list["join_table"]) && $fields_list["join_table"] != "" && isset($fields_list["join_table_value"])){
        $query.=" JOIN #_".$fields_list["join_table"]." on #_".$fields_list["join_table"].".".$fields_list["join_table_value"]." = #_".$fields_list["table_name"].".".$fields_list["field_value"];
    }
    $query .= " group by #_".$fields_list["table_name"].".".$fields_list["field_value"].", #_".$fields_list["table_name"].".".$fields_list["field_name"];*/

    if(isset($fields_list["filter"])){
        $query .= " WHERE ".$fields_list["filter"];
    }
    else if(!empty($filter)){
        $query .= " WHERE ".$filter;
    }
    //write_log("quert ".$query." table_name ".$table_name." field_name ".$field_name);
    $list = run_query($query);

    if($table_name == "agents") {
        $users = array();
        foreach ($list as $row) {
            $user_info = get_userdata($row->value);
            if ($user_info) {
                $display_name = $user_info->display_name;
                $row->text = $display_name;
                array_push($users,$row);
            }
        }
        return $users;
    }

    //write_log("list ".json_encode($list));
    return $list;
}
function test_mode_table_prefix() {
    if(get_user_test_mode()) {
        global $wpdb;
        $wpdb->prefix = 'test_';
        //write_log ("prefix ".$wpdb->prefix);
    }
}
add_action('init', 'test_mode_table_prefix');
?>