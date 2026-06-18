<?php


add_action('wp_ajax_get_products_last_order', 'get_products_last_order');
function get_products_last_order()
{
    $products_str = "";
    $filters= array(array("filter_field" => "client_id", "filter_value"=>$_POST["client_id"]));
    $orders = get_data_table("orders",$filters, " id DESC");
    if(count($orders)>0){
        $row = $orders[0];
        $filters = array();
        $filters[]=array("filter_field" => "order_id", "filter_value"=>$row->id);
        $value = get_data_table("order_products",$filters);

        if($value && is_array($value)){
            $products_str = catalog_gallery($value,array("table_name"=>"orders","get_products_last_order"=>true));

        }
    }
    echo json_encode (array(
    "products" => $products_str));
    die();
}

add_action('wp_ajax_view_catalog_gallery_ajax', 'view_catalog_gallery_ajax');
function view_catalog_gallery_ajax(){//לתוח פופאפ לבחור איזה מוצר להוסיף להזמנה
//write_log("1");
    //$cache_key = 'catalog_result';

    //$result = $cached_output = get_transient($cache_key);
    /* if ($cached_output === false || !is_array ($cached_output) || get_option("catalog_result_changed")) {
         update_option("catalog_result_changed", 0);
         $result = get_page_data("products");
         set_transient ($cache_key, $result, 12 * 3600 );
     }*/
    $html = "";
    if(isset($_POST['client_id'])){
        $products = get_favorite_products($_POST['client_id']);
        if(count($products)>0) {
            //write_log("2 client: " . $_POST['client_id']);
            $html = '<div class="archive-actions flex-display space-between margin-bottom-20">
                    <h2 class="page-title font-30 bold">מוצרים מועדפים</h2>
                </div>';
            $html .= catalog_gallery($products,array("table_name"=>"order_products","class_grid"=>"favorite one-row"));
            $html .= '<div class="margin-bottom-20"></div>';
        }
    }
    $result = get_data_table("products",'','',"pc.client_id = ".$_POST['client_id']);
    //$archive_actions = view_archive_actions("products",true);
    $html .= archive_header("products",true);
    $html .= catalog_gallery($result,array("table_name"=>"order_products","class_grid"=>"catalog one-row"));

    echo json_encode (array("html" => $html));
    die();

}


?>