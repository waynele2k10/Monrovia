<?php

	$catalog_limit = 500;


	// REQUIRED
	$top_level_section = '';
	$page_title = 'Monrovia Sales Catalogs';
	$page_meta_description = '';
	
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_catalog.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/header.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_search_plant.php');

	$plant_images = array();

	if(!ini_get('safe_mode')) set_time_limit(60); // ALLOW UP TO 1 MINUTE

	//////////////////

	function before_output(){
		// MAKE SURE USER IS LOGGED IN AND SALES CATALOG BELONGS TO USER
		global $catalog_id, $catalog, $monrovia_user;
		$catalog_id = intval($_GET['id']);

		// MAKE SURE USER HAS CATALOG PERMISSION
		$monrovia_user->permission_requirement('pdfs');

		if($catalog_id==0){
			if($_GET['id']=='new'){
				$catalog = new catalog();
				$catalog->info['user_id'] = $monrovia_user->info['id'];

				if(isset($_GET['original_id'])){
					// DUPLICATE
					$original_id = intval($_GET['original_id']);
					$original_catalog = new catalog($original_id);
					
					$original_catalog_exists = ($original_catalog->info['id']!='');
					$original_catalog_belongs_to_user = ($original_catalog->info['user_id']==$monrovia_user->info['id']);
					$original_catalog_is_official = ($original_catalog->info['is_official_catalog']=='1');
					
					if(!$original_catalog_exists||!($original_catalog_is_official||$original_catalog_belongs_to_user)){
						header('location:../catalogs/');
						exit;
					}
					
					// ASSIGN ORIGINAL CATALOG ID
					if($original_catalog_is_official){
						$catalog->info['original_catalog_id'] = $original_catalog->info['id'];
					}else{
						$catalog->info['original_catalog_id'] = $original_catalog->info['original_catalog_id'];
					}

					// IF BASED ON 2012 CATALOG, MAKE SURE CUSTOM TEMPLATE ISN'T USED; OTHERWISE, USE CUSTOM COVER
					if(isset($original_catalog->info['original_catalog_id'])&&$catalog->info['original_catalog_id']=='1'){
						$catalog->info['template_id'] = '';
					}else{
						$catalog->info['template_id'] = $original_catalog->info['template_id'];
					}

					// PREPARING COPY FOR THE FIRST TIME
					$catalog->info['name'] = 'Copy of ' . $original_catalog->info['name'];
					$catalog->info['plant_count'] = $original_catalog->info['plant_count'];
					$catalog->info['plant_ids'] = $original_catalog->info['plant_ids'];

					// COPY CATALOG COVER FIELDS
					$catalog->info['title'] = trim($original_catalog->info['title']);
					$catalog->info['customer_contact'] = trim($original_catalog->info['customer_contact']);
					$catalog->info['customer'] = trim($original_catalog->info['customer']);
					$catalog->info['sales_rep_name'] = trim($original_catalog->info['sales_rep_name']);
					$catalog->info['additional_info_1'] = trim($original_catalog->info['additional_info_1']);
					$catalog->info['additional_info_2'] = trim($original_catalog->info['additional_info_2']);
					$catalog->info['additional_info_3'] = trim($original_catalog->info['additional_info_3']);
					$catalog->info['monrovia_locations'] = trim($original_catalog->info['monrovia_locations']);
					
					// IF NEW CATALOG, DEFAULT TO ALL LOCATIONS
					if($catalog->info['monrovia_locations']=='') $catalog->info['monrovia_locations'] = 'azusa,visalia,dayton,cairo,lagrange';

					$catalog->info['plant_1_image_set_id'] = trim($original_catalog->info['plant_1_image_set_id']);
					$catalog->info['plant_2_image_set_id'] = trim($original_catalog->info['plant_2_image_set_id']);
					$catalog->info['plant_3_image_set_id'] = trim($original_catalog->info['plant_3_image_set_id']);
				}
			}else{
				header('location:../catalogs/');
				exit;				
			}
		}else{
			$catalog = new catalog($catalog_id);
			$catalog->generate_covers(true);
			if($catalog->info['user_id']!=$monrovia_user->info['id']){
				header('location:../catalogs/');
				exit;
			}
		}

		// SAVE
		if(isset($_POST['save'])&&$_POST['save']=='1'){
			$name = trim($_POST['catalog']['name']);
			$plant_ids = trim(trim($_POST['catalog']['plant_ids'],','));
			
			$catalog->get_plant_count();
			if(intval($catalog->info['plant_count'])>$GLOBALS['catalog_limit']){
				header('location:../catalogs/?msg=error_not_saved');
				exit;
			}else{
				if($name!=''){
					$catalog->info['name'] = $name;
					$catalog->info['plant_ids'] = $plant_ids;

					// CATALOG COVER FIELDS
					if(isset($catalog->info['template_id'])&&intval($catalog->info['template_id'])>0){
						$catalog->info['title'] = trim($_POST['catalog']['title']);
						$catalog->info['customer_contact'] = trim($_POST['catalog']['customer_contact']);
						$catalog->info['customer'] = trim($_POST['catalog']['customer']);
						$catalog->info['sales_rep_name'] = trim($_POST['catalog']['sales_rep_name']);
						$catalog->info['additional_info_1'] = trim($_POST['catalog']['additional_info_1']);
						$catalog->info['additional_info_2'] = trim($_POST['catalog']['additional_info_2']);
						$catalog->info['additional_info_3'] = trim($_POST['catalog']['additional_info_3']);
						$catalog->info['monrovia_locations'] = trim($_POST['catalog']['monrovia_locations']);

						$catalog->info['plant_1_image_set_id'] = trim($_POST['catalog']['plant_1_image_set_id']);
						$catalog->info['plant_2_image_set_id'] = trim($_POST['catalog']['plant_2_image_set_id']);
						$catalog->info['plant_3_image_set_id'] = trim($_POST['catalog']['plant_3_image_set_id']);
					}
					
					if($catalog->save()){
						header('location:../catalogs/?msg=saved');
						exit;
					}else{
						header('location:../catalogs/?msg=error_not_saved');
						exit;
					}
				}			
			}
		}

	}
	
	function get_plant_id_and_verify_image_set($image_set_id){
		$result = sql_query('SELECT plants.id AS plant_id,plant_image_sets.id AS image_set_id FROM plants INNER JOIN plant_image_sets ON plant_image_sets.plant_id = plants.id WHERE plants.is_active=1 AND plants.release_status_id IN (1,2,3,6) AND plant_image_sets.is_active=1 AND plant_image_sets.is_distributable=1 AND (plant_image_sets.expiration_date>NOW() OR plant_image_sets.expiration_date="0000-00-00") AND plant_image_sets.id = "' . intval($image_set_id) . '"');
		
		if(mysql_numrows($result)==1){
			return array('plant_id'=>mysql_result($result,0,'plant_id'),'image_set_id'=>mysql_result($result,0,'image_set_id'));
		}else{
			return array('plant_id'=>null,'image_set_id'=>null);
		}
	}
	
	function output_action_group(){
	?>
		<div class="action_group">
			<a href="javascript:select_all();void(0);">select all</a>&nbsp;&nbsp;|&nbsp;&nbsp;
			<a href="javascript:unselect_all();void(0);">unselect all</a>&nbsp;&nbsp;|&nbsp;&nbsp;
			<a href="javascript:add_selected_plants();void(0);">add selected plants &raquo;</a>&nbsp;&nbsp;
		</div>
		<div style="clear:both;"></div>
	<?
	}
	
	function output_modals(){
		global $catalog, $plant_images;
		
	
		// PLANT IMAGES
		$plant_images = array();
		
		if(isset($catalog->info['plant_1_image_set_id'])&&intval($catalog->info['plant_1_image_set_id'])>0){
			$plant_images[] = get_plant_id_and_verify_image_set(intval($catalog->info['plant_1_image_set_id']));
		}else{
			$plant_images[] = array(
				'plant_id'=>null,
				'image_set_id'=>null
			);
		}
		if(isset($catalog->info['plant_2_image_set_id'])&&intval($catalog->info['plant_2_image_set_id'])>0){
			$plant_images[] = get_plant_id_and_verify_image_set(intval($catalog->info['plant_2_image_set_id']));
		}else{
			$plant_images[] = array(
				'plant_id'=>null,
				'image_set_id'=>null
			);
		}
		if(isset($catalog->info['plant_3_image_set_id'])&&intval($catalog->info['plant_3_image_set_id'])>0){
			$plant_images[] = get_plant_id_and_verify_image_set(intval($catalog->info['plant_3_image_set_id']));
		}else{
			$plant_images[] = array(
				'plant_id'=>null,
				'image_set_id'=>null
			);
		}

		
		
	?>
	<style>
	    .plant_image_field, .plant_image_field select {
	        width:195px;
	    }
	    .plant_image_field {
	        margin-right:10px;
	    }
	    .modal_dialog_backing h1 {
	        font-size:20px;
	        
	    }
	    .halfsize{	        
	        width:240px;
	        padding-right:10px;
	    }
	    .left {
	        float:left;
	    }
	    #add_location {
	        width:224px;
	    }
	    .field_group .text_field {
          width: 234px;
        }
	    div.slimTabBlurb {
	        padding-top: 10px;
	    }
	    .plant_image_field {
            background: url(/img/loading_E5E2BF.gif) center 45px no-repeat; 
        }
		#csv-import-form-progress{
			width:100px;
		}
		#csv-import-form-progress-bar{
			width:0;
			height:10px;
			background-color: green;
		}
		#results_column{
			position: relative;
			overflow-x: hidden;
			overflow-y: auto;
			height: 1250px;
		}
		#results_table{
			position: absolute;
		}

	</style>
		<div id="modal_error" class="modal_dialog" style="text-align:center;padding-top:10px;color:#000;font-weight:bold;">
			<table class="modal_dialog_backing">
				<tr>
					<td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
				</tr>
				<tr>
					<td class="corner corner_left"></td>
					<td class="corner_middle">
						<div style="width:400px;">
							<div id="modal_error_message"></div>
							<div style="padding-top:12px;bottom:0px;position:relative;text-align:center;">
								<div class="btn_green" style="width:110px;margin:auto;">
									<img src="/img/spacer.gif" class="side_left" /><a href="javascript:void(0);" onclick="modal_hide();">close</a><img src="/img/spacer.gif" />
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
		<div id="modal_directions" class="modal_dialog" style="text-align:center;padding-top:10px;color:#000;font-weight:bold;">
			<table class="modal_dialog_backing">
				<tr>
					<td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
				</tr>
				<tr>
					<td class="corner corner_left"></td>
					<td class="corner_middle">
						<div style="width:640px;text-align:left;font-weight:normal;">
							<h1>help</h1>
							<br />
							How to create customized catalogs for your customers:
							<br /><br />
							<div style="width:50%;float:left;">
								<div class="padding" style="padding-right:16px;">
									<b>Step 1</b>: Name the catalog (on the right side of the page) for easy recall.
									<br />
									<img src="/img/catalog_directions_edit_name.gif" style="margin-top:.5em;width:182px;height:81px;" />
								</div>
							</div>
							<div style="width:50%;float:left">
								<div class="padding">
									<b>Step 2</b>: Search for the plants you want to include, by using one of the search options on the left.  
									<br />
									<img src="/img/catalog_directions_edit_search.gif" style="margin-top:.5em;width:142px;height:106px;" />
								</div>
							</div>
							<div style="clear:both;"></div>
							<div style="float:left;">
								<div class="padding">
									<b>Step 3</b>: To make a plant selection, click on the checkboxes and when finished, choose "add selected plants." Your plants will appear in the column on the right. Plants may be removed if you change your mind. You may also click and drag plants individually or by multiples to reorder them.
									<br />
									<img src="/img/catalog_directions_edit_select.gif" style="margin-top:.5em;width:111px;height:74px;" />
								</div>
							</div>
							<div style="clear:both;"></div>
							<div style="float:left">
								<div class="padding">
									<b>Step 4</b>: Save the catalog.
									<br /><br />
									<b>Step 5</b>: Click "generate PDF" and choose your sorting method. A link to your catalog will be emailed to you.
									<br />
									<img src="/img/catalog_directions_edit_generate.gif" style="margin-top:.5em;width:240px;height:123px;" />
								</div>
							</div>
							<div style="clear:both;"></div>
							<div style="padding-top:12px;bottom:0px;position:relative;text-align:center;">
								<div class="btn_green" style="width:110px;margin:auto;">
									<img src="/img/spacer.gif" class="side_left" /><a href="javascript:void(0);" onclick="modal_hide();">close</a><img src="/img/spacer.gif" />
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

		<? if(isset($catalog->info['template_id'])&&intval($catalog->info['template_id'])>0){ ?>
			<div id="modal_edit_cover" class="modal_dialog" style="text-align:center;color:#000;font-weight:bold;">
				<table class="modal_dialog_backing">
					<tr>
						<td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
					</tr>
					<tr>
						<td class="corner corner_left"></td>
						<td class="corner_middle">
							<h1>Edit Catalog Covers</h1>
							<div class="slimTabContainer" id="ctlTabsEditCovers">
								<div>
									<div class="slimTab spacer" style="width:4px">&nbsp;</div>
									<div class="slimTab selected" tab="1">Front and back covers</div>
									<div class="slimTab" tab="2">Front cover</div>
									<div class="slimTab" tab="3">Back cover</div>
									<div class="slimTab spacer" style="width:245px;">&nbsp;</div>
								</div>
								<div class="slimTabBlurb sel" tab="1">
									<div class="field_groups">
										<div class="field_group">
											<label>Title</label>
											<input class="text_field" maxlength="40" name="cover[title]" mapped_field="title" value="<?=(isset($catalog->info['title'])?html_sanitize($catalog->info['title']):'')?>" />
											<div class="example">
												Example: New Plants for <?=date('Y');?>
											</div>
										</div>
									</div>
									<div class="cover_images">
										<div class="cover_image">
											<img src="<?=$catalog->info['cover_image_paths']['front']?>?rnd=<?=time()?>" class="cover_thumbnail_front" />
											<div class="outline" style="left:13px;top:53px;width:125px;height:5px;" mapped_field="title"></div>
										</div>
										<label>Front cover</label>
										<div class="cover_image">
											<img src="<?=$catalog->info['cover_image_paths']['back']?>?rnd=<?=time()?>" class="cover_thumbnail_back" />
											<div class="outline" style="left:9px;top:108px;width:64px;height:2px;" mapped_field="title"></div>
											<div class="outline" style="left:9px;top:70px;width:44px;height:2px;" mapped_field="customer"></div>
										</div>
										<label>Back cover</label>
									</div>
									<div style="clear:both;"></div>
								</div>
								<div class="slimTabBlurb" tab="2">
									<div class="field_groups">
										<div class="field_group">
											<label>Customer</label>
											<input class="text_field" maxlength="40" name="cover[customer]" mapped_field="customer" value="<?=(isset($catalog->info['customer'])?html_sanitize($catalog->info['customer']):'')?>" />
											<div class="example">
												Example: Acme Garden Centers
											</div>
										</div>
										<div class="field_group">
											<label>Customer Contact</label>
											<input class="text_field" maxlength="40" name="cover[customer_contact]" mapped_field="customer_contact" value="<?=(isset($catalog->info['customer_contact'])?html_sanitize($catalog->info['customer_contact']):'')?>" />
											<div class="example">
												Example: John Smith
											</div>
										</div>
										<div class="field_group">
											<h2 style="margin-top:.5em;">Plant Images</h2>
											<div class="instructions">
												You may choose up to three plant images to include on the front cover based on the plants you have in your catalog.
												<br /><br />
												<strong>Note: Thumbnails displaying a question mark indicate that no image is available for the plant, and that a random plant image will be used instead.</strong>
											</div>								
                                        </div>
									</div>
									<div class="cover_images">
										<div class="cover_image">
											<img src="<?=$catalog->info['cover_image_paths']['front']?>?rnd=<?=time()?>" class="cover_thumbnail_front" />
											<div class="outline" style="left:13px;top:161px;width:48px;height:2px;" mapped_field="customer"></div>
											<div class="outline" style="left:11px;top:166px;width:48px;height:2px;" mapped_field="customer_contact"></div>
											<div class="outline" style="left:0px;top:88px;width:49px;height:44px;" mapped_field="plant_1"></div>
											<div class="outline" style="left:52px;top:88px;width:47px;height:44px;" mapped_field="plant_2"></div>
											<div class="outline" style="left:103px;top:88px;width:47px;height:44px;" mapped_field="plant_3"></div>
										</div>
										<label>Front cover</label>
									</div>
									<div style="clear:both;"></div>
									<div class="plant_image_field">
                                        <? if(isset($catalog->info['plant_1_image_set_id'])&&intval($catalog->info['plant_1_image_set_id'])>0){ ?>
                                             <img src="/img/plants/image_set_thumbail.php?id=<?=$catalog->info['plant_1_image_set_id']?>&width=90&height=90" class="image_thumbnail" />
                                        <? }else{ ?>
                                             <img src="/img/catalog_cover_plant_random.gif" class="image_thumbnail" />
                                        <? } ?>
                                        <div class="fields">
                                             <label>Plant image (left)</label>
                                             <select name="cover[plant_1_id]" mapped_field="plant_1" _value="<?=$plant_images[0]['plant_id']?>" onchange="plant_dropdown_changed('1',window.event||event);">
                                                   <option value="">(Random plant)</option>
                                              </select>
                                              <label>Plant image</label>
                                               <select name="cover[plant_1_image_set_id]" mapped_field="plant_1" _value="<?=$plant_images[0]['image_set_id']?>" onchange="plant_image_dropdown_changed('1',window.event||event);"></select>
                                        </div>
                                     </div>

                                     <div class="plant_image_field">
                                          <? if(isset($catalog->info['plant_2_image_set_id'])&&intval($catalog->info['plant_2_image_set_id'])>0){ ?>
                                              <img src="/img/plants/image_set_thumbail.php?id=<?=$catalog->info['plant_2_image_set_id']?>&width=90&height=90" class="image_thumbnail" />
                                          <? }else{ ?>
                                              <img src="/img/catalog_cover_plant_random.gif" class="image_thumbnail" />
                                          <? } ?>
                                          <div class="fields">
                                               <label>Plant image (center)</label>
                                               <select name="cover[plant_2_id]" mapped_field="plant_2" _value="<?=$plant_images[1]['plant_id']?>" onchange="plant_dropdown_changed('2',window.event||event);">
                                                    <option value="">(Random plant)</option>
                                               </select>
                                               <label>Plant image</label>
                                               <select name="cover[plant_2_image_set_id]" mapped_field="plant_2" _value="<?=$plant_images[1]['image_set_id']?>" onchange="plant_image_dropdown_changed('2',window.event||event);"></select>
                                           </div>
                                      </div>

                                      <div class="plant_image_field">
                                           <? if(isset($catalog->info['plant_3_image_set_id'])&&intval($catalog->info['plant_3_image_set_id'])>0){ ?>
                                                <img src="/img/plants/image_set_thumbail.php?id=<?=$catalog->info['plant_3_image_set_id']?>&width=90&height=90" class="image_thumbnail" />
                                           <? }else{ ?>
                                                <img src="/img/catalog_cover_plant_random.gif" class="image_thumbnail" />
                                           <? } ?>
                                           <div class="fields">
                                               <label>Plant image (right)</label>
                                               <select name="cover[plant_3_id]" mapped_field="plant_3" _value="<?=$plant_images[2]['plant_id']?>" onchange="plant_dropdown_changed('3',window.event||event);">
                                                    <option value="">(Random plant)</option>
                                               </select>
                                               <label>Plant image</label>
                                               <select name="cover[plant_3_image_set_id]" mapped_field="plant_3" _value="<?=$plant_images[2]['image_set_id']?>" onchange="plant_image_dropdown_changed('3',window.event||event);"></select>
                                           </div>
                                       </div>
                                       <div style="clear:both;"></div>
								</div>
								<div class="slimTabBlurb" tab="3">
									<div class="field_groups">
										<div class="field_group">
											<label>Sales rep name</label>
											<input class="text_field" maxlength="40" name="cover[sales_rep_name]" mapped_field="sales_rep_name" value="<?=(isset($catalog->info['sales_rep_name'])?html_sanitize($catalog->info['sales_rep_name']):'')?>" />
											<div class="example">
												<?
													$example = 'John Smith';
													if(isset($GLOBALS['monrovia_user']->info['last_name'])&&$GLOBALS['monrovia_user']->info['last_name']!='') $example = trim($GLOBALS['monrovia_user']->info['first_name'] . ' ' . $GLOBALS['monrovia_user']->info['last_name']);
												?>
												Example: <?=$example?>
											</div>
										</div>
										<div class="field_group halfsize left">
											<label>Additional info, line 1</label>
											<input class="text_field" maxlength="40" name="cover[additional_info_1]" mapped_field="additional_info_1" value="<?=(isset($catalog->info['additional_info_1'])?html_sanitize($catalog->info['additional_info_1']):'')?>" />
											<div class="example">
												Examples: Address, Phone, Fax, Website
											</div>
										</div>
										<div class="field_group halfsize left">
											<label>Additional info, line 2</label>
											<input class="text_field" maxlength="40" name="cover[additional_info_2]" mapped_field="additional_info_2" value="<?=(isset($catalog->info['additional_info_2'])?html_sanitize($catalog->info['additional_info_2']):'')?>" />
											<div class="example">
												Examples: Address, Phone, Fax, Website
											</div>

										</div>
										<div class="field_group">
											<label>Additional info, line 3</label>
											<input class="text_field" maxlength="40" name="cover[additional_info_3]" mapped_field="additional_info_3" value="<?=(isset($catalog->info['additional_info_3'])?html_sanitize($catalog->info['additional_info_3']):'')?>" />
											<div class="example">
												Examples: Address, Phone, Fax, Website
											</div>
										</div>
										<h2 style="margin-top:.5em;">Monrovia Locations</h2>
										<div class="field_group halfsize left">
											<div id="add_location">
												<label>Add a location</label>
												<div>
													<select id="select_locations" style="float:left;width:140px;margin-right:6px;margin-top:2px;" mapped_field="monrovia_locations">
														<option value="">(Select a location)</option>
													</select>
													<div class="btn_green" style="float:left;width:50px;">
														<img src="/img/spacer.gif" class="side_left" /><a href="javascript:void(0);" onclick="add_monrovia_location();">add</a><img src="/img/spacer.gif" />
													</div>
													<div style="clear:both;"></div>
												</div>
											</div>
											<table id="table_monrovia_locations">
												<tbody></tbody>
											</table>
										</div>
										<div class="instructions left halfsize">
                                            The Monrovia locations you select will be displayed at the bottom of the back cover. When creating a new catalog, all five locations are included by default.
                                        </div>
									</div>
									<div class="cover_images">
										<div class="cover_image">
											<img src="<?=$catalog->info['cover_image_paths']['back']?>?rnd=<?=time()?>" class="cover_thumbnail_back" />
											<div class="outline" style="left:9px;top:66px;width:32px;height:2px;" mapped_field="sales_rep_name"></div>
											<div class="outline" style="left:9px;top:71px;width:32px;height:3px;" mapped_field="additional_info_1"></div>
											<div class="outline" style="left:9px;top:74px;width:32px;height:3px;" mapped_field="additional_info_2"></div>
											<div class="outline" style="left:9px;top:78px;width:32px;height:3px;" mapped_field="additional_info_3"></div>
											<div class="outline" style="left:9px;top:125px;width:80px;height:56px;" mapped_field="monrovia_locations"></div>
										</div>
										<label>Back cover</label>
									</div>
									<div style="clear:both;"></div>
								</div>
							</div>
						
							<div style="">
								<div style="padding-top:12px;bottom:0px;position:relative;text-align:center;">
									<div class="btn_green" style="width:110px;margin:auto;">
										<img src="/img/spacer.gif" class="side_left" /><a href="javascript:void(0);" onclick="modal_hide();show_unobtrusive_message('Your changes will not be saved until you click the &quot;Save Changes&quot; button.',5000);">OK</a><img src="/img/spacer.gif" />
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
		<? } ?>

	<?
	}
	
	function output_search_multiselect($table_name,$field_id,$table_title,$css_class = 'five_col'){

		// CUSTOM
		switch($field_id){
			case 'cold_zone':
				$list_records = array();
				for($i=1;$i<=11;$i++){
					$list_records[] = array('id'=>$i,'name'=>'Zone '.$i);
				}
			break;
			default:
				$list_records = get_table_data($table_name);
		}

		?>
			<div class="module_multiselect <?=$css_class?>" id="multiselect_<?=$field_id?>">
				<div class="list_content">
						<?
							for($i=0;$i<count($list_records);$i++){
								if(!isset($list_records[$i]['is_historical'])||$list_records[$i]['is_historical']!='1'){
									echo("<span value=\"".$list_records[$i]['id']."\">".html_sanitize($list_records[$i]['name'])."</span><div></div>");
								}
							}
						?>
				</div>
			</div>
		<?
	}
	
	// GET PLANTS

	$json_results = '[]';

	if(isset($catalog->info['plant_ids'])){
		$catalog->info['plant_ids'] = trim($catalog->info['plant_ids'],',');	
		
		if($catalog->info['plant_ids']!=''){

			$sorting_method = 'FIELD(plants.id,'.$catalog->info['plant_ids'].')';
			
			$search = new search_plant('','id,is_active,item_number,common_name,botanical_name,collection_name,cold_zone_low,cold_zone_high');
			$search->order_by = $sorting_method . ' ASC';
			$search->results_per_page = 10000;
			//$search->max_pagination_links = 6;

			$search->criteria['id'] = explode(',',$catalog->info['plant_ids']);

			$search->criteria['release_status_id'] = array();
			$search->criteria['release_status_id'][] = '1'; // A (ACTIVE)
			$search->criteria['release_status_id'][] = '2'; // NA (NEW/ACTIVE)
			$search->criteria['release_status_id'][] = '3'; // NI (NEW/INACTIVE)
			$search->criteria['release_status_id'][] = '6'; // F (FUTURE)
			$search->criteria['is_active'] = '1';
			
			$search->search(false);

			for($i=0;$i<count($search->results);$i++){
				// UNESCAPE SPECIAL CHARACTERS
				$search->results[$i]->info['common_name'] = (unescape_special_characters($search->results[$i]->info['common_name']));
				$search->results[$i]->info['botanical_name'] = (unescape_special_characters($search->results[$i]->info['botanical_name']));

				// GET ADDITIONAL INFO
				//$search->results[$i]->get_primary_image();
				$search->results[$i]->get_types();
				$search->results[$i]->get_sun_exposures();
				unset($search->results[$i]->table_fields);
				unset($search->results[$i]->table_name);
				//$json_results = to_json($search->results);
			}
			$json_results = to_json($search->results);
		}
	}
	
	$page_title = '';
	if(isset($catalog->info['name'])) $page_title = $catalog->info['name'];
	if($page_title=='') $page_title = 'create a catalog';

