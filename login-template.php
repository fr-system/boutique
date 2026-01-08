<?php /* Template Name: login */
/*if(is_user_logged_in()){
    if(is_manager()){
        wp_redirect(get_site_url());
        exit;
    }
    else{
        wp_redirect(get_site_url());
        exit;
    }
}*/
?>
<?php wp_head();

get_header();?>

<div id="primary" class="login-container">
    <section class="login-section flex-display">
        <div class="login-image part-80">
        </div>
        <div class="login-area part-50 background-black flex-display direction-column space-between">
            <form id="login_form" class="site_form flex-display direction-column space-between" data-success="reload_page" data-error="show_error_messages">
                <input type="hidden" name="form_func" value="login">
                <div class="font-60">ברוכים הבאים</div>
                <div class="font-25">בואו נתחבר</div>
                <input class="font-18" required="" type="text" name="username"  autocomplete='off' placeholder="מייל">
                <input class="font-18" required="" type="password" name="password"  autocomplete='off' placeholder="סיסמא">
                <div class="pointer" id="login_forgot_password_btn">שכחתי סיסמא</div>
                <button type="submit" class="btn-login font-18 bold">נכנסתי</button>
                <div id="form_error_msgs_container" class="red" style="margin-bottom: 10px;"></div>
            </form>
            <?//= forgot_password_form()?>
        </div>
    </section>
</div>

