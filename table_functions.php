<?php

function get_archive_table($table_name,$data,$attr)
{
    $is_list = false;
    if (array_key_exists($table_name, BOUTIQUE_TABLES)) {
        $page_info = BOUTIQUE_TABLES[$table_name];
    }
    else if (array_key_exists($table_name, BOUTIQUE_LISTS)) {
        $page_info = BOUTIQUE_LISTS[$table_name];
        $is_list = true;
    }

    //$page_info = BOUTIQUE_TABLES[$table_name];

    $class_table = "";
    if(isset($attr["class_table"])){
        $class_table = $attr["class_table"];
    }
    //'.$table_name.'
    $html = "<table name='' class='archive-table {$class_table} dataTable {$table_name}'>
                <thead><tr class='tr-head gold'>";


   /* data-column-name ="'.$column["field_name"].'" data-column-type ="'.$column["widget"].'"
                        data-table ="'.$column["join_table"].'" data-column-options="'.$dataOptions.'">'
        .$column['label'].'</th>';*/

    if($table_name == "order_products"){
        $html .= '<th class="no-sort dupl-action" style="width:10px"></th>';//לחצן בונוס בעגלה
    }
    if (isset($page_info["more_columns_in_table"])) {
        foreach ($page_info["more_columns_in_table"] as $column) {
            $dataOptions="";
            if(isset($column["options"])) {
                $dataOptions = htmlspecialchars(json_encode($column["options"]), ENT_QUOTES, 'UTF-8');
            }
            $html .= '<th class="' . (isset($column["label"]) ? '' : 'no-sort') . '" data-column-name ="'.$column["field_name"].'"  data-column-type="'.$column["widget"].'" 
                        data-table="'.($column["join_table"]??"").'" data-column-options="'.$dataOptions.'">' . ($column["label"] ?? '') . '</th>';
        }
    }

    if(is_manager() && $table_name == "collection" && !isset($_GET["payed"])){
        $html .= '<th class="no-sort"></th>';//בשביל עדכון תשלום
    }


    if(!isset($page_info["update_remove"]) || $page_info["update_remove"] == true) {
        if (is_manager() || is_agent() && $table_name == "orders") {//update/readonly
            $html .= '<th class="no-sort" style="width:10px"></th>';//update
        }
        if($table_name == "orders"){
            $html .= '<th class="no-sort" style="width:10px"></th>';//print
        }
        if (is_manager()) {//remove
            $html .= '<th class="no-sort" style="width:20px"></th>';//remove
        }
    }

    foreach ($page_info["columns"] as $column) {
        if (isset($column["create_input"])) {
            //$html .= '<th class="no-sort"></th>';
        } else {
            if (isset($column["hide_in_table"]) && !$is_list || !isset($column["label"]) ||
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
        $class_td = "";
        if ($column["widget"] == "hidden") {
            $class_td .= " no-sort ";
        }
        if(isset($column["hide_in_table"])) {
            $class_td .= " hidden-col ";
        }

        $dataOptions="";
        if(isset($column["options"])) {
            $dataOptions = htmlspecialchars(json_encode($column["options"]), ENT_QUOTES, 'UTF-8');
        }

        $html .= '<th  class="' . $class_td . '" ' . (empty($width) ? '' : 'style="width:' . $width . '"') . ' data-column-name ="'.$column["field_name"].'"  data-column-type ="'.$column["widget"].'" 
                        data-table ="'.($column["join_table"]??"").'" data-column-options="'.$dataOptions.'">' . ($column["label"] ?? '') . '</th>';
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

    $is_list = false;
    if (array_key_exists($table_name, BOUTIQUE_TABLES)) {
        $page_info = BOUTIQUE_TABLES[$table_name];
    }
    else if (array_key_exists($table_name, BOUTIQUE_LISTS)) {
        $page_info = BOUTIQUE_LISTS[$table_name];
        $is_list = true;
    }

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
            if(!empty($row->discount_percent) && $row->discount_percent ==100){
                $tr_class .= " bonus ";
                $bonus_row=true;
            }
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
    if($table_name == "order_products") { // שכפול מוצר - בונוס
        $html .= '<td class="td-action dupl-action">';
        write_log ('bonus '.isset($bonus_row).' empty '.empty($bonus_row));
        if (!isset($bonus_row) || empty($bonus_row)) {
            $html .= '<a class="has-tooltip" data-tooltip="הוספת מוצר בונוס" onclick="addProdoctBonus(jQuery(this).closest(\'tr\'))">
                      <svg width="24" height="23" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.375 3.7085C2.375 3.35483 2.51549 3.01565 2.76557 2.76557C3.01565 2.51549 3.35483 2.375 3.7085 2.375H8.0415C8.21662 2.375 8.39002 2.40949 8.55181 2.47651C8.7136 2.54352 8.8606 2.64175 8.98443 2.76557C9.10825 2.8894 9.20648 3.0364 9.27349 3.19819C9.34051 3.35998 9.375 3.53338 9.375 3.7085V8.0415C9.375 8.21662 9.34051 8.39002 9.27349 8.55181C9.20648 8.7136 9.10825 8.8606 8.98443 8.98443C8.8606 9.10825 8.7136 9.20648 8.55181 9.27349C8.39002 9.34051 8.21662 9.375 8.0415 9.375H3.7085C3.53338 9.375 3.35998 9.34051 3.19819 9.27349C3.0364 9.20648 2.8894 9.10825 2.76557 8.98443C2.64175 8.8606 2.54352 8.7136 2.47651 8.55181C2.40949 8.39002 2.375 8.21662 2.375 8.0415V3.7085Z" class="stroke-background-gold" stroke-width="0.75" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M0.881 7.2435C0.7275 7.15629 0.599825 7.02999 0.51095 6.87745C0.422076 6.7249 0.37517 6.55155 0.375 6.375V1.375C0.375 0.825 0.825 0.375 1.375 0.375H6.375C6.75 0.375 6.954 0.5675 7.125 0.875M4.375 5.875H7.375M5.875 4.375V7.375" class="stroke-background-gold" stroke-width="0.75" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg></a>';
        }
        $html .= '</td>';
    }
    if(!isset($page_info["update_remove"]) || $page_info["update_remove"] == true) {
        if($is_list){
            $html .= '<td class="td-action"><a class="has-tooltip" data-tooltip="עדכון ' . $page_info['single'] . '" data-bs-toggle="modal" href="#edit-list" role="button" data-action="edit"> 
                        <svg class="edit-row" xmlns="http://www.w3.org/2000/svg" width="24" height="23" viewBox="0 0 24 23" fill="none">
                            <path d="M7 16.3041L11.413 16.2898L21.045 7.14726C21.423 6.78501 21.631 6.30393 21.631 5.79218C21.631 5.28043 21.423 4.79934 21.045 4.43709L19.459 2.91717C18.703 2.19267 17.384 2.19651 16.634 2.9143L7 12.0587V16.3041ZM18.045 4.27226L19.634 5.7893L18.037 7.30538L16.451 5.78643L18.045 4.27226ZM9 12.858L15.03 7.13384L16.616 8.65376L10.587 14.376L9 14.3808V12.858Z" fill="#E2B252"/>
                            <path d="M5 20.125H19C20.103 20.125 21 19.2654 21 18.2083V9.9015L19 11.8182V18.2083H8.158C8.132 18.2083 8.105 18.2179 8.079 18.2179C8.046 18.2179 8.013 18.2093 7.979 18.2083H5V4.79167H11.847L13.847 2.875H5C3.897 2.875 3 3.73462 3 4.79167V18.2083C3 19.2654 3.897 20.125 5 20.125Z" fill="#E2B252"/>
                        </svg></a>
                  </td>';
        }
        else {
            if (is_manager() || is_agent() && $table_name == "orders") {
                if ($table_name != "orders" || $row->done == 0) {
                    $html .= '<td class="td-action"><a   class="has-tooltip" data-tooltip="עדכון ' . $page_info['single'] . '"  href="single?subject=' . $table_name . '&action=edit&id=' . $row->id . '">
                        <svg class="edit-row" xmlns="http://www.w3.org/2000/svg" width="24" height="23" viewBox="0 0 24 23" fill="none">
                            <path d="M7 16.3041L11.413 16.2898L21.045 7.14726C21.423 6.78501 21.631 6.30393 21.631 5.79218C21.631 5.28043 21.423 4.79934 21.045 4.43709L19.459 2.91717C18.703 2.19267 17.384 2.19651 16.634 2.9143L7 12.0587V16.3041ZM18.045 4.27226L19.634 5.7893L18.037 7.30538L16.451 5.78643L18.045 4.27226ZM9 12.858L15.03 7.13384L16.616 8.65376L10.587 14.376L9 14.3808V12.858Z" class="background-gold"/>
                            <path d="M5 20.125H19C20.103 20.125 21 19.2654 21 18.2083V9.9015L19 11.8182V18.2083H8.158C8.132 18.2083 8.105 18.2179 8.079 18.2179C8.046 18.2179 8.013 18.2093 7.979 18.2083H5V4.79167H11.847L13.847 2.875H5C3.897 2.875 3 3.73462 3 4.79167V18.2083C3 19.2654 3.897 20.125 5 20.125Z" class="background-gold"/>
                        </svg></a>
                  </td>';
                }

                if ($table_name == "orders" && $row->done == 1) {
                    $html .= '<td class="td-action"><a class="has-tooltip" data-tooltip="מעבר להזמנה" href="single?subject=' . $table_name . '&action=readonly&id=' . $row->id . '">
                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
                        <path d="M12.5 5C5.93017 5 2.74267 10.683 2.17704 11.808C2.14681 11.8678 2.1311 11.9335 2.1311 12C2.1311 12.0665 2.14681 12.1322 2.17704 12.192C2.74163 13.317 5.92913 19 12.5 19C19.0708 19 22.2573 13.317 22.8229 12.192C22.8531 12.1322 22.8688 12.0665 22.8688 12C22.8688 11.9335 22.8531 11.8678 22.8229 11.808C22.2583 10.683 19.0708 5 12.5 5Z" class="stroke-background-gold" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12.5 15C14.2259 15 15.625 13.6569 15.625 12C15.625 10.3431 14.2259 9 12.5 9C10.7741 9 9.375 10.3431 9.375 12C9.375 13.6569 10.7741 15 12.5 15Z" class="background-gold" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        </a>
                  </td>';
                }
            }

            if ($table_name == "orders") {
                $html .= '<td class="td-action"><a class="has-tooltip" data-tooltip="הדפסת ' . $page_info['single'] . '" href="' . get_bloginfo('stylesheet_directory') . '/lib/export_pdf.php?file=pdf&export=single&subject=' . $table_name . '&order_id=' . $row->id . '&client_id=' . $row->client_id . '" target="_blank" >
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="21" viewBox="0 0 22 21" fill="none">
                            <path d="M3.4375 10.9375C3.61984 10.9375 3.7947 10.8684 3.92364 10.7453C4.05257 10.6222 4.125 10.4553 4.125 10.2812C4.125 10.1072 4.05257 9.94028 3.92364 9.81721C3.7947 9.69414 3.61984 9.625 3.4375 9.625C3.25516 9.625 3.0803 9.69414 2.95136 9.81721C2.82243 9.94028 2.75 10.1072 2.75 10.2812C2.75 10.4553 2.82243 10.6222 2.95136 10.7453C3.0803 10.8684 3.25516 10.9375 3.4375 10.9375Z" fill="#1A7870"/>
                            <path d="M6.875 1.75C6.14565 1.75 5.44618 2.02656 4.93046 2.51884C4.41473 3.01113 4.125 3.67881 4.125 4.375V7H2.75C2.02065 7 1.32118 7.27656 0.805456 7.76884C0.289731 8.26113 0 8.92881 0 9.625L0 13.5625C0 14.2587 0.289731 14.9264 0.805456 15.4187C1.32118 15.9109 2.02065 16.1875 2.75 16.1875H4.125V17.5C4.125 18.1962 4.41473 18.8639 4.93046 19.3562C5.44618 19.8484 6.14565 20.125 6.875 20.125H15.125C15.8543 20.125 16.5538 19.8484 17.0695 19.3562C17.5853 18.8639 17.875 18.1962 17.875 17.5V16.1875H19.25C19.9793 16.1875 20.6788 15.9109 21.1945 15.4187C21.7103 14.9264 22 14.2587 22 13.5625V9.625C22 8.92881 21.7103 8.26113 21.1945 7.76884C20.6788 7.27656 19.9793 7 19.25 7H17.875V4.375C17.875 3.67881 17.5853 3.01113 17.0695 2.51884C16.5538 2.02656 15.8543 1.75 15.125 1.75H6.875ZM5.5 4.375C5.5 4.0269 5.64487 3.69306 5.90273 3.44692C6.16059 3.20078 6.51033 3.0625 6.875 3.0625H15.125C15.4897 3.0625 15.8394 3.20078 16.0973 3.44692C16.3551 3.69306 16.5 4.0269 16.5 4.375V7H5.5V4.375ZM6.875 10.9375C6.14565 10.9375 5.44618 11.2141 4.93046 11.7063C4.41473 12.1986 4.125 12.8663 4.125 13.5625V14.875H2.75C2.38533 14.875 2.03559 14.7367 1.77773 14.4906C1.51987 14.2444 1.375 13.9106 1.375 13.5625V9.625C1.375 9.2769 1.51987 8.94306 1.77773 8.69692C2.03559 8.45078 2.38533 8.3125 2.75 8.3125H19.25C19.6147 8.3125 19.9644 8.45078 20.2223 8.69692C20.4801 8.94306 20.625 9.2769 20.625 9.625V13.5625C20.625 13.9106 20.4801 14.2444 20.2223 14.4906C19.9644 14.7367 19.6147 14.875 19.25 14.875H17.875V13.5625C17.875 12.8663 17.5853 12.1986 17.0695 11.7063C16.5538 11.2141 15.8543 10.9375 15.125 10.9375H6.875ZM16.5 13.5625V17.5C16.5 17.8481 16.3551 18.1819 16.0973 18.4281C15.8394 18.6742 15.4897 18.8125 15.125 18.8125H6.875C6.51033 18.8125 6.16059 18.6742 5.90273 18.4281C5.64487 18.1819 5.5 17.8481 5.5 17.5V13.5625C5.5 13.2144 5.64487 12.8806 5.90273 12.6344C6.16059 12.3883 6.51033 12.25 6.875 12.25H15.125C15.4897 12.25 15.8394 12.3883 16.0973 12.6344C16.3551 12.8806 16.5 13.2144 16.5 13.5625Z" fill="#1A7870"/>
                            </svg></a>
                  </td>';
            }
        }

        if (is_manager() ) {
            $html .= '<td class="td-action"><a  data-bs-toggle="modal" href="#bout-massage" role="button" data-action="remove">
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
            if(isset( $bonus_row) && !empty($bonus_row) && $column["field_name"] =="name"){
                $column_value .= ' - מבצע';
            }
            $html .= '<td class="' . $column["field_name"] .'">' . $column_value. '</td>';
            $columns_counter++;
        }
    }

    foreach($page_info["columns"] as $column) {
        //איזה שדות שמראים בכותרת(גם אם אין כיתוב של כותרת) אותו דבר להראות בשורה בטבלה והפוך שדות שמסתירים בכותרת להסתיר גם בשורה בטבלה
        if (isset($column["create_input"])) {
        } else {
            if (isset($column["hide_in_table"]) && !$is_list  || !isset($column["label"]) ||
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
        //write_log("row->field" .$field." row ". json_encode( $row));
        $column_value = get_column_value($column, $row, $field, $list, $key,isset($attr["readonly"])&& !empty($attr["readonly"]) || isset( $bonus_row) && !empty($bonus_row));
        //write_log("value ".$column_value);
        $class_td = "";
        if(isset($column["hide_in_table"])){
            $class_td = " hidden-col ";
        }
        $hidden = "";//(isset($column["widget"]) && $column["widget"]== "hidden"?' hidden':'');
        $html .= '<td ' . $data_id . ' class="' . $field .$class_td .'">' . $column_value . '</td>';
    }

    if(isset($page_info["actions"])) {
        foreach ($page_info["actions"] as $action) {
            if(is_array($action) && isset($action["dialog"])) {
                $obligation = 0;
                if($table_name == "clients" && is_manager()) {
                    $res = get_client_details($row->id);
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