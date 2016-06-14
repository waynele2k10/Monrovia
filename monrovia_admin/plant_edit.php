<? include('inc/header.php'); ?>
<?php require_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/monrovia/includes/utility_functions.php'); ?>
<?php require_once('../inc/class_plant.php'); ?>

<?

	function before_output(){
		// BY ITEM NUMBER
		$item_number = '';
		if(isset($_GET['item_number'])) $item_number = $_GET['item_number'];
		if($item_number!=''){
			$record = new plant(get_plant_id_by_item_number($item_number));
			$record_id = $record->info['id'];
			if($record_id==''){
				header('location:./?msg=plant_not_found');
				exit;
			}else{
				header('location:plant_edit.php?id='.$record_id);
				exit;
			}
		}
	}

	//if($GLOBALS['browser_info']['name']=='opera') die('Opera has a known issue with certain form elements used by this page. Please use another browser.'); // MULTIPLE SELECTS, SPECIFICALLY.

	$record = '';
	if(isset($_POST['action'])&&$_POST['action']=='save') require('inc/plant_save.php');
	
	if(isset($_GET['msg'])&&$_GET['msg']=='saved') output_page_notice('Your changes have been saved.');

	if($record!=''){
		// RETAIN SAVED PLANT
		$record_id = $record->info['id'];
	}else{
		if(isset($_GET['id'])) $record_id = $_GET['id'];
		$record = new plant($record_id);
		if((!isset($record->info['id'])||$record->info['id']=='')&&$_GET['id']!='new'){
			die('Plant not found.');
		}else{
			if(isset($_GET['action'])&&$_GET['action']=='delete'){
				$record->delete();
				die('Plant has been deleted successfully.');
			}
		}
	}

	// REDIRECT TO NEW RECORD CREATED
	if($_GET['id']==''&&$record->info['id']!=''){
		header('location:plant_edit.php?id='.$record->info['id'].'&msg=saved');
		exit;
	}

	$last_modified_by = $record->info['last_modified_by_user'];
