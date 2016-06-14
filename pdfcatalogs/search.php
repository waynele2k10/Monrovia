<?php
require( '../wp-load.php' );
include_once( '../wp-config.php');

	header('Content-Type: text/javascript');

	//require_once($_SERVER['DOCUMENT_ROOT'].'/inc/init.php');
	
	require_once('../wp-content/themes/monrovia/includes/classes/class_plant.php');
	require_once('../wp-content/themes/monrovia/includes/classes/class_search_plant.php');

	function add_one_to_many_criterion($list_name){
		global $search;
		$raw_list = '';
		if(isset($_GET[$list_name])) $raw_list = $_GET[$list_name];
		$search->criteria[$list_name.'s'] = array();
		if($raw_list!=''){
			$list = explode(',',$raw_list);
			for($i=0;$i<count($list);$i++){
				if($list[$i]!='') $search->criteria[$list_name.'s'][] = $list[$i];
			}
		}
	}

	$search = new search_plant('','id,is_active,item_number,common_name,botanical_name,collection_name,cold_zone_low,cold_zone_high');
	$search->order_by = 'botanical_name ASC';
	if ( isset($_GET['res_per_page']) ){	
		$search->results_per_page = $_GET['res_per_page'];
	}else{
		$search->results_per_page = 25;
	}	
	$search->max_pagination_links = 8;

	for($i=0;$i<count($search->plant_fields);$i++){
		$field_name = $search->plant_fields[$i];
		$field_values_list = '';
		if(isset($_GET[$search->plant_fields[$i]])) $field_values_list = $_GET[$search->plant_fields[$i]];
		if($field_values_list!=''){
			$field_values = explode(',',$field_values_list);
			$search->criteria[$field_name] = $field_values;
		}
	}

	// COLD ZONES
	$cold_zones_list = '';
	if(isset($_GET['cold_zone'])) $cold_zones_list = $_GET['cold_zone'];
	if($cold_zones_list!=''){
		$cold_zones = explode(',',$cold_zones_list);
		$cold_zone_low = 11;
		$cold_zone_high = 0;
		for($i=0;$i<count($cold_zones);$i++){
			$cold_zone_low = min($cold_zone_low,intval($cold_zones[$i]));
			$cold_zone_high = max($cold_zone_high,intval($cold_zones[$i]));
		}
		$search->criteria['cold_zone_low'] = $cold_zone_low;
		$search->criteria['cold_zone_high'] = $cold_zone_high;
	}

	// ONE-TO-MANY CRITERIA
	add_one_to_many_criterion('type');
	add_one_to_many_criterion('sun_exposure');
	
	// COLLECTION NAMES
	if(isset($_GET['collection_names'])){
		$search->criteria['collection_names'] = explode(',',$_GET['collection_names']);
	}

	$search->results_start_page = max((isset($_GET['start_page'])?intval($_GET['start_page']):1),1);

	$search->criteria['is_active'] = '1';
	
	// THESE RELEASE STATUSES ARE ALLOWED IN CUSTOM CATALOGS: A (Active), NA (New/Active), NI (New/Inactive), F (Future)
	$search->criteria['release_status_id'] = array('1','2','3','6');

	$search->search(false);

	for($i=0;$i<count($search->results);$i++){
		// UNESCAPE SPECIAL CHARACTERS
		$search->results[$i]->info['common_name'] = (unescape_special_characters($search->results[$i]->info['common_name']));
		$search->results[$i]->info['botanical_name'] = (unescape_special_characters($search->results[$i]->info['botanical_name']));
		
		// GET ADDITIONAL INFO
		//$search->results[$i]->get_primary_image();
		$search->results[$i]->get_types();
		$search->results[$i]->get_sun_exposures();
		unset($search->results[$i]->table_fields);
		unset($search->results[$i]->table_name);
	}
	
	// UNSET UNNEEDED INFO MEMBERS TO REDUCE TRAFFIC
	unset($search->criteria);
	unset($search->order_by);
	unset($search->plant_fields);
	unset($search->result_fields);
	unset($search->results_per_page);
	
	echo(to_json($search));
?>