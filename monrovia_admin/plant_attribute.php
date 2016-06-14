<? include('inc/header.php'); ?>
<? require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_plant_attribute.php'); ?>
<?

	$list_name = '';
	$record = '';
	try {
		$list_name = $_GET['list_name'];
		$attribute_id = $_GET['attribute_id'];
		if(!in_array('list_'.$list_name,get_list_table_names())) throw new Exception();
		$list = new plant_attribute_list($list_name);
		$record = new plant_attribute_list_item($list_name,$attribute_id);
		if($record->info['id']==''&&$attribute_id!='new') throw new Exception();
		$num_plants = $record->get_usage();
	}catch(Exception $err){
		die('Attribute not found.');
		exit;
	}
	
	function append_to_head(){
	?>
		<script>
			function cancel(){
				window.location = 'plant_attributes.php?list_name=<?=$_GET['list_name']?>';
			}
		</script>
	<?
	}

	if(isset($_GET['action'])&&$_GET['action']=='edit'&&isset($_POST['name'])&&$_POST['name']!=''){
	?>
		<div style="font-size:11pt;">
		<?

			//if(is_suspicious($_POST)) die('Error: Invalid information provided.');
			$record->info['name'] = $_POST['name'];
			if(isset($_POST['synonyms'])) $record->info['synonyms'] = $_POST['synonyms'];
			$record->info['is_historical'] = ((isset($_POST['is_historical'])&&$_POST['is_historical']=='on')?'1':'0');

			// IF PRE-EXISTING ATTRIBUTE, UPDATE PLANT KEYWORDS
			if($attribute_id!='new'){
				if($record->save(true)){
					$num_plants_affected = $record->get_usage();
					echo('Your changes were saved and all ' . $num_plants_affected . ' associated plants were updated successfully.');
				}else{
					echo('An error occurred.');
				}
			}else{

				$record->save();
			?>
				Please wait...
				<script>
					cancel();
				</script>
				<?
			}
			?>
			<br /><br />
			<a href="javascript:cancel();void(0);">Go back</a>
			<?
			//clear_cache();
			exit;
		?>
		</div>
		<?
	}

	if(isset($_GET['action'])&&$_GET['action']=='reassign'&&isset($_POST['new_attribute_id'])&&$_POST['new_attribute_id']!=''){
	?>
		<div style="font-size:11pt;">
		<?
			$num_plants_affected = $record->reassign($_POST['new_attribute_id']);
			$num_plants = $record->get_usage();
			if($num_plants==0){
				echo('All ' . $num_plants_affected . ' plants were reassigned successfully.');
			}else{
				echo('An error occurred and not all plants were reassigned.');
			}
			?>
			<br /><br />
			<a href="javascript:cancel();void(0);">Go back</a>
			<?
			clear_cache();
			exit;
		?>
		</div>
		<?
	}

	if(isset($_GET['action'])&&$_GET['action']=='delete'&&isset($_POST['confirmed'])&&$_POST['confirmed']=='1'){
	?>
		<div style="font-size:11pt;">
		<?
			$num_plants_affected = $record->delete();
			$num_plants = $record->get_usage();
			if($num_plants==0){
				echo('All ' . $num_plants_affected . ' plants were updated successfully and the attribute was deleted.');
			}else{
				echo('An error occurred and not all plants were updated.');
			}
			?>
			<br /><br />
			<a href="javascript:cancel();void(0);">Go back</a>
			<?
			clear_cache();
			exit;
		?>
		</div>
		<?
	}

