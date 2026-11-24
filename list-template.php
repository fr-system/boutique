<?php /* Template Name: list */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
?>
<?php get_header();?>
<section class="page" data-single="">
    <?php //הלשוניות של הטבלאות (tables-list) נבנות בכותרת, ליד כפתור ההוספה - archive_header
    archive_header("lists",false,array("new_single"=>"חדש"));    ?>
    <div class="flex-display">
        <div class="part-80 list-area">
            <!--<table name="" class="list-table dataTable ">-->
                <?php //כאן הולך לפונקציה הזו  lists_table_rows ?>
            <!--</table>-->
        </div>
    </div>
</section>
<?php get_footer();?>