<?php
	header('Content-Type: text/javascript');

	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/init.php');
	
	// MAKE SURE USER HAS Q&A MODERATION PERMISSION
	$monrovia_user = new monrovia_user($_SESSION['monrovia_user_id']);
	$monrovia_user->permission_requirement('qamd');
	
	$results_per_page = 25;
	$page_num = 1;
	if(isset($_GET['page'])&&intval($_GET['page'])>=1) $page_num = intval($_GET['page']);
	
	// RETRIEVE CRITERIA VIA URL PARAMS
	$criteria = array(
		'status'=>'',
		'type'=>'',
		'date_before'=>'',
		'date_after'=>'',
		'flagging'=>'',
		'up_votes'=>'',
		'down_votes'=>'',
		'author'=>'',
		'keywords'=>''
	);
	if(isset($_GET['status'])&&($_GET['status']=='1'||$_GET['status']=='0')) $criteria['status'] = $_GET['status'];
	if(isset($_GET['type'])&&($_GET['type']=='question'||$_GET['type']=='answer')) $criteria['type'] = $_GET['type'];
	if(isset($_GET['date_before'])&&strtotime($_GET['date_before'])!==false) $criteria['date_before'] = $_GET['date_before'];
	if(isset($_GET['date_after'])&&strtotime($_GET['date_after'])!==false) $criteria['date_after'] = $_GET['date_after'];
	if(isset($_GET['flagging'])&&($_GET['flagging']=='1'||$_GET['flagging']=='0')) $criteria['flagging'] = $_GET['flagging'];
	if(isset($_GET['up_votes'])&&($_GET['up_votes']=='1'||$_GET['up_votes']=='0')) $criteria['up_votes'] = $_GET['up_votes'];
	if(isset($_GET['down_votes'])&&($_GET['down_votes']=='1'||$_GET['down_votes']=='0')) $criteria['down_votes'] = $_GET['down_votes'];
	if(isset($_GET['author'])&&$_GET['author']!=''){
		$user = get_user_by_user_name($_GET['author'],true);
		if(is_null($user)){
			die(json_encode(array(
				'success'=>false,
				'field'=>'author'
			)));
		}
		$criteria['author'] = $user->info['id'];
	}
	if(isset($_GET['keywords'])) $criteria['keywords'] = $_GET['keywords'];

	function generate_pagination_html($total_pages,$total_results,$page_num = null,$max_pagination_links_to_display = null){
		if(is_null($page_num)) $page_num = 1;
		if(is_null($max_pagination_links_to_display)) $max_pagination_links_to_display = 7;
		
		$leftmost_page = max($page_num-floor($max_pagination_links_to_display/2),1);
		$rightmost_page = min($leftmost_page+$max_pagination_links_to_display-1,$total_pages);
		$leftmost_page = max(1,$rightmost_page-$max_pagination_links_to_display+1);

		$pagination_html = array();

		$pagination_html[1] = 'Pages: <a href="javascript:void(0);"'. (($page_num==1)?' class="selected"':'').' data-page-num="1">1</a>';
		$num_pagination_links = 1;

		for($i=$leftmost_page;$i<$rightmost_page+1;$i++){
			if($i!=1){
				$pagination_html[$i] = '<a href="javascript:void(0);"' . (($page_num==$i)?' class="selected"':''). ' data-page-num="'.$i.'">'.$i.'</a>';
				$num_pagination_links++;
			}
		}

		$pagination_html[$total_pages] = '<a href="javascript:void(0);"'. (($page_num==$total_pages)?' class="selected"':'') .' data-page-num="'.$total_pages.'">'.$total_pages.'</a>';
		$num_pagination_links++;

		// TRIM OFF EXTRA PAGINATION LINKS
		for($i=1;$i<2&&$num_pagination_links>$max_pagination_links_to_display;$i++){
			if($leftmost_page>1){
				$pagination_html[$leftmost_page] = '&middot;&middot;&middot;&nbsp;';
				$num_pagination_links--;
			}
			if($rightmost_page<$total_pages){
				$pagination_html[$rightmost_page] = '&middot;&middot;&middot;&nbsp;';
				$num_pagination_links--;
			}
		};
		if($total_pages>1){
			$pagination_html = implode('',$pagination_html);
		}else{
			$pagination_html = '';
		}
		return $pagination_html;
	}


	

	$sql_template = <<<JPL
