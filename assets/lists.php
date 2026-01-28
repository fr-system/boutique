<?php

const BOUTIQUE_TABLES = array(
    "clients" => array(
        "title" => "לקוחות",
        "single" => "לקוח",
        "male_female" => "male",
        "columns" => array(
            array("field_name" => "name", "widget" => "text", "label" => "שם הלקוח","required"=>true),
            array("field_name" => "mobile", "widget" => "text", "label" => "נייד","required"=>true),
            array("field_name" => "BnNumber", "widget" => "text", "label" => "ח\"פ","required"=>true),
            array("field_name" => "address", "widget" => "text", "label" => "כתובת"),
            array("field_name" => "city_id", "widget" => "select", "join_table" => "cities", "join_value" => "name", "label" => "עיר"),
            array("field_name" => "note", "widget" => "text", "label" => "הערה","hidden"=>true),
            array("field_name" => "payment_term_id", "widget" => "select", "label" => "תנאי תשלום","required"=>true,"hidden"=>true,
                "options"=>array(
                    array("value"=>"1","text"=>"מזומן"),
                    array("value"=>"2","text"=>"שוטף+60"),
                    array("value"=>"3","text"=>"שוטף+90"),
                )
            ),
            array("field_name" => "agent_id", "widget" => "select", "type" => "user_data", "label" => "סוכן", "join_table" => "agents", "join_value" => "user_id", "user_field" => "display_name","required"=>true),
            array("field_name" => "email", "widget" => "text", "label" => "דוא\"ל","required"=>true),
            array("field_name" => "obligo", "un_apostrophe" => true, "widget" => "text", "label" => "אובליגו"),
            array("field_name" => "exceeding_conditions", "widget" => "bool", "label" => "חריגה מתנאי תשלום","hidden"=>true),
        )),
    "products" => array(
        "title" => "קטלוג המוצרים",
        "single" => "מוצר",
        "male_female" => "male",
        "columns" => array(
            array("field_name" => "name", "widget" => "text","label"=>"שם","required"=>true),
            array("field_name" => "barcode", "widget" => "text","label"=>"ברקוד","required"=>true),
            array("field_name" => "supplier_id", "widget" => "select","label"=>"ספק", "join_table" => "suppliers", "join_value" => "name"),
            array("field_name" => "price", "type" => "float", "widget" => "text","label"=>"מחיר", "un_apostrophe" => true),
            array("field_name" => "description", "widget" => "textarea","label"=>"תיאור"),
            array("field_name" => "file_id", "widget" => "file","label"=>"דף מוצר","hidden"=>true),
            array("field_name" => "image_id", "widget" => "image","label"=>"תמונה"),
            array("field_name" => "blocked", "widget" => "bool","label"=>"מוצר חסום","hidden"=>true),
            array("field_name" => "factor_of_friction", "widget" => "select","label"=>"גורם אירוז","hidden"=>true),
            array("field_name" => "individually", "widget" => "bool","label"=>"ניתן למכירה בבודדים","hidden"=>true),
        )),
    "tasks" => array(
        "title" => "משימות",
        "single" => "משימה",
        "male_female" => "female",
        "columns" => array(
            array("field_name" => "client_id", "join_table" => "clients", "join_value" => "name", "label" => "שם לקוח","hidden"=>true),
            array("field_name" => "subject", "widget" => "text", "label" => "משימה","required"=>true),
            array("field_name" => "open_date", "widget" => "date", "label" => "תאריך פתיחה","locked"=>true),
            array("field_name" => "agent_id", "widget" => "select", "label" => "סוכן","required"=>true),//להביא מטבלת יוזר
            array("field_name" => "details", "widget" => "textarea", "label" => "פירוט"),
            array("field_name" => "importance_id", "widget" => "select", "label" => "חשיבות"),
            array("field_name" => "status_id", "widget" => "select", "label" => "מצב משימה"),
            array("field_name" => "target_date", "widget" => "date", "label" => "תאריך יעד"),
        )),
    "suppliers" => array(
        "title" => "ספקים",
        "single" => "ספק",
        "male_female" => "male",
        "columns" => array(
            array("field_name" => "name", "widget" => "text", "label" => "שם","required"=>true),
            array("field_name" => "email", "widget" => "email", "label" => "דוא\"ל","required"=>true),
            array("field_name" => "phone", "widget" => "text", "label" => "טלפון"),
            array("field_name" => "mobile", "widget" => "text", "label" => "נייד"),
            array("field_name" => "address", "widget" => "text", "label" => "כתובת"),
            array("field_name" => "city_id","widget" => "select", "join_table" => "cities", "join_value" => "name", "label" => "עיר"),
            array("field_name" => "notes", "widget" => "textarea", "label" => "הערות"),
        )),
    "orders" => array(
        "title" => "הזמנות",
        "single" => "הזמנה",
        "male_female" => "female",
        "columns" => array(
            array("field_name" => "order_date", "widget" => "date", "label" => "תאריך הזמנה","required"=>true),
            array("field_name" => "client_id","widget" => "select", "join_table" => "clients", "join_value" => "name", "label" => "שם הלקוח","required"=>true),
            array("field_name" => "doc_type","widget" => "file", "label" => "מסמך"),
            array("field_name" => "notes","widget" => "textarea", "label" => "הערות"),
            array("field_name" => "user_opens","widget" => "text","locked"=>true, "label" => "מקים ההזמנה"),
            array("field_name" => "user_confirms","widget" => "text","locked"=>true, "label" => "מאשר ההזמנה"),
        )),
    "orders_products" => array(
        "title" => "הזמנות מוצרים",
        "columns" => array(
            array("field_name" => "order_id"),
            array("field_name" => "product_id"),
            array("field_name" => "price", "widget" => "text", "un_apostrophe" => true),
            array("field_name" => "bonus", "widget" => "bool"),
            array("field_name" => "discount_percent", "widget" => "text", "un_apostrophe" => true),
        )),
    "agents" => array(
        "title" => "סוכנים",
        "single" => "סוכן",
        "male_female" => "male",
        "columns" => array(
            array("field_name" => "user_id", "widget" => "text", "type" => "user_data", "label" => "שם", "user_field" => "display_name","required"=>true),
            array("field_name" => "user_id", "widget" => "text", "type" => "user_data", "label" => "שם משתמש", "user_field" => "user_login","required"=>true),
            array("field_name" => "user_id", "widget" => "email", "type" => "user_data", "label" => "דוא\"ל", "user_field" => "user_email","required"=>true),

            array("field_name" => "mobile", "widget" => "text", "label" => "נייד"),
            array("field_name" => "work_area_id","widget" => "select", "join_table" => "areas", "join_value" => "area", "label" => "אזור עבודה","required"=>true),// סינון אזור
            array("field_name" => "notes", "widget" => "textarea", "label" => "הערה"),
            array("field_name" => "target","widget" => "number", "label" => "יעד כללי"),
        ),
        //"filter"=>"area_id == []"
    ),
//    "cities" =>
//        array(
//        "title" => "ערים",
//        "single" => "עיר",
//            "male_female" => "female",
//        "columns" => array(
//            array("field_name" => "name", "widget" => "text"),
//            array("field_name" => "area_id","widget" => "select"),
//            array("field_name" => "is_area", "widget" => "bool"),
//        ),
//        "filter"=>"is_area != true",
//        "data-field"=>"area_id"
//        ),
    "collection" =>
        array(
        "title" => "חשבוניות",
        "single" => "חשבונית",
        "columns" => array(
            array("field_name" => "supplier_id","widget" => "select", "join_table" => "suppliers", "join_value" => "name", "label" => "שם הספק"),
            array("field_name" => "client_id","widget" => "select", "join_table" => "clients", "join_value" => "name", "label" => "שם הלקוח"),
            array("field_name" => "obligation", "widget" => "text","un_apostrophe" => true, "label" => "חיוב"),
            array("field_name" => "doc_number", "type" => "text","un_apostrophe" => true, "label" => "מס' חשבונית"),
            array("field_name" => "date", "widget" => "date", "label" => "תאריך"),
            array("field_name" => "doc_type", "widget" => "select", "label" => "סוג חשבונית"),
            array("field_name" => "payment_date", "widget" => "date", "label" => "תאריך תשלום"),
            //array("field_name" => "credit_number", "widget" => "text", "label" => "מספר כרטיס אשראי"),
            array("field_name" => "payment_type", "widget" => "select", "label" => "אופן תשלום"),
            array("field_name" => "check_number", "widget" => "text", "label" => "מספר צ'ק"),
        ))

);
const BOUTIQUE_LISTS = array(
    "cities" =>
        array(
            "title" => "ערים",
            "single" => "עיר",
            "male_female" => "female",
            "columns" => array(
                array("field_name" => "name","widget" => "text"),
                array("field_name" => "area_id","widget" => "select", "join_table" => "areas", "join_value" => "area", "label" => "איזור"),
            ),
            "filter"=>"is_area != true",
            "data-field"=>"area_id"
        ),
    "areas" =>
        array(
            "title" => "איזורים",
            "single" => "אזור",
            "columns" => array(
                array("field_name" => "area","widget" => "text"),
            )
        ),
    "importance" =>
        array(
            "title" => "חשיבות",
            "single" => "חשיבות",
            "columns" => array(
                array("field_name" => "importance","widget" => "text"),
            )
        ),
    "payment_terms" =>
        array(
            "title" => "תנאי תשלום",
            "single" => "תנאי תשלום",
            "columns" => array(
                array("field_name" => "importance","widget" => "text"),
            )
        ),
    "specials" =>
        array(
            "title" => "הטבות ומבצעים",
            "single" => "מבצע",
            "columns" => array(
                array("field_name" => "descript","widget" => "text"),
                array("field_name" => "discount","widget" => "text","un_apostrophe" => true),
            )
        ),
    );
?>
