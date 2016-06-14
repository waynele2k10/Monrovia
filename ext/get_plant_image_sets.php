<?php
require( '../wp-load.php' );
include_once( '../wp-config.php');

	ini_set('error_reporting', E_ERROR|E_PARSE);
	error_reporting(E_ERROR|E_PARSE);
	$id = intval($_GET['id']);
	if($id==0) exit;

	require_once('../inc/class_sql.php');

	$result = mysql_query("SELECT id, title FROM plant_image_sets WHERE plant_id='$id' AND is_active=1 AND is_distributable=1 AND (expiration_date>NOW() OR expiration_date='0000-00-00') ORDER BY plant_image_sets.is_primary DESC, plant_image_sets.title ASC");

	$num_rows = intval(mysql_num_rows($result));
	$ret = array();
	
	for($i=0;$i<$num_rows;$i++){
		$ret[] = array(
			'id'=>mysql_result($result,$i,'id'),
			'title'=>mysql_result($result,$i,'title')
		);
	}
	echo(to_json($ret));
?>