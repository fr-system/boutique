let xhr;
function getParameterByName(name){
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    var value = results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    return value;
}
jQuery(document).ready(function($){

    jQuery('input[data-a-sign=₪]').autoNumeric('init', { vMin: '-9999999999999.99' });

    jQuery("form.site_form").validate({
        rules: {
            'input, textarea':{ required: true},
            /*name: {
                required: true,
                minlength: 2
            },*/
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
            /*'input, textarea': {
                required: function () {
                    return "אנא הזן ערך עבור " + jQuery(this).attr("name");
                }
            },*/
           /* name:{
                required:true,
            },*/
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

        },
       /* submitHandler: function(form) {
            form.submit(); // שלח את הטופס אם הוולידציה מצליחה
        }*/
    });

    jQuery('.user-logged').click(function(){
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

    jQuery('.popup .button.cancel').click(function(){
        closePopup();
    });

    jQuery('body').on('submit', '.site_form', function(e){
        e.preventDefault();
        var $form = jQuery(this);
        if (!$form.valid()) {
            return;
        }

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
                window[func]($form, data);
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

    jQuery(".archive-table .remove-row").click(function(){
        var id = jQuery(this).parent().parent().data("id");
        var postData = [
            {name: "id", value: id},
            {name: "remove", value: true},
            {name: "action", value: "build_query_boutique"},
            {name: "table_name", value: getParameterByName("subject")},
        ];
        call_ajax_function(postData,"remove_row",id);

    })

    jQuery('.open-file-uploader, .file-name').click(function () {
        jQuery('input.upload-file').click();
    });

    jQuery('input.upload-file').change(function () {
        jQuery('.file-name').text(this.files[0].name);

    });

    jQuery('.open-image-uploader , .image-name').click(function () {
        jQuery('input.upload-image').click();
    });

    jQuery('input.upload-image').change(function () {
        jQuery('.image-name').text(this.files[0].name);
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = jQuery('.protuct-image');
                e.target.result.replace("data:image/png;base64,","");
                //img.src = e.target.result;
                img.attr('src', e.target.result).show(); // מציג את התמונה

                //img.style.display = 'block'; // מציג את התמונה
            }
            reader.readAsDataURL(file);
        }

    });
    jQuery('select.list-combo').change(function () {
        //var listName=this.value;
        var postData = [
            {name: "table_display", value: "1"},
            {name: "action", value: "get_list_ajax"},
            {name: "table_name", value: this.value},
        ];
        call_ajax_function(postData,"fillListTable","list-table");
        //call_ajax_function(postData,"get_list",id);
    });

    jQuery('[data-view]').click(function () {
        var postData = [
            {name: "action", value: "update_user_meta_value"},
            {name: "meta_key", value: "products_view" },
            {name: "meta_value", value: jQuery(this).data("view")},
        ];
        call_ajax_function(postData,"reload_page");
    });
})

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
    jQuery('.popup .popup-title svg').hide();
    if(name == "task"){
        jQuery('.icon-task').show();
    }
    else if(name == "client") {
        jQuery('.icon-client').show();
    }

    jQuery('html').addClass('popup_open');
    jQuery('.popup_page_overlay').find('form').addClass("hide");
    jQuery('.popup_page_overlay').find('form.'+name+'-popup').removeClass("hide");
    jQuery('.popup').find('.popup-title div').text(title);

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
        window[func](result,targetElement);
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



function remove_row(result,id){
    var tr = jQuery(".archive-table tr[data-id="+id+"]");
    tr.remove();
}
function fillListTable(result,targetElement){
    if(result.tableData) {
        jQuery("." + targetElement).html(result.tableData);
    }
}