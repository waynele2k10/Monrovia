<?php
	require_once('class_qa_question.php');
	require_once('sphinxapi.php');

	class search_qa_question {
		function search_qa_question($category_id = null,$query = null,$offset_index = null,$results_per_page = null,$include_inactive = null){
		
			if(is_null($category_id)) $category_id = '';
			if(is_null($query)) $query = '';
			if(is_null($offset_index)) $offset_index = 0;
			if(is_null($results_per_page)) $results_per_page = 50;
			if(is_null($include_inactive)) $include_inactive = false;
			
			$this->criteria = array(
				'category_id'		=>$category_id,
				'query'				=>$query,
				'offset_index'		=>$offset_index,
				'results_per_page'	=>$results_per_page,
				'include_inactive'	=>$include_inactive
			);
		}
		
		function search(){
		
			$ret = array('criteria'=>$this->criteria);
		
			$sql_where_conditions = array();

			$include_inactive = (isset($this->criteria['include_inactive'])&&$this->criteria['include_inactive']);

			if(!$include_inactive) $sql_where_conditions[] = 'qa_questions.is_active = 1';
	
			if(!isset($this->criteria['offset_index'])||$this->criteria['offset_index']=='') $this->criteria['offset_index'] = 0;

			if(!isset($this->criteria['results_per_page'])||$this->criteria['results_per_page']==''||$this->criteria['results_per_page']>$GLOBALS['view_all_max']) $this->criteria['results_per_page'] = $GLOBALS['view_all_max'];

			$sql = 'SELECT qa_questions.id, qa_questions.is_active, qa_questions.category_id, qa_questions.author_user_id, qa_questions.url_key, qa_questions.title, qa_questions.details, qa_questions.date_created, qa_questions.num_answers, monrovia_users.user_name FROM qa_questions LEFT JOIN monrovia_users ON monrovia_users.id = qa_questions.author_user_id'; // LEFT JOIN GETS QUESTIONS, REGARDLESS OF WHETHER THE USER RECORD COULD BE FOUND OR NOT
	
			if(isset($this->criteria['query'])&&$this->criteria['query']!=''){
				$griffin = new SphinxClient();
				
				$griffin->SetLimits($this->criteria['offset_index'],$this->criteria['results_per_page']); // PAGINATION
				$griffin->SetFieldWeights ( array ( "title"=>100, "details"=>50 ) );
				$griffin->SetSortMode ( SPH_SORT_EXTENDED,'@weight DESC, num_answers DESC, title ASC');
		
				// FULL-TEXT SEARCH USING SPHINX
				$search_index_name = 'qa_questions';
				
				if(isset($this->criteria['category_id'])&&intval($this->criteria['category_id'])>0) $griffin->SetFilter('category_id',array(intval($this->criteria['category_id'])));
				
				if(!$include_inactive) $griffin->SetFilter('is_active',array(1));

				$result = $griffin->Query($this->criteria['query'],$search_index_name);
				
				$ids = '0';

				if(isset($result['matches'])&&count($result['matches'])){
					$result_weights = array();
					$ids = array_keys($result['matches']);
					for($i=0;$i<count($ids);$i++){
						$result_weights[] = $result['matches'][$ids[$i]]['weight'];
					}
					$ids = implode(',',$ids);
				}

				// AT THIS POINT, WE SHOULD HAVE A LIST OF IDs
				$sql .= ' WHERE qa_questions.id IN ('.$ids.') ORDER BY FIELD(qa_questions.id,'.$ids.')';
				
				$ret['total_results'] = intval($result['total_found']);				
			}else{
				// NORMAL SEARCH USING SQL			
				if(isset($this->criteria['category_id'])&&$this->criteria['category_id']!='') $sql_where_conditions[] = 'category_id = "' . $this->criteria['category_id'] . '"';
				
				$sql_count = 'SELECT COUNT(*) AS total FROM qa_questions';
				
				if(count($sql_where_conditions)) $sql .= ' WHERE ' . implode(' AND ',$sql_where_conditions);						
				if(count($sql_where_conditions)) $sql_count .= ' WHERE ' . implode(' AND ',$sql_where_conditions);

				// FIGURE OUT TOTAL RESULTS				
				$query_result = sql_query($sql_count);
				$ret['total_results'] = mysql_result($query_result,0,'total');
								
				$sql .= ' ORDER BY quality_rating_overall DESC, num_answers DESC, qa_questions.title ASC LIMIT ' . $this->criteria['offset_index'] . ', ' . $this->criteria['results_per_page'];
			}
			
			$query_result = sql_query($sql);
			$num_rows = @mysql_numrows($query_result);
			
			$results = array();
			$titles = array();
			$details = array();
			
			for($i=0;$i<$num_rows;$i++){
				$temp = new qa_question();
				$temp->info['id'] = mysql_result($query_result,$i,'id');
				$temp->info['is_active'] = mysql_result($query_result,$i,'is_active');
				$temp->info['category_id'] = mysql_result($query_result,$i,'category_id');
				$temp->info['author_user_id'] = mysql_result($query_result,$i,'author_user_id');
				$temp->info['author_user_name'] = mysql_result($query_result,$i,'user_name'); // NOT ACTUAL DATABASE FIELD
				$temp->info['url_key'] = mysql_result($query_result,$i,'url_key');
				$temp->info['title'] = mysql_result($query_result,$i,'title');
				$temp->info['details'] = mysql_result($query_result,$i,'details');
				$temp->info['date_created'] = mysql_result($query_result,$i,'date_created');
				$temp->info['num_answers'] = mysql_result($query_result,$i,'num_answers');
				$temp->populate_dumb_values();
				
				if($temp->info['category_id']!='') $temp->get_full_path();
				
				$results[] = array(
					'user_name'=>mysql_result($query_result,$i,'user_name'),
					'qa_question'=>$temp
				);
				
				$titles[] = $temp->info['title'];
				$details[] = $temp->info['details'];
			}
			
			// BUILD SEARCH EXCERPTS
			if(isset($this->criteria['query'])&&$this->criteria['query']!=''){
				$titles = build_search_excerpts($titles,$search_index_name,$this->criteria['query']);
				$details = build_search_excerpts($details,$search_index_name,$this->criteria['query']);
				for($i=0;$i<$num_rows;$i++){
					$results[$i]['search_excerpts'] = array(
						'title'=>unescape_special_characters($titles[$i]),
						'details'=>unescape_special_characters($details[$i]),
					);
					$results[$i]['weight'] = $result_weights[$i];
				}
			}
						
			$ret['results'] = $results;

			return $ret;
			
		}
	}

?>