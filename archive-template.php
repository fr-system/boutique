<?php /* Template Name: archive */
if(!is_user_logged_in()){
    wp_redirect(get_site_url()."/login");
}
?>
<?php get_header();?>
<?php
if(!isset($_GET["subject"]) || !is_manager() && $_GET["subject"] == "clients") return;
$table_name = $_GET["subject"];
$query = get_page_query($table_name);
   // print_r ("aa ".$query."<br>");
$result = run_query ($query);
$fields_arr  = BOUTIQUE_TABLES[$table_name];
  //  write_log("cols  ".json_encode( $fields_arr["columns"]));
?>
<section class="page">
    <?php
    if($table_name == "lists"){   ?>
    <h1 class="page-title  font-30 bold">רשימות</h1>
        <select><?php
            foreach (BOUTIQUE_LISTS as $B_LIST){
               echo "<option>".$B_LIST->title."</option>";
            }
            ?>

        </select>
     <?php
    }
    else{
    ?>

    <div class="archive-actions flex-display space-between">
        <div class="flex-display space-between">
            <?php if($table_name=="products"){ ?>
                <svg class="margin-after-10 pointer" data-view="gallery" xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 44 44" fill="none">
                    <circle cx="22" cy="22" r="22" fill="#D9F5F3"/>
                    <rect x="13" y="13" width="7.55827" height="7.23077" rx="1" stroke="#1A7870" stroke-width="2"/>
                    <rect x="24.1511" y="13" width="7.55827" height="7.23077" rx="1" stroke="#1A7870" stroke-width="2"/>
                    <rect x="13" y="23.7692" width="7.55827" height="7.23077" rx="1" stroke="#1A7870" stroke-width="2"/>
                    <rect x="24.1511" y="23.7692" width="7.55827" height="7.23077" rx="1" stroke="#1A7870" stroke-width="2"/>
                </svg>
                <svg class="margin-after-10 pointer" data-view=table" xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 44 44" fill="none">
                    <circle cx="22" cy="22" r="22" fill="#D9F5F3"/>
                    <path d="M12.5 13.2412C12.5 12.1534 13.382 11.2725 14.4688 11.2725H28.5312C29.618 11.2725 30.5 12.1534 30.5 13.2412V16.0537C30.5 16.5759 30.2926 17.0767 29.9234 17.4459C29.5542 17.8151 29.0534 18.0225 28.5312 18.0225H14.4688C13.9466 18.0225 13.4458 17.8151 13.0766 17.4459C12.7074 17.0767 12.5 16.5759 12.5 16.0537V13.2412ZM14.4688 12.96C14.3942 12.96 14.3226 12.9896 14.2699 13.0424C14.2171 13.0951 14.1875 13.1667 14.1875 13.2412V16.0537C14.1875 16.209 14.3135 16.335 14.4688 16.335H28.5312C28.6058 16.335 28.6774 16.3054 28.7301 16.2526C28.7829 16.1999 28.8125 16.1283 28.8125 16.0537V13.2412C28.8125 13.1667 28.7829 13.0951 28.7301 13.0424C28.6774 12.9896 28.6058 12.96 28.5312 12.96H14.4688ZM12.5 21.1162C12.5 20.0284 13.382 19.1475 14.4688 19.1475H28.5312C29.618 19.1475 30.5 20.0284 30.5 21.1162V23.9287C30.5 24.4509 30.2926 24.9517 29.9234 25.3209C29.5542 25.6901 29.0534 25.8975 28.5312 25.8975H14.4688C13.9466 25.8975 13.4458 25.6901 13.0766 25.3209C12.7074 24.9517 12.5 24.4509 12.5 23.9287V21.1162ZM14.4688 20.835C14.3942 20.835 14.3226 20.8646 14.2699 20.9174C14.2171 20.9701 14.1875 21.0417 14.1875 21.1162V23.9287C14.1875 24.084 14.3135 24.21 14.4688 24.21H28.5312C28.6058 24.21 28.6774 24.1804 28.7301 24.1276C28.7829 24.0749 28.8125 24.0033 28.8125 23.9287V21.1162C28.8125 21.0417 28.7829 20.9701 28.7301 20.9174C28.6774 20.8646 28.6058 20.835 28.5312 20.835H14.4688ZM14.4688 27.0225C13.9466 27.0225 13.4458 27.2299 13.0766 27.5991C12.7074 27.9683 12.5 28.4691 12.5 28.9912V31.8037C12.5 32.8905 13.382 33.7725 14.4688 33.7725H28.5312C29.0534 33.7725 29.5542 33.5651 29.9234 33.1959C30.2926 32.8267 30.5 32.3259 30.5 31.8037V28.9912C30.5 28.4691 30.2926 27.9683 29.9234 27.5991C29.5542 27.2299 29.0534 27.0225 28.5312 27.0225H14.4688ZM14.1875 28.9912C14.1875 28.9167 14.2171 28.8451 14.2699 28.7924C14.3226 28.7396 14.3942 28.71 14.4688 28.71H28.5312C28.6058 28.71 28.6774 28.7396 28.7301 28.7924C28.7829 28.8451 28.8125 28.9167 28.8125 28.9912V31.8037C28.8125 31.8783 28.7829 31.9499 28.7301 32.0026C28.6774 32.0554 28.6058 32.085 28.5312 32.085H14.4688C14.3942 32.085 14.3226 32.0554 14.2699 32.0026C14.2171 31.9499 14.1875 31.8783 14.1875 31.8037V28.9912Z" fill="#1A7870"/>
                </svg>
            <?php }//get_svg ("clients","new",false,"class-name"); ?>
            <h1 class="page-title font-30 bold"><?php echo $fields_arr["title"] ?></h1>
        </div>
        <div class="flex-display space-between">
            <div class="border-dark-gray archive-search flex-display align-center align-self-center margin-after-10">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <g clip-path="url(#clip0_39_370)">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M5.6875 10.5C6.31949 10.5 6.94528 10.3755 7.52916 10.1337C8.11304 9.89182 8.64357 9.53733 9.09045 9.09045C9.53733 8.64357 9.89182 8.11304 10.1337 7.52916C10.3755 6.94528 10.5 6.31949 10.5 5.6875C10.5 5.05551 10.3755 4.42972 10.1337 3.84584C9.89182 3.26196 9.53733 2.73143 9.09045 2.28455C8.64357 1.83767 8.11304 1.48318 7.52916 1.24133C6.94528 0.999479 6.31949 0.875 5.6875 0.875C4.41115 0.875 3.18707 1.38203 2.28455 2.28455C1.38203 3.18707 0.875 4.41115 0.875 5.6875C0.875 6.96385 1.38203 8.18793 2.28455 9.09045C3.18707 9.99297 4.41115 10.5 5.6875 10.5ZM11.375 5.6875C11.375 7.19592 10.7758 8.64256 9.70917 9.70917C8.64256 10.7758 7.19592 11.375 5.6875 11.375C4.17908 11.375 2.73244 10.7758 1.66583 9.70917C0.599217 8.64256 0 7.19592 0 5.6875C0 4.17908 0.599217 2.73244 1.66583 1.66583C2.73244 0.599217 4.17908 0 5.6875 0C7.19592 0 8.64256 0.599217 9.70917 1.66583C10.7758 2.73244 11.375 4.17908 11.375 5.6875Z" fill="black"/>
                        <path d="M9.33337 10.5575C9.35962 10.5925 9.38762 10.6257 9.41912 10.6581L12.7879 14.0268C12.9519 14.191 13.1745 14.2833 13.4066 14.2834C13.6387 14.2835 13.8614 14.1913 14.0256 14.0273C14.1897 13.8632 14.282 13.6406 14.2821 13.4085C14.2822 13.1764 14.1901 12.9538 14.026 12.7896L10.6572 9.42083C10.626 9.38916 10.5923 9.35991 10.5566 9.33333C10.2134 9.80135 9.8009 10.2144 9.33337 10.5583V10.5575Z" fill="black"/>
                    </g>
                    <defs>
                        <clipPath id="clip0_39_370">
                            <rect width="14" height="14" fill="white"/>
                        </clipPath>
                    </defs>
                </svg>
                <input type="search" id="search" class="" placeholder="חיפוש" />
            </div>
            <a href="<?php echo 'single?subject='.$table_name.'&action=new' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60" fill="none">
                    <circle cx="30" cy="30" r="29.5" fill="#1A7870" stroke="white"/>
                    <line x1="30" y1="20" x2="30" y2="42" stroke="white" stroke-width="2"/>
                    <line x1="41" y1="31" x2="19" y2="31" stroke="white" stroke-width="2"/>
                </svg>
            </a>
        </div>
    </div>
    <?php
    }
    $user_meta = get_user_meta( get_current_user_id(), "products_view", true);
    //write_log("meta ".$user_meta);
    //$user_meta = true;
    if($table_name == "products" && $user_meta == "gallery"){
        view_catalog_gallery($result);
    }
    else{
    ?>
    <table name="" class="archive-table">
        <thead><tr class="gold">
            <?php
            foreach($fields_arr["columns"] as $column){
                if(isset($column["hidden"]) ||!isset($column["label"]) ){continue;}
                ?>
                <th><?= $column["label"]?></th>
            <?php } ?>
            <th></th>
        </tr></thead>
        <?php foreach($result as $row){
            echo get_tr_data($table_name,$row ,"id");
        }?>
    </table>
    <?php } ?>
