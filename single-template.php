<?php /* Template Name: single */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
?>
<?php get_header();?>
<?php
if(!isset($_GET["subject"])) wp_redirect(get_site_url());;
$table_name = $_GET["subject"];
/*$query = get_page_query($table_name);
// print_r ("aa ".$query."<br>");
$result =run_query ($query);*/

$fields_arr = FIELDS[$table_name];
?>
<section class="page">

</section>

<?php

?>
