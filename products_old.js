
function openPopupAddOrderProduct(result){
    jQuery(".popup-body").empty();
    jQuery(".popup-body").html(result.html);

    jQuery('.popup-body #search').on("keyup",function(event){
        var text = jQuery(this).val();
        //searchProducts(text,".popup-body .product");
        searchElements(text, ".popup-body .products-gallery.catalog .product",".product-name");
    });

    jQuery('.popup-body button.order-product').click(function () {
        var element = jQuery(this).parent().parent().clone();
        var id = jQuery(this).parent().parent().data("id");

        element.addClass("current");
        element.find("a, .order-product").addClass("hidden");
        var key = 0;
        jQuery.each(jQuery(".page .products-gallery .product"), function (k) {
            var product = jQuery(this);
            var name = product.find('[name*="][product_id]"]').attr('name');
            if(name) {
                var a = parseInt(name.substring(name.indexOf("[") + 1, name.indexOf("]")))
                if (a > key) {
                    key = a;
                }
            }
        })

        var order_id = jQuery(".page.single input.orders_id[name=id]").val();
        key++;
        element.prepend(
            '<input type="hidden" name="products['+key+'][id]" value="">'+//id של השורה של מוצר_הזמנה
            '<input type="hidden" name="products['+key+'][order_id]" value="'+order_id+'">' +
            '<input type="hidden" name="products['+key+'][product_id]" value="'+id+'">');
        //element.find("input[name*=\"][product_id]").prop("name","products["+key+"][product_id]");
        //element.find("input[name*=\"][count]").prop("name","products["+key+"][count]");

        element.find('.plus-minus-count input').prop("name","products["+key+"][count]");
        element.find('.price-part.discount-percent').prop("name","products["+key+"][discount_percent]");
        element.find('.price-part.bonus').prop("name","products["+key+"][bonus]");
        element.find('.price-part.unit-price').prop("name","products["+key+"][order_price]");
        element.find('.price-part.unit-price').autoNumeric('init');
        element.find('.calculated-price-input').prop("name","products["+key+"][total]");

        jQuery('input[name=dirty]').val("1");
        jQuery(".add-order-product").after(element);

        var total_order = parseInt(jQuery(".page.single input[name=total]").autoNumeric('get')||0);
        total_order+=parseInt( element.find('.calculaded-price').text()||0);
        jQuery("input[name=total]").autoNumeric('set', total_order);

        jQuery(".page .products-gallery .product .plus-minus-count span.pointer").click(function (e) {
            plusMinusCountProduct(this);
        })
        registerToCalculatePrice();
        //jQuery(".products").prepend(element);
        closePopup();

    })
    openPopup();

}

/*function fillProductsLastOrder(result) {
    jQuery(".add-order-product").after(jQuery(result.products));
    jQuery(".products-gallery .products-last-order").hide();
}*/

jQuery('.add-order-product svg').click(function () {
    if(jQuery('.grid-display select[name=client_id]').val()) {

        var postData = [
            {name: "action", value: "view_catalog_gallery_ajax"},
            {name: "client_id", value: jQuery('.grid-display select[name=client_id]').val()},
        ];
        call_ajax_function(postData, "openPopupAddOrderProduct");
    }
    else{
        var $form = jQuery('.page.single form.site_form');
        $form.valid();
    }
})

/*jQuery(".products-gallery .products-last-order").click(function (){
    var client_id = jQuery(".page.single select[name=client_id]").val();
    if(client_id) {
        var postData = [
            {name: "action", value: "get_products_last_order"},
            {name: "client_id", value: client_id}
        ];
        call_ajax_function(postData, "fillProductsLastOrder");
    }
})*/



