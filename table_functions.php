<?php

function get_archive_table($table_name,$data,$attr)
{
    $page_info = BOUTIQUE_TABLES[$table_name];
    //'.$table_name.'
    $html = '<table name="" class="archive-table dataTable '.$table_name.'">
                <thead><tr class="tr-head gold">';
    if (isset($page_info["more_columns_in_table"])) {
        foreach ($page_info["more_columns_in_table"] as $column) {
            $html .= '<th class="' . (isset($column["label"]) ? '' : 'no-sort') . '">' . (isset($column["label"]) ? $column["label"] : '') . '</th>';
        }
    }

    if(is_manager() && $table_name == "collection" && !isset($_GET["payed"])){
        $html .= '<th class="no-sort"></th>';//בשביל עדכון תשלום
    }

    if(!isset($page_info["update_remove"]) || $page_info["update_remove"] == true) {
        if (is_manager() || is_agent() && $table_name == "orders") {//update/readonly
            $html .= '<th class="no-sort" style="width:10px"></th>';
        }
        if (is_manager()) {//remove
            $html .= '<th class="no-sort" style="width:20px"></th>';
        }
    }

    foreach ($page_info["columns"] as $column) {
        if (isset($column["create_input"])) {
            //$html .= '<th class="no-sort"></th>';
        } else {
            if (isset($column["hide_in_table"]) || !isset($column["label"]) ||
                isset($attr["add_text"]) && !empty($attr["add_text"]) && $column["field_name"] == "client_id" ||
                is_agent () && $column["field_name"] == "agent_id") {
                continue;
            }
        }
        $width = null;//לא עובד רציתי להקטין עמודות של תמונה או של אייקון עדכון
        if (isset($column["width"])) {
            $width = $column["width"];
        } else if ($column["widget"] == "image") {
            $width = '20px';
        }
        $class_ = "";
        if ($column["widget"] == "hidden") {
            $class_ = "no-sort";
        }

        $html .= '<th  class="' . $class_ . '" ' . (empty($width) ? '' : 'style="width:' . $width . '"') . '>' . (isset($column["label"]) ? $column["label"] : '') . '</th>';
    }

    if (isset($page_info["actions"])) {
        foreach ($page_info["actions"] as $action) {
            $html .= '<th class="no-sort"></th>';
        }
    }
    $html .= '</tr></thead>';

    foreach ($data as $key => $row) {
        $html .= get_tr_data ($table_name, $row, $key, $attr);
    }
    $html .= '</table>';
    return $html;
}

