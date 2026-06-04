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

jQuery(document).ready(function($) {
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

    setTimeout(function () {
        var table = jQuery('.dataTable').DataTable({
            //bFilter: true,
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
    }, 500);

    jQuery("#payment_modal button.ok").click(function () {

        var id = jQuery('#payment_modal input[name="id"]').val();
        var tr = jQuery('.archive-table tr[data-id=' + id + "]");

        var payment_date = jQuery('#payment_modal input[name="payment_date"]').val();
        if (payment_date) {
            const parts = payment_date.split('-');
            payment_date = `${parts[2]}/${parts[1]}/${parts[0]}`;

        }
        tr.find("td.payment_date").html(payment_date);

        var payment_type = jQuery('#payment_modal select[name="payment_type"]').val();
        var text ="";
        if(payment_type){
            var text = jQuery('#payment_modal select[name="payment_type"] option:selected').text();
        }
        tr.find("td.payment_type").html(text);
        tr.find("td.payment_type").data("id",payment_type);
        tr.find("td.check_number").html(jQuery('#payment_modal').find('[name="check_number"]').val());

        closeModal();
    })

    jQuery('#payment_modal').on('show.bs.modal', function (e) {

        btn = jQuery(e.relatedTarget);
        var tr = btn.closest('tr');
        var id = tr.data("id");
        jQuery(this).find('[name="id"]').val(id);
        jQuery(this).find('.bill-num').html(id);

        var payment_date = tr.find("td.payment_date").html();
        if(payment_date){
            const parts = payment_date.split('/');
            payment_date = `${parts[2]}-${parts[1]}-${parts[0]}`;
            jQuery(this).find('[name="payment_date"]').val(payment_date);
        }
        var payment_type = tr.find("td.payment_type").html();
        if(payment_type) {
            jQuery(this).find('[name="payment_type"]').val(tr.find("td.payment_type").data("id"));
        }
        jQuery(this).find('[name="check_number"]').val(tr.find("td.check_number").html());
        //closeModal();
    });

})

jQuery(function ($) {

    var $tooltip = $('<div class="tooltip-box"></div>').appendTo('body');

    $('.has-tooltip').on('mouseenter', function (e) {
        var text = $(this).data('tooltip');

        $tooltip.text(text).fadeIn(150);

        $(this).on('mousemove.tooltip', function (e) {
            $tooltip.css({
                top: e.pageY + 10,
                left: e.pageX + 10
            });
        });
    });

    $('.has-tooltip').on('mouseleave', function () {
        $(this).off('mousemove.tooltip');
        $tooltip.fadeOut(150);
    });

});
/*
jQuery.fn.dataTable.ext.type.order['date-pre'] = function (dateString) {
    let parts = dateString.split("/"); // מפצל את המחרוזת
    let dateObject = new Date(parts[2], parts[1] - 1, parts[0]); // בונה את התאריך
    return  dateObject.getTime();
};

jQuery.fn.dataTable.ext.type.order['num-pre'] = function (data) {
    return data == ""? 0: parseInt(data);
};*/
