<?php
/*
 *  Author: Todd Motto | @toddmotto
 *  URL: monrovia.com | @monrovia
 *  Custom functions, support, custom post types and more.
 */
define( 'MONROVIA_USE_CACHING', true );
define( 'MONROVIA_TRANSIENT_EXPIRE', 300 ); 
define( 'MONROVIA_PLANT_AVAILABILITY_EXPIRE', 300 );
define( 'MONROVIA_PLANT_DATA_TRANSIENT_EXPIRE', 300 ); // Does not clear on demand so keep this value low 

/*------------------------------------*\
	External Modules/Files
\*------------------------------------*/

// Load any external files you have here
include('includes/utility_functions.php');

// Load the iContact library
require_once('lib/iContact.php');

// Load XML Functions
//require_once('includes/class_xml.php');
//Try absolute path so they point to the same file.
require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_xml.php');


/*------------------------------------*\
	Theme Support
\*------------------------------------*/

if (!isset($content_width))
{
    $content_width = 900;
}

if (function_exists('add_theme_support'))
{
    // Add Menu Support
    add_theme_support('menus');

    // Add Thumbnail Theme Support
    add_theme_support('post-thumbnails');
    add_image_size('large', 700, '', true); // Large Thumbnail
    add_image_size('medium', 250, '', true); // Medium Thumbnail
    add_image_size('small', 120, '', true); // Small Thumbnail
    add_image_size('custom-size', 700, 200, true); // Custom Thumbnail Size call using the_post_thumbnail('custom-size');

    // Add Support for Custom Backgrounds - Uncomment below if you're going to use
    /*add_theme_support('custom-background', array(
	'default-color' => 'FFF',
	'default-image' => get_template_directory_uri() . '/img/bg.jpg'
    ));*/

    // Add Support for Custom Header - Uncomment below if you're going to use
   /* add_theme_support('custom-header', array(
	'default-image'			=> get_template_directory_uri() . '/img/headers/default.jpg',
	'header-text'			=> false,
	'default-text-color'		=> '000',
	'width'				=> 1000,
	'height'			=> 198,
	'random-default'		=> false,
	'wp-head-callback'		=> $wphead_cb,
	'admin-head-callback'		=> $adminhead_cb,
	'admin-preview-callback'	=> $adminpreview_cb
    )); */

    // Enables post and comment RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Localisation Support
    load_theme_textdomain('monrovia', get_template_directory() . '/languages');
}

/*------------------------------------*\
	Functions
\*------------------------------------*/

// HTML5 Blank navigation
function monrovia_nav()
{
	wp_nav_menu(
	array(
		'theme_location'  => 'header-menu',
		'menu'            => '', 
		'container'       => 'div', 
		'container_class' => 'menu-{menu slug}-container', 
		'container_id'    => '',
		'menu_class'      => 'menu', 
		'menu_id'         => '',
		'echo'            => true,
		'fallback_cb'     => 'wp_page_menu',
		'before'          => '',
		'after'           => '',
		'link_before'     => '',
		'link_after'      => '',
		'items_wrap'      => '<ul>%3$s</ul>',
		'depth'           => 0,
		'walker'          => ''
		)
	);
}

// Load HTML5 Blank scripts (header.php)
function monrovia_header_scripts()
{
    if (!is_admin()) {
    
    	//wp_deregister_script('jquery'); // Deregister WordPress jQuery
    	//wp_register_script('jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js', array(), '1.9.1'); // Google CDN jQuery
    	//wp_enqueue_script('jquery'); // Enqueue it!
    	
    	wp_register_script('conditionizr', 'http://cdnjs.cloudflare.com/ajax/libs/conditionizr.js/2.2.0/conditionizr.min.js', array(), '2.2.0'); // Conditionizr
        wp_enqueue_script('conditionizr'); // Enqueue it!
        
        wp_register_script('modernizr', 'http://cdnjs.cloudflare.com/ajax/libs/modernizr/2.6.2/modernizr.min.js', array(), '2.6.2'); // Modernizr
        wp_enqueue_script('modernizr'); // Enqueue it!
		
        wp_register_script('monroviascripts', get_template_directory_uri() . '/js/scripts.js?123', array( 'jquery' ), '1.0.0', true); // Custom scripts
        wp_enqueue_script('monroviascripts'); // Enqueue it!
		
		wp_register_script('jcycle', get_template_directory_uri() . '/js/jcycle2.min.js', array(), '2.0.0', true); // Custom scripts
        wp_enqueue_script('jcycle', get_template_directory_uri() . '/js/jcycle2.min.js', array(), '2.0.0', true); // Enqueue it!
		
		wp_register_script('swipe', get_template_directory_uri() . '/js/swipe.js', array( 'jcycle' ), '1.0.0', true); // Custom scripts
        wp_enqueue_script('swipe', get_template_directory_uri() . '/js/swipe.js', array( 'jcycle' ), '1.0.0', true); // Enqueue it!
		
		wp_register_script('jcyclecarousel', get_template_directory_uri() . '/js/jcycle.carousel.min.js', array(), '2.0.0', true); // Custom scripts
        wp_enqueue_script('jcyclecarousel', get_template_directory_uri() . '/js/jcycle.carousel.min.js', array(), '2.0.0', true); // Enqueue it!
		
		wp_register_script('jqueryselect', get_template_directory_uri() . '/js/customSelect.min.js', array(), '0.4.1', true); // Custom scripts
        wp_enqueue_script('jqueryselect'); // Enqueue it!
		
		wp_register_script('jtools', get_template_directory_uri() . '/js/overlay.min.js', array( 'jquery' ), '1.0.0', true); // Custom scripts
        wp_enqueue_script('jtools'); // Enqueue it!
		
		wp_register_script('jtoolsT', get_template_directory_uri() . '/js/toolbox.min.js', array( 'jquery' ), '1.0.0', true); // Custom scripts
        wp_enqueue_script('jtoolsT'); // Enqueue it!
		
    }
}

//Load jQuery UI to front end
function add_wordpress_scripts() {
    wp_enqueue_script( 'jquery-ui-tabs' );

	if( is_page() ) { //Check if we are viewing a page
		global $wp_query;

		//Check which template is assigned to current page we are looking at
		$template_name = get_post_meta( $wp_query->post->ID, '_wp_page_template', true );

		if ( 'plant.php' == $template_name ) {
			$plant_id = $wp_query->query_vars['pid'];
			if ( $plant_id ) {
				$record = monrovia_get_plant_record( $plant_id );

			wp_register_script( 'plant', get_stylesheet_directory_uri() .'/js/plant.min.js', array( 'jquery', 'jquery-ui-tabs', 'monroviascripts', 'jcycle', 'jcyclecarousel' ) );
				
				// [todo] - create two hidden fields in the template and read values from there instead of localizing
			wp_localize_script( 'plant', 
					'monrovia_plant_record', 
					array(
						'plant_id'   => $record->info['id'],
						'plant_item' => $record->info['item_number'],
						'plant_genus' => $record->info['botanical_genus'] 
					) 
				);
				wp_enqueue_script( 'plant' );
			}
		} // End Plant
	}	
	//If viewing a YouTube video, add Youtube tracking script
	if('youtube_video' == get_post_type()){
		wp_register_script('trackYouTube', get_template_directory_uri() . '/js/trackYouTube.min.js', array( 'jquery' ), '3.0', true); // Custom scripts
		wp_enqueue_script('trackYouTube'); // Enqueue it!
	}
	
	//If viewing a SpokePerson or Collection Type, add cycle and caursel
	if('spokesperson' == get_post_type() || 'plant_collection' == get_post_type()){
		wp_register_script( 'collection', get_stylesheet_directory_uri() .'/js/plant.min.js', array( 'jquery', 'jquery-ui-tabs', 'monroviascripts', 'jcycle', 'jcyclecarousel' ) );
		wp_enqueue_script( 'collection' );
	}
}

add_action( 'wp_enqueue_scripts', 'add_wordpress_scripts' ); // wp_enqueue_scripts action hook to link only on the front-end

add_filter('wpmu_signup_user_notification', 'auto_activate_users', 10, 4);
function auto_activate_users($user, $user_email, $key, $meta){
  wpmu_activate_signup($key);
  return false;
}

/******* CRON Functions **********/

// Create Custom Intervals for CRON Jobs 
function my_intervals( $schedules ) {
	$schedules['minute'] = array(
 		'interval' => 60,
 		'display' => __( 'Once 1 Minute' ) // Is that a Word??
 	);
 	$schedules['minutes'] = array(
 		'interval' => 10*60,
 		'display' => __( 'Once 10 Minutes' ) // Is that a Word??
 	);
	$schedules['daily'] = array(
 		'interval' => 60*60*24,
 		'display' => __( 'Once Daily' ) // Is that a Word??
 	);
	$schedules['min20'] = array(
 		'interval' => 20*60,
 		'display' => __( '20 minutes' ) // Is that a Word??
 	);
	$schedules['min5'] = array(
 		'interval' => 5*60,
 		'display' => __( '5 minutes' ) // Is that a Word??
 	);
 	return $schedules;
}
add_filter( 'cron_schedules', 'my_intervals' ); 
 
/* Catalog Queue Cron Job */ 
 if (!wp_next_scheduled('cron_catalog')) {
    wp_schedule_event(time(), 'minutes', 'cron_catalog');
 }

function ProcessCatalogQueue() {
	$handle = fopen("http://".$_SERVER['HTTP_HOST']."/inc/crons/catalog_queue.php", "r");
 	//wp_mail( 'brettex@hotmail.com', 'Catalog Queue', 'Another CRON Job Success' );
}

add_action( 'cron_catalog', 'ProcessCatalogQueue' );

/* Shop Availibility Database Update */
if ( ! wp_next_scheduled( 'shop_updates' ) ) {
  wp_schedule_event( time(), 'daily', 'shop_updates' );
}

add_action( 'shop_updates', 'updateShopTable' );

/* Hort Sync Cron Job */ 

/* if (!wp_next_scheduled('cron_hort_db')) {
   wp_schedule_event(time(), 'min20', 'cron_hort_db');
}
function ProcessHortSyncDB() {
	$handle = fopen("http://".$_SERVER['HTTP_HOST']."/inc/crons/test_sync_db.php", "r");
 	//wp_mail( 'brettex@hotmail.com', 'Hort Sync', 'Another CRON Job Success' );
}
add_action( 'cron_hort_db', 'ProcessHortSyncDB' ); */

if (!wp_next_scheduled('cron_hort')) {
   wp_schedule_event(time(), 'min20', 'cron_hort');
}

function ProcessHortSync() {
	//$handle = fopen("http://".$_SERVER['HTTP_HOST']."/inc/crons/test_sync_xml.php", "r");
	//$handle = fopen("http://".$_SERVER['HTTP_HOST']."/inc/crons/horticultural_printers_sync.php", "r");
 	//wp_mail( 'brettex@hotmail.com', 'Hort Sync', 'Another CRON Job Success' );
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/nusoap/lib/nusoap.php');
	//$rpc_url = "http://www.horticulturalprinters.com";
	$rpc_url = "http://www.epopglobal.com";
	//$rpc_path = "/monrovia/webservices/monroviaupdateservice.asmx";
	$rpc_path = "/monroviaIX/webservices/monroviaupdateservice.asmx";
	$username = 'monrovia';
	$password = 'plants';
	$wsdl = $rpc_url . $rpc_path . "?wsdl";

	$is_full_synch = false;
	//if (isset($_GET['full_synch'])) {
	//    $is_full_synch = ($_GET['full_synch'] == '1') ? true : false;
	//}
	//$plant_id = '';
	//if (isset($_GET['id'])) {
	//    $plant_id = $_GET['id'];
	//}

	$response = generate_xml($is_full_synch);
	$xml_data = $response[1];
	$id = $response[0];

	if ($xml_data != '' && count($id) > 0) {
		$_time = time();

		$file_handler = fopen($_SERVER['DOCUMENT_ROOT']."/inc/crons/logs/test_sync_" . $_time . ".txt", 'a+');

		$status = '';
		$details = '';
		$err = '';

		$message = "DATE/TIME: " . date('Y-m-d H:i:s') . "\nREQUEST:\n" . " xml_data " . "\n\n*************\n\n";
		$filemessage = $message;

		$params = array('plantXML' => $xml_data, 'username' => $username, 'password' => $password);

		$namespace = $rpc_url . '/MonroviaIX/Webservices';

		$client = new nusoap_client($wsdl, 'wsdl', false, false, false, false, 600, 600);

		// SEND REQUEST
		try {
			$client->call('UpdatePlant', $params, $namespace, 'MonroviaDataSynchronizer/UpdatePlant/');
		} catch (Exception $exc) {
			echo $exc->getMessage();
			echo $exc->getTraceAsString();
			echo "Exception";
			exit();
		}


		//$client->call('UpdatePlant', $params, $namespace, 'UpdatePlant');
		if ($client->fault) {
			$fault = $client->fault;
			echo 'Test:' . print_r($fault);
		}

		$err = trim($client->getError());
		
		$status = ($err != '') ? 'ERROR' : 'SUCCESS';

		$details = $client->response;
		$request = $client->request;

		$filemessage .= "DATE/TIME: " . date('Y-m-d H:i:s') . "\r\nSTATUS: "
				. $status . "\r\nREQUEST:\r\n"
				. " $request " . "\r\n\r\nRESPONSE:\r\n\r\n"
				. $details . "\r\n\r\n*************\r\n\r\n";
		
		fwrite($file_handler, $filemessage);

	//     if ($is_full_synch) {

		if ($err == '') {
			// UNMARK PLANTS
			foreach ($id as $pid) {
				mysql_query("UPDATE plants SET synch_with_hort=0 WHERE id=" . $pid);
			}
		} else {
			// UNMARK PLANTS
			foreach ($id as $pid) {
				mysql_query("UPDATE plants SET synch_with_hort=2 WHERE id=" . $pid);
			}
		}


		$_email_message = "Result: $err " . var_export($id, true);
		wp_mail('wayne.le2k10@gmail.com', 'Hort Sync', $_email_message);
		wp_mail('jvo@bluecalypso.com', 'Hort Sync', $_email_message);
		unset($client);
	} else {
		wp_mail('wayne.le2k10@gmail.com', 'Hort Sync', 'No item update!');
		wp_mail('jvo@bluecalypso.com', 'Hort Sync', 'No item update!');
	}
}

add_action( 'cron_hort', 'ProcessHortSync' );


