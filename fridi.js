
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
    registerToCalculatePrice();

    jQuery("svg.file-upload").on("click",function (){
        $(this).closest('form').find('.input-label').removeClass('hidden');
        $(this).closest('form').addClass("margin-after-10");
    })
    jQuery("select[name=supplier_id]").on("change",function (){
        $(this).closest('form').find('input[type=file]').click();
    })

    jQuery('input[type="file"][name="bills"]').on('change', function () {
        if (this.files.length > 0) {
            $(this).closest('form').submit();
        }
    });
});
function registerToCalculatePrice(){
    jQuery('.products-gallery.orders .product .price-part').on('change', function (e) {
        calculatePrice(this);
    });
    jQuery(".products-gallery.orders .product .calculated-price-input").on("change", function () {
        var total = 0;
        jQuery(".products-gallery.orders .product .calculated-price-input").each(function (i,totalProductPrice){
            total+=parseInt( jQuery(totalProductPrice).val()||0);
        })
        jQuery("input[name=total]").autoNumeric('set', total);
        //jQuery("input[name=total]").autoNumeric.set("input[name=total]", total);

    });
}
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

    var count = parseInt(product.find('.count').val());
    var unitsInBox = parseInt(product.find('.units-in-box').val());
    var selectIndividually  = product.find("select.price-part.individually")
    if(selectIndividually.length>0 && selectIndividually.val()==0 ||selectIndividually.length ==0) {// אם לא ניתן לבחור בודדים , או שבחור ארגז
        count=count*unitsInBox;
    }
    var unitPrice = parseInt(product.find('.unit-price').val().replace('₪',''));
    var discountPercent = parseInt(product.find('.discount-percent').val()||0);
    var calculatedPrice = (unitPrice*count) - (unitPrice*count*discountPercent/100);
    product.find(".calculaded-price").html(calculatedPrice);
    product.find(".calculated-price-input").val(calculatedPrice).trigger("change");
}

function alert_msg(form,data){
    alert(data.msg);
}

function choose_supplier_column_mapping(form, data){
    jQuery('#supplier_column_mapping_modal').find('input[name=supplier_id]').val(data.supplier_id);
    jQuery('#supplier_column_mapping_modal').find('table.excel-rows').html(data.excel_rows);
    openModal("#supplier_column_mapping_modal");
}