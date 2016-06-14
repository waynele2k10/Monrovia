<?php
	/*
		THE PURPOSE OF THIS SCRIPT IS TO POPULATE A PLANT RECORD OBJECT WITH INFORMATION POSTED TO THE BACKEND FORM
	*/
	require_once('../inc/class_plant.php');


	// ENSURE USER HAS PERMISSIONS
	$record = new plant($_POST['id']);

	//$record->info = array_merge($record->info,$_POST['plant']); // WE NEED TO MANIPULATE VALUES FIRST. ARRAY_WALK DOESN'T WORK ON STAGING SERVER
	$fields = explode(',',$record->table_fields);
	for($i=0;$i<count($fields);$i++){
		$field_value = stripslashes(isset($_POST['plant'][$fields[$i]])?$_POST['plant'][$fields[$i]]:'');
		$field_value = str_replace('  ',' ',$field_value);

		// UPDATE FIELD ONLY IF INFO PROVIDED (THIS PREVENTS HISTORICAL DATA FROM BEING WIPED OUT). ALWAYS UPDATE BOOLEANS
		$update_field = (isset($_POST['plant'][$fields[$i]])||strpos($fields[$i],'is_')===0);
		if($update_field) $record->info[$fields[$i]] = $field_value;
	}

	$record->info['types'] = multiselect_to_attributes(isset($_POST['plant']['types'])?$_POST['plant']['types']:'');
	$primary_type_id = $_POST['plant']['primary_type_id'];
	if($primary_type_id!=''){
		$in_list = false;
		for($i=0;$i<count($record->info['types']);$i++){
			if($record->info['types'][$i]->id==$primary_type_id){
				$record->info['types'][$i]->is_primary = '1';
				$in_list = true;
			}else{
				$record->info['types'][$i]->is_primary = '0';
			}
		}
		if(!$in_list) $record->info['types'][] = new plant_attribute($primary_type_id,null,null,1);
	}

	$record->info['flowering_seasons'] = multiselect_to_attributes(isset($_POST['plant']['flowering_seasons'])?$_POST['plant']['flowering_seasons']:'');
	$record->info['flower_attributes'] = multiselect_to_attributes(isset($_POST['plant']['flower_attributes'])?$_POST['plant']['flower_attributes']:'');
	$record->info['sun_exposures'] = multiselect_to_attributes(isset($_POST['plant']['sun_exposures'])?$_POST['plant']['sun_exposures']:'');
	if(isset($_POST['plant']['sunset_zones'])) $record->info['sunset_zones'] = $_POST['plant']['sunset_zones'];
	$record->info['growth_habits'] = multiselect_to_attributes(isset($_POST['plant']['growth_habits'])?$_POST['plant']['growth_habits']:'');

	$record->info['garden_styles'] = id_csv_to_attributes(isset($_POST['plant']['garden_style_ids'])?$_POST['plant']['garden_style_ids']:'');
	$record->info['special_features'] = id_csv_to_attributes(isset($_POST['plant']['special_feature_ids'])?$_POST['plant']['special_feature_ids']:'');
	$record->info['problem_solutions'] = id_csv_to_attributes(isset($_POST['plant']['problem_solution_ids'])?$_POST['plant']['problem_solution_ids']:'');
	$record->info['landscape_uses'] = id_csv_to_attributes(isset($_POST['plant']['landscape_use_ids'])?$_POST['plant']['landscape_use_ids']:'');

	// ATTRIBUTES
	/*
	$record->attributes = array();
	$attribute_ids = explode(',',$_POST['plant']['attribute_ids']);
	for($i=0;$i<count($attribute_ids);$i++){
		$record->attributes[] = new plant_attribute($attribute_ids[$i],null,,($_POST['plant_attributes_primary']==$attribute_ids[$i])?1:0);
	}
	*/
	/*
	// COMPANION PLANTS
	$record->companion_plant_ids = array();
	$companion_plant_ids = explode(',',$_POST['plant']['companion_plant_ids']);
	for($i=0;$i<count($companion_plant_ids);$i++){
		$record->companion_plant_ids[] = $companion_plant_ids[$i];
	}
	*/

	//Add null for this if empty, required by primitive spark
	if ( $record->info['special_third_party'] == "" ){
		$record->info['special_third_party'] = null;
	}

	$record->info['last_modified'] = date('Y-m-d H:i:s');
	$record->info['last_modified_by_user_id'] = $current_user->ID;
	$record->info['synch_with_hort'] = '1'; // FLAG PLANT TO BE ADDED TO XML FILE
	$record->info['synch_with_mage'] = '1'; // Flag plant to be sync with magento

	//var_dump($_POST);
	//var_dump($record);exit;

	$success = $record->save();

	// UPDATE KEYWORDS
	$keywords = $record->compile_keywords();
	$records->info['keywords'] = $keywords;
	mysql_query("UPDATE plants SET keywords='".sql_sanitize($keywords)."' WHERE id=".$record->info['id']);

	if($success){
		output_page_notice('Your changes have been saved.');
	}else{
		output_page_notice('ERROR: An error occurred and your changes may not have been saved.');
	}
	//IF PLANT WAS UPDATED, CHECK FOR IT IN THE LOG DATABASE
	if(isset($_GET['id'])){
		$item = $_GET['id'];
		$plant = mysql_query("SELECT common_name FROM plants WHERE is_active = 1 AND release_status_id IN (1,2,3,4) AND `id` = '$item'");
		if(mysql_num_rows($plant) > 0){

			//ITS ACTIVE, SO MAKE SURE ITS IN THE LOG TABLE, IF NOT ALREADY
			$log = 	mysql_query("SELECT * FROM wp_relevanssi_log WHERE `ip` = '$item'");
			if(mysql_num_rows($log) < 1){
				echo 'Its Active, Added to Auto-Suggest!';
				$date = date('Y-m-d H:i:s');
				$name = mysql_result($plant, 0);
				$array = array('{{#174}}', '{{#153}}');
				$name =  str_replace($array, '', $name);
				$name  = mysql_real_escape_string($name); 
			mysql_query("INSERT INTO wp_relevanssi_log ( `query`, `hits`, `time`, `user_id`, `ip`) VALUES ( '$name', '1000', '$date', '0', '$item')");
			//echo mysql_insert_id();
			}
		// ITS NOT ACTIVE, SO MAKE SURE ITS NOT IN THE LOG TABLE. IF SO, REMOVE IT	
		} else{
			$log = 	mysql_query("SELECT * FROM wp_relevanssi_log WHERE `ip` = '$item'");
			if(mysql_num_rows($log) > 0){
				echo 'Not Active, Removed from Auto-Suggest!';
				mysql_query("DELETE FROM wp_relevanssi_log WHERE `ip` = '$item'");
			}
		}
		
	}
	// www.monrovia.com ONLY--NEVER USE STAGING OR DEV SERVERS


	// INVOKE SYNCH WITH HORT
	// $item = $_GET['id'];
	
        // get_url_async(array('http://www.monrovia.com/inc/crons/horticultural_printers_sync.php?synch=1'));
	
        // www.monrovia.com ONLY--NEVER USE STAGING OR DEV SERVERS
	// UPDATE THE RELEVANSSI HITS TABLE

	$record = new plant($record->info['id']);	// RELOAD WITH NEW INFO
?>