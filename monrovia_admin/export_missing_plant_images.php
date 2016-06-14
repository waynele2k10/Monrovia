<?php
require( '../wp-load.php' );
include_once( '../wp-config.php');

	//require($_SERVER['DOCUMENT_ROOT'].'/inc/init.php');
		global $current_user;
        get_currentuserinfo();
		$email = $current_user->user_email;

	// ENSURE USER HAS PERMISSIONS
	// Only Allow Admins For the Main Sections
	if(check_user_role("administrator", $current_user->ID )){ 


	function do_export($email = 'brettex@hotmail.com'){


		$template_rows = <<<HTML
			<tr>
				<td>{item_number}</td>
				<td>{botanical_name}</td>
				<td>{common_name}</td>
				<td>{year_introduced}</td>
			</tr>
HTML;

		$export_id = date('Ymd');
		$file_handler = fopen("export/plant_missing_images_$export_id.xls", 'w+');
		if(!$file_handler) die('fail');
		//if(!$file_handler) die('<br /><br /><b>Error: Could not write to export file. Please make sure no one else is currently running an export.</b>');
		fwrite($file_handler,file_get_contents('inc/templates/missing_plant_images_export.htm'));

		//$result = mysql_query('SELECT a.item_number, botanical_name, REPLACE(REPLACE(common_name,"{{#174}}","&reg;"),"{{#153}}","&trade;") AS common_name, year_introduced FROM plants a LEFT JOIN plant_image_sets b ON b.plant_id = a.id AND b.is_active = 1 WHERE a.is_active = 1 AND b.title IS NULL;');
		$result = mysql_query('SELECT id, item_number, botanical_name, REPLACE(REPLACE(common_name,"{{#174}}","&reg;"),"{{#153}}","&trade;") AS common_name, year_introduced  FROM plants WHERE is_active = 1');
		$image_sets_result = mysql_query('SELECT plant_id, title FROM plant_image_sets WHERE title IS NOT NULL GROUP BY plant_id ORDER BY plant_id ASC');
		//Loop through current plants with photos to create an exclude list
		while($exclude = mysql_fetch_array($image_sets_result)){
			$exclude_array[] = $exclude['plant_id'];	
		}
		//$num_rows = mysql_num_rows($result);
		//print_r($exclude_array);
		while($plants = mysql_fetch_array($result)){
			//Only include the plant if its missing an image
			if(!in_array($plants['id'],$exclude_array, true)){
				//set_time_limit(5); // ALLOW UP TO 5 SECONDS PER RECORD
	
				$html = $template_rows;
				
				$year = $plants['year_introduced'];
				if($year=='0') $year = '';
				
				$html = str_replace('{item_number}','#'.$plants['item_number'],$html);
				$html = str_replace('{botanical_name}',$plants['botanical_name'],$html);
				$html = str_replace('{common_name}',$plants['common_name'],$html);
				$html = str_replace('{year_introduced}',$year,$html);
				
				fwrite($file_handler,$html);
			}
		}

		fwrite($file_handler,'</table></body></html>');
		fclose($file_handler);

		$url = 'http://'.$_SERVER['HTTP_HOST'].'/monrovia_admin/export_missing_plant_images.php?export_id='.$export_id;
		
		$emailHeaders = "Reply-To: noreply@monrovia.com\r\n";
		$emailHeaders .= "Return-Path: noreply@monrovia.com\r\n";
		$emailHeaders .= "From: Monrovia <noreply@monrovia.com>\r\n";
		$emailHeaders .= 'Signed-by: monrovia.com\r\n"';
		$emailHeaders .= 'MIME-Version: 1.0' . "\r\n";
		$emailHeaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		//$email = 'bexnowski@primitivespark.com';

		wp_mail( $email, 'Monrovia missing plant images report','<a href="'.$url.'">'.$url.'</a>', $emailHeaders );
		echo('success');

	}

	function download_export($export_id){
		$file_name = 'plant_missing_images_' . $export_id . '.xls';
		$file_path = 'export/' . $file_name;

		// OUTPUT FORCE-DOWNLOAD HEADER
		header("Pragma:public");
		header("Expires:0");
		header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
		header("Cache-Control:private",false);
		header("Content-Type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=\"".$file_name);
		header("Content-Transfer-Encoding:binary");
		header("Content-Length:".filesize($file_path));
		header('location:'.$file_path);
	}

	$export_id = '';
	if(isset($_GET['export_id'])) $export_id = $_GET['export_id'];
	if($export_id==''){
		do_export($email);
	}else{
		download_export($export_id);
	}
    
    } else {
		header("Location:/");
	}
?>