<?php /* Template Name: single */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
?>
<?php get_header();?>
<?php
if(!isset($_GET["subject"])) wp_redirect(get_site_url());
$table_name = $_GET["subject"];
$action = $_GET["action"];//new


$fields_arr = BOUTIQUE_TABLES[$table_name];

?>
<section class="page">
<div class="font-30 margin-bottom-20"><?php echo "הוספת ". $fields_arr["single"]." חדשה" ?></div>
    <div class="border-dark-gray grid-display cols-2 padding-20">
        <?php
        foreach($fields_arr["columns"] as $column){
            ?>
            <div class="input-label flex-display align-center">
                <?php if (isset($column["label"])){?>
                    <label class="bold" for="<?= $column["field_name"] ?>"><?= $column["label"].":"?></label>
                <?php }?>
                <?php //echo create_input($column); ?>
            </div>
        <?php } ?>

    </div>
</section>

<?php

?>
