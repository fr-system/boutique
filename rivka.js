function fillObligation(results){
    jQuery("input[name=obligation]").val(results.obligation);

    if(results.obligation){
        jQuery(".manager-approval").removeClass("hidden");
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

function fillOrderId(result){
    //צריך לשים למעלה בכתובת של האתר את מספר ההזמנה ולשנות את ה action ל edit

    if(!jQuery("input[name=id]").val()) {
        window.history.pushState({}, '', 'single/?subject=orders&action=edit&id='+result.id);
        jQuery("input[name=id]").val(result.id);
        var selectedOption = jQuery("section select[name=client_id]").find('option:selected');
        getObligationClient(selectedOption.val());
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
                    {name: "client_id", value: jQuery('.page.single form input[name=id]').val() },
                ];

                call_ajax_function(postData,"onCheckingDuplicates");
            }
        });
    }

//לדעת אם הלקוח שעכשיו יוצרים לו הזמנה הוא מחאר בתשלום לא לאפשר לאשר הזמנה אלא לשלוח לאישור מנהל
    if(getParameterByName("subject") == "orders" && getParameterByName("action") == "edit"){
        var selectedOption = jQuery("section select[name=client_id]").find('option:selected');
        getObligationClient(selectedOption.val());
    }

    jQuery(".manager-approval").click(function (){
        var postData = [
            {name: "action", value: "sent_to_manager"},
            {name: "id", value: jQuery('input[name=id]').val() },
        ];
        call_ajax_function(postData,"mail_sent");
    })

    var aTargets = [];
    jQuery.each(jQuery( "table" ).find( "th.no-sort" ),function (){
        var th = jQuery(this);
        aTargets.push(th.index());
    });

    //setTimeout(function () {
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

function fillProductsLastOrder(result) {
    jQuery(".add-order-product").after(jQuery(result.products));
    jQuery(".products-gallery .products-last-order").hide();
}

function plusMinusCountProduct(me){
    var numberInput = jQuery(me).parent().find("input");
    var currentValue = parseInt(numberInput.val()) || 0;

    if(jQuery(me).hasClass("plus")){
        numberInput.val(currentValue + 1);
    }
    else{
        if(currentValue > 0) {
            numberInput.val(currentValue - 1);
        }
    }
    calculatePrice(me);
}

jQuery(".page .products-gallery .product .plus-minus-count span.pointer:not(.readonly)").click(function (e) {
    plusMinusCountProduct(this)
})

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

/*
jQuery.fn.dataTable.ext.type.order['date-pre'] = function (dateString) {
    let parts = dateString.split("/"); // מפצל את המחרוזת
    let dateObject = new Date(parts[2], parts[1] - 1, parts[0]); // בונה את התאריך
    return  dateObject.getTime();
};

jQuery.fn.dataTable.ext.type.order['num-pre'] = function (data) {
    return data == ""? 0: parseInt(data);
};*/
