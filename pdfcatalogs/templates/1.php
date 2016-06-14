<?php
	// INCLUDE A PHP FILE ONLY IF FIELDS IN A TEMPLATE XML FILE NEED SPECIAL LOGIC
	
	function before_output_field($catalog_info,$field_id,$field_value){
		$location_info = array(
			'azusa'=>array(
				'title'		=> 'AZUSA (Corporate Headquarters)',
				'address_1'	=> '817 E. Monrovia Place',
				'address_2'	=> 'Azusa, CA 91702',
				'phone'		=> '(800) 999-9321',
				'fax'		=> '(626) 334-3126'
			),
			'visalia'=>array(
				'title'		=> 'VISALIA & VENICE HILLS',
				'address_1'	=> '32643 Road 196',
				'address_2'	=> 'Woodlake, CA 93286',
				'phone'		=> '(800) 449-9321',
				'fax'		=> '(559) 564-5820'
			),
			'dayton'=>array(
				'title'		=> 'DAYTON',
				'address_1'	=> '13455 S.E. Lafayette Hwy',
				'address_2'	=> 'Dayton, OR 97114',
				'phone'		=> '(800) 666-9321',
				'fax'		=> '(503) 868-7352'
			),
			'cairo'=>array(
				'title'		=> 'CAIRO',
				'address_1'	=> '1579 Highway 111 South',
				'address_2'	=> 'Cairo, GA 39828',
				'phone'		=> '(800) 342-6012',
				'fax'		=> '(229) 377-9394'
			),
			'granby'=>array(
				'title'		=> 'GRANBY',
				'address_1'	=> '90 Salmon Brook St',
				'address_2'	=> 'Granby, CT 06035',
				'phone'		=> '(860) 653-4541'
			)
		);

		// THIS METHOD ALLOWS MANIPULATION OF THE VALUE BEFORE IT IS RENDERED
		switch($field_id){
			case 'front_cover_customer_contact':
				if($field_value!='') $field_value = 'For ' . $field_value;
			break;
		}
		
		// LOCATIONS
		if(isset($catalog_info['monrovia_locations'])&&$catalog_info['monrovia_locations']!=''&&strpos($field_id,'back_cover_location_')===0){
			$location_num = intval(substr($field_id,strlen('back_cover_location_'),1));
			$locations = explode(',',$catalog_info['monrovia_locations']);
			$field_value = '';
			if($location_num<=count($locations)){
				$location_num--;
				$db_field_id = substr($field_id,strlen('back_cover_location_')+2);
				if(isset($location_info[$locations[$location_num]][$db_field_id])) $field_value = $location_info[$locations[$location_num]][$db_field_id];
			}			
		}
		
		return $field_value;
	}
	function before_output_img($catalog_info,$field_id,$attributes){
		// THIS METHOD ALLOWS MANIPULATION OF THE IMAGE PROPERTIES BEFORE IT IS RENDERED
		switch($field_id){
			case 'front_cover_plant_1':
			case 'front_cover_plant_2':
			case 'front_cover_plant_3':
				if($field_id=='front_cover_plant_1') $image_number = 1;
				if($field_id=='front_cover_plant_2') $image_number = 2;
				if($field_id=='front_cover_plant_3') $image_number = 3;
				//$aspect_ratio = floatval($attributes['width_pct']) / floatval($attributes['height_pct']);
				$width = 400; $height = 400; // ARBITRARY IMAGE WIDTH AND HEIGHT. SEEMS LIKE A PRETTY GOOD NUMBER. THE HIGHER THE NUMBER, THE BETTER THE QUALITY
				//$height = floor($width/$aspect_ratio);				
				$image_set_id = 0;
				
				if(isset($catalog_info['plant_'.$image_number.'_image_set_id'])) $image_set_id = intval($catalog_info['plant_'.$image_number.'_image_set_id']);
						
				$choose_random_image = ($image_set_id==0);
				
				// MAKE SURE IMAGE SET IS STILL ACTIVE AND DISTRIBUTABLE
				if($image_set_id!=''){
					$result = mysql_query('SELECT COUNT(*) AS is_still_usable FROM plant_image_sets WHERE is_active=1 AND is_distributable=1 AND (expiration_date>NOW() OR expiration_date = "0000-00-00") AND id="'.$image_set_id.'"');
					$choose_random_image = (mysql_result($result,0,'is_still_usable')===0);
				}
				
				// CHOOSE A RANDOM IMAGE
				if($choose_random_image){
					// THESE RELEASE STATUSES ARE ALLOWED IN CUSTOM CATALOGS: A (Active), NA (New/Active), NI (New/Inactive), F (Future)
					$result = mysql_query('SELECT plant_image_sets.id FROM plant_image_sets INNER JOIN plants ON plants.id = plant_image_sets.plant_id WHERE plant_image_sets.is_active=1 AND plant_image_sets.is_distributable=1 AND (plant_image_sets.expiration_date>NOW() OR plant_image_sets.expiration_date = "0000-00-00") AND plants.is_active=1 AND plants.release_status_id IN (1,2,3,6) ORDER BY RAND() LIMIT 1');
					$image_set_id = mysql_result($result,0,"id");
					$attributes['thumbnail_src'] = '/pdfcatalogs/assets/random_plant.jpg';
				}
				$attributes['src'] = 'http://'.$_SERVER['HTTP_HOST'] . '/wp-content/uploads/plants/image_set_thumbail.php?id=' . $image_set_id . '&width=' . $width . '&height=' . $height;
			break;
		}
		return $attributes;
	}
?>