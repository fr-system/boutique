<?php

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
            array("field_name" => "obligo", "un_apostrophe" => true, "widget" => "text", "label" => "תקרת חוב","sign"=>"₪"),
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
            array("field_name" => "price", "type" => "float", "widget" => "text","label"=>"מחיר", "un_apostrophe" => true,"sign"=>"₪",
                "popup_button"=>array("label"=>"","target_modal"=>"update_client_price","tooltip"=>"מחיר מיוחד ללקוח",
                    "svg"=>
                        '<svg xmlns="http://www.w3.org/2000/svg" width="25" height="29" viewBox="0 0 25 29" fill="none">
<path d="M9.375 16.5C10.6182 16.5 11.8105 16.0061 12.6896 15.1271C13.5686 14.248 14.0625 13.0557 14.0625 11.8125C14.0625 10.5693 13.5686 9.37701 12.6896 8.49794C11.8105 7.61886 10.6182 7.125 9.375 7.125C8.1318 7.125 6.93951 7.61886 6.06044 8.49794C5.18136 9.37701 4.6875 10.5693 4.6875 11.8125C4.6875 13.0557 5.18136 14.248 6.06044 15.1271C6.93951 16.0061 8.1318 16.5 9.375 16.5ZM12.5 11.8125C12.5 12.6413 12.1708 13.4362 11.5847 14.0222C10.9987 14.6083 10.2038 14.9375 9.375 14.9375C8.5462 14.9375 7.75134 14.6083 7.16529 14.0222C6.57924 13.4362 6.25 12.6413 6.25 11.8125C6.25 10.9837 6.57924 10.1888 7.16529 9.60279C7.75134 9.01674 8.5462 8.6875 9.375 8.6875C10.2038 8.6875 10.9987 9.01674 11.5847 9.60279C12.1708 10.1888 12.5 10.9837 12.5 11.8125ZM18.75 24.3125C18.75 25.875 17.1875 25.875 17.1875 25.875H1.5625C1.5625 25.875 0 25.875 0 24.3125C0 22.75 1.5625 18.0625 9.375 18.0625C17.1875 18.0625 18.75 22.75 18.75 24.3125ZM17.1875 24.3062C17.1859 23.9219 16.9469 22.7656 15.8875 21.7062C14.8688 20.6875 12.9516 19.625 9.375 19.625C5.79688 19.625 3.88125 20.6875 2.8625 21.7062C1.80313 22.7656 1.56562 23.9219 1.5625 24.3062H17.1875Z" fill="black"/>
<g clip-path="url(#clip0_877_886)">
<path fill-rule="evenodd" clip-rule="evenodd" d="M16.5 14C18.99 14 21 13 21 11.5V2C21 1 19 0 16.5 0C14 0 12 1 12 2V5.5C13.17 5.99 14.17 6.81 14.88 7.85C15.37 7.946 15.91 7.999 16.5 7.999C17.81 7.999 18.9 7.738 19.68 7.313C19.7907 7.25214 19.8985 7.18606 20.003 7.115V8.495C20.003 8.73 19.816 9.095 19.201 9.431C18.605 9.756 17.691 9.995 16.501 9.995C16.263 9.995 16.0363 9.986 15.821 9.968C15.901 10.2967 15.9543 10.6333 15.981 10.978C16.1497 10.9873 16.323 10.992 16.501 10.992C17.811 10.992 18.901 10.731 19.681 10.306C19.7917 10.2451 19.8995 10.1791 20.004 10.108V11.488C20.004 11.724 19.855 12.074 19.213 12.42C18.581 12.76 17.633 12.988 16.503 12.988C16.273 12.988 16.0503 12.9787 15.835 12.96C15.7581 13.2924 15.6548 13.6181 15.526 13.934C15.8407 13.9693 16.166 13.987 16.502 13.987L16.5 14ZM19.2 6.44C19.815 6.104 20.002 5.739 20.002 5.504V4.124C19.8987 4.19467 19.791 4.26067 19.679 4.322C18.901 4.747 17.809 5.008 16.499 5.008C15.189 5.008 14.099 4.747 13.319 4.322C13.2083 4.26114 13.1005 4.19506 12.996 4.124V5.504C12.996 5.739 13.183 6.104 13.798 6.439C14.394 6.764 15.308 7.003 16.498 7.003C17.688 7.003 18.598 6.764 19.198 6.439L19.2 6.44ZM13 2.5C13 2.212 13.125 1.935 13.358 1.766C13.485 1.674 13.623 1.582 13.732 1.532C14.005 1.406 15.372 0.999 16.502 0.999C17.632 0.999 18.612 1.226 19.272 1.532C19.396 1.589 19.533 1.678 19.654 1.766C19.885 1.933 20.004 2.208 20.004 2.493V2.499C20.004 2.734 19.817 3.099 19.202 3.435C18.606 3.76 17.692 3.999 16.502 3.999C15.312 3.999 14.402 3.759 13.802 3.435C13.187 3.1 13 2.734 13 2.5Z" fill="black"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M14 11.5C14 13.99 11.99 16 9.5 16C7.01 16 5 13.99 5 11.5C5 9.01 7.01 7 9.5 7C11.99 7 14 9.01 14 11.5ZM13 11.5C13 13.43 11.43 15 9.5 15C7.57 15 6 13.43 6 11.5C6 9.57 7.57 8 9.5 8C11.43 8 13 9.57 13 11.5Z" fill="black"/>
</g>
<defs>
<clipPath id="clip0_877_886">
<rect width="16" height="16" fill="white" transform="translate(5)"/>
</clipPath>
</defs>
</svg>')
            ),
            array("field_name" => "description", "widget" => "textarea","label"=>"תיאור"),
            //array("field_name" => "count", "widget" => "number","label"=>"כמות בקבוקים בארגז","hidden"=>true),
            array("field_name" => "file_id", "widget" => "file","label"=>"דף מוצר"),
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
            array("field_name" => "agent_id", "widget" => "select", "label" => "סוכן", "join_table" => "agents", "join_value" => "name","required"=>true,"filter"=>true),//להביא מטבלת יוזר
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
    "orders" => array(
        "title" => "הזמנות",
        "single" => "הזמנה",
        "male_female" => "female",
        "columns" => array(
            array("field_name" => "client_id","widget" => "select", "join_table" => "clients", "join_value" => "name", "label" => "שם הלקוח","required"=>true),
            array("field_name" => "order_date", "widget" => "datetime-local", "label" => "תאריך הזמנה","required"=>true),
            array("field_name" => "user_opens","widget" => "hidden"),
            array("widget" => "products"),
            array("field_name" => "total","widget" => "text", "label" => "סה\"כ", "un_apostrophe" => true,"sign"=>"₪"),
            array("field_name" => "doc_type","widget" => "file", "label" => "שטר חוב"),
            array("field_name" => "notes","widget" => "textarea", "label" => "הערות","hidden"=>true),
            array("field_name" => "user_confirms","widget" => "select","locked"=>true, "label" => "מאשר ההזמנה","join_table" => "agents", "join_value" => "name"),
            array("field_name" => "done","widget" => "bool"),
        )),
    "order_products" => array(
        "title" => "הזמנות מוצרים",
        "columns" => array(
            array("field_name" => "order_id", "widget" => "number"),
            array("field_name" => "product_id", "widget" => "number", "join_table" => "products","join_values_select"=>array("name","price","image_id","supplier_id","individually","units_in_box")),
            array("field_name" => "count", "widget" => "number"),
            array("field_name" => "order_price", "widget" => "text", "un_apostrophe" => true,"sign"=>"₪"),
            array("field_name" => "bonus", "widget" => "number"),
            array("field_name" => "discount_percent", "widget" => "text", "un_apostrophe" => true,"sign"=>"%"),
            array("field_name" => "order_individual", "widget" => "bool"),
            array("field_name" => "total", "widget" => "text", "un_apostrophe" => true,"sign"=>"₪"),

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
            array("field_name" => "target","widget" => "text", "label" => "יעד כללי", "un_apostrophe" => true,"sign"=>"₪"),
            array("field_name" => "notes", "widget" => "textarea", "label" => "הערה","hidden"=>true),
            array("field_name" => "user_id", "widget" => "hidden"),
        ),
        //"filter"=>"area_id == []"
    ),

    "collection" =>//invoices
        array(
        "title" => "חשבוניות",
        "single" => "חשבונית",
        "columns" => array(
            array("field_name" => "supplier_id","widget" => "select", "join_table" => "suppliers", "join_value" => "name", "label" => "שם הספק"),
            array("field_name" => "client_id","widget" => "select", "join_table" => "clients", "join_value" => "name", "label" => "שם הלקוח"),
            array("field_name" => "obligation", "widget" => "text","un_apostrophe" => true, "label" => "חיוב","sign"=>"₪"),
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
