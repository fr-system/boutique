<?php
/**
 * The template for displaying the header
 *
 * This is the template that displays all of the <head> section, opens the <body> tag and adds the site's header.
 *
 * @package boutique
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Assistant:wght@200..800&family=Noto+Sans+Hebrew:wght@100..900&display=swap" rel="stylesheet">
</head>

<div class="slider-message">
    <h1 class="font-50 blue"></h1>
    <div class="secondary-text font-30 blue"></div>
</div>
<body <?php body_class(); ?>

<a class="skip-link screen-reader-text" href="#content"></a>
<?php if(is_user_logged_in()){ ?>
<header class="boutique-header flex-display start align-center font-17">
    <img class="part-10 logo-header" src="<?= get_stylesheet_directory_uri()."/assets/images/logo_header.png"?>">
    <div class="search-site grow">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
            <g clip-path="url(#clip0_27_100)">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.6875 10.5C6.31949 10.5 6.94528 10.3755 7.52916 10.1337C8.11304 9.89182 8.64357 9.53733 9.09045 9.09045C9.53733 8.64357 9.89182 8.11304 10.1337 7.52916C10.3755 6.94528 10.5 6.31949 10.5 5.6875C10.5 5.05551 10.3755 4.42972 10.1337 3.84584C9.89182 3.26196 9.53733 2.73143 9.09045 2.28455C8.64357 1.83767 8.11304 1.48318 7.52916 1.24133C6.94528 0.999479 6.31949 0.875 5.6875 0.875C4.41115 0.875 3.18707 1.38203 2.28455 2.28455C1.38203 3.18707 0.875 4.41115 0.875 5.6875C0.875 6.96385 1.38203 8.18793 2.28455 9.09045C3.18707 9.99297 4.41115 10.5 5.6875 10.5V10.5ZM11.375 5.6875C11.375 7.19592 10.7758 8.64256 9.70917 9.70917C8.64256 10.7758 7.19592 11.375 5.6875 11.375C4.17908 11.375 2.73244 10.7758 1.66583 9.70917C0.599217 8.64256 0 7.19592 0 5.6875C0 4.17908 0.599217 2.73244 1.66583 1.66583C2.73244 0.599217 4.17908 0 5.6875 0C7.19592 0 8.64256 0.599217 9.70917 1.66583C10.7758 2.73244 11.375 4.17908 11.375 5.6875V5.6875Z" fill="black"/>
                <path d="M9.33337 10.5575C9.35962 10.5925 9.38762 10.6257 9.41912 10.6581L12.7879 14.0268C12.9519 14.191 13.1745 14.2833 13.4066 14.2834C13.6387 14.2835 13.8614 14.1913 14.0256 14.0273C14.1897 13.8632 14.282 13.6406 14.2821 13.4085C14.2822 13.1764 14.1901 12.9538 14.026 12.7896L10.6572 9.42083C10.626 9.38917 10.5923 9.35992 10.5566 9.33333C10.2134 9.80135 9.8009 10.2144 9.33337 10.5583V10.5575Z" fill="black"/>
            </g>
            <defs>
                <clipPath id="clip0_27_100">
                    <rect width="14" height="14" fill="white"/>
                </clipPath>
            </defs>
        </svg>
        <input id="search_site" type="search" placeholder="חיפוש">
    </div>

    <div class="user-logged pointer part-10 flex-display space-between align-center border-dark-gray">
        <img class="user-logo" src="<?=wp_get_attachment_url(9)?>">
        <span class="user-name"><?= get_user_display_name();?></span>
        <svg xmlns="http://www.w3.org/2000/svg" width="7" height="4" viewBox="0 0 7 4" fill="none">
            <path d="M6.13282 0L3.5 2.41146L0.86718 0L0 0.79427L3.5 4L7 0.79427L6.13282 0Z" fill="black"/>
        </svg>
    </div>
    <div class="part-5"></div>
</header>
<?php } ?>
<main id="main" class="site-main flex-display" role="main">
      <?php if(is_user_logged_in()) { get_side_menu(); }?>