<?php
//"widget" => "hidden" - בעמוד של סינגל לא להכין לו ווידגט
//"hide_in_table"=>true - בעמוד ארכיב לא להכין לו עמודה בטבלה
const BOUTIQUE_TABLES = array(
    "clients" => array(
        "title" => "לקוחות",
        "single" => "לקוח",
        "male_female" => "male",
        "columns" => array(
            array("field_name" => "name", "widget" => "text", "label" => "שם הלקוח","required"=>true),
            array("field_name" => "mobile", "widget" => "text", "label" => "נייד","required"=>true),
            array("field_name" => "BnNumber", "widget" => "text", "label" => "ח\"פ","required"=>true,"hide_in_table"=>true),
            array("field_name" => "address", "widget" => "text", "label" => "כתובת","hide_in_table"=>true),
            array("field_name" => "city_id", "widget" => "select", "join_table" => "cities", "join_value" => "name", "label" => "עיר"),
            array("field_name" => "note", "widget" => "text", "label" => "הערה","hide_in_table"=>true),
            array("field_name" => "payment_term_id", "widget" => "select", "label" => "תנאי תשלום","required"=>true,"hide_in_table"=>true,"filter"=>true,
                "options"=>array(
                    array("value"=>"1","text"=>"מזומן"),
                    array("value"=>"2","text"=>"שוטף+60"),
                    array("value"=>"3","text"=>"שוטף+90"),
                )
            ),
            array("field_name" => "agent_id", "widget" => "select", "label" => "סוכן", "join_table" => "agents", "join_value" => "name","required"=>true),
            array("field_name" => "email", "widget" => "text", "label" => "דוא\"ל","required"=>true),
            array("field_name" => "email2", "widget" => "text", "label" => "דוא\"ל נוסף","hide_in_table"=>true),
            array("field_name" => "accounting_phone_number", "widget" => "text", "label" => "טלפון הנה\"ח","hide_in_table"=>true),
            array("field_name" => "obligo", "un_apostrophe" => true, "widget" => "text", "label" => "אובליגו","sign"=>"₪"),
            array("field_name" => "promissory_note","widget" => "file", "label" => "שטר חוב","hide_in_table"=>true),
            array("field_name" => "blocked", "widget" => "hidden","create_input"=>true),
            array("field_name" => "clients_branches" ,"widget" => "table" ,"field_id"=>"main_client_id","hide_in_table"=>true,"new_row"=>true),


        ),
        "filter" => "blocked is null or blocked = 0",
        "actions" => array("orders","tasks",array("title"=>"שליחת דוח חיוב","dialog"=>"bout-massage",
            "ajax_func"=>"client_billing_report","text"=>"האם לשלוח ללקוח דוח חיוב למייל?"))
    ),
    //id	name	main_client_id	city_id	address	phone	email
    "clients_branches" => array(
        "title" => "סניפים",
        "columns" => array(
            array("field_name" => "name", "widget" => "text", "label" => "שם הסניף","create_input"=>true),
            array("field_name" => "address", "widget" => "text", "label" => "כתובת","hide_in_table"=>true,"create_input"=>true),
/*            array("field_name" => "city_id", "widget" => "select", "join_table" => "cities", "join_value" => "name", "label" => "עיר","create_input"=>true),*/
            array("field_name" => "mobile", "widget" => "text", "label" => "נייד","required"=>true,"create_input"=>true),
            array("field_name" => "email", "widget" => "text", "label" => "דוא\"ל","required"=>true,"create_input"=>true),
            array("field_name" => "id", "widget" => "hidden","create_input"=>true),
            array("field_name" => "main_client_id", "widget" => "hidden","create_input"=>true),
        ),
        "update_remove"=>false
    ),

    "products" => array(
        "title" => "קטלוג המוצרים",
        "single" => "מוצר",
        "male_female" => "male",
        "columns" => array(
            array("field_name" => "name", "widget" => "text","label"=>"שם","required"=>true),
            array("field_name" => "barcode", "widget" => "text","label"=>"ברקוד","required"=>true),
            array("field_name" => "supplier_id", "widget" => "select","label"=>"ספק", "join_table" => "suppliers", "join_value" => "name","filter"=>true),
            array("field_name" => "price", "widget" => "text","label"=>"מחיר", "un_apostrophe" => true,"sign"=>"₪"),
            array("field_name" => "description", "widget" => "textarea","label"=>"תיאור","hide_in_table"=>true),
            //array("field_name" => "count", "widget" => "number","label"=>"כמות בקבוקים בארגז","hide_in_table"=>true),
            array("field_name" => "file_id", "widget" => "file","label"=>"דף מוצר","hide_in_table"=>true),
            array("field_name" => "image_id", "widget" => "image","label"=>"תמונת מוצר"),
            array("field_name" => "blocked", "widget" => "checkbox","label"=>"מוצר חסום","hide_in_table"=>true),
            /*array("field_name" => "factor_of_friction", "widget" => "select","label"=>"גורם אירוז","hide_in_table"=>true),*/
            array("field_name" => "individually", "widget" => "checkbox","label"=>"ניתן למכירה בבודדים","hide_in_table"=>true),
            array("field_name" => "units_in_box", "widget" => "number","label"=>"כמות יחידות בארגז","required"=>true,"hide_in_table"=>true,"min"=>1,"max"=>49),
        )),
    "tasks" => array(
        "title" => "משימות",
        "single" => "משימה",
        "male_female" => "female",
        "columns" => array(
            array("field_name" => "client_id", "widget" => "select", "join_table" => "clients", "join_value" => "name", "label" => "שם לקוח"),
            array("field_name" => "subject", "widget" => "select", "label" => "נושא משימה","required"=>true,
             "join_table" => "subjects", "join_value" => "text","save_as_text"=>true, "add_option"=>true),
            array("field_name" => "open_date", "widget" => "hidden", "label" => "תאריך פתיחה"),
            array("field_name" => "agent_id", "widget" => "select", "label" => "סוכן", "join_table" => "agents", "join_value" => "name","required"=>true,"filter"=>true),
            array("field_name" => "details", "widget" => "textarea", "label" => "פירוט","hide_in_table" => true),
            array("field_name" => "importance_id", "widget" => "radio", "label" => "חשיבות","filter"=>true,
                "values"=>array(
                    1=>array("class"=>"high","label"=> "גבוהה","color"=>"#1A7870"),
                    2=>array("class"=>"medium","label"=> "בינונית","color"=>"#4CD2C6"),
                    3=>array("class"=>"low","label"=> "נמוכה","color"=>"#C0DBD9")
                )
            ),
            array("field_name" => "status_id", "widget" => "status", "label" => "מצב משימה","filter"=>true,
                "values"=>array(
                    1=>array("class"=>"done background-light-light-blue","label"=> "בוצע"),
                    2=>array("class"=>"in-treatment background-dark-green","label"=> "בטיפול"),
                    3=>array("class"=>"not-yet-treated background-light-orange","label"=> "טרם טופל")
                )),
            array("field_name" => "target_date", "widget" => "date", "label" => "תאריך יעד"),
            array("field_name" => "sending_reminder", "widget" => "date", "label" => "שליחת תזכורת"),
        )),
    "suppliers" => array(
        "title" => "ספקים",
        "single" => "ספק",
        "male_female" => "male",
        "columns" => [
            ["field_name" => "name", "widget" => "text", "label" => "שם","required"=>true],
            ["field_name" => "email", "widget" => "email", "label" => "דוא\"ל","required"=>true],
            ["field_name" => "email2", "widget" => "email", "label" => "דוא\"ל"],
            ["field_name" => "email3", "widget" => "email", "label" => "דוא\"ל","hide_in_table" => true],
            ["field_name" => "email4", "widget" => "email", "label" => "דוא\"ל","hide_in_table" => true],
            ["field_name" => "phone", "widget" => "text", "label" => "טלפון"],
            ["field_name" => "mobile", "widget" => "text", "label" => "נייד"],
            ["field_name" => "address", "widget" => "text", "label" => "כתובת"],
            ["field_name" => "city_id","widget" => "select", "join_table" => "cities", "join_value" => "name", "label" => "עיר"],
            ["field_name" => "notes", "widget" => "textarea", "label" => "הערות","hide_in_table" => true],
            ["field_name" => "user_id", "widget" => "hidden"],
        ]),
    "supplier_column_mapping" => array(
        "title" => "מיפוי עמודות ספקים",
        "single" => "ספק",
        "male_female" => "male",
        "columns" => [
            ["field_name" => "supplier_id", "widget" => "text", "un_apostrophe" => true, "label" => "","required"=>true],
            ["field_name" => "excel_column_index", "widget" => "text", "un_apostrophe" => true, "label" => "","required"=>true],
            ["field_name" => "field_name", "widget" => "text","label" => ""],
        ]),
    "orders" => array(
        "title" => "הזמנות",
        "single" => "הזמנה",
        "male_female" => "female",
        "columns" => array(
            array("field_name" => "client_id","widget" => "select", "join_table" => "clients", "join_value" => "name", "label" => "שם הלקוח","required"=>true),
            array("field_name" => "branch", "widget" => "none", "label" => "סניף"),
            array("field_name" => "order_date", "widget" => "datetime-local", "label" => "תאריך הזמנה","required"=>true),
            array("field_name" => "user_opens","widget" => "none", "label" => "מקים ההזמנה", "type" => "user","join_table" => "agents"/*, "join_value" => "id"*/,"join_field"=>"user_id","join_values_select"=>array("id","name")),//
          //  array("field_name" => "order_products" ,"widget" => "table" ,"field_id"=>"order_id","hide_in_table"=>true,"target_table"=>"specials"),
            array("field_name" => "order_products" ,"widget" => "table" ,"field_id"=>"order_id","hide_in_table"=>true,"target_table"=>"products"),
            array("field_name" => "total","widget" => "text", "label" => "סה\"כ", "un_apostrophe" => true,"sign"=>"₪"),
            array("field_name" => "notes","widget" => "textarea", "label" => "הערות","hide_in_table"=>true),
            array("field_name" => "user_confirms","widget" => "none", "label" => "מאשר ההזמנה", "type" => "user"),//"join_table" => "agents", "join_value" => "name","join_field"=>"user_id"),
            array("field_name" => "done"/*,"widget" => "bool"*/),
        )),
    "order_products" => array(
        "title" => "הזמנות מוצרים",
        "columns" => array(
            array("field_name" => "order_price", "widget" => "readonly", "label" => "מחיר", "un_apostrophe" => true,"sign"=>"₪","create_input"=>true),
            array("field_name" => "count", "widget" => "number", "label" => "כמות","create_input"=>true),
            array("field_name" => "order_individual", "widget" => "toggle", "label" => "ארגזים/בודדים","create_input"=>true,
                "values"=>array(
                    0=>array("class"=>"background-light-light-blue right","label"=> "ארגזים"),
                    1=>array("class"=>"background-dark-green left","label"=> "בודדים"),
                )),
           // array("field_name" => "bonus", "widget" => "number", "label" => "בונוס","create_input"=>true),
            array("field_name" => "discount_percent", "widget" => "text", "label" => "אחוזי הנחה", "un_apostrophe" => true,"sign"=>"%","create_input"=>true),
            array("field_name" => "total", "widget" => "readonly", "label" => "סה\"כ", "un_apostrophe" => true,"sign"=>"₪","create_input"=>true),
            array("field_name" => "id", "widget" => "hidden","create_input"=>true),
            array("field_name" => "order_id", "widget" => "hidden","create_input"=>true),
            array("field_name" => "product_id", "widget" => "hidden","create_input"=>true),
            //array("field_name" => "supplier_id", "join_table_from" => "products", "join_table" => "suppliers", "join_value" => "name"),
            //array("field_name" => "agent_id","widget" => "hidden", "join_table_from" => "clients", "join_table" => "agents", "join_value" => "name", "label" => "סוכן"),

        ),
        "more_columns_in_table" => array(
            array("field_name" => "image_id", "widget" => "image"),
            array("field_name" => "name", "widget" => "text","label"=>"שם המוצר"/*,"width"=>"1000px"*/),
            array("field_name" => "individually", "widget" => "hidden","hide_in_table"=>true,"hide_in_pdf"=>true),
            array("field_name" => "units_in_box", "widget" => "hidden","hide_in_table"=>true,"hide_in_pdf"=>true),
            array("field_name" => "supplier_id",  "widget" => "hidden","hide_in_table"=>true,"hide_in_pdf"=>true),
        ),
        "update_remove"=>false
    ),

    "agents" => array(
        "title" => "סוכנים",
        "single" => "סוכן",
        "male_female" => "male",
        "columns" => array(
            array("field_name" => "name", "widget" => "text", "label" => "שם","required"=>true),
            array("field_name" => "email", "widget" => "email", "label" => "דוא\"ל","required"=>true),
            array("field_name" => "mobile", "widget" => "text", "label" => "נייד"),
            array("field_name" => "work_area_id","widget" => "select", "join_table" => "areas", "join_value" => "area", "label" => "אזור עבודה","required"=>true),// סינון אזור
            array("field_name" => "agent_target_supplier", "widget" => "table" ,"hide_in_table"=>true,"field_id"=>"agent_id","target_table"=>"suppliers"),

            array("field_name" => "target","widget" => "text", "label" => "יעד כללי", "un_apostrophe" => true,"sign"=>"₪"),
            array("field_name" => "notes", "widget" => "textarea", "label" => "הערה","hide_in_table"=>true),
            array("field_name" => "user_id", "widget" => "hidden"),
        ),
        //"filter"=>"area_id == []"
    ),
    "agent_target_supplier"=>array(
        "title" => "יעד לסוכן לכל ספק",
        "columns" => array(
            array("field_name" => "target", "widget" => "text", "label" => "יעד", "un_apostrophe" => true,"sign"=>"₪","create_input"=>true),
            array("field_name" => "date_from", "widget" => "date", "label" => "מתאריך","create_input"=>true),
            array("field_name" => "date_to", "widget" => "date", "label" => "עד תאריך","create_input"=>true),
            array("field_name" => "id","widget" => "hidden","create_input"=>true),
            array("field_name" => "supplier_id","widget" => "hidden","create_input"=>true),
            array("field_name" => "agent_id","widget" => "hidden","create_input"=>true),
            array("field_name" => "total", "widget" => "אקסא","label"=>"הזמין בפועל","not_in_query"=>true),
        ),
        "more_columns_in_table" => array(
            array("field_name" => "name", "widget" => "select","label"=>"ספק"),
        ),
        "update_remove"=>false
    ),
    "collection" =>//invoices
        array(
        "title" => "חשבוניות",
        "single" => "חשבונית",
        "columns" => array(
            array("field_name" => "supplier_id","widget" => "select", "join_table" => "suppliers", "join_value" => "name", "label" => "שם הספק"),
            array("field_name" => "client_id","widget" => "select", "join_table" => "clients", "join_value" => "name", "label" => "שם הלקוח"),
            array("field_name" => "obligation", "widget" => "text","un_apostrophe" => true, "label" => "חיוב","sign"=>"₪"),
            array("field_name" => "agent_id","widget" => "select", "join_table_from" => "clients", "join_table" => "agents", "join_value" => "name", "label" => "סוכן","hide_in_pdf"=>true),

            array("field_name" => "doc_number", "widget" => "text", "label" => "מס' חשבונית"),
            array("field_name" => "date", "widget" => "date", "label" => "תאריך"),
            array("field_name" => "payment_until", "widget" => "date", "label" => "לתשלום עד"),
/*            array("field_name" => "doc_type", "widget" => "select", "label" => "סוג חשבונית"),*/
            array("field_name" => "doc_type", "widget" => "status", "label" => "חשבונית","filter"=>true,
                "values"=>array(
                    1=>array("class"=>"done background-light-light-blue","label"=> "חיוב"),
                    2=>array("class"=>"in-treatment background-dark-green","label"=> "זיכוי"),
                )),

            array("field_name" => "payment_date", "widget" => "date", "label" => "תאריך תשלום","hide_in_pdf"=>true),
            //array("field_name" => "credit_number", "widget" => "text", "label" => "מספר כרטיס אשראי"),
            array("field_name" => "payment_type", "widget" => "select", "label" => "אופן תשלום",
                "options"=>array(
                    array("value"=>"1","text"=>"מזומן"),
                    array("value"=>"2","text"=>"כרטיס אשראי"),
                    array("value"=>"3","text"=>"המחאה"),
                    array("value"=>"4","text"=>"העברה בנקאית"),
                    array("value"=>"5","text"=>"ביט"),

                ),"hide_in_pdf"=>true),
            array("field_name" => "check_number", "widget" => "text", "label" => "מספר צ'ק","hide_in_pdf"=>true),
            array("field_name" => "imported_at", "widget" => "date","hide_in_table"=>true,"hide_in_pdf"=>true),
/*            array("field_name" => "agent_id", "widget" => "select", "label" => "סוכן", "join_table" => "agents", "join_value" => "user_id", "user_field" => "display_name"),//להביא מטבלת יוזר*/

        ),
            "update_remove"=>false
        ),
    "products_clients"=>array(
        "title" => "מחיר מיוחד ללקוח",
        "columns" => array(
            array("field_name" => "client_id", "widget" => "number"),
            array("field_name" => "product_id", "widget" => "number", ),
            array("field_name" => "client_price", "widget" => "text", "un_apostrophe" => true),

        )),
    "chat"=>array(
        "title" => "צ'אט",
        "columns" => array(
            array("field_name" => "task_id", "widget" => "number"),
            array("field_name" => "user_id","widget" => "number"/*, "user_field" => "display_name"*/),
            array("field_name" => "date", "widget" => "datetime-local", ),
            array("field_name" => "text", "widget" => "text"),

        ))
);
const BOUTIQUE_LISTS = array(
    "cities" =>
        array(
            "title" => "ערים",
            "single" => "עיר",
            "male_female" => "female",
            "columns" => array(
                array("field_name" => "name","widget" => "text","label"=>"עיר"),
                array("field_name" => "area_id","widget" => "select", "join_table" => "areas", "join_value" => "area", "label" => "איזור"),
            ),
        ),
    "areas" =>
        array(
            "title" => "איזורים",
            "single" => "אזור",
            "columns" => array(
                array("field_name" => "area","widget" => "text","label"=>"אזור"),
            )
        ),
    /*"importance" =>
        array(
            "title" => "חשיבות",
            "single" => "חשיבות",
            "columns" => array(
                array("field_name" => "importance","widget" => "text"),
            )
        ),*/
   /* "payment_terms" =>
        array(
            "title" => "תנאי תשלום",
            "single" => "תנאי תשלום",
            "columns" => array(
                array("field_name" => "importance","widget" => "text"),
            )
        ),*/
    "subjects" =>
        array(
            "title" => "נושא משימה",
            "single" => "נושא",
            "columns" => array(
                array("field_name" => "text","widget" => "text","label"=>"נושא"),
            )
        ),
    "specials" =>
        array(
            "title" => "מבצעים",
            "single" => "מבצע",
            "columns" => array(
                array("field_name" => "descript","label"=>"תיאור המבצע","widget" => "text"),
                array("field_name" => "supplier_id", "widget" => "select","label"=>"ספק", "join_table" => "suppliers", "join_value" => "name"),
                array("field_name" => "date_end", "widget" => "date", "label" => "תאריך סיום"),

                array("field_name" => "type", "widget" => "select", "label" => "סוג מבצע",
                    "options"=>array(
                        array("value"=>"1","text"=>"קנה קבל"),
                        array("value"=>"2","text"=>"קנה מעל"),
                    )
                ),
                array("field_name" => "products" ,"widget" => "special","label"=>"מוצרים","save_as_text"=> true ,"field_id"=>"product_id","hide_in_table"=>true),

                //array("field_name" => "products", "widget" => "none","multiple"=>true,"label"=>"מוצרים"/*, "join_table" => "products", "join_value" => "name"*/),
                array("field_name" => "price_more","widget" => "text","label"=>"קנה מעל סכום","un_apostrophe" => true,"sign"=>"₪"),
                array("field_name" => "buy", "widget" => "number", "label" => "קנה כמות"),
                array("field_name" => "get", "widget" => "number", "label" => "קבל"),
            )
        ),
    );
?>
