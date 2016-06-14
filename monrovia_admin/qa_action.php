<?php
	header('Content-Type: text/javascript');

	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/init.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_qa_question.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_qa_answer.php');
	
	// MAKE SURE USER HAS Q&A MODERATION PERMISSION
	$monrovia_user = new monrovia_user($_SESSION['monrovia_user_id']);
	$monrovia_user->permission_requirement('qamd');

	$record_type = $_GET['type'];
	$record_id = intval($_GET['id']);
	$action = $_GET['action'];
	
	// VALIDATE INPUT
	if(($record_type!='question'&&$record_type!='answer')||$record_id==0) exit;
	
	switch($action){
		case 'get':
			if($record_type=='question'){
				$question = new qa_question($record_id);
				die(json_encode(array(
					'id'=>$question->info['id'],
					'is_active'=>$question->info['is_active'],
					'title'=>$question->info['title'],
					'details'=>$question->info['details'],
					'category_id'=>$question->info['category_id'],
					'full_path'=>$question->full_path,
					'author_info'=>$question->get_basic_author_info()
				)));
			}else if($record_type=='answer'){
				$answer = new qa_answer($record_id);
				$answer->get_question();
				die(json_encode(array(
					'id'=>$answer->info['id'],
					'is_active'=>$answer->info['is_active'],
					'title'=>$answer->info['title'],
					'details'=>$answer->info['details'],
					'question'=>array(
						'title'=>$answer->question->info['title'],
						'full_path'=>$answer->question->full_path
					),
					'author_info'=>$answer->get_basic_author_info()
				)));
			}
		break;
		case 'save':
			if($record_type=='question'){			
				if(!isset($_GET['category_id'])||intval($_GET['category_id'])==0||!isset($_GET['is_active'])||(intval($_GET['is_active'])!=0&&intval($_GET['is_active'])!=1)) exit;
			
				$question = new qa_question($record_id);
				$question->info['is_active'] = intval($_GET['is_active']);
				$question->info['category_id'] = intval($_GET['category_id']);
				$question->info['title'] = $_GET['title'];
				$question->info['details'] = $_GET['details'];
				$question->save();
			}else if($record_type=='answer'){
				if(!isset($_GET['is_active'])||(intval($_GET['is_active'])!=0&&intval($_GET['is_active'])!=1)) exit;
			
				$answer = new qa_answer($record_id);				
				$answer->info['is_active'] = intval($_GET['is_active']);
				$answer->info['title'] = $_GET['title'];
				$answer->info['details'] = $_GET['details'];
				$answer->save();
			}
		break;
		case 'delete':
			if($record_type=='question'){
				$question = new qa_question($record_id);
				$question->delete();
			}else if($record_type=='answer'){
				$answer = new qa_answer($record_id);				
				$answer->delete();
			}
		break;
	}
?>