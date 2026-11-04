<?php
function login()
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
        if (!is_wp_error($user)) {
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID, true);

            do_action('wp_login', $user->user_login, $user);
        }

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

function register_new_user1($display_name, $email, $role, $username = "", $password = "")
{
    $email = fixXSS($email);
    $username = sanitize_user($username, true);

    if (empty($email)) {
        return array("status" => "failed", "msg" => "יש להזין מייל");
    }

    //יוזר קיים עם המייל הזה
    $existing_id = email_exists($email);
    if (!$existing_id) {
        $existing_id = username_exists($email);
    }
    $existing = $existing_id ? get_userdata($existing_id) : null;

    if (is_admin_email($email)) {
        return array("status" => "failed", "msg" => "המייל שייך למנהל המערכת, יש להזין מייל אחר");
    }

    if (empty($username)) {
        //בלי שם משתמש - משייכים ליוזר הקיים רק אם הוא באותו תפקיד ולא משויך לשום רשומה (סוכן/ספק)
        if ($existing && in_array($role, $existing->roles) && !is_user_linked_to_record($existing->ID)) {
            if (!empty($password)) {
                wp_set_password($password, $existing->ID);
            }
            return array("status" => "success", "user_id" => $existing->ID,
                "notice" => "שויך למשתמש קיים במערכת עם המייל הזה (שם משתמש: " . $existing->user_login . ")");
        }
        //יוזר קיים שאי אפשר לשייך - כדי ליצור יוזר נפרד חובה להזין שם משתמש (קיים רק בטופס סוכן)
        if ($existing) {
            $msg = "קיים כבר במערכת משתמש עם המייל הזה";
            $msg .= $role == "agents" ? ". כדי ליצור משתמש נפרד יש להזין שם משתמש" : ", יש להזין מייל אחר";
            return array("status" => "failed", "msg" => $msg);
        }
        //אין יוזר עם המייל - שם המשתמש הוא המייל
        $username = $email;
    }
    else {
        //עם שם משתמש - תמיד יוזר חדש
        $username_error = get_username_error($username);
        if ($username_error) {
            return array("status" => "failed", "msg" => $username_error);
        }
    }

    //וורדפרס לא מאפשר שני יוזרים עם אותו מייל - אם המייל תפוס היוזר נוצר בלי מייל, והמייל נשמר רק ברשומה (סוכן/ספק).
    //איפוס סיסמה מוצא את היוזר גם לפי המייל שברשומה (send_password_reset_link)
    $user_email = email_exists($email) ? "" : $email;

    $userdata = array(
        'user_login' => $username,
        'user_pass' => !empty($password) ? $password : wp_generate_password(16),
        'user_email' => $user_email,
        'last_name' => fixXSS($display_name),
        'display_name' => fixXSS($display_name),
        'role' => $role,
    );

    $user_id = wp_insert_user($userdata);

    if (is_wp_error($user_id))
        return array("status" => "failed", "msg" => $user_id->get_error_message());

    $notice = "נוצר משתמש חדש, שם משתמש: " . $username;
    if ($existing) {
        $notice = "קיים כבר במערכת משתמש אחר עם המייל הזה, ולכן " . $notice;
    }
    if (empty($password)) {
        $notice .= ". לא הוגדרה סיסמה - לכניסה ראשונה יש להשתמש ב'שכחתי סיסמא'";
    }
    return array("status" => "success", "user_id" => $user_id, "notice" => $notice, "created" => true);
}

//עמודה בלוח הבקרה, עמוד משתמשים - הרשומה (סוכן/ספק) שהיוזר משויך אליה, עם קישור לעריכה
add_filter('manage_users_columns', 'add_linked_record_user_column');
function add_linked_record_user_column($columns) {
    $columns['linked_record'] = 'רשומה משויכת';
    return $columns;
}