?>

	<form id="form_record" action="?id=<?=(isset($record->info['id'])?$record->info['id']:'')?>" onsubmit="return page_validate();" onkeypress="return cancel_enter_key(window.event||event);" method="post">

	<style>
		.slimTabBlurb td {
			font-size:9pt;
		}
		.table_nested {
			margin-bottom:8px;
		}
		.table_nested .field_label {
			font-size:8pt;
			width:75px!important;
		}
		.table_nested .text_field {
			width:166px;
		}
		.table_attributes {
			width:500px;
		}
		#table_companion_plants {
			width:500px;
		}
		.table_attributes td, #table_companion_plants td {
			font-size:9pt;
		}
		.centered, .centered * {
			text-align:center;
		}
		.table_attributes table, #table_companion_plants {
			border-collapse:collapse;
		}
		.table_attributes table td, #table_companion_plants td {
			padding:4px 8px 4px 8px;
		}
		.link_remove {
			text-decoration:underline;
			color:#00f;
			cursor:pointer;
		}
		#table_growth_care {
			width:600px;
		}
		#table_growth_care .full_width {
			#width:99%!important;
		}
	</style>
	<script>
		var record_id = '<?=(isset($record->info['id'])?$record->info['id']:'')?>';

		function page_validate(){
			get_field('plant[is_new]').value = ($('plant_is_new').checked)?'1':'0';
			get_field('plant[is_plant_select]').value = ($('plant_is_plant_select').checked)?'1':'0';
			get_field('plant[is_monrovia_exclusive]').value = ($('plant_is_monrovia_exclusive').checked)?'1':'0';
			get_field('plant[is_monrovia_patented]').value = ($('plant_is_monrovia_patented').checked)?'1':'0';
			get_field('plant[is_monrovia_trademarked]').value = ($('plant_is_monrovia_trademarked').checked)?'1':'0';
			get_field('plant[is_active]').value = ($('plant_is_active').checked)?'1':'0';

			/* TODO - SYNCH UP REAL-TIME
				// ATTRIBUTES
				var attribute_ids = '';
				for(var i=0;i<plant_attributes.length;i++){
					attribute_ids += ','+plant_attributes[i].id;
				}
				get_field('plant[attribute_ids]').value = attribute_ids.substr(1);

				// COMPANION PLANTS
				var companion_plant_ids = '';
				for(var i=0;i<companion_plants.length;i++){
					companion_plant_ids += ','+companion_plants[i].id;
				}
				get_field('plant[companion_plant_ids]').value = companion_plant_ids.substr(1);
			*/

			if(!get_field('plant[item_number]').value){
				alert('Please provide an item number.');
				return false;
			}

		}

		function upload_complete(){
			if(!$('iframe_upload_image')) return;
			var iframe_upload = $('iframe_upload_image').contentWindow;
			var iframe_result = Element.down(iframe_upload.document.body,'iframe').contentWindow;
			if(iframe_result.action=='upload'){
				var success = (iframe_result.result=='1');
				if(success){
					Element.down(iframe_upload.document.body,'form').reset();
					refresh_image_sets();
				}else{
					alert('An error occurred and your file was not uploaded.');
				}
				Element.down(iframe_upload.document.body,'div[id=upload_form_container]').style.visibility = 'visible';
				iframe_result.location.href = 'about:blank';
			}
		}

		// KEYBOARD SHORTCUTS
		Event.observe(document,'keyup',function(objEvent){
			objEvent = (objEvent||window.event);
			if(altPressed(objEvent)&&shiftPressed(objEvent)){
				slimTab_showTab('ctlTabs',window.parseFloat(String.fromCharCode(objEvent.keyCode)));
			}
			$$('.slimTab').each(function(tab){
				if(tab.style.backgroundColor!='#ffffff') tab.style.backgroundColor = '#fff';
			});
		});
		Event.observe(document,'keydown',function(objEvent){
			objEvent = (objEvent||window.event);
			if(altPressed(objEvent)&&shiftPressed(objEvent)){
				$$('.slimTab').each(function(tab){
					tab.style.backgroundColor = '#ffc';
				});
			}
		});

		Event.observe(window,'load',function(){
			get_field('plant[item_number]').observe('blur',search_duplicates);
			search_duplicates();
		});

		function altPressed(objEvent){
			return (objEvent.altKey||(objEvent.modifiers%2));
		}
		function ctrlPressed(objEvent){
			return (objEvent.ctrlKey||objEvent.modifiers==2||objEvent.modifiers==3||objEvent.modifiers>5);
		}
		function shiftPressed(objEvent){
			return (objEvent.shiftKey||objEvent.modifiers>3);
		}
		function search_duplicates(){
			$('error_notice').style.display = 'none';
			var item_number = get_field('plant[item_number]').value;
			if(!item_number) return;
			new Ajax.Request('/monrovia_admin/query_plant_ids.php', {
			  parameters:{'item_number':item_number,'id_exclude':record_id},
			  onComplete:function(transport){
				if(transport.responseText){
					var ids = transport.responseText.split(',');
					var html = '';
					for(var i=0;i<ids.length;i++){
						html += '<a href="plant_edit.php?id='+ids[i]+'" target="_blank" style="display:block;">Record #'+ids[i]+'</a>';
					}
					var msg = '';
					if(ids.length>1){
						msg = 'Warning: There are other records with the same item number:';
					}else{
						msg = 'Warning: There is another record with the same item number:';
					}
					html = msg + '<br /><br />'+html;
					$('error_notice').update(html);
					$('error_notice').style.display = 'block';
				}
			  }
			});
		}

	</script>
	<h2 id="page_subheader"><?=(isset($record->info['common_name'])?$record->info['common_name']:'Add New Plant')?></h2>
	
	<? if(isset($record->info['id'])&&$record->info['id']!=''){ ?>
	
		<style>
			#where_shown {
				font-size:11px;
				padding:.5em 0px;
			}
			.is_shown {
				color:#090;
				display:block;
			}
			.is_not_shown {
				color:#900;
				display:block;
			}
		</style>
		<div id="where_shown">
			<?
				$where_shown = $record->determine_where_active();
				if(strpos($where_shown,',site,')!==false){
					?>
					<span class="is_shown">This plant is currently shown on the website.</span>
					<?
				}else{
					?>
					<span class="is_not_shown">This plant is currently not shown on the website.</span>
					<?
				}

				if(strpos($where_shown,',pdf,')!==false){
					?>
					<span class="is_shown">This plant is currently allowed in the Catalog Creator.</span>
					<?
				}else{
					?>
					<span class="is_not_shown">This plant is currently not allowed in the Catalog Creator.</span>
					<?
				}

			?>
		</div>
		
	<? } ?>
	
	<div style="margin-top:4px;">
		<input type="submit" value="Save Changes" />
		<input type="button" value="Cancel" onclick="window.location='./';" />
		<? if($record_id!='new'){ ?>
			<input type="button" value="Delete Record" onclick="modal_show({'modal_id':'modal_delete_plant'});" style="margin-left:600px;color:#a00;" />
		<? } ?>
	</div>
	<? if(isset($record->info['id'])&&$record->info['id']!=''){ ?>
		<div style="font-size:8pt;color:#717171;padding-top:8px;">Last modified on <?=$record->info['last_modified']?><?if($last_modified_by!='') echo(' by '.$last_modified_by);?></div>
	<? } ?>
	<div class="slimTabContainer" id="ctlTabs">
		<div>
			<div class="slimTab spacer" style="width:4px">&nbsp;</div>
			<div class="slimTab selected" tab="1" title="Alt + Shift + 1">1. General</div>
			<div class="slimTab" tab="2" title="Alt + Shift + 2">2. Descriptions</div>
			<div class="slimTab" tab="3" title="Alt + Shift + 3">3. Attributes/Features/Use</div>
			<div class="slimTab" tab="4" title="Alt + Shift + 4">4. Flower/Foliage</div>
			<div class="slimTab" tab="5" title="Alt + Shift + 5">5. Growth/Care</div>
			<div class="slimTab" tab="6" title="Alt + Shift + 6">6. Needs/Zones</div>
			<div class="slimTab" tab="7" title="Alt + Shift + 7">7. Images</div>
			<!--<div class="slimTab" tab="8" title="Alt + Shift + 8">8. Companion Plants</div>-->
			<div class="slimTab spacer" style="width:32px;">&nbsp;</div>
		</div>
		<div class="slimTabBlurb sel" tab="1">
			<table>
				<tr>
					<td></td>
					<td colspan="3">
						<input type="checkbox" name="plant[is_active]" <?=(isset($record->info['is_active'])&&$record->info['is_active']=='1')?'checked':''?> class="checkbox" id="plant_is_active" /><label class="field_label" for="plant_is_active" style="margin-right:40px;">Active</label>
						<input type="checkbox" name="plant[is_new]" <?=(isset($record->info['is_new'])&&$record->info['is_new']=='1')?'checked':''?> class="checkbox" id="plant_is_new" /><label class="field_label" for="plant_is_new" style="margin-right:40px;">New plant</label>
						<input type="checkbox" name="plant[is_plant_select]" <?=(isset($record->info['is_plant_select'])&&$record->info['is_plant_select']=='1')?'checked':''?> class="checkbox" id="plant_is_plant_select" /><label class="field_label" for="plant_is_plant_select">Plant Select&reg; Plant</label>
					</td>
				</tr>
				<tr>
					<td class="field_label">Item Number:</td>
					<td><input name="plant[item_number]" class="text_field" maxlength="6" value="<?=(isset($record->info['item_number'])?$record->info['item_number']:'');?>" maxlength="9" /></td>
				</tr>
			</table>
			<div class="field_group">
				<table>
					<tr>
						<td class="field_label">Types:<br /><span class="small">(Hold down Ctrl and click to select multiple.)</span></td>
						<td>
							<select name="plant[types][]" class="text_field" _value="<?=generate_attribute_id_csv((isset($record->info['types'])?$record->info['types']:''));?>" multiple style="height:100px;">
								<? output_select_options('list_type'); ?>
							</select>
						</td>
						<!--
						<td class="field_label" style="vertical-align:top;">Collections:</td>
						<td style="vertical-align:top;">
							<? //$record->output_collections_html(); ?>
						</td>
						-->
					</tr>
					<tr>
						<td class="field_label">Primary type:</td>
						<td>
							<select name="plant[primary_type_id]" class="text_field" _value="<?=(isset($record->info['type_primary']->id)?$record->info['type_primary']->id:'');?>">
								<option value=""></option>
								<? output_select_options('list_type'); ?>
							</select>
						</td>
						<td></td><td></td>
					</tr>
					<tr>
						<td class="field_label">Deciduous/Evergreen:</td>
						<td>
							<select name="plant[deciduous_evergreen_id]" class="text_field" _value="<?=(isset($record->info['deciduous_evergreen_id'])?$record->info['deciduous_evergreen_id']:'');?>">
								<option value=""></option>
								<? output_select_options('list_deciduous_evergreen'); ?>
							</select>
						</td>
						<td class="field_label" style="vertical-align:top;">Subcategory:</td>
						<td style="vertical-align:top;">
							<?=(isset($record->info['subcategory'])?$record->info['subcategory']:'');?>
						</td>
					</tr>
					<tr>
						<td class="field_label">Plant Collection:</td>
						<td>
							<select name="plant[collection_name]" class="text_field" _value="<?=(isset($record->info['collection_name'])?$record->info['collection_name']:'');?>">
								<option value=""></option>
								<option value="Dan Hinkley">Dan Hinkley</option>
								<option value="Distinctively Better">Distinctively Better</option>
								<option value="Distinctively Better Perennials">Distinctively Better Perennials</option>
								<option value="Edibles">Edibles</option>
								<option value="Itoh Peonies">Itoh Peonies</option>
								<option value="Proven Winners">Proven Winners</option>
								<option value="Succulents">Succulents</option>
							</select>
						</td>
						<td class="field_label" style="vertical-align:top;"></td>
						<td style="vertical-align:top;">
						</td>
					</tr>
					<tr>
						<td class="field_label">Special-3rd-Party:</td>
						<td>
							<select name="plant[special_third_party]" class="text_field" _value="<?=(isset($record->info['special_third_party'])?$record->info['special_third_party']:'');?>">
								<option value=""></option>
								<option value="Lowes">Lowes</option>
							</select>
						</td>
						<td></td><td></td>
					</tr>
				</table>
			</div>
			<div class="field_group">
				<table>
					<tr>
						<td class="field_label">Common Name:</td>
						<td><input name="plant[common_name]" class="text_field" value="<?=(isset($record->info['common_name'])?$record->info['common_name']:'')?>" maxlength="255" /></td>
						<td class="field_label">Trademark Name:</td>
						<td><input name="plant[trademark_name]" class="text_field" value="<?=(isset($record->info['trademark_name'])?$record->info['trademark_name']:'');?>" maxlength="255" /></td>
					</tr>
					<tr>
						<td class="field_label">Synonym:</td>
						<td><input name="plant[synonym]" class="text_field" value="<?=(isset($record->info['synonym'])?$record->info['synonym']:'')?>" maxlength="255" /></td>
						<td class="field_label">Botanical Name:</td>
						<td><input name="plant[botanical_name]" class="text_field" value="<?=(isset($record->info['botanical_name'])?$record->info['botanical_name']:'');?>" maxlength="255" /></td>
					</tr>
					<tr>
						<td class="field_label">Classification:</td>
					</tr>
					<tr>
						<td></td>
						<td colspan="3">
							<table class="table_nested">
								<tr>
									<td class="field_label">Family:</td>
									<td>
										<input id="plant_family" name="plant[botanical_family]" maxlength="40" class="text_field" value="<?=(isset($record->info['botanical_family'])?$record->info['botanical_family']:'');?>" />
										<div class="auto_complete" autocomplete_type="botanical_family"></div>
									</td>
									<td class="field_label">Genus:</td>
									<td>
										<input id="plant_genus" name="plant[botanical_genus]" maxlength="40" class="text_field" value="<?=(isset($record->info['botanical_genus'])?$record->info['botanical_genus']:'');?>" />
										<div class="auto_complete" autocomplete_type="botanical_genus"></div>
									</td>
								</tr>
								<tr>
									<td class="field_label">Species:</td>
									<td>
										<input id="plant_species" name="plant[botanical_species]" maxlength="40" class="text_field" value="<?=(isset($record->info['botanical_species'])?$record->info['botanical_species']:'');?>" />
										<div class="auto_complete" autocomplete_type="botanical_species"></div>
									</td>
									<td class="field_label">Sub-Species:</td>
									<td>
										<input id="plant_subspecies" name="plant[botanical_subspecies]" maxlength="40" class="text_field" value="<?=(isset($record->info['botanical_subspecies'])?$record->info['botanical_subspecies']:'');?>" />
										<div class="auto_complete" autocomplete_type="botanical_subspecies"></div>
									</td>
								</tr>
								<tr>
									<td class="field_label">Cultivar:</td>
									<td><input name="plant[botanical_cultivar]" class="text_field" value="<?=(isset($record->info['botanical_cultivar'])?$record->info['botanical_cultivar']:'');?>" maxlength="40" /></td>
									<td class="field_label"></td>
									<td></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td class="field_label">Phonetic&nbsp;Spelling:</td>
						<td colspan="3"><input name="plant[phonetic_spelling]" class="text_field" value="<?=(isset($record->info['phonetic_spelling'])?$record->info['phonetic_spelling']:'');?>" maxlength="255" /><!-- <input type="button" value="Perform Lookup" />--></td>
					</tr>
				</table>
			</div>
			<div class="field_group">
				<table>
					<tr>
						<td></td>
						<td colspan="3" style="padding-bottom:.5em;">
							<input type="checkbox" name="plant[is_monrovia_exclusive]" <?=(isset($record->info['is_monrovia_exclusive'])&&$record->info['is_monrovia_exclusive']=='1')?'checked':''?> class="checkbox" id="plant_is_monrovia_exclusive" /><label class="field_label" for="plant_is_monrovia_exclusive" title="This affects the sales catalog PDFs only." style="cursor:help;margin-right:32px;">Monrovia Exclusive</label>

							<input type="checkbox" name="plant[is_monrovia_patented]" <?=(isset($record->info['is_monrovia_patented'])&&$record->info['is_monrovia_patented']=='1')?'checked':''?> class="checkbox" id="plant_is_monrovia_patented" /><label class="field_label" for="plant_is_monrovia_patented" title="This determines whether this plant appears on the Patented Plants page or not." style="cursor:help;margin-right:32px;">Monrovia Patented</label>
							
							<input type="checkbox" name="plant[is_monrovia_trademarked]" <?=(isset($record->info['is_monrovia_trademarked'])&&$record->info['is_monrovia_trademarked']=='1')?'checked':''?> class="checkbox" id="plant_is_monrovia_trademarked" /><label class="field_label" for="plant_is_monrovia_trademarked" title="This determines whether this plant appears on the Trademarked Plants page or not." style="cursor:help;">Monrovia Trademarked</label>
						</td>
					</tr>
					<tr>
						<td class="field_label">Release status:</td>
						<td>
							<select name="plant[release_status_id]" class="text_field" _value="<?=(isset($record->info['release_status_id'])?$record->info['release_status_id']:'');?>">
								<option value=""></option>
								<? output_select_options('list_release_status'); ?>
							</select>
						</td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td></td>
						<td colspan="3">
							<span sclass="small">
								<strong>Notes:</strong><br />
								<br />
								Plants with these release statuses will appear in the Catalog Creator:<br />
								- A (Active), NA (New/Active), NI (New/Inactive), F (Future)<br /><br />
								Plants with these release statuses will <strong>not</strong> appear in the Catalog Creator:<br />
								- II (Inventory/Inactive), D (Deleted)
								<br />&nbsp;
							</span>
						</td>
					</tr>
					<tr>
						<td class="field_label">Patent:</td>
						<td>
							<input name="plant[patent]" class="text_field" value="<?=(isset($record->info['patent'])?$record->info['patent']:'');?>" maxlength="40" />
						</td>
						<td class="field_label">Patent Act:</td>
						<td>
							<input id="plant_patent_act" name="plant[patent_act]" class="text_field" value="<?=(isset($record->info['patent_act'])?$record->info['patent_act']:'');?>" maxlength="255" />
							<div class="auto_complete" autocomplete_type="patent_act"></div>
						</td>
					</tr>
					<tr>
						<td class="field_label">Year introduced:</td>
						<td>
							<input name="plant[year_introduced]" class="text_field" value="<?$year_introduced = intval(isset($record->info['year_introduced'])?$record->info['year_introduced']:'0');if($year_introduced>0) echo($year_introduced);?>" maxlength="4" />
						</td>
						<td class="field_label">Geographical Origin:</td>
						<td>
							<input id="plant_geographical_origin" name="plant[geographical_origin]" maxlength="128" class="text_field" value="<?=(isset($record->info['geographical_origin'])?$record->info['geographical_origin']:'');?>" />
							<div class="auto_complete" autocomplete_type="geographical_origin"></div>
						</td>
					</tr>
					<tr>
						<td class="field_label" style="padding-right:23px;">Learn2Grow&nbsp;URL:</td>
						<td colspan="3">
							<input name="plant[learn2grow_url]" class="text_field full_width" value="<?=(isset($record->info['learn2grow_url'])?$record->info['learn2grow_url']:'');?>" maxlength="1024" />
						</td>
					</tr>
					<tr>
						<td class="field_label" style="padding-right:23px;">"Buy&nbsp;Now"&nbsp;URL:</td>
						<td colspan="3">
							<input name="plant[buy_now_url]" class="text_field full_width" value="<?=(isset($record->info['buy_now_url'])?$record->info['buy_now_url']:'');?>" maxlength="1024" />
						</td>
					</tr>
					<tr>
						<td class="field_label" style="vertical-align:top;">Subcategory:</td>
						<td style="vertical-align:top;">
							<?=(isset($record->info['subcategory'])?$record->info['subcategory']:'');?>
						</td>
						<!--
						<td class="field_label" style="vertical-align:top;">Collections:</td>
						<td style="vertical-align:top;">
							<? //$record->output_collections_html(); ?>
						</td>
						-->
					</tr>
				</table>
			</div>
		</div>
		<div class="slimTabBlurb" tab="2">
			<table width="655">
				<tr>
					<td class="field_label">Design Ideas:</td>
					<td colspan="3">
						<textarea name="plant[description_design]" class="text_field full_width"><?=(isset($record->info['description_design'])?html_sanitize($record->info['description_design']):'')?></textarea>
					</td>
				</tr>
				<tr>
					<td class="field_label">Plant Benefits:</td>
					<td colspan="3">
						<textarea name="plant[description_benefits]" class="text_field full_width"><?=(isset($record->info['description_benefits'])?html_sanitize($record->info['description_benefits']):'')?></textarea>
					</td>
				</tr>
				<tr>
					<td class="field_label">Lore:</td>
					<td colspan="3">
						<textarea name="plant[description_lore]" class="text_field full_width"><?=(isset($record->info['description_lore'])?html_sanitize($record->info['description_lore']):'')?></textarea>
					</td>
				</tr>
				<tr>
					<td class="field_label">History:</td>
					<td colspan="3">
						<textarea name="plant[description_history]" class="text_field full_width"><?=(isset($record->info['description_history'])?html_sanitize($record->info['description_history']):'')?></textarea>
					</td>
				</tr>
				<tr>
					<td class="field_label">Companion Plants:</td>
					<td colspan="3">
						<textarea name="plant[description_companion_plants]" class="text_field full_width"><?=(isset($record->info['description_companion_plants'])?html_sanitize($record->info['description_companion_plants']):'')?></textarea>
					</td>
				</tr>
				<tr>
					<td class="field_label" width="115">Catalog Description:</td>
					<td colspan="3">
						<textarea name="plant[description_catalog]" class="text_field full_width"><?=(isset($record->info['description_catalog'])?html_sanitize($record->info['description_catalog']):'')?></textarea>
					</td>
				</tr>
			</table>
		</div>
		<div class="slimTabBlurb" tab="3">
			<table>
				<tr>
					<td class="field_label">Key Feature/Primary Attribute:</td>
					<td>
						<input id="plant_primary_attribute" name="plant[primary_attribute]" class="text_field full_width" maxlength="40" value="<?=(isset($record->info['primary_attribute'])?$record->info['primary_attribute']:'');?>" />
						<div class="auto_complete" autocomplete_type="primary_attribute"></div>
					</td>
				</tr>
			</table>
			<!-- GROUP -->
				<div class="field_group" style="width:512px;">
					<div class="attributes_container" attribute_type="garden_style">
						<h3 style="margin:0px 0px 0px 6px;">Garden Styles</h3>
						<table class="table_attributes">
							<tr>
								<td>
									<select id="plant_garden_style_name" class="text_field">
										<option value="">(Select one)</option>
										<? output_select_options('list_garden_style'); ?>
									</select>
									<input type="button" value="Add" onclick="add_plant_attribute(this,'plant_garden_style_name');" />
								</td>
							</tr>
							<tr>
								<td>
									<table id="table_garden_styles" class="table_attributes data_table">
										<thead>
											<tr style="background-color:#666;color:#fff;">
												<td>Name</td>
												<td width="1">Remove</td>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</td>
							</tr>
						</table>
					</div>
				</div>
			<!-- /GROUP -->
			<!-- GROUP -->
				<div class="field_group" style="width:512px;">
					<div class="attributes_container" attribute_type="special_feature">
						<h3 style="margin:0px 0px 0px 6px;">Special Features</h3>
						<table class="table_attributes">
							<tr>
								<td>
									<select id="plant_special_feature_name" class="text_field">
										<option value="">(Select one)</option>
										<? output_select_options('list_special_feature'); ?>
									</select>
									<input type="button" value="Add" onclick="add_plant_attribute(this,'plant_special_feature_name');" />
								</td>
							</tr>
							<tr>
								<td>
									<table id="table_special_features" class="table_attributes data_table">
										<thead>
											<tr style="background-color:#666;color:#fff;">
												<td>Name</td>
												<td width="1">Remove</td>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</td>
							</tr>
						</table>
					</div>
				</div>
			<!-- /GROUP -->
			<!-- GROUP -->
				<div class="field_group" style="width:512px;">
					<div class="attributes_container" attribute_type="problem_solution">
						<h3 style="margin:0px 0px 0px 6px;">Problems/Solutions</h3>
						<table class="table_attributes">
							<tr>
								<td>
									<select id="plant_problem_solution_name" class="text_field">
										<option value="">(Select one)</option>
										<? output_select_options('list_problem_solution'); ?>
									</select>
									<input type="button" value="Add" onclick="add_plant_attribute(this,'plant_problem_solution_name');" />
								</td>
							</tr>
							<tr>
								<td>
									<table id="table_problem_solutions" class="table_attributes data_table">
										<thead>
											<tr style="background-color:#666;color:#fff;">
												<td>Name</td>
												<td width="1">Remove</td>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</td>
							</tr>
						</table>
					</div>
				</div>
			<!-- /GROUP -->
			<!-- GROUP -->
				<div class="field_group" style="width:512px;">
					<div class="attributes_container" attribute_type="landscape_use">
						<h3 style="margin:0px 0px 0px 6px;">Landscape Uses</h3>
						<table class="table_attributes">
							<tr>
								<td>
									<select id="plant_landscape_use_name" class="text_field">
										<option value="">(Select one)</option>
										<? output_select_options('list_landscape_use'); ?>
									</select>
									<input type="button" value="Add" onclick="add_plant_attribute(this,'plant_landscape_use_name');" />
								</td>
							</tr>
							<tr>
								<td>
									<table id="table_landscape_uses" class="table_attributes data_table">
										<thead>
											<tr style="background-color:#666;color:#fff;">
												<td>Name</td>
												<td width="1">Remove</td>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</td>
							</tr>
						</table>
					</div>
				</div>
			<!-- /GROUP -->

			<div class="field_group" style="width:512px;">
				<div class="attributes_container">
					<h3 style="margin:0px 0px 0px 6px;">Attributes:</h3>
					<div style="padding-left:6px;"><?=(isset($record->info['attributes'])?html_sanitize($record->info['attributes']):'')?></div>
				</div>
			</div>
		</div>
		<div class="slimTabBlurb" tab="4">
			<div class="field_group">
				<h3 style="margin:0px 0px 0px 6px;">Foliage</h3>
				<table>
					<tr>
						<td class="field_label">Color:</td>
						<td>
							<select name="plant[foliage_color_id]" class="text_field" _value="<?=(isset($record->info['foliage_color_id'])?$record->info['foliage_color_id']:'');?>">
								<option value=""></option>
								<? output_select_options('list_foliage_color'); ?>
							</select>
						</td>
						<td class="field_label">Under Color:</td>
						<td class="text_field"><?=(isset($record->info['foliage_color_under'])?$record->info['foliage_color_under']:'');?></td>
					</tr>
					<tr>
						<td class="field_label">New Color:</td>
						<td class="text_field"><?=(isset($record->info['foliage_color_new'])?$record->info['foliage_color_new']:'');?></td>
					</tr>
					<tr>
						<td class="field_label">Seasonal Colors:</td>
					</tr>
					<tr>
						<td></td>
						<td colspan="3">
							<table class="table_nested" style="border:1px inset;">
								<tr>
									<td class="field_label">Winter:</td>
									<td class="text_field">
									<?=(isset($record->info['foliage_color_winter'])?$record->info['foliage_color_winter']:'');?></td>
									<td class="field_label">Spring:</td>
									<td class="text_field"><?=(isset($record->info['foliage_color_spring'])?$record->info['foliage_color_spring']:'');?></td>
								</tr>
								<tr>
									<td class="field_label">Summer:</td>
									<td class="text_field"><?=(isset($record->info['foliage_color_summer'])?$record->info['foliage_color_summer']:'');?></td>
									<td class="field_label">Fall:</td>
									<td class="text_field"><?=(isset($record->info['foliage_color_fall'])?$record->info['foliage_color_fall']:'');?></td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td class="field_label">Description:</td>
						<td colspan="3">
							<input name="plant[description_foliage]" class="text_field full_width" value="<?=(isset($record->info['description_foliage'])?$record->info['description_foliage']:'');?>" maxlength="40" />
						</td>
					</tr>
					<tr>
						<td class="field_label">Shape:</td>
						<td colspan="3">
							<?=(isset($record->info['foliage_shape'])?$record->info['foliage_shape']:'')?>
						</td>
					</tr>
				</table>
			</div>
			<div class="field_group">
				<h3 style="margin:0px 0px 0px 6px;">Flower</h3>
				<table>
					<tr>
						<td class="field_label">Color:</td>
						<td>
							<select name="plant[flower_color_id]" class="text_field" _value="<?=(isset($record->info['flower_color_id'])?$record->info['flower_color_id']:'');?>">
								<option value=""></option>
								<? output_select_options('list_flower_color'); ?>
							</select>
						</td>
						<td class="field_label">Flowering Time:</td>
						<td>
							<input id="plant_flowering_time" name="plant[flowering_time]" value="<?=(isset($record->info['flowering_time'])?$record->info['flowering_time']:'');?>" class="text_field" maxlength="255" />
							<div class="auto_complete" autocomplete_type="flowering_time"></div>
						</td>
					</tr>
					<tr>
						<td class="field_label">Flowering Seasons:</td>
						<td>
							<select name="plant[flowering_seasons][]" class="text_field" _value="<?=generate_attribute_id_csv((isset($record->info['flowering_seasons'])?$record->info['flowering_seasons']:''));?>" multiple style="height:100px;">
								<? output_select_options('list_flowering_season'); ?>
							</select>
						</td>
						<td class="field_label">Attributes:</td>
						<td>
							<select name="plant[flower_attributes][]" class="text_field" _value="<?=generate_attribute_id_csv((isset($record->info['flower_attributes'])?$record->info['flower_attributes']:''));?>" multiple style="height:100px;">
								<? output_select_options('list_flower_attribute'); ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="field_label">Description:</td>
						<td colspan="3">
							<?=(isset($record->info['description_flower'])?$record->info['description_flower']:'');?>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="slimTabBlurb" tab="5">
			<table id="table_growth_care">
				<tr>
						<td class="field_label">Growth habits:</td>
						<td>
							<select name="plant[growth_habits][]" class="text_field" _value="<?=generate_attribute_id_csv((isset($record->info['growth_habits'])?$record->info['growth_habits']:''));?>" multiple style="height:100px;">
								<? output_select_options('list_growth_habit'); ?>
							</select>
						</td>
				</tr>
				<tr>
					<td class="field_label">Growth Rate:</td>
					<td>
						<select name="plant[growth_rate_id]" class="text_field" _value="<?=(isset($record->info['growth_rate_id'])?$record->info['growth_rate_id']:'');?>">
							<option value=""></option>
							<? output_select_options('list_growth_rate'); ?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="field_label">Fertilizer:</td>
					<td>
						<select name="plant[fertilizer_id]" class="text_field" _value="<?=(isset($record->info['fertilizer_id'])?$record->info['fertilizer_id']:'');?>">
							<option value=""></option>
							<? output_select_options('list_fertilizer'); ?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="field_label">Spread:</td>
					<td>
						<select name="plant[spread_id]" class="text_field" _value="<?=(isset($record->info['spread_id'])?$record->info['spread_id']:'');?>">
							<option value=""></option>
							<? output_select_options('list_spread'); ?>
						</select>
					</td>
					<td class="field_label">Height:</td>
					<td>
						<select name="plant[height_id]" class="text_field" _value="<?=(isset($record->info['height_id'])?$record->info['height_id']:'');?>">
							<option value=""></option>
							<? output_select_options('list_height'); ?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="field_label">Pruning Time:</td>
					<td colspan="3">
						<input name="plant[pruning_time]" value="<?=(isset($record->info['pruning_time'])?$record->info['pruning_time']:'');?>" class="text_field full_width" maxlength="255" />
					</td>
				</tr>
				<tr>
					<td class="field_label">Average Landscape Size:</td>
					<td colspan="3">						
						<input name="plant[average_landscape_size]" value="<?=(isset($record->info['average_landscape_size'])?html_sanitize($record->info['average_landscape_size']):'')?>" class="text_field full_width" maxlength="128" /> 						
					</td>
				</tr>
				<tr>
					<td class="field_label">Propagation Method:</td>
					<td colspan="3">
						<input id="plant_propagation" name="plant[propagation]" class="text_field full_width" maxlength="40" value="<?=(isset($record->info['propagation'])?$record->info['propagation']:'');?>" />
						<div class="auto_complete" autocomplete_type="propagation"></div>
					</td>
				</tr>
				<tr>
					<td class="field_label">Care Instructions:</td>
					<td colspan="3">
						<textarea name="plant[description_care]" class="text_field full_width"><?=(isset($record->info['description_care'])?html_sanitize($record->info['description_care']):'')?></textarea>
					</td>
				</tr>
				<tr>
					<td class="field_label">Growth habit (historical):</td>
					<td colspan="3">
						<?=(isset($record->info['growth_habit'])?$record->info['growth_habit']:'');?>
					</td>
				</tr>
			</table>
		</div>
		<div class="slimTabBlurb" tab="6">
			<table>
				<tr>
					<td class="field_label">Water Requirement:</td>
					<td>
						<select name="plant[water_requirement_id]" class="text_field" _value="<?=(isset($record->info['water_requirement_id'])?$record->info['water_requirement_id']:'');?>">
							<option value=""></option>
							<? output_select_options('list_water_requirement'); ?>
						</select>
					</td>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td class="field_label">Water Requirement Details:</td>
					<td>
						<textarea id="plant_water_requirement_details" name="plant[water_requirement_details]" class="text_field full_width"><?=(isset($record->info['water_requirement_details'])?html_sanitize($record->info['water_requirement_details']):'')?></textarea>
					</td>
					<td class="field_label">Sun Exposure:</td>
					<td>
						<?
							// PRODUCE SUNSET ZONE CSV
							$sunset_zones = '';
							if(isset($record->info['sunset_zones'])){
								for($i=0;$i<count($record->info['sunset_zones']);$i++){
									$sunset_zones .= ','.$record->info['sunset_zones'][$i];
								}
							}
							if($sunset_zones!='') $sunset_zones = substr($sunset_zones,1);
						?>
						<select name="plant[sun_exposures][]" class="text_field" _value="<?=generate_attribute_id_csv((isset($record->info['sun_exposures'])?$record->info['sun_exposures']:''));?>" multiple style="height:100px;">
							<? output_select_options('list_sun_exposure'); ?>
						</select>
					</td>
				</tr>
				<tr>
						<td class="field_label">Cold Zone (low):</td>
						<td>
							<select name="plant[cold_zone_low]" class="text_field" _value="<?=(isset($record->info['cold_zone_low'])?$record->info['cold_zone_low']:'');?>">
								<option value=""></option>
								<? for($i=1;$i<=11;$i++){ ?>
									<option value="<?=$i?>"><?=$i?></option>
								<? } ?>
							</select>
						</td>
						<td class="field_label">Cold Zone (high):</td>
						<td>
							<select name="plant[cold_zone_high]" class="text_field" _value="<?=(isset($record->info['cold_zone_high'])?$record->info['cold_zone_high']:'');?>">
								<option value=""></option>
								<? for($i=1;$i<=11;$i++){ ?>
									<option value="<?=$i?>"><?=$i?></option>
								<? } ?>
							</select>
						</td>
				</tr>
				<tr>
					<td class="field_label">Sunset Zones:</td>
					<td>
						<select name="plant[sunset_zones][]" class="text_field" _value="<?=$sunset_zones?>" multiple style="height:300px;">
							<? for($i=1;$i<=45;$i++){ ?>
								<option value="<?=$i?>"><?=$i?></option>
							<? } ?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<div class="slimTabBlurb" tab="7">

		<style>
			.plant_image_segment {
				border:1px solid #ddd;
				/*padding:4px;*/
				width:500px;
				height:200px;
				font-size:9pt;
				margin-bottom:8px;
			}
			.plant_image_segment.primary {
				background-color:#fec;
			}
			.plant_image_segment.inactive {
				opacity:.5;
				filter:alpha(opacity=50);
				zoom:1;
			}
			.plant_image_segment .details_distributable {
				display:none;
			}
			.plant_image_segment.distributable .details_not_distributable {
				display:none;
			}
			.plant_image_segment.distributable .details_distributable {
				display:block;
			}

			.details_primary, .details_status_inactive {
				display:none;
				font-weight:bold;
			}
			.plant_image_segment.primary .details_primary {
				display:block;
			}
			.plant_image_segment.inactive .details_status_inactive {
				display:block;
			}
			.details_title {
				font-size:1.5em;
			}
			.details_links {
				margin-top:8px;
			}
			.details_links ul {
				margin:0px;
				padding:0px;
			}
			.details_links li {
				margin:0px;
				padding:0px;
				float:left;
				width:145px;
			}
			.details_links a {
				display:block;
				margin-bottom:2px;
				text-indent:12px;
			}
			.plant_image_segment .thumbnail {
				height:184px;
				width:154px;
				background-position:center;
				background-repeat:no-repeat;
				margin-right:8px;
				float:left;
			}
			.plant_image_segment .details_action {
				background-color:#ccc;
				margin-bottom:8px;
				padding:2px 0px 2px 0px;
			}
			.plant_image_segment .details_action a {
				margin:0px 4px 0px 4px;
			}
			.module_right {
				float:left;
				border:1px solid #ddd;
				/*padding:4px 8px 4px 8px;*/
				width:250px;
				height:200px;
				font-size:9pt;
				margin-left:16px;
				overflow:hidden;
				/*background:#fff url(img/loading.gif) center no-repeat;*/
			}
			.module_right .title {
				font-weight:bold;
			}
			.module_right .field_label {
				text-align:left;
				padding-top:6px;
				padding-bottom:2px;
			}
			.module_right .text_field {
				width:100%;
			}

			.module_right iframe {
				height:100%;
				width:100%;
				margin:0px;
				border:0px;
			}
			#table_plant_attributes input {
				#position:absolute;
				#margin-left:-10px;
				#margin-top:-3px;
			}
			#table_companion_plants_tbody td {
				vertical-align:middle;
			}
			.companion_plants_search_result {
				border-collapse:collapse;
				width:235px;
			}
			.companion_plants_search_result td {
				vertical-align:middle;
				font-size:8pt;
			}
			.module_right a {
				text-decoration:none;
			}
			.module_right a:hover table {
				background-color:#eee;
				cursor:pointer;
			}
			#companion_plants_search_results {
				margin-top:4px;
			}
			#table_companion_plants_tbody td {
				height:64px;
			}
			#modal_edit_plant .field_label {
				text-align:left;
			}
			.checkbox_group input {
				margin:0px 4px 0px 0px;
			}
		</style>

		<script>
			function refresh_image_sets(){
				new Ajax.Request('query_plant_images.php', {
				  method: 'post',
				  parameters:'id=<?=(isset($record->info['id'])?$record->info['id']:'')?>',
				  onSuccess:function(transport){
					  $('image_segments').update(transport.responseText);
				  }
				});
			}

