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

$page_info = BOUTIQUE_TABLES[$table_name];
if($action == "new") {
    $title_page = "הוספת " . $page_info["single"] . " חדש" . ($page_info["male_female"] == "female" ? "ה" : "");
    $row = (object)array();

    if($table_name == "orders"){
        $row->order_date = date('Y-m-d');
        $row->user_opens = get_current_user_id();
    }
    if($table_name == "tasks"){
        $row->open_date = date('Y-m-d');
        //אולי צריך לשמור מי פתח את המשימה???
    }
}
else if($action == "edit") {
    $id = $_GET["id"];
    $title_page = "עדכון ". $page_info["single"];
    $result = get_page_data($table_name,"id" ,$id);
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
    //write_log(" row ".json_encode($row));
}
$previous_page = null;
if (isset($_SERVER['HTTP_REFERER'])) {
    $previous_page = $_SERVER['HTTP_REFERER'];
}
$class_form = "border-dark-gray padding-20 flex-display direction-column part-60"

?>

<section class="page single">
<div class="font-30 margin-bottom-20"><?php echo $title_page ?></div>
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


            <?php
            //write_log("row ".json_encode($row));
            get_single_view($table_name,$row); ?>
            <div class="buttons flex-display align-self-center">
                <button type="post" class="save background-gold bold font-18">שמור</button>
                <?php if($previous_page) { ?>
                    <a href="<?php echo $previous_page?>" class="cancel button background-white gold bold font-18">בטל</a>
                <?php } ?>
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
                                <div class="date text-left part-20">
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


