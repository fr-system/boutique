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
    <div class="border-dark-gray padding-20 flex-display direction-column">
        <div class="grid-display cols-2 margin-bottom-40">
            <?php
            foreach($fields_arr["columns"] as $column){
                ?>
                <div class="input-label flex-display <?php echo $column["widget"] != "textarea" ? 'align-center' :''?> ">
                    <?php if (isset($column["label"])){?>
                        <label class="bold" for="<?= $column["field_name"] ?>"><?= $column["label"].":"?></label>
                    <?php }?>
                    <?php echo create_input($column); ?>
                </div>
            <?php } ?>

        </div>
        <div class="buttons flex-display align-self-center">
            <button type="post" class="save btn background-gold bold font-18">שמור</button>
            <button type="button" class="cancel btn background-white gold bold font-18">בטל</button>

        </div>
    </div>
</section>

<?php

?>
