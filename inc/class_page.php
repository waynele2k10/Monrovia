<?php
	class page extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM pages TABLE, EXCEPT FOR id
		var $table_fields = 'section_top_level,title,path,date_last_access';

		function page($record_id = ''){
			$this->table_name = 'pages';
			if($record_id!='') $this->load($record_id);
		}
		function load($record_id,$load_module_data = false){
			parent::load($record_id);

			if($load_module_data&&is_numeric($this->info['id'])){
				// LOAD MODULE DATA
				$result = sql_query("SELECT editable_module_id FROM page_modules WHERE page_id = ".$this->info['id']." ORDER BY load_order ASC");
				$num_rows = @mysql_numrows($result);
				$this->modules = array();
				for($i=0;$i<$num_rows;$i++){
					//$this->info['search_summary'] = mysql_result($result,$i,"editable_module_id");
					$module_id = mysql_result($result,$i,"editable_module_id");
					$module = new editable_module($module_id);
					$this->modules[] = $module;
					if($i==0) $this->info['search_summary'] = substr($module->info['content_search'],0,50) . '...';
				}
			}
		}
	}
/* COMMENTED OUT 4/13/2011 FOR SITE SPEED REASONS*/
/* COMMENTED BACK IN 4/25/2012 AFTER DATABASE OPTIMIZATIONS */
	$current_page = new page(get_page_id());
	if($current_page->info['id']!=''){
	}
/**/
?>