add_filter('manage_users_custom_column', 'show_linked_record_user_column', 10, 3);
function show_linked_record_user_column($output, $column_name, $user_id) {
    if ($column_name != 'linked_record') {
        return $output;
    }
    global $wpdb;
    //כל השיוכים נטענים פעם אחת לכל העמוד
    static $records = null;
    if ($records === null) {
        $records = array();
        foreach (USER_EMAIL_TABLES as $table) {
            $rows = $wpdb->get_results("SELECT id, name, user_id FROM {$wpdb->prefix}{$table} WHERE user_id > 0");
            foreach ($rows as $row) {
                $records[$row->user_id][] = array("table" => $table, "id" => $row->id, "name" => $row->name);
            }
        }
    }
    if (empty($records[$user_id])) {
        return '—';
    }
    $links = array();
    foreach ($records[$user_id] as $record) {
        $url = home_url('/single/?subject=' . $record["table"] . '&action=edit&id=' . $record["id"]);
        $links[] = '<a href="' . esc_url($url) . '">' . esc_html(BOUTIQUE_TABLES[$record["table"]]["single"]) . ' #' . intval($record["id"]) . '</a> ' . esc_html($record["name"]);
    }
    return implode('<br>', $links);
}

//מחיקת היוזרים של סוכנים/ספקים שנמחקו - חוץ ממנהל ומיוזר שמשויך לרשומה נוספת. מחזיר הודעה על מה שנעשה
function delete_users_of_removed_records($user_ids) {
    require_once ABSPATH . 'wp-admin/includes/user.php';
    $deleted = 0;
    $kept = array();
    foreach ($user_ids as $user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            continue;
        }
        if (in_array("administrator", $user->roles)) {
            $kept[] = "המשתמש " . $user->user_login . " לא נמחק כי הוא מנהל";
        }
        else if (is_user_linked_to_record($user_id)) {
            $kept[] = "המשתמש " . $user->user_login . " לא נמחק כי הוא משויך לרשומה נוספת";
        }
        else if (wp_delete_user($user_id)) {
            $deleted++;
        }
    }
    $notices = $kept;
    if ($deleted > 0) {
        array_unshift($notices, $deleted == 1 ? "המשתמש המקושר נמחק" : "נמחקו " . $deleted . " משתמשים מקושרים");
    }
    return implode(". ", $notices);
}

//בדיקת שם משתמש לסוכן - מחזיר הודעת שגיאה, או ריק אם תקין. $user_id - היוזר שמעדכנים (בעריכה)
//וורדפרס מאפשר להתחבר גם עם מייל, ולכן שם משתמש שהוא מייל של יוזר אחר מבלבל ומסוכן - חסום
function get_username_error($username, $user_id = 0) {
    if (is_admin_email($username)) {
        return "אין אפשרות להשתמש במייל של מנהל המערכת כשם משתמש";
    }
    $login_owner = username_exists($username);
    if ($login_owner && $login_owner != $user_id) {
        return "שם המשתמש כבר קיים במערכת";
    }
    $email_owner = email_exists($username);
    if ($email_owner && $email_owner != $user_id) {
        return "שם המשתמש הוא מייל של משתמש אחר במערכת, יש לבחור שם משתמש אחר";
    }
    return "";
}

//האם המייל שייך למנהל - מייל של יוזר מנהל (או שם משתמש של מנהל), או מייל הניהול של האתר
function is_admin_email($email) {
    if (strcasecmp(trim($email), get_option('admin_email')) == 0) {
        return true;
    }
    foreach (array(email_exists($email), username_exists($email)) as $user_id) {
        $user = $user_id ? get_userdata($user_id) : null;
        if ($user && in_array("administrator", $user->roles)) {
            return true;
        }
    }
    return false;
}

