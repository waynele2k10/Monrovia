<?php
	require_once('class_record.php');
	require_once('qa_general.php');

	class qa_answer extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM qa_answers TABLE, EXCEPT FOR id
		var $table_fields = 'is_active,question_id,author_user_id,title,details,date_created,quality_rating'; // MAKE SURE THIS VALUE DOES NOT BEGIN OR END WITH COMMAS

		function qa_answer($record_id = ''){
			$this->table_name = 'qa_answers';
			$this->full_path = '';
			
			if($record_id!='') $this->load($record_id);
		}

		function load($record_id){
			parent::load($record_id);
			//$this->populate_dumb_values();
		}
		
		function save(){
		
			parent::save(array(
				'allow_line_breaks'=>array(
					'details'
				)
			));

			$this->update_quality_rating();
		}
		
		function delete(){
			$this->delete_associated_data();
		
			parent::delete();
			
			// UPDATE QUESTION AS WELL
			$question = new qa_question($this->info['question_id']);
			if(isset($question->info['id'])&&$question->info['id']!='') $question->save();						
		}

		function delete_associated_data(){
			sql_query('DELETE FROM qa_flags WHERE item_type="answer" AND item_id="' . $this->info['id'] . '"');
			sql_query('DELETE FROM qa_scrapbooked_items WHERE item_type="answer" AND item_id="' . $this->info['id'] . '"');
			sql_query('DELETE FROM qa_votes WHERE item_type="answer" AND item_id="' . $this->info['id'] . '"');
		}

		function set_flagged_status($user_id,$reason_id){
			if(isset($this->info['id'])&&$this->info['id']!=''){
				return set_flagged_status('answer',$this->info['id'],$user_id,$reason_id);
			}else{
				return false;
			}
		}
		
		function get_scrapbook_status($user_id){
			if(isset($this->info['id'])&&$this->info['id']!=''){
				return get_scrapbook_status('answer',$this->info['id'],$user_id);
			}else{
				return false;
			}
		}
		
		function set_vote($user_id,$direction){
			if(isset($this->info['id'])&&$this->info['id']!=''){
				$ret = set_vote('answer',$this->info['id'],$user_id,$direction);				
				return $ret;
			}else{
				return false;
			}
		}
		
		function get_votes($user_id = 0){
			$this->votes = get_votes('answer',$this->info['id'],$user_id);
			return $this->votes;
		}
	
		function flagged_by_user($user_id = 0){
			$sql = 'SELECT COUNT(*) AS flagged FROM qa_flags WHERE item_type="answer" AND item_id="' . $this->info['id'] . '" AND flagger_user_id="' . $user_id . '"';
			$result = sql_query($sql);
			return mysql_result($result,0,'flagged')>0;
		}

		function get_question(){
			require_once('class_qa_question.php');
			$this->question = new qa_question($this->info['question_id']);
			$this->full_path = $this->question->full_path . '#answer_' . $this->info['id'];
			return $this->question;
		}

		function update_quality_rating(){
			// DETERMINE RATING BASED ON VOTES
			$query_result = sql_query('SELECT SUM(value) AS rating FROM qa_votes WHERE item_type="answer" AND item_id="'.$this->info['id'].'"');
			$quality_rating = mysql_result($query_result,0,'rating');
			
			if(is_null($quality_rating)||$quality_rating=='') $quality_rating = 0;
			
			// IF ANSWERED BY AN OFFICIAL, WE'LL ADD 100 TO THE QUALITY RATING
			$author = new monrovia_user($this->info['author_user_id']);
			if(isset($author->info['is_official_qa_rep'])&&$author->info['is_official_qa_rep']=='1') $quality_rating += 100;
			
			sql_query('UPDATE qa_answers SET quality_rating="'.$quality_rating.'" WHERE id="'.$this->info['id'].'"');
			
			// UPDATE QUESTION AS WELL
			$question = $this->get_question();
			if(isset($question->info['id'])&&$question->info['id']!='') $question->save();			
		}
		
		function populate_dumb_values(){
			if(isset($this->info['id'])&&is_numeric($this->info['id'])){
				//$this->get_subcategories();

			}
		}
		
		function get_basic_author_info(){
			return get_basic_author_info($this->info['author_user_id']);
		}

	}
?>
