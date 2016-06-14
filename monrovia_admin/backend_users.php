<? require_once($_SERVER['DOCUMENT_ROOT'].'/monrovia_admin/inc/header.php'); ?>
<? $monrovia_user->permission_requirement('user'); ?>

	<?
		if($GLOBALS['browser_info']['name']=='opera') die('Opera has a known issue with certain form elements used by this page. Please use another browser.'); // MULTIPLE SELECTS, SPECIFICALLY.

		if(isset($_POST['action'])&&$_POST['action']=='save'){
			$data = json_parse($_POST['data']);
			
			//var_dump_exit($data);
			
			$num_errors = 0;
			for($i=0;$i<count($data);$i++){		
			
				$permissions = $data[$i]->permissions;
				
				// PREPEND THE NECESSARY CMS PERMISSION IF NOT ALREADY IN THERE
				if(strpos(','.$permissions.',',',cmgt,')===false) $permissions = 'cmgt,' . $permissions;

				if($data[$i]->id==''){
					// NEW USERS
					$user = new monrovia_user();
					$user->info['is_active'] = $data[$i]->is_active;
					$user->info['permissions'] = $permissions;
					$user->info['user_name'] = $data[$i]->user_name;
					$user->info['first_name'] = $data[$i]->first_name;
					$user->info['last_name'] = $data[$i]->last_name;
					$user->info['password'] = $data[$i]->password;
					$user->info['email_address'] = $data[$i]->email_address;
					$user->info['zip'] = $data[$i]->zip;

					// CHECK FOR EXISTING USERS
					$validation_result = $user->validate();
					
					if($validation_result['status']!='valid'){
						$num_errors++;
					}else{
						$num_errors += ($user->save())?0:1;
					}
				}else{
					// EXISTING USERS
					sql_query('UPDATE monrovia_users SET is_active="' . $data[$i]->is_active . '", permissions="' . $permissions . '" WHERE id="' . $data[$i]->id .'"');
					// ASSUME SUCCESS
				}
			}
			if($num_errors==0){
				output_page_notice('Your changes have been saved.');
			}else{
				output_page_notice('ERROR: An error occurred and your changes may not have been saved.');
			}
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
		#table_users td {
			white-space:nowrap;
		}
		#user_editor_permissions {
			font-size:9pt;
			width:100%
		}
		.highlight_permission_header {
			background-color:#006;
			width:50px;
			text-align:center;
			cursor:help;
			text-decoration:underline;
		}
		.highlight_permission {
			background-color:#ddf;
			text-align:center;
			font-weight:bold;
		}
		.row_even .highlight_permission {
			background-color:#cce;
		}
		#table_users {
			margin-top:8px;
			width:982px;
		}
		#table_users td {
			font-size:9pt;
		}
		#table_users {
			border-collapse:collapse;
		}
		#table_users td {
			padding:4px 8px 4px 8px;
		}
		#user_editor {
			width:800px;
			border:2px outset #eee;
			background-color:#eee;
			padding:2px;
			display:none;
		}
		#user_editor_title_bar {
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
		#img_preview {
			max-width:200px;
			max-height:200px;
			#width:200px;
			position:absolute;
			display:none;
			margin-left:16px;
			border:2px outset #ccc;
		}
	</style>

	<script>
		var monrovia_users = [<? cms_output_backend_users(); ?>];
		var num_new_users = 0;
		function monrovia_user(id,is_active,user_name,password,email_address,first_name,last_name,zip,permissions,date_last_login){
			this.id = id;
			this.is_active = is_active;
			this.user_name = user_name;
			this.password = password;
			this.email_address = email_address;
			this.first_name = first_name;
			this.last_name = last_name;
			this.zip = zip;
			this.permissions = permissions;
			this.date_last_login = date_last_login;
		}

		function remove_monrovia_user(id){
			for(var i=0;i<monrovia_users.length;i++){
				if(monrovia_users[i].id==id){
					monrovia_users.splice(i,1);
					break;
				}
			}
			refresh_monrovia_users_table_data();
		}
		function get_monrovia_user(id){
			for(var i=0;i<monrovia_users.length;i++){
				if(monrovia_users[i].id==id){
					return monrovia_users[i];
				}
			}
		}
		function get_monrovia_user_index(id){
			for(var i=0;i<monrovia_users.length;i++){
				if(monrovia_users[i].id==id){
					return i;
				}
			}
		}
		function add_monrovia_user(data){
			num_new_users++;
			monrovia_users.push(data);
			refresh_monrovia_users_table_data();
		}
		function refresh_monrovia_users_table_data(){
			var data = monrovia_users;
			var parent_element = $('table_users_contents');
			parent_element.update('');
			for(var i=0;i<data.length;i++){
				var tr = new Element('tr');
				if(i%2) tr.addClassName('row_even');
				var permissions = ',' + data[i].permissions + ',';
				tr.appendChild(new td((data[i].is_active=='1')?'Active':'Inactive'));
				tr.appendChild(new td('<a href="javascript:launch_editor(\''+data[i].id+'\');void(0);">'+data[i].user_name+'</a>'));
				tr.appendChild(new td('<!--<a href="mailto:'+data[i].email_address+'">-->'+data[i].email_address+'<!--</a>-->'));
				tr.appendChild(new td(permissions.contains(',hres,')?'x':'',{'class':'highlight_permission'}));
				tr.appendChild(new td(permissions.contains(',pldb,')?'x':'',{'class':'highlight_permission'}));
				//tr.appendChild(new td(permissions.contains(',blog,')?'x':'',{'class':'highlight_permission'}));
				tr.appendChild(new td(permissions.contains(',html,')?'x':'',{'class':'highlight_permission'}));
				tr.appendChild(new td(permissions.contains(',caln,')?'x':'',{'class':'highlight_permission'}));
				tr.appendChild(new td(permissions.contains(',user,')?'x':'',{'class':'highlight_permission'}));
				tr.appendChild(new td(permissions.contains(',pimg,')?'x':'',{'class':'highlight_permission'}));
				tr.appendChild(new td(permissions.contains(',prof,')?'x':'',{'class':'highlight_permission'}));
				tr.appendChild(new td(permissions.contains(',qamd,')?'x':'',{'class':'highlight_permission'}));
				tr.appendChild(new td(permissions.contains(',pdfs,')?'x':'',{'class':'highlight_permission'}));
				tr.appendChild(new td(data[i].date_last_login.replace('0000-00-00 00:00:00','')));
				tr.monrovia_user_id = data[i].id;
				parent_element.appendChild(tr);
			}
		}
		function td(innerHTML,customizations){
			var ret = new Element('td');
			ret.innerHTML = innerHTML;
			if(customizations){
				if(customizations['class']){
					ret.addClassName(customizations['class']);
				}
			}
			return ret;
		}
		function update_user(){
			if(!$('form_editor').validate()) return;

			var user_id = $('user_editor').current_user_id.toString();
			var new_is_active = replace_special_characters($('user_editor_status').value.strip());
			var new_permissions = get_multiselect_values($('user_editor_permissions'));
			var new_user_name = replace_special_characters($('user_editor_user_name').value.strip());
			var new_password = replace_special_characters($('user_editor_password').value.strip());
			var new_email_address = replace_special_characters($('user_editor_email_address').value.strip());
			var new_first_name = replace_special_characters($('user_editor_first_name').value.strip());
			var new_last_name = replace_special_characters($('user_editor_last_name').value.strip());
			var new_zip = replace_special_characters($('user_editor_zip').value.strip());

			if(user_id==monrovia_user_data.id&&!(','+new_permissions+',').contains(',user,')){
				// PREVENT USERS FROM LOCKING THEMSELVES OUT
				alert('Please grant yourself the Back-end User Management permission--you\'ll be locked out of this interface otherwise.');
				return false;
			}
			
			if(!user_id||user_id.contains('new_')){
				// IF NEW USER, SET/UPDATE INFO
				if(!user_id){
					// NEVER ADDED; ADD NOW
					var new_user = new monrovia_user('new_'+(num_new_users+1),new_is_active,new_user_name,new_password,new_email_address,new_first_name,new_last_name,new_zip,new_permissions,'0000-00-00 00:00:00');
					add_monrovia_user(new_user);
				}else{
					// PREVIOUSLY ADDED, BUT STILL NEW USER
					var current_user = monrovia_users[get_monrovia_user_index(user_id)];
					// UPDATE ONLY STATUS, FIRST NAME, LAST NAME, USER NAME, EMAIL, ZIP, PERMISSIONS
					current_user.is_active = new_is_active;
					current_user.permissions = new_permissions;
					current_user.user_name = new_user_name;
					current_user.first_name = new_first_name;
					current_user.last_name = new_last_name;
					current_user.email_address = new_email_address;
					current_user.zip = new_zip;
					monrovia_users[get_monrovia_user_index(user_id)] = current_user;
				}
			}else{

				var current_user = monrovia_users[get_monrovia_user_index(user_id)];
	
				// IF NOT CURRENTLY IN ARRAY, ASSUME IT'S AN EXISTING USER THAT HAD BEEN PULLED IN
				if(!current_user){
					add_monrovia_user(new monrovia_user(user_id,new_is_active,new_user_name,new_password,new_email_address,new_first_name,new_last_name,new_zip,new_permissions,'0000-00-00 00:00:00'));
				}else{
					// UPDATE ONLY STATUS AND PERMISSIONS
					current_user.is_active = new_is_active;
					current_user.permissions = new_permissions;
					monrovia_users[get_monrovia_user_index(user_id)] = current_user;				
				}
			}

			refresh_monrovia_users_table_data();
			editor_toggle(false);
		}
		function launch_editor(monrovia_user_id){
			if(monrovia_user_id){
				// EDIT
				var monrovia_user = get_monrovia_user(monrovia_user_id);
				$('user_editor').current_user_id = monrovia_user.id;
				$('user_editor_user_name').value = monrovia_user.user_name;
				$('user_editor_status').value = monrovia_user.is_active;
				set_multiselect_values($('user_editor_permissions'),monrovia_user.permissions);
				$('user_editor_email_address').value = monrovia_user.email_address;
				$('user_editor_first_name').value = monrovia_user.first_name;
				$('user_editor_last_name').value = monrovia_user.last_name;
				$('user_editor_zip').value = monrovia_user.zip;
				$('user_editor_password').value = monrovia_user.password;
			}else{
				// NEW
				$('user_editor').current_user_id = '';
				$('form_editor').reset();
				$('user_editor_password').value = random_password();
				
				$('user_editor_user_name').observe('blur',function(){
					search_users('user_editor_user_name');
				});
				$('user_editor_email_address').observe('blur',function(){
					search_users('user_editor_email_address');
				});
			}
			
			set_field_states(monrovia_user_id&&monrovia_user_id.contains('new_'));
			
			$('user_editor_title_bar').update((monrovia_user_id)?'Edit Back-end User':'Add A New Back-end User');
			
			set_field_states(!monrovia_user_id||!monrovia_user_id.indexOf('new_'));
			
			editor_toggle(true);
			//$('user_editor_zip').focus();
			//$('user_editor_zip').select();
		}
		function search_users(src_field){
		
			var id = null;
			
			// LOOK THROUGH ARRAY
			for(var i=0;i<monrovia_users.length;i++){
				if(monrovia_users[i].user_name.toLowerCase().trim()==$('user_editor_user_name').value.toLowerCase().trim()||monrovia_users[i].email_address.toLowerCase().trim()==$('user_editor_email_address').value.toLowerCase().trim()){
					confirm_edit(src_field,monrovia_users[i]);
					return;
				}
			}
		
			new Ajax.Request('query_user.php?user_name=' + $('user_editor_user_name').value + '&email_address=' + $('user_editor_email_address').value,{
				method:'get',
				onComplete:function(resp){
					if(resp.responseText) confirm_edit(src_field,resp.responseText.evalJSON());
				}
			});
		}
		
		function confirm_edit(src_field,user){
			if(confirm('An existing user was found with that ' + (src_field=='user_editor_user_name'?'user name':'email address') + '. Would you like to edit that user?')){
				$('user_editor').current_user_id = user.id;
				$('form_editor').reset();
				$('user_editor_status').value = user.is_active;
				$('user_editor_user_name').value = user.user_name;
				$('user_editor_email_address').value = user.email_address;
				$('user_editor_first_name').value = user.first_name;
				$('user_editor_last_name').value = user.last_name;
				$('user_editor_zip').value = user.zip;
				$('user_editor_password').value = user.password;

				Event.stopObserving($('user_editor_user_name'),'blur');
				Event.stopObserving($('user_editor_email_address'),'blur');

				set_field_states(false);

				set_multiselect_values($('user_editor_permissions'),user.permissions);
			}else{
				$(src_field).value = '';
			}
		}
		
		function set_field_states(is_new){
			if(is_new){
				$('user_editor_user_name').removeAttribute('disabled');
				$('user_editor_email_address').removeAttribute('disabled');
				$('user_editor_first_name').removeAttribute('disabled');
				$('user_editor_last_name').removeAttribute('disabled');
				$('user_editor_zip').removeAttribute('disabled');
			}else{
				$('user_editor_user_name').setAttribute('disabled','disabled');
				$('user_editor_email_address').setAttribute('disabled','disabled');
				$('user_editor_first_name').setAttribute('disabled','disabled');
				$('user_editor_last_name').setAttribute('disabled','disabled');
				$('user_editor_zip').setAttribute('disabled','disabled');
			}
		}
		
		function editor_toggle(show){
			clear_validation_errors();
			if(show){
				$('page_content').fade({duration:.25});
				window.setTimeout(function(){$('user_editor').style.display='block';},250);
			}else{
				$('page_content').appear({duration:.25});
				$('user_editor').style.display = 'none';
				
				Event.stopObserving($('user_editor_user_name'),'blur');
				Event.stopObserving($('user_editor_email_address'),'blur');
			}
		}

		function page_validate(){

			var new_users = false;
			for(var i=0;i<monrovia_users.length;i++){
				if(monrovia_users[i].id.toString().contains('new_')) new_users = true;
			}
			//if(!new_users||(new_users&&confirm('Random passwords were assigned to the user(s) you are about to create. If you haven\'t written them down yet, please click Cancel and do so.\n\nSave now?'))){
				for(var i=0;i<monrovia_users.length;i++){
					if(monrovia_users[i].id.toString().contains('new_')) monrovia_users[i].id = '';
				}
				get_field('data').value = Object.toJSON(monrovia_users);
				// SET "SAVE" FLAG ONLY IF THIS GETS EXECUTED, WHICH SHOULDN'T HAPPEN IF A JS ERROR OCCURS
				get_field('action').value = 'save';
				return true;
			//}else{
			//	return false;
			//}
		}

		function random_password(){
			var base = ['rose','hibiscus','tulip','daisy'];
			var numbers = '';
			for(var i=0;i<4;i++){
				numbers += Math.ceil(Math.random()*9).toString();
			}
			return base[Math.floor(Math.random()*base.length)] + numbers;
		}

		Event.observe(window,'load',function(){
			refresh_monrovia_users_table_data();

			// CUSTOM FIELD VALIDATION
			$('user_editor_email_address').custom_validation = function(){
				if(!this.value.match(/^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-Z0-9]{2,4}$/)){
					this.error_message = 'Please enter a valid email address.';
					return false;
				}
				if(this.value.toLowerCase().endsWith('.con')){
					this.error_message = 'The email address specified ends in<br />".con." Did you mean ".com"?';
					return false;
				}
				return true;
			}
			$('user_editor_zip').custom_validation = function(){
				if(!is_valid_us_canadian_zip(this.value)){
					this.error_message = 'Please enter a valid zip code.';
					return false;
				}
				return true;
			}
		});
	</script>

	<h2>Back-end Users</h2>

	<div id="page_content">
		<div style="margin-top:4px;">
			<form id="form_users" action="" onsubmit="return page_validate();" method="post">
				<input type="hidden" name="data" />
				<input type="hidden" name="action" />
				<input type="submit" value="Save Changes" />
				<input type="button" value="Cancel" onclick="page_cancel();" />
			</form>
		</div>
		<hr />
		<input type="button" value="Add A New Back-end User" onclick="launch_editor();" />
		<table id="table_users">
			<thead>
				<tr style="background-color:#666;color:#fff;">
					<td width="1">Status</td>
					<td>User Name</td>
					<td>Email Address</td>
					<td class="highlight_permission_header" title="Human Resources">HR</td>
					<td class="highlight_permission_header" title="Plant Database">Plants</td>
					<!--<td class="highlight_permission_header" title="Blog Administration">Blog</td>-->
					<td class="highlight_permission_header" title="Website Content Editing">Website</td>
					<td class="highlight_permission_header" title="Event Calendar Administration">Calendar</td>
					<td class="highlight_permission_header" title="Back-end User Management">Users</td>
					<td class="highlight_permission_header" title="Hi-Res Plant Image Downloads">Hi-Res Images</td>
					<td class="highlight_permission_header" title="Designer Profiles">Designer Profiles</td>
					<td class="highlight_permission_header" title="Q&amp;A Moderation">Q&amp;A</td>
					<td class="highlight_permission_header" title="Catalogs">Catalogs</td>
					<td width="1">Last&nbsp;Login</td>
				</tr>
			</thead>
			<tbody id="table_users_contents"></tbody>
		</table>
	</div>
	<div id="user_editor">
		<div id="user_editor_title_bar">Edit User</div>
		<div style="padding:0px 0px 4px 4px;">
			<form id="form_editor" onsubmit="return return_false();" validation_enabled="true">
				<table>
					<tr>
						<td><label class="field_label">User Name:</label></td>
						<td><input class="text_field" id="user_editor_user_name" disabled maxlength="40" /></td>
						<td><label class="field_label">Password:</label></td>
						<td><input class="text_field" id="user_editor_password" disabled /></td>
					</tr>
					<tr>
						<td><label class="field_label">Status:</label></td>
						<td><select class="text_field" id="user_editor_status"><option value="1">Active</option><option value="0">Inactive</option></select></td>
						<td><label class="field_label">Email Address:</label></td>
						<td><input class="text_field" id="user_editor_email_address" validation_required="true" /></td>
					</tr>
					<tr>
						<td><label class="field_label">First Name:</label></td>
						<td><input class="text_field" id="user_editor_first_name" validation_required="true" maxlength="50" /></td>
						<td><label class="field_label">Last Name:</label></td>
						<td><input class="text_field" id="user_editor_last_name" maxlength="255" /></td>
					</tr>
					<tr>
						<td><label class="field_label">Zip:</label></td>
						<td><input class="text_field" id="user_editor_zip" validation_required="true" maxlength="5" /></td>
					</tr>
					<tr>
						<td><label class="field_label">Permissions:</label></td>
						<td colspan="3">
							<select id="user_editor_permissions" multiple size="6">
								<option value="hres">Human Resources</option>
								<option value="pldb">Plant Database</option>
								<!--<option value="blog">Blog Administration</option>-->
								<option value="html">Website Content Editing</option>
								<option value="caln">Event Calendar Administration</option>
								<option value="user">Back-end User Management</option>
								<option value="pimg">Hi-res Plant Images</option>
								<option value="prof">Designer Profiles</option>
								<option value="qamd">Q&amp;A Moderation</option>
								<option value="pdfs">Catalogs</option>
							</select>
						</td>
					</tr>
					<tr>
						<td></td>
						<td colspan="3" style="font-size:9pt;">
							(Hold down Ctrl and click to select multiple or to unselect selected items.)
						</td>
					</tr>
				</table>
			</form>
		</div>
		<div style="padding-top:3px;text-align:center;">
			<input type="button" value="OK" onclick="update_user();" />
			<input type="button" value="Cancel" onclick="editor_toggle(false);" />
		</div>
	</div>
<? include('inc/footer.php'); ?>