?>
<script>
	var catalog = {
		'plant_count':<?=(isset($catalog->info['plant_count'])?intval($catalog->info['plant_count']):'0')?>,
		'plants':<?=$json_results?>,
		'plants_selected_ids':[],
		'template_id':'<?=(isset($catalog->info['template_id'])?intval($catalog->info['template_id']):'0')?>',
		'limit':<?=($catalog_limit.'')?>,
		'plant_images':<?=to_json($plant_images)?>
	}
</script>
<script src="/js/catalogs_edit.js?rnd=3"></script>
<script src="/js/slim_tabs.js" type="text/javascript"></script>
<link href="/css/catalogs.css?rnd=1" rel="stylesheet" type="text/css" />
<link href="/css/slim_tabs.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript">
jQuery.noConflict();
</script>
<script type="text/javascript" src="../js/jquery.form.js"></script>
<script type="text/javascript" src="../js/csv_importer.js"></script>
<div>
	<!--<div class="page_content_body">-->
		<div class="breadcrumb print_hide">
		  <a href="/">home</a> \\ <a href="/catalogs/">sales catalogs</a> \\ <?=strtolower(html_sanitize($page_title))?>
		</div>
		<h1><?=strtolower(html_sanitize($page_title))?></h1>
		
		<div style="padding-top:1.5em;">
		
			For directions on how to create customized catalogs, click <a href="javascript:void(0);" onclick="modal_show({'modal_id':'modal_directions','effect':'fade'});">here</a>.
		
			<div style="width:100%;">
				<div style="padding-right:212px;"><? output_action_group(); ?></div>
			</div>
			<div id="column_left">
				<table class="module_wrapper list" style="margin-right:0px;width:100%;">
				<tr>
					<td class="side_left"></td>
					<td class="content" style="padding:0px;">
						<div class="title">
							search by item number
						</div>
						<div class="list_content" style="padding-bottom:16px;">
							<form action="" method="get" id="form_plant_search_item_number">
								<div class="black">
									<div class="filter_section">
										<label>Item number:</label>
										<input name="item_number" class="text_field" maxlength="5" />
									</div>
								</div>
								<div style="text-align:center;">
									<input type="image" src="../img/btn_search_sm.gif" />
								</div>
							</form>
						</div>
					</td>
					<td class="side_right"></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
				</tr>
				</table>

				<table class="module_wrapper list" style="margin-right:0px;width:100%;">
				<tbody><tr>
					<td class="side_left"></td>
					<td class="content" style="padding:0px;">
						<div class="title">
							import plants from CVS
						</div>
						<div class="list_content" style="padding-bottom:16px;">
								<div class="black">
									<div class="filter_section">
										<form id="csv-import-form" action="inc/csv-file-import.php" method="post" enctype="multipart/form-data">
											<label style="display:inline-block;" for="csv-file">CSV file:</label>
											<input style="width: 75px;font-size: 10px;" type="file" size="60" name="csv-file" id="csv-file"><br>
											<br>
											<div style="text-align:center;">
												<input type="submit" name="submit" value="Import" style="-webkit-border-radius: 5px 0 5px 0;border-radius: 5px 0 5px 0;background: #92c027 no-repeat center;width:52px;height:15px;border:0;cursor:pointer;color:#fff;font-size:10px;">
											</div>
											<br>
											<div id="csv-import-form-status"></div>
											<div id="csv-import-form-result"></div>
											<div class="hr"></div>
											<center><a href="/catalogs/assets/plants.csv" target="_blank" download>Download Template</a></center>
										</form>
									</div>
								</div>								
						</div>
					</td>
					<td class="side_right"></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
				</tr>
				</tbody></table>

				<table class="module_wrapper list" style="margin-right:0px;width:100%;">
				<tr>
					<td class="side_left"></td>
					<td class="content" style="padding:0px;">
						<div class="title" style="padding-left:5px;">
							advanced search
						</div>
						<div class="list_content" style="padding-bottom:16px;">
							<div class="black" style="border-bottom:2px solid #fff;padding-bottom:6px;text-align:center;">
								<a href="javascript:reset_form();void(0);">start a new search</a>
							</div>
							<form action="" method="get" id="form_plant_search">
								<div id="module_refine_search">
									<div class="filter_section">
										<label>Common name:</label>
										<input name="common_name" class="text_field" />
									</div>
									<div class="filter_section">
										<label>Botanical name:</label>
										<input name="botanical_name" class="text_field" />
									</div>
									<div style="text-align:center;padding-top:4px;">
										<input type="image" src="../img/btn_search_sm.gif" />
									</div>
								</div>
								<div class="accordion_segments" id="filters">
									<? include ('filters.php'); ?>
								</div>
								<div style="text-align:center;padding-top:6px;">
									<input type="image" src="../img/btn_search_sm.gif" />
								</div>
								<input type="hidden" name="start_page" value="1" />
							</form>
						</div>
					</td>
					<td class="side_right"></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
				</tr>
				</table>
			
			</div>

			<div id="results_column">

				<div style="clear:both;"></div>
			
				<div id="results_msg"></div>
				<table class="module_wrapper list" id="results_table">
						<thead><tr style="height:31px;">
							<td class="side_left" width="1"></td>
							<td width="1">&nbsp;</td>
							<td width="175"></td>
							<td>
								<label>Types</label>
							</td>
							<td>
								<label>Light Needs</label>
							</td>
							<td>
								<label>USDA Zones</label>
							</td>
							<td>
								<label>Collection</label>
							</td>
							<td class="side_right" style="padding:0px;"></td>
						</tr>
						<tr style="height:22px;">
							<td colspan="4" style="padding-left:8px;">Click checkboxes to select plants for your catalog.</td>
							<td colspan="5">
								<div class="paging"></div>
							</td>
						</tr>
						</thead>
						<tbody id="results"></tbody></table>
						
						<div style="float:right;padding-bottom:1em;" id="bottom_pagination">
							<div class="paging"></div>
						</div>
						<div style="clear:both;"></div>
						
						<? output_action_group(); ?>
						
						
			</div>

			<div id="right_column">
				<table class="module_wrapper list" style="width:100%;">
					<tr style="height:31px;">
						<td class="side_left"></td>
						<td style="padding:0px;" class="content">
							<div class="title" style="padding-left:5px;">your sales catalog</div>
						</td>
						<td class="side_right"></td>
					</tr>
					<tr>
						<td></td>
						<td>
							<div>
								<h3 style="margin:0px;"><label>Catalog name <a href="#" style="text-transform:none;cursor:help;" title="The catalog name is used for your reference and is not shown in the catalog. Catalog names must be unique.">(?)</a></label></h3>
								<input id="catalog_name" value="<?=(isset($catalog->info['name'])?html_sanitize($catalog->info['name']):'')?>" class="text_field" />
								<? if(isset($catalog->info['template_id'])&&intval($catalog->info['template_id'])>0){ ?>
									<!--<h3 style="margin-bottom:0px;"><label>Catalog title <a href="#" style="text-transform:none;cursor:help;" title="The catalog title is the title shown in the catalog. Catalog titles do not have to be unique.">(?)</a></label></h3>
									<input id="catalog_title" value="<?=(isset($catalog->info['title'])?html_sanitize($catalog->info['title']):'')?>" class="text_field" />
									-->
								<? } ?>
								<div class="hr"></div>
								<? if(isset($catalog->info['template_id'])&&intval($catalog->info['template_id'])>0){ ?>
									<h3 style="margin-bottom:2px;">Catalog Covers <a href="javascript:launch_cover_editor();void(0);" style="text-transform:none;">[edit]</a></h3>
									<div id="covers">
										<div class="item" title="Click to edit" onclick="launch_cover_editor();">
											<img src="<?=$catalog->info['cover_image_paths']['front']?>?rnd=<?=time()?>" class="thumbnail cover_thumbnail_front" style="width:90px;height:115px;" />
											<div class="label">Front cover</div>
										</div>
										<div class="item" title="Click to edit" onclick="launch_cover_editor();">
											<img src="<?=$catalog->info['cover_image_paths']['back']?>?rnd=<?=time()?>" class="thumbnail cover_thumbnail_back" style="width:90px;height:115px;" />
											<div class="label">Back cover</div>
										</div>
										<div style="clear:both;"></div>
									</div>
									<div class="hr"></div>
								<? } ?>
								<h3 style="margin-bottom:0px;">Plants <span id="msg_count_indicator"></span></h3>
								<div style="padding-bottom:.5em;">
									Limit <?=$catalog_limit.''?> plants per catalog.
								</div>

								<div style="padding-top:.75em;">
									By default, your plants will be rendered to your catalog in the order below.
									<br /><br />
									<? if(!isset($GLOBALS['browser_info']['family'])||$GLOBALS['browser_info']['family']!='ios'){ ?>
										Click and drag plants individually or by multiples to reorder them.
									<? }else{ ?>
										Click a plant to select it.
									<? } ?>
									
								</div>
								<div id="plant_list_scroller">
									<ul id="plant_list"></ul>
								</div>
								
								<div>
									actions: <a href="javascript:catalog.select_all();void(0);">select all</a><br />
									selected plants: <a href="javascript:catalog.unselect_all();void(0);">unselect</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="javascript:catalog.remove_selected();void(0);">remove</a>
								</div>
								
								<div class="hr"></div>
							
								<div class="btn_green btn_green_dark_bg" style="width:160px;margin:10px 0px;font-size:10pt;">
									<img src="/img/spacer.gif" class="side_left side_left_dark_bg" /><a href="#" onclick="catalog.save();">save changes</a><img src="/img/spacer.gif" />
								</div>
								<div class="btn_green btn_green_dark_bg" style="width:130px;margin:10px 0px;">
									<img src="/img/spacer.gif" class="side_left side_left_dark_bg" /><a href="./">do not save changes</a><img src="/img/spacer.gif" />
								</div>
							</div>
						</td>
						<td></td>
					</tr>
				</table>
			</div>
			<div style="clear:both;"></div>
		</div>
		
		<div style="overflow:hidden;height:0px;width:0px;position:absolute;top:-5000px;">
			<table id="sample_result">
				<tr class="result">
					<td colspan="2" class="thumbnail"><div class="padding"><img src="/img/spacer.gif" /></div></td>
					<td><span class="common_name"></span><a href="#" target="_blank" class="lnk_details">[view]</a><br /><span class="botanical_name"></span><br /><span class="item_number"></span></td>
					<td><span class="types"></span></td>
					<td><span class="sun_exposures"></span></td>
					<td><span class="cold_zones"></span></td>
					<td colspan="2"><span class="collection_name">collection</span></td>
				</tr>
			</table>
			<ul id="sample_plant_listing">
				<li><div class="common_name"></div><div class="botanical_name"></div><div class="item_number"></div><div class="collection_name"></div></li>
			</ul>
		</div>
		
	<!--</div>-->
