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

})
/*
jQuery.fn.dataTable.ext.type.order['date-pre'] = function (dateString) {
    let parts = dateString.split("/"); // מפצל את המחרוזת
    let dateObject = new Date(parts[2], parts[1] - 1, parts[0]); // בונה את התאריך
    return  dateObject.getTime();
};

jQuery.fn.dataTable.ext.type.order['num-pre'] = function (data) {
    return data == ""? 0: parseInt(data);
};*/
