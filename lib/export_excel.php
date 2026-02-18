<?php
require("xlsxwriter.class.php");
require_once( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php' );

if(!isset($_GET['export']))return;
$data = array();
switch ( fixXSS( $_GET['export'] ) ) {
	case 'archive':
		test_mode_table_prefix();
		$table_name = $_GET["subject"];
		$page_info = BOUTIQUE_TABLES[$table_name];
		$list = get_page_data($table_name);

		$fname = BOUTIQUE_TABLES[$table_name]["title"];
		$header = array();
		foreach ($page_info["columns"] as $column) {
			if (isset($column["hidden"]) || !isset($column["label"])) {
				continue;
			}
			$header[] = array($column["label"] => $column["widget"]);
		}

		$data = array();

		/*foreach ($list as $item) {
			$row = array();

			foreach ($page_info["columns"] as $column) {
				if (!isset($column['field_name'])) {
					continue;
				}

				$field = isset($column['join_table']) ? substr($column['join_table'], 0, -1) . "_" . $column['join_value'] : $column["field_name"];
				$list_name = isset($column['table_name']) ? constant($column['table_name']) : null;

				if (!isset($column["hidden"]) && isset($column["label"])) {
					$column_value = get_column_value($column, $row, $field, $list_name);
					$row[] = $item;
				}
			}
			$data[] = $row;
		}*/
	break;
}
//write_log("4445453");
$time   = date( 'd-m-Y' );
//$fname  = "$fname $time.xlsx";
//header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
//header( 'Content-Disposition: attachment;filename="' . $fname . '"' );
//header( 'Cache-Control: max-age=0' );
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="file.csv"');

$writer = new XLSXWriter();
$writer->writeSheetHeader('Sheet1', []);
$writer->writeSheet([], 'Sheet1');

// שלח את הקובץ לסטנדרט פלט
//$writer->writeToStdOut();
//exit(0);



//$writer = new XLSXWriter();
//$writer->setAuthor( 'בוטיק כשר' );
//$header = array(
//	'c1-text'=>'string',//text
//	'c2-text'=>'string',//text
//);
//$data = array(
//	array('abcdefg','hijklmnop'),
//);
//$writer = new XLSXWriter();
//$writer->setRightToLeft(true);
//$writer->writeSheet( $data, 'MySheet1',$header );  // with headers



//$writer->writeSheetHeader('Sheet1', $header);
//foreach($rows as $row) {
//	$writer->writeSheetRow('Sheet1', $row);
//	write_log("+++++++++++++");
//}
//$writer->writeToFile('xlsx-right-to-left.xlsx');

$writer->writeToStdOut();
exit(0);
?>
