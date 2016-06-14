<?
	session_start();
	require_once('../inc/class_monrovia_user.php');
	$monrovia_user = new monrovia_user($_SESSION['monrovia_user_id']);
	if(!contains($monrovia_user->info['permissions'],',caln,')) exit;

	require_once('../inc/class_monrovia_event_image.php');
	//require_once('../inc/class_monrovia_event.php');

	$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
	$title = isset($_REQUEST['title'])?$_REQUEST['title']:'';
	
	$is_active = isset($_REQUEST['is_active'])?$_REQUEST['is_active']:'0';

	//$monrovia_event_id = $_REQUEST['event_id'];
	$id = $_REQUEST['id'];
	$event_image = new monrovia_event_image();
    $event_image -> event_image_set($id);

	if($action=='delete'){
		$event_image->delete();
	}else{
		//$event_image->info['monrovia_event_id'] = $monrovia_event_id ;
		$event_image->info['is_active'] = $is_active;
		$event_image->info['title'] = $title;
        
		$event_image->save();
		$event_image->generate_thumbnails();
	}
	
?>