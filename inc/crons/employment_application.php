<?php
require( '../../wp-load.php' );
include_once( '../../wp-config.php');

    // Email Headers
	$emailHeaders = "Reply-To: noreply@monrovia.com\r\n";
	$emailHeaders .= "Return-Path: noreply@monrovia.com\r\n";
	$emailHeaders .= "From: Monrovia <noreply@monrovia.com>\r\n";
	$emailHeaders .= 'Signed-by: monrovia.com\r\n"';
	$emailHeaders .= 'MIME-Version: 1.0' . "\r\n";
	$emailHeaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	switch($_GET['action']){
		case 'send_email':
			$html = file_get_contents('../../email_templates/employment_app_confirmation.htm');
			$html = str_replace('{EMAIL}',$_GET['email'],$html);
			wp_mail( $_GET['email'], 'Employment Application Received--Thank You!', $html, $emailHeaders );
		break;
		case 'retrieve':
			require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_record.php');
			output_job_listings();
		break;
	}

	function output_job_listings(){
		global $office_locations;
		// ORDER BY LOCATION, THEN BY ORDINAL
		$addendum = '';
		foreach($office_locations as $office_location){
			$addendum .= 'location_id="' . $office_location->id . '" DESC,';
		}
		$result = mysql_query("SELECT location_id,title FROM job_listings WHERE is_active=1 ORDER BY $addendum ordinal ASC");
		$num_rows = intval(mysql_num_rows($result));
		$ret = '';
		if($num_rows){
			for($i=0;$i<$num_rows;$i++){
				$ret .= '|' . mysql_result($result,$i,"location_id") . ',' . mysql_result($result,$i,"title");
			}
			if($ret!='') echo(substr($ret,1));
		}
	}

?>