function get_tr_data($table_name, $data, $key,$attr){
    $page_info = BOUTIQUE_TABLES[$table_name];

    $row = is_array ($data)? $data[0]:$data;
    //echo json_encode ($row);
    $tr_class ="";
    switch ($table_name) {
        case "orders":
            if ($row->done) $tr_class = " order-confirm";
            break;
        case "clients":
        case "products":
            if ($row->blocked) $tr_class = " blocked";
            break;
        case "order_products":
            $tr_class = " product ";
            if(isset($attr["readonly"]))$tr_class .= $attr["readonly"];
            if(!empty($row->count))$tr_class .= " in-cart ";
        case "agent_target_supplier":
            $tr_class .= " sub-table";
            break;
    }

    $html="<tr data-id='{$row->id}' class='{$tr_class}'>";
    if(is_manager() && $table_name == "collection" && !isset($_GET["payed"])){
        $html.= '<td>';
        if($row->doc_type == 1) {
            $html .= '<input class="pointer" type="checkbox"/>';
        }
        $html.= '</td>';
    }
    if(!isset($page_info["update_remove"]) || $page_info["update_remove"] == true) {
        if (is_manager() || is_agent() && $table_name == "orders") {
            if ($table_name != "orders" || $row->done == 0) {
                $html .= '<td><a   class="has-tooltip" data-tooltip="עדכון ' . $page_info['single'] . '"  href="single?subject=' . $table_name . '&action=edit&id=' . $row->id . '">
                        <svg class="edit-row" xmlns="http://www.w3.org/2000/svg" width="24" height="23" viewBox="0 0 24 23" fill="none">
                            <path d="M7 16.3041L11.413 16.2898L21.045 7.14726C21.423 6.78501 21.631 6.30393 21.631 5.79218C21.631 5.28043 21.423 4.79934 21.045 4.43709L19.459 2.91717C18.703 2.19267 17.384 2.19651 16.634 2.9143L7 12.0587V16.3041ZM18.045 4.27226L19.634 5.7893L18.037 7.30538L16.451 5.78643L18.045 4.27226ZM9 12.858L15.03 7.13384L16.616 8.65376L10.587 14.376L9 14.3808V12.858Z" class="background-gold"/>
                            <path d="M5 20.125H19C20.103 20.125 21 19.2654 21 18.2083V9.9015L19 11.8182V18.2083H8.158C8.132 18.2083 8.105 18.2179 8.079 18.2179C8.046 18.2179 8.013 18.2093 7.979 18.2083H5V4.79167H11.847L13.847 2.875H5C3.897 2.875 3 3.73462 3 4.79167V18.2083C3 19.2654 3.897 20.125 5 20.125Z" class="background-gold"/>
                        </svg></a>
                  </td>';
            }
            if ($table_name == "orders" && $row->done == 1) {
                $html .= '<td><a class="has-tooltip" data-tooltip="מעבר להזמנה" href="single?subject=' . $table_name . '&action=readonly&id=' . $row->id . '">
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
                        <path d="M12.5 5C5.93017 5 2.74267 10.683 2.17704 11.808C2.14681 11.8678 2.1311 11.9335 2.1311 12C2.1311 12.0665 2.14681 12.1322 2.17704 12.192C2.74163 13.317 5.92913 19 12.5 19C19.0708 19 22.2573 13.317 22.8229 12.192C22.8531 12.1322 22.8688 12.0665 22.8688 12C22.8688 11.9335 22.8531 11.8678 22.8229 11.808C22.2583 10.683 19.0708 5 12.5 5Z" class="stroke-background-gold" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12.5 15C14.2259 15 15.625 13.6569 15.625 12C15.625 10.3431 14.2259 9 12.5 9C10.7741 9 9.375 10.3431 9.375 12C9.375 13.6569 10.7741 15 12.5 15Z" class="background-gold" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        </a>
                  </td>';
            }
        }

        if (is_manager() ) {
            $html .= '<td><a  data-bs-toggle="modal" href="#bout-massage" role="button" data-action="remove">
                <svg  class="has-tooltip" data-tooltip="מחיקת ' . $page_info['single'] . '"  xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
                    <path d="M4.16663 7H20.8333M10.4166 11V17M14.5833 11V17M5.20829 7L6.24996 19C6.24996 19.5304 6.46945 20.0391 6.86015 20.4142C7.25085 20.7893 7.78076 21 8.33329 21H16.6666C17.2192 21 17.7491 20.7893 18.1398 20.4142C18.5305 20.0391 18.75 19.5304 18.75 19L19.7916 7M9.37496 7V4C9.37496 3.73478 9.48471 3.48043 9.68006 3.29289C9.87541 3.10536 10.1404 3 10.4166 3H14.5833C14.8596 3 15.1245 3.10536 15.3199 3.29289C15.5152 3.48043 15.625 3.73478 15.625 4V7" class="stroke-background-gold" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    </a>   
            </td>';
        }
    }
    $columns_counter = 0;
    if (isset($page_info["more_columns_in_table"])) {
        foreach ($page_info["more_columns_in_table"] as $column) {
            $column_value = get_column_value($column, $row, $column["field_name"], null,$key);
            $html .= '<td class="' . $column["field_name"] .'">' . $column_value . '</td>';
            $columns_counter++;
        }
    }

    foreach($page_info["columns"] as $column) {
        //איזה שדות שמראים בכותרת(גם אם אין כיתוב של כותרת) אותו דבר להראות בשורה בטבלה והפוך שדות שמסתירים בכותרת להסתיר גם בשורה בטבלה
        if (isset($column["create_input"])) {
        } else {
            if (isset($column["hide_in_table"]) || !isset($column["label"]) ||
                isset($attr["add_text"]) && !empty($attr["add_text"]) && $column["field_name"] == "client_id" ||
                is_agent() && $column["field_name"] == "agent_id") {
                continue;
            }
        }

        $field = isset($column['join_table']) && !isset($column['type']) ?
            substr($column['join_table'], 0, -1) . (isset($column['join_value']) ? "_" . $column['join_value'] : '') :
            $column["field_name"];

        $list = isset($column['table_name']) ? constant($column['table_name']) : null;
        $data_id = "";
        if ($column["widget"] == "select" && isset($column["options"])) {
            $data_id = 'data-id="' . $row->$field . '"';
        }
        //write_log("row->field" .$field." ". $row->$field);
        $column_value = get_column_value($column, $row, $field, $list, $key,isset($attr["readonly"])&& !empty($attr["readonly"]));
        //write_log("value ".$column_value);
        $hidden = "";//(isset($column["widget"]) && $column["widget"]== "hidden"?' hidden':'');
        $html .= '<td ' . $data_id . ' class="' . $field .$hidden .'">' . $column_value . '</td>';
    }

    if(isset($page_info["actions"])) {
        foreach ($page_info["actions"] as $action) {
            if(is_array($action) && isset($action["dialog"])) {
                $obligation = 0;
                if($table_name == "clients" && is_manager()) {
                    $res = get_obligation_client($row->id);
                    $obligation = $res["debts"];
                }
                $html .= '<td>'.($table_name != "clients" || $obligation > 0  ?
                        '<a data-text="'.$action["text"].'" data-ajax_func="'.$action["ajax_func"].'" class="button background-gold font-17" data-bs-toggle="modal" href="#'.$action["dialog"].'" role="button">'.$action["title"].'</a>':'').
                    '</td>';
            }
            else {
                $html .= '<td ><a class="button background-gold font-17" href="/archive?subject=' . $action . '&id=' . $row->id . '">' . BOUTIQUE_TABLES[$action]["title"] . '</a></td>';
            }
        }
    }
    $html .='</tr>';
    return $html;
}

?>