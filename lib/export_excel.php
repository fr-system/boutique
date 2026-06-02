<?php
require("xlsxwriter.class.php");
require_once( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php' );

if(isset($_GET['export'])) {
	$headers = [];
	$data = array();
	switch (fixXSS ($_GET['export'])) {
		case 'archive':
			test_mode_table_prefix ();
			$table_name = $_GET["subject"];
			$page_info = BOUTIQUE_TABLES[$table_name];
			$filters = array();
			if(isset($_GET["ids"])){
				$filters[]=array("filter_field"=>"id","filter_value"=>$_GET["ids"],"filter_type" => "array");
			}
			$list = get_data_table ($table_name,$filters);

			$fname = $page_info["title"];

			foreach ($page_info["columns"] as $column) {
				if (isset($column["hidden"]) || !isset($column["label"])) {
					continue;
				}

				$headers[$column["label"]] = get_column_type ($column["widget"]);
			}

			$data = [];
			foreach ($list as $item) {
				$row = [];
				foreach ($page_info["columns"] as $column) {
					if (!isset($column['field_name'])) {
						continue;
					}

					$field = isset($column['join_table']) ? substr ($column['join_table'], 0, -1) . "_" . $column['join_value'] : $column["field_name"];
					$list_name = isset($column['table_name']) ? constant ($column['table_name']) : null;

					if (!isset($column["hidden"]) && isset($column["label"])) {
						$column_value = get_value ($column, $item, $field);
						$row[] = $column_value;
					}
				}
				$data[] = $row;
			}
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

function  get_column_type($widget)
{
	switch ($widget){
		case "number":
			return "integer";
		case "date":
			return "date";
		default:
			return "string";
	}


}

function get_value($column,$row,$field)
{		$column_value = "";
		switch ($column["widget"]) {
			case "select":
				/*if ($column["join_table"] == "agents") {
					//write_log ('fiel ' . $field);
					//write_log ('row ' . json_encode ($row));
					$user_field = $column["field_name"];
					$column_value = empty($row->$user_field) ? '' : get_userdata($row->$user_field)->display_name;
				} else {*/
					$column_value = $row->$field;
				/*}*/
				break;
			case "radio":
				case "status":
				$column_value = $column["values"][$row->$field]["label"];
				break;
			case "date":
				case "datetime-local":
				if ($row->$field) {
					$timestamp = strtotime($row->$field); // המרת התאריך לאטימות זמן
					$format = 'd/m/Y';
					if ($column["widget"] == "datetime-local") {
						$format .= " H:i:s";
					}
					$column_value = date($format, $timestamp);
				}
				break;
			default:
				/*if ($column["field_name"] == "display_name" || $column["field_name"] == "user_email") {
					$user_field = $column["field_name"];
					$column_value =  get_userdata($row->user_id)->$user_field;
				}
				else {*/
					$column_value = isset($column['list_name']) && isset($list[$row->$field]) ? $list[$row->$field] : $row->$field;
				/*}*/
				break;
		}

		return $column_value;
}


?>
