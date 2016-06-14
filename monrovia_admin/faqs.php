<?
	$audience = 'consumer';
	if(isset($_GET['audience'])&&$_GET['audience']=='retailer') $audience = 'retailer';
?>
<? include('inc/header.php'); ?>
<? $monrovia_user->permission_requirement('cmgt'); ?>
<? $monrovia_user->permission_requirement('html'); ?>
<? require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_faqs.php'); ?>
<?
	if(isset($_POST['action'])&&$_POST['action']=='save'){
		// ITERATE CATEGORIES
		$ret = true;
		$keys = array_keys($_POST['data']);
		for($i=0;$i<count($keys);$i++){
			$category_id = str_replace('category_','',$keys[$i]);
			$raw_json = $_POST['data'][$keys[$i]];
			if(get_magic_quotes_gpc()) $raw_json = stripslashes($raw_json);
			$ret = ($ret&&save_faq_data(json_parse($raw_json),$category_id));
		}
		if($ret){
			output_page_notice('Your changes have been saved.');
		}else{
			output_page_notice('ERROR: An error occurred and your changes may not have been saved.');
		}
	}
	function save_faq_data($data,$category_id){
		// MAY NOT SAVE IF UNICODE CHARACTERS PRESENT
		$num_errors = 0;
		for($i=0;$i<count($data);$i++){
			$faq = new faq_item($data[$i]->id);
			if($data[$i]->id!=$faq->info['id']){
				$num_errors++; // THIS MAY OCCUR WHEN RECORD DOESN'T EXIST
			}else{
				if($data[$i]->is_delete=='1'){
					$num_errors += ($faq->delete())?0:1;
				}else{
					if($data[$i]->question==''||$data[$i]->answer==''){
						$num_errors++;
					}else{
						//var_dump($data[$i]);exit;
						$faq->info['is_active'] = $data[$i]->is_active;
						$faq->info['question'] = wysiwyg_strip_tags($data[$i]->question);
						$faq->info['faq_category_id'] = $category_id;
						$faq->info['ordinal'] = $i;
						$faq->info['answer'] = wysiwyg_strip_tags($data[$i]->answer);
						$num_errors += ($faq->save())?0:1;
					}
				}
			}
		}

		// CLEAR CACHE FILES (IF ONES EXIST)
		$cache = new cache('home :: about us :: faqs');
		if($cache->exists) $cache->remove();
		$cache = new cache('home :: retail :: faqs');
		if($cache->exists) $cache->remove();

		return ($num_errors==0);
	}
	function json_parse($raw){
		$ret = array();
		$raw = str_replace('\\\\','\\',$raw);
		$raw = str_replace('[{','',$raw);
		$raw = str_replace('}]','',$raw);
		$raw_json = explode('},{',$raw);
		for($i=0;$i<count($raw_json);$i++){
			$value = stripslashes($raw_json[$i]);
			$value = from_json('{'.$value.'}');
			$value->answer = replace_smart_characters($value->answer);
			$value->question = replace_smart_characters($value->question);
			$ret[] = ($value);
		}
		return $ret;
	}
	function append_to_head(){
		global $audience;
		?>
		<link href="/inc/packer.php?path=/monrovia_admin/css/sortable_list.css" rel="stylesheet" type="text/css" />
		<style>
			#page_content {
				position:absolute;
			}
			#ctlTabs {
				margin-top:8px;
			}
			#faq_editor {
				width:800px;
				border:2px outset #eee;
				background-color:#eee;
				padding:2px;
				display:none;
			}
			#faq_editor_title_bar {
				font-weight:bold;
				font-size:9pt;
				margin-bottom:4px;
				background-color:#cfcfcf;
				padding:2px;
			}
			#faq_editor_question {
				width:400px;
			}
			.slimTabBlurb {
				padding-top:8px;
			}
			.sortable_list {
				margin-top:8px;
			}
		</style>
		<script type="text/javascript" src="/fckeditor/fckeditor.js"></script>
		<script type="text/javascript" src="/inc/packer.php?path=/monrovia_admin/js/sortable_list.js"></script>
		<script type="text/javascript">
			var num_new_items = 0;
			var data_changed = false;
			function set_audience(){
				if((data_changed)?confirm('Are you sure you want to navigate away? Your changes will be lost.'):true){
					window.location = '?audience='+$('audience').value;
				}else{
					$('audience').value = '<?=$audience?>';
				}
			}
			function page_validate(){
				$$('.sortable_list').each(function(list){
					var field = new Element('input');
					field.type = 'hidden';
					field.name = 'data[category_'+list.getAttribute('category_id')+']';
					field.value = faqs_serialize(list.id);
					if(field.value!='[]') $('form_faqs').appendChild(field);
				});
				// SET "SAVE" FLAG ONLY IF THIS GETS EXECUTED, WHICH SHOULDN'T HAPPEN IF A JS ERROR OCCURS
				get_field('action').value = 'save';
				return true;
			}
			function faqs_serialize(id){
				var ret = [];
				$(id).select('li').each(function(li){
					var answer = remove_newlines(li.getAttribute('answer'));
					var question = remove_newlines(li.down('span.title').textContent||li.down('span.title').innerText);
					var id = (li.id.indexOf('_new_')==-1)?li.id.replace('item_',''):'';
					var is_active = (li.hasClassName('inactive'))?'0':'1';
					var is_delete = (li.hasClassName('deleted'))?'1':'0';
					var item = new faq_item(id,question,answer,is_active,is_delete);
					ret.push(item);
				});
				ret = Object.toJSON(ret);
				ret = replace_special_characters(ret);
				ret = replace_all(ret,'\\\"','{ESCAPED_QUOTE}');
				ret = replace_all(ret,'{ESCAPED_QUOTE}','\\\\\\\"');
				//alert(ret);
				return ret;
			}
			function faq_item(id,question,answer,is_active,is_delete){
				this.id = id;
				this.question = question;
				this.answer = answer;
				this.is_active = is_active;
				this.is_delete = is_delete;
			}

			function editor_toggle(show){
				if(show){
					$('page_content').fade({duration:.25});
					window.setTimeout(function(){$('faq_editor').style.display='block';},250);
				}else{
					$('page_content').appear({duration:.25});
					$('faq_editor').style.display = 'none';
				}
			}
			function launch_editor(elt){
				editor_toggle(true);
				if(elt){
					// EDIT
					var li = elt.up('li');
					$('faq_editor').current_li_id = li.id;
					var answer = (li.answer||li.getAttribute('answer'));
					FCKeditorAPI.GetInstance('richtext_editor').SetHTML(answer);
					$('faq_editor_question').value = (li.down('span.title').textContent||li.down('span.title').innerText);
				}else{
					// NEW
					$('faq_editor').current_li_id = '';
					FCKeditorAPI.GetInstance('richtext_editor').SetHTML('');
					$('faq_editor_question').value = '';
				}
				$('faq_editor_title_bar').update((elt)?'Edit FAQ':'Add A New FAQ');
				//editor_toggle(true);
				window.setTimeout(function(){
					try{
						$('faq_editor_question').focus();
						$('faq_editor_question').select();
					}catch(err){}
				},100);
			}
			function update_faq_item(){
				var li;
				if($('faq_editor').current_li_id) li = $($('faq_editor').current_li_id);

				var new_answer = replace_special_characters(FCKeditorAPI.GetInstance('richtext_editor').GetHTML().strip());
				var new_question = replace_special_characters($('faq_editor_question').value.strip()).stripTags();

				if(new_question.empty()){
					alert('Please specify a question for this item.');
					return;
				}
				if(new_answer.empty()){
					alert('Please specify an answer for this item.');
					return;
				}
				if(!li){
					// NEW ITEM
					var current_tab = $$('#ctlTabs div.slimTabBlurb[tab="' + slimTab_get_current('ctlTabs') + '"]')[0];
					var list = Element.down(current_tab,'ul');
					var li = new Element('li');
					li.id = 'item_new_'+num_new_items++;
					li.innerHTML = '<div class="control"><img class="make_active_inactive" title="Make Inactive" src="/img/spacer.gif"/><img class="rename" title="Edit" src="img/icon_pencil.png"/><img class="delete" title="Delete" src="img/icon_cross.png"/></div><span class="title"></span>';
					list.appendChild(li);
					li.highlight();
					init_row(li);
					Sortable.destroy(list.id);
					Sortable.create(list.id);
				}
				// POPULATE DATA
				li.down('span.title').textContent = li.down('span.title').innerText = new_question;
				li.answer = new_answer;
				// TO MAKE THINGS EASIER TO DEBUG WITH FIREBUG, WE'LL UPDATE THE HTML ATTRIBUTE AS WELL
				li.setAttribute('answer',new_answer);
				editor_toggle(false);
				data_changed = true;
			}
			function init_row(li){
				li.down('div.control img.rename').observe('click',function(){
					launch_editor(this);
				},true);
				li.down('div.control img.delete').observe('click',function(){
					var title = this.up('li').down('span');
					sortable_list_item_begin_delete(this,title.textContent||title.innerText);
				},true);
				li.down('div.control img.make_active_inactive').observe('click',function(){
					sortable_list_item_toggle_active(this);
				},true);
			}
			Event.observe(window,'load',function(){
				// INIT ROW CONTROLS
				$$('.sortable_list li').each(function(li){
					init_row(li);
				});
			});

		</script>

		<?
	}
