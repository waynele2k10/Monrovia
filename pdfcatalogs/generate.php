<?php
require( '../wp-load.php' );
include_once( '../wp-config.php');

	//require_once($_SERVER['DOCUMENT_ROOT'].'/inc/init.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_catalog.php');
	
	global $current_user;
	get_currentuserinfo();
	
	$catalog_id = intval($_REQUEST['id']);
	$sorting_method = $_REQUEST['sorting_method']; // THIS IS SANITIZED LATER ON, SO NO NEED TO SANITIZE IT NOW
	$include_collection = intval($_REQUEST['include_collection_page']);

	if($catalog_id==0) exit;

	// CHECK IF ALREADY IN QUEUE
	$result = mysql_query("SELECT COUNT(*) AS already_queued FROM catalog_queue WHERE catalog_id = '$catalog_id' AND sorting_method='$sorting_method' AND (date_process_start = '0000-00-00' OR (date_process_start <> '0000-00-00' AND date_process_start > DATE_ADD(NOW(),INTERVAL -10 MINUTE)))");

	if(intval(mysql_result($result,0,'already_queued'))!=0) die('already queued');

	$catalog = new catalog($catalog_id);    
    
	if($catalog->info['is_official_catalog']!='1'&&$catalog->info['user_id']==$current_user->ID){
		mysql_query("INSERT INTO catalog_queue (catalog_id,sorting_method,include_collection,date_requested) VALUES('$catalog_id','$sorting_method','$include_collection',NOW())");
		echo('queued');
	}
?>