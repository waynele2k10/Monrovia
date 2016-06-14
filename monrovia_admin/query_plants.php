<?php
	// THIS SCRIPT IS USED TO QUERY COMPANION PLANTS
	require_once('../inc/class_search_plant.php');
	$search = new search_plant($_POST['query'],'ALL',false);
	$search->search(false);
	$search->output_results_cms();
?>