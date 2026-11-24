<?php
require("xlsxwriter.class.php");
require_once( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php' );

if(isset($_GET['export'])) {
	//רק למשתמש מחובר
	if (!is_user_logged_in()) {
		wp_die("יש להתחבר למערכת", "", array("response" => 403));
	}
	switch (fixXSS ($_GET['export'])) {
		case 'archive':
			$table_name = $_GET["subject"];
			//אותו סינון ומיון כמו בעמוד הארכיב
			//השורות שמוצגות בעמוד אחרי סינון - נשלחות בטופס POST (rivka.js, export-link)
			$params = $_GET;
			if (isset($_POST["ids"])) {
				$params["ids"] = $_POST["ids"];
			}
			$archive = get_archive_filters($table_name, $params);
			$packet = get_data_to_export($table_name,"xlsx",$archive["filters"],$archive["orderby"]);
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
