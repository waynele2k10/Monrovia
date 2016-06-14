<?php
/*
  Plugin Name: Monrovia Popup
  Plugin URI:  www.monrovia.com
  Description: Create location-based popup
  Version:     1.0
  Author:      Waynele
  Author URI:  http://www.monrovia.com
 */

add_action('admin_menu', 'monrovia_popup_admin_menu');

function monrovia_popup_admin_menu() {

    add_options_page(
            'Popup Setting', 'Monrovia Popup', 'manage_options', 'monrovia-popup-plugin', 'monrovia_popup_options_page'
    );

    //call register settings function
    add_action('admin_init', 'register_monrovia_popup_plugin_settings');
}

function register_monrovia_popup_plugin_settings() {
    //register our settings
    register_setting('monrovia-popup-settings-group', 'monrovia_popup_content');
    register_setting('monrovia-popup-settings-group', 'monrovia_popup_state');
}

function monrovia_popup_options_page() {
    ?>
    <div class="wrap">
        <h2>Popup setting</h2>
        <form method="post" action="options.php">
            <?php settings_fields('monrovia-popup-settings-group'); ?>
            <?php do_settings_sections('monrovia-popup-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Content Popup</th>
                    <td>
                        <?php
                        wp_editor(get_option('monrovia_popup_content', ''), 'monrovia_popup_content');
                        ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">State allow</th>
                    <td><input type="text" name="monrovia_popup_state" value="<?php echo esc_attr(get_option('monrovia_popup_state')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function monrovia_popup_footer() {
    if (is_page_template('plant.php') || is_home()) {
        wp_register_style('monrovia_popup', plugins_url('css/monrovia_popup.css', __FILE__));
        wp_enqueue_style('monrovia_popup');
        wp_enqueue_script('jquery.simplePopup', plugin_dir_url(__FILE__) . 'js/jquery.simplePopup.js', array('jquery'));
        ?>
        <!-- monrovia_popup1  -->
        <div id="monrovia-popup" style="display:none">
            <?php echo get_option('monrovia_popup_content', ''); ?>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                if ($('#monrovia-popup').length > 0) {
                    var thumbtack = getCookie('thumbtack');
                    if (thumbtack == "undefined" || thumbtack != 1) {
                        var zip_code = getCookie('zip_code');
                        if (zip_code && zip_code != 'undefined') {
                        } else {
                            var onSuccess = function (location) {

                                var string = JSON.stringify(location, undefined, 4);
                                var data = JSON.parse(string);
                                var postalCode = data.postal.code;
                                getZone(postalCode);

                            }

                            var onError = function (error) {
                                // The error
                                //console.log(JSON.stringify(error, undefined, 4));
                            }

                            geoip2.city(onSuccess, onError);
                        }
                        zip_code = getCookie('zip_code');
                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {action: 'is_state_allow', zipcode: zip_code},
                            dataType: 'json',
                            success: function (data) {
                                if (data.result == true) {
                                    var delay = 5000;
                                    setTimeout(function () {
                                        $('#monrovia-popup').simplePopup({
                                            centerPopup: true
                                        });
                                    }, delay);
                                    setCookie('thumbtack', '1', 365);
                                }
                            }
                        });
                    }
                }
            });
        </script>
        <?php
    }
}

add_action('wp_footer', 'monrovia_popup_footer');

function is_state_allow() {

    //Set up an array to store the return values
    $values = Array();

    // Check to see if zipcode is set
    if (isset($_POST['zipcode']))
        $zipcode = $_POST['zipcode'];

    //Check to see if the User is logged in
    if (is_user_logged_in()) {
        // Use the Users saved zip code
        $userID = get_current_user_id();
        $values['zipcode'] = get_cimyFieldValue($userID, 'ZIP_CODE');
    } elseif (isset($zipcode)) {
        $values['zipcode'] = $zipcode;
    }
    $zip = $values['zipcode'];
    $sql = "SELECT * FROM monrovia_zipcodes WHERE zip_code ='$zip'";
    if (mysql_num_rows(mysql_query($sql)) > 0) {
        $result = mysql_fetch_array(mysql_query($sql));
        $state = $result['state'];
        $state_allow = get_option('monrovia_popup_state', '');
        $state_allow_arr = explode(',', $state_allow);
        if (in_array($state, $state_allow_arr)) {
            $values['result'] = true;
        } else {
            $values['result'] = false;
        }
    } else {
        $values['result'] = false;
    }

    echo json_encode($values);
    exit();
}

add_action('wp_ajax_is_state_allow', 'is_state_allow');
add_action('wp_ajax_nopriv_is_state_allow', 'is_state_allow');
