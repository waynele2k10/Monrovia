<?php //Plant Tab Details 
	  //These details will have a lot of logic weather to
	  //display the tabs or not, depending if a specific tab
	  //has data in it or not
	  
	  // Create attribute friendly format for attribute values
	  function attributeFriendly($string){
		$new = strtolower(str_replace(' ', '-', $string)); 
		return $new; 
	  }

// Set up the variables we need to determine to create tabs or not
$overview = array();
$detail = array();
$care = array();
$history = array();

/* Overview Data */
// If it has Light Needs, Watering Needs, Size Key Feature, Flowering season Or Landscape
	$x=0;
	if(isset($record->info['sun_exposures_friendly'])&&$record->info['sun_exposures_friendly']!=''){
		$overview[$x]['attribute'] = "Light Needs:";
		$overview[$x]['value']= html_sanitize($record->info['sun_exposures_friendly']);
		$overview[$x]['img'] = $sunIcon; //Non-Generic
		$x++;
	}
	if(isset($record->info['water_requirement_details'])&&$record->info['water_requirement_details']!=''){
		$overview[$x]['attribute'] = "Watering Needs:";
		$overview[$x]['value']= html_sanitize($record->info['water_requirement_details']);
		$overview[$x]['img'] = $waterIcon[0]; //Non-Generic
		$x++;
	 }
	if(isset($record->info['average_landscape_size'])&&$record->info['average_landscape_size']!=''){
		$overview[$x]['attribute'] = "Average Landscape Size:";
		$overview[$x]['value']= html_sanitize($record->info['average_landscape_size']);
		$overview[$x]['img'] = "/img/catalog/icons/size_32.gif"; //Generic
		$x++;
	}
	/*if(isset($record->info['special_features'])&&count($record->info['special_features'])>0){
			$special_features_html = '';
			for($i=0;$i<count($record->info['special_features']);$i++){
				if(!isset($record->info['special_features'][$i]->is_historical)||$record->info['special_features'][$i]->is_historical!='1'){
					$special_features_html .= ', <a href="/plant-catalog/search/?special_feature=' . $record->info['special_features'][$i]->id . '">' . $record->info['special_features'][$i]->name . '</a>';
				}
			}
			$special_features_html = substr($special_features_html,2);
			if($special_features_html!=''){
				$overview[$x]['attribute'] = "Key Feature:";
				$overview[$x]['value']= $special_features_html;
				$overview[$x]['img'] = $keyIcon[0]; //Non-Generic
				$x++;
			}
		} */
		if(isset($record->info['primary_attribute'])&&$record->info['primary_attribute']!=''){
				$overview[$x]['attribute'] = "Key Feature:";
				$overview[$x]['value']= html_sanitize($record->info['primary_attribute']);
				$overview[$x]['img'] = '/img/catalog/icons/key_32.gif'; //Generic
				$x++;
		}
		if(isset($record->info['flowering_time'])&&$record->info['flowering_time']!=''){
			$overview[$x]['attribute'] = "Blooms:";
			$overview[$x]['value']= html_sanitize($record->info['flowering_time']);
			$overview[$x]['img'] = "/img/catalog/icons/flower_32.gif"; //Generic
			$x++;

		}
		if(isset($record->info['landscape_uses'])&&count($record->info['landscape_uses'])>0){
			$landscape_uses_html = '';
			for($i=0;$i<count($record->info['landscape_uses']);$i++){
				if(!isset($record->info['landscape_uses'][$i]->is_historical)||$record->info['landscape_uses'][$i]->is_historical!='1'){
					$landscape_uses_html .= ', <a href="/plant-catalog/search/?landscape_use=' . $record->info['landscape_uses'][$i]->id . '">' . $record->info['landscape_uses'][$i]->name . '</a>';
				}
			}
			$landscape_uses_html = substr($landscape_uses_html,2);
			if($landscape_uses_html!=''){
				$overview[$x]['attribute'] = "Landscape Uses:";
				$overview[$x]['value']= $landscape_uses_html;
				$overview[$x]['img'] = "/img/catalog/icons/landscape_32.gif"; //Generic
			}
		}
