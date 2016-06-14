<?php 

require_once 'vendor/data-source.php';


function csvLoadFile($fname){

	$csvStatus = array();
	$csvStatus['status'] = true;
	$csvStatus['content'] = "";
	$clmID = 'Item';
	$clmIDTwo =  'item';

	//get file and load array by column
	$csv = new File_CSV_DataSource;
	$csv->load('../csv-tmp/'.$fname);
	$plants = $csv->getColumn($clmID);
		//check for capital
	if (empty($plants)){
		$plants = $csv->getColumn($clmIDTwo);
	}

	if (empty($plants)){	
		$csvStatus['status'] = false;
		$csvStatus['fname'] = $fname;
		$csvStatus['msg'] = "No Plants found in your file.";	
	}else{
		// strip to 5 char
		foreach ($plants as &$plant) { $plant = substr($plant, 0, 5); }
		$csvStatus['content'] = $plants;
		$csvStatus['status'] = true;
		$csvStatus['fname'] = $fname;

	}

	 return json_encode($csvStatus);
}

?>