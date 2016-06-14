<?php
	require_once('class_record.php');
	require_once('class_campaign_monitor.php');
	
	//require_once('blog_proxy.php');

	/*
		PERMISSIONS CHECKLIST
			cmgt		CMS access
			hres		Human Resources (job listings, resumes)
			pldb		Plant Database (plant, collections, attributes, etc.)
			blog		Blog Editing
			html		Website Content Editing
			caln		Event Calendar
			user		Manage Users/Permissions
			pimg		High-res Plant Image Downloading
			prof		Designer Profiles
			pdfs		Catalogs
			qamd		Q&A Moderation
	*/

	// CONSTANTS

	class monrovia_user extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM plant TABLE, EXCEPT FOR id
		var $table_fields = 'is_active,user_name,permissions,is_official_qa_rep,date_created,date_last_login,date_latest_qa_activity,blog_user_id,avatar,password,email_address,first_name,last_name,address,city,state,zip,website_url,website_name,newsletter,newsletter_versions,newsletter_interests,legacy_id,favorite_garden_center,campaign_participation'; // NOTE - WHEN ADDING NEW FIELDS, ADD THEM TO community/register-user-agreement.php ALSO
		function monrovia_user($record_id = ''){
			$this->table_name = 'monrovia_users';
			
			$this->qa_subscriptions = array(
				'questions'=>array(),
				'users'=>array()
			);
			
			if(is_numeric($record_id)){
				$this->info['id'] = $record_id;
				$this->load_data();
			}else{
				$this->wish_lists = array();
			}
		}
		function load($record_id){
			$ret = parent::load($record_id);
			if(isset($this->info['id'])&&$this->info['id']!=''){
				$this->choose_default_avatar();
				$this->info['password'] = $this->password_decrypt($this->info['password']);			
			}
			return $ret;
		}
		function delete(){
			if(isset($this->info['id'])&&$this->info['id']!=''){
				$this->delete_associated_data();
				parent::delete();
			}
		}
		function delete_associated_data(){
			sql_query('DELETE FROM qa_subscriptions WHERE item_type="user" AND item_id="' . $this->info['id'] . '"');
			sql_query('DELETE FROM qa_subscriptions WHERE subscriber_user_id="' . $this->info['id'] . '"');
			sql_query('DELETE FROM qa_scrapbooked_items WHERE user_id="' . $this->info['id'] . '"');
			sql_query('DELETE FROM qa_flags WHERE flagger_user_id="' . $this->info['id'] . '"');
			sql_query('DELETE FROM qa_votes WHERE voter_user_id="' . $this->info['id'] . '"');

			// NOTE: THIS SCENARIO IS NOT ACCOUNTED FOR:
			// - problem user flags and/or votes on questions and/or answers.
			// - problem user account is deleted by admin.
			//
			// The quality ratings of the questions and answers flagged by or voted on by the user are not updated. If another user votes on one of those questions or answers, the up-vote or down-vote count may appear to not update to the user. This is a very edge case, as users are almost never deleted, so we won't worry about it.

			// QUESTIONS
			$questions = $this->qa_get_my_questions(true);
			for($i=0;$i<count($questions);$i++){
				$questions[$i]->delete();
			}
			
			// ANSWERS
			$answers = $this->qa_get_my_answers(true);
			for($i=0;$i<count($answers);$i++){
				$answers[$i]->delete();
			}

			sql_query("UPDATE editable_modules SET locked_by='' WHERE locked_by='" . $this->info['user_name'] . "'");

			// WISH LISTS
			if(!isset($this->wish_lists)||!count($this->wish_lists)) $this->load_data();
			for($i=0;$i<count($this->wish_lists);$i++){
				$this->wish_lists[$i]->delete();
			}
			
		}
		function choose_default_avatar(){
			if(!isset($this->info['avatar'])||$this->info['avatar']=='') $this->info['avatar'] = 'avatar-generic.png';
			if(isset($this->info['is_official_qa_rep'])&&$this->info['is_official_qa_rep']=='1') $this->info['avatar'] = 'avatar-expert.png';
		}
		function load_data(){
			if(is_numeric($this->info['id'])){
				$this->load($this->info['id']);

				// FOR STRING COMPARISON PURPOSES, ENCASE IN COMMAS
				if(isset($this->info['permissions'])){
					$this->info['permissions'] = ',' . $this->info['permissions'] . ',';
				}else{
					$this->info['permissions'] = '';
				}

				// IF CMS USER, USE DIFFERENT DB USER FOR INCREASED PERMISSIONS
				if(contains($this->info['permissions'],',cmgt,')){
					sql_disconnect();
					sql_set_user('med');
					sql_connect();
					page_log('SQL reset');
				}

				// LOAD WISH LISTS
				$this->wish_lists = array();
				require_once('class_wish_list.php');
				$result = sql_query("SELECT id FROM wish_lists WHERE user_id=".$this->info['id']);
				$num_rows = intval(@mysql_numrows($result));
				if($num_rows>0){
					for($i=0;$i<$num_rows;$i++){
						$this->wish_lists[] = new wish_list(mysql_result($result,$i,"id"));
					}
				}else{
					// NO WISH LISTS FOR THIS USER; ADD ONE
					$result = sql_query("INSERT INTO wish_lists (user_id) VALUES(".$this->info['id'].")");
					if($result==1){
						// ADD WISH LIST WORKED
						$this->wish_lists[] = new wish_list(mysql_insert_id());
					}else{
						// ADD WISH LIST FAILED
					}
				}
			}
		}
		function log_in($user_name,$user_password,$remember_me=false){
			
			global $cm;
			
			$ret = false;
			$user_password = $this->password_encrypt($user_password);
			if(is_suspicious($user_name)){
				@header('location:/');
				exit;
			}

			$result = sql_query("SELECT id FROM monrovia_users WHERE LOWER(user_name)='". strtolower(sql_sanitize($user_name)) . "' AND password='$user_password' AND is_active=1");
			//die("SELECT id FROM monrovia_users WHERE LOWER(user_name)='". strtolower(sql_sanitize($user_name)) . "' AND password='$user_password' AND is_active=1");
			if(intval(@mysql_numrows($result))==1){
				$this->info['id'] = mysql_result($result,0,'id');

				$this->load_data();

				//if(blog_proxy_log_in($user_name,$user_password)){
					$_SESSION['monrovia_user_id'] = $this->info['id'];
					// TRACK LAST LOGIN DATE
					$this->info['date_last_login'] = current_mysql_date();
					sql_query("UPDATE monrovia_users SET date_last_login = '".$this->info['date_last_login']."' WHERE id=".$this->info['id']);
						
									
					//update newsletter status
					sql_query("UPDATE monrovia_users SET newsletter = '".$cm->get_subscription_status($this->info['email_address'])."' WHERE id=".$this->info['id']);
										
					//$this->save();
					$ret = true;
					if($remember_me){
						setcookie("user_name",$user_name,time()+60*60*24*30,'/'); // REMEMBER FOR ONE MONTH
					}else{
						setcookie('user_name','',time()-3600,'/'); // CLEAR COOKIE
					}
					if($_COOKIE['zip']=='') setcookie('zip',$this->info['zip'],0,'/');
				//}
			}
			return $ret;
		}

		function save(){
			if(!isset($this->info['id'])||$this->info['id']==''){
				if(!isset($this->info['user_name'])||$this->info['user_name']=='') return false;
				if((!isset($this->info['legacy_id'])||$this->info['legacy_id']=='')&&(!isset($this->info['email_address'])||$this->info['email_address']==''||!isset($this->info['zip'])||$this->info['zip']=='')) return false;
				if(!isset($this->info['date_created'])||$this->info['date_created']=='') $this->info['date_created'] = current_mysql_date();
			}

			// FORMAT CANADIAN ZIP CODES
			if(strlen($this->info['zip'])>5){
				// CANADIAN ZIP CODE; FORMAT
				$this->info['zip'] = strtoupper(parse_alphanumeric($this->info['zip']));
				$this->info['zip'] = substr($this->info['zip'],0,3) . ' ' . substr($this->info['zip'],3);
			}

			// RESET ZIP CODE
			setcookie('zip',$this->info['zip'],0,'/');

			// TRIM ENCASING COMMAS
			$this->info['permissions'] = trim($this->info['permissions'],',');

			// PASSWORD ENCRYPTION
			$password_unencrypted = $this->info['password'];
			$this->info['password'] = $this->password_encrypt($password_unencrypted);

			$ret = parent::save();

			$this->info['password'] = $password_unencrypted;

			// FOR STRING COMPARISON PURPOSES, ENCASE IN COMMAS
			if($this->info['permissions']!='') $this->info['permissions'] = ',' . $this->info['permissions'] . ',';
			return $ret;
		}
		function log_out(){
			session_destroy();
			//blog_proxy_log_out();
		}
		function is_logged_in(){
			return ($this->info['id']!='');
		}
		function get_wish_list_item_by_plant_id($plant_id){
			foreach($this->wish_lists as &$wish_list){
				$item = $wish_list->get_item($plant_id);
				if($item!='') return $item;
			}
			return '';
		}
		/*
		function get_wish_list_item_by_id($wish_list_id){
			foreach($this->wish_lists as &$wish_list){
				if($wish_list->info['id']==$wish_list_id) return $wish_list->info['id'];
			}
			return '';
		}
		*/
		function get_wish_list_by_id($wish_list_id){
			foreach($this->wish_lists as &$wish_list){
				if($wish_list->info['id']==$wish_list_id) return $wish_list;
			}
			return '';
		}
		function permission_requirement($permission){
			if(!contains($this->info['permissions'],','.$permission.',')){
				// ACCESS DENIED
				@header('location:/');
				die('<script>window.location=\'http://www.monrovia.com\';</script>');
			}
		}
		function validate(){
			// CHECKS BASIC INFORMATION FOR INVALID INFORMATION, RETURNS true OR ERROR CODE.
			// USED DURING REGISTRATION PROCESS.

			// CHECK FOR MISSING VALUES
			if(trim($this->info['user_name']==''))					return array('code'=>'ERROR_VALUE_NOT_SPECIFIED','status'=>'invalid','field'=>'user_name');
			if(trim($this->info['password']==''))					return array('code'=>'ERROR_VALUE_NOT_SPECIFIED','status'=>'invalid','field'=>'password');
			if(trim($this->info['first_name']==''))					return array('code'=>'ERROR_VALUE_NOT_SPECIFIED','status'=>'invalid','field'=>'first_name');
			if(trim($this->info['zip']==''))						return array('code'=>'ERROR_VALUE_NOT_SPECIFIED','status'=>'invalid','field'=>'zip');
			if(trim($this->info['email_address']==''))				return array('code'=>'ERROR_VALUE_NOT_SPECIFIED','status'=>'invalid','field'=>'email_address');

			if(isset($this->info['website_url'])&&$this->info['website_url']!=''&&(!isset($this->info['website_name'])||$this->info['website_name']=='')) return array('code'=>'ERROR_VALUE_NOT_SPECIFIED','status'=>'invalid','field'=>'website_name');
			if(isset($this->info['website_name'])&&$this->info['website_name']!=''&&(!isset($this->info['website_url'])||$this->info['website_url']=='')) return array('code'=>'ERROR_VALUE_NOT_SPECIFIED','status'=>'invalid','field'=>'website_url');

			// CHECK FOR INVALID VALUE LENGTHS
			if(!within(strlen($this->info['user_name']),4,40))		return array('code'=>'ERROR_VALUE_INVALID_LENGTH','status'=>'invalid','field'=>'user_name');
			if(!within(strlen($this->info['password']),6,40))		return array('code'=>'ERROR_VALUE_INVALID_LENGTH','status'=>'invalid','field'=>'password');
			if(strlen($this->info['first_name'])>40)				return array('code'=>'ERROR_VALUE_INVALID_LENGTH','status'=>'invalid','field'=>'first_name');
			if(strlen($this->info['last_name'])>40)					return array('code'=>'ERROR_VALUE_INVALID_LENGTH','status'=>'invalid','field'=>'last_name');
			if(strlen($this->info['zip'])<5||strlen($this->info['zip'])>7)						return array('code'=>'ERROR_VALUE_INVALID_LENGTH','status'=>'invalid','field'=>'zip');
			if(strlen($this->info['email_address'])>255)					return array('code'=>'ERROR_VALUE_INVALID_LENGTH','status'=>'invalid','field'=>'email_address');
			if(isset($this->info['website_url'])&&strlen($this->info['website_url'])>255)					return array('code'=>'ERROR_VALUE_INVALID_LENGTH','status'=>'invalid','field'=>'website_url');
			if(isset($this->info['website_name'])&&strlen($this->info['website_name'])>40)					return array('code'=>'ERROR_VALUE_INVALID_LENGTH','status'=>'invalid','field'=>'website_name');

			// CHECK FOR INCORRECTLY FORMATTED VALUES
			if($this->info['user_name']!=parse_alphanumeric($this->info['user_name'])) return array('code'=>'ERROR_VALUE_INVALID_FORMAT','status'=>'invalid','field'=>'user_name');
			if(!valid_email($this->info['email_address']))			return array('code'=>'ERROR_VALUE_INVALID_FORMAT','status'=>'invalid','field'=>'email_address');
			if(!is_valid_us_canadian_zip($this->info['zip']))						return array('code'=>'ERROR_VALUE_INVALID_FORMAT','status'=>'invalid','field'=>'zip');
			if(contains($this->info['password'],' '))				return array('code'=>'ERROR_VALUE_INVALID_FORMAT','status'=>'invalid','field'=>'password');
			
			if(isset($this->info['website_url'])&&$this->info['website_url']!=''&&!valid_url($this->info['website_url'])) return array('code'=>'ERROR_VALUE_INVALID_FORMAT','status'=>'invalid','field'=>'website_url');
			
			// AVATAR
			if(isset($this->info['avatar'])&&array_search($this->info['avatar'],array('avatar-male1.png','avatar-male2.png','avatar-male3.png','avatar-male4.png','avatar-female1.png','avatar-female2.png','avatar-female3.png','avatar-female4.png','avatar-object1.png','avatar-object2.png','avatar-object3.png','avatar-object4.png','avatar-generic.png','avatar-expert.png'))===false) return array('code'=>'ERROR_VALUE_INVALID_OPTION','status'=>'invalid','field'=>'avatar');
			
			if((!isset($this->info['is_official_qa_rep'])||$this->info['is_official_qa_rep']!='1')&&isset($this->info['avatar'])&&$this->info['avatar']=='avatar-expert.png') return array('code'=>'ERROR_VALUE_INVALID_OPTION','status'=>'invalid','field'=>'avatar');

			if(isset($this->info['is_official_qa_rep'])&&$this->info['is_official_qa_rep']=='1'&&isset($this->info['avatar'])&&$this->info['avatar']!='avatar-expert.png') return array('code'=>'ERROR_VALUE_INVALID_OPTION','status'=>'invalid','field'=>'avatar');

			/*
			// SQL INJECTION-SAFE
			$data_check = $this->info;
			$data_check['newsletter_versions'] = ids_sanitize($data_check['newsletter_versions']);
			$data_check['newsletter_interests'] = ids_sanitize($data_check['newsletter_interests']);
			$data_check['favorite_garden_center'] = ids_sanitize($data_check['favorite_garden_center']);
			if(is_suspicious($data_check)){
				return array('code'=>'ERROR_VALUE_SUSPICIOUS','status'=>'invalid');
			}
			*/

			// MAKE SURE EMAIL ADDRESS/USER NAME ISN'T ALREADY REGISTERED
			if(!isset($this->info['id'])||$this->info['id']==''){
				$sql = "SELECT COUNT(*) AS total FROM monrovia_users WHERE is_active=1 AND ( LOWER(user_name)='".strtolower(sql_sanitize($this->info['user_name']))."' OR LOWER(email_address)='".strtolower(sql_sanitize($this->info['email_address']))."')";
			}else{
				$sql = "SELECT COUNT(*) AS total FROM monrovia_users WHERE is_active=1 AND ( LOWER(email_address)='".strtolower(sql_sanitize($this->info['email_address']))."' AND id <> " . $this->info['id'] . ")";
			}

			$result = sql_query($sql);
			$num_records = @mysql_result($result,0,"total");
			if($num_records>0) return array('code'=>'ERROR_USER_NAME_OR_EMAIL_EXISTS','status'=>'invalid');

			return array('status'=>'valid');
		}
		function password_encrypt($password){
			// LOW-SECURITY PASSWORD ENCRYPTION--NEEDED IF PASSWORD RETRIEVAL IS DESIRED. ASCII ONLY.
			$password = str_rot13(base64_encode($password));
			return $password;
		}
		function password_decrypt($password){
			$password = base64_decode(str_rot13($password));
			return $password;
		}

		function has_designer_profile()
		{
			return (bool) mysql_num_rows( sql_query("SELECT id FROM monrovia_profiles WHERE user_id='".sql_sanitize($this->info['id'])."'") );
		}
		
		/* BEGIN Q&A */

		function qa_get_activity($num_days = 30){
			$items = array();

			$reference_date = strtotime('-' . $num_days . ' day',time());
			
			if(!isset($this->info['date_latest_qa_activity'])||$this->info['date_latest_qa_activity']==''||strtotime($this->info['date_latest_qa_activity'])>=$reference_date){
			
				$cached_questions = array();
				$cached_answers = array();

				// ANSWERS
				$query = 'SELECT qa_answers.id, qa_answers.is_active, qa_answers.question_id, qa_answers.title, qa_answers.is_active, qa_answers.date_created, qa_questions.category_id, qa_questions.url_key FROM qa_answers INNER JOIN qa_questions ON qa_questions.id = qa_answers.question_id WHERE qa_answers.is_active=1 AND qa_questions.is_active=1 AND qa_answers.author_user_id="'.$this->info['id'].'" AND qa_answers.date_created >= "' . date('Y-m-d',$reference_date) . '"';

				$query_result = sql_query($query);
				$num_rows = @mysql_numrows($query_result);

				for($i=0;$i<$num_rows;$i++){
					$results_answer = get_answer_once($cached_answers,mysql_result($query_result,$i,'id'));
					$cached_answers = $results_answer[0];

					$results_question = get_question_once($cached_questions,mysql_result($query_result,$i,'question_id'));
					$cached_questions = $results_question[0];

					$items[] = array(
						'type'=>'answer',
						'date_created'=>strtotime(mysql_result($query_result,$i,'date_created')),
						'item'=>$results_answer[1],
						'parent'=>$results_question[1]
					);
				}

				// QUESTIONS
				$query = 'SELECT id, date_created FROM qa_questions WHERE is_active=1 AND author_user_id="'.$this->info['id'].'" AND date_created >= "' . date('Y-m-d',$reference_date) . '"';

				$query_result = sql_query($query);
				$num_rows = @mysql_numrows($query_result);

				for($i=0;$i<$num_rows;$i++){
					$results = get_question_once($cached_questions,mysql_result($query_result,$i,'id'));
					$cached_questions = $results[0];

					$items[] = array(
						'type'=>'question',
						'date_created'=>strtotime(mysql_result($query_result,$i,'date_created')),
						'item'=>$results[1]
					);
				}

				// VOTES
				$query = 'SELECT item_type, item_id, value, date_created FROM qa_votes WHERE voter_user_id="'.$this->info['id'].'" AND date_created >= "' . date('Y-m-d',$reference_date) . '"';

				$query_result = sql_query($query);
				$num_rows = @mysql_numrows($query_result);

				for($i=0;$i<$num_rows;$i++){
				
					$item_type = mysql_result($query_result,$i,'item_type');
				
					$temp = null;
				
					if($item_type=='question'){
						$results_question = get_question_once($cached_questions,mysql_result($query_result,$i,'item_id'));
						$cached_questions = $results_question[0];
						$temp = $results_question[1];
					}else if($item_type=='answer'){
						$results_answer = get_answer_once($cached_answers,mysql_result($query_result,$i,'item_id'));
						$cached_answers = $results_answer[0];
						$temp = $results_answer[1];
					}
				
					$items[] = array(
						'type'=>'vote',
						'date_created'=>strtotime(mysql_result($query_result,$i,'date_created')),
						'item'=>array(
							'item_type'=>$item_type,
							'item_id'=>mysql_result($query_result,$i,'item_id'),
							'value'=>mysql_result($query_result,$i,'value'),
							'date_created'=>mysql_result($query_result,$i,'date_created')
						),
						'parent'=>$temp
					);
				}
				// SORT BY DATE, DESC
				usort($items,'sort_activity_dates_desc');
				
			}
			return $items;
		}
		
		function qa_get_my_questions($include_inactives = null,$limit = null){
		
			require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_qa_question.php');
		
			$sql = 'SELECT id FROM qa_questions WHERE author_user_id="' . $this->info['id'] . '" ';
			if(is_null($include_inactives)) $include_inactives = false;
			if(is_null($limit)) $limit = 50;
			
			if(!$include_inactives) $sql .= ' AND is_active=1';
			
			$sql .= ' ORDER BY date_created DESC';
			
			if($limit>0) $sql .= ' LIMIT ' . $limit;
			
			$result = sql_query($sql);

			$num_rows = intval(@mysql_numrows($result));
			$this->qa_questions = array();

			for($i=0;$i<$num_rows;$i++){
				$this->qa_questions[] = new qa_question(mysql_result($result,$i,'id'));
			}
			return $this->qa_questions;
		}
		
		function qa_get_my_answers($include_inactives = null,$limit = null){
		
			require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_qa_answer.php');
		
			$sql = 'SELECT id FROM qa_answers WHERE author_user_id="' . $this->info['id'] . '" ';		
			if(is_null($include_inactives)) $include_inactives = false;
			if(is_null($limit)) $limit = 50;
			
			if(!$include_inactives) $sql .= ' AND is_active=1';
			
			$sql .= ' ORDER BY date_created DESC';
			
			if($limit>0) $sql .= ' LIMIT ' . $limit;

			$result = sql_query($sql);

			$num_rows = intval(@mysql_numrows($result));
			$this->qa_answers = array();

			for($i=0;$i<$num_rows;$i++){
				$this->qa_answers[] = new qa_answer(mysql_result($result,$i,'id'));
			}
			return $this->qa_answers;
		}

		function qa_get_my_subscriptions($item_type,$limit = 50){			
			if($item_type=='questions'){
				$sql = 'SELECT item_id FROM qa_subscriptions INNER JOIN qa_questions ON qa_subscriptions.item_id = qa_questions.id WHERE qa_subscriptions.item_type="question" AND qa_subscriptions.subscriber_user_id="'.$this->info['id'].'" AND qa_questions.is_active = 1 LIMIT ' . $limit;
				
				$result = sql_query($sql);
				$num_rows = intval(@mysql_numrows($result));
				$ret = array();
				
				for($i=0;$i<$num_rows;$i++){
					$ret[] = new qa_question(mysql_result($result,$i,'item_id'));
				}
			}else if($item_type=='users'){
				$sql = 'SELECT item_id FROM qa_subscriptions INNER JOIN monrovia_users ON qa_subscriptions.item_id = monrovia_users.id WHERE qa_subscriptions.item_type="user" AND qa_subscriptions.subscriber_user_id="'.$this->info['id'].'" AND monrovia_users.is_active = 1 AND monrovia_users.id <> "' . $this->info['id'] . '" LIMIT ' . $limit;
				
				$result = sql_query($sql);
				$num_rows = intval(@mysql_numrows($result));
				$ret = array();
				
				for($i=0;$i<$num_rows;$i++){
					$ret[] = new monrovia_user(mysql_result($result,$i,'item_id'));
				}
			}else{
				return;
			}
			
			$this->qa_subscriptions[$item_type] = $ret;
			return $this->qa_subscriptions[$item_type];
		}
		
		function qa_is_subscribed_to_user($user_id){
			$sql = 'SELECT COUNT(*) AS is_subscribed FROM qa_subscriptions WHERE item_type="user" AND item_id="' . $user_id . '" AND subscriber_user_id="'.$this->info['id'].'"';
			$result = sql_query($sql);
			return mysql_result($result,0,'is_subscribed')>0;
		}
		
		function get_subscriber_info($start_index = null,$how_many = null,$prefer_user_id = null){
			$ret = array(
				'total'=>0,
				'subscribers'=>array()
			);
			$result = sql_query('SELECT COUNT(*) AS total FROM qa_subscriptions INNER JOIN monrovia_users ON monrovia_users.id = qa_subscriptions.subscriber_user_id WHERE qa_subscriptions.item_type="user" AND qa_subscriptions.item_id="' . $this->info['id'] . '" AND monrovia_users.is_active = 1 AND qa_subscriptions.subscriber_user_id <> "' . $this->info['id'] . '"');

			$ret['total'] = intval(mysql_result($result,0,'total'));
			if($ret['total']>0){
				$ret['subscribers'] = $this->get_subscribers($start_index,$how_many,$prefer_user_id);
				$ret['subscribers_remaining'] = $ret['total'] - ($start_index + count($ret['subscribers']));
			}
			return $ret;
		}
		
		function get_subscribers($start_index = null,$how_many = null,$prefer_user_id = null){
		
			if(is_null($start_index)) $start_index = 0;
			if(is_null($how_many)) $how_many = 20;
			if(is_null($prefer_user_id)) $prefer_user_id = '';
		
			$result = sql_query('SELECT monrovia_users.id, monrovia_users.website_name,monrovia_users.avatar,monrovia_users.website_url,monrovia_users.user_name,monrovia_users.date_created FROM qa_subscriptions INNER JOIN monrovia_users ON monrovia_users.id = qa_subscriptions.subscriber_user_id WHERE qa_subscriptions.item_type="user" AND qa_subscriptions.item_id="' . $this->info['id'] . '" AND monrovia_users.is_active = 1 AND qa_subscriptions.subscriber_user_id <> "' . $this->info['id'] . '" ORDER BY monrovia_users.id = "' . $prefer_user_id . '" DESC LIMIT ' . $start_index . ', ' . $how_many);
			
			$num_rows = intval(@mysql_numrows($result));
			
			$ret = array();
			for($i=0;$i<$num_rows;$i++){
					$ret[] = array(
						'user_name'=>mysql_result($result,$i,'user_name'),
						'avatar'=>mysql_result($result,$i,'avatar')!=''?mysql_result($result,$i,'avatar'):'avatar-generic.png',
						'website_name'=>mysql_result($result,$i,'website_name'),
						'website_url'=>mysql_result($result,$i,'website_url'),
						'total_questions'=>get_total_questions_by_user(mysql_result($result,$i,'id')),
						'total_answers'=>get_total_questions_by_user(mysql_result($result,$i,'id')),
						'date_created'=>mysql_result($result,$i,'date_created')
					);
			}
			return $ret;
		}

		function get_scrapbooked_items($limit_for_each_type = 50){
			$ret = array();
			// STEP 1: GET ANSWERS
			$result = sql_query('SELECT qa_answers.id,qa_answers.question_id,qa_answers.author_user_id,qa_answers.title,qa_answers.details AS details,qa_scrapbooked_items.date_created AS date_scrapbooked, qa_answers.date_created, monrovia_users.user_name AS author_user_name, monrovia_users.id AS author_user_id, monrovia_users.is_active AS author_is_active, monrovia_users.is_official_qa_rep AS author_is_official_qa_rep, monrovia_users.avatar AS author_avatar FROM qa_scrapbooked_items INNER JOIN qa_answers ON qa_scrapbooked_items.item_id = qa_answers.id LEFT JOIN monrovia_users on qa_answers.author_user_id = monrovia_users.id WHERE qa_scrapbooked_items.user_id="' . $this->info['id'] . '" AND qa_scrapbooked_items.item_type="answer" AND qa_answers.is_active=1 LIMIT ' . $limit_for_each_type);
			
			$num_rows = intval(@mysql_numrows($result));
			
			$question_ids = array();
			
			$answers = array();
			
			for($i=0;$i<$num_rows;$i++){
			
				$avatar = mysql_result($result,$i,'author_avatar');
				if(mysql_result($result,$i,'author_is_official_qa_rep')=='1'){
					$avatar = 'avatar-expert.png';
				}else if($avatar==''){
					$avatar = 'avatar-generic.png';
				}
			
				$answers[] = array(
					'item_type'=>'answer',
					'date_scrapbooked'=>mysql_result($result,$i,'date_scrapbooked'),
					'item'=>array(
						'id'=>mysql_result($result,$i,'id'),
						'author_user_id'=>mysql_result($result,$i,'author_user_id'),
						'author_is_active'=>mysql_result($result,$i,'author_is_active'),
						'author_user_name'=>mysql_result($result,$i,'author_user_name'),
						'author_is_official_qa_rep'=>mysql_result($result,$i,'author_is_official_qa_rep'),
						'author_avatar'=>$avatar,
						'question_id'=>mysql_result($result,$i,'question_id'),
						'title'=>mysql_result($result,$i,'title'),
						'details'=>mysql_result($result,$i,'details'),
						'date_created'=>mysql_result($result,$i,'date_created')
					)
				);
				$question_ids[] = mysql_result($result,$i,'question_id');
			}
			
			// STEP 2: GET REFERENCES TO SCRAPBOOKED ANSWERS
				
			$result = sql_query('SELECT item_id,date_created from qa_scrapbooked_items WHERE user_id="' . $this->info['id'] . '" AND item_type="question"');
						
			$num_rows = intval(@mysql_numrows($result));

			$scrapbooked_questions = array();
			for($i=0;$i<$num_rows;$i++){
				$scrapbooked_questions[] = array(
					'date_scrapbooked'=>mysql_result($result,$i,'date_created'),
					'id'=>mysql_result($result,$i,'item_id')
				);
				$question_ids[] = mysql_result($result,$i,'item_id');
			}
			
			$question_ids = implode(',',array_unique($question_ids));

			if($question_ids!=''){

				// STEP 3: GET ALL QUESTIONS
				$result = sql_query('SELECT qa_questions.id, qa_questions.category_id, qa_questions.url_key, qa_questions.title, IF(monrovia_users.is_active = 1, monrovia_users.user_name, NULL) AS author_user_name, IF(monrovia_users.is_active = 1, monrovia_users.is_official_qa_rep, 0) AS author_is_official_qa_rep, IF(monrovia_users.avatar <> "",monrovia_users.avatar,"avatar-generic.png") AS avatar, qa_questions.date_created FROM qa_questions LEFT JOIN monrovia_users ON monrovia_users.id = qa_questions.author_user_id WHERE qa_questions.id IN (' . $question_ids . ') AND qa_questions.is_active=1 LIMIT ' . $limit_for_each_type);
				
				$questions = array();
				$num_rows = intval(@mysql_numrows($result));
				for($i=0;$i<$num_rows;$i++){
					$avatar = mysql_result($result,$i,'avatar');
					if(mysql_result($result,$i,'author_is_official_qa_rep')=='1'){
						$avatar = 'avatar-expert.png';
					}else if($avatar==''){
						$avatar = 'avatar-generic.png';
					}
				
					$questions[] = array(
						'id'=>mysql_result($result,$i,'id'),
						'category_id'=>mysql_result($result,$i,'category_id'),
						'title'=>mysql_result($result,$i,'title'),
						'author_user_name'=>mysql_result($result,$i,'author_user_name'),
						'url_key'=>mysql_result($result,$i,'url_key'),
						'avatar'=>$avatar,
						'date_created'=>mysql_result($result,$i,'date_created'),
						'answers'=>array()
					);
				}

				// STEP 4: ASSIGN ANSWERS TO QUESTIONS
				for($i=0;$i<count($answers);$i++){
					$question_index = get_question_index($questions,$answers[$i]['item']['question_id']);
					if($question_index>-1){
						$questions[$question_index]['answers'][] = $answers[$i];
					}
				}
				
				// STEP 5: ADD date_scrapbooked FLAG
				for($i=0;$i<count($scrapbooked_questions);$i++){
					$question_index = get_question_index($questions,$scrapbooked_questions[$i]['id']);
					if($question_index>-1){
						$questions[$question_index]['date_scrapbooked'] = $scrapbooked_questions[$i]['date_scrapbooked'];
						$questions[$question_index]['effective_date_scrapbooked'] = $scrapbooked_questions[$i]['date_scrapbooked'];
					}
				}

				// STEP 6: SET effective_date_scrapbooked FLAG
				for($i=0;$i<count($questions);$i++){
					$date_answer_scrapbooked = '2000-01-01'; // ARBITRARY
					if(count($questions[$i]['answers'])) $date_answer_scrapbooked = $questions[$i]['answers'][0]['date_scrapbooked'];
					
					// IF THE ANSWER WAS ADDED TO THE SCRAPBOOK LATER THAN THE QUESTION WAS, SET effective_date_scrapbooked TO THE ANSWER'S SCRAPBOOKED DATE
					if(!isset($questions[$i]['effective_date_scrapbooked'])||strtotime($date_answer_scrapbooked)>strtotime($questions[$i]['effective_date_scrapbooked'])) $questions[$i]['effective_date_scrapbooked'] = $questions[$i]['answers'][0]['date_scrapbooked'];
				}

				// STEP 7: SORT QUESTIONS BY effective_date_scrapbooked
				usort($questions,'compare_question_effective_scrapbooked_date');
				$ret = $questions;
			}
			
			return $ret;

		}
		
		/* END Q&A */

		
		
	}
	
	
	function get_question_index($questions,$question_id){
		for($i=0;$i<count($questions);$i++){
			if($questions[$i]['id']==$question_id) return $i;
		}
		return -1;
	}
	
	function compare_question_effective_scrapbooked_date($a,$b){
		$a = strtotime($a['effective_date_scrapbooked']);
		$b = strtotime($b['effective_date_scrapbooked']);
		
		if($a==$b) return 0;
		return ($a>$b)?-1:1;
	}

	function cms_output_backend_users(){
		$result = sql_query("SELECT id,is_active,user_name,password,email_address,first_name,last_name,zip,permissions,date_last_login FROM monrovia_users WHERE permissions NOT IN ('','cmgt','cmgt,') ORDER BY is_active DESC, user_name ASC");
		$num_rows = intval(@mysql_numrows($result));
		$ret = '';
		$temp = new monrovia_user();
		for($i=0;$i<$num_rows;$i++){
				$ret .= ",new monrovia_user(".js_sanitize(mysql_result($result,$i,"id")).",'".js_sanitize(mysql_result($result,$i,"is_active"))."','".js_sanitize(mysql_result($result,$i,"user_name"))."','".js_sanitize($temp->password_decrypt(mysql_result($result,$i,"password")))."','".js_sanitize(mysql_result($result,$i,"email_address"))."','".js_sanitize(mysql_result($result,$i,"first_name"))."','".js_sanitize(mysql_result($result,$i,"last_name"))."','".js_sanitize(mysql_result($result,$i,"zip"))."','".js_sanitize(mysql_result($result,$i,"permissions"))."','".js_sanitize(mysql_result($result,$i,"date_last_login"))."')";
		}
		echo(substr($ret,1));
	}
	
	function get_user_by_user_name($user_name,$include_inactives = false){
		$sql = 'SELECT id FROM monrovia_users WHERE user_name = "' . $user_name . '"';
		
		if(!$include_inactives) $sql .= ' AND is_active = 1';
	
		$result = sql_query($sql);
		if(@mysql_numrows($result)==1){
			return new monrovia_user(mysql_result($result,0,'id'));
		}else{
			return null;
		}
	}
	
?>