<?php
	ini_set('error_reporting', E_ERROR|E_PARSE);
	error_reporting(E_ERROR|E_PARSE);
	
require( $_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );
include_once( $_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
/*
	if(isset($_GET['version'])){
		$version = $_GET['version'];	
	}else{
		exit;
	}
	*/
	if(!isset($_GET['template_id'])||intval($_GET['template_id'])==0) exit;

	require($_SERVER['DOCUMENT_ROOT'].'/inc/class_catalog.php');


	$catalog = new catalog();
	$catalog->info = $_GET;
	$catalog->info['id'] = 'temp';

	//header('Content-Type: image/jpeg');		
	if($catalog->generate_covers(true)){
		echo('success');
		//header('location:/downloads/pdf/custom_catalogs/temp/thumbnail_'.$version.'.jpg');
	}else{
		echo('fail');
		//header('location:/catalogs/templates/'.$catalog->info['template_id'].'_thumbnail_'.$version.'.jpg');
	}

?>