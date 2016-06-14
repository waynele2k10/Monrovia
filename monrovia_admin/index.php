<?php

require_once('inc/header.php'); 


// Only Allow Admins For the Main Sections
if(check_user_role("administrator", $current_user->ID )){ ?>
			<div class="admin_module" id="admin_module_hr">
				<h2>Human Resources</h2>
				<div class="listitem"><a href="javascript:view_resumes();">R&eacute;sum&eacute;s</a></div>
			</div>
			<script>
				function view_resumes(){
					$('form_resumes').submit();
				}
			</script>
			<form action="https://azlink.monrovia.com/employment/admin/login.php" method="POST" target="_blank" id="form_resumes">
				<input type="hidden" name="username" value="admin" />
				<input type="hidden" name="password" value="??iwtl7wl!!" />
				<input type="hidden" name="Submit" value="Login" />
			</form>

			<script>
				function confirm_export(what,part){
					part = part || 0;
					var msg = what=='db'?'Click OK to begin exporting the plant database. An email notification will be sent to you at <?=$current_user->user_email;?> as soon as it\'s ready to be downloaded.':'Click OK to generate an export of missing plant images. An email notification will be sent to you at <?=$current_user->user_email;?> as soon as it\'s ready to be downloaded.';
					var lnk_name = what=='db'?'lnk_export_database_'+part:'lnk_export_missing_plant_images';
					var url = what=='db'?'export_plants.php':'export_missing_plant_images.php';
					var msg_success = 'Export complete';
					var msg_fail = 'Export failed';
					
					/* Landscape Designer Values */
					if(what == 'LD'){
						var msg = 'Click OK to begin exporting the Landscape Designer database. An email notification will be sent to you at <?=$current_user->user_email;?> as soon as it\'s ready to be downloaded.';
						lnk_name = 'lnk_landscape_user';
						url = 'export_landscape_users.php';
					}
					
					if(what == 'images'){
						msg  = 'Click OK to generate an export of Plant Images Data. An email notification will be sent to you at <?=$current_user->user_email;?> as soon as it\'s ready to be downloaded.'
						lnk_name = 'lnk_export_plant_images_data';
						url = 'plant_images_export.php'
					}
					
					if(confirm(msg)){
						$(lnk_name).update('Exporting...');
						$(lnk_name).href = '#';

						new Ajax.Request(url, {
						  method: 'post',
						  parameters: {part: part},
						  onComplete:function(transport){
							if(transport.responseText!='success'){
								if(msg_fail) $(lnk_name).update(msg_fail);
							}else{
								if(msg_success) $(lnk_name).update(msg_success);
							}
						  }
						});
					}
				}
				function perform_item_number_search(){
					$('error_not_found').style.display = 'none';
					$('multiple_found').style.display = 'none';
					$('multiple_found_list').update();

					var item_number = get_field('item_number').value;
					if(!item_number) return;
					new Ajax.Request('/monrovia_admin/query_plant_ids.php', {
					  parameters:{'item_number':item_number},
					  onComplete:function(transport){
						if(!transport.responseText){
							$('error_not_found').style.display = 'block';
						}else{
							var ids = transport.responseText.split(',');
							if(ids.length==1){
								window.location.href = 'plant_edit.php?id='+ids[0];
							}else{
								var html = '';
								for(var i=0;i<ids.length;i++){
									html += '<a href="plant_edit.php?id='+ids[i]+'" target="_blank">Record #'+ids[i]+'</a>';
								}
								$('multiple_found_list').update(html);
								$('multiple_found').style.display = 'block';
							}
						}
					  }
					});
				}
				Event.observe(window,'load',function(){
					$('item_number').observe('keypress',function(evt){
						if(evt.keyCode==13) perform_item_number_search();
					});
				});
				jQuery(document).ready( function(){
					function exp_timeout() {
						if(jQuery("#exp_status").val() != 0 ) {
							check_exp();
							jQuery('#export-list-file').html('generating export file...');
						}
						if(jQuery("#exp_status").val() == 0 ) {
							get_exp();
							jQuery("#btn-export").show();
						}
					}
					function check_exp(){
						jQuery.ajax({
								dataType: 'json',
								data: {'exp': 'check'},
								url: '<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/monrovia_admin/export_handle_ajax.php' ?>'
						}).done(function (response) { 
							console.log(response);
							jQuery("#exp_status").val(response.flag);
							//exp_timeout();
						});
					} 
					function get_exp(){
						jQuery.ajax({
								dataType: 'html',
								data: {'exp': 'get'},
								url: '<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/monrovia_admin/export_handle_ajax.php' ?>'
						}).done(function (response) { 
							//jQuery("#exp_status").val(response.flag);
							jQuery('#export-list-file').html(response);
						});
					} 
					jQuery('#btn-export').on('click', function () {
						jQuery.ajax({
							dataType: 'html',
							data: {'exp': 'update'},
							url: '<?php echo 'http://'.$_SERVER['HTTP_HOST'].'/monrovia_admin/export_handle_ajax.php' ?>'
						}).done(function (response) {
							jQuery("#exp_status").val(1);
							jQuery('#export-list-file').html('generating export file...');
							jQuery("#btn-export").hide();
						});
					});
					// running first time
					if(jQuery("#exp_status").val() != 0 ) {
						check_exp();
						jQuery('#export-list-file').html('generating export file...');
						jQuery("#btn-export").hide();
					}
					if(jQuery("#exp_status").val() == 0 ) {
						get_exp();
						jQuery("#btn-export").show();
					}
					
					var interval = setInterval( exp_timeout, 10000);
				});
			</script>
			<style>
				#error_not_found, #multiple_found {
					display:none;
				}
				#multiple_found {
					font-size:8pt;
					border-top:1px solid #ccc;
					padding-top:.5em;
					margin-top:.5em;
				}
				#multiple_found_list a {
					display:block;
				}
				#export-list-file {
					font-size: 12px;
					font-style: italic;
				}
				#export-list-file .rs-export-list p {
					color: red;
				}
				#export-list-file .rs-export-list a {
					font-size: 13px;
					font-style: normal;
				}
			</style>
			<div class="admin_module" id="admin_module_plants">
				<h2>Plant Database</h2>
				<div class="listitem">
					<div style="border:1px solid #ccc;background-color:#eee;padding:4px;font-size:11pt;width:200px;">
						<b>Edit Plant</b><br />
						Item number: <input id="item_number" style="width:40px;" maxlength="5" /><input type="button" onclick="perform_item_number_search();" value="Go" />
						<div style="color:#d00;" id="error_not_found">Plant not found in database.</div>
						<div id="multiple_found">
							<div style="color:#d00;">Multiple plant records with that item number were found:</div>
							<div id="multiple_found_list"></div>
						</div>
					</div>
				</div>
				<div class="listitem"><a href="plant_edit.php?id=new">Add a new plant</a></div>
				<div class="listitem"><a href="plant_attributes.php">Plant attributes</a></div>
				<div class="listitem">
					<a href="#" id="lnk_export_database">Export plant database to XLS:</a>
					<input type="hidden" name="exp_status" id="exp_status" value = "<?php echo get_option('monrovia_export_flag', '0'); ?>" />
					<button id="btn-export">Export</button>
					<div id="export-list-file"></div>
					<?php 
						/* $result = mysql_query("SELECT COUNT(*) FROM plants");
						if (!$result) echo mysql_error();
						
						$row = mysql_fetch_row($result);
						$nrecords = 2000;
						if ($row[0] > $nrecords) {
							$count = ceil($row[0]/$nrecords);
							echo '<table border="1" cellspacing="0" cellpadding="2" style="
								margin: 12px 0;
								font-size: 12px;
								border-color: #ADADAD;
								border-collapse: collapse;
							"><tr><th>Total part: '.$count.'</th><th>Total record: '.$row[0].'</th></tr>';
							for ($i = 1; $i <= $count; $i++) {
								echo '<tr>';
								if ($i == $count) {
									echo '<td>Part '.$i.'(From '.((($i-1)*$nrecords)+1).' to '.$row[0].')</td>';
								} else {
									echo '<td>Part '.$i.'(From '.((($i-1)*$nrecords)+1).' to '.($i*$nrecords).')</td>';
								}
								echo '<td><a href="javascript:confirm_export(\'db\','.$i.');" id="lnk_export_database_'.$i.'">Export here</a></td>';
								echo '</tr>';
							}
							echo '</table>';
						} else {
							echo ' <a href="javascript:confirm_export(\'db\',0);" id="lnk_export_database_0">All '.$row[0].' records</a> ';
						} */
					?>
				</div>
				<div class="listitem"><a href="javascript:confirm_export('img');" id="lnk_export_missing_plant_images">Generate report of missing plant images (XLS)</a></div>
                <div class="listitem"><a href="javascript:confirm_export('images');" id="lnk_export_plant_images_data">Generate report of Plant Image Data (XLS)</a></div>

			</div>

<? } // End Admin Only Tools ?>

<? if(check_user_role("administrator", $current_user->ID)){ ?>
			<div class="admin_module" id="admin_module_permissions">
				<h2>Designer Profiles</h2>
				<div class="listitem"><a href="designer_profiles.php">Manage designer profiles</a></div>
                <div class="listitem"><a href="javascript:confirm_export('LD');" id="lnk_landscape_user">Export Designer Profiles to XLS</a></div>
			</div>
<? } ?>



<? include('inc/footer.php'); ?>