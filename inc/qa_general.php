<?php

/*

	THIS SCRIPT SHOULD NOT OUTPUT ANYTHING WITHOUT EXPLICIT INVOCATION

*/


	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_qa_category.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_qa_question.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_qa_answer.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_search_qa_question.php');

	function build_search_excerpts($docs,$index,$words,$opts = null){
		require_once('sphinxapi.php');
		if($opts==null){
			$opts = array
			(
				"before_match"		=> "<strong class=\"phrase_match\">",
				"after_match"		=> "</strong>",
				"chunk_separator"	=> "&hellip;",
				"limit"				=> 60,
				"around"			=> 3,
				"exact_phrase"		=> 0
			);
		}
		$griffin = new SphinxClient();
		return $griffin->BuildExcerpts($docs,$index,$words,$opts);
	}

	function get_user_name($user_id,$include_inactives = false){
		$sql = 'SELECT user_name FROM monrovia_users WHERE ';
		if(!$include_inactives) $sql .= 'is_active="1" AND ';
		$sql .= 'id="'.$user_id.'"
		';
		$query_result = sql_query($sql);
		$num_rows = @mysql_numrows($query_result);

		if($num_rows==1){
			return mysql_result($query_result,0,'user_name');
		}else{
			return '';
		}
	}
	
	function get_category_name($category_id,$include_inactives = false){
		$sql = 'SELECT name FROM qa_categories WHERE ';
		if(!$include_inactives) $sql .= 'is_active="1" AND ';
		$sql .= 'id="'.$category_id.'"
		';
		$query_result = sql_query($sql);
		$num_rows = @mysql_numrows($query_result);

		if($num_rows==1){
			return mysql_result($query_result,0,'name');
		}else{
			return '';
		}
	}
	
	function set_flagged_status($item_type,$item_id,$user_id,$reason_id){
		$sql = 'SELECT * FROM qa_flags WHERE item_type="'.$item_type.'" AND item_id="'.$item_id.'" AND flagger_user_id="'.$user_id.'"';
		$query_result = sql_query($sql);

		$num_rows = @mysql_numrows($query_result);
		if($num_rows==0){
			sql_query('INSERT INTO qa_flags (item_type,item_id,flagger_user_id,flag_reason_id,date_created) VALUES ("'.$item_type.'","'.$item_id.'","'.$user_id.'","'.$reason_id.'",NOW());');
			return true;
		}else{
			return false;
		}
	}

	function set_vote($item_type,$item_id,$user_id,$direction = 'cancel'){
		$query_result = sql_query('DELETE FROM qa_votes WHERE item_type="'.$item_type.'" AND item_id="'.$item_id.'" AND voter_user_id="'.$user_id.'"');
		if($direction!='cancel') sql_query('INSERT INTO qa_votes (item_type,item_id,voter_user_id,value,date_created) VALUES ("'.$item_type.'","'.$item_id.'","'.$user_id.'",'.($direction=='down'?-1:1).',NOW());');
		
		sql_query('UPDATE monrovia_users SET date_latest_qa_activity = NOW() WHERE id="' . $user_id .'";');

		// UPDATE QUALITY RATINGS
		if($item_type=='question'){
			$question = new qa_question($item_id);
			$question->update_quality_rating();
		}else if($item_type=='answer'){
			$answer = new qa_answer($item_id);
			$answer->update_quality_rating();
		}
		
		return true;
	}
	
	function set_subscription_status($item_type,$item_id,$subscriber_user_id,$subscribe = true){
		if($subscribe){
			// BEFORE INSERTING, CHECK FOR EXISTENCE
			$query_result = sql_query('SELECT * FROM qa_subscriptions WHERE item_type="'.$item_type.'" AND item_id="'.$item_id.'" AND subscriber_user_id="'.$subscriber_user_id.'"');

			$num_rows = @mysql_numrows($query_result);
			if($num_rows==0) sql_query('INSERT INTO qa_subscriptions (item_type,item_id,subscriber_user_id,date_created) VALUES ("'.$item_type.'","'.$item_id.'","'.$subscriber_user_id.'",NOW());');
			
		}else{
			sql_query('DELETE FROM qa_subscriptions WHERE item_type="'.$item_type.'" AND item_id="'.$item_id.'" AND subscriber_user_id="'.$subscriber_user_id.'"');		
		}
	}

	function get_subscription_status($item_type,$item_id,$subscriber_user_id){
		$query_result = sql_query('SELECT * FROM qa_subscriptions WHERE item_type="'.$item_type.'" AND item_id="'.$item_id.'" AND subscriber_user_id="'.$subscriber_user_id.'"');

		$num_rows = @mysql_numrows($query_result);
		return ($num_rows>0);
	}

	function set_scrapbook_status($item_type,$item_id,$user_id,$add = true){
		if($add){
			// BEFORE INSERTING, CHECK FOR EXISTENCE
			$query_result = sql_query('SELECT * FROM qa_scrapbooked_items WHERE user_id="' . $user_id . '" AND item_type="'.$item_type.'" AND item_id="'.$item_id.'"');

			$num_rows = @mysql_numrows($query_result);
			if($num_rows==0){
				sql_query('INSERT INTO qa_scrapbooked_items (user_id,item_type,item_id,date_created) VALUES ("' . $user_id . '","'.$item_type.'","'.$item_id.'",NOW());');
				return true;
			}else{
				return false;
			}
		}else{
			sql_query('DELETE FROM qa_scrapbooked_items WHERE user_id="' . $user_id . '" AND item_type="'.$item_type.'" AND item_id="'.$item_id.'"');
		}
	}

	function get_scrapbook_status($item_type,$item_id,$user_id){
			$query_result = sql_query('SELECT * FROM qa_scrapbooked_items WHERE user_id="' . $user_id . '" AND item_type="'.$item_type.'" AND item_id="'.$item_id.'"');

			$num_rows = @mysql_numrows($query_result);
			return ($num_rows>0);
	}
	
	function get_votes($item_type,$item_id,$user_id = 0){
		$ret = array();

		// IF $user_id PROVIDED, THE RETURNED INTEGER REFLECTS HOW (IF AT ALL) THE USER HAS VOTED ON THIS PARTICULAR ITEM
		$sql = 'SELECT COUNT(*) AS total FROM qa_votes WHERE item_type="' . $item_type . '" AND item_id="'. $item_id .'" AND value=1';
		$result = sql_query($sql);
		$ret['up'] = mysql_result($result,0,'total');

		$sql = 'SELECT COUNT(*) AS total FROM qa_votes WHERE item_type="' . $item_type . '" AND item_id="'. $item_id .'" AND value=-1';
		$result = sql_query($sql);
		$ret['down'] = mysql_result($result,0,'total');

		if($user_id>0){
			$sql = 'SELECT SUM(value) AS total FROM qa_votes WHERE item_type="' . $item_type . '" AND item_id="'. $item_id . '" AND voter_user_id="' . $user_id . '"';
			$result = sql_query($sql);
			$ret['user_vote'] = mysql_result($result,0,'total');
		}
		return $ret;
	}


	function get_question_once($questions = null,$question_id){
		// RETURNS QUESTION FROM CACHE; OTHERWISE, FETCHES
		if($questions==null) $questions = array();
		
		for($i=0;$i<count($questions);$i++){
			if($questions[$i]->info['id']==$question_id) return array($questions,$questions[$i]);
		}
		
		// NOT CACHED; FETCH
		$temp = new qa_question($question_id);
		$questions[] = $temp;
		return array($questions,$temp);		
	}

	function get_answer_once($answers = null,$answer_id){
		// RETURNS ANSWER FROM CACHE; OTHERWISE, FETCHES
		if($answers==null) $answers = array();
		
		for($i=0;$i<count($answers);$i++){
			if($answers[$i]->info['id']==$answer_id) return array($answers,$answers[$i]);
		}
		
		// NOT CACHED; FETCH
		$temp = new qa_answer($answer_id);
		$answers[] = $temp;
		return array($answers,$temp);		
	}
	
	function get_total_questions_by_user($user_id,$include_inactives = false){
		$sql = 'SELECT COUNT(*) AS total FROM qa_questions WHERE author_user_id="' . $user_id . '"';
		if(!$include_inactives) $sql .= ' AND is_active=1';
		$query_result = sql_query($sql);
		return mysql_result($query_result,0,'total');
	}
	
	function get_total_answers_by_user($user_id,$include_inactives = false){
		$sql = 'SELECT COUNT(*) AS total FROM qa_answers WHERE author_user_id="' . $user_id . '"';
		if(!$include_inactives) $sql .= ' AND is_active=1';
		$query_result = sql_query($sql);
		return mysql_result($query_result,0,'total');
	}

	function sort_activity_dates_desc($a,$b){
		$a = $a['date_created'];
		$b = $b['date_created'];
		if($a==$b) return 0;
		return ($a>$b)?-1:1;
	}
	
	function get_subcategories($parent_category_id = 0,$parent_category_full_path = '',$include_inactive = false){

		if($parent_category_full_path=='') $parent_category_full_path = '/' . $GLOBALS['server_info']['qa_root'] . '/';

		$temp = new qa_category();

		$ret = array();

		if($include_inactive){
			$query = "SELECT id, ". $temp->table_fields ." FROM qa_categories WHERE parent_category_id = ".$parent_category_id." ORDER BY name ASC";				
		}else{
			$query = "SELECT id, ". $temp->table_fields ." FROM qa_categories WHERE is_active=1 AND parent_category_id = ".$parent_category_id." ORDER BY name ASC";				
		}

		$result = sql_query($query);
		$num_rows = @mysql_numrows($result);

		$table_fields = explode(',',$temp->table_fields);

		for($i=0;$i<$num_rows;$i++){
			// POPULATE qa_category OBJECT
			$temp = new qa_category();
			$temp->info['id'] = mysql_result($result,$i,'id');
			for($n=0;$n<count($table_fields);$n++){
				$temp->info[trim($table_fields[$n])] = mysql_result($result,$i,trim($table_fields[$n]));
			}
			$temp->populate_dumb_values();
			$temp->full_path = $parent_category_full_path . $temp->info['url_key'] . '/';
			$ret[] = $temp;
		}
		return $ret;
	}

	function get_basic_author_info($author_id){
		$query = 'SELECT id, is_active, user_name, email_address, is_official_qa_rep, avatar FROM monrovia_users WHERE id="' . $author_id . '"';
		$result = sql_query($query);
		if(mysql_numrows($result)==1){

			$user = new monrovia_user();
			$user->info['is_official_qa_rep'] = mysql_result($result,0,'is_official_qa_rep');
			$user->info['avatar'] = mysql_result($result,0,'avatar');
			$user->choose_default_avatar();

			return array(
				'id'=>mysql_result($result,0,'id'),
				'is_active'=>mysql_result($result,0,'is_active'),
				'user_name'=>mysql_result($result,0,'user_name'),
				'email_address'=>mysql_result($result,0,'email_address'),
				'avatar'=>$user->info['avatar'],
				'is_official_qa_rep'=>mysql_result($result,0,'is_official_qa_rep'),
			);
		}else{
			return null;
		}
	}

	function output_modals(){
	?>
		<div id="modal_qa_flag" class="modal_dialog" style="text-align:center;padding-top:10px;color:#000;font-weight:bold;">
			<table class="modal_dialog_backing">
				<tr>
					<td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
				</tr>
				<tr>
					<td class="corner corner_left"></td>
					<td class="corner_middle">
						<div style="width:450px;">
							<div>
								Are you sure you would like to flag this content as inappropriate and notify our moderators to review it?
							</div>
							<div style="padding-top:12px;bottom:0px;position:relative;text-align:center;">
								<div class="btn_green" style="width:200px;margin:auto;">
									<img src="/img/spacer.gif" class="side_left" /><a href="javascript:void(0);" onclick="modal_hide(true);">Yes, flag it as inappropriate</a><img src="/img/spacer.gif" />
								</div>
								<div class="btn_green" style="width:200px;margin:auto;margin-top:.5em;">
									<img src="/img/spacer.gif" class="side_left" /><a href="javascript:void(0);" onclick="modal_hide();">No, don't flag it as inappropriate</a><img src="/img/spacer.gif" />
								</div>
							</div>
						</div>
					</td>
					<td class="corner corner_right"></td>
				</tr>
				<tr>
					<td class="corner corner_bottomleft"></td><td class="corner corner_bottom"><td class="corner corner_bottomright"><td>
				</tr>
			</table>
		</div>
		<div id="modal_qa_yes_no" class="modal_dialog" style="text-align:center;padding-top:10px;color:#000;font-weight:bold;">
			<table class="modal_dialog_backing">
				<tr>
					<td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
				</tr>
				<tr>
					<td class="corner corner_left"></td>
					<td class="corner_middle">
						<div style="width:450px;">
							<div class="msg"></div>
							<div style="padding-top:12px;bottom:0px;position:relative;padding-left:50%;">
								<div class="btn_green" style="width:50px;margin-left:-56px;margin-right:12px;float:left;">
									<img src="/img/spacer.gif" class="side_left" /><a href="javascript:void(0);" onclick="modal_hide(true);">Yes</a><img src="/img/spacer.gif" />
								</div>
								<div class="btn_green" style="width:50px;float:left;">
									<img src="/img/spacer.gif" class="side_left" /><a href="javascript:void(0);" onclick="modal_hide();">No</a><img src="/img/spacer.gif" />
								</div>
								<div style="clear:both;"></div>
							</div>
						</div>
					</td>
					<td class="corner corner_right"></td>
				</tr>
				<tr>
					<td class="corner corner_bottomleft"></td><td class="corner corner_bottom"><td class="corner corner_bottomright"><td>
				</tr>
			</table>
		</div>
		<div id="modal_qa_avatars" class="modal_dialog" style="text-align:center;padding-top:10px;color:#000;font-weight:bold;">
			<table class="modal_dialog_backing">
				<tr>
					<td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
				</tr>
				<tr>
					<td class="corner corner_left"></td>
					<td class="corner_middle">
						<div style="width:450px;">
							<h3>
								Choose an avatar
							</h3>
							<? output_avatar_options(); ?>
						</div>
					</td>
					<td class="corner corner_right"></td>
				</tr>
				<tr>
					<td class="corner corner_bottomleft"></td><td class="corner corner_bottom"><td class="corner corner_bottomright"><td>
				</tr>
			</table>
		</div>
	<?
	}
	
	function make_title($a = '',$b = ''){
		$ret = $a;
		if($ret=='') $ret = $b;
		$ret = truncate($ret,128);
		return $ret;
	}
	
	function output_question_subscription($question,$version,$index){
		$activity = $question->get_activity();
		$author_user_name = get_user_name($question->info['author_user_id']);
		$category = new qa_category($question->info['category_id']);

	?>
		<div class="question_item<? if($index==0){ echo(' first');} ?>" data-item-type="question" data-item-id="<?=$question->info['id']?>">
			<div class="question_item_details">
				<span class="question heading"><a href="<?=$question->full_path?>"><?=$question->info['title']?></a></span>
				<? if($version=='question subscribed to'){ ?>
					<div class="unsubscribe"><img src="/img/btn_unsubscribe.gif" class="btn_unsubscribe" data-item-type="question" data-item-id="<?=$question->info['id']?>" /></div>
				<? } ?>
				Posted by <a class="question_author_name" href="/<?=$GLOBALS['server_info']['qa_root']?>/profiles/<?=$author_user_name?>"><?=$author_user_name?></a> under <a class="question_category" href="<?=$category->full_path?>"><?=$category->info['name']?></a> <?=date('F j, Y g:i A',strtotime($question->info['date_created']))?> PDT - <?=(intval($question->info['num_answers'])==1?'1 answer':intval($question->info['num_answers']).' answers')?>
			</div>
			<div class="clear"></div>
			<? if(count($activity)){ ?>
				<div style="margin:.5em 0px;" class="tease_widget">
					<div class="content recent_activity">
						<? for($n=0;$n<count($activity);$n++){
								$title = html_sanitize(make_title($activity[$n]['item']->info['title'], $activity[$n]['item']->info['details']));
						?>
							<p><a class="border" href="/<?=$GLOBALS['server_info']['qa_root']?>/profiles/<?=$activity[$n]['item']->info['author_user_name']?>"><?=$activity[$n]['item']->info['author_user_name']?></a> answered <a class="border" href="<?=$question->full_path?>#answer_<?=$activity[$n]['item']->info['id']?>">"<?=$title?>"</a> - <?=date('F j, Y g:i A',strtotime($activity[$n]['item']->info['date_created']))?> PDT</p>
						<? } ?>
					</div>
					<div style="text-align:right;">
						<a href="#" class="lnk_expand">view more recent activity</a>
					</div>
				</div>
			<? }else{ ?>
				<div class="msg_no_results" style="display:block;">No recent activity.</div>
			<? } ?>
		</div>
	<?
	}
	
	function output_user_activity($user,$num_days = 30){
		$activity = $user->qa_get_activity($num_days);
		
		if(count($activity)){ ?>
			<div style="margin:.5em 0px;" class="tease_widget">
				<div class="content recent_activity">
					<? for($n=0;$n<count($activity);$n++){
						if($activity[$n]['type']=='answer'){								
							$title = make_title($activity[$n]['item']->info['title'], $activity[$n]['item']->info['details']);
							$question = $activity[$n]['item']->get_question();
							?>					
								<p><a class="border" href="/<?=$GLOBALS['server_info']['qa_root']?>/profiles/<?=$user->info['user_name']?>"><?=$user->info['user_name']?></a> answered <a class="border" href="<?=$question->full_path?>#answer_<?=$activity[$n]['item']->info['id']?>">"<?=$title?>"</a> - <?=date('F j, Y g:i A',strtotime($activity[$n]['item']->info['date_created']))?> PDT</p>
							<?					
						}else if($activity[$n]['type']=='question'){
							$title = make_title($activity[$n]['item']->info['title'], $activity[$n]['item']->info['details']);
							?>
								<p><a class="border" href="/<?=$GLOBALS['server_info']['qa_root']?>/profiles/<?=$user->info['user_name']?>"><?=$user->info['user_name']?></a> asked <a class="border" href="<?=$activity[$n]['item']->full_path?>">"<?=$title?>"</a> - <?=date('F j, Y g:i A',strtotime($activity[$n]['item']->info['date_created']))?> PDT</p>
							<?
						}else if($activity[$n]['type']=='vote'){

							$value = $activity[$n]['item']['value']==1?'liked':'disliked';

							$item = null;

							if($activity[$n]['item']['item_type']=='question'){
								$item = new qa_question($activity[$n]['item']['item_id']);
							}else if($activity[$n]['item']['item_type']=='answer'){
								$item = new qa_answer($activity[$n]['item']['item_id']);
								$item->get_question();
							}
							
							$title = make_title($item->info['title'], $item->info['details']);
							$full_path = $item->full_path;

							?>					
								<p><a class="border" href="/<?=$GLOBALS['server_info']['qa_root']?>/profiles/<?=$user->info['user_name']?>"><?=$user->info['user_name']?></a> <?=$value?> <a class="border" href="<?=$full_path?>">"<?=$title?>"</a> - <?=date('F j, Y g:i A',strtotime($activity[$n]['item']['date_created']))?> PDT</p>
							<?
						}

					?>
					<? } ?>
				</div>
				<div style="text-align:right;">
					<a href="#" class="lnk_expand">view more recent activity</a>
				</div>
			</div>
		<? }else{ ?>
			<div class="msg_no_results" style="display:block;">No recent activity.</div>
		<? }
	}
	
	function output_user_subscription($user,$index){	
		?>								
		<div class="question_item<? if($index==0){ echo(' first');} ?>" data-item-type="user" data-item-id="<?=$user->info['id']?>">
			<a href="/<?=$GLOBALS['server_info']['qa_root']?>/profiles/<?=$user->info['user_name']?>"><img class="avatar" src="/img/qa/<?=$user->info['avatar']?>" /></a>
			<div class="question_item_details">
				<a class="question_author_name" href="/<?=$GLOBALS['server_info']['qa_root']?>/profiles/<?=$user->info['user_name']?>"><?=$user->info['user_name']?></a> <br />
				<? if($user->info['website_url']!=''&&$user->info['website_name']!=''){ ?><a class="lnk_website" href="<?=$user->info['website_url']?>"><?=$user->info['website_name']?></a><? } ?>
			</div>
			<div class="unsubscribe"><img src="/img/btn_unsubscribe.gif" class="btn_unsubscribe" data-item-type="user" data-item-id="<?=$user->info['id']?>" /></div>
			<div class="clear"></div>
			<? output_user_activity($user); ?>
		</div>

		<?

	}
	
	function output_breadcrumb($ancestor_keys){
	
		$ancestor_keys = array_merge(array(
			array(
				'name'=>'home',
				'full_path'=>'/'
			),
			array(
				'name'=>'Gardening Questions And Answers',
				'full_path'=>'/' . $GLOBALS['server_info']['qa_root'] . '/'
			)
		),$ancestor_keys);	
	?>
		<div class="breadcrumb">
			<? for($i=0;$i<count($ancestor_keys);$i++){ ?>
				<? if($i<count($ancestor_keys)-1){ ?>
					<a href="<?=$ancestor_keys[$i]['full_path']?>"><?=$ancestor_keys[$i]['name']?></a> \\
				<? }else{ ?>
					<?=$ancestor_keys[$i]['name']?>
				<? } ?>
			<? } ?>
		</div>
	<?
	}
	
	function generate_pagination_html($total_pages,$total_results,$page_num = null,$max_pagination_links_to_display = null,$carryover_params = null){
		if(is_null($page_num)) $page_num = 1;
		if(is_null($max_pagination_links_to_display)) $max_pagination_links_to_display = 7;

		if(is_null($carryover_params)||$carryover_params==''){
			$carryover_params = '';
		}else{
			$carryover_params = $carryover_params . '&';
		}
		
		$leftmost_page = max($page_num-floor($max_pagination_links_to_display/2),1);
		$rightmost_page = min($leftmost_page+$max_pagination_links_to_display-1,$total_pages);
		$leftmost_page = max(1,$rightmost_page-$max_pagination_links_to_display+1);

		$pagination_html = array();

		$pagination_html[1] = '<a href="?'.$carryover_params. 'page=1"'. (($page_num==1)?' class="selected"':'').' data-page-num="1">1</a>';
		$num_pagination_links = 1;

		for($i=$leftmost_page;$i<$rightmost_page+1;$i++){
			if($i!=1){
				$pagination_html[$i] = '<a href="?'.$carryover_params.'page='.$i.'"' . (($page_num==$i)?' class="selected"':''). ' data-page-num="'.$i.'">'.$i.'</a>';
				$num_pagination_links++;
			}
		}

		$pagination_html[$total_pages] = '<a href="?'.$carryover_params.'page='.$total_pages.'"'. (($page_num==$total_pages)?' class="selected"':'') .' data-page-num="'.$total_pages.'">'.$total_pages.'</a>';
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
			$view_all_html = ($total_results<=$GLOBALS['view_all_max'])?'<a href="?'.$carryover_params.'view_all=1" style="background-color:transparent!important" class="lnk_view_all">view all</a>':'';
			$pagination_html = implode('',$pagination_html) . $view_all_html;
		}else{
			$pagination_html = '';
		}
		
		return $pagination_html;
	}

	
	function output_question_search_results($results,$version = null,$query = null){
		$questions = $results['results'];
		
		if(is_null($version)) $version = 'category';
		if(is_null($query)) $query = '';
		
		// DETERMINE PAGINATION INFO
		$total_pages = ceil(intval($results['total_results']) / $results['criteria']['results_per_page']);
		$page_num = ceil(($results['criteria']['offset_index'] + 1) / $results['criteria']['results_per_page']);
		
		if(count($questions)){
			$pagination_html = generate_pagination_html($total_pages,intval($results['total_results']),$page_num,7,$query);
		?>
			<div id="questions_inner">
				<div class="paging"><?=$pagination_html?></div>
				
				<div class="clear"></div>
				<div id="questions_items">
				<? for($i=0;$i<count($questions);$i++){
					$question_info = $questions[$i];
					
					$category = new qa_category($question_info['qa_question']->info['category_id']);

					$author_user_is_active = ($question_info['user_name']!='');

					if(is_null($category)||!isset($category->info['thumbnail'])||$category->info['thumbnail']==''){
						$thumbnail = '/img/qa/category-default.png';
					}else{
						$thumbnail = '/img/qa/' . $category->info['thumbnail'];
					}

					if(isset($question_info['search_excerpts'])){
						$title = $question_info['search_excerpts']['title'];
						if($title=='') $title = $question_info['search_excerpts']['title'];					
					}else{
						$title = make_title($question_info['qa_question']->info['title'],$question_info['qa_question']->info['details']);
					}
					
					//$title = html_sanitize($title); // REMOVED BECAUSE <strong>'s WERE SHOWING UP IN RESULTS
					
					$title = unescape_special_characters($title);
				?>
					<div class="question_item">
						<img class="avatar" src="<?=$thumbnail?>" />
						<div class="question_item_details">
							<? if($author_user_is_active){ ?>
								<a class="question_author_name" href="/<?=$GLOBALS['server_info']['qa_root']?>/profiles/<?=$question_info['qa_question']->info['author_user_name']?>"><?=$question_info['qa_question']->info['author_user_name']?></a> 
							<? }else{ ?>
								An inactive user 
							<? } ?>
							asked:<br />
							<span class="question heading"><a href="<?=$question_info['qa_question']->full_path?>"><?=$title?></a></span>
							Posted under <a class="question_category" href="<?=$category->full_path?>"><?=$category->info['name']?></a> <?=date('F j, Y g:i A',strtotime($question_info['qa_question']->info['date_created']))?> PDT - <?=(intval($question_info['qa_question']->info['num_answers'])==1?'1 answer':$question_info['qa_question']->info['num_answers'] . ' answers')?>
						</div>
						<div class="clear"></div>
					</div>
				<? } ?>
				</div>
				<br />
				<div class="paging"><?=$pagination_html?></div>
				<div class="clear"></div>
			</div>
		<? }else{
		
			switch($version){
				case 'category':
				?>
					<div class="msg_no_results">No questions have been posted yet.</div>
				<?
				break;
				case 'search results':
				?>
					<div class="msg_no_results">No matching questions were found.</div>
				<?
				break;
			}
		}
	}
	
	function output_question_search_box($prepopulated_query = null, $category_id = null){
		if(is_null($prepopulated_query)) $prepopulated_query = '';
		if(is_null($category_id)) $category_id = 0;
	?>
	
		<div id="qa_question">
			<br />
			<table style="width:360px;" class="module_wrapper plain">
						<tbody><tr>
							<td class="corner top_left"></td>
							<td class="top_center"></td>
							<td class="corner top_right"></td>
						</tr>
						<tr>
							<td class="side_left"></td>
							<td class="content">
								<h3 style="margin:3px 8px 10px;font-size:14px;font-weight:bold;">What's your question?</h3>
								<div style="padding-left:8px;">
									<style>
										.autocomplete_dropdown {
											background-color:#fff;
											border-radius:0px 0px 10px 10px;
											box-shadow:2px 2px 5px #ccc;
											padding:.5em 1em;
											width:300px;
											position:absolute;
											display:none;
											z-index:9999;
										}
										.autocomplete_results {
											margin:0px;
											padding:0px;
										}
										.autocomplete_results li {
											display:block;
											list-style-type:none;
											padding:0px;
											margin:.15em 0px;
										}
										#qa_search_container.autocomplete .autocomplete_dropdown {
											display:block;
										}
										#input_qa_query {
											width:320px;
										}
									</style>
									<div id="qa_search_container">
										<form action="/<?=$GLOBALS['server_info']['qa_root']?>/search/results/" id="form_qa_search" onsubmit="return validate_qa_search();">
											<input id="input_qa_query" name="q" value="<?=$prepopulated_query?>" autocomplete="off" />
											<div class="autocomplete_dropdown">
												<ul class="autocomplete_results"></ul>
												<div style="text-align:right;padding:.25em 0px;"><a href="javascript:void(0);" onclick="$('form_qa_search').submit();">See more matching questions &raquo;</a></div>
												<div style="padding-top:.5em;">
													<div style="font-size:12px;float:left;">Don't see your question listed? Ask it!</div>
													<span class="btn_green btn_green_dark_bg" style="display:block;width:80px;float:right;smargin-right:410px;">
														<img class="side_left side_left_dark_bg" src="/img/spacer.gif">
														<a href="/<?=$GLOBALS['server_info']['qa_root']?>/ask-a-question/<?=($category_id!=0?'?category_id='.$category_id:'')?>">go</a>
														<img src="/img/spacer.gif">
													</span>
													<div style="clear:both;"></div>
												</div>
											</div>
										</form>
										<div class="btn_green" style="width:130px;margin-top:.5em;font-size:12px;">
											<img src="/img/spacer.gif" class="side_left" /><a href="javascript:void(0);" onclick="if(validate_qa_search()) $('form_qa_search').submit();">ask question</a><img src="/img/spacer.gif" />
										</div>
									</div>
									<script>	
										var tmr_autocomplete;
										function qa_autocomplete(){
											var query = $('input_qa_query').value;
											new Ajax.Request('/qa-autocomplete.php', {
												method: 'get',
												parameters:'query='+query,
												cache:true,
												onComplete:function(transport){
													$$('#qa_search_container .autocomplete_results')[0].update();
													var json = transport.responseText.strip();
													if(json){
														var listings = transport.responseText.evalJSON();
														if(listings.length){
															var search_listings = $$('#qa_search_container .autocomplete_results')[0];
															search_listings.update();

															listings.each(function(listing){
																var li = new Element('li');
																var anchor = new Element('a');
																anchor.setAttribute('href',listing.url);

																anchor.onclick = function(){
																	$('qa_search_container').removeClassName('autocomplete');
																	$('input_qa_query').value = (anchor.textContent||anchor.innerText);
																}

																anchor.innerHTML = listing.title;
																li.appendChild(anchor);
																search_listings.appendChild(li);
															});

															$('qa_search_container').addClassName('autocomplete');
														}else{
															$('qa_search_container').removeClassName('autocomplete');
														}
													}
												}
											});
										}
										
										function validate_qa_search(){
											return !!$('input_qa_query').value;
										}
										
										Event.observe(window,'load',function(){
											$('input_qa_query').observe('keyup',function(){

												if(this.value.length>3){
													window.clearTimeout(tmr_autocomplete);
													tmr_autocomplete = window.setTimeout(qa_autocomplete,250);
												}else{
													$$('#qa_search_container .autocomplete_results')[0].update();
													$('qa_search_container').removeClassName('autocomplete');	
												}
											});
											$('input_qa_query').observe('blur',function(){
												window.setTimeout(function(){
													$('qa_search_container').removeClassName('autocomplete');
												},500);
											});
										});
									</script>
							</div>
							<div style="clear:both;"></div>
						</td>
						<td class="side_right"></td>
					</tr>
					<tr>
						<td class="corner bottom_left"></td>
						<td class="bottom_center"></td>
						<td class="corner bottom_right"></td>
					</tr>
		</tbody></table>
	</div>
		
	<?
	}
	
	function post_question($author_user_id,$category_id,$title,$details){
		if($title==''||$author_user_id==0) return false;
	
		$question = new qa_question();
		$question->info['is_active'] = '1';
		$question->info['author_user_id'] = $author_user_id;
		$question->info['category_id'] = $category_id;
		$question->info['title'] = $title;
		$question->info['details'] = $details;
		$question->info['date_created'] = date('Y-m-d H:i:s');
		$question->save();
		return $question->info['id'];
	}
	
	function generate_subscriber_html($subscriber_info){
		return '<div class="subscriber" data-user-name="'.$subscriber_info['user_name'].'" data-member-since="'.date('F j, Y g:i A',strtotime($subscriber_info['date_created'])).' PDT" data-website-name="'.$subscriber_info['website_name'].'" data-website-url="'.$subscriber_info['website_url'].'" data-total-questions="'.$subscriber_info['total_questions'].'" data-total-answers="'.$subscriber_info['total_answers'].'"><a href="/'.$GLOBALS['server_info']['qa_root'].'/profiles/'.$subscriber_info['user_name'].'"><img src="/img/qa/'.$subscriber_info['avatar'].'" alt="'.$subscriber_info['user_name'].'" /></a></div>';
	}
	
	
	function output_avatar_options(){
	?>
		<style>
			.avatar_option {
				padding:4px;
				cursor:pointer;
				border-radius:6px;
			}
			.avatar_option:hover, .avatar_option.selected {
				background-color:#93C027;
			}
		</style>
		<div style="padding-top:12px;bottom:0px;position:relative;text-align:center;">
			<div>
				<img src="/img/qa/avatar-female1.png" class="avatar_option" data-avatar="avatar-female1.png" />
				<img src="/img/qa/avatar-female2.png" class="avatar_option" data-avatar="avatar-female2.png" />
				<img src="/img/qa/avatar-female3.png" class="avatar_option" data-avatar="avatar-female3.png" />
				<img src="/img/qa/avatar-female4.png" class="avatar_option" data-avatar="avatar-female4.png" />
				<div style="clear:both;"></div>
			</div>
			<div>
				<img src="/img/qa/avatar-male1.png" class="avatar_option" data-avatar="avatar-male1.png" />
				<img src="/img/qa/avatar-male2.png" class="avatar_option" data-avatar="avatar-male2.png" />
				<img src="/img/qa/avatar-male3.png" class="avatar_option" data-avatar="avatar-male3.png" />
				<img src="/img/qa/avatar-male4.png" class="avatar_option" data-avatar="avatar-male4.png" />
				<div style="clear:both;"></div>
			</div>
			<div>
				<img src="/img/qa/avatar-object1.png" class="avatar_option" data-avatar="avatar-object1.png" />
				<img src="/img/qa/avatar-object2.png" class="avatar_option" data-avatar="avatar-object2.png" />
				<img src="/img/qa/avatar-object3.png" class="avatar_option" data-avatar="avatar-object3.png" />
				<img src="/img/qa/avatar-object4.png" class="avatar_option" data-avatar="avatar-object4.png" />
				<div style="clear:both;"></div>
			</div>
		</div>
	<?
	}
	
?>