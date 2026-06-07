<?php

test_mode_table_prefix();
function run_query($query, $type="")
{
    //write_log("qu ".$query);
    global $wpdb;

    if ($type == "execute") {
        $result = $wpdb->query($query);
    } else {
        $result = $wpdb->get_results($query);
    }

    //write_log("result ". json_encode($result));
    return $result;
}

function pre_action_query($table_name, $row){
    $update = array();
    $fields = array();
    $values = array();

    //write_log("row ".json_encode($row));
    foreach ($row as $key => $value) {
        $field = get_field($table_name, $key);
        if ($field != null) {
            /*if($field["widget"] == "file" || $field["widget"] == "image"){
                $value = upload_file($field["field_name"]);
            }
            else {*/
                if (!empty($value) && (isset($field["un_apostrophe"]) || $field["widget"] == "file"|| $field["widget"] == "image")) {
                    $value = str_replace("₪", "", $value);
                    $value = str_replace(",", "", $value);
                    $value = (int)$value;
                }
            /*}*/

            $apostrophe = is_needed_apostrophe($field["widget"], isset($field["un_apostrophe"]));
            array_push($fields, $key);
            array_push($values, (empty($value) ? "NULL" : $apostrophe . $value . $apostrophe));
            array_push($update, $key . " = " . (empty($value) ? "NULL" : $apostrophe . $value . $apostrophe));
        }
    }

    return array("fields"=>$fields, "values"=>$values,"update"=>$update);
}

function run_action_query($table_name, $id, $action, $options)
{
    global $wpdb;

    switch ($action) {
        case "remove":
            $query = "DELETE FROM {$wpdb->prefix}" . $table_name;
            if($table_name == "orders"){
                run_query("DELETE FROM {$wpdb->prefix}order_products WHERE order_id = ".$id, "execute");
            }
            //$id = 999999;
            break;
        case "update":
            $query = "UPDATE {$wpdb->prefix}".$table_name." SET ". implode(",", $options["update"]);
            break;
        case "new":
            $query = "INSERT INTO {$wpdb->prefix}" . $table_name . " (" . implode(",", $options["fields"]) . " ) 
                SELECT " . implode(",", $options["values"]);
            break;
    }
    if (!empty($id)) {
        $query .= " where id = " . $id;
    }
    $ok = run_query($query, "execute");
    return $ok;
}

