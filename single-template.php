<?php /* Template Name: single */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}

?>
<?php get_header();?>
<?php
if(!isset($_GET["subject"])) wp_redirect(get_site_url());
$table_name = $_GET["subject"];

$action = $_GET["action"];

$fields_arr = BOUTIQUE_TABLES[$table_name];
$title_page = "הוספת ". $fields_arr["single"]." חדש".($fields_arr["male_female"] == "female" ? "ה":"");
$row = (object)array();
if($action == "edit") {
    $id = $_GET["id"];
    $title_page = "עדכון ". $fields_arr["single"];
    $query = get_page_query($table_name,"id" ,$id);
    $result =run_query ($query);
    if(count($result)>0){
        $row= $result[0];
    }
    //write_log(" row ".json_encode($row));
}
$previous_page = null;
if (isset($_SERVER['HTTP_REFERER'])) {
    $previous_page = $_SERVER['HTTP_REFERER'];
}
$class_form = "border-dark-gray padding-20 flex-display direction-column part-60"

?>
<section class="page single">
<div class="font-30 margin-bottom-20"><?php echo $title_page ?></div>
    <div class="flex-display space-between">
        <?php if($table_name == "products"){
            ?>
        <form novalidate="" id="product-form" class=" <?php echo $class_form?>" method="post" enctype="multipart/form-data"  onsubmit="required()">
            <input type="hidden" name="save_product" value="" />
            <?php
        }
        else{?>
        <form class="site_form <?php echo $class_form?> " novalidate="" data-success='reload_page' data-failed='show_error_messages'>
            <?php } ?>

            <input type="hidden" name="form_func" value="build_query_boutique" />
            <input type="hidden" name="table_name" value="<?php echo $table_name ?>" />
            <input type="hidden" name="id" value="<?php echo $id ?>" />
            <input type="hidden" name="previous_page" value="<?php echo $previous_page ?>" />
            <div class="grid-display cols-2 margin-bottom-40">
                <?php
                foreach($fields_arr["columns"] as $column){
                    if(!isset($column["widget"])){continue;}
                    ?>
                    <div class="input-label flex-display <?php echo $column["widget"] != "textarea" ? 'align-center' :'stretch'?> ">
                        <?php if (isset($column["label"])){?>
                            <label class="bold" for="<?= $column["field_name"] ?>"><?= $column["label"].":"?></label>
                        <?php }?>
                        <?php
                        $field_name = $column["field_name"];
                        echo create_input($column,isset($row->$field_name) ? $row->$field_name :""); ?>
                    </div>
                <?php } ?>

            </div>
            <div class="buttons flex-display align-self-center">
                <button type="post" class="save background-gold bold font-18">שמור</button>
                <?php if($previous_page) { ?>
                    <a href="<?php echo $previous_page?>" class="cancel button background-white gold bold font-18">בטל</a>
                <?php } ?>
            </div>

        </form>
         <?php if($table_name == "products"){
             if(isset($row->image_id) && !empty($row->image_id))
                 //write_log("fff".$row->image_id);
                 ?>
                <img class="part-30 protuct-image" src="<?php echo wp_get_attachment_url($row->image_id)?>"/>
         <?php } ?>
    </div>
</section>

<?php

?>
