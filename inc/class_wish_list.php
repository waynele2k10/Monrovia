<?php
	require_once('class_record.php');
	class wish_list extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM plant TABLE, EXCEPT FOR id
		var $table_fields = 'wish_list_name,user_id,date_last_accessed';

		function wish_list($record_id = ''){
			$this->table_name = 'wish_lists';

	//		$this->info['wish_list_name'] = 'TODO - John\'s Wish List';
			$this->items = array();
			if($record_id!=''){
				$this->load($record_id);

				// SQL INJECTION-SAFE
				if(is_numeric($this->info['id'])){
					$result = sql_query("SELECT wish_lists.id,wish_lists.wish_list_name,wish_lists.user_id,wish_list_items.plant_id,wish_list_items.notes, plants.item_number FROM wish_lists INNER JOIN wish_list_items ON wish_list_items.wish_list_id = wish_lists.id LEFT JOIN plants ON wish_list_items.plant_id = plants.id WHERE wish_lists.id=".$this->info['id'] . " AND plants.is_active=1 AND plants.release_status_id IN (1,2,3,4,6)");
					$num_rows = intval(@mysql_numrows($result));
					if($num_rows>0){
						$this->info['wish_list_name'] = mysql_result($result,0,"wish_list_name");
						$this->info['user_id'] = mysql_result($result,0,"user_id");
						for($i=0;$i<$num_rows;$i++){
							$item = new wish_list_item($this->info['id'],mysql_result($result,$i,"plant_id"),mysql_result($result,$i,"item_number"),mysql_result($result,$i,"notes"));
							$this->items[] = $item;
						}
					}
				}
			}
		}
		function set_last_accessed_date(){
			if($this->info['id']!=''){
				sql_query("UPDATE wish_lists SET date_last_accessed = NOW() WHERE id=".$this->info['id']);
			}
		}
		function save(){
			$this->clear();
			foreach($this->items as &$item){
				$this->item_add($item);
			}
			parent::save();
		}
		function delete(){
			$this->clear();
			parent::delete();
		}
		function clear(){
			if(is_numeric($this->info['id'])){
				sql_query("DELETE FROM wish_list_items WHERE wish_list_id='".$this->info['id']."'");
			}
			unset($this->items);
			$this->items = array();
			$this->set_last_accessed_date();
		}
		function item_add(&$item){

			$notes = sql_sanitize($item->info['notes']);
			if(is_numeric($item->info['wish_list_id'])&&is_numeric($item->info['plant_id'])&&!is_suspicious(ids_sanitize($notes))){
				$ret = sql_query("INSERT INTO wish_list_items (wish_list_id,plant_id,notes) VALUES('".$this->info['id']."','".$item->info['plant_id']."','".$notes."')");
				if($ret>0) $this->items[] = $item;
				$this->set_last_accessed_date();
			}
			return ($ret>0);
		}
		function item_update(&$item){
			$notes = sql_sanitize(strip_tags($item->info['notes']));
			if(is_numeric($item->info['wish_list_id'])&&is_numeric($item->info['plant_id'])&&!is_suspicious(ids_sanitize($notes))){
				$ret = sql_query("UPDATE wish_list_items SET notes='".$notes."' WHERE wish_list_id='".$item->info['wish_list_id']."' AND plant_id='".$item->info['plant_id']."'");
				//$this->set_last_accessed_date(); // NOT NEEDED HERE, SINCE THE ONLY WAY TO UPDATE AN ITEM IS VIA THE WISH LIST PAGE
			}
			return ($ret>0);
		}
		function item_remove(&$item){
			if(is_numeric($this->info['id'])&&is_numeric($item->info['plant_id'])){
				$ret = sql_query("DELETE FROM wish_list_items WHERE wish_list_id='".$this->info['id']."' AND plant_id='".$item->info['plant_id']."'");
				if($ret>0){
					// REMOVE FROM ARRAY
					for($i=0;$i<count($this->items);$i++){
						if($item->info['plant_id']==$this->items[$i]->info['plant_id']) unset($this->items[$i]);
					}
				}
				//$this->set_last_accessed_date(); // NOT NEEDED HERE, SINCE THE ONLY WAY TO REMOVE AN ITEM IS VIA THE WISH LIST PAGE
			}
			return ($ret>0);
		}
		function get_item($plant_id){
			foreach($this->items as &$item){
				if($item->info['plant_id']==$plant_id) return $item;
			}
			return '';
		}
		function xls_export_string(){

			set_time_limit(60*3);	// ALLOW UP TO THREE MINUTES FOR EXPORT

			require_once('class_plant.php');

			$ret = '<html><table border="1"><tr><td colspan="2" bgcolor="#ffff00"><center><b>MONROVIA</b></center></td><td colspan="2" bgcolor="#ffcc99"><center><b>PLANT NAME</b></center></td><td colspan="2" bgcolor="#ccffff"><center><b>HARDINESS ZONE</b></center></td><td bgcolor="#cc99ff"><center><b>MATURE PLANT</b></center></td><td colspan="3" bgcolor="#ff9900"><center><b>FOLIAGE</b></center></td><td colspan="3" bgcolor="#3366ff"><center><b>FLOWER</b></center></td><td colspan="3" bgcolor="#c0c0c0"><center><b>CULTURAL</b></center></td><td bgcolor="#00aa00"><center><b>OTHER</b></center></td></tr><tr><td><center><b>Monrovia Item #</b></center></td><td><center><b>Introduction Year</b></center></td><td><center><b>Botanical Name</b></center></td><td><center><b>Common Name</b></center></td><td><center><b>Cold Hardiness Zone</b></center></td><td><center><b>Sunset Hardiness Zone</b></center></td><td><center><b>Growth Habit, Mature Landscape Height and Width</b></center></td><td><center><b>Spring Foliage Color</b></center></td><td><center><b>Fall Foliage Color</b></center></td><td><center><b>Foliage Shape</b></center></td><td><center><b>Flower Color</b></center></td><td><center><b>Bloom Time</b></center></td><td><center><b>Ornamental or Edible Fruit</b></center></td><td><center><b>Origin</b></center></td><td><center><b>Light Exposure</b></center></td><td><center><b>Water Needs</b></center></td><td><center><b>Your Notes</b></center></td></tr>';

			foreach($this->items as &$item){

				$plant = new plant($item->info['plant_id']);

				$year_introduced = ($plant->info['year_introduced']!='0')?$plant->info['year_introduced']:'';
				$sunset_zones = ($plant->info['sunset_zones_friendly']!='')?'Zones '.$plant->info['sunset_zones_friendly']:'';
				$cold_zones = ($plant->info['cold_zones_friendly']!='')?'Zones '.$plant->info['cold_zones_friendly']:'';

				$ret .= '<tr><td>'.$plant->info['item_number'].'</td><td>'.$year_introduced.'</td><td>'.$plant->info['botanical_name'].'</td><td>'.$plant->info['common_name'].'</td><td>'.$cold_zones.'</td><td>'.$sunset_zones.'</td><td>'.$plant->info['average_landscape_size'].'</td><td>'.$plant->info['foliage_color_spring'].'</td><td>'.$plant->info['foliage_color_fall'].'</td><td>'.$plant->info['foliage_shape'].'</td><td>'.$plant->info['flower_color'].'</td><td>'.$plant->info['flowering_time'].'</td><td>'.$plant->info['Ornamental or Edible Fruit'].'</td><td>'.$plant->info['geographical_origin'].'</td><td>'.$plant->info['sun_exposures_friendly'].'</td><td>'.$plant->info['water_requirement_details'].'</td><td>'.$item->info['notes'].'</td></tr>';
			}
			$ret .= '</table></html>';
			return $ret;
		}
		function email_export_string($wish_list_title,$version = 'email'){

			set_time_limit(60*3);	// ALLOW UP TO THREE MINUTES FOR EXPORT

			require_once('class_plant.php');

			if($version=='print'){
				$template_main = file_get_contents('../community/email_templates/wish_list_print.htm');
				$template_items = file_get_contents('../community/email_templates/wish_list_items_print.htm');
			}else{
				$template_main = file_get_contents('../community/email_templates/wish_list.htm');
				$template_items = file_get_contents('../community/email_templates/wish_list_items.htm');
			}

			$html_items = '';

			foreach($this->items as &$item){
				$plant = new plant($item->info['plant_id']);
				//$plant->get_images();
				$plant->get_primary_image();

				$temp = $template_items;
				$common_name = strtoupper($plant->info['common_name']);
				$common_name = str_replace('&TRADE;','&trade;',$common_name);
				$common_name = str_replace('&REG;','&reg;',$common_name);
				$common_name = str_replace('&#174;','&reg;',$common_name);
				$common_name = str_replace('&#153;','&trade;',$common_name);

				$temp = str_replace('{common_name}','<b>'.$common_name.'</b><br />',$temp);
				if(isset($plant->info['botanical_name'])&&$plant->info['botanical_name']!='') $temp = str_replace('{botanical_name}','<i>'.$plant->info['botanical_name'].'</i>',$temp);

				$temp = $this->email_export_fieldset($temp,'Monrovia Item #: ','item_number',isset($plant->info['item_number'])?$plant->info['item_number']:'',$version);
				$temp = $this->email_export_fieldset($temp,'Cold Zones: ','cold_zones',isset($plant->info['cold_zones_friendly'])?$plant->info['cold_zones_friendly']:'',$version);
				$temp = $this->email_export_fieldset($temp,'Light Exposure: ','sun_exposures',isset($plant->info['sun_exposures_friendly'])?$plant->info['sun_exposures_friendly']:'',$version);
				$temp = $this->email_export_fieldset($temp,'Flower Color: ','flower_color',isset($plant->info['flower_color'])?$plant->info['flower_color']:'',$version);
				$temp = $this->email_export_fieldset($temp,'Bloom Time: ','flowering_time',isset($plant->info['flowering_time'])?$plant->info['flowering_time']:'',$version);
				$temp = $this->email_export_fieldset($temp,'Water Needs: ','water_requirements',isset($plant->info['water_requirements'])?$plant->info['water_requirements']:'',$version);
				$temp = $this->email_export_fieldset($temp,'Your Notes: ','notes',isset($item->info['notes'])?$item->info['notes']:'',$version);

				$temp = str_replace('{plant_details_url}',$plant->info['details_url'],$temp);

				$image_path = '';
				if(isset($plant->info['image_primary'])) $image_path = $plant->info['image_primary']->info['path_search_result'];
				if($image_path=='') $image_path = 'http://www.monrovia.com/img/404_sr.gif';

				$temp = str_replace('{plant_image_url}',$image_path,$temp);

				$html_items .= $temp;
			}

			$ret = $template_main;
			$ret = str_replace('{wish_list_items}',$html_items,$ret);
			$ret = str_replace('{wish_list_title}',$wish_list_title,$ret);

			return trim($ret);
		}
		function email_export_fieldset($html,$field_friendly_name,$field_name,$field_value,$version = 'email'){
			$s_replace = '';
			if($field_value!=''){
				if($version=='print'){
					$s_replace = '<div class="field_label">'.$field_friendly_name.'</div><div class="field_value">'.html_sanitize($field_value).'</div><div style="clear:both;"></div>';
				}else{
					$s_replace = '<tr><td width="100" valign="top"><font size="2" face="arial" color="#666666"><nobr>'.$field_friendly_name.'</nobr></font></td><td width="600"><font size="2" face="arial">'.html_sanitize($field_value).'</font></td></tr>';
				}
			}
			return str_replace('{'.$field_name.'}',$s_replace,$html);
		}
	}
	class wish_list_item {
		function wish_list_item($wish_list_id,$plant_id,$plant_item_number,$notes){
			$this->info['wish_list_id'] = $wish_list_id;
			$this->info['plant_id'] = $plant_id;
			$this->info['plant_item_number'] = $plant_item_number;
			$this->info['notes'] = $notes;
		}
	}
?>