<?php
require( '../wp-load.php' );
include_once( '../wp-config.php');

	
	header('Content-Type: text/html; charset=ISO-8859-1');
    require_once('../inc/init.php');
//	require_once('../inc/json.php');

	function output_page_notice($html){
	echo('<script>$(\'page_notice\').update(\''.js_sanitize($html).'\');$(\'page_notice\').style.display=\'block\';$(\'page_notice\').highlight();</script>');
	} 
	
	// Set up User data if logged in
	if(is_user_logged_in()){
	 // User is Admin and Logged in	
			 global $current_user;
      		 get_currentuserinfo();
			 if(check_user_role("administrator", $current_user->ID ) || $current_user->ID == '25329'){
				 // OK, you are an admin!
				// echo "Youre and Admin OR Katharine";
			 } else { 
			 	// You are not an admin, redirect you! 
				header("Location: ".site_url());
			 }
	} else {
		// Not logged in, redirect to site
		echo "Redirecting you";
		header("Location: ".site_url());
	}

	if(function_exists('before_output')) before_output();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<title>Monrovia - Administration</title>
		<meta http-equiv="Content-type" value="text/html; charset=windows-1252">
		<!--<script src="/inc/packer.php?path=/js/prototype.js,/js/prototype_extensions.js,/js/slim_tabs.js,/js/general.js,/js/modal_min.js" type="text/javascript"></script>-->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="../js/prototype.js" type="text/javascript"></script>
		<script src="../js/prototype_extensions.js" type="text/javascript"></script>
		<script src="../js/slim_tabs.js" type="text/javascript"></script>
		<script src="../js/general.js" type="text/javascript"></script>		
		<script src="../js/modal_min.js" type="text/javascript"></script>
        
		
		<link rel="stylesheet" type="text/css" href="/inc/packer.php?path=/css/slim_tabs.css,/monrovia_admin/css/general.css,/css/modal.css" />
		<script src="../js/scriptaculous/scriptaculous.js?load=effects,dragdrop,controls" type="text/javascript"></script>
		<script>
			var autocompleters = [];
			// TODO: encrypt and output sensitive info in backend only
			var monrovia_user_data = {
				'name':'<?=$current_user->user_name?>',
				'id':'<?=$current_user->id?>',
				'email_address':'<?=$current_user->user_email?>',
				'is_logged_in':true
			}
			Event.observe(window,'load',function(){
				// AUTOCOMPLETE FIELD INIT
				$$('div.auto_complete').each(function(div){
					var field = div.previous('input.text_field');
					if(field){
						div.id = 'autocomplete_' + field.id;
						autocompleters[div.id] = new Ajax.Autocompleter(field.id,div.id,'query_list.php?list='+div.getAttribute('autocomplete_type'),{paramName:'query'});
						
						// IE9: auto_complete DIVs HAVE INCORRECT OFFSET PARENT
						if(window.details.ieVersion()>=9){
							var tab_offset = $('ctlTabs').cumulativeOffset();
							div.style.margin = '-'+(tab_offset.top-4)+'px 0px 0px -'+(tab_offset.left-4)+'px';
						}
					}
				});
				/*
				// POPULATE SELECT DROPDOWNS
				$$('select').each(function(select){
					select.value = select.getAttribute('_value');
				});
				*/
			});
			function page_cancel(){
				window.location = 'index.php';
			}
			function return_false(){ return false; }
			function cancel_enter_key(evt){
				return evt.keyCode!=13;
			}
		</script>
		<? if(function_exists('append_to_head')) append_to_head(); ?>
	</head>
	<body>
		<div>
			<a href="/"><img src="img/logo.jpg" /></a>
			<div id="nav_ribbon">
				<a href="/">Main site</a> | <a href="./">Back-end</a>
			</div>
			<div id="user_ribbon">
				<a href="<?php echo get_permalink(407); ?>" title="Log Out">Log Out</a>
			</div>
		</div>
		<div id="main_content">
			<div id="page_notice"></div>
			<div id="error_notice"></div>