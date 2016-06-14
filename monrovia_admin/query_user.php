<?

	header('Content-Type: text/javascript');
	require_once('../inc/init.php');
	require_once('../inc/class_monrovia_user.php');
	
	// MAKE SURE USER HAS Q&A MODERATION PERMISSION
	$monrovia_user = new monrovia_user($_SESSION['monrovia_user_id']);
		
	$monrovia_user->permission_requirement('user');

	@$email_address = $_GET['email_address'];
	@$user_name = $_GET['user_name'];

	$result = sql_query('SELECT id FROM monrovia_users WHERE (email_address = "' . $email_address . '" AND email_address <> "") OR (user_name = "' . $user_name . '" AND user_name <> "")');
	if(intval(@mysql_numrows($result))==1){
		$user = new monrovia_user(mysql_result($result,0,'id'));
		die(json_encode(array(
			'id'=>js_sanitize($user->info['id']),
			'is_active'=>js_sanitize($user->info['is_active']),
			'user_name'=>js_sanitize($user->info['user_name']),
			'password'=>js_sanitize($user->info['password']),
			'email_address'=>js_sanitize($user->info['email_address']),
			'first_name'=>js_sanitize($user->info['first_name']),
			'last_name'=>js_sanitize($user->info['last_name']),
			'zip'=>js_sanitize($user->info['zip']),
			'permissions'=>js_sanitize($user->info['permissions']),
			'date_last_login'=>js_sanitize($user->info['date_last_login'])
		)));
	}else{
		exit;
	}
?>