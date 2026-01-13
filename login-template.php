<?php /* Template Name: login */
if(is_user_logged_in()){
    if(is_manager()){
        wp_redirect(get_site_url()."/archive/?subject=clients");
        exit;
    }
    else{
        wp_redirect(get_site_url()."/archive/?subject=tasks");
        exit;
    }
}
?>
<?php wp_head();
?>
    <section class="login-section flex-display">
        <div class="login-rigth part-50">
            <div class="login-image"></div>
        </div>
        <div class="login-area part-50 background-black flex-display end direction-column">
            <form id="login_form" class="site_form flex-display direction-column space-between" data-success="reload_page" data-error="show_error_messages">
                <input type="hidden" name="form_func" value="login">
                <div class="font-40 bold">ברוכים הבאים</div>
                <div class="font-17">בואו נתחבר</div>
                <input class="font-18 border-dark-gray" required="" type="text" name="username"  autocomplete='off' placeholder="מייל">
                <input class="font-18 border-dark-gray" required="" type="password" name="password"  autocomplete='off' placeholder="סיסמא">
                <div class="flex-display space-between align-center">
                    <button type="submit" class="btn-login background-gold font-18 bold">נכנסתי</button>
                    <div class="underline pointer text-align-end" id="login_forgot_password_btn">שכחתי סיסמא</div>
                </div>

                <div id="form_error_msgs_container" class="red" style="margin-bottom: 10px;"></div>
            </form>
            <?php echo display_forgot_password_form()?>
            <!--<form id="registration" class="site_form flex-display direction-column space-between" data-success="reload_page" data-error="show_error_messages">
                <input type="hidden" name="form_func" value="register">
                <div class="font-18">חדש פה? <button type="submit" class="underline pointer font-18" id="">הירשם</button></div>
                <div id="form_error_msgs_container" class="red" style="margin-bottom: 10px;"></div>
            </form>-->
        </div>
    </section>
<?php

