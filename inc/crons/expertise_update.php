<?php
	require($_SERVER['DOCUMENT_ROOT'].'/inc/class_plant.php');
	require($_SERVER['DOCUMENT_ROOT'].'/inc/class_plant_image_set.php');
	sql_disconnect();
	sql_set_user('med');
	sql_connect();
	
	function rename_expertise($val){
		
		$expertise_update_array = array('Asian' => 'Asian / Zen','Conservatory' => 'Conservatory / Indoor / Tropical','Tropical' => 'Conservatory / Indoor / Tropical','Desert' => 'Desert / Mediterranean / Water-efficient Garden','Mediterranean' => 'Desert / Mediterranean / Water-efficient Garden','Water-efficient Garden' => 'Desert / Mediterranean / Water-efficient Garden','Estate - 5 acre plus' => 'Estate - 5 acres or larger','Estate - 5 acres of larger' => 'Estate - 5 acres or larger','Vertical Gardening' => 'Green Wall / Vertical Gardening','Sustainable / Permaculture' => 'Sustainable / Permaculture / Native','Native' => 'Sustainable / Permaculture / Native','Woodland' => 'Woodland / Rustic / Natural','Rustic / Natural' => 'Woodland / Rustic / Natural','Coastal' => 'Water Garden / Pond / Coastal','Water Garden / Pond' => 'Water Garden / Pond / Coastal','Containers' => 'Urban / Small Space / Containers','Urban / Small Space' => 'Urban / Small Space / Containers','Edible' => 'Edible for humans or wildlife','Wildlife-nourishing Garden' => 'Edible for humans or wildlife','Streetscape / Median' => 'Streetscapes / Medians','Green Roof' => 'Green Roofs');
		
		//if value for the update isn't listed, keep the old value 
		if (isset($expertise_update_array[$val])) $val = $expertise_update_array[$val];
		return $val;
	}

	function update_expertise(){

		$result = sql_query("SELECT expertise,id FROM monrovia_profiles");

		while($row = mysql_fetch_array($result)){
			$exp = explode(',',$row['expertise']);
			$exp_new = array();
			
			//rename expertise values
			foreach ($exp as $value) {
				$val = rename_expertise(sql_sanitize($value));
				array_push($exp_new,$val);
			}
			
			//check if there are repeating values
			$updated_expertise = array_unique($exp_new);
			$expertise = implode(", ", $updated_expertise);
			
			sql_query("UPDATE monrovia_profiles SET expertise='".$expertise."' WHERE id=".$row['id']);
			
			echo("UPDATE monrovia_profiles SET expertise='".$expertise."' WHERE id=".$row['id']."<br/>");
			
		}
		echo('done');

	}

	update_expertise();
		 
	

?>