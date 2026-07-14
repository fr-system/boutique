<?php
//export_pdf.php?file=pdf&export=single&subject='.$table_name.'&id=' . $row->id
if(isset($_GET['export']) && isset($_GET['file']) && $_GET['file'] == "pdf") {
    create_pdf($_GET["subject"], fixXSS($_GET['export']),$_GET['id']);
}

function create_pdf($table_name,$export,$id,$print = true,$filters = array())
{
    require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php');

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'default_font' => 'dejavusans'
    ]);

    $mpdf->SetDirectionality('rtl');
    test_mode_table_prefix ();
    $html='<style>
                body {
                    direction: rtl;
                    text-align: right;
                    font-family: dejavusans;
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
                    border:1px solid #000;
                    padding:10px;
                    margin-bottom:10px;
                }
                .strong{
                    font-weight:bold;
                }
                
                .title{
                    font-size:16pt;
                    font-weight:bold;
                    text-align:center;
                    margin-bottom:15px;
                }
                </style>
                <div  style="text-align: center;"><img src="https://kosherboutique.co.il/wp-content/themes/boutique/assets/images/logo_header.png"/>
                </div>';

    switch ($export) {
        case "single":
            //$mpdf->SetHTMLHeader('<img  src="https://kosherboutique.co.il/wp-content/themes/boutique/assets/images/logo_header.png">');
            //$mpdf->SetHTMLHeader('<div>'.BOUTIQUE_TABLES[$table_name]["single"].'</div>');
            $result = get_data_table($table_name,array(array("filter_field" => "id", "filter_value"=>$id)))[0];

            if($table_name=="orders"){
                $client = get_data_table("clients",array(array("filter_field" => "id", "filter_value"=>$result->client_id)))[0];
                //write_log("r ".json_encode($client));
                /*$branch = "";
                if(!empty($result->branch)){
                    $branch = "סניף";
                }*/

                $html.="<div class='section'>
                            <div class='title'>פרטי הלקוח</div><br>
                            <strong>שם הלקוח: </strong><span>".$client->name."</span><br>
                            <strong>כתובת: </strong><span>".$client->address."</span><br>
                            <strong>נייד: </strong><span>".$client->mobile."</span><br>
                            <strong>דוא''ל: </strong><span>".$client->email."</span>   <br>                         
                        </div>";

                $html.="<br><div class='section' >
                            <div class='title'>הזמנה מס. {$result->id}</div><br>
                            <strong>תאריך הזמנה: </strong><span>".date('d/m/Y',strtotime ($result->order_date))."</span><br>
                            <strong>סוכן: </strong><span>{$result->user_opens}</span><br>
                            <strong>הערות: </strong><span>{$result->notes}</span><br>
                        </div>";

                $html .= draw_table_pdf("order_products",$filters);
                $html .= "<div style='text-align: left;'><strong>סה''כ לתשלום: </strong>{$result->total}</div>";
            }
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
    //write_log("html ". $html);
    //echo mb_detect_encoding($html);
    $mpdf->WriteHTML($html);
    if($print) {
        $mpdf->Output();
    }
    else {
        $file = 'report_' . time() . '.pdf';
        $mpdf->Output($file, \Mpdf\Output\Destination::FILE);
        return $file;
    }
}

function draw_table_pdf($table_name, $filters)
{
    $packet = get_data_to_export($table_name,"pdf",$filters);
    $headers = $packet["headers"];
    $data = $packet["data"];

    $html='<table style="width: 100%">
                <thead><tr>';

    foreach($headers as $key=>$header) {
        $html .= '<th>' . $key . '</th>';
    }
    $html.='</tr></thead><tbody>';
    foreach($data as $row){
        $html .= '<tr>';
        foreach($row as $td) {
            $html .= '<td>' . $td . '</td>';
        }
        $html .= '</tr>';
    }
    $html.="</tbody></table>";

    return $html;
}
?>