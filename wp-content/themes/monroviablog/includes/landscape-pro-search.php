<?php

	// Define static array of options
	$options_radius = array(10, 25, 50, 100);
	$options_specialty = get_designers_specialty_options();
	$options_experise = get_designers_expertise_options();
	
	//If Form was submitted, get the values
	if(isset($_POST['search_city'])) $search_city = $_POST['search_city']; else $search_city ='Enter zip or city & state';
	if(isset($_POST['search_specialty'])) $search_specialty = $_POST['search_specialty'];
	if(isset($_POST['search_expertise'])) $search_expertise = $_POST['search_expertise'];
	
?>
<div>
    <form method="post" action="" name="designers_search_form" id="designers_search_form" class="designers_search_form" onSubmit="return validateForm(this);">
    <table border="0" cellpadding="5" cellspacing="0">
    <tr>
        <td><label for="search_city" class="req">Find</label>
        <div>
        <input type="text" name="search_city" id="search_city" value="<?php echo $search_city; ?>" onfocus="if (this.value == 'Enter zip or city & state') this.value='';" onblur="if (this.value == '') this.value='Enter zip or city & state';" />
        </div></td>

        <td><label for="search_specialty">Specialty</label>
        <div class="select-wrap">
        	<select name="search_specialty" class="field_select field_specialty">
            <option value="">-----------------------</option>
        						
            <?php
				foreach ( $options_specialty as $option )
				{
					if ( $option == $search_specialty )
						echo '<option value="'.$option.'" selected>'.$option.'</option>';
					else
						echo '<option value="'.$option.'">'.$option.'</option>';
				}
			?>
        </select></div></td>
        <td><label for="search_expertise">Expertise</label>
        <div class="select-wrap">
        	<select name="search_expertise" class="field_select field_expertise">
            <option value="">-----------------------</option>
            <?php
				foreach ( $options_experise as $option )
				{
					if ( $option == $search_expertise )
						echo '<option value="'.$option.'" selected>'.$option.'</option>';
					else
						echo '<option value="'.$option.'">'.$option.'</option>';
				}
			?>
        </select></div></td>
        <td>&nbsp;
        	<input type="submit" class="green-btn small" value="Go" />
        </td>
    </tr>
    </table>
    </form>
</div>
<script type="text/javascript">
	function validateForm(form){
		
		// Get Form Variable
		var city = form.search_city.value;
		if(city == "" || city == "Enter zip or city & state"){
			jQuery('#search_city').addClass('form-error');
			return false;
		} else {
			jQuery('#search_city').removeClass('form-error');
			return true;
		}
	}

