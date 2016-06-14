<?php
	//require_once('class_cache.php');
	//require_once('utility_functions.php');
	
	$show_all_errors = true;
	$admin_email = 'msteele@phelpsagency.com';
	$top_level_sections = array('About Us','Design Inspiration','Gardening How-To','Plant Catalog','Home','Community','Plant Savvy','Find A Garden Center','Specialty Gardening','Retailers/Professionals'); // SITE SEARCH USES THIS ARRAY
    $view_all_max = 200; // THE MAXIMUM NUMBER OF RESULTS ALLOWED ON A SEARCH-RESULT PAGE
	$wish_list_item_limit = 200;
	
	$campaign_monitor_api_key = 'edb49e3db7b9fa2a69d2b1a64db22447';
	$campaign_monitor_client_id = '81b5d59d4c4367ce419af0eff67b37f8';
	$campaign_monitor_list_id = 'ba5d884f4349700f38f0d816d0083212';		// Monrovia Test List - AUTOMATICALLY OVERRIDDEN ON THE PRODUCTION SERVER
	
	$mapquest_api_key = 'Fmjtd%7Cluua2hutnd%2C2n%3Do5-96ax9r';
    
	// INIT GOOGLE ANALYTICS INFO OBJECT
	$google_analytics = array(
		'account_id'=>'desktop',
		'page_id'=>'',
		'custom_variables'=>array()
	);
	
	$GLOBALS['server_info'] = array('physical_root'=>'','www_root'=>'','environment'=>'','server_info'=>array());
	
	if($GLOBALS['server_info']['environment']==''&&@file_exists('/var/www/monrovia.com/root/')){

		// PRODUCTION SERVER
		$GLOBALS['server_info'] = array(
			'physical_root'=>'/var/www/monrovia.com/root/',
			'www_root'=>'http://www.monrovia.com/',
			'www_root_mobile'=>'http://m.monrovia.com/',
			'qa_root'=>'gardening-q-and-a',
			'environment'=>'prod',
			'db'=>array(
				'host'=>'localhost',
				'name'=>'monrovia_website',
				'low_user'=>'websiteuser',
				'low_pass'=>'?dsf24d1!',
				'med_user'=>'cmsuser',
				'med_pass'=>'!jbhd3ngf!'
			)
		);		
		$campaign_monitor_list_id = '0418a08fd1498123104cfa34615d20ae';
		$google_analytics['account_id'] = 'UA-3929008-1';
	}
	
	if($GLOBALS['server_info']['environment']==''){
		// STAGING SERVER HAS open_basedir RESTRICTION, SO WE'LL ASSUME IT'S STAGING IF IT ERRORS OUT

		$root_path = '';$script_path = '';
		if(isset($_SERVER['HTTP_HOST'])) $root_path = 'http://' . $_SERVER['HTTP_HOST'] . '/'; // SERVER_NAME
		if(isset($_SERVER['argv'])&&isset($_SERVER['argv'][0])) $script_path = @$_SERVER['argv'][0];
		
		if(strpos(strtolower($root_path),'tpgphpdev1')!==false||strpos($script_path,'/var/www/vhosts/tpgphpdev1.net/httpdocs/')!==false){
			// STAGING SERVER
			$GLOBALS['server_info'] = array(
				'physical_root'=>'/var/www/vhosts/tpgphpdev1.net/httpdocs/',
				'www_root'=>'http://www.tpgphpdev1.net/',
				'www_root_mobile'=>'http://m.tpgphpdev1.net/',
				'qa_root'=>'gardening-q-and-a',
				'environment'=>'staging',
				'db'=>array(
					'host'=>'localhost',
					'name'=>'monrovia',
					'low_user'=>'userofdb',
					'low_pass'=>'??w2e4w2a4n2t4t2a4c2o4s!!',
					'med_user'=>'userofdb',
					'med_pass'=>'??w2e4w2a4n2t4t2a4c2o4s!!'
				),
				'http_auth'=>array(
					'user'=>'monrovia',
					'password'=>'monrovia'
				)
			);

			// HTTP AUTH; CRONS EXEMPT
			if(isset($_SERVER['HTTP_HOST'])&&$script_path==''){
				// image_set_thumbail.php IS EXEMPT AND OPEN TO THE PUBLIC (CATALOG CREATOR NECESSITY)
				if(!isset($_SERVER['REQUEST_URI'])||strpos($_SERVER['REQUEST_URI'],'image_set_thumbail.php')===false){
				
					if(isset($_GET['m'])&&$_GET['m']=='1'){
						setcookie('validated','1',time()+60*60*24*30,'/');
					}else{
						if(!isset($_COOKIE['validated'])||$_COOKIE['validated']!='1'){
							header('HTTP/1.1 301 Moved Permanently');
							header('location:http://www.monrovia.com');
							exit;
						}					
					}
				
				/*
					if (!isset($_SERVER['REMOTE_USER'])||$_SERVER['REMOTE_USER']=='') {
						header('WWW-Authenticate: Basic realm="Staging Server"');
						header('HTTP/1.1 401 Unauthorized');
						exit;
					} else {
						$credentials_entered = base64_decode(str_replace('Basic ','',$_SERVER['REMOTE_USER'])); // EXAMPLE: Basic bW9ucm92aWE6bW9ucm92aWE=
						if($credentials_entered!=$GLOBALS['server_info']['http_auth']['user'].':'.$GLOBALS['server_info']['http_auth']['password']){
							header("Location: http://www.monrovia.com/");
							exit();
						}
					}
				*/
				}
			}


			
		}		
	}
	
	if($GLOBALS['server_info']['environment']==''){
	
		// DEV SERVER
		
		$server_name = str_replace('m.','',$_SERVER['SERVER_NAME']); // m.monrovia.localhost -> monrovia.localhost
		
		$GLOBALS['server_info'] = array(
			'physical_root'=>trim(str_replace('/mobile','/',$_SERVER['DOCUMENT_ROOT']),'/').'/',
			'www_root'=>'http://'.$server_name.'/',
			'www_root_mobile'=>'http://m.'.$server_name.'/',
			'qa_root'=>'gardening-q-and-a',
			'environment'=>'dev',
			'db'=>array(
				'host'=>'localhost',
				'name'=>'monrovia_website',
				'low_user'=>'root',
				'low_pass'=>'mysql123',
				'med_user'=>'root',
				'med_pass'=>'mysql123'
			)
		);
	}
			
	// MAKE SURE MAGIC QUOTES ARE DISABLED
	if(get_magic_quotes_gpc()&&$GLOBALS['server_info']['environment']!='prod') die('Please turn off magic quotes (http://www.php.net/manual/en/security.magicquotes.disabling.php)');

	// HIGH-LEVEL INIT STUFF
	date_default_timezone_set('America/Los_Angeles');
	ini_set('display_errors',($GLOBALS['server_info']['environment']=='prod')?0:1);
	
	if($show_all_errors&&$GLOBALS['server_info']['environment']!='prod'){
		ini_set('error_reporting', E_ALL);
		error_reporting(E_ALL);
	}else{
		if($show_all_errors&&$GLOBALS['server_info']['environment']=='prod'){
			ini_set('error_reporting', 0);
			error_reporting(0);
		}else{
			ini_set('error_reporting', E_ERROR|E_PARSE);
			error_reporting(E_ERROR|E_PARSE);		
		}
	}
	
	sql_set_user();
	
	function sql_set_user($permissions_level = 'low'){
		if($permissions_level=='med'){
			$GLOBALS['db_user'] = $GLOBALS['server_info']['db']['med_user'];
			$GLOBALS['db_pass'] = $GLOBALS['server_info']['db']['med_pass'];
		}else{
			$GLOBALS['db_user'] = $GLOBALS['server_info']['db']['low_user'];
			$GLOBALS['db_pass'] = $GLOBALS['server_info']['db']['low_pass'];
		}
	}
	
?>