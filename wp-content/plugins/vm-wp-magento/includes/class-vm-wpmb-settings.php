<?php
/**
 * WP to Magento Bridge Settings
 */

class VM_WPMB_Settings {
	private static $_instance;
	private static $includes_dir;
	private static $textdomain;
	
	/**
	 *
	 */
	public static function get_instance( $textdomain ) {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self( $textdomain );
		}
		return self::$_instance;
	}

	/**
	 * Class constructor
	 *
	 * @since 1.0
	 */
	private function __construct( $textdomain ) {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_and_styles' ) );

		self::$includes_dir = plugin_dir_path( __FILE__ );
		self::$textdomain = $textdomain;
	}

	public function admin_init() {
		// Sections
		add_settings_section( 'vmwpmb_wp2magento-section', 
			__( 'Connect to Magento Settings (SOAP)' ),
			array( $this, 'wp2magento_settings_callback' ),
			'wp2magento_settings'
		);
		
		add_settings_section( 'vmwpmb_wp2magento_soap-section', 
			'',
			array( $this, 'wp2magento_oauth_settings_callback' ),
			'wp2magento_oauth_settings'
		);
		
		add_settings_section( 'vmwpmb_wp2magento_oauth_authenticate-section', 
			'',
			array( $this, 'wp2magento_oauth_authenticate_callback' ),
			'wp2magento_oauth_authenticate'
		);		

		add_settings_section( 'vmwpmb_magento2wp-section', 
			__( 'WordPress Settings' ),
			array( $this, 'magento2wp_settings_callback' ),
			'magento2wp_settings'
		);



		// Fields
		add_settings_field( 'vmwpmb_magento_api_user', 
			__( 'API User', self::$textdomain ), 
			array( $this, 'text_field_callback' ), 
			'wp2magento_settings',
			'vmwpmb_wp2magento-section',
			array(
				'field' => 'vmwpmb_magento_api_user'
			)
		);

		add_settings_field( 'vmwpmb_magento_api_key', 
			__( 'API Key', self::$textdomain ), 
			array( $this, 'password_field_callback' ), 
			'wp2magento_settings',
			'vmwpmb_wp2magento-section',
			array(
				'field' => 'vmwpmb_magento_api_key'
			)
		);
		
		add_settings_field( 'vmwpmb_magento_site_url', 
			__( 'Magento Site URL', self::$textdomain ), 
			array( $this, 'text_field_callback' ), 
			'wp2magento_settings',
			'vmwpmb_wp2magento-section',
			array(
				'field' => 'vmwpmb_magento_site_url'
			)
		);
		
		add_settings_field( 'vmwpmb_magento_site_id', 
			__( 'Magento Site ID', self::$textdomain ), 
			array( $this, 'text_field_callback' ), 
			'wp2magento_settings',
			'vmwpmb_wp2magento-section',
			array(
				'field' => 'vmwpmb_magento_site_id',
				'description' => __( 'If you are not sure just set this value to 1', self::$textdomain )
			)
		);
		
		add_settings_field( 'vmwpmb_magento_group_id', 
			__( 'Magento User Group ID', self::$textdomain ), 
			array( $this, 'text_field_callback' ), 
			'wp2magento_settings',
			'vmwpmb_wp2magento-section',
			array(
				'field' => 'vmwpmb_magento_group_id',
				'description' => __( 'If you are not sure just set this value to 1', self::$textdomain )
			)
		);			
	
		add_settings_field( 'vmwpmb_wp_setting1', 
			__( 'TODO Setting 1', self::$textdomain ), 
			array( $this, 'text_field_callback' ), 
			'magento2wp_settings',
			'vmwpmb_magento2wp-section',
			array(
				'field' => 'vmwpmb_wp_setting1'
			)
		);

		register_setting( 'vmwpmb_wp2magento_group', 'vmwpmb_magento_api_user' );
		register_setting( 'vmwpmb_wp2magento_group', 'vmwpmb_magento_api_key' );
		register_setting( 'vmwpmb_wp2magento_group', 'vmwpmb_magento_site_url' );
		register_setting( 'vmwpmb_wp2magento_group', 'vmwpmb_magento_site_id', array( $this, 'magento_site_id_sanitize' ) );
		register_setting( 'vmwpmb_wp2magento_group', 'vmwpmb_magento_group_id', array( $this, 'magento_group_id_sanitize' ) );

		register_setting( 'vmwpmb_magento2wp_group', 'vmwpmb_wp_setting1' );
		
	}

	public function admin_menu() {
		add_options_page( __( 'WP Magento Bridge', self::$textdomain ),
			__( 'WP Magento Bridge', self::$textdomain ), 
			'manage_options',
			'wp-magento-bridge', 
			array( $this, 'admin_settings_page' ) );
	}

	public function admin_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', self::$textdomain ) ); 
		}

		include( self::$includes_dir . '/views/admin-settings.php' );
	}

	public function wp2magento_settings_callback() {
	}

	public function magento2wp_settings_callback() {
	}

	public function text_field_callback( $args ) {
		$field = $args['field'];
		$value = get_option( $field );
		printf( '<input type="text" name="%s" id="%s" class="regular-text" value="%s" />', esc_html( $field ), esc_html( $field ), esc_html( $value ) );
		if ( isset( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	public function password_field_callback( $args ) {
		$field = $args['field'];
		$value = get_option( $field );
		printf( '<input type="password" name="%s" id="%s" class="regular-text" value="%s" />', esc_html( $field ), esc_html( $field ), esc_html( $value ) );
		if ( isset( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}
	
	public function readonly_text_field_callback( $args ) {
		$field = $args['field'];
		$value = get_option( $field );
		printf( '<input type="text" readonly="readonly" name="%s" id="%s" class="regular-text" value="%s" />', esc_html( $field ), esc_html( $field ), esc_html( $value ) );
	}
	
	public function admin_scripts_and_styles() {
	}
	
	/**
	 * Sanitize Magento Site ID value
	 * 
	 * @param string $field
	 * @return int
	 */	
	public function magento_site_id_sanitize( $field ) {
		$val = (int) $field;
		if ( $val <= 0 ) {
			$val = 1;
		}
		return $val;
	}
	
	/**
	 * Sanitize Magento Customer Group ID value
	 * 
	 * @param string $field
	 * @return int
	 */
	public function magento_group_id_sanitize( $field ) {
		$val = (int) $field;
		if ( $val <= 0 ) {
			$val = 1;
		}
		return $val;
	}	
}
