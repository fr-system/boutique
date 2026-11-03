<?php
if(!isset($argv) || count($argv)==0)return;
require_once(__DIR__ . '/../../../wp-load.php');
//write_log ($argv[1]);
date_default_timezone_set('Asia/Jerusalem');
write_log("task sheduler: ".date('d/m/Y H:i')." argv ".json_encode($argv));
//בשישי ובשבת עד 20:00 לא שולחים. במוצאי שבת הדוחות כוללים גם את הנתונים של שישי
//(drow_html_obligations, drow_html_orders_today)
if(date('w') == 5 || date('w') == 6 && date('G') < 20)return;//לחשוב אם רוצים לבדוק חגים
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
    delete_report_file($file);
}

//משימות שעבר תאריך היעד שלהן: לכל סוכן המשימות שלו, ולמנהל סיכום של כל הסוכנים.
//במוצאי שבת נכללות גם המשימות של שישי - הדוח כולל את כל המשימות הפתוחות שהתאריך שלהן עבר
function send_unclosed_tasks($type)
{
    global $wpdb;
    $query = "SELECT agent_id FROM {$wpdb->prefix}tasks WHERE status_id != 1 AND target_date < CURDATE() GROUP BY agent_id";
    $results = run_query($query);
    if (empty($results)) {
        return;
    }
    $subject = "משימות שעבר תאריך היעד שלהן";

    //לכל סוכן - רק המשימות שלו
    foreach ($results as $result) {
        $agent = get_data_table("agents", array(array("filter_field" => "id", "filter_value" => intval($result->agent_id))))[0] ?? null;
        $agent_email = $agent ? get_agent_email($agent) : "";
        if (!$agent_email) {
            continue;
        }
        $attr = ["report_title"=>"משימות שעבר תאריך היעד", "export" => "archive", "type" => $type, "packet" => ["tasks"], "send_mail" => true, "create_only_fill" => true,"agent_id"=>$result->agent_id];
        $file = create_pdf($attr);
        if (empty($file)) {
            continue;
        }
        $body = build_email_layout("תזכורת משימות", "שלום " . esc_html($agent->name) . ",",
            '<div style="font-size:15px; line-height:1.7; color:#444444; margin-bottom:28px;">מצורפות המשימות שלך שעבר תאריך היעד שלהן ועדיין לא נסגרו.<br>אחרי ביצוע משימה יש לעדכן את המצב שלה במערכת.</div>');
        send_mail($agent_email, $subject, $body, ["משימות פתוחות.pdf" => $file]);
        delete_report_file($file);
    }

    //למנהל - סיכום של כל הסוכנים
    $attr = ["report_title"=>"משימות שעבר תאריך היעד", "export" => "archive", "type" => $type, "packet" => ["tasks_by_agent"], "send_mail" => true, "create_only_fill" => true];
    $file = create_pdf($attr);
    if (empty($file)) {
        return;
    }
    $body = build_email_layout("משימות שלא נסגרו", "שלום,",
        '<div style="font-size:15px; line-height:1.7; color:#444444; margin-bottom:28px;">מצורף סיכום המשימות שעבר תאריך היעד שלהן ועדיין לא נסגרו, לפי סוכן.</div>');
    send_mail(get_option('admin_email'), $subject . " - סיכום", $body, ["משימות פתוחות - סיכום.pdf" => $file]);
    delete_report_file($file);
}
function send_orders_today($time)
{
    $attr = ["report_title"=>"פרטי הזמנה","export" => "single", "time" => $time, "packet" => ["orders_today"], "send_mail" => true, "create_only_fill" => true];
    $file = create_pdf($attr);
    if ($file == null) exit;
    $subject = (date('w') == 6 ? "הזמנות משישי ומוצאי שבת" : "הזמנות מהיום") . " נכון ל: " .date ('d/m/y H:i');
    send_mail(get_option('admin_email'), $subject, "<br><br>בברכה, בוטיק כשר", [$file]);
    delete_report_file($file);
}

?>