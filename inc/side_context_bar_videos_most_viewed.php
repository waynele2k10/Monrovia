<h3>most viewed<br />videos</h3>
<ul>
<?
$result = sql_query("SELECT title, url_friendly_name FROM videos ORDER BY views DESC,title LIMIT 5");
$num_rows = mysql_numrows($result);

for($i=0;$i<$num_rows;$i++){
	$title = html_sanitize(mysql_result($result,$i,'title'));
	$url_friendly_name = html_sanitize(mysql_result($result,$i,'url_friendly_name'));
	$location = $url_friendly_name.'-video.php';
	?>
		<li id="lnk_sidebar_videos_<?=$url_friendly_name?>"><a href="/gardening-videos/<?=$location?>"><?=$title?></a></li>
	<?
}
?>
</ul>