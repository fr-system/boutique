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
        $type = get_field_type($table_name, $key);
        if($type !== false){
            $delimiter = $type == "text" || $type == "date" ? "'" : "";
            array_push($fields,$key);
            array_push($values,$delimiter . $value . $delimiter );
            array_push($update, $key." = " . $delimiter . $value . $delimiter );
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

function get_table_obj($table_name)
{
    $table_obj = array_filter(BOUTIQUE_TABLES, function ($table) use ($table_name) {
        //write_log("fff ".json_encode($table). "fff ".$table_name);
        return $table["subject"] == $table_name;
    });
    return $table_obj[0];
}
function get_table_by_parameter($table_name,$parameter)
{
    $table_ogj = get_table_obj($table_name);
    write_log("table ".json_encode($table_ogj)." param ".$parameter);
    return $table_ogj[$parameter];
}

function get_field_type($table_name, $field_name)
{
    $fields = get_table_by_parameter($table_name,"columns");
    $field = array_filter($fields, function ($field_row) use ($field_name) {
        return $field_row["field_name"] == $field_name;
    });
    if (count($field) > 0) {
        $type = array_pop($field)["type"];
        return $type;
    }
    return false;
}

function get_query($table_name,$field_name=null,$field_value=null)
{
    $query = "SELECT * FROM " .$table_name;
    if($field_name=!null && $field_value!=null){
        $type = get_field_type($table_name, $field_name);
        if($type !== false){
            $delimiter = $type == "text" || $type == "date" ? "'" : "";
            $query.=" WHERE ".$field_name." = ".$delimiter.$field_value.$delimiter;
        }
    }
    $results = run_query($query);
    return $results;
}
function get_page_query($table_name,$field_filter=null ,$filter_value=null)
{
    $columns = get_table_by_parameter($table_name,"columns");
    $join = "";
    $query = "SELECT ";
    foreach ($columns as $column) {
        if ($column["type"] == "action") continue;
        $query .=$table_name.".". $column["field_name"] . ", ";
        if (isset($column['join_table'])) {
            $query .= $column['join_table'] . "." . $column['join_value'] . " AS ".substr($column['join_table'], 0, -1)  . "_" . $column['join_value'].", ";
            $join .= " LEFT JOIN " . $column['join_table'] . " ON " . $table_name . "." . $column["field_name"] . " = " . $column['join_table'] . ".id";
        }
    }
    $query = substr($query,0,-2);
    $query .= " FROM ".$table_name. $join;
    if($field_filter=!null && $filter_value!=null){
        $type = get_field_type($table_name, $field_filter);
        if($type !== false){
            $delimiter = $type == "text" || $type == "date" ? "'" : "";
            $query.=" WHERE ".$field_filter." = ".$delimiter.$filter_value.$delimiter;
        }
    }
//    if($filter_value!= 0){
//        $query .= " WHERE ".get_id_column_in_page($page_name)." = ".$filter_value;
//    }
    return $query ;
}
?>