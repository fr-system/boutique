<?php
function function_login()
{
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

        echo json_encode(array(
            'status' => 'success',
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
                'msg' => 'אינך מזוהה במערכת',
            ));
        }
    }
    die();

}

function function_register()
{
    $email = fixXSS($_POST['username']);
    $pass = fixXSS($_POST['password']);
    if (strlen($email) < 5 || strlen($pass) < 6) {
        echo json_encode(array(
            'status' => 'error',
            'msg' => "הכנס את המייל שלך וסיסמא"
        ));
        die;
    }

    if (email_exists($email) || username_exists($email)) {
        echo json_encode(array(
            'status' => 'error',
            'msg' => "המייל רשום כבר במערכת"
        ));
        die;
    }

    $userdata = array(
        'user_login' => $email,
        'user_pass' => $pass,
        'user_email' => $email,
    );

    $user_id = wp_insert_user($userdata);
    if (!is_wp_error($user_id)) {
        echo json_encode(array(
            'status' => 'success',
        ));
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
    $user = get_user_connected($user_obj);
    return $user ?  $user->data->display_name : null;
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

        $subject = "איפוס סיסמה בתוכנה של בוטיק כשר";

        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        //$headers .= "From: ".get_option("blogname")." <wordpress@kosherboutique.co.il/> \r\n";
        $headers .= "Reply-To: ".get_option("blogname")." <".get_option('admin_email')."> \r\n";
        $to = $user_email;

        $message = '<div style="direction: rtl;font-family:Arial,Georgia,Verdana; color:black"">
                        <span style="font-size:25px; font-weight: bold;">'.get_user_display_name($user).',</span><br><br>
                            <span style="margin-bottom: 10px">התקבלה בקשה לאיפוס הסיסמא</span>
                            <a style="background-color:#E2B252; color:white; display: block; padding: 0 20px;width: fit-content; text-decoration: none; font-weight: 700; 
                            border-radius: 64px; border: none" 
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

function display_forgot_password_form($login_page = true){
    ?>
    <div class="forgot-password-form d-none <?php echo(!$login_page?'flex-display center':'')?>">
        <form novalidate="" id="forgot_password_form" class="site_form row" data-success="show_success_msg" data-failed="show_error_messages">
            <?php if($login_page){ ?>
                <p class="font-18">שכחת את הסיסמה? יש להזין כתובת מייל.
                    קישור לאיפוס הסיסמה יישלח אליך במייל</p>
            <?php }
            else {
                ?>
                <div class="margin-bottom-10">שכחת את הסיסמה? יש להזין כתובת מייל.</div>
                <div class="margin-bottom-10">קישור לאיפוס הסיסמה יישלח אליך במייל</div>
                <?php
            }
            ?>
            <input type="hidden" name="form_func" value="send_password_reset_link">
            <?php /*wp_nonce_field( 'ajax-lostpass-nonce', 'security' ); */?>
            <div class="flex-display  <?=(!$login_page?' direction-column space-between':'')?>">
                <input class="margin-after-10 font-18 <?=(!$login_page?' margin-bottom-20':'')?>" required="" type="email" name="email"  id="email" placeholder="הכנס כתובת אימייל">
                <button  id="reset_pass_button" type="submit" class="btn-login font-18 bold background-gold margin-after-10 margin-bottom-20">איפוס סיסמא</button>
            </div>
            <div id="form_error_msgs_container"></div>
        </form>
        <div id="success_msg_of_form " class="success_msg_of_form d-none font-18">נשלח אליך קישור לאיפוס סיסמה לכתובת המייל שהזנת</div>
    </div>
    <?php
}
// Add the field to user's own profile editing screen.
add_action('show_user_profile','wporg_usermeta_form_field_lang',90);
// Add the field to user profile editing screen.
add_action('edit_user_profile','wporg_usermeta_form_field_lang',90);
// Add the save action to user's own profile editing screen update.
add_action('personal_options_update', 'wporg_usermeta_form_field_lang_update');
// Add the save action to user profile editing screen update.
add_action('edit_user_profile_update', 'wporg_usermeta_form_field_lang_update');
function wporg_usermeta_form_field_lang( $user ) {
    ?>
    <h3>מצב עבודה</h3>
    <table class="form-table">
        <tr>
            <th>
                <label for="test_mode">מצב בדיקות</label>
            </th>
            <td>
                <input type="number" id="test_mode" name="test_mode" max="1" value="<?= get_user_test_mode($user->data->ID);?>"/>
                <p class="description">לבדיקות ללא שימוש בנתוני אמת.</p>
            </td>
        </tr>
    </table>
    <?php
}
function get_user_test_mode($user_id=null)
{
    if(empty($user_id)){
        $user_id = get_user_connected()->ID;
    }
    return get_user_meta($user_id,'test_mode',true) ?? 0;
}
/**
 * The save action.
 *
 * @param $user_id int the ID of the current user.
 *
 * @return bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function wporg_usermeta_form_field_lang_update( $user_id ) {
    // check that the current user have the capability to edit the $user_id
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
        return false;
    }
    return update_user_meta($user_id,'test_mode',$_POST['test_mode']);
}
?>