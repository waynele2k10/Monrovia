<?php
	$file_name = 'track_editable_module_locks.txt';
	$last_modified = filemtime($file_name);
	$seconds_since_last_touch = (time()-$last_modified);

	if($seconds_since_last_touch>(5 * 60)){
		// IF NO CONTENT EDITOR HAS BEEN ON A PAGE IN THE LAST 5 MINS, CLEAR ALL LOCKS
		require($_SERVER['DOCUMENT_ROOT'].'/inc/class_sql.php');
		sql_disconnect();
		sql_set_user('med');
		sql_connect();
		sql_query('UPDATE editable_modules SET locked_by="" WHERE locked_by <> "";');
	}
?>