add_action('wp_ajax_save_single_data', 'save_single_data');
function save_single_data()
{
    $table_name = $_POST["table_name"];
    if (($table_name == "agents" || $table_name == "suppliers"))  {
        if(empty($_POST["id"])) {
            $result = register_new_user1($_POST["name"], $_POST["email"], $table_name);
            if ($result == null || $result["status"] == "failed") {
                echo json_encode($result);
                die();
            }
            $_POST["user_id"] = $result["user_id"];
        }
        else if(!empty($_POST["user_id"])){
            $result = wp_update_user([
                'ID'         => $_POST["user_id"],
                'display_name' => $_POST["name"],
                'user_email' => $_POST["email"],
            ]);
        }
    }

    global $wpdb;
    if (isset($_POST["remove"]) && $_POST["remove"]) {
        $action = "remove";
        $result = array();
    } else {
        $action = isset($_POST["id"]) && !empty($_POST["id"]) ? "update" : "new";
        $result = pre_action_query ($table_name, $_POST);
    }

    $id = isset($_POST["id"]) ? $_POST["id"] : null;
    run_action_query ($table_name, $id, $action, $result);
    if (!$_POST["id"]) {
        $id = $wpdb->get_var ("SELECT MAX(id) FROM {$wpdb->prefix}" . $table_name);
    }

    if ($table_name == "orders" && isset($_POST["products"])) {
        foreach ($_POST["products"] as $row) {
            if (isset($row["product_id"]) && !empty($row["product_id"]) && !empty($id)) {
                if ($action == "new") {
                    $row["order_id"] = $id;
                }

                $action_product = (isset($row["id"]) && !empty($row["id"])) ?
                    (isset($row["remove"]) && $row["remove"] ? "remove" : "update") : "new";
                // write_log("row order product" . json_encode($row));
                $result = pre_action_query ("order_products", $row);
                run_action_query ("order_products", $row["id"], $action_product, $result);
            }
        }
    }
    echo json_encode (array(
        'status' => 'success',
        'id' => $id,
        'redirect' => (isset($_POST["previous_page"]) ? $_POST["previous_page"] : ''),
    ));
    wp_die ();

}
function get_data_table($table_name, $filters=null, $orderby = null, $join_filter=null)
{
    global $wpdb;
    $wpdb->prefix = 'test_';
    $apostrophe = "";

    $columns = BOUTIQUE_TABLES[$table_name]["columns"];
    $join = "";

    $query = "SELECT ".$wpdb->prefix.$table_name.".id, ";
    $i = 0;
    foreach ($columns as $column) {

        if(!isset($column["field_name"]) || isset($column["type"]) && $column["type"] == "user_data"  )continue;
       // if (/*$column["type"] == "action" ||*/isset($column["type"]) && $column["type"] == "user_data" && !isset($column['join_table'])) continue;
        $i++;
        $query .=$wpdb->prefix.$table_name.".". $column["field_name"] . ", ";
        if (isset($column['join_table'])) {
            if(isset($column['join_value'])) {
                $query .= $wpdb->prefix . $column['join_table'] . "." . $column['join_value'] . " AS " . substr($column['join_table'], 0, -1) . "_" . $column['join_value'] . ", ";
            }
            else if(isset($column['join_values_select'])){
                foreach ($column['join_values_select'] as $join_values_select){
                    $query .= $wpdb->prefix . $column['join_table'] . "." . $join_values_select. ", ";
                }
            }
            else{
                $query .= $wpdb->prefix . $column['join_table'] . ".*, " ;
            }
            $join .= " LEFT JOIN " . $wpdb->prefix.$column['join_table'] . " ON " .$wpdb->prefix. $table_name . "." . $column["field_name"] . " = " . $wpdb->prefix.$column['join_table'] .".id";
        }

    }
    if($table_name=="products") {
        $query .= " pc.client_price, " ;
        $join .= " LEFT JOIN " .$wpdb->prefix . "products_clients as pc 
        ON {$wpdb->prefix}products.id = pc.product_id ".
            (empty($join_filter)?'':"AND ".$join_filter);
    }
    $query = substr($query,0,-2);
    $query .= " FROM ".$wpdb->prefix.$table_name. $join;
    if($filters!=null) {
        $filter_str = array();
        foreach ($filters as $filter) {
            if(!isset($filter["filter_type"])){
                $filter["filter_type"]="";
            }
            //write_log("filter ".json_encode($filter));
            if ($filter["filter_field"] == "id") {
                $filter_field = $wpdb->prefix . $table_name . "." . $filter["filter_field"];
                $apostrophe = "";
            } else {
                $filter_field = (isset($filter["filter_table"]) ? $wpdb->prefix . $filter["filter_table"] . "." : "") . $filter["filter_field"];
                $field = get_field($table_name, $filter["filter_field"]);
                if ($field != null && isset($filter["filter_value"]) && $filter["filter_value"] != null) {
                    $apostrophe = is_needed_apostrophe($field["widget"], isset($field["un_apostrophe"]));
                }
            }

            switch ($filter["filter_type"]) {
                case "date":
                    $filter_str[] = $filter_field . " " . $filter["filter_ratio"] . " " . $filter["filter_value"];
                    break;
                case "null":
                    $filter_str[] = $filter_field . " is null ";
                    break;
                case "not_null":
                    $filter_str[] = $filter_field . " is not null ";
                    break;
                case "array":
                    $filter_str[] = $filter_field . " in ( " . $apostrophe . $filter["filter_value"] . $apostrophe . ")";
                    break;
                case "!=":
                    if($filter["filter_value"]){
                        $filter_str[] = $filter_field . " != " . $apostrophe . $filter["filter_value"] . $apostrophe;
                    }
                    break;
                default:
                    $filter_str[] = $filter_field . " = " . $apostrophe . $filter["filter_value"] . $apostrophe;
                    break;
            }

           /* if (isset($filter["filter_type"]) && $filter["filter_type"] == "date") {
                $filter_str[] = $filter_field . " " . $filter["filter_ratio"] . " " . $filter["filter_value"];
            } else if (isset($filter["filter_type"]) && $filter["filter_type"] == "null") {
                $filter_str[] = $filter_field . " is null ";
            }
            else if (isset($filter["filter_type"]) && $filter["filter_type"] == "not_null") {
                $filter_str[] = $filter_field . " is not null ";
            }
            else if(isset($filter["filter_type"]) && $filter["filter_type"] == "array"){
                $filter_str[] = $filter_field . " in ( " . $apostrophe . $filter["filter_value"] . $apostrophe . ")";
            }
            else {
                $filter_str[] = $filter_field . " = " . $apostrophe . $filter["filter_value"] . $apostrophe;
            }*/
        }
        $query .= " WHERE " . implode(" AND ", $filter_str);

    }

    if($orderby != null){
        $query .= " ORDER BY " . $orderby;
    }

    //echo $query;
    write_log("query ".$query);
//    if($filter_value!= 0){
//        $query .= " WHERE ".get_id_column_in_page($page_name)." = ".$filter_value;
//    }
    $result = run_query ($query);
   // write_log("res ".json_encode($result));
    return $result ;
}