add_option( 'monrovia_export_flag', '0', '', 'no' );
if (!wp_next_scheduled('cron_report_generate')) {
    wp_schedule_event(time(), 'min5', 'cron_report_generate');
}
function generate_file_export() {
	$_export_flag = get_option('monrovia_export_flag', '0');
	if ($_export_flag == '1') {
		//$handle = fopen("http://".$_SERVER['HTTP_HOST']."/monrovia_admin/export_handle.php?exbycron=1", "r");
		require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_plant.php');
		try {
			$_exp_path = $_SERVER['DOCUMENT_ROOT']."/monrovia_admin/export_auto/";
			$_exp_filename = date("Y-m-d--H-i-s", time()) . ".xls";
			//$_is_cron = isset($_GET['exbycron']) ? $_GET['exbycron'] : "";
			$_max_file = 3;
			$_email = "wayne.le2k10@gmail.com";
			update_option( 'monrovia_export_flag', '2' );
			if ($email == null) {
				$email = $_email;
			}
			//echo('<div style="font-family:tahoma;font-size:11pt;">Generating plant database export. An email will be sent to ' . $monrovia_user->info['email_address'] . ' once it\'s ready. To cancel, press Esc and close this tab.');
			//flush();

			$field_ids = explode(',','is_active,is_new,item_number,types_friendly,deciduous_evergreen,collection_name,common_name,botanical_name,trademark_name,synonym,botanical_family,botanical_genus,botanical_species,botanical_subspecies,botanical_cultivar,phonetic_spelling,is_monrovia_exclusive,special_third_party,release_status,patent,patent_act,year_introduced,geographical_origin,buy_now_url,subcategory,description_design,description_benefits,description_lore,description_history,description_companion_plants,description_catalog,primary_attribute,garden_styles_friendly,special_features_friendly,problem_solutions_friendly,landscape_uses_friendly,attributes,foliage_color,foliage_color_under,foliage_color_new,foliage_color_winter,foliage_color_spring,foliage_color_summer,foliage_color_fall,description_foliage,foliage_shape,flower_color,flowering_time,flowering_seasons_friendly,flower_attributes_friendly,description_flower,growth_habits_friendly,growth_rate,fertilizer[\'name\'],spread,height,pruning_time,average_landscape_size,propagation,description_care,growth_habit,water_requirement,water_requirement_details,sun_exposures_friendly,cold_zone_low,cold_zone_high,sunset_zones_friendly');

			$template_rows = <<<HTML
				<tr>
					<td>{is_active}</td>
					<td>{is_new}</td>
					<td>{item_number}</td>
					<td>{types_friendly}</td>
					<td>{deciduous_evergreen}</td>
					<td>{collection_name}</td>
					<td>{common_name}</td>
					<td>{botanical_name}</td>
					<td>{trademark_name}</td>
					<td>{synonym}</td>
					<td>{botanical_family}</td>
					<td>{botanical_genus}</td>
					<td>{botanical_species}</td>
					<td>{botanical_subspecies}</td>
					<td>{botanical_cultivar}</td>
					<td>{phonetic_spelling}</td>
					<td>{is_monrovia_exclusive}</td>
					<td>{special_third_party}</td>
					<td>{release_status}</td>
					<td>{patent}</td>
					<td>{patent_act}</td>
					<td>{year_introduced}</td>
					<td>{geographical_origin}</td>
					<td>{buy_now_url}</td>
					<td>{subcategory}</td>
					<td>{description_design}</td>
					<td>{description_benefits}</td>
					<td>{description_lore}</td>
					<td>{description_history}</td>
					<td>{description_companion_plants}</td>
					<td>{description_catalog}</td>
					<td>{primary_attribute}</td>
					<td>{garden_styles_friendly}</td>
					<td>{special_features_friendly}</td>
					<td>{problem_solutions_friendly}</td>
					<td>{landscape_uses_friendly}</td>
					<td>{attributes}</td>
					<td>{foliage_color}</td>
					<td>{foliage_color_under}</td>
					<td>{foliage_color_new}</td>
					<td>{foliage_color_winter}</td>
					<td>{foliage_color_spring}</td>
					<td>{foliage_color_summer}</td>
					<td>{foliage_color_fall}</td>
					<td>{description_foliage}</td>
					<td>{foliage_shape}</td>
					<td>{flower_color}</td>
					<td>{flowering_time}</td>
					<td>{flowering_seasons_friendly}</td>
					<td>{flower_attributes_friendly}</td>
					<td>{description_flower}</td>
					<td>{growth_habits_friendly}</td>
					<td>{growth_rate}</td>
					<td>{fertilizer['name']}</td>
					<td>{spread}</td>
					<td>{height}</td>
					<td>{pruning_time}</td>
					<td>{average_landscape_size}</td>
					<td>{propagation}</td>
					<td>{description_care}</td>
					<td>{growth_habit}</td>
					<td>{water_requirement}</td>
					<td>{water_requirement_details}</td>
					<td>{sun_exposures_friendly}</td>
					<td>{cold_zone_low}</td>
					<td>{cold_zone_high}</td>
					<td>{sunset_zones_friendly}</td>
				</tr>
HTML;

			$export_id = date('Ymd');
			$file_handler = fopen($_exp_path.$_exp_filename, 'w+');
			if(!$file_handler) die('fail');
			//if(!$file_handler) die('<br /><br /><b>Error: Could not write to export file. Please make sure no one else is currently running an export.</b>');
			fwrite($file_handler,file_get_contents($_SERVER['DOCUMENT_ROOT'].'/monrovia_admin/inc/templates/plant_export.htm'));
			$_sql = "SELECT id FROM plants";
			$result = mysql_query($_sql);
			$num_rows = mysql_num_rows($result);

			for($i=0;$i<$num_rows;$i++){
				//set_time_limit(5); // ALLOW UP TO 5 SECONDS PER PLANT RECORD

				$id = mysql_result($result,$i,"id");
				$record = new plant($id);
				$html = $template_rows;
				foreach($field_ids as $field_id){

					switch($field_id){
						case 'fertilizer[\'name\']':
							$html = str_replace('{'.$field_id.'}',(isset($record->info['fertilizer'])?html_sanitize($record->info['fertilizer']->name):''),$html);
							break;
						case 'is_new':
							$html = str_replace('{'.$field_id.'}',($record->info[$field_id]=='1')?'Yes':'No',$html);
							break;
						case 'is_monrovia_exclusive':
							$html = str_replace('{'.$field_id.'}',($record->info[$field_id]=='1')?'Yes':'No',$html);
							break;
						case 'is_active':
							$html = str_replace('{'.$field_id.'}',($record->info[$field_id]=='1')?'Yes':'No',$html);
							break;
						case 'item_number':
							$html = str_replace('{'.$field_id.'}','#'.$record->info[$field_id],$html);
							break;
						case 'year_introduced':
							$year = $record->info[$field_id];
							if($year!='0000'){
								$html = str_replace('{'.$field_id.'}',$year,$html);
							}else{
								$html = str_replace('{'.$field_id.'}','',$html);
							}
							break;
						default:
							$html = str_replace('{'.$field_id.'}',(isset($record->info[$field_id]))?html_sanitize($record->info[$field_id]):'',$html);
					}
				}
				fwrite($file_handler,$html);
			}

			fwrite($file_handler,'</table></body></html>');
			fclose($file_handler);

			// $url = 'http://'.$_SERVER['HTTP_HOST'].'/monrovia_admin/export_plants.php?export_id='.$export_id;
			
			// $emailHeaders = "Reply-To: noreply@monrovia.com\r\n";
			// $emailHeaders .= "Return-Path: noreply@monrovia.com\r\n";
			// $emailHeaders .= "From: Monrovia <noreply@monrovia.com>\r\n";
			// $emailHeaders .= 'Signed-by: monrovia.com\r\n"';
			// $emailHeaders .= 'MIME-Version: 1.0' . "\r\n";
			// $emailHeaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

			// wp_mail( $email, 'Monrovia plant database export','<a href="'.$url.'">'.$url.'</a>', $emailHeaders );
			$exp_files = glob($_SERVER['DOCUMENT_ROOT'].'/monrovia_admin/export_auto/*.xls');
			if (count($exp_files) > 0) {
				arsort($exp_files);
				$_i = 0;
				foreach ($exp_files as $file) {
					$_i ++;
					if ($_i > $_max_file) {
						if (is_file($file)) {
							unlink($file);
						}
					}
				}
			}
			update_option('monrovia_export_flag', '0');
		} catch (Exception $e) {
			update_option('monrovia_export_flag', '1');
			error_log($e->getMessage());
		}
	}
}

add_action( 'cron_report_generate', 'generate_file_export' );
/*************  END CRON ************/
/*************  NWN option ************/
add_option( 'monrovia_plant_type_not_sale', '', '', 'no' );
/*************  END NWN option ************/

function tml_title_filter( $title, $action ) {
	if ( $action == 'login' || $action == 'register' )
		return __( '' );
	return $title;
}
add_filter( 'tml_title', 'tml_title_filter', 10, 2 );

// Load HTML5 Blank conditional scripts
function monrovia_conditional_scripts()
{
    if (is_page('plants')) {
        wp_register_script('lightbox', get_template_directory_uri() . '/js/lightbox2.min.js', array('jquery'), '2.0.0'); // Conditional script(s)
        wp_enqueue_script('lightbox'); // Enqueue it!
    }
}

// Load HTML5 Blank styles
function monrovia_styles()
{
    wp_register_style('normalize', get_template_directory_uri() . '/normalize.min.css', array(), '1.0', 'all');
    wp_enqueue_style('normalize'); // Enqueue it!
    
	if(isset($_GET['screen']) && $_GET['screen']=='print'){
	// Only attach print.css!
	wp_register_style('print', get_template_directory_uri() . '/css/print.min.css', array(), '1.0', 'all');
    wp_enqueue_style('print'); // Enqueue it!
	} else {
    wp_register_style('monrovia', get_template_directory_uri() . '/style.css?'.time(), array(), '1.0', 'all');
    wp_enqueue_style('monrovia'); // Enqueue it!
	}
	wp_register_style( 'monrovia-styles', get_template_directory_uri().'/css/customize.css', array(), '1.0', 'all');
	wp_enqueue_style( 'monrovia-styles' );
	wp_register_style( 'monrovia-home-slider-styles', get_template_directory_uri().'/css/home-slider.css', array(), '1.0', 'all');
	wp_enqueue_style( 'monrovia-home-slider-styles' );
}

// Register HTML5 Blank Navigation
function register_monrovia_menu()
{
    register_nav_menus(array( // Using array to specify more menus if needed
        'header-menu' => __('Header Menu', 'monrovia'), // Main Navigation
        'sidebar-menu' => __('Sidebar Menu', 'monrovia'), // Sidebar Navigation
        'footer-menu' => __('Footer Menu', 'monrovia') // Extra Navigation if needed (duplicate as many as you need!)
    ));
}

// Remove the <div> surrounding the dynamic navigation to cleanup markup
function my_wp_nav_menu_args($args = '')
{
    $args['container'] = false;
    return $args;
}

// Remove Injected classes, ID's and Page ID's from Navigation <li> items
function my_css_attributes_filter($var)
{
    return is_array($var) ? array() : '';
}

// Remove HTML Filter on Category Descriptions so HTML is allowed
foreach ( array( 'pre_term_description' ) as $filter ) {
remove_filter( $filter, 'wp_filter_kses' );
}
foreach ( array( 'term_description' ) as $filter ) {
remove_filter( $filter, 'wp_kses_data' );
}

// Remove invalid rel attribute values in the categorylist
function remove_category_rel_from_category_list($thelist)
{
    return str_replace('rel="category tag"', 'rel="tag"', $thelist);
}

// Add page slug to body class, love this - Credit: Starkers Wordpress Theme
function add_slug_to_body_class($classes)
{
    global $post;
    if (is_home()) {
        $key = array_search('blog', $classes);
        if ($key > -1) {
            unset($classes[$key]);
        }
    } elseif (is_page()) {
        $classes[] = sanitize_html_class($post->post_name);
    } elseif (is_singular()) {
        $classes[] = sanitize_html_class($post->post_name);
    }


    return $classes;
}

// Add sidebar Classes to Body if sidebars are present */
add_action('wp_head', create_function("",'ob_start();') );
add_action('get_sidebar', 'my_sidebar_class');
add_action('wp_footer', 'my_sidebar_class_replace');
 
function my_sidebar_class($name=''){
  static $class="sidebar";
  if(!empty($name))$class.=" sidebar-{$name}";
  my_sidebar_class_replace($class);
}
 
function my_sidebar_class_replace($c=''){
  static $class='';
  if(!empty($c)) $class=$c;
  else {
    echo str_replace('<body class="','<body class="'.$class.' ',ob_get_clean());
    ob_start();
  }
}

	// Register Regions
	if (!function_exists('monrovia_register_sidebars')) {
	function monrovia_register_sidebars() {
		foreach (array(
					__('Homepage', 'monrovia'),
					__('Left Sidebar', 'monrovia'),
					__('Right Sidebar', 'monrovia'),
					__('Lower Right Sidebar', 'monrovia'),
					__('Footer', 'monrovia')
					) as $sidebartitle) {
			register_sidebar(array(
						'name'=> $sidebartitle,
						'id' => 'sidebar-'.sanitize_title($sidebartitle),
    					'before_widget' => '<div id="%1$s" class="widget %2$s">',
    					'after_widget'  => '</div>',
    					'before_title'  => '<h3>',
    					'after_title'   => '</h3>'
						));
		}
	}
}
add_action('widgets_init', 'monrovia_register_sidebars');

// Remove wp_head() injected Recent Comment styles
function my_remove_recent_comments_style()
{
    global $wp_widget_factory;
    remove_action('wp_head', array(
        $wp_widget_factory->widgets['WP_Widget_Recent_Comments'],
        'recent_comments_style'
    ));
}

// Get a post thumbnail attributes
function wp_get_attachment( $attachment_id ) {

	$attachment = get_post( $attachment_id );
	return array(
		'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
		'caption' => $attachment->post_excerpt,
		'description' => $attachment->post_content,
		'href' => get_permalink( $attachment->ID ),
		'src' => $attachment->guid,
		'title' => $attachment->post_title
	);
}

// Force get_the_content() to maintain the formatting like the_content() does

function get_the_content_with_formatting ($more_link_text = '(more...)', $stripteaser = 0, $more_file = '') {
	$content = get_the_content($more_link_text, $stripteaser, $more_file);
	$content = apply_filters('the_content', $content);
	$content = str_replace(']]>', ']]&gt;', $content);
	return $content;
}

// Remove Login Attempts remaining after a user has 
// Logged in
/*function limit_login_add_error_message() {
	global $error, $limit_login_my_error_shown;

	if ( !is_user_logged_in() ) {
		if (!should_limit_login_show_msg() || $limit_login_my_error_shown) {
			return;
		}

		$msg = limit_login_get_message();

		if ($msg != '') {
			$limit_login_my_error_shown = true;
			$error .= $msg;
		}

		return;
	}else{
		return;
	}
} */

