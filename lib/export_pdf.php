<?php

if(isset($_GET['export']) && isset($_GET['file']) && $_GET['file'] == "pdf") {
//require_once __DIR__ . '/vendor/autoload.php';
    require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php');

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
    'default_font' => 'dejavusans'
    ]);

    $mpdf->SetDirectionality('rtl');

    switch (fixXSS ($_GET['export'])) {
        case 'archive':

            test_mode_table_prefix ();
            $table_name = $_GET["subject"];
            $mpdf->SetHTMLHeader('<div>'.BOUTIQUE_TABLES[$table_name]["title"].'</div>');

            $packet = get_data_to_export($table_name,"pdf");
            $headers = $packet["headers"];
            $data = $packet["data"];

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
                }
                
                th, td {
                    padding: 5px;
                    text-align: right;
                }
                </style>
                <table>            
                <thead>
                <tr class="">';

            foreach($headers as $key=>$header) {
                $html .= '<th>' . $key . '</th>';
            }
            $html.='</tr></thead>';
            foreach($data as $row){
                $html .= '<tr>';
                foreach($row as $td) {
                    $html .= '<td>' . $td . '</td>';
                }
                $html .= '</tr>';
            }
            $html.="</table>";

            break;
    }

    //write_log("html ".json_encode($html));

    //echo mb_detect_encoding($html);
    $mpdf->WriteHTML($html);
    $mpdf->Output();
}

?>