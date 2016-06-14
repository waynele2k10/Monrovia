<?
	require_once('../wp-content/uploads/plants/connect.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/monrovia/includes/utility_functions.php');
	require_once('../inc/class_plant.php');
	require_once('../inc/class_plant_image_set.php');

	$record_id = $_POST['id'];
	$record = new plant($record_id);
	$record->output_cms_image_segments_html();

?>