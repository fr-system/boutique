<?php
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php');

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
            ],
        'default_font_size' => 10,
        'default_font' => 'noto-sans',
    //    'default_font' => 'Noto-Sans',//'dejavusans',//'Heebo'
        'margin_top'    => 30,
        'margin_bottom' => 5,
        'margin_left'   => 0,
        'margin_right'  => 0,

        'margin_header' => 0,
        'margin_footer' => 0
    ]);

    $mpdf->setAutoTopMargin = false;

    $mpdf->SetDirectionality('rtl');

    $html='<style>
                body {
                    letter-spacing: 1px;
                    direction: rtl;
                    text-align: right;
                    font-size:12pt;
                    /*font-family: dejavusans;*/
                }
                
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                
                table, th, td {                
                    border: 1px solid #000;
                    border-collapse:collapse;
                }
                
                th, td {
                    padding: 5px;
                    text-align: right;
                }
                .details{
                    padding: 10px;
                    margin-bottom:10px;

                }
                .strong{
                    font-weight:bold;
                }
                
                .title{
                    text-decoration: underline;
                    font-size:14pt;
                }
                .report-title{
                    font-size:20pt;
                    color: white;
                    margin: auto 0;
                    padding-right: 40px
                }
                </style>
                <div style="padding: 30px;">';


    $packet = array();
    if(isset($attr["packet"])){
        $packet = $attr["packet"];
    }

    $report_title = "";

    switch ($attr["export"]) {
        case "single":
            if($table_name=="orders") {
                if(isset($attr["supplier_id"])){
                    $packet = ["client", "sup_order", "order_products"];
                    $report_title = "פרטי הזמנת הלקוח";
                }
                else {
                    $packet = ["client", "order", "order_products"];
                    $report_title = "פרטי הזמנה";
                }
            }
            else{
                $report_title = $attr["report_title"];
            }

            foreach ($packet as $func) {
                $func_name = "drow_html_" . $func;
                //write_log("func_name ".$func_name);
                $html .= $func_name($attr);
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
            $html .= draw_table_pdf($table_name,$filters);
            break;
    }

    $html.="</div>";
    $header = '<table style="width: 100%; text-align: right; background-color: black;">
                <tr>
                   <td class="report-title"><strong>'.$report_title.'</strong> </td>
                   <td style="text-align: left;"><img  src="https://kosherboutique.co.il/wp-content/themes/boutique/assets/images/logo_header.png"/></td>
              </tr>  </table>';

    $mpdf->SetHTMLHeader($header);
    //$html = "akuo kfuko!!";
    //echo mb_detect_encoding($html);
    $mpdf->WriteHTML($html);
    //write_log("html ". $html);

    if(isset($attr["send_mail"])) {
        if(isset($attr["create_only_fill"]) && !preg_match('/<tbody[^>]*>.*?<tr\b/is', $html)){
            return null;
        }
        $file = $table_name.'_rpt_' . time() . '.pdf';
        $mpdf->Output($file, \Mpdf\Output\Destination::FILE);
        return $file;
    }
    else {
        $mpdf->Output();
        exit();
    }
}

function draw_table_pdf($table_name, $filters)
{
    $packet = get_data_to_export($table_name,"pdf",$filters);
    $headers = $packet["headers"];
    $data = $packet["data"];

    $html='<div style="clear:both;"></div><table style="table-layout: fixed; width: 100%">
                <thead><tr>';

    foreach($headers as $key=>$header) {
        $width = "100px";
        if($key == "שם המוצר"){
            $width = "150px";
        }
        $html .= "<th style='width: {$width}'>{$key}</th>";
    }
    $html.='</tr></thead><tbody>';
    foreach($data as $row){
        $html .= "<tr>";
        foreach($row as $td) {
            $html .= "<td >{$td}</td>";
        }
        $html .= '</tr>';
    }
    $html.="</tbody></table>";

    return $html;
}
function drow_html_orders_today($attr){

    $filters = array();
    if(date('H')<23) {
        $filters[] = array("filter_field" => "order_date", "filter_value" => "order_date >= CONCAT(CURDATE(), ' 00:00:00')", "filter_type" => "filter");
    }
    else{
        $filters[] = array("filter_field" => "order_date", "filter_value" => "order_date >= CONCAT(CURDATE(), ' 18:00:00')", "filter_type" => "filter");
    }
    $orders = get_data_table("orders",$filters);
    $html="";
    foreach ($orders as $order){
       // $html.= "<table style='width:100%;table-layout: fixed'><tbody><tr>";
        $html.= "<td>".drow_html_client(["client_id"=>$order->client_id])."</td>";
        $html.= "<td>".drow_html_order(["order_id"=>$order->id])."</td>";
        //$html.= "</tr></tbody></table>";
        $html.=drow_html_order_products(["order_id"=>$order->id]);
        $html.="<pagebreak />";
    }
    return $html;
}
function drow_html_sup_order($attr){
    $order = get_data_table("orders",array(array("filter_field" => "id", "filter_value"=>$attr["order_id"])))[0];
    global $wpdb;
    $query = "SELECT op.* FROM {$wpdb->prefix}order_products  as op            
                  JOIN {$wpdb->prefix}products as p ON p.id = op.product_id                 
                  WHERE op.order_id = " . $attr["order_id"] . " AND p.supplier_id = ". $attr["supplier_id"];
    $order_products_to_sup = run_query($query);//המוצרים בהזמנה מספק זה
    $sup_total=0;
    $total_bonus=0;
    foreach ($order_products_to_sup as $pro){
        $sup_total+=(float)($pro->total ?? 0);
        $total_bonus += $pro->bonus =='bonus' ?  $pro->count:0;
    }

    $html="<div class='details'>
          <strong class='title'>הזמנה מס. {$order->id}</strong><br>
              <strong>תאריך הזמנה: </strong><span>".date('d/m/Y בשעה H:i',strtotime ($order->order_date))."</span><br>
                        
              <strong>סה''כ לתשלום: </strong><span>₪".$sup_total."</span><br>
              <strong>כמות בונוס: </strong><span>".$total_bonus."</span><br></div>";
    //<strong>הערות: </strong><span>{$order->notes}</span>
    return $html;
}

