<?php /* Template Name: archive */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
?>

<?php get_header();
?>
<main id="main" class="site-main " role="main">
    <?php  rotenberg_header();  ?>
    <section class="">
        <table name="" class="">
            <thead>
            <?
            $table_name= $_GET["subgect"];
            $result = array();
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

?>