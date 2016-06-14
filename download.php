<?
require( '../../../wp-load.php' );
include_once( '../../../wp-config.php');
require_once(get_template_directory().'/includes/classes/class_sql.php');
require_once(get_template_directory().'/includes/classes/class_plant.php');
require_once(get_template_directory().'/includes/utility_functions.php');
ini_set('display_errors','on');
	error_reporting(E_ALL);

	ini_set('zlib.output_compression','Off');
	$plant_id = $_POST['id'];
	//$notes = strip_tags($_POST['notes']);

	//require($_SERVER['DOCUMENT_ROOT'].'/inc/init.php');
	
	$plant = new plant($plant_id);
	$plant->get_images();

	if($plant->info['id']!=''){
		$zip_file_name = $plant->info['item_number'].'-'.generate_plant_seo_name($plant->info['common_name']).'.zip';
		$zip_file_path = './zip_temp/'.$zip_file_name;

/*		if(@filesize($zip_file_path)&&(time()-filemtime($zip_file_path)<60*60*24)){
		
			// LOG DOWNLOAD
			//sql_query("INSERT INTO plant_image_downloads(monrovia_user_id,plant_image_set_id,notes,download_datetime) VALUES('".$monrovia_user->info['id']."','".$image_set_info['id']."','!".sql_sanitize($notes)."',NOW())");												

			// FILE ALREADY EXISTS AND WAS LAST MODIFIED WITHIN 24 HOURS
			header('location:'.$zip_file_path);
		}else{
*/
			$zip = new ZipArchive();
			if ($zip->open($zip_file_path,ZIPARCHIVE::OVERWRITE)===TRUE){
				//$zip->addFromString('readme.txt',"Thank you for visiting Monrovia.com!\n\nYou can find more information on this plant at ".$plant->info['details_url'].".\n\nFor your reference, here are your usage notes for these images:\n\n".stripslashes($notes));
				for($i=0;$i<count($plant->image_sets);$i++){
					$image_set_info = $plant->image_sets[$i]->info;
					if($image_set_info['is_active']=='1'&&$image_set_info['is_distributable']=='1'){
						$zip->addFile($image_set_info['server_path_original'],strtolower($plant->info['item_number'].'-'.generate_plant_seo_name($plant->info['common_name']).'-'.generate_plant_seo_name($image_set_info['title']).'.jpg'));
						
						// LOG DOWNLOAD
					//	sql_query("INSERT INTO plant_image_downloads(monrovia_user_id,plant_image_set_id,notes,download_datetime) VALUES('".$monrovia_user->info['id']."','".$image_set_info['id']."','".sql_sanitize($notes)."',NOW())");												
					};
				}
				$zip->close();

				header('location:'.$zip_file_path);

				// OUTPUT FORCE-DOWNLOAD HEADER
				/*
				header("Pragma:public");
				header("Expires:0");
				header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
				header("Cache-Control:private",false);
				header("Content-Type:application/zip");
				header("Content-Disposition:attachment;filename=\"".$zip_file_name);
				header("Content-Transfer-Encoding:binary");
				header("Content-Length:".filesize($zip_file_path));

				// OUTPUT FILE CONTENTS
				$file = @fopen($zip_file_path,'rb');
				if($file){
					fpassthru($file);
					// TRACK DOWNLOAD OF EACH INDIVIDUAL IMAGE
					for($i=0;$i<count($plant->image_sets);$i++){
						$image_set_info = $plant->image_sets[$i]->info;
						sql_query("INSERT INTO plant_image_downloads(monrovia_user_id,plant_image_set_id,notes,download_datetime) VALUES('".$monrovia_user->info['id']."','".$image_set_info['id']."','".sql_sanitize($notes)."',NOW())");
					}
					//unset($zip_file_path);
				}
				*/
			}else{
				echo('Sorry! An unexpected error occurred.');
			}
		//}
	}else{
		echo('Sorry! An error occurred. Please make sure you are <a href="/community/login.php">logged in</a>.<script>//window.close();</script>');
	}
?>