<?php

/**
 * Db craeter class
 *
 * Initializes or creates the whole database of the plugin
 *
 * @link       http://registration_magic.com
 * @since      1.0.0
 *
 * @package    Registraion_Magic
 * @subpackage Registraion_Magic/includes
 */
class RM_Table_Tech
{

    private static $instance;
    private static $table_name_for;

    private function __construct()
    {
        global $wpdb;

        $prefix = $wpdb->prefix . 'rm_';
        self::$table_name_for = array();
        self::$table_name_for['FORMS'] = $prefix . 'forms';
        self::$table_name_for['FIELDS'] = $prefix . 'fields';
        self::$table_name_for['SUBMISSIONS'] = $prefix . 'submissions';
        self::$table_name_for['SUBMISSION_FIELDS'] = $prefix . 'submission_fields';
        self::$table_name_for['FORM_RESPONSES'] = $prefix . 'form_responses';
        self::$table_name_for['PAYPAL_FIELDS'] = $prefix . 'paypal_fields';
        self::$table_name_for['PAYPAL_LOGS'] = $prefix . 'paypal_logs';
        self::$table_name_for['FRONT_USERS'] = $prefix . 'front_users';
        self::$table_name_for['STATS'] = $prefix . 'stats';
        self::$table_name_for['NOTES'] = $prefix . 'notes';
    }

    private function __wakeup()
    {

    }

    private function __clone()
    {

    }

    public static function get_instance()
    {
        if (null === static::$instance)
        {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public static function create_tables()
    {

        require_once( ABSPATH . 'wp-includes/wp-db.php');
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        global $wpdb;

        $table_name = self::$table_name_for['FORMS'];

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
        {
            //Ensures proper charset support. Also limits support for WP v3.5+.
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                    `form_id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `form_name` VARCHAR(1000 ),
                    `form_type` INT(6),
                    `form_user_role` VARCHAR(1000),
                    `default_user_role` VARCHAR(255),
                    `form_should_send_email` TINYINT(1),
                    `form_redirect` VARCHAR(10),
                    `form_redirect_to_page` VARCHAR(500),
                    `form_redirect_to_url` VARCHAR(500),
                    `mailchimp_list` VARCHAR(255),
                    `form_should_auto_expire` TINYINT(1),
                    `form_options` TEXT,
                    `created_on` DATETIME,
                    `created_by` INT(6),
                    `modified_on` DATETIME,
                    `modified_by` INT(6)
                    )$charset_collate;";

            dbDelta($sql);
        }

        $table_name = self::$table_name_for['FIELDS'];

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
        {
            //Ensures proper charset support. Also limits support for WP v3.5+.
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                    `field_id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `form_id` INT(6),
                    `field_label` TEXT,
                    `field_type` TEXT,
                    `field_value` MEDIUMTEXT,
                    `field_order` INT(6),
                    `field_show_on_user_page` TINYINT(1),
                    `is_field_primary` TINYINT(1),
                    `field_options` MEDIUMTEXT
                    )$charset_collate;";

            dbDelta($sql);
        }

        $table_name = self::$table_name_for['SUBMISSIONS'];

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
        {
            //Ensures proper charset support. Also limits support for WP v3.5+.
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                    `submission_id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `form_id` INT(6),
                    `data` TEXT,
                    `user_email` VARCHAR(250),
                    `submitted_on` DATETIME,
                    `unique_token` VARCHAR(250)
                    )$charset_collate;";

            dbDelta($sql);
        }

        $table_name = self::$table_name_for['SUBMISSION_FIELDS'];

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
        {
            //Ensures proper charset support. Also limits support for WP v3.5+.
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                    `sub_field_id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `submission_id` INT(6),
                    `field_id` INT(6),
                    `form_id` INT(6),
                    `value` TEXT
                    )$charset_collate;";

            dbDelta($sql);
        }

        /*$table_name = self::$table_name_for['FORM_RESPONSES'];

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
        {
            //Ensures proper charset support. Also limits support for WP v3.5+.
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                    `response_id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `form_id` INT(6),
                    `response_code` varchar(50),
                    `response` MEDIUMTEXT
                    )$charset_collate;";

            dbDelta($sql);
        }*/
        
        $table_name = self::$table_name_for['PAYPAL_FIELDS'];
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
        {
            //Ensures proper charset support. Also limits support for WP v3.5+.
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                    `field_id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `type` VARCHAR(50),
                    `name` VARCHAR(256),
                    `value` LONGTEXT,
                    `class` VARCHAR(256),
                    `option_label` LONGTEXT,
                    `option_price` LONGTEXT,
                    `option_value` LONGTEXT,
                    `description` LONGTEXT,
                    `require` LONGTEXT,
                    `order` INT(11),
                    `extra_options` LONGTEXT
                    )$charset_collate;";

            dbDelta($sql);
        }
        
        $table_name = self::$table_name_for['PAYPAL_LOGS'];
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
        {
            //Ensures proper charset support. Also limits support for WP v3.5+.
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                    `id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `submission_id` INT(6),
                    `form_id` INT(6),
                    `invoice` VARCHAR(50),
                    `txn_id` VARCHAR(600),
                    `status` VARCHAR(200),
                    `total_amount` DOUBLE,
                    `currency` VARCHAR(5),
                    `log` LONGTEXT,
                    `posted_date` VARCHAR(50)
                    )$charset_collate;";

            dbDelta($sql);
        }
        
