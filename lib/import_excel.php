<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
function import_from_xlsx()
{
    //$mpdf = new \Mpdf\Mpdf();
    //write_log ('mpdf '.json_encode ( class_exists('\Mpdf\Mpdf')));

   // write_log("import_from_xlsx");
    $msg = "";
    $count_success=0;
    if (empty($_FILES) || ($_FILES["bills"]["size"] == 0)) {
        write_log("_FILES ".json_encode($_FILES));
        write_log("empty_FILES ");
        die(json_encode(array(
            'status' => 'error',
            'msg' => 'לא נמצא קובץ.'
        )));
    }
    write_log("_FILES ".json_encode($_FILES));
    if (file_exists ($_FILES["bills"]["tmp_name"])) {
        $tmpFile = $_FILES['bills']['tmp_name'];
        $supplier_id = $_POST["supplier_id"];
        //$list= get_excel_order_field($supplier_id);//צריך לשמור בטבלה לכל ספק את השדות שלו?
        $list = array(1=>"date",4=>"client_name",7=>"obligation",10=>"payment_until",13=>"doc_type");
        $spreadsheet = IOFactory::load($tmpFile);
        $sheet = $spreadsheet->getActiveSheet();
        //write_log("sheets ".json_encode($sheet->toArray()));
        foreach ($sheet->toArray() as $key=>$row_from_file) {
            if($key==0)continue;
            $row_to_table =[];
            $row_to_table["supplier_id"]= $supplier_id;
            foreach ($list as $index => $field_name) {
                if (isset($row_from_file[$index])) {
                    if(empty($row_from_file[$index])&& $field_name =="payment_until"){//אם לא הגיע לחשב לפי תנאי תשלום ללקוח

                    }
                    elseif ($field_name=="date"|| $field_name =="payment_until"){
                       // $date = DateTime::createFromFormat('d/m/Y', $row_from_file[$index]);
                        //$row_to_table[$field_name] = $date->format('Y-m-d');

                    }
                    else {
                        $row_to_table[$field_name] = $row_from_file[$index];
                    }
                }
            }
            write_log ('row_to_table '.json_encode ($row_to_table));
            $result = pre_action_query ("collection", $row_to_table);
            write_log ('pre_action_query '.json_encode ($result));
            $ok = 1;// run_action_query ("collection", null, "new", $result);
            if($ok){
                $count_success++;
            }
           // write_log(json_encode ( $row));
        }
        if($count_success ==0){
            $msg="אירעה שגיאה בעת קליטת הקובץ , הרשומות לא נקלטו";
        }
        elseif($key==$count_success){
            $msg="הקובץ נקלט בהצלחה, {$count_success}   רשומות עודכנו";
        }
        elseif($count_success<$key){
            $msg="הקובץ נקלט בהצלחה, עודכנו {$count_success} רשומות, מתוך {$key} רשומות ";
        }
    }
    else{
        write_log ('file no exist');
    }

    echo json_encode (array(
        'status' => 'success',
        'msg' => $msg //'הקובץ נקלט בהצלחה',
        //'redirect' => isset($_POST["previous_page"]) ? $_POST["previous_page"]:'',
    ));
    wp_die();
}
?>