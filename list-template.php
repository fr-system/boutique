<?php /* Template Name: list */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
?>
<?php get_header();?>
<section class="page" data-single="שורה">
    <?php
    $archive_actions = view_archive_actions("lists",false);
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


<form class="modal fade site_form" id="edit-list"  tabindex='-1' role="dialog" data-success='getTableAjaxData' data-failed='show_error_messages'>
    <input type="hidden" name="form_func" value="build_query_boutique" />
    <input type="hidden" name="table_name" value="" />
    <input type="hidden" name="id" value="" />
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title grow" >123</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="סגור">
                    </button>
                </div>
                <div class="modal-body border-dark-gray padding-20 flex-display direction-column margin-20">

                </div>
                <div class="modal-footer">
                    <button type="post" class="save background-gold bold font-18">שמור</button>
                    <button type="button" data-bs-dismiss="modal" class="cancel button background-white gold bold font-18">בטל</button>

                </div>
            </div>
        </div>
</form>

