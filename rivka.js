var table;
let cartMode = false;

function fillClientDetails(results){
    if(results.branches){
        var disabled = jQuery("input.orders_id[name=id]").val() ?" disabled ":"";
        var required = disabled==""?" required ":"";
        var html ='<div class="branch-area margin-r-10">' +
                '<label class="bold" for="branch">סניף:</label>' +
                '<select class="font-17" id="branch" name="branch" '+disabled+'>'+results.branches+'</select>' +
            '</div>';
        //getSelectClientId().removeClass("grow");
        getSelectClientId().after(html);

    }
    else{
        jQuery('.page.single form .branch-area').remove();
        //getSelectClientId().addClass("grow");
    }

    if(getParameterByName("subject") == "orders")
    {
        if (jQuery('.page.single .obligation-msg').length == 0) {
            jQuery('.page.single .title-page').after('<span class="margin-before-10 color-red obligation-msg"></span>');
        }
        var spanObligation = jQuery('.page.single .obligation-msg');

        if (results.debts > results.obligo) {
            spanObligation.text('חוב מעבר לאובליגו' + " " + "₪" + (results.debts - results.obligo).toLocaleString());
            if (jQuery(".manager-approval").length > 0) {
                jQuery(".manager-approval").removeClass("hidden");
            } else {
                jQuery(".order-confirmation").removeClass("hidden");

            }
        } else {
            spanObligation.text("");
            jQuery(".order-confirmation").removeClass("hidden");
        }
    }
}

function onSelectClient(clientId){
    var postData = [
        {name: "action", value: "get_client_details"},
        {name: "client_id", value: clientId },
        {name: "selected_value", value: jQuery('.page.single input.branch-client').val()}
    ];
    call_ajax_function(postData,"fillClientDetails");
}

function getSelectClientId(){
    return jQuery('.page.single form .grid-display select[name=client_id]');
}

