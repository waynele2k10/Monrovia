<?php
	// Get current users info
	global $current_user;
    get_currentuserinfo();
	
	
	// GET PROFILE INFORMATION
	// ADMIN PROFILE PREVIEW
	// User is of User ID 25329, Katharine Rudnyk // Removed 7/23/14 
	if ( is_user_logged_in() && (check_user_role("administrator", $current_user->ID ) ) && isset($wp_query->query_vars['profile']) && (is_numeric($wp_query->query_vars['profile'])) ){
		$profile_query = "SELECT monrovia_profiles.* FROM monrovia_profiles JOIN wp_users WHERE (monrovia_profiles.user_id = wp_users.ID) AND (monrovia_profiles.id='".sql_sanitize($wp_query->query_vars['profile'])."') LIMIT 1";
	// USER PROFILE PREVIEW
	} else if ( is_user_logged_in() && $wp_query->query_vars['profile'] == 'my-profile') {
		$profile_query = "SELECT monrovia_profiles.* FROM monrovia_profiles JOIN wp_users WHERE (monrovia_profiles.user_id = wp_users.ID) AND (wp_users.ID='".sql_sanitize($current_user->ID)."') ORDER BY monrovia_profiles.date_created DESC LIMIT 1";
	// PUBLIC PROFILE PREVIEW
	}else{
		$profile_query = "SELECT monrovia_profiles.* FROM monrovia_profiles JOIN wp_users WHERE (monrovia_profiles.user_id = wp_users.ID) AND (monrovia_profiles.is_active='1') AND (monrovia_profiles.approval_status='1') AND (monrovia_profiles.url_key='".sql_sanitize($wp_query->query_vars['profile'])."') ORDER BY monrovia_profiles.date_created DESC LIMIT 1";
	}
	$profile_result = mysql_query($profile_query);
	if ( mysql_num_rows($profile_result) )
	{
		$profile_found = true;

		$profile = mysql_fetch_array($profile_result);

		foreach ( $profile as $key=>$value )
			$profile[$key] = unescape_special_characters($value);

		// MEMBERSHIP AFFILIATION OTHER
		if($profile['membership_affiliation_other']!=''){
			if($profile['membership_affiliation']!='') $profile['membership_affiliation'] .= ', ';
			$profile['membership_affiliation'] .= $profile['membership_affiliation_other'];
		}

		$profile_title = $profile['first_name'].' '.$profile['last_name'];

		// SUBMIT FOR APPROVAL ACTION
		if ( isset($_GET['action']) && ($_GET['action'] == 'profile_submit') )
		{
			if ( is_user_logged_in() && isset($wp_query->query_vars['profile']) && ($wp_query->query_vars['profile'] == 'my-profile') )
			{
				// CHECK IF OTHER PROFILES SUBMITTED FOR APPROVAL
				$results_preexisting_profiles = mysql_query("SELECT COUNT(*) AS num FROM monrovia_profiles WHERE user_id='".sql_sanitize($current_user->ID)."' AND (is_submitted_for_approval=1 OR approval_status <> 0)");
				$num_preexisting_profiles = mysql_result($results_preexisting_profiles,0,'num');

				mysql_query("UPDATE monrovia_profiles SET is_submitted_for_approval='1' WHERE id='".sql_sanitize($profile['id'])."' LIMIT 1");
				$profile['is_submitted_for_approval'] = 1;

				// CLEAN UP ALL PROFILES EDITED BUT NOT SUBMITTED FOR APPROVAL BY THE USER
				$delete_profiles = mysql_query("SELECT id FROM monrovia_profiles WHERE (user_id='".sql_sanitize($current_user->ID)."') AND (is_submitted_for_approval='0')");
				while ( $delete_profile = mysql_fetch_array($delete_profiles) )
				{
					mysql_query("DELETE FROM monrovia_profiles WHERE id='".$delete_profile['id']."' LIMIT 1");
					mysql_query("DELETE FROM monrovia_profiles_images WHERE profile_id='".$delete_profile['id']."'");
					mysql_query("DELETE FROM monrovia_profiles_social_networks WHERE profile_id='".$delete_profile['id']."'");
				}

				// CLEAN UP ALL PROFILES EDITED, SUBMITTED FOR APPROVAL BY THE USER EXCEPT FOR LATEST VERSION
				$delete_profiles = mysql_query("SELECT id FROM monrovia_profiles WHERE (user_id='".sql_sanitize($current_user->ID)."') AND (is_submitted_for_approval='1') AND (approval_status='0') AND (id!='".sql_sanitize($profile['id'])."')");
				while ( $delete_profile = mysql_fetch_array($delete_profiles) )
				{
					mysql_query("DELETE FROM monrovia_profiles WHERE id='".$delete_profile['id']."' LIMIT 1");
					mysql_query("DELETE FROM monrovia_profiles_images WHERE profile_id='".$delete_profile['id']."'");
					mysql_query("DELETE FROM monrovia_profiles_social_networks WHERE profile_id='".$delete_profile['id']."'");
				}

				// SEND EMAIL CONFIRMATION IF FIRST TIME SUBMISSION
				if ($num_preexisting_profiles==0)
				{
					$email_message_html = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/email_templates/designers_new_profile.htm');
					$email_message_html = str_replace('{profile_email}',$profile['email'],$email_message_html);

					//$email_message_txt = str_replace('{profile_email}',$profile['email'],$email_message_txt);
					$emailHeaders = "Reply-To: noreply@monrovia.com\r\n";
			   		$emailHeaders .= "Return-Path: noreply@monrovia.com\r\n";
			   		$emailHeaders .= "From: Monrovia <noreply@monrovia.com>\r\n";
			   		$emailHeaders .= 'Signed-by: monrovia.com\r\n"';
					$emailHeaders .= 'MIME-Version: 1.0' . "\r\n";
					$emailHeaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

					wp_mail( $profile['email'], "Thank you for joining Monrovia's Find a Design Professional", $email_message_html, $emailHeaders );	
					//send_email($profile['email'],"Thank you for joining Monrovia's Find a Design Professional",$email_message_html,$email_message_txt,true);
				}

			}
		}
		
		// Set the Designer Profiles Image Directory
		$profile_images_path =  get_site_url().'/wp-content/designers/profiles/'.$profile['user_id'].'/';
	}
	else
	{
		$profile_found = false;
		$profile = array();
		$profile_title = 'Designer profile not found';

		// REDIRECT TO DESIGNER SEARCH IF PROFILE NOT FOUND
		header("Location:/landscape-architects/");
		die();
	}

