
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