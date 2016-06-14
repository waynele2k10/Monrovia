<? require_once($_SERVER['DOCUMENT_ROOT'].'/monrovia_admin/inc/header.php'); ?>
<? $monrovia_user->permission_requirement('cmgt'); ?>
<? $monrovia_user->permission_requirement('html'); ?>

	<?
		if(isset($_POST['action'])&&$_POST['action']=='save'){

			$data = json_parse($_POST['data']);

			$num_errors = 0;
			for($i=0;$i<count($data);$i++){
				$press_release = new press_release($data[$i]->id);
				if($data[$i]->id!=$press_release->info['id']){
					$num_errors++; // THIS MAY OCCUR WHEN RECORD DOESN'T EXIST
				}else{
					if(isset($data[$i]->is_delete)&&$data[$i]->is_delete=='1'){
						$num_errors += ($press_release->delete())?0:1;
					}else{
						$press_release->info['is_active'] = $data[$i]->is_active;
						$press_release->info['title'] = strip_tags($data[$i]->title);
						$press_release->info['release_date'] = $data[$i]->release_date;

						if($data[$i]->id==''){
							// NEW RECORD; CREATE A NEW EDITABLE MODULE RECORD FOR CONTENT
							$editable_module = new editable_module();;
							$editable_module->info['name'] = 'About Us :: Press Room :: ' . $press_release->info['release_date'] . ' ' . $press_release->info['title']; // NOTE: THIS NEVER GETS UPDATED
							$editable_module->save();
							$editable_module_id = $editable_module->info['id'];
							if($editable_module_id==''){
								// EDITABLE MODULE COULD NOT BE CREATED; DO NOT SAVE PRESS RELEASE
								$num_errors++;
							}else{
								// EDITABLE MODULE CREATED SUCCESSFULLY; CREATE NEW PRESS RELEASE
								$press_release->info['editable_module_id'] = $editable_module_id;
								$num_errors += ($press_release->save())?0:1;
							}
						}else{
							// EXISTING RECORD
							$num_errors += ($press_release->save())?0:1;
						}
					}
				}
			}

			if($num_errors==0){
				output_page_notice('Your changes have been saved.');
			}else{
				output_page_notice('ERROR: An error occurred and your changes may not have been saved.');
			}

			// CLEAR CACHE FILE (IF ONE EXISTS)
			$cache = new cache('home :: about us :: press room :: press releases');
			if($cache->exists) $cache->remove();

		}
		function json_parse($raw){
			$ret = array();
			$raw = str_replace('[{','',$raw);
			$raw = str_replace('}]','',$raw);
			$raw_json = explode('},{',$raw);
			for($i=0;$i<count($raw_json);$i++){
				$ret[] = from_json('{'.stripslashes($raw_json[$i]).'}');
			}
			return $ret;
		}

	?>
	<style>
		#table_press_releases {
			margin-top:8px;
			width:982px;
		}
		#table_press_releases td {
			font-size:9pt;
		}
		#table_press_releases {
			border-collapse:collapse;
		}
		#table_press_releases td {
			padding:4px 8px 4px 8px;
		}
		#press_release_editor {
			width:800px;
			border:2px outset #eee;
			background-color:#eee;
			padding:2px;
			display:none;
		}
		#press_release_editor_title_bar {
			font-weight:bold;
			font-size:9pt;
			margin-bottom:4px;
			background-color:#cfcfcf;
			padding:2px;
		}
		#page_content {
			position:absolute;
		}
		.field_label {
			float:right;
			whitespace:no-wrap;
		}
	</style>

	<script>
		var monrovia_press_releases = [<?cms_output_press_releases();?>];
		var num_new_press_releases = 0;
		function press_release(id,is_active,release_date,title){
			this.id = id;
			this.is_active = is_active;
			this.title = title;
			this.release_date = release_date;
		}

		function remove_press_release(id){
			for(var i=0;i<monrovia_press_releases.length;i++){
				if(monrovia_press_releases[i].id==id){
					monrovia_press_releases.splice(i,1);
					break;
				}
			}
			refresh_monrovia_press_releases_table_data();
		}
		function get_press_release(id){
			for(var i=0;i<monrovia_press_releases.length;i++){
				if(monrovia_press_releases[i].id==id){
					return monrovia_press_releases[i];
				}
			}
		}
		function get_press_release_index(id){
			for(var i=0;i<monrovia_press_releases.length;i++){
				if(monrovia_press_releases[i].id==id){
					return i;
				}
			}
		}
		function add_press_release(data){
			num_new_press_releases++;
			monrovia_press_releases.push(data);
			refresh_monrovia_press_releases_table_data();
		}
		function refresh_monrovia_press_releases_table_data(){
			var data = monrovia_press_releases;
			var parent_element = $('table_press_releases_contents');
			parent_element.update('');
			for(var i=0;i<data.length;i++){
				var tr = new Element('tr');
				if(i%2) tr.addClassName('row_even');
				tr.appendChild(new td((data[i].is_active=='1')?'Active':'Inactive'));
				tr.appendChild(new td(data[i].release_date));
				tr.appendChild(new td('<a href="javascript:launch_editor(\''+data[i].id+'\');void(0);">'+data[i].title+'</a>'));
				tr.appendChild(new td('<a href="javascript:view_press_release(\''+data[i].id+'\');void(0);">'+(!data[i].id.contains('new_')?'View':'')+'</a>'));
				tr.press_release_id = data[i].id;
				parent_element.appendChild(tr);
			}
		}
		function view_press_release(id){
			var press_release = get_press_release(id);
			var title = press_release.title.gsub(/[^a-zA-Z ]+/,'');
			title = title.gsub(/(\s)/,'_').toLowerCase();
			window.open('/about-us/press-releases/'+id+'/view.php');
		}
		function td(innerHTML){
			var ret = new Element('td');
			ret.innerHTML = innerHTML;
			return ret;
		}
		function update_press_release(){
			var press_release_id = $('press_release_editor').current_press_release_id;
			var new_title = replace_special_characters($('press_release_editor_title').value.strip());
			var new_is_active = replace_special_characters($('press_release_editor_status').value.strip());
			var new_release_date = replace_special_characters($('press_release_editor_release_date').value.strip());

			var new_press_release = new press_release(press_release_id,new_is_active,new_release_date,new_title);

			if(!$('form_editor').validate()) return;

			if(new_press_release.id){
				// UPDATE
				monrovia_press_releases[get_press_release_index(new_press_release.id)] = new_press_release;
				refresh_monrovia_press_releases_table_data();
			}else{
				// ADD
				new_press_release.id = 'new_'+(num_new_press_releases+1);
				add_press_release(new_press_release);
			}
			editor_toggle(false);
		}
		function launch_editor(press_release_id){
			if(press_release_id){
				// EDIT
				var press_release = get_press_release(press_release_id);
				$('press_release_editor').current_press_release_id = press_release.id;
				$('press_release_editor_title').value = press_release.title;
				$('press_release_editor_status').value = press_release.is_active;
				$('press_release_editor_release_date').value = press_release.release_date;

			}else{
				// NEW
				$('press_release_editor').current_press_release_id = '';
				$('form_editor').reset();
			}
			$('press_release_editor_title_bar').update((press_release_id)?'Edit Press Release':'Add A New Press Release');
			editor_toggle(true);
			(function(){
				window.setTimeout(function(){
					$('press_release_editor_title').focus;
					$('press_release_editor_title').select();
				},500);
			}).defer()
		}
		function editor_toggle(show){
			if(show){
				$('page_content').fade({duration:.25});
				window.setTimeout(function(){$('press_release_editor').style.display='block';},250);
			}else{
				$('page_content').appear({duration:.25});
				$('press_release_editor').style.display = 'none';
			}
		}

		function page_validate(){
			for(var i=0;i<monrovia_press_releases.length;i++){
				if(monrovia_press_releases[i].id.toString().contains('new_')) monrovia_press_releases[i].id = '';
			}
			get_field('data').value = Object.toJSON(monrovia_press_releases);
			// SET "SAVE" FLAG ONLY IF THIS GETS EXECUTED, WHICH SHOULDN'T HAPPEN IF A JS ERROR OCCURS
			get_field('action').value = 'save';
			return true;
		}

		Event.observe(window,'load',function(){
			refresh_monrovia_press_releases_table_data();

			// FORM CUSTOM VALIDATION
			/*
			$('press_release_editor_date_start').custom_validation = function(){
				alert('fds');
				return false;
			}
			*/
		});



	</script>

	<h2>Press Releases</h2>

	<div id="page_content">
		<div style="margin-top:4px;">
			<form id="form_events" action="" onsubmit="return page_validate();" method="post">
				<input type="hidden" name="data" />
				<input type="hidden" name="action" />
				<input type="submit" value="Save Changes" />
				<input type="button" value="Cancel" onclick="page_cancel();" />
			</form>
		</div>
		<hr />
		<input type="button" value="Add A New Press Release" onclick="launch_editor();" />
		<table id="table_press_releases">
			<thead>
				<tr style="background-color:#666;color:#fff;">
					<td width="1">Status</td>
					<td width="1">Date</td>
					<td>Title</td>
					<td width="1">View</td>
				</tr>
			</thead>
			<tbody id="table_press_releases_contents"></tbody>
		</table>
	</div>
	<div id="press_release_editor">
		<div id="press_release_editor_title_bar">Edit Press Release</div>
		<div style="padding:0px 0px 4px 4px;">
			<form id="form_editor" onsubmit="return return_false();" validation_enabled="true">
				<table>
					<tr>
						<td><label class="field_label">Title:</label></td>
						<td colspan="3"><input class="text_field" id="press_release_editor_title" style="width:515px;" validation_required="true" maxlength="255" /></td>
					</tr>
					<tr>
						<td><label class="field_label">Status:</label></td>
						<td><select class="text_field" id="press_release_editor_status"><option value="1">Active</option><option value="0">Inactive</option></select></td>
						<td><label class="field_label">Start Date:</label></td>
						<td><input class="text_field" id="press_release_editor_release_date" validation_type="mysql_date" validation_required="true" maxlength="10" /></td>
					</tr>
				</table>
			</form>
		</div>
		<div style="padding-top:3px;text-align:center;">
			<input type="button" value="OK" onclick="update_press_release();" />
			<input type="button" value="Cancel" onclick="editor_toggle(false);" />
		</div>
	</div>
<? include('inc/footer.php'); ?>