//קישור לא תקין של רשומה (סוכן/ספק) ליוזר - מחזיר תיאור הבעיה, או מחרוזת ריקה אם הקישור תקין.
//לא תקין: היוזר לא קיים, הוא מנהל, הוא בתפקיד אחר, או שהוא משויך גם לרשומה אחרת
function get_bad_user_link($user_id, $table_name, $record_id) {
    global $wpdb;
    $user = get_userdata($user_id);
    if (!$user) {
        return "היה מקושר למשתמש שלא קיים";
    }
    if (in_array("administrator", $user->roles)) {
        return "היה מקושר למשתמש של מנהל המערכת (שלא שונה)";
    }
    if (!in_array($table_name, $user->roles)) {
        return "היה מקושר למשתמש בתפקיד אחר (" . $user->user_login . ", שלא שונה)";
    }
    foreach (USER_EMAIL_TABLES as $table) {
        $other = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}{$table} WHERE user_id = %d AND NOT (%s = %s AND id = %d)",
            $user_id, $table, $table_name, $record_id));
        if ($other > 0) {
            return "היה מקושר למשתמש שמשויך גם לרשומה אחרת (" . $user->user_login . ", שלא שונה)";
        }
    }
    return "";
}

//האם היוזר משויך לרשומה כלשהי (סוכן/ספק)
function is_user_linked_to_record($user_id) {
    global $wpdb;
    foreach (USER_EMAIL_TABLES as $table) {
        if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}{$table} WHERE user_id = %d", $user_id)) > 0) {
            return true;
        }
    }
    return false;
}

add_action('wp_ajax_user_logout', 'user_logout');
function user_logout()
{
    wp_logout();
    die();
}

