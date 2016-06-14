<?php
	require_once('class_record.php');
	require_once('qa_general.php');

	class qa_category extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM qa_categories TABLE, EXCEPT FOR id
		var $table_fields = 'is_active,parent_category_id,url_key,parent_path,name,description,thumbnail'; // MAKE SURE THIS VALUE DOES NOT BEGIN OR END WITH COMMAS

		function qa_category($record_id = ''){
			$this->table_name = 'qa_categories';
			
			$this->subcategories = array();

			$this->full_path = '';
			$this->ancestor_info = null;
			$this->ancestor_keys = null;
			
			if($record_id!='') $this->load($record_id);
		}

		function load($record_id){
			parent::load($record_id);

			if($this->info['id']!=''){
				$this->populate_dumb_values();
				$this->get_full_path();
			}
		}
		
		function get_ancestor_info(){
			if($this->ancestor_info!=null) return $this->ancestor_info;
			$ret = array(array(
				'id'=>$this->info['id'],
				'url_key'=>$this->info['url_key'],
				'parent_category_id'=>$this->info['parent_category_id'],
				'name'=>$this->info['name']
			));
			$parent_id = $this->info['parent_category_id'];
			do {
				$query = 'SELECT url_key, parent_category_id, name FROM qa_categories WHERE id="' . $parent_id . '"';
				$result = sql_query($query);
				$num_rows = @mysql_numrows($result);
				if($num_rows==1){
					$ret[] = array(
						'id'=>$parent_id,
						'url_key'=>mysql_result($result,0,'url_key'),
						'parent_category_id'=>mysql_result($result,0,'parent_category_id'),
						'name'=>mysql_result($result,0,'name')
					);
					$parent_id = mysql_result($result,0,'parent_category_id');
				}else{
					$parent_id = '';
				}
			}while(isset($parent_id)&&intval($parent_id)!=0);
			$this->ancestor_info = array_reverse($ret);

			// POPULATE full_path
			$full_path = '/' . $GLOBALS['server_info']['qa_root'] . '/';
			for($i=0;$i<count($this->ancestor_info);$i++){
				$full_path .= $this->ancestor_info[$i]['url_key'] . '/';
				$this->ancestor_info[$i]['full_path'] = $full_path;
			}
			return $this->ancestor_info;
		}
		
		function get_full_path(){
			if($this->full_path==''){
				$ancestor_info = $this->get_ancestor_info();
				$ret = array();
				for($i=0;$i<count($ancestor_info);$i++){
					$ret[] = $ancestor_info[$i]['url_key'];
				}
				$this->full_path = '/' . $GLOBALS['server_info']['qa_root'] . '/' . implode('/',$ret) . '/';
			}
			return $this->full_path;
		}
		
		function get_subcategories($include_inactive = false){
			if(isset($this->info['id'])&&intval($this->info['id'])>0){
				$this->subcategories = get_subcategories($this->info['id'],$this->get_full_path(),$include_inactive);
			}
			
			return $this->subcategories;
		}
		
		function get_questions($offset_index = null,$results_per_page = null){
			$temp = new search_qa_question($this->info['id'],null,$offset_index,$results_per_page);
			$results = $temp->search();
			$this->questions = $results['results'];
			return $results;
		}
		
		function populate_dumb_values(){
			if(isset($this->info['id'])&&is_numeric($this->info['id'])){

			}
			
		}


	}
?>