function automaticOrderSavingSuccess(result){
    const time = new Date().toLocaleTimeString('he-IL', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
    jQuery(".saving-automatic").html("ההזמנה נשמרה בשעה: "+time);

    if(!jQuery("input.orders_id[name=id]").val()) {//צריך לשים למעלה בכתובת של האתר את מספר ההזמנה ולשנות את ה action ל edit
        window.history.pushState({}, '', 'single/?subject=orders&action=edit&id='+result.id);
        jQuery("section form input.orders_id[name=id]").val(result.id);
        getSelectClientId().prop('disabled', true);
        jQuery("section form input[name=branch]").prop('disabled', true);
        jQuery("section input[name=order_date]").prop('disabled', true);
    }
    if(result.temp_list){
        result.temp_list.forEach(temp_row => {
            var temp_input =  jQuery(".archive-table.order_products tbody tr td.id input.temp[value=\""+temp_row.temp_id+"\"]");
            temp_input.siblings("input.id").val(temp_row.id);
        });
    }
}

function onCheckingDuplicates(result){
    show_error_messages(jQuery("form.single-form"), result);
    if(result.dupple == true){
        jQuery('input[name=BnNumber]').val("");
    }

}

jQuery(document).ready(function($) {
    //לבדוק אם יש כבר מספר ח"פ של לקוח לא ליצור לקוח נוסף
    if(getParameterByName("subject") == "clients") {
        jQuery('input[name=BnNumber]').change(function (){
            if(jQuery(this).val()){
                var postData = [
                    {name: "action", value: "checking_duplicates"},
                    {name: "BnNumber", value: jQuery(this).val()},
                    {name: "client_id", value: jQuery('.page.single form input.clients_id[name=id]').val() },
                ];

                call_ajax_function(postData,"onCheckingDuplicates");
            }
        });
    }

    if(getParameterByName("subject") == "orders" || getParameterByName("subject") == "tasks") {
        getSelectClientId().change(function () {
            if (jQuery(this).val()) {
                onSelectClient(jQuery(this).val());

            }
        });

        //לדעת אם הלקוח שעכשיו יוצרים לו הזמנה הוא מחאר בתשלום לא לאפשר לאשר הזמנה אלא לשלוח לאישור מנהל
        if (getParameterByName("action") == "edit") {
            var selectedOption = getSelectClientId().find('option:selected');
            onSelectClient(selectedOption.val());
            getSelectClientId().prop('disabled', true);
            jQuery("section form input[name=branch]").prop('disabled', true);
            jQuery("section input[name=order_date]").prop('disabled', true);
        }
    }

    jQuery(".manager-approval").click(function (){
        var postData = [
            {name: "action", value: "sent_to_manager"},
            {name: "id", value: jQuery('input.orders_id[name=id]').val() },
        ];
        call_ajax_function(postData);
    })

    startingDataTable();

    jQuery("#payment_modal button.ok").click(function () {

        //var id = jQuery('#payment_modal input[name="id"]').val();
        //var tr = jQuery('.archive-table tr[data-id=' + id + "]");

        var checkeds = jQuery(".archive-table").find("input:checkbox:checked");
        jQuery.map(checkeds, function (check) {
            var tr = jQuery(check).closest('tr');


            var payment_date = jQuery('#payment_modal input[name="payment_date"]').val();
            if (payment_date) {
                const parts = payment_date.split('-');
                payment_date = `${parts[2]}/${parts[1]}/${parts[0]}`;

            }
            tr.find("td.payment_date").html(payment_date);

            var payment_type = jQuery('#payment_modal select[name="payment_type"]').val();
            var text = "";
            if (payment_type) {
                var text = jQuery('#payment_modal select[name="payment_type"] option:selected').text();
            }
            tr.find("td.payment_type").html(text);
            tr.find("td.payment_type").data("id", payment_type);
            tr.find("td.check_number").html(jQuery('#payment_modal').find('[name="check_number"]').val());
        })
        closeModal();
    })

    jQuery('#payment_modal').on('show.bs.modal', function (e) {
        var checkeds = jQuery(".archive-table").find("input:checkbox:checked");
        if(checkeds.length == 0){
            alert("לא נבחרו חשבוניות לעדכון תשלום");
            e.preventDefault();
            return false;
        }

        var ids = jQuery.map(checkeds , function (check){
            return jQuery(check).closest('tr').data("id");
        })

        jQuery(this).find('[name="id"]').val(ids.join(','));

        var date = new Date();
        var day = String(date.getDate()).padStart(2, '0');
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var year = date.getFullYear();
        var dateStr = `${day}/${month}/${year}`;
        var parts = dateStr.split('/');
        var payment_date = `${parts[2]}-${parts[1]}-${parts[0]}`;
        jQuery(this).find('[name=payment_date]').val(payment_date);
    });

    jQuery('.grid-display [name=discount]').on('change',function (){
        var total = parseFloat( jQuery('.grid-display [name=total]').autoNumeric('get')||0)
        calculateForPayment(total);
    })
})

jQuery(function ($) {

    var $tooltip = $('<div class="tooltip-box"></div>').appendTo('body');
    show_tooltip();
});

function show_tooltip(){
    jQuery('.has-tooltip').on('mouseenter', function (e) {
        var text = jQuery(this).data('tooltip');
        var $tooltip = jQuery(".tooltip-box");
        $tooltip.text(text).fadeIn(150);

        jQuery(this).on('mousemove.tooltip', function (e) {
            $tooltip.css({
                top: e.pageY + 10,
                left: e.pageX + 10
            });
        });
    });

    jQuery('.has-tooltip').on('mouseleave', function () {
        var $tooltip = jQuery(".tooltip-box");
        jQuery(this).off('mousemove.tooltip');
        $tooltip.fadeOut(150);
    });
}

function plusMinusCountProduct(me){
    var currentValue,product;
    if(jQuery(me).is("tr") ){
        currentValue = 0;
        product = me;
    }
    else {
        var numberInput = jQuery(me).parent().find("input");
        product = numberInput.closest("tr.product");
        currentValue = parseInt(numberInput.val()) || 0;

        if (jQuery(me).hasClass("plus")) {
            currentValue++;
        } else {
            if (currentValue > 0) {
                currentValue--;
            }
        }
        numberInput.val(currentValue);
    }

    if(currentValue > 0) {
        product.addClass('in-cart');
        product.find("input").prop('disabled',false);
        if (product.find(".individually span").text()) {
            product.find(".order_individual span.readonly.right").removeClass("un-value");
            product.find(".order_individual span.readonly").removeClass("readonly");
        } else {
            product.find(".order_individual span.readonly.right").removeClass("readonly un-value");
        }
    }
    else{
        product.removeClass('in-cart');
        product.find(".order_individual input").val(0);
        product.find(".order_individual span").addClass("readonly un-value");
    }
    calculatePrice(product);
    if(!product.hasClass("bonus")) {//אם הורידו מוצר לבדוק להוריד את המבצע
        checkPromotions(product, currentValue);
    }
}
function countUnitsForProduct(product,count) {
    product = jQuery(product);
    var countOrdered = count ??= parseInt( product.find('.count input').val());
    var unitsInBox = parseInt(product.find('.units_in_box span').text());
    var selectIndividually = product.find(".order_individual input").val();
    return  selectIndividually == "0" ? countOrdered * unitsInBox : countOrdered;
}
function getProductsThisOrder(productId,className){

}
function getCountPromotions(productId,className){
    var countPromotions =0;
    jQuery("tr.promo:has(.product_id input[type=hidden][value="+productId+"])").each(function(k,p){
        countPromotions += countUnitsForProduct(p);
    });
    return countPromotions;
}
function addProdoctBonus(product,countBonus = 0){
    var countRows =  table.rows().count();
    var pBonus =  product.clone(true);

    pBonus.find(".count input").val(countBonus || 1);
    var rowIndex = pBonus.find(".count input").attr("name").replace("rows[","").replace("][count]","");
    jQuery.each(  pBonus.find("input"),function (k,input){
        var name =  jQuery(input).attr("name");
        name = name.replace("rows["+rowIndex+"]", "rows["+countRows+"]");
        jQuery(input).attr("name",name);
    })

    pBonus.find(".name").text(pBonus.find(".name").text() + (countBonus == 0 ? ' - בונוס' :' - מבצע'));
    pBonus.find(".discount_percent input").autoNumeric('set', 100);
    pBonus.find(".total input").autoNumeric('set', 0);
    pBonus.find(".id input.id").val("");
    const random = String(Math.floor(Math.random() * 10000000)).padStart(7, '0');
    pBonus.find(".id input.temp").val("temp_"+random);
    pBonus.find(".bonus input").val((countBonus == 0 ? 'bonus' :'promo'));
    pBonus.find(".order_individual input").val("1");
    pBonus.find(".order_individual span.right").addClass("un-value");
    pBonus.find(".order_individual span.left").removeClass("un-value");
    pBonus.find(".dupl-action").html('');
    pBonus.addClass('in-cart');
    pBonus.addClass((countBonus == 0 ? 'bonus' :'promo'));

    table.row.add(pBonus).draw(false);

    var columnIndex =  table
        .columns()
        .header()
        .toArray()
        .findIndex(function (th) {
            return $(th).data('column-name') === 'name';
        });

    table.order([columnIndex, 'asc']).draw();
    if(countBonus != 0) {
        pBonus.find(".count span.pointer").off();
    }
}

function removeProdoctFromOrder(product){
    product.removeClass("in-cart");
    product.find("td.count input").val(0);
    product.find("td.discount_percent input").val("");
    product.find("td.order_individual input").val(0);
    product.find("td.order_individual span").addClass("readonly un-value");

    product.find("td.count input").trigger('change');

    if(product.hasClass('bonus')){
        product.removeClass('bonus');
        //product.addClass('hidden');
    }
    else if(product.hasClass('promo')){
        product.removeClass('promo');
       // product.addClass('hidden');
    }
    else {
        plusMinusCountProduct(product);
        //calculatePrice(product.find("td.count input"));
        product.find("td.total input").trigger('change');
    }
}

function registerToCalculatePrice(){
    jQuery('tr.product td:not(.total) input').on('change', function (e) {
        calculatePrice(jQuery(this).closest("tr"));
    });
    jQuery("tr.product .total input").on("change", function () {
        var total = 0;
        jQuery("tr.product .total input").each(function (i,totalProductPrice){
            total+=parseFloat( jQuery(totalProductPrice).autoNumeric('get')||0);
        })
        jQuery("input[name=total]").autoNumeric('set', total);
        calculateForPayment(total);
        //jQuery("input[name=total]").autoNumeric.set("input[name=total]", total);

    });
}

function calculateForPayment(total){
    var discount = parseInt( jQuery("input[name=discount]").autoNumeric('get')||0);
    var forPayment = total
    if(discount>0){
        forPayment = total*(100-discount)/100;
    }
    jQuery("input[name=for_payment]").autoNumeric('set', forPayment);
}

function calculatePrice(product){
    //var product = jQuery(me).closest(".product");

    var count = parseInt(product.find('.count input').val());
    var unitsInBox = parseInt(product.find('.units_in_box span').text());
    var selectIndividually  = product.find(".order_individual input").val();
    if(selectIndividually ==0) {// אם לא ניתן לבחור בודדים , או שבחור ארגז
        count=count*unitsInBox;
    }
   // var total_order = parseInt(jQuery(".page.single input[name=total]").autoNumeric('get')||0);
    var unitPrice = parseFloat(product.find('.order_price input').autoNumeric('get'));
    var discountPercent = parseFloat(product.find('.discount_percent input').autoNumeric('get')||0);
    var calculatedPrice = (unitPrice*count) - (unitPrice*count*discountPercent/100);
    //product.find(".total span").html(calculatedPrice);
    product.find(".total input").autoNumeric('set',calculatedPrice).trigger("change");
    //product.find('td.order_id input').val();
}
function onOrderConfirmation(){
    jQuery(".order-confirmation").hide();
    setTimeout(function (){
        window.location.href = '/archive/?subject=orders';
    },5000)
}

function startingDataTable(){
    var aTargets = [];
    jQuery.each(jQuery( "table" ).find( "th.no-sort" ),function (){
        var th = jQuery(this);
        aTargets.push(th.index());
    });

    var tableName = getParameterByName("subject");
    var currentUrl = window.location.pathname;
    var single = currentUrl.includes('single');

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (!cartMode) {
            return true;
        }
        return $(settings.aoData[dataIndex].nTr).hasClass('in-cart');
    });

    var buttons = getButtonsTable(tableName,currentUrl);

    table = jQuery('.dataTable').DataTable({
        bFilter: true,
        layout: { topStart: { buttons: buttons } },
        searching: true,
        paging: false,
        info: false,
        "language":
            {
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
        "columnDefs": [
            {"orderable": false, "targets": aTargets},
        ],
    });
    jQuery('.dt-layout-cell.dt-layout-start').removeClass('dt-layout-start');
    jQuery('.dt-layout-cell.dt-layout-end').removeClass('dt-layout-end');
    jQuery('.dt-button.show-cart, .dt-button.add-row-table ').removeClass('dt-button');


    if(tableName == "orders" && currentUrl.includes('single')){
        table.column(".dupl-action").nodes().to$().find("a").hide();
    }
    else {
        if(currentUrl.includes('single')){}
        else{
            jQuery('.dt-search').closest(".dt-layout-row").hide();
        }
    }

    jQuery.fn.dataTable.ext.search.push(function (settings, data) {
        if(currentUrl.includes('single'))return true;

        const option = jQuery('.filter-by option:selected');
        const widgetType = option.data('widget');
        var fieldName =  option.val();

        // אינדקס עמודת order_date בטבלה
        //const columnIndex = table.column('[data-column-name="'+fieldName+'"]:name').index();
        const columnIndex = parseInt(
            jQuery('th[data-column-name="' + fieldName + '"]')
                .data('dt-column'),
            10
        );

        const cellValue = data[columnIndex];

        if (widgetType === 'text') {
            const valueToSearch = jQuery('input.filter-value').val();

            if (!valueToSearch) {
                return true;
            }

            if (!cellValue) {
                return false;
            }

            return data[columnIndex].toLowerCase().includes(valueToSearch.toLowerCase());
        }

        if (widgetType === 'select') {
            const valueToSearch = jQuery('select.filter-value').val();

            if (!valueToSearch) {
                return true;
            }

            if (!cellValue) {
                return false;
            }

            return data[columnIndex] === valueToSearch;
        }

        if (widgetType === 'date' || widgetType === 'datetime-local') {

            const from = jQuery('.filter-value.filter-from').val();
            const to = jQuery('.filter-value.filter-to').val();
            if (!from || !to) {
                return true;
            }

            const datePart = cellValue.split(' ')[0];
            const [day, month, year] = datePart.split('/');

            const rowDate = new Date(year, month - 1, day);

            if (from && rowDate < new Date(from)) {
                return false;
            }

            if (to) {
                const toDate = new Date(to);
                toDate.setHours(23, 59, 59, 999);

                if (rowDate > toDate) {
                    return false;
                }
            }
        }

        return true;
    });


    jQuery('.filter-by-area .filter-value').on('keyup change', function () {
        table.draw();
    });
}

