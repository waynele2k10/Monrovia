<?php
set_time_limit(600);
include_once( '../../wp-config.php');
require_once('../class_plant.php');
ini_set('display_errors', 'on');
error_reporting(E_ALL);

function generate_xml($full_synch) {
	global $wpdb;
	$table_name = 'plants_sync';
    set_time_limit(3600); // ALLOW UP TO AN HOUR

    $xml_data = "";
	$_sql = "SELECT id FROM plants WHERE synch_with_hort=1 AND item_number <> '' AND release_status_id IN (1,2,3,4,6) ORDER BY last_modified DESC LIMIT 10";
    
    $result = mysql_query($_sql);

    $num_rows = mysql_num_rows($result);
    $id = array();
    if (count($num_rows) > 0) {

        for ($i = 0; $i < $num_rows; $i++) {
			$id[] = $plantID = mysql_result($result, $i, 'id');
			$wpdb->delete( $table_name, array( 'plantID' => $plantID ) );
			echo $plantID.'<br>';
			
            $plant = new plant($plantID);
            $label_info_array = get_plant_label_info($plant->info['item_number'], $plantID);
            foreach ($label_info_array as $label_info) {
                $item_size_id = $label_info['item_size_id'];
				echo $item_size_id.'<br>';
				$item_number = $plant->info['item_number'];
				echo $item_number.'<br>';
				$special_icon = '';
                if ($plant->info['is_new'] == '1')
                    $special_icon = 'New';
				$size = $label_info['size'];
				$form = $label_info['form'];
				$botanical_name = $plant->info['botanical_name'];
				$botanical_name_short = get_short_botanical_name($plant->info['botanical_name']);
				$common_name = $plant->info['common_name'];
				$primary_attribute = $plant->info['primary_attribute'];
				$average_landscape_size = _xml_sanitize(strip_tags($label_info['average_landscape_size']));
				$care_instructions = _xml_sanitize(strip_tags($label_info['care_instructions']));
				$description_companion_plants = strip_tags($label_info['description_companion_plants']);
				if ($average_landscape_size == '') {
					$average_landscape_size = $plant->info['average_landscape_size'];
				} else {
					$average_landscape_size = strip_tags($label_info['average_landscape_size']);
				}
				if ($care_instructions == "") {
					$care_instructions = $plant->info['description_care'];
				} else {
					$care_instructions = strip_tags($label_info['care_instructions']);
				}
				$description_care = $plant->info['description_care'];
				$container_size = $label_info['container_size'];
				$volume = $label_info['volume'];
				$upc = $label_info['upc'];
				$flowering_time = $plant->info['flowering_time'];
				$description_benefits = $plant->info['description_benefits'];
				$sun_exposures_friendly = $plant->info['sun_exposures_friendly'];
				$water_requirement_details = $plant->info['water_requirement_details'];
				$sun_exposure_value = '';
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
					$sun_exposure_value = implode(',', $sun_exposures);
                }
				$water_requirement = '';
				if (isset($plant->info['water_requirement'])) {
					$water_requirement = $plant->info['water_requirement'];
                }
				
				$cold_zone_high = $plant->info['cold_zone_high'];
				$cold_zone_low = $plant->info['cold_zone_low'];
				$special_features = $label_info['special_features'];
				$problem_solutions = $label_info['problem_solutions'];
				$landscape_uses = $label_info['landscape_uses'];
				$wpdb->insert( 
					$table_name, 
					array( 
					'plantID' => $plantID,
					'item_size_id' =>  $item_size_id,
					'item_number' =>  $item_number,
					'special_icon' =>  $special_icon,
					'size' =>  $size,
					'form' =>  $form,
					'botanical_name' =>  $botanical_name,
					'botanical_name_short' =>  $botanical_name_short,
					'common_name' =>  $common_name,
					'primary_attribute' =>  $primary_attribute,
					'average_landscape_size' =>  $average_landscape_size,
					'care_instructions' =>  $care_instructions,
					'description_companion_plants' =>  $description_companion_plants,
					'container_size' =>  $container_size,
					'volume' =>  $volume,
					'upc' =>  $upc,
					'flowering_time' =>  $flowering_time,
					'description_benefits' =>  $description_benefits,
					'sun_exposures_friendly' =>  $sun_exposures_friendly,
					'water_requirement_details' =>  $water_requirement_details,
					'sun_exposure_value' =>  $sun_exposure_value,
					'water_requirement' =>  $water_requirement,
					'cold_zone_high' =>  $cold_zone_high,
					'cold_zone_low' =>  $cold_zone_low,
					'special_features' =>  $special_features,
					'problem_solutions' =>  $problem_solutions,
					'landscape_uses' =>  $landscape_uses,
					) 
				);
            }
			$wpdb->update( 
				'plants', 
				array( 
					'synch_with_hort' => '2'
				), 
				array( 'id' => $plantID ), 
				array( 
					'%d'	// value2
				)
			);
        }
    }
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
    //$xml = str_replace('&#8482;','&#x2122;',$xml);
    //$xml = str_replace('&&','&amp;&',$xml);
    //$xml = str_replace(' & ',' &amp; ',$xml);
    return escape_entities($xml);
}

$time_start = microtime(true);
generate_xml(false);
$time_end = microtime(true);
$execution_time = $time_end - $time_start;
echo 'Total Execution Time: '.$execution_time.' s';
//$response = generate_xml(false);

	//echo $response[1];

