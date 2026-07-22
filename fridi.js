
jQuery(document).ready(function($){

    jQuery(".page tr.product:not(.promo) .count span.pointer:not(.readonly)").click(function (e) {
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
function filterOrderProdoctsRowsToSave() {
    var rows = jQuery('tr').filter(function () {
        var $row = jQuery(this);

        return jQuery.trim($row.find('td.count input').val() || '') === '' &&
            jQuery.trim($row.find('td.id input').val() || '') === '';
    });
    rows.find('td input').prop('disabled', true);
}
function checkPromotions(product, currentValue){
    var supplier_id = parseInt(product.find(".supplier_id span").text() || 0);
    var product_id = parseInt(product.find(".product_id span").text() || 0);
    var relevant_promotions = promotionsByProduct[product_id] ??= [];
    if (promotionsBySupplier[supplier_id]) {
        relevant_promotions.push(promotionsBySupplier[supplier_id] ??= []);
    }
    var currentCount = countUnitsForProduct(product, currentValue);

    relevant_promotions.forEach(pro => {
        var needToBuy = parseInt(pro.buy);
        var countToGet = parseInt(pro.get);
        switch (pro.type) {
            case "1": //  קנה קבל
                var countProductsInOrder = 0;
                if (pro.products_buy) {
                    var productsInSpecial = JSON.parse(pro.products_buy);
                    productsInSpecial.forEach(productId => {
                        jQuery("tr.in-cart:not(.bonus,.promo):has(.product_id input[type=hidden][value=" + productId + "])").each(function (k, p) {
                            countProductsInOrder += countUnitsForProduct(p);
                            //countProductsInOrder += parseInt(jQuery(p).find(".count input").val());
                        });
                    });
                } else {
                    jQuery("tr.in-cart:not(.bonus,.promo)").each(function () {
                        const row = jQuery(this);

                        if (row.find("td.supplier_id span").text().trim() !== pro.supplier_id) {
                            return;
                        }
                        countProductsInOrder += countUnitsForProduct(row);//parseInt(p.find(".count input").val());
                    });
                }
                var countPromotions = getCountPromotions(pro.product_get);
                var productToGet = jQuery("tr:not(.bonus,.promo):has(.product_id input[type=hidden][value=" + pro.product_get + "])");
                // if (countProductsInOrder == needToBuy && countPromotions == 0) {
                //     addProdoctBonus(productToGet, countToGet);
                //     break;
                // }
                if (countProductsInOrder >= needToBuy /*&& countProductsInOrder % needToBuy == 0*/) {
                    var toAdd = countToGet * parseInt(countProductsInOrder / needToBuy);
                    if (countPromotions == 0) {// קורה כי הכמות לארגד לא מדויקת לכמות המבצע
                        addProdoctBonus(productToGet, toAdd);
                    } else {
                        jQuery("tr.promo:has(.product_id input[type=hidden][value=" + pro.product_get + "])").eq(0)
                            .find(".count input").val(toAdd);
                    }
                }
                break;
            case "2": //  קנה מעל
                var priceToSupplier = 0;

                jQuery("tr.in-cart:not(.bonus,.promo)").each(function () {
                    const row = jQuery(this);

                    if (row.find("td.supplier_id span").text().trim() !== pro.supplier_id) {
                        return;
                    }
                    priceToSupplier += parseFloat(row.find("td.total input").autoNumeric('get')) || 0;
                });
                var countPromotions = getCountPromotions(countToGet);
                if (priceToSupplier >= pro.price_more) {
                    if (pro.get && countPromotions == 0) {
                        var productToGet = jQuery("tr:not(.bonus,.promo):has(.product_id input[type=hidden][value=" + pro.product_get + "])");
                        addProdoctBonus(productToGet, countToGet);
                        break;
                    }
                    if (pro.discount) {//
                        var totalOrder = jQuery("input[name=total]").val();
                        jQuery("input[name=total]").autoNumeric('set', totalOrder * (100 - parseFloat(pro.discount)) / 100)
                    }
                }
                break;
            case "3": //  קנה קבל מאותו מוצר
                var countPromotions = getCountPromotions(product_id);
                if (currentCount >= needToBuy /*&& currentCount % needToBuy == 0*/) {
                    if (countPromotions == 0) {
                        addProdoctBonus(product, parseInt(currentCount / needToBuy));
                    } else {
                        jQuery("tr.promo:has( .product_id input[type=hidden][value=" + product_id + "])").eq(0)
                            .find(".count input").val(parseInt(currentCount / needToBuy));
                    }
                }
                break;
        }
    });
}
