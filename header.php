<?php
/**
* The template for displaying the header
*
* This is the template that displays all of the <head> section, opens the <body> tag and adds the site's header.
*
* @package HelloElementor
*/

if ( ! defined( 'ABSPATH' ) ) {
exit; // Exit if accessed directly.
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <?php $viewport_content = apply_filters( 'hello_elementor_viewport_content', 'width=device-width, initial-scale=1' ); ?>
    <meta name="viewport" content="<?php echo esc_attr( $viewport_content ); ?>">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Assistant:wght@200..800&family=Noto+Sans+Hebrew:wght@100..900&display=swap" rel="stylesheet">
</head>
<div class="tooltip hidden font-15"></div>

<div class="slider-message">
    <h1 class="font-50 blue"></h1>
    <div class="secondary-text font-30 blue"></div>
</div>
<body <?php body_class(); ?>>

<a class="skip-link screen-reader-text" href="#content">
    <?php esc_html_e( 'Skip to content', 'hello-elementor' ); ?></a>

