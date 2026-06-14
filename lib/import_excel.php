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
        write_log("_FILES ".json_encode($_FILES));
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
            foreach ($rows as $key=>$row){
                $html_rows.="<tr>";
                foreach ($row as $col){
                    if($key==0){
                        $html_rows.="<th>{$col}</th>";
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
                $ok = run_action_query ("collection", null, "new", $result);
                if ($ok) {
                    $count_success++;
                }
                // write_log(json_encode ( $row));
            }
            if($count_exists>0){
                $msg = "חלק מהחשבוניות כבר קיימות במערכת";
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
    //add_notice( 'import_excel' ,$msg );
    echo json_encode (array(
        'status' => 'success',
        'msg' => $msg //'הקובץ נקלט בהצלחה',
        //'redirect' => isset($_POST["previous_page"]) ? $_POST["previous_page"]:'',
    ));
    wp_die();
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
    foreach ($_POST['field_name'] as $field_name => $column_index){
        $fields["field_name"]=$field_name;
        $fields["excel_column_index"]=$column_index;
        $result =pre_action_query ($table_name,$fields);
        $ok = run_action_query ($table_name, null, "new", $result);
    }

    echo json_encode (array(
        'status' => 'success',
        'msg' => 'נשמרו השדות לספק' //'הקובץ נקלט בהצלחה',
        //'redirect' => isset($_POST["previous_page"]) ? $_POST["previous_page"]:'',
    ));
    wp_die();
}
?>