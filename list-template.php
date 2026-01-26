<?php /* Template Name: list */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
?>
<?php get_header();?>
<section class="page">
    <h1 class="page-title  font-30 bold">רשימות</h1>
        <select class="list-combo font-17 grow"><?php
            foreach (BOUTIQUE_LISTS as $list_name=> $B_LIST){

                //print_r ($B_LIST);
               echo "<option value='{$list_name}'>".$B_LIST["title"]."</option>";
            }
            ?>

        </select>


    <div class="archive-actions flex-display end">
        <?php //get_svg ("clients","new",false,"class-name"); ?>
        <a href="<?php echo 'single?subject='.$table_name.'&action=new' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                <circle cx="30" cy="30" r="29.5" fill="#1A7870" stroke="white"/>
                <line x1="30" y1="20" x2="30" y2="42" stroke="white" stroke-width="2"/>
                <line x1="41" y1="31" x2="19" y2="31" stroke="white" stroke-width="2"/>
            </svg>
        </a>

    </div>
    <table name="" class="list-table">
        <thead><tr class="gold">

            <th></th>
        </tr></thead>
<!--        --><?php //foreach($result as $row){
//            echo get_tr_data($table_name,$row ,"id");
//        }?>
    </table>
</section>
<?php
?>
