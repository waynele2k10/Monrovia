<?php
	/*
		THE PURPOSE OF THIS SCRIPT IS TO POPULATE AN EVENT RECORD OBJECT WITH INFORMATION POSTED TO THE BACKEND FORM
	*/
	require_once('../inc/class_monrovia_event.php');
	$monrovia_user = new monrovia_user($_SESSION['monrovia_user_id']);

	// ENSURE USER HAS PERMISSIONS
	$monrovia_user->permission_requirement('cmgt');
	$monrovia_user->permission_requirement('caln');

	$event = new monrovia_event($_POST['id']);

	$fields = explode(',',$event->table_fields);
	for($i=0;$i<count($fields);$i++){
		$field_value = stripslashes(isset($_POST['event'][$fields[$i]])?$_POST['event'][$fields[$i]]:'');
		$field_value = str_replace('  ',' ',$field_value);

		// UPDATE FIELD ONLY IF INFO PROVIDED (THIS PREVENTS HISTORICAL DATA FROM BEING WIPED OUT). ALWAYS UPDATE BOOLEANS
		$update_field = (isset($_POST['event'][$fields[$i]])||strpos($fields[$i],'is_')===0);
		if($update_field) $event->info[$fields[$i]] = $field_value;
	}
    
    $dates_array = json_decode(stripcslashes($_POST['event']['event_dates']));
    
    $event->info['event_dates'] = $dates_array;

	$success = $event->save();

	if($success){
		output_page_notice('Your changes have been saved.');
	}else{
		output_page_notice('ERROR: An error occurred and your changes may not have been saved.');
	}

	$event = new monrovia_event($event->info['id']);	// RELOAD WITH NEW INFO
	$event->load_associated_data();
?>