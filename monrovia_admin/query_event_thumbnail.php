<?
	require_once('../inc/class_monrovia_event.php');
	require_once('../inc/class_monrovia_event_image.php');
	$event_id = $_POST['id'];
        
	$event = new monrovia_event($event_id);
	$event->get_event_images();
	$event->output_thumbnail_html();
	sql_disconnect();
?>