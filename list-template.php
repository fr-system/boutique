<?php /* Template Name: list */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
?>
<?php get_header();?>
<section class="page" data-single="">
    <?php
    $archive_actions = archive_header("lists",false,array("new_single"=>"חדש"));
    echo $archive_actions;
    ?>
        <ul id="list" class="tables-list font-17 grow"><?php
            foreach (BOUTIQUE_LISTS as $list_name => $B_LIST){
               echo "<li class='pointer' data-list-name='{$list_name}'>{$B_LIST["title"]}</li>";
            }
            ?>
        </ul>
    <div class="flex-display">
        <div class="part-80 list-area">
            <!--<table name="" class="list-table dataTable ">-->
                <?php //כאן הולך לפונקציה הזו  lists_table_rows ?>
            <!--</table>-->
        </div>
    </div>
</section>
<?php get_footer();?>