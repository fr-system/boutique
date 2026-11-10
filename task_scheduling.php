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
        send_agents_tasks_mail();
        send_unclosed_tasks($argv[2]);
        break;
    case "orders":
        send_orders_today($argv[2]);
        break;
    case "agents_daily":
        send_agents_daily_summary();
        break;
}

//סיכום יומי לכל סוכן (תזמון ב-19:00): רק הזמנות שהסוכן עצמו הקים (user_opens), מהסיכום הקודם עד עכשיו -
//טבלה לפי חנות וטבלה לפי יקב (ספק), ותעודות ההחזרה בטבלה נפרדת. סוכן בלי הזמנות והחזרות - לא מקבל מייל.
//ביום ראשון - מחמישי ב-19:00 (בשישי ובשבת לפני 20:00 לא שולחים)
function send_agents_daily_summary()
{
    global $wpdb;
    $p = $wpdb->prefix;
    $from = date('w') == 0 ? "CURDATE() - INTERVAL 3 DAY + INTERVAL 19 HOUR" : "CURDATE() - INTERVAL 1 DAY + INTERVAL 19 HOUR";
    $to = "CURDATE() + INTERVAL 19 HOUR";
    $range_text = date('w') == 0 ? "מחמישי 19:00 עד היום 19:00" : "מאתמול 19:00 עד היום 19:00";

    $agents = $wpdb->get_results("SELECT id, name, email, user_id FROM {$p}agents WHERE user_id > 0");
    foreach ($agents as $agent) {
        $email = get_agent_email($agent);
        if (!$email) {
            continue;
        }
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT o.id AS order_id, o.done, o.return_certificate, c.name AS client_name, cb.name AS branch_name, s.name AS supplier_name,
                    op.count, op.order_individual, p.units_in_box, op.total
             FROM {$p}orders o
             JOIN {$p}clients c ON c.id = o.client_id
             LEFT JOIN {$p}clients_branches cb ON cb.id = o.branch
             JOIN {$p}order_products op ON op.order_id = o.id
             JOIN {$p}products p ON p.id = op.product_id
             LEFT JOIN {$p}suppliers s ON s.id = p.supplier_id
             WHERE o.order_date > {$from} AND o.order_date <= {$to}
               AND op.count > 0
               AND o.user_opens = %d",
            intval($agent->user_id)));
        if (empty($rows)) {
            continue;
        }

        $stores = array();
        $suppliers = array();
        $total_units = 0;
        $total_sum = 0;
        $orders = array();
        $not_approved = array();
        //תעודות החזרה - נספרות בנפרד ומוצגות בטבלה משלהן
        $return_stores = array();
        $return_orders = array();
        $return_units = 0;
        $return_sum = 0;
        foreach ($rows as $row) {
            $units = intval($row->order_individual) == 1 ? intval($row->count) : intval($row->count) * max(1, intval($row->units_in_box));
            $sum = abs(floatval($row->total));
            $store = $row->client_name . (!empty($row->branch_name) ? " - " . $row->branch_name : "");
            $supplier = $row->supplier_name ?: "ללא ספק";

            if (!empty($row->return_certificate)) {
                $return_stores[$store]["orders"][$row->order_id] = true;
                $return_stores[$store]["units"] = ($return_stores[$store]["units"] ?? 0) + $units;
                $return_stores[$store]["sum"] = ($return_stores[$store]["sum"] ?? 0) + $sum;
                $return_orders[$row->order_id] = true;
                $return_units += $units;
                $return_sum += $sum;
                continue;
            }
            $stores[$store]["orders"][$row->order_id] = true;
            $stores[$store]["units"] = ($stores[$store]["units"] ?? 0) + $units;
            $stores[$store]["sum"] = ($stores[$store]["sum"] ?? 0) + $sum;
            $suppliers[$supplier]["units"] = ($suppliers[$supplier]["units"] ?? 0) + $units;
            $suppliers[$supplier]["sum"] = ($suppliers[$supplier]["sum"] ?? 0) + $sum;
            $total_units += $units;
            $total_sum += $sum;
            $orders[$row->order_id] = true;
            if (intval($row->done) != 1) {
                $not_approved[$row->order_id] = true;
            }
        }
        $by_sum = function ($a, $b) { return $b["sum"] <=> $a["sum"]; };
        uasort($stores, $by_sum);
        uasort($suppliers, $by_sum);
        uasort($return_stores, $by_sum);

        $intro = array();
        if (count($orders) > 0) {
            $intro[] = '<strong>' . count($orders) . '</strong> הזמנות ב-<strong>' . count($stores) . '</strong> חנויות, '
                . '<strong>' . number_format($total_units) . '</strong> בקבוקים, <strong>' . number_format($total_sum, 2) . ' ₪</strong>.';
        }
        if (count($return_orders) > 0) {
            $intro[] = '<span style="color:#c0392b;"><strong>' . count($return_orders) . '</strong> תעודות החזרה: '
                . number_format($return_units) . ' בקבוקים, ' . number_format($return_sum, 2) . ' ₪.</span>';
        }
        if (count($not_approved) > 0) {
            $intro[] = '<span style="color:#c0392b;">' . count($not_approved) . ' מההזמנות עדיין לא אושרו.</span>';
        }
        $content = '<div style="font-size:15px; line-height:1.7; color:#444444; margin-bottom:20px;">ההזמנות שהקמת ' . esc_html($range_text) . ':<br>'
            . implode('<br>', $intro) . '</div>';

        if (count($orders) > 0) {
            $store_rows = array();
            foreach ($stores as $name => $s) {
                $store_rows[] = array($name, count($s["orders"]), $s["units"], $s["sum"]);
            }
            $content .= agent_summary_table("הזמנות לפי חנות", array("חנות", "הזמנות", "בקבוקים", "סה\"כ"), $store_rows,
                array("סה\"כ", count($orders), $total_units, $total_sum));

            $supplier_rows = array();
            foreach ($suppliers as $name => $s) {
                $supplier_rows[] = array($name, null, $s["units"], $s["sum"]);
            }
            $content .= agent_summary_table("הזמנות לפי יקב", array("יקב", null, "בקבוקים", "סה\"כ"), $supplier_rows,
                array("סה\"כ", null, $total_units, $total_sum));
        }

        if (count($return_orders) > 0) {
            $return_rows = array();
            foreach ($return_stores as $name => $s) {
                $return_rows[] = array($name, count($s["orders"]), $s["units"], $s["sum"]);
            }
            $content .= agent_summary_table("תעודות החזרה", array("חנות", "תעודות", "בקבוקים", "סה\"כ"), $return_rows,
                array("סה\"כ", count($return_orders), $return_units, $return_sum), "#c0392b");
        }

        $content .= '<div style="font-size:13px; line-height:1.7; color:#8A8A8A; margin:8px 0 28px 0;">הסכומים לפני הנחה כללית על ההזמנה. בקבוקי בונוס ומבצע נספרים בכמות.</div>';

        $body = build_email_layout("סיכום הזמנות יומי", "שלום " . esc_html($agent->name) . ",", $content);
        send_mail($email, "סיכום הזמנות - " . date('d/m/y'), $body);
    }
}

