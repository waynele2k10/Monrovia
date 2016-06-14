<?php
	// REQUIRED
	$top_level_section = '';
	$page_title = 'Monrovia Catalogs';
	$page_meta_description = '';

	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/header.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_catalog.php');

	// MAKE SURE USER HAS CATALOG PERMISSION
	$monrovia_user->permission_requirement('pdfs');
	
	//////////////////
	
	function output_modals(){
	?>
		<div id="modal_delete" class="modal_dialog" style="text-align:center;padding-top:10px;color:#000;font-weight:bold;">
			<table class="modal_dialog_backing">
				<tr>
					<td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
				</tr>
				<tr>
					<td class="corner corner_left"></td>
					<td class="corner corner_middle">
						<div style="width:400px;">
							Are you sure you want to delete "<span id="lbl_name"></span>"?
							<div style="padding-top:12px;padding-left:25px;bottom:0px;position:relative;text-align:center;width:350px;">
								<div class="btn_green" style="width:110px;margin:auto;float:right;">
									<img src="/img/spacer.gif" class="side_left" /><a href="javascript:void(0);" onclick="delete_catalog();">delete</a><img src="/img/spacer.gif" />
								</div>
								<div class="btn_green" style="width:110px;margin:auto;float:left;">
									<img src="/img/spacer.gif" class="side_left" /><a href="javascript:void(0);" onclick="modal_hide();">cancel</a><img src="/img/spacer.gif" />
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

		<div id="modal_generate" class="modal_dialog" style="padding-top:10px;color:#000;font-weight:bold;">
			<table class="modal_dialog_backing">
				<tr>
					<td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
				</tr>
				<tr>
					<td class="corner corner_left"></td>
					<td class="corner corner_middle">
						<div style="display:none;width:400px;" id="screen_wait">
							<h1>Generate PDF</h1>
							Please wait...
						</div>
						<div style="width:400px;" id="screen_main">
							<h1>Generate PDF</h1>
							<!--A link to the PDF will be emailed to you at <span class="lbl_email_address"></span> as soon as it has been generated. Please allow up to 20 minutes.
							<br /><br />-->
							<div class="fieldset_group">
								<h3>Plant Sorting</h3>
								<div class="fieldset">
									<input type="radio" id="chk_keep_order" name="sort_boolean" checked />
									<label for="chk_keep_order">Keep my plants in the same order as I have them.</label>
								</div>
								<div class="fieldset">
									<input type="radio" id="chk_sort" name="sort_boolean" />
									<label for="chk_sort">Sort my plants by:</label>
									<select id="sort_by" disabled>
										<option value="botanicalname" selected>Botanical Name</option>
										<option value="collectionname">Collection</option>
										<option value="commonname">Common Name</option>
										<option value="itemnumber">Item Number</option>
									</select>
								</div>
							</div>
							<div class="fieldset_group">
								<h3>Plant Collections</h3>
								<div class="fieldset">
									<input id="include_collection_page" type="checkbox" checked="checked" name="include_collection_page">
									<label for="include_collection_page">Include introduction pages for each plant collection</label>
								</div>
							</div>
							<div style="padding-top:8px;padding-left:25px;bottom:0px;position:relative;text-align:center;width:350px;">
								<div class="btn_green" style="width:110px;margin:auto;float:right;">
									<img src="/img/spacer.gif" class="side_left" /><a href="javascript:void(0);" onclick="generate_pdf();">generate PDF</a><img src="/img/spacer.gif" />
								</div>
								<div class="btn_green" style="width:110px;margin:auto;float:left;">
									<img src="/img/spacer.gif" class="side_left" /><a href="javascript:void(0);" onclick="modal_hide();">cancel</a><img src="/img/spacer.gif" />
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

		<div id="modal_generate_confirm" class="modal_dialog" style="padding-top:10px;color:#000;font-weight:bold;">
			<table class="modal_dialog_backing">
				<tr>
					<td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
				</tr>
				<tr>
					<td class="corner corner_left"></td>
					<td class="corner corner_middle">
						<div style="width:400px;">
							<h1>Generate PDF</h1>
							A link to the PDF will be emailed to you at <span class="lbl_email_address"></span> as soon as it has been generated. The process may take up to 20 minutes.
							<div style="padding-top:12px;padding-left:25px;bottom:0px;position:relative;text-align:center;width:350px;">
								<div class="btn_green" style="width:110px;margin:auto;">
									<img src="/img/spacer.gif" class="side_left" /><a href="javascript:void(0);" onclick="modal_hide();">OK</a><img src="/img/spacer.gif" />
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

		<div id="modal_error" class="modal_dialog" style="text-align:center;padding-top:10px;color:#000;font-weight:bold;">
			<table class="modal_dialog_backing">
				<tr>
					<td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
				</tr>
				<tr>
					<td class="corner corner_left"></td>
					<td class="corner corner_middle">
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
					<td class="corner corner_middle">
						<div style="width:640px;text-align:left;font-weight:normal;">
							<h1>help</h1>
							There are two options for building a custom catalog:<br /><br />
							<b>a.</b> Make a duplicate of the official catalog, and then add or delete plants to fit your needs.
							<br /><br />
							<b>b.</b> Create a new catalog from a blank template. Once saved, you can duplicate and adjust these catalogs too.
							<br /><br />
							Your custom catalogs will remain on our website for one year, or until you delete them. If you want to save beyond one year, simply create a PDF and save it to your desktop.
							<img src="/img/catalog_directions_main.jpg" style="width:638px;height:227px;" />
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
	
	<?
	}
	
