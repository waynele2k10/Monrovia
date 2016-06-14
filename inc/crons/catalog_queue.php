<?php
require( '../../wp-load.php' );
include_once( '../../wp-config.php');

	ini_set('error_reporting', E_ERROR|E_PARSE);
	error_reporting(E_ERROR|E_PARSE);

	//include_once($_SERVER['DOCUMENT_ROOT'].'/inc/init.php');
	include_once($_SERVER['DOCUMENT_ROOT'] . '/inc/class_catalog.php');
	include_once($_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/monrovia/includes/classes/class_plant.php');
	
	// Email Headers
	$emailHeaders = "Reply-To: noreply@monrovia.com\r\n";
	$emailHeaders .= "Return-Path: noreply@monrovia.com\r\n";
	$emailHeaders .= "From: Monrovia <noreply@monrovia.com>\r\n";
	$emailHeaders .= 'Signed-by: monrovia.com\r\n"';
	$emailHeaders .= 'X-Mailer: monrovia.com\r\n"';
	$emailHeaders .= 'MIME-Version: 1.0' . "\r\n";
	$emailHeaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	

	function clear_stalled_exports(){
		global $emailHeaders;
		$result = mysql_query("SELECT catalog_queue.id, catalog_queue.catalog_id, catalog_queue.sorting_method, catalog_queue.include_collection, wp_users.user_email FROM catalog_queue INNER JOIN catalogs ON catalog_queue.catalog_id = catalogs.id INNER JOIN wp_users ON wp_users.ID = catalogs.user_id WHERE date_process_start <> '0000-00-00' AND date_process_start < DATE_ADD(NOW(),INTERVAL -10 MINUTE)");
				
		// THERE SHOULDN'T EVER BE MORE THAN ONE CATALOG PROCESSING AT ANY GIVEN TIME, SO WE'LL ASSUME 1 ROW OR NONE
		if(mysql_num_rows($result)>0){
			// STALLED EXPORT FOUND (STALLED EXPORT = EXPORT GOING ON FOR MORE THAN 10 MINS)

			$id = mysql_result($result,0,'id');
			$catalog_id = mysql_result($result,0,'catalog_id');
			$sorting_method = mysql_result($result,0,'sorting_method');
            $include_collection = mysql_result($result,0,'include_collection');
			$email_address = mysql_result($result,0,'wp_users.user_email');

			$catalog = new catalog($catalog_id);
			if(intval($catalog->info['id'])==$catalog_id){			
				wp_mail('brettex@hotmail.com','Stalled Monrovia Custom Catalog PDF',to_json($catalog->info), $emailHeaders);
				$txt = 'Your custom PDF catalog "'.html_sanitize($catalog->info['name']).'" could not be generated. Please try to generate the PDF again. We apologize for the inconvenience. An alert has been sent to our technical team.';
				wp_mail($email_address,'Monrovia Custom Catalog PDF',$txt, $emailHeaders);
			}
			$result = mysql_query("DELETE FROM catalog_queue WHERE id='".$id."'");
		}
	}
	
	function process_next_item(){
		
		global $emailHeaders;
	
		if(!ini_get('safe_mode')) set_time_limit(120);	// ALLOW UP TO TWO MINUTES
	
		// BAIL IF ANOTHER CATALOG IS PROCESSING
		$result = mysql_query("SELECT id FROM catalog_queue WHERE date_process_start <> '0000-00-00'");
		if(mysql_num_rows($result)>0) return;
	
		$result = mysql_query("SELECT catalog_queue.id, catalog_queue.catalog_id, catalog_queue.sorting_method, catalog_queue.include_collection, wp_users.user_email FROM catalog_queue INNER JOIN catalogs ON catalog_queue.catalog_id = catalogs.id INNER JOIN wp_users ON wp_users.ID = catalogs.user_id WHERE catalog_queue.date_process_start IS NULL");
		
		if(mysql_num_rows($result)>0){
		
			$id = mysql_result($result,0,'catalog_queue.id');
			$catalog_id = mysql_result($result,0,'catalog_queue.catalog_id');
			$sorting_method = mysql_result($result,0,'catalog_queue.sorting_method');
            $include_collection = mysql_result($result,0,'include_collection')==1;
			$email_address = mysql_result($result,0,'wp_users.user_email');

			mysql_query("UPDATE catalog_queue SET date_process_start=NOW() WHERE id=$id");

			$catalog = new catalog($catalog_id);
			if(intval($catalog->info['id'])==$catalog_id){

				$result = $catalog->generate_pdf($sorting_method,$include_collection);
				
				if($result['success']===true){
					// SEND EMAIL NOTIFICATION
					$html = 'Your catalog PDF "'.html_sanitize($catalog->info['name']).'" can be downloaded here:<br /><a href="'.$result['path'].'">'.$result['path'].'</a><br /><br />The link to your PDF will expire in 30 days, although your catalog will remain on the website for one year. You may generate another PDF at anytime during that period. To save time, consider saving the PDFs to your desktop.';
					$txt = 'Your catalog PDF "'.html_sanitize($catalog->info['name']).'" can be downloaded here:\n'.$result['path'].'\n\nThe link to your PDF will expire in 30 days, although your catalog will remain on the website for one year. You may generate another PDF at anytime during that period. To save time, consider saving the PDFs to your desktop.';
					
					wp_mail( $email_address, 'Monrovia Custom Catalog PDF', $html, $emailHeaders );
					//mail( $email_address, 'Monrovia Custom Catalog PDF', $html, $emailHeaders, "-f noreply@monrovia.com" );
					
				}else{
					// FAILED; SEND EMAIL NOTIFICATION
					wp_mail('brettex@hotmail.com','Failed Monrovia Custom Catalog PDF','Catalog ID: ' . $catalog->info['id'] . '<br />Last modified: ' . $catalog->info['date_last_modified'] . '<br /><br />Error: ' . $result['error'], $emailHeaders);

					$txt = 'Your custom PDF catalog "'.html_sanitize($catalog->info['name']).'" could not be generated. We apologize for the inconvenience. An alert has been sent to our technical team.';

					wp_mail($email_address,'Monrovia Custom Catalog PDF',$txt, $emailHeaders);
					
					// LOG IF CRON
					if(is_cron()){
						echo(date('Y-m-d h:i:s')."\n");
						var_dump($result);
						echo("\n\n");
					}
					
				}
			}
			mysql_query("DELETE FROM catalog_queue WHERE id='".$id."'");
		}
	}
	clear_stalled_exports();
	process_next_item();
?>