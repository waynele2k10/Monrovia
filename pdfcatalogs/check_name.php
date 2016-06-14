<?php
	header('Content-Type: text/javascript');

	$name = md5($_POST['name']); // SANITIZES
	$exclude_id = 0;
	if(isset($_POST['exclude_id'])) $exclude_id = intval($_POST['exclude_id']);

	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/init.php');
	
	$sql = "SELECT COUNT(*) AS is_invalid FROM catalogs WHERE user_id='" . $current_user->ID . "' AND MD5(name)='" . $name . "'";
	
	if($exclude_id>0) $sql .= ' AND id <> ' . $exclude_id;
	
	$results = mysql_query($sql);
	
	echo(mysql_result($results,0,'is_invalid')=='1'?'invalid':'valid');
?>