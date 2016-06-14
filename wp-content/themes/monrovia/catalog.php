<?php
/*
	Template Name: Catalog
*/

get_header(); ?>

<?php 
// If User is not logged in, redirect them to the Homepage
if(!is_user_logged_in()){ ?>

	<script type="text/javascript">
	window.location = "/community/login/?notice=catalog";
	</script>
    
<?php } ?>

		<script src="/js/prototype.js" type="text/javascript"></script>
		<script src="/js/prototype_extensions.js" type="text/javascript"></script>
		<script src="/js/general.js" type="text/javascript"></script>		
        <script src="/js/scriptaculous/scriptaculous.js?load=effects" type="text/javascript"></script>
        <link href="<?php site_url();?>/css/modal.css" rel="stylesheet" type="text/css" />
		<script src="/js/modal.js" type="text/javascript"></script>
        
    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<!-- article -->
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php the_content(); ?>
			</article>
			<!-- /article -->
            <?php endwhile; endif; ?>
            <!-- Imported code from OLD Monrovia -->
            <?php
           require_once($_SERVER['DOCUMENT_ROOT'].'/inc/class_catalog.php');
	function output_modals(){
		global $current_user;
		get_currentuserinfo();
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
									<a href="javascript:void(0);" onclick="delete_catalog();" class="green-btn left">delete</a>
								</div>
								<div class="btn_green" style="width:110px;margin:auto;float:left;">
									<a href="javascript:void(0);" onclick="modal_hide();" class="white-btn left">cancel</a>
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
									<label for="chk_keep_order" style="display:inline-block;">Keep my plants in the same order as I have them.</label>
								</div>
								<div class="fieldset">
									<input type="radio" id="chk_sort" name="sort_boolean" />
									<label for="chk_sort" style="display:inline-block;">Sort my plants by:</label>
									<select id="sort_by">
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
									<a href="javascript:void(0);" onclick="generate_pdf();" class="green-btn">generate PDF</a>
								</div>
								<div class="btn_green" style="width:110px;margin:auto;float:left;">
									<a href="javascript:void(0);" onclick="modal_hide();" class="white-btn left">cancel</a>
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
							A link to the PDF will be emailed to you at <span class="lbl_email_address"><?php echo $current_user->user_email;?></span> as soon as it has been generated. The process may take up to 20 minutes.
							<div style="padding-top:12px;padding-left:25px;bottom:0px;position:relative;text-align:center;width:350px;">
								<div class="btn_green" style="width:110px;margin:auto;">
									<a href="javascript:void(0);" onclick="modal_hide();" class="green-btn left">OK</a>
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
									<a href="javascript:void(0);" onclick="modal_hide();" class="green-btn left">close</a>
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
							<h1>Help</h1>
							There are two options for building a custom catalog:<br /><br />
							<b>a.</b> Make a duplicate of the official catalog, and then add or delete plants to fit your needs.
							<br /><br />
							<b>b.</b> Create a new catalog from a blank template. Once saved, you can duplicate and adjust these catalogs too.
							<br /><br />
							Your custom catalogs will remain on our website for one year, or until you delete them. If you want to save beyond one year, simply create a PDF and save it to your desktop.
							<img src="/wp-content/uploads/catalog_directions_main.jpg" style="width:638px;height:227px;" />
							<div style="padding-top:12px;bottom:0px;position:relative;text-align:center;">
								<div class="btn_green" style="width:110px;margin:auto;">
									<a href="javascript:void(0);" onclick="modal_hide();" class="green-btn left">close</a>
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
	
	<?php
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
		new Ajax.Request('/pdfcatalogs/delete.php', {
		  method: 'post', parameters:'id='+delete_id,
		  onComplete:function(transport){
		  	if(transport.responseText!='fail'){
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
			new Ajax.Request('/pdfcatalogs/generate.php', {
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
							new Ajax.Request('<?php echo 'http://'.$_SERVER['HTTP_HOST'];?>/inc/crons/catalog_queue.php');
						},500);
					}
			  }
			});
		},1);
	}
	
	Event.observe(window,'load',function(){
		//var email_address_labels = $$('.lbl_email_address');
		//email_address_labels.each(function(label){
			//label.update(monrovia_user_data.email_address);
		//});
		
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
				padding-bottom:10px;
				overflow:hidden;
			}
			.checkwrap{
				float:left;
				margin-right:10px;
			}
		</style>
		<?php if(isset($_GET['msg'])){
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
					<div id="page_notice"><?php echo $msg?></div>
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

				<?php
				}
			}
		?>
		<div>
			
			<div style="padding:20px 0px;">
				Welcome to the Custom Catalog Creator. For directions on how to get started, click <a href="javascript:void(0);" onclick="modal_show({'modal_id':'modal_directions','effect':'fade'});">here</a>.
			</div>
		
			<div style="float:left;width:440px;margin-right:40px;height:100%;">
            	<div class="clear border bottom double">
					<h2 class="left">My Catalogs</h2>
                	<a href="/catalogs/edit/?id=new" class="right">create a new catalog</a>
                </div>
				<div class="listings">
					<?php
					$result = mysql_query("SELECT id FROM catalogs WHERE user_id=".$current_user->ID." ORDER BY date_last_modified DESC");
					$num_rows = intval(mysql_num_rows($result));
					if($num_rows>0){
						for($i=0;$i<$num_rows;$i++){
							$catalog = new catalog(mysql_result($result,$i,'id'));
						?>
							<div class="catalog-lists clear">
								<img src="<?php echo $catalog->info['cover_image_paths']['front']?>?rnd=<?php echo time()?>" class="thumbnail left" />
								<div class="listing_info">
									<h3><?php echo html_sanitize($catalog->info['name'])?></h3>
									<?php if(isset($catalog->info['created_from_name'])&&$catalog->info['created_from_name']!=''){ ?>
										<div>Original catalog: <?php echo $catalog->info['created_from_name']?></div>
									<?php } ?>
									<div>Date created: <?php echo date('m/d/Y',strtotime($catalog->info['date_created']))?></div>
									<div>Date last updated: <?php echo date('m/d/Y',strtotime($catalog->info['date_last_modified']))?></div>
									<div>Total plants: <?php echo intval($catalog->info['plant_count']).''?></div>
									<ul class="clearfix">
										<li class="left"><a href="./edit/?id=<?php echo $catalog->info['id']?>">edit</a></li>
										<li class="left">&nbsp;&nbsp;|&nbsp;&nbsp;</li>
										<li class="left"><a href="./edit/?id=new&original_id=<?php echo $catalog->info['id']?>">duplicate</a></li>
										<li class="left">&nbsp;&nbsp;|&nbsp;&nbsp;</li>
										<li class="left"><a href="javascript:void(0);" onclick="confirm_delete_catalog('<?php echo $catalog->info['id']?>','<?php echo js_sanitize(addslashes($catalog->info['name']))?>');">delete</a></li>
										<?php if(intval($catalog->info['plant_count'])>0){ ?>
											<li class="left">&nbsp;&nbsp;|&nbsp;&nbsp;</li>
											<li class="left"><a href="javascript:void(0);" onclick="confirm_generate_pdf('<?php echo $catalog->info['id']?>');">generate PDF</a></li>
										<?php } ?>
									</ul>
								</div>
								<div style="clear:both;"></div>
							</div>
						<?php
						}
					}else{
					?>
						You haven't created any custom catalogs.
					<?php
					}
					?>
				</div>		
			
			</div>
		
			<div style="float:left;width:440px;height:100%;">
            	<div class="border bottom double">
					<h2>Official Catalogs</h2>
                </div>
				<div class="listings">
					<?php
					$result = mysql_query("SELECT id FROM catalogs WHERE is_official_catalog=1 ORDER BY date_created DESC");
					$num_rows = intval(mysql_num_rows($result));
					if($num_rows>0){
						for($i=0;$i<$num_rows;$i++){
							$catalog = new catalog(mysql_result($result,$i,'id'));
						?>
							<div class="catalog-lists clear">
								<img src="<?php echo $catalog->info['cover_image_paths']['front']?>" class="thumbnail left" />
								<div class="listing_info">
									<h3><?php echo html_sanitize($catalog->info['name'])?></h3>
									<div>Date released: <?php echo date('F Y',strtotime($catalog->info['date_created']))?></div>
									<div>Total plants: <?php echo intval($catalog->info['plant_count']).''?></div>
									<ul class="clearfix">
										<li class="left"><a href="edit/?id=new&original_id=<?php echo $catalog->info['id']?>">duplicate</a></li>
										<li class="left">&nbsp;&nbsp;|&nbsp;&nbsp;</li>
										<li class="left"><a href="<?php echo $catalog->info['download_path']?>" target="_blank">download PDF</a></li>
									</ul>
								</div>
							</div>
						<?php
						}
					}else{
					?>
						No official catalogs have been released yet.
					<?php
					}
					?>

				</div>
				
			</div>


			<div style="clear:both;"></div>
		
		
		
		</div>
	<!--</div>-->
