<?php
/**
 * Plugin Name: WP to Magento Bridge
 * Plugin URI:
 * Description: Basic account synchronization between Wordpress and Magento. Create a user acount on one system every time a user account has been created on the other system. Requires PHP oAuth
 * Author: Velomedia
 * Author URI:
 * Version: 1.1
 */

include plugin_dir_path( __FILE__ ) . '/includes/class-vm-wpmb-settings.php';

class VM_WP_Magento_Bridge {
	private static $_instance;
	public static $textdomain = 'VM_WP_Magento_Bridge';
	private static $includes_dir;
	private $settings;

	/**
	 *
	 */
	public static function get_instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	/**
	 * Class constructor
	 *
	 * @since 1.0
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts_and_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_and_styles' ) );

		add_action( 'user_register', array( $this, 'user_register_hook' ), 10, 1 );
		add_action( 'profile_update', array( $this, 'user_profile_update' ), 10, 1 );
		add_action( 'password_reset', array( $this, 'password_reset' ), 10, 2 );

		self::$includes_dir = path_join( plugin_dir_path( __FILE__ ), 'includes' );

		$this->settings = VM_WPMB_Settings::get_instance( self::$textdomain );
	}

	public function init() {
	}

	public function admin_init() {
	}

	public function admin_menu() {
	}

	public function scripts_and_styles() {
	}

	public function admin_scripts_and_styles() {
	}

	public function get_settings_object() {
		return $this->settings;
	}

	/**
	 * When a new user is created try to create an identinc account on Magento site
	 *
	 * @param int $user_id
	 */
	public function user_register_hook( $user_id ) {
		$user = get_user_by( 'id', $user_id );
		if ( false !== $user ) {
			if ( isset( $_POST['pass1'] ) ) {
				$this->create_user_on_magento( $user, $_POST['pass1'] );
			}
		}
	}

	private function get_soap_client_and_session() {
		$api_user = get_option( 'vmwpmb_magento_api_user' );
		if ( empty( $api_user ) ) {
			throw new Exception( 'Magento API user not set.' );
		}

		$api_key = get_option( 'vmwpmb_magento_api_key' );
		if ( empty( $api_key ) ) {
			throw new Exception( 'Magento API key not set.' );
		}

		$magento_url = get_option( 'vmwpmb_magento_site_url', '' );
		if ( empty( $magento_url ) ) {
			throw new Exception( 'Magento Site URL not set.' );
		}

		$client = new SoapClient( sprintf( '%sindex.php/api/soap?wsdl', trailingslashit( $magento_url ) ) );
		$session = $client->login( $api_user, $api_key );
		return array( $client, $session );
	}

	private function create_user_on_magento( $user, $password ) {
		$magento_password = md5( $password );

		$website_id = get_option( 'vmwpmb_magento_site_id', 1 );
		$group_id = get_option( 'vmwpmb_magento_group_id', 1 );

		try {
			list( $client, $session ) = $this->get_soap_client_and_session();

			$customer_data = array(
				'firstname'  => $user->first_name,
				'lastname'   => $user->last_name,
				'email'      => $user->get( 'user_email' ),
				'password_hash' => $magento_password,
				'website_id' => $website_id,
				'group_id'   => $group_id,
			);

			$result = $client->call( $session, 'customer.create', array( $customer_data ) );
			if ( is_integer( $result ) ) {
				update_user_meta( $user->ID, 'magento_id', absint( $result ) );
			}

			delete_option( 'wp_magento_bridge_error' );
			return intval( $result );
		} catch ( Exception $ex ) {
			error_log( sprintf( 'WP-Magento_Bridge exception: %s ', $ex->getMessage() ) );
			update_option( 'wp_magento_bridge_error', $ex->getMessage() );
			return null;
		}
	}

	private function create_magento_customer_id( $user_id, $password ) {
		$customer_id = null;
		try {
			$user = get_user_by( 'id', $user_id );
			if ( false === $user ) {
				throw new Exception( sprintf( 'Cannot find WP user with ID [%d]', $user_id ) );
			}

			list( $client, $session ) = $this->get_soap_client_and_session();
			$filters = array(
				'email' => $user->user_email
			);

			$result = $client->call( $session, 'customer.list', array( $filters ) );
			if ( is_array( $result ) ) {
				if ( 0 == count( $result ) ) {
					// Magento user does not exist, try to create
					$customer_id = $this->create_user_on_magento( $user, $password );
					if ( is_null( $customer_id ) ) {
						throw new Exception( sprintf( 'Cannot create a new Magento user for WP email [%s]', $user->user_email ) );
					}
				}
				else if ( 1 == count( $result ) ) {
					$customer_id = $result[0]['customer_id'];
					update_user_meta( $user_id, 'magento_id', absint( $customer_id ) );
				}
				else {
					throw new Exception( sprintf( 'Multiple Magento users with the same email [%s]', $user->user_email ) );
				}
			}
		} catch ( Exception $ex ) {
			error_log( sprintf( 'WP-Magento_Bridge {create_magento_customer_id} exception: %s ', $ex->getMessage() ) );
		}

		return $customer_id;
	}

