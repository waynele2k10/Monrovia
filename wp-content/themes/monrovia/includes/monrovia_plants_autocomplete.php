<?
ini_set('display_errors','on');
	error_reporting(E_ALL);

	//header('Content-Type: text/javascript');
	require( dirname(__FILE__) . '/wp-load.php' );
	include_once( dirname(__FILE__) . '/wp-config.php'); 
	require_once('classes/class_sql.php');
	require_once('utility_functions.php');
	$plants_result = mysql_query("SELECT common_name FROM plants WHERE common_name LIKE '%".sql_sanitize('rose')."%' LIMIT 10");
	$plants = array();
	while ( $plant = mysql_fetch_array($plants_result) )
	{
		$plants[] = "'".js_sanitize(unescape_special_characters($plant['common_name']))."'";
	}

	echo "({ query:'".$_REQUEST['query']."', suggestions:[".join(',', $plants)."], data:[".join(',', $plants)."] })";

?>