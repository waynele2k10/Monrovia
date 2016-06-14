<?
	session_start();
	require_once('../inc/class_monrovia_user.php');
	$monrovia_user = new monrovia_user($_SESSION['monrovia_user_id']);
	if(!contains($monrovia_user->info['permissions'],',caln,')) exit;

	if(!isset($_REQUEST['action'])||$_REQUEST['action']!='delete') exit;

	require_once('../inc/class_monrovia_event.php');
		
	$event_id = intval($_REQUEST['id']);
    
	$event = new monrovia_event($event_id);
  	$event->delete_thumbnail();
	
?>