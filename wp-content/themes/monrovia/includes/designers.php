<?php

	//die(ini_set('upload_max_filesize','1M').'!');
	//die(ini_get('post_max_size').'!');

	function get_designers_search_radius_options()
	{
		return array(10, 25, 50, 100);
	}

	function get_designers_membership_affiliation_options()
	{
		return array('ANLA', 'ASLA', 'APLD', 'GCA', 'PLANET', 'USGBC');
	}

	function get_designers_specialty_options()
	{
		return array('Commercial', 'Residential', 'Commercial and Residential');
	}

	function get_designers_expertise_options()
	{
		return array('Asian / Zen','Conservatory / Indoor / Tropical','Contemporary','Cottage','Desert / Mediterranean / Water-efficient Garden','Edible for humans or wildlife','Estate - 5 acres or larger','Green Roofs','Green Wall / Vertical Gardening','Rock Garden / Alpine','Streetscapes / Medians','Sustainable / Permaculture / Native','Urban / Small Space / Containers','Water Garden / Pond / Coastal','Woodland / Rustic / Natural');
	}

	function get_designers_services_options()
	{
		return array('Design', 'Design/Build', 'Installation', 'Maintenance');
	}

	function get_designers_social_networks_options()
	{
		return array('Facebook', 'Twitter', 'YouTube', 'LinkedIn', 'Pinterest', 'Houzz');
	}

	function get_designers_countries_options()
	{
		return array('USA'=>'United States of America', 'CAN'=>'Canada');
	}

	function get_designers_dropdown_options($type, $selected_value=NULL, $use_keys_as_values=false)
	{
		$options = array();
		switch ( $type )
		{
			case 'membership_affiliation':
				$options = get_designers_membership_affiliation_options();
				break;
			case 'specialty':
				$options = get_designers_specialty_options();
				break;
			case 'expertise':
				$options = get_designers_expertise_options();
				break;
			case 'services':
				$options = get_designers_services_options();
				break;
			case 'social_networks':
				$options = get_designers_social_networks_options();
				break;
			case 'countries':
				$options = get_designers_countries_options();
				$use_keys_as_values = true;
				break;
		}

		$return = '';
		foreach ( $options as $key=>$option )
		{
			$value = ($use_keys_as_values) ? $key : $option;
			if ( (! is_null($selected_value)) && ($selected_value == $option) )
				$return .= '<option value="'.$value.'" selected>'.$option.'</option>';
			else
				$return .= '<option value="'.$option.'">'.$option.'</option>';
		}

		return $return;
	}

	function display_designers_dropdown_options($type, $selected_value=NULL, $use_keys_as_values=false)
	{
		echo get_designers_dropdown_options($type, $selected_value, $use_keys_as_values);
	}

	function display_designers_checkboxes($type, $field_name, $selected_options=NULL)
	{
		switch ( $type )
		{
			case 'expertise':
				$options = get_designers_expertise_options();
				break;
			case 'membership_affiliation':
				$options = get_designers_membership_affiliation_options();
				break;
			case 'services':
				$options = get_designers_services_options();
				break;
		}

		$i=0;
		foreach ( $options as $option )
		{			
			$checked = ( is_array($selected_options) && (array_search($option, $selected_options) !== false) ) ? ' checked' : '';
			
			echo '<div class="checkbox_and_label"><input type="checkbox" name="'.$field_name.'[]" id="'.$field_name.'_'.$i.'" value="'.$option.'" '.$checked.'><label for="'.$field_name.'_'.$i.'">'.$option.'</label></div>';
			$i++;
		}
	}

	function designers_resize_image($src_path, $dest_path, $max_width=379, $max_height=340, $add_watermark=false)
	{
		$dest_filename = $dest_path;
		$src = @imagecreatefromjpeg($src_path);
		if(!$src) return false;
		$src_width = imagesx($src);
		$src_height = imagesy($src);

		if($src_width > $src_height) {
			$dest_width = $max_width;
			$dest_height = $src_height * ($dest_width/$src_width);
		} else {
			$dest_height = $max_height;
			$dest_width = $src_width * ($dest_height/$src_height);
		}

		if($dest_height > $max_height) {
			$dest_height = $max_height;
			$dest_width = (int)($src_width * ($max_height/$src_height));
		}

		if($dest_width > $max_width) {
			$dest_width = $max_width;
			$dest_height = $dest_height * ($max_width/$dest_width);
		}

		$dest= imagecreatetruecolor($dest_width, $dest_height);

		//if($overwrite == true) {
			if(file_exists($dest_filename)) {
				unlink($dest_filename);
			}
		//}

		imagecopyresampled($dest, $src, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);

		if ( false ) // if ( $add_watermark )
		{
			// ADD WATERMARK
			$overlay = imagecreatefrompng($_SERVER["DOCUMENT_ROOT"] . "/img/watermark_monrovia.png");
			$overlay_width = imagesx($overlay);
			$overlay_height = imagesy($overlay);
			$offset_x = ($dest_width/2) - ($overlay_width/2);
			//$offset_y = $dest_height - $overlay_height - ($dest_height * 0.05);
			$offset_y = $dest_height - $overlay_height - 5;

			imagecopyresampled($dest,$overlay,$offset_x,$offset_y,0,0,$overlay_width,$overlay_height,$overlay_width,$overlay_height);
		}

		imagejpeg($dest,$dest_filename,100);
	}

?>