/*
			function generate_image_base_name(plant_item_number,plant_common_name,image_title){
				return plant_item_number + '_' + parse_alphabetic_characters(plant_common_name) + '_' + parse_alphabetic_characters(image_title);
			}
*/
			function get_image_segment_data(id){
				var segment = $$('.plant_image_segment[image_set_id='+id+']')[0];
				var ret = [];
				ret['title'] = segment.down('.details_title').innerHTML;
				ret['expiration_date'] = segment.down('.details_expiration_date').innerHTML.replace('Expiration Date: ','');
				ret['source'] = segment.down('.details_source').innerHTML.replace('Source: ','');

				ret['credit'] = segment.down('.details_credit').innerHTML.replace('Credit: ','');
				ret['is_primary'] = segment.hasClassName('primary');
				ret['is_active'] = !segment.hasClassName('inactive');
				ret['is_distributable'] = segment.hasClassName('distributable');
				return ret;
			}
		</script>
			<? if(!isset($record->info['id'])||$record->info['id']==''){ ?>
				<h4>You will be able to add images after saving.</h4><!-- TODO: HIDE FOR EXISTING PLANT RECORDS -->
			<? }else{ ?>
				<h5>Note: Image updates, additions, and deletions are published immediately.</h5>
				<div style="float:left;" id="image_segments">
					<?php $record->output_cms_image_segments_html();?>
				</div>
				<div class="module_right" style="height:330px;">
					<div style="padding:4px 8px;height:100%;width:100%;">
						<iframe src="upload_image.php?plant_id=<?=(isset($record->info['id'])?$record->info['id']:'')?>" id="iframe_upload_image" frameborder="0"></iframe>
					</div>
				</div>
			<? } ?>
		</div>
		<div class="slimTabBlurb" tab="8">
			<div style="float:left;">
				<table id="table_companion_plants">
					<thead>
						<tr style="background-color:#666;color:#fff;">
							<td width="64">Image</td>
							<td>Name</td>
							<td width="1">Item&nbsp;#</td>
							<td width="1">Remove</td>
						</tr>
					</thead>
					<tbody id="table_companion_plants_tbody"></tbody>
				</table>
			</div>
			<div style="float:left;">
				<div class="module_right" style="margin-bottom:8px;background-image:none;height:auto;">
					<div style="padding:4px 8px 8px 8px;">
						<div class="title">Add A Companion Plant</div>
						<div id="companion_plant_input">
							<div class="field_label">Name or Number:</div><input class="text_field" type="text" size="40" id="companion_plant_query" />
							<input type="button" value="Search" style="margin-top:2px;" onclick="search_companion_plants();" />
						</div>
						<div id="companion_plants_search_results"></div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		function search_companion_plants(){
			$('companion_plant_input').setOpacity(.5);
			$('companion_plants_search_results').update('<center>Searching...</center>');
			var query = $('companion_plant_query').value;

			query = query.gsub('&','{{AMP}}');
			query = query.gsub('%','{{PERCENT}}');
			query = query.gsub('#','{{HASH}}');
			query = query.gsub('\\?','{{QUESTION}}');


			$('companion_plant_query').focus();
			new Ajax.Request('query_plants.php',{
			  method: 'post',
			  parameters:'query='+query,
			  onSuccess:function(transport){
				  $('companion_plants_search_results').update(transport.responseText);
				  $('companion_plant_input').setOpacity(1);
			  }
			});
		}
	</script>

	<script>
		var current_plant_image_id;
		//var plant_attributes = [<? // TODO $record->output_attributes_js(); ?>];
		//var companion_plants = [<? //$record->output_companion_plants_js(); ?>];

		var plant_attributes = [];
		// INITIALIZE ARRAYS FOR THE DIFFERENT ONE-TO-MANY ARRAYS
		plant_attributes['special_feature'] = [<? $record->output_attribute_js(isset($record->info['special_features'])?$record->info['special_features']:''); ?>];
		plant_attributes['garden_style'] = [<? $record->output_attribute_js(isset($record->info['garden_styles'])?$record->info['garden_styles']:''); ?>];
		plant_attributes['problem_solution'] = [<? $record->output_attribute_js(isset($record->info['problem_solutions'])?$record->info['problem_solutions']:''); ?>];
		plant_attributes['landscape_use'] = [<? $record->output_attribute_js(isset($record->info['landscape_uses'])?$record->info['landscape_uses']:''); ?>];

		function sanitize_quotes(attribute){
			return attribute.gsub('\'','\\\'');
		}
		function td(innerHTML) {
			var ret = document.createElement('td');
			ret.innerHTML = (innerHTML||'');
			return ret;
		}

