<?php 

// Design Profile Form -  Retrofitted from the Orginal Monrovia.com form
// Template Name: Design Profile Edit
// @author Brett Exnowski
// @date 11-5-2013

require_once('designers.php');

	//If no user redirect to login
		if ( !is_user_logged_in() )
		{
			header("location:/community/login.php");
			die();
	}
	
	// Get current users info
	global $current_user;
    get_currentuserinfo();


// GET PROFILE INFORMATION
	$profile_query = "SELECT monrovia_profiles.* FROM monrovia_profiles JOIN wp_users WHERE (monrovia_profiles.user_id = wp_users.ID) AND (wp_users.ID='".sql_sanitize($current_user->ID)."') ORDER BY monrovia_profiles.date_created DESC LIMIT 1";
	$profile_result = sql_query($profile_query);
	if ( mysql_num_rows($profile_result) )
	{
		$profile_found = true;

		$profile = mysql_fetch_array($profile_result);

		foreach ( $profile as $key=>$value )
			$profile[$key] = unescape_special_characters($value);

		$profile_title = $profile['first_name'].' '.$profile['last_name'];
		if ( isset($profile['membership_affliation'])&&$profile['membership_affliation'] != '' )
			$profile_title .= ', '.$profile['membership_affliation'];

		// SAVE PROFILE INFO
		if ( isset($_POST['submitted']) && ($_POST['submitted'] == 'edit_profile') && isset($_POST['action']) && ($_POST['action'] == 'update') )
		{

			// CHECK FOR ERRORS
			$form_errors = array();

			$required_fields = array('first_name'=>'First name', 'last_name'=>'Last name', 'firm_name'=>'Firm name', 'email'=>'Email address', 'city'=>'City', 'state'=>'State', 'zip'=>'Zip', 'phone'=>'Phone', 'specialty'=>'Specialty', 'favorite_plant'=>'My favorite Monrovia plant', 'favorite_plant_why'=>"Why it's my favorite");

			foreach ( $required_fields as $key=>$value )
			{
				if ( trim($_POST[$key]) == '' )
					$form_errors[] = $value.' is required.';
			}

			/*if ( ! is_array($_POST['membership_affiliation'])&&trim($_POST['membership_affiliation_other'])=='' )
				$form_errors[] = 'Please provide at least one membership affiliation.';*/

			if ( ! is_array($_POST['services']) )
				$form_errors[] = 'Please provide at least one service.';

			if ( ! is_array($_POST['expertise']) )
				$form_errors[] = 'Please select at least one area of expertise.';

			if ( (trim($_POST['email']) != '') && mysql_num_rows( mysql_query("SELECT ID FROM wp_users WHERE (user_email='".sql_sanitize($_POST['email'])."') AND (ID != '".sql_sanitize($current_user->ID)."')") ) )
				$form_errors[] = 'The email address you specified is already registered with another account.';

			// GETTING LONGITUDE AND LATITUDE			
			$geocode_result = do_geocode($_POST['address'].','.$_POST['city'].','.$_POST['state'].' '.$_POST['zip'].','.$_POST['country']);
			if($geocode_result['success']){
				$latitude = $geocode_result['lat_long'][0];
				$longitude = $geocode_result['lat_long'][1];   	
			}else{
				$form_errors[] = 'The address you entered is invalid.';	
			}

			if ( $_FILES['firm_logo_new']['error'] == 0 )
			{
				if ( ! in_array($_FILES['firm_logo_new']['type'], array('image/jpeg', 'image/pjpeg')) )
					$form_errors[] = 'Firm logo is not a JPEG.';

				if ( $_FILES['firm_logo_new']['size'] > 512000 ){
					$form_errors[] = 'Firm logo file size exceeds maximum.';
				}else{
					// VALIDATE IMAGE FILE
					$im = @imagecreatefromjpeg($_FILES['firm_logo_new']['tmp_name']);
					if(!$im) $form_errors[] = 'There was a problem processing firm logo.';
				}
			}elseif($_FILES['firm_logo_new']['error'] == 1){
				$form_errors[] = 'Firm logo file size exceeds maximum.';
			}

			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			$tmp_img_errors = array();//tmp error values to upload each image
			for ( $i=0; $i<6; $i++ )
			{
			$tmp_img_errors[$i] = false;

				$_POST['portfolio'][$i] = sql_sanitize(strip_tags(preg_replace("/[^.-_0-9a-zA-Z]/", "",$_POST['portfolio'][$i])));//strip chars to match upload filename

				if ( $_FILES['portfolio_new']['error'][$i] == 0 )
				{
					if ( ! in_array($_FILES['portfolio_new']['type'][$i], array('image/jpeg', 'image/pjpeg')) ){
						$form_errors[] = 'Portfolio Image #'.($i+1).' '.strip_tags($_POST['portfolio'][$i]).' is not a JPEG.';
						$_POST['portfolio'][$i] = '';
						$tmp_img_errors[$i] = true;
					}

					if ( $_FILES['portfolio_new']['size'][$i] > 1048576 ){
						$form_errors[] = 'Portfolio Image #'.($i+1).' '.strip_tags($_POST['portfolio'][$i]).' file size exceeds maximum.';
						$_POST['portfolio'][$i] = '';
						$tmp_img_errors[$i] = true;
					}else{
						// VALIDATE IMAGE FILE
						$im = imagecreatefromjpeg($_FILES['portfolio_new']['tmp_name'][$i]);
						if(!$im){
							$form_errors[] = 'There was a problem processing Portfolio Image #'.($i+1).' '.$_POST['portfolio'][$i].'.';	
							$_POST['portfolio'][$i] = '';				
							$tmp_img_errors[$i] = true;
						}
					}
				}elseif($_FILES['portfolio_new']['error'][$i] == 1){
					$form_errors[] = 'Portfolio Image #'.($i+1).' '.strip_tags($_POST['portfolio'][$i]).' file size exceeds maximum.';
					$_POST['portfolio'][$i] = '';
					$tmp_img_errors[$i] = true;
					
				}
				//echo "<div>@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@start ".$i.' "'.$tmp_img_errors[$i].'"" '.$_FILES['portfolio_new']['name'][$i].'</div>';
				//
				//check if no erros for this file and it exists
				if ($tmp_img_errors[$i] == false && $_FILES['portfolio_new']['name'][$i] != ''){
					$_POST['portfolio'][$i] = $_FILES['portfolio_new']['name'][$i]; //remove the fakepath caused by problems with js add/remove
					//echo "<div>@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@start ".$i.'</div>';
					////add here

					// CREATE FOLDER FOR USER IMAGES
					mkdir($_SERVER['DOCUMENT_ROOT'].'/wp-content/designers/profiles/'.$current_user->ID);

					// PREPARE FILE NAME					
					$uploaded_file_name_friendly = preg_replace("/[^.-_0-9a-zA-Z]/", "", $_FILES['portfolio_new']['name'][$i]);					
					$file_suffix_number = 2;
					while ( file_exists($_SERVER['DOCUMENT_ROOT'].'/wp-content/designers/profiles/'.$current_user->ID.'/'.$uploaded_file_name_friendly) )
					{
						$path_info = pathinfo($uploaded_file_name_friendly);						
						$file_name = basename($uploaded_file_name_friendly, '.'.$path_info['extension']);

						if ( preg_match('/_\d+$/', $file_name, $matches) )
							$file_name = substr($file_name, 0, -strlen($matches[0]));
						$uploaded_file_name_friendly = $file_name.'_'.$file_suffix_number.'.'.$path_info['extension'];
						$file_suffix_number++;
					}

					// MOVE UPLOADED FILE
					move_uploaded_file($_FILES['portfolio_new']['tmp_name'][$i], $_SERVER['DOCUMENT_ROOT'].'/wp-content/designers/profiles/tmp/'.$uploaded_file_name_friendly);

					// RESIZE WITH WATERMARK
					designers_resize_image($_SERVER['DOCUMENT_ROOT'].'/wp-content/designers/profiles/tmp/'.$uploaded_file_name_friendly, $_SERVER['DOCUMENT_ROOT'].'/wp-content/designers/profiles/'.$current_user->ID.'/'.$uploaded_file_name_friendly, 320, 220);
					unlink($_SERVER['DOCUMENT_ROOT'].'/wp-content/designers/profiles/tmp/'.$uploaded_file_name_friendly);
					//echo "<div>@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@hit ".$i.'</div>';
				/////write all images without errors
				}
				//if image had error erase its name to prevent it passing as a previous uploaded image
			}
			///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			if ( isset($_POST['new_password']) && (trim($_POST['new_password']) != '') )
			{
				if ( strlen($_POST['new_password']) < 6 )
					$form_errors[] = 'Update Password: Passwords needs to be at least 6 characters.';

				if ( $_POST['new_password'] != $_POST['new_password_reenter'] )
					$form_errors[] = 'Update Password: Passwords do not match.';
			}

		
			// IF NO ERRORS - SAVE RECORD
			if ( count($form_errors) == 0 )
			{
				// PREP BASIC INFO
				$fields = array('first_name','last_name','firm_name','address','city','state','zip','country','email','website','phone','fax','specialty','favorite_plant','favorite_plant_why', 'profile');
				$save_data = array();
				foreach ($fields as $field)
					$save_data[$field] = $_POST[$field];

				// CLEAN UP MEMBERSHIP AFFILIATION OTHER
				$membership_affiliation_other = '';
				$membership_affiliation_other_parts = explode(',',$_POST['membership_affiliation_other']);
				for($i=0;$i<count($membership_affiliation_other_parts);$i++){
					$membership_affiliation_other .= ', ' . trim($membership_affiliation_other_parts[$i]);
				}
				$membership_affiliation_other = substr($membership_affiliation_other,2);
				$save_data['membership_affiliation_other'] = $membership_affiliation_other;

				if ( (trim($save_data['website']) != '') && (strpos(strtolower($save_data['website']), 'http://') === false) )
					$save_data['website'] = 'http://'.$save_data['website'];

				$save_data['services'] = join(', ', $_POST['services']);
				$save_data['membership_affiliation'] = join(', ', $_POST['membership_affiliation']);
				$save_data['expertise'] = join(', ', $_POST['expertise']);

				// LONGITUDE AND LATITUDE
				$save_data['longitude'] = $longitude;
				$save_data['latitude'] = $latitude;

				// UPLOAD LOGO
				if ( isset($_FILES['firm_logo_new']) && ($_FILES['firm_logo_new']['error'] == 0) )
				{
					// CREATE FOLDER FOR USER IMAGES
					mkdir($_SERVER['DOCUMENT_ROOT'].'/wp-content/designers/profiles/'.$current_user->ID);

					// PREPARE FILE NAME
					$uploaded_file_name_friendly = ereg_replace("[^.-_0-9a-zA-Z]", "", $_FILES['firm_logo_new']['name']);
					$file_suffix_number = 2;
					while ( file_exists($_SERVER['DOCUMENT_ROOT'].'/wp-content/designers/profiles/'.$current_user->ID.'/'.$uploaded_file_name_friendly) )
					{
						$path_info = pathinfo($uploaded_file_name_friendly);
						$file_name = basename($uploaded_file_name_friendly, '.'.$path_info['extension']);

						if ( preg_match('/_\d+$/', $file_name, $matches) )
							$file_name = substr($file_name, 0, -strlen($matches[0]));
						$uploaded_file_name_friendly = $file_name.'_'.$file_suffix_number.'.'.$path_info['extension'];
						$file_suffix_number++;
					}

					// MOVE UPLOADED FILE
					move_uploaded_file($_FILES['firm_logo_new']['tmp_name'], $_SERVER['DOCUMENT_ROOT'].'/wp-content/designers/profiles/tmp/'.$uploaded_file_name_friendly);
					designers_resize_image($_SERVER['DOCUMENT_ROOT'].'/wp-content/designers/profiles/tmp/'.$uploaded_file_name_friendly, $_SERVER['DOCUMENT_ROOT'].'/wp-content/designers/profiles/'.$current_user->ID.'/'.$uploaded_file_name_friendly, 130, 100);
					unlink($_SERVER['DOCUMENT_ROOT'].'/wp-content/designers/profiles/tmp/'.$uploaded_file_name_friendly);

					$save_data['firm_logo'] = $uploaded_file_name_friendly;
				}
				else
					$save_data['firm_logo'] = $_POST['firm_logo'];

				$save_data['user_id'] = $current_user->ID;
				$save_data['is_active'] = 1;
				$save_data['approval_status'] = 0;
				$save_data['is_submitted_for_approval'] = 0;
				$save_data['date_created'] = date("Y-m-d H:i:s");
				$save_data['url_key'] = $profile['url_key'];

				$save_query = '';
				foreach ( $save_data as $key=>$value )
				{
					if ( $save_query != '' )
						$save_query .= ',';

					if ( $key == 'profile' ) // DON'T REMOVE LINE BREAKS ON PROFILE
						$value = sql_sanitize($value, false);
					else
						$value = sql_sanitize($value);

					$save_query .= "`".$key."`='".strip_tags($value)."'";
				}

				$save_query = "INSERT INTO monrovia_profiles SET ".$save_query;

				mysql_query($save_query);
				$new_profile_id = mysql_insert_id();

				// SAVE SOCIAL NETWORKS
				if ( is_array($_POST['social_network_type']) )
					for ( $i=0; $i<count($_POST['social_network_type']); $i++ )
					{
						mysql_query("INSERT INTO monrovia_profiles_social_networks SET `profile_id`='".sql_sanitize($new_profile_id)."', `social_network`='".sql_sanitize(strip_tags($_POST['social_network_type'][$i]))."', `url`='".sql_sanitize(strip_tags($_POST['social_network_url'][$i]))."'");

					}

				// UPLOAD IMAGES
				/*if ( is_array($_FILES['portfolio_new']) )
					for ( $i=0; $i<6; $i++ )
					{
						if ( $_FILES['portfolio_new']['error'][$i] == 0 )
						{
							// CREATE FOLDER FOR USER IMAGES
							@mkdir('/nas/wp/www/cluster-1975/monrovia/wp-content/designers/profiles/'.$current_user->ID);

							// PREPARE FILE NAME
							$uploaded_file_name_friendly = ereg_replace("[^.-_0-9a-zA-Z]", "", $_FILES['portfolio_new']['name'][$i]);
							$file_suffix_number = 2;
							while ( file_exists('/nas/wp/www/cluster-1975/monrovia/wp-content/designers/profiles/'.$current_user->ID.'/'.$uploaded_file_name_friendly) )
							{
								$path_info = pathinfo($uploaded_file_name_friendly);
								$file_name = basename($uploaded_file_name_friendly, '.'.$path_info['extension']);

								if ( preg_match('/_\d+$/', $file_name, $matches) )
									$file_name = substr($file_name, 0, -strlen($matches[0]));
								$uploaded_file_name_friendly = $file_name.'_'.$file_suffix_number.'.'.$path_info['extension'];
								$file_suffix_number++;
							}

							// MOVE UPLOADED FILE
							move_uploaded_file($_FILES['portfolio_new']['tmp_name'][$i], '/nas/wp/www/cluster-1975/monrovia/wp-content/designers/profiles/tmp/'.$uploaded_file_name_friendly);

							// RESIZE WITH WATERMARK
							designers_resize_image('/nas/wp/www/cluster-1975/monrovia/wp-content/designers/profiles/tmp/'.$uploaded_file_name_friendly, '/nas/wp/www/cluster-1975/monrovia/wp-content/designers/profiles/'.$current_user->ID.'/'.$uploaded_file_name_friendly, 320, 220);
							@unlink('/nas/wp/www/cluster-1975/monrovia/wp-content/designers/profiles/tmp/'.$uploaded_file_name_friendly);

							$_POST['portfolio'][$i] = $uploaded_file_name_friendly;
						}
					}*/

				// SAVE IMAGES
				if ( is_array($_POST['portfolio']) )
					for ( $i=0; $i<6; $i++ )
					{
						$_POST['portfolio'][$i] = sql_sanitize(strip_tags(preg_replace("/[^.-_0-9a-zA-Z]/", "",$_POST['portfolio'][$i])));
						if ( trim($_POST['portfolio'][$i]) != '' )
							mysql_query("INSERT INTO monrovia_profiles_images SET `profile_id`='".sql_sanitize($new_profile_id)."', `path_thumbnail`='".sql_sanitize(strip_tags($_POST['portfolio'][$i]))."'");
					}

				// UPDATE monrovia_users
				/*$monrovia_user->info['email_address'] = $save_data['email'];
				$monrovia_user->info['first_name'] 	= $save_data['first_name'];
				$monrovia_user->info['last_name']	= $save_data['last_name'];
				$monrovia_user->info['address'] = $save_data['address'];
				$monrovia_user->info['city'] 	= $save_data['city'];
				$monrovia_user->info['state'] 	= $save_data['state'];
				$monrovia_user->info['zip'] 	= $save_data['zip'];
				*/

				//if ( $_POST['new_password'] != '' )
					//$monrovia_user->info['password'] = $_POST['new_password'];

				//$monrovia_user->save();
				//If Submitted for Approval
				if(isset($_POST['approve']) && $_POST['approve'] == 'true'){
					header("Location:/landscape-architects/profiles/my-profile/?action=profile_submit");
					die();
				} else {
					header("Location:/landscape-architects/profiles/my-profile/");
					die();
				}
			}
			else
			{
				$fields_to_populate = array('first_name','last_name','firm_name','address','city','state','zip','country','email','website','phone','fax','specialty','favorite_plant','favorite_plant_why', 'profile');
				foreach ( $fields_to_populate as $field_to_populate )
				{
					$profile[$field_to_populate] = $_POST[$field_to_populate];
				}
				
				if ( isset($_POST['membership_affiliation']) ){
					$profile['membership_affiliation'] = join(', ', $_POST['membership_affiliation']);
				}
					$profile['services'] = join(', ', $_POST['services']);
					$profile['expertise'] = join(', ', $_POST['expertise']);
			}
		}

	}
	else
	{
		$profile_found = false;
		$profile_title = 'Designer profile not found';
	}