// Custom Breadcrumb Function
function the_breadcrumb($id = '', $calendar = '', $plant = '', $tax = ''){
	
		if(!is_front_page()) {
			echo '<div class="breadcrumb"><a href="';
			echo get_option('home');
			echo '">Home</a>';
		}
		
		if($calendar == 'calendar'){
			echo " &gt; Calendar";
		}
		if($calendar == 'event'){
			  echo " &gt; <a href='".get_bloginfo('url')."/event-calendar/' title='Calendar'>Calendar</a>";
		}
		
			// If its single post 
			if ((is_single() || is_archive()) && $calendar != 'event' ) {
				if('youtube_video' == get_post_type()){
					$parentID = 39;
				} elseif('press_release' == get_post_type()){
					$parentID = 24;
				} elseif('design_style' == get_post_type()){
					$parentID = 35;
				} elseif('collection' == get_post_type() || 'spokesperson' == get_post_type() || 'plant_collection' == get_post_type() ){
					$parentID = 34;
				} elseif('garden' == get_post_type()){
					
					$tempParent = getGardenParent($tax);
					$parentID = $tempParent['ID'];	
				}
			$link = get_permalink($parentID);
			$title = get_page($parentID)->post_title;
			$step_parent = "  &gt; <a href='".$link."' title='".$title."'>".$title."</a>";
			}

			if($id != '' || $calendar == 'event'){
				$parents = get_ancestors($id, 'page');
				$parents  = array_reverse($parents);
				//If Garden Type, set to Design Inspiration landing page
				if('garden' == get_post_type()){
					$parents = array('35');
				}
				// Loop through all parents 
				foreach($parents as $parent){
					$link = get_permalink($parent);
					$title = get_page($parent)->post_title;
					echo "  &gt; <a href='".$link."' title='".$title."'>".$title."</a>";
				}
				if(isset($step_parent)){ echo $step_parent; }
			}
			
			if( is_page_template('plant.php')){
				// Print Plant Title
				echo  " &gt; ".$plant;
			} else {
				if($id != ''){
					// Print the Page Title
					echo  " &gt; ".get_page($id)->post_title;	
				}
			}
			echo "</div>"; // End Bredcrumb div

	} // End function

add_filter( 'tribe-events-bar-should-show', '__return_true' );

// Pagination for paged posts, Page 1, Page 2, Page 3, with Next and Previous Links, No plugin
function html5wp_pagination()
{
    global $wp_query;
    $big = 999999999;
    echo paginate_links(array(
        'base' => str_replace($big, '%#%', get_pagenum_link($big)),
        'format' => '?paged=%#%',
        'current' => max(1, get_query_var('paged')),
        'total' => $wp_query->max_num_pages
    ));
}

/**********   Query for the Facebook Contest Winner ************/
function get_facebook_banner($home = ''){
	global $wpdb;
	$args = array('post_per_page' => 1, 'post_type' => 'facebook_contest');
  	$query  = get_posts( $args );  
	foreach ( $query as $post ) :
  		setup_postdata( $post );
		$credit = get('photo_credit',1,1,$post->ID);
			echo "<div><h2>".get_the_title($post->ID)."</h2>";
			if($home == 'true'){
				echo get_the_content()."</div>";
				echo get_the_post_thumbnail($post->ID, 'full');
				if($credit) echo "<span>".$credit."</span>";
			} else {
				echo get_the_content();
				if($credit) echo " <span>".$credit."</span>";
				echo "</div>";
				echo get_the_post_thumbnail($post->ID, 'full');
			}
	endforeach; 
	wp_reset_postdata();
}
/**
 * Checks if a particular user has a role. 
 * Returns true if a match was found.
 *
 * @param string $role Role name.
 * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
 * @return bool
 */
function check_user_role( $role, $user_id = null ) {
 
    if ( is_numeric( $user_id ) )
	$user = get_userdata( $user_id );
    else
        $user = wp_get_current_user();
 
    if ( empty( $user ) )
	return false;
 
    return in_array( $role, (array) $user->roles );
}


/**********   Query for the Facebook Contest Winner ************/
/**
	@variable $home - Send Slightly different HTML markup if is_home()

*/
function getPromoBanner($home = ''){
	if ( 'true' == $home ) {
		$content = monrovia_get_cache( 'promo_banner_home' );
	}
	else {
		$content = monrovia_get_cache( 'promo_banner' );
	}
	
	if ( false === $content ) {
		ob_start();
	
		global $wpdb;
		global $post;
		$args = array('post_per_page' => 1, 'post_type' => 'facebook_contest');
		$query  = get_posts( $args );  
		foreach ( $query as $post ) :
			setup_postdata( $post );
			$credit = get('photo_credit',1,1,$post->ID);
			$link = get('image_link',1,1,$post->ID);
				if($home == 'true'){
					echo "<div><h2>".get_the_title($post->ID)."</h2>";
					echo get_the_content()."</div>";
					echo get_the_post_thumbnail($post->ID, 'full');
					if($credit) echo "<span>".$credit."</span>";
				} else {
					echo '<div id="facebook_contest_banner">';
					echo "<div><h2>".get_the_title($post->ID)."</h2>";
					echo get_the_content($post->ID);
					if($credit) echo " <span>".$credit."</span>";
					echo "</div>";
					if($link != ''){
						echo "<a href='".$link."'>";
						echo get_the_post_thumbnail($post->ID, 'full');
						echo "</a>";
					} else {
						echo get_the_post_thumbnail($post->ID, 'full');
					}
					echo "</div>";
				}
		endforeach; 
		wp_reset_postdata();
		
		$content = ob_get_clean();
		
		if ( 'true' == $home ) {
			$transient_name = 'promo_banner_home';
		}
		else {
			$transient_name = 'promo_banner';
		}
		monrovia_set_cache( $transient_name, $content, MONROVIA_TRANSIENT_EXPIRE );
	}
	echo $content;
}

/**********   Return Tooltip content for Cold Zone Box (?) icon ************/
/**
	Currently does not take any variables
	Pulls the excerpt and Featured Image from the Cold Zone page
	- Page ID-=
*/
function getZoneTip(){
	global $wpdb;
	global $post;
	$args = array('p' => 238, 'post_type' => 'page');
  	$query  = get_posts( $args );  
	foreach ( $query as $post ) :
  		setup_postdata( $post );
			$img = wp_get_attachment(get_post_thumbnail_id( $post->ID ));
			echo "<div class='tip-content'><span class='tip-close'><i class='fa fa-times-circle'></i></span>";
			echo "<h4>".get_the_title($post->ID)."</h4>";
			echo "<img src='".$img['src']."' class='alignleft hideMobile' alt='".$img['alt']."' />";
			echo get_the_excerpt();
			echo "<br /><a href='".get_permalink($post->ID)."' title='Learn More'>Learn More ></a></div>";
	endforeach; 
	wp_reset_postdata();
}

/********* Add a view Counter for Videos ***********************/
/**	
	@variable $id -  the post ID where the Video Lives
	@returns  - NULL
*/

function addVideoView($postID){
		
	//Get Video Count for post meta
	$sql = "SELECT meta_value FROM wp_postmeta WHERE post_id = '$postID' AND meta_key = 'video-views-count'";
	//If the count field already exists
	if(mysql_num_rows(mysql_query($sql))> 0){
		$count = mysql_fetch_array(mysql_query($sql));
		$count = $count['meta_value'] + 1;
		// Update the value
		mysql_query("UPDATE wp_postmeta SET meta_value = '$count' WHERE post_id = '$postID' AND meta_key = 'video-views-count'");
	} else {
		// Create a new record in wp_postmeta
		mysql_query("INSERT INTO wp_postmeta ( post_id, meta_key, meta_value ) VALUES ( '$postID', 'video-views-count', 1)");
	}
			
}

/*********  Get the Most Watched Videos ************************/
/**
	@variable $number - the number of videos to display
*/
function  most_watched_videos($number){
	global $wpdb;
	$args = array('post_per_page' => $number, 'post_type' => 'youtube_video', 'orderby' => 'meta_value_num', 'meta_key' => 'video-views-count', 'order' => 'DESC' );
	$query = get_posts( $args );
	foreach ( $query as $post ) :
  		setup_postdata( $post );
			echo "<a href='".get_permalink($post->ID)."' title='".get_the_title($post->ID)."'>".get_the_title($post->ID)."</a>";
	endforeach; 
	wp_reset_postdata();
}

/*********  Get the Most Recent Videos ************************/
/**
	@variable $number - the number of videos to display
*/
function  get_videos($number){
	global $wpdb;
	$args = array('post_per_page' => $number, 'post_type' => 'youtube_video', 'order' => 'DESC' );
	$query = get_posts( $args );
	foreach ( $query as $post ) :
  		setup_postdata( $post );
			$thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID));
			echo "<div class='video-listing clear'>";
			echo "<a href='".get_permalink($post->ID)."' title='".get_the_title($post->ID)."' class='left'><img src='http://img.youtube.com/vi/".get('youtube_id',1,1,$post->ID)."/default.jpg' /></a>";
			echo "<div class='video-info small left'><a href='".get_permalink($post->ID)."'>".get_the_title($post->ID)."</a>";
			//echo monrovia_wp_excerpt('monrovia_short_excerpt', '...');
			echo "</div></div>";
	endforeach; 
	wp_reset_postdata();
}

/******** Get the USDA Cold Zone based on varying Criteria from the user ******/
/** 
	@string -  returns Zone, and Zip Code as an Array()
	@variable - $zipcode If non-logged in user updated zip code, use this
*/
function get_cold_zone(){
			
		//Set up an array to store the return values
		$values = Array();
		
		// Check to see if zipcode is set
		if(isset($_POST['zipcode']))$zipcode = $_POST['zipcode'];
			
		//Check to see if the User is logged in
		if( is_user_logged_in() ){
			// Use the Users saved zip code
			$userID = get_current_user_id();
			$values['zipcode'] = get_cimyFieldValue($userID, 'ZIP_CODE');
		} elseif(isset($zipcode)){
			$values['zipcode'] = $zipcode;
		}
		$zip = $values['zipcode'];
		$sql = "SELECT * FROM monrovia_zones_usda_zipcode_relation WHERE zip_code ='$zip'";
		if(mysql_num_rows(mysql_query($sql))>0){
			$result = mysql_fetch_array(mysql_query($sql));
			//Remove Letters from zone
			$letters = array('a', 'b');
			$values['cold_zone'] = str_replace($letters, '', $result['zone']);
		} else {
		// No zone found
			$values['cold_zone'] = "n/a";
		}
			
		echo json_encode($values);
		exit();
}

add_action('wp_ajax_get_cold_zone', 'get_cold_zone');
add_action('wp_ajax_nopriv_get_cold_zone', 'get_cold_zone');

add_filter( 'template_include', 'my_callback' );
 
function my_callback( $original_template ) {
  if ( isset($_GET['s']) ) {
    return locate_template( 'search.php' );
  } else {
    return $original_template;
  }
}

/******** Get Geo Coordinates from IP address ******/
/** 
	@string -  returns Zone, and Zip Code as an Array()
	@variable - $zipcode If non-logged in user updated zip code, use this
*/
/*
function getGeoCoordinates(){
	$ip = $_SERVER['REMOTE_ADDR'];

	// [todo] - nonce
	$transient_name = 'iploc_' . str_replace( '.', '_', $ip );
	$location = monrovia_get_cache( $transient_name );
		
	$coords = array(
		'lat' => '',
		'lon' => ''
	);	
	
	if ( false === $location ) {		
		$data = wp_remote_get( sprintf( "http://ipinfo.io/%s", esc_attr( $ip ) ) );

		if ( ! is_wp_error( $data ) ) {
			preg_match( '/"loc": "([^"]+)"/msU', $data['body'], $matches );
			if ( ! empty( $matches[1] ) ) {
				$location = $matches[1];
				$coordinates = explode( ',', $location );
				if ( count( $coordinates ) > 1 ) {
					monrovia_set_cache( $transient_name, $location, 60 * 60 * 24 );

					$coords = array(
						'lat' => $coordinates[0],
						'lon' => $coordinates[1]
					);
				}
			}
		}
	}
		echo json_encode($coords);
		exit();
}
*/

/******** Get Geo Coordinates from IP address ******/
/** 
	@string -  returns Lat and Long

*/

function getGeoCoordinates(){

        $ip = $_SERVER['REMOTE_ADDR'];
		$details = json_decode(file_get_contents("http://ipinfo.io/{$ip}"));
		$coordinates = explode(",", $details->loc);
		$coords['lat'] = $coordinates[0];
		$coords['long'] = $coordinates[1];
		
		echo json_encode($coords);
		exit();
}
add_action('wp_ajax_getGeoCoordinates', 'getGeoCoordinates');
add_action('wp_ajax_nopriv_getGeoCoordinates', 'getGeoCoordinates');

add_action('wp_head','ajaxurl');
function ajaxurl() {
?>
<script type="text/javascript">
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
<?php
}

/******** Shop Plant Availability - Magento API Call ******/
/** 
	Used for calling the Magento API
	@string -  Boolean, returns true or false
	@variable - $plant Item Number
*/

function plantAvailibilityShop($plantID = "", $inStockOnly = false) {
    $is_available = false;
    $result = array();
	
	$api_user = get_option( 'vmwpmb_magento_api_user' );
	$api_key = get_option( 'vmwpmb_magento_api_key' );
	$magento_url = get_option( 'vmwpmb_magento_site_url', '' );
    
    if (false === $is_available) {
        try {
            //$proxy = new SoapClient('http://shop.monrovia.com/api/soap/?wsdl');                                                                                                          
            //$proxy = new SoapClient('http://local.monrovia.net/api/soap/?wsdl');            
            // FOR DEBUG ONLY
            //$proxy->__setCookie('XDEBUG_SESSION', 'netbeans-xdebug');

            // Connect
            $proxy = new SoapClient($magento_url.'/api/soap/?wsdl');                                                                                                                                                                  
            // Login
            $sessionId = $proxy->login($api_user, $api_key);
            // Get stock info                                                                                                                                                                                                                       
            $result = $proxy->call($sessionId, 'product_stock.list', array($plantID, $inStockOnly));
            //Return true if product is in stock                                                                                                                                                                                                    
            if (isset($result[0]['is_in_stock']) && $result[0]['is_in_stock'] == 1) {
                $is_available = true;
            }
        } catch (Exception $e) {
            echo $e->getmessage();
        }
    }
    return $result;
}