/** End Overview Data **/
/** Detail Data **/
		$x=0;
		if(isset($record->info['phonetic_spelling'])&&$record->info['phonetic_spelling']!=''){ 
			$detail[$x]['attribute'] = "Botanical Pronunciation:";
			$detail[$x]['value']= html_sanitize($record->info['phonetic_spelling']);
			$x++;	
		}
		if(isset($record->info['types'])&&count($record->info['types'])>0){
			$types_friendly_html = '';
			for($i=0;$i<count($record->info['types']);$i++){
				$types_friendly_html .= ', <a href="/plant-catalog/search/?type=' . $record->info['types'][$i]->id . '">' . $record->info['types'][$i]->name . '</a>';
			}
			$types_friendly_html = substr($types_friendly_html,2);
			if($types_friendly_html!=''){
				$detail[$x]['attribute'] = "Plant type:";
				$detail[$x]['value']= $types_friendly_html;
				$x++;
			}
		}
		if(isset($record->info['deciduous_evergreen'])&&$record->info['deciduous_evergreen']!=''){
				$detail[$x]['attribute'] = "Deciduous/evergreen:";
				$detail[$x]['value']= "<a href='/plant-catalog/search/?deciduous_evergreen_id=".$record->info['deciduous_evergreen_id']."'>".html_sanitize($record->info['deciduous_evergreen'])."</a>";
				$x++;
		}
		if(isset($record->info['sunset_zones_friendly'])&&$record->info['sunset_zones_friendly']!=''){
			$detail[$x]['attribute'] = "Sunset climate zones:";
			$detail[$x]['value']= html_sanitize($record->info['sunset_zones_friendly']);
			$x++;
		}
		if(isset($record->info['growth_habits'])&&count($record->info['growth_habits'])>0){
			$growth_habits_csv = generate_attribute_name_csv($record->info['growth_habits']);
			$detail[$x]['attribute'] = "Growth habit:";
			$detail[$x]['value']= $growth_habits_csv;
			$x++;
		}
		if(isset($record->info['growth_rate'])&&$record->info['growth_rate']!=''){
			$detail[$x]['attribute'] = "Growth rate:";
			$detail[$x]['value']=html_sanitize($record->info['growth_rate']);
			$x++;
		}
		if(isset($record->info['average_landscape_size'])&&$record->info['average_landscape_size']!=''){
			$detail[$x]['attribute'] = "Average landscape size:";
			$detail[$x]['value']=html_sanitize($record->info['average_landscape_size']);
			$x++;
		}
		if(isset($record->info['special_features'])&&count($record->info['special_features'])>0){
			$special_features_html = '';
			for($i=0;$i<count($record->info['special_features']);$i++){
				if(!isset($record->info['special_features'][$i]->is_historical)||$record->info['special_features'][$i]->is_historical!='1'){
					$special_features_html .= ', <a href="/plant-catalog/search/?special_feature=' . $record->info['special_features'][$i]->id . '">' . $record->info['special_features'][$i]->name . '</a>';
				}
			}
			$special_features_html = substr($special_features_html,2);
			if($special_features_html!=''){
				$detail[$x]['attribute'] = "Special features:";
				$detail[$x]['value']= $special_features_html;
				$x++;
			}
		}
		if(isset($record->info['foliage_color'])&&$record->info['foliage_color']!=''){
			$detail[$x]['attribute'] = "Foliage color:";
			$detail[$x]['value']= "<a href='/plant-catalog/search/?foliage_color_id=".html_sanitize($record->info['foliage_color_id'])."'>".html_sanitize($record->info['foliage_color'])."</a>";
			 $x++;
		}
		if(isset($record->info['flowering_time'])&&$record->info['flowering_time']!=''){
			$detail[$x]['attribute'] = "Blooms:";
			$detail[$x]['value']= html_sanitize($record->info['flowering_time']);
			$x++;

		}
		if(isset($record->info['flower_color'])&&$record->info['flower_color']!=''){
			$detail[$x]['attribute'] = "Flower color:";
			$detail[$x]['value']= "<a href='/plant-catalog/search/?flower_color_id=".html_sanitize($record->info['flower_color_id'])."'>".html_sanitize($record->info['flower_color'])."</a>";
			$x++;
		}
		if(isset($record->info['flower_attributes'])&&count($record->info['flower_attributes'])>0){
			$flower_attributes_html = '';
			for($i=0;$i<count($record->info['flower_attributes']);$i++){
				if(!isset($record->info['flower_attributes'][$i]->is_historical)||$record->info['flower_attributes'][$i]->is_historical!='1'){
					$flower_attributes_html .= ', <a href="/plant-catalog/search/?flower_attribute=' . $record->info['flower_attributes'][$i]->id . '">' . $record->info['flower_attributes'][$i]->name . '</a>';
				}
			}
			$flower_attributes_html = substr($flower_attributes_html,2);
			if($flower_attributes_html!=''){
				$detail[$x]['attribute'] = "Flower attributes";
				$detail[$x]['value']=$flower_attributes_html;
			 $x++;
			} 
		}
		if(isset($record->info['garden_styles'])&&count($record->info['garden_styles'])>0){
			$garden_styles_html = '';
			for($i=0;$i<count($record->info['garden_styles']);$i++){
				$garden_styles_html .= ', <a href="/plant-catalog/search/?garden_style=' . $record->info['garden_styles'][$i]->id . '">' . $record->info['garden_styles'][$i]->name . '</a>';
			}
			$garden_styles_html = substr($garden_styles_html,2);
			if($garden_styles_html!=''){
			$detail[$x]['attribute'] = "Garden style";
			$detail[$x]['value']= $garden_styles_html;
			$x++;
			}
		}
		if(isset($record->info['patent_act'])&&$record->info['patent_act']!=''){
			$detail[$x]['attribute'] = "Patent Act:";
			$detail[$x]['value']= html_sanitize($record->info['patent_act']);
			$x++;
		}
		if(isset($record->info['description_design'])&&$record->info['description_design']!=''){
			$detail[$x]['attribute'] = "Design Ideas";
			$detail[$x]['value']= html_sanitize($record->info['description_design']);
			$detail[$x]['class']= 'paragraph';
			$x++;
		}
		if(isset($record->info['description_companion_plants'])&&$record->info['description_companion_plants']!=''){
			$detail[$x]['attribute'] = "Companion Plants";
			$detail[$x]['value']= html_sanitize($record->info['description_companion_plants']);
			$detail[$x]['class']= 'paragraph';
		}