	public function user_profile_update( $user_id ) {
		$customer_data = array();
		if ( isset( $_POST['email'] ) ) {
			$user = get_user_by( 'id', $user_id );
			if( false === $user ) {
				return;
			}
			$post_email = trim( $_POST['email'] );
			$old_email = $user->get( 'user_email' );
			if ( 0 == strcmp( $post_email, $old_email ) ) {
				$customer_data['email'] = $post_email;
			}
		}

		if ( ! isset( $_POST['pass1'] ) || '' == $_POST['pass1'] ) {
			if ( empty( $customer_data ) ) {
				return;
			}
		}
			
		if(isset($_POST['pass1']) && $_POST['pass1'] != ''){
			$password = trim( $_POST['pass1'] );
		}

		$customer_id = get_user_meta( $user_id, 'magento_id', true );
		if ( empty( $customer_id ) && isset($password) ) {
			$customer_id = $this->create_magento_customer_id( $user_id, $password );
		}

		if ( is_null( $customer_id ) ) {
			return;
		}
		
		if(isset($password)){
			$customer_data['password_hash'] = md5( $password );
		}
		
		if( empty($customer_data) ){
				return;
		}
			$this->update_magento_customer( $customer_id, $customer_data );
	}

	public function password_reset( $user, $new_pass ) {
		$user_id = $user->ID;

		$customer_id = get_user_meta( $user_id, 'magento_id', true );
		if ( empty( $customer_id ) ) {
			$customer_id = $this->create_magento_customer_id( $user_id, $password );
		}

		if ( is_null( $customer_id ) ) {
			return;
		}

		$this->update_magento_customer_password( $customer_id, $new_pass );
	}

	private function update_magento_customer( $customer_id, $customer_data = array() ) {
		try {
			list( $client, $session ) = $this->get_soap_client_and_session();
			$result = $client->call( $session, 'customer.update',
				array(
					'customerId' => $customer_id,
					'customerData' => $customer_data
				)
			);
			if ( false == ( boolean ) $result ) {
				error_log( sprintf( 'WP-Magento-Bridge unable to update Magento customer [%d]', $customer_id ) );
			}
		} catch ( Exception $ex ) {
			error_log( sprintf( 'WP-Magento_Bridge {user_profile_update} exception: %s ', $ex->getMessage() ) );
		}
	}

	private function update_magento_customer_password( $customer_id, $password ) {
		try {
			$magento_password = md5( $password );

			list( $client, $session ) = $this->get_soap_client_and_session();
			$result = $client->call( $session, 'customer.update',
				array(
					'customerId' => $customer_id,
					'customerData' => array( 'password_hash' => $magento_password )
				)
			);
			if ( false == ( boolean ) $result ) {
				error_log( sprintf( 'WP-Magento-Bridge unable to update Magento customer [%d] password', $customer_id ) );
			}
		} catch ( Exception $ex ) {
			error_log( sprintf( 'WP-Magento_Bridge {user_profile_update} exception: %s ', $ex->getMessage() ) );
		}
	}

	private function update_magento_customer_email( $customer_id, $email ) {
		try {
			list( $client, $session ) = $this->get_soap_client_and_session();
			$result = $client->call( $session, 'customer.update',
				array(
					'customerId' => $customer_id,
					'customerData' => array( 'email' => $email )
				)
			);
			if ( false == ( boolean ) $result ) {
				error_log( sprintf( 'WP-Magento-Bridge unable to update Magento customer [%d] email', $customer_id ) );
			}
		} catch ( Exception $ex ) {
			error_log( sprintf( 'WP-Magento_Bridge {user_profile_update} exception: %s ', $ex->getMessage() ) );
		}
	}
}

$GLOBALS['vm_wp_magento_bridge'] = VM_WP_Magento_Bridge::get_instance();

