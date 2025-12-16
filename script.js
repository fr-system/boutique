
jQuery(document).ready(function($){


// logout
jQuery('.logout_button').click(function(){
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

    jQuery('.popup .btn.cancel').click(function(){
        closePopup();
    });

    jQuery('body').on('submit', '.site_form', function(e){
        e.preventDefault();
        var $form = jQuery(this);
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

})

function reload_page($form, data){
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