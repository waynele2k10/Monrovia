<?php
/*
Plugin Name: Contact Form 7 Monrovia: Multi Fields
Description: Add multi upload field to the popular Contact Form 7 plugin.
Author: Wayne Le.
Version: 1.0
Text Domain: cf7_multifile
Domain Path: languages
*/

add_action('plugins_loaded', 'contact_form_7_multifile_fields', 11);

function contact_form_7_multifile_fields() {
	global $pagenow;
	if( class_exists('WPCF7_Shortcode') ) {
		wpcf7_add_shortcode( array( 'multifile', 'multifile*' ), 'wpcf7_multifile_shortcode_handler', true );
	} else {
		if($pagenow != 'plugins.php') { return; }
		add_action('admin_notices', 'cfmultifilefieldserror');
		//add_action('admin_enqueue_scripts', 'contact_form_7_hidden_fields_scripts');
		
		function cfmultifilefieldserror() {
			$out = '<div class="error" id="messages"><p>';
			if(file_exists(WP_PLUGIN_DIR.'/contact-form-7/wp-contact-form-7.php')) {
				$out .= 'The Contact Form 7 is installed, but <strong>you must activate Contact Form 7</strong> below for the Multifile Fields Module to work.';
			} else {
				$out .= 'The Contact Form 7 plugin must be installed for the Multifile Fields Module to work. <a href="'.admin_url('plugin-install.php?tab=plugin-information&plugin=contact-form-7&from=plugins&TB_iframe=true&width=600&height=550').'" class="thickbox" title="Contact Form 7">Install Now.</a>';
			}
			$out .= '</p></div>';
			echo $out;
		}
	}
}

function contact_form_7_hidden_fields_scripts() {
	wp_enqueue_script('thickbox');
}

/**
** A base module for [multifile], [multifile*]
**/

/* Shortcode handler */

function wpcf7_multifile_shortcode_handler( $tag ) {

	$tag = new WPCF7_Shortcode( $tag );

	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-multifile' );

	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
	}

	$class .= ' wpcf7-multifile';

	if ( 'multifile*' === $tag->type ) {
		$class .= ' wpcf7-validates-as-required';
	}
	
	$value = (string) reset( $tag->values );
	$value = $tag->get_default_option( $value );
	$value = wpcf7_get_hangover( $tag->name, $value );

	$atts = array(
		'type'        => 'file',
		'class'       => $tag->get_class_option( $class ),
		'id'          => $tag->get_id_option(),
		'name'        => $tag->name,
		'tabindex'    => $tag->get_option( 'tabindex', 'int', true ),
		'value'		  => $value,
	);
	
	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
	}
	
	$atts['aria-invalid'] = $validation_error ? 'true' : 'false';
	
	$atts = wpcf7_format_atts( $atts );

	$html = sprintf(
		'<span class="wpcf7-form-control-wrap %1$s"><input %2$s/>%3$s</span>', sanitize_html_class( $tag->name ), $atts, $validation_error );
		
	//error_log('init html');
	return $html;
}

/* Encode type filter */

add_filter( 'wpcf7_form_enctype', 'wpcf7_multifile_form_enctype_filter' );

function wpcf7_multifile_form_enctype_filter( $enctype ) {
	$multipart = (bool) wpcf7_scan_shortcode( array( 'type' => array( 'multifile', 'multifile*' ) ) );

	if ( $multipart ) {
		$enctype = 'multipart/form-data';
	}

	return $enctype;
}

add_filter( 'wpcf7_validate_multifile', 'wpcf7_multifile_validation_filter', 10, 2 );
add_filter( 'wpcf7_validate_multifile*', 'wpcf7_multifile_validation_filter', 10, 2 );

