<?php
function import_from_xlsx()
{
    require 'XLSXReader.php';
    write_log("import_from_xlsx");
    $msg = "";
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
//        $tmpFile = $_FILES['bills']['tmp_name'];
//
       $uploadDir = ABSPATH . 'uploads/my-folder/';
//
//        if (!file_exists($uploadDir)) {
//            wp_mkdir_p($uploadDir);
//        }
//
        $destination = $uploadDir . basename($_FILES['bills']['name']);
//
//        if (move_uploaded_file($tmpFile, $destination)) {
//            write_log( 'File moved');
//        } else {
//            write_log ('Move failed');
//        }
//        if ($xlsx = SimpleXLSX::parse ($destination)) {
//            write_log ('rows ' . json_encode ($xlsx->rows ()));
//        }
        $sheets = getXlsxData($destination);
        write_log("heets ".json_encode($sheets));
    }
    else{
        write_log ('file no exist');
    }
    /* $sheets = getXlsxData($_FILES["bills"]["tmp_name"]);
     write_log("heets ".json_encode($sheets));
     foreach ($sheets as $sheet) {
         foreach ($sheet as $key => $row) {
             if ($key == 0) continue;

         }
     }*/
    die;
}
function getXlsxData ($file){
    write_log ('getXlsxData '.$file);
    $xlsx = new XLSXReader($file);
    write_log ('getXlsxData xlsx '.json_encode ( $xlsx));
    $sheetNames = $xlsx->getSheetNames();
    write_log ('getXlsxData sheetNames '.json_encode ( $sheetNames));
    die;
    $data = array();
    foreach($sheetNames as $sheetName) {
        $sheet = $xlsx->getSheet($sheetName);
        $data[] = $sheet->getData();
    }
    return $data;
}
?>