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
        $spreadsheet = IOFactory::load($tmpFile);

        $sheet = $spreadsheet->getActiveSheet();
        //write_log("sheets ".json_encode($sheet->toArray()));
        foreach ($sheet->toArray() as $key=>$row_from_file) {
//$row_to_table =[];
//$row_to_table["obligation"]= $row_from_file["חיוב"]
            $result = pre_action_query ("collection", $row);
            $ok = run_action_query ("collection", null, "new", $result);
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