function wpcf7_multifile_validation_filter( $result, $tag ) {
	$tag = new WPCF7_Shortcode( $tag );

	$name = $tag->name;
	$id = $tag->get_id_option();

	$file = isset( $_FILES[$name] ) ? $_FILES[$name] : null;
	if (!is_null($file)) {
		//wpcf7_init_miltifile_uploads(); // Confirm upload dir
		//$uploads_dir = wpcf7_upload_tmp_dir();
		//$uploads_dir = wpcf7_maybe_add_random_dir( $uploads_dir );
		// DIR copy
		$_upload_dir = wp_upload_dir();
		$uploads_real_dir = $_upload_dir['basedir'] . '/contact-form-uploads/';
		//error_log($uploads_real_dir);
		//error_log(print_r($_FILES,true));
		$allowed_file_types = array();

		if ( $file_types_a = $tag->get_option( 'filetypes' ) ) {
			foreach ( $file_types_a as $file_types ) {
				$file_types = explode( '|', $file_types );

				foreach ( $file_types as $file_type ) {
					$file_type = trim( $file_type, '.' );
					$file_type = str_replace( array( '.', '+', '*', '?' ),
						array( '\.', '\+', '\*', '\?' ), $file_type );
					$allowed_file_types[] = $file_type;
				}
			}
		}

		$allowed_file_types = array_unique( $allowed_file_types );
		$file_type_pattern = implode( '|', $allowed_file_types );

		$allowed_size = 1048576; // default size 1 MB

		if ( $file_size_a = $tag->get_option( 'limit' ) ) {
			$limit_pattern = '/^([1-9][0-9]*)([kKmM]?[bB])?$/';

			foreach ( $file_size_a as $file_size ) {
				if ( preg_match( $limit_pattern, $file_size, $matches ) ) {
					$allowed_size = (int) $matches[1];

					if ( ! empty( $matches[2] ) ) {
						$kbmb = strtolower( $matches[2] );

						if ( 'kb' == $kbmb )
							$allowed_size *= 1024;
						elseif ( 'mb' == $kbmb )
							$allowed_size *= 1024 * 1024;
					}

					break;
				}
			}
		}

		/* File type validation */

		// Default file-type restriction
		if ( '' == $file_type_pattern )
			$file_type_pattern = 'jpg|jpeg|png|gif|pdf|doc|docx|ppt|pptx|odt|avi|ogg|m4a|mov|mp3|mp4|mpg|wav|wmv';

		$file_type_pattern = trim( $file_type_pattern, '|' );
		$file_type_pattern = '(' . $file_type_pattern . ')';
		$file_type_pattern = '/\.' . $file_type_pattern . '$/i';
	
		//file
			
			if ( $file['error'] && UPLOAD_ERR_NO_FILE != $file['error'] ) {
				$result->invalidate( $tag, wpcf7_get_message( 'upload_failed_php_error' ) );
				return $result;
			}
			if ( empty( $file['tmp_name'] ) && 'multifile*' === $tag->type ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
				return $result;
			}
			if ( ! is_uploaded_file( $file['tmp_name'] ) )
				return $result;

			if ( ! preg_match( $file_type_pattern, $file['name'] ) ) {
				$result->invalidate( $tag, wpcf7_get_message( 'upload_file_type_invalid' ) );
				return $result;
			}

			/* File size validation */

			if ( $file['size'] > $allowed_size ) {
				$result->invalidate( $tag, wpcf7_get_message( 'upload_file_too_large' ) );
				return $result;
			}
	
			$filename = $file['name'];
			$filename = wpcf7_canonicalize( $filename );
			$filename = sanitize_file_name( $filename );
			$filename = wpcf7_antiscript_file_name( $filename );
			$filename = wp_unique_filename( $uploads_real_dir, $filename );

			$new_file = trailingslashit( $uploads_real_dir ) . $filename;

			if ( false === @move_uploaded_file( $file['tmp_name'], $new_file ) ) {
				$result->invalidate( $tag, wpcf7_get_message( 'upload_failed' ) );
				return $result;
			}
			
		//error_log(print_r($_array_value,true));
		$_POST[$name] = $filename;
		//error_log(print_r($_POST,true));
	} else {
		if ('multifile*' === $tag->type) {
			$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
			return $result;
		}
	}
	//error_log('validate - upload');
	return $result;
}

/* File uploading functions */

function wpcf7_init_miltifile_uploads() {
	$dir = wpcf7_upload_tmp_dir();
	wp_mkdir_p( $dir );
}

add_filter( 'wpcf7_messages', 'wpcf7_multifile_messages' );

function wpcf7_multifile_messages( $messages ) {
	return array_merge( $messages, array(
		'upload_failed' => array(
			'description' => __( "Uploading a file fails for any reason", 'contact-form-7' ),
			'default' => __( 'Failed to upload file.', 'contact-form-7' )
		),

		'upload_file_type_invalid' => array(
			'description' => __( "Uploaded file is not allowed file type", 'contact-form-7' ),
			'default' => __( 'This file type is not allowed.', 'contact-form-7' )
		),

		'upload_file_too_large' => array(
			'description' => __( "Uploaded file is too large", 'contact-form-7' ),
			'default' => __( 'This file is too large.', 'contact-form-7' )
		),

		'upload_failed_php_error' => array(
			'description' => __( "Uploading a file fails for PHP error", 'contact-form-7' ),
			'default' => __( 'Failed to upload file. Error occurred.', 'contact-form-7' )
		)
	) );
}

