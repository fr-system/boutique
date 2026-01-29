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
function build_query_boutique($from_ajax = true)
{
    $table_name = $_POST["table_name"];

    if($table_name == "agents" && empty($_POST["id"])){
       $result = register_new_user1( $_POST["display_name"], $_POST["user_email"]);
       if($result == null || $result["status"] == "failed") {
           echo json_encode($result);
           die();
       }
       unset($_POST["display_name"]);
        unset($_POST["user_email"]);
        $_POST["user_id"] = $result["user_id"];
    }


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
                    //write_log("val " . PHP_FLOAT_MAX);
                    //write_log("val2 " . str_replace("₪", "", $value));

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
    if($from_ajax) {
        echo json_encode(array(
            'status' => 'success',
            'redirect' => $_POST["previous_page"],
        ));
        die();
    }

}

function get_field($table_name, $field_name)
{
    $fields = BOUTIQUE_TABLES[$table_name]["columns"];
    $field = array_filter($fields, function ($field_row) use ($field_name) {
        return isset($field_row["field_name"]) && $field_row["field_name"] == $field_name;
    });
    return count($field) > 0 ? array_pop($field) : null;
}

function get_page_query($table_name,$filter_field=null ,$filter_value=null)
{
    global $wpdb;
    $columns = BOUTIQUE_TABLES[$table_name]["columns"];
    $join = "";

    $query = "SELECT ".$wpdb->prefix.$table_name.".id, ";
    foreach ($columns as $column) {
        if(!isset($column["field_name"]) || isset($column["type"]) && $column["type"] == "user_data"  )continue;
       // if (/*$column["type"] == "action" ||*/isset($column["type"]) && $column["type"] == "user_data" && !isset($column['join_table'])) continue;
        $query .=$wpdb->prefix.$table_name.".". $column["field_name"] . ", ";
        if (isset($column['join_table'])) {
            if(isset($column['join_value'])) {
                $query .= $wpdb->prefix . $column['join_table'] . "." . $column['join_value'] . " AS " . substr($column['join_table'], 0, -1) . "_" . $column['join_value'] . ", ";
            }
            else{
                $query .= $wpdb->prefix . $column['join_table'] . ".*, " ;
            }
            $join .= " LEFT JOIN " . $wpdb->prefix.$column['join_table'] . " ON " .$wpdb->prefix. $table_name . "." . $column["field_name"] . " = " . $wpdb->prefix.$column['join_table'] . ".id";
        }
    }

    $query = substr($query,0,-2);
    $query .= " FROM ".$wpdb->prefix.$table_name. $join;
    if($filter_field!=null && $filter_value!=null) {
        if ($filter_field == "id") {
            $filter_field=$wpdb->prefix.$table_name.".".$filter_field;
            $apostrophe = "";
        } else {
            $field = get_field($table_name, $filter_field);
            if ($field != null) {
                $apostrophe = is_needed_apostrophe($field["widget"], isset($field["un_apostrophe"]));
            }
        }
        $query .= " WHERE " . $filter_field . " = " . $apostrophe . $filter_value . $apostrophe;
    }
//    if($filter_value!= 0){
//        $query .= " WHERE ".get_id_column_in_page($page_name)." = ".$filter_value;
//    }
    //write_log ('select '.$query);
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


function build_options($list_name,$value=null,$filter=null)
{
    write_log("list name " .$list_name);
    $fields_list = BOUTIQUE_LISTS[$list_name];
    if(isset($fields_list["data-field"])) {
        $field = $fields_list["data-field"];
    }
    $list = get_list($list_name,$filter);
    write_log("list  " .json_encode($list));
    $options = '<option value=""></option>';
    foreach ($list as $row) {
        $data_field = "";
        if(isset($fields_list["data-field"])){
            $data_field =' data-field="'.$row->$field.'"';
        }
        $options .= '<option '.$data_field.' value="' . $row->value . '"' . (!empty($value)&& (is_array($value) && in_array($row->value, $value) || !is_array($value) && $row->value == $value) ? 'selected' : '') . '>' . $row->text . '</option>';
    }
    return $options;
}

function get_list($list_name,$filter = ''){
    global $wpdb;

    if (array_key_exists($list_name, BOUTIQUE_LISTS)) {
        $fields_list = BOUTIQUE_LISTS[$list_name];
    }
    else if (array_key_exists($list_name, BOUTIQUE_TABLES)) {
        $fields_list = BOUTIQUE_TABLES[$list_name];
    }

    $field_name = $fields_list["columns"][0]["field_name"];

    $query = "SELECT id as value, ".$field_name." as text";
    if(isset($fields_list["data-field"])) {
        $query .= ", ".$fields_list["data-field"];
    }
    $table_name=$list_name;
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
    write_log("quert ".$query." table_name ".$table_name." field_name ".$field_name);
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

function get_post_by_name($file_name,$type)
{
    $args = array(
        'name' => $file_name, // הכנס את הכותרת כאן
        'post_type' => $type,
        //'post_status' => 'publish',
        'numberposts' => 1
    );

    $pages = get_posts($args);

    if (!empty($pages)) {
        $page = $pages[0]; // הדף הראשון שנמצא
        return $page->id;
    }
    return null;
}

function save_media($file,$file_name)
{
    $file_attr = wp_handle_upload ($file, array('test_form' => FALSE));
    if ($file_attr && !isset($file_attr['error'])) {

        setlocale (LC_ALL, 'he_IL.UTF-8');
        $attachment = array(
            'guid' => $file_attr['url'],
            'post_mime_type' => $file_attr['type'],
            'post_title' => basename ($file_attr['file']),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attachment_id = wp_insert_attachment ($attachment, $file_attr['file'], null,$file_name);
        $attachedData = wp_generate_attachment_metadata ($attachment_id, $file_attr['file']);

        wp_update_attachment_metadata ($attachment_id, $attachedData);

        return $attachment_id;
    }
}


if(isset($_POST['save_product']) && $_SERVER["REQUEST_METHOD"] == "POST") {
//write_log("save_product");
    test_mode_table_prefix();

    $fields_arr = BOUTIQUE_TABLES[$_POST['table_name']]["columns"];
    $array_uploads = array_filter($fields_arr, function ($field_row) {
        return ($field_row["widget"] == "image" || $field_row["widget"] == "file");
    });
    foreach ($array_uploads as $field) {
        $field_name = $field["field_name"];
        if (isset($_FILES[$field_name]['name']) && $_FILES[$field_name]["size"] > 0) {
            write_log("file!!!!! ");
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $file = array(
                'name' => $_FILES[$field_name]['name'],
                'type' => $_FILES[$field_name]['type'],
                'tmp_name' => $_FILES[$field_name]['tmp_name'],
                'error' => $_FILES[$field_name]['error'],
                'size' => $_FILES[$field_name]['size']
            );
            if ($file["error"] == 1) {
                $error_msg = '&error_msg=upload_err_ini_size&file_index=';
                write_log("upload_image err " . $error_msg);
            }
            //$exist_image = get_post_by_name($file["name"], 'attachment');
            //write_log ("exist_image ".json_encode ( $exist_image));
            $image_id = null;
            //if ($exist_image) {
                //$image_id = $exist_image->id;
                //$image_id = 38;
            //} else {
                if ($file["size"] > 0) {
                    write_log("upload_" . $field_name);
                    $image_id = save_media($file, $_POST['name']);
                }
            //}

            if ($image_id) {
                write_log("imag ".$image_id);
                $_POST[$field_name] = $image_id;
            }
        }
    }

    write_log("post ".json_encode($_POST));

    build_query_boutique(false);
    wp_redirect($_SERVER['REQUEST_URI']);
    exit();



}
?>