<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
	require_once('../class_plant.php');
error_reporting(E_ALL);
ini_set('display_errors', '1');
	// USE MEDIUM PERMISSIONS USER
	sql_disconnect();
	sql_set_user('med');
	sql_connect();

	function generate_xml($full_synch){

		set_time_limit(3600);	// ALLOW UP TO AN HOUR

		$xml_data = "";
		$xml_data .= "<?xml version=\"1.0\"?>\n";
		$xml_data .= "<DataSync>\n";

		if($full_synch){
			$result = sql_query("SELECT id FROM plants WHERE synch_with_hort=1 AND item_number <> '' AND is_active=1 ORDER BY last_modified DESC LIMIT 1");
		}else{
			$result = sql_query("SELECT id FROM plants WHERE synch_with_hort=1 AND item_number <> '' AND is_active=1 ORDER BY last_modified DESC");
		}

		$num_rows = @mysql_numrows($result);

		$id = mysql_result($result,0,'id');

		for($i=0;$i<$num_rows;$i++){
			$plant = new plant(mysql_result($result,$i,'id'));

			$label_info_array = get_plant_label_info($plant->info['item_number']);

			foreach($label_info_array as $label_info){
					$xml_data .= "\t<Plant>\n";
					$xml_data .= "\t\t<ActionCode>UPDATE</ActionCode>\n";
					$xml_data .= "\t\t<ItemSizeID>".$label_info['item_size_id']."</ItemSizeID>\n";
					//$xml_data .= "\t\t<ItemSizeID>".str_pad($plant->info['item_number'],5,'0',STR_PAD_LEFT).$label_info['size']."</ItemSizeID>\n";
					$xml_data .= "\t\t<Item>".htmlentities(str_pad($plant->info['item_number'],5,'0',STR_PAD_LEFT))."</Item>\n";

					$special_icon = '';
					if($plant->info['is_new']=='1') $special_icon = 'New';

					$xml_data .= "\t\t<SpecialIcon>".htmlentities($special_icon)."</SpecialIcon>\n";
					$xml_data .= "\t\t<Size>".htmlentities($label_info['size'])."</Size>\n";

					$form = htmlentities($label_info['form']);

					//if($form!='')
					$xml_data .= "\t\t<SizeDescription>".$form."</SizeDescription>\n";
					$xml_data .= "\t\t<Botanical>"._xml_sanitize($plant->info['botanical_name'])."</Botanical>\n";
					$xml_data .= "\t\t<BotanicalShort>"._xml_sanitize(get_short_botanical_name($plant->info['botanical_name']))."</BotanicalShort>\n";
					$xml_data .= "\t\t<CommonName>"._xml_sanitize($plant->info['common_name'])."</CommonName>\n";
					$xml_data .= "\t\t<PriAttribute>"._xml_sanitize($plant->info['primary_attribute'])."</PriAttribute>\n";

					$average_landscape_size = _xml_sanitize(strip_tags($label_info['average_landscape_size']));
					$care_instructions = _xml_sanitize(strip_tags($label_info['care_instructions']));

					if($average_landscape_size=='') $average_landscape_size = _xml_sanitize($plant->info['average_landscape_size']);
					$xml_data .= "\t\t<AvgLandscapeSize>".$average_landscape_size."</AvgLandscapeSize>\n";
					if($care_instructions=='') $care_instructions = _xml_sanitize($plant->info['description_care']);
					$xml_data .= "\t\t<CareInstructions>".$care_instructions."</CareInstructions>\n";
					$xml_data .= "\t\t<ContainerSize>".htmlentities($label_info['container_size'])."</ContainerSize>\n";
					$xml_data .= "\t\t<LegalVolume>".htmlentities($label_info['volume'])."</LegalVolume>\n";
					//$xml_data .= "\t\t<US_Volume>".htmlentities($label_info['volume_us'])."</US_Volume>\n";
					//$xml_data .= "\t\t<Metric_Volume>".htmlentities($label_info['volume_metric'])."</Metric_Volume>\n";
					$xml_data .= "\t\t<UPC>".htmlentities($label_info['upc'])."</UPC>\n";
					$xml_data .= "\t\t<FlowerTime>"._xml_sanitize(strip_tags($plant->info['flowering_time']))."</FlowerTime>\n";
					$xml_data .= "\t\t<PlantBenefits>"._xml_sanitize(strip_tags($plant->info['description_benefits']))."</PlantBenefits>\n";
					$xml_data .= "\t\t<SunExpose>".htmlentities($plant->info['sun_exposures_friendly'])."</SunExpose>\n";
					$xml_data .= "\t\t<WaterReq>"._xml_sanitize(strip_tags($plant->info['water_requirement_details']))."</WaterReq>\n";
					$xml_data .= "\t\t<CZoneHigh>".htmlentities($plant->info['cold_zone_high'])."</CZoneHigh>\n";
					$xml_data .= "\t\t<CZoneLow>".htmlentities($plant->info['cold_zone_low'])."</CZoneLow>\n";

					//$temperature_range = get_cold_zone_temperature_range($plant->info['cold_zone_low'],$plant->info['cold_zone_high']);

					//if($temperature_range!='') $xml_data .= "\t\t<ColdHard>".$temperature_range."</ColdHard>\n";
					//$xml_data .= "\t\t<HZoneHigh>".$plant->info['heat_zone_high']."</HZoneHigh>\n";
					//$xml_data .= "\t\t<HZoneLow>".$plant->info['heat_zone_high']."</HZoneLow>\n";
					//$xml_data .= "\t\t<Category>".htmlentities($plant->info['subcategory'])."</Category>\n";
					$xml_data .= "\t</Plant>\n";
					// SEE IF mysql_free_result IS NEEDED HERE
					// ADD TO class_sql (each call)
				}
		}

		$xml_data .= "</DataSync>\n";

		$xml_data = str_replace('™','&#8482;',$xml_data);

		return array($id,$xml_data);
	}

	function get_short_botanical_name($botanical_name){
		$search_suffixes = array(' P.P.',' Plant Patent Applied For');
		$pos = -1;
		for($i=0;$i<count($search_suffixes);$i++){
			if($pos==-1) $pos = strpos($botanical_name,$search_suffixes[$i]);
		}
		if($pos>-1){
			$botanical_name = trim(substr($botanical_name,0,$pos));
		}
		return $botanical_name;
	}

	function get_plant_label_info($item_number){

		// SQL INJECTION-SAFE
		$item_number = intval($item_number);

		$ret = array();

		$items = unserialize(get_url('http://azlink.monrovia.com/tpg_items.php?item_number=' . $item_number));

		for($i=0;$i<count($items);$i++){
			$item = $items[$i];
			$label_size_info = get_label_size_info($item['SIZE'],$item_number);
			//var_dump($label_size_info);
			if($label_size_info!=null) $ret[] = array('item_size_id'=>$item['IMLITM'],'size'=>$item['SIZE'],'form'=>$label_size_info['form'],'upc'=>$item['IMUPCN'],'volume'=>$label_size_info['volume'],'container_size'=>$label_size_info['container_size'],'average_landscape_size'=>$label_size_info['average_landscape_size'],'care_instructions'=>$label_size_info['care_instructions']);
		}
		return $ret;
	}

	function get_label_size_info($item_size,$item_number){

		$item_number = str_pad($item_number,5,'0',STR_PAD_LEFT);

		$result = sql_query("SELECT item_size,container_size,form,volume_us,volume_metric FROM hort_label_item_sizes WHERE item_size = '$item_size' LIMIT 1");

		$ret = array('volume'=>'','container_size'=>'','average_landscape_size'=>'','care_instructions'=>'','form'=>'');

		if(mysql_num_rows($result)>0){
			$volume_metric = mysql_result($result,0,'volume_metric');
			$volume = mysql_result($result,0,'volume_us');
			if($volume_metric!='') $volume .= ' (' . $volume_metric . ')';

			$ret['volume'] = $volume;
			$ret['container_size'] = mysql_result($result,0,'container_size');
			$ret['form'] = mysql_result($result,0,'form');

			$result = sql_query("SELECT average_landscape_size,care_instructions FROM hort_label_descriptions WHERE item_size_id = '$item_number$item_size' LIMIT 1");

			if(mysql_num_rows($result)>0){
				$ret['average_landscape_size'] = mysql_result($result,0,'average_landscape_size');
				$ret['care_instructions'] = mysql_result($result,0,'care_instructions');
			}
			return $ret;
		}else{
			return null;
		}

	}

	function _xml_sanitize($xml){
		$xml = str_replace('&trade;','&#8482;',$xml);
		$xml = str_replace('&reg;','&#x00AE;',$xml);
		$xml = str_replace('&copy;','&#169;',$xml);
		//$xml = str_replace('&&','&amp;&',$xml);
		//$xml = str_replace(' & ',' &amp; ',$xml);
		return escape_entities($xml);
	}

	$response = generate_xml(true);

	echo $response[1];

?>