        $table_name = self::$table_name_for['FRONT_USERS'];
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
        {
            //Ensures proper charset support. Also limits support for WP v3.5+.
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                    `id` int(11) AUTO_INCREMENT,
                    `email` varchar(255),
                    `otp_code` varchar(255) NOT NULL,
                    `last_activity_time` DATETIME,
                    `created_date` DATETIME, 
                    PRIMARY KEY (`Id`)
                    )$charset_collate;";

            dbDelta($sql);
        }

        $table_name = self::$table_name_for['STATS'];
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
        {
            //Ensures proper charset support. Also limits support for WP v3.5+.
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                    `stat_id` int(11) AUTO_INCREMENT PRIMARY KEY,
                    `form_id` INT(6),
                    `user_ip` varchar(20),
                    `ua_string` varchar(255),
                    `browser_name` varchar(50),
                    `visited_on` varchar(50),
                    `submitted_on` varchar(50),
                    `time_taken` INT(11)
                    )$charset_collate;";

            dbDelta($sql);
        }
        
        
        $table_name = self::$table_name_for['NOTES'];
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
        {
            //Ensures proper charset support. Also limits support for WP v3.5+.
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                      `note_id` int(11) NOT NULL AUTO_INCREMENT,
                      `submission_id` int(11) NOT NULL,
                      `notes` longtext DEFAULT NULL,
                      `status` varchar(255) DEFAULT NULL,
                      `publication_date` datetime NOT NULL,
                      `published_by` bigint(20) DEFAULT NULL,
                      `last_edit_date` datetime DEFAULT NULL,
                      `last_edited_by` bigint(20) DEFAULT NULL,
                      `note_options` longtext DEFAULT NULL,  
                       PRIMARY KEY (`note_id`))$charset_collate;";
                       //`type` longtext NOT NULL,
            dbDelta($sql);
        }
       
    }

    /**
     * Gets unique ids name for a table
     *
     * @param string $model_identifier
     * @return boolean|string
     */
    public static function get_unique_id_name($model_identifier)
    {

        switch ($model_identifier)
        {
            case 'FORMS':
                $unique_id_name = 'form_id';
                break;

            case 'FIELDS':
                $unique_id_name = 'field_id';
                break;

            case 'SUBMISSIONS':
                $unique_id_name = 'submission_id';
                break;

            case 'SUBMISSION_FIELDS':
                $unique_id_name = 'sub_field_id';
                break;

            case 'FORM_RESPONSES':
                $unique_id_name = 'response_id';
                break;
            
            case 'PAYPAL_FIELDS':
                $unique_id_name = 'field_id';
                break;
            
            case 'PAYPAL_LOGS':
                $unique_id_name = 'id';
                break;
            
            case 'FRONT_USERS' :
                $unique_id_name = 'id';
                break;

            case 'STATS' :
                $unique_id_name = 'stat_id';
                break;

            case 'NOTES' :
                $unique_id_name = 'note_id';
                break;

            default:
                return false;
        }

        return $unique_id_name;
    }

    /**
     * returns the table name according to its identifier
     *
     * @param string $model_identifier
     * @return string
     */
    public static function get_table_name_for($model_identifier){
        if(isset(self::$table_name_for[$model_identifier]))
            return self::$table_name_for[$model_identifier];
    }


    public static function delete_and_reset_table($identifier)
    {
      global $wpdb;

      $table_name = self::get_table_name_for($identifier);

      $qry = "TRUNCATE `$table_name`";
      $wpdb->query($qry);
    }


}

$RM_Table_Tech = RM_Table_Tech::get_instance();
