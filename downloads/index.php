<?
	function is_valid_type($file_name){
		$valid_types = array('.jpg','.zip','.jpeg','.pdf');
		$valid_type_mimes = array('image/jpeg','application/zip','image/jpeg','application/pdf');
		for($i=0;$i<count($valid_types);$i++){
			if(strpos(strtolower($file_name),$valid_types[$i])!==false){
				header("Content-Type:".$valid_type_mimes[$i]);
				return true;
			}
		}
		return false;
	}

	ini_set('zlib.output_compression','Off');

	$file_path = $_GET['file'];

	if($file_path==''){
		header('location:/');
		exit;
	}
	$file_name = basename($file_path);

	// MAKE SURE ONLY LOOKING IN downloads FOLDER AND ONLY OF CERTAIN FILE TYPES
	if((strpos(realpath($file_path),'downloads')!==false)&&is_valid_type($file_name)){
		// OUTPUT FORCE-DOWNLOAD HEADER
		header("Pragma:public");
		header("Expires:0");
		header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
		header("Cache-Control:private",false);
		header("Content-Disposition:attachment;filename=\"".$file_name."\"");
		header("Content-Transfer-Encoding:binary");
		header("Content-Length:".filesize($file_path));

		// OUTPUT FILE CONTENTS
		$file = fopen($file_path,'rb');
		if($file) fpassthru($file);
	}else{
		header('location:/');
		exit;
	}
	
	// http://monrovia.localhost/downloads/?file=pdf/monrovia_catalog_2011.pdf
	
?>