/******** Plant Availability - Magento API Call ******/
/** 
	@string -  Boolean, returns true or false
	@variable - $plant Item Number
*/

function plantAvailibility( $plantID ){
	// Get the same API creds as the WP_Magento Bridege Plugin
	$api_user = get_option( 'vmwpmb_magento_api_user' );
	$api_key = get_option( 'vmwpmb_magento_api_key' );
	$magento_url = get_option( 'vmwpmb_magento_site_url', '' );
	
	//$proxy = new SoapClient('http://shop.monrovia.com/api/soap/?wsdl');
	//$sessionId = $proxy->login('wpsync', 'PoMzTMeP72o6VkT');
	
	$proxy = new SoapClient($magento_url.'/api/soap/?wsdl');
	$sessionId = $proxy->login($api_user, $api_key);

	// Get stock info
	$stock = $proxy->call($sessionId, 'product_stock.list', $plantID );
	//print_r($stock);
	//Return true if product is in stock
	if(isset($stock[0]['is_in_stock']) && $stock[0]['is_in_stock'] == 1){
		$plant['availibility'] = 1;
		$plant['product_id'] = $stock[0]['product_id'];
		$plant['url'] = $stock[0]['url_path'];
		return $plant;
	} else {
		$plant['availibility'] = 0;
		return $plant;
	}
	
}

/******** Plant For Sale - Query Shop Availibility Table ******/
/** 
	@returns -  array() or false if empty
	@variable - $plant Item Number
*/


function isForSale( $item ){
	
	$data = false;
	$magento_url = get_option( 'vmwpmb_magento_site_url', '' );
	$_plant_type_not_sale = get_option( 'monrovia_plant_type_not_sale', '' );
	if($item){
		$sql = "SELECT * FROM shop_plant_availibility WHERE item_number = '$item' LIMIT 1";
		$sql_plant_type = "SELECT 
			DISTINCT 1 AS relevancy,
			1 AS relevancy_metaphone,
			plants.id,
			plants.item_number,
			plants.common_name,
			plant_type_plants.type_id
			FROM plants 
			INNER JOIN plant_type_plants 
			ON plants.id=plant_type_plants.plant_id 
			WHERE is_active IN ('1') AND plants.item_number = ".$item."
			ORDER BY relevancy DESC, relevancy_metaphone DESC, common_name ASC";
		if(mysql_num_rows(mysql_query($sql))> 0){
			$plant_data = mysql_fetch_array(mysql_query($sql));
			//Only display if it aactually has inventory
			if (mysql_num_rows(mysql_query($sql_plant_type))> 0) {
				$plant_type = mysql_query($sql_plant_type);
				$_plant_type_dis = explode(",", $_plant_type_not_sale);
				while ($row = mysql_fetch_array($plant_type, MYSQL_ASSOC)) {
					if (in_array($row['type_id'], $_plant_type_dis)) {
						$data = false;
						return $data;
					}
				}
				
			}
			if($plant_data['quantity'] > 0){
				$data = $magento_url . $plant_data['url']; // The Buy Now Url
			}
		} 
	}
	
	return $data;
}

/******** Plant For Sale - Populate Shop Availibility Table ******/
/** 
	Runs Once per day via Cron
	@returns -  array()
	@variable - $plant Item Number
*/

function updateShopTable(){

    // QUERY ALL PLANTS IN STOCK
    $plants = plantAvailibilityShop('*', true);
	
	// Truncate the Current Incarnation of the table
	mysql_query("TRUNCATE TABLE shop_plant_availibility");
	//Insert into the Database
	foreach($plants as $plant){
		
		//If Leading number is '4'
		if(strpos($plant['sku'], '4') === 0){
			//Take the first 5 numbers
			$item = substr($plant['sku'], 0, 5);
		} else {
			//Convert SKU to item number if longer than 4 digits
			if(strlen($plant['sku'])>4){
				//Remove 1st number, then use the next 4
				$item = substr($plant['sku'], 1, 4);
			} else {
				$item = $plant['sku'];
			}
		}
		$sku = $plant['sku'];
		$url = $plant['url_path'];
		$quantity = $plant['qty'];
		
		mysql_query("INSERT INTO shop_plant_availibility ( item_number, url, quantity, SKU ) VALUES ( '$item', '$url', '$quantity', '$sku')");
	}
	
	//wp_mail( 'brettex@hotmail.com', 'Plant Table Updated', 'Another CRON Job Success' );
}

/*************** Get Plant Data from Plant ID ***************/
/**
	@variable $plantID - Set to Null if not availiable
	@variable $plantItemNum - Fallback to Plant Item Number 
	@return an Array of Plant Data - Image ID, Plant Title, Item Number, Primary Attribute
*/

function getPlantData($plantID='', $plantItemNum=''){
	$transient_name = ( $plantID != '' ) ? sprintf( 'plant_data_id_%s', $plantID ) : sprintf( 'plant_data_in_%s', $plantItemNum ); 

	$plant_data_content = monrovia_get_cache( $transient_name );
	if ( false === $plant_data_content ) :	

	// Set the SQL statement
	$sql = "SELECT plants.id, plants.common_name, plants.botanical_name, plants.item_number, plants.primary_attribute, plants.is_new, plants.cold_zone_high, plants.cold_zone_low, plants.flowering_time, plants.flower_color_id, list_flower_color.name
			FROM plants
			INNER JOIN list_flower_color
			ON plants.flower_color_id=list_flower_color.id";
			if($plantID != ''){
				$sql .= " WHERE plants.id = '$plantID'";
			} else {
				$sql .= " WHERE plants.item_number = '$plantItemNum'";
			}
	//If the count field already exists
	if(mysql_num_rows(mysql_query($sql))> 0){
		$plant_data = mysql_fetch_array(mysql_query($sql));
		$data['title'] = unescape_special_characters($plant_data['common_name']); // The Title
		$data['botanical'] = unescape_special_characters($plant_data['botanical_name']); // Botanical Name
		$data['item'] = $plant_data['item_number']; // Item #
		$data['pid'] = $plant_data['id']; //ID
		$data['attribute'] = $plant_data['primary_attribute']; // Primary Attribute
		$data['seo'] = generate_plant_seo_name($data['title']); //Seo URL name
		$data['new'] = $plant_data['is_new']; // Set to 1 if its new
		$data['zone-high'] = $plant_data['cold_zone_high'];
		$data['zone-low'] = $plant_data['cold_zone_low'];
		$data['time'] = $plant_data['flowering_time'];
		$data['color'] = $plant_data['name'];
		
	}
	$id = $data['pid'];
	
	// Set the SQL statement
	$sql = "SELECT id FROM plant_image_sets WHERE plant_id = '$id' AND (expiration_date>NOW() OR expiration_date='0000-00-00') AND is_active = '1' LIMIT 1";
	//If the count field already exists
	if(mysql_num_rows(mysql_query($sql))> 0){
		$imageID = mysql_fetch_array(mysql_query($sql));
		$data['image-id'] =  $imageID['id'];
	} else{
		$data['image-id'] = 'no-image';
	}
	
	$plant_data_content = $data;
	monrovia_set_cache( $transient_name, $plant_data_content, MONROVIA_PLANT_DATA_TRANSIENT_EXPIRE );

	endif;

	$data = $plant_data_content;
	
	return $data;
}

/*************** Get and Display Plants in the Spotlight ******************/
/**
	Mainlt Used for the Sidebar Regions
	@variable $number - Optional, number of plants to display
	@return - Returns HTML formatted list of plants	
*/

function displaySpotlightPlants($number=''){
	
	// Query the meta table for All spot light plants
	$sql = "SELECT meta_value FROM wp_postmeta WHERE meta_key = 'featured_plants_plant_id_number'";
	$query = mysql_query($sql);
	// Set the Limit
	if($number == ''){ $count = mysql_num_rows($query); } else { $count = $number; }

	$i=1;
	while($plant = mysql_fetch_array($query)){
		if($i <= $count){
		//Get Plant Data
		$data = getPlantData('',$plant['meta_value']); ?>
        <div class="spot-plant clear">
        <a href="<?php echo site_url().'/plant-catalog/plants/'.$data['pid'].'/'.$data['seo']; ?>">
        	<img src="<?php echo site_url().'/wp-content/uploads/plants/search_results/'.$data['image-id'].'.jpg'?>"  />
        </a>
        	<div class="left item-num hideMobile">Item #<?php echo $data['item']; ?><br /><?php echo $data['attribute']; ?></div>
        	<div class="clearfix">
        	<a href="<?php echo site_url().'/plant-catalog/plants/'.$data['pid'].'/'.$data['seo']; ?>" title="<?php echo $data['title']; ?>"><?php echo $data['title']; ?></a>
           		<div class='botanical hideMobile'><?php echo $data['botanical']; ?></div>
        	</div>
        </div>
		<?php 
		}
	}
}

/*************** Get Number of Plants in a Design Inspiration Category *****/
/**
	@variable $taxonomy the taxonomy slug
	@variable $count Boolean - whether to return the count
	@return an array of all the plants or Count of Array
	Deduped the array in case the same plant exists in more
	than one Garden Style
	
**/

function getDesignPlants($taxonomy="", $count=""){
	
	global $wpdb;
	$plantArray= array();
	$args = array('post_per_page' => -1, 'post_type' => 'garden', 'garden_style' => "$taxonomy" );
	$query = get_posts( $args );
	$i=0;
	foreach ( $query as $post ) {
  		setup_postdata( $post );
		
		$meta = get_post_meta( $post->ID, 'plants_in_garden_plant_item_number', false );
		//If meta data is not empty
		if(!empty($meta)){
			foreach($meta as $plant){
				$plantArray[] = $plant;
			}
		}
		$i++;
	}
	
	//Dedupe the Array
	$plantArray = array_unique( $plantArray, SORT_NUMERIC );
	//Reset the Array Keys
	$plantArray = array_values($plantArray);
	if($count == true){
		$gardens['plants'] = count($plantArray);
		$gardens['gardens'] = $i;
	} else {
		$gardens = $plantArray;
	}
	return $gardens;
	
}

/*************** Get Garden Style link From Child Page *****/
/**
	@variable $taxonomy the taxonomy slug
	@return an Array containing the URL string and Title
	
**/

function getGardenParent($taxonomy){
	
	global $wpdb;
	$parent = array();
	$args = array('post_per_page' => 1, 'post_type' => 'design_style', 'garden_style' => "$taxonomy" );
	$query = get_posts( $args );
	
	foreach ( $query as $post ) {
  		setup_postdata( $post );
		$parent['link'] = get_permalink($post->ID);
		$parent['title'] = get_the_title($post->ID);
		$parent['ID'] = $post->ID;
	}
	
	return $parent;
}


/*************** Get Previous/Next Links for Individual Garden Pages *****/
/**
	@variable $taxonomy the taxonomy term
	@variable $currentPostID the post ID of the current page
	@return an Array containing the URLs for next and prev
	The Wordpress get_adjacent_posts() doesent allow ordering
	by menu order nor does it allow infinite links (If at last post,
	next link would go back to the first post)
	Created own function to achieve this
	
**/

function getGardenLinks($taxonomy, $currentPostID){
	// Get posts in same custom taxonomy
	$args = array(
   		'posts_per_page'  => -1,
   		'orderby'         => 'menu_order',
   		'order'           => 'ASC',
   		'post_type'       => 'garden',
   		'garden_style' => "$taxonomy"
	); 
	$posts = get_posts( $args );

	// Store ids of posts in an array
	$ids = array();
	foreach ($posts as $thepost) {
   		$ids[] = $thepost->ID;
	}

	// Get the permalink and store it in an array       
	$postIndex = array_search($currentPostID, $ids);
	
	//If its the first Post, Set the Previous
	//Link to the last post
	if($postIndex == 0){
		$previd = end($ids);
	} else {
		$previd = $ids[$postIndex-1];
	}
	//If its the Last post, set the Next Link
	// to the first post
	if($postIndex == (count($ids)-1)){
		$nextid = $ids[0];
	} else {
		$nextid = $ids[$postIndex+1];
	}
	//Set the link URLs
   	$links['prev'] = get_permalink($previd);
	$links['next'] = get_permalink($nextid);
	
	return $links;
}


/*************** Check to see if user has Plant in Favorites ***************/
/**
	@variable $plantID
	@return boolean
	Leave plantID empty to return an Favorites Object for
	The Favorites page
	@return $plant - Array contain all plants in a Favorite List
*/

function getUserWishlist($plantID =''){
	
	// Plant array
	$plant = array();
	
	//Onlt run if the user is logged in
	if( ! is_user_logged_in() ) {
		return false;
	}
	// Get current user info
	global $current_user;
    get_currentuserinfo();
	$wp_id = $current_user->ID; 
	//Convert to legacy ID, temporary
	//$legacy = mysql_fetch_array(mysql_query("SELECT legacy_user_id FROM user_legacy_id WHERE wp_user_id = '$wp_id'"));
	//$legacyID = $legacy['legacy_user_id'];
	
	//Get the wish list
	$wishlist = mysql_fetch_array(mysql_query("SELECT id FROM wish_lists WHERE user_id = '$wp_id'"));
	$wishID = $wishlist['id'];
	
	// Just check to see if a plant is in the Wish List or not
	if($plantID != ''){
		// Set the SQL statement
		$sql = "SELECT * FROM wish_list_items WHERE wish_list_id = '$wishID' AND plant_id = '$plantID'";
		//If the count field already exists
		if(mysql_num_rows(mysql_query($sql))> 0){
			$ret = true;
		} else{
			$ret = false;
		}
	} else {
		// Get all the Favorite Plants
		// Set the SQL statement
		$sql = "SELECT * FROM wish_list_items WHERE wish_list_id = '$wishID'";
		
		$query = mysql_query($sql);
		if(mysql_num_rows($query)> 0){
			$i=0;
			while($plants = mysql_fetch_array($query)){
				$plant[$i]['pid'] = $plants['plant_id'];
				$plant[$i]['notes'] = $plants['notes'];
				$plant[$i]['enable_send_mail'] = $plants['enable_send_mail'];
				$i++;
			}
		}
		
		$ret = $plant; 	
	}
			
	return $ret;
}

/******** Add/Remove User Plants to Favorites ******/
/** 
	@variable $action -  action, either Delete, Add, or Update
	@variable $plantID - ID of plant to add
	@variable $plantItem - Item number of the plant
	@variable- $notes The notes created, if present
*/

