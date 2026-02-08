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


<body <?php body_class(); ?>>
<?php create_popup(); ?>
<div class="slider-message">
    <h1 class="font-50 blue"></h1>
    <div class="secondary-text font-30 blue"></div>
</div>
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

            <div class="popup-logout box-shadow hidden  border-dark-gray">
                <div class="flex-display direction-column space-between">
                    <div class="pointer margin-bottom-10">הפרטים שלי</div>
                    <div class="logout-button flex-display space-between pointer">
                        <div>יציאה</div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M8 14C7.83008 13.9998 7.66665 13.9347 7.54309 13.8181C7.41953 13.7015 7.34518 13.542 7.33522 13.3724C7.32526 13.2028 7.38045 13.0357 7.48951 12.9054C7.59857 12.7751 7.75327 12.6914 7.922 12.6713L8 12.6667L11.3333 12.6667C11.4966 12.6666 11.6542 12.6067 11.7763 12.4982C11.8983 12.3897 11.9762 12.2402 11.9953 12.078L12 12L12 4C12 3.83671 11.94 3.67911 11.8315 3.55709C11.723 3.43506 11.5735 3.35711 11.4113 3.338L11.3333 3.33333L8.33334 3.33333C8.16342 3.33315 7.99998 3.26808 7.87643 3.15143C7.75287 3.03479 7.67851 2.87537 7.66856 2.70574C7.6586 2.53611 7.71379 2.36908 7.82285 2.23878C7.93191 2.10848 8.08661 2.02474 8.25534 2.00467L8.33334 2L11.3333 2C11.8435 1.99997 12.3343 2.19488 12.7055 2.54486C13.0767 2.89483 13.3001 3.37341 13.33 3.88267L13.3333 4L13.3333 12C13.3334 12.5101 13.1385 13.001 12.7885 13.3722C12.4385 13.7433 11.9599 13.9667 11.4507 13.9967L11.3333 14L8 14ZM4.19534 10.3573L2.31 8.47133C2.18502 8.34631 2.11481 8.17678 2.11481 8C2.11481 7.82322 2.18502 7.65369 2.31 7.52867L4.19534 5.64267C4.32043 5.51766 4.49006 5.44747 4.66691 5.44753C4.84375 5.44759 5.01333 5.51791 5.13834 5.643C5.26334 5.76809 5.33353 5.93772 5.33347 6.11457C5.33341 6.29142 5.2631 6.46099 5.138 6.586L4.39067 7.33333L8 7.33333C8.17682 7.33333 8.34638 7.40357 8.47141 7.5286C8.59643 7.65362 8.66667 7.82319 8.66667 8C8.66667 8.17681 8.59643 8.34638 8.47141 8.4714C8.34638 8.59643 8.17682 8.66667 8 8.66667L4.39067 8.66667L5.138 9.414C5.2631 9.53901 5.33341 9.70858 5.33347 9.88543C5.33353 10.0623 5.26334 10.2319 5.13834 10.357C5.01333 10.4821 4.84375 10.5524 4.66691 10.5525C4.49006 10.5525 4.32043 10.4823 4.19534 10.3573Z" fill="black"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>


    <div class="part-5"></div>
</header>
<?php } ?>
<main id="main" class="site-main flex-display" role="main">
      <?php if(is_user_logged_in()) { get_side_menu(); }?>