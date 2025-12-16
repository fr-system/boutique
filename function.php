
<?php
/**
 * Theme functions and definitions
 *
 * @package boutique
 */

require_once dirname(__FILE__) . "/assets/lists.php";
//require_once dirname(__FILE__) . "/users.php";
require_once dirname(__FILE__) . "/queries.php";


function rotenberg_enqueue_scripts()
{
    wp_enqueue_style(
        'rotenberg',
        get_template_directory_uri() . '/style.css',
        [],
        '1.0.0'
    );

    wp_enqueue_script('jquery');

    wp_register_script('script', get_stylesheet_directory_uri() . '/script.js');
    wp_enqueue_script('script');

    wp_enqueue_media();
}
add_action('wp_enqueue_scripts', 'rotenberg_enqueue_scripts', 98);

function send_site_forms()
{
    $func_name = 'function_' . $_POST['form_func'];
    $func_name($_POST);
    echo json_encode(array(
        'status' => 'success',
    ));

    die();
}

function fixXSS($str)
{
    return htmlspecialchars($str);
}



function write_log($text)
{
    $log  = date("d-m-Y h:i:s").' ' . $text.' '.PHP_EOL;

    file_put_contents( ABSPATH . '/wp-content/themes/rotenberg/assets/mate_log.log', $log, FILE_APPEND);
}

?>