function updateFavorites(){
	
	//Get POST Variables
	$action = $_POST['ax'];
	$plantID = $_POST['pid'];
	$value = $_POST['value'];
	if(isset($_POST['itemNum'])) $plantItem = $_POST['itemNum'];
	if(isset($_POST['note'])){ $notes = $_POST['note']; } else { $notes = ''; } 
	
	// Get current user info
	global $current_user;
    get_currentuserinfo();
	$wp_id = $current_user->ID; 
	//Convert to legacy ID, temporary
	//$legacy = mysql_fetch_array(mysql_query("SELECT legacy_user_id FROM user_legacy_id WHERE wp_user_id = '$wp_id'"));
	//$legacyID = $legacy['legacy_user_id'];
	
	// Add to Favorites
	if($action == 'add'){
		//Get the wish list ID, if present, or else create a new wish list
		$wish = mysql_query("SELECT id FROM wish_lists WHERE user_id = '$wp_id'");
		if(mysql_num_rows($wish)> 0){
			//Get the Wish list ID
			$wishlist = mysql_fetch_array($wish);
			$wishID = $wishlist['id'];
			mysql_query("INSERT INTO wish_list_items (legacy_id, plant_item_number, notes, wish_list_id, plant_id ) VALUES ( '0', '$plantItem', '$notes', '$wishID', '$plantID' )");
		} else {
			//Create new Wish list
			mysql_query("INSERT INTO wish_lists ( id, wish_list_name, user_id, date_last_accessed ) VALUES ( NULL, NULL, '$wp_id', NULL )");
			mysql_query("INSERT INTO wish_list_items (legacy_id, plant_item_number, notes, wish_list_id, plant_id ) VALUES ( '0', '$plantItem', '$notes',  LAST_INSERT_ID(), '$plantID' )");
		}
		$favorite['result'] = "Added";
	}
	//Update the Favorites (Only the notes field)
	if($action == 'update'){
		//Get the wish list ID, if present, or else create a new wish list
		$wish = mysql_query("SELECT id FROM wish_lists WHERE user_id = '$wp_id'");
		if(mysql_num_rows($wish)> 0){
			//Get the Wish list ID
			$wishlist = mysql_fetch_array($wish);
			$wishID = $wishlist['id'];
			mysql_query("UPDATE wish_list_items SET notes='$notes' WHERE wish_list_id = '$wishID' AND plant_id = '$plantID'");
		}
		$favorite['result'] = "Updated";
		$favorite['wish'] = $wishID;
		$favorite['pid'] = $plantID;
		$favorite['note'] = $notes;
	}
	
	if($action == "remove"){
		// Delete Favorite from Table
		$wishlist = mysql_fetch_array(mysql_query("SELECT id FROM wish_lists WHERE user_id = '$wp_id'"));
		$wishID = $wishlist['id'];
		mysql_query("DELETE FROM wish_list_items WHERE plant_id = '$plantID' AND wish_list_id = '$wishID'");
		$favorite['result'] = "Removed";
	}

	if($action == 'enable'){
		$wish = mysql_query("SELECT id FROM wish_lists WHERE user_id = '$wp_id'");
		if(mysql_num_rows($wish)> 0){
			//Get the Wish list ID
			$wishlist = mysql_fetch_array($wish);
			$wishID = $wishlist['id'];
			mysql_query("UPDATE wish_list_items SET enable_send_mail= {$value} WHERE wish_list_id = '$wishID' AND plant_id = '$plantID'");
		}
		$favorite['result'] = "Enabled";
		$favorite['wish'] = $wishID;
		$favorite['pid'] = $plantID;
		$favorite['enable_send_mail'] = $value;
	}
		
	echo json_encode($favorite);
	exit();
}

add_action('wp_ajax_updateFavorites', 'updateFavorites');
add_action('wp_ajax_nopriv_updateFavorites', 'updateFavorites');


/******** Check to see if a Plant is availiable Locally ******/
/** 
	@variable $zipcode - The users zipcode
	@variable $plantItem - Item number of the plant
*/

function checkLocally(){
	
	//Get POST Variables
	$zipcode = false;
	$plantItem = $_POST['itemNum'];
	if(isset($_POST['zip'])) $zipcode = $_POST['zip'];
	
	if($zipcode){
		// Call the AZLink API
		
		 set_time_limit(60); // ALLOW UP TO A MINUTE
            
         $url = 'http://azlink.monrovia.com/tpg.php?zip='.$zipcode.'&filter_type=&item_number='.$plantItem.'&range=100&gc_type=&max_results=100';
         $xml = get_url($url,60);
         $xml_doc = XML_unserialize($xml);
		 $garden_centers = '';
		 if(isset($xml_doc['response']['results']['result'])){
         	$garden_centers = $xml_doc['response']['results']['result'];
		 }
		 
		 // If no resutls, found create an empty array
		 if(gettype($garden_centers)=='string'&&trim($garden_centers)=='') $garden_centers = array();
            
         // IF ONLY ONE RESULT, WE NEED TO MAKE SURE IT'S IN ARRAY FORM, WHICH XML_unserialize BREAKS
         if(is_array($garden_centers)&&count($garden_centers)&&!isset($garden_centers[0])){
                $garden_centers = array($garden_centers);
		 }
		 
		 // If the Array is not empy, then we have positive results!
		 if(count($garden_centers)>0){
			 $results['result'] = "true";
		 } else {
         	$results['result'] = "false";
		 }
		
	} else {
		//No zipcode provided, dont make API call
		$results['result'] = "false";
	}
	
	echo json_encode($results);
	exit();
}

add_action('wp_ajax_checkLocally', 'checkLocally');
add_action('wp_ajax_nopriv_checkLocally', 'checkLocally');

/*************** iContact Update User Subscription ***************/
/**
	@variable $user_id
	@returns no variable
*/

function checkSubscription(){
	
	$prevValue = $_POST['cimy_uef_NEWSLETTER_1_prev_value']; // YES or NO
	$currentValue = $_POST['cimy_uef_NEWSLETTER']; // 1 or none
	$fname = $_POST['first_name']; //First Name
	$lname = $_POST['last_name']; // Last Name
	$oldZip - $_POST['cimy_uef_ZIP_CODE_2_prev_value'];
	$zipcode = $_POST['cimy_uef_ZIP_CODE']; //Zip Code
	$email = $_POST['email'];
	$coldzone = 0;
	$sql = "SELECT * FROM monrovia_zones_usda_zipcode_relation WHERE zip_code ='$zipcode'";
		if(mysql_num_rows(mysql_query($sql))>0){
			$result = mysql_fetch_array(mysql_query($sql));
			//Remove Letters from zone
			$letters = array('a', 'b');
			$coldzone = str_replace($letters, '', $result['zone']);
		}	
	
	// Action is User Update
	if(isset($_POST['cimy_uef_NEWSLETTER_1_prev_value'])){
		if($prevValue == 'YES' && $currentValue  != '1'){
			//User was Subscribed, but unsubscribed
			$result = IContactUnsubscribe('589567', '7vXdoHq37vaIJNhT9Vsf7e6ZCEsqagpA', 'pSparkla', 'Savvy123^', $email, '101121', $result_str);

		}elseif($prevValue == 'NO' && $currentValue  == '1'){
			//User was not subscribed, but Subscribed
			$contactID = IContactAddContact('589567', '7vXdoHq37vaIJNhT9Vsf7e6ZCEsqagpA', 'pSparkla', 'Savvy123^', $email, $fname, $lname, $zipcode, $coldzone, $result_str);
			$result = IContactSubscribe('589567', '7vXdoHq37vaIJNhT9Vsf7e6ZCEsqagpA', 'pSparkla', 'Savvy123^', $email, '101121', $result_str);
			
		}
	} else { // Action is User Register
		if(isset($_POST['cimy_uef_NEWSLETTER']) && $_POST['cimy_uef_NEWSLETTER'] == '1'){
			// If User Checked Subscribe on Register Page
		}
	}
	// Run the getZone script if User updated their Zip Code within profile
	if($oldZip != $zipcode){
		//IF user updaets zip code in profile, run getZone again
		// Cant get this to work currently
        }
	
	if(isset($_POST['pass1']) && $_POST['pass1'] != ''){
		$new_pass = $_POST['pass1'];
		//Get User Object
		$user_id = $_POST['user_id'];
		//Encrpyt the Password
		$password = str_rot13(base64_encode($new_pass));
	 	//Check to see if already in temp table
	   $sql = mysql_query("SELECT * FROM temp_password_store WHERE wp_user_id = '$user_id'"); 
	   if(mysql_num_rows($sql)> 0){
			mysql_query("UPDATE temp_password_store SET password = '$password' WHERE wp_user_id = '$user_id'");
	   } else {
	 		mysql_query("INSERT INTO temp_password_store ( wp_user_id, email, password ) VALUES ( '$user_id', '$email', '$password' )"); 
	   }	
	}

}
add_action('personal_options_update', 'checkSubscription', 0); // Fire if User updating Own profile
add_action('edit_user_profile_update', 'checkSubscription', 0); // Fire if Admin is updating another Profile

/*
	Add to iContact
	Fire this Function on Registration Sign up to add user
	to the iContact mail list if checkbox is checked
	Also fired during Newsletter Sign-up form
	 
*/
function addSubscription(){
	// If not registration
	if(isset($_POST['newsletter']) && $_POST['newsletter'] == 'true'){
		$email = $_POST['email'];
		$zipcode = $_POST['zipcode'];
		$coldzone = $_POST['coldzone'];
		
		$contactID = IContactAddContact('589567', '7vXdoHq37vaIJNhT9Vsf7e6ZCEsqagpA', 'pSparkla', 'Savvy123^', $email, $fname, $lname, $zipcode, $coldzone, $result_str);
		$result = IContactSubscribe('589567', '7vXdoHq37vaIJNhT9Vsf7e6ZCEsqagpA', 'pSparkla', 'Savvy123^', $email, '101121', $result_str);
		
		//Set up Success of Fail messages
		$message['contactID'] = $contactID;
		$message['string'] = $result_str;
		$message['result'] = $result;
		if($result){
			$message['result'] = "subscribed";
		}
		echo json_encode($message);
		exit();

	} else {
		//Its registration
		$value = $_POST['cimy_uef_NEWSLETTER']; // 1 or none
		$email = $_POST['user_email'];
		$fname = $_POST['cimy_uef_wp_FIRSTNAME'];
		$lname = $_POST['cimy_uef_wp_LASTNAME'];
		$zipcode = $_POST['cimy_uef_ZIP_CODE'];	
		$coldzone = 0;
		$sql = "SELECT * FROM monrovia_zones_usda_zipcode_relation WHERE zip_code ='$zipcode'";
			if(mysql_num_rows(mysql_query($sql))>0){
				$result = mysql_fetch_array(mysql_query($sql));
				//Remove Letters from zone
				$letters = array('a', 'b');
				$coldzone = str_replace($letters, '', $result['zone']);
			}	 
		
		// If user checked Yes on registration, then sign 'em up! 
		if($value == 1){
			$contactID = IContactAddContact('589567', '7vXdoHq37vaIJNhT9Vsf7e6ZCEsqagpA', 'pSparkla', 'Savvy123^', $email, $fname, $lname, $zipcode, $coldzone, $result_str);
			$result = IContactSubscribe('589567', '7vXdoHq37vaIJNhT9Vsf7e6ZCEsqagpA', 'pSparkla', 'Savvy123^', $email, '101121', $result_str);
		}
	}
 }
add_action('user_register', 'addSubscription', 0); //Fire when new User is created
// Allow function to be used via ajax
add_action('wp_ajax_addSubscription', 'addSubscription');
add_action('wp_ajax_nopriv_addSubscription', 'addSubscription');



/*
	Store Password in a Temp Table
	So it can be decrypted later for
	Magento import
*/
function storePassword( $user_id ){
	
	$email = $_POST['user_email'];
	$password = $_POST['pass1'];
	
	//Encrypt Password
	$password = str_rot13(base64_encode($password));

	//Store password in Temp Table
	mysql_query("INSERT INTO temp_password_store ( wp_user_id, email, password ) VALUES ( '$user_id', '$email', '$password' )"); 
	
 }
add_action('user_register', 'storePassword', 0); //Fire when new User is created

/*** Update the Temp Table to store passwords when user Resets their password **/
function storePasswordReset() {
		//Get the plain password
		$new_pass = $_POST['pass1'];
		//Get User Object
		$user = get_user_by( 'login', $_POST['login'] );
		//Encrpyt the Password
		$password = str_rot13(base64_encode($new_pass));
		//User ID
		$user_id = $user->ID;
		$email = $user->user_email;
       //Check to see if already in temp table
	   $sql = mysql_query("SELECT * FROM temp_password_store WHERE wp_user_id = '$user_id'"); 
	   if(mysql_num_rows($sql)> 0){
			mysql_query("UPDATE temp_password_store SET password = '$password' WHERE wp_user_id = '$user_id'");
	   } else {
	 		mysql_query("INSERT INTO temp_password_store ( wp_user_id, email, password ) VALUES ( '$user_id', '$email', '$password' )"); 
	   }
}
add_action( 'password_reset', 'storePasswordReset', 0);

/******** Get the a Plant Collection Based on the COLD ZONE of the user ******/
/** 
	@string -  returns Zone, and Zip Code as an Array()
	@variable - $zipcode If non-logged in user updated zip code, use this
*/
function getPlantCollection(){
		
		//POST DATA
		$coldzone = $_POST['cold_zone'];
		$zip = $_POST['zip'];
		$variable = $_POST['query'];
		$column = $_POST['column'];
		$number = $_POST['number'];
			
		//Set up an array to store the return values
		$values = Array();
		
		if($coldzone == false){
			$sql = "SELECT * FROM monrovia_zones_usda_zipcode_relation m
			LEFT JOIN monrovia_zipcodes z
			ON m.zip_code = z.zip_code
			WHERE m.zip_code ='$zip'";
			if(mysql_num_rows(mysql_query($sql))>0){
				$result = mysql_fetch_array(mysql_query($sql));
				//Remove Letters from zone
				$letters = array('a', 'b');
				$values['cold_zone'] = str_replace($letters, '', $result['zone']);
				$values['city'] = $result['city'];
				$values['state'] = $result['state'];
			} else {
			// No zone found
				$values['cold_zone'] = 0;
			}
			$coldzone = $values['cold_zone'];
		}
		
		$values['zipcode'] = $zip;
		require_once('includes/classes/class_plant.php');		
		require_once('includes/classes/class_search_plant.php');
		$search_plants = new search_plant('','id,item_number,common_name,botanical_name,is_new',false);
		$search_plants->order_by = 'common_name ASC';
		$search_plants->results_per_page = $number;
		$search_plants->criteria['cold_zone_low'] = $coldzone;
		$search_plants->criteria['cold_zone_high'] = $coldzone;
		$search_plants->criteria[$column] = $variable;
		$search_plants->search(false);
		ob_start();
		$search_plants->output_results_plant_search();
		$html = ob_get_contents();
		//Clean up HTML
		$html = preg_replace('~>\s+<~', '><', $html);
		$values['html'] = str_replace('\"','\'',$html);
		
		ob_end_clean();
			
		echo json_encode($values);
		exit();
}