?>


<style>
<?php 	// PREVIEW BACKGROUND IMAGE IF NECESSARY
	if ( is_user_logged_in() && isset($wp_query->query_vars['profile']) && ($wp_query->query_vars['profile'] == 'my-profile') && $profile['approval_status'] < 1 ) { ?>
	
/* body { background:url(/img/designers/preview_bg.gif); } */
<?php } ?>
</style>

<?php // ALERT BAR
	if ( is_user_logged_in() && isset($wp_query->query_vars['profile']) && ($wp_query->query_vars['profile'] == 'my-profile') && $profile['approval_status'] < 1 ) {
		// IF NOT SUBMITTED FOR APPROVAL
		$alert_message = '';
		if ( $profile['is_submitted_for_approval'] == 0 )
			$alert_message = 'You\'re viewing a preview of your profile. Click "submit for approval" if you\'re satisfied, or "edit your profile" to make further changes.';
		else
		{
			if ( isset($_GET['action']) && ($_GET['action'] == 'profile_submit') )
				$alert_message = '<b>Changes saved.</b> Your profile has been submitted for approval.';
			else
				$alert_message = 'Your profile has been submitted for approval.';
		}

		echo '<p class="alert_bar message">'.$alert_message.'</p>';
	}

	// ADMIN ACTIONS
	if ( is_user_logged_in() && ( check_user_role("administrator", $current_user->ID ) ) && isset($wp_query->query_vars['profile']) && (is_numeric($wp_query->query_vars['profile'])) ) {

		if ( isset($_REQUEST['submitted']) && ($_REQUEST['submitted'] == 'profile_edit_admin') )
		{
			// UPDATE APPROVAL STATUS
			mysql_query("UPDATE monrovia_profiles SET approval_status='".sql_sanitize($_REQUEST['approval_status'])."',  is_active='".sql_sanitize($_REQUEST['is_active'])."' WHERE id='".sql_sanitize($wp_query->query_vars['profile'])."' LIMIT 1");

			$profile['approval_status'] = $_REQUEST['approval_status'];
			$profile['is_active'] = $_REQUEST['is_active'];

			if ( trim($_REQUEST['email_message']) != '' )
			{
				// SEND EMAIL CONFIRMATION
				$email_message_html = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/email_templates/designers_notice.htm');
				$email_message_html = str_replace('{message}',$_REQUEST['email_message'],$email_message_html);
				$email_message_html = str_replace('{profile_email}',$profile['email'],$email_message_html);

				$email_message_txt = $_REQUEST['email_message'];
				$email_message_txt = str_replace('{profile_email}',$profile['email'],$email_message_txt);
				$emailHeaders = "Reply-To: noreply@monrovia.com\r\n";
			   	$emailHeaders .= "Return-Path: noreply@monrovia.com\r\n";
			   	$emailHeaders .= "From: Monrovia <noreply@monrovia.com>\r\n";
			   	$emailHeaders .= 'Signed-by: monrovia.com\r\n"';
				$emailHeaders .= 'MIME-Version: 1.0' . "\r\n";
				$emailHeaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

					wp_mail( $profile['email'], "Monrovia's Designer Profile Notice", $email_message_html, $emailHeaders );	
					// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
					

				//send_email($profile['email'],"Monrovia's Designer Profile Notice",$email_message_html,$email_message_txt,true);
			}


			// IF APPROVED, REMOVE OTHER APPROVED USER PROFILES
			if ( $_REQUEST['approval_status'] == '1' )
			{
				$delete_profiles = mysql_query("SELECT id FROM monrovia_profiles WHERE (user_id = '".sql_sanitize($profile['user_id'])."') AND (id != '".sql_sanitize($wp_query->query_vars['profile'])."') AND (approval_status='1')");
				while ( $delete_profile = mysql_fetch_array($delete_profiles) )
				{
					mysql_query("DELETE FROM monrovia_profiles WHERE id='".$delete_profile['id']."' LIMIT 1");
					mysql_query("DELETE FROM monrovia_profiles_images WHERE profile_id='".$delete_profile['id']."'");
					mysql_query("DELETE FROM monrovia_profiles_social_networks WHERE profile_id='".$delete_profile['id']."'");
				}
			}

			// REMOVE ALL PROFILE IMAGES THAT ARE NOT BEING USED BY USER
			$dir = $_SERVER['DOCUMENT_ROOT']."/wp-content";
			$images_being_used = array();
			$images = mysql_query("SELECT DISTINCT firm_logo FROM monrovia_profiles WHERE (user_id = '".sql_sanitize($profile['user_id'])."')");
			while ( $image = mysql_fetch_array($images) )
				$images_being_used[] = $image['firm_logo'];
			$images = mysql_query("SELECT DISTINCT monrovia_profiles_images.path_thumbnail FROM monrovia_profiles_images JOIN monrovia_profiles WHERE (monrovia_profiles_images.profile_id = monrovia_profiles.id) AND (monrovia_profiles.user_id = '".sql_sanitize($profile['user_id'])."')");

			while ( $image = mysql_fetch_array($images) )
				$images_being_used[] = $image['path_thumbnail'];

			if ($handle = opendir($dir.'/designers/profiles/'.$profile['user_id'].'/')) {
				while (false !== ($file = readdir($handle))) {
					if ( ($file != '.') && ($file != '..') && (! in_array($file, $images_being_used)) )
						unlink($dir.'/designers/profiles/'.$profile['user_id'].'/'.$file);
				}
				closedir($handle);
			}


			/*
			// IF APPROVED, DEACTIVATE OTHER USER PROFILES
			if ( $_REQUEST['approval_status'] == '1' )
			{
				sql_query("UPDATE monrovia_profiles SET is_active='0' WHERE (user_id = '".sql_sanitize($profile['user_id'])."') AND (id != '".sql_sanitize($_REQUEST['profile_id'])."')");
			}
			else // OTHERWISE ACTIVATE LATEST APPROVED
			{
				$latest_approved_profile = sql_query("SELECT id FROM monrovia_profiles WHERE (user_id = '".sql_sanitize($profile['user_id'])."') AND (approval_status='1') ORDER BY date_created DESC LIMIT 1");
				if ( mysql_num_rows($latest_approved_profile) )
				{
					$latest_approved_profile_id = mysql_result($latest_approved_profile, 0, 0);
					sql_query("UPDATE monrovia_profiles SET is_active='1' WHERE (user_id = '".sql_sanitize($profile['user_id'])."') AND (id = '".sql_sanitize($latest_approved_profile_id)."')");
				}
			}*/

			// REFRESH ADMIN SCREEN
			echo '<script>window.opener.document.location.href = window.opener.document.location.href;</script>';
		}

		$alert_message = '';
		switch ( $profile['approval_status'] )
		{
			case '-1':
				$alert_message = 'This profile has been <b>rejected.</b>';
				break;
			case '0':
				$alert_message = 'This profile is <b>pending approval.</b>';
				break;
			case '1':
				$alert_message = 'This profile has been <b>approved.</b>';
				break;
		}

		$alert_message .= '<form method="post" id="profile_edit_admin_form">
		<input type="hidden" name="submitted" value="profile_edit_admin">
		<table>
		<tr>
			<td>
			<fieldset style="margin-bottom:5px">
                <legend>Approval Status</legend>
				<input type="radio" name="approval_status" value="-1" '.( ($profile['approval_status'] == '-1') ? ' checked ': '').'> Reject
				<input type="radio" name="approval_status" value="0" '.( ($profile['approval_status'] == '0') ? ' checked ': '').'> Pending Approval
				<input type="radio" name="approval_status" value="1" '.( ($profile['approval_status'] == '1') ? ' checked ': '').'> Approve
			</fieldset>
			<fieldset>
                <legend>Active</legend>
				<input type="radio" name="is_active" value="1" '.( ($profile['is_active'] == '1') ? ' checked ': '').'> Yes
				<input type="radio" name="is_active" value="0" '.( ($profile['is_active'] == '0') ? ' checked ': '').'> No
			</fieldset>
			</td>
			<td>
			<fieldset class="email_message">
                <legend>Send a message to '.$profile['first_name'].' '.$profile['last_name'].' ('.$profile['email'].')</legend>
				<textarea name="email_message" style="width: 320px; height: 120px;"></textarea>
			</fieldset>
			</td>
			<td>
			<input type="submit" value="Save">
			</td>
		</tr>
		</table>
		</form>';

		echo '<div class="alert_bar_tall">'.$alert_message.'</div>';
	}