?>

<script>
	var delete_id = '';
	var generate_id = '';
	function confirm_delete_catalog(id,catalog_name){
		delete_id = id;
		$('lbl_name').update(catalog_name);
		modal_show({'modal_id':'modal_delete','effect':'fade'});
	}
	function delete_catalog(){
		new Ajax.Request('/catalogs/delete.php', {
		  method: 'post', parameters:'id='+delete_id,
		  onComplete:function(transport){
		  	if(transport.responseText=='success'){
		  		window.location.href = './?msg=deleted';
		  	}else{
		  		window.location.href = './?msg=error_not_deleted';
		  	}
		  }
		});
	}
	function confirm_generate_pdf(id){
		generate_id = id;
		$('sort_by').value = 'botanicalname';
		$('chk_keep_order').checked = true;
		$('sort_by').setAttribute('disabled','disabled');
		$('include_collection_page').checked = true;
  		$('screen_main').style.display = 'block';
  		$('screen_wait').style.display = 'none';
		modal_show({'modal_id':'modal_generate','effect':'fade'});
	}
	function generate_pdf(){
  		$('screen_main').style.display = 'none';
  		$('screen_wait').style.display = 'block';
  		var include_collection_page = $('include_collection_page').checked?'1':'0';
  		
  		var sort_by = $('chk_sort').checked?$('sort_by').value:'custom';
  		  		  		
  		window.setTimeout(function(){
			new Ajax.Request('/catalogs/generate.php', {
			  method: 'post', parameters:'id='+generate_id+'&sorting_method='+sort_by+'&include_collection_page='+include_collection_page,
			  onComplete:function(response){
					modal_hide();
					if(response.responseText=='already queued'){
						window.setTimeout(function(){
							$('modal_error_message').update('Your catalog has already been added to the queue and should be processed shortly.');
							modal_show({'modal_id':'modal_error','effect':'fade'});
						},500);
					}else{
						window.setTimeout(function(){
							modal_show({'modal_id':'modal_generate_confirm','effect':'fade'});
							new Ajax.Request('/inc/crons/catalog_queue.php');
						},500);
					}
			  }
			});
		},1);
	}
	
	Event.observe(window,'load',function(){
		var email_address_labels = $$('.lbl_email_address');
		email_address_labels.each(function(label){
			label.update(monrovia_user_data.email_address);
		});
		
		$('chk_sort').observe('change',toggle_sort_by);
		$('chk_keep_order').observe('change',toggle_sort_by);
	});
	
	
	function toggle_sort_by(){
		if($('chk_sort').checked){
			$('sort_by').removeAttribute('disabled');
		}else{
			$('sort_by').setAttribute('disabled','disabled');		
		}
	}

	
</script>