</section>
<?php
function get_tr_data($page_name, $data, $id_column){
    //error_log ("add_tr_data");
    global $actions_icons;
    $page_info = BOUTIQUE_TABLES[$page_name];
    $row = is_array ($data)? $data[0]:$data;
    //error_log ('row '.json_encode ($row));
    $html='<tr class="border-dark-gray" data-id="'.$row->id.'">';
//        <td data-id="checkbox" class="td-checkbox"><input type="checkbox" class="checkbox-row" value="'.$row->$id_column.'" id=""/></td>';
    foreach($page_info["columns"] as $column) {
        $field = isset($column['join_table']) ? substr($column['join_table'], 0, -1)  . "_" . $column['join_value']: $column["field_name"];
        $list = isset($column['table_name'])? constant($column['table_name']):null;

        if($field != $id_column && !isset($column["hidden"]) && isset($column["label"])){
            if(isset($column['type']) && $column['type']=="user_data") {
                //write_log ('fiel ' . $field);
                //write_log ('row ' . json_encode ($row));
                $user_field = $column["field_name"];
                $column_value = empty($row->user_id) ? '' : get_userdata ($row->user_id)->$user_field;
            }
            //else if($column['type']=="action"){
                //$column_value = '<button  class="action bg-lightblue" name="'.$column['field_name'].'" onclick="action_func(this)"><i class="'.$actions_icons[$column['field_name']].'"></i><span>פעולה</span></button>';
            //}
        else{
                $column_value = isset($column['list_name']) && isset($list[$row->$field])?$list[$row->$field]: $row->$field;
            }
            $html .='<td>'. $column_value.'</td>';
        }
    }

    $html .='<td class="flex-display space-around">';
    $html .='<a href="single?subject='.$_GET["subject"].'&action=edit&id='.$row->id.'">
<svg class="edit-row" xmlns="http://www.w3.org/2000/svg" width="24" height="23" viewBox="0 0 24 23" fill="none">
    <path d="M7 16.3041L11.413 16.2898L21.045 7.14726C21.423 6.78501 21.631 6.30393 21.631 5.79218C21.631 5.28043 21.423 4.79934 21.045 4.43709L19.459 2.91717C18.703 2.19267 17.384 2.19651 16.634 2.9143L7 12.0587V16.3041ZM18.045 4.27226L19.634 5.7893L18.037 7.30538L16.451 5.78643L18.045 4.27226ZM9 12.858L15.03 7.13384L16.616 8.65376L10.587 14.376L9 14.3808V12.858Z" fill="#E2B252"/>
    <path d="M5 20.125H19C20.103 20.125 21 19.2654 21 18.2083V9.9015L19 11.8182V18.2083H8.158C8.132 18.2083 8.105 18.2179 8.079 18.2179C8.046 18.2179 8.013 18.2093 7.979 18.2083H5V4.79167H11.847L13.847 2.875H5C3.897 2.875 3 3.73462 3 4.79167V18.2083C3 19.2654 3.897 20.125 5 20.125Z" fill="#E2B252"/>
</svg>
</a>';
    $html .='<svg class="remove-row pointer"  xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
<path d="M4.16663 7H20.8333M10.4166 11V17M14.5833 11V17M5.20829 7L6.24996 19C6.24996 19.5304 6.46945 20.0391 6.86015 20.4142C7.25085 20.7893 7.78076 21 8.33329 21H16.6666C17.2192 21 17.7491 20.7893 18.1398 20.4142C18.5305 20.0391 18.75 19.5304 18.75 19L19.7916 7M9.37496 7V4C9.37496 3.73478 9.48471 3.48043 9.68006 3.29289C9.87541 3.10536 10.1404 3 10.4166 3H14.5833C14.8596 3 15.1245 3.10536 15.3199 3.29289C15.5152 3.48043 15.625 3.73478 15.625 4V7" stroke="#E2B252" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>';
    $html .='</td>';
    //$html .='<td></td>';
    if(isset($page_info["actions"]) && is_array($page_info["actions"])) {
        $html .='<td class="flex-display space-around">';
        foreach($page_info["actions"] as $action) {
            $html .='<button  class="action bg-lightblue" name="'.$action.'" onclick="action_func(this)">               
                 <i class="'. $actions_icons[$action].'"></i><span>פעולה</span></button>';
        }
        $html .='</td>';
    }
    $html .='</tr>';
    //error_log ("add_tr_data enf ".$html);
    return $html;
}
?>
