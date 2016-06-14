<? require_once($_SERVER['DOCUMENT_ROOT'].'/monrovia_admin/inc/header.php'); ?>
<? $monrovia_user->permission_requirement('cmgt'); ?>
<? $monrovia_user->permission_requirement('qamd'); ?>
<? require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_monrovia_event.php'); ?>

<?
	$criteria = array(
		'status'=>isset($_GET['status'])?','. $_GET['status'] .',':'',
		'type'=>isset($_GET['type'])?','. $_GET['type'] .',':'',
		'date_before'=>isset($_GET['date_before'])?$_GET['date_before']:'',
		'date_after'=>isset($_GET['date_after'])?$_GET['date_after']:'',
		'flagging'=>isset($_GET['flagging'])?','. $_GET['flagging'] .',':'',
		'up_votes'=>isset($_GET['up_votes'])?','. $_GET['up_votes'] .',':'',
		'down_votes'=>isset($_GET['down_votes'])?','. $_GET['down_votes'] .',':'',
		'author'=>isset($_GET['author'])?$_GET['author']:'',
		'keywords'=>isset($_GET['keywords'])?$_GET['keywords']:''
	);
	
    function output_modals(){
    ?>
        <div class="modal_dialog" id="modal_edit">
            <table class="modal_dialog_backing">
                <tr>
                    <td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
                </tr>
                <tr>
                    <td class="corner corner_left"></td>
                    <td class="corner corner_middle" style="width:500px;">
                    	<strong>Title:</strong>
                        <input id="txt_title" class="full_width" /><br /><br />
                        <strong>Details:</strong>
                        <textarea id="txt_details" class="full_width"></textarea>
                    	<div id="lbl_author" style="padding-top:1em;font-size:.85em;">Author</div>
                        <hr noshade size="1" color="#ccc" />
                        <div id="view_question"><a href="#" target="_blank" id="lnk_view_question">View question page</a></div>
                        <div id="in_response_to">In response to <a href="#" target="_blank" id="lnk_in_response_to"></a>.</div>
                        <hr noshade size="1" color="#ccc" />
                        <div style="margin-top:6px;">
                            <div class="checkbox_group">
                                <div style="float:left;"><input type="checkbox" id="edit_is_active" /><label for="edit_is_active" class="field_label">Active</label></div>
                                <div style="clear:both;"></div>
                            </div>
                            <div style="padding-bottom:.25em;" id="category_dropdown_container">
                            	<div>Category: <span id="lbl_category_breadcrumb_backend"></span></div>
								<div id="category_dropdowns"></div>
                            	<input id="effective_category_id" style="display:none;" />
                            </div>
                            <div style="float:left;">
								<input type="button" value="Save Changes" onclick="perform_save();" />
								<input type="button" value="Cancel" onclick="modal_hide();" />
							</div>
                            <input type="button" value="Delete" onclick="confirm_delete();" style="float:right;" />
                            <div style="clear:both;"></div>
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

		function append_to_head(){
		?>
			<script src="/js/qa_general.js?r=1"></script>
			<script>
				monrovia.config.qa_root = '<?=$GLOBALS['server_info']['qa_root']?>';
			</script>
		<?
	}
	?>

	<style>
		#page_content {
			position:absolute;
		}
		.field_label {
			vertical-align:top;
		}
		
		.field_stack {
			float:left;
			padding-top:.25em;
		}
		
		.field {
			padding-bottom:.25em;
		}
		
		#table_results {
			border-collapse:collapse;
			font-size:12px;
			width:100%;
		}
		
		#table_results thead {
			background-color:#f6f6f6;
			font-weight:bold;
		}
		
		#table_results td {
			padding:.5em;
		}
		
		.paging {
			text-align:right;
			font-size:14px;
			padding-bottom:.5em;
		}
		
		.paging a {
			text-decoration:none;
			border:1px solid transparent;
			padding:4px;
		}
		
		.paging a.selected {
			background-color:#f6f6f6;
			border-color:#ddd;
		}
		
		.lbl_up_votes {
			color:#0a0;
		}

		.lbl_down_votes {
			color:#a00;
		}

		.col_flagged, .lbl_up_votes, .lbl_down_votes {
			font-weight:bold;
			text-align:center;
		}
		
		#sample_result {
			display:none;
		}
		
		#category_dropdowns {
			margin-top:.25em;
		}
		
		#category_dropdowns select {
			display:block;
		}
		
		#modal_edit {
			font-size:13px;
		}
		
		#txt_details {
			font-size:13px;
			height:20em;
		}
		
	</style>
	
	<script>
	
		var ajax_request_id;
		var last_search_params;
	
		function initiate_search(){
			var params = {
				'status':'',
				'type':'',
				'date_before':'',
				'date_after':'',
				'flagging':'',
				'up_votes':'',
				'down_votes':'',
				'author':'',
				'keywords':''
			}
			
			// STATUS
			if($('chk_active').checked) params.status += (params.status?',':'') + '1';
			if($('chk_inactive').checked) params.status += (params.status?',':'') + '0';

			// TYPE
			if($('chk_question').checked) params.type += (params.type?',':'') + 'question';
			if($('chk_answer').checked) params.type += (params.type?',':'') + 'answer';
			
			// DATE
			params.date_before = $('date_before').value;
			params.date_after = $('date_after').value;
			
			// FLAGGING
			if($('chk_flagged').checked) params.flagging += (params.flagging?',':'') + '1';
			if($('chk_not_flagged').checked) params.flagging += (params.flagging?',':'') + '0';

			// UP VOTES
			if($('chk_up_votes').checked) params.up_votes += (params.up_votes?',':'') + '1';
			if($('chk_no_up_votes').checked) params.up_votes += (params.up_votes?',':'') + '0';

			// DOWN VOTES
			if($('chk_down_votes').checked) params.down_votes += (params.down_votes?',':'') + '1';
			if($('chk_no_down_votes').checked) params.down_votes += (params.down_votes?',':'') + '0';
	
			// AUTHOR
			params.author = $('author').value;

			// KEYWORDS
			params.keywords = $('keywords').value;
			
			last_search_params = Object.toQueryString(params);
			
			perform_search(last_search_params);

		}
		
		function perform_search(params){
			var this_ajax_request_id = ajax_request_id = Math.random();
			
			$('table_results').style.visibility = 'hidden';
			
			new Ajax.Request('query_qa.php',{
				method:'get',parameters:params,
				onComplete:function(transport){
					if(this_ajax_request_id!=ajax_request_id){
						return;
					}else{
						ajax_request_id = null;
					}
					display_results(transport.responseText.evalJSON());
				}
			});
		}
		
		function display_results(results){
			// OUTPUT PAGINATION
			$$('.paging').each(function(elt){
				elt.update(results.pagination_html||'');
			});

			$$('#table_results tbody')[0].update();

			if(results.success===true){

				if(results.results&&results.results.length){
					for(var i=0;i<results.results.length;i++){
						var result = results.results[i];

						var tr = $$('#sample_result tr')[0].cloneNode(true);

						tr.down('.col_status').update(result['is_active']=='1'?'Active':'Inactive');
						tr.down('.col_type').update(result['type']);
						tr.down('.col_desc').update('<a href="javascript:void(0);" onclick="launch_edit(&quot;'+result['type'].toLowerCase()+'&quot;,'+result['id']+');">'+result['description']+'</a>');
						tr.down('.col_author').update(result['author_user_name']);
						tr.down('.col_category').update(result['category_name']);
						tr.down('.col_date').update(result['date_created']);
						tr.down('.col_flagged').update(result['times_flagged']);
						tr.down('.lbl_up_votes').update(result['up_votes']);
						tr.down('.lbl_down_votes').update(result['down_votes']);

						tr.plant = result;

						$$('#table_results tbody')[0].appendChild(tr);
					};

					$('table_results').style.visibility = 'visible';
					$('results_msg').style.display = 'none';

					var pagination_items = $$('#results_column .paging a');
					pagination_items.each(function(item){
						item.observe('click',function(evt){
							evt.preventDefault();
							window.setTimeout(function(){
								perform_search(last_search_params + '&page=' + item.getAttribute('data-page-num'));				
							},1);
						});
					});

				}else{
					$('results_msg').style.display = 'block';
					$('results_msg').update('No results.');
				}
			}else{
				$('results_msg').style.display = 'block';
				$('results_msg').update('No results.');
				
				switch(results.field){
					case 'author':
						$('results_msg').update('No user could be found with the name "' + $('author').value + '"');
					break;
				}
			}

			$('results_column').style.backgroundImage = 'none';
		}

		function launch_edit(record_type,record_id){
			var this_ajax_request_id = ajax_request_id = Math.random();
			
			modal_hide();
			
			new Ajax.Request('qa_action.php',{
				method:'get',parameters:{
					'action':'get',
					'type':record_type,
					'id':record_id
				},
				onComplete:function(transport){
					if(this_ajax_request_id!=ajax_request_id){
						return;
					}else{
						ajax_request_id = null;
					}
					populate_edit_modal(record_type,transport.responseText.evalJSON());
				}
			});

		}
		
		function perform_deletion(record_type,record_id){
			new Ajax.Request('qa_action.php',{
				method:'get',parameters:{
					'action':'delete',
					'type':record_type,
					'id':record_id
				},
				onComplete:function(){
					modal_hide();
					perform_search(last_search_params);
				}
			});
			
			// SUCCESS ASSUMED
		}
		
		function populate_edit_modal(record_type,data){
		
			monrovia.runtime_data.record_type = record_type;
			monrovia.runtime_data.record_id = data.id;
		
			// CHEAP WAY TO TURN ENTITIES BACK TO SPECIAL CHARACTERS
			$('txt_details').update(data.title);
			window.setTimeout(function(){
				$('txt_title').value = $('txt_details').innerText||$('txt_details').textContent;
				window.setTimeout(function(){
					$('txt_details').update(replace_all(data.details||'','\n','<br />'));
				},1);			
			},1);
			
			if(data.author_info){
				$('lbl_author').update('Author: <a href="/' + monrovia.config.qa_root + '/profiles/' + data.author_info.user_name + '" target="_blank">' + data.author_info.user_name + '</a> (<a href="mailto:'+ data.author_info.email_address +'">' + data.author_info.email_address + '</a>)');			
			}else{
				$('lbl_author').update();			
			}
						
			if(record_type=='question'){
				$('in_response_to').style.display = 'none';
				$('view_question').style.display = 'block';				
				$('category_dropdown_container').style.display = 'block';
				load_category_dropdowns(data.category_id)
				$('lnk_view_question').setAttribute('href',data.full_path);
			}else{
				$('in_response_to').style.display = 'block';
				$('view_question').style.display = 'none';
				$('category_dropdown_container').style.display = 'none';
				$('lnk_in_response_to').update(data.question.title);
				$('lnk_in_response_to').setAttribute('href',data.question.full_path);
			}
			
			$('edit_is_active').checked = (data.is_active=='1');
			modal_show({'modal_id':'modal_edit','effect':'fade'});

		}

		function confirm_delete(){
			if(monrovia.runtime_data.record_type&&monrovia.runtime_data.record_id){
				if(confirm('Are you sure you want to permanently delete this '+monrovia.runtime_data.record_type+'? Click OK to delete it.')){
					perform_deletion(monrovia.runtime_data.record_type,monrovia.runtime_data.record_id);
					modal_hide();
				}
			}
		}
		
		function perform_save(){
			if(monrovia.runtime_data.record_type&&monrovia.runtime_data.record_id){			
				var data = {
					'action':'save',
					'type':monrovia.runtime_data.record_type,
					'id':monrovia.runtime_data.record_id,
					'is_active':$('edit_is_active').checked?'1':'0',
					'title':htmlentities(replace_special_characters($('txt_title').value||'')),
					'details':htmlentities(replace_special_characters($('txt_details').value||''))
				}
				if(monrovia.runtime_data.record_type=='question') data['category_id'] = $('effective_category_id').value;
				
				// SAVE
				new Ajax.Request('qa_action.php',{
					method:'get',parameters:data,
					onComplete:function(){
						modal_hide();
						perform_search(last_search_params);
					}
				});

			}
		}

		Event.observe(window,'load',initiate_search);

	</script>

	<h2>Q&amp;A</h2>

	<table id="sample_result">
		<tr>
			<td class="col_status"></td>
			<td class="col_type"></td>
			<td class="col_desc"></td>
			<td class="col_category"></td>
			<td class="col_author"></td>
			<td class="col_date"></td>
			<td class="col_flagged"></td>
			<td class="col_votes"><span class="lbl_up_votes"></span> / <span class="lbl_down_votes"></span></td>
		</tr>
	</table>

	<div id="page_content">
		<div style="margin-top:4px;">
		
			<div class="field_group" style="width:850px;">
			
				<div style="float:left;width:100px;">
					<div style="font-weight:bold;">Statuses</div>
					<div class="field_stack">
						<div class="field">
							<input type="checkbox" <?=strpos(','.$criteria['status'].',',',1,')!==false?'checked':''?> class="checkbox" id="chk_active" /><label class="field_label" for="chk_active">Active</label>
						</div>
						<div class="field">
							<input type="checkbox" <?=strpos(','.$criteria['status'].',',',0,')!==false?'checked':''?> class="checkbox" id="chk_inactive" /><label class="field_label" for="chk_inactive">Inactive</label>
						</div>
					</div>
				</div>
				
				<div style="float:left;width:175px;">
					<div style="font-weight:bold;">Types</div>
					<div class="field_stack">
						<div class="field">
							<input type="checkbox" <?=strpos(','.$criteria['type'].',',',question,')!==false?'checked':''?> class="checkbox" id="chk_question" /><label class="field_label" for="chk_question">Questions</label>
						</div>
						<div class="field">
							<input type="checkbox" <?=strpos(','.$criteria['type'].',',',answer,')!==false?'checked':''?> class="checkbox" id="chk_answer" /><label class="field_label" for="chk_answer">Answers</label>
						</div>
					</div>
				</div>
				
				<div style="float:left;">
					<div style="font-weight:bold;">Dates Posted</div>
					<div class="field_stack">
						<div class="field">
							<label class="field_label" style="width:50px;display:block;float:left;text-align:left;">Before</label>
							<input id="date_before" class="text_field" value="<?=$criteria['date_before']?>" maxlength="10" placeholder="yyyy-mm-dd" style="float:left;" />
							<div style="clear:both;"></div>
						</div>
						<div class="field">
							<label class="field_label" style="width:50px;display:block;float:left;text-align:left;">After</label>
							<input id="date_after" class="text_field" value="<?=$criteria['date_after']?>" maxlength="10" placeholder="yyyy-mm-dd" />
							<div style="clear:both;"></div>
						</div>
					</div>
				</div>
				
				<div style="clear:both;height:1em;"></div>
				
				<div style="float:left;width:100px;">
					<div style="font-weight:bold;">Flagging</div>
					<div class="field_stack">
						<div class="field">
							<input type="checkbox" <?=strpos(','.$criteria['flagging'].',',',1,')!==false?'checked':''?> class="checkbox" id="chk_flagged" /><label class="field_label" for="chk_flagged">Flagged</label>
						</div>
						<div class="field">
							<input type="checkbox" <?=strpos(','.$criteria['flagging'].',',',0,')!==false?'checked':''?> class="checkbox" id="chk_not_flagged" /><label class="field_label" for="chk_not_flagged">Not flagged</label>
						</div>
					</div>
				</div>
				
				<div style="float:left;width:175px;">
					<div style="font-weight:bold;">Up Votes</div>
					<div class="field_stack">
						<div class="field">
							<input type="checkbox" <?=strpos(','.$criteria['up_votes'].',',',1,')!==false?'checked':''?> class="checkbox" id="chk_up_votes" /><label class="field_label" for="chk_up_votes">Has up votes</label>
						</div>
						<div class="field">
							<input type="checkbox" <?=strpos(','.$criteria['up_votes'].',',',0,')!==false?'checked':''?> class="checkbox" id="chk_no_up_votes" /><label class="field_label" for="chk_no_up_votes">Does not have up votes</label>
						</div>
					</div>
				</div>
				
				<div style="float:left;">
					<div style="font-weight:bold;">Down Votes</div>
					<div class="field_stack">
						<div class="field">
							<input type="checkbox" <?=strpos(','.$criteria['down_votes'].',',',1,')!==false?'checked':''?> class="checkbox" id="chk_down_votes" /><label class="field_label" for="chk_down_votes">Has down votes</label>
						</div>
						<div class="field">
							<input type="checkbox" <?=strpos(','.$criteria['down_votes'].',',',0,')!==false?'checked':''?> class="checkbox" id="chk_no_down_votes" /><label class="field_label" for="chk_no_down_votes">Does not have down votes</label>
						</div>
					</div>
				</div>
				
				<div style="clear:both;height:1em;"></div>
	
				<div style="float:left;width:275px;">
					<div style="font-weight:bold;padding-bottom:.25em;">Author</div>
					<input id="author" class="text_field" value="<?=$criteria['author']?>" maxlength="40" />
				</div>
				
				<div style="float:left;">
					<div style="font-weight:bold;padding-bottom:.25em;">Keywords</div>
					<input id="keywords" class="text_field" value="<?=$criteria['keywords']?>" maxlength="40" />
				</div>
	
				<div style="clear:both;height:1em;"></div>
				
				<input type="button" value="Search" onclick="initiate_search( );" />
	
			</div>
		
			<div style="padding-top:1em;" id="results_column">
				<div id="results_msg"></div>
				<div class="paging"></div>
				<table id="table_results">
					<thead>
						<tr>
							<td width="45">Status</td>
							<td width="50">Type</td>
							<td>Title</td>
							<td width="150">Category</td>
							<td width="100">Author</td>
							<td width="80">Date Posted</td>
							<td width="50">Flagged</td>
							<td width="50">Votes</td>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
				<div class="paging"></div>
			</div>
			
        </div>
	</div>
	<script>

	</script>
<? include('inc/footer.php'); ?>