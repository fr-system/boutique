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

    //write_log("CLIENTS_FIELDS ".json_encode(CLIENTS_FIELDS));

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
function get_field_type($table_name, $field_name)
{
    $field = array_filter(FIELDS[$table_name], function ($field_row) use ($field_name) {
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

?>