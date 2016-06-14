<?php
	require_once($_SERVER['DOCUMENT_ROOT'].'/monrovia_blog/blog-blog-header.php');

	function blog_proxy_log_in($user_login,$user_password){
		$credentials = array('user_login'=>$user_login,'user_password'=>$user_password,'remember'=>true);
		$result = blog_signon($credentials,false);
		return ($result instanceOf BLOG_User);
	}

	function blog_proxy_log_out(){
		blog_logout();
	}

//	function blog_proxy_get_credentials($user_id){
//		var_dump(get_userdata($user_id));
//	}

	function blog_proxy_set_password($user_id,$password){
		blog_set_password($password,$user_id);
	}

	function blog_proxy_create_user($user_login, $user_pass, $user_email, $user_firstname, $user_lastname, $user_role = 'subscriber'){
		// RETURNS ID OF NEW USER, OR NULL IF FAILED
		require_once('../monrovia_blog/blog-includes/registration.php');
		$user_friendlyname = $user_firstname . ' ' . $user_lastname;
		return blog_insert_user(array('user_login'=>$user_login,'user_pass'=>$user_pass,'user_email'=>$user_email,'first_name'=>$user_firstname,'last_name'=>$user_lastname,'display_name'=>$user_friendlyname,'nickname'=>$user_friendlyname,'rich_editing'=>false,'role'=>$user_role));
	}
	//echo blog_proxy_log_in('test','test');

	//echo(blog_proxy_create_user('test2','test2','test2@test.com','first','last'));

//blog_proxy_get_credentials(11);

	function blog_proxy_output_avatar($id,$size = 71){
		echo(get_avatar($id,$size));
	}
?>