add_action('wp_ajax_getPlantCollection', 'getPlantCollection');
add_action('wp_ajax_nopriv_getPlantCollection', 'getPlantCollection');




/*************************************************************/
#               EXTENDED RELEVANSSI SEARCH
/*************************************************************/

/* Group results by Content Type */
//add_filter('relevanssi_hits_filter', 'separate_result_types');
function separate_result_types($hits) {
    $types = array();
	$types['design_style'] = array();
	$types['plant_savvy_template'] = array(); 
	$types['spokesperson'] = array(); 
	$types['plant_collection'] = array(); 
	$types['collection'] = array(); 
	$types['youtube_video'] = array(); 
	$types['page'] = array();
	$types['press_release'] = array(); 
	$types['newsletter_archive'] = array(); 
	$types['post'] = array();
 
    // Split the post types in array $types
    if (!empty($hits)) {
        foreach ($hits[0] as $hit) {
            if (!is_array($types[$hit->post_type])) $types[$hit->post_type] = array();                        
            array_push($types[$hit->post_type], $hit);
        }
    }
 
    // Merge back to $hits in the desired order
    $hits[0] = array_merge($types['design_style'], $types['plant_savvy_template'], $types['newsletter_archive'], $types['spokesperson'], $types['plant_collection'], $types['collection'], $types['youtube_video'], $types['page'], $types['press_release'], $types['post']);
    return $hits;
}

/* Add meta data to the excerpts */
add_filter('relevanssi_excerpt_content', 'add_meta_data_search', 8, 3);
function add_meta_data_search($content, $post, $query) {
	//The date
	//$date = 'Date: '.get_the_date('m j Y', $post->ID);
	//$content = $date.$content;
	
	return $content;
}

/* Add meta data to the excerpts */
add_filter('relevanssi_pre_excerpt_content', 'pre_meta_data_search', 7, 3);
function pre_meta_data_search($content, $post, $query){
	//The date
	//$date = 'Test me';
	//$content = $date.$content;
	
	return $content;
}

/**********  AUTOCOMPLETE ****************/
//add_filter( 'search_autocomplete_modify_results', 'saModifyResults' );

function saModifyResults( $results ) {
  $newResults = array();
  foreach( $results as $result ) {
	  
	  //Clean up titles
	  $remove = array('&#8211;', '(COLD)', '(WARM)');
	  $title =  str_replace($remove, '', $result['title']);
	  if(strlen($title)> 40) $title = substr($title, 0, 37).'...';
    $newResults[] = array(
      'title' => $title,
      'url' => $result['url']
    );
  }
  return $newResults;
}

/*** Function to take Post Type machine name and convert to 
	 a User Friendly name for search results 
	 
***/

function convertPostType($post_type, $postID){
	// Set default
	$label = 'Article';
	switch($post_type){
		case 'plant_savvy_template':
			$label = 'Plant Savvy Newsletter';
			break;
		case 'newsletter_achive':
			$label = 'Plant Savvy Newsletter';
			break;
		case 'design_style':
			$label = 'Design Style';
			break;
		case 'press_release':
			$label = 'Press Release';
			break;
		case 'page':
			$label =  get_the_title( wp_get_post_parent_id( $postID ) );
			break;
		case 'spokesperson':
			$label = "Plant Collection";
			break;
		case 'plant_collection':
			$label = "Plant Collection";
			break;
		case 'collection':
			$label = "Plant Collection";
			break;
		case 'youtube_video':
			$label = "Videos";
			break;	
	}
	return $label;
}

/***************  Add Query Variable to the URL **************/

function add_query_vars($aVars) {
$aVars[] = "profile";
$aVars[] = "pid";
$aVars[] = "name";
$aVars[] = "plantname";

return $aVars;
}
 
// hook add_query_vars function into query_vars
add_filter('init', 'add_query_vars');

add_action('init', 'rewrite_urls');

function rewrite_urls(){
	
//For Plant Detail Pages
add_rewrite_tag('%pid%','([^&]+)');
add_rewrite_tag('%plantname%','([^&]+)');
//For Landscape Architect Profile Pages
add_rewrite_tag('%profile%','([^&]+)');

}

function add_rewrite_rulers() {
	
add_rewrite_rule('^plant-catalog/plants/([^/]*)/([^/]*)/?','index.php?page_id=1149&pid=$matches[1]&plantname=$matches[2]','top');
add_rewrite_rule('^landscape-architects/profiles/([^/]*)/?','index.php?page_id=1134&profile=$matches[1]','top');
//Ensure the $wp_rewrite global is loaded
//global $wp_rewrite;
//Call flush_rules() as a method of the $wp_rewrite object
//$wp_rewrite->flush_rules( true );

}
add_action('init', 'add_rewrite_rulers');


 

// Custom Excerpts
function monrovia_short_excerpt($length) // Create 10 Word Callback for Index page Excerpts, call using monrovia_wp_excerpt('monrovia_short_excerpt');
{
    return 10;
}

// Create 40 Word Callback for Custom Post Excerpts, call using monrovia_wp_excerpt('monrovia_long_excerpt');
function monrovia_long_excerpt($length)
{
    return 20;
}

// Create the Custom Excerpts callback
function monrovia_wp_excerpt($length_callback = '', $more_callback = '')
{
    global $post;
    if (function_exists($length_callback)) {
        add_filter('excerpt_length', $length_callback);
    }
    if (function_exists($more_callback)) {
        add_filter('excerpt_more', $more_callback);
    }
    $output = get_the_excerpt();
    $output = apply_filters('wptexturize', $output);
    $output = apply_filters('convert_chars', $output);
    echo $output;
}


add_filter( 'wp_page_menu', 'change_page_menu_classes', 0 );

// Custom View Article link to Post
function html5_blank_view_article($more)
{
    global $post;
    //return '... <a class="view-article" href="' . get_permalink($post->ID) . '">' . __('View Article', 'monrovia') . '</a>';
	return '...';
}

// Remove Admin bar
function remove_admin_bar()
{
    return false;
}

// Remove 'text/css' from our enqueued stylesheet
function html5_style_remove($tag)
{
    return preg_replace('~\s+type=["\'][^"\']++["\']~', '', $tag);
}

// Remove thumbnail width and height dimensions that prevent fluid images in the_thumbnail
function remove_thumbnail_dimensions( $html )
{
    $html = preg_replace('/(width|height)=\"\d*\"\s/', "", $html);
    return $html;
}

// Custom Gravatar in Settings > Discussion
function monroviagravatar ($avatar_defaults)
{
    $myavatar = get_template_directory_uri() . '/img/gravatar.jpg';
    $avatar_defaults[$myavatar] = "Custom Gravatar";
    return $avatar_defaults;
}

// Threaded Comments
function enable_threaded_comments()
{
    if (!is_admin()) {
        if (is_singular() AND comments_open() AND (get_option('thread_comments') == 1)) {
            wp_enqueue_script('comment-reply');
        }
    }
}

// Custom Comments Callback
function monroviacomments($comment, $args, $depth)
{
	$GLOBALS['comment'] = $comment;
	extract($args, EXTR_SKIP);
	
	if ( 'div' == $args['style'] ) {
		$tag = 'div';
		$add_below = 'comment';
	} else {
		$tag = 'li';
		$add_below = 'div-comment';
	}
?>
    <!-- heads up: starting < for the html tag (li or div) in the next line: -->
    <<?php echo $tag ?> <?php comment_class(empty( $args['has_children'] ) ? '' : 'parent') ?> id="comment-<?php comment_ID() ?>">
	<?php if ( 'div' != $args['style'] ) : ?>
	<div id="div-comment-<?php comment_ID() ?>" class="comment-body">
	<?php endif; ?>
	<div class="comment-author vcard">
	<?php if ($args['avatar_size'] != 0) echo get_avatar( $comment, $args['180'] ); ?>
	<?php printf(__('<cite class="fn">%s</cite> <span class="says">says:</span>'), get_comment_author_link()) ?>
	</div>
<?php if ($comment->comment_approved == '0') : ?>
	<em class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.') ?></em>
	<br />
<?php endif; ?>

	<div class="comment-meta commentmetadata"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>">
		<?php
			printf( __('%1$s at %2$s'), get_comment_date(),  get_comment_time()) ?></a><?php edit_comment_link(__('(Edit)'),'  ','' );
		?>
	</div>

	<?php comment_text() ?>

	<div class="reply">
	<?php comment_reply_link(array_merge( $args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
	</div>
	<?php if ( 'div' != $args['style'] ) : ?>
	</div>
	<?php endif; ?>
<?php }

/*------------------------------------*\
	Actions + Filters + ShortCodes
\*------------------------------------*/

//Add Support for Excerpts on Pages
add_post_type_support( 'page', 'excerpt' );

// Add Actions
add_action('init', 'monrovia_header_scripts'); // Add Custom Scripts to wp_head
add_action('wp_print_scripts', 'monrovia_conditional_scripts'); // Add Conditional Page Scripts
add_action('get_header', 'enable_threaded_comments'); // Enable Threaded Comments
add_action('wp_enqueue_scripts', 'monrovia_styles'); // Add Theme Stylesheet
add_action('init', 'register_monrovia_menu'); // Add HTML5 Blank Menu
//add_action('init', 'create_post_type_html5'); // Add our HTML5 Blank Custom Post Type
add_action('widgets_init', 'my_remove_recent_comments_style'); // Remove inline Recent Comment Styles from wp_head()
add_action('init', 'html5wp_pagination'); // Add our HTML5 Pagination

// Remove Actions
remove_action('wp_head', 'feed_links_extra', 3); // Display the links to the extra feeds such as category feeds
remove_action('wp_head', 'feed_links', 2); // Display the links to the general feeds: Post and Comment Feed
remove_action('wp_head', 'rsd_link'); // Display the link to the Really Simple Discovery service endpoint, EditURI link
remove_action('wp_head', 'wlwmanifest_link'); // Display the link to the Windows Live Writer manifest file.
remove_action('wp_head', 'index_rel_link'); // Index link
remove_action('wp_head', 'parent_post_rel_link', 10, 0); // Prev link
remove_action('wp_head', 'start_post_rel_link', 10, 0); // Start link
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0); // Display relational links for the posts adjacent to the current post.
remove_action('wp_head', 'wp_generator'); // Display the XHTML generator that is generated on the wp_head hook, WP version
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'rel_canonical');
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

// Add Filters

add_filter('avatar_defaults', 'monroviagravatar'); // Custom Gravatar in Settings > Discussion
add_filter('body_class', 'add_slug_to_body_class'); // Add slug to body class (Starkers build)
add_filter('widget_text', 'do_shortcode'); // Allow shortcodes in Dynamic Sidebar
add_filter('widget_text', 'shortcode_unautop'); // Remove <p> tags in Dynamic Sidebars (better!)
add_filter('wp_nav_menu_args', 'my_wp_nav_menu_args'); // Remove surrounding <div> from WP Navigation
// add_filter('nav_menu_css_class', 'my_css_attributes_filter', 100, 1); // Remove Navigation <li> injected classes (Commented out by default)
// add_filter('nav_menu_item_id', 'my_css_attributes_filter', 100, 1); // Remove Navigation <li> injected ID (Commented out by default)
// add_filter('page_css_class', 'my_css_attributes_filter', 100, 1); // Remove Navigation <li> Page ID's (Commented out by default)
add_filter('the_category', 'remove_category_rel_from_category_list'); // Remove invalid rel attribute
add_filter('the_excerpt', 'shortcode_unautop'); // Remove auto <p> tags in Excerpt (Manual Excerpts only)
add_filter('the_excerpt', 'do_shortcode'); // Allows Shortcodes to be executed in Excerpt (Manual Excerpts only)
add_filter('excerpt_more', 'html5_blank_view_article'); // Add 'View Article' button instead of [...] for Excerpts
//add_filter('show_admin_bar', 'remove_admin_bar'); // Remove Admin bar
add_filter('style_loader_tag', 'html5_style_remove'); // Remove 'text/css' from enqueued stylesheet
add_filter('post_thumbnail_html', 'remove_thumbnail_dimensions', 10); // Remove width and height dynamic attributes to thumbnails
add_filter('image_send_to_editor', 'remove_thumbnail_dimensions', 10); // Remove width and height dynamic attributes to post images

// Remove Filters
remove_filter('the_excerpt', 'wpautop'); // Remove <p> tags from Excerpt altogether

// Shortcodes
add_shortcode('html5_shortcode_demo', 'html5_shortcode_demo'); // You can place [html5_shortcode_demo] in Pages, Posts now.
add_shortcode('html5_shortcode_demo_2', 'html5_shortcode_demo_2'); // Place [html5_shortcode_demo_2] in Pages, Posts now.

// Shortcodes above would be nested like this -
// [html5_shortcode_demo] [html5_shortcode_demo_2] Here's the page title! [/html5_shortcode_demo_2] [/html5_shortcode_demo]

/*------------------------------------*\
	ShortCode Functions
\*------------------------------------*/

// Shortcode Demo with Nested Capability
function html5_shortcode_demo($atts, $content = null)
{
    return '<div class="shortcode-demo">' . do_shortcode($content) . '</div>'; // do_shortcode allows for nested Shortcodes
}

// Shortcode Demo with simple <h2> tag
function html5_shortcode_demo_2($atts, $content = null) // Demo Heading H2 shortcode, allows for nesting within above element. Fully expandable.
{
    return '<h2>' . $content . '</h2>';
}

function get_collection_abbreviation( $full_name ){
	$mappings = array(
		'Dan Hinkley'                     => 'danhinkley',
		'Distinctively Better'            => 'distinctivelybetter',
		'Distinctively Better Perennials' => 'distinctivelybetterperennials',
		'Edibles'                         => 'edibles',
		'Itoh Peonies'                    => 'itohpeonies',
		'Proven Winners'                  => 'provenwinners',
		'Succulents'                      => 'succulents'
	);
	return $mappings[$full_name];
}

function monrovia_get_plant_record( $plant_id ) {
	$transient_name = 'plant_record_' . $plant_id;
	$record = monrovia_get_cache( $transient_name );
	if ( false === $record ) :
		$record = new plant($plant_id);
		monrovia_set_cache( $transient_name, $record, MONROVIA_TRANSIENT_EXPIRE );
	endif;
	return $record;
}

function monrovia_use_caching() {
	return MONROVIA_USE_CACHING;
}

