<?php

require( '../wp-load.php' );
include_once( '../wp-config.php');

include('../inc/class_plant.php');

	echo $item_number = intval(substr($_GET['item_number'],0,5));

		if(is_numeric($item_number)){
			$result = mysql_query("SELECT id FROM plants WHERE CAST(item_number AS UNSIGNED) = CAST('$item_number' AS UNSIGNED)");
			if(mysql_num_rows($result)==1){
				$plant_id = mysql_result($result,0,"id");
			}
		}
	
	$record = monrovia_get_plant_record( $plant_id );


	// TRACK 
	// Do we ever use this for anything??
	mysql_query("INSERT INTO qr_code_usage(date_occurred,item_number_raw,item_number_parsed,user_agent) VALUES('".date('Y-m-d H:i:s')."','".sql_sanitize($_GET['item_number'])."','".$item_number."','".sql_sanitize($_SERVER['HTTP_USER_AGENT'])."')");

	if($item_number>0){
		header("location:/plant-catalog/plants/".$record->info['id']."/".generate_plant_seo_name($record->info['common_name']));
	}else{
		header('location:/plant-catalog/');
	}
?>