</div>

<?
	$params = '';
	if(isset($_GET['id'])){
		if($_GET['id']=='new'){
			$params .= 'id=new';
		}else{
			$params .= 'id=' . intval($_GET['id']);
		}
	}
	if(isset($_GET['original_id'])) $params .= '&original_id=' . intval($_GET['original_id']);
?>

<form id="form_save" action="?<?=$params?>" method="post">
	<input type="hidden" id="save_catalog_name" name="catalog[name]" />
	<input type="hidden" id="save_catalog_plant_ids" name="catalog[plant_ids]" />
	<input type="hidden" id="save_monrovia_locations" name="catalog[monrovia_locations]" value="<?=(isset($catalog->info['monrovia_locations'])?html_sanitize($catalog->info['monrovia_locations']):'')?>" />
	<input type="hidden" id="save_catalog_title" name="catalog[title]" />
	<input type="hidden" id="save_catalog_customer_contact" name="catalog[customer_contact]" />
	<input type="hidden" id="save_catalog_customer" name="catalog[customer]" />
	<input type="hidden" id="save_catalog_sales_rep_name" name="catalog[sales_rep_name]" />
	<input type="hidden" id="save_catalog_additional_info_1" name="catalog[additional_info_1]" />
	<input type="hidden" id="save_catalog_additional_info_2" name="catalog[additional_info_2]" />
	<input type="hidden" id="save_catalog_additional_info_3" name="catalog[additional_info_3]" />
	<input type="hidden" id="save_catalog_plant_1_image_set_id" name="catalog[plant_1_image_set_id]" />
	<input type="hidden" id="save_catalog_plant_2_image_set_id" name="catalog[plant_2_image_set_id]" />
	<input type="hidden" id="save_catalog_plant_3_image_set_id" name="catalog[plant_3_image_set_id]" />	
	
	<input type="hidden" name="save" value="1" />
