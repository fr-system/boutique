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

    if(getParameterByName("subject") == "orders" && getParameterByName("action") && getParameterByName("action") != "readonly")
    setInterval(function() {
        if(jQuery('input[name=dirty]').val() =="1" && jQuery('input[name=order_date]').val()
            && jQuery('.grid-display select[name=client_id]').val() && jQuery('.grid-display .border-dark-gray.product').length > 0){
            jQuery('input[name=dirty]').val("0");
            if(getParameterByName("action") == "new"){

            }
            automaticOrderSaving();
            //לשאול את פרידי לבדוק אם נגעו ב-2 דקות האלו לשמור ואם לא אז לא לשמור אולי לעשות שרק אם עזבו את המסך ולא נגעו בו כבר יותר מ2 דקות אז ללכת לשמירה
        }
    }, 120000); // 120000 מילישניות = 2 דקות

    jQuery('input, select, textarea').change(function (){
        //לבדוק אם רק הוסיפו מוצר חדש וכמות וכו'
        // לבדוק שבכל האפשרויות הוא רואה שהרשומה עודכנה
        jQuery('input[name=dirty]').val("1");
    })

    jQuery('a, button:not(.save), .logout-button ').click(function(e) {
        if(jQuery('input[name=dirty]').val() == "1"){
            const confirmation = confirm('הערכים עדיין לא נשמרו. האם אתה בטוח שברצונך לצאת ללא שמירה?');
            if (!confirmation) {
                e.preventDefault(); // מניעת לחיצה אם המשתמש לא מאשר
            }
        }
    });

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

    jQuery('body').on('submit', '.site_form', function(e){
        e.preventDefault();
        var $form = jQuery(this);
        if (!$form.valid()) {
            return;
        }

        if(jQuery('.page.single').length > 0){
            if(jQuery('.products-gallery .product').length ==0){//לא לשמור הזמנה שאין בה מוצרים!!!!
            }


        }

        jQuery(".products-gallery .products-last-order").click(function (){
            var client_id = jQuery(".page.single select[name=client_id]").val();

            jQuery(".page.single

        $form.addClass('disabled').find('[type="submit"]').prop('disabled', true);
        //grecaptcha.execute(globalVars.recaptcha_key, {action: 'submit'})
        //.then(function (token) {
        $form.find('#form_error_msgs_container').html('');

        var formData = $form.serializeArray();

        formData.push({
            name: "action",
            value: "send_site_forms"
        });
        /*formData.push({
            name: "recaptcha_token",
            value: token
        });*/

        if (xhr && xhr.readyState != 4)
            xhr.abort();
        xhr = jQuery.ajax({
            url: "/wp-admin/admin-ajax.php",//globalVars.ajaxurl,
            data: formData,
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


    jQuery('.open-file-uploader, .file-name').click(function () {
        jQuery('input.upload-file').click();
    });

    jQuery('input.upload-file').change(function () {
       /* jQuery('.file-name').text(this.files[0].name);*/

    });

    jQuery('.open-image-uploader , .image-name').click(function () {
        jQuery('input.upload-image').click();
    });

    jQuery('input.upload-image').change(function () {
        /*jQuery('.image-name').text(this.files[0].name);*/
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = jQuery('.protuct-image');
                e.target.result.replace("data:image/png;base64,","");
                //img.src = e.target.result;
                img.attr('src', e.target.result).show(); // מציג את התמונה
                img.removeClass("hidden");
                //img.style.display = 'block'; // מציג את התמונה
            }
            reader.readAsDataURL(file);
        }

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

    jQuery('.add-order-product svg').click(function () {
        if(jQuery('.grid-display select[name=client_id]').val()) {

            var postData = [
                {name: "action", value: "view_catalog_gallery_ajax"},
                {name: "client_id", value: jQuery('.grid-display select[name=client_id]').val()},
            ];
            call_ajax_function(postData, "openPopupAddOrderProduct");
        }
        else{
            var $form = jQuery('.page.single form.site_form');
            $form.valid();
        }
    })

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
        ];
        call_ajax_function(postData,"onOrderConfirmation");

    })

    jQuery(".page .products-gallery .product .plus-minus-count div").click(function (e) {
        plusMinusCountProduct(e)
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
        var product = jQuery('.products-gallery .product[data-id='+id+']');
        product.addClass("hidden");
        product.find("input.input-remove").val("1");
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

        if(subject == "orders" && getParameterByName("action")=="edit" && btn.parent().parent().hasClass("product")){
            single = "מוצר מההזמנה";
            jQuery(this).find('button.remove-product-order').removeClass("hidden");
            jQuery(this).find('button[type=submit]').hide();
            id = btn.parent().parent().data("id");
        }
        else{
            jQuery(this).find('[name="remove"]').val(1);
            jQuery(this).find('[name="form_func"]').val("build_query_boutique");
            jQuery(this).find('[name="table_name"]').val(subject);
        }

        jQuery(this).find('[name="id"]').val(id);
        jQuery("#bout-massage .modal-title").html(title);
        jQuery("#bout-massage .modal-body").html("האם אתה מאשר למחוק את ה"+single+"?");
    });

    jQuery(".page.single select[name=client_id]").change(function (){
        if(jQuery(".products-gallery .product").length == 0){
            jQuery(".products-gallery .products-last-order").removeClass("hidden");
        }

    })

    jQuery(".products-gallery .products-last-order").click(function (){
        var client_id = jQuery(".page.single select[name=client_id]").val();
        if(client_id) {
            var postData = [
                {name: "action", value: "get_products_last_order"},
                {name: "client_id", value: client_id}
            ];
            call_ajax_function(postData, "fillProductsLastOrder");
        }
    })

})

function fillProductsLastOrder(result) {
    jQuery(".add-order-product").after(jQuery(result.products));
    jQuery(".products-gallery .products-last-order").hide();
}

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

function onAddChat(result,targetElement){
    var chatList = jQuery(".chat-list");
    chatList.append(jQuery(' <div class="input-label flex-display space-between">'+
        '<div class="part-20"><img class="user-logo" src="'+result.client_logo+'"></div>'+
        '<div class="text part-50 text-center">'+jQuery( "#newChat" ).val()+'</div>'+
        '<div class="date text-left part-20">'+result.time +'</div></div>'));

    if (chatList.length) {
        chatList.scrollTop(chatList[0].scrollHeight);
    }
    jQuery( "#newChat" ).val("");
}

function searchElements(text,selector,searchSelector){

    jQuery(selector).show();
    if(text.length > 0) {
        jQuery.each(jQuery(selector), function (k) {
            var product = jQuery(this);
            if (product.find(searchSelector+':contains(' + text + ')').length) {
                product.show();
            } else {
                product.hide();
            }
        })
    }
}

    function openPopupAddOrderProduct(result,targetElement){
        jQuery(".popup-body").empty();
        jQuery(".popup-body").html(result.html);

        jQuery('.popup-body #search').on("keyup",function(event){
            var text = jQuery(this).val();
            //searchProducts(text,".popup-body .product");
            searchElements(text, ".popup-body .products-gallery.catalog .product",".product-name");
        });

        jQuery('.popup-body button.order-product').click(function () {
            var element = jQuery(this).parent().parent().clone();
            var id = jQuery(this).parent().parent().data("id");

            element.addClass("current");
            element.find("a, .order-product").addClass("hidden");
            var key = 0;
            jQuery.each(jQuery(".page .products-gallery .product"), function (k) {
                var product = jQuery(this);
                var name = product.find('[name*="][product_id]"]').attr('name');
                if(name) {
                    var a = parseInt(name.substring(name.indexOf("[") + 1, name.indexOf("]")))
                    if (a > key) {
                        key = a;
                    }
                }
            })

            var order_id = jQuery(".page.single input[name=id]").val();
            key++;
            element.prepend(
                '<input type="hidden" name="products['+key+'][id]" value="">'+//id של השורה של מוצר_הזמנה
                '<input type="hidden" name="products['+key+'][order_id]" value="'+order_id+'">' +
                '<input type="hidden" name="products['+key+'][product_id]" value="'+id+'">');
            //element.find("input[name*=\"][product_id]").prop("name","products["+key+"][product_id]");
            //element.find("input[name*=\"][count]").prop("name","products["+key+"][count]");
            element.find('.plus-minus-count').removeClass("hidden");
            element.find('.plus-minus-count input').prop("name","products["+key+"][count]");
            element.find('.discount_percent-bonus').removeClass("hidden");
            element.find('.discount_percent-bonus input[type="text"]').prop("name","products["+key+"][discount_percent]");
            element.find('.discount_percent-bonus input[type="text"]').prop("name","products["+key+"][bonus]");


            jQuery('input[name=dirty]').val("1");
            jQuery(".add-order-product").after(element);

            jQuery(".page .products-gallery .product.current .plus-minus-count div").click(function (e) {
                plusMinusCountProduct(e);
            })
            //jQuery(".products").prepend(element);
            closePopup();

        })
    openPopup();

}

function plusMinusCountProduct(e){
    var numberInput = jQuery(e.currentTarget).parent().find("input");
    var currentValue = parseInt(numberInput.val()) || 0;

    if(e.currentTarget.classList[0] == "plus"){
        numberInput.val(currentValue + 1);
    }
    else{
        if(currentValue > 0) {
            numberInput.val(currentValue - 1);
        }
    }
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

function show_slider_message(options) {
    var messageElement = jQuery(".slider-message");
    messageElement.find("h1").text(options.message|| "");
    messageElement.find(".secondary-text").text(options.subMessage || "");
    if(jQuery(window).width() < 767 ){
        messageElement.css("width","80vw");
    }
    else {
        messageElement.css("width", options.width || "400px");
    }

    messageElement.show();
    messageElement.attr("display", "block");
    messageElement.animate(
        {display: "block", right: "80px", opacity: "0.95"}
        , 500
        ,
        function () {
            messageElement.fadeOut(10000, function() {
                messageElement.css("right", "-500px");
            });
        }
    );
}

function call_ajax_function(postData,func,targetElement) {

    xhr = jQuery.ajax({
        url: "/wp-admin/admin-ajax.php",
        data: postData,
        dataType: 'json',
        method: 'POST'
    }).done(function (result) {
        //window[func]($form, data);
        if(func) {
            window[func](result, targetElement);
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
        call_ajax_function(postData,"fillAgentsSelect","agent_id");
    }
}

function fillAgentsSelect(result,targetElement){
    var options = result.options;
    var select = jQuery("select[name="+targetElement+"]");
    select.children().remove();
    select.append(result.options);
}

function fillListTable(result,targetElement){
    if(result.tableData) {
        jQuery("." + targetElement).html(result.tableData);
        jQuery(".page-title").html(result.options["title"]);
    }
    else{
        jQuery("." + targetElement).html("");
    }
}

function automaticOrderSaving(){
    var $form = jQuery('form');
    if (!$form.valid()) {
        return;
    }

    var formData = $form.serializeArray();

    formData.push({
        name: "action",
        value: "send_site_forms"
    });

    call_ajax_function(formData);
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
    call_ajax_function(postData,"fillListTable","list-table");
}
function openModal(modalId,message){
    //jQuery(modalId+'.modal').modal('show');
}
function closeModal(){
    jQuery('.modal.show').modal('hide');
}

function onOrderConfirmation(result){
    openModal("#bout-massage",result.notice);

}