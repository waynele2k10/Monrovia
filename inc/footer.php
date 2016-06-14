		<div class="clear"></div>
	</div>
</div>
<div class="print_only print_bold small" style="clear:both;">This page located at <?=current_url();?></div>
<div id="page_footer" class="print_hide">
	<a href="/about-us/press-room.php">press room</a>
	<a href="/about-us/horticulture-careers.php">careers</a>
	<a href="/legal.php">legal</a>
	<a href="/site-map.php">site map</a>
	<a href="/contact.php">contact us</a>
	<a href="/plant-savvy-newsletter/">newsletter</a>
	<a href="/about-us/">about us</a>
	<a href="/about-us/gardening-faqs.php">FAQs</a>


	<div id="social_icons">
		<a href="http://www.facebook.com/pages/Monrovia/102411039815423?v=wall&ref=sgm" target="_blank" google_social_tracking="facebook|page|{currenturl}"><img src="/img/spacer.gif" style="background-position:0px 0px;" title="Check us out on Facebook!" /></a>
		<a href="https://plus.google.com/106439322773521086880/" rel="publisher" target="_blank" google_social_tracking="google|page|{currenturl}"><img src="/img/spacer.gif" style="background-position:-19px 0px;" title="Find us on Google+!" /></a>
		<a href="http://pinterest.com/monroviagrowers/" target="_blank" google_social_tracking="pinterest|page|{currenturl}"><img src="/img/spacer.gif" style="background-position:-38px 0px;" title="Watch our boards on Pinterest!" /></a>
		<a href="http://twitter.com/plantsavvy/" target="_blank" google_social_tracking="twitter|page|{currenturl}"><img src="/img/spacer.gif" style="background-position:-57px 0px;" title="Follow us on Twitter!" /></a>
		<a href="http://www.youtube.com/user/MonroviaPlants" target="_blank" google_social_tracking="youtube|page|{currenturl}"><img src="/img/spacer.gif" style="background-position:-76px 0px;" title="Watch us on YouTube!" /></a>
	</div>

	Copyright &copy; <?=date('Y');?> Monrovia. All rights reserved.
	
	<? if($GLOBALS['browser_info']['medium']=='mobile'){ ?>
		<div id="view_mobile_site_container">
			<a href="<?=(isset($GLOBALS['mobile_version_url'])?$GLOBALS['mobile_version_url']:$GLOBALS['server_info']['www_root_mobile'].'?mobile=on')?>">view mobile site</a>
		</div>
	<? } ?>
	
</div>
<div class="preload">
	<img src="/img/print_logo.gif" />
</div>
<div id="field_error_message">
	<div id="tip"></div>
	<div id="message"></div>
</div>
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
	<? if(function_exists('output_modals')) output_modals(); ?>
</div>

<? //if($GLOBALS['server_info']['environment']=='prod'){ ?>
	<!-- GOOGLE ANALYTICS -->
		<script type="text/javascript">
			var gaJsHost = (("https:" == document.location.protocol)?"https://ssl.":"http://www.");
			document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
		</script>
		<script type="text/javascript">
			try {
				var pageTracker = _gat._getTracker('<?=$google_analytics['account_id']?>');
				<?
					if(isset($google_analytics['custom_variables'])&&count($google_analytics['custom_variables'])>0){
						for($i=0;$i<count($google_analytics['custom_variables']);$i++){
							$variable_params = $google_analytics['custom_variables'][$i];
							?>
								pageTracker._setCustomVar('<?=(isset($variable_params[0])?js_sanitize($variable_params[0]):'')?>','<?=(isset($variable_params[1])?js_sanitize($variable_params[1]):'')?>','<?=(isset($variable_params[2])?js_sanitize($variable_params[2]):'')?>'<?=(isset($variable_params[3])?',\''.js_sanitize($variable_params[3]).'\'':'')?>);
							<?
						}
					}
				?>
				pageTracker._trackPageview(<? if(isset($google_analytics['page_id'])&&$google_analytics['page_id']!=''){ echo("'" . js_sanitize($google_analytics['page_id']) . "'"); } ?>);
				pageTracker._trackPageLoadTime();
			} catch(err) {}
		</script>
	<!-- /GOOGLE ANALYTICS -->
	<!-- ADDTHIS -->
		<script type="text/javascript">
			var addthis_config = {
				//'data_track_addressbar':true,
				'services_expanded':'delicious,digg,live',
				'data_ga_property':'UA-3929008-1',
				'data_ga_social':true
			};
		</script>
		<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=tpgmonrovia"></script>
	<!-- /ADDTHIS -->
<? //} ?>

