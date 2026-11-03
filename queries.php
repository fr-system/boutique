<?php

function run_query($query, $type="")
{
    //write_log("qu ".$query);
    global $wpdb, $boutique_query_errors;
   // write_log ('run query '. $query );
    if ($type == "execute") {
        $result = $wpdb->query($query);
    } else {
        $result = $wpdb->get_results($query);
    }
    //שגיאות נאספות כדי ש-save_single_data תדע שהשמירה נכשלה
    if ($wpdb->last_error) {
        $boutique_query_errors[] = $wpdb->last_error;
    }

    //write_log("result ". json_encode($result));
    return $result;
}

function pre_action_query($table_name, $row)
{
    $update = array();
    $fields = array();
    $values = array();

    foreach ($row as $key => $value) {
        if($key == "id") continue;
        $field = get_field($table_name, $key);
        if ($field != null) {
            if (!empty($value) && (isset($field["un_apostrophe"]) || $field["widget"] == "file" || $field["widget"] == "image")) {
                $value = str_replace("₪", "", $value);
                $value = str_replace("%", "", $value);
                $value = str_replace(",", "", $value);
                $value = (float)$value;
            }

            $apostrophe = is_needed_apostrophe($field["widget"], isset($field["un_apostrophe"]),isset($field["save_as_text"]));
            array_push($fields, $key);
            array_push($values, ($value === null || $value === '' ? "NULL" : $apostrophe . $value . $apostrophe));
            array_push($update, $key . " = " . ($value === null || $value === '' ? "NULL" : $apostrophe . $value . $apostrophe));
        }
    }

    return array("fields" => $fields, "values" => $values, "update" => $update);
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
            $table_options = array();
            if (array_key_exists($table_name, BOUTIQUE_TABLES)) {
                $table_options = BOUTIQUE_TABLES[$table_name];
            }
            else if (array_key_exists($table_name, BOUTIQUE_LISTS)) {
                $table_options = BOUTIQUE_LISTS[$table_name];
            }

            if(isset($table_options["linked_tables"])){
                $linked_tables = $table_options["linked_tables"];
                $field = $linked_tables["field"];
                foreach($linked_tables["tables"] as $linked_table){
                    $linked_table_name = $wpdb->prefix . $linked_table["table_name"];
                    switch ($linked_table["action"]){
                        case "update":
                            run_query("UPDATE {$linked_table_name} SET {$field} = NULL WHERE {$field} = ".intval($id), "execute");
                            break;
                        case "remove":
                            run_query("DELETE FROM {$linked_table_name} WHERE {$field} = ".intval($id), "execute");
                            break;
                    }
                }
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
        if(str_contains($id, ',')){
            $query .= " where id in(" . $id.")";
        }
        else {
            $query .= " where id = " . $id;
        }
    }
    //write_log("query ".$query);
    $ok = run_query($query, "execute");
    return $ok;
}

