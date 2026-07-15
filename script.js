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
    jQuery('input[data-a-sign=₪]').autoNumeric('init', {
        vMin: '-9999999999999',
        mDec: 1,
        aSign: ' ₪',
        wEmpty: 'empty'
    });
    jQuery('input[data-a-sign="%"]').autoNumeric('init', {
        vMin: '-9999999999999',
        mDec:0,
        wEmpty: 'empty',
        aSign: '%',
        pSign: 's'
    });

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

        if (getParameterByName("subject") == "orders" && jQuery('.page.single').length > 0){//עמוד הזמנה
            var rows = jQuery('tr').filter(function () {
                var $row = jQuery(this);

                return jQuery.trim($row.find('td.count input').val() || '') === '' &&
                    jQuery.trim($row.find('td.id input').val() || '') === '';
            });
            rows.find('td input').prop('disabled', true);
        }

        $form.addClass('disabled').find('[type="submit"]').prop('disabled', true);
        //grecaptcha.execute(globalVars.recaptcha_key, {action: 'submit'})
        //.then(function (token) {
        $form.find('#form_error_msgs_container').html('');

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
        var currentChooser = ellipse.closest(".status-options");
        var input = currentChooser.find("input");

        if(!ellipse.hasClass("un-value")){//היה בחור ורצים לבטל הבחירה
            ellipse.addClass("un-value");
            input.val("").trigger("change");
        }
        else{//רוצים לבחור את מי שלחצתי עכשיו
            currentChooser.find(".ellipse").addClass("un-value");
            ellipse.removeClass("un-value");
            input.val(ellipse.data("value")).trigger("change");
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
            {name: "client_id", value: getSelectClientId().val()},
        ];
        call_ajax_function(postData,"onOrderConfirmation");
    })

    jQuery('#accept_process_modal').on('hide.bs.modal', function (e) {
        jQuery('.site_form').find('[type = "radio"], [type = "checkbox"]').prop('checked', false);
        jQuery('.site_form').find('input, select').not(dont_reset_val).val('');
    });
    jQuery('#edit-list').on('show.bs.modal', function (e) {
        var btn = jQuery(e.relatedTarget), comboListName,html='', tr,rowData=[];

        var listName= "",title="";
        if(window.location.pathname.includes('single')){
            listName = btn.data("list-name");
            title = btn.closest("div").find("label").html();
        }
        else{
            listName = jQuery("ul.tables-list li.selected").data("list-name");
            title = jQuery("ul.tables-list li.selected").html();
        }

        jQuery('#edit-list .modal-body').addClass(listName)

        jQuery("#edit-list input[name=table_name]").val(listName);
        jQuery("#edit-list .modal-title").text(title);
        if(window.location.pathname.includes('single')){//הוספת עיר בתוך עמוד לקוח
            html = '<span class="input-label flex-display align-center">' +
                ' <label class="bold" for="text">' + btn.data("single") + ':</label>'+
                '<input type="text" id="text" name="text"  class="font-17 grow"' +
                '</span>';
        }
        else {
            if (btn.data("action") == "edit") {
                tr = btn.parent().parent();
                jQuery('.list-table tbody tr').removeClass("current");
                tr.addClass("current");
                rowData = jQuery.map(jQuery(tr).find('td:not(.td-action)'), function (td) {
                    return jQuery(td).html();
                });
                jQuery('#edit-list input[name=id]').val(tr.data("id"));
            }

            jQuery('table.list-table tr:first-child th:not(.no-sort)').each(function (k, th) {
                th = jQuery(th)
                var columnName = th.data("column-name");
                var columnType = th.data("column-type") ?? "text";
                html += '<div class="input-label flex-display align-center '+columnName+'">' +
                    ' <label class="bold" for="' + columnName + '">' + th.text() + ':</label>';

                if (th.data("column-type") == "select") {
                    html += '<select id="' + columnName + '" name="' + columnName + '"  class="font-17 grow"' +
                        (rowData.length > 0 ? ' value="' + rowData[k] + '"' : '') + '>';
                    if(th.data("table") ) {
                        comboListName = th.data("table");
                        var postData = [
                            {name: "action", value: "get_list_ajax"},
                            {name: "table_name", value: comboListName},
                            {name: "selected_value", value: rowData[k]}
                        ];
                        call_ajax_function(postData, "fill_modal_list");
                    }
                    else if (th.data("column-options")){//הליסט לא מטבלה אלא מרשימה בקוד
                        //var jsonOptions = th.data("options").replaceAll('"', "'");
                        //console.log("aaaa  " + th.data("column-options"));
                        var options = th.data("column-options");
                        jQuery.each(options,function (){
                            var option = this;
                            html += '<option value="'+option.value+'" '+(rowData[k] ==  option.value ?  "selected" : "") +'>'+option.text+'</option>';
                        })
                    }
                    html += '</select>';
                }
                else if (th.data("column-type") == "special") {

                }
                else {
                    html += '<input type="'+columnType+'" id="' + columnName + '" name="' + columnName + '"  class="font-17 grow"' +
                        (rowData.length > 0 ? ' value="' + rowData[k] + '"' : '') + '>';
                }
                html += '</div>';
            });


        }
        jQuery('#edit-list .modal-body').empty();
        jQuery('#edit-list .modal-body').append(html);

        if(listName == "specials"){
            jQuery('#edit-list .modal-body input[name=price_more]').closest("div").addClass("hidden");
            jQuery('#edit-list .modal-body select[name=type]').on("change",function (){
                if(jQuery(this).val()==1){
                    jQuery('#edit-list .modal-body input[name=price_more]').closest("div").addClass("hidden");
                    jQuery('#edit-list .modal-body input[name=buy]').closest("div").removeClass("hidden");

                }
                else{
                    jQuery('#edit-list .modal-body input[name=price_more]').closest("div").removeClass("hidden");
                    jQuery('#edit-list .modal-body input[name=buy]').closest("div").addClass("hidden");
                }
            })
        }
    });
    jQuery('#edit-list').on('shown.bs.modal', function () {
        jQuery('#edit-list .modal-body input:first-child').focus();
    })


    jQuery('#bout-massage').on('hide.bs.modal', function (e,a) {
        jQuery(this).find('button.ajax-button').addClass("hidden");
        jQuery(this).find('button[type=submit]').show();
        jQuery(this).find('[name="remove"]').val(0);
    });

    jQuery("#bout-massage button.ajax-button").click(function (){
        //לקחת את הפונקציה של האז'אקס
        var postData = [
            {name: "action", value: jQuery("#bout-massage").find('[name="form_func"]').val()},
            {name: "id", value: jQuery("#bout-massage").find('[name="id"]').val()},
        ];
        call_ajax_function(postData);
        closeModal()
    })

    jQuery('#bout-massage').on('show.bs.modal', function (e) {
        //action = remove
        var title,body;
        var btn = jQuery(e.relatedTarget);
        if(btn.data("ajax_func")){
            title = btn.text().trim();
            jQuery(this).find('button.ajax-button').removeClass("hidden");
            jQuery(this).find('button[type=submit]').hide();
            body = btn.data("text");
            jQuery(this).find('[name="form_func"]').val(btn.data("ajax_func"));
            jQuery(this).find('[name="id"]').val(btn.closest("tr").data("id"));
        }
        else {
            var subject = getParameterByName("subject");

            var single = jQuery("body").find("section").data("single");
            title = jQuery("body").find(".page-title").html()
            var class_table = subject == "lists" ? "list-table" : "archive-table";

            if (subject == "lists") {
                subject = jQuery("ul.tables-list li.selected").data("list-name");
                title = jQuery("ul.tables-list li.selected").html();
                //single = jQuery(".list-table .tr-head th:first-child").html();
            }

            if (getParameterByName("action") == "edit") {
                id = getParameterByName("id");
            } else if (btn.is('a')) {
                var tr = btn.parent().parent();
                id = tr.data("id");
                jQuery('.' + class_table + ' tbody tr').removeClass("current");
                //".list-table .tr-head th:first-child"
            //.children("td").not(".myClass").first();
                single = single +" "+ tr.children("td").not(".td-action").first().html();

               // single = single +" "+ jQuery('.' + class_table + ' tbody tr td:not(.td-action):first-child').html();
                tr.addClass("current");
            }

            jQuery(this).find('[name="remove"]').val(1);
            jQuery(this).find('[name="form_func"]').val("save_single_data");
            jQuery(this).find('[name="table_name"]').val(subject);


            jQuery(this).find('[name="id"]').val(id);
            body = "האם אתה מאשר למחוק את ה" + single + "?";
        }

        jQuery("#bout-massage .modal-title").html(title);
        jQuery("#bout-massage .modal-body").html(body);


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
    //const url = new URL(jQuery(".export-link").attr("href"));
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
    }
    jQuery('.export-link').each(function() {
        let url = new URL(this.href);
        if(text.length > 0) {
            url.searchParams.set('ids', ids);
        }
        else{
            url.searchParams.delete('ids');
        }
        this.href = url.toString();
    });
    //jQuery(".export-link").attr("href",url.toString());
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
    if(text) {
        var messageElement = jQuery(".slider-message");
        messageElement.find(".text").html(text);
        messageElement
            .css({
                display: "block",
                top: "-200px",
                opacity: 0
            })
            .animate({
                top: "45%",
                opacity: 0.95
            }, 1000);

        setTimeout(function () {
            messageElement.animate({
                top: "-200px",
                opacity: 0
            }, 500, function () {
                jQuery(this).hide();
            });
        }, 5000);
    }
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
        jQuery(".list-area").html(result.tableData);
        jQuery(".page-title").html(result.options.title);
        jQuery("section.page").attr("data-single", result.options.single);
        show_tooltip();
    }
    else if(result.options){
        jQuery("select.subject").children().remove();
        jQuery("select.subject").append(result.options);

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
    if(result.options) {
        jQuery(".modal-body select[name=" + result.tableName.slice(0, -1) + "_id]").eq(0).html(result.options);
        if (result.tableName == "suppliers" && result.options && result.options.includes("selected")) {
            onSelectSupplier(5);
        }
    }
    else if(result.checkboxes){
        jQuery(".modal-body ."+result.tableName).eq(0).append(result.checkboxes);
    }
}
function onSelectSupplier(supplier_id){

    var postData = [
        {name: "action", value: "get_list_ajax"},
        {name: "table_name", value: "products"},
        {name: "format", value: "checkboxes"},
        {name: "selected_value", value: /*rowData[k]*/""},
        {name: "filter", value: "supplier_id = "+supplier_id}
    ];
    call_ajax_function(postData, "fill_modal_list");
}


function getTableAjaxData(tableName) {
    format = "table";
    if (window.location.pathname.includes('single')) {
        closeModal();
        tableName="subjects";
        format = "options";
        //selected_value =
    } else {
        if (jQuery(tableName).is("form")) {
            closeModal();
            var selected = jQuery("ul.tables-list li.selected")
            tableName = selected.data("list-name");
        }
    }
    var postData = [
        {name: "format", value: format},
        {name: "action", value: "get_list_ajax"},
        {name: "table_name", value: tableName},
    ];
    call_ajax_function(postData, "fillListTable");

}
function openModal(modalId,message){
    jQuery(modalId+'.modal').modal('show');
}
function closeModal(){
    jQuery('.modal.show').modal('hide');
}