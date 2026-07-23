<?php
if(!isset($argv) || count($argv)==0)return;
require_once(__DIR__ . '/../../../wp-load.php');
//write_log ($argv[1]);
date_default_timezone_set('Asia/Jerusalem');
write_log("task sheduler: ".date('d/m/Y H:i')." argv ".json_encode($argv));
//לבדוק אם היום שבת שלא ישלח ובמוצ"ש לקחת את הנתונים של יום קודם
if(date('w') == 5)return;//לחשוב אם רוצים לבדוק חגים
switch ($argv[1]){
    case "obligations":
        send_who_needs_pay_today($argv[2]);
        break;
    case "tasks":
        send_unclosed_tasks($argv[2]);
        break;
    case "orders":
        send_orders_today($argv[2]);
        break;

}


function send_who_needs_pay_today($type)
{
    //$argv = array("", "weekly");

    $attr = ["export" => "single", "type" => $type, "packet" => ["obligations"], "send_mail" => true, "create_only_fill" => true];
    $file = create_pdf($attr);
    if ($file == null) exit;

    if ($type == "daily") {
        $subject = "חשבוניות שלא שולמו היום";
    } else if ($type == "weekly") {
        $subject = "סיכום שבועי לחשבוניות שלא שולמו";
    }

    send_mail(get_option('admin_email'), $subject, "<br><br>בברכה, בוטיק כשר", [$file]);
}

function send_unclosed_tasks($type)
{
    $query = "SELECT agent_id FROM test_tasks WHERE status_id != 1 AND target_date < CURDATE() GROUP BY agent_id";
    $results = run_query($query);
    //write_log("res ".json_encode($results));
    foreach ($results as $result) {
        $attr = ["export" => "archive", "type" => $type, "packet" => ["tasks"], "send_mail" => true, "create_only_fill" => true,"agent_id"=>$result->agent_id];
        $file = create_pdf($attr);
        if ($file == null) exit;
        $subject = "משימות שלא נסגרו ועבר תאריך היעד";
        send_mail(get_option('admin_email'), $subject, "<br><br>בברכה, בוטיק כשר", [$file]);
    }
}
function send_orders_today($type)
{
    $attr = ["export" => "single", "type" => $type, "packet" => ["orders_today"], "send_mail" => true, "create_only_fill" => true];
    $file = create_pdf($attr);
    if ($file == null) exit;
    $subject = "הזמנות מהיום";
    send_mail(get_option('admin_email'), $subject, "<br><br>בברכה, בוטיק כשר", [$file]);

}

?>