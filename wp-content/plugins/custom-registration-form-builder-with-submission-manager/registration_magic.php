<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.registrationmagic.com
 * @since             3.0.0
 * @package           registration_magic
 *
 * @wordpress-plugin
 * Plugin Name:       RegistrationMagic
 * Plugin URI:        http://www.registrationmagic.com
 * Description:       RegistrationMagic - Easy to use powerful WordPress Registration, SignUp and Contact Forms.
 * Version:           3.1.4
 * Tags:              registration, form, custom, analytics, simple, submissions
 * Requires at least: 3.3.0
 * Author:            CMSHelplive
 * Author URI:        http://cmshelplive.com
 * Text Domain:       custom-registration-form-builder-with-submission-manager
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if (!defined('WPINC'))
{
    die;
}

define('RM_PLUGIN_VERSION', '3.1.4');
define('RM_DB_VERSION', 4.0);

//define FB SDK req flags. Flags should be combined using logical OR and should be checked using AND.
define('RM_FB_SDK_REQ_PHP_NA', 0x2);  //Php version is not sufficient
define('RM_FB_SDK_REQ_EXT_NA', 0x4);  //mbstring extension not installed or disabled
define('RM_FB_SDK_REQ_OK', 0x1);      //Requirements met. DO NOT TEST FOR THIS FLAG USING &. use === instead.

define('RM_BASE_DIR', plugin_dir_path(__FILE__));
define('RM_BASE_URL', plugin_dir_url(__FILE__));
define('RM_ADMIN_DIR', RM_BASE_DIR."admin/");
define('RM_PUBLIC_DIR', RM_BASE_DIR."public/");
define('RM_IMG_DIR', RM_BASE_DIR."images/");
define('RM_IMG_URL', plugin_dir_url(__FILE__) . 'images/');
define('RM_INCLUDES_DIR', RM_BASE_DIR.'includes/');
define('RM_EXTERNAL_DIR', RM_BASE_DIR.'external/');

//form types
define('RM_BASE_FORM', 99);
define('RM_CONTACT_FORM', 0);
define('RM_REG_FORM', 1);

$rm_fb_sdk_req = RM_FB_SDK_REQ_OK;  //Set default value.

/**
 * registers the plugin autoload
 */
function registration_magic_register_autoload()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class_rm_autoloader.php';

    $autoloader = new RM_Autoloader();
    $autoloader->register();
}

/**
 * includes or initializes all the external libraries used in the plugin
 * 
 * @since 3.0.0
 */
function registration_magic_include_external_libs()
{
    require_once plugin_dir_path(__FILE__) . 'external/PFBC/Form.php';
    require_once plugin_dir_path(__FILE__) . 'external/mailchimp/class_rm_mailchimp.php';
    //require_once plugin_dir_path(__FILE__) . 'external/Facebook/autoload.php';
    require_once plugin_dir_path(__FILE__) . 'external/cron/cron_helper.php';
    require_once ABSPATH . 'wp-includes/pluggable.php';
    require_once ABSPATH . 'wp-admin/includes/user.php';

    //check for FB SDK v5 requirements and setup the global var accordingly.
    global $rm_fb_sdk_req;

    $installed_php_version = phpversion();
    $mbstring_ext_available = extension_loaded('mbstring');

    if(version_compare('5.4', $installed_php_version,'>'))
    $rm_fb_sdk_req |= RM_FB_SDK_REQ_PHP_NA;

    if ($mbstring_ext_available === false)
        $rm_fb_sdk_req |= RM_FB_SDK_REQ_EXT_NA;

    if ($rm_fb_sdk_req !== RM_FB_SDK_REQ_OK)
        require_once plugin_dir_path(__FILE__) . 'external/Facebook_legacy/src/facebook.php';
    else
        require_once plugin_dir_path(__FILE__) . 'external/Facebook/autoload.php';
}

registration_magic_register_autoload();
registration_magic_include_external_libs();

register_activation_hook(__FILE__, 'RM_Activator::activate');
register_deactivation_hook(__FILE__, 'RM_Deactivator::deactivate');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    3.0.0
 */
function run_registration_magic()
{
    $plugin = new Registration_Magic();
    $plugin->run();
}

run_registration_magic();
//add_action( 'init', 'run_registration_magic' );