?>

	<form id="form_attribute" onkeypress="return cancel_enter_key(window.event||event);" method="post" validation_enabled="true">
		<? if(isset($_GET['action'])&&$_GET['action']=='edit'){ ?>
		<!-- "EDIT" INTERFACE -->

		<style>
			.table_attributes {
				width:500px;
			}
			#table_companion_plants {
				width:500px;
			}
			.table_attributes td {
				font-size:9pt;
			}
			.centered, .centered * {
				text-align:center;
			}
			.table_attributes table {
				border-collapse:collapse;
			}
			.table_attributes table td {
				padding:4px 8px 4px 8px;
			}
			h3 {
				color:#717171;
			}

		</style>
		<script>
			function td(innerHTML) {
				var ret = document.createElement('td');
				ret.innerHTML = (innerHTML||'');
				return ret;
			}

			Event.observe(window,'load',function(){
				if(get_field('synonyms')){
					// CUSTOM FIELD VALIDATION
					get_field('synonyms').custom_validation = function(){
						if(this.value.length>this.getAttribute('maxlength')){
							this.error_message = 'Please enter no more than ' + this.getAttribute('maxlength') + ' characters (you entered '+this.value.length+').';
							return false;
						}
						return true;
					}
					// ADD ON-THE-FLY VALIDATION
					get_field('synonyms').observe('keypress',function(){
						if(this.value.length>this.getAttribute('maxlength')){
							this.addClassName('error_validation');
						}else{
							this.removeClassName('error_validation');
						}

					})
				}
				$('form_attribute').observe('submit',function(){
					if($('form_attribute').validate()){
						$('action_box').style.visibility = 'hidden';
						$('notification').style.display = 'block';
					}
				});
			});
		</script>

		<h2 id="page_subheader">Plant Attribute</h2>
		<div id="page_content">
			<div class="field_group" id="notification" style="display:none;">
				Saving attribute and updating keywords. This can take several minutes. Please wait...
			</div>
			<div id="action_box">
				<div style="margin-top:4px;">
					<input type="submit" value="Save Changes" />
					<input type="button" value="Cancel" onclick="cancel();" />
					<input type="hidden" name="attribute_id" value="<?=$attribute_id?>" />
					<input type="hidden" name="list_name" value="<?=$list->name?>" />
				</div>
				<hr />
				<table>
				<? if($list->supports_historical){ ?>
					<tr>
						<td class="field_label" title="&quot;Historical&quot; attributes do not appear on the site.">Historical:</td>
						<td><input type="checkbox" name="is_historical" <? if($record->info['is_historical']=='1'){ echo(' checked'); } ?> title="&quot;Historical&quot; attributes do not appear on the site." /></td>
					</tr>
				<? } ?>
					<tr>
						<td class="field_label">Name:</td>
						<td><input type="text" name="name" value="<?=html_sanitize($record->info['name'])?>" class="text_field" maxlength="<?=$list->column_info['name']->max_length?>" validation_required="true" /></td>
					</tr>
				<? if($list->supports_synonyms){ ?>
					<tr>
						<td class="field_label">Synonyms:</td>
						<td>
							<textarea class="text_field" maxlength="<?=$list->column_info['synonyms']->max_length?>" name="synonyms" validation_type="custom" style="width:500px;"><?=html_sanitize($record->info['synonyms'])?></textarea>
							<div class="small" style="padding-top:4px;">(<?=$list->column_info['synonyms']->max_length?> characters max)</div>
						</td>
					</tr>
				<? } ?>
				<table>
			</div>
		</div>
		<!-- /"EDIT" INTERFACE -->
		<? } ?>
		<? if(isset($_GET['action'])&&$_GET['action']=='reassign'){ ?>
		<!-- "REASSIGN" INTERFACE -->
		<script>
			Event.observe(window,'load',function(){
				$('form_attribute').observe('submit',function(){
					if($('form_attribute').validate()){
						$('action_box').style.visibility = 'hidden';
						$('notification').style.display = 'block';
					}
				});
			});
		</script>
		<h2 id="page_subheader">Reassign "<?=$record->info['name'];?>" Plant Attribute</h2>
		<div id="page_content">
			<div class="field_group" id="notification" style="display:none;">
				Reassigning attribute and updating keywords. This can take several minutes. Please wait...
			</div>
			<div class="field_group" id="action_box">
				Please choose the new attribute to use for the plants currently marked <b><?=$record->info['name'];?></b> (<?=$num_plants?> plants total).<br />
				<br />
				Note: The Key Feature/Primary Attribute field will not be affected.
				<br /><br />
				<select name="new_attribute_id" class="text_field" validation_required="true">
					<option value=""></option>
					<?
						$attributes = get_table_data('list_'.$list_name);
						foreach($attributes as $attribute){
							$attribute = new plant_attribute_list_item($list_name,$attribute['id']);
							if($attribute->info['name']!=$record->info['name']){
							?>
							<option value="<?=$attribute->info['id']?>"><?=$attribute->info['name']?><?if(isset($attribute->info['is_historical'])&&$attribute->info['is_historical']=='1'){ echo(' (historical)'); }?></option>
							<?
							}
					} ?>
				</select>
				<div style="margin-top:4px;">
					<input type="submit" value="Reassign" />
					<input type="button" value="Cancel" onclick="cancel();" />
				</div>
			</div>
		</div>
		<!-- /"REASSIGN" INTERFACE -->
		<? } ?>
		<? if(isset($_GET['action'])&&$_GET['action']=='delete'){ ?>
		<!-- "DELETE" INTERFACE -->
		<script>
			Event.observe(window,'load',function(){
				$('form_attribute').observe('submit',function(){
					if($('form_attribute').validate()){
						$('action_box').style.visibility = 'hidden';
						$('notification').style.display = 'block';
					}
				});
			});
		</script>
		<h2 id="page_subheader">Delete "<?=$record->info['name'];?>" Plant Attribute</h2>
		<div id="page_content">
			<div class="field_group" id="notification" style="display:none;">
				Deleting attribute and updating keywords. This can take several minutes. Please wait...
			</div>
			<div class="field_group" id="action_box">
				This will remove the "<?=$record->info['name'];?>" attribute from <?=$num_plants?> plants and <b>permanently</b> delete the attribute from the system.<br /><br />Are you sure you want to do this?<br />
				<br />
				Note: The Key Feature/Primary Attribute field will not be affected.
				<div style="margin-top:4px;">
					<input type="submit" value="Yes, Delete" />
					<input type="button" value="No, Cancel" onclick="cancel();" />
					<input type="hidden" name="confirmed" value="1" />
				</div>
			</div>
		</div>
		<!-- /"DELETE" INTERFACE -->
		<? } ?>
		<input type="hidden" name="action" value="" />
	</form>
<? include('inc/footer.php'); ?>