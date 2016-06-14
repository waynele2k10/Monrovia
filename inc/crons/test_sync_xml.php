<?php
set_time_limit(600);
require( '../../wp-load.php' );
include_once( '../../wp-config.php');
ini_set('display_errors', 'on');
error_reporting(E_ALL);

global $wpdb;
$_GET['full_synch'] = '1';
//set_time_limit(5400); // ALLOW UP TO AN HOUR AND A HALF
require_once('test_sync_generate_xml.php');

require_once('../nusoap/lib/nusoap.php');
//$rpc_url = "http://www.horticulturalprinters.com";
$rpc_url = "http://www.epopglobal.com";
//$rpc_path = "/monrovia/webservices/monroviaupdateservice.asmx";
$rpc_path = "/monroviaIX/webservices/monroviaupdateservice.asmx";
$username = 'monrovia';
$password = 'plants';
$wsdl = $rpc_url . $rpc_path . "?wsdl";

$is_full_synch = true;
if (isset($_GET['full_synch'])) {
    $is_full_synch = ($_GET['full_synch'] == '1') ? true : false;
}
$plant_id = '';
if (isset($_GET['id'])) {
    $plant_id = $_GET['id'];
}

$response = generate_xml($is_full_synch);
$xml_data = $response[2];
$ids = $response[0];
$id_plants = $response[1];


if ($xml_data != '' && count($ids) > 0) {
    $_time = time();

    $file_handler = fopen("logs/test_sync_" . $_time . ".txt", 'a+');

    $status = '';
    $details = '';
    $err = '';

    $message = "DATE/TIME: " . date('Y-m-d H:i:s') . "\nREQUEST:\n" . " xml_data " . "\n\n*************\n\n";
    $filemessage = $message;

    //echo "</pre>".$xml_data."</pre>"; exit();
    // SET NAMESPACE/PARAMETERS
    $params = array('plantXML' => $xml_data, 'username' => $username, 'password' => $password);
    //$namespace = $rpc_url.'/monrovia/webservices';
    $namespace = $rpc_url . '/MonroviaIX/Webservices';

    $client = new nusoap_client($wsdl, 'wsdl', false, false, false, false, 600, 600);

    // SEND REQUEST
    try {
        $client->call('UpdatePlant', $params, $namespace, 'MonroviaDataSynchronizer/UpdatePlant/');
    } catch (Exception $exc) {
        echo $exc->getMessage();
        echo $exc->getTraceAsString();
        echo "Exception";
        exit();
    }


    //$client->call('UpdatePlant', $params, $namespace, 'UpdatePlant');
    if ($client->fault) {
        $fault = $client->fault;
        echo 'Test:' . print_r($fault);
    }

    $err = trim($client->getError());
	
    $status = ($err != '') ? 'ERROR' : 'SUCCESS';
    $details = $client->response;
    $request = $client->request;

    $filemessage .= "DATE/TIME: " . date('Y-m-d H:i:s') . "\r\nSTATUS: "
            . $status . "\r\nREQUEST:\r\n"
            . " $request " . "\r\n\r\nRESPONSE:\r\n\r\n"
            . $details . "\r\n\r\n*************\r\n\r\n";
    
    fwrite($file_handler, $filemessage);

    if ($err == '') {
        foreach ($ids as $pid) {
            $wpdb->update( 
				'plants', 
				array( 
					'synch_with_hort' => '0'
				), 
				array( 'id' => $pid ), 
				array( 
					'%d'
				)
			);
        }
		foreach ($id_plants as $id_plant) {
			$wpdb->delete( 'plants_sync', array( 'id' => $id_plant ) );
		}
    } else {
        foreach ($ids as $pid) {
			$wpdb->update( 
				'plants', 
				array( 
					'synch_with_hort' => '2'
				), 
				array( 'id' => $pid ), 
				array( 
					'%d'
				)
			);
        }
    }


    $_email_message = "Result: $err " . var_export($ids, true);
    wp_mail('wayne.le2k10@gmail.com', 'Hort Sync', $_email_message);
    wp_mail('jvo@bluecalypso.com', 'Hort Sync', $_email_message);

    unset($client);
}