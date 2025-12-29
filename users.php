<?php
function function_login()
{
    //error_log("function_login");

    $email = fixXSS($_POST['username']);
    $pass = fixXSS($_POST['password']);

    $user = wp_authenticate($email, $pass); // בדיקה האם מייל וסיסמה תקינים

    if (!is_wp_error($user)) {
        $creds = array(
            'user_login' => $email,
            'user_password' => $pass,
            'remember' => true
        );

        $user = wp_signon($creds, false);
        if(get_user_role($user)=="administrator"){
            $redirect = 'clients';
        }
        else{
            $client_id = get_user_meta($user->ID, 'client_id',true);
            $redirect = 'tasks-list/?clientId='.$client_id;
        }

        echo json_encode(array(
            'status' => 'success',
            'redirect' => get_site_url() .'/'.$redirect
        ));

    } else {
        if (username_exists($email)) {
            // אם הפרטים שהגולש הזין לא תקינים - חוזרת הודעת שגיאה
            echo json_encode(array(
                'status' => 'error',
                'msg' => 'שם המשתמש או הסיסמה אינם מזוהים'
            ));
        } else {
            // אם לא קיים משתמש במערכת - חוזרת הודעה על אפשרות רישום
            echo json_encode(array(
                'status' => 'error',
                'reason' => 'no_registration',
                'msg' => 'אינך מזוהה במערכת, פנה למנהל שמחה רוטנברג',
            ));
        }
    }
    die();

}

add_action('wp_ajax_user_logout', 'user_logout');
function user_logout()
{
    wp_logout();
    die();
}

function get_user_role($user = null){
    return get_user_connected($user)->roles[0];
}
function is_manager($user = null){
    return get_user_role($user) == "administrator";
}
function get_user_display_name($user_obj = null){
    return get_user_connected($user_obj)->data->display_name;
}
function get_user_connected($user = null)
{
    global $current_user;
    $user = $user ? $user : $current_user;
    if (!$user) {
        $user = wp_get_current_user ();
    }
    return $user;
}

function get_reset_password_page(){
    return get_site_url().'/reset-password';
}

function function_send_password_reset_link() {

    //check_ajax_referer( 'ajax-lostpass-nonce', 'security' );
    $user_email = sanitize_text_field( $_POST['email'] );

    $user = get_user_by( 'email', $user_email );
    if ( $user instanceof WP_User ) {
        $user_id    = $user->ID;
        $user_info  = get_userdata( $user_id );
        $unique     = get_password_reset_key( $user_info );
        $unique_url = get_reset_password_page() . "?action=reset_pass&key=$unique&login=" . rawurlencode( $user_email );

        $subject = "איפוס סיסמה בתוכנה של שמחה רוטנברג";

        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= "From: ".get_option("blogname")." <wordpress@simcha.f-rotenberg.com> \r\n";
        $headers .= "Reply-To: ".get_option("blogname")." <".get_option('admin_email')."> \r\n";
        $to = $user_email;

        $message = '<div style="direction: rtl;font-family:Georgia,Verdana; font-size:25px; color:black"">
                        <span style="font-weight: bold; color: #0166FF">'.get_user_display_name($user).',</span><br><br>
                            התקבלה בקשה לאיפוס הסיסמא<br><br>
                            <a 
                            style="background-color:#0166FF; color:white; display: block; padding: 0 20px;width: fit-content; text-decoration: none; font-weight: 700; 
                            border-radius: 64px; border: 1px solid #0166FF;" 
                            href="'.$unique_url.'">לסיסמא חדשה לחץ כאן 
                            <svg xmlns="http://www.w3.org/2000/svg" width="102" height="21" viewBox="0 0 102 21" fill="none" class="">
                                <path d="M101 10H2" stroke="#0166FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M11 19.0328L1 10.0164L11 1" stroke="#0166FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                            </a>
                    </div>';

        $ok = wp_mail( $to, $subject, $message, $headers );

        echo json_encode( array(
            'status' => 'success',
            'ok' => $ok
        ) );
        die;
    } else {

        echo json_encode( array(
            'status' => 'failed',
            'msg'    => 'אתה לא רשום במערכת'
        ) );
        die;
    }
}

function function_reset_password() {
    //check_ajax_referer( 'ajax-resetpassword-nonce', 'security' );

    $pass    = fixXSS( $_POST['password1'] );
    $secpass = fixXSS( $_POST['password2'] );

    // בדיקה שאכן הזינו את שתי השדות
    if ( ! $pass || ! $secpass ) {
        die( json_encode( array(
            'status' => 'failed',
            'msg'    => 'לא הכנסת סיסמה חדשה או אימות סיסמה חדשה'
        ) ) );
    }
    // בדיקה שהשדות זהים
    if ( $pass != $secpass ) {
        die( json_encode( array(
            'status' => 'failed',
            'msg'    => 'הסיסמאות שהזנת אינן זהות'
        ) ) );
    }

    $user_info  = get_userdata( fixXSS($_POST['user_id'] ));
    $user_login = $user_info->user_login;
    $user = check_password_reset_key( fixXSS( $_POST['rp_key'] ), $user_login );

    if ( $user instanceof WP_User ) {
        wp_set_password( $pass, $user->ID );

        echo json_encode( array(
            'status'   => 'success',
            'redirect' => get_site_url()."/login",
        ) );
        die();
    } else {

        echo json_encode( array(
            'status' => 'failed',
            'msg'    => '"איפוס הסיסמה נכשל, אנא נסו שנית'
        ) );
        die();
    }
}

function forgot_password_form($login_page = true){
    ?>
    <div class="forgot-password-form d-none <?=(!$login_page?'flex-display center':'')?>">
        <form novalidate="" id="forgot_password_form" class="site_form row" data-success="show_success_msg" data-failed="show_error_messages">
            <?if($login_page){ ?>
                <p class="font-18">שכחת את הסיסמה? יש להזין כתובת מייל.
                    קישור לאיפוס הסיסמה יישלח אליך במייל</p>
            <?}
            else {
                ?>
                <div class="margin-bottom-10">שכחת את הסיסמה? יש להזין כתובת מייל.</div>
                <div class="margin-bottom-10">קישור לאיפוס הסיסמה יישלח אליך במייל</div>
                <?
            }
            ?>
            <input type="hidden" name="form_func" value="send_password_reset_link">
            <?php /*wp_nonce_field( 'ajax-lostpass-nonce', 'security' ); */?>
            <div class="flex-display  <?=(!$login_page?' direction-column space-between':'')?>">
                <input class="margin-after-10 font-18 <?=(!$login_page?' margin-bottom-20':'')?>" required="" type="email" name="email"  id="email" placeholder="הכנס כתובת אימייל">
                <button  id="reset_pass_button" type="submit" class="btn-login font-18 bold background-blue margin-after-10 margin-bottom-20">איפוס סיסמא</button>
            </div>
            <div id="form_error_msgs_container"></div>
        </form>
        <div id="success_msg_of_form " class="success_msg_of_form d-none font-18">נשלח אליך קישור לאיפוס סיסמה לכתובת המייל שהזנת</div>
    </div>
    <?php
}

?>