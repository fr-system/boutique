<?php
if(!isset($argv) || count($argv)==0)return;
require_once(__DIR__ . '/../../../wp-load.php');
//write_log ($argv[1]);
write_log("send_who_needs_pay_today ".date('d/m/Y H:i'));
$filters = array();
$filters[] = array("filter_field" => "payment_date", "filter_type" => "null");

    $filters[] = array("filter_field" => "doc_type", "filter_value" => "1");
    if ($argv[1] == "daily") {
        $filters[] = array("filter_field" => "payment_until","filter_type"=>"date","filter_ratio"=>"=","filter_value"=>"CURDATE()");

    }

    if ($argv[1] == "weekly") {
        $filters[] = array("filter_field" => "payment_until", "filter_type" => "date", "filter_ratio" => "<", "filter_value" => "CURDATE()");
    }
$result = get_data_table("collection", $filters);
write_log("collection ".json_encode($result));
$late_pay = "";
$count = 0;
foreach ($result as $row) {
    //$client = get_data_table("clients", array(array("filter_field" => "client_id", "filter_value" => $row->client_id)));

    /*if ($attr["type"] == "daily") ||//לבדוק אם היום מוצ"ש ???
        $attr["type"] == "weekly" && $row->payment_until < date('Y-m-d')) {*/
    $late_pay = "חשבונית מספר " . $row->doc_number . " בתאריך" . date('d/m/Y', strtotime($row->date)) .
        " ללקוח " . $row->client_name . " על סך של " . $row->obligation . " ₪ <br>";
    $count++;
    /* }*/
}

if($count > 0) {
    if ($argv[1] == "daily") {
        $body = "להלן רשימת החשבוניות שהיו צריכים לשלם אותן היום ועדיין לא שולמו  <br>";
        $subject = "חשבוניות שלא שולמו היום";
    } else if ($argv[1] == "weekly") {
        $body = "להלן רשימת החשבוניות שעדיין לא שולמו<br>";
        $subject = "סיכום שבועי לחשבוניות שלא שולמו";
    }
    $body .= $late_pay;
    //write_log("body ".$body);

    //send_mail(get_option('admin_email'), $subject, $body);
}


?>