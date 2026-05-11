
jQuery(document).ready(function($){
    jQuery('#update_client_price').on('show.bs.modal', function (e) {
         $('#update_client_price input[name=product_id]').val($(".page.single input[name=id]").val());
        $('#update_client_price select[name=client_id]').val("");
        $('#update_client_price input[name=client_price]').val("");
    });

    jQuery('#update_client_price select[id=client_id]').on('change', function (e) {
       var tableName = jQuery('#update_client_price input[name=table_name]').val();
        var postData = [
            {name: "action", value: "get_list_ajax"},
            {name: "table_name", value: tableName },
            {name: "filter", value: "client_id = "+ $(this).val() +" And product_id = "+  $('#update_client_price input[name=product_id]').val() },
            {name: "format", value: "array"},
        ];
       call_ajax_function(postData,"fillClientPriceModal")
    });
    jQuery('.products-gallery.orders .product .price-part').on('change', function (e) {
        calculatePrice(this);

    });
});
function fillClientPriceModal(result) {
    if (result.array.length > 0) {
        jQuery('#update_client_price input[name=id]').val(result.array[0].id);
        //jQuery('#update_client_price input[name=client_id]').val(result.client_id);
        jQuery('#update_client_price input[name=client_price]').val(result.array[0].client_price);
    }
    else{
        jQuery('#update_client_price input[name=id]').val("");
        jQuery('#update_client_price input[name=client_price]').val("");
    }
}

function calculatePrice(me){
    var product = jQuery(me).closest(".product");
    var unitsInBox = parseInt(product.find('.units-in-box').val()) || 1;
    var count = parseInt(product.find('.count').val());
    var unitsInBox = parseInt(product.find('.units-in-box').val());
    var individually = parseInt(product.find('.individually').val());
    if(individually ==1){
        count=count*unitsInBox;
    }
    var unitPrice = parseInt(product.find('.unit-price').val().replace('₪',''));
    var discountPercent = parseInt(product.find('.discount-percent').val());
    var calculatedPrice = (unitPrice*count) - (unitPrice*count*discountPercent/100);
    product.find(".calculaded-price").html(calculatedPrice);
}