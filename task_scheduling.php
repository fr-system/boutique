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
        send_task_reminders();
        send_unclosed_tasks($argv[2]);
        break;
    case "orders":
        send_orders_today($argv[2]);
        break;
}


function send_who_needs_pay_today($type)
{
    //daily - חשבוניות שמועד התשלום שלהן היום (במוצאי שבת גם של שישי), weekly - כל החשבוניות שמועד התשלום שלהן עבר
    if ($type == "weekly") {
        $title = "חשבוניות שלא שולמו - סיכום שבועי";
        $text = "מצורפות כל החשבוניות שמועד התשלום שלהן עבר ועדיין לא שולמו.";
    } else {
        $title = "חשבוניות שמועד התשלום שלהן היום";
        $text = date('w') == 6
            ? "מצורפות החשבוניות שמועד התשלום שלהן היה בשישי או בשבת, ועדיין לא שולמו."
            : "מצורפות החשבוניות שמועד התשלום שלהן היום, ועדיין לא שולמו.";
    }

    $attr = ["report_title" => $title, "export" => "single", "type" => $type, "packet" => ["obligations"], "send_mail" => true, "create_only_fill" => true];
    $file = create_pdf($attr);
    if (empty($file)) {
        return;
    }

    $body = build_email_layout($title, "שלום,",
        '<div style="font-size:15px; line-height:1.7; color:#444444; margin-bottom:28px;">' . esc_html($text) . '<br>אחרי קבלת תשלום יש לעדכן אותו במערכת, במסך החשבוניות.</div>');
    send_mail(get_option('admin_email'), $title . " - " . date('d/m/y'), $body, [$title . ".pdf" => $file]);
    delete_report_file($file);
}

//תזכורות: משימות פתוחות שתאריך התזכורת שלהן הגיע (או עבר - למשל שישי ושבת, שבהם לא שולחים) ועוד לא נשלחה עליהן תזכורת.
//לכל סוכן מייל אחד עם המשימות שלו; משימה בלי סוכן (או סוכן בלי מייל תקין) - למנהל. אחרי שליחה - reminder_sent = 1
function send_task_reminders()
{
    global $wpdb;
    $filters = array(
        array("filter_field" => "status_id", "filter_type" => "!=", "filter_value" => "1"),
        array("filter_type" => "filter", "filter_field" => "",
            "filter_value" => "({$wpdb->prefix}tasks.sending_reminder <= CURDATE() AND {$wpdb->prefix}tasks.reminder_sent = 0)"),
    );
    $tasks = get_data_table("tasks", $filters);
    if (empty($tasks)) {
        return;
    }

    //קיבוץ לפי נמען
    $groups = array();
    foreach ($tasks as $task) {
        $agent = !empty($task->agent_id) ? (get_data_table("agents", array(array("filter_field" => "id", "filter_value" => intval($task->agent_id))))[0] ?? null) : null;
        $email = $agent ? get_agent_email($agent) : "";
        if (!$email) {
            $email = get_option('admin_email');
            $name = "";
        } else {
            $name = $agent->name;
        }
        $groups[$email]["name"] = $name;
        $groups[$email]["tasks"][] = $task;
    }

    foreach ($groups as $email => $group) {
        $rows = "";
        foreach ($group["tasks"] as $task) {
            $rows .= '<tr>
                <td style="padding:8px 10px; border-bottom:1px solid #EFE6D2; font-weight:700;">' . esc_html($task->subject_text) . '</td>
                <td style="padding:8px 10px; border-bottom:1px solid #EFE6D2;">' . esc_html($task->client_name) . '</td>
                <td style="padding:8px 10px; border-bottom:1px solid #EFE6D2;">' . nl2br(esc_html($task->details)) . '</td>
                <td style="padding:8px 10px; border-bottom:1px solid #EFE6D2; white-space:nowrap;">' . (!empty($task->target_date) ? date('d/m/Y', strtotime($task->target_date)) : '') . '</td>
            </tr>';
        }
        $intro = count($group["tasks"]) == 1 ? "תזכורת למשימה שלך:" : "תזכורת למשימות שלך:";
        if ($group["name"] === "") {
            $intro = "תזכורת למשימות שאין להן סוכן עם מייל תקין:";
        }
        $content = '<div style="font-size:15px; line-height:1.7; color:#444444; margin-bottom:16px;">' . $intro . '</div>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; color:#1A1A1A; text-align:right; margin-bottom:24px;">
                <tr style="background-color:#FAF7F0;"><th style="padding:8px 10px;">נושא</th><th style="padding:8px 10px;">לקוח</th><th style="padding:8px 10px;">פירוט</th><th style="padding:8px 10px;">תאריך יעד</th></tr>
                ' . $rows . '
            </table>
            <div style="font-size:13px; line-height:1.7; color:#8A8A8A; margin-bottom:28px;">אחרי ביצוע משימה יש לעדכן את המצב שלה במערכת.</div>';
        $body = build_email_layout("תזכורת משימות", "שלום" . ($group["name"] !== "" ? " " . esc_html($group["name"]) : "") . ",", $content);
        if (send_mail($email, "תזכורת משימות - " . date('d/m/y'), $body)) {
            $ids = array_map(function ($task) { return intval($task->id); }, $group["tasks"]);
            $wpdb->query("UPDATE {$wpdb->prefix}tasks SET reminder_sent = 1 WHERE id IN (" . implode(',', $ids) . ")");
        }
    }
}