<div>
	<!--<div class="page_content_body">-->
		<div class="breadcrumb print_hide">
		  <a href="/">home</a> \\ catalogs
		</div>
		<h1>catalogs</h1>
		<style>
			.listing {
				padding:16px 0px 8px 0px;
			}
			.listing ul {
				margin:8px 0px 0px 0px;
				padding:0px;
			}
			.listing li {
				float:left;
				list-style-type:none;
				margin-right:8px;
			}
			.listing .thumbnail {
				float:left;
			}
			.listing_info {
				float:left;
				padding-left:16px;
				width:265px;
			}
			.thumbnail {
				width:154px;
				height:197px;
			}
			.fieldset_group {
				padding-bottom:1em;
				font-weight:normal;
			}
			.fieldset {
				padding-bottom:.5em;
			}
		</style>
		<? if(isset($_GET['msg'])){
				$msg = '';
				switch($_GET['msg']){
					case 'saved':
						$msg = 'Your changes have been saved.';
					break;
					case 'error_not_saved':
						$msg = 'An error occurred and your changes were not saved. Please contact <span class="email_link">customercare(#)monrovia.com</span>.';
					break;
					case 'deleted':
						$msg = 'Your catalog was successfully deleted.';
					break;
					case 'error_not_deleted':
						$msg = 'An error occurred and your catalog may not have been deleted. Please contact <span class="email_link">customercare(#)monrovia.com</span>.';
					break;
				}
				if($msg!=''){
				?>
					<div id="page_notice"><?=$msg?></div>
					<style>
						#page_notice {
							border:1px solid #E4DF9B;
							width:666px;
							background-color:#FCFBE8;
							font-size:10pt;
							font-weight:bold;
							text-align:center;
							padding:8px;
							margin-bottom:1em;
						}
					</style>
					<script>
						$('page_notice').highlight();
					</script>

				<?
				}
			}
		?>
		<div>
			
			<div style="">
				Welcome to the Custom Catalog Creator. For directions on how to get started, click <a href="javascript:void(0);" onclick="modal_show({'modal_id':'modal_directions','effect':'fade'});">here</a>.
			</div>
		
			<div style="float:left;width:440px;margin-right:40px;height:100%;">
				<div style="float:right;display:inline;padding-top:24px;"><a href="/catalogs/edit.php?id=new">create a new catalog</a></div>
				<div style="float:left;display:inline;"><h2 class="header">My Catalogs</h2></div>
				<div style="clear:both;"></div>
				<div style="margin:4px 0px 12px 0px;" class="hr"></div>
				
				<div class="listings">
				
					<?
					$result = sql_query("SELECT id FROM catalogs WHERE user_id=".$monrovia_user->info['id']." ORDER BY date_last_modified DESC");
					$num_rows = intval(@mysql_numrows($result));
					if($num_rows>0){
						for($i=0;$i<$num_rows;$i++){
							$catalog = new catalog(mysql_result($result,$i,'id'));
						?>
							<div class="listing">
								<img src="<?=$catalog->info['cover_image_paths']['front']?>?rnd=<?=time()?>" class="thumbnail" />
								<div class="listing_info">
									<h3><?=html_sanitize($catalog->info['name'])?></h3>
									<? if(isset($catalog->info['created_from_name'])&&$catalog->info['created_from_name']!=''){ ?>
										<div>Original catalog: <?=$catalog->info['created_from_name']?></div>
									<? } ?>
									<div>Date created: <?=date('m/d/Y',strtotime($catalog->info['date_created']))?></div>
									<div>Date last updated: <?=date('m/d/Y',strtotime($catalog->info['date_last_modified']))?></div>
									<div>Total plants: <?=intval($catalog->info['plant_count']).''?></div>
									<ul>
										<li><a href="./edit.php?id=<?=$catalog->info['id']?>">edit</a></li>
										<li>|</li>
										<li><a href="./edit.php?id=new&original_id=<?=$catalog->info['id']?>">duplicate</a></li>
										<li>|</li>
										<li><a href="javascript:void(0);" onclick="confirm_delete_catalog('<?=$catalog->info['id']?>','<?=js_sanitize($catalog->info['name'])?>');">delete</a></li>
										<? if(intval($catalog->info['plant_count'])>0){ ?>
											<li>|</li>
											<li><a href="javascript:void(0);" onclick="confirm_generate_pdf('<?=$catalog->info['id']?>');">generate PDF</a></li>
										<? } ?>
										<li style="clear:both;"></li>
									</ul>
								</div>
								<div style="clear:both;"></div>
							</div>
						<?
						}
					}else{
					?>
						You haven't created any custom catalogs.
					<?
					}
					?>
				</div>		
			
			</div>
		
			<div style="float:left;width:440px;height:100%;">
				<div style="float:left;display:inline;">
					<h2 class="header">Official Catalogs</h2>
				</div>
				<div style="clear:both;"></div>
				<div style="margin:4px 0px 12px 0px;" class="hr"></div>
				
				<div class="listings">

					<?
					$result = sql_query("SELECT id FROM catalogs WHERE is_official_catalog=1 ORDER BY date_created DESC");
					$num_rows = intval(@mysql_numrows($result));
					if($num_rows>0){
						for($i=0;$i<$num_rows;$i++){
							$catalog = new catalog(mysql_result($result,$i,'id'));
						?>
							<div class="listing">
								<img src="<?=$catalog->info['cover_image_paths']['front']?>" class="thumbnail" />
								<div class="listing_info">
									<h3><?=html_sanitize($catalog->info['name'])?></h3>
									<div>Date released: <?=date('F Y',strtotime($catalog->info['date_created']))?></div>
									<div>Total plants: <?=intval($catalog->info['plant_count']).''?></div>
									<ul>
										<li><a href="./edit.php?id=new&original_id=<?=$catalog->info['id']?>">duplicate</a></li>
										<li>|</li>
										<li><a href="<?=$catalog->info['download_path']?>" target="_blank">download PDF</a></li>
										<li style="clear:both;"></li>
									</ul>
								</div>
								<div style="clear:both;"></div>
							</div>
						<?
						}
					}else{
					?>
						No official catalogs have been released yet.
					<?
					}
					?>

				</div>
				
			</div>


			<div style="clear:both;"></div>
		
		
		
		</div>
	<!--</div>-->
</div>

<? require_once($_SERVER['DOCUMENT_ROOT'].'/inc/footer.php'); ?>