?>

<!--<script type="text/javascript" src="/js/si.files.js"></script> -->
<script type="text/javascript" src="<?php echo get_template_directory_uri();?>/js/designers.js"></script>
<script type="text/javascript" src="<?php echo get_template_directory_uri();?>/js/autocomplete.js"></script>
<script type="text/javascript">
	jQuery(document).ready( function(){
	jQuery('#favorite_plant').autocomplete({
    serviceUrl: '<?php echo get_site_url();?>/monrovia_plants_autocomplete.php'
    });
	});

	// MAKE INPUT FILE LOOK PRETTY
	//SI.Files.stylizeAll();

	function add_social_network_option()
	{

		jQuery('.social_networks_items').append('<div><a href="#" onclick="remove_social_network_option(this); return false"><i class="fa fa-times-circle"></i></a><div class="select-wrap"><select name="social_network_type[]" onchange="change_social_network_type(this);" class="field_select"><?php display_designers_dropdown_options('social_networks') ?></select></div><label class="prepend_text">http://www.facebook.com/</label><input type="text" name="social_network_url[]" onchange="change_social_network(this);" value="" class="field_text" style="width:80px;" /></div>');
	}
</script>

<div class="page_content_body">

		<div id="profile_edit">
        	<div>
                <div class="cancel_return">
                <div class="finished">
                    <a href="javascript:void(0);" onclick="jQuery('#edit_profile_form').submit();" class="green-btn left" style="margin-right:10px;">save as draft</a>
                    <a href="javascript:void(0);" onclick="jQuery('#edit_profile_form').append('<input name=approve value=true type=hidden />');jQuery('#edit_profile_form').submit();" class="green-btn left" style="margin:0 10px 10px 0;">save as and submit for approval</a>
                    <a href="/landscape-architects/profiles/my-profile" class="white-btn left">cancel changes</a>
                </div>
                </div>
                <div class="clear"></div>
            </div>

            <form method="post" name="edit_profile" id="edit_profile_form" validation_enabled="true" enctype="multipart/form-data" class="clear">
            <input type="hidden" name="submitted" value="edit_profile" />
            <input type="hidden" name="action" value="update" />

            <?php
				if ( isset($form_errors)&&count($form_errors) > 0 )
				{
					echo '<p class="message">';
					echo 'Sorry! An error occurred and your changes were not saved.';
					echo '<ul>';
					foreach ( $form_errors as $form_error )
					{
						echo '<li>'.$form_error.'</li>';
					}
					echo '</ul></p>';
				} elseif(isset($_POST['action'])) {
					echo "<p class='message'>Profile Sucessfully saved.</p>";
				}
			?>

            <div class="left">
            	<table border="0">
					<?php include('form-fields-left.php'); ?>
				</table>
			</div>
             <div class="right" style="width:400px">

                <div class="expertise_checkboxes">
	                <div class="field_label" style="text-align:left;padding-bottom:8px;">Area(s) of expertise:</div>
	                <div style="font-size:8pt;padding-bottom:8px;">(Please check at least one.)</div>
                	<?php display_designers_checkboxes('expertise', 'expertise', explode(', ', $profile['expertise'])); ?>
                    <div class="clear"></div>
                </div>
                <div class="social_networks">
	                <div class="heading">Social Networks:</div>
                    <div class="social_networks_items" id="social_networks_items">
                    	<?php
                    		//get non saved social
                    		if ( isset( $_POST["social_network_url"] ) ){
                    			$rePostSocial = array();
                    			$loopSize = count($_POST["social_network_url"]);
                    			for($i=0;$i<$loopSize;$i++ ){
                    				switch ( $_POST["social_network_type"][$i] )
									{
										case 'Facebook':
											$social_network_url = 'http://www.facebook.com/';
											break;
										case 'Twitter':
											$social_network_url = 'http://twitter.com/';
											break;
										case 'YouTube':
											$social_network_url = 'http://www.youtube.com/';
											break;
										case 'LinkedIn':
											$social_network_url = 'http://www.linkedin.com/';
											break;
										case 'Pinterest':
											$social_network_url = 'http://www.pinterest.com/';
											break;
										case 'Houzz':
											$social_network_url = 'http://www.houzz.com/';
											break;		
										default:
											$social_network_url = '';
											break;
									}
									//echo $_POST["social_network_type"][$i].": ".$_POST["social_network_url"][$i];
									echo '<div><a href="#" onclick="remove_social_network_option(this); return false"><i class="fa fa-times-circle"></i></a><div class="select-wrap"><select name="social_network_type[]" onchange="change_social_network_type(this);" class="field_select">'.get_designers_dropdown_options('social_networks', $_POST["social_network_type"][$i]).'</select></div><label class="prepend_text">'.$social_network_url.'</label><input type="text" name="social_network_url[]" onchange="change_social_network(this);" value="'.$_POST["social_network_url"][$i].'" class="field_text" style="width:80px;" /></div>';
                    			}
                    		}else{

								$social_networks_query = "SELECT * FROM monrovia_profiles_social_networks WHERE profile_id='".$profile['id']."'";
								$social_networks_result = sql_query($social_networks_query);
								while ( $social_network = mysql_fetch_array($social_networks_result, MYSQL_ASSOC) )
								{
									switch ( $social_network['social_network'] )
									{
										case 'Facebook':
											$social_network_url = 'http://www.facebook.com/';
											break;
										case 'Twitter':
											$social_network_url = 'http://twitter.com/';
											break;
										case 'YouTube':
											$social_network_url = 'http://www.youtube.com/';
											break;
										case 'LinkedIn':
											$social_network_url = 'http://www.linkedin.com/';
											break;
										case 'Pinterest':
											$social_network_url = 'http://www.pinterest.com/';
											break;
										case 'Houzz':
											$social_network_url = 'http://www.houzz.com/';
											break;		
										default:
											$social_network_url = '';
											break;
									}

									echo '<div><a href="#" onclick="remove_social_network_option(this); return false"><i class="fa fa-times-circle"></i></a><div class="select-wrap"><select name="social_network_type[]" onchange="change_social_network_type(this);" class="field_select">'.get_designers_dropdown_options('social_networks', $social_network['social_network']).'</select></div><label class="prepend_text">'.$social_network_url.'</label><input type="text" name="social_network_url[]" onchange="change_social_network(this);" value="'.$social_network['url'].'" class="field_text" style="width:80px;" /></div>';
								}
							}//END IF							
							
						?>
                    </div>
                    <div class="add_new"><a href="#" onclick="add_social_network_option(); return false;">add new</a></div>
                </div>
                <br/>
				<div id="errors-firm-images" style="padding: 2px 4px 2px 5px;font-size: 8pt;background-color: #ff0073;color: #fff;float: left;cursor: default;display:none;"></div>
				<br/>
                <div class="firm_logo">
	                <div class="heading">Firm Logo (<strong>max 500 KB</strong>):</div>
                    <!--<input type="text" name="firm_logo" class="field_text" value="<?php echo  $profile['firm_logo'] ?>" /><label class="cabinet"><input type="file" name="firm_logo_new" class="file" /></label><div class="clear"></div>-->
                    <div id="firm_logo_text_wrap"><a href="javascript:void(0);" onClick="clearFileInputField('firm_logo_text_wrap');jQuery('firm_logo_text').value='';"><i class="fa fa-times-circle"></i></a><input  type="text" name="firm_logo" id="firm_logo_text" class="field_text" value="<?php $profile['firm_logo'] ?>" /><label class="cabinet"><input type="file"  onchange="writeNewFileLabel('firm_logo_text',this.value);" name="firm_logo_new" class="file" /></label><div class="clear"></div></div>
                </div>

                <div class="portfolio_images">
                	<div id="errors-images" style="padding: 2px 4px 2px 5px;font-size: 8pt;background-color: #ff0073;color: #fff;float: left;cursor: default;display:none;"></div>
	                <br/><br/><div class="heading">Portfolio Images (6 photos / <strong>each image max 1 MB</strong>):</div>
                    <?php
						$profile_images_result = mysql_query("SELECT * FROM monrovia_profiles_images WHERE profile_id='".sql_sanitize($profile['id'])."' ORDER BY id");
						$profile_images = array();
						while ( $profile_image = mysql_fetch_array($profile_images_result, MYSQL_ASSOC) )
							$profile_images[] = $profile_image['path_thumbnail'];

						for ( $i=0; $i<6; $i++ )
						{
							if ( isset($profile_images[$i]) ){
								$profile_images_existing_path = $profile_images[$i];
							}else if ( isset($_POST['portfolio'][$i]) ){
								$profile_images_existing_path = $_POST['portfolio'][$i];
							}else{
								$profile_images_existing_path = '';
							}
							echo '<div id="portfolio_wrap['.$i.']"><a href="javascript:void(0);" onClick="clearFileInputField(\'portfolio_wrap['.$i.']\');jQuery(\'portfolio_'.$i.'\').value=\'\';"><i class="fa fa-times-circle"></i></a><input type="text" name="portfolio['.$i.']" id="portfolio_'.$i.'" value="'.$profile_images_existing_path.'" class="field_text" /><label class="cabinet"><input type="file" onchange="writeNewFileLabel(\'portfolio_'.$i.'\',this.value);" name="portfolio_new['.$i.']" class="file" /></label><div class="clear"></div></div>';
						}
					?>
                </div>

             <!--   <div style="margin-top:20px; margin-bottom:5px">Update Password:</div>
                <table>
                <tr><td valign="top"><div class="field_label not_required">new password</div></td><td><input type="password" name="new_password" autocomplete="off" value="" class="field_text" maxlength="40" validation_type="" /></td></tr>
				<tr><td colspan="2" style="height:6px;"></td></tr>
            	<tr><div id="errors-pass" style="top: -20px;left: 110px;position: relative;padding: 2px 4px 2px 5px;font-size: 8pt;background-color: #ff0073;color: #fff;float: left;cursor: default;display:none;"></div><td valign="top"><div class="field_label not_required">re-enter password</div></td><td><input type="password" name="new_password_reenter" autocomplete="off" value="" class="field_text" maxlength="40" validation_type="" /></td></tr>
                </table> -->

                <div class="finished">
                	<br /><br />All finished?<br /><br />
                    <a href="javascript:void(0);" onclick="jQuery('#edit_profile_form').submit();" class="green-btn left">save as draft</a><br class="clearfix" /><br />
                    <a href="javascript:void(0);" onclick="jQuery('#edit_profile_form').append('<input name=approve value=true type=hidden />');jQuery('#edit_profile_form').submit();" class="green-btn left">save as and submit for approval</a><br class="clearfix" /><br />
                    <a href="/landscape-architects/profiles/my-profile" class="white-btn left">cancel changes</a>
                </div>
			</div>

			</form>

		</div>
</div>

