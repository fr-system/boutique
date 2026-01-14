<?php

define("BOUTIQUE_TABLES"  ,array(
    "clients" => array(
        "title" => "לקוחות",
        "single"=> "לקוח",
        "columns" => array(
        array("field_name"=>"name", "type"=>"text","label"=>"שם הלקוח"),
        array("field_name"=>"mobile", "type"=>"text","label"=>"נייד"),
        array("field_name"=>"BnNumber", "type"=>"text","label"=>"ח\"פ"),
        array("field_name"=>"address", "type"=>"text","label"=>"כתובת"),
        array("field_name"=>"city_id","type"=>"int","join_table" => "cities",  "join_value" => "name", "label"=>"עיר"),
        array("field_name"=>"note", "type"=>"text","label"=>"הערה"),
        array("field_name"=>"payment_term_id", "type"=>"int","label"=>"תנאי תשלום"),
        array("field_name"=>"agent_id", "type"=>"user_data","label"=>"סוכן","join_table" => "agents", "join_value" => "user_id","user_field"=>"display_name"),
        array("field_name"=>"email", "type"=>"text","label"=>"דוא\"ל"),
        array("field_name"=>"obligo", "type"=>"int","label"=>"אובליגו"),
        array("field_name"=>"exceeding_conditions", "type"=>"bool","label"=>"חריגה מתנאי תשלום"),
)),
   "products"=> array(
        "title"  => "מוצרים",
        "single"=> "מוצר",
        "columns" => array(
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
    "tasks" => array(
        "title"  => "משימות",
        "single"=> "משימה",
        "columns" => array(
        array("field_name"=>"client_id", "type"=>"int","join_table" => "clients",  "join_value" => "name","label"=>"שם לקוח"),
        array("field_name"=>"subject", "type"=>"text","label"=>"משימה"),
        array("field_name"=>"open_date", "type"=>"date","label"=>"תאריך פתיחה"),
        array("field_name"=>"agent_id", "type"=>"int","label"=>"סוכן"),//להביא מטבלת יוזר
        array("field_name"=>"details", "type"=>"text","label"=>"פירוט"),
        array("field_name"=>"importance_id", "type"=>"int","label"=>"חשיבות"),
        array("field_name"=>"status_id", "type"=>"int","label"=>"מצב משימה"),
        array("field_name"=>"target_date", "type"=>"date","label"=>"תאריך יעד"),
    )),
    "suppliers"=> array(
        "title"  => "ספקים",
        "single"=> "ספק",
        "columns" => array(
        array("field_name"=>"name", "type"=>"text","label"=>"שם"),
        array("field_name"=>"email", "type"=>"text","label"=>"דוא\"ל"),
        array("field_name"=>"phone", "type"=>"text","label"=>"טלפון"),
        array("field_name"=>"mobile", "type"=>"text","label"=>"נייד"),
        array("field_name"=>"address", "type"=>"text","label"=>"כתובת"),
        array("field_name"=>"city_id", "type"=>"int","join_table" => "cities",  "join_value" => "name","label"=>"עיר"),
        array("field_name"=>"notes", "type"=>"text","label"=>"הערות"),
    )),
    "orders"=> array(
        "title"  => "הזמנות",
        "single"=> "הזמנה",
        "columns" => array(
        array("field_name"=>"order_date", "type"=>"date","label"=>"תאריך הזמנה"),
        array("field_name"=>"client_id", "type"=>"int","join_table" => "clients",  "join_value" => "name","label"=>"שם הלקוח"),
        array("field_name"=>"doc_type", "type"=>"int","label"=>"מסמך"),
        array("field_name"=>"notes", "type"=>"text","label"=>"הערות"),
        array("field_name"=>"user_opens", "type"=>"int","label"=>"מקים ההזמנה"),
        array("field_name"=>"user_confirms", "type"=>"int","label"=>"מאשר ההזמנה"),
        array("field_name"=>"nisayon", "type"=>"select","label"=>"ניסיון","options"=>array(array("value"=>"1","text"=>"shoshanana"))),

        )),
    "orders_products"=> array(
        "title"  => "הזמנות מוצרים",
        "columns" => array(
        array("field_name"=>"order_id", "type"=>"int"),
        array("field_name"=>"product_id", "type"=>"int"),
        array("field_name"=>"price", "type"=>"float"),
        array("field_name"=>"bonus", "type"=>"bool"),
        array("field_name"=>"discount_percent", "type"=>"float"),
    )),
"agents"=> array(
        "title"  => "סוכנים",
        "single"=> "סוכן",
        "columns" => array(
        array("field_name"=>"user_id", "type"=>"user_data","label"=>"שם","user_field"=>"display_name"),
        array("field_name"=>"user_id", "type"=>"user_data","label"=>"שם משתמש","user_field"=>"user_login"),
        array("field_name"=>"user_id", "type"=>"user_data","label"=>"דוא\"ל","user_field"=>"user_email"),

        array("field_name"=>"mobile", "type"=>"text","label"=>"נייד"),
        array("field_name"=>"work_area_id", "type"=>"int","join_table" => "cities",  "join_value" => "name","label"=>"אזור עבודה"),// סינון אזור
        array("field_name"=>"notes", "type"=>"text","label"=>"הערה"),
        array("field_name"=>"target", "type"=>"int","label"=>"יעד כללי"),
    )),
"cities"=> array(
        "title"  => "ערים",
        "single"=> "עיר",
        "columns" => array(
        array("field_name"=>"name", "type"=>"text"),
        array("field_name"=>"area_id", "type"=>"int"),
        array("field_name"=>"is_area", "type"=>"bool"),
    )),
"supplier_invoices"=> array(
        "title"  => "חשבוניות ספקים",
        "single"=> "",
        "columns" => array(
        array("field_name"=>"supplier_id", "type"=>"int","join_table" => "suppliers",  "join_value" => "name","label"=>"שם הספק"),
        array("field_name"=>"client_id", "type"=>"int","join_table" => "clients",  "join_value" => "name","label"=>"שם הלקוח"),
        array("field_name"=>"obligation", "type"=>"float","label"=>"חיוב"),
        array("field_name"=>"invoic_number", "type"=>"int","label"=>"מס' חשבונית"),
        array("field_name"=>"date", "type"=>"date","label"=>"תאריך"),
        array("field_name"=>"payment_date", "type"=>"date","label"=>"סוג חשבונית"),
        array("field_name"=>"credit_number", "type"=>"int","label"=>"מספר כרטיס אשראי"),
        array("field_name"=>"payment_type", "type"=>"int","label"=>"אופן תשלום"),
        array("field_name"=>"check_number", "type"=>"text","label"=>"מספר צ'ק"),
    ))

));

?>