?>




<?php if ( $profile_found ) {  // IF: PROFILE FOUND ?>

	<script>
	/* Override Page Title with User Name */
	document.title="Landscape Profiles | <?php echo $profile_title; ?>";
	</script>

		<div id="profile_detail">
            <div class="profile_logo left">
            <?php
                if ( $profile['firm_logo'] != '' )
                    echo '<img src="'.get_template_directory_uri().'/img/spacer.gif" style="background-image:url('.$profile_images_path.$profile['firm_logo'].');" alt="'.$profile_title.' - '.$profile['firm_name'].'" />';
            ?>
            </div>
            <div class="profile_contact_information">
                <h1><?php echo $profile_title; ?></h1>
                <div class="profile_firm_name"><strong><?php echo $profile['firm_name'] ?></strong></div>
                <div class="profile_address"><?
					if ( trim($profile['address']) != '' )
						echo $profile['address'].', ';
					echo $profile['city'].', '.$profile['state'].' '.$profile['zip'];
					if ( ($profile['longitude'] != '0') && ($profile['latitude'] != '0') )
						echo ' (<a href="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q='.urlencode($profile['address'].','.$profile['city'].','.$profile['state'].' '.$profile['zip']).'&sll='.$profile['latitude'].','.$profile['longitude'].'&z=16" google_event_tag="Designer DB|Click|'.$profile_title.' - Map - Profile" target="_blank">view map</a>)';
					?></div>
                <div class="profile_phone_fax">
                <?php
                    if ( $profile['phone'] != '' )
                        echo 'phone: '.$profile['phone'];

                    if ( $profile['fax'] != '' )
                    {
                        if ( $profile['phone'] != '' )
                            echo ' &bull; ';
                        echo 'fax: '.$profile['fax'];
                    }
                ?>
                </div>
                <div class="profile_email_website">
                <?php
                    if ( $profile['email'] != '' )
                        echo  "<a href='mailto:".$profile['email']."'>".$profile['email']."</a>";

                    if ( $profile['website'] != '' )
                    {
                        if ( $profile['email'] != '' )
                            echo ' &bull; ';
                        echo '<a href="'.$profile['website'].'" google_event_tag="Designer DB|Click|'.$profile_title.' - Website" target="_blank">'.$profile['website'].'</a>';
                    }
                ?>
                </div>
            </div>
            <div class="profile_social_icons">
            <?php
                $profile_social_networks_query = "SELECT monrovia_profiles_social_networks.* FROM monrovia_profiles_social_networks JOIN monrovia_profiles WHERE (monrovia_profiles_social_networks.profile_id = monrovia_profiles.id) AND (monrovia_profiles.id='".sql_sanitize($profile['id'])."') GROUP BY monrovia_profiles_social_networks.id";
                $profile_social_networks_result = mysql_query($profile_social_networks_query);
                if ( mysql_num_rows($profile_social_networks_result) )
                {
                    while( $profile_social_network = mysql_fetch_array($profile_social_networks_result) )
                    {
                        if ( trim($profile_social_network['url']) != '' )
                        {
                            switch ($profile_social_network['social_network'])
                            {
                                case 'Facebook':
                                    echo '<div class="profile_social_icon"><a href="http://www.facebook.com/'.$profile_social_network['url'].'" target="_blank"><img src="'.get_template_directory_uri().'/img/spacer.gif" style="background:url('.get_template_directory_uri().'/img/icons_social.gif) -32px 0px;width:16px;height:16px;" title="'.$profile['firm_name'].' on Facebook!" /></a></div>';
                                    break;
                                case 'Twitter':
                                    echo '<div class="profile_social_icon"><a href="http://twitter.com/'.$profile_social_network['url'].'" target="_blank"><img src="'.get_template_directory_uri().'/img/spacer.gif" style="background:url('.get_template_directory_uri().'/img/icons_social.gif); width:16px;height:16px;" title="'.$profile['firm_name'].' on Twitter!" /></a></div>';
                                    break;
                                case 'YouTube':
                                    echo '<div class="profile_social_icon"><a href="http://www.youtube.com/'.$profile_social_network['url'].'" target="_blank"><img src="'.get_template_directory_uri().'/img/spacer.gif" style="background:url('.get_template_directory_uri().'/img/icons_social.gif) -16px 0px;width:16px;height:16px;" title="'.$profile['firm_name'].' on YouTube!" /></a></div>';
									break;
                                case 'LinkedIn':
                                    echo '<div class="profile_social_icon"><a href="http://www.linkedin.com/'.$profile_social_network['url'].'" target="_blank"><img src="'.get_template_directory_uri().'/img/spacer.gif" style="background:url('.get_template_directory_uri().'/img/icons_social.gif) -48px 0px;width:16px;height:16px;" title="'.$profile['firm_name'].' on LinkedIn!" /></a></div>';
                                    break;
								case 'Pinterest':
                                    echo '<div class="profile_social_icon"><a href="http://pinterest.com/'.$profile_social_network['url'].'" target="_blank"><img src="'.get_template_directory_uri().'/img/spacer.gif" style="background:url('.get_template_directory_uri().'/img/icons_social.gif) -64px 0px;width:16px;height:16px;" title="'.$profile['firm_name'].' on Pintrest" /></a></div>';
                                    break;                                    
                                case 'Houzz':
                                    echo '<div class="profile_social_icon"><a href="http://www.houzz.com/'.$profile_social_network['url'].'" target="_blank"><img src="'.get_template_directory_uri().'/img/spacer.gif" style="background:url('.get_template_directory_uri().'/img/icons_social.gif) -80px 0px;width:16px;height:16px;" title="'.$profile['firm_name'].' on Houzz!" /></a></div>';
                                    break;                                    
                            }
                        }
                    }
                }
            ?>
            </div>
            <?php
			if ( is_user_logged_in() && ($current_user->ID == $profile['user_id']) ) 
				{	echo '<div class="profile_edit_buttons clearfix">';
		            echo '<a href="/landscape-architects/edit-profile/" class="green-btn left">Edit your Profile</a>';

		if ( is_user_logged_in() && $current_user->ID == $profile['user_id'] && ($profile['approval_status'] < 1) && ($profile['is_submitted_for_approval'] == 0) ){
						echo '<a href="/landscape-architects/profiles/my-profile/?action=profile_submit" class="green-btn left">Submit for Approval</a>'; }
					echo '</div>';
				}
			?>
            <div style="clear:both"></div>

            <h2>Design Professional Details</h2>
            <div class="profile_details">
            	<div class="profile_details_information">
					<?php
                        if (trim($profile['membership_affiliation']) != '')
                            echo '<div class="profile_details_line_item"><label>Membership affiliation:</label><div class="profile_details_line_item_text">'.$profile['membership_affiliation'].'</div><br></div>';

                        if (trim($profile['specialty']) != '')
                            echo '<div class="profile_details_line_item"><label>Specialty:</label><div class="profile_details_line_item_text">'.$profile['specialty'].'</div><br></div>';

                        if (trim($profile['services']) != '')
                            echo '<div class="profile_details_line_item"><label>Services:</label><div class="profile_details_line_item_text">'.$profile['services'].'</div><br></div>';

                        if (trim($profile['expertise']) != '')
                            echo '<div class="profile_details_line_item"><label>Area(s) of Expertise:</label><div class="profile_details_line_item_text">'.$profile['expertise'].'</div><br></div>';

                        if (trim($profile['favorite_plant']) != '')
                            echo '<div class="profile_details_line_item"><label>My favorite Monrovia plant:</label><div class="profile_details_line_item_text">'.$profile['favorite_plant'].'</div><br></div>';

                        if (trim($profile['favorite_plant_why']) != '')
                            echo '<div class="profile_details_line_item"><label>Why it\'s my favorite:</label><div class="profile_details_line_item_text">'.$profile['favorite_plant_why'].'</div><br></div>';
                    ?>
                </div>
            	<div class="profile_details_images left">
                	<?php
						$profile_images_query = "SELECT monrovia_profiles_images.* FROM monrovia_profiles_images JOIN monrovia_profiles WHERE (monrovia_profiles_images.profile_id = monrovia_profiles.id) AND (monrovia_profiles.id='".sql_sanitize($profile['id'])."') GROUP BY monrovia_profiles_images.id ORDER BY monrovia_profiles_images.id";
						$profile_images_result = mysql_query($profile_images_query);
						if ( mysql_num_rows($profile_images_result) )
						{ ?>
                        <!-- Start Mini Slideshow Markup -->
                        <div class="slide-wrap mini">
            				<div class="cycle-slideshow"
                            data-cycle-slides="> a"
    						data-cycle-fx="scrollHorz"
    						data-cycle-pause-on-hover="true"
    						data-cycle-speed="1000"
                            data-cycle-timeout="8000"
                			data-cycle-swipe=true
                			data-cycle-pager=".cycle-pager"
                            data-cycle-auto-height="false"
    						data-cycle-pager-template="<span></span>"
    						>
							<?php while( $profile_image = mysql_fetch_array($profile_images_result) )
							{
								echo "<a><img src='".$profile_images_path.$profile_image['path_thumbnail']."' title='' /></a>";
							} ?>
                       		</div><!-- end cycle-slideshow --> 
                                 <div class="cycle-pager">
                        		</div><!-- end cycle pager -->    
                       </div><!-- slide-wrap -->    
                       <!-- End Mini Slideshow -->
					<?php	}
					?>
                </div>
                <div class="clear"></div>
            </div>

            <?php
                if ( trim($profile['profile']) != '' )
                    echo '
                    <div class="profile_description">
                    <label>Profile:</label>
                    '.nl2br($profile['profile']).'
                    </div>';

            ?>
		</div>

        <?php } // END IF: PROFILE FOUND ?>