<?php require_once('inc/init.php');

	$plant_test = new plant();
	$plant_test->load(1);
	$plant_test->info['is_new'] = '1';
	$plant_test->save();

	/*
	$plant2 = $plant_test;
	$plant2->info['id'] = '';
	$plant2->save();
*/


	//var_dump($plant_test);

 $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $endtime = $mtime;
   $totaltime = ($endtime - $starttime);
   echo "This page was created in ".$totaltime." seconds";

?>