<?php
	function track_banner_impression($banner_group='',$banner_version='',$banner_size=''){
		$referrer = '';
		if(isset($_SERVER['HTTP_REFERER'])) $referrer = $_SERVER['HTTP_REFERER'];
		require($_SERVER['DOCUMENT_ROOT'].'/inc/init.php');
		sql_query("INSERT INTO banner_impressions(impression_date,referrer,banner_group,banner_version,banner_size) VALUES(NOW(),'".sql_sanitize($referrer)."','".sql_sanitize($banner_group)."','".sql_sanitize($banner_version)."','".sql_sanitize($banner_size)."')");
	}
?>