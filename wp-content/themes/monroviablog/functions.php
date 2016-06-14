<?php
/**
 * Monroviablog functions file.
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    echo '<h1>Forbidden</h1>';
    exit();
}

// Get Theme Options
global $monroviablog_theme_options;
$monroviablog_theme_options = get_option('monroviablog_theme_options');
// Register Custom Navigation Walker
require_once('inc/wp_bootstrap_navwalker.php');
if (defined( 'WPB_VC_VERSION' )) {
	require_once('inc/monroviablog_vc.php' );
}
require_once('inc/shortcodes.php' );
require_once('inc/metabox.php' );

require_once('includes/classes/class_plant.php');		
require_once('includes/classes/class_search_plant.php');
// Load any external files you have here
include('includes/utility_functions.php');
/* * *******************************************************************
 * THEME SETUP
 */

function monroviablog_setup() {

    // Translations support. Find language files in monroviablog/languages
    load_theme_textdomain('monroviablog', get_template_directory() . '/languages');
    $locale = get_locale();
    $locale_file = get_template_directory() . "/languages/{$locale}.php";
    if (is_readable($locale_file)) {
        require_once($locale_file);
    }

    // Set content width
    if (!isset($content_width))
        $content_width = 720;

    // Editor style (editor-style.css)
    add_editor_style(array('assets/css/editor-style.css'));

    // Load up our theme options page and related code.
    require(get_template_directory() . '/inc/theme-options.php');

    // Widget areas
    if (function_exists('register_sidebar')) :
        // Sidebar right
        register_sidebar(array(
            'name' => "Sidebar right",
            'id' => "ft-widgets-aside-right",
            'description' => __('Widgets placed here will display in the right sidebar', 'monroviablog'),
            'before_widget' => '<div id="%1$s" class="well well-sm widget %2$s">',
            'after_widget' => '</div>'
        ));
        // Footer Block 1
        register_sidebar(array(
            'name' => "Footer Block 1",
            'id' => "ft-widgets-footer-block-1",
            'description' => __('Widgets placed here will display in the first footer block', 'monroviablog'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>'
        ));
        // Footer Block 2
        register_sidebar(array(
            'name' => "Footer Block 2",
            'id' => "ft-widgets-footer-block-2",
            'description' => __('Widgets placed here will display in the second footer block', 'monroviablog'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>'
        ));
        // Footer Block 3
        register_sidebar(array(
            'name' => "Footer Block 3",
            'id' => "ft-widgets-footer-block-3",
            'description' => __('Widgets placed here will display in the third footer block', 'monroviablog'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>'
        ));
    endif;

    // Nav Menu (Custom menu support)
    if (function_exists('register_nav_menu')) :
        register_nav_menu('primary', __('Monroviablog Primary Menu', 'monroviablog'));
        register_nav_menu('header', __('Monroviablog Header Menu', 'monroviablog'));
        register_nav_menu('mobile', __('Monroviablog Mobile Menu', 'monroviablog'));
    endif;

    // Theme Features: Automatic Feed Links
    add_theme_support('automatic-feed-links');

    // Theme Features: Post Thumbnails and custom image sizes for post-thumbnails
    add_theme_support('post-thumbnails', array('post', 'page'));

    // Theme Features: Post Formats
    //add_theme_support('post-formats', array('aside', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video', 'audio'));

    add_image_size('monroviablog-list', 490, 275, array('center', 'center'));
    add_image_size('monroviablog-child-feature', 309, 175, array('center', 'center'));
    add_image_size('monroviablog-main-feature', 644, 361, array('center', 'center'));
    add_image_size('monroviablog-thumbnails', 236, 134, array('center', 'center'));

    // Theme Features: Custom Background
    $custom_background_args = array(
        'default-color' => 'f5f5f5',
    );
    add_theme_support('custom-background', $custom_background_args);
}

add_action('after_setup_theme', 'monroviablog_setup');

function wpse_allowedtags() {
    // Add custom tags to this string
	//Don't add the <p> or it will break.
        return '<script>,<style>,<br>,<em>,<i>,<ul>,<ol>,<li>,<a>,<img>,<video>,<audio>'; 
    }

if ( ! function_exists( 'custom_excerpt' ) ) : 

    function custom_excerpt($test = '') {
    //global $post;
    $raw_excerpt = $text;
        if ( '' == $text ) {

            $text = get_the_content('');
            //$text = strip_shortcodes( $text );
            $text = apply_filters('the_content', $text);
            $text = str_replace(']]>', ']]&gt;', $text);
            $text = strip_tags($text, wpse_allowedtags()); /*IF you need to allow just certain tags. Delete if all tags are allowed */

            //Set the excerpt word count and only break after sentence is complete.
                $excerpt_word_count = 13;
                $excerpt_length = apply_filters('excerpt_length', $excerpt_word_count); 
                $tokens = array();
                $excerptOutput = '';
                $count = 0;

                // Divide the string into tokens; HTML tags, or words, followed by any whitespace
                preg_match_all('/(<[^>]+>|[^<>\s]+)\s*/u', $text, $tokens);

                foreach ($tokens[0] as $token) { 

                    //if ($count >= $excerpt_word_count && preg_match('/[\,\;\?\.\!]\s*$/uS', $token)) { 
					if ($count >= $excerpt_word_count) { 
                    // Limit reached, continue until , ; ? . or ! occur at the end
                        $excerptOutput .= trim($token);
                        break;
                    }

                    // Add words to complete sentence
                    $count++;

                    // Append what's left of the token
                    $excerptOutput .= $token;
                }

            $text = trim(force_balance_tags($excerptOutput));

                $excerpt_end = '...'; 
                $excerpt_more = apply_filters('excerpt_more', ' ' . $excerpt_end); 

                //$pos = strrpos($text, '</');
                //if ($pos !== false){
                // Inside last HTML tag
				//	$text = substr_replace($text, $excerpt_end, $pos, 0); /* Add read more next to last word */
				//}
                //else{
                // After the content
					$text .= $excerpt_end; /*Add read more in new paragraph */
				//}
            return $text;   

        }
        return apply_filters('custom_excerpt', $text, $raw_excerpt);
    }

endif; 

remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'custom_excerpt'); 


// function custom_excerpt($wpse_excerpt) {
    // $raw_excerpt = $text;
    // if ('' == $text) {
        // $text = get_the_content('');
        ////$text = strip_shortcodes( $text );
        // $text = do_shortcode($text);
        // $text = apply_filters('the_content', $text);
        // $text = str_replace(']]>', ']]>', $text);
        // $excerpt_length = apply_filters('excerpt_length', 13);        
		// $excerpt_more = apply_filters('excerpt_more', ' ' . '...');
        // $text = wp_trim_words($text, $excerpt_length, $excerpt_more);
    // }
    // return apply_filters('wp_trim_excerpt', $text, $raw_excerpt);
// }

// remove_filter( 'get_the_excerpt', 'wp_trim_excerpt' );
// add_filter( 'get_the_excerpt', 'custom_excerpt' );


function monroviablog_shorten_title( $title ) {
	$count = 32;
	if (is_single() OR is_page() AND !is_front_page()) {
		return $title;
	} if (strlen($title) > $count) {
		$newTitle = substr( $title, 0, $count ); // Only take the first 20 characters
		$newTitle = substr($newTitle, 0, strripos($newTitle, " "));
		$newTitle .= " &hellip;";
		return $newTitle ;
	} else {
		return $title;
	}
}
add_filter( 'the_title', 'monroviablog_shorten_title', 10, 1 );

// wp_title filter
function monroviablog_title($output) {
    echo $output;
    // Add the blog name
    bloginfo('name');
    // Add the blog description for the home/front page
    $site_description = get_bloginfo('description', 'display');
    if ($site_description && (is_home() || is_front_page()))
        echo ' - ' . $site_description;
    // Add a page number if necessary
    if (!empty($paged) && ($paged >= 2 || $page >= 2))
        echo ' - ' . sprintf(__('Page %s', 'olabaworks'), max($paged, $page));
}

add_filter('wp_title', 'monroviablog_title');

/* * *******************************************************************
 * Function to load all theme assets (scripts and styles) in header
 */

function monroviablog_load_theme_assets() {

    global $monroviablog_theme_options;
	global $wp_query;
    // HTML5shiv
    // Do not know any method to enqueue a script with conditional tags!
    echo '
    <!--[if lt IE 9]>
      <script src="' . get_template_directory_uri() . '/assets/libs/html5shiv.min.js"></script>
    <![endif]-->
    ';

    // Enqueue Font Awesome CSS
    wp_enqueue_style('font-awesome', get_template_directory_uri() . '/assets/libs/font-awesome/css/font-awesome.min.css');
    wp_enqueue_style('font-awesome-ie7', get_template_directory_uri() . '/assets/libs/font-awesome/css/font-awesome-ie7.min.css');
    wp_style_add_data('font-awesome-ie7', 'conditional', 'lt IE 9');

    // Enqueue Bootstrap
    wp_enqueue_style('bootstrap', get_template_directory_uri() . '/assets/libs/bootstrap/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap', get_template_directory_uri() . '/assets/libs/bootstrap/js/bootstrap.min.js', array(), FALSE, TRUE);

    // Enqueue Main CSS (style.css)
    wp_enqueue_style('theme-style', get_stylesheet_directory_uri() . '/style.css');

    // Enqueue Monroviablog CSS and JS
    wp_enqueue_style('monroviablog-col10', get_stylesheet_directory_uri() . '/assets/css/col10.css');
    wp_enqueue_style('monroviablog-css', get_stylesheet_directory_uri() . '/assets/css/monroviablog.css');
    wp_enqueue_style('monroviablog-grid', get_stylesheet_directory_uri() . '/assets/css/grid.css');
    wp_enqueue_style('monroviablog-ngwayne', get_stylesheet_directory_uri() . '/assets/css/ngwayne.css');
    wp_enqueue_style('monroviablog-responsive', get_stylesheet_directory_uri() . '/assets/css/responsive.css');

    wp_enqueue_script('monroviablog-js', get_template_directory_uri() . '/assets/js/monroviablog.js', array(), FALSE, TRUE);
	
    wp_enqueue_script('monroviablog-ajaxpagination', get_template_directory_uri().'/assets/js/ajax-pagination.js', array(), FALSE, TRUE);
	wp_enqueue_script('monroviablog-ajaxpagination');
	wp_localize_script( 'monroviablog-ajaxpagination', 'ajax_posts', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'query_vars' => json_encode( $wp_query->query ),
		'noposts' => __('No older posts found', 'monroviablog'),
	));
    // Enqueue Wordpress Thickbox
    wp_enqueue_script('thickbox', FALSE, array(), FALSE, TRUE);
    wp_enqueue_style('thickbox');

    // Enqueue Retina.js
    wp_enqueue_script('retina-js', get_template_directory_uri() . '/assets/libs/retina.min.js', array(), FALSE, TRUE);
	wp_enqueue_script('jquery-bxslider-js', get_template_directory_uri() . '/assets/libs/jquery.bxslider/jquery.bxslider.min.js', array(), FALSE, TRUE);
}

add_action('wp_enqueue_scripts', 'monroviablog_load_theme_assets');

function monroviablog_admin_enqueue($hook) {
    if ( 'post-new.php' != $hook && 'post.php' != $hook) {
        return;
    }
	wp_enqueue_style('select2_style', get_template_directory_uri() . '/assets/libs/select2/css/select2.min.css');
    wp_enqueue_script( 'select2_script', get_template_directory_uri() . '/assets/libs/select2/js/select2.min.js' );
}
add_action( 'admin_enqueue_scripts', 'monroviablog_admin_enqueue' );

/* * *******************************************************************
 * RETINA SUPPORT
 */
add_filter('wp_generate_attachment_metadata', 'monroviablog_retina_support_attachment_meta', 10, 2);

function monroviablog_retina_support_attachment_meta($metadata, $attachment_id) {

    // Create first image @2
    monroviablog_retina_support_create_images(get_attached_file($attachment_id), 0, 0, false);

    foreach ($metadata as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $image => $attr) {
                if (is_array($attr))
                    monroviablog_retina_support_create_images(get_attached_file($attachment_id), $attr['width'], $attr['height'], true);
            }
        }
    }

    return $metadata;
}

