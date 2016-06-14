<?php
	require($_SERVER['DOCUMENT_ROOT'].'/inc/class_monrovia_user.php');

	$input = $_GET['input'];
	if($input!=''){
		$test = new monrovia_user();
		if($_GET['action']=='encrypt'){
			die($test->password_encrypt($input));
		}else{
			die($test->password_decrypt($input));
		}
	}
?>
<form action="?" method="get">
	<input name="input" />
	<br />
	<select name="action">
		<option value="decrypt">decrypt</option>
		<option value="encrypt">encrypt</option>
	</select>
	<br />
	<input type="submit" />
</form>