</script>

            <?php
			
				$filter_ok = false;

				if ($search_city && $search_city != '' && $search_city != 'Enter zip or city & state')
				{

					// GEO CODING THE ORIGIN LOCATION
					$geocode_result = do_geocode($search_city);
					if($geocode_result['success']){
						$latitude = $geocode_result['lat_long'][0];
						$longitude = $geocode_result['lat_long'][1];
                        $filter_ok = true;
					}
				}
						

				// ORIGIN GEO CODING SUCCESSFUL
				if ( $filter_ok )
				{
					$profiles_filter_query = '';
					
					// FILTER BY SPECIALTY
					if ($search_specialty && $search_specialty != ''){
						if($search_specialty=='Commercial and Residential'){
							$profiles_filter_query .= " AND (monrovia_profiles.specialty IN ('Commercial and Residential','Commercial','Residential')) ";						
						}else{
							$profiles_filter_query .= " AND (monrovia_profiles.specialty IN ('".$search_specialty."','Commercial and Residential')) ";						
						}					
					}
					
					// FILTER BY EXPERTISE
					if ( $search_expertise && $search_expertise != '')
						$profiles_filter_query .= " AND (monrovia_profiles.expertise LIKE '%".$search_expertise."%') ";
					
					$radius = isset($_GET['search_radius']) ? (int)$_GET['search_radius'] : 25;
					
					// NARROWING THE LAT/LON SQUARE FOR OPTIMIZATION
					$sq_longitude_1 = $longitude-$radius/abs(cos(deg2rad($latitude))*69);
					$sq_longitude_2 = $longitude+$radius/abs(cos(deg2rad($latitude))*69);
					$sq_latitude_1 = $latitude-($radius/69);
					$sq_latitude_2 = $latitude+($radius/69);
					
					// BUILDING QUERY FOR DISTANCE
					//$profiles_query = "SELECT monrovia_profiles.*, 3956 * 2 * ASIN(SQRT(POWER(SIN((".$latitude." - ABS(monrovia_profiles.latitude)) * pi()/180 / 2), 2) + COS(".$latitude." * pi()/180) * COS(ABS(monrovia_profiles.latitude) * pi()/180) * POWER(SIN((".$longitude." - monrovia_profiles.longitude) * pi()/180 / 2), 2) )) AS distance FROM monrovia_profiles JOIN monrovia_users WHERE (monrovia_profiles.user_id = monrovia_users.id) AND (monrovia_profiles.is_active='1') AND (monrovia_profiles.approval_status='1') AND (monrovia_users.is_active='1') and (monrovia_profiles.longitude BETWEEN ".$sq_longitude_1." and ".$sq_longitude_2.") AND (monrovia_profiles.latitude BETWEEN ".$sq_latitude_1." AND ".$sq_latitude_2.") ".$profiles_filter_query." HAVING distance < 25 ORDER BY distance";
					//$profiles_query = "SELECT monrovia_profiles.*, 3956 * 2 * ASIN(SQRT(POWER(SIN((".$latitude." - ABS(monrovia_profiles.latitude)) * pi()/180 / 2), 2) + COS(".$latitude." * pi()/180) * COS(ABS(monrovia_profiles.latitude) * pi()/180) * POWER(SIN((".$longitude." - monrovia_profiles.longitude) * pi()/180 / 2), 2) )) AS distance FROM monrovia_profiles JOIN monrovia_users WHERE (monrovia_profiles.user_id = monrovia_users.id) AND (monrovia_profiles.is_active='1') AND (monrovia_profiles.approval_status='1') AND (monrovia_users.is_active='1') and (monrovia_profiles.longitude BETWEEN ".$sq_longitude_1." and ".$sq_longitude_2.") AND (monrovia_profiles.latitude BETWEEN ".$sq_latitude_1." AND ".$sq_latitude_2.") ".$profiles_filter_query." HAVING distance < ".$radius." ORDER BY distance";
					$profiles_query = "SELECT monrovia_profiles.*, 3956 * 2 * ASIN(SQRT(POWER(SIN((".$latitude." - ABS(monrovia_profiles.latitude)) * pi()/180 / 2), 2) + COS(".$latitude." * pi()/180) * COS(ABS(monrovia_profiles.latitude) * pi()/180) * POWER(SIN((".$longitude." - monrovia_profiles.longitude) * pi()/180 / 2), 2) )) AS distance FROM monrovia_profiles JOIN wp_users WHERE (monrovia_profiles.user_id = wp_users.id) AND (monrovia_profiles.is_active='1') AND (monrovia_profiles.approval_status='1') AND (monrovia_profiles.longitude BETWEEN ".$sq_longitude_1." and ".$sq_longitude_2.") AND (monrovia_profiles.latitude BETWEEN ".$sq_latitude_1." AND ".$sq_latitude_2.") ".$profiles_filter_query." HAVING distance < ".$radius." ORDER BY distance";
					//$profiles_query .= " LIMIT 10";					

					$profiles_result = mysql_query($profiles_query);
					if ( mysql_num_rows($profiles_result) )
					{
						echo "<h2>Your search returned ".mysql_num_rows($profiles_result)." results</h2>";
						
						while ( $profile = mysql_fetch_array($profiles_result) )
						{
							// Get WP Upload Directory
							$upload_dir = get_site_url();
							// Set the Designer Profiles Image Directory
							$profile_images_path =  $upload_dir.'/wp-content/designers/profiles/'.$profile['user_id'].'/';
							
							$line_1_name = $profile['first_name'].' '.$profile['last_name'];

							$map_link = '';
							if ( ($profile['longitude'] != '0') && ($profile['latitude'] != '0') )
								$map_link =  ' (<a href="http://maps.google.com/maps?f=q&source=s_q&hl=en&geocode=&q='.urlencode($profile['address'].','.$profile['city'].','.$profile['state'].' '.$profile['zip']).'&sll='.$profile['latitude'].','.$profile['longitude'].'&z=16" google_event_tag="Designer DB|Click|'.$line_1_name.' - Map" target="_blank">view map</a>)';
							//get an image
							$profile_images_query = "SELECT monrovia_profiles_images.* FROM monrovia_profiles_images JOIN monrovia_profiles WHERE (monrovia_profiles_images.profile_id = monrovia_profiles.id) AND (monrovia_profiles.id='".sql_sanitize($profile['id'])."') GROUP BY monrovia_profiles_images.id ORDER BY monrovia_profiles_images.id LIMIT 1";
							$profile_images_result = mysql_query($profile_images_query);
							if ( mysql_num_rows($profile_images_result) ){
								$thumbnail_img =  mysql_fetch_array($profile_images_result);
								$line_0 = '<div style="background-image:url('.$profile_images_path.$thumbnail_img["path_thumbnail"].');" class="profile_result_img" ></div>';
							}else{
								$line_0 = '<div style="" class="profile_result_img clear" ></div>';
							}


							$line_1 = '<a href="/landscape-architects/profiles/'.$profile['url_key'].'" class="search_result_item_title">'.$line_1_name.'</a>';
							$line_2 = '<span class="search_result_item_firm_name">'.$profile['firm_name'].'</span><br/> ';
							if ( trim($profile['address']) != '' )
								$line_2 .= $profile['address'].', ';
							$line_2 .= $profile['city'].', '.$profile['state'].' '.$profile['zip'].$map_link;
							
							$line_3 = '';
							if ( $profile['phone'] != '' )
								$line_3 .= 'Phone: '.$profile['phone'];
								
							if ( $profile['fax'] != '' )
							{
								if ( $line_3 != '' )
									$line_3 .= ' &bull; ';
								$line_3 .= 'Fax: '.$profile['fax'];
							}
							
							if ( $profile['email'] != '' )
							{
								if ( $line_3 != '' )
									$line_3 .= ' &bull; ';
								$line_3 .= "<a href='mailto:".$profile['email']."'>".$profile['email']."</a>";
							}
							
							$distance = round($profile['distance'], 2).' miles';
							
							echo '
							<div class="search_result_item">
								<div class="search_result_item_left clear">
									'.$line_0.'
									<div class="profile-result-inner left">
										<div>'.$line_1.'</div>
										<div>'.$line_2.'</div>
										<div>'.$line_3.'</div>
									</div>
								</div>
								<div class="search_result_item_right">
								'.$distance.'
								<div class="view_profile_wrap"><a class="green-btn" href="/landscape-architects/profiles/'.$profile['url_key'].'">view profile</a></div>
								</div>
								<div class="clear"></div>
							</div>';
						}
					}
					else
						echo '<div class="designers_search_results_no_results">There are no designers matching your criteria.<br><br><b>Monrovia\'s <i>Find a Design Professional</i> directory is new, and more designers are signing up every day. Please either expand your search or check back again soon for new listings.</b></div>';
				}
				
			?>
			
            <div class="disclaimer">NOTE: Monrovia is providing the "find a design professional" as a service, but makes no guarantee of each participant's quality or workmanship. We highly recommend that you prepare a list of questions and interview more than one design professional before making a selection. </div>