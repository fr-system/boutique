<?php
/**
 * The site's entry point.
 *
 * Loads the relevant template part,
 * the loop is executed (when needed) by the relevant template part.
 *
 * @package boutique
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

get_header();

while ( have_posts() ) :
    the_post();
    ?>

    <main id="content" <?php post_class( 'site-main' ); ?>>


        <div class="page-header">
            <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
        </div>


        <div class="page-content">
            <?php the_content(); ?>

            <?php wp_link_pages(); ?>
        </div>

        <?php comments_template(); ?>

    </main>

<?php
endwhile;


get_footer();
