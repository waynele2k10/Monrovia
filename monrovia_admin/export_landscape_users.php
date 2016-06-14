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
	function export_landscape_users_csv($email = "brettex@hotmail.com"){


		//echo('<div style="font-family:tahoma;font-size:11pt;">Generating plant database export. An email will be sent to ' . $monrovia_user->info['email_address'] . ' once it\'s ready. To cancel, press Esc and close this tab.');
		//flush();
		
		$export_id = date('Ymd');
		$file_handler = fopen("export/landscape_users_$export_id.xls", 'w+');
		if(!$file_handler) die('fail');
		//if(!$file_handler) die('<br /><br /><b>Error: Could not write to export file. Please make sure no one else is currently running an export.</b>');
		fwrite($file_handler,file_get_contents('inc/templates/designer_export.htm'));

		$query = mysql_query("SELECT * FROM monrovia_profiles WHERE approval_status = 1 GROUP BY(user_id) ORDER BY first_name ASC, last_name ASC");
		$num_rows = mysql_num_rows($query);

		$xls = '';
		while($results = mysql_fetch_array($query)){
					$xls .= "<tr>
								<td>".$results['first_name']."</td>
								<td>".$results['last_name']."</td>
								<td>".$results['email']."</td>
								<td>".$results['firm_name']."</td>
							</tr>";

		}
		fwrite($file_handler,$xls);
		

		fwrite($file_handler,'</table></body></html>');
		fclose($file_handler);

		$url = 'http://'.$_SERVER['HTTP_HOST'].'/monrovia_admin/export_landscape_users.php?export_id='.$export_id;
		
		$emailHeaders = "Reply-To: noreply@monrovia.com\r\n";
		$emailHeaders .= "Return-Path: noreply@monrovia.com\r\n";
		$emailHeaders .= "From: Monrovia <noreply@monrovia.com>\r\n";
		$emailHeaders .= 'Signed-by: monrovia.com\r\n"';
		$emailHeaders .= 'MIME-Version: 1.0' . "\r\n";
		$emailHeaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		wp_mail( $email, 'Monrovia landscape user database export','<a href="'.$url.'">'.$url.'</a>', $emailHeaders );
		
		echo('success');

	}

	function download_export($export_id){
		$file_name = 'landscape_users_' . $export_id . '.xls';
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
		export_landscape_users_csv($email);
	}else{
		download_export($export_id);
	}
	
	} else {
		header("Location:/");
	}
?>