function viewFilterByArea(){
    jQuery(".filter-by-area").toggleClass("hidden");
    jQuery('.filter-value').val("");
    table.search('').columns().search('').draw();

}
function onSelectFilterBy(filterBy){
    table.search('').columns().search('').draw();
    jQuery('.filter-value').val("");
    var fieldName =  jQuery(filterBy).val();
    var widget =  jQuery(filterBy).data("widget");
    var listName =  jQuery(filterBy).data("list-name");
    const columnIndex = parseInt(jQuery('th[data-column-name="' + fieldName + '"]').data('dt-column'),10);

    jQuery(".filter-by-area .filter-value").addClass("hidden");

    switch (widget) {
        case "text":
            jQuery(".filter-by-area input[type=\"text\"]").removeClass("hidden");
        break;
        case "date":
        case "datetime-local":
            jQuery(".filter-by-area input[type=\"date\"]").removeClass("hidden");
            jQuery(".filter-by-area input[type=\"date\"]").removeClass("hidden");
            break;
        case "select":
            jQuery(".filter-by-area select.filter-value").removeClass("hidden");
            const select = jQuery('select.filter-value');
            select.empty();
            select.append('<option value="">הכל</option>');

            table
                .column(columnIndex)
                .data()
                .unique()
                .sort()
                .each(function (value) {
                    if(value) {
                        select.append(
                            '<option value="' + value + '">' + value + '</option>'
                        );
                    }
                });
            break;

    }
    //table.draw();
}

