<?php
	@include_once($_SERVER['DOCUMENT_ROOT'].'/inc/init.php');				// DEV
	@include_once('/var/www/monrovia.com/root/inc/init.php');				// LIVE
	@include_once('/var/www/vhosts/tpgphpdev1.net/httpdocs/inc/init.php');	// STAGING

	$admin_email = 'jleung@thephelpsgroup.com';
	if($GLOBALS['server_info']['environment']=='prod') $admin_email = 'krudnyk@monrovia.com';

	sql_disconnect();
	sql_set_user('med');
	sql_connect();

	$monrovia_profiles_result = sql_query("SELECT * FROM monrovia_profiles WHERE approval_status='0' AND is_submitted_for_approval=1");

	$monrovia_profiles_pending_approval = mysql_num_rows($monrovia_profiles_result);
	if ( $monrovia_profiles_pending_approval > 0 )
	{
		$message = "There are ".$monrovia_profiles_pending_approval." designer profiles pending approval.";

		$email_message_html = file_get_contents($GLOBALS['server_info']['physical_root'].'community/email_templates/designers_notice.htm');
		$email_message_html = str_replace('{message}',$message,$email_message_html);
		$email_message_html = str_replace('{profile_email}',$admin_email,$email_message_html);

		$email_message_txt = $message;

		send_email($admin_email,$email_message_txt,$email_message_html,$email_message_txt,true);
	}
?>