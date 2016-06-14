<?
	/*session_start();
	require_once('../inc/class_monrovia_user.php');
	$monrovia_user = new monrovia_user($_SESSION['monrovia_user_id']);
	if(!contains($monrovia_user->info['permissions'],',pldb,')) exit; */
	require_once('../wp-content/uploads/plants/connect.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/monrovia/includes/utility_functions.php');
	require_once('../inc/class_plant_image_set.php');
	require_once('../inc/class_plant.php');

	$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
	$title = isset($_REQUEST['title'])?$_REQUEST['title']:'';
	$credit = isset($_REQUEST['credit'])?$_REQUEST['credit']:'';
	$expiration_date = isset($_REQUEST['expiration_date'])?$_REQUEST['expiration_date']:'';
	$source = isset($_REQUEST['source'])?$_REQUEST['source']:'';

	$is_active = isset($_REQUEST['is_active'])?$_REQUEST['is_active']:'0';
	$is_primary = isset($_REQUEST['is_primary'])?$_REQUEST['is_primary']:'0';
	$is_distributable = isset($_REQUEST['is_distributable'])?$_REQUEST['is_distributable']:'0';

//	$base_name = $_REQUEST['base_name'];
	$plant_id = $_REQUEST['plant_id'];
	$id = $_REQUEST['id'];
	$plant_image_set = new plant_image_set($id);

	if($action=='delete'){
		$plant_image_set->delete();
	}else{
		$plant_image_set->info['plant_id'] = $plant_id;
		$plant_image_set->info['is_active'] = $is_active;
		$plant_image_set->info['is_primary'] = $is_primary;
		$plant_image_set->info['is_distributable'] = $is_distributable;

		$plant_image_set->info['title'] = $title;
		$plant_image_set->info['expiration_date'] = $expiration_date;
		$plant_image_set->info['source'] = $source;

//		$plant_image_set->info['base_name'] = $base_name;
		$plant_image_set->info['photography_credit'] = $credit;
		$plant_image_set->save();
		$plant_image_set->generate_thumbnails();
	}
	//http://monrovia.localhost/monrovia_admin/update_image_set.php?credit=Doreen Wynja!&expiration_date=2009-11-30&id=677&is_active=0&is_distributable=0&is_primary=0&plant_id=776&source=source&title=Close Up!
?>