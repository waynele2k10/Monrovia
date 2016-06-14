<?php
	// THIS WAS UPDATED FOR MOBILE PUSH
	require('../utility_functions.php');
	$result = clear_cache();
	if(isset($_REQUEST['output_result'])&&$_REQUEST['output_result']=='1') echo(($result==1)?'success':'fail');
?>