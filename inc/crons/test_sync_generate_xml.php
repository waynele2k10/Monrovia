<?php
include_once( '../../wp-config.php');
require_once('../class_plant.php');
function generate_xml($full_synch) {
	global $wpdb;
    set_time_limit(3600); // ALLOW UP TO AN HOUR

    $xml_data = "";
	$ids = array();
	$id_items = array();
	$plants_sync = $wpdb->get_results(  "SELECT * FROM plants_sync LIMIT 50" );
	if (count($plants_sync) > 0) {
		$xml_data .= "<?xml version=\"1.0\"?>\r\n";
		$xml_data .= "<DataSync>\r\n";
		foreach ($plants_sync as $plant) {
			if (!in_array($plant->plantID, $ids)) {
				$ids[] = $plant->plantID;
			}
			$id_items[] = $plant->id;
			$xml_data .= "\t<Plant>\r\n";
			$xml_data .= "\t\t<ActionCode>UPDATE</ActionCode>\r\n";
			$xml_data .= "\t\t<ItemSizeID>" . $plant->item_size_id . "</ItemSizeID>\r\n";
			$xml_data .= "\t\t<Item>" . htmlentities(str_pad($plant->item_number, 5, '0', STR_PAD_LEFT)) . "</Item>\r\n";
			$xml_data .= "\t\t<SpecialIcon>" . htmlentities($plant->special_icon) . "</SpecialIcon>\r\n";
			$xml_data .= "\t\t<Size>" . htmlentities($plant->size) . "</Size>\r\n";
			$xml_data .= "\t\t<SizeDescription>" . $plant->form . "</SizeDescription>\r\n";
			$xml_data .= "\t\t<Botanical>" . _xml_sanitize($plant->botanical_name) . "</Botanical>\r\n";
			$xml_data .= "\t\t<BotanicalShort>" . _xml_sanitize($plant->botanical_name) . "</BotanicalShort>\r\n";
			$xml_data .= "\t\t<CommonName>" . _xml_sanitize($plant->common_name) . "</CommonName>\r\n";
			$xml_data .= "\t\t<PriAttribute>" . _xml_sanitize($plant->primary_attribute) . "</PriAttribute>\r\n";
			$companion_plant_description = _xml_sanitize($plant->description_companion_plants);/** Added 10/16 */
			if ($companion_plant_description != '')
				$xml_data .= "\t\t<CompanionPlantDescription>" . $companion_plant_description . "</CompanionPlantDescription>\r\n";
			$xml_data .= "\t\t<AvgLandscapeSize>" . _xml_sanitize($plant->average_landscape_size) . "</AvgLandscapeSize>\r\n";
			$xml_data .= "\t\t<CareInstructions>" . _xml_sanitize($plant->care_instructions) . "</CareInstructions>\r\n";
			$xml_data .= "\t\t<ContainerSize>" . _xml_sanitize(htmlentities($plant->container_size, ENT_COMPAT, 'UTF-8')) . "</ContainerSize>\r\n";
			$xml_data .= "\t\t<LegalVolume>" . htmlentities($plant->volume) . "</LegalVolume>\r\n";
			$xml_data .= "\t\t<UPC>" . htmlentities($plant->upc) . "</UPC>\r\n";
			$xml_data .= "\t\t<FlowerTime>" . _xml_sanitize(strip_tags($plant->flowering_time)) . "</FlowerTime>\r\n";
			$xml_data .= "\t\t<PlantBenefits>" . _xml_sanitize(strip_tags($plant->description_benefits)) . "</PlantBenefits>\r\n";
			$xml_data .= "\t\t<SunExpose>" . htmlentities($plant->sun_exposures_friendly) . "</SunExpose>\r\n";
			$xml_data .= "\t\t<WaterReq>" . _xml_sanitize(strip_tags($plant->water_requirement_details)) . "</WaterReq>\r\n";
			$xml_data .= "\t\t<Sun_exposure>" . htmlentities($plant->sun_exposure_value) . "</Sun_exposure>\r\n";
			$xml_data .= "\t\t<Water>" . _xml_sanitize(strip_tags($plant->water_requirement)) . "</Water>\r\n";
			$xml_data .= "\t\t<CZoneHigh>" . htmlentities($plant->cold_zone_high) . "</CZoneHigh>\r\n";
			$xml_data .= "\t\t<CZoneLow>" . htmlentities($plant->cold_zone_low) . "</CZoneLow>\r\n";
			$xml_data .= "\t\t<SpecialFeatures>" . htmlentities($plant->special_features) . "</SpecialFeatures>\r\n";
			$xml_data .= "\t\t<ProblemSolutions>" . htmlentities($plant->problem_solutions) . "</ProblemSolutions>\r\n";
			$xml_data .= "\t\t<LandscapeUses>" . htmlentities($plant->landscape_uses) . "</LandscapeUses>\r\n";
			$xml_data .= "\t</Plant>\r\n";
		}
		$xml_data .= "</DataSync>\r\n";
		$xml_data = str_replace('™', '&#8482;', $xml_data);
	}
	return array($ids, $id_items, $xml_data);
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