//טבלה מעוצבת למייל (inline style): כותרת, שורות ושורת סה"כ. עמודה שהכותרת שלה null - לא מוצגת.
//$color - צבע הכותרת (תעודות החזרה באדום), ושורת הסה"כ בגוון בהיר שלו
function agent_summary_table($title, $headers, $rows, $total_row, $color = "#1A7870")
{
    $total_bg = $color == "#1A7870" ? "#E8F2F1" : "#FBEAEA";
    $cell = 'padding:8px 12px; border-bottom:1px solid #EFE6D2;';
    $html = '<div style="font-size:16px; font-weight:700; color:' . $color . '; margin:18px 0 8px 0;">' . esc_html($title) . '</div>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; color:#1A1A1A; text-align:right; border:1px solid #EFE6D2; border-radius:8px; border-collapse:separate; margin-bottom:8px;">'
        . '<tr style="background-color:#FAF7F0;">';
    foreach ($headers as $i => $h) {
        if ($h === null) continue;
        $html .= '<th style="' . $cell . ($i > 0 ? ' text-align:center;' : '') . '">' . esc_html($h) . '</th>';
    }
    $html .= '</tr>';
    $format = function ($i, $v) {
        if ($i == 0) return esc_html($v);
        if ($i == 3) return number_format(floatval($v), 2) . ' ₪';
        return number_format(intval($v));
    };
    foreach ($rows as $n => $row) {
        $html .= '<tr' . ($n % 2 ? ' style="background-color:#FCFBF8;"' : '') . '>';
        foreach ($row as $i => $v) {
            if ($headers[$i] === null) continue;
            $html .= '<td style="' . $cell . ($i > 0 ? ' text-align:center; white-space:nowrap;' : '') . '">' . $format($i, $v) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '<tr style="background-color:' . $total_bg . '; font-weight:700;">';
    foreach ($total_row as $i => $v) {
        if ($headers[$i] === null) continue;
        $html .= '<td style="' . $cell . ($i > 0 ? ' text-align:center; white-space:nowrap;' : '') . '">' . $format($i, $v) . '</td>';
    }
    return $html . '</tr></table>';
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

//מייל משימות בוקר - מייל אחד לכל סוכן, ובגוף המייל שתי טבלאות:
//1. תזכורות - משימות פתוחות שתאריך התזכורת שלהן הגיע (או עבר - למשל שישי ושבת, שבהם לא שולחים) ועוד לא נשלחה עליהן תזכורת.
//   אחרי שליחה - reminder_sent = 1 (שינוי תאריך התזכורת מאפס אותו - save_single_data)
//2. תאריך יעד - משימות פתוחות שתאריך היעד שלהן היום או שעבר, חוץ ממשימות שיש להן תזכורת מאוחרת יותר (יופיעו כשתגיע התזכורת)
//   ומשימות שכבר מופיעות בטבלת התזכורות.
//תזכורת למשימה בלי סוכן (או סוכן בלי מייל תקין) - למנהל, רק טבלת התזכורות. סוכן בלי משימות בשתי הטבלאות - לא מקבל מייל
function send_agents_tasks_mail()
{
    global $wpdb;
    $t = $wpdb->prefix . "tasks";
    $reminders = get_data_table("tasks", array(
        array("filter_field" => "status_id", "filter_type" => "!=", "filter_value" => "1"),
        array("filter_type" => "filter", "filter_field" => "",
            "filter_value" => "({$t}.sending_reminder <= CURDATE() AND {$t}.reminder_sent = 0)"),
    ));
    $due = get_data_table("tasks", array(
        array("filter_field" => "status_id", "filter_type" => "!=", "filter_value" => "1"),
        array("filter_type" => "filter", "filter_field" => "",
            "filter_value" => "(DATE({$t}.target_date) <= CURDATE() AND ({$t}.sending_reminder IS NULL OR {$t}.sending_reminder <= CURDATE()))"),
    ));

    $agents = array();
    $get_agent = function ($agent_id) use (&$agents) {
        $agent_id = intval($agent_id);
        if (!$agent_id) {
            return null;
        }
        if (!array_key_exists($agent_id, $agents)) {
            $agents[$agent_id] = get_data_table("agents", array(array("filter_field" => "id", "filter_value" => $agent_id)))[0] ?? null;
        }
        return $agents[$agent_id];
    };

    //קיבוץ לפי נמען
    $groups = array();
    $reminder_ids = array();
    foreach ((array)$reminders as $task) {
        $agent = $get_agent($task->agent_id);
        $email = $agent ? get_agent_email($agent) : "";
        $name = $email ? $agent->name : "";
        if (!$email) {
            $email = get_option('admin_email');
        }
        $groups[$email]["name"] = $name;
        $groups[$email]["reminders"][] = $task;
        $reminder_ids[intval($task->id)] = true;
    }
    foreach ((array)$due as $task) {
        if (isset($reminder_ids[intval($task->id)])) {
            continue;
        }
        $agent = $get_agent($task->agent_id);
        $email = $agent ? get_agent_email($agent) : "";
        if (!$email) {
            continue;//משימות בלי סוכן מופיעות בסיכום למנהל (send_unclosed_tasks)
        }
        $groups[$email]["name"] = $agent->name;
        $groups[$email]["due"][] = $task;
    }

    foreach ($groups as $email => $group) {
        $content = "";
        if (!empty($group["reminders"])) {
            $title = $group["name"] === "" ? "תזכורות למשימות שאין להן סוכן עם מייל תקין" : "תזכורות להיום";
            $content .= tasks_mail_table($title, $group["reminders"]);
        }
        if (!empty($group["due"])) {
            $content .= tasks_mail_table("תאריך היעד היום או שכבר עבר", $group["due"], "#c0392b");
        }
        $content .= '<div style="font-size:13px; line-height:1.7; color:#8A8A8A; margin:8px 0 28px 0;">אחרי ביצוע משימה יש לעדכן את המצב שלה במערכת.</div>';
        $body = build_email_layout("המשימות שלך להיום", "שלום" . ($group["name"] !== "" ? " " . esc_html($group["name"]) : "") . ",", $content);
        if (send_mail($email, "משימות להיום - " . date('d/m/y'), $body) && !empty($group["reminders"])) {
            $ids = array_map(function ($task) { return intval($task->id); }, $group["reminders"]);
            $wpdb->query("UPDATE {$t} SET reminder_sent = 1 WHERE id IN (" . implode(',', $ids) . ")");
        }
    }
}

//טבלת משימות למייל (inline style): נושא, לקוח, פירוט, תאריך יעד וכפתור למעבר למשימה במערכת
function tasks_mail_table($title, $tasks, $color = "#1A7870")
{
    $cell = 'padding:8px 10px; border-bottom:1px solid #EFE6D2;';
    $html = '<div style="font-size:16px; font-weight:700; color:' . $color . '; margin:18px 0 8px 0;">' . esc_html($title)
        . ' <span style="font-size:13px; font-weight:400; color:#8A8A8A;">(' . (count($tasks) == 1 ? "משימה אחת" : count($tasks) . " משימות") . ')</span></div>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; color:#1A1A1A; text-align:right; border:1px solid #EFE6D2; border-radius:8px; border-collapse:separate; margin-bottom:8px;">'
        . '<tr style="background-color:#FAF7F0;"><th style="' . $cell . '">נושא</th><th style="' . $cell . '">לקוח</th><th style="' . $cell . '">פירוט</th>'
        . '<th style="' . $cell . ' text-align:center;">תאריך יעד</th><th style="' . $cell . '"></th></tr>';
    foreach ($tasks as $n => $task) {
        $url = get_site_url() . '/single?subject=tasks&action=edit&id=' . intval($task->id);
        $html .= '<tr' . ($n % 2 ? ' style="background-color:#FCFBF8;"' : '') . '>'
            . '<td style="' . $cell . ' font-weight:700;">' . esc_html($task->subject_text) . '</td>'
            . '<td style="' . $cell . '">' . esc_html($task->client_name) . '</td>'
            . '<td style="' . $cell . '">' . nl2br(esc_html($task->details)) . '</td>'
            . '<td style="' . $cell . ' text-align:center; white-space:nowrap;">' . (!empty($task->target_date) ? date('d/m/Y', strtotime($task->target_date)) : '') . '</td>'
            . '<td style="' . $cell . ' text-align:center; white-space:nowrap;"><a href="' . esc_url($url) . '" style="display:inline-block; padding:5px 12px; background-color:' . $color . '; color:#ffffff; border-radius:6px; text-decoration:none; font-size:13px;">למשימה</a></td>'
            . '</tr>';
    }
    return $html . '</table>';
}

//למנהל - סיכום של המשימות שתאריך היעד שלהן עבר (אתמול ולפני), לפי סוכן. הסוכנים מקבלים את המשימות שלהם בגוף המייל - send_agents_tasks_mail
//במוצאי שבת נכללות גם המשימות של שישי - הדוח כולל את כל המשימות הפתוחות שהתאריך שלהן עבר
function send_unclosed_tasks($type)
{
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