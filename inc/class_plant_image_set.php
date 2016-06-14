<?php
	ini_set('memory_limit', '300M'); // INCREASE MEMORY LIMIT TO ALLOW FOR IMAGE PROCESSING OF LARGE IMAGES
	ini_set('gd.jpeg_ignore_warning',1);

	require_once('class_record.php');

	class plant_image_set extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM plant TABLE, EXCEPT FOR id
		var $table_fields = 'plant_id,is_active,is_primary,title,photography_credit,is_distributable,expiration_date,source';

		function plant_image_set($record_id = ''){
			$this->table_name = 'plant_image_sets';
			if($record_id!='') $this->load($record_id);
		}
		function load($record_id){
			parent::load($record_id);
			if($this->info['expiration_date']=='0000-00-00') $this->info['expiration_date'] = '';
			$this->determine_image_paths();
		}
		function save(){
			// MAKE SURE ONLY ONE PRIMARY IMAGE FOR THIS PLANT
			if($this->info['is_primary']=='1'){
				$this->info['is_active'] = '1';
				if(is_numeric($this->info['plant_id'])){
					sql_query("UPDATE $this->table_name SET is_primary=0 WHERE plant_id=".$this->info['plant_id']);
				}
			}
			sql_query("UPDATE plants SET synch_with_mage=1 WHERE id=".$this->info['plant_id']);
			parent::save();
			// CLEAR CACHE
			require_once('class_plant.php');
			$plant = new plant();
			$plant->info['id'] = $this->info['plant_id'];
			//$plant->clear_cache();

			//$this->generate_thumbnails();
		}
		function delete(){
			unlink($this->info['server_path_original']);
			unlink($this->info['server_path_detail']);
			unlink($this->info['server_path_search_result']);
			unlink($this->info['server_path_detail_thumbnail']);
			sql_query("DELETE FROM plant_image_downloads WHERE plant_image_set_id = " . $this->info['id']);
			sql_query("UPDATE plants SET synch_with_mage=1 WHERE id=".$this->info['plant_id']);
			parent::delete();
		}
		function determine_image_paths($plant_name='monrovia',$plant_item_number=''){
			// GENERATE IMAGE PATHS PER NAMING CONVENTION
			// THIS METHOD IS CALLED BY plant_edit.php AND PASSED A PLANT NAME
			require_once('class_plant.php');

			$base_name = generate_plant_seo_name($plant_name.'-'.(isset($this->info['title'])?$this->info['title']:''));

			$this->info['path_original'] = 	"http://".$_SERVER['HTTP_HOST'].'/wp-content/uploads/plants/originals/'.$this->info['id'].'.jpg';
			$this->info['path_detail'] = 	"http://".$_SERVER['HTTP_HOST'].'/wp-content/uploads/plants/details/'.$this->info['id'].'.jpg';
			$this->info['path_search_result'] = "http://".$_SERVER['HTTP_HOST'].'/wp-content/uploads/plants/search_results/'.$this->info['id'].'.jpg';
			$this->info['path_detail_thumbnail'] = "http://".$_SERVER['HTTP_HOST'].'/wp-content/uploads/plants/details_thumbnails/'.$this->info['id'].'.jpg';

			$this->info['server_path_original'] = $_SERVER['DOCUMENT_ROOT'].'/wp-content/uploads/plants/originals/' . $this->info['id'] . '.jpg';
			$this->info['server_path_detail'] = $_SERVER['DOCUMENT_ROOT'].'/wp-content/uploads/plants/details/' . $this->info['id'] . '.jpg';
			$this->info['server_path_search_result'] = $_SERVER['DOCUMENT_ROOT'].'/wp-content/uploads/plants/search_results/' . $this->info['id'] . '.jpg';
			$this->info['server_path_detail_thumbnail'] = $_SERVER['DOCUMENT_ROOT'].'/wp-content/uploads/plants/details_thumbnails/' . $this->info['id'] . '.jpg';
		}
		function generate_thumbnails(){
			$success = true;
			try {
				$this->determine_image_paths();
				if($success) $success = ($this->resize_detail()!='');
				if($success) $success = ($this->resize_search_result()!='');
				if($success) $success = ($this->resize_detail_thumb()!='');
			}catch(Exception $err){
				$success = false;
			}
			return $success;
		}

		function resize_detail(){

			try{
				$dest_filename = $this->info['server_path_detail'];
				$src = imagecreatefromjpeg($this->info['server_path_original']);
				if(!$src) return false;
				$src_width = imagesx($src);
				$src_height = imagesy($src);

				if($src_width > $src_height) {
					$dest_width = 379;
					$dest_height = $src_height * ($dest_width/$src_width);
				} else {
					$dest_height = 340;
					$dest_width = $src_width * ($dest_height/$src_height);
				}

				if($dest_height > 340) {
					$dest_height = 340;
					$dest_width = (int)($src_width * (340/$src_height));
				}

				if($dest_width > 379) {
					$dest_width = 379;
					$dest_height = $dest_height * (379/$dest_width);
				}

				$dest= imagecreatetruecolor($dest_width, $dest_height);

				//if($overwrite == true) {
					if(file_exists($dest_filename)) {
						unlink($dest_filename);
					}
				//}

				imagecopyresampled($dest, $src, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);

				// ADD WATERMARK
				$overlay = imagecreatefrompng($_SERVER["DOCUMENT_ROOT"] . "/wp-content/uploads/watermark_monrovia.png");
				$overlay_width = imagesx($overlay);
				$overlay_height = imagesy($overlay);
				$offset_x = ($dest_width/2) - ($overlay_width/2);
				$offset_y = $dest_height - $overlay_height - ($dest_height * 0.025);
				imagecopyresampled($dest,$overlay,$offset_x,$offset_y,0,0,$overlay_width,$overlay_height,$overlay_width,$overlay_height);

				imagejpeg($dest,$dest_filename,100);
				return $dest_filename;

			}catch(Exception $err){
				return false;
			}
		}

		function resize_search_result(){
			try {

				$dest_filename = $this->info['server_path_search_result'];
				$dest= imagecreatetruecolor(154, 184);
				$dest_width = imagesx($dest);
				$dest_height = imagesy($dest);

				if(file_exists($this->info['server_path_original'])){
					$src = imagecreatefromjpeg($this->info['server_path_original']);
				}else{
					$src = imagecreatefromjpeg($this->info['server_path_detail']);
				}

				$src_width = imagesx($src);
				$src_height = imagesy($src);

				$dst_x = 0;
				$dst_y = 0;
				$src_x = 0;
				$src_y = 0;
				$dst_w = 0;
				$dst_h = 0;
				$src_w = 0;
				$src_h = 0;

				$white = imagecolorallocate($dest, 255,255,255);
				imagefill($dest, 0, 0, $white);

				if(($src_width*($dest_height/$src_height)) < $dest_width) {
					//center the resized image
					$src_w = $src_width;
					$src_h = $src_height;
					$src_x = 0; //full image = left most side
					$src_y = 0; //full image  = top most side
					$dst_w = ($src_width*($dest_height/$src_height)); //
					$dst_h = $dest_height;
					$dst_x = ($dest_width/2) - ($dst_w/2);  //left side placement
					$dst_y = 0; //top placement
					//echo("RESIZING: SMALLER DEST( X: $dst_x, Y: $dst_y, W: $dst_w, H: $dst_h) SRC ( X: $src_x, Y: $src_y,  W: $src_w, H: $src_h)<BR>");
					imagecopyresampled($dest, $src, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
					//$black_idx = imagecolorat($dest, 0,0);
					//imagecolorset($dest, $black_idx, 255,255,255);

				} else {
					//crop the resized image from the middle
					$src_w = ($src_height/$dest_height) * $dest_width;
					$src_h = $src_height;
					$src_x = ($src_width/2) - ($src_w/2);
					$src_y = 0; //top
					$dst_w = $dest_width;
					$dst_h = $dest_height;
					$dst_x = 0; //left side placement
					$dst_y = 0; //top side placement
					//echo("RESIZING: LARGER DEST( X: $dst_x, Y: $dst_y, W: $dst_w, H: $dst_h) SRC ( X: $src_x, Y: $src_y,  W: $src_w, H: $src_h)<BR>");
					imagecopyresampled($dest, $src, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
				}

				//if($overwrite == true) {
					if(file_exists($dest_filename)) {
						unlink($dest_filename);
					}
				//}

				imagejpeg($dest,$dest_filename,100);
				return $dest_filename;
			}catch(Exception $err){
				return false;
			}
		}

		function resize_detail_thumb(){
			try {
				$dest_filename = $this->info['server_path_detail_thumbnail'];

				if(file_exists($this->info['server_path_original'])){
					$src = imagecreatefromjpeg($this->info['server_path_original']);
				}else{
					$src = imagecreatefromjpeg($this->info['server_path_detail']);
				}

				$src_width = imagesx($src);
				$src_height = imagesy($src);

				if($src_width > $src_height) {
					$dest_width = 64;
					$dest_height = $src_height * ($dest_width/$src_width);
				} else {
					$dest_height = 64;
					$dest_width = $src_width * ($dest_height/$src_height);
				}

				if($dest_height > 64) {
					$dest_height = 64;
					$dest_width = (int)($src_width * (64/$src_height));
				}

				if($dest_width > 64) {
					$dest_width = 64;
					$dest_height = (int)($src_height * (64/$src_width));
				}

				$dest= imagecreatetruecolor($dest_width, $dest_height);

				$dst_x = 0;
				$dst_y = 0;
				$src_x = 0;
				$src_y = 0;
				$dst_w = 0;
				$dst_h = 0;
				$src_w = 0;
				$src_h = 0;

				imagecopyresampled($dest, $src, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);

				//if($overwrite == true) {
					if(file_exists($dest_filename)) {
						unlink($dest_filename);
					}
				//}

				imagejpeg($dest,$dest_filename,100);
				return $dest_filename;
			}catch(Exception $err){
				return false;
			}
		}

		function generate_custom_thumbnail($width,$height,$horizontal_alignment = null,$vertical_alignment = null){
			if(is_null($horizontal_alignment)) $horizontal_alignment = 'center';
			if(is_null($vertical_alignment)) $vertical_alignment = 'center';

			// THIS IS USED BY THE CATALOG CREATOR. IT STRETCHES THE ORIGINAL IMAGE TO FIT WITHIN THE BOUNDS PROVIDED, THEN CROPPING.
			// RETURN VALUE: RAW JPEG DATA
			try {
				$dest_filename = $GLOBALS['server_info']['physical_root'].'temp/plant_image_set_' . $this->info['id'] . '_' . time() . '_' . rand() . '.jpg';

				if(file_exists($this->info['server_path_original'])){
					$src = imagecreatefromjpeg($this->info['server_path_original']);
				}else{
					if(!file_exists($this->info['server_path_detail'])) throw new Exception('Section: generate_custom_thumbnail; error: file not found');
					$src = imagecreatefromjpeg($this->info['server_path_detail']);
				}

				$src_width = imagesx($src);
				$src_height = imagesy($src);

				$aspect_ratio_src = $src_width / $src_height;
				$aspect_ratio_dest = $width / $height;

				// CALCULATE DIFFERENCES IN DIMENSIONS OF SOURCE AND DESTINATION IMAGES TO DETERMINE WHICH DIMENSION TO USE AS BASE
				$width_diff = $src_width - $width;
				$height_diff = $src_height - $height;

				if($width_diff>$height_diff){
					// IMAGES ARE CLOSER IN HEIGHT THAN IN WIDTH; USE HEIGHT
					$dest_width = $width * $aspect_ratio_src;
					$dest_height = $height;
				}else if($width_diff<$height_diff){
					// IMAGES ARE CLOSER IN WIDTH THAN IN WIDTH; USE WIDTH
					$dest_width = $width;
					$dest_height = $height / $aspect_ratio_src;
				}else{
					$dest_width = $width;
					$dest_height = $height;
				}
				// AT THIS POINT, DESTINATION DIMENSIONS ARE ALWAYS EQUAL TO OR GREATER THAN REQUESTED DIMENSIONS ($width, $height)

				// ALIGNMENT CALCULATIONS
				$dest_x = 0; $dest_y = 0;
				if($horizontal_alignment=='right'){
					$dest_x = $width - $dest_width;
				}else if($horizontal_alignment=='center'){
					$dest_x = -(($dest_width-$width)/2);
				}
				if($vertical_alignment=='bottom'){
					$dest_y = $height - $dest_height;
				}else if($vertical_alignment=='center'){
					$dest_y = -(($dest_height-$height)/2);
				}

				$dest = imagecreatetruecolor($width, $height);

				imagecopyresampled($dest,$src,$dest_x,$dest_y,0,0,$dest_width,$dest_height,$src_width,$src_height);

				if(file_exists($dest_filename)) unlink($dest_filename);
				imagejpeg($dest,$dest_filename,100);

				$fp = fopen($dest_filename, 'rb');
				header('Content-Type: image/jpeg');
				header("Content-Length: " . filesize($dest_filename));

				fpassthru($fp);
				fclose($fp);
				imagedestroy($dest);
				unlink($dest_filename);
				return true;
				//return $dest_filename;
			}catch(Exception $err){
				return false;
			}
		}

		//////////////////////////////////////////REMOVE, THIS IS FOR TROUBLESHOOTING
		function generate_custom_thumbnail_test($width,$height,$horizontal_alignment = null,$vertical_alignment = null){		
			if(is_null($horizontal_alignment)) $horizontal_alignment = 'center';
			if(is_null($vertical_alignment)) $vertical_alignment = 'center';
			echo "1";

			// THIS IS USED BY THE CATALOG CREATOR. IT STRETCHES THE ORIGINAL IMAGE TO FIT WITHIN THE BOUNDS PROVIDED, THEN CROPPING.
			// RETURN VALUE: RAW JPEG DATA
			try {
				$dest_filename = $GLOBALS['server_info']['physical_root'].'temp/plant_image_set_' . $this->info['id'] . '_' . time() . '_' . rand() . '.jpg';

				if(file_exists($this->info['server_path_original'])){
					echo "exists";
					$src = imagecreatefromjpeg($this->info['server_path_original']);
				}else{
					echo "nofile";
					if(!file_exists($this->info['server_path_detail'])) throw new Exception('Section: generate_custom_thumbnail; error: file not found');
					$src = imagecreatefromjpeg($this->info['server_path_detail']);
				}

				$src_width = imagesx($src);
				$src_height = imagesy($src);

				$aspect_ratio_src = $src_width / $src_height;
				$aspect_ratio_dest = $width / $height;

				// CALCULATE DIFFERENCES IN DIMENSIONS OF SOURCE AND DESTINATION IMAGES TO DETERMINE WHICH DIMENSION TO USE AS BASE
				$width_diff = $src_width - $width;
				$height_diff = $src_height - $height;

				if($width_diff>$height_diff){
					// IMAGES ARE CLOSER IN HEIGHT THAN IN WIDTH; USE HEIGHT
					$dest_width = $width * $aspect_ratio_src;
					$dest_height = $height;
				}else if($width_diff<$height_diff){
					// IMAGES ARE CLOSER IN WIDTH THAN IN WIDTH; USE WIDTH
					$dest_width = $width;
					$dest_height = $height / $aspect_ratio_src;
				}else{
					$dest_width = $width;
					$dest_height = $height;
				}
				// AT THIS POINT, DESTINATION DIMENSIONS ARE ALWAYS EQUAL TO OR GREATER THAN REQUESTED DIMENSIONS ($width, $height)

				// ALIGNMENT CALCULATIONS
				$dest_x = 0; $dest_y = 0;
				if($horizontal_alignment=='right'){
					$dest_x = $width - $dest_width;
				}else if($horizontal_alignment=='center'){
					$dest_x = -(($dest_width-$width)/2);
				}
				if($vertical_alignment=='bottom'){
					$dest_y = $height - $dest_height;
				}else if($vertical_alignment=='center'){
					$dest_y = -(($dest_height-$height)/2);
				}

				$dest = imagecreatetruecolor($width, $height);
				echo "dest:".$dest;				
				echo "\nimg:".$dest_filename;
				imagecopyresampled($dest,$src,$dest_x,$dest_y,0,0,$dest_width,$dest_height,$src_width,$src_height);
				echo "imagecopy";
				if(file_exists($dest_filename)) unlink($dest_filename);
				imagejpeg($dest,$dest_filename,100);

				$fp = fopen($dest_filename, 'rb');	
				//$pageErros = error_get_last();	
				//var_dump($pageErros);

				//if ($pageErros){
					//foreach ($pageErros as $items) {
					//	echo ":".$items;
					//}
				//}

				if($fp){
					echo "yesy";
				}else{
					echo "no";
				}
				//header('Content-Type: image/jpeg');
				header("Content-Length: " . filesize($dest_filename));

				fpassthru($fp);
				fclose($fp);
				imagedestroy($dest);
				unlink($dest_filename);				
				return true;
				//return $dest_filename;
			}catch(Exception $err){
				return false;
			}
		}

	}
?>