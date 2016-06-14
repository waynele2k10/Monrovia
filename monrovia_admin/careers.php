<? include('inc/header.php'); ?>
<? $monrovia_user->permission_requirement('cmgt'); ?>
<? $monrovia_user->permission_requirement('hres'); ?>
<?
	$location_id = $_GET['location_id'];
	$location = $office_locations[$location_id];
	if($location==''){
		@header('location:./');
		?>
		Invalid office location specified.
		<script>window.location = './';</script>
		<?
		exit;
	}

	if(isset($_POST['action'])&&$_POST['action']=='save'){
		$raw_json = $_POST['data'];
		if(get_magic_quotes_gpc()) $raw_json = stripslashes($raw_json);
		$ret = (save_listing_data(json_parse($raw_json),$location_id));

		if($ret){
			output_page_notice('Your changes have been saved.');
		}else{
			output_page_notice('ERROR: An error occurred and your changes may not have been saved.');
		}

		// TRIGGER AZLINK SYNCH
		get_url('https://azlink.monrovia.com/employment/inc/synch.php');

	}
	function save_listing_data($data,$location_id){
		// MAY NOT SAVE IF UNICODE CHARACTERS PRESENT
		$num_errors = 0;
		for($i=0;$i<count($data);$i++){
			$listing = new job_listing($data[$i]->id);
			if($data[$i]->id!=$listing->info['id']){
				$num_errors++; // THIS MAY OCCUR WHEN RECORD DOESN'T EXIST
			}else{
				if($data[$i]->is_delete=='1'){
					$num_errors += ($listing->delete())?0:1;
				}else{
					if($data[$i]->title==''||$data[$i]->html==''){
						$num_errors++;
					}else{
						$listing->info['is_active'] = $data[$i]->is_active;
						$listing->info['title'] = wysiwyg_strip_tags($data[$i]->title);
						$listing->info['location_id'] = $location_id;
						$listing->info['ordinal'] = $i;
						$listing->info['html'] = wysiwyg_strip_tags($data[$i]->html);
						$num_errors += ($listing->save())?0:1;
					}
				}
			}
		}
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
			$value->html = replace_smart_characters($value->html);
			$value->title = replace_smart_characters($value->title);
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
				width:700px;
			}
			#listing_editor {
				width:800px;
				border:2px outset #eee;
				background-color:#eee;
				padding:2px;
				display:none;
			}
			#listing_editor_title_bar {
				font-weight:bold;
				font-size:9pt;
				margin-bottom:4px;
				background-color:#cfcfcf;
				padding:2px;
			}
			#listing_editor_title {
				width:400px;
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

			function page_validate(){
				get_field('data').value = listings_serialize();

				// SET "SAVE" FLAG ONLY IF THIS GETS EXECUTED, WHICH SHOULDN'T HAPPEN IF A JS ERROR OCCURS
				get_field('action').value = 'save';
				return true;
			}
			function listings_serialize(){
				var ret = [];
				$$('#lst_listings li').each(function(li){
					var html = remove_newlines(li.getAttribute('html'));
					var title = remove_newlines(li.down('span.title').textContent||li.down('span.title').innerText);
					var id = (li.id.indexOf('_new_')==-1)?li.id.replace('item_',''):'';
					var is_active = (li.hasClassName('inactive'))?'0':'1';
					var is_delete = (li.hasClassName('deleted'))?'1':'0';
					var item = new listing(id,title,html,is_active,is_delete);
					ret.push(item);
				});
				ret = Object.toJSON(ret);
				ret = replace_special_characters(ret);
				ret = replace_all(ret,'\\\"','{ESCAPED_QUOTE}');
				ret = replace_all(ret,'{ESCAPED_QUOTE}','\\\\\\\"');
				return ret;
			}
			function listing(id,title,html,is_active,is_delete){
				this.id = id;
				this.title = title;
				this.html = html;
				this.is_active = is_active;
				this.is_delete = is_delete;
			}

			function editor_toggle(show){
				if(show){
					$('page_content').fade({duration:.25});
					window.setTimeout(function(){$('listing_editor').style.display='block';},250);
				}else{
					$('page_content').appear({duration:.25});
					$('listing_editor').style.display = 'none';
				}
			}
			function launch_editor(elt){
				editor_toggle(true);
				if(elt){
					// EDIT
					var li = elt.up('li');
					$('listing_editor').current_li_id = li.id;
					var html = (li.html||li.getAttribute('html'));
					FCKeditorAPI.GetInstance('richtext_editor').SetHTML(html);
					$('listing_editor_title').value = (li.down('span.title').textContent||li.down('span.title').innerText);
				}else{
					// NEW
					$('listing_editor').current_li_id = '';
					FCKeditorAPI.GetInstance('richtext_editor').SetHTML('');
					$('listing_editor_title').value = '';
				}
				$('listing_editor_title_bar').update((elt)?'Edit Job Listing':'Add A New Job Listing');
				//editor_toggle(true);
				window.setTimeout(function(){
					try{
						$('listing_editor_title').focus();
						$('listing_editor_title').select();
					}catch(err){}
				},100);
			}
			function update_listing(){
				var li;
				if($('listing_editor').current_li_id) li = $($('listing_editor').current_li_id);

				var new_html = replace_special_characters(FCKeditorAPI.GetInstance('richtext_editor').GetHTML().strip());
				var new_title = replace_special_characters($('listing_editor_title').value.strip()).stripTags();

				if(new_title.empty()){
					alert('Please specify a title for this listing.');
					return;
				}
				if(new_html.empty()){
					alert('Please provide a description for this listing.');
					return;
				}
				if(!li){
					// NEW ITEM
					if($('no_listings')) $('no_listings').hide();
					var list = $('lst_listings');
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
				li.down('span.title').textContent = li.down('span.title').innerText = new_title;
				li.html = new_html;
				// TO MAKE THINGS EASIER TO DEBUG WITH FIREBUG, WE'LL UPDATE THE HTML ATTRIBUTE AS WELL
				li.setAttribute('html',new_html);
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
	<form id="form_listings" action="" onsubmit="return page_validate();" onkeypress="return cancel_enter_key(window.event||event);" method="post">
		<h2 id="page_subheader">Job Listings - <?=$location->name?></h2>
		<div id="page_content">
			<div style="margin-top:4px;">
				<input type="submit" value="Save Changes" />
				<input type="button" value="Cancel" onclick="page_cancel();" />
			</div>
			<hr />
			<input type="button" value="Add a new job listing" onclick="launch_editor();" />
			<ul id="lst_listings" class="sortable_list">
				<? cms_output_job_listings($location_id); ?>
			</ul>
		</div>
		<div id="listing_editor">
			<div id="listing_editor_title_bar">Edit Job Listing</div>
			<div style="padding:0px 0px 4px 4px;">
				<label class="field_label">Title:</label>
				<input class="text_field" id="listing_editor_title" />
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
				<input type="button" value="OK" onclick="update_listing();" />
				<input type="button" value="Cancel" onclick="editor_toggle(false);" />
			</div>
		</div>
		<input type="hidden" name="action" value="" />
		<input type="hidden" name="location_id" value="<?=$location_id?>" />
		<input type="hidden" name="data" value="" />
	</form>


<? include('inc/footer.php'); ?>