//////////////////////////////////////

		function plant_attribute(id,name,is_historical,is_primary){
			this.id = id;
			this.name = name;
			this.is_historical = is_historical;
			this.is_primary = is_primary;
		}

		function remove_plant_attribute(element){
			var container = element.up('.attributes_container');
			var table_id = element.up('.data_table').id;
			var attribute_type = container.getAttribute('attribute_type');
			var attribute_id = element.getAttribute('attribute_id');
			for(var i=plant_attributes[attribute_type].length-1;i>-1;i--){
				if(plant_attributes[attribute_type][i].id==attribute_id){
					plant_attributes[attribute_type].splice(i,1);
					//break;
				}
			}
			refresh_table_data(table_id);
			//return data_source;
		}

		function add_plant_attribute(element,dropdown_id){
			var container = element.up('.attributes_container');
			var attribute_type = container.getAttribute('attribute_type');
			var option = $$('#'+dropdown_id + ' option[value="'+$(dropdown_id).value+'"]')[0];
			if($(dropdown_id).value){
				if(option&&!plant_attribute_present(attribute_type,option.getAttribute('value'))){
					plant_attributes[attribute_type].push(new plant_attribute(option.getAttribute('value'),option.getAttribute('attribute_name'),option.getAttribute('attribute_is_historical'),0));
					refresh_table_data(container.down('.data_table').id);
				}
			}
		}

		function plant_attribute_present(attribute_type,attribute_id){
			var attributes = plant_attributes[attribute_type];
			for(var i=0;i<attributes.length;i++){
				if(attributes[i].id==attribute_id) return true;
			}
		}

		function refresh_table_data(table_id){
			/*
			var primary_specified;
			for(var i=0;i<plant_attributes.length;i++){
				if(plant_attributes[i].primary){
					primary_specified = true;
					break;
				}
			}
			if(!primary_specified&&plant_attributes.length>0) plant_attributes[0].primary = true;
			*/
			//var data = plant_attributes;

			var container = $(table_id).up('.attributes_container');
			var attribute_type = container.getAttribute('attribute_type');
			var data = plant_attributes[attribute_type];
			var parent_element = $$('#'+table_id+' tbody')[0];
			parent_element.update('');
			var ids = '';
			for(var i=0;i<data.length;i++){
				ids += ',' + data[i].id;
				var tr = new Element('tr');
				if(i%2) tr.addClassName('row_even');
				tr.appendChild(new td(data[i].name));

				//var radio_button = '<input type="radio" value="'+data[i].id+'" name="plant_attributes_primary"'+((data[i].primary)?' checked':'')+' />';
				//var td_col_primary = new td(radio_button);
				//td_col_primary.className = 'centered';
				//tr.appendChild(td_col_primary);

				var td_col_remove = new td('<span onclick="remove_plant_attribute(this);" attribute_id="'+data[i].id+'" class="link_remove">Remove</span>');
				tr.appendChild(td_col_remove);
				parent_element.appendChild(tr);
			}
			if(ids) ids = ids.substr(1);
			get_field('plant['+attribute_type+'_ids]').value = ids;
		}


