<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

function import_from_xlsx()
{
    //$mpdf = new \Mpdf\Mpdf();
    //write_log ('mpdf '.json_encode ( class_exists('\Mpdf\Mpdf')));

   // write_log("import_from_xlsx");
    $msg = "";
    $count_success=0;
    $count_exists = 0;
    if (empty($_FILES) || ($_FILES["bills"]["size"] == 0)) {
        //write_log("_FILES ".json_encode($_FILES));
        write_log("empty_FILES ");
        die(json_encode(array(
            'status' => 'error',
            'msg' => 'לא נמצא קובץ.'
        )));
    }
   // write_log("_FILES ".json_encode($_FILES));
    if (file_exists ($_FILES["bills"]["tmp_name"])) {
        $tmpFile = $_FILES['bills']['tmp_name'];
        $extension = pathinfo($_FILES['bills']['name'], PATHINFO_EXTENSION);
        if($extension =="csv") {
            $reader = new Csv();
            $reader->setDelimiter(',');
            $reader->setInputEncoding('Windows-1255');
            $spreadsheet = $reader->load ($tmpFile);
        }
        else{
            $spreadsheet = IOFactory::load ($tmpFile);
        }
        $sheet = $spreadsheet->getActiveSheet ();
        //$methodReadSheet = $extension =="csv"? "getRowIterator":"toArray";
        $supplier_id = $_POST["supplier_id"];
        $table_data =get_data_table ("supplier_column_mapping",array(array("filter_field" => "supplier_id", "filter_value" => $supplier_id)));
        if(empty($table_data)){
            $rows = array_slice($sheet->toArray(), 0, 10);
           // $excel_rows=[];
            $html_rows='';
            $html_options = "<option value=''></option>";
            $html = "";
            foreach ($rows as $key=>$row){
                $html_rows.="<tr>";
                foreach ($row as $c=>$col){
                    if($key==0){
                        $html_rows.="<th>{$col}</th>";
                        $html_options.="<option value={$c}>{$col}</option>";
                    }
                    else{
                    $html_rows.="<td>{$col}</td>";
                    }
                }
                $html_rows.="</tr>";
            }
            write_log("empty supplier_column_mapping supplier_id ".$supplier_id);
            die(json_encode(array(
                'status' => 'failed',
                'supplier_id'=>$supplier_id,
                'excel_rows'=>$html_rows,
                'columns_options' =>$html_options,
                'msg' => 'נא ליצור מיפוי שדות לספק'
            )));
        }
        foreach ($table_data as $row)
        {
            $mapping[$row->excel_column_index] = $row->field_name;
        }
        //write_log ('sup mapping '.json_encode ($mapping));

        //$table_data = array(0=>"doc_number",1 => "date", 7 => "obligation", 10 => "payment_until", 13 => "doc_type", 16 => "BnNumber");

       // write_log ('sheet '.json_encode ($sheet->toArray()));
        $BnNumber_index = array_search ( "BnNumber",$mapping);
        $doc_number_index = array_search ( "doc_number",$mapping);//מספר חשבונית

        if ($BnNumber_index !==false && $doc_number_index !==false) {//אם בין השדות בקובץ של הספק יש ח.פ. ללקוח
            //write_log("sheets ".json_encode($sheet->toArray()));
            foreach ($sheet->toArray() as $key => $row_from_file) {
                if ($key == 0) continue;
                $row_to_table = [];
                $row_to_table["supplier_id"] = $supplier_id;
                $row_to_table["imported_at"] = date('Y-m-d',strtotime ('today'));
                write_log ('BnNumber '.json_encode ($row_from_file[$BnNumber_index]));
                if (empty($row_from_file[$BnNumber_index])) {//אם לא הגיע ח.פ. ללקוח בקובץ
                    continue;
                }
                global $wpdb;
                write_log (' query '."SELECT 1 FROM ".$wpdb->prefix."collection WHERE supplier_id = ".$supplier_id." and  doc_number ='".$row_from_file[$doc_number_index]."' LIMIT 1");
                $result = run_query ("SELECT 1 FROM ".$wpdb->prefix."collection WHERE supplier_id = ".$supplier_id." and doc_number ='".$row_from_file[$doc_number_index]."' LIMIT 1");
                write_log ('result '.json_encode ($result));
                 if(!empty($result)) {//this invoise number is exists
                     $count_exists++;
                     continue;
                 }
                $client = get_data_table("clients", array(array("filter_field" => "BnNumber", "filter_value" => $row_from_file[$BnNumber_index])));

                if(empty($client)){// אם לא נמצא לקוח עם הח.פ. שבשורה זו
                    continue;
                }
                $client=$client[0];
                $row_to_table["client_id"] =$client->id;
                foreach ($mapping as $index => $field_name) {
                    if (isset($row_from_file[$index])) {
                        if ($field_name == "date" || $field_name == "payment_until") {// צריך לשמור קודם את תאריך הקבלה ואח"כ לחשב את תשלום עד
                            $row_to_table[$field_name] = excel_date_to_php_date ($row_from_file[$index]);
                            // $date = DateTime::createFromFormat('d/m/Y', $row_from_file[$index]);
                            //$row_to_table[$field_name] = $date->format('Y-m-d');
                        } else {
                            $row_to_table[$field_name] = $row_from_file[$index];
                        }
                    } elseif ($field_name == "payment_until") {//אם לא הגיע לחשב לפי תנאי תשלום ללקוח
                        $date_index = array_search ("date", $mapping);
                        $date = date ('Y-m-d', strtotime (excel_date_to_php_date ($row_from_file[$date_index])));
                        $row_to_table[$field_name] = get_payment_until ($client->payment_term_id, $date);

                    }
                }
                write_log ('row_to_table ' . json_encode ($row_to_table));
                $result = pre_action_query ("collection", $row_to_table);
                write_log ('pre_action_query ' . json_encode ($result));
                //$ok = run_action_query ("collection", null, "new", $result);
                $ok = 1;
                if ($ok) {
                    $html .= get_tr_data ("collection", $result, null, array());
                    $count_success++;
                }
                // write_log(json_encode ( $row));
            }
            if($count_exists>0){
                $msg = "חלק מהחשבוניות כבר קיימות במערכת\n";
            }
            if ($count_success == 0 && $count_exists ==0) {
                $msg = "אירעה שגיאה בעת קליטת הקובץ , הרשומות לא נקלטו";
            } elseif ($key == $count_success) {
                $msg .= "הקובץ נקלט בהצלחה, {$count_success}   רשומות עודכנו";
            } elseif ($count_success < $key) {
                $msg .= "הקובץ נקלט בהצלחה, עודכנו {$count_success} רשומות, מתוך {$key} רשומות ";
            }
        }
         else {
            $msg = "לא נמצא שדה מזהה ללקוחות ";
        }
    }
    else{
        write_log ('file no exist');
    }

    write_log ('import collaction '.$msg);

    wp_send_json([
        'status' => 'success',
        'message' => $msg, //'הקובץ נקלט בהצלחה',
        'rows'=>$html,
        //'redirect' => isset($_POST["previous_page"]) ? $_POST["previous_page"]:'',
    ]);
}

