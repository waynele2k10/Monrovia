<?php
set_time_limit(0);
include_once( 'wp-config.php');
if (isset($_GET['plant_id'])) {
	$_plant_id = $_GET['plant_id'];
	$_sql = "SELECT * FROM plant_image_sets WHERE is_active = 1 AND plant_id = ".$_plant_id;
	$result = mysql_query($_sql);
	$num_rows = mysql_num_rows($result);
	$_plants = array();
	if ($num_rows > 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$row['url'] = "http://".$_SERVER['HTTP_HOST']."/wp-content/uploads/plants/originals/".$row['id'].".jpg";
			$_plants[] = $row;
		}
		echo json_encode($_plants);
	}
}
