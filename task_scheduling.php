<?php
if(!isset($argv) || count($argv)==0)return;
require_once(__DIR__ . '/../../../wp-load.php');
//write_log ($argv[1]);
write_log("send_who_needs_pay_today ".date('d/m/Y H:i'));
//לבדוק אם היום שבת שלא ישלח ובמוצ"ש לקחת את הנתונים של יום קודם

function send_who_needs_pay_today()
{
    $argv = array("", "weekly");

    $attr = ["export" => "single", "type" => $argv[1], "packet" => ["obligations"], "send_mail" => true, "create_only_fill" => true];
    $file = create_pdf($attr);
    if ($file == null) exit;

    if ($argv[1] == "daily") {
        $subject = "חשבוניות שלא שולמו היום";
    } else if ($argv[1] == "weekly") {
        $subject = "סיכום שבועי לחשבוניות שלא שולמו";
    }

    send_mail(get_option('admin_email'), $subject, "<br><br>בברכה, בוטיק כשר", [$file]);
}

?>