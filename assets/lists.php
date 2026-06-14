<?php
//"widget" => "hidden" - בעמוד של סינגל לא להכין לו ווידגט
//"hidden"=>true - בעמוד ארכיב לא להכין לו עמודה בטבלה
const BOUTIQUE_TABLES = array(
    "clients" => array(
        "title" => "לקוחות",
        "single" => "לקוח",
        "male_female" => "male",
        "columns" => array(
            array("field_name" => "name", "widget" => "text", "label" => "שם הלקוח","required"=>true),
            array("field_name" => "mobile", "widget" => "text", "label" => "נייד","required"=>true),
            array("field_name" => "BnNumber", "widget" => "text", "label" => "ח\"פ","required"=>true,"hidden"=>true),
            array("field_name" => "address", "widget" => "text", "label" => "כתובת","hidden"=>true),
            array("field_name" => "city_id", "widget" => "select", "join_table" => "cities", "join_value" => "name", "label" => "עיר"),
            array("field_name" => "note", "widget" => "text", "label" => "הערה","hidden"=>true),
            array("field_name" => "payment_term_id", "widget" => "select", "label" => "תנאי תשלום","required"=>true,"hidden"=>true,"filter"=>true,
                "options"=>array(
                    array("value"=>"1","text"=>"מזומן"),
                    array("value"=>"2","text"=>"שוטף+60"),
                    array("value"=>"3","text"=>"שוטף+90"),
                )
            ),
            array("field_name" => "agent_id", "widget" => "select", "label" => "סוכן", "join_table" => "agents", "join_value" => "name","required"=>true),
            array("field_name" => "email", "widget" => "text", "label" => "דוא\"ל","required"=>true),
            array("field_name" => "email2", "widget" => "text", "label" => "דוא\"ל נוסף","hidden"=>true),
            array("field_name" => "accounting_phone_number", "widget" => "text", "label" => "טלפון הנה\"ח","hidden"=>true),
            array("field_name" => "obligo", "un_apostrophe" => true, "widget" => "text", "label" => "אובליגו","sign"=>"₪"),
            array("field_name" => "doc_type","widget" => "file", "label" => "שטר חוב","hidden"=>true),
            array("field_name" => "blocked", "widget" => "hidden"),


        ),
        "filter" => "blocked is null or blocked = 0",
        "actions" => array("orders","tasks")
    ),
    "products" => array(
        "title" => "קטלוג המוצרים",
        "single" => "מוצר",
        "male_female" => "male",
        "columns" => array(
            array("field_name" => "name", "widget" => "text","label"=>"שם","required"=>true),
            array("field_name" => "barcode", "widget" => "text","label"=>"ברקוד","required"=>true),
            array("field_name" => "supplier_id", "widget" => "select","label"=>"ספק", "join_table" => "suppliers", "join_value" => "name","filter"=>true),
            array("field_name" => "price", "type" => "float", "widget" => "text","label"=>"מחיר", "un_apostrophe" => true,"sign"=>"₪"),
            array("field_name" => "description", "widget" => "textarea","label"=>"תיאור","hidden"=>true),
            //array("field_name" => "count", "widget" => "number","label"=>"כמות בקבוקים בארגז","hidden"=>true),
            array("field_name" => "file_id", "widget" => "file","label"=>"דף מוצר","hidden"=>true),
            array("field_name" => "image_id", "widget" => "image","label"=>"תמונת מוצר"),
            array("field_name" => "blocked", "widget" => "checkbox","label"=>"מוצר חסום","hidden"=>true),
            /*array("field_name" => "factor_of_friction", "widget" => "select","label"=>"גורם אירוז","hidden"=>true),*/
            array("field_name" => "individually", "widget" => "checkbox","label"=>"ניתן למכירה בבודדים","hidden"=>true),
            array("field_name" => "units_in_box", "widget" => "number","label"=>"כמות יחידות בארגז","required"=>true,"hidden"=>true,"min"=>1,"max"=>49),
        )),
    "tasks" => array(
        "title" => "משימות",
        "single" => "משימה",
        "male_female" => "female",
        "columns" => array(
            array("field_name" => "client_id", "widget" => "select", "join_table" => "clients", "join_value" => "name", "label" => "שם לקוח"),
            array("field_name" => "subject", "widget" => "text", "label" => "משימה","required"=>true),
            array("field_name" => "open_date", "widget" => "datetime-local", "label" => "תאריך פתיחה","locked"=>true),
            array("field_name" => "agent_id", "widget" => "select", "label" => "סוכן", "join_table" => "agents", "join_value" => "name","required"=>true,"filter"=>true),
            array("field_name" => "details", "widget" => "textarea", "label" => "פירוט","hidden" => true),
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
        )),
    "suppliers" => array(
        "title" => "ספקים",
        "single" => "ספק",
        "male_female" => "male",
        "columns" => [
            ["field_name" => "name", "widget" => "text", "label" => "שם","required"=>true],
            ["field_name" => "email", "widget" => "email", "label" => "דוא\"ל","required"=>true],
            ["field_name" => "email2", "widget" => "email", "label" => "דוא\"ל"],
            ["field_name" => "email3", "widget" => "email", "label" => "דוא\"ל"],
            ["field_name" => "email4", "widget" => "email", "label" => "דוא\"ל"],
            ["field_name" => "phone", "widget" => "text", "label" => "טלפון"],
            ["field_name" => "mobile", "widget" => "text", "label" => "נייד"],
            ["field_name" => "address", "widget" => "text", "label" => "כתובת"],
            ["field_name" => "city_id","widget" => "select", "join_table" => "cities", "join_value" => "name", "label" => "עיר"],
            ["field_name" => "notes", "widget" => "textarea", "label" => "הערות"],
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
            array("field_name" => "order_date", "widget" => "datetime-local", "label" => "תאריך הזמנה","required"=>true),
            array("field_name" => "user_opens","widget" => "hidden", "label" => "מקים ההזמנה", "type" => "user"),//"join_table" => "agents", "join_value" => "name","join_field"=>"user_id"),
/*            array("field_name" => "order_products" ,"widget" => "table","label" => "מוצרים","field_id"=>"order_id","hidden"=>true,"target_table"=>"products"),*/
            array("field_name" => "total","widget" => "text", "label" => "סה\"כ", "un_apostrophe" => true,"sign"=>"₪"),
            array("field_name" => "notes","widget" => "textarea", "label" => "הערות","hidden"=>true),
            array("field_name" => "user_confirms","widget" => "hidden","locked"=>true, "label" => "מאשר ההזמנה", "type" => "user"),//"join_table" => "agents", "join_value" => "name","join_field"=>"user_id"),
            array("field_name" => "done","widget" => "bool"),
        )),
    "order_products" => array(
        "title" => "הזמנות מוצרים",
        "columns" => array(
            array("field_name" => "order_id", "widget" => "number"),
            array("field_name" => "product_id", "widget" => "number", "join_table" => "products","join_values_select"=>array("name","price","image_id","supplier_id","individually","units_in_box")),
            array("field_name" => "count", "widget" => "number", "label" => "כמות"),
            array("field_name" => "order_price", "widget" => "text", "label" => "מחיר", "un_apostrophe" => true,"sign"=>"₪"),
            array("field_name" => "bonus", "widget" => "number", "label" => "בונוס"),
            array("field_name" => "discount_percent", "widget" => "text", "label" => "אחוזי הנחה", "un_apostrophe" => true,"sign"=>"%"),
            array("field_name" => "order_individual", "widget" => "bool"),
            array("field_name" => "total", "widget" => "text", "label" => "סה\"כ", "un_apostrophe" => true,"sign"=>"₪"),

           // array("field_name" => "product_id", "widget" => "text", "un_apostrophe" => true, "join_table" => "products_clients","join_id_column" => "product_id", "join_value" => "client_price","sign"=>"₪"),
        )),
    "agents" => array(
        "title" => "סוכנים",
        "single" => "סוכן",
        "male_female" => "male",
        "columns" => array(
            array("field_name" => "name", "widget" => "text", "label" => "שם","required"=>true),
            array("field_name" => "email", "widget" => "email", "label" => "דוא\"ל","required"=>true),
            array("field_name" => "mobile", "widget" => "text", "label" => "נייד"),
            array("field_name" => "work_area_id","widget" => "select", "join_table" => "areas", "join_value" => "area", "label" => "אזור עבודה","required"=>true),// סינון אזור
            array("field_name" => "agent_target_supplier", "widget" => "table", "label" => "יעד לכל ספק","hidden"=>true,"field_id"=>"agent_id","target_table"=>"suppliers"),

            array("field_name" => "target","widget" => "text", "label" => "יעד כללי", "un_apostrophe" => true,"sign"=>"₪"),
            array("field_name" => "notes", "widget" => "textarea", "label" => "הערה","hidden"=>true),
            array("field_name" => "user_id", "widget" => "hidden"),
        ),
        //"filter"=>"area_id == []"
    ),
    "agent_target_supplier"=>array(
        "title" => "יעד לסוכן לכל ספק",
        "columns" => array(
            array("field_name" => "supplier_id","hidden"=>true),
            array("field_name" => "supplier_id", "widget" => "select","label"=>"ספק", "join_table" => "suppliers", "join_value" => "name","filter"=>true),
            array("field_name" => "target", "widget" => "text", "label" => "יעד", "un_apostrophe" => true,"sign"=>"₪"),
            array("field_name" => "period_days", "widget" => "number", "label" => "תקופה" ),
        )),
    "collection" =>//invoices
        array(
        "title" => "חשבוניות",
        "single" => "חשבונית",
        "columns" => array(
            array("field_name" => "supplier_id","widget" => "select", "join_table" => "suppliers", "join_value" => "name", "label" => "שם הספק"),
            array("field_name" => "client_id","widget" => "select", "join_table" => "clients", "join_value" => "name", "label" => "שם הלקוח"),
            array("field_name" => "obligation", "widget" => "text","un_apostrophe" => true, "label" => "חיוב","sign"=>"₪"),
            array("field_name" => "agent_id","widget" => "hidden", "join_table_from" => "clients", "join_table" => "agents", "join_value" => "name", "label" => "סוכן"),

            array("field_name" => "doc_number", "widget" => "text", "label" => "מס' חשבונית"),
            array("field_name" => "date", "widget" => "date", "label" => "תאריך"),
            array("field_name" => "payment_until", "widget" => "date", "label" => "לתשלום עד"),
/*            array("field_name" => "doc_type", "widget" => "select", "label" => "סוג חשבונית"),*/
            array("field_name" => "doc_type", "widget" => "status", "label" => "חשבונית","filter"=>true,
                "values"=>array(
                    1=>array("class"=>"done background-light-light-blue","label"=> "חיוב"),
                    2=>array("class"=>"in-treatment background-dark-green","label"=> "זיכוי"),
                )),

            array("field_name" => "payment_date", "widget" => "date", "label" => "תאריך תשלום"),
            //array("field_name" => "credit_number", "widget" => "text", "label" => "מספר כרטיס אשראי"),
            array("field_name" => "payment_type", "widget" => "select", "label" => "אופן תשלום",
                "options"=>array(
                    array("value"=>"1","text"=>"מזומן"),
                    array("value"=>"2","text"=>"כרטיס אשראי"),
                    array("value"=>"3","text"=>"המחאה"),
                    array("value"=>"4","text"=>"העברה בנקאית"),
                    array("value"=>"5","text"=>"ביט"),

                )),
            array("field_name" => "check_number", "widget" => "text", "label" => "מספר צ'ק"),
/*            array("field_name" => "agent_id", "widget" => "select", "label" => "סוכן", "join_table" => "agents", "join_value" => "user_id", "user_field" => "display_name"),//להביא מטבלת יוזר*/

        ),
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
                array("field_name" => "name","widget" => "text"),
                array("field_name" => "area_id","widget" => "select", "join_table" => "areas", "join_value" => "area", "label" => "איזור"),
            ),
        ),
    "areas" =>
        array(
            "title" => "איזורים",
            "single" => "אזור",
            "columns" => array(
                array("field_name" => "area","widget" => "text"),
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
    "payment_terms" =>
        array(
            "title" => "תנאי תשלום",
            "single" => "תנאי תשלום",
            "columns" => array(
                array("field_name" => "importance","widget" => "text"),
            )
        ),
    "mission_topic" =>
        array(
            "title" => "נושא משימה",
            "single" => "נושא המשימה",
            "columns" => array(
                array("field_name" => "area","widget" => "text"),
            )
        ),
    /*"specials" =>
        array(
            "title" => "הטבות ומבצעים",
            "single" => "מבצע",
            "columns" => array(
                array("field_name" => "descript","widget" => "text"),
                array("field_name" => "discount","widget" => "text","un_apostrophe" => true),
            )
        ),*/
    );
?>