//////////////////////////////////////

		function companion_plant(id,name,url,item_number,image_url){
			this.id = id;
			this.name = name;
			this.url = url;
			this.item_number = item_number;
			this.image_url = image_url;
		}

		function remove_companion_plant(item_number){
			for(var i=0;i<companion_plants.length;i++){
				if(companion_plants[i].item_number==item_number){
					companion_plants.splice(i,1);
					break;
				}
			}
			refresh_companion_plants_table_data();
		}
		function add_companion_plant(data){
			if(!companion_plants.find(function(obj_companion_plant){
				return obj_companion_plant.item_number==data.item_number;
			})){
				companion_plants.push(data);
				refresh_companion_plants_table_data();
			}
		}

		function refresh_companion_plants_table_data(){
			var data = companion_plants;
			var parent_element = $('table_companion_plants_tbody');
			parent_element.update('');
			for(var i=0;i<data.length;i++){
				var tr = new Element('tr');
				if(i%2) tr.addClassName('row_even');
				var td_col_image = new td('<a href="'+data[i].url+'"><img src="'+data[i].image_url+'" /></a>');
				tr.appendChild(td_col_image);
				tr.appendChild(new td(data[i].name));
				tr.appendChild(new td(data[i].item_number));
				var td_col_remove = new td('<a href="javascript:remove_companion_plant(\''+data[i].item_number+'\');void(0);">Remove</a>');
				tr.appendChild(td_col_remove);
				parent_element.appendChild(tr);
			}
		}

		function edit_plant_image_update(){
			var title = $('edit_plant_title').value;
			var credit = $('edit_plant_credit').value;
			var expiration_date = $('edit_plant_expiration_date').value;
			var source = $('edit_plant_source').value;

			var is_primary = ($('edit_plant_is_primary').checked)?'1':'0';
			var is_active = ($('edit_plant_is_active').checked)?'1':'0';
			var is_distributable = ($('edit_plant_is_distributable').checked)?'1':'0';

			var id = current_plant_image_id;
			var plant_item_number = get_field('plant[item_number]').value;
			var plant_common_name = get_field('plant[common_name]').value;

			if(!title){ alert('Please specify a title for this image.'); return false; }
			if(is_primary=='1'&&is_active=='0'){ alert('The primary image cannot be inactive.'); return false; }
			if(expiration_date&&!is_valid_mysql_date(expiration_date)){ alert('The expiration date must be in this format: yyyy-mm-dd.'); return false; }

			modal_hide();
			new Ajax.Request('update_image_set.php', {
			  method: 'post',
			  parameters:'id='+id+'&plant_id=<?=(isset($record->info['id'])?$record->info['id']:'')?>&title='+title+'&credit='+credit+'&is_primary='+is_primary+'&is_active='+is_active+'&is_distributable='+is_distributable+'&expiration_date='+expiration_date+'&source='+source,
			  onSuccess:refresh_image_sets
			});
		}
		function edit_plant_image_delete(id){
			if(!confirm('Warning: Deleting an image is irreversible.\n\nDelete this image?')) return;

			new Ajax.Request('update_image_set.php', {
			  method: 'post',
			  parameters:'id='+id+'&action=delete',
			  onSuccess:function(transport){
				  refresh_image_sets();
			  }
			});
		}
		function edit_plant_image(id){
			current_plant_image_id = id;
			modal_show({'modal_id':'modal_edit_plant','effect':'fade'});
		}////////////////////////////////////////
		Event.observe(window,'load',function(){
			refresh_table_data('table_garden_styles');
			refresh_table_data('table_special_features');
			refresh_table_data('table_problem_solutions');
			refresh_table_data('table_landscape_uses');

			//refresh_companion_plants_table_data();
		});

		function delete_record(){
			window.location.href = '?id='+record_id+'&action=delete';
		}
	</script>
		<input type="hidden" name="id" value="<?=(isset($record->info['id'])?$record->info['id']:'')?>" />
		<input type="hidden" name="plant[garden_style_ids]" />
		<input type="hidden" name="plant[special_feature_ids]" />
		<input type="hidden" name="plant[problem_solution_ids]" />
		<input type="hidden" name="plant[landscape_use_ids]" />

		<input type="hidden" name="plant[companion_plant_ids]" />
		<input type="hidden" name="action" value="save" />
	</form>
