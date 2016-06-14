<?php require( dirname(__FILE__) . '/wp-load.php' ); ?>
<?php include_once( dirname(__FILE__) . '/wp-config.php'); ?>
<?php
	$date = date('Y-m-d H:i:s');
	//IF PLANT WAS UPDATED, CHECK FOR IT IN THE LOG DATABASE
	if(isset($_GET['id'])){
		$id = $_GET['id'];
		$plant = mysql_query("SELECT common_name FROM plants WHERE is_active = 1 AND release_status_id IN (1,2,3,4) AND `id` = '$id'");
		if(mysql_num_rows($plant) > 0){
			echo 'Its active!';
			//ITS ACTIVE, SO MAKE SURE ITS IN THE LOG TABLE, IF NOT ALREADY
			$log = 	mysql_query("SELECT * FROM wp_relevanssi_log WHERE `ip` = '$id'");
			if(mysql_num_rows($log) < 1){
				echo 'Added to Log!';
				$name = mysql_result($plant, 0);
				$array = array('{{#174}}', '{{#153}}');
				$name =  str_replace($array, '', $name);
			mysql_query("INSERT INTO wp_relevanssi_log ( `query`, `hits`, `time`, `user_id`, `ip`) VALUES ( '$name', '1000', '$date', '0', '$id')");
			}
		// ITS NOT ACTIVE, SO MAKE SURE ITS NOT IN THE LOG TABLE. IF SO, REMOVE IT	
		} else{
			echo 'Not Active!';
			$log = 	mysql_query("SELECT * FROM wp_relevanssi_log WHERE `ip` = '$id'");
			if(mysql_num_rows($log) > 0){
				echo 'Removed from log!';
				mysql_query("DELETE FROM wp_relevanssi_log WHERE `ip` = '$id'");
			}
		}
		
	} else {
		
	// REMOVE ALL PLANTS FIRST
	mysql_query("DELETE FROM wp_relevanssi_log WHERE `hits` = 1000");
    // QUERY ALL PLANTS
    $plants = mysql_query("SELECT id, common_name FROM plants WHERE is_active = 1 AND release_status_id IN (1,2,3,4)");
	
	$i=0;
	//Insert into the Database
	while($row = mysql_fetch_array($plants)){
		
		//Remove special Characters 
		$array = array('{{#174}}', '{{#153}}');
		$name =  str_replace($array, '', $row['common_name']);
		$item = $row['id'];
		//echo $name;
		if($name != ''){
			mysql_query("INSERT INTO wp_relevanssi_log ( `query`, `hits`, `time`, `user_id`, `ip`) VALUES ( '$name', '1000', '$date', '0', '$item')");
			$i++;
		}
	}
	echo "Total Inserts: ".$i;
	//wp_mail( 'brettex@hotmail.com', 'Plant Table Updated', 'Another CRON Job Success' );
	}



?>