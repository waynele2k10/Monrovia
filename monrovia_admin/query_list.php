<?
	require_once('../inc/class_sql.php');
	$query = $_POST['query'];
?>
<ul>
	<? output_unique_values($_GET['list'],$query); ?>
</ul>
<?
	function output_unique_values($field_id,$query){
		$list_records = get_unique_plant_values($field_id,$query,10);
		for($i=0;$i<count($list_records);$i++){
			echo("<li>" . html_sanitize($list_records[$i]['value'])."</li>");
		}
	}
?>
<? sql_disconnect(); ?>