/** End Detail Data **/
/** Begin Care Data **/
			$x=0;
			if(isset($record->info['description_care'])&&$record->info['description_care']!=''){
				if(isset($record->info['pruning_time'])&&$record->info['pruning_time']!='') $pruning_time = 'Pruning time: ' . html_sanitize(strtolower($record->info['pruning_time'])) . '.';
	
			$care[$x]['attribute'] = "Care Information";
			$care[$x]['value']= html_sanitize($record->info['description_care']).(isset($pruning_time)?$pruning_time:'');
			$care[$x]['class']= 'paragraph';
			$x++;
			}
			if(isset($record->info['sun_exposures_friendly'])&&$record->info['sun_exposures_friendly']!=''){
				$care[$x]['attribute'] = "Light Needs:";
				$care[$x]['value']= html_sanitize($record->info['sun_exposures_friendly']);
				$care[$x]['img'] = $sunIcon; //Non-Generic
				$x++;
			}
			if(isset($record->info['water_requirement_details'])&&$record->info['water_requirement_details']!=''){
				$care[$x]['attribute'] = "Watering Needs:";
				$care[$x]['value']= html_sanitize($record->info['water_requirement_details']);
				$care[$x]['img'] = $waterIcon[0]; //Non-Generic
				$x++;
			}			 
