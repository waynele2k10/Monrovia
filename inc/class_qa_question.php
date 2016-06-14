<?php
	require_once('class_record.php');
	require_once('qa_general.php');
	require_once('class_qa_answer.php');
	class qa_question extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM qa_questions TABLE, EXCEPT FOR id
		var $table_fields = 'is_active,category_id,author_user_id,url_key,parent_path,title,details,date_created,date_latest_activity,num_answers'; // MAKE SURE THIS VALUE DOES NOT BEGIN OR END WITH COMMAS

		function qa_question($record_id = ''){
			$this->table_name = 'qa_questions';
			
			$this->full_path = '';
			
			if($record_id!='') $this->load($record_id);
		}

		function load($record_id){
			parent::load($record_id);
			//$this->populate_dumb_values();
						
			$this->get_full_path();
		}
		
		function save(){		
			$category = new qa_category($this->info['category_id']);
			$parent_path = trim($category->info['parent_path'] . '/' . $category->info['url_key'],'/');

			$is_new_record = (!isset($this->info['id'])||$this->info['id']=='');
			parent::save(array(
				'allow_line_breaks'=>array(
					'details'
				)
			));
			
			if($is_new_record){
				sql_query('UPDATE monrovia_users SET date_latest_qa_activity = NOW() WHERE id="' . $this->info['author_user_id'] .'";');
				$this->info['date_latest_activity'] = date('Y-m-d H:i:s');
			}else{
				$this->get_and_update_num_answers();
				$this->update_quality_rating();
			}
			$this->info['url_key'] = generate_url_friendly_string($this->info['id'] . ' ' . $this->info['title']);
			$this->info['parent_path'] = $parent_path;

			parent::save(array(
				'allow_line_breaks'=>array(
					'details'
				)
			));
		}
		
		function delete(){
			// DELETE ANSWERS
			$this->get_answers();
			for($i=0;$i<count($this->answers);$i++){
				$this->answers[$i]->delete();
			}
			
			// DELETE ASSOCIATED DATA
			$this->delete_associated_data();
			
			// DELETE RECORD
			parent::delete();
		}
		
		function get_parent_path(){
			$category = new qa_category($this->info['category_id']);
			$ancestor_info = $category->get_ancestor_info();
			$ret = array();
			for($i=0;$i<count($ancestor_info);$i++){
				$ret[] = $ancestor_info[$i]['url_key'];
			}
			$this->parent_path = implode('/',$ret);
			return $this->parent_path;
		}

		function get_full_path(){
			if(isset($this->info['category_id'])){
				$category = new qa_category($this->info['category_id']);
				$this->full_path = $category->full_path . $this->info['url_key'];
				return $this->full_path;
			}
		}

		
		function delete_associated_data(){
			sql_query('DELETE FROM qa_flags WHERE item_type="question" AND item_id="' . $this->info['id'] . '"');
			sql_query('DELETE FROM qa_scrapbooked_items WHERE item_type="question" AND item_id="' . $this->info['id'] . '"');
			sql_query('DELETE FROM qa_subscriptions WHERE item_type="question" AND item_id="' . $this->info['id'] . '"');
			sql_query('DELETE FROM qa_votes WHERE item_type="question" AND item_id="' . $this->info['id'] . '"');
		}
		
		function set_flagged_status($user_id,$reason_id){
			if(isset($this->info['id'])&&$this->info['id']!=''){
				return set_flagged_status('question',$this->info['id'],$user_id,$reason_id);
			}else{
				return false;
			}
		}
		
		function set_subscription_status($user_id,$subscribe = true){
			if(isset($this->info['id'])&&$this->info['id']!=''){
				return set_subscription_status('question',$this->info['id'],$user_id,$subscribe);
			}else{
				return false;
			}
		}
		
		function get_subscription_status($user_id){
			if(isset($this->info['id'])&&$this->info['id']!=''){
				return get_subscription_status('question',$this->info['id'],$user_id);
			}else{
				return false;
			}
		}
		
		function get_scrapbook_status($user_id){
			if(isset($this->info['id'])&&$this->info['id']!=''){
				return get_scrapbook_status('question',$this->info['id'],$user_id);
			}else{
				return false;
			}
		}

		
		function get_ancestor_info($max_length = null){
			$title = $this->info['title'];
			if(!is_null($max_length)) $title = truncate($title,$max_length,false,true,false);
		
			$category = new qa_category($this->info['category_id']);
			return array_merge($category->get_ancestor_info(),array(
				array(
					'name'=>$title,
					'full_path'=>$this->get_full_path()
				)
			));
		}
		
		function set_vote($user_id,$direction){
			if(isset($this->info['id'])&&$this->info['id']!=''){
				$ret = set_vote('question',$this->info['id'],$user_id,$direction);
				return $ret;			
			}else{
				return false;
			}
		}
		
		function get_votes($user_id = 0){
			$this->votes = get_votes('question',$this->info['id'],$user_id);
			return $this->votes;
		}

		function flagged_by_user($user_id = 0){
			$sql = 'SELECT COUNT(*) AS flagged FROM qa_flags WHERE item_type="question" AND item_id="' . $this->info['id'] . '" AND flagger_user_id="' . $user_id . '"';
			$result = sql_query($sql);
			return mysql_result($result,0,'flagged')>0;
		}
		
		function get_and_update_num_answers(){
			$sql = 'SELECT COUNT(*) AS total FROM qa_answers WHERE is_active=1 AND question_id = "'.$this->info['id'].'"';
			$result = sql_query($sql);
			$this->info['num_answers'] = mysql_result($result,0,'total');
			return $this->info['num_answers'];
		}
		
		function update_quality_rating(){
			/*
				quality_rating_overall = RATING (BASED ON VOTES) OF QUESTION + OVERALL RATING OF ANSWERS
			*/
			
			// DETERMINE RATING BASED ON VOTES
			$query_result = sql_query('SELECT SUM(value) AS rating FROM qa_votes WHERE item_type="question" AND item_id="'.$this->info['id'].'"');
			$quality_rating = mysql_result($query_result,0,'rating');

			if(is_null($quality_rating)||$quality_rating=='') $quality_rating = 0;

			$query_result = sql_query('SELECT SUM(quality_rating) AS rating FROM qa_answers WHERE question_id="'.$this->info['id'].'" AND is_active=1');
			$quality_rating_answers = mysql_result($query_result,0,'rating');
			if(is_null($quality_rating_answers)||$quality_rating_answers=='') $quality_rating_answers = 0;
			
			sql_query('UPDATE qa_questions SET quality_rating="'.$quality_rating.'", quality_rating_overall = "' . ($quality_rating_answers + $quality_rating) . '" WHERE id="'.$this->info['id'].'"');
			
		}
		
		function populate_dumb_values(){
			if(isset($this->info['id'])&&is_numeric($this->info['id'])){
				//$this->get_subcategories();

			}
		}
		
		function get_answers($include_inactive = false){

			if(isset($this->answers)&&count($this->answers)) exit; // BAIL OUT IF ALREADY HAVE ANSWERS

			$temp = new qa_answer();
			
			// BEFORE USING COLUMN NAMES IN A SQL STATEMENT, PREPEND "qa_answers." TO EACH
			$table_fields_csv = 'qa_answers.' . str_replace(',',',qa_answers.',$temp->table_fields);
		
			$this->answers = array();
		
			if(isset($this->info['id'])&&is_numeric($this->info['id'])){
			
				if($include_inactive){
					$query = "SELECT qa_answers.id, ". $table_fields_csv .", monrovia_users.user_name AS author_user_name, monrovia_users.avatar, monrovia_users.is_official_qa_rep FROM qa_answers LEFT JOIN monrovia_users ON monrovia_users.id = qa_answers.author_user_id WHERE qa_answers.question_id = ".$this->info['id']." ORDER BY qa_answers.quality_rating DESC, qa_answers.date_created DESC";
				}else{
					$query = "SELECT qa_answers.id, ". $table_fields_csv .", monrovia_users.user_name AS author_user_name, monrovia_users.avatar, monrovia_users.is_official_qa_rep FROM qa_answers LEFT JOIN monrovia_users ON monrovia_users.id = qa_answers.author_user_id WHERE qa_answers.is_active=1 AND qa_answers.question_id = ".$this->info['id']." ORDER BY qa_answers.quality_rating DESC, qa_answers.date_created DESC";
				}
				
				$result = sql_query($query);
				$num_rows = @mysql_numrows($result);
				
				$table_fields = explode(',',$temp->table_fields);
				
				for($i=0;$i<$num_rows;$i++){
					// POPULATE qa_answer OBJECT
					$temp = new qa_answer();
					$temp->info['id'] = mysql_result($result,$i,'id');
					for($n=0;$n<count($table_fields);$n++){
						$temp->info[trim($table_fields[$n])] = mysql_result($result,$i,trim($table_fields[$n]));
					}
					$temp->info['author_user_name'] = mysql_result($result,$i,'author_user_name');
					$temp->info['author_avatar'] = mysql_result($result,$i,'avatar');
					$temp->info['author_is_official_qa_rep'] = mysql_result($result,$i,'is_official_qa_rep');
					
					if($temp->info['author_avatar']=='') $temp->info['author_avatar'] = $temp->info['author_is_official_qa_rep']=='1'?'avatar-expert.png':'avatar-generic.png';

					$temp->populate_dumb_values();
					$this->answers[] = $temp;
				}
			}
			return $this->answers;
		}
		
		function get_activity($num_days = 30){
			$query = "SELECT qa_answers.id, qa_answers.author_user_id, qa_answers.title, qa_answers.details, qa_answers.date_created, monrovia_users.user_name AS author_user_name FROM qa_answers LEFT JOIN monrovia_users ON monrovia_users.id = qa_answers.author_user_id WHERE qa_answers.is_active=1 AND qa_answers.question_id = ".$this->info['id']." AND qa_answers.date_created > TIMESTAMP(DATE_SUB(NOW(), INTERVAL $num_days day)) ORDER BY qa_answers.date_created DESC";
			
			$result = sql_query($query);
			$num_rows = @mysql_numrows($result);
			$ret = array();

			for($i=0;$i<$num_rows;$i++){
				$temp = new qa_answer();
				$temp->info['id'] = mysql_result($result,$i,'id');
				$temp->info['date_created'] = mysql_result($result,$i,'date_created');
				$temp->info['author_user_id'] = mysql_result($result,$i,'author_user_id');
				$temp->info['title'] = mysql_result($result,$i,'title');
				$temp->info['details'] = mysql_result($result,$i,'details');
				$temp->info['author_user_name'] = mysql_result($result,$i,'author_user_name');

				$temp->populate_dumb_values();
				$ret[] = array(
					'type'=>'answer',
					'date_created'=>strtotime(mysql_result($result,$i,'date_created')),
					'item'=>$temp
				);
			}

			return $ret;
			
		}
		
		function get_basic_author_info(){
			return get_basic_author_info($this->info['author_user_id']);
		}
		
		function post_answer($user_id,$title,$details){
			if($details==''||$user_id==0) return false;

			$answer = new qa_answer();
			$answer->info['is_active'] = '1';
			$answer->info['question_id'] = $this->info['id'];
			$answer->info['author_user_id'] = $user_id;
			$answer->info['title'] = $title;
			$answer->info['details'] = $details;
			$answer->info['date_created'] = date('Y-m-d H:i:s');
			$answer->save();
			
			sql_query('UPDATE monrovia_users SET date_latest_qa_activity = NOW() WHERE id="' . $user_id .'";');
			
			return $answer->info['id'];
		}
		

		
	}
?>