add_action('wp_ajax_save_single_data', 'save_single_data');
function save_single_data()
{
    $table_name = $_POST["table_name"];
    $is_remove = isset($_POST["remove"]) && $_POST["remove"];
    if (($table_name == "agents" || $table_name == "suppliers") && !$is_remove)  {
        //שם משתמש וסיסמה - לסוכנים בלבד. נשמרים רק ביוזר, לא בטבלה
        $raw_username = $table_name == "agents" && isset($_POST["username"]) ? trim(wp_unslash($_POST["username"])) : "";
        $username = sanitize_user($raw_username, true);
        $password = $table_name == "agents" && isset($_POST["password"]) ? wp_unslash($_POST["password"]) : "";
        unset($_POST["username"], $_POST["password"]);

        if ($username !== $raw_username) {
            echo json_encode(array("status" => "failed", "msg" => "שם המשתמש יכול להכיל רק אותיות באנגלית, ספרות והתווים . _ - @"));
            die();
        }
        if (!empty($password) && mb_strlen($password) < 8) {
            echo json_encode(array("status" => "failed", "msg" => "הסיסמה חייבת להכיל לפחות 8 תווים"));
            die();
        }
        //user_id לא נשלח בטופס - בעריכה מביאים אותו מהטבלה
        if (!empty($_POST["id"])) {
            $existing = get_data_table($table_name, array(array("filter_field" => "id", "filter_value" => intval($_POST["id"]))));
            $_POST["user_id"] = count($existing) > 0 ? $existing[0]->user_id : "";
        }

        //אין להוסיף או לעדכן סוכן/ספק עם מייל של מנהל
        if (!empty($_POST["email"]) && is_admin_email(wp_unslash($_POST["email"]))) {
            echo json_encode(array("status" => "failed", "msg" => "המייל שייך למנהל המערכת, יש להזין מייל אחר"));
            die();
        }

        if(empty($_POST["id"])) {
            $result = register_new_user1($_POST["name"], $_POST["email"], $table_name, $username, $password);
            if ($result == null || $result["status"] == "failed") {
                echo json_encode($result);
                die();
            }
            $_POST["user_id"] = $result["user_id"];
            $user_notice = $result["notice"] ?? "";
            $created_user_id = !empty($result["created"]) ? $result["user_id"] : 0;
        }
        else if(!empty($_POST["user_id"]) && ($bad_link = get_bad_user_link(intval($_POST["user_id"]), $table_name, intval($_POST["id"])))){
            //הרשומה מקושרת ליוזר שלא קיים, ליוזר של מנהל או ליוזר בתפקיד אחר (למשל סוכן שמקושר ליוזר של ספק).
            //לא נוגעים ביוזר הזה - הרשומה מקבלת יוזר משלה (לפי שם המשתמש, ובלי שם משתמש - לפי המייל) ומנותקת ממנו
            $result = register_new_user1($_POST["name"], $_POST["email"], $table_name, $username, $password);
            if ($result == null || $result["status"] == "failed") {
                $result["msg"] = "ה" . BOUTIQUE_TABLES[$table_name]["single"] . " " . str_replace("היה מקושר", "מקושר", $bad_link) . ", ולכן צריך ליצור לו משתמש חדש: " . ($result["msg"] ?? "");
                echo json_encode($result);
                die();
            }
            $_POST["user_id"] = $result["user_id"];
            $user_notice = "ה" . BOUTIQUE_TABLES[$table_name]["single"] . " " . $bad_link . " ונותק ממנו. " . ($result["notice"] ?? "");
            $created_user_id = !empty($result["created"]) ? $result["user_id"] : 0;
        }
        else if(!empty($_POST["user_id"])){
            global $wpdb;
            $user_id = intval($_POST["user_id"]);
            $user = get_userdata($user_id);
            $change_login = false;
            $user_email = $_POST["email"];
            if ($table_name == "agents" && $user) {
                if (empty($_POST["email"])) {
                    echo json_encode(array("status" => "failed", "msg" => "יש להזין מייל"));
                    die();
                }
                $change_login = !empty($username) && $username != $user->user_login;
                $username_error = $change_login ? get_username_error($username, $user_id) : "";
                if ($username_error) {
                    echo json_encode(array("status" => "failed", "msg" => $username_error));
                    die();
                }
                //מייל של יוזר אחר - נשמר רק בטבלת הסוכנים, היוזר נשאר בלי מייל
                $email_owner = email_exists($user_email);
                if ($email_owner && $email_owner != $user_id) {
                    $user_email = "";
                }
            }
            $result = wp_update_user([
                'ID'         => $user_id,
                'display_name' => $_POST["name"],
                'last_name'  => $_POST["name"],
                'user_email' => $user_email,
            ]);
            if (is_wp_error($result)) {
                echo json_encode(array("status" => "failed", "msg" => $result->get_error_message()));
                die();
            }
            //wp_update_user לא משנה user_login - מעדכנים ישירות
            if ($change_login) {
                $wpdb->update($wpdb->users, array("user_login" => $username), array("ID" => $user_id));
                clean_user_cache($user_id);
            }
            if (!empty($password)) {
                wp_set_password($password, $user_id);
            }
        }
    }

    if($table_name == "specials"){
        write_log("post ".json_encode($_POST));
        $_POST["products_buy"] = json_encode($_POST["products_buy"]);
    }

    global $wpdb;
    if (isset($_POST["remove"]) && $_POST["remove"]) {
        $action = "remove";
        $result = array();
    } else {
        $action = isset($_POST["id"]) && !empty($_POST["id"]) ? "update" : "new";
        //write_log ('tablename '.$table_name. ' post '.json_encode ( $_POST));
        $result = pre_action_query ($table_name, $_POST);
    }

    $id = isset($_POST["id"]) ? $_POST["id"] : null;

    //מחיקת סוכן/ספק - היוזרים המקושרים נמחקים אחרי מחיקת הרשומה (delete_users_of_removed_records)
    $removed_user_ids = array();
    if ($action == "remove" && in_array($table_name, USER_EMAIL_TABLES) && !empty($id)) {
        $ids = array_map('intval', explode(',', $id));
        $removed_user_ids = $wpdb->get_col("SELECT DISTINCT user_id FROM {$wpdb->prefix}{$table_name} WHERE user_id > 0 AND id IN (" . implode(',', $ids) . ")");
    }

    //כל השאילתות של השמירה (הרשומה, הטבלאות המקושרות ושורות המשנה) בטרנזקציה אחת - אם אחת נכשלת, שום דבר לא נשמר
    global $boutique_query_errors;
    $boutique_query_errors = array();
    $wpdb->query("START TRANSACTION");

   // write_log("run_action_query ".json_encode($result));
    run_action_query ($table_name, $id, $action, $result);
    if (!$_POST["id"]) {
            $id = $wpdb->insert_id;
        //$id = $wpdb->get_var ("SELECT MAX(id) FROM {$wpdb->prefix}" . $table_name);
    }

    //write_log("save_single_data " .json_encode($_POST));
    $temp_list = array();
    $removed_list = array();
    if (isset($_POST["rows"])) {
        //write_log("rows ".json_encode($_POST["rows"]));

        foreach ($_POST["rows"] as $row) {
            if ($table_name == "agents") {
                if (empty($row["target"])) {
                    if ($row["id"]) {
                        $row["remove"] = true;
                    } else {
                        continue;
                    }
                }
                $sub_table_name = "agent_target_supplier";
                //write_log("row agent_target_supplier" . json_encode($row));
            }

            if ($table_name == "orders") {
                if (empty($row["count"])) {
                    if (!empty($row["id"])) {
                        $row["remove"] = true;
                    } else {
                        continue;
                    }
                }

                if (empty($row["order_id"])) {
                    $row["order_id"] = $id;
                }
                $sub_table_name = "order_products";
                // write_log("row order product" . json_encode($row));
            }

            if($table_name == "clients"){
                //write_log ("row to save" . json_encode ($row));

                if (empty($row["name"])) {
                    if ($row["id"]) {
                        $row["remove"] = true;
                    } else {
                        continue;
                    }
                }
                if (empty($row["main_client_id"])) {
                    $row["main_client_id"] = $id;
                }
                $sub_table_name = "clients_branches";
            }

            $action_product = (isset($row["id"]) && !empty($row["id"])) ?
                (isset($row["remove"]) && $row["remove"] ? "remove" : "update") : "new";
           // write_log ("action_product " . $action_product);
            //write_log ("row to save" . json_encode ($row));
            if($action_product == "new" && $sub_table_name == "order_products") {
                $copy =  $row;
                unset($row["temp_id"]);
                //write_log ('copy '.json_encode ($copy).' row '.json_encode ($row));
            }
            if($action_product == "remove" && $sub_table_name == "order_products" && empty($row["bonus"])) {
                $removed_list[]=$row["id"];
            }
            $result = pre_action_query ($sub_table_name, $row);

            //write_log("result to save" . json_encode($result));
            run_action_query ($sub_table_name, $row["id"], $action_product, $result);
            if($action_product == "new" && $table_name = "orders") {
                $copy["id"] = $wpdb->insert_id;
                $temp_list[] = $copy;
            }
        }
    }

    if (!empty($boutique_query_errors)) {
        $wpdb->query("ROLLBACK");
        //יוזר שנוצר בשביל הרשומה הזו - מוחקים, כדי שלא יישאר יוזר בלי רשומה
        if (!empty($created_user_id)) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
            wp_delete_user($created_user_id);
        }
        write_log("save_single_data failed ({$table_name}, {$action}): " . implode(" | ", $boutique_query_errors));
        $failed_msg = $action == "remove" ? "המחיקה נכשלה, שום דבר לא נמחק." : "השמירה נכשלה, השינויים לא נשמרו.";
        echo json_encode(array("status" => "failed", "msg" => $failed_msg . " יש לנסות שוב, ואם הבעיה חוזרת לפנות למנהל המערכת"));
        wp_die();
    }
    $wpdb->query("COMMIT");

    //מחיקת היוזרים - רק אחרי שמחיקת הרשומה נשמרה
    $user_notice = $action == "remove" ? delete_users_of_removed_records($removed_user_ids) : ($user_notice ?? "");
    //הודעה אחרי הוספה (מה נוסף, ומה נעשה עם היוזר של סוכן/ספק) ואחרי מחיקה
    $message = "";
    $table_info = BOUTIQUE_TABLES[$table_name] ?? (BOUTIQUE_LISTS[$table_name] ?? array());
    if (isset($table_info["single"])) {
        $is_female = ($table_info["male_female"] ?? "male") == "female";
        if ($action == "new") {
            $message = ($is_female ? "נוספה " : "נוסף ") . $table_info["single"] . ($is_female ? " חדשה" : " חדש");
            if (!empty($user_notice)) {
                $message .= "<br>" . esc_html($user_notice);
            }
        }
        else if ($action == "update" && !empty($user_notice)) {
            $message = "השינויים נשמרו<br>" . esc_html($user_notice);
        }
        else if ($action == "remove") {
            $message = "ה" . $table_info["single"] . ($is_female ? " נמחקה" : " נמחק");
            if (!empty($user_notice)) {
                $message .= "<br>" . esc_html($user_notice);
            }
        }
    }

    echo json_encode (array(
        'status' => 'success',
        'id' => $id,
        'message' => $message,
        'redirect' => (isset($_POST["previous_page"]) ? $_POST["previous_page"] : ''),
        'temp_list'=>$temp_list,
        'removed_list'=>$removed_list,
    ));
    wp_die ();

}
function get_data_table($table_name, $filters=null, $orderby = null, $join_filter=null)
{
    global $wpdb;
    $apostrophe = "";
    if (array_key_exists($table_name, BOUTIQUE_TABLES)) {
        $columns = BOUTIQUE_TABLES[$table_name]["columns"];
    }
    else if (array_key_exists($table_name, BOUTIQUE_LISTS)) {
        $columns = BOUTIQUE_LISTS[$table_name]["columns"];
    }
    //$columns = BOUTIQUE_TABLES[$table_name]["columns"];
    $join = "";

    $query = "SELECT ".$wpdb->prefix.$table_name.".id, ";
    $i = 0;
    foreach ($columns as $column) {
        if(!isset($column["field_name"]) || isset($column["widget"]) && $column["widget"] == "table" ||
            isset($column["not_in_query"]))continue;

        if(isset($column['join_table_from'])) {
            //קישור של טבלה אחרת עם טבלה אחרת
            // למשל חשבוניות יש צורך לקשר לקוחות עם סוכנים
            /*            "join_table_from" => "clients", "join_table" => "agent"
                        "join_table"."join_value"
                        "join_table_from"."field_name" =  "join_table"."id"*/
            $query .= $wpdb->prefix.$column['join_table'].".id as ".$column['join_table']."_id".", ". $wpdb->prefix.$column['join_table'].".".$column["join_value"]. " AS " . substr($column['join_table'], 0, -1) . "_" . $column['join_value']. ", ";
            $join .= " LEFT JOIN ".$wpdb->prefix.$column['join_table']." ON " . $wpdb->prefix . $column["join_table_from"] . "." . $column["field_name"] . " = " . $wpdb->prefix . $column['join_table'] . ".id";
        }
        else {
            $query .= $wpdb->prefix . $table_name . "." . $column["field_name"] . ", ";

            if (isset($column['join_table'])) {
                if (isset($column['join_value'])) {
                    $query .= $wpdb->prefix . $column['join_table'] . "." . $column['join_value'] . " AS " . substr($column['join_table'], 0, -1) . "_" . $column['join_value'] . ", ";
                } else if (isset($column['join_values_select'])) {
                    foreach ($column['join_values_select'] as $join_values_select) {
                        $query .= $wpdb->prefix . $column['join_table'] . "." . $join_values_select. " AS " . substr($column['join_table'], 0, -1) . "_" . $join_values_select . ", ";
                    }
                } else {
                    $query .= $wpdb->prefix . $column['join_table'] . ".*, ";
                }

                if (isset($column['join_field'])) {
                    $join .= " LEFT JOIN " . $wpdb->prefix . $column['join_table'] . " ON " . $wpdb->prefix . $table_name . "." . $column["field_name"] . " = " . $wpdb->prefix . $column['join_table'] . "." . $column['join_field'];
                } else {
                    $join .= " LEFT JOIN " . $wpdb->prefix . $column['join_table'] . " ON " . $wpdb->prefix . $table_name . "." . $column["field_name"] . " = " . $wpdb->prefix . $column['join_table'] . ".id";
                }
            }
        }
    }

    if($table_name=="order_products") {
        $query .= " {$wpdb->prefix}products.name, ";
        $join .= " LEFT JOIN {$wpdb->prefix}products  
                    ON {$wpdb->prefix}order_products.product_id = {$wpdb->prefix}products.id " .
            (empty($join_filter) ? '' : "AND " . $join_filter);
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
                    $apostrophe = is_needed_apostrophe($field["widget"], isset($field["un_apostrophe"]), isset($field["save_as_text"]));
                }
            }

            switch ($filter["filter_type"]) {
                case "dateime":
                    $filter_str[] = "".$filter_field . " " . $filter["filter_ratio"] . " " . $filter["filter_value"];
                    break;
                case "date":
                    $filter_str[] ="DATE(" .$filter_field . ") " . $filter["filter_ratio"] . " " . $filter["filter_value"];
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
                case "between":
                    $filter_str[] = $filter_field . " between " .$apostrophe . $filter["filter_value"][0] .$apostrophe ." AND ".$apostrophe .$filter["filter_value"][1].$apostrophe;
                    break;
                case "filter":
                    $filter_str[] = $filter["filter_value"];
                    break;
                default:
                    $filter_str[] = $filter_field . " = " . $apostrophe . $filter["filter_value"] . $apostrophe;
                    break;
            }
        }
        $query .= " WHERE " . implode(" AND ", $filter_str);

    }

    if($orderby != null){
        foreach ($orderby as $key=>$order) {
            $query .= " ORDER BY " . $key ." ".($order ?? '');
        }
    }

    //echo $query;
   // write_log("query ".$query);
    $result = run_query ($query);
   //write_log("res ".json_encode($result));
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

    //הסינון הקבוע של הטבלה (למשל לקוחות לא חסומים) וגם הסינון שהתקבל (למשל הלקוחות של הסוכן) - שניהם יחד
    $where = array();
    if(!empty($page_info["filter"])){
        $where[] = "(" . $page_info["filter"] . ")";
    }
    if(!empty($filter)){
        $where[] = "(" . $filter . ")";
    }
    if($where){
        $query .= " WHERE " . implode(" AND ", $where);
    }
    $query .= " ORDER BY ".$field_name;
    $list = run_query($query);
    return $list;
}