function get_list($list_name,$filter = '',$table_display =false)
{
    global $wpdb;
    if (array_key_exists ($list_name, BOUTIQUE_LISTS)) {
        $page_info = BOUTIQUE_LISTS[$list_name];
    } else if (array_key_exists ($list_name, BOUTIQUE_TABLES)) {
        $page_info = BOUTIQUE_TABLES[$list_name];
    }
    $table_name=$list_name;
    $join="";
    $field_name = $page_info["columns"][0]["field_name"];
    if ($table_display) {
        $query = "SELECT {$wpdb->prefix}{$table_name}.id ,";
        foreach ($page_info["columns"] as $column) {
            if (isset($column['join_table'])) {
                if(isset($column['join_value'])) {
                    $query .= $wpdb->prefix . $column['join_table'] . "." . $column['join_value'] . " AS " . $column['join_value'] . ", ";
                }
                else{
                    $query .= $wpdb->prefix . $column['join_table'] . ".*, " ;
                }
                $join .= " LEFT JOIN " . $wpdb->prefix.$column['join_table'] . " ON " .$wpdb->prefix. $table_name . "." . $column["field_name"] . " = " . $wpdb->prefix.$column['join_table'] . ".id";
            }
            $query .= $column["field_name"].", ";
        }
        $query = substr ($query,0,-2);
    }
    else {
        $query = "SELECT id as value , ". $field_name . " as text";
    }
    if(isset($page_info["data-field"])) {
        $query .= ", ".$page_info["data-field"];
    }
    $table_name=$list_name;
    $query .= " FROM ".$wpdb->prefix.$table_name.$join;

    if(isset($page_info["filter"])){
        $query .= " WHERE ".$page_info["filter"];
    }
    else if(!empty($filter)){
        $query .= " WHERE ".$filter;
    }
    //write_log("quert ".$query." table_name ".$table_name." field_name ".$field_name);
    $list = run_query($query);
    /*if($table_name == "agents") {
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
    }*/

    //write_log("list ".json_encode($list));
    return $list;
}
function test_mode_table_prefix() {
    //if(get_user_test_mode()) {
        global $wpdb;
        $wpdb->prefix = 'test_';
        //write_log ("prefix ".$wpdb->prefix);
    //}
}

add_action('wp_ajax_new_chat_ajax', 'new_chat_ajax');
function new_chat_ajax()
{
    global $wpdb;
    $user_id = get_current_user_id();
    //write_log ('new chat user id '.$user_id);
    $query = "INSERT into ".$wpdb->prefix."chat(text,task_id,user_id,date) select '" . $_POST['text'] . "'," . $_POST['task_id'] . "," . get_current_user_id () . ",NOW()";
    run_query ($query,"execute");
    //run_query ("UPDATE tasks set treatment_date = NOW()");

    $media_id =9 ;
    //write_log ("media id new chat " .json_encode ( $media_id));
    $query = "SELECT * FROM ".$wpdb->prefix."chat where task_id = " . $_POST['task_id'] ." ORDER BY id DESC LIMIT 1";
    $rows = run_query ($query);

    $media_url =  wp_get_attachment_url($media_id );
    echo json_encode (array(
        "rows" => $rows,
        "add_message" => true
      /*  "id" => $chat_time[0]->id,
        "time" => date("H:i:s",strtotime( $chat_time[0]->date)),
        "client_logo" => $media_url*/
        )
    );
    die();
}

add_action('wp_ajax_get_chat_ajax', 'get_chat_ajax');
//add_action('wp_ajax_nopriv_new_chat_ajax', 'new_chat_ajax');
function get_chat_ajax()
{
    global $wpdb;
    $user_id = get_current_user_id();
    $media_id =9 ;
    $filters=array();
    $filters[]=array("filter_field" => "task_id", "filter_value"=>$_POST['task_id']);
    $filters[]=array("filter_field" => "date", "filter_value"=>"NOW() - interval 30 minute","filter_type"=>"date","filter_ratio"=>">");
    $rows = get_data_table("chat",$filters);
    //write_log("rows ".json_encode($rows));
    //$query = "SELECT date FROM ".$wpdb->prefix."chat where task_id = " . $_POST['task_id'] ." ORDER BY id DESC LIMIT 1";
    //$chat_time = run_query ($query);
    //$media_url =  wp_get_attachment_url($media_id );
    echo json_encode (array(
        "rows" => $rows,
        "get_messages" => true
        )
    );
    die();
}

?>