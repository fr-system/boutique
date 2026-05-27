<?php

add_action('wp_ajax_get_obligation_client', 'get_obligation_client');
function get_obligation_client()
{
    $filters = array();
    $filters[]=array("filter_field" => "id", "filter_value" => $_POST["client_id"]);
    $client = get_page_data("clients", $filters)[0];

    $filters = array();
    $filters[]=array("filter_field" => "client_id", "filter_value" => $_POST["client_id"]);
    $filters[]=array("filter_field" => "payment_date", "filter_type" => "null");
    $result = get_page_data("collection", $filters);
    write_log("eres ".json_encode($result));
    $obligo = 0;
    foreach ($result as $row){

        $obligo+=$row->obligation;
    }

    write_log("obligo ".$obligo);
    echo json_encode (array("obligation" => $obligo > $client->obligo));
    die();

}

add_action('wp_ajax_sent_to_manager', 'sent_to_manager');
function sent_to_manager()
{
    $filters = array(array("filter_field" => "id", "filter_value"=>$_POST["id"]));
    $order = get_page_data("orders",$filters)[0];

    $filters = array(array("filter_field" => "id", "filter_value"=>$order->client_id));
    $client = get_page_data("clients",$filters)[0];

    $body = "ללקוח " . $client->name."<br>". "יש חריגה מתשלום יש לו חוב בסכום של: "  .  $client->obligo.
       "<br>"."ותקרת החוב שלו היא:" .  $client->obligo;
    send_mail(get_option('admin_email'),"בקשה לאישור הזמנה חדשה ללקוח: " .$client->name,$body);

    add_notice( 'sent_to_manager' ,"נשלח מייל למנהל לאישור ההזמנה" );
}

?>