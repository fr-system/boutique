
jQuery(document).ready(function($){

    jQuery(".page tr.product:not(.bonus) .count span.pointer:not(.readonly)").click(function (e) {
        plusMinusCountProduct(this)
    })
    jQuery('form input').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
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

// function fillClientPriceModal(result) {
//     if (result.array.length > 0) {
//         jQuery('#update_client_price input[name=id]').val(result.array[0].id);
//         //jQuery('#update_client_price input[name=client_id]').val(result.client_id);
//         jQuery('#update_client_price input[name=client_price]').val(result.array[0].client_price);
//     }
//     else{
//         jQuery('#update_client_price input[name=id]').val("");
//         jQuery('#update_client_price input[name=client_price]').val("");
//     }
// }


function import_excel_done(form,data){
    //jQuery(".archive-table").closest(".dt-container ").remove();
    if(data.rows) {
        jQuery(".page .archive-table tbody").prepend(data.rows);
        //setDataTable();
    }
    show_slider_message(data.message);
    //alert(data.msg);
}

function choose_supplier_column_mapping(form, data){
    jQuery('#supplier_column_mapping_modal').find('input[name=supplier_id]').val(data.supplier_id);
    jQuery('#supplier_column_mapping_modal').find('table.excel-rows').html(data.excel_rows);
    jQuery('#supplier_column_mapping_modal').find('select').html(data.columns_options);

    openModal("#supplier_column_mapping_modal");
}

function import_from_xlsx(form, data){
    closeModal();
    jQuery('#importCollection').submit();
}
function setDataTable (){
    var table = jQuery('.dataTable').DataTable({
        //bFilter: true,
        paging: false,
        info: false,
        "language": {
            "lengthMenu": "מציג  _MENU_  שורות",
            "zeroRecords": "לא נמצאו שורות מתאימות",
            "info": "מציג עמוד _PAGE_ מתוך _PAGES_",
            "infoEmpty": "לא נמצאו שורות מתאימות",
            "emptyTable": "לא נמצאו שורות בטבלה",
            "infoFiltered": "(מתוך _MAX_ שורות סך הכל)",
            "infoPostFix": "",
            "thousands": ",",
            "loadingRecords": "טוען...",
            "processing": "בעבודה...",
            "search": "חיפוש: ",
            "paginate": {
                "first": "התחלה",
                "last": "סוף",
                "next": "הבא",
                "previous": "הקודם"
            },
            "aria": {
                "sortAscending": ": activate to sort column ascending",
                "sortDescending": ": activate to sort column descending"
            },

        },
        "ordering": true,
        order: [],

        /*  "columnDefs": [{
              orderable: false,
              targets: "no-sort"
          }],*/
        "aoColumnDefs": [
            { "bSortable": false, "aTargets": aTargets }//[ 4, 5, 6 ]
        ],
        /*   "columnDefs": [
               {"type": "date", "targets": [3, 4]}, // החל על העמודה הראשונה
               {"type": "num", "targets": [6]} // החל על העמודה השנייה
           ],*/
        // "order": []
        // "order": [[ 3, "desc" ]]
    });
    jQuery('.dt-layout-cell.dt-layout-start').removeClass('dt-layout-start');
    jQuery('.dt-layout-cell.dt-layout-end').removeClass('dt-layout-end');
}

function addNewRow(row){
    var countRows =  table.rows().count();
    var newRow =  row.clone(true);
    var rowIndex = row.find(".name input").attr("name").replace("rows[","").replace("][name]","");

    jQuery.each(  newRow.find("input"),function (k,input) {
        var name = jQuery(input).attr("name");
        name = name.replace("rows[" + rowIndex + "]", "rows[" + countRows + "]");
        jQuery(input).attr("name", name);
        if (!name.includes("main")) {
            jQuery(input).val("");
        }
    })

    row.before(newRow);
}
