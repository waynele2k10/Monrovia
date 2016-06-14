<?php
require( '../wp-load.php' );
include_once( '../wp-config.php');
ini_set('display_errors','on');
	error_reporting(E_ALL);

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
                <td>{plant_active}</td>
                <td>{is_new}</td>
				<td>{botanical_name}</td>
				<td>{common_name}</td>
                <td>{photo_id}</td>
                <td>{photo_active}</td>
                <td>{is_primary}</td>
                <td>{title}</td>
                <td>{photography_credit}</td>
                <td>{expiration_date}</td>
                <td>{source}</td>
                <td>{url}</td>
			</tr>
HTML;

		$export_id = date('Ymd');
		$file_handler = fopen("export/plant_images_$export_id.xls", 'w+');
		if(!$file_handler) die('fail');
		//if(!$file_handler) die('<br /><br /><b>Error: Could not write to export file. Please make sure no one else is currently running an export.</b>');
		fwrite($file_handler,file_get_contents('inc/templates/plant_images_export.htm'));

		$result = mysql_query("Select
p.item_number,p.is_active plant_active,is_new,botanical_name,common_name,
i.id photo_id,i.is_active photo_active,i.is_primary,i.title,i.photography_credit,i.expiration_date,i.source,i.id
from plants p
join plant_image_sets i on
plant_id=p.id");
		$num_rows = mysql_num_rows($result);

		for($i=0;$i<$num_rows;$i++){

			set_time_limit(60); // ALLOW UP TO 5 SECONDS PER RECORD

			$html = $template_rows;
			
			$html = str_replace('{item_number}','#'.mysql_result($result,$i,"item_number"),$html);
			$html = str_replace('{plant_active}',mysql_result($result,$i,"plant_active"),$html);
			$html = str_replace('{is_new}',mysql_result($result,$i,"is_new"),$html);
			$html = str_replace('{botanical_name}',mysql_result($result,$i,"botanical_name"),$html);
			$html = str_replace('{common_name}',mysql_result($result,$i,"common_name"),$html);
			$html = str_replace('{photo_id}',mysql_result($result,$i,"photo_id"),$html);
			$html = str_replace('{photo_active}',mysql_result($result,$i,"photo_active"),$html);
			$html = str_replace('{is_primary}',mysql_result($result,$i,"is_primary"),$html);
			$html = str_replace('{title}',mysql_result($result,$i,"title"),$html);
			$html = str_replace('{photography_credit}',mysql_result($result,$i,"photography_credit"),$html);
			$html = str_replace('{expiration_date}',mysql_result($result,$i,"expiration_date"),$html);
			$html = str_replace('{source}',mysql_result($result,$i,"source"),$html);
			$html = str_replace('{url}','http://www.monrovia.com/wp-content/uploads/plants/details/'.mysql_result($result,$i,"id").'.jpg',$html);
			

			
			fwrite($file_handler,$html);
		}

		fwrite($file_handler,'</table></body></html>');
		fclose($file_handler);

		$url = 'http://'.$_SERVER['HTTP_HOST'].'/monrovia_admin/plant_images_export.php?export_id='.$export_id;
		
		$emailHeaders = "Reply-To: noreply@monrovia.com\r\n";
		$emailHeaders .= "Return-Path: noreply@monrovia.com\r\n";
		$emailHeaders .= "From: Monrovia <noreply@monrovia.com>\r\n";
		$emailHeaders .= 'Signed-by: monrovia.com\r\n"';
		$emailHeaders .= 'MIME-Version: 1.0' . "\r\n";
		$emailHeaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		//$email = 'bexnowski@primitivespark.com';

		wp_mail( $email, 'Monrovia Plant Images Report','<a href="'.$url.'">'.$url.'</a>', $emailHeaders );
		echo('success');
		//echo('Numrums'.$num_rows);

	}

	function download_export($export_id){
		$file_name = 'plant_images_' . $export_id . '.xls';
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