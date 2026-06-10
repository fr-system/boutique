<?php /* Template Name: home page */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
if(is_supplier()){
    wp_redirect(get_site_url()."/archive/?subject=collection");
}
?>

<?php get_header();
?>
<section class="page flex-display direction-column">
    <?php if(is_manager()){?>
    <div class="part-30">
        <div class="font-30 bold margin-bottom-30">פעולות מהירות</div>
        <div class="grid-display cols-4 margin-bottom-20">
            <?php $actions_list = array(
                array("text"=>"הזמנה חדשה","type"=>"single","subject"=>"orders","action"=>"new"),
                array("text"=>"ספק חדש","type"=>"single","subject"=>"suppliers","action"=>"new"),
                array("text"=>"מוצר חדש","type"=>"single","subject"=>"products","action"=>"new"),
                array("text"=>"לקוח חדש","type"=>"single","subject"=>"clients","action"=>"new"),
                array("text"=>"קטלוג","type"=>"archive","subject"=>"products"),
            );
            foreach ($actions_list as $act){
                //write_log("gg ". json_encode($action));
                ?>
                <a href="<?php echo $act["type"].'?subject='.$act["subject"].(isset($act["action"]) ? '&action='.$act["action"]:'') ?>" class="quick-action flex-display align-center border-dark-gray pointer not-link">
                    <?php echo get_svg($act["subject"],(isset($act["action"]) ? $act["action"] :null),false); ?>
                    <div><?php echo $act["text"] ?></div>
                </a>
            <?php } ?>
        </div>
    </div>
    <?php }?>
    <div class=" part-30 flex-display space-between">
        <div class="part-45">
            <div class="flex-display space-between">
                <div class="font-20 bold">סיכום גבייה</div>
                <a class="not-link font-15 dark-green" href="/archive/?subject=collection">לפירוט המלא -></a>
            </div>
            <div class="graphs-charts quick-action border-dark-gray">
                <div class="flex-display direction-column">
                    <?php
                    $filters = array(array("filter_field" => "payment_date","filter_type"=>"not_null"));
                    $filters[] = array("filter_field" => "doc_type","filter_value"=>"1");
                    $result = get_data_table("collection",$filters);
                    $sum = array_reduce(
                        $result,
                        fn($carry, $item) => $carry + $item->obligation,
                        0
                    );
                    ?>
                    <div class="font-20 gold bold"></div>
                    <div class="font-17">חובות פתוחים</div>
                </div>
            </div>
        </div>
        <div class="part-45">
            <div class="flex-display space-between">
                <div class="font-20 bold">גרף מכירות</div>
                <div class="font-15 dark-green" >לפירוט המלא -></div>
            </div>
            <div class="graphs-charts quick-action border-dark-gray"></div>
        </div>
    </div>
    <div class="part-30">
        <div class="flex-display space-between">
            <div class="font-20 bold">משימות פתוחות</div>
            <div class="font-15 dark-green" >לפירוט המלא -></div>
        </div>
        <div class="graphs-charts quick-action border-dark-gray">
            <?php
            $filters = array(array("filter_field" => "status_id", "filter_value" =>1,"filter_type"=>"!="));
            $result = get_data_table("tasks",$filters);
            foreach ($result as $task){
                //write_log("row ".json_encode( $task));
                ?>
                <div class="font-18"><?php echo $task->subject ?><span class="margin-before-10 font-15"><?php echo $task->details ?></span></div>
                    <?php
            }
            ?>
        </div>
    </div>
</section>
<?php get_footer();?>