</form>

<div id="unobtrusive_message" style="display:none;"></div>

<div id="preload" style="display:none;"><img src="/img/icon_loading_yellow.gif" /></div>

<script src="/js/jquery.min.js"></script>
<script>
	 jQuery.noConflict();
	jQuery(document).ready(function(){
	
		jQuery('#plant_list').bind('mousedown',function(){
			jQuery('#plant_list').attr('mousedown','true');
		}).bind('mouseup',function(){
			jQuery('#plant_list').removeAttr('mousedown');
		}).bind('mouseleave',function(){
			jQuery('#plant_list').removeAttr('mousedown');
		});
	
		jQuery('#plant_list li').live('mousemove',function(evt){
			if(jQuery('#plant_list').attr('mousedown')=='true') jQuery('#plant_list').addClass('drag');
			
			if(jQuery('#plant_list').hasClass('drag')&&!jQuery('#plant_list li.drag').length){
				if(jQuery(this).hasClass('selected')){
					jQuery('#plant_list li.selected').addClass('drag');
				}else{
					jQuery(this).addClass('drag');
				}
			}
		}).live('mouseenter',function(){
			if(jQuery('#plant_list').hasClass('drag')) jQuery(this).addClass('targeted');
		}).live('mouseleave',function(){
			jQuery(this).removeClass('targeted');
		}).live('mouseup',function(){
			var plant_ids = [];
			var catalog_plants = [];
			var items = jQuery('#plant_list li:not(.reordering_above)');
			var items_dragged = jQuery('#plant_list .drag');
			var item_before_insert = jQuery('#plant_list_scroller .targeted').eq(0);
			var item_id_before_insert = item_before_insert.attr('plant_id');

			var dragged_ids = [];
			for(var i=0;i<items_dragged.length;i++){
				dragged_ids.push(items_dragged[i].getAttribute('plant_id'));
			}

			var is_reordering_above = item_before_insert.hasClass('reordering_above');
			if(item_id_before_insert||is_reordering_above){
				var targeted_index = is_reordering_above?-1:catalog.get_plant_index(item_id_before_insert);
				var non_dragged_ids_before = [];
				var non_dragged_ids_after = [];
				for(var i=0;i<targeted_index;i++){
					if(!jQuery(items[i]).hasClass('drag')) non_dragged_ids_before.push(items[i].getAttribute('plant_id'));
				}
				
				
				if(!item_before_insert.hasClass('drag')&&!is_reordering_above) non_dragged_ids_before.push(item_id_before_insert);

				for(var i=targeted_index+1;i<items.length;i++){
					if(!jQuery(items[i]).hasClass('drag')) non_dragged_ids_after.push(items[i].getAttribute('plant_id'));
				}
				
				var plant_ids = plant_ids.concat(non_dragged_ids_before,dragged_ids,non_dragged_ids_after);
				
				for(var i=0;i<plant_ids.length;i++){			
					catalog_plants.push(catalog.get_plant(plant_ids[i]));
				}
				
				// FAILSAFE: CHECK TO SEE IF THE CATALOG STILL HAS THE SAME NUMBER OF PLANTS
				if(catalog.plants.length!=catalog_plants.length){
					exit_drag_mode();
					show_unobtrusive_message('An error occurred and your plants could not be reordered.',5000);
				}else{
					catalog.plants = catalog_plants;
					catalog.refresh();				
				}
			}
			
			
			jQuery('#plant_list_scroller .drag').removeClass('drag');
		});
		
		jQuery('#plant_list_scroller').bind('mouseleave',exit_drag_mode).bind('mouseup',function(){
			window.setTimeout(exit_drag_mode,1);
		});
	});
	
	function exit_drag_mode(){
		jQuery('#plant_list_scroller .drag').removeClass('drag');
	}
	
</script>

<style>
	#plant_list.drag li.drag {
		-moz-box-shadow: 2px 2px 5px #888;
		-webkit-box-shadow: 2px 2px 5px #888;
		-ms-box-shadow: 2px 2px 5px #888;
		box-shadow: 2px 2px 5px #888;
		-webkit-transform:scale(1);
		-moz-transform:scale(1);
		-ms-transform:scale(1);
		transform:scale(1);
		zoom:1;
	}
	
	#plant_list.drag .targeted, #plant_list .drag {
		cursor:move;
	}
	
	#plant_list.drag .targeted {
		border-bottom:3px solid #6C6934;
	}
	
	#plant_list .reordering_above {
		display:none;
		height:6px;
		padding:0px;
	}
	
	#plant_list.drag .reordering_above {
		display:block;
	}
	
	#plant_list .reordering_above.targeted {
		border:0px;
		background-color:#6C6934;
	}

</style>

<? require_once($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php'); ?>