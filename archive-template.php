<?php /* Template Name: archive */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
?>
<?php get_header();?>
<?php
global $wpdb;
$table_name = $_GET["subject"];
$page_info  = BOUTIQUE_TABLES[$table_name];
?>
<section class="page part-80 archive-page" data-single="<?php echo $page_info['single']?>">
    <?php
    //הסינון והמיון - אותה פונקציה כמו בהדפסה ובייצוא לאקסל (get_archive_filters), כדי שיודפס מה שמוצג
    $archive = get_archive_filters($table_name, $_GET);
    $filters = $archive["filters"];
    $add_text = $archive["add_text"];
    $client_id = $archive["client_id"];
    $blocked = $archive["blocked"];

    $new_single = $page_info['single']. " חדש" . (isset($page_info["male_female"]) && $page_info["male_female"] == "female" ? "ה" : "");
    $attr = array( "add_text"=>$add_text, "client_id" =>$client_id,"blocked"=>$blocked,"new_single"=>$new_single);
    archive_header($table_name,false,$attr);

    $result = get_data_table($table_name,$filters,$archive["orderby"]);
    //write_log ('table data '.json_encode ($result));
    $user_meta = get_user_meta( get_current_user_id(), "products_view", true);
    if($table_name == "products" && $user_meta == "gallery" ){
        products_gallery($result);
    }
    else if($table_name == "specials"){
        specials_gallery($result);
    }
    else{
        if(is_manager() && $table_name == "collection" && !isset($_GET["payed"]) && count($result)>0){?>
            <a class="update-payment center button background-white gold bold font-18"  data-bs-toggle="modal" href="#payment_modal" role="button">לעדכון תשלום</a>
            <?php
        }
        $attr = array("add_text"=>$add_text);
        echo get_archive_table($table_name,$result,$attr);
    } ?>
</section>
<?php
if($table_name == "collection") {
    supplier_column_mapping_modal ();
}
?>
<?php get_footer();?>

