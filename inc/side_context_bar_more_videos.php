<div style="height:8px;"></div>
<a href="/gardening-videos/" style="float:right;font-size:9pt;">view all</a>
<h3 style="display:inline;">more videos</h3>
<div style="clear:both;height:8px;"></div>
<?
$result = sql_query("SELECT youtube_id, title, duration, description, url_friendly_name FROM videos WHERE id <> ".$video_info['id']." ORDER BY date DESC,title LIMIT 8");
$num_rows = mysql_numrows($result);

for($i=0;$i<$num_rows;$i++){
	$title = html_sanitize(mysql_result($result,$i,'title'));
	$url_friendly_name = html_sanitize(mysql_result($result,$i,'url_friendly_name'));
	$location = $url_friendly_name.'-video.php';
	$description = html_sanitize(truncate(mysql_result($result,$i,'description'),55));
	$description = str_replace('....','&hellip;',$description);
	$description = str_replace('...','&hellip;',$description);
	?>
		<div class="video_listing">
			<a href="/gardening-videos/<?=$location?>" class="video_thumbnail" style="background-image:url(/img/videos/<?=mysql_result($result,$i,'youtube_id')?>.gif);" title="<?=$title?>">&nbsp;</a>
			<div class="video_info">
				<a href="/gardening-videos/<?=$location?>" class="video_title"><?=$title?><!-- (<?=mysql_result($result,$i,'duration')?>)--></a>
				<div class="video_description"><?=$description?></div>
			</div>

			<div style="clear:both;"></div>
		</div>
	<?
}
?>