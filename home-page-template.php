<?php /* Template Name: home page */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
?>

<?php get_header();
?>
<section class="page">
    <div>
        <div class="font-30 bold margin-bottom-30">פעולות מהירות</div>
        <div class="grid-display cols-4 margin-bottom-20">
            <?php $actions_list = array(
                array("text"=>"הזמנה חדשה","type"=>"single","subject"=>"new_order"),
                array("text"=>"ספק חדש","type"=>"single","subject"=>"new_supplier"),
                array("text"=>"מוצר חדש","type"=>"single","subject"=>"new_product"),
                array("text"=>"לקוח חדש","type"=>"single","subject"=>"new_client"),
                array("text"=>"קטלוג","type"=>"archive","subject"=>"products"),
            );
            foreach ($actions_list as $act){
                //write_log("gg ". json_encode($action));
                ?>
                <div class="quick-action flex-display align-center border-dark-gray pointer">
                    <?php echo get_svg($act["subject"],false); ?>
                    <a href="<?php echo $act["type"].'?subject='.$act["subject"] ?>" class="not-link"><?php echo $act["text"] ?></a>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="flex-display space-between">
        <div class="part-45">
            <div class="flex-display space-between">
                <div>סיכום גבייה</div>
                <div>לפירוט המלא -></div>
            </div>
            <div class="border-dark-gray"></div>
        </div>
        <div class="part-45">
            <div class="flex-display space-between">
                <div>גרף מכירות</div>
                <div>לפירוט המלא -></div>
            </div>
            <div class="border-dark-gray"></div>
        </div>
    </div>
</section>