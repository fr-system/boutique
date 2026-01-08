<?php /* Template Name: archive */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
?>
<?php //get_header();?>

<main id="main" class="site-main " role="main">
    <section class="">
        <table name="12" class="table">
            <thead>
            <?
            $table_name= $_GET["subject"];
            $result = array();
            print_r (FIELDS[$table_name] );
            write_log ("list ".json_encode (FIELDS[$table_name]));
            foreach(FIELDS[$table_name] as $field)
            ?>
                <td><?= $field["text"]?></td>
            </thead>
            <? foreach($result as $row){?>
                <tr class="">
                    <td> <?= $row["group_id"]; ?> </td>
                </tr>
            <? }?>
        </table>

    </section>
</main>

