<?php /* Template Name: single */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}

?>
<?php get_header();?>
<?php
if(!isset($_GET["subject"])) wp_redirect(get_site_url());
$table_name = $_GET["subject"];

$action = $_GET["action"];
$readonly ="";
$page_info = BOUTIQUE_TABLES[$table_name];
if($action == "new") {
    $title_page = "הוספת " . $page_info["single"] . " חדש" . ($page_info["male_female"] == "female" ? "ה" : "");
    $row = (object)array();
    if(isset($_GET["client_id"])){
        $row->client_id = $_GET["client_id"];
    }

    if($table_name == "orders"){
        $row->order_date = date('Y-m-d');
        $row->user_opens = get_current_user_id();
    }
    if($table_name == "tasks"){
        $row->open_date = date('Y-m-d');
        //אולי צריך לשמור מי פתח את המשימה???
    }
}
else{//edit || readonly
    $id = $_GET["id"];
    $title_page = "עדכון ". $page_info["single"];
    $filters=array(array("filter_field" => "id", "filter_value"=>$id));
    $result = get_page_data($table_name,$filters);
    if(count($result)>0){
        $row = $result[0];
        // write_log("row ".json_encode($row));
    }

    if($table_name == "agents"){
        $user_info = get_userdata($row->user_id);
        if ($user_info) {
            $row->display_name = $user_info->display_name;
            $row->user_email = $user_info->user_email;
        }
    }

    if($action == "edit") {    }
    if($action == "readonly") {
        $readonly = "readonly";
    }
}


$previous_page = null;
if (isset($_SERVER['HTTP_REFERER'])) {
    $previous_page = $_SERVER['HTTP_REFERER'];
}
$class_form = "border-dark-gray padding-20 flex-display direction-column ";
if($table_name != "orders"){
    $class_form.="part-60 ";
}
else{
    $class_form.="part-80 ";
}

?>

