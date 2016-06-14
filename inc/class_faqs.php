<?php
	require_once('class_record.php');
	class faq_category extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM faq_categories TABLE, EXCEPT FOR id
		var $table_fields = 'is_active,name,audience,ordinal';

		function faq_category($record_id = ''){
			$this->table_name = 'faq_categories';
			if($record_id!='') $this->load($record_id);
		}

		function get_faq_item_count(){
			if(is_numeric($this->info['id'])){
				$this->items_total = intval(@mysql_result(sql_query("SELECT COUNT(*) as total FROM faq_items WHERE faq_category_id='.$this->info['id'].'"),0,"total"));
				return $this->items_total;
			}
		}
	}

	function cms_output_categories($audience){
		if(!is_suspicious($audience)){
			$result = sql_query("SELECT faq_categories.id, faq_categories.name, faq_categories.is_active, COUNT(faq_items.faq_category_id) AS total_items FROM faq_categories LEFT JOIN faq_items ON faq_categories.id = faq_items.faq_category_id WHERE audience='$audience' GROUP BY faq_categories.id ORDER BY faq_categories.ordinal ASC");
			$num_rows = intval(@mysql_numrows($result));
			for($i=0;$i<$num_rows;$i++){
				$inactive = (mysql_result($result,$i,"is_active")!='1');
				$total_items = intval(mysql_result($result,$i,"total_items"));
				$msg_total_items = ($total_items==1)?'1 item':$total_items . ' items';
				?>
					<li id="item_<?=mysql_result($result,$i,"id")?>" class="<?if($inactive) echo('inactive')?>" total_items="<?=mysql_result($result,$i,"total_items")?>">
						<div class="control"><img src="/img/spacer.gif" title="<?=($inactive)?'Make Active':'Make Inactive'?>" class="make_active_inactive" /><img src="img/icon_pencil.png" title="Rename" class="rename" /><img src="img/icon_cross.png" title="Delete" class="delete" /></div><input maxlength="40" value="<?=html_sanitize(mysql_result($result,$i,"name"))?>" /><span class="title"><?=html_sanitize(mysql_result($result,$i,"name"))?></span>
						<span class="total_items"><?=$msg_total_items?></span>
					</li>
				<?
			}
		}
	}

	/* FAQ ITEMS */

	class faq_item extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM faq_items TABLE, EXCEPT FOR id
		var $table_fields = 'faq_category_id,is_active,question,answer,ordinal';

		function faq_item($record_id = ''){
			$this->table_name = 'faq_items';
			if($record_id!='') $this->load($record_id);
		}
	}

	function cms_output_tabs($audience){
		if(!is_suspicious($audience)){
			$result = sql_query("SELECT * FROM faq_categories WHERE audience='$audience' ORDER BY ordinal ASC");
			$num_rows = intval(@mysql_numrows($result));
			if($num_rows){
				echo('<div><div class="slimTab spacer" style="width:4px">&nbsp;</div>');

				// OUTPUT TABS
				for($i=0;$i<$num_rows;$i++){
					$inactive = (mysql_result($result,$i,"is_active")!='1');
				?>
						<div class="slimTab <? if($i==0) echo('selected'); ?>" tab="<?=($i+1)?>" title="Alt + Shift + <?=($i+1)?>"><?=($i+1)?>. <?=html_sanitize(mysql_result($result,$i,"name"))?><? if($inactive) echo(' (inactive)'); ?></div>
				<?
				}
				echo('<div class="slimTab spacer" style="width:32px;">&nbsp;</div></div>');

				// OUTPUT TAB SHEETS
				for($i=0;$i<$num_rows;$i++){
					$inactive = (mysql_result($result,$i,"is_active")!='1');
					$category_id = mysql_result($result,$i,"id");
				?>

					<div class="slimTabBlurb<?if($i==0) echo(' sel')?>" tab="<?=($i+1)?>">
						<input type="button" value="Add a new FAQ" onclick="launch_editor();" />
						<ul id="lst_faqs_<?=$category_id?>" class="sortable_list" category_id="<?=$category_id?>">
							<? cms_output_faqs($category_id); ?>
						</ul>
					</div>

				<?
				}
			}else{
			?>
				<div style="font-weight:bold;padding-top:8px;">No categories configured. Please <a href="faq_categories.php">add a category</a> first.</div>
			<?
			}
		}
	}
	function cms_output_faqs($category_id){
		if(is_numeric($category_id)){
			$result = sql_query("SELECT * FROM faq_items WHERE faq_category_id='$category_id' ORDER BY ordinal ASC");
			$num_rows = intval(@mysql_numrows($result));
			for($i=0;$i<$num_rows;$i++){
				$inactive = (mysql_result($result,$i,"is_active")!='1');
				?>
					<li id="item_<?=mysql_result($result,$i,"id")?>" class="<?if($inactive) echo('inactive')?>" answer="<?=html_sanitize(mysql_result($result,$i,"answer"))?>">
						<div class="control"><img src="/img/spacer.gif" title="<?=($inactive)?'Make Active':'Make Inactive'?>" class="make_active_inactive" /><img src="img/icon_pencil.png" title="Edit" class="rename" /><img src="img/icon_cross.png" title="Delete" class="delete" /></div><span class="title"><?=html_sanitize(mysql_result($result,$i,"question"))?></span>
					</li>
				<?
			}
		}
	}
	function faqs_website_output_tabs($audience){
		if(!is_suspicious($audience)){
			$result = sql_query("SELECT * FROM faq_categories WHERE audience='$audience' AND is_active='1' ORDER BY ordinal ASC");
			$num_rows = intval(@mysql_numrows($result));
			?>
				<tr>
					<td style="width:11px;" id="left_corner" class="selected"></td>
					<td class="nav_items"  style="width:708px">
						<!--<div style="display:inline;float:left;font-size:0.9em;height:25px;color:#4F4A14;">Consumer FAQs <img src="/img/page_nav_leaf_right.gif" align="absmiddle" style="margin:0px 4px 0px 4px;" /></div>-->
					<?
						// OUTPUT TABS
						for($i=0;$i<$num_rows;$i++){
						?>
							<div class="nav_item <? if($i==0) echo('selected'); ?>" tab_id="<?=($i+1)?>"><span><?=html_sanitize(mysql_result($result,$i,"name"))?></span><div class="triangle <? if($i==0) echo('selected'); ?>"></div></div>
						<?
							if($i<$num_rows-1) echo('<div class="nav_divider">&nbsp;</div>');
						}
								?>
					</td>
					<td style="background-image: url(/img/page_nav_right.gif); background-position: right top; width: 11px;"></td>
				</tr>
				<tr>
					<td colspan="3" style="width:720px">
									<?
									// OUTPUT TAB SHEETS
									for($i=0;$i<$num_rows;$i++){
										?>
									<div class="nav_item_content" tab_id="<?=($i+1)?>">
										<? website_output_faqs(mysql_result($result,$i,"id")); ?>
									</div>
									<? } ?>
					</td>
				</tr>
			<?
		}
	}
	function website_output_faqs($category_id){
		if(is_numeric($category_id)){
			$result = sql_query("SELECT * FROM faq_items WHERE faq_category_id='$category_id' AND is_active='1' ORDER BY ordinal ASC");
			$num_rows = intval(@mysql_numrows($result));
			for($i=0;$i<$num_rows;$i++){
				?>
					<div class="faq">
						<h3><?=html_sanitize(mysql_result($result,$i,"question"))?></h3>
						<?=mysql_result($result,$i,"answer")?>
					</div>
				<?
			}
		}
	}
?>