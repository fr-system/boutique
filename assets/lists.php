<?php

define("BOUTIQUE_TABLES"    ,array(
    array(
    "subject" => "clients",
    "text" => "לקוחות",
    "single"=> "לקוח",
    "fields" => array(
    array("field_name"=>"name", "type"=>"text"),
    array("field_name"=>"mobile", "type"=>"text"),
    array("field_name"=>"BnNumber", "type"=>"text"),
    array("field_name"=>"address", "type"=>"text"),
    array("field_name"=>"city_id", "type"=>"int"),
    array("field_name"=>"note", "type"=>"text"),
    array("field_name"=>"payment_term_id", "type"=>"int"),
   // array("field_name"=>"agent_id", "type"=>"int"),
    array("field_name"=>"email", "type"=>"text"),
    array("field_name"=>"obligo", "type"=>"int"),
    array("field_name"=>"exceeding_conditions", "type"=>"bool"),
)),
    array(
        "subject" => "products",
        "text" => "מוצרים",
        "single"=> "מוצר חדש",
        "fields" => array(
        array("field_name"=>"name", "type"=>"text"),
        array("field_name"=>"barcode", "type"=>"text"),
        array("field_name"=>"supplier_id", "type"=>"int"),
        array("field_name"=>"price", "type"=>"float"),
        array("field_name"=>"description", "type"=>"text"),
        array("field_name"=>"file_id", "type"=>"int"),
        array("field_name"=>"image_id", "type"=>"int"),
        array("field_name"=>"blocked", "type"=>"bool"),
        array("field_name"=>"factor_of_friction", "type"=>"int"),
        array("field_name"=>"individually", "type"=>"bool"),
    )),
    array(
        "subject" => "tasks",
        "text" => "משימות",
        "single"=> "משימה",
        "fields" => array(
        array("field_name"=>"client_id", "type"=>"int"),
        array("field_name"=>"subject", "type"=>"text"),
        array("field_name"=>"open_date", "type"=>"date"),
        array("field_name"=>"agent_id", "type"=>"int"),
        array("field_name"=>"details", "type"=>"text"),
        array("field_name"=>"importance_id", "type"=>"int"),
        array("field_name"=>"status_id", "type"=>"int"),
        array("field_name"=>"target_date", "type"=>"date"),
    )),
    array(
        "subject" => "suppliers",
        "text" => "ספקים",
        "single"=> "ספק",
        "fields" => array(
        array("field_name"=>"name", "type"=>"text"),
        array("field_name"=>"email", "type"=>"text"),
        array("field_name"=>"phone", "type"=>"text"),
        array("field_name"=>"mobile", "type"=>"text"),
        array("field_name"=>"address", "type"=>"text"),
        array("field_name"=>"city_id", "type"=>"int"),
        array("field_name"=>"notes", "type"=>"text"),
    )),
    array(
        "subject" => "orders",
        "text" => "הזמנות",
        "single"=> "הזמנה",
        "fields" => array(
        array("field_name"=>"order_date", "type"=>"date"),
        array("field_name"=>"client_id", "type"=>"int"),
        array("field_name"=>"doc_type", "type"=>"int"),
        array("field_name"=>"notes", "type"=>"text"),
        array("field_name"=>"user_opens", "type"=>"int"),
        array("field_name"=>"user_confirms", "type"=>"int"),
    )),
    array(
        "subject" => "orders_products",
        "text" => "הזמנות מוצרים",
            "fields" => array(
        array("field_name"=>"order_id", "type"=>"int"),
        array("field_name"=>"product_id", "type"=>"int"),
        array("field_name"=>"price", "type"=>"float"),
        array("field_name"=>"bonus", "type"=>"bool"),
        array("field_name"=>"discount_percent", "type"=>"float"),
    )),
    array(
        "subject" => "agents",
        "text" => "סוכנים",
        "single"=> "סוכן",
        "fields" => array(
        array("field_name"=>"user_id", "type"=>"int"),
        array("field_name"=>"mobile", "type"=>"text"),
        array("field_name"=>"work_area_id", "type"=>"int"),
        array("field_name"=>"notes", "type"=>"text"),
        array("field_name"=>"target", "type"=>"int"),
    )),
    array(
        "subject" => "cities",
        "text" => "ערים",
        "single"=> "עיר",
        "fields" => array(
        array("field_name"=>"name", "type"=>"text"),
        array("field_name"=>"area_id", "type"=>"int"),
        array("field_name"=>"is_area", "type"=>"bool"),
    )),
    array(
        "subject" => "supplier_invoices",
        "text" => "חשבוניות ספקים",
        "single"=> "",
        "fields" => array(
        array("field_name"=>"supplier_id", "type"=>"int"),
        array("field_name"=>"client_id", "type"=>"int"),
        array("field_name"=>"obligation", "type"=>"float"),
        array("field_name"=>"invoic_number", "type"=>"int"),
        array("field_name"=>"date", "type"=>"date"),
        array("field_name"=>"payment_date", "type"=>"date"),
        array("field_name"=>"credit_number", "type"=>"int"),
        array("field_name"=>"payment_type", "type"=>"int"),
        array("field_name"=>"check_number", "type"=>"text"),
    ))

));

?>