add_filter('wpcf7_mail_components', 'wpcf7_multifile_before_send_mail');

function wpcf7_multifile_before_send_mail($array) {
	//error_log('mail_components');
	//error_log(print_r($array,true));
	//error_log(print_r($_POST,true));
	
	$post = $_POST;
	$html = false;
	$postbody = '';
	
	if(wpautop($array['body']) == $array['body']) { $html = true; }
	
	$_upload_dir = wp_upload_dir();
	$_upload_url = $_upload_dir['baseurl'].'/contact-form-uploads/';
	foreach ($post as $k => $v) {
		$html_item = '';
		if (strpos($k, 'multifile-upload') !== false) {
			if (!empty($v)) {
				if ($html) {
					$html_item = '<a href="'.$_upload_url.$v.'">'.$v.'</a>';
				} else {
					$html_item = $_upload_url.$v;
				}
			}
			$array['body'] = str_replace('['.$k.'-link]',$html_item, $array['body']);
		}
	}
	//error_log(print_r($array,true));
	return $array;
}

add_filter('wpcf7_collect_mail_tags', 'wpcf7_multifile_collect_mail_tags');

function wpcf7_multifile_collect_mail_tags( $mailtags = array() ) {
	error_log(print_r($mailtags,true));
	
	if ($mailtags) {
		foreach ($mailtags as $k => $v) {
			if (strpos($v, 'multifile-upload') !== false)
				$mailtags[$k] = $v.'-link';
		}
	}
	return $mailtags;
}


function cfdbFilterSaveFiles($formData) {
	//error_log(print_r($formData->posted_data,true));
	//error_log(print_r($_POST,true));
    // CHANGE THIS: CF7 form name you want to manipulate
	if ($formData && $_POST) {
		$post = $_POST;
		$array_value = array();
		foreach ($post as $k => $v) {
			if (strpos($k, 'multifile-upload') !== false) {
				if (!empty($v)) {
					$array_value[] = $v;
				}
				unset($formData->posted_data[$k]);
			} 
		}
		$formData->posted_data['multifile-upload'] = implode(", ", $array_value);
	}
    //error_log(print_r($formData->posted_data,true));
    return $formData;
}

add_filter('cfdb_form_data', 'cfdbFilterSaveFiles');

/**
 * Register with hook 'wp_enqueue_scripts', which can be used for front end CSS and JavaScript
 */
add_action( 'wp_enqueue_scripts', 'cf7_monrovia_add_stylesheet' );

/**
 * Enqueue plugin style-file
 */
function cf7_monrovia_add_stylesheet() {
    // Respects SSL, Style.css is relative to the current file
    wp_register_style( 'cf7-monrovia-style', plugins_url('style.css', __FILE__) );
	wp_enqueue_style( 'cf7-monrovia-style' );
	wp_register_script( 'cf7-monrovia-script', plugins_url( 'main.js', __FILE__ ) );
	wp_enqueue_script( 'cf7-monrovia-script' );
}

/* Tag generator */

if ( is_admin() ) {
	add_action( 'admin_init', 'wpcf7_add_tag_generator_multifile', 30 );
}

function wpcf7_add_tag_generator_multifile() {

	if( class_exists('WPCF7_TagGenerator') ) {

		$tag_generator = WPCF7_TagGenerator::get_instance();
		$tag_generator->add( 'multifile', __( 'multifile', 'cf7_monrovia' ), 'wpcf7_tg_pane_multifile' );

	}
}

function wpcf7_tg_pane_multifile( $contact_form, $args = '' ) {

	$args = wp_parse_args( $args, array() );

	$description = __( "Generate a form tag for a multifile field. For more details, see %s.", 'contact-form-7' );
?>
<div class="control-box">
	<fieldset>
		<legend>Multi File Upload</legend>

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></label></th>
					<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
				</tr>

				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'ID attribute', 'contact-form-7' ) ); ?> (<?php echo esc_html( __( 'optional', 'cf7_modules' ) ); ?>)</label></th>
					<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
				</tr>
				
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'contact-form-7' ) ); ?></label></th>
					<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
				</tr>
			</tbody>
		</table>
	</fieldset>
</div>
	<div class="insert-box">
		<input type="text" name="multifile" class="tag code" readonly="readonly" onfocus="this.select()" />

		<div class="submitbox">
			<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
		</div>

		<br class="clear" />

		<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code multifile" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
	</div>
<?php
}