<section class="page single" data-single="<?php echo $page_info['single']?>">
<div class="font-30 margin-bottom-20 bold"><?php echo $title_page ?></div>
    <div class="flex-display space-between">
        <input type="hidden" name="dirty" value="" />
        <?php if($table_name == "products"){
            ?>
            <form novalidate="" id="product-form" class=" <?php echo $class_form?>" method="post" enctype="multipart/form-data"  <!--onsubmit="required()-->">
                <input type="hidden" name="save_product" value="" />
        <?php }
        else{?>
            <form class="site_form <?php echo $class_form?> " novalidate="" data-success='reload_page' data-failed='show_error_messages'>
        <?php } ?>
            <div id="form_error_msgs_container" class="margin-bottom-20"></div>
            <input type="hidden" name="form_func" value="build_query_boutique" />
            <input type="hidden" name="table_name" value="<?php echo $table_name ?>" />
            <input type="hidden" name="id" value="<?php echo $id ?>" />
            <input type="hidden" name="previous_page" value="<?php echo $previous_page ?>" />
            <input type="hidden" name="action" value="<?php echo $id ?>" />

            <?php
            //write_log("row ".json_encode($row));
            get_single_view($table_name,$row,$readonly); ?>
            <div class="buttons flex-display align-self-center">
                <button type="post" class="save background-gold flex-display center align-center bold font-18">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M12.1875 15.75V12.1763C12.1873 12.0195 12.1562 11.8644 12.096 11.7197C12.0358 11.575 11.9477 11.4436 11.8366 11.3329C11.7256 11.2223 11.5939 11.1347 11.4489 11.075C11.304 11.0154 11.1487 10.9849 10.992 10.9853H7.008C6.85127 10.9849 6.696 11.0154 6.55108 11.075C6.40615 11.1347 6.2744 11.2223 6.16337 11.3329C6.05233 11.4436 5.96419 11.575 5.90399 11.7197C5.84379 11.8644 5.8127 12.0195 5.8125 12.1763V15.75M12.1875 2.46376V4.23526C12.1873 4.39199 12.1562 4.54714 12.096 4.69184C12.0358 4.83654 11.9477 4.96796 11.8366 5.07857C11.7256 5.18919 11.5939 5.27683 11.4489 5.33649C11.304 5.39615 11.1487 5.42665 10.992 5.42626H7.008C6.85127 5.42665 6.696 5.39615 6.55108 5.33649C6.40615 5.27683 6.2744 5.18919 6.16337 5.07857C6.05233 4.96796 5.96419 4.83654 5.90399 4.69184C5.84379 4.54714 5.8127 4.39199 5.8125 4.23526V2.25001M12.1875 2.46376C11.8765 2.3229 11.5389 2.25002 11.1975 2.25001H5.8125M12.1875 2.46376C12.4455 2.58076 12.6833 2.74426 12.888 2.94751L14.6745 4.72876C14.8963 4.9495 15.0724 5.21186 15.1926 5.5008C15.3128 5.78974 15.3748 6.09956 15.375 6.41251V13.3665C15.3748 13.68 15.3128 13.9904 15.1925 14.28C15.0722 14.5695 14.8961 14.8325 14.6741 15.0538C14.4521 15.2752 14.1886 15.4507 13.8987 15.5701C13.6088 15.6896 13.2983 15.7507 12.9847 15.75H5.016C4.70248 15.7508 4.39188 15.6898 4.10198 15.5704C3.81207 15.451 3.54854 15.2757 3.32647 15.0544C3.10439 14.8331 2.92813 14.5701 2.80777 14.2807C2.6874 13.9912 2.62529 13.6808 2.625 13.3673V4.63201C2.62539 4.31855 2.68757 4.00825 2.80798 3.71884C2.92839 3.42943 3.10467 3.1666 3.32673 2.94537C3.5488 2.72414 3.81229 2.54886 4.10215 2.42954C4.39201 2.31023 4.70255 2.24922 5.016 2.25001H5.8125" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>שמור</span>
                </button>
                <?php if($previous_page) { ?>
                    <a href="<?php echo $previous_page?>" class="cancel flex-display center button background-white gold bold font-18">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <circle cx="8.5" cy="9.5" r="6" stroke="#E2B252"/>
                            <g clip-path="url(#clip0_650_945)">
                                <path d="M9.1767 9.49997L10.4867 8.18993C10.6736 8.00306 10.6736 7.70008 10.4867 7.5132C10.2999 7.32633 9.99689 7.32633 9.81001 7.5132L8.49997 8.82324L7.18993 7.5132C7.00306 7.32633 6.70008 7.32633 6.5132 7.5132C6.32633 7.70008 6.32633 8.00306 6.5132 8.18993L7.82324 9.49997L6.5132 10.81C6.32633 10.9969 6.32633 11.2999 6.5132 11.4867C6.60664 11.5802 6.7291 11.6269 6.85156 11.6269C6.97402 11.6269 7.09649 11.5802 7.18992 11.4867L8.49997 10.1767L9.81001 11.4867C9.90345 11.5802 10.0259 11.6269 10.1484 11.6269C10.2708 11.6269 10.3933 11.5802 10.4867 11.4867C10.6736 11.2999 10.6736 10.9969 10.4867 10.81L9.1767 9.49997Z" fill="#E2B252"/>
                            </g>
                            <defs>
                                <clipPath id="clip0_650_945">
                                    <rect width="5" height="5" fill="white" transform="translate(6 7)"/>
                                </clipPath>
                            </defs>
                        </svg>
                        <span>בטל</span>

                    </a>
                <?php }
                if($action != "new") {?>
                    <a data-bs-toggle="modal" href="#bout-massage" class=" flex-display center button background-dark-green bold font-18" role="button" data-action="remove">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <path d="M2.3335 4.08333H11.6668M5.8335 6.41667V9.91667M8.16683 6.41667V9.91667M2.91683 4.08333L3.50016 11.0833C3.50016 11.3928 3.62308 11.6895 3.84187 11.9083C4.06066 12.1271 4.35741 12.25 4.66683 12.25H9.3335C9.64292 12.25 9.93966 12.1271 10.1585 11.9083C10.3772 11.6895 10.5002 11.3928 10.5002 11.0833L11.0835 4.08333M5.25016 4.08333V2.33333C5.25016 2.17862 5.31162 2.03025 5.42102 1.92085C5.53041 1.81146 5.67879 1.75 5.8335 1.75H8.16683C8.32154 1.75 8.46991 1.81146 8.57931 1.92085C8.6887 2.03025 8.75016 2.17862 8.75016 2.33333V4.08333" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>מחיקת <?php echo $page_info["single"]; ?></span>
                    </a>
                    <!--<button type="button"  class="flex-display center align-center background-dark-green bold font-18">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <path d="M2.3335 4.08333H11.6668M5.8335 6.41667V9.91667M8.16683 6.41667V9.91667M2.91683 4.08333L3.50016 11.0833C3.50016 11.3928 3.62308 11.6895 3.84187 11.9083C4.06066 12.1271 4.35741 12.25 4.66683 12.25H9.3335C9.64292 12.25 9.93966 12.1271 10.1585 11.9083C10.3772 11.6895 10.5002 11.3928 10.5002 11.0833L11.0835 4.08333M5.25016 4.08333V2.33333C5.25016 2.17862 5.31162 2.03025 5.42102 1.92085C5.53041 1.81146 5.67879 1.75 5.8335 1.75H8.16683C8.32154 1.75 8.46991 1.81146 8.57931 1.92085C8.6887 2.03025 8.75016 2.17862 8.75016 2.33333V4.08333" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>מחיקת <?php /*echo $page_info["single"]; */?></span>
                    </button>-->
                <?php }
                 if($table_name == "orders" ){
                     if(!isset($row->done) || !$row->done){
                        ?><button type="button" class="order-confirmation flex-display center align-center background-white dark-green bold font-18">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M10.8474 7.14639C10.9411 7.24016 10.9937 7.36731 10.9937 7.49989C10.9937 7.63248 10.9411 7.75963 10.8474 7.85339L8.09736 10.6034C8.00359 10.6971 7.87644 10.7498 7.74386 10.7498C7.61127 10.7498 7.48412 10.6971 7.39036 10.6034L6.14036 9.35339C6.04928 9.25909 5.99888 9.13279 6.00002 9.00169C6.00116 8.87059 6.05374 8.74519 6.14645 8.65248C6.23915 8.55978 6.36456 8.50719 6.49566 8.50606C6.62675 8.50492 6.75305 8.55531 6.84736 8.64639L7.74386 9.54289L10.1404 7.14639C10.2341 7.05266 10.3613 7 10.4939 7C10.6264 7 10.7536 7.05266 10.8474 7.14639Z" fill="#1A7870"/>
                            <circle cx="8.5" cy="8.5" r="6" stroke="#1A7870"/>
                        </svg>
                        <span>אישור <?php echo $page_info["single"]; ?></span>
                        </button>
                        <?php
                    }
                }
                ?>
            </div>

        </form>
         <?php if($table_name == "products"){
                 $class=(!isset($row->image_id) || empty($row->image_id)) ? "hidden": "";
                 ?>
                <img class="part-30 protuct-image <?php echo $class?>"   src="<?php echo wp_get_attachment_url($row->image_id)?>"/>
         <?php }
         else if($table_name == "tasks" && $action == "edit") {?>
             <div class="chat-area border-dark-gray padding-20 part-30">
                     <div class="font-25 margin-bottom-20">צ’אט משימה</div>

                    <div class="chat-list">
                        <?php
                        $query = "SELECT * FROM chat where task_id = ".$id;
                        $chat_list  = run_query($query);

                        foreach($chat_list as $message)
                        {
                            $user_info = get_userdata($message->user_id);
                            ?>
                            <div class="input-label flex-display space-between">
                                <div class="part-20">
                                    <img class="user-logo" src="<?= wp_get_attachment_url(9); ?>"/>
                                </div>
                                <div class="text part-50 text-center"><?= $message->text?></div>
                                <div class="date text-left part-20 font-12">
                                    <div><?=  date('d/m/y', strtotime($message->date))?></div>
                                    <div><?=  date('H:i', strtotime($message->date))?></div>
                                </div>
                            </div>
                        <?php }?>
                    </div>
                    <div class="new-chat input-label flex-display space-between ">
                        <div class="part-20"><img class="user-logo" src="<?= wp_get_attachment_url(9); ?>"></div>
                        <div class="text part-50"><input class="text-center" id="newChat" type="text" placeholder="הכנס הודעה חדשה"/></div>
                        <div class="date text-left part-20"></div>
                    </div>
            </div>
         <?php } ?>
    </div>
</section>


