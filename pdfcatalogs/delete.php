<?php

	
require( '../wp-load.php' );
include_once( '../wp-config.php');

	//require_once($_SERVER['DOCUMENT_ROOT'].'/inc/init.php');
	
	// MAKE SURE USER HAS CATALOG PERMISSION
	global $current_user;
	get_currentuserinfo();

	
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_catalog.php');
	
	$catalog_id = intval($_POST['id']);
	$catalog = new catalog($catalog_id);
	
	if($catalog->info['is_official_catalog']!='1'&&$catalog->info['user_id']==$current_user->ID){
		if($catalog->delete()){
			echo('success');
		} else {
			echo('fail');
		}
	}
?>