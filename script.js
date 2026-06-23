let xhr;
function getParameterByName(name){
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    var value = results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    return value;
}
jQuery(document).ready(function($){
    var currentUrl = window.location.pathname;
    if (currentUrl.includes('manage-lists'))
    {
        var selected = jQuery("ul.tables-list li:first-child")
        selected.addClass("selected");
        getTableAjaxData(selected.data("list-name"));
    }

    if(getParameterByName("subject") == "orders" && getParameterByName("action") && getParameterByName("action") != "readonly") {
       /* if(getParameterByName("action") == "new" && jQuery(".page.single .grid-display select[name=client_id]").val()){
            jQuery(".products-gallery.orders .products-last-order").removeClass("hidden");
        }*/

        /*jQuery(".page.single .grid-display select[name=client_id]").change(function (){
            if(getParameterByName("action") == "new" && jQuery(".products-gallery.orders .product").length == 0){
                jQuery(".products-gallery.orders .products-last-order").removeClass("hidden");
            }
        })*/


        setInterval(function () {

                    /*                if (getParameterByName("action") == "new") {//אין לי מושג למה רציתי לשאול אם זה חדש

                                    }*/
                    automaticOrderSaving();

                //לשאול את פרידי לבדוק אם נגעו ב-2 דקות האלו לשמור ואם לא אז לא לשמור אולי לעשות שרק אם עזבו את המסך ולא נגעו בו כבר יותר מ2 דקות אז ללכת לשמירה

        }, 12000); // 120000 מילישניות = 2 דקות
    }

    if(getParameterByName("subject") == "tasks" && getParameterByName("action") == "edit"){
        /*var postData = [
            {name: "action", value: "get_chat_ajax"},
            {name: "task_id", value: getParameterByName("id")},
        ];

        call_ajax_function(postData,"onAddChat");*/

           setInterval(function () {
            var postData = [
                {name: "action", value: "get_chat_ajax"},
                {name: "task_id", value: getParameterByName("id")},
            ];
            call_ajax_function(postData,"onAddChat");
        }, 10000); // 120000 מילישניות = 2 דקות
    }

    jQuery('input, select, textarea').change(function (){
        //לבדוק אם רק הוסיפו מוצר חדש וכמות וכו'
        // לבדוק שבכל האפשרויות הוא רואה שהרשומה עודכנה
        jQuery('input[name=dirty]').val("1");
    })

    //צריך לבדוק מתי לא לתת לצאת לפני שאלה האם לצאת בלי שמירה
    //כרגע אני עושה רק על לחצן בטל או logout
    // a, button:not(.save)

     if (window.location.pathname.includes('single')) {
        jQuery('a.cancel, .logout-button, ul.menu li a ').click(function (e) {
            if (jQuery('input[name=dirty]').val() == "1") {
                if(getParameterByName("subject") == "orders"){
                    automaticOrderSaving();
                }
                else {
                    const confirmation = confirm('הערכים עדיין לא נשמרו. האם אתה בטוח שברצונך לצאת ללא שמירה?');
                    if (!confirmation) {
                        e.preventDefault(); // מניעת לחיצה אם המשתמש לא מאשר
                    }
                }
            }
        });
    }
    // jQuery('input[data-a-sign=₪]').each(function () {
    //
    //     new AutoNumeric(this, {
    //         currencySymbol: '₪ ',
    //         decimalPlaces: 2
    //     });
    //  });
    // new AutoNumeric('input[data-a-sign=₪]', {
    //     currencySymbol: '₪ ',
    //     decimalPlaces: 2
    // });
    jQuery('input[data-a-sign=₪]').autoNumeric('init', { vMin: '-9999999999999' });

    jQuery("form").validate({
        rules: {
            'input, textarea':{ required: true},
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 8
            },
            confirm_password: {
                required: true,
                equalTo: "#password"
            }

        },
        messages: {
            email: {
                //required: "אנא הזן כתובת אימייל",
                email: "אנא הזן כתובת אימייל תקינה"
            },
            password: {
                required: "אנא הזן סיסמה",
                minlength: "הסיסמה חייבת להיות לפחות 8 תווים"
            },
            confirm_password: {
                required: "אנא הזן סיסמת אישור",
                equalTo: "סיסמת האישור אינה תואמת"
            }

        }});

    jQuery('.user-logged:not(.popup-logout)').click(function(){
        jQuery('.popup-logout').toggleClass("hidden");
    });

    jQuery('.logout-button').click(function(){
        jQuery.ajax({
            type: "GET",
            url: "/wp-admin/admin-ajax.php",
            data: { action: 'user_logout' },
            success: function (output) {
                location.reload();
            }
        });
    });

    jQuery('#login_forgot_password_btn').click(function(){
        forgot_password_state();
    });

    jQuery('.popup .btn.cancel, .popup .close-popup').click(function(){
        closePopup();
    });

    let clickedButton = null;

    jQuery('body').on('click', '.site_form button[type="submit"], .site_form input[type="submit"]', function() {
        clickedButton = this;
    });

    jQuery('body').on('submit', '.site_form', function(e) {
        e.preventDefault();
        var $form = jQuery(this);
        if (!$form.valid()) {
            return;
        }

        if (getParameterByName("subject") == "orders" && jQuery('.page.single').length > 0 && jQuery('.products-gallery .product').length == 0) {
            return;
        }

        $form.addClass('disabled').find('[type="submit"]').prop('disabled', true);
        //grecaptcha.execute(globalVars.recaptcha_key, {action: 'submit'})
        //.then(function (token) {
        $form.find('#form_error_msgs_container').html('');

        //var formData = $form.serializeArray();
        if(jQuery(clickedButton).hasClass("block-client")){
            var blocked = "0";
            if(jQuery('input[name=blocked]').val()!="1"){
                blocked = "1";
            }
            jQuery('input[name=blocked]').val(blocked);
        }

            var formData = new FormData($form[0]);
            formData.append('action', 'send_site_forms');

        if (xhr && xhr.readyState != 4)
            xhr.abort();
        xhr = jQuery.ajax({
            url: "/wp-admin/admin-ajax.php",//globalVars.ajaxurl,
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            method: 'POST'
        }).done(function (data) {
            $form.find('[type = "submit"]').find(".animation-sending").empty();
            $form.removeClass('disabled').find('[type = "submit"]').prop('disabled', false);
            var func;
            if (data.status == 'success') {
                func = $form.data('success');
                if(func == "reload_page"){
                    window[func](data);
                }else{
                    window[func]($form, data);
                }
            }
            else {
                if (data.status == 'exception') {
                    alert(data.reason);
                }
                else if (data.status == 'error') {
                    func = $form.data('error');
                    window[func]($form, data);
                }
                else if($form.data('failed')) {
                    func = $form.data('failed');
                    window[func]($form, data);
                } else {
                    alert(data.msg);
                }
            }

        }).fail(function (data) {
            $form.find('[type = "submit"]').find(".animation-sending").empty();
            $form.removeClass('disabled').find('[type = "submit"]').prop('disabled', false);
            if(data.responseText){
                alert(data.responseText)
            }
            else{
                alert('השליחה נכשלה! שורה 50');
            }

        });
    })
    jQuery('.input-label:has(.open-file-uploader)').find(".remove-file").click(function (e) {
        var field =  jQuery(this.closest(".input-label"));

        field.find("input[type=hidden]").val("");
        field.find("a").attr("href","");
        field.find("a").html("");
        if(field.find(".open-file-uploader").hasClass("product-image")) {
            jQuery(".protuct-image").attr("src", "");
            jQuery(".protuct-image").addClass("hidden");
        }
        field.find(".remove-file").addClass("hidden");
    });

    jQuery('.open-file-uploader').click(function (e) {
        var field =  jQuery(this.closest(".input-label"));

        e.preventDefault();
        var image = wp.media({
            title: 'בחר קובץ',
            multiple: false
        }).open()
            .on('select', function(e){
                var uploaded_image = image.state().get('selection').first();
                field.find("input[type=hidden]").val(uploaded_image.id);
                field.find("a").attr("href",uploaded_image.attributes.url);
                field.find("a").html(uploaded_image.attributes.filename);
                jQuery(".protuct-image").attr("src",uploaded_image.attributes.url);
                field.find(".remove-file").removeClass("hidden");
                jQuery(".protuct-image").removeClass("hidden");
            });
        //mediaFrame.open();

    });

    jQuery('ul.tables-list li').click(function () {
        jQuery(this).parent().children().removeClass("selected");
        jQuery(this).addClass("selected");
        getTableAjaxData( jQuery(this).data("list-name"));
    });

    jQuery('[data-view]').click(function () {
        var postData = [
            {name: "action", value: "update_user_meta_value"},
            {name: "meta_key", value: "products_view" },
            {name: "meta_value", value: jQuery(this).data("view")},
        ];
        call_ajax_function(postData,"reload_page");
    });

    jQuery('#search').on('input', function() {
        var text = jQuery(this).val();

        if((jQuery('.grid-display').length)){
            searchElements(text, ".product",".product-name");
        }
        else{
            searchElements(text, "tr:not(:first)","td");
        }
    });

    jQuery(".status-options .ellipse:not(.readonly)").click(function () {
        var ellipse = jQuery(this);
        var input = jQuery(".status-options input");

        if(!ellipse.hasClass("un-value")){//היה בחור ורצים לבטל הבחירה
            ellipse.addClass("un-value ");
            input.val("");

        }
        else{//רוצים לבחור את מי שלחצתי עכשיו
            jQuery(".status-options .ellipse").addClass("un-value ");
            ellipse.removeClass("un-value");
            input.val(ellipse.data("value"));
        }
        jQuery(".status-options .ellipse.un-value").on("mouseover",function (){
            jQuery(this).addClass("un-value-over");
        }).on("mouseout",function (){
            jQuery(this).removeClass("un-value-over");
        })
    })

    jQuery(".status-options .ellipse.un-value").on("mouseover",function (){
        jQuery(this).addClass("un-value-over");
    }).on("mouseout",function (){
        jQuery(this).removeClass("un-value-over");
    })


    jQuery( "#newChat" ).on("change",function(event){
        if(jQuery( "#newChat" ).val() && jQuery( "#newChat" ).val().length > 2){

            var postData = [
                {name: "action", value: "new_chat_ajax"},
                {name: "text", value: jQuery( "#newChat" ).val() },
                {name: "task_id", value: getParameterByName("id")},
            ];
            call_ajax_function(postData,"onAddChat");
        }
    })

    jQuery( ".order-confirmation" ).on("click",function(event){
        var postData = [
            {name: "action", value: "on_order_confirmation"},
            {name: "order_id", value: getParameterByName("id")},
            {name: "order_id", value: getParameterByName("id")},
        ];
        call_ajax_function(postData);

    })

    jQuery('#accept_process_modal').on('hide.bs.modal', function (e) {
        jQuery('.site_form').find('[type = "radio"], [type = "checkbox"]').prop('checked', false);
        jQuery('.site_form').find('input, select').not(dont_reset_val).val('');
    });
    jQuery('#edit-list').on('show.bs.modal', function (e) {
        var btn = jQuery(e.relatedTarget), comboListName,html='', tr,rowData=[];
        var listName= jQuery("ul.tables-list li.selected").data("list-name");
        jQuery("#edit-list input[name=table_name]").val(listName);
        jQuery("#edit-list .modal-title").text(jQuery("ul.tables-list li.selected").text());
        if(btn.data("action")=="edit"){
            tr=btn.parent().parent();
            rowData= jQuery.map(jQuery(tr).find('td:not(.td-action)'),function(td){
                return jQuery(td).html();
            });
            jQuery('#edit-list input[name=id]').val(tr.data("id"));
        }
        jQuery('table.list-table tr:first-child th').each(function(k,th) {
            th = jQuery(th)
            var columnName = th.data("column-name");
            var columnType = th.data("column-type") ?? "text";
            html += '<span class="input-label flex-display align-center">' +
                ' <label class="bold" for="' + columnName + '">' + th.text() + ':</label>';
            if (th.data("column-type") == "select") {
                html += '<select id="' + columnName + '" name="' + columnName + '"  class="font-17 grow"' +
                    (rowData.length > 0 ? ' value="' + rowData[k] + '"' : '') + '>';
                comboListName = th.data("table");
                var postData = [
                    {name: "action", value: "get_list_ajax"},
                    {name: "table_name", value: comboListName},
                    {name: "selected_value",value:  rowData[k]}
                ];
                call_ajax_function(postData,"fill_modal_list")
            } else {
                html += '<input type="text" id="' + columnName + '" name="' + columnName + '"  class="font-17 grow"' +
                    (rowData.length > 0 ? ' value="' + rowData[k] + '"' : '') + '>';
            }
            html += '</span>';
        });
        jQuery('#edit-list .modal-body').empty();
        jQuery('#edit-list .modal-body').append(html);


    });
    jQuery('#edit-list').on('shown.bs.modal', function () {
        jQuery('#edit-list .modal-body input:first-child').focus();
    })


    jQuery('#bout-massage').on('hide.bs.modal', function (e,a) {
        jQuery(this).find('button.remove-product-order').addClass("hidden");
        jQuery(this).find('button[type=submit]').show();
        jQuery(this).find('[name="remove"]').val(0);
    });

    jQuery("#bout-massage button.remove-product-order").click(function (){
        var id = jQuery('#bout-massage').find('[name="id"]').val();
        if(id) {
            var product = jQuery('.products-gallery .product[data-id=' + id + ']');
            product.addClass("hidden");
            product.find("input.input-remove").val("1");

            var total_order = parseInt(jQuery(".page.single input[name=total]").autoNumeric('get')||0);
            total_order-=parseInt( product.find('.calculaded-price').text()||0);
            jQuery("input[name=total]").autoNumeric('set', total_order);
        }
        else{
            $.each(jQuery('.products-gallery .product'),function () {
                var product = jQuery(this);
                if(product.find(".input-remove").val()=="1"){
                    product.remove();
                }
            });
        }
        closeModal()
    })

    jQuery('#bout-massage').on('show.bs.modal', function (e) {
        //action = remove
        var btn = jQuery(e.relatedTarget);
        var subject = getParameterByName("subject");

        var single = jQuery("body").find("section").data("single");
        var title = jQuery("body").find(".page-title").html()

        if(subject == "lists"){
            subject = jQuery("ul.tables-list li.selected").data("list-name");
            title = jQuery("ul.tables-list li.selected").html();
            single = jQuery(".list-table .tr-head th:first-child").html();
        }

        if (getParameterByName("action")=="edit") {
            id = getParameterByName("id");
        } else if (btn.is('a')) {
            id = btn.parent().parent().data("id");
        }

        if(subject == "orders"/* && getParameterByName("action")=="edit"*/ && btn.parent().parent().hasClass("product")){
            single = "מוצר מההזמנה";
            title = "הזמנות";
            jQuery(this).find('button.remove-product-order').removeClass("hidden");
            jQuery(this).find('button[type=submit]').hide();
            if(getParameterByName("action")=="new"){
                id="";
                btn.parent().parent().find(".input-remove").val("1");
            }
            else{
                id = btn.parent().parent().data("id");
            }
            //id = btn.parent().parent().data("id");
        }
        else{
            jQuery(this).find('[name="remove"]').val(1);
            jQuery(this).find('[name="form_func"]').val("save_single_data");
            jQuery(this).find('[name="table_name"]').val(subject);
        }

        jQuery(this).find('[name="id"]').val(id);
        jQuery("#bout-massage .modal-title").html(title);
        jQuery("#bout-massage .modal-body").html("האם אתה מאשר למחוק את ה"+single+"?");
    });

})

