<?

	$file_name = 'track_editable_module_locks.txt';
	if(!file_exists($file_name)){
	die('wtf');
		$file_handler = fopen($file_name,'w');
		fclose($file_handler);
	}else{
		die(touch($file_name).'!');
	}

?>