<?php /* Template Name: list */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
?>
<?php get_header();?>
<section class="page" data-single="שורה">
    <?php
    $archive_actions = archive_header("lists",false,array("new_single"=>"חדש"));
    echo $archive_actions;
    ?>
        <ul id="list" class="tables-list font-17 grow"><?php
            //echo "<option value=''></option>";
            foreach (BOUTIQUE_LISTS as $list_name => $B_LIST){
               echo "<li class='pointer' data-list-name='{$list_name}'>".$B_LIST["title"]."</li>";
            }
            ?>
        </ul>

    <table name="" class="list-table">

    </table>
</section>
<?php get_footer();?>