function get_user_role($user = null){
    $roles = get_user_connected($user)->roles;
    return is_array($roles) && count($roles)>0 ? $roles[0] : null;

}
function is_manager($user = null){
    return get_user_role($user) == "administrator";
}
function is_agent($user = null){
    return get_user_role($user) == "agents";
}
function is_supplier($user = null){
    return get_user_role($user) == "suppliers";
}
function get_id_by_user()
{
    $filters = array();
    $table_name = is_agent() ? "agents" : (is_supplier() ? "suppliers" : "");
    $filters[] = array("filter_field" => "user_id", "filter_value" => get_current_user_id());
    $users = get_data_table($table_name, $filters);
    write_log("user ".json_encode($users));
    if (count($users) > 0)
        return $users[0]->id;
    return null;
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

//טבלאות שהמייל של היוזר יכול להישמר בהן כשהוא לא נשמר ביוזר עצמו (מייל משותף לכמה יוזרים)
const USER_EMAIL_TABLES = array("agents", "suppliers");

//המייל של היוזר - מהיוזר עצמו, ואם אין - מהרשומה המקושרת (סוכן/ספק)
function get_user_email_for_reset($user) {
    global $wpdb;
    if (!empty($user->user_email)) {
        return $user->user_email;
    }
    foreach (USER_EMAIL_TABLES as $table) {
        $email = $wpdb->get_var($wpdb->prepare("SELECT email FROM {$wpdb->prefix}{$table} WHERE user_id = %d LIMIT 1", $user->ID));
        if (!empty($email)) {
            return $email;
        }
    }
    return "";
}

//כל היוזרים שהמייל שייך להם - היוזר שהמייל רשום אצלו, וגם יוזרים בלי מייל שהמייל רשום ברשומה המקושרת שלהם
function get_users_by_email_for_reset($email) {
    global $wpdb;
    $users = array();
    $user = get_user_by('email', $email);
    if ($user) {
        $users[$user->ID] = $user;
    }
    foreach (USER_EMAIL_TABLES as $table) {
        $user_ids = $wpdb->get_col($wpdb->prepare("SELECT user_id FROM {$wpdb->prefix}{$table} WHERE email = %s AND user_id > 0", $email));
        foreach ($user_ids as $user_id) {
            $user = get_userdata($user_id);
            if ($user && !isset($users[$user->ID])) {
                $users[$user->ID] = $user;
            }
        }
    }
    return array_values($users);
}

//היוזר לפי הפרמטר login בקישור האיפוס - שם משתמש, או מייל בקישורים ישנים
function get_user_by_reset_login($login) {
    $user = get_user_by('login', $login);
    if (!$user && is_email($login)) {
        $user = get_user_by('email', $login);
    }
    return $user;
}

//תוכן מייל איפוס הסיסמה - קישור נפרד לכל יוזר. בנוי בטבלאות וסגנון inline כדי שיוצג נכון בכל תוכנות המייל
function build_reset_password_email($users) {
    $gold = '#C9A24B';
    $is_single = count($users) == 1;

    $greeting = $is_single ? 'שלום ' . esc_html(get_user_display_name($users[0])) . ',' : 'שלום,';
    $intro = $is_single
        ? 'קיבלנו בקשה לאיפוס הסיסמה שלך בתוכנה של בוטיק כשר. כדי לבחור סיסמה חדשה, יש ללחוץ על הכפתור:'
        : 'קיבלנו בקשה לאיפוס סיסמה בתוכנה של בוטיק כשר. כתובת המייל הזו משויכת לכמה משתמשים, יש ללחוץ על הכפתור של המשתמש שאת הסיסמה שלו רוצים לאפס:';

    $users_html = '';
    foreach ($users as $user) {
        $key = get_password_reset_key($user);
        $url = get_reset_password_page() . "?action=reset_pass&key=$key&login=" . rawurlencode($user->user_login);

        $users_html .= '
            <tr><td style="padding: 0 0 16px 0;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#FAF7F0; border:1px solid #EFE6D2; border-radius:12px;">
                    <tr><td style="padding:20px 24px; text-align:right;">
                        <div style="font-size:17px; font-weight:700; color:#1A1A1A; margin-bottom:4px;">' . esc_html(get_user_display_name($user)) . '</div>
                        <div style="font-size:14px; color:#6B6B6B; margin-bottom:18px;">שם משתמש: <span style="direction:ltr; unicode-bidi:embed; color:#1A1A1A;">' . esc_html($user->user_login) . '</span></div>
                        <a href="' . esc_url($url) . '" style="display:inline-block; background-color:' . $gold . '; color:#FFFFFF; font-size:16px; font-weight:700; text-decoration:none; padding:12px 32px; border-radius:50px;">לבחירת סיסמה חדשה</a>
                    </td></tr>
                </table>
            </td></tr>';
    }

    return build_email_layout('איפוס סיסמה', $greeting, '
                    <div style="font-size:15px; line-height:1.7; color:#444444; margin-bottom:24px;">' . $intro . '</div>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">' . $users_html . '</table>
                    <div style="font-size:13px; line-height:1.7; color:#8A8A8A; margin:8px 0 28px 0;">
                        הקישור תקף ל-24 שעות.<br>
                        אם לא ביקשת לאפס את הסיסמה, אפשר להתעלם מהמייל הזה. הסיסמה הנוכחית לא תשתנה.
                    </div>');
}

//מייל ברוכים הבאים לסוכן שנוצר לו משתמש: שם המשתמש, קישור לכניסה וכפתור לבחירת סיסמה.
//הסיסמה עצמה לא נשלחת במייל - אם המנהל הגדיר סיסמה, הוא מוסר אותה לסוכן בעצמו
function send_new_agent_welcome_email($user_id, $email, $has_password) {
    $user = get_userdata($user_id);
    if (!$user || !is_email($email) || is_admin_email($email)) {
        return false;
    }
    $gold = '#C9A24B';
    $key = get_password_reset_key($user);
    if (is_wp_error($key)) {
        return false;
    }
    $reset_url = get_reset_password_page() . "?action=reset_pass&key=$key&login=" . rawurlencode($user->user_login);
    $login_url = get_site_url() . "/login";

    $password_text = $has_password
        ? 'את הסיסמה תקבל/י ממשרד בוטיק כשר. אפשר גם לבחור סיסמה אחרת בכפתור למטה.'
        : 'כדי להיכנס בפעם הראשונה, יש לבחור סיסמה בכפתור למטה.';

    $content = '
        <div style="font-size:15px; line-height:1.7; color:#444444; margin-bottom:24px;">נוצר עבורך משתמש בתוכנה של בוטיק כשר.<br>' . $password_text . '</div>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#FAF7F0; border:1px solid #EFE6D2; border-radius:12px; margin-bottom:16px;">
            <tr><td style="padding:20px 24px; text-align:right;">
                <div style="font-size:14px; color:#6B6B6B; margin-bottom:6px;">שם משתמש: <span style="direction:ltr; unicode-bidi:embed; color:#1A1A1A; font-weight:700;">' . esc_html($user->user_login) . '</span></div>
                <div style="font-size:14px; color:#6B6B6B; margin-bottom:18px;">כתובת הכניסה: <a href="' . esc_url($login_url) . '" style="color:#8A6A1F; direction:ltr; unicode-bidi:embed;">' . esc_html($login_url) . '</a></div>
                <a href="' . esc_url($reset_url) . '" style="display:inline-block; background-color:' . $gold . '; color:#FFFFFF; font-size:16px; font-weight:700; text-decoration:none; padding:12px 32px; border-radius:50px;">' . ($has_password ? 'לבחירת סיסמה אחרת' : 'לבחירת סיסמה') . '</a>
            </td></tr>
        </table>
        <div style="font-size:13px; line-height:1.7; color:#8A8A8A; margin:8px 0 28px 0;">
            הקישור לבחירת סיסמה תקף ל-24 שעות. אחר כך אפשר להשתמש ב"שכחתי סיסמה" בדף הכניסה.
        </div>';

    $body = build_email_layout('ברוכים הבאים', 'שלום ' . esc_html($user->display_name) . ',', $content);
    return send_mail($email, "המשתמש שלך בתוכנה של בוטיק כשר", $body);
}

//מסגרת משותפת למיילים: לוגו, כותרת, פנייה, תוכן וחתימה. בנוי בטבלאות וסגנון inline כדי שיוצג נכון בכל תוכנות המייל
//$greeting ו-$content הם HTML - טקסט של משתמש צריך לעבור esc_html לפני
function build_email_layout($title, $greeting, $content) {
    $gold = '#C9A24B';
    $logo = get_stylesheet_directory_uri() . '/assets/images/logo_header.png';

    return '
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F3F1EC; padding:32px 12px; direction:rtl;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background-color:#FFFFFF; border-radius:16px; overflow:hidden; box-shadow:0 4px 18px rgba(0,0,0,0.06);">
                <tr><td align="center" style="background-color:#000000; padding:22px 24px; border-bottom:4px solid ' . $gold . ';">
                    <img src="' . esc_url($logo) . '" alt="בוטיק כשר" height="48" style="display:block; height:48px; border:0;">
                </td></tr>
                <tr><td style="padding:36px 32px 12px 32px; text-align:right;">
                    <div style="font-size:24px; font-weight:700; color:#1A1A1A; margin-bottom:6px;">' . esc_html($title) . '</div>
                    <div style="width:48px; height:3px; background-color:' . $gold . '; border-radius:2px; margin-bottom:24px;"></div>
                    <div style="font-size:16px; font-weight:700; color:#1A1A1A; margin-bottom:8px;">' . $greeting . '</div>
                    ' . $content . '
                </td></tr>
                <tr><td style="background-color:#FAF7F0; padding:18px 32px; text-align:center; font-size:13px; color:#8A8A8A; border-top:1px solid #EFE6D2;">
                    בברכה, <span style="color:#1A1A1A; font-weight:700;">בוטיק כשר</span>
                </td></tr>
            </table>
        </td></tr>
    </table>';
}

function send_password_reset_link() {

    //check_ajax_referer( 'ajax-lostpass-nonce', 'security' );
    $login = sanitize_text_field( wp_unslash( $_POST['login'] ?? '' ) );

    //אפשר להזין מייל או שם משתמש
    if (is_email($login)) {
        $users = get_users_by_email_for_reset($login);
        $to = $login;
    } else {
        $user = get_user_by('login', $login);
        $users = $user ? array($user) : array();
        $to = $user ? get_user_email_for_reset($user) : "";
    }

    if (count($users) == 0) {
        echo json_encode( array(
            'status' => 'failed',
            'msg'    => 'אתה לא רשום במערכת'
        ) );
        die;
    }
    if (empty($to)) {
        echo json_encode( array(
            'status' => 'failed',
            'msg'    => 'לא רשום מייל למשתמש הזה, יש לפנות למנהל המערכת'
        ) );
        die;
    }

    $subject = "איפוס סיסמה בתוכנה של בוטיק כשר";
    $message = build_reset_password_email($users);

    $ok = send_mail($to,$subject,$message);
    echo json_encode( array(
        'status' => 'success',
        'ok' => $ok
    ) );
    die;
}

function send_mail($to,$subject,$message,$files = array())
{
    //$to = "kosherboutique@gmail.com";
    //$to = "fr305070@gmail.com";
    //$to = "fr305070@gmail.com";

    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= "From: ".get_option("blogname")." <info@kosherboutique.co.il> \r\n";
    $headers .= "Reply-To: ".get_option("blogname")." <".get_option('admin_email')."> \r\n";

    $body = '<div style="direction: rtl;font-family:system-ui,-apple-system,BlinkMacSystemFont,SF Pro,Segoe UI,Helvetica Neue,Helvetica,Arial,sans-serif; color:black"">
                        '.$message.'
                    </div>';


    $ok = wp_mail($to, $subject, $body, $headers,$files);
    return $ok;
}

function _reset_password() {
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
        <form novalidate="" id="forgot_password_form" class="site_form row" data-success="forgot_password_success" data-failed="forgot_password_failed">
            <div class="forgot-password-fields">
            <?php if($login_page){ ?>
                <p class="font-18">שכחת את הסיסמה? יש להזין כתובת מייל שאיתה נכנסים למערכת או שם משתמש.
                    קישור לאיפוס הסיסמה יישלח אליך במייל</p>
            <?php }
            else {
                ?>
                <div class="margin-bottom-10">שכחת את הסיסמה? יש להזין כתובת מייל או שם משתמש.</div>
                <div class="margin-bottom-10">קישור לאיפוס הסיסמה יישלח אליך במייל</div>
                <?php
            }
            ?>
            <input type="hidden" name="form_func" value="send_password_reset_link">
            <?php /*wp_nonce_field( 'ajax-lostpass-nonce', 'security' ); */?>
            <div class="flex-display  <?=(!$login_page?' direction-column space-between':'')?>">
                <input class="border-dark-gray margin-after-10 font-18 <?=(!$login_page?' margin-bottom-20':'')?>" required="" type="text" name="login"  id="reset_login" placeholder="מייל או שם משתמש" autocomplete="username">
                <button  id="reset_pass_button" type="submit" class="align-self-center btn-login font-18 bold background-gold margin-after-10 margin-bottom-20">איפוס סיסמא</button>
            </div>
            </div>
            <div id="form_error_msgs_container"></div>
            <div class="forgot-password-retry underline pointer font-18 d-none">ניסיון נוסף</div>
        </form>
        <div id="success_msg_of_form " class="success_msg_of_form d-none font-18">נשלח אליך קישור לאיפוס סיסמה לכתובת המייל שהזנת</div>
    </div>
    <?php
}

add_action('wp_ajax_update_user_meta_value', 'update_user_meta_value');
function update_user_meta_value()
{
    update_user_meta( get_current_user_id(),$_POST['meta_key'],$_POST['meta_value']);
    echo json_encode( array(        'status'   => 'success'    ) );
    die();
}

?>