<?

	if(function_exists('append_to_footer')) append_to_footer();


	if($GLOBALS['render_editable']&&count($GLOBALS['page_module_ids'])>0){
?>
	<div id="module_editor">
		<div class="title_bar">Edit Content</div>
		<script>
			var oFCKeditor = new FCKeditor('richtext_editor');
			oFCKeditor.BasePath = "/fckeditor/";
			oFCKeditor.Config["CustomConfigurationsPath"] = "/fckeditor/custom.js"  ;
			oFCKeditor.Height = '450';
			oFCKeditor.Value = '';
			oFCKeditor.Create();
		</script>
		<div style="padding-top:3px;text-align:center;">
			<input type="button" value="Preview" onclick="editor_preview();editor_toggle(false);" />
			<input type="button" value="Cancel" onclick="editor_toggle(false);" />
		</div>
	</div>
<? }

	// TODO: if path no longer exists, remove from page_modules
/* COMMENTED OUT 4/13/2011 FOR SITE SPEED REASONS */
/* COMMENTED BACK IN 4/25/2012 AFTER DATABASE OPTIMIZATIONS */
	$current_page_id = $current_page->info['id'];
	if(!is_cron()&&(!isset($page_no_index)||$page_no_index!=true)){
		// SKIP ADD IF PRESS ROOM URL REWRITE
		$current_page_path = get_page_path();

		$is_press_release = contains($current_page_path,'/press-releases/');
		$is_press_release_php = ($is_press_release&&contains($current_page_path,'.php'));

		$page_title = sql_sanitize($page_title);

		// SQL INJECTION-SAFE
		// CHECK VALUES FOR INTEGRITY
		$check_values = array();
		$check_values[] = ids_sanitize(get_page_path());
		$check_values[] = $current_page_id;
		if(!is_suspicious($check_values)){
			if(!$current_page_id&&!contains($current_page_path,'/press-room.php')&&!$is_press_release_php){
				// ADD PAGE TO DATABASE
				sql_query("INSERT INTO pages(section_top_level,title,path) VALUES ('$top_level_section','".$page_title."','".get_page_path()."')");
				$current_page_id = mysql_insert_id();
			}
			// UPDATE PAGE INFO; FOR PRESS RELEASES, UPDATE ONLY FOR NEW ONES
			if(!$is_press_release_php&&$current_page_id!=''){
				// UPDATE PAGE INFO
				sql_query("UPDATE pages SET title='".$page_title."', path='".get_page_path()."', date_last_access='".date('Y-m-d H:i:s')."' WHERE id=".$current_page_id);
				
				// SKIP THIS IF IT LOOKS LIKE THE PAGE WAS CACHED
				if(count($page_module_ids)>0){
					sql_query("DELETE FROM page_modules WHERE page_id = $current_page_id");

					for($i=0;$i<count($page_module_ids);$i++){
						if($page_module_ids[$i]!=''){
							sql_query("INSERT INTO page_modules(page_id,editable_module_id,load_order) VALUES($current_page_id,$page_module_ids[$i],$i)");
						}
					}
				}
			}
		}else{
			if($GLOBALS['server_info']['environment']=='prod'){
				echo('<!-- ERROR -->');
			}else{
				echo('ERROR');
			}
		}
		$current_page->info['id'] = $current_page_id;
	}
/**/

	if(isset($render_editable)&&$render_editable) echo('<script>var editable_module_count = '.count($page_module_ids).';</script>');

	sql_disconnect();
?>
<!-- <?
	if($GLOBALS['server_info']['environment']!='prod'){
	   $mtime = explode(' ',microtime());
	   $mtime = $mtime[1] + $mtime[0];
	   $page_end_time = $mtime;
	   $totaltime = ($page_end_time - $page_begin_time);
	   echo "This page was created in ".$totaltime." seconds";
	   if(isset($GLOBALS['sql_queries'])) var_dump($GLOBALS['sql_queries']);
	   if(isset($GLOBALS['page_log'])) var_dump($GLOBALS['page_log']);
	   //var_dump($GLOBALS['monrovia_user']);
	}
?> -->

<?php  
$currentPath = $_SERVER["PHP_SELF"]; 
if( $currentPath !== '/plant-catalog/index.php' && $currentPath !== '/find-a-garden-center/index.php' ) : ?>

<script type="text/javascript">
var fb_param = {};
fb_param.pixel_id = '6007795409400';
fb_param.value = '0.00';
fb_param.currency = 'USD';
(function(){
  var fpw = document.createElement('script');
  fpw.async = true;
  fpw.src = '//connect.facebook.net/en_US/fp.js';
  var ref = document.getElementsByTagName('script')[0];
  ref.parentNode.insertBefore(fpw, ref);
})();
</script>
<noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/offsite_event.php?id=6007795409400&amp;value=0&amp;currency=USD" /></noscript>
<script type="text/javascript">
var fb_param = {};
fb_param.pixel_id = '6007795396000';
fb_param.value = '0.00';
fb_param.currency = 'USD';
(function(){
  var fpw = document.createElement('script');
  fpw.async = true;
  fpw.src = '//connect.facebook.net/en_US/fp.js';
  var ref = document.getElementsByTagName('script')[0];
  ref.parentNode.insertBefore(fpw, ref);
})();
</script>
<noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/offsite_event.php?id=6007795396000&amp;value=0&amp;currency=USD" /></noscript>

<?php endif; ?>

</body></html>