/** End Care Data **/
/** Start History Data **/
		$x=0;
		if(isset($record->info['description_history'])&&$record->info['description_history']!=''){ 
			$history[$x]['attribute'] = "History:";
			$history[$x]['value']= html_sanitize($record->info['description_history']);
			$x++;
		}
		if(isset($record->info['description_lore'])&&$record->info['description_lore']!=''){
			$history[$x]['attribute'] = "Lore:";
			$history[$x]['value']= html_sanitize($record->info['description_lore']);
		}
/** End History Data **/


//print_r($detail);
?>

<div class="accordian-wrap">
	<ul class="accordian clear">
    <?php  //If a section has data, print out the tab
		/*if(count($overview)>0) echo "<li><a href='#Overview'>Overview</a></li>";
		if(count($detail)>0) echo "<li><a href='#Detail'>Detail</a></li>";
		if(count($care)>0) echo "<li><a href='#Care'>Care</a></li>";
		if(count($history)>0) echo "<li><a href='#History'>History & Lore</a></li>";
		*/
	?>
    	<!-- Print out Overview Data -->
		<?php if(count($overview)>0){
			echo "<li><a href='#Overview' class='open'>Overview</a>";
			echo "<div id='Overview' class='accordianTarget' style='display:block;'>";
				$i=0;
				foreach($overview as $item){
					if($i==0 || $i ==3) echo "<div class='row clear'>";
					echo "<div class='attribute left overview'>";
					echo "<div class='label'>".$item['attribute']."</div>";
					//echo "<img class='icons left' src='".$theme_path.$item['img']."' title='".$icon_alts[$item['img']]."' alt='".$icon_alts[$item['img']]."' />";
					echo "<img class='icons left' src='".$theme_path.$item['img']."' data-tooltip='".$icon_alts[$item['img']]."' />";
					echo "<div class='value left'>".$item['value']."</div>";
					echo "</div>";
					if($i==2) echo "</div>";
					$i++;	
				}
				if($i!=3) echo "</div>";
			echo "</div></li>";
		} ?>
		<!-- Print out Detail Data -->
        <?php if(count($detail)>0){
			echo "<li><a href='#Detail'>Detail</a>";
			echo "<div id='Detail' class='accordianTarget'>";
				foreach($detail as $item){
					if(isset($item['class'])){
						echo "<div class='attribute details clear paragraph'>";
					} else{
						echo "<div class='attribute details clear'>";
					}
					echo "<span class='label'>". $item['attribute'] . "</span>";
					echo "<span class='left'>" . $item['value'] . "</span>";
					echo "</div>";	
				}
			echo "</div></li>";
		} ?>
        <!-- Print out Detail Data -->
        <?php if(count($care)>0){
			echo "<li><a href='#Care'>Care</a>";
			echo "<div id='Care' class='clear accordianTarget'>";
			$i=0;
				foreach($care as $item){
					if(isset($item['class'])){
						echo "<div class='attribute care paragraph'>";
						echo "<div class='label'>".$item['attribute']."</div>";
						echo $item['value'];
						echo "</div>";	
					} else{
						if($i==0 || $i ==3) echo "<div class='row clear'>";
						echo "<div class='attribute care left'>";
						echo "<div class='label'>".$item['attribute']."</div>";
						echo "<img class='icons left' src='".$theme_path.$item['img']."' title='".$icon_alts[$item['img']]."' alt='".$icon_alts[$item['img']]."' />";
					echo "<div class='value left'>".$item['value']."</div>";
					echo "</div>";
						$i++;
					}
					if($i==3) echo "</div><!-- end row -->";
				}
				if($i!=3) echo "</div><!-- end row -->";
			echo "</div><!-- end Care --></li>";
		} ?>
        <!-- Print out Detail Data -->
        <?php if(count($history)>0){
			echo "<li><a href='#History'>History & Lore</a>";
			echo "<div id='History' class='accordianTarget'>";
				foreach($history as $item){
					echo "<div class='history paragraph'>";
					echo "<div class='label'>".$item['attribute']."</div>";
					echo $item['value'];
					echo "</div>";	
				}
			echo "</div></li>";
		} ?>
	</ul><!-- end accordian -->
</div><!-- end accordian -->