function removeRowSuccess(form,result){
    var currentUrl = window.location.pathname;
    if (currentUrl.includes('archive')) {
        var tr = jQuery(".archive-table tr[data-id=" + result.id + "]");
        tr.remove();
    }
    else if(currentUrl.includes("manage-lists")){
        var tr = jQuery(".list-table tr[data-id=" + result.id + "]");
        tr.remove();
    }
    else{
        window.location.href =jQuery("input[name=previous_page]").val();
    }
    closeModal();
}

function onAddChat(result){
    var chatList = jQuery(".chat-list");
    var get_mes = result.get_messages;
    jQuery.each(result.rows,function (){
        var row = this;

        var d = new Date(row.date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear(),
            hour = d.getHours(),
            minutes = d.getMinutes();

        if (month.length < 2)
            month = '0' + month;
        if (day.length < 2)
            day = '0' + day;
        if (hour.length < 2)
            hour = '0' + hour;
        if (minutes.length < 2)
            minutes = '0' + minutes;

        var date = [day, month, year].join('/')+ "\n" + hour+":"+minutes;

        //'+result.client_logo+'
        var message = '<div class="input-label flex-display space-between" data-id="'+row.id+'">'+
            '<div class="part-20">'+row.logo+'</div>'+
            '<div class="text part-50 text-center">'+row.text+'</div>'+
            '<div class="date text-left part-20 font-12">'+date+'</div></div>';

        if(get_mes){
            var result = jQuery.grep(jQuery(".chat-list .input-label"), function (mess) {
                return jQuery(mess).data("id") == row.id
            });
            if (result.length > 0) {
                return;
            }
        }

        chatList.append(jQuery(message));
    })

    if (chatList.length) {
        chatList.scrollTop(chatList[0].scrollHeight);
    }

    if(result.add_message) {
        jQuery("#newChat").val("");
    }
}

function searchElements(text,selector,searchSelector){

    jQuery(selector).show();
    const url = new URL(jQuery(".export-excel").attr("href"));
    var ids = [];
    if(text.length > 0) {
        jQuery.each(jQuery(selector), function (k) {
            var product = jQuery(this);
            if (product.find(searchSelector+':contains(' + text + ')').length) {
                product.show();
                ids.push(product.data("id"));
            } else {
                product.hide();
            }
        })


        url.searchParams.set('ids', ids);
    }
    else{
        url.searchParams.delete('id');
    }

    jQuery(".export-excel").attr("href",url.toString());
}

function reload_page(data){
    if(data.redirect) {
        window.location.href = data.redirect;
    }
    else{
        location.reload();
    }
}
function show_error_messages($form, data){
    jQuery($form).find('#form_error_msgs_container').html(data.msg);
}

function show_success_msg($form, data){
    $form.next('.success_msg_of_form').removeClass('d-none');
}
function closePopup(){
    jQuery('html').removeClass('popup_open');
}

function openPopup(name, title){
    jQuery('html').addClass('popup_open');
    //jQuery('.popup_page_overlay').find('form').addClass("hide");
    //jQuery('.popup_page_overlay').find('form.'+name+'-popup').removeClass("hide");

}
function forgot_password_state(data){
    jQuery('#login_form').hide();
    jQuery('.forgot-password-form').removeClass('d-none');

}

jQuery(".slider-message").click(function(){
    jQuery(this).stop(true, true).fadeOut();
    jQuery(this).fadeOut(1000, function() {
        messageElement.css("right", "-500px");
    });
})

function show_slider_message(text) {
    var messageElement = jQuery(".slider-message");
    messageElement.find(".text").text(text);
    if(jQuery(window).width() < 767 ){
        messageElement.css("width","80vw");
    }
    else {
        messageElement.css("width", "400px");
    }

    messageElement.show();
    messageElement.attr("display", "block");
    messageElement.animate(
        {display: "block", right: "80px", opacity: "0.95"}
        , 500
        ,
        function () {
            messageElement.fadeOut(5000, function() {
                messageElement.css("right", "-500px");
            });
        }
    );
}

function call_ajax_function(postData,func) {

    xhr = jQuery.ajax({
        url: "/wp-admin/admin-ajax.php",
        data: postData,
        dataType: 'json',
        method: 'POST'
    }).done(function (result) {
        if(result.message){
            show_slider_message(result.message);
        }
        //window[func]($form, data);
        if(func) {
            window[func](result);
        }
    })
}

function onchangeSelect(e,element,value){
    if(jQuery(element).hasClass("city_id")){
        var selectedOption = jQuery(element).find('option:selected');
        var value = selectedOption.val();
        var extraData = selectedOption.data('field');

        var postData = [
                {name: "filter", value: "work_area_id = "+extraData},
                {name: "action", value: "get_list_ajax"},
                {name: "table_name", value: "agents"},
            ];
        call_ajax_function(postData,"fillAgentsSelect");
    }
}

function fillAgentsSelect(result){
    var options = result.options;
    var select = jQuery("select[name=agent_id]");
    select.children().remove();
    select.append(result.options);
}

function fillListTable(result){
    if(result.tableData) {
        jQuery(".list-table").html(result.tableData);
        jQuery(".page-title").html(result.options["title"]);
        show_tooltip();
    }
    else{
        jQuery(".list-table").html("");
    }
}

function automaticOrderSaving(){

    if (jQuery('input[name=dirty]').val() == "1" && jQuery('input[name=order_date]').val()
        && jQuery('.grid-display select[name=client_id]').val()) {

        var inputs = jQuery('.grid-display .input-label table tr td.count input');
        var count = jQuery.grep(inputs, function (input, k) {
            return jQuery(input).val() > 0;
        })
        if (count.length > 0) {
            jQuery('input[name=dirty]').val("0");
            var $form = jQuery('form');
            if (!$form.valid()) {
                return;
            }

            var formData = $form.serializeArray();

            formData.push({
                name: "action",
                value: "send_site_forms"
            });

            call_ajax_function(formData, "fillOrderId");
        }
    }
}
function fill_modal_list(result){
    jQuery(".modal-body select").html(result.options);
}
function getTableAjaxData(tableName){
    if(jQuery(tableName).is("form")){
        closeModal();
        var selected = jQuery("ul.tables-list li.selected")
        tableName = selected.data("list-name");
    }
    var postData = [
        {name: "format", value: "table"},
        {name: "action", value: "get_list_ajax"},
        {name: "table_name", value: tableName},
    ];
    call_ajax_function(postData,"fillListTable");
}
function openModal(modalId,message){
    jQuery(modalId+'.modal').modal('show');
}
function closeModal(){
    jQuery('.modal.show').modal('hide');
}