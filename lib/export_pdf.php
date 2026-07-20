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

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'default_font' => 'Heebo',//'dejavusans'
        'margin_top' => 30
    ]);

    $mpdf->SetDirectionality('rtl');
    test_mode_table_prefix ();
    $html='<style>
                body {
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
                .section{
                    /*border:1px solid #000;*/
                    margin-bottom:10px;
                    width: 8cm;
                }
                .section .details{
                    padding: 10px;
                }
                .strong{
                    font-weight:bold;
                }
                
                .title{
                    width:8cm; 
                    /*background-color: black;
                    color: white;*/
                    font-size:12pt;
                    font-weight:bold;
                }
                </style>
                ';

    $mpdf->SetHTMLHeader('<div style="margin:0;padding:0;background-color: black;width: 100%; text-align: center; margin-bottom: 10px"><img src="https://kosherboutique.co.il/wp-content/themes/boutique/assets/images/logo_header.png"/></div>');

    switch ($attr["export"]) {
        case "single":
            //$mpdf->SetHTMLHeader('<div>'.BOUTIQUE_TABLES[$table_name]["single"].'</div>');
            if(isset($attr["packet"])){
                $packet = $attr["packet"];
            }

            if($table_name=="orders") {
                $packet = ["client", "order", "order_products"];
            }

            foreach ($packet as $func) {
                $func_name = "drow_html_" . $func;
                //write_log("func_name ".$func_name);
                $html .= $func_name($attr);
            }

                //write_log("r ".json_encode($client));
                /*$branch = "";
                if(!empty($result->branch)){
                    $branch = "סניף";
                }*/
                //$html .= "<div style='text-align: left;'><strong>סה''כ לתשלום: </strong>{$result->total} ₪</div>";

            break;
        case 'archive':
            $filters = array();
            $mpdf->SetHTMLHeader('<div>'.BOUTIQUE_TABLES[$table_name]["title"].'</div>');
            if(isset($_GET["ids"])){
                $filters[]=array("filter_field"=>"id","filter_value"=>$_GET["ids"],"filter_type" => "array");
            }
            else if(isset($_GET["id"])){
                $filters[]=array("filter_field"=>"order_id","filter_value"=>$_GET["id"]);
            }

            $html .= draw_table_pdf($table_name,$filters);


            break;
    }


    //$html = "akuo kfuko!!";
    //echo mb_detect_encoding($html);
    $mpdf->WriteHTML($html);
    //write_log("html ". $html);

    if(isset($attr["send_mail"])) {
        if(isset($attr["create_only_fill"]) && !preg_match('/<tbody[^>]*>.*?<tr\b/is', $html)){
            return null;
        }
        $file = 'report_' . time() . '.pdf';
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

function drow_html_order($attr){
    $result = get_data_table("orders",array(array("filter_field" => "id", "filter_value"=>$attr["order_id"])))[0];


    $html="<div class='section'  >
              <div class='title'>הזמנה מס. {$result->id}</div><br>
                <div class='details'>
                            <strong>תאריך הזמנה: </strong><span>".date('d/m/Y בשעה H:i',strtotime ($result->order_date))."</span><br>
                            <strong>סוכן: </strong><span>".get_userdata($result->user_opens)->display_name."</span><br>
                            <strong>הערות: </strong><span>{$result->notes}</span>
                </div>
              </div>";
    return $html;

}

function drow_html_client($attr){
    $client = get_data_table("clients",array(array("filter_field" => "id", "filter_value"=>$attr["client_id"])))[0];
    $html="<div class='section'>
<div class='details'>
                            <div class='title'>פרטי לקוח</div><br>
                            <strong>שם הלקוח: </strong><span>".$client->name."</span><br>
                            <strong>כתובת: </strong><span>".$client->address."</span><br>
                            <strong>נייד: </strong><span>".$client->mobile."</span><br>
                            <strong>דוא''ל: </strong><span>".$client->email."</span>
                            </div>                   
                        </div>";
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
        $filters[] = array("filter_field" => "payment_until", "filter_type" => "date", "filter_ratio" => "=", "filter_value" => "CURDATE()");

    }

    if ($attr["type"] == "weekly") {
        $filters[] = array("filter_field" => "payment_until", "filter_type" => "date", "filter_ratio" => "<", "filter_value" => "CURDATE()");
    }
    $html = draw_table_pdf("collection", $filters);
    return $html;
}
?>