function monroviablog_retina_support_create_images($file, $width, $height, $crop = false) {

    $resized_file = wp_get_image_editor($file);
    if (!is_wp_error($resized_file)) {

        if ($width || $height) {
            $filename = $resized_file->generate_filename($width . 'x' . $height . '@2x');
            $resized_file->resize($width * 2, $height * 2, $crop);
        } else {
            $filename = str_replace('-@2x', '@2x', $resized_file->generate_filename('@2x'));
        }
        $resized_file->save($filename);

        $info = $resized_file->get_size();

        return array(
            'file' => wp_basename($filename),
            'width' => $info['width'],
            'height' => $info['height'],
        );
    }

    return false;
}

add_filter('delete_attachment', 'monroviablog_delete_retina_support_images');

function monroviablog_delete_retina_support_images($attachment_id) {
    $meta = wp_get_attachment_metadata($attachment_id);
    $upload_dir = wp_upload_dir();
    $path = pathinfo($meta['file']);

    // First image (without width-height specified
    $original_filename = $upload_dir['basedir'] . '/' . $path['dirname'] . '/' . wp_basename($meta['file']);
    $retina_filename = substr_replace($original_filename, '@2x.', strrpos($original_filename, '.'), strlen('.'));
    if (file_exists($retina_filename))
        unlink($retina_filename);

    foreach ($meta as $key => $value) {
        if ('sizes' === $key) {
            foreach ($value as $sizes => $size) {
                $original_filename = $upload_dir['basedir'] . '/' . $path['dirname'] . '/' . $size['file'];
                $retina_filename = substr_replace($original_filename, '@2x.', strrpos($original_filename, '.'), strlen('.'));
                if (file_exists($retina_filename))
                    unlink($retina_filename);
            }
        }
    }
}

