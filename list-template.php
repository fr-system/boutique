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
               echo "<li data-list-name='{$list_name}'>".$B_LIST["title"]."</li>";
            }
            ?>
        </ul>

    <table name="" class="list-table">

    </table>
</section>


<form class="modal fade site_form" id="edit-list"  tabindex='-1' role="dialog">
    <input type="hidden" name="form_func" value="build_query_boutique" />
    <input type="hidden" name="table_name" value="" />
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title grow" >123</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="סגור">
                    </button>
                </div>
                <div class="modal-body border-dark-gray padding-20 flex-display direction-column margin-20">
                    <input type="hidden" name="id"/>
                    <span class="input-label flex-display align-center">
                        <label class="bold" for="text"></label>
                        <input type="text" id="text" name="text" class="font-17 grow">
                    </span>
                    <span class="input-label flex-display align-center">
                        <label class="bold" for="area-list"></label>
                        <select name="area-list" id="area-list" class="font-17 grow"></select>
                    </span>
                </div>
                <div class="modal-footer">
                    <button type="post" class="save background-gold bold font-18">שמור</button>
                    <button type="button" data-bs-dismiss="modal" class=" font-18">בטל</button>

                </div>
            </div>
        </div>
</form>



