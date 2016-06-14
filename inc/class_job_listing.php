<?php
	require_once('class_record.php');
	class job_listing extends record {
		// THIS IS SIMPLY A LIST OF ALL COLUMNS FROM faq_categories TABLE, EXCEPT FOR id
		var $table_fields = 'is_active,location_id,title,html,ordinal';

		function job_listing($record_id = ''){
			$this->table_name = 'job_listings';
			if($record_id!='') $this->load($record_id);
		}
	}
	function cms_output_job_listings($location_id){

		$result = sql_query("SELECT * FROM job_listings WHERE location_id='$location_id' ORDER BY ordinal ASC");
		$num_rows = intval(@mysql_numrows($result));
		if($num_rows==0){
			?>
			<div id="no_listings" style="font-size:11pt;font-size:bold;">There are no job listings for this location.</div>
			<?
		}else{
			for($i=0;$i<$num_rows;$i++){
				$inactive = (mysql_result($result,$i,"is_active")!='1');
				?>
					<li id="item_<?=mysql_result($result,$i,"id")?>" class="<?if($inactive) echo('inactive')?>" html="<?=html_sanitize(mysql_result($result,$i,"html"))?>">
						<div class="control"><img src="/img/spacer.gif" title="<?=($inactive)?'Make Active':'Make Inactive'?>" class="make_active_inactive" /><img src="img/icon_pencil.png" title="Edit" class="rename" /><img src="img/icon_cross.png" title="Delete" class="delete" /></div><span class="title"><?=html_sanitize(mysql_result($result,$i,"title"))?></span>
					</li>
				<?
			}
		}
	}
	function website_output_job_listings(){
		global $office_locations;
		// ORDER BY LOCATION, THEN BY ORDINAL
		$addendum = '';
		foreach($office_locations as $office_location){
			$addendum .= 'location_id="' . $office_location->id . '" DESC,';
		}
		$result = sql_query("SELECT * FROM job_listings WHERE is_active=1 ORDER BY $addendum ordinal ASC");
		$num_rows = intval(@mysql_numrows($result));
		if($num_rows){
			for($i=0;$i<$num_rows;$i++){
				?>
				<div class="accordion_segment location_<?=mysql_result($result,$i,"location_id")?>" location_id="<?=mysql_result($result,$i,"location_id")?>">
					<div class="title">
						<a href="https://rn21.ultipro.com/MON1010/JobBoard/ListJobs.aspx?__VT=ExtCan" target="_blank" class="lnk_apply">apply now</a>
						<?=html_sanitize(mysql_result($result,$i,"title"))?><span style="font-weight:normal;"> - <?=$office_locations[mysql_result($result,$i,"location_id")]->name?></span>
					</div>
					<div class="content">
						<?
						$html = mysql_result($result,$i,"html");
						//$html = wysiwyg_strip_tags($html);
						echo($html);
						?>
						<div class="btn_green btn_apply">
							<img src="/img/spacer.gif" class="side_left" /><a href="https://rn21.ultipro.com/MON1010/JobBoard/ListJobs.aspx?__VT=ExtCan" target="_blank">apply now</a><img src="/img/spacer.gif" />
						</div>
					</div>
				</div>
				<?
			}
		}
	}

?>