?>
	<form id="form_faqs" action="" onsubmit="return page_validate();" onkeypress="return cancel_enter_key(window.event||event);" method="post">
		<h2 id="page_subheader"><?=ucwords($audience)?> FAQs</h2>
		<div id="page_content">
			<div style="margin-top:4px;">
				<input type="submit" value="Save Changes" />
				<input type="button" value="Cancel" onclick="page_cancel();" />
			</div>
			<div style="margin-top:8px;">
				<label class="field_label">Audience: </label>
				<select id="audience" class="text_field" onchange="set_audience();" _value="<?=$audience?>">
					<option value="consumer">Consumer</option>
					<option value="retailer">Retailer</option>
				</select>
			</div>
			<div class="slimTabContainer" id="ctlTabs">
				<? cms_output_tabs($audience); ?>
			</div>
		</div>
		<div id="faq_editor">
			<div id="faq_editor_title_bar">Edit FAQ</div>
			<div style="padding:0px 0px 4px 4px;">
				<label class="field_label">Question:</label>
				<input class="text_field" id="faq_editor_question" />
			</div>
			<script>
				var oFCKeditor = new FCKeditor('richtext_editor');
				oFCKeditor.BasePath = "/fckeditor/";
				oFCKeditor.Config["CustomConfigurationsPath"] = "/fckeditor/custom_minimal.js"  ;
				oFCKeditor.Height = '250';
				oFCKeditor.Value = '';
				oFCKeditor.Create();
			</script>
			<div style="padding-top:3px;text-align:center;">
				<input type="button" value="OK" onclick="update_faq_item();" />
				<input type="button" value="Cancel" onclick="editor_toggle(false);" />
			</div>
		</div>
		<input type="hidden" name="action" value="" />
	</form>


<? include('inc/footer.php'); ?>