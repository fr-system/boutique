<?php /* Template Name: Reset-Password */ ?>
<?php get_header(); ?>
    <section class="page reset-password-section background-white flex-display direction-column align-center">
        <div class="font-25 bold gold margin-bottom-20 ">איפוס סיסמא</div>
        <?php
        $error_msg ='';
        // קודם כל - בדיקה האם הקישור תקין ובתוקף
        $user = get_user_by('email', fixXSS($_GET['login']));
        if (!$user|| is_wp_error($user)){
            $error_msg = "אתה לא רשום כמשתמש בתוכנה";
        } else {
            $user_info  = get_userdata( $user->ID );
            $user_login = $user_info->user_login;
            $check = check_password_reset_key(fixXSS($_GET['key']), $user_login);
            if ($check->get_error_code()) {
                $error_msg = "עבר התוקף של הקישור לחץ שוב לשליחה";
            }
        }
        if($error_msg) {
            echo "    
        <div class=''>
            <div class ='reset_password_error_msg'>
                <div class='margin-bottom-20'>$error_msg</div>
                <div class='flex-display center'>
                    <button  id='reset_password_link_btn' class='btn-login grow font-20 bold background-gold  margin-bottom-20'>שלח קישור מחדש</button>
               </div>
            </div>".
                display_forgot_password_form(false).
                "</div>
        <script> jQuery('#reset_password_link_btn').click(function(){
            jQuery('.forgot-password-form').removeClass('d-none')
            jQuery('.reset_password_error_msg').addClass('d-none')
        });</script>";

        } else {
            // במידה והקישור אכן תקין - הצגת טופס איפוס סיסמה
            echo "
        <form class='site_form row' novalidate data-success='reload_page' data-failed='show_error_messages'>
              <input type='hidden' name='form_func' value='_reset_password' />
              <input type='hidden' id='user_login' name='user_id' value='" . $user->ID . "' autocomplete='off'/>

            <div class='flex-display direction-column margin-bottom-30'>
                <span class='bold text-center margin-bottom-10'>נא לבחור סיסמא חדשה</span>
                <span class='text-center margin-bottom-20'>הסיסמה תשמש מעתה להתחברות</span>
                <input type='hidden' name='rp_key' value='" . fixXSS($_REQUEST['key']) . "'/>
                ";
            wp_nonce_field( 'ajax-resetpassword-nonce', 'security' );
            ?>
            <div class='flex-display space-between margin-bottom-10'>
                <label for="password1" class="margin-after-10">סיסמא חדשה:</label>
                <input class="border-dark-gray" type='password' name='password1'/>
            </div>
            <div class='flex-display space-between margin-bottom-40'>
                <label for="password2" class="margin-after-10">אימות סיסמה חדשה:</label>
                <input class="border-dark-gray" type='password' name='password2'/>
            </div>
            <div id="form_error_msgs_container"></div>
            <div class="flex-display center margin-bottom-20">
                <button type="submit" class="btn-login font-18 bold background-gold ">שמירה</button>
            </div>
            </div>
            </form>

            <div id="success_msg_of_form" class="d-none">נשלח אליך קישור לאיפוס סיסמה לכתובת המייל שהזנת</div>
            <?php
        } ?>
    </section>