//לכל סוכן - המשימות שלו שתאריך היעד שלהן היום או שכבר עבר (הדוח נשלח בבוקר). למנהל - סיכום של המשימות שתאריך היעד שלהן עבר (אתמול ולפני), לפי סוכן
//במוצאי שבת נכללות גם המשימות של שישי - הדוח כולל את כל המשימות הפתוחות שהתאריך שלהן עבר
function send_unclosed_tasks($type)
{
    global $wpdb;
    $query = "SELECT agent_id FROM {$wpdb->prefix}tasks WHERE status_id != 1 AND target_date <= CURDATE() GROUP BY agent_id";
    $results = run_query($query);
    if (empty($results)) {
        return;
    }
    $subject = "משימות שהגיע תאריך היעד שלהן";

    //לכל סוכן - רק המשימות שלו
    foreach ($results as $result) {
        $agent = get_data_table("agents", array(array("filter_field" => "id", "filter_value" => intval($result->agent_id))))[0] ?? null;
        $agent_email = $agent ? get_agent_email($agent) : "";
        if (!$agent_email) {
            continue;
        }
        $attr = ["report_title"=>"משימות שהגיע תאריך היעד", "export" => "archive", "type" => $type, "packet" => ["tasks"], "send_mail" => true, "create_only_fill" => true,"agent_id"=>$result->agent_id, "include_today" => true];
        $file = create_pdf($attr);
        if (empty($file)) {
            continue;
        }
        $body = build_email_layout("תזכורת משימות", "שלום " . esc_html($agent->name) . ",",
            '<div style="font-size:15px; line-height:1.7; color:#444444; margin-bottom:28px;">מצורפות המשימות שלך שתאריך היעד שלהן היום או שכבר עבר, ועדיין לא נסגרו.<br>אחרי ביצוע משימה יש לעדכן את המצב שלה במערכת.</div>');
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
    send_mail(get_option('admin_email'), "משימות שעבר תאריך היעד שלהן - סיכום", $body, ["משימות פתוחות - סיכום.pdf" => $file]);
    delete_report_file($file);
}
function send_orders_today($time)
{
    //שני קבצים: הזמנות שאושרו והזמנות שעדיין לא אושרו. קובץ בלי הזמנות לא נוצר
    $attr = ["report_title"=>"פרטי הזמנה","export" => "single", "time" => $time, "packet" => ["orders_today"], "send_mail" => true, "create_only_fill" => true];
    $files = array();
    $parts = array();
    foreach (array("הזמנות שלא אושרו" => false, "הזמנות שאושרו" => true) as $name => $approved) {
        $file = create_pdf($attr + ["approved" => $approved]);
        if (!empty($file)) {
            $files[$name . ".pdf"] = $file;
            $parts[] = $name;
        }
    }
    if (empty($files)) {
        return;
    }
    //הטווח של הדוח (drow_html_orders_today) - מהדוח הקודם עד עכשיו
    $from_text = array(13 => "מאתמול 23:00", 18 => "מ-13:00", 23 => "מ-18:00")[intval($time)] ?? "";
    $range = (date('w') == 6 ? "מחמישי 23:00" : $from_text) . " עד " . intval($time) . ":00";
    $subject = "הזמנות " . $range . " - " . date('d/m/y');
    $body = build_email_layout("סיכום הזמנות", "שלום,",
        '<div style="font-size:15px; line-height:1.7; color:#444444; margin-bottom:28px;">מצורפות ההזמנות שנפתחו ' . esc_html($range) . ':<br>' . esc_html(implode(", ", $parts)) . '.</div>');
    send_mail(get_option('admin_email'), $subject, $body, $files);
    foreach ($files as $file) {
        delete_report_file($file);
    }
}

?>