SELECT {{SELECT_COLUMNS}}
FROM {{DB_TABLE}} q
{{CATEGORY_JOINS}}
LEFT JOIN
monrovia_users ON monrovia_users.id = q.author_user_id
LEFT JOIN 
(SELECT item_id, item_type, value FROM qa_votes WHERE value = 1 AND item_type = "{{ITEM_TYPE}}") AS up_vote_table on q.id = up_vote_table.item_id
LEFT JOIN 
(SELECT item_id, item_type, value FROM qa_votes WHERE value = -1 AND item_type = "{{ITEM_TYPE}}") AS down_vote_table on q.id = down_vote_table.item_id
LEFT JOIN
(SELECT item_id, item_type FROM qa_flags WHERE item_type = "{{ITEM_TYPE}}") AS flag_table on q.id = flag_table.item_id
{{WHERE_CLAUSE}}
GROUP BY q.id, q.is_active, q.date_created
JPL;

	$sql_select_columns = 'q.id, q.is_active, "{{ITEM_TYPE}}" AS type, q.title, LEFT(q.details,40) AS details, q.date_created, qa_categories.name AS category_name, q.author_user_id, monrovia_users.user_name, COUNT(flag_table.item_id) AS times_flagged, COUNT(up_vote_table.value) AS up_votes, COUNT(down_vote_table.value) AS down_votes';

	$sql_questions = $sql_template;
	$sql_answers = $sql_template;
	
	$sql_questions = str_replace('{{CATEGORY_JOINS}}','LEFT JOIN qa_categories ON qa_categories.id = q.category_id',$sql_questions);
	$sql_answers = str_replace('{{CATEGORY_JOINS}}','LEFT JOIN qa_questions ON qa_questions.id = q.question_id LEFT JOIN qa_categories ON qa_categories.id = qa_questions.category_id',$sql_answers);	
	
	$where_clause = array();

	// STATUS
	if($criteria['status']!='')
		$where_clause[] = 'q.is_active = "' . $criteria['status'] . '"';

	// POSTED AFTER
	if($criteria['date_after']!='')
		$where_clause[] = 'q.date_created > "' . $criteria['date_after'] . '"';

	// POSTED BEFORE
	if($criteria['date_before']!='')
		$where_clause[] = 'q.date_created < "' . $criteria['date_before'] . '"';

	// FLAGGING
	if($criteria['flagging']=='0'){
		$where_clause[] = 'flag_table.item_type IS NULL';
	}else if($criteria['flagging']=='1'){
		$where_clause[] = 'flag_table.item_type = "{{ITEM_TYPE}}"';
	}else{
		$where_clause[] = '(flag_table.item_type = "{{ITEM_TYPE}}" OR flag_table.item_type IS NULL)';
	}
	
	// UP VOTES
	if($criteria['up_votes']=='0'){
		$where_clause[] = 'up_vote_table.value IS NULL';
	}else if($criteria['up_votes']=='1'){
		$where_clause[] = 'up_vote_table.item_type = "{{ITEM_TYPE}}"';
	}else{
		$where_clause[] = '(up_vote_table.item_type = "{{ITEM_TYPE}}" OR up_vote_table.value IS NULL)';
	}
	
	// DOWN VOTES
	if($criteria['down_votes']=='0'){
		$where_clause[] = 'down_vote_table.value IS NULL';
	}else if($criteria['down_votes']=='1'){
		$where_clause[] = 'down_vote_table.item_type = "{{ITEM_TYPE}}"';
	}else{
		$where_clause[] = '(down_vote_table.item_type = "{{ITEM_TYPE}}" OR down_vote_table.value IS NULL)';
	}

	// AUTHOR
	if($criteria['author']!='')
		$where_clause[] = 'q.author_user_id = "' . $criteria['author'] . '"';
		
	// KEYWORDS
	if($criteria['keywords']!='')
		$where_clause[] = 'MATCH(q.title,q.details) AGAINST("' . $criteria['keywords'] . '" IN BOOLEAN MODE)';

	// FLATTEN WHERES
	$where_clause_str = '';
	if(count($where_clause)) $where_clause_str = ' WHERE ' . implode(' AND ',$where_clause);
	
	// POPULATE PLACEHOLDERS
	$sql_questions = str_replace('{{DB_TABLE}}','qa_questions',$sql_questions);
	$sql_questions = str_replace('{{WHERE_CLAUSE}}',$where_clause_str,$sql_questions);
	
	// PREPARE "COUNT" STATEMENT DIFFERENTLY
	$sql_questions_count = $sql_questions;
	$sql_questions_count = str_replace('{{SELECT_COLUMNS}}','q.date_created',$sql_questions_count);
	$sql_questions_count = str_replace('{{ITEM_TYPE}}','Question',$sql_questions_count);
	
	$sql_questions = str_replace('{{SELECT_COLUMNS}}',$sql_select_columns,$sql_questions);
	$sql_questions = str_replace('{{ITEM_TYPE}}','Question',$sql_questions);
	
	$sql_answers = str_replace('{{DB_TABLE}}','qa_answers',$sql_answers);
	$sql_answers = str_replace('{{WHERE_CLAUSE}}',$where_clause_str,$sql_answers);
	
	// PREPARE "COUNT" STATEMENT DIFFERENTLY
	$sql_answers_count = $sql_answers;
	$sql_answers_count = str_replace('{{SELECT_COLUMNS}}','q.date_created',$sql_answers_count);
	$sql_answers_count = str_replace('{{ITEM_TYPE}}','Answer',$sql_answers_count);

	$sql_answers = str_replace('{{SELECT_COLUMNS}}',$sql_select_columns,$sql_answers);
	$sql_answers = str_replace('{{ITEM_TYPE}}','Answer',$sql_answers);
	
	if($criteria['type']=='question'){
		$sql_final = $sql_questions . ' ORDER BY date_created DESC';	
		$sql_final_count = $sql_questions_count;
	}else if($criteria['type']=='answer'){
		$sql_final = $sql_answers . ' ORDER BY date_created DESC';	
		$sql_final_count = $sql_answers_count;	
	}else{
		$sql_final = $sql_questions . ' UNION ' . $sql_answers . ' ORDER BY date_created DESC';	
		$sql_final_count = $sql_questions_count . ' UNION ' . $sql_answers_count;
	}

	$total_results = @mysql_numrows(sql_query($sql_final_count));
	
	$total_pages = ceil($total_results/$results_per_page);

	if($page_num>$total_pages&&$total_pages>0) $page_num = $total_pages;

	$record_offset = ($page_num - 1) * $results_per_page;
	
	$sql_final .= ' LIMIT ' . $record_offset . ',' . $results_per_page;
	$query_result = sql_query($sql_final);
	$num_records = @mysql_numrows($query_result);

