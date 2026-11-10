<?php
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php');

//חבילות שמציירות כותרת מסמך משלהן - בשאר המסמכים create_pdf מוסיף כותרת כללית.
//מוגדר לפני הקריאה ל-create_pdf למטה (גישה ישירה לקובץ)
const PDF_OWN_TITLE_PACKETS = array("billing", "orders_today");

//export_pdf.php?file=pdf&export=single&subject='.$table_name.'&id=' . $row->id
if(isset($_GET['export']) && isset($_GET['file']) && $_GET['file'] == "pdf") {
    //write_log("!!!!");

    create_pdf($_GET);
}

function create_pdf($attr)
{
    $table_name = $attr["subject"] ?? "";

    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];
    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'fontDir' => array_merge($fontDirs, [
            __DIR__ . '/fonts',
        ]),
        'fontdata' => $fontData + [
                'noto-sans' => [
                    'R' => 'NotoSansHebrew-Regular.ttf',
                ],
                //פונט קבצי ה-PDF - כמו באתר. עם קובץ מודגש אמיתי (בלי B, mPDF מדגיש באופן מלאכותי)
                'assistant' => [
                    'R' => 'Assistant-Regular.ttf',
                    'B' => 'Assistant-Bold.ttf',
                ],
            ],
        'default_font_size' => 10.5,
        'default_font' => 'assistant',
    //    'default_font' => 'Noto-Sans',//'dejavusans',//'Heebo'
        //מקום לפס השחור למעלה ולשורת העמודים למטה
        'margin_top'    => 22,
        'margin_bottom' => 18,
        'margin_left'   => 0,
        'margin_right'  => 0,

        'margin_header' => 0,
        'margin_footer' => 4
    ]);

    $mpdf->setAutoTopMargin = false;

    $mpdf->SetDirectionality('rtl');

    $html = pdf_style() . '<div class="doc-body">';

    $packet = array();
    if(isset($attr["packet"])){
        $packet = $attr["packet"];
    }

    $report_title = "";

    switch ($attr["export"]) {
        case "single":
            if($table_name=="orders") {
                $supplier_id = $attr["supplier_id"] ?? null;
                $report_title = $supplier_id ? "הזמנת רכש" : "הזמנה";
                $html .= pdf_order_document($attr["order_id"], $supplier_id);
            }
            else{
                $report_title = $attr["report_title"] ?? "";
                if (!array_intersect($packet, PDF_OWN_TITLE_PACKETS)) {
                    $html .= pdf_title_row($report_title, "", array("תאריך" => pdf_now('d/m/Y')));
                }
                foreach ($packet as $func) {
                    $func_name = "drow_html_" . $func;
                    //write_log("func_name ".$func_name);
                    $html .= $func_name($attr);
                }
            }

            break;
        case 'archive':
            $filters = array();
            if($table_name){
                $report_title = BOUTIQUE_TABLES[$table_name]["title"];
            }
            else if(isset($attr["report_title"])){
                $report_title = $attr["report_title"];
            }


            /*if(isset($_GET["ids"])){
                $filters[]=array("filter_field"=>"id","filter_value"=>$_GET["ids"],"filter_type" => "array");
            }
            else if(isset($_GET["id"])){
                $filters[]=array("filter_field"=>"order_id","filter_value"=>$_GET["id"]);
            }*/
            $html .= pdf_title_row($report_title, "", array("תאריך" => pdf_now('d/m/Y')));
            if ($table_name) {
                $html .= draw_table_pdf($table_name,$filters);
            }
            else {
                //דוח בלי טבלה (למשל משימות שלא נסגרו) - החלקים לפי packet
                foreach ($packet as $func) {
                    $func_name = "drow_html_" . $func;
                    $html .= $func_name($attr);
                }
            }
            break;
    }

    $html.="</div>";

    $mpdf->SetTitle($report_title);
    $mpdf->SetHTMLHeader(pdf_header());
    $mpdf->SetHTMLFooter(pdf_footer());
    //$html = "akuo kfuko!!";
    //echo mb_detect_encoding($html);
    $mpdf->WriteHTML($html);
    //write_log("html ". $html);

    if(isset($attr["send_mail"])) {
        if(isset($attr["create_only_fill"]) && !preg_match('/<tbody[^>]*>.*?<tr\b/is', $html)){
            return null;
        }
        //בתיקייה הזמנית - ב-cron התיקייה הנוכחית היא תיקיית הבית בשרת, וקבצים שנשארו שם הצטברו
        $file = trailingslashit(get_temp_dir()) . $table_name . '_rpt_' . uniqid() . '.pdf';
        $mpdf->Output($file, \Mpdf\Output\Destination::FILE);
        return $file;
    }
    else {
        $mpdf->Output();
        exit();
    }
}