<?
	function output_modals(){
	?>
		<div class="modal_dialog" id="modal_edit_plant">
			<table class="modal_dialog_backing">
				<tr>
					<td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
				</tr>
				<tr>
					<td class="corner corner_left"></td>
					<td class="corner corner_middle" style="width:210px;">
						<h5 style="margin:0px 0px 8px 0px;">Edit Image</h5>
						<div class="field_label">Title:</div>
						<input class="text_field" id="edit_plant_title" maxlength="40" />
						<div style="margin-top:6px;">
							<div class="checkbox_group">
								<div style="float:left;"><input type="checkbox" id="edit_plant_is_active" /><label for="image_status" class="field_label">Active</label></div>
								<div style="padding-left:75px;"><input type="checkbox" id="edit_plant_is_primary" /><label for="image_primary" class="field_label">Primary Image</label></div>
								<div><input type="checkbox" id="edit_plant_is_distributable" /><label for="image_distributable" class="field_label">Distributable (allow hi-res downloads)</label></div>
							</div>
							<div class="field_label" style="padding-top:8px;">Photography Credit:</div>
							<input class="text_field" id="edit_plant_credit" maxlength="40" />
							<div class="field_label" style="padding-top:8px;">Expiration Date:</div>
							<input class="text_field" id="edit_plant_expiration_date" maxlength="10" />
							<div class="field_label" style="padding-top:8px;">Source:</div>
							<input class="text_field" id="edit_plant_source" maxlength="40" />
							<div style="display:block;height:8px;"></div>
							<input type="button" value="OK" onclick="edit_plant_image_update();" />
							<input type="button" value="Cancel" onclick="modal_hide();" />
						</div>
					</td>
					<td class="corner corner_right"></td>
				</tr>
				<tr>
					<td class="corner corner_bottomleft"></td><td class="corner corner_bottom"><td class="corner corner_bottomright"><td>
				</tr>
			</table>
		</div>
		<div class="modal_dialog" id="modal_delete_plant">
			<table class="modal_dialog_backing">
				<tr>
					<td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
				</tr>
				<tr>
					<td class="corner corner_left"></td>
					<td class="corner corner_middle" style="width:210px;">
						<h5 style="margin:0px 0px 8px 0px;">Delete Record</h5>
						<div style="font-size:9pt;width:500px;">Are you sure you want to permanently delete this record and all related information from the database?<br /><br />You may alternatively deactivate this record, which will prevent it from appearing on the website.<br /><br /><b>Click OK to proceed and permanently delete this record.</b></div>
						<div style="margin-top:6px;">
							<div style="display:block;height:8px;"></div>
							<input type="button" value="Cancel" onclick="modal_hide();" style="float:right;" />
							<input type="button" value="OK" onclick="delete_record();" style="color:#900;" />
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
?>
<? include('inc/footer.php'); ?>