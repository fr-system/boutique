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
<section class="page part-80" data-single="<?php echo $page_info['single']?>">
    <?php
    $filters = array();
    $add_text = null;
    $client_id = null;
    $blocked = null;
    $lastKey = array_key_last($_GET);

    if ($lastKey != "subject" && ($table_name == "orders" || $table_name == "tasks")) {
        $query = "SELECT name,blocked from {$wpdb->prefix}clients WHERE " .$lastKey."=".$_GET[$lastKey];
        $result = run_query ($query);
        $add_text =" של ". $result[0]->name;
        $filters[]=array("filter_field" => "client_id", "filter_value"=>$_GET[$lastKey]);
        $client_id = $_GET[$lastKey];
        $blocked = $result[0]->blocked;
    }
    if($table_name == "collection") {
        if (isset($_GET["payed"])) {
            $filters[] = array("filter_field" => "payment_date", "filter_type" => "not_null");
            $add_text = " ששולמו";
        } else {
            $filters[] = array("filter_field" => "payment_date", "filter_type" => "null");
        }
        if(is_supplier()){
            $filters[] = array("filter_field" => "supplier_id", "filter_value" => get_id_by_user() );
        }
    }
    $new_single = $page_info['single']. " חדש" . (isset($page_info["male_female"]) && $page_info["male_female"] == "female" ? "ה" : "");
    $attr = array( "add_text"=>$add_text, "client_id" =>$client_id,"blocked"=>$blocked,"new_single"=>$new_single);
    echo archive_header($table_name,false,$attr);

    if(is_agent() ){
        $agent_id = get_id_by_user();
        if($table_name == "clients" ) {
            $filters[] = array("filter_field" => "agent_id", "filter_value" =>$agent_id);
        }
        if($table_name == "tasks"){
            $filters[] = array("filter_table"=>"tasks", "filter_field" => "agent_id", "filter_value" => $agent_id );
        }

        if($table_name == "orders" || $table_name == "collection") {
            $filters[] = array("filter_table"=>"clients", "filter_field" => "agent_id", "filter_value" => $agent_id);
        }
    }

    $result = get_data_table($table_name,$filters);
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