function drow_html_order($attr){
    $result = get_data_table("orders",array(array("filter_field" => "id", "filter_value"=>$attr["order_id"])))[0];

    $html="<div class='details'>
          <strong class='title'>הזמנה מס. {$result->id}</strong><br>
              <strong>תאריך הזמנה: </strong><span>".date('d/m/Y בשעה H:i',strtotime ($result->order_date))."</span><br>
              <strong>סכום: </strong><span>₪".$result->total."</span><br>
              <strong>הנחה: </strong><span>".(!empty($result->discount) ? "% ". $result->discount:"00.00")."</span><br>
              <strong>סה''כ לתשלום: </strong><span>₪"."1000"."</span><br>
              <strong>הערות: </strong><span>{$result->notes}</span></div>";

    return $html;
}

function drow_html_client($attr){
    $client = get_data_table("clients",array(array("filter_field" => "id", "filter_value"=>$attr["client_id"])))[0];
    $html="<div class='details'>
           <strong class='title'>אספקה ללקוח</strong><br>
                <strong>שם הלקוח: </strong><span>".$client->name."</span><br>
                <strong>כתובת: </strong><span>".$client->address."</span><br>
                <strong>נייד: </strong><span>".$client->mobile."</span><br>
                <strong>דוא''ל: </strong><span>".$client->email."</span>                  
         </div>";
           //
    return $html;
}
function drow_html_order_products($attr)
{
    $filters = array(array("filter_field" => "order_id", "filter_value" => $attr["order_id"]));
    if(isset($attr["supplier_id"])){
        $filters[] = array("filter_field" => "supplier_id", "filter_value" => $attr["supplier_id"]);
    }
    $html = draw_table_pdf("order_products", $filters);
    return $html;
}

function drow_html_obligation_client($attr)
{
    $filters = array();
    $filters[]=array("filter_field" => "client_id", "filter_value" => $attr["client_id"]);
    $filters[]=array("filter_field" => "payment_date", "filter_type" => "null");
    $filters[]=array("filter_field" => "payment_until", "filter_type" => "date", "filter_ratio" => "<","filter_value"=>"NOW()");
    $html = draw_table_pdf("collection", $filters);
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
        $filters[]=array("filter_field" => "tasks.agent_id", "filter_value" => $attr["agent_id"]);
    }

    $filters[]=array("filter_field" => "status_id", "filter_type" => "!=", "filter_value" => "1");
    $filters[]=array("filter_field" => "target_date", "filter_type" => "date", "filter_ratio" => "<","filter_value"=>"CURDATE()");
    $tasks = get_data_table("tasks",$filters);
    $html = "<table><tbody>";
    foreach ($tasks as $task) {
        //write_log("task ".json_encode($task));

        $html .= "<tr><td><div class='details'>
                <strong class='title'>".$task->subject_text."</strong><br>                
                <strong>שם הלקוח: </strong><span>".$task->client_name."</span><br>
                <strong>פירוט: </strong><span>".$task->details."</span><br>               
                <strong>תאריך יעד: </strong><span>".date('d/m/Y', strtotime($task->target_date))."</span>                                  
           </div></td></tr>";
    }
    $html .= "</tbody></table>";
/*    <strong>חשיבות: </strong><span>{$task->importance}</span><br>*/
    //$html = draw_table_pdf("tasks", $filters);
    write_log("html ".$html);
    return $html;
}
?>