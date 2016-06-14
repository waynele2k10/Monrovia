<?php
	ini_set('memory_limit', '300M'); // INCREASE MEMORY LIMIT TO ALLOW FOR IMAGE PROCESSING OF LARGE IMAGES
	ini_set('gd.jpeg_ignore_warning',1);

	require_once('class_record.php');

	class monrovia_event_image extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM monrovia_event_images TABLE, EXCEPT FOR id
		var $table_fields = 'monrovia_event_id,is_active,title,ordinal';
        var $table_name = 'monrovia_event_images';

		function event_image_set($record_id){
			if($record_id!='') $this->load($record_id);
			if($this->info['id']!=''){
				$this->info['path_full'] = $GLOBALS['server_info']['www_root'] . 'img/events/'.$this->info['monrovia_event_id'].'/'.$this->info['id'].'_full.jpg';
				$this->info['path_thumbnail'] = $GLOBALS['server_info']['www_root'] . 'img/events/'.$this->info['monrovia_event_id'].'/'.$this->info['id'].'_thumb.jpg';
				$this->info['server_path_full'] = $GLOBALS['server_info']['physical_root'] . 'img/events/'.$this->info['monrovia_event_id'].'/'.$this->info['id'].'_full.jpg';
				$this->info['server_path_thumbnail'] = $GLOBALS['server_info']['physical_root'] . 'img/events/'.$this->info['monrovia_event_id'].'/'.$this->info['id'].'_thumb.jpg';
                
			}
		}
		
        function determine_order(){
            if($this->info['monrovia_event_id']!=''){
                if ($result = sql_query("SELECT * FROM monrovia_event_images WHERE monrovia_event_id='".$this->info['monrovia_event_id']."'")){
                    return mysql_num_rows($result);
                    
                }
            }
        }
		
		function load($id){
			parent::load($id);
			$this->determine_image_paths();
			
		}
		function save(){
		
			// SANITIZE
			$this->info['title'] = strip_tags($this->info['title']);
		
			parent::save();

			// CLEAR EVENT CACHE
			if(intval($this->info['monrovia_event_id'])>0){
				require_once('class_monrovia_event.php');
				$event = new monrovia_event($this->info['monrovia_event_id']);
				$event->clear_cache();
			}

		}

		function delete(){
			unlink($this->info['server_path_full']);
			unlink($this->info['server_path_thumbnail']);
			
			sql_query("DELETE FROM monrovia_event_images WHERE id = ".$this->info['id']);
			
		}

        
		function determine_image_paths(){
		    if($this->info['id']!=''){
                $this->info['path_full'] = $GLOBALS['server_info']['www_root'] . 'img/events/'.$this->info['monrovia_event_id'].'/'.$this->info['id'].'_full.jpg';
                $this->info['path_thumbnail'] = $GLOBALS['server_info']['www_root'] . 'img/events/'.$this->info['monrovia_event_id'].'/'.$this->info['id'].'_thumb.jpg';
                $this->info['server_path_full'] = $GLOBALS['server_info']['physical_root'] . 'img/events/'.$this->info['monrovia_event_id'].'/'.$this->info['id'].'_full.jpg';
                $this->info['server_path_thumbnail'] = $GLOBALS['server_info']['physical_root'] . 'img/events/'.$this->info['monrovia_event_id'].'/'.$this->info['id'].'_thumb.jpg';
                $this->info['server_path_original'] = $GLOBALS['server_info']['www_root'] . 'img/events/'.$this->info['monrovia_event_id'].'/'.$this->info['id'].'_original.jpg';
                
            }
		}

		function generate_thumbnails(){
			$success = true;
			try {
			    $this->determine_image_paths();
				if($success) $success = ($this->resize_enlarged()!='');
				if($success) $success = ($this->resize_thumb()!='');
			}catch(Exception $err){
				$success = false;
			}
			return $success;
		}

		function resize_enlarged(){

			try{
				$dest_filename = $this->info['server_path_full'];
				$src = @imagecreatefromjpeg($this->info['server_path_original']);
				if(!$src) return false;
				$src_width = imagesx($src);
				$src_height = imagesy($src);
                
                // resize image only if it's larger than 800x700
                if($src_width>800||$src_height>700){

    				if($src_width > $src_height) {
    					$dest_width = 800;
    					$dest_height = $src_height * ($dest_width/$src_width);
    				} else {
    					$dest_height = 700;
    					$dest_width = $src_width * ($dest_height/$src_height);
    				}
    
    				if($dest_height > 700) {
    					$dest_height = 700;
    					$dest_width = (int)($src_width * (700/$src_height));
    				}
    
    				if($dest_width > 800) {
    					$dest_width = 800;
    					$dest_height = $dest_height * (800/$dest_width);
    				}
                // otherwise keep the dimensions the way they are
                } else {
                    $dest_width = $src_width;
                    $dest_height = $src_height;
                }

				$dest= imagecreatetruecolor($dest_width, $dest_height);

				
			    if(file_exists($dest_filename)) {
					unlink($dest_filename);
				}
				

				imagecopyresampled($dest, $src, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);

				imagejpeg($dest,$dest_filename,100);
                     
                return $dest_filename;
                
			}catch(Exception $err){
				return false;
			}
		}
        

		function resize_thumb(){
			try {
				$dest_filename = $this->info['server_path_thumbnail'];
				$src = imagecreatefromjpeg($this->info['server_path_original']);
				$src_width = imagesx($src);
				$src_height = imagesy($src);

				if($src_width > $src_height) {
					$dest_width = 74;
					$dest_height = $src_height * ($dest_width/$src_width);
				} else {
					$dest_height = 108;
					$dest_width = $src_width * ($dest_height/$src_height);
				}

				if($dest_height > 108) {
					$dest_height = 108;
					$dest_width = (int)($src_width * (108/$src_height));
				}

				if($dest_width > 74) {
					$dest_width = 74;
					$dest_height = (int)($src_height * (74/$src_width));
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
	}
?>