add_action('wp_ajax_new_chat_ajax', 'new_chat_ajax');
function new_chat_ajax()
{
    global $wpdb;
    //write_log ('new chat user id '.get_current_user_id ());
    $query = "INSERT into ".$wpdb->prefix."chat(text,task_id,user_id,date) select '" . $_POST['text'] . "'," . $_POST['task_id'] . "," . get_current_user_id () . ",NOW()";
    run_query ($query,"execute");
    $query = "SELECT * FROM ".$wpdb->prefix."chat where task_id = " . $_POST['task_id'] ." ORDER BY id DESC LIMIT 1";
    $rows = run_query ($query);
    foreach ($rows as $row){
        $row->logo = get_logo_chat($row->user_id);
    }
    wp_send_json([
        "rows" => $rows,
        "add_message" => true
    ]);
}
//'.wp_get_attachment_url(9).'
function get_logo_chat($user_id){
    if(is_manager(get_user_by('ID', $user_id))) {
        return '<img class="user-logo" src="https://kosherboutique.co.il/wp-content/themes/boutique/assets/images/logo_icon.png"/>';
    }
    else{
        return '<span class="user-logo"></span>';
    }
}

add_action('wp_ajax_get_chat_ajax', 'get_chat_ajax');
function get_chat_ajax()
{
    //write_log("task ".$_POST['task_id']);
    date_default_timezone_set('Asia/Jerusalem');
    $filters=array();
    $filters[]=array("filter_field" => "task_id", "filter_value"=>$_POST['task_id']);
    $filters[]=array("filter_field" => "date", "filter_value"=>"NOW() - interval 30 minute","filter_type"=>"dateime","filter_ratio"=>">");
    $rows = get_data_table("chat",$filters);
    //write_log("chat rows ".json_encode($rows));
    foreach ($rows as $row){
        $row->logo = get_logo_chat($row->user_id);
    }
    wp_send_json([
        "rows" => $rows,
        "add_message" => true
    ]);
}

?>