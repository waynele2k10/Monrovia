<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/init.php');
	
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_catalog.php');
	
	$catalog_id = intval($_GET['id']);
	$sorting_method = $_GET['sorting_method']; // THIS IS SANITIZED LATER ON, SO NO NEED TO SANITIZE IT NOW

	$catalog = new catalog($catalog_id);
	
	if($catalog->info['is_official_catalog']!='1'&&$catalog->info['user_id']==$monrovia_user->info['id']){
		$path = $catalog->generate_pdf($sorting_method);
		if($path==''){
			die('Sorry! An unexpected error occurred.');
		}else{
				header('Content-type:application/pdf');
				if(!@readfile('..'.$path)) die('Sorry! An unexpected error occurred.');
				exit;
		}
	}else{
		die('Sorry! An error occurred. Please make sure you are <a href="/community/login.php">logged in</a>.');
	}
?>