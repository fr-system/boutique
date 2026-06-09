<?php
require("xlsxwriter.class.php");
require_once( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php' );

if(isset($_GET['export'])) {
	switch (fixXSS ($_GET['export'])) {
		case 'archive':
			test_mode_table_prefix ();
			$table_name = $_GET["subject"];
			$packet = get_data_to_export($table_name,"xlsx");
			$headers = $packet["headers"];
			$data = $packet["data"];
			break;
	}

	ob_end_clean ();
	header ('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header ('Content-Disposition: attachment; filename="file.xlsx"');
	header ('Cache-Control: max-age=0');

	$writer = new XLSXWriter();

	$writer->writeSheetHeader ('Sheet1',
		$headers
	);
	foreach ($data as $row) {
		//$writer->writeSheetRow('Sheet1', [1, 'David']);
		$writer->writeSheetRow ('Sheet1', $row);
	}

	$writer->writeToStdOut ();
	exit;
}

?>