function monrovia_get_cache( $key ) {
	if ( ! monrovia_use_caching() ) {
		return false;
	}
	
	return get_transient( $key );
}

function monrovia_set_cache( $key, $value, $expire_time_seconds ) {
	if ( monrovia_use_caching() ) {
		set_transient( $key, $value, $expire_time_seconds );
	}
}

function monrovia_delete_cache( $key ) {
	if ( monrovia_use_caching() ) {
		delete_transient( $key);
	}
}


add_action( 'trashed_post', 'monrovia_delete_template_cache' );
add_action( 'untrashed_post', 'monrovia_delete_template_cache' );
add_action( 'save_post', 'monrovia_delete_template_cache' );

$template_transients = array(
	'job_listing'   => 'careers',
	'faq'           => 	array( 'faq', 'retailer_faq' ),
	'newsletter'    => 'newsletter', 
	'design_style'  => 'inspiration_gallery',
	'press_release' => 'press_release',
	'page'          => 'second_level',
	'youtube_video' => 'videos',
	'home_page'     => 'home_page',
	'facebook_contest' => array( 'promo_banner_home', 'promo_banner' ),
	'collection'    => 'single_collection_%d'
);

$term_transients = array(
	'job_location' => array('locations', 'careers' ),
	'faq_category' => 'faq'
);

$page_transients = array(
	'new-plants.php' => 'new_plants'
);

function monrovia_delete_template_cache() {
	global $template_transients, $page_transients;
	
	global $POST;
	$id = get_the_ID();
	
	foreach ( $template_transients as $template => $transient ) {
		if ( $template == get_post_type( $id ) ) {
			if ( is_array( $transient ) ) {
				foreach ( $transient as $trans ) {
					monrovia_delete_cache( $trans );
				}
			} else {
				$transient_name = ( false === strpos( $transient, '%d' ) ) ? $transient : sprintf( $transient, $id );
				monrovia_delete_cache( $transient_name );
			}
		}
	}
	
	$template_name = get_post_meta( $id, '_wp_page_template', true );

	foreach ( $page_transients as $page_template => $transient ) {
		if ( $page_template == $template_name ) {
			monrovia_delete_cache( $transient );
		}
	}
}

add_action( 'edit_term', 'monrovia_delete_term_cache' );
add_action( 'create_term', 'monrovia_delete_term_cache' );
add_action( 'delete_term', 'monrovia_delete_term_cache' );
function monrovia_delete_term_cache () {	
	global $term_transients;
	foreach ( $term_transients as $term => $transient ) {
		if (is_array($transient)) {
				foreach ($transient as $trans) {
					monrovia_delete_cache( $trans);
				}
				
			} else {
			monrovia_delete_cache( $transient );
			}
	}
}

function monrovia_clear_cache() {
	global $template_transients, $term_transients, $page_transients;

	foreach ( $template_transients as $template => $transient ) {
		if ( is_array( $transient ) ) {
			foreach ( $transient as $trans ) {
				monrovia_delete_cache( $trans );
			}
		} else {
			monrovia_delete_cache( $transient );
		}
	}
	
	foreach ( $term_transients as $term => $transient ) {
		if (is_array($transient)) {
			foreach ($transient as $trans) {
				monrovia_delete_cache( $trans);
			}
		} else {
			monrovia_delete_cache( $transient );
		}
	}
	
	$template_name = get_post_meta( $id, '_wp_page_template', true );

	foreach ( $page_transients as $page_template => $transient ) {
		monrovia_delete_cache( $transient );
	}
	
}

/** Strip out <p> tags, used on Magic Fields mostly **/	
function stripTag($string){
	$needles = array('<p>','</p>');
	$string = str_replace($needles, '', $string);
	
	return $string;	
}

function add_fields_for_mailchimp() {
	?>
     <input type="hidden" name="ZIP_CODE" value="" id="zipcode" />
     <input type="hidden" name="COLDZONE" value="" id="coldzone" />
	<?php
}

add_action( 'register_form', 'add_fields_for_mailchimp' );
	

function monrovia_username_or_email_login() {
		//if ( 'wp-login.php' != basename( $_SERVER['SCRIPT_NAME'] ) )
		//return;
	?><script type="text/javascript">
	// Form Label
	if ( document.getElementById('loginform') )
		document.getElementById('loginform').childNodes[1].childNodes[1].childNodes[0].nodeValue = '<?php echo esc_js( __( 'Username or Email', 'email-login' ) ); ?>';

	// Error Messages
	if ( document.getElementById('login_error') )
		document.getElementById('login_error').innerHTML = document.getElementById('login_error').innerHTML.replace( '<?php echo esc_js( __( 'username' ) ); ?>', '<?php echo esc_js( __( 'Username or Email' , 'email-login' ) ); ?>' );
	</script><?php
}
add_action( 'login_form', 'monrovia_username_or_email_login' );
remove_action( 'login_form', 'username_or_email_login' );


add_action( 'admin_init', 'monrovia_admin_init' );
add_action( 'admin_menu', 'monrovia_admin_menu' );
function monrovia_admin_init() {
	// Sections
	add_settings_section( 'monrovia-caching-section', 
		__( '' ),
		'monrovia_caching_callback',
		'monrovia_caching_settings'
	);
	
	add_settings_field( 'monrovia_clear_cache', 
		__( '' ), 
		'monrovia_hidden_field_callback', 
		'monrovia_caching_settings',
		'monrovia-caching-section',
		array(
			'field' => 'monrovia_clear_cache',
			'value' => 1
		)
	);
		
	register_setting( 'monrovia_caching_group', 'monrovia_clear_cache', 'monrovia_clear_cache_sanitize' );
}

function monrovia_admin_menu() {
	add_options_page( __( 'Caching', 'Monrovia' ),
		__( 'Caching', 'Monrovia' ), 
		'manage_options',
		'monrovia-caching', 
		'monrovia_caching_page' );
}

function monrovia_caching_callback() {
	
}

function monrovia_clear_cache_sanitize( $field ) {
	monrovia_clear_cache();
}

function monrovia_hidden_field_callback( $args ) {
	$field = $args['field'];
	$value = $args['value'];	
	printf( '<input type="hidden" name="%s" id="%s" value="%s" />', esc_html( $field ), esc_html( $field ), esc_html( $value ) );
}

function monrovia_caching_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'Monrovia' ) ); 
	}
?>
	<div class="wrap">
	<?php screen_icon(); ?>
 	<h2><?php _e( 'Transient Cache', 'Magento' ); ?></h2>
	
	<form method="post" action="options.php">
	<?php
	
			settings_fields( 'monrovia_caching_group' );
		//	do_settings_fields( 'monrovia_caching_group', 'vmwpmbr_wp2magento-section' );

			do_settings_sections( 'monrovia_caching_settings' );
			submit_button( 'Clear Cache' );
	?>
	</form>
</div>

<?php	
}

function generate_xml($full_synch) {
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_plant.php');
    //set_time_limit(3600); // ALLOW UP TO AN HOUR

    $xml_data = "";
    $xml_data .= "<?xml version=\"1.0\"?>\r\n";
    $xml_data .= "<DataSync>\r\n";
    // $_sql = "SELECT * FROM plants "
            // . "WHERE item_number <> '' "
            // . "AND release_status_id IN (1,2,3,4,6) "
            // . "AND (last_modified BETWEEN '2015-12-22 00:00:00' AND '2015-12-25 00:00:00') "
            // . "ORDER BY last_modified DESC "
            // . "LIMIT 10 OFFSET 45";
	$_sql = "SELECT id FROM plants WHERE synch_with_hort=1 AND item_number <> '' AND release_status_id IN (1,2,3,4,6) ORDER BY last_modified DESC LIMIT 5";
    
    $result = mysql_query($_sql);

//    $sync_arr = array('07613', '01398', '07301', '09957', '01398');
//    $result = mysql_query("SELECT id FROM plants WHERE "
//            . "1=1 "
//            // . "synch_with_hort=1 "
//            . "AND item_number <> '' "
//            . "AND item_number IN (".implode(',',$sync_arr).") "
//            . "AND release_status_id IN (1,2,3,4,6) ORDER BY last_modified DESC LIMIT 100");


//    if ($full_synch) {
//        $result = mysql_query("SELECT id FROM plants WHERE synch_with_hort=1 AND item_number <> '' AND release_status_id IN (1,2,3,4,6) ORDER BY last_modified DESC LIMIT 100");
//    } else {
//        $result = mysql_query("SELECT id FROM plants WHERE synch_with_hort=1 AND item_number <> '' AND release_status_id IN (1,2,3,4,6) ORDER BY last_modified DESC");
//    }

    $num_rows = mysql_num_rows($result);
    $id = array();
    if (count($num_rows) > 0) {

        //$id = mysql_result($result,0,'id');

        /*
          Need data from these tables
          -list_special_feature
          -list_problem_solution
          -list_landscape_use
          -plant_problem_solution_plants
          -plant_special_feature_plants
          -plant_landscape_use_plants

          IGNORE Butterflies and Hummingbirds in Special Features
          AVOID DUPLICATES for Special Features and Problem Solution (This ever happen?)

          "SELECT * FROM plant_special_feature_plants p LEFT JOIN list_special_feature l ON p.special_feature_id = l.id WHERE `plant_id` = '$id'";
          "SELECT * FROM plant_problem_solution_plants p LEFT JOIN list_problem_solution l ON p.problem_solution_id = l.id WHERE `plant_id` = '$id'";
          "SELECT * FROM plant_landscape_use_plants p LEFT JOIN list_landscape_use l ON p.landscape_use_id = l.id WHERE `plant_id` = '$id'";

         */
        for ($i = 0; $i < $num_rows; $i++) {
            $plant = new plant(mysql_result($result, $i, 'id'));
            $id[] = $plantID = mysql_result($result, $i, 'id');
            $label_info_array = get_plant_label_info($plant->info['item_number'], $plantID);
            foreach ($label_info_array as $label_info) {
                $xml_data .= "\t<Plant>\r\n";
                $xml_data .= "\t\t<ActionCode>UPDATE</ActionCode>\r\n";
                $xml_data .= "\t\t<ItemSizeID>" . $label_info['item_size_id'] . "</ItemSizeID>\r\n";
                //$xml_data .= "\t\t<ItemSizeID>".str_pad($plant->info['item_number'],5,'0',STR_PAD_LEFT).$label_info['size']."</ItemSizeID>\r\n";
                $xml_data .= "\t\t<Item>" . htmlentities(str_pad($plant->info['item_number'], 5, '0', STR_PAD_LEFT)) . "</Item>\r\n";

                $special_icon = '';
                if ($plant->info['is_monrovia_exclusive'] == '1') {
					$special_icon = 'Exclusive';
				} else if ($plant->info['is_new'] == '1') {
					$special_icon = 'New';
				}
                $xml_data .= "\t\t<SpecialIcon>" . htmlentities($special_icon) . "</SpecialIcon>\r\n";
                $xml_data .= "\t\t<Size>" . htmlentities($label_info['size']) . "</Size>\r\n";

                $form = _xml_sanitize(htmlentities($label_info['form'], ENT_COMPAT, 'UTF-8'));

                //if($form!='')
                $xml_data .= "\t\t<SizeDescription>" . $form . "</SizeDescription>\r\n";
                $xml_data .= "\t\t<Botanical>" . _xml_sanitize($plant->info['botanical_name']) . "</Botanical>\r\n";
                $xml_data .= "\t\t<BotanicalShort>" . _xml_sanitize(get_short_botanical_name($plant->info['botanical_name'])) . "</BotanicalShort>\r\n";
                $xml_data .= "\t\t<CommonName>" . _xml_sanitize($plant->info['common_name']) . "</CommonName>\r\n";
                $xml_data .= "\t\t<PriAttribute>" . _xml_sanitize($plant->info['primary_attribute']) . "</PriAttribute>\r\n";

                $average_landscape_size = _xml_sanitize(strip_tags($label_info['average_landscape_size']));
                $care_instructions = _xml_sanitize(strip_tags($label_info['care_instructions']));
                $companion_plant_description = _xml_sanitize(strip_tags($plant->info['description_companion_plants']));/** Added 10/16 */
                if ($companion_plant_description != '')
                    $xml_data .= "\t\t<CompanionPlantDescription>" . $companion_plant_description . "</CompanionPlantDescription>\r\n";

                if ($average_landscape_size == '')
                    $average_landscape_size = _xml_sanitize($plant->info['average_landscape_size']);
                $xml_data .= "\t\t<AvgLandscapeSize>" . $average_landscape_size . "</AvgLandscapeSize>\r\n";
                if ($care_instructions == '')
                    $care_instructions = _xml_sanitize($plant->info['description_care']);
                $xml_data .= "\t\t<CareInstructions>" . $care_instructions . "</CareInstructions>\r\n";
                $xml_data .= "\t\t<ContainerSize>" . _xml_sanitize(htmlentities($label_info['container_size'], ENT_COMPAT, 'UTF-8')) . "</ContainerSize>\r\n";
                $xml_data .= "\t\t<LegalVolume>" . htmlentities($label_info['volume']) . "</LegalVolume>\r\n";
                //$xml_data .= "\t\t<US_Volume>".htmlentities($label_info['volume_us'])."</US_Volume>\r\n";
                //$xml_data .= "\t\t<Metric_Volume>".htmlentities($label_info['volume_metric'])."</Metric_Volume>\r\n";
                $xml_data .= "\t\t<UPC>" . htmlentities($label_info['upc']) . "</UPC>\r\n";
                $xml_data .= "\t\t<FlowerTime>" . _xml_sanitize(strip_tags($plant->info['flowering_time'])) . "</FlowerTime>\r\n";
                $xml_data .= "\t\t<PlantBenefits>" . _xml_sanitize(strip_tags($plant->info['description_benefits'])) . "</PlantBenefits>\r\n";
                $xml_data .= "\t\t<SunExpose>" . htmlentities($plant->info['sun_exposures_friendly']) . "</SunExpose>\r\n";
                $xml_data .= "\t\t<WaterReq>" . _xml_sanitize(strip_tags($plant->info['water_requirement_details'])) . "</WaterReq>\r\n";

                if (isset($plant->info['sun_exposures']) && count($plant->info['sun_exposures']) > 0) {
                    $sun_exposures = array();
                    for ($n = 0; $n < count($plant->info['sun_exposures']); $n++) {
                        $sun_exposure = $plant->info['sun_exposures'][$n]->name;
                        if ($sun_exposure != 'Full shade' && $sun_exposure != 'Full sun')
                            $sun_exposure = 'Partial sun';

                        if (!in_array($sun_exposure, $sun_exposures))
                            $sun_exposures[] = $sun_exposure;
                    }
                    $sun_exposures = array_reverse($sun_exposures);
                    $xml_data .= "\t\t<Sun_exposure>" . htmlentities(implode(',', $sun_exposures)) . "</Sun_exposure>\r\n";
                }else {
                    $xml_data .= "\t\t<Sun_exposure></Sun_exposure>\r\n";
                }

                if (isset($plant->info['water_requirement'])) {
                    $xml_data .= "\t\t<Water>" . _xml_sanitize(strip_tags($plant->info['water_requirement'])) . "</Water>\r\n";
                } else {
                    $xml_data .= "\t\t<Water></Water>\r\n";
                }

                $xml_data .= "\t\t<CZoneHigh>" . htmlentities($plant->info['cold_zone_high']) . "</CZoneHigh>\r\n";
                $xml_data .= "\t\t<CZoneLow>" . htmlentities($plant->info['cold_zone_low']) . "</CZoneLow>\r\n";

                // Special Features, Landscape Uses and Problem Solutions added 5/8/15
                $xml_data .= "\t\t<SpecialFeatures>" . htmlentities($label_info['special_features']) . "</SpecialFeatures>\r\n";
                $xml_data .= "\t\t<ProblemSolutions>" . htmlentities($label_info['problem_solutions']) . "</ProblemSolutions>\r\n";
                $xml_data .= "\t\t<LandscapeUses>" . htmlentities($label_info['landscape_uses']) . "</LandscapeUses>\r\n";

                //$temperature_range = get_cold_zone_temperature_range($plant->info['cold_zone_low'],$plant->info['cold_zone_high']);
                //if($temperature_range!='') $xml_data .= "\t\t<ColdHard>".$temperature_range."</ColdHard>\r\n";
                //$xml_data .= "\t\t<HZoneHigh>".$plant->info['heat_zone_high']."</HZoneHigh>\r\n";
                //$xml_data .= "\t\t<HZoneLow>".$plant->info['heat_zone_high']."</HZoneLow>\r\n";
                //$xml_data .= "\t\t<Category>".htmlentities($plant->info['subcategory'])."</Category>\r\n";
                $xml_data .= "\t</Plant>\r\n";
                // SEE IF mysql_free_result IS NEEDED HERE
                // ADD TO class_sql (each call)
            }
        }
    }

    $xml_data .= "</DataSync>\r\n";

    $xml_data = str_replace('™', '&#8482;', $xml_data);

    return array($id, $xml_data);
}