function getButtonsTable(tableName,currentUrl ){
    if(tableName == "orders" && currentUrl.includes('single')) {
        return [
            {
                text: '<svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 16 16" fill="none">' +
                    '<path d="M4.00683 10.7573L2.76083 2.66667H2.00016C1.82335 2.66667 1.65378 2.59643 1.52876 2.47141C1.40373 2.34638 1.3335 2.17681 1.3335 2C1.3335 1.82319 1.40373 1.65362 1.52876 1.5286C1.65378 1.40357 1.82335 1.33334 2.00016 1.33334H3.3235C3.48426 1.33079 3.64052 1.38643 3.7635 1.49C3.88935 1.59608 3.97151 1.74497 3.99416 1.908L4.21283 3.33334H9.3335V4.66667H4.41816L5.23816 10H11.5042L12.5042 6.66667H13.8962L12.6388 10.858C12.5977 10.9954 12.5133 11.1158 12.3982 11.2015C12.2832 11.2871 12.1436 11.3333 12.0002 11.3333H4.6775C4.51218 11.3359 4.35181 11.277 4.2275 11.168C4.10667 11.0622 4.02874 10.9164 4.00683 10.7573ZM6.66683 13.3333C6.66683 13.687 6.52635 14.0261 6.27631 14.2761C6.02626 14.5262 5.68712 14.6667 5.3335 14.6667C4.97987 14.6667 4.64074 14.5262 4.39069 14.2761C4.14064 14.0261 4.00016 13.687 4.00016 13.3333C4.00016 12.9797 4.14064 12.6406 4.39069 12.3905C4.64074 12.1405 4.97987 12 5.3335 12C5.68712 12 6.02626 12.1405 6.27631 12.3905C6.52635 12.6406 6.66683 12.9797 6.66683 13.3333ZM12.6668 13.3333C12.6668 13.687 12.5264 14.0261 12.2763 14.2761C12.0263 14.5262 11.6871 14.6667 11.3335 14.6667C10.9799 14.6667 10.6407 14.5262 10.3907 14.2761C10.1406 14.0261 10.0002 13.687 10.0002 13.3333C10.0002 12.9797 10.1406 12.6406 10.3907 12.3905C10.6407 12.1405 10.9799 12 11.3335 12C11.6871 12 12.0263 12.1405 12.2763 12.3905C12.5264 12.6406 12.6668 12.9797 12.6668 13.3333Z" fill="white"/>' +
                    '<path d="M10.9985 6.56559C11.0102 6.72591 10.9517 6.88392 10.8357 7.00483L9.12183 8.79299C9.01159 8.90807 8.85776 8.98084 8.69119 8.99672C8.52462 9.0126 8.35764 8.97041 8.22372 8.8786L7.26545 8.22198C7.12465 8.12558 7.03156 7.98208 7.00667 7.82305C6.98178 7.66401 7.02713 7.50246 7.13273 7.37393C7.23833 7.24541 7.39554 7.16044 7.56978 7.13773C7.74401 7.11501 7.921 7.15639 8.0618 7.25279L8.52722 7.57181L9.83677 6.20687C9.9527 6.08595 10.1165 6.01202 10.2921 6.00134C10.4678 5.99066 10.6409 6.04411 10.7734 6.14993C10.9058 6.25574 10.9868 6.40526 10.9985 6.56559Z" fill="white" />' +
                    '</svg><span class="button-text">הצגת עגלה</span>',
                className: 'show-cart background-gold flex-display center align-center bold ',
                action: function (e, t, sender, sProp) {
                    jQuery(sender).toggleClass("cart-mode");
                    cartMode = !cartMode;
                    if (cartMode) {
                        table.column(".dupl-action").nodes().to$().find("a").show();
                        show_tooltip();
                        jQuery(sender).find(".button-text").text("חזרה להזמנה");
                    } else {
                        jQuery(sender).find(".button-text").text("הצגת עגלה");
                        table.column(".dupl-action").nodes().to$().find("a").hide();
                    }

                    table.draw();
                }
            }
        ];
    }

    if(tableName == "clients" && currentUrl.includes('single')){
        return [
            {
                text:'<svg width="24" height="23" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                    '                <path d="M2.375 3.7085C2.375 3.35483 2.51549 3.01565 2.76557 2.76557C3.01565 2.51549 3.35483 2.375 3.7085 2.375H8.0415C8.21662 2.375 8.39002 2.40949 8.55181 2.47651C8.7136 2.54352 8.8606 2.64175 8.98443 2.76557C9.10825 2.8894 9.20648 3.0364 9.27349 3.19819C9.34051 3.35998 9.375 3.53338 9.375 3.7085V8.0415C9.375 8.21662 9.34051 8.39002 9.27349 8.55181C9.20648 8.7136 9.10825 8.8606 8.98443 8.98443C8.8606 9.10825 8.7136 9.20648 8.55181 9.27349C8.39002 9.34051 8.21662 9.375 8.0415 9.375H3.7085C3.53338 9.375 3.35998 9.34051 3.19819 9.27349C3.0364 9.20648 2.8894 9.10825 2.76557 8.98443C2.64175 8.8606 2.54352 8.7136 2.47651 8.55181C2.40949 8.39002 2.375 8.21662 2.375 8.0415V3.7085Z" class="stroke-background-gold" stroke-width="0.75" stroke-linecap="round" stroke-linejoin="round"></path>' +
                    '            <path d="M0.881 7.2435C0.7275 7.15629 0.599825 7.02999 0.51095 6.87745C0.422076 6.7249 0.37517 6.55155 0.375 6.375V1.375C0.375 0.825 0.825 0.375 1.375 0.375H6.375C6.75 0.375 6.954 0.5675 7.125 0.875M4.375 5.875H7.375M5.875 4.375V7.375" class="stroke-background-gold" stroke-width="0.75" stroke-linecap="round" stroke-linejoin="round"></path>' +
                    '    </svg><span class="button-text">הוספת סניף</span>',
                className: 'add-row-table flex-display center button background-white gold bold',
                action: function (e, t, sender, sProp) {
                    const $tr = $('<tr>', {'data-id': ''});
                    var countRows =  table.rows().count();
                    var columns =  [
                        {name: 'text'},
                        {address: 'text'},
                        {mobile: 'text'},
                        {email: 'text'},
                        {id: 'hidden'},
                        {main_client_id: 'hidden'}
                    ];

                    columns.forEach(function (column) {
                        const field = Object.keys(column)[0];
                        const type = Object.values(column)[0];

                        const $td = $('<td>', {class: field});
                        const $span = $('<span>', {class: 'hidden',text: ''});

                        const $input = $('<input>', {
                            type: type,
                            class: field,
                            name: `rows[${countRows}][${field}]`,
                            placeholder:'הכנס...'
                        });

                        $td.append($span, $input);
                        $tr.append($td);
                    });

                    $('tbody').prepend($tr);
                }
            }
        ];
    }

    return [];
}


