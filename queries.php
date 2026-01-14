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

function build_query_boutique($table_name)
{
    $update = array();
    $fields = array();
    $values = array();

    foreach ($_POST as $key => $value) {
        $field = get_field($table_name, $key);
        if($field != null){
            $apostrophe = is_needed_apostrophe($field["widget"],isset($field["un_apostrophe"]));
            array_push($fields,$key);
            array_push($values,$apostrophe . $value . $apostrophe );
            array_push($update, $key." = " . $apostrophe . $value . $apostrophe );
        }
    }

    if($_POST["id"]) {//"update"
            $query = "UPDATE " . $table_name . " set " . implode(",", $update) . " where id = " . $_POST["id"];
    }
    else{//new

        /*if($table_name == "tasks"){
            $fields.= " date_write, assignor_id"." ,";
            $values .= " NOW(), ". get_current_user_id() ." ,";
        }*/

        $query = "INSERT INTO ".$table_name." (".implode(",", $fields)." ) select " . implode(",", $values);
    }
    write_log(" query " . $query);
    $ok = run_query($query, "execute");
    //write_log("ok run_query " . $ok);
    return  $ok;

}

function get_field($table_name, $field_name)
{
    $fields = BOUTIQUE_TABLES[$table_name]["columns"];
    $field = array_filter($fields, function ($field_row) use ($field_name) {
        return $field_row["field_name"] == $field_name;
    });

    return count($field) > 0 ? array_pop($field) : null;
}

function get_query($table_name,$field_name=null,$field_value=null)
{
    $query = "SELECT * FROM " .$table_name;
    if($field_name=!null && $field_value!=null){
        $field = get_field($table_name, $field_name);
        if($field != null){
            $apostrophe = is_needed_apostrophe($field["widget"],isset($field["un_apostrophe"]));
            $query.=" WHERE ".$field_name." = ".$apostrophe.$field_value.$apostrophe;
        }
    }
    $results = run_query($query);
    return $results;
}
function get_page_query($table_name,$field_filter=null ,$filter_value=null)
{
    $columns = BOUTIQUE_TABLES[$table_name]["columns"];
    $join = "";
    $query = "SELECT ";
    foreach ($columns as $column) {
        if ($column["type"] == "action" || $column["type"] == "user_data" && !isset($column['join_table'])) continue;
        $query .=$table_name.".". $column["field_name"] . ", ";
        if (isset($column['join_table'])) {
            $query .= $column['join_table'] . "." . $column['join_value'] . " AS ".substr($column['join_table'], 0, -1)  . "_" . $column['join_value'].", ";
            $join .= " LEFT JOIN " . $column['join_table'] . " ON " . $table_name . "." . $column["field_name"] . " = " . $column['join_table'] . ".id";
        }
    }
    $query = substr($query,0,-2);
    $query .= " FROM ".$table_name. $join;
    if($field_filter=!null && $filter_value!=null){
        $field = get_field($table_name, $field_filter);
        if($field != null){
            $apostrophe = is_needed_apostrophe($field["widget"],isset($field["un_apostrophe"]));
            $query.=" WHERE ".$field_filter." = ".$apostrophe.$filter_value.$apostrophe;
        }
    }
//    if($filter_value!= 0){
//        $query .= " WHERE ".get_id_column_in_page($page_name)." = ".$filter_value;
//    }
    return $query ;
}

function is_needed_apostrophe($widget,$un_apostrophe)
{
    if($un_apostrophe)return "";
    $widgets = array("text","date","textarea");
    if(in_array($widget, $widgets)){
        return "'";
    }
    return "";
}

function get_fields_list($list_name)
{
    switch($list_name) {
        case "cities":
            return array( "field_name" => "name", "filter"=>"area_id != true");
    }
    return array();
}

function get_list($list_name){

    $fields_list = get_fields_list($list_name);
    $query = "SELECT id as value,".$fields_list["field_name"]." as text 
              FROM ".$list_name;
    /*if(isset($fields_list["join_table"]) && $fields_list["join_table"] != "" && isset($fields_list["join_table_value"])){
        $query.=" JOIN #_".$fields_list["join_table"]." on #_".$fields_list["join_table"].".".$fields_list["join_table_value"]." = #_".$fields_list["table_name"].".".$fields_list["field_value"];
    }
    $query .= " group by #_".$fields_list["table_name"].".".$fields_list["field_value"].", #_".$fields_list["table_name"].".".$fields_list["field_name"];*/

    if(isset($fields_list["filter"])){
        $query .= " WHERE ".$fields_list["filter"];
    }

    $list = run_query($query);
    return $list;
}
?>