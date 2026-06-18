
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


function alert_msg(form,data){
    alert(data.msg);
}

function choose_supplier_column_mapping(form, data){
    jQuery('#supplier_column_mapping_modal').find('input[name=supplier_id]').val(data.supplier_id);
    jQuery('#supplier_column_mapping_modal').find('table.excel-rows').html(data.excel_rows);
    openModal("#supplier_column_mapping_modal");
}

function import_from_xlsx(form, data){
    jQuery('#importCollection').submit();
}