//מחיקת קובץ דוח אחרי שליחה במייל - יש בו נתוני לקוחות
function delete_report_file($file)
{
    if (!empty($file) && file_exists($file)) {
        unlink($file);
    }
}

// ===================== עיצוב כללי לכל קבצי ה-PDF =====================
//באותם צבעים של המיילים: שחור, זהב ושמנת.
//mPDF תומך רק בחלק מה-CSS - הפריסה בנויה בטבלאות בלבד (בלי flex), והסלקטורים הם מחלקות פשוטות

function pdf_style()
{
    return '<style>
        body { direction: rtl; text-align: right; font-size: 10.5pt; color: #1A1A1A; }
        table { border-collapse: collapse; }
        .doc-body { padding: 0 28px; }

        .doc-title { font-size: 20pt; font-weight: bold; }
        .doc-subtitle { font-size: 10pt; color: #8A8A8A; }
        .meta-label { padding: 5px 12px; border: 1px solid #EFE6D2; background-color: #FAF7F0; color: #8A8A8A; font-size: 9.5pt; }
        .meta-value { padding: 5px 12px; border: 1px solid #EFE6D2; background-color: #FAF7F0; font-weight: bold; font-size: 9.5pt; }

        .info-box-title { font-size: 10pt; font-weight: bold; color: #C9A24B; border-bottom: 1px solid #EFE6D2; padding: 9px 14px 5px 14px; }
        .info-k { color: #6B6B6B; width: 34%; padding: 3px 14px 3px 0; vertical-align: top; }
        .info-v { padding: 3px 0 3px 14px; vertical-align: top; }

        .data-th { background-color: #1A1A1A; color: #FFFFFF; font-weight: bold; padding: 7px 8px; text-align: right; }
        .data-th-soft { background-color: #FAF7F0; color: #8A6A1F; font-weight: bold; font-size: 9pt; padding: 3px 8px; text-align: right; border-top: 1px solid #EFE6D2; border-bottom: 1px solid #EFE6D2; }
        .data-td { padding: 6px 8px; border-bottom: 1px solid #ECE9E2; text-align: right; vertical-align: top; }
        .data-alt { background-color: #FAF8F4; }
        .num { text-align: left; white-space: nowrap; }
        .center { text-align: center; }
        .idx { color: #8A8A8A; width: 24px; }
        .badge { font-size: 8pt; color: #8A6A1F; background-color: #F3E9D2; }

        .notes { border-right: 3px solid #C9A24B; background-color: #FAF7F0; padding: 8px 12px; line-height: 1.6; vertical-align: top; }
        .notes-k { color: #8A8A8A; font-size: 9pt; }
        .tot-k { padding: 6px 12px; border-bottom: 1px solid #ECE9E2; }
        .tot-v { padding: 6px 12px; border-bottom: 1px solid #ECE9E2; text-align: left; white-space: nowrap; }
        .grand-k { padding: 8px 12px; background-color: #000000; color: #FFFFFF; font-size: 12pt; font-weight: bold; }
        .grand-v { padding: 8px 12px; background-color: #000000; color: #E5C77F; font-size: 12pt; font-weight: bold; text-align: left; white-space: nowrap; }
    </style>';
}

//פס שחור עם לוגו וקו זהב - בראש כל עמוד
function pdf_header()
{
    $logo = get_stylesheet_directory() . '/assets/images/logo_header.png';
    return '<table width="100%" style="background-color:#000000; border-collapse:collapse;"><tr>
                <td style="padding:8px 28px; text-align:right;"><img src="' . $logo . '" height="32"/></td>
                <td style="padding:8px 28px; text-align:left; color:#C9A24B; font-size:9.5pt;">kosherboutique.co.il</td>
            </tr></table>
            <div style="height:4px; background-color:#C9A24B; font-size:1pt;">&nbsp;</div>';
}

//שורת תחתית - תאריך ההפקה ומספרי עמודים
function pdf_footer()
{
    return '<table width="100%" style="border-collapse:collapse; font-size:8pt; color:#8A8A8A;"><tr>
                <td style="padding:8px 28px; border-top:1px solid #EFE6D2; text-align:right;">בוטיק כשר · הופק ב-' . pdf_now('d/m/Y H:i') . '</td>
                <td style="padding:8px 28px; border-top:1px solid #EFE6D2; text-align:left;">עמוד {PAGENO} מתוך {nbpg}</td>
            </tr></table>';
}

//התאריך והשעה עכשיו לפי שעון ישראל (אזור הזמן של האתר מוגדר UTC)
function pdf_now($format)
{
    return (new DateTime('now', new DateTimeZone('Asia/Jerusalem')))->format($format);
}

function pdf_esc($value)
{
    return esc_html((string)$value);
}

function pdf_number($value)
{
    return floatval(str_replace(',', '', (string)$value));
}

function pdf_money($value)
{
    return "₪ " . number_format(pdf_number($value), 2);
}

//כותרת המסמך, ובצד שמאל תיבת פרטים. $meta: תווית => ערך
function pdf_title_row($title, $subtitle = "", $meta = array())
{
    $meta_html = "";
    foreach ($meta as $label => $value) {
        //ltr - כדי שתאריך ושעה לא יתהפכו
        $meta_html .= "<tr><td class='meta-label'>" . pdf_esc($label) . "</td><td class='meta-value' dir='ltr'>" . pdf_esc($value) . "</td></tr>";
    }
    return "<table width='100%' style='margin-bottom:16px;'><tr>
                <td style='vertical-align:bottom;'>
                    <div class='doc-title'>" . pdf_esc($title) . "</div>" .
                    ($subtitle !== "" ? "<div class='doc-subtitle'>" . pdf_esc($subtitle) . "</div>" : "") . "
                </td>
                <td style='vertical-align:bottom; text-align:left;'>" .
                    ($meta_html ? "<table align='left'>{$meta_html}</table>" : "") . "
                </td>
            </tr></table>";
}

//תיבת מידע. $rows: תווית => ערך (ערך ריק לא מוצג).
//בנויה כטבלה ולא div - mPDF לא מצייר מסגרת של div שבתוך תא טבלה
function pdf_info_box($title, $rows)
{
    $rows_html = "";
    foreach ($rows as $label => $value) {
        if ($value === null || $value === "") {
            continue;
        }
        $rows_html .= "<tr><td class='info-k'>" . pdf_esc($label) . "</td><td class='info-v'>" . pdf_esc($value) . "</td></tr>";
    }
    return "<table width='100%' style='border:1px solid #E6E2D8;'>
                <tr><td colspan='2' class='info-box-title'>" . pdf_esc($title) . "</td></tr>
                <tr><td colspan='2' style='height:4px;'></td></tr>
                {$rows_html}
                <tr><td colspan='2' style='height:6px;'></td></tr>
            </table>";
}

//שתי תיבות מידע זו לצד זו. 48% + 4% + 48% - כך המרווח ביניהן לא נבלע
function pdf_info_boxes($right_box, $left_box)
{
    return "<table width='100%' style='margin-bottom:16px;'><tr>
                <td style='width:48%; vertical-align:top;'>{$right_box}</td>
                <td style='width:4%;'></td>
                <td style='width:48%; vertical-align:top;'>{$left_box}</td>
            </tr></table>";
}

//טבלת נתונים עם כותרת כהה ושורות לסירוגין. $rows - שורות של HTML מוכן, $classes - מחלקה לכל עמודה (num / center / idx)
//$th_class: מחלקת שורת הכותרות - data-th (שחורה) כברירת מחדל, data-th-soft לכותרת בהירה ונמוכה (טבלאות המשימות)
function pdf_data_table($headers, $rows, $classes = array(), $th_class = "data-th")
{
    $html = "<table width='100%' style='border-collapse:collapse;'><thead><tr>";
    foreach ($headers as $i => $header) {
        $html .= "<th class='" . $th_class . " " . ($classes[$i] ?? "") . "'>" . pdf_esc($header) . "</th>";
    }
    $html .= "</tr></thead><tbody>";
    foreach ($rows as $r => $row) {
        $html .= "<tr>";
        foreach (array_values($row) as $i => $cell) {
            $html .= "<td class='data-td " . ($r % 2 ? "data-alt " : "") . ($classes[$i] ?? "") . "'>" . $cell . "</td>";
        }
        $html .= "</tr>";
    }
    return $html . "</tbody></table>";
}

//הערות מימין וסיכום משמאל. $lines: תווית => ערך, ואחריהן שורת הסה"כ בשחור
function pdf_totals($lines, $grand_label, $grand_value, $notes = "")
{
    $lines_html = "";
    foreach ($lines as $label => $value) {
        $lines_html .= "<tr><td class='tot-k'>" . pdf_esc($label) . "</td><td class='tot-v'>" . pdf_esc($value) . "</td></tr>";
    }
    $notes_html = trim((string)$notes) !== "" ? "<table width='100%'><tr><td class='notes'><div class='notes-k'>הערות</div>" . nl2br(pdf_esc($notes)) . "</td></tr></table>" : "";
    return "<table width='100%' style='margin-top:16px;'><tr>
                <td style='vertical-align:top; padding-left:20px;'>{$notes_html}</td>
                <td style='width:240px; vertical-align:top;'>
                    <table width='100%' style='border-collapse:collapse;'>{$lines_html}
                        <tr><td class='grand-k'>" . pdf_esc($grand_label) . "</td><td class='grand-v'>" . pdf_esc($grand_value) . "</td></tr>
                    </table>
                </td>
            </tr></table>";
}

function pdf_user_name($user_id)
{
    $user = $user_id ? get_userdata(intval($user_id)) : null;
    return $user ? $user->display_name : "";
}

function pdf_client_address($client)
{
    return trim(($client->address ?? "") . ", " . ($client->citie_name ?? ""), ", ");
}

// ===================== המסמכים =====================

function draw_table_pdf($table_name, $filters)
{
    $packet = get_data_to_export($table_name,"pdf",$filters);
    return pdf_data_table(array_keys($packet["headers"]), $packet["data"]);
}

//מסמך הזמנה - למנהל, לסוכן ולהדפסה. עם $supplier_id - הזמנת רכש לספק, רק עם המוצרים שלו
function pdf_order_document($order_id, $supplier_id = null)
{
    $order = get_data_table("orders",array(array("filter_field" => "id", "filter_value"=>intval($order_id))))[0] ?? null;
    if (!$order) {
        return "";
    }
    $client = get_data_table("clients",array(array("filter_field" => "id", "filter_value"=>intval($order->client_id))))[0] ?? null;

    $filters = array(array("filter_field" => "order_id", "filter_value" => intval($order_id)));
    if($supplier_id){
        $filters[] = array("filter_field" => "supplier_id", "filter_value" => intval($supplier_id));
    }
    $products = get_data_table("order_products", $filters);

    $rows = array();
    $products_total = 0;
    $bonus_count = 0;
    foreach ($products as $i => $product) {
        $is_bonus = $product->bonus == 'bonus';
        if ($is_bonus) {
            $bonus_count += pdf_number($product->count);
        }
        $products_total += pdf_number($product->total);
        $discount = pdf_number($product->discount_percent);
        $rows[] = array(
            $i + 1,
            pdf_esc($product->name) . ($is_bonus ? " <span class='badge'>&nbsp;בונוס&nbsp;</span>" : ""),
            $product->order_individual ? "בודדים" : "ארגזים",
            pdf_esc($product->count),
            pdf_money($product->order_price),
            $discount ? $discount . "%" : "—",
            pdf_money($product->total),
        );
    }
    $products_table = pdf_data_table(array("#", "שם המוצר", "יחידה", "כמות", "מחיר", "הנחה", "סה\"כ"), $rows,
        array("idx", "", "center", "center", "num", "center", "num"));

    $meta = array("מס' הזמנה" => $order->id, "תאריך" => date('d/m/Y H:i', strtotime($order->order_date)));
    $client_rows = array(
        "שם הלקוח" => $client->name ?? $order->client_name,
        "סניף" => $order->clients_branche_name ?? "",
        "כתובת" => $client ? pdf_client_address($client) : "",
        "נייד" => $client->mobile ?? "",
    );

    if ($supplier_id) {
        $supplier = get_data_table("suppliers",array(array("filter_field" => "id", "filter_value"=>intval($supplier_id))))[0] ?? null;
        $html = pdf_title_row("הזמנת רכש", $supplier ? "לכבוד: " . $supplier->name : "", $meta);
        $html .= pdf_info_boxes(
            pdf_info_box("אספקה ללקוח", $client_rows),
            pdf_info_box("סיכום לספק", array("מספר מוצרים" => count($products), "כמות בונוס" => $bonus_count))
        );
        $html .= $products_table;
        $html .= pdf_totals(array(), "סה\"כ לספק", pdf_money($products_total));
        return $html;
    }

    $client_rows["דוא\"ל"] = $client->email ?? "";
    $html = pdf_title_row("הזמנה", "", $meta);
    $html .= pdf_info_boxes(
        pdf_info_box("פרטי לקוח", $client_rows),
        pdf_info_box("פרטי הזמנה", array(
            "סוכן" => $client->agent_name ?? "",
            "מקים ההזמנה" => $order->agent_name ?: pdf_user_name($order->user_opens),
            "מאשר ההזמנה" => pdf_user_name($order->user_confirms),
            "מספר מוצרים" => count($products),
        ))
    );
    $html .= $products_table;

    $total = pdf_number($order->total);
    $for_payment = $order->for_payment !== null && $order->for_payment !== "" ? pdf_number($order->for_payment) : $total;
    $lines = array("סכום" => pdf_money($total));
    if (pdf_number($order->discount)) {
        $lines["הנחה (" . pdf_number($order->discount) . "%)"] = "− " . pdf_money($total - $for_payment);
    }
    $html .= pdf_totals($lines, "לתשלום", pdf_money($for_payment), $order->notes);
    return $html;
}

//כל ההזמנות מהיום, הזמנה בכל עמוד
function drow_html_orders_today($attr){

    $filters = array();
   /* if(date('H')<23) {
        $filters[] = array("filter_field" => "order_date", "filter_value" => "order_date >= CONCAT(CURDATE(), ' 00:00:00')", "filter_type" => "filter");
    }
    else{
        $filters[] = array("filter_field" => "order_date", "filter_value" => "order_date >= CONCAT(CURDATE(), ' 18:00:00')", "filter_type" => "filter");
    }*/
    //כל דוח - מהדוח הקודם עד שעת הדוח, בלי כפילויות: 13:00 מאתמול 23:00, 18:00 מ-13:00, 23:00 מ-18:00.
    //במוצאי שבת - מחמישי ב-23:00 (בשישי ובשבת עד 20:00 הדוחות לא נשלחים)
    $time = intval($attr['time']);
    $report_hours = array(13, 18, 23);
    $prev_hours = array_values(array_filter($report_hours, function ($h) use ($time) { return $h < $time; }));
    if (date('w') == 6) {
        $from = "CURDATE() - INTERVAL 1 DAY - INTERVAL 1 HOUR";
    } else if ($prev_hours) {
        $from = "CURDATE() + INTERVAL " . end($prev_hours) . " HOUR";
    } else {
        $from = "CURDATE() - INTERVAL " . (24 - end($report_hours)) . " HOUR";
    }
    $filters[] = array("filter_field" => "order_date", "filter_value" => "order_date > {$from} And order_date <= CURDATE() + INTERVAL {$time} HOUR", "filter_type" => "filter");
    //approved: true - רק הזמנות שאושרו, false - רק שלא אושרו, בלי - הכול
    if (isset($attr["approved"])) {
        global $wpdb;
        $filters[] = array("filter_field" => "done", "filter_type" => "filter", "filter_value" => $attr["approved"]
            ? "{$wpdb->prefix}orders.done = 1"
            : "({$wpdb->prefix}orders.done IS NULL OR {$wpdb->prefix}orders.done != 1)");
    }

    $orders = get_data_table("orders",$filters);
    $html="";
    foreach ($orders as $i=>$order){
        if($i>0){
            $html.="<pagebreak />";
        }
        $html .= pdf_order_document($order->id);
    }
    return $html;
}

//דוח חיוב ללקוח - חשבוניות חיוב (בלי זיכויים) שלא שולמו ומועד התשלום שלהן עבר
function drow_html_billing($attr)
{
    $client = get_data_table("clients",array(array("filter_field" => "id", "filter_value"=>intval($attr["client_id"]))))[0] ?? null;
    if (!$client) {
        return "";
    }
    $filters = array();
    $filters[]=array("filter_field" => "client_id", "filter_value" => intval($attr["client_id"]));
    $filters[]=array("filter_field" => "payment_date", "filter_type" => "null");
    $filters[]=array("filter_field" => "payment_until", "filter_type" => "date", "filter_ratio" => "<","filter_value"=>"NOW()");
    $filters[]=array("filter_field" => "doc_type", "filter_value" => "1");

    $invoices = get_data_table("collection", $filters);
    $total = 0;
    foreach ($invoices as $invoice) {
        $total += pdf_number($invoice->obligation);
    }

    $html = pdf_title_row("דוח חיוב", "חשבוניות שמועד התשלום שלהן עבר", array("תאריך" => pdf_now('d/m/Y')));
    $html .= pdf_info_boxes(
        pdf_info_box("פרטי הלקוח", array(
            "שם הלקוח" => $client->name,
            "ח\"פ" => $client->BnNumber ?? "",
            "כתובת" => pdf_client_address($client),
            "נייד" => $client->mobile ?? "",
            "דוא\"ל" => $client->email ?? "",
        )),
        pdf_info_box("סיכום", array(
            "מספר חשבוניות" => count($invoices),
            "סה\"כ לתשלום" => pdf_money($total),
        ))
    );
    $html .= draw_table_pdf("collection", $filters);
    $html .= pdf_totals(array(), "סה\"כ לתשלום", pdf_money($total));
    return $html;
}

function drow_html_obligations($attr)
{
    $filters = array();
    $filters[] = array("filter_field" => "payment_date", "filter_type" => "null");
    $filters[] = array("filter_field" => "doc_type", "filter_value" => "1");
    if ($attr["type"] == "daily") {
        if(date('w') == 6){
            $filters[] = array("filter_field" => "payment_until", "filter_type" => "date", "filter_ratio" => ">=", "filter_value" => "CURDATE() - INTERVAL 1 DAY");

        }else {
            $filters[] = array("filter_field" => "payment_until", "filter_type" => "date", "filter_ratio" => "=", "filter_value" => "CURDATE()");
        }
    }

    if ($attr["type"] == "weekly") {
        $filters[] = array("filter_field" => "payment_until", "filter_type" => "date", "filter_ratio" => "<", "filter_value" => "CURDATE()");
    }
    $html = draw_table_pdf("collection", $filters);
    return $html;
}

function drow_html_tasks($attr)
{
    $filters = array();
    if(($attr["agent_id"]??null)!= null){
        $filters[]=array("filter_field" => "agent_id", "filter_table" => "tasks", "filter_value" => $attr["agent_id"]);
    }

    $filters[]=array("filter_field" => "status_id", "filter_type" => "!=", "filter_value" => "1");
    $filters[]=array("filter_field" => "target_date", "filter_type" => "date", "filter_ratio" => (!empty($attr["include_today"]) ? "<=" : "<"),"filter_value"=>"CURDATE()");//דוח לסוכן - כולל היום; סיכום למנהל - רק מה שעבר
    $tasks = get_data_table("tasks",$filters);
    //משימות בלי סוכן: agent_id ריק (הסוכן נמחק) או סוכן שכבר לא קיים
    if (!empty($attr["no_agent"])) {
        global $wpdb;
        $agent_ids = array_map('intval', $wpdb->get_col("SELECT id FROM {$wpdb->prefix}agents"));
        $tasks = array_filter($tasks, function ($task) use ($agent_ids) {
            return empty($task->agent_id) || !in_array(intval($task->agent_id), $agent_ids);
        });
    }
    $rows = array();
    foreach ($tasks as $task) {
        $rows[] = array(
            "<strong>" . pdf_esc($task->subject_text) . "</strong>",
            pdf_esc($task->client_name),
            nl2br(pdf_esc($task->details)),
            date('d/m/Y', strtotime($task->target_date)),
        );
    }
/*    <strong>חשיבות: </strong><span>{$task->importance}</span><br>*/
    return pdf_data_table(array("נושא", "לקוח", "פירוט", "תאריך יעד"), $rows, array("", "", "", "center"), "data-th-soft");
}

//סיכום למנהל - טבלת משימות נפרדת לכל סוכן, עם שם הסוכן ומספר המשימות
function drow_html_tasks_by_agent($attr)
{
    global $wpdb;
    $agents = run_query("SELECT a.id, a.name, COUNT(*) AS tasks_count FROM {$wpdb->prefix}tasks t
                         LEFT JOIN {$wpdb->prefix}agents a ON a.id = t.agent_id
                         WHERE t.status_id != 1 AND t.target_date < CURDATE()
                         GROUP BY a.id ORDER BY a.name");
    $html = "";
    foreach ($agents as $agent) {
        //משימות בלי סוכן (הסוכן נמחק) - קבוצה משלהן, כדי שלא ייפלו בין הכיסאות (מופיעה ראשונה, NULL ממוין ראשון)
        if (empty($agent->id)) {
            $html .= "<div style='font-size:12pt; font-weight:bold; margin:14px 0 6px 0; color:#c0392b;'>בלי סוכן" .
                " <span style='color:#8A8A8A; font-weight:normal; font-size:10pt;'>(" . ($agent->tasks_count == 1 ? "משימה אחת" : intval($agent->tasks_count) . " משימות") . " - יש לשייך לסוכן)</span></div>";
            $html .= drow_html_tasks(array("no_agent" => true));
            continue;
        }
        $html .= "<div style='font-size:12pt; font-weight:bold; margin:14px 0 6px 0;'>" . pdf_esc($agent->name) .
            " <span style='color:#8A8A8A; font-weight:normal; font-size:10pt;'>(" . ($agent->tasks_count == 1 ? "משימה אחת" : intval($agent->tasks_count) . " משימות") . ")</span></div>";
        $html .= drow_html_tasks(array("agent_id" => $agent->id));
    }
    return $html;
}
?>
