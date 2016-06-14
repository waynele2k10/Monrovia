<?php
// I DON'T THINK THIS FILE IS BEING USED
	require_once('../inc/init.php');

	function perform_search(){
		$query = 'test';
		$sections = search_modules($query);
		output_section_results($sections,'About Us');
	}
	function output_section_results(&$search_results,$section_name){
		if($search_results[$section_name]){
			foreach($search_results[$section_name] as &$page){
				?>
					<a href="<?=$page->info['path']?>"><h2><?=$page->info['title']?></h2></a>
					<?=$page->info['search_summary']?>
				<?
			}
		}
	}
	perform_search();
?>