<?php
	require_once('class_record.php');
	class plant extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM plant TABLE, EXCEPT FOR id
		var $table_fields = 'last_modified,last_modified_by_user_id,synch_with_hort,is_active,is_new,is_plant_select,item_number,release_status_id,is_monrovia_exclusive,is_monrovia_patented,is_monrovia_trademarked,learn2grow_url,buy_now_url,common_name,botanical_name,botanical_family,botanical_genus,botanical_species,botanical_subspecies,botanical_cultivar,synonym,trademark_name,phonetic_spelling,patent,patent_act,php_metaphone,deciduous_evergreen_id,collection_name,special_third_party,subcategory,water_requirement_id,water_requirement_details,growth_rate_id,growth_habit,attributes,height_id,spread_id,cold_zone_low,cold_zone_high,description_catalog,description_benefits,description_design,description_care,description_lore,description_history,description_foliage,description_flower,description_companion_plants,primary_attribute,geographical_origin,propagation,year_introduced,pruning_time,fertilizer_id,flower_color_id,flowering_time,average_landscape_size,foliage_shape,foliage_color_id,foliage_color_under,foliage_color_new,foliage_color_winter,foliage_color_spring,foliage_color_summer,foliage_color_fall,keywords';

		function plant($record_id = ''){
			$this->table_name = 'plants';
			$this->image_sets = array();
			if($record_id!='') $this->load($record_id);
		}

		function load_by_item_number($item_number){
			$id = get_plant_id_by_item_number($item_number);
			if($id){
				return $this->load($id);
			}else{
				return false;
			}
		}

		function load($record_id){
			parent::load($record_id);
			
			if(isset($this->info['id'])&&is_numeric($this->info['id'])){
				$this->populate_list_values();
				$this->populate_dumb_values();
				//$this->get_companion_plant_ids();
				$this->get_types();
				$this->get_special_features();
				$this->get_sun_exposures();
				$this->get_sunset_zones();
				$this->info['flowering_seasons'] = $this->get_unordered_attributes('flowering_season');
				$this->info['flower_attributes'] = $this->get_unordered_attributes('flower_attribute');
				$this->info['problem_solutions'] = $this->get_unordered_attributes('problem_solution');
				$this->info['garden_styles'] = $this->get_unordered_attributes('garden_style');
				$this->info['landscape_uses'] = $this->get_unordered_attributes('landscape_use');
				$this->info['growth_habits'] = $this->get_unordered_attributes('growth_habit');
				$this->get_fertilizer();

				// TEMPORARY MEASURE TO FORMAT ATTRIBUTES WITH SPACES
				if(isset($this->info['attributes'])){
					$this->info['attributes'] = str_replace(',',', ',$this->info['attributes']);
					$this->info['attributes'] = str_replace(' ','  ',$this->info['attributes']);
				}
			}			
		}

		function determine_where_active(){
			// RETURNS A CSV STRING INDICATING WHERE THIS PLANT IS ALLOWED TO BE SHOWN
			$ret = '';

			// THESE RELEASE STATUSES ARE ALLOWED ON THE SITE: A (Active), NA (New/Active), NI (New/Inactive), II (Inventory/Inactive), F (Future)
			if(isset($this->info['is_active'])&&$this->info['is_active']=='1'&&isset($this->info['release_status_id'])&&($this->info['release_status_id']=='1'||$this->info['release_status_id']=='2'||$this->info['release_status_id']=='3'||$this->info['release_status_id']=='4'||$this->info['release_status_id']=='6')) $ret .= ',site';

			// THESE RELEASE STATUSES ARE ALLOWED IN CUSTOM CATALOGS: A (Active), NA (New/Active), NI (New/Inactive), F (Future)
			if(isset($this->info['is_active'])&&$this->info['is_active']=='1'&&isset($this->info['release_status_id'])&&($this->info['release_status_id']=='1'||$this->info['release_status_id']=='2'||$this->info['release_status_id']=='3'||$this->info['release_status_id']=='6')) $ret .= ',pdf';
			
			if($ret!='') $ret .= ',';
			return $ret;
		}

		function populate_dumb_values(){
			// MAKE SURE THIS FUNCTION DOES NOT NEED ANY EXTRA LOOKUPS
			if(isset($this->info['id'])&&is_numeric($this->info['id'])){
				$this->info['details_url'] = site_url().'/plant-catalog/plants/'.$this->info['id'].'/'.generate_plant_seo_name($this->info['common_name']);
			} 

			// COLD ZONES
			
			$cold_zone_low = (isset($this->info['cold_zone_low'])?intval($this->info['cold_zone_low']):0);
			$cold_zone_high = (isset($this->info['cold_zone_high'])?intval($this->info['cold_zone_high']):0);
			
			if($cold_zone_low!=0||$cold_zone_high){
				// IF BOTH cold_zone_low AND cold_zone_high ARE PROVIDED...
				if($cold_zone_low!=0&&$cold_zone_high!=0){
					// IF cold_zone_low IS THE SAME AS cold_zone_high...
					if($cold_zone_low==$cold_zone_high){
						$this->info['cold_zones_friendly'] = $cold_zone_low;
					}else{
						$this->info['cold_zones_friendly'] = $cold_zone_low . ' - ' . $cold_zone_high;
					}
				}else{
				// IF ONLY ONE IS PROVIDED, USE SUM OF BOTH
					 $this->info['cold_zones_friendly'] = $cold_zone_low + $cold_zone_high;
				}
			}else{
				$this->info['cold_zones_friendly'] = '';
			}
			
		}

		function delete(){
			// DELETE IMAGE SETS AND IMAGE SET DOWNLOAD HISTORY
			if(count($this->image_sets)==0) $this->get_images();
			for($i=0;$i<count($this->image_sets);$i++){
				$this->image_sets[$i]->delete();
			}

			// DELETE ASSOCIATED DATA
			$associated_tables = array('plant_flowering_season_plants','plant_flower_attribute_plants','plant_garden_style_plants','plant_growth_habit_plants','plant_landscape_use_plants','plant_problem_solution_plants','plant_special_feature_plants','plant_sunset_zones','plant_sun_exposure_plants','plant_type_plants','videos_plants','wish_list_items');

			for($i=0;$i<count($associated_tables);$i++){
				sql_query('DELETE FROM '.$associated_tables[$i].' WHERE plant_id=' . $this->info['id']);
			}

			// DELETE RECORD ITSELF
			parent::delete();
		}

		function save($params = NULL){

			// METAPHONE
			$name_combination = $this->info['common_name'] . ' ' . $this->info['botanical_name'] . ' ' . $this->info['trademark_name'];
			$this->info['php_metaphone'] = ' ' . to_metaphone_string($name_combination) . ' ';

			// CORRECT COLD ZONE ORDERING, IF NEEDED
			$cold_zone_low = $this->info['cold_zone_low'];
			$cold_zone_high = $this->info['cold_zone_high'];
			if($cold_zone_low!=''&&$cold_zone_high!=''){
				$this->info['cold_zone_low'] = min(intval($cold_zone_low),intval($cold_zone_high));
				$this->info['cold_zone_high'] = max(intval($cold_zone_low),intval($cold_zone_high));
			}

			$success = parent::save();

			if(is_numeric($this->info['id'])){
				try {
					// UPDATE TYPES
					mysql_query("DELETE FROM plant_type_plants WHERE plant_id = ".$this->info['id']);
					for($i=0;$i<count($this->info['types']);$i++){
						mysql_query("INSERT INTO plant_type_plants(type_id,plant_id,is_primary) VALUES(".$this->info['types'][$i]->id.",".$this->info['id'].",'". $this->info['types'][$i]->is_primary . "')");
					}

					// UPDATE FLOWER ATTRIBUTES
					mysql_query("DELETE FROM plant_flower_attribute_plants WHERE plant_id = ".$this->info['id']);
					for($i=0;$i<count($this->info['flower_attributes']);$i++){
						mysql_query("INSERT INTO plant_flower_attribute_plants(flower_attribute_id,plant_id) VALUES(".$this->info['flower_attributes'][$i]->id.",".$this->info['id'] . ")");
					}

					// UPDATE FLOWERING SEASONS
					mysql_query("DELETE FROM plant_flowering_season_plants WHERE plant_id = ".$this->info['id']);
					for($i=0;$i<count($this->info['flowering_seasons']);$i++){
						mysql_query("INSERT INTO plant_flowering_season_plants(flowering_season_id,plant_id) VALUES(".$this->info['flowering_seasons'][$i]->id.",".$this->info['id'] . ")");
					}

					// SUN EXPOSURES
					mysql_query("DELETE FROM plant_sun_exposure_plants WHERE plant_id = ".$this->info['id']);
					for($i=0;$i<count($this->info['sun_exposures']);$i++){
						mysql_query("INSERT INTO plant_sun_exposure_plants(sun_exposure_id,plant_id) VALUES(".$this->info['sun_exposures'][$i]->id.",".$this->info['id'].")");
					}

					// SUNSET ZONES
					mysql_query("DELETE FROM plant_sunset_zones WHERE plant_id = ".$this->info['id']);
					for($i=0;$i<count($this->info['sunset_zones']);$i++){
						mysql_query("INSERT INTO plant_sunset_zones (sunset_zone,plant_id) VALUES(".$this->info['sunset_zones'][$i].",".$this->info['id'].")");
					}

					// GARDEN STYLES
					mysql_query("DELETE FROM plant_garden_style_plants WHERE plant_id = ".$this->info['id']);
					for($i=0;$i<count($this->info['garden_styles']);$i++){
						mysql_query("INSERT INTO plant_garden_style_plants(garden_style_id,plant_id) VALUES(".$this->info['garden_styles'][$i]->id.",".$this->info['id'].")");
					}

					// SPECIAL FEATURES
					mysql_query("DELETE FROM plant_special_feature_plants WHERE plant_id = ".$this->info['id']);
					for($i=0;$i<count($this->info['special_features']);$i++){
						mysql_query("INSERT INTO plant_special_feature_plants(special_feature_id,plant_id) VALUES(".$this->info['special_features'][$i]->id.",".$this->info['id'].")");
					}

					// PROBLEM/SOLUTIONS
					mysql_query("DELETE FROM plant_problem_solution_plants WHERE plant_id = ".$this->info['id']);
					for($i=0;$i<count($this->info['problem_solutions']);$i++){
						mysql_query("INSERT INTO plant_problem_solution_plants(problem_solution_id,plant_id) VALUES(".$this->info['problem_solutions'][$i]->id.",".$this->info['id'].")");
					}

					// LANDSCAPE USES
					mysql_query("DELETE FROM plant_landscape_use_plants WHERE plant_id = ".$this->info['id']);
					for($i=0;$i<count($this->info['landscape_uses']);$i++){
						mysql_query("INSERT INTO plant_landscape_use_plants(landscape_use_id,plant_id) VALUES(".$this->info['landscape_uses'][$i]->id.",".$this->info['id'].")");
					}

					// GROWTH HABITS
					mysql_query("DELETE FROM plant_growth_habit_plants WHERE plant_id = ".$this->info['id']);
					for($i=0;$i<count($this->info['growth_habits']);$i++){
						mysql_query("INSERT INTO plant_growth_habit_plants(growth_habit_id,plant_id) VALUES(".$this->info['growth_habits'][$i]->id.",".$this->info['id'].")");
					}

					/*
					// UPDATE SPECIAL FEATURES
					sql_query("DELETE FROM plant_special_feature_plants WHERE plant_id = ".$this->info['id']);
					for($i=0;$i<count($this->special_features);$i++){
						//sql_query("INSERT INTO plant_special_feature_plants (special_feature_id,plant_id,is_primary) VALUES(".$this->special_features[$i]->id.",".$this->info['id'].")");
					}
					*/
					// UPDATE COMPANION PLANTS
					/*
					sql_query("DELETE FROM plant_companion_plants WHERE plant_id_1=".$this->info['id']." OR plant_id_2=".$this->info['id']);
					for($i=0;$i<count($this->companion_plant_ids);$i++){
						sql_query("INSERT INTO plant_companion_plants(plant_id_1,plant_id_2) VALUES(".$this->info['id'].",".$this->companion_plant_ids[$i].")");
					}
					*/

					$this->update_keywords();
					//$this->clear_cache();

				}catch(Exception $err){
					$success = false;
				}
			}else{
				$success = false;
			}
			return $success;
		}

		/*function clear_cache(){
			try {
				require_once('class_cache.php');
				
				// CLEAR CACHE FILES (IF ONES EXIST)
				
				// DESKTOP VERSION
					$cache = new cache('home :: plant catalog :: plant ' . $this->info['id'] . ' :: on wish list');
					if($cache->exists) $cache->remove();
					$cache = new cache('home :: plant catalog :: plant ' . $this->info['id'] . ' :: not on wish list');
					if($cache->exists) $cache->remove();
				
				// MOBILE VERSION
					$cache = new cache('mobile :: home :: plant catalog :: plant ' . $this->info['id'] . ' :: ');
					if($cache->exists) $cache->remove();
				
				return true;
			}catch(Exception $err){
				return false;
			}
		}
		*/
		function get_fertilizer(){
			if(isset($this->info['fertilizer_id'])&&is_numeric($this->info['fertilizer_id'])){
				$result =mysql_query("SELECT * FROM list_fertilizer WHERE id=".$this->info['fertilizer_id']);
				$num_rows = mysql_num_rows($result);
				if($num_rows==1){
					$this->info['fertilizer'] = new plant_fertilizer(mysql_result($result,0,'id'),mysql_result($result,0,'name'),mysql_result($result,0,'image_url'));
				}
			}
		}

		function populate_list_values(){
			$this->info['last_modified_by_user'] = get_list_item_value('monrovia_users',$this->info['last_modified_by_user_id'],'user_name');
			$this->info['release_status'] = isset($this->info['release_status_id'])?get_list_item_value('list_release_status',$this->info['release_status_id']):'';
			$this->info['deciduous_evergreen'] = isset($this->info['deciduous_evergreen_id'])?get_list_item_value('list_deciduous_evergreen',$this->info['deciduous_evergreen_id']):'';
			//$this->info['subcategory'] = isset($this->info['subcategory_id'])?get_list_item_value('list_subcategory',$this->info['subcategory_id']):'';
			$this->info['flower_color'] = isset($this->info['flower_color_id'])?get_list_item_value('list_flower_color',$this->info['flower_color_id']):'';
			$this->info['water_requirement'] = isset($this->info['water_requirement_id'])?get_list_item_value('list_water_requirement',$this->info['water_requirement_id']):'';
			$this->info['growth_rate'] = isset($this->info['growth_rate_id'])?get_list_item_value('list_growth_rate',$this->info['growth_rate_id']):'';
//			$this->info['growth_habit'] = get_list_item_value('list_growth_habit',$this->info['growth_habit_id']);
			$this->info['height'] = isset($this->info['height_id'])?get_list_item_value('list_height',$this->info['height_id']):'';
			$this->info['spread'] = isset($this->info['spread_id'])?get_list_item_value('list_spread',$this->info['spread_id']):'';
			$this->info['fertilizer'] = isset($this->info['fertilizer_id'])?get_list_item_value('list_fertilizer',$this->info['fertilizer_id']):'';
			$this->info['foliage_color'] = isset($this->info['foliage_color_id'])?get_list_item_value('list_foliage_color',$this->info['foliage_color_id']):'';
		}

		function get_images($include_expired = false){
			if(isset($this->info['id'])&&is_numeric($this->info['id'])){
				include_once('class_plant_image_set.php');
				
				if($include_expired){
					$query = "SELECT id FROM plant_image_sets WHERE plant_id = ".$this->info['id']." ORDER BY is_active DESC, is_primary DESC, title ASC";
				}else{
					$query = "SELECT id FROM plant_image_sets WHERE plant_id = ".$this->info['id']." AND (expiration_date>NOW() OR expiration_date='0000-00-00') ORDER BY is_active DESC, is_primary DESC, title ASC";  //BES20110913
				}
				
				$result =mysql_query($query);
				$num_rows = mysql_num_rows($result);
				$is_downloadable = false;
				for($i=0;$i<$num_rows;$i++){
					$image_set = new plant_image_set(mysql_result($result,$i,"id"));
					$image_set->determine_image_paths($this->info['common_name'],$this->info['item_number']);
					$this->image_sets[] = $image_set;
					if(($image_set->info['is_primary']=='1'||$num_rows==1)&&$image_set->info['is_active']=='1') $this->info['image_primary'] = $image_set;

					if($image_set->info['is_active']=='1'&&$image_set->info['is_distributable']=='1') $is_downloadable = true;

				}
				$this->info['has_downloadable_images'] = $is_downloadable;

				// IF NO PRIMARY, SET FIRST ACTIVE ONE AS PRIMARY
				if(!$this->has_primary_image()){
					foreach($this->image_sets as $image_set){
						if($image_set->info['is_active']=='1'){
							$this->info['image_primary'] = $image_set;
							return;
						}
					}
				}
			}
		}

		function has_primary_image(){
			$ret = false;
			try {
				foreach($this->image_sets as $image_set){
					if($image_set->info['is_primary']=='1') $ret = true;
				}
				return $ret;
			}catch(Exception $err){
				return false;
			}
		}

		function get_primary_image(){
			if(is_numeric($this->info['id'])){
				require_once('class_plant_image_set.php');
				$result =mysql_query("SELECT id FROM plant_image_sets WHERE plant_id = ".$this->info['id']." AND is_active='1' AND (expiration_date>NOW() OR expiration_date='0000-00-00') ORDER BY (is_active=1 AND is_primary=1) DESC LIMIT 1"); //BES20110913
				$num_rows = mysql_num_rows($result);
				if($num_rows==1){
					$common_name = isset($this->info['common_name'])?$this->info['common_name']:'monrovia';
					$item_number = isset($this->info['item_number'])?$this->info['item_number']:$this->info['id'];
					
					$image_set = new plant_image_set();
					$image_set->info['id'] = mysql_result($result,0,'id');
					$image_set->determine_image_paths($common_name,$item_number);
					$this->info['image_primary'] = $image_set;
				}
			}
		}

		function get_types(){
			if(isset($this->info['id'])&&is_numeric($this->info['id'])){
				$types_friendly = '';
				$this->info['types'] = array();
				$result =mysql_query("SELECT id, name,is_primary FROM plant_type_plants INNER JOIN list_type ON type_id = list_type.id WHERE plant_id=".$this->info['id']." ORDER BY is_primary,name ASC");
				$num_rows = mysql_num_rows($result);
				for($i=0;$i<$num_rows;$i++){
					$type = new plant_attribute(mysql_result($result,$i,"id"),mysql_result($result,$i,"name"),0,mysql_result($result,$i,"is_primary"));
					$this->info['types'][] = $type;
					if($type->is_primary=='1') $this->info['type_primary'] = $type;
					$types_friendly .= ', ' . $type->name;
				}
				$this->info['types_friendly'] = substr($types_friendly,2);
				if($this->info['types_friendly']===false) $this->info['types_friendly'] = '';
			}
		}

		function get_special_features(){
			if(isset($this->info['id'])&&is_numeric($this->info['id'])){
				$special_features_friendly = '';
				$this->info['special_features'] = array();
				$result =mysql_query("SELECT id, name, list_special_feature.is_historical FROM plant_special_feature_plants INNER JOIN list_special_feature ON special_feature_id = list_special_feature.id WHERE plant_id=".$this->info['id']." ORDER BY name ASC");
				$num_rows = mysql_num_rows($result);
				for($i=0;$i<$num_rows;$i++){
					$special_feature = new plant_attribute(mysql_result($result,$i,"id"),mysql_result($result,$i,"name"),mysql_result($result,$i,"is_historical"),0);
					$this->info['special_features'][] = $special_feature;
					//if($special_feature->is_primary=='1') $this->info['special_feature_primary'] = $special_feature;
					$special_features_friendly .= ', ' . $special_feature->name;
				}
				$this->info['special_features_friendly'] = substr($special_features_friendly,2);
			}
		}

		function get_sun_exposures(){
			if(isset($this->info['id'])&&is_numeric($this->info['id'])){
				$this->info['sun_exposures'] = array();
				$result =mysql_query("SELECT DISTINCT id, name, list_sun_exposure.is_historical FROM plant_sun_exposure_plants INNER JOIN list_sun_exposure ON sun_exposure_id = list_sun_exposure.id WHERE plant_id=".$this->info['id']." ORDER BY list_sun_exposure.ordinal ASC");
				$num_rows = mysql_num_rows($result);
				for($i=0;$i<$num_rows;$i++){
					$sun_exposure = new plant_attribute(mysql_result($result,$i,"id"),mysql_result($result,$i,"name"),mysql_result($result,$i,"is_historical"),0);
					$this->info['sun_exposures'][] = $sun_exposure;
				}
				if($num_rows==1) $this->info['sun_exposures_friendly'] = $this->info['sun_exposures'][0]->name;
				if($num_rows>1){
					$low_last_word = substr($this->info['sun_exposures'][0]->name,strrpos($this->info['sun_exposures'][0]->name,' ')+1);
					$high_last_word = substr($this->info['sun_exposures'][$num_rows-1]->name,strrpos($this->info['sun_exposures'][$num_rows-1]->name,' ')+1);

					$low_friendly = trim((($low_last_word==$high_last_word)?trim($this->info['sun_exposures'][0]->name,$low_last_word):$this->info['sun_exposures'][0]->name));
					$high_friendly = $this->info['sun_exposures'][$num_rows-1]->name;

					$this->info['sun_exposures_friendly'] = ucfirst(strtolower($low_friendly . ' to ' . $high_friendly));
				}
				if(!isset($this->info['sun_exposures_friendly'])) $this->info['sun_exposures_friendly'] = '';
			}
		}

		function get_sunset_zones(){
			if(isset($this->info['id'])&&is_numeric($this->info['id'])){
				$this->info['sunset_zones'] = array();
				$result =mysql_query("SELECT sunset_zone FROM plant_sunset_zones WHERE plant_id=".$this->info['id']." ORDER BY sunset_zone ASC");
				$num_rows = mysql_num_rows($result);
				$groups = array();
				$last_sunset_zone = 0;
				$str_group = '';
				for($i=0;$i<$num_rows;$i++){
					$sunset_zone = intval(mysql_result($result,$i,'sunset_zone'));
					$this->info['sunset_zones'][] = $sunset_zone;
					if($sunset_zone==$last_sunset_zone+1){
						$str_group .= ',' . $sunset_zone;
					}else{
						$groups[] = explode(',',trim($str_group,','));
						$str_group = $sunset_zone;
					}
					$last_sunset_zone = $sunset_zone;
				}
				$str_group = trim($str_group,',');

				$groups[] = explode(',',$str_group);

				$friendly = '';

				for($i=0;$i<count($groups);$i++){
					if(count($groups[$i])>2){
						$friendly .= ', ' . $groups[$i][0] . ' - ' . $groups[$i][count($groups[$i])-1];
					}else{
						$friendly .= ', ' . implode(', ',$groups[$i]);
					}
				}
				$this->info['sunset_zones_friendly'] = trim($friendly,', ');

				// SPECIAL CASE
				//if($this->info['sunset_zones_friendly']=='1 - 45') $this->info['sunset_zones_friendly'] = 'All zones';
			}
		}

		function get_unordered_attributes($attribute_name){
			if(!is_suspicious($attribute_name)){
				$ret = array();
				if(isset($this->info['id'])&&is_numeric($this->info['id'])){
					$friendly = '';
					$result =mysql_query("SELECT DISTINCT id, name, list_".$attribute_name.".is_historical, ".$attribute_name."_id FROM plant_".$attribute_name."_plants INNER JOIN list_".$attribute_name." ON ".$attribute_name."_id = list_".$attribute_name.".id WHERE plant_id=".$this->info['id']." ORDER BY name ASC");

					$num_rows = mysql_num_rows($result);
					for($i=0;$i<$num_rows;$i++){
						$ret[] = new plant_attribute(mysql_result($result,$i,"id"),mysql_result($result,$i,"name"),mysql_result($result,$i,"is_historical"),0);
						$friendly .= ', ' . mysql_result($result,$i,"name");
					}
					if($friendly!=''){
						$friendly = substr($friendly,2);
						$this->info[$attribute_name.'s_friendly'] = $friendly;
					}
				}
				return $ret;
			}
		}

		function get_companion_plant_ids(){
			if(isset($this->info['id'])&&is_numeric($this->info['id'])){
				$this->companion_plant_ids = array();
				$result =mysql_query("(SELECT plant_id_1 AS id FROM plant_companion_plants WHERE plant_id_2=".$this->info['id'].") UNION (SELECT plant_id_2 AS id FROM plant_companion_plants WHERE plant_id_1=".$this->info['id'].")");
				$num_rows = mysql_num_rows($result);
				for($i=0;$i<$num_rows;$i++){
					$this->companion_plant_ids[] = mysql_result($result,$i,"id");
				}
			}
		}

		function website_output_companion_plants_html(){
			$ret = '';
			for($i=0;$i<count($this->companion_plant_ids);$i++){
				$companion_plant = new plant($this->companion_plant_ids[$i]);
				if($companion_plant->info['status_id']=='1'){
					$companion_plant->get_images();
					//var_dump($companion_plant->info['image_primary']);
					?>
						<div class="companion_plant_listing">
							<div class="thumbnail" style="background-image:url(<?php echo $companion_plant->info['image_primary']->info['path_detail_thumbnail']?>)"></div>
							<div class="common_name uppercase"><?php echo html_sanitize($companion_plant->info['common_name'])?></div>
							<div class="botanical_name"><?php echo html_sanitize($companion_plant->info['botanical_name'])?></div>
						</div>
						<div class="clear"></div>
					<?php
				}
			}
		}

		function output_attribute_js($attributes){
			$ret = '';
			for($i=0;$i<count($attributes);$i++){
				if(isset($attributes[$i])) $ret .= ",new plant_attribute('".$attributes[$i]->id."','".js_sanitize($attributes[$i]->name)."')";
			}
			if($ret!='') $ret = substr($ret,1);
			echo($ret);
		}

		function output_cms_image_segments_html(){
			if(count($this->image_sets)==0) $this->get_images(true);
			if(count($this->image_sets)==0){
				?>
					<div class="plant_image_segment">
						<div style="padding:85px 0px 0px 4px;text-align:center;font-size:12pt;font-weight:bold;">No images have been added.</div>
					</div>
				<?php
			}else{
				for($i=0;$i<count($this->image_sets);$i++){
					$image_set = $this->image_sets[$i];
					$image_set->determine_image_paths($this->info['common_name'],$this->info['item_number']);
					$special_status = '';
					if($image_set->info['is_primary']=='1') $special_status .= ' primary';
					if($image_set->info['is_active']=='0') $special_status .= ' inactive';
					if($image_set->info['is_distributable']=='1') $special_status .= ' distributable';
					?>
						<div class="plant_image_segment <?php echo $special_status?>" image_set_id="<?php echo $image_set->info['id']?>">
							<div style="padding:8px 4px;">
								<div class="thumbnail" style="background-image:url(<?php echo $image_set->info['path_search_result']?>)"></div>
								<div class="details">
									<div class="details_action"><a href="javascript:edit_plant_image(<?php echo $image_set->info['id']?>);void(0);">[edit]</a> <a href="javascript:edit_plant_image_delete(<?php echo $image_set->info['id']?>);void(0);">[delete]</a></div>
									<div class="details_title"><?php echo $image_set->info['title']?></div>
									<div class="details_status_inactive">Inactive</div>
									<div class="details_primary">Primary Image</div>
									<div class="details_distributable">Distributable</div>
									<div class="details_not_distributable">Not distributable</div>
									<div class="details_credit">Credit: <?php echo $image_set->info['photography_credit']?></div>
									<div class="details_expiration_date">Expiration Date: <?php echo $image_set->info['expiration_date']?></div>
									<div class="details_source">Source: <?php echo $image_set->info['source']?></div>

									<div class="details_links">
										Image sizes:<br />
										<ul>
											<li><a href="<?php echo $image_set->info['path_original']?>" target="_blank">Original</a></li>
											<li><a href="javascript:void(0);" onClick="lightview_show_image('<?php echo $image_set->info['path_detail']?>');">Plant details</a></li>
											<li><a href="javascript:void(0);" onClick="lightview_show_image('<?php echo $image_set->info['path_search_result']?>');">Search results</a></li>
											<li><a href="javascript:void(0);" onClick="lightview_show_image('<?php echo $image_set->info['path_detail_thumbnail']?>');">Plant details thumbnail</a></li>
										</ul>
									</div>
								</div>
							</div>
						</div>
					<?php
				}
			}
		}
		function website_output_details_thumbnails($version = 'desktop'){
			if(count($this->image_sets)==0) $this->get_images();
			if(count($this->image_sets)>0){
				for($i=0;$i<count($this->image_sets);$i++){
					$image_set = $this->image_sets[$i];
					$image_set->determine_image_paths($this->info['common_name'],$this->info['item_number']);
					if($image_set->info['is_active']=='1'){
						$title = html_sanitize($image_set->info['title']);
						$caption = $title;
						if($caption!='') $caption .= ' :: ';
						$credit = html_sanitize($image_set->info['photography_credit']);
						if($credit!='') $caption .= 'Credit: '.$credit; ?>
					
						<img src="<?php echo $image_set->info['path_detail_thumbnail']?>" hires="<?php echo $image_set->info['path_detail']?>" alt="" />
						<?php
						
					}
				}
			}
		}
		
		function website_output_details($version = 'desktop'){
			if(count($this->image_sets)==0) $this->get_images();
			if(count($this->image_sets)>0){
				for($i=0;$i<count($this->image_sets);$i++){
					$image_set = $this->image_sets[$i];
					$image_set->determine_image_paths($this->info['common_name'],$this->info['item_number']);
					if($image_set->info['is_active']=='1'){
						$title = html_sanitize($image_set->info['title']);
						$credit = html_sanitize($image_set->info['photography_credit']);
						if($credit!='') $credit = 'Credit: '.$credit; ?>
						<a href="<?php echo $image_set->info['path_original']?> " class="slide-a" data-lightbox="<?php echo $this->info['common_name']?>]" title="<?php echo $title." :: ".$credit?>" ><img src="<?php echo $image_set->info['path_detail']?>" alt="" />
                        </a>
						<?php $pin_url='http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; ?>
						<a style="display:<?php echo ($i==0) ? 'inline-block;' : 'none'; ?>" class="addthis_button_pinterest_pinit pinterest_pin <?php echo 'pin_'.$i; ?>" pi:pinit:url="<?php echo $pin_url; ?>" pi:pinit:media="<?php echo $image_set->info['path_detail'] ?>"></a>
						<?php
						
					}
				}
			}
		}

		function update_keywords(){
			$keywords = sql_sanitize(str_replace("'","''",$this->compile_keywords()));
			//echo("UPDATE plants SET keywords='$keywords' WHERE id=".$this->info['id'] . "<br />");
			sql_query("UPDATE plants SET keywords='$keywords' WHERE id=".$this->info['id']);
		}

		function compile_keywords(){

	$sql = <<<EOD

			SELECT GROUP_CONCAT(synonyms) AS keywords, plant_id FROM (

			(SELECT CONCAT_WS(',',name,synonyms) AS synonyms, table_fk.plant_id
			FROM list_garden_style table_list
			INNER JOIN plant_garden_style_plants table_fk ON table_list.id = table_fk.garden_style_id
			WHERE table_fk.plant_id='{PLANT_ID}')

			UNION ALL

			(SELECT CONCAT_WS(',',name,synonyms) AS synonyms, table_fk.plant_id
			FROM list_type table_list
			INNER JOIN plant_type_plants table_fk ON table_list.id = table_fk.type_id
			WHERE table_fk.plant_id='{PLANT_ID}')

			UNION ALL

			(SELECT CONCAT_WS(',',name,synonyms) AS synonyms, table_fk.plant_id
			FROM list_sun_exposure table_list
			INNER JOIN plant_sun_exposure_plants table_fk ON table_list.id = table_fk.sun_exposure_id
			WHERE table_fk.plant_id='{PLANT_ID}')

			UNION ALL

			(SELECT CONCAT_WS(',',name,synonyms) AS synonyms, table_fk.plant_id
			FROM list_growth_habit table_list
			INNER JOIN plant_growth_habit_plants table_fk ON table_list.id = table_fk.growth_habit_id
			WHERE table_fk.plant_id='{PLANT_ID}')

			UNION ALL

			(SELECT CONCAT_WS(',',name,synonyms) AS synonyms, table_fk.plant_id
			FROM list_flowering_season table_list
			INNER JOIN plant_flowering_season_plants table_fk ON table_list.id = table_fk.flowering_season_id
			WHERE table_fk.plant_id='{PLANT_ID}')

			UNION ALL

			(SELECT CONCAT_WS(',',name,synonyms) AS synonyms, table_fk.plant_id
			FROM list_flower_attribute table_list
			INNER JOIN plant_flower_attribute_plants table_fk ON table_list.id = table_fk.flower_attribute_id
			WHERE table_fk.plant_id='{PLANT_ID}')


			UNION ALL

			(SELECT CONCAT_WS(',',name,synonyms) AS synonyms, table_fk.plant_id
			FROM list_special_feature table_list
			INNER JOIN plant_special_feature_plants table_fk ON table_list.id = table_fk.special_feature_id
			WHERE table_fk.plant_id='{PLANT_ID}')

			UNION ALL

			(SELECT CONCAT_WS(',',name,synonyms) AS synonyms, table_fk.plant_id
			FROM list_landscape_use table_list
			INNER JOIN plant_landscape_use_plants table_fk ON table_list.id = table_fk.landscape_use_id
			WHERE table_fk.plant_id='{PLANT_ID}')


			UNION ALL

			(SELECT CONCAT_WS(',',name,synonyms) AS synonyms, table_fk.plant_id
			FROM list_garden_style table_list
			INNER JOIN plant_garden_style_plants table_fk ON table_list.id = table_fk.garden_style_id
			WHERE table_fk.plant_id='{PLANT_ID}')

			UNION ALL

			(SELECT CONCAT_WS(',',name,synonyms) AS synonyms, table_fk.plant_id
			FROM list_problem_solution table_list
			INNER JOIN plant_problem_solution_plants table_fk ON table_list.id = table_fk.problem_solution_id
			WHERE table_fk.plant_id='{PLANT_ID}')

			UNION ALL

			(SELECT CONCAT_WS(',',name,synonyms) AS synonyms, plants.id FROM list_growth_rate INNER JOIN plants ON plants.growth_rate_id = list_growth_rate.id WHERE plants.id='{PLANT_ID}')

			UNION ALL

			(SELECT CONCAT_WS(',',name,synonyms) AS synonyms, plants.id FROM list_deciduous_evergreen INNER JOIN plants ON plants.deciduous_evergreen_id = list_deciduous_evergreen.id WHERE plants.id='{PLANT_ID}')

			) t3 GROUP BY plant_id;

EOD;

			if(is_numeric($this->info['id'])){
				$result_colors_foliage =mysql_query("SELECT CONCAT_WS(',',name,synonyms) AS keywords, plants.id FROM list_foliage_color INNER JOIN plants ON plants.foliage_color_id = list_foliage_color.id WHERE plants.id='".$this->info['id']."'");

				$result_colors_flower =mysql_query("SELECT CONCAT_WS(',',name,synonyms) AS keywords, plants.id FROM list_flower_color INNER JOIN plants ON plants.flower_color_id = list_flower_color.id WHERE plants.id='".$this->info['id']."'");

				$sql = str_replace('{PLANT_ID}',$this->info['id'],$sql);
				$result =mysql_query($sql);
//die($sql);
				$keywords = '';
				if(mysql_num_rows($result)==1) $keywords.= $this->sanitize_keywords(mysql_result($result,0,"keywords"));
				if(mysql_num_rows($result_colors_foliage)==1) $keywords.= $this->sanitize_keywords(mysql_result($result_colors_foliage,0,"keywords"),' foliage');
				if(mysql_num_rows($result_colors_flower)==1) $keywords.= $this->sanitize_keywords(mysql_result($result_colors_flower,0,"keywords"),' flowers');

			}
			return $keywords;
		}

		function sanitize_keywords($keywords, $addition = ''){
				$keywords = str_replace(' ,',',',$keywords);
				$keywords = str_replace(', ',',',$keywords);
				$keywords = ',' . str_replace(',',$addition.',',trim($keywords,',')) . $addition . ',';
				$keywords = str_replace(',,',',',$keywords);

				if($addition!='') $keywords = str_replace($addition.$addition,$addition,$keywords);

				return strtolower($keywords);
		}
	}
	function get_plant_id_by_item_number($item_number){
		if(is_numeric($item_number)){
			$result =mysql_query("SELECT id FROM plants WHERE CAST(item_number AS UNSIGNED) = CAST('$item_number' AS UNSIGNED)");
			if(mysql_num_rows($result)==1){
				return mysql_result($result,0,"id");
			}else{
				return false;
			}
		}
	}
	class plant_attribute {
		function plant_attribute($id,$name,$is_historical,$is_primary){
			$this->id = $id;
			$this->name = $name;
			$this->is_historical = $is_historical;
			$this->is_primary = $is_primary;
		}
	}

	class plant_fertilizer {
		function plant_fertilizer($id,$name,$image_url){
			$this->id = $id;
			$this->name = $name;
			$this->image_url = $image_url;
		}
	}


	function generate_plant_seo_name($txt){
		$txt = strtolower($txt);
		$txt = str_replace('&trade;','',$txt);
		$txt = str_replace('&reg;','',$txt);
		$txt = str_replace('&#174;','',$txt);
		$txt = str_replace('&#153;','',$txt);

		// IF "formerly" IS IN PLANT NAME, TAKE ONLY PRECEDING TEXT
		$index = strpos($txt,'formerly');
		if($index!==false) $txt = substr($txt,0,$index-2);

		$txt = trim(str_replace(' ','-',parse_alpha($txt,'\\- ')),'-');
		$txt = str_replace('--','-',$txt);
		return $txt;
	}

	function update_plant_keywords($plant_ids){
		foreach($plant_ids as $plant_id){
			$plant = new plant($plant_id);
			$plant->update_keywords();
			//$plant->clear_cache();
			set_time_limit(60);	// ALLOW UP TO A MINUTE PER RECORD
		}
	}

/*
	class plant_unordered_attribute {
		function plant_unordered_attribute($id,$name,$is_historical,$is_primary){
			$this->id = $id;
			$this->name = $name;
			$this->is_historical = $is_historical;
			$this->is_primary = $is_primary;
		}
	}

*/
	//$p = new plant(1);$p->info['id'] = '';$p->save();\
	//$p = new plant(13);die($p->compile_keywords());

?>