function get_short_botanical_name($botanical_name) {
    $search_suffixes = array(' P.P.', ' Plant Patent Applied For');
    $pos = -1;
    for ($i = 0; $i < count($search_suffixes); $i++) {
        if ($pos == -1)
            $pos = strpos($botanical_name, $search_suffixes[$i]);
    }
    if ($pos > -1) {
        $botanical_name = trim(substr($botanical_name, 0, $pos));
    }
    return $botanical_name;
}

function get_plant_label_info($item_number, $plantID) {

    // SQL INJECTION-SAFE
    $item_number = intval($item_number);

    $ret = array();

    $items = unserialize(get_url('http://azlink.monrovia.com/tpg_items.php?item_number=' . $item_number));

    for ($i = 0; $i < count($items); $i++) {
        $item = $items[$i];
        $label_size_info = get_label_size_info($item['SIZE'], $item_number, $plantID);
        //var_dump($label_size_info);
        if ($label_size_info != null) {
            $ret[] = array(
                'item_size_id' => $item['IMLITM'],
                'size' => $item['SIZE'],
                'form' => $label_size_info['form'],
                'upc' => $item['IMUPCN'],
                'volume' => $label_size_info['volume'],
                'container_size' => $label_size_info['container_size'],
                'average_landscape_size' => $label_size_info['average_landscape_size'],
                'care_instructions' => $label_size_info['care_instructions'],
                'special_features' => $label_size_info['special_features'],
                'problem_solutions' => $label_size_info['problem_solutions'],
                'landscape_uses' => $label_size_info['landscape_uses'],
                'description_companion_plants' => $label_size_info['description_companion_plants']
            );
        }
    }
    return $ret;
}

function get_label_size_info($item_size, $item_number, $plantID) {

    $item_number = str_pad($item_number, 5, '0', STR_PAD_LEFT);

    $result = mysql_query("SELECT item_size,container_size,form,volume_us,volume_metric FROM hort_label_item_sizes WHERE item_size = '$item_size' LIMIT 1");

    $ret = array('volume' => '', 'container_size' => '',
        'average_landscape_size' => '', 'care_instructions' => '',
        'form' => '', 'special_features' => '', 'landscape_uses' => '',
        'problem_solutions' => '');

    if (mysql_num_rows($result) > 0) {
        $volume_metric = mysql_result($result, 0, 'volume_metric');
        $volume = mysql_result($result, 0, 'volume_us');
        if ($volume_metric != '')
            $volume .= ' (' . $volume_metric . ')';

        $ret['volume'] = $volume;
        $ret['container_size'] = mysql_result($result, 0, 'container_size');
        $ret['form'] = mysql_result($result, 0, 'form');

        $result = mysql_query("SELECT average_landscape_size,care_instructions FROM hort_label_descriptions WHERE item_size_id = '$item_number$item_size' LIMIT 1");

        if (mysql_num_rows($result) > 0) {
            $ret['average_landscape_size'] = mysql_result($result, 0, 'average_landscape_size');
            $ret['care_instructions'] = mysql_result($result, 0, 'care_instructions');
        }
        // Special Feature Exclude Array
        $special_feature_exclusions = array('Attracts Butterflies', 'Attracts Hummingbirds');

        // Special Feature Query
        $special = mysql_query("SELECT * FROM plant_special_feature_plants p LEFT JOIN list_special_feature l ON p.special_feature_id = l.id WHERE `plant_id` = '$plantID'");
        if (mysql_num_rows($special) > 0) {
            while ($rows = mysql_fetch_array($special)) {
                //Exclude Specific types
                if (!in_array($rows['name'], $special_feature_exclusions)) {
                    $temp[] = $rows['name']; //Store all special features in a temp array
                }
            }
            $ret['special_features'] = implode(',', $temp);
        }
        //Problem/Solution Query
        $solution = mysql_query("SELECT * FROM plant_problem_solution_plants p LEFT JOIN list_problem_solution l ON p.problem_solution_id = l.id WHERE `plant_id` = '$plantID'");
        if (mysql_num_rows($solution) > 0) {
            while ($rows = mysql_fetch_array($solution)) {
                //Only grab the Solution if it hasnt been used in Special Features
                if (!in_array($rows['name'], $temp)) {
                    $temper[] = $rows['name']; //Store all special features in a temp array
                }
            }
            if (isset($temper)) {
                $ret['problem_solutions'] = implode(',', $temper);
            }
        }
        //Landscape Query
        $landscape = mysql_query("SELECT * FROM plant_landscape_use_plants p LEFT JOIN list_landscape_use l ON p.landscape_use_id = l.id WHERE `plant_id` = '$plantID'");
        if (mysql_num_rows($landscape) > 0) {
            while ($rows = mysql_fetch_array($landscape)) {
                $temps[] = $rows['name']; //Store all special features in a temp array
            }
            $ret['landscape_uses'] = implode(',', $temps);
        }

        //Description_companion_plants Query
        $companion = mysql_query("SELECT description_companion_plants FROM plants WHERE `id` = '$plantID'");
        if (mysql_num_rows($companion) > 0) {
            while ($rows = mysql_fetch_array($companion)) {
                $temps[] = $rows['description_companion_plants'];
            }
            $ret['description_companion_plants'] = implode(',', $temps);
        }
        return $ret;
    } else {
        return null;
    }
}

function _xml_sanitize($xml) {
    $xml = str_replace('&trade;', '&#8482;', $xml);
    $xml = str_replace('™', '&#8482;', $xml);
    $xml = str_replace('&#153;', '&#8482;', $xml);
    $xml = str_replace('&reg;', '&#x00AE;', $xml);
    $xml = str_replace('&copy;', '&#169;', $xml);
    $xml = str_replace('&acirc;', '&#194;', $xml);
    return escape_entities($xml);
}
/* // Register Custom Post Type
function monrovia_post_type() {

	$labels = array(
		'name'                  => __( 'Home Slider'),
		'singular_name'         => __( 'Home Slider' ),
	);
	$args = array(
		'label'                 => __( 'Home Slider' ),
		'description'           => __( 'Home Slider' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'page-attributes', 'revisions', 'thumbnail' ),
		'taxonomies'            => array( ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 25,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => false,		
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
		
	);
	register_post_type( 'home_slider', $args );
}

function monrovia_init_slider() {
	if ( monrovia_check_slider() == false ) {
	
		$post_data = array(
            'post_title' => 'Home Slider',
            'post_type' => 'home_slider',
            'post_statue' => 'publish',
            'post_author' => 1
        );
		
        wp_insert_post( $post_data );
	}
}

function monrovia_check_slider() {
	//check post type
	if ( post_type_exists( 'home_slider' ) == false ) {
	   monrovia_post_type();
	}
	// check post
	if (get_page_by_title('Home Slider', OBJECT, 'home_slider' )) {
		return true;
	} else {
		return false;
	}
} */

function monrovia_edit_link_menu() {
	//global $submenu, $pagenow;

	// query the home slider posts
	//$_home_slider = new WP_Query( 'post_type=home_slider' );

	// if we have home slider post, show the edit link else the add home slider link
	//if ( $_home_slider->have_posts() ) {
	//	$_home_slider->the_post();
	//	$link = get_edit_post_link( get_the_ID(), 'return' );
	//	$title = 'Edit Home Slider';
	//} else {
		// in case if the user has deleted the default post
	//	$link = get_bloginfo( 'url' ). '/wp-admin/post-new.php?post_type=home_slider';
	//	$title = 'Add Home Page';
	//}
	//$submenu['edit.php?post_type=home_slider'] = array( array( $title, 'edit_pages', $link ) );
}
/*
function monrovia_home_slider_fields() {
	$_prefix = 'monrovia_home_slider_';
	return array(
		'Background Slider' => array(
			'left' => array(
				array(
					'label' => 'Background Image',
					'desc' 	=> '',
					'id'	=> $_prefix.'background_iamge',
					'type'	=> 'image',
					'position' => 'left'
				),
			),
			'right' => array()	
		),
		'Feature Block' => array(
			'left' => array(
				array(
					'label' => 'Desktop Image',
					'desc' 	=> '',
					'id'	=> $_prefix.'feature_desktop_image',
					'type'	=> 'image',
				),
				array(
					'label' => 'Mobile Image',
					'desc' 	=> '',
					'id'	=> $_prefix.'feature_mobile_image',
					'type'	=> 'image',
				),
			),
			'right' => array(
				array(
				'label' => 'Title',
				'desc' 	=> '',
				'id'	=> $_prefix.'feature_title',
				'type'	=> 'text',
				'position' => 'right'
			),
			)
		)
		
	);
}

function monrovia_wp_admin_style() {
        wp_register_style( 'monrovia_admin_css', get_template_directory_uri() . '/css/admin-customize.css', false, '1.0.0' );
        wp_enqueue_style( 'monrovia_admin_css' );
		wp_enqueue_script( 'monrovia_admin_script', get_template_directory_uri() . '/js/admin-customize.js' );
}
add_action( 'admin_enqueue_scripts', 'monrovia_wp_admin_style' );

function monrovia_home_slider_meta_box_markup() {
	global $custom_meta_fields, $post;
	$image = get_template_directory_uri().'/images/image.png'; 
	// Use nonce for verification
	echo '<input type="hidden" name="custom_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';
		 
    // Begin the field table and loop
    echo '<div class="mf-group-wrapper">';
    foreach (monrovia_home_slider_fields() as $fieldset => $fields) {
		echo '<div class="mf_group ">';
		echo '<div class="legend">'.$fieldset.'</div>';
		echo '<div class="row">';
		echo '<div class="col-md-3 col-sm-12">';
		foreach($fields['left'] as $ele_left) {
			$meta = get_post_meta($post->ID, $ele_left['id'], true);
			echo '<label for="'.$ele_left['id'].'">'.$ele_left['label'].'</label>';
			switch($ele_left['type']) {
				case 'text':
					echo '<input type="text" name="'.$ele_left['id'].'" id="'.$ele_left['id'].'" value="'.$meta.'" size="30" />
						<br /><span class="description">'.$ele_left['desc'].'</span>';
				break;
				case 'image':
					if ($meta) { $image = wp_get_attachment_image_src($meta, 'medium'); $image = $image[0]; }
					echo '<div class="field-image">';
					echo '<div class="preview">';
					echo '<img src="'.$image.'" class="custom_preview_image"/>';
					echo '</div>';
					echo '<div class="control-upload">';
					echo '	<input class="custom_upload_image_button button" type="button" value="Set image" />
							<small> <a href="#" class="custom_clear_image_button">Remove Image</a></small>';
					echo '</div>';
					echo '</div>';
					echo ''.$ele_left['desc'].'';				
				break;  
			} //end switch
		}
		echo '</div>';
		echo '<div class="col-md-9 col-sm-12">';
		foreach($fields['right'] as $ele_right) {
			$meta = get_post_meta($post->ID, $ele_right['id'], true);
			echo '<label for="'.$ele_right['id'].'">'.$ele_right['label'].'</label>';
			switch($ele_right['type']) {
				case 'text':
					echo '<input type="text" name="'.$ele_right['id'].'" id="'.$ele_right['id'].'" value="'.$meta.'" size="30" />
						<br /><span class="description">'.$ele_right['desc'].'</span>';
				break;
			} //end switch
		}
		echo '</div>';
		echo '</div>';
		echo '</div>';
        //$meta = get_post_meta($post->ID, $field['id'], true);
        // begin a table row with
        /echo '<tr>
                <th><label for="'.$field['id'].'">'.$field['label'].'</label></th>
                <td>';
                switch($field['type']) {
                    // case items will go here
                } //end switch
        echo '</td></tr>';/
    } // end foreach
    echo '</div>'; // end table
}

function monrovia_add_custom_meta_box_home_slider() {
    add_meta_box(
        'home_slider_meta_box', // $id
        'Home Slider', // $title 
        'monrovia_home_slider_meta_box_markup', // $callback
        'home_slider', // $page
        'normal', // $context
        'high'); // $priority
}
add_action('add_meta_boxes', 'monrovia_add_custom_meta_box_home_slider');
*/
//add_action( 'init', 'monrovia_post_type', 0 );
//add_action( 'init', 'monrovia_init_slider', 10 );
//add_action( 'admin_menu', 'monrovia_edit_link_menu' );