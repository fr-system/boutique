function fillObligation(results){

    if(jQuery('.page.single form .grid-display .obligation').length == 0) {
        getSelectClientId().after('<span class="margin-before-10 color-red obligation"></span>');
    }
    var spanObligation =jQuery('.page.single form .grid-display .obligation');
    //spanObligation.html(results.obligation);

    if(results.debts > results.obligo){
        spanObligation.text('חוב מעבר לאובליגו'+" "+"₪" + (results.debts - results.obligo).toLocaleString() );
        if(jQuery(".manager-approval").length > 0) {
            jQuery(".manager-approval").removeClass("hidden");
        }
        else{
            jQuery(".order-confirmation").removeClass("hidden");

        }
    }
    else{
        jQuery(".order-confirmation").removeClass("hidden");
    }
}

function getObligationClient(clientId){
    var postData = [
        {name: "action", value: "get_obligation_client"},
        {name: "client_id", value: clientId }
    ];
    call_ajax_function(postData,"fillObligation");
}

function getSelectClientId(){
    return jQuery('.page.single form .grid-display select[name=client_id]');
}

function fillOrderId(result){
    //צריך לשים למעלה בכתובת של האתר את מספר ההזמנה ולשנות את ה action ל edit

    if(!jQuery("input.orders_id[name=id]").val()) {
        window.history.pushState({}, '', 'single/?subject=orders&action=edit&id='+result.id);
        jQuery("section form input.orders_id[name=id]").val(result.id);
        getSelectClientId().prop('disabled', true);
        jQuery("section input[name=order_date]").prop('disabled', true);
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

    if(getParameterByName("subject") == "orders") {
        getSelectClientId().change(function () {
            if (jQuery(this).val()) {
                getObligationClient(jQuery(this).val());

            }
        });
//לדעת אם הלקוח שעכשיו יוצרים לו הזמנה הוא מחאר בתשלום לא לאפשר לאשר הזמנה אלא לשלוח לאישור מנהל
        if (getParameterByName("action") == "edit") {
            var selectedOption = getSelectClientId().find('option:selected');
            getObligationClient(selectedOption.val());
            getSelectClientId().prop('disabled', true);
            jQuery("section input[name=order_date]").prop('disabled', true);
        }
    }

    jQuery(".manager-approval").click(function (){
        var postData = [
            {name: "action", value: "sent_to_manager"},
            {name: "id", value: jQuery('input.orders_id[name=id]').val() },
        ];
        call_ajax_function(postData,"mail_sent");
    })

    var aTargets = [];
    jQuery.each(jQuery( "table" ).find( "th.no-sort" ),function (){
        var th = jQuery(this);
        aTargets.push(th.index());
    });

    //setTimeout(function () {
    var tableName = getParameterByName("subject");
    var currentUrl = window.location.pathname;
    var single = currentUrl.includes('single');
    if(tableName == "orders"){

    }

        var table = jQuery('.dataTable').DataTable({
            //bFilter: true,
            searching: (tableName == "orders" && currentUrl.includes('single')),
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
    //}, 500);

    if(tableName == "orders" && currentUrl.includes('single')){
    }
    else
    {
        jQuery('.dataTables_filter').hide();
    }

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

/*function fillProductsLastOrder(result) {
    jQuery(".add-order-product").after(jQuery(result.products));
    jQuery(".products-gallery .products-last-order").hide();
}*/

function plusMinusCountProduct(me){
    var numberInput = jQuery(me).parent().find("input");
    var product = numberInput.closest("tr.product");
    var currentValue = parseInt(numberInput.val()) || 0;

    if(jQuery(me).hasClass("plus")){
        currentValue++;
    }
    else{
        if(currentValue > 0) {
            currentValue--;
        }
    }
    numberInput.val(currentValue);
    if(currentValue > 0) {
        if(product.find(".individually span").text()) {
            product.find(".order_individual span.readonly").removeClass("readonly");
        }
        else{
            product.find(".order_individual span.readonly.right").removeClass("readonly un-value");
        }
    }
    else{
        product.find(".order_individual input").val(0);
        product.find(".order_individual span").addClass("readonly un-value");
    }
    calculatePrice(me);
}


function registerToCalculatePrice(){
    jQuery('tr.product td:not(.total) input').on('change', function (e) {
        calculatePrice(this);
    });
    jQuery("tr.product .total input").on("change", function () {
        var total = 0;
        jQuery("tr.product .total input").each(function (i,totalProductPrice){
            total+=parseFloat( jQuery(totalProductPrice).autoNumeric('get')||0);
        })
        jQuery("input[name=total]").autoNumeric('set', total);
        //jQuery("input[name=total]").autoNumeric.set("input[name=total]", total);

    });
}

function calculatePrice(me){
    var product = jQuery(me).closest(".product");

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

/*
jQuery.fn.dataTable.ext.type.order['date-pre'] = function (dateString) {
    let parts = dateString.split("/"); // מפצל את המחרוזת
    let dateObject = new Date(parts[2], parts[1] - 1, parts[0]); // בונה את התאריך
    return  dateObject.getTime();
};

jQuery.fn.dataTable.ext.type.order['num-pre'] = function (data) {
    return data == ""? 0: parseInt(data);
};*/
