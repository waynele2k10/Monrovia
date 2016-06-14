<?php
require( '../../wp-load.php' );
include_once( '../../wp-config.php');

ini_set('display_errors','on');
	error_reporting(E_ALL);

	////////////////////////////////////////////////////////////////////////////
	//  Sync Paused for new labels, remove exit to re-enable
	////////////////////////////////////////////////////////////////////////////

	//if (  !$_GET['dev'] == 'yes' ){
		//exit();
	//}
	////////////////////////////////////////////////////////////////////////////
	
	set_time_limit(5400);	// ALLOW UP TO AN HOUR AND A HALF

	require_once('horticultural_printers_generate_xml.php');
	require_once('../nusoap/lib/nusoap.php');

	//$rpc_url = "http://www.horticulturalprinters.com";
	$rpc_url = "http://www.epopglobal.com";
	//$rpc_path = "/monrovia/webservices/monroviaupdateservice.asmx";
	$rpc_path = "/MonroviaIX/Webservices/MonroviaUpdateService.asmx";
	$username = 'monrovia';
	$password = 'plants';
	$wsdl=$rpc_url.$rpc_path."?wsdl";

	$is_full_synch = $_GET['full_synch']=='1';
	$plant_id = $_GET['id'];

	$response = generate_xml($is_full_synch);
	$xml_data = $response[1];
	$id = $response[0];

/*
$xml_data = <<< XMLDATA
<?xml version="1.0"?>
<DataSync>
	<Plant>
		<ActionCode>UPDATE</ActionCode>
		<ItemSizeID>99999#5</ItemSizeID>
		<Item>99999</Item>
		<SpecialIcon></SpecialIcon>
		<Size>310</Size>
		<SizeDescription>10-12&quot;</SizeDescription>
		<Botanical>Lantana x 'New Gold'</Botanical>
		<BotanicalShort>Lantana x 'New Gold'</BotanicalShort>
		<CommonName>New Gold Lantana</CommonName>
		<PriAttribute>Summer Flowering</PriAttribute>
		<AvgLandscapeSize>Moderate grower to 12 to 15 in. tall, 18 to 24 in. wide.</AvgLandscapeSize>
		<CareInstructions>Follow a regular watering schedule during the first growing season to establish a deep, extensive root system. Feed with a general purpose fertilizer before new growth begins in spring. For a tidy, neat appearance, shear annually to shape.</CareInstructions>
		<ContainerSize>3</ContainerSize>
		<LegalVolume>2.8 gal. (10.44 L)</LegalVolume>
		<UPC>014949020562</UPC>
		<FlowerTime>Spring through fall</FlowerTime>
		<PlantBenefits>Brilliant golden yellow flowers in profuse clusters bloom from spring through fall. Trailing growth is excellent for use as groundcover or tumbling from hanging baskets. Excellent annual for colder climates. Evergreen in frost-free climates.</PlantBenefits>
		<SunExpose>Full sun</SunExpose>
		<WaterReq>Once established, needs only occasional watering.</WaterReq>
		<CZoneHigh>11</CZoneHigh>
		<CZoneLow>10</CZoneLow>
	</Plant>
</DataSync>
XMLDATA;
*/

if($_GET['synch']!='1') die($xml_data);

if($xml_data!=''){


	$status = '';
	$details = '';
	$err = '';
	echo $message = "DATE/TIME: " . date('Y-m-d H:i:s') . "\nREQUEST:\n" . $xml_data . "\n\n*************\n\n";


	

	// SET NAMESPACE/PARAMETERS
	$params = array('plantXML'=>$xml_data, 'username'=>$username, 'password'=>$password);
	//$namespace = $rpc_url.'/monrovia/webservices';
	$namespace = $rpc_url.'/MonroviaIX/Webservices';

	$client = new nusoap_client($wsdl, 'wsdl', false, false, false, false, 600, 600);

	// SEND REQUEST
	
	$client->call('UpdatePlant', $params, $namespace, 'MonroviaDataSynchronizer/UpdatePlant');
	//$client->call('UpdatePlant', $params, $namespace, 'UpdatePlant');
	if ($client->fault) {
		echo $fault = $client->fault;
	}

	$err = trim($client->getError());
	echo $soapReq;
	echo '<pre>'.$client->request.'</pre>';
	echo "url:".$namespace."\n";
	echo $err;
	$status = ($err!='')?'ERROR':'SUCCESS';
	$details = $client->response;

	$message = "DATE/TIME: " . date('Y-m-d H:i:s') . "\r\nSTATUS: " . $status . "\r\nREQUEST:\r\n" . $xml_data . "\r\n\r\nRESPONSE:\r\n\r\n" . $details . "\r\n\r\n*************\r\n\r\n";

	if($is_full_synch){
		if($err==''){
			// UNMARK PLANTS
			foreach($id as $pid){
				mysql_query("UPDATE plants SET synch_with_hort=0 WHERE id=".$pid);
			}
		}else{
			// UNMARK PLANTS
			foreach($id as $pid){
				mysql_query("UPDATE plants SET synch_with_hort=2 WHERE id=".$pid);
				//wp_mail('brettex@hotmail.com','Monrovia Plant Updated',$message);
			}
		}
	}else{
		if($err==''){
			// UNMARK PLANTS
			mysql_query("UPDATE plants SET synch_with_hort=0 WHERE synch_with_hort=1");
			//wp_mail('brettex@hotmail.com','Failed Hort Synch','No Error: '.$err.'<br />Fault:'.$fault);
		}else{
			wp_mail('brettex@hotmail.com','Failed Hort Synch','Yes Error: '.$err.'<br />Fault:'.$fault);
			//mysql_query("UPDATE plants SET synch_with_hort=0 WHERE synch_with_hort=1");
		}
		
		// SEND ALERT TO GROWER TAGS
		//wp_mail(array('josh@growertags.com','tim@growertags.com'),'Monrovia Plant Updated',$message);
	}
	
	
	// UNMARK PLANTS
	if($err==''){
		mysql_query("UPDATE plants SET synch_with_hort=0 WHERE synch_with_hort=1");
		//wp_mail('brettex@hotmail.com','Monrovia Plant Updated',$message);
	}
	//wp_mail(array('josh@growertags.com','tim@growertags.com'),'Monrovia Plant Updated',$message);
	//wp_mail('brettex@hotmail.com','Monrovia Plant Updated',$message);


	$message = $xml_data;

	// WRITE LOG
	$file_handler = fopen("logs/horticultural_printers_sync.txt", 'a+');
	fwrite($file_handler,$message);

	// CLEAN UP
	unset($client);

	echo($xml_data);
}

?>
