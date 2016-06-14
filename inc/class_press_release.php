<?php
	require_once('class_record.php');
	class press_release extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM press_releases TABLE, EXCEPT FOR id
		var $table_fields = 'is_active,release_date,title,editable_module_id';

		function press_release($record_id = ''){
			$this->table_name = 'press_releases';
			if($record_id!='') $this->load($record_id);
		}
	}

/*
	function website_output_press_releases(){
		$result = sql_query("SELECT * FROM monrovia_events WHERE is_active='1' ORDER BY date_start ASC");
		$num_rows = intval(@mysql_numrows($result));
		$ret = '';
		for($i=0;$i<$num_rows;$i++){
			?>
				<div class="article_event">
					<? if(mysql_result($result,$i,"image_path")!=''){ ?>
						<img src="/img/events/<?=mysql_result($result,$i,"image_path")?>" style="float:right;display:inline;" />
					<? } ?>
					<div>
						<h2 style="margin:0px;" class="uppercase"><?=html_sanitize(mysql_result($result,$i,"title"))?></h2>
						<p>
							<?=ucwords(html_sanitize(mysql_result($result,$i,"dates")))?><br />
							<span class="uppercase"><?=html_sanitize(mysql_result($result,$i,"location_general"))?></span>
						</p>
						<p><?=(htmlspecialchars_decode(mysql_result($result,$i,"details")))?></p>
					</div>
					<div class="clear"></div>
				</div>
			<?
				if($i!=$num_rows-1) echo('<hr class="article_event_divider" noshade color="#ADA975" />');
		}
	}
*/

	function cms_output_press_releases(){
		$result = sql_query("SELECT * FROM press_releases ORDER BY release_date DESC,is_active DESC");
		$num_rows = intval(@mysql_numrows($result));
		$ret = '';
		for($i=0;$i<$num_rows;$i++){
				$ret .= ",new press_release('".js_sanitize(mysql_result($result,$i,"id"))."','".js_sanitize(mysql_result($result,$i,"is_active"))."','".js_sanitize(mysql_result($result,$i,"release_date"))."','".js_sanitize(mysql_result($result,$i,"title"))."')";
		}
		echo(substr($ret,1));
	}

	function press_releases_website_output_tabs(){
		$years = array();
		$result = sql_query("SELECT EXTRACT(YEAR FROM release_date) as year FROM press_releases WHERE is_active='1' GROUP BY year ORDER BY year DESC");
		$num_rows = intval(@mysql_numrows($result));
		?>
			<tr>
				<td style="width:11px;" id="left_corner" class="selected"></td>
				<td class="nav_items">
				<?
					// OUTPUT TABS
					for($i=0;$i<$num_rows;$i++){
						$years[] = mysql_result($result,$i,"year");
					?>
						<div class="nav_item <? if($i==0) echo('selected'); ?>" tab_id="<?=mysql_result($result,$i,"year")?>"><span><?=mysql_result($result,$i,"year")?></span><div class="triangle <? if($i==0) echo('selected'); ?>"></div></div>
					<?
						if($i<$num_rows-1) echo('<div class="nav_divider">&nbsp;</div>');
					}
							?>
							</td>
							<td style="background:url(/img/page_nav_right.gif) top right no-repeat;padding-right:10px;" width="1">
						</tr>
						<tr>
							<td colspan="3">
								<?
								// OUTPUT TAB SHEETS
								$result = sql_query("SELECT id,release_date,title FROM press_releases WHERE is_active='1' ORDER BY release_date DESC");
								$num_rows = intval(@mysql_numrows($result));

								foreach($years as &$year){
									?>
									<div class="nav_item_content" tab_id="<?=$year?>">
										<h2><?=$year?> Press Releases</h2>
									<?
									for($i=0;$i<$num_rows;$i++){
										if(contains(mysql_result($result,$i,"release_date"),$year.'-')){

											// CHANGE PRESS RELEASE TITLE TO FILENAME FORM
											$url_filename = ' ' . strtolower(mysql_result($result,$i,"title")) . ' ';
											$url_filename = str_replace('&trade;','',$url_filename);
											$url_filename = str_replace('&reg;','',$url_filename);
											$url_filename = ' ' . parse_alphanumeric($url_filename,"\\- ") . ' ';
											$url_filename = mysql_replace_stopwords($url_filename);
											$url_filename = truncate($url_filename,50,false,false);
											$url_filename = str_replace(' ','-',$url_filename);
											$url_filename = trim(trim($url_filename,'-'));

											?>
											<div class="press_release">
												<span><?=date('M d, Y',strtotime(mysql_result($result,$i,"release_date")))?></span>
												<a href="press-releases/<?=html_sanitize(mysql_result($result,$i,"id"))?>/<?=$url_filename?>"><?=html_sanitize(mysql_result($result,$i,"title"))?></a>
											</div>
											<?
										}
									}
									?>
									</div>
									<?
								}
							 ?>
							</td>
						</tr>
		<?
	}


?>