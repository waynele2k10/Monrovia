<?php
	require($_SERVER['DOCUMENT_ROOT'].'/inc/class_plant.php');
	require($_SERVER['DOCUMENT_ROOT'].'/inc/class_plant_image_set.php');
	sql_disconnect();
	sql_set_user('med');
	sql_connect();

	function populate_phpmetaphones(){

		$result = sql_query("SELECT id,common_name,botanical_name,trademark_name,synonym FROM plants WHERE id > 2584");

		$num_rows = @mysql_numrows($result);
		for($i=0;$i<$num_rows;$i++){
			$name_combination = mysql_result($result,$i,"common_name") . ' ' . mysql_result($result,$i,"botanical_name") . ' ' . mysql_result($result,$i,"trademark_name") . ' ' . mysql_result($result,$i,"synonym");
			$str_metaphone = (' ' . to_metaphone_string($name_combination) . ' ');
			sql_query("UPDATE plants SET php_metaphone='$str_metaphone' WHERE id=".mysql_result($result,$i,'id'));
		}

		echo('done');

	}

	function populate_keywords(){

		$result = sql_query("SELECT id FROM plants WHERE id > 2584");

		$num_rows = @mysql_numrows($result);
		for($i=0;$i<$num_rows;$i++){
			$plant = new plant(mysql_result($result,$i,"id"));
			$plant->update_keywords();
			//$keywords = sql_sanitize(str_replace("'","''",$plant->compile_keywords()));
			//sql_query("UPDATE plants SET keywords='$keywords' WHERE id=".mysql_result($result,$i,'id'));
		}

		echo('done');

	}

	function generate_thumbnails(){
	?>
	<table>
	<?

		$result = sql_query("SELECT id FROM plant_image_sets WHERE id > 3418");

		$num_rows = @mysql_numrows($result);
		for($i=0;$i<$num_rows;$i++){

			set_time_limit(60);	// ALLOW UP TO A MINUTE PER IMAGE SET

			$image_set = new plant_image_set(mysql_result($result,$i,"id"));
			$image_set->determine_image_paths();
			$success = $image_set->generate_thumbnails();
			?>

			<tr><td><?=$image_set->info['id']?></td><td><?=($success)?'success':'fail'?></td></tr>

			<?

			flush();

		}
	?>
	</table>
	<?
		echo('done');

	}

	function clear_search_index(){
		// BE SURE TO RUN A WEBSITE CRAWLER ON THE SITE AFTER EXECUTING THIS METHOD
		sql_query("DELETE FROM pages;");
		sql_query("DELETE FROM page_modules;");
		echo('done');
	}

		//populate_phpmetaphones();
		//generate_thumbnails();
		//populate_keywords();

		//$record = new plant(672);
		//var_dump($record);

		//clear_search_index();

?>