/* GALLERY SHORTCODE FILTER FOR CAROUSEL

  Usage: [ft_carousel include="123,456,789"]content[/ft_carousel]
  (*) 123,456,789 are the Media attachments IDs you want to be displayed

 */
add_shortcode('ft_carousel', 'ft_shortcode_carousel');

function ft_shortcode_carousel($attr, $content) {

    global $post;

    // Little fix as the order of arguments is not the same when
    // in "gallery" post formats
    if (!empty($content) && is_array($content)) {
        $attr = $content;
        if (!empty($attr[0]))
            $content = $attr[0];
        else
            $content = '';
    }

    $output = $content;

    // OrderBy
    $orderby = 'menu_order';
    if (!empty($attr['orderby']))
        $orderby = sanitize_sql_orderby($attr['orderby']);

    // If we got an include attr
    if (!empty($attr['include']))
        $images = get_posts(array('include' => $attr['include'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => $orderby));

    // If we do not have images yet...
    if (empty($images)) :
        // Get Post Images
        $images = get_children(array(
            'post_parent' => $post->ID,
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'numberposts' => 100,
            'orderby' => $orderby,
            'order' => 'DESC'
        ));
    endif;

    // If images are found, proceed
    if (!empty($images)) :

        $indicators = '';
        $items = '';

        $i = 0;
        foreach ($images as $image) :

            $act = ($i == 0) ? 'active' : '';

            $indicators .= '
                <li data-target="#slideshow-' . $post->ID . '" data-slide-to="' . $i . '" class="' . $act . '"></li>
            ';

            $items .= '
            <div class="item ' . $act . '">
                <a rel="attached-to-slider-' . $post->ID . '" class="thickbox" href="' . $image->guid . '" title="' . get_the_title($image->ID) . '">
                    <img src="' . $image->guid . '" alt="' . get_the_title($image->ID) . '"/>
                </a>
                <div class="carousel-caption">
                    <h4>' . get_the_title($image->ID) . '</h4>
                    <br />
                </div>
            </div>
            ';

            $i++;
        endforeach;

        // BEGIN OUTPUT
        // Clearfix
        $output .= '<div class="clearfix"></div>';

        $output .= '<div id="slideshow-' . $post->ID . '" class="carousel slide" data-ride="carousel">';

        // INDICATORS
        $output .= '<ol class="carousel-indicators">' . $indicators . '</ol>';

        // ITEMS
        $output .= '<div class="carousel-inner">' . $items . '</div>';

        $output .= '</div>';

        // Clearfix
        $output .= '<div class="clearfix"></div><br />';

        // END OUTPUT

        return $output;

    endif;

    // Return nothing
    return;
}

/* To automatically execute carousel shortcode when post type is "gallery" */
add_action('post_gallery', 'ft_shortcode_carousel', 10, 2);

function exclude_protected($where) {
	global $wpdb;
	return $where .= " AND {$wpdb->posts}.post_password = '' ";
}

add_action('pre_get_posts','monroviablog_alter_query');
function monroviablog_alter_query($query){

    if( $query->is_main_query() ){
            $_year = "";
            $_month = "";
            if (isset($_POST['year']) && $_POST['year'] != "") {
                    $_year = $_POST['year'];
                    if (isset($_POST['month']) && $_POST['month'] != "") {
                            $_month = $_POST['month'];
                            $query->set( 'year', $_year );
                            $query->set( 'monthnum', $_month );
                    } else {
                            $query->set( 'year', $_year );
                    }
            }
            $query->set( 'paged', 1 );
    }

    if (is_feed()) {
        add_filter( 'posts_where', 'exclude_protected' );
    }
}

function select_month_html($month = "") {
    $html = '<option value=""> - Select - </option>';
    for ($i = 1; $i <= 12; $i++) {
        if ($i == $month) {
            $html .= '<option value="' . $i . '" selected>' . date("F", strtotime(date("Y") . "-" . $i . "-01")) . '</option>';
        } else {
            $html .= '<option value="' . $i . '">' . date("F", strtotime(date("Y") . "-" . $i . "-01")) . '</option>';
        }
    }
    return $html;
}

function select_year_html($year = "") {
    global $monroviablog_theme_options;
    $html = '<option value=""> - Select - </option>';
    if (isset($monroviablog_theme_options['year_blog_start'])) {
        for ($i = $monroviablog_theme_options['year_blog_start']; $i <= date("Y"); $i++) {
            if ($i == $year) {
                $html .= '<option value="' . $i . '" selected>' . $i . '</option>';
            } else {
                $html .= '<option value="' . $i . '">' . $i . '</option>';
            }
        }
    }
    return $html;
}

function monrovia_use_caching() {
    return MONROVIA_USE_CACHING;
}

function monrovia_get_cache($key) {
    if (!monrovia_use_caching()) {
        return false;
    }

    return get_transient($key);
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


/* * ************* Get Plant Data from Plant ID ************** */

/**
  @variable $plantID - Set to Null if not availiable
  @variable $plantItemNum - Fallback to Plant Item Number
  @return an Array of Plant Data - Image ID, Plant Title, Item Number, Primary Attribute
 */
function getPlantData($plantID = '', $plantItemNum = '') {
    $transient_name = ( $plantID != '' ) ? sprintf('plant_data_id_%s', $plantID) : sprintf('plant_data_in_%s', $plantItemNum);
    $plant_data_content = monrovia_get_cache($transient_name);
    if (false === $plant_data_content) :
        // Set the SQL statement
        $sql = "SELECT plants.id, plants.common_name, plants.botanical_name, plants.item_number, plants.primary_attribute, plants.is_new, plants.cold_zone_high, plants.cold_zone_low, plants.flowering_time, plants.flower_color_id, list_flower_color.name
			FROM plants
			INNER JOIN list_flower_color
			ON plants.flower_color_id=list_flower_color.id";
        if ($plantID != '') {
            $sql .= " WHERE plants.id = '$plantID'";
        } else {
            $sql .= " WHERE plants.item_number = '$plantItemNum'";
        }
        //If the count field already exists
        if (mysql_num_rows(mysql_query($sql)) > 0) {
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
        if (mysql_num_rows(mysql_query($sql)) > 0) {
            $imageID = mysql_fetch_array(mysql_query($sql));
            $data['image-id'] = $imageID['id'];
        } else {
            $data['image-id'] = 'no-image';
        }
        $plant_data_content = $data;
        monrovia_set_cache($transient_name, $plant_data_content, MONROVIA_PLANT_DATA_TRANSIENT_EXPIRE);
    endif;

    $data = $plant_data_content;
    return $data;
}

function get_social_facebook_html () {
	global $monroviablog_theme_options;
	$html = "#";
	if (isset($monroviablog_theme_options['social_facebook'])) {
		$html = $monroviablog_theme_options['social_facebook'];
	}
	return $html;
}

function get_social_twitter_html () {
	global $monroviablog_theme_options;
	$html = "#";
	if (isset($monroviablog_theme_options['social_twitter'])) {
		$html = $monroviablog_theme_options['social_twitter'];
	}
	return $html;
}

function get_social_pinterest_html () {
	global $monroviablog_theme_options;
	$html = "#";
	if (isset($monroviablog_theme_options['social_pinterest'])) {
		$html = $monroviablog_theme_options['social_pinterest'];
	}
	return $html;
}

function get_social_googleplus_html () {
	global $monroviablog_theme_options;
	$html = "#";
	if (isset($monroviablog_theme_options['social_googleplus'])) {
		$html = $monroviablog_theme_options['social_googleplus'];
	}
	return $html;
}

function get_social_tumblr_html () {
	global $monroviablog_theme_options;
	$html = "#";
	if (isset($monroviablog_theme_options['social_tumblr'])) {
		$html = $monroviablog_theme_options['social_tumblr'];
	}
	return $html;
}

function get_icons() {
	require_once ( 'inc/better-font-awesome-library/better-font-awesome-library.php' );

		$args = array(
			'version'				=> 'latest',
			'minified'				=> true,
			'remove_existing_fa'	=> false,
			'load_styles'			=> false,
			'load_admin_styles'		=> false,
			'load_shortcode'		=> false,
			'load_tinymce_plugin'	=> false
		);

		$bfa 		= Better_Font_Awesome_Library::get_instance( $args );
		$bfa_icons	= $bfa->get_icons();
		$bfa_prefix	= $bfa->get_prefix() . '-';
		$new_icons	= array();

		$stylesheet	= $bfa->get_stylesheet_url();
		$version		= $bfa->get_version();

		foreach ( $bfa_icons as $hex => $class ) {
			$new_icons[ $bfa_prefix . $class ] = $class;
		}

		$new_icons = array_merge( array( '' => '- Select -' ), $new_icons );
		
		return $new_icons;
}


function more_post_ajax(){
        wp_reset_query();
	$query_vars = json_decode( stripslashes( $_POST['query_vars'] ), true );
	if (isset($query_vars['pagename']) && $query_vars['pagename'] == 'blog') {
		$query_vars = array(
			'post_type'=>'post',
			'post_status'=>'publish',
			'posts_per_page'=>4,
		);
	}
	if (isset($_POST['year']) && $_POST['year'] != "") {
		$_year = $_POST['year'];
		if (isset($_POST['monthnum']) && $_POST['monthnum'] != "") {
			$_month = $_POST['monthnum'];
			$query_vars['year'] = $_POST['year'];
			$query_vars['monthnum'] = $_POST['monthnum'];
		} else {
			$query_vars['year'] = $_POST['year'];
		}
	}
	$query_vars['posts_per_page'] = 4;
	$query_vars['paged'] = 1;
	$post_not_in = array();
	$post_displayed = new WP_Query($query_vars);
	if ($post_displayed->have_posts()) {
		while ($post_displayed -> have_posts()) {
			$post_displayed -> the_post();
			$post_not_in[] = $post_displayed->post->ID;
		}
		wp_reset_postdata();
	}
        $query_vars['post_type'] = 'post';
        $query_vars['post_status'] = 'publish';
	$query_vars['post__not_in'] = $post_not_in;
	$query_vars['posts_per_page'] = 10;
	$query_vars['paged'] = $_POST['page'] - 1;
    header("Content-Type: text/html");
    $loop = new WP_Query($query_vars);
    $out = '';
    if ($loop -> have_posts()) :  while ($loop -> have_posts()) : $loop -> the_post();
        $the_content = get_the_content();
        $the_content = preg_replace("~(?:\[/?)[^/\]]+/?\]~s", '', $the_content);
        $the_content = preg_replace("/\[(.*?)\]/i", '', $the_content);
        $the_content = strip_tags($the_content);
        $excerpt_length = apply_filters('excerpt_length', 15);
        $excerpt_more = apply_filters('excerpt_more', ' ' . '...');
        $the_content = wp_trim_words($the_content, $excerpt_length, $excerpt_more);
        $out .= '<li class="item">';
        $out .= '<a class="thumbnail-box" href="'.get_the_permalink().'" title="'.get_the_title().'"';
			if (has_post_thumbnail()) { 
				$out .= 'style="background-position: 50% center; background-size: cover; background-image: url(' . wp_get_attachment_image_src(get_post_thumbnail_id( get_the_ID() ), 'large' )[0] . ');"'; 
			}else{
				$out .= 'style="background-position: 50% center;background-size: cover;background-image: url(' . get_bloginfo('stylesheet_directory') . '/img/thumbnail-default.jpg);"'; 
			}
		$out .= ' >';
		
		if (has_post_thumbnail()) {
            $out .= get_the_post_thumbnail(get_the_ID(), 'monroviablog-list', array());
        } else {
            $out .= '<img width="490" height="275" class="attachment-monroviablog-list size-monroviablog-list wp-post-image" src="' . get_bloginfo( 'stylesheet_directory' ) . '/img/thumbnail-default.jpg" />';
        }
        $yellow_value = get_post_meta(get_the_ID(), "yellow_label", true);
        if (! empty( $yellow_value )) {
            $out .= 	'<div class="yellow-label"><span>'.$yellow_value.'</span></div>';
        }
        $out .= '</a>';
        $out .= '<div class="content-box" style="background: #f9f8f3;">';
        $icon_name = get_post_meta(get_the_ID(), "icon_name", true);
        $icon_value = get_post_meta(get_the_ID(), "icon_value", true);
        if (! empty( $icon_name ) && ! empty( $icon_value )) {
            $out .= '<span class="icon-label">'.get_icon_label($icon_value,$icon_name).'</span>';
        }
        $out .= '<h2 class="title"><a href="'.get_the_permalink().'" class="title-body-text" title="outdoor living">'.get_the_title().'</a></h2>'; 
        $out .= '<p class="content">'.$the_content.'</p>'; 
        $out .= '<p class="more-info"><a href="'.get_author_posts_url(get_the_author_meta('ID'), get_the_author_meta('user_nicename')).'">'.get_the_author().'</a> | <a href="'.get_day_link(get_the_time('Y'), get_the_time('m'), get_the_time('d')).'" title="'.get_the_time('F j, Y').'">'.get_the_time('F j, Y').'</a></p>'; 
        $out .= '<a href="'.get_the_permalink().'" class="read-more">Read More</a>'; 
        $out .= '</li>';
    endwhile;
    endif;
    echo $out;
    wp_reset_postdata();
    die();
}

add_action('wp_ajax_nopriv_more_post_ajax', 'more_post_ajax');
add_action('wp_ajax_more_post_ajax', 'more_post_ajax');

add_filter( 'tiny_mce_before_init', 'wpex_mce_google_fonts_array' );
function wpex_mce_google_fonts_array( $initArray ) {
    $initArray['font_formats'] = 'Lato=Lato;Andale Mono=andale mono,times;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Trebuchet MS=trebuchet ms,geneva;Verdana=verdana,geneva';
    // $theme_advanced_fonts = 'Aclonica=Aclonica;';
    // $theme_advanced_fonts .= 'Lato=Lato;';
    // $theme_advanced_fonts .= 'Michroma=Michroma;';
    // $theme_advanced_fonts .= 'Paytone One=Paytone One';
    // $initArray['font_formats'] = $theme_advanced_fonts;
    return $initArray;
}

add_action( 'admin_init', 'wpex_mce_google_fonts_styles' );
function wpex_mce_google_fonts_styles() {
   $font1 = 'http://fonts.googleapis.com/css?family=Aclonica:300,400,700';
   add_editor_style( str_replace( ',', '%2C', $font1 ) ); 
   $font2 = 'http://fonts.googleapis.com/css?family=Lato:300,400,700';
   add_editor_style( str_replace( ',', '%2C', $font2 ) );
   $font3 = 'http://fonts.googleapis.com/css?family=Michroma:300,400,700';
   add_editor_style( str_replace( ',', '%2C', $font3 ) );
   $font4 = 'http://fonts.googleapis.com/css?family=Paytone+One:300,400,700';
   add_editor_style( str_replace( ',', '%2C', $font4 ) );
}

add_action('admin_head-post.php', function() {
    ?>
    <style>
    @import url(http://fonts.googleapis.com/css?family=Aclonica);
    @import url(http://fonts.googleapis.com/css?family=Lato);
    @import url(http://fonts.googleapis.com/css?family=Michroma);
    @import url(http://fonts.googleapis.com/css?family=Paytone+One); 
    </style>
    <?php
});

function get_icon_mapper_html () {
	global $monroviablog_theme_options;
	$html = "";
	if (isset($monroviablog_theme_options['icon_mapper']) & !empty($monroviablog_theme_options['icon_mapper'])) {
		$html = "background-image: url('".$monroviablog_theme_options['icon_mapper']."')";
	}
	return $html;
}

add_filter('wpmu_signup_user_notification', 'auto_activate_users', 10, 4);
function auto_activate_users($user, $user_email, $key, $meta){
  wpmu_activate_signup($key);
  return false;
}

function tml_action_url( $url, $action, $instance ) {
	if ($action == 'register') {
		setcookie( 'url_redirect', get_home_url().'/login/', 1 * DAYS_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
	}
	return $url;
}
add_filter( 'tml_action_url', 'tml_action_url', 10, 3 );

function monroviablog_uns_cookie() {
	unset( $_COOKIE['url_redirect'] );
	setcookie( 'url_redirect', '', time() - ( 15 * 60 ) );
}
add_action('wp_logout', 'monroviablog_uns_cookie');

add_filter('wp_nav_menu_items','monroviablog_add_social_box_to_menu', 10, 2);
function monroviablog_add_social_box_to_menu( $items, $args ) {
    $html = '<li>
                <div class="social">
                    <a target="_blank" href="'.get_social_facebook_html ().'" title="Facebook">
                        <i class="fa fa-facebook"></i>
                    </a>
                    <a target="_blank" href="'.get_social_twitter_html ().'" title="Twitter">
                        <i class="fa fa-twitter"></i>
                    </a>
                    <a target="_blank" href="'.get_social_pinterest_html ().'" title="Pinterest">
                        <i class="fa fa-pinterest"></i>
                    </a>
                    <a target="_blank" href="'.get_social_googleplus_html ().'" title="Google+">
                        <i class="fa fa-google-plus"></i>
                    </a>
                </div>
            </li>';
    if( $args->theme_location == 'mobile' )
        return $items.$html;

    return $items;
}

add_filter('post_updated_messages','monroviablog_post_updated_messages', 10, 2);
function monroviablog_post_updated_messages($messages) {
    $post_ID = isset($post_ID) ? (int) $post_ID : 0;
    $messages['post'][1] = __( 'Post updated.' ) . sprintf( ' <a target="_blank" href="%1$s">%2$s</a>', esc_url( get_permalink($post_ID) ), __( 'View post' ));
    $messages['post'][6] = __( 'Post published.' ) . sprintf( ' <a target="_blank" href="%1$s">%2$s</a>', esc_url( get_permalink($post_ID) ), __( 'View post' ));
    return $messages;
}

function get_icon_label($icon_id, $icon_label) {
    global $monroviablog_theme_options;
    if (!empty($icon_id)) {
        $_icons = $monroviablog_theme_options['icon_label'];
        if (isset($_icons[$icon_id])) {
            $_icon_label = '';
            if (!empty($icon_label)) {
                $_icon_label = $icon_label;
            } else {
                $_icon_label = $_icons[$icon_id]["label"];
            }
            return '<img src="'.$_icons[$icon_id]["image"].'" width="16" height="16"/>'.$_icon_label;
        }
    }
    return '';
}

function get_favicon() {
    global $monroviablog_theme_options;
    if (!empty($monroviablog_theme_options['favicon'])) {
        return $monroviablog_theme_options['favicon'];
    } else {
        return get_stylesheet_directory_uri().'/favicon.ico';
    }
}

function isForSale( $item ){
    $blog_id = 1;
    $data = false;
    $magento_url = get_blog_option( $blog_id, 'vmwpmb_magento_site_url', 'http://shop.monrovia.com/' );
    $_plant_type_not_sale = get_blog_option( $blog_id, 'monrovia_plant_type_not_sale', '' );
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

add_filter('the_excerpt_rss', 'dbt_custom_feed');
add_filter('the_content', 'dbt_custom_feed');

function dbt_custom_feed($content = '') {
    global $post;

    if (!is_feed())
        return $content;

    $content = $post->post_content;

    $content = do_shortcode($content);
    $content = str_replace( ']]>', ']]&gt;', $content );
    
    // Remove all html tags, except these
    $allowed_tags = array(
        'p' => array(),
        'a' => array('href' => array()),
        'strong' => array(),
        'em' => array(),
        'img' => array('src' => array(), 'width' => array(), 'height' => array()),
    );
    $content = wp_kses($content, $allowed_tags);
    
    // Get only some characters
//    $chars_count = 600;
//    $content = wordwrap($content, $chars_count, '[dbt]');
//    $content = explode('[dbt]', $content);
//    $content = $content[0];
    $content = preg_replace('/\[[\/]?vc_[^\]]*\]/', '', $content);

    // Balance tags
    $content = balanceTags($content, true);

    return $content;
}

function insert_image_src_rel_in_head() {
	global $post;
	if ( !is_singular()) //if it is not a post or a page
		return;
        //echo '<meta property="fb:app_id"             content="1002743856487691" />';
        echo '<meta property="og:url"                content="'.get_permalink().'" />';
        echo '<meta property="og:type"               content="article" />';
        echo '<meta property="og:title"              content="'.get_the_title().'" />';
        //echo '<meta property="og:description"        content="'.dbt_custom_feed().'" />';
	if(!has_post_thumbnail( $post->ID )) { //the post does not have featured image, use a default image
		$default_image=get_bloginfo( 'stylesheet_directory' ) . '/img/thumbnail-default.jpg'; //replace this with a default image on your server or an image in your media library
		echo '<meta property="og:image" content="' . $default_image . '"/>';
	}
	else{
		$thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
		echo '<meta property="og:image" content="' . esc_attr( $thumbnail_src[0] ) . '"/>';       
	}
        
        echo "";
}
add_action( 'wp_head', 'insert_image_src_rel_in_head', 5 );

//**if( function_exists('wp_password_change_notification') ){
//**    runkit_function_redefine('wp_password_change_notification','','');
//**    //function wp_password_change_notification(){}
//**}