//die($sql_final);

	$response = array(
		'success'=>true,
		'pagination_html'=>generate_pagination_html($total_pages,$total_results,$page_num,7),
		'results'=>array()
	);
	
	if($GLOBALS['server_info']['environment']!='prod'){
		$response['sql_final'] = $sql_final;
		$response['sql_final_count'] = $sql_final_count;
	}

	for($i=0;$i<$num_records;$i++){
	
		$desc = mysql_result($query_result,$i,'title');
		if($desc=='') $desc = mysql_result($query_result,$i,'details');
	
		$response['results'][] = array(
			'id'=>mysql_result($query_result,$i,'id'),
			'is_active'=>mysql_result($query_result,$i,'is_active'),
			'type'=>mysql_result($query_result,$i,'type'),
			'description'=>html_sanitize($desc),
			'date_created'=>date('Y-m-d',strtotime(mysql_result($query_result,$i,'date_created'))),
			'category_name'=>mysql_result($query_result,$i,'category_name'),
			'author_user_id'=>mysql_result($query_result,$i,'author_user_id'),
			'author_user_name'=>mysql_result($query_result,$i,'user_name'),
			'times_flagged'=>mysql_result($query_result,$i,'times_flagged'),
			'up_votes'=>mysql_result($query_result,$i,'up_votes'),
			'down_votes'=>mysql_result($query_result,$i,'down_votes'),
		);
	}

	die(json_encode($response));

?>