</div>
<!-- end OLD Code -->
</section>
</div><!-- end content_wrapper -->

    <!-- Modal Container Code from OLD Monrovia.com -->			       
    <div id="modal_container">
	<div class="modal_dialog" id="modal_lightview">
		<table class="modal_dialog_backing">
			<tr>
				<td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
			</tr>
			<tr>
				<td class="corner corner_left"></td>
				<td class="corner corner_middle">
					<div id="image_container"></div>
					<div id="lightview_info">
						<div id="lightview_title"></div>
						<div id="lightview_description"></div>
					</div>
				</td>
				<td class="corner corner_right"></td>
			</tr>
			<tr>
				<td class="corner corner_bottomleft"></td><td class="corner corner_bottom"><td class="corner corner_bottomright"><td>
			</tr>
		</table>
	</div>
		<div id="modal_wish_list_full" class="modal_dialog" style="text-align:center;padding-top:10px;color:#000;font-weight:bold;">
			<table class="modal_dialog_backing">
				<tr>
					<td class="corner corner_topleft"></td><td class="corner corner_top"><td class="corner corner_topright"></td>
				</tr>
				<tr>
					<td class="corner corner_left"></td>
					<td class="corner corner_middle">
						<div style="width:400px;">
							Sorry, but your wish list has reached the maximum limit allowed.
							<br />
							Please choose a plant to delete before adding a new one.
							<div style="padding-top:12px;bottom:0px;position:relative;text-align:center;">
								<div class="btn_green" style="width:110px;margin:auto;">
									<a href="javascript:void(0);" onclick="modal_hide();">close</a>
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
	<?php  output_modals(); ?>
</div>
<!-- end modal code -->
    
<?php get_footer(); ?>