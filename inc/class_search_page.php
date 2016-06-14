<?php
	function search_modules($query){
		$query = parse_alphanumeric($query,'" ');
		$query_used = to_mysql_boolean_mode($query);
		//$inflection = new Inflect();
		//$query_used = $inflection->singularize_all(parse_alphanumeric($query,'" '));

		$ret = array();

		//$result = sql_query("SELECT id,section_top_level FROM pages WHERE id IN (SELECT page_id FROM editable_modules INNER JOIN page_modules ON editable_module_id = id WHERE content_search LIKE '%$query_used%') ORDER BY section_top_level");

		$result = sql_query("SELECT GROUP_CONCAT(page_id) AS ids FROM pages INNER JOIN page_modules on page_modules.page_id = pages.id INNER JOIN press_releases ON press_releases.editable_module_id = page_modules.editable_module_id WHERE press_releases.is_active = 0");
		$inactive_press_release_csv = mysql_result($result,0,"ids");
		if($inactive_press_release_csv=='') $inactive_press_release_csv = '0';

		// 2012-04-24 - BROKE OUT INTO TWO QUERIES FOR BETTER PERFORMANCE
		$result = sql_query("SELECT page_id FROM editable_modules INNER JOIN page_modules ON editable_module_id = id WHERE MATCH(name,content_search) AGAINST ('$query_used' IN BOOLEAN MODE)");
		$page_ids = '';
		$num_rows = @mysql_numrows($result);
		for($i=0;$i<$num_rows;$i++){
			$page_ids .= ',' . mysql_result($result,$i,"page_id");			
		}
		if($page_ids!=''){
			$page_ids = substr($page_ids,1);

			$result = sql_query("SELECT id,section_top_level,path,title FROM pages WHERE id IN ($page_ids) AND id NOT IN ($inactive_press_release_csv) ORDER BY section_top_level ASC, id DESC");

			$page_ids = array();

			$num_rows = @mysql_numrows($result);
			for($i=0;$i<$num_rows;$i++){
				$page_id = mysql_result($result,$i,"id");
				$title = mysql_result($result,$i,"title");
				$path = mysql_result($result,$i,"path");
				$path = str_replace('//','/',$path);
				$path = rtrim($path,'/');

				if(strpos($path,'.js')===false&&strpos($path,'text/javascript')===false&&strpos($path,'.cab')===false){
					if(!isset($page_ids[$path])&&!isset($page_ids[$title])){
						$page_ids[$path] = '1';
						$page_ids[$title] = '1';

						$page = new page();
						$page->info['id'] = $page_id;
						$page->info['path'] = $path;
						$page->info['title'] = $title;
						$ret[mysql_result($result,$i,"section_top_level")][] = $page;
					}
				}
			}
		}

		return $ret;
	}
?>