function excel_date_to_php_date($excel_date)
{
    $dateString = str_replace('\/', '/', trim ( $excel_date));
    list($day, $month, $year) = explode('/', $dateString);
    if (strlen ($year)==2) $year = '20' . $year;
    return  "$year-$month-$day";
}
add_action('wp_ajax_save_list_data', 'save_list_data');
function save_list_data(){
    $table_name = $_POST["table_name"];
    $fields=[];
    $fields["supplier_id"]=$_POST["supplier_id"];
    write_log ('post  field name '.json_encode ($_POST['field_name']));
    foreach ($_POST['field_name'] as $field_name => $column_index){
        if(empty($column_index)) continue;
        $fields["field_name"]=$field_name;
        $fields["excel_column_index"]=$column_index;
        $result =pre_action_query ($table_name,$fields);
        $ok = run_action_query ($table_name, null, "new", $result);
    }

    echo json_encode (array(
        'status' => 'success',
        'message' => 'נשמרו השדות לספק' //'הקובץ נקלט בהצלחה',
        //'redirect' => isset($_POST["previous_page"]) ? $_POST["previous_page"]:'',
    ));
    wp_die();
}
function supplier_column_mapping_modal(){
    ?>
    <form class="modal fade site_form" id="supplier_column_mapping_modal" data-success="import_from_xlsx"  tabindex='-1' role="dialog">
        <input type="hidden" name="form_func" value="save_list_data">
        <input type="hidden" name="supplier_id" value="">
        <input type="hidden" name="table_name" value="supplier_column_mapping">
        <div class="modal-dialog" role="document">

            <div class="modal-content">
                <div class="modal-header flex-display">
                    <h3 class="modal-title grow" >הינך מבקש לקלוט קובץ גביה מספק, עליך לבחור עמודות מתוך הקובץ שיקלטו לתוך החשבוניות במערכת<span class="bill-num"></span></h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="סגור">
                    </button>
                </div>
                <div class="modal-body border-dark-gray padding-30 flex-display direction-column margin-20 font-15">
                    <table class="part-20">
                        <tr>
                            <th>המידע לחשבונית</th>
                            <td>ח.פ. של הלקוח *</td>
                            <td>מספר חשבונית *</td>
                            <td>תאריך החשבונית *</td>
                            <td>סכום *</td>
                            <td>חיוב/זיכוי</td>
                            <td>לתשלום עד</td>
                        </tr>
                        <tr>
                            <th>העמודה מקובץ האקסל</th>
                            <td><select name="field_name[BnNumber]" required></select></td>
                            <td><select name="field_name[doc_number]" required></select></td>
                            <td><select name="field_name[date]" required></select></td>
                            <td><select name="field_name[obligation]" required></select></td>
                            <td><select name="field_name[doc_type]"></select></td>
                            <td><select name="field_name[payment_until]"></select></td>
                        </tr>
                    </table>
                    <span class="bold">המידע מהספק כפי שמופיע בקובץ האקסל </span>
                    <div class="excel-table-container">
                        <table class="excel-rows part-70">
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="ok background-gold bold font-18">אישור</button>
                    <button type="button" class="background-white gold" data-bs-dismiss="modal">ביטול</button>

                </div>
            </div>
        </div>
    </form>
    <?php
}
?>