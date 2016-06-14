<?php

require_once('../class_plant.php');
function generate_xml($full_synch) {

    set_time_limit(3600); // ALLOW UP TO AN HOUR
	
    $xml_data = "";
    $xml_data .= "<?xml version=\"1.0\"?>\r\n";
    $xml_data .= "<DataSync>\r\n";
    // $_sql = "SELECT * FROM plants "
            // . "WHERE item_number <> '' "
            // . "AND release_status_id IN (1,2,3,4,6) "
            // . "AND (last_modified BETWEEN '2015-12-22 00:00:00' AND '2015-12-25 00:00:00') "
            // . "ORDER BY last_modified DESC "
            // . "LIMIT 10 OFFSET 45";
    
    // echo $_sql;
    
    //$result = mysql_query($_sql);

   $sync_arr = array('4593', '4636', '4756', '2403');
   $sql = "SELECT id FROM plants WHERE "
           . "1=1 "
           //. "synch_with_hort=1 "
           . "AND item_number <> '' "
           . "AND item_number IN (".implode(',',$sync_arr).") "
           . "AND release_status_id IN (1,2,3,4,6) ORDER BY last_modified DESC LIMIT 100";
  
   $result = mysql_query($sql);
	
//    if ($full_synch) {
//        $result = mysql_query("SELECT id FROM plants WHERE synch_with_hort=1 AND item_number <> '' AND release_status_id IN (1,2,3,4,6) ORDER BY last_modified DESC LIMIT 100");
//    } else {
//        $result = mysql_query("SELECT id FROM plants WHERE synch_with_hort=1 AND item_number <> '' AND release_status_id IN (1,2,3,4,6) ORDER BY last_modified DESC");
//    }

    $num_rows = mysql_num_rows($result);
    $id = array();
    if (count($num_rows) > 0) {

        //$id = mysql_result($result,0,'id');

        /*
          Need data from these tables
          -list_special_feature
          -list_problem_solution
          -list_landscape_use
          -plant_problem_solution_plants
          -plant_special_feature_plants
          -plant_landscape_use_plants

          IGNORE Butterflies and Hummingbirds in Special Features
          AVOID DUPLICATES for Special Features and Problem Solution (This ever happen?)

          "SELECT * FROM plant_special_feature_plants p LEFT JOIN list_special_feature l ON p.special_feature_id = l.id WHERE `plant_id` = '$id'";
          "SELECT * FROM plant_problem_solution_plants p LEFT JOIN list_problem_solution l ON p.problem_solution_id = l.id WHERE `plant_id` = '$id'";
          "SELECT * FROM plant_landscape_use_plants p LEFT JOIN list_landscape_use l ON p.landscape_use_id = l.id WHERE `plant_id` = '$id'";

         */
        for ($i = 0; $i < $num_rows; $i++) {
            $plant = new plant(mysql_result($result, $i, 'id'));
            $id[] = $plantID = mysql_result($result, $i, 'id');
            echo "run on ".$plantID . " ";
            $label_info_array = get_plant_label_info($plant->info['item_number'], $plantID);
            foreach ($label_info_array as $label_info) {
                $xml_data .= "\t<Plant>\r\n";
                $xml_data .= "\t\t<ActionCode>UPDATE</ActionCode>\r\n";
                $xml_data .= "\t\t<ItemSizeID>" . $label_info['item_size_id'] . "</ItemSizeID>\r\n";
                //$xml_data .= "\t\t<ItemSizeID>".str_pad($plant->info['item_number'],5,'0',STR_PAD_LEFT).$label_info['size']."</ItemSizeID>\r\n";
                $xml_data .= "\t\t<Item>" . htmlentities(str_pad($plant->info['item_number'], 5, '0', STR_PAD_LEFT)) . "</Item>\r\n";

                $special_icon = '';
                if ($plant->info['is_new'] == '1')
                    $special_icon = 'New';

                $xml_data .= "\t\t<SpecialIcon>" . htmlentities($special_icon) . "</SpecialIcon>\r\n";
                $xml_data .= "\t\t<Size>" . htmlentities($label_info['size']) . "</Size>\r\n";

                $form = _xml_sanitize(htmlentities($label_info['form'], ENT_COMPAT, 'UTF-8'));

                //if($form!='')
                $xml_data .= "\t\t<SizeDescription>" . $form . "</SizeDescription>\r\n";
                $xml_data .= "\t\t<Botanical>" . _xml_sanitize($plant->info['botanical_name']) . "</Botanical>\r\n";
                $xml_data .= "\t\t<BotanicalShort>" . _xml_sanitize(get_short_botanical_name($plant->info['botanical_name'])) . "</BotanicalShort>\r\n";
                $xml_data .= "\t\t<CommonName>" . _xml_sanitize($plant->info['common_name']) . "</CommonName>\r\n";
                $xml_data .= "\t\t<PriAttribute>" . _xml_sanitize($plant->info['primary_attribute']) . "</PriAttribute>\r\n";

                $average_landscape_size = _xml_sanitize(strip_tags($label_info['average_landscape_size']));
                $care_instructions = _xml_sanitize(strip_tags($label_info['care_instructions']));
                $companion_plant_description = _xml_sanitize(strip_tags($label_info['description_companion_plants']));/** Added 10/16 */
                if ($companion_plant_description != '')
                    $xml_data .= "\t\t<CompanionPlantDescription>" . $companion_plant_description . "</CompanionPlantDescription>\r\n";

                if ($average_landscape_size == '')
                    $average_landscape_size = _xml_sanitize($plant->info['average_landscape_size']);
                $xml_data .= "\t\t<AvgLandscapeSize>" . $average_landscape_size . "</AvgLandscapeSize>\r\n";
                if ($care_instructions == '')
                    $care_instructions = _xml_sanitize($plant->info['description_care']);
                $xml_data .= "\t\t<CareInstructions>" . $care_instructions . "</CareInstructions>\r\n";
                $xml_data .= "\t\t<ContainerSize>" . _xml_sanitize(htmlentities($label_info['container_size'], ENT_COMPAT, 'UTF-8')) . "</ContainerSize>\r\n";
                $xml_data .= "\t\t<LegalVolume>" . htmlentities($label_info['volume']) . "</LegalVolume>\r\n";
                //$xml_data .= "\t\t<US_Volume>".htmlentities($label_info['volume_us'])."</US_Volume>\r\n";
                //$xml_data .= "\t\t<Metric_Volume>".htmlentities($label_info['volume_metric'])."</Metric_Volume>\r\n";
                $xml_data .= "\t\t<UPC>" . htmlentities($label_info['upc']) . "</UPC>\r\n";
                $xml_data .= "\t\t<FlowerTime>" . _xml_sanitize(strip_tags($plant->info['flowering_time'])) . "</FlowerTime>\r\n";
                $xml_data .= "\t\t<PlantBenefits>" . _xml_sanitize(strip_tags($plant->info['description_benefits'])) . "</PlantBenefits>\r\n";
                $xml_data .= "\t\t<SunExpose>" . htmlentities($plant->info['sun_exposures_friendly']) . "</SunExpose>\r\n";
                $xml_data .= "\t\t<WaterReq>" . _xml_sanitize(strip_tags($plant->info['water_requirement_details'])) . "</WaterReq>\r\n";

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
                    $xml_data .= "\t\t<Sun_exposure>" . htmlentities(implode(',', $sun_exposures)) . "</Sun_exposure>\r\n";
                }else {
                    $xml_data .= "\t\t<Sun_exposure></Sun_exposure>\r\n";
                }

                if (isset($plant->info['water_requirement'])) {
                    $xml_data .= "\t\t<Water>" . _xml_sanitize(strip_tags($plant->info['water_requirement'])) . "</Water>\r\n";
                } else {
                    $xml_data .= "\t\t<Water></Water>\r\n";
                }

                $xml_data .= "\t\t<CZoneHigh>" . htmlentities($plant->info['cold_zone_high']) . "</CZoneHigh>\r\n";
                $xml_data .= "\t\t<CZoneLow>" . htmlentities($plant->info['cold_zone_low']) . "</CZoneLow>\r\n";

                // Special Features, Landscape Uses and Problem Solutions added 5/8/15
                $xml_data .= "\t\t<SpecialFeatures>" . htmlentities($label_info['special_features']) . "</SpecialFeatures>\r\n";
                $xml_data .= "\t\t<ProblemSolutions>" . htmlentities($label_info['problem_solutions']) . "</ProblemSolutions>\r\n";
                $xml_data .= "\t\t<LandscapeUses>" . htmlentities($label_info['landscape_uses']) . "</LandscapeUses>\r\n";

                //$temperature_range = get_cold_zone_temperature_range($plant->info['cold_zone_low'],$plant->info['cold_zone_high']);
                //if($temperature_range!='') $xml_data .= "\t\t<ColdHard>".$temperature_range."</ColdHard>\r\n";
                //$xml_data .= "\t\t<HZoneHigh>".$plant->info['heat_zone_high']."</HZoneHigh>\r\n";
                //$xml_data .= "\t\t<HZoneLow>".$plant->info['heat_zone_high']."</HZoneLow>\r\n";
                //$xml_data .= "\t\t<Category>".htmlentities($plant->info['subcategory'])."</Category>\r\n";
                $xml_data .= "\t</Plant>\r\n";
                // SEE IF mysql_free_result IS NEEDED HERE
                // ADD TO class_sql (each call)
            }
        }
    }

    $xml_data .= "</DataSync>\r\n";

    $xml_data = str_replace('™', '&#8482;', $xml_data);

    return array($id, $xml_data);
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

//$response = generate_xml(false);

	//echo $response[1];

