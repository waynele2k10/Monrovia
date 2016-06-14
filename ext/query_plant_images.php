<?
	$item_number = intval($_GET['item_number']);
	if($item_number==0) exit;

	require_once('../inc/class_sql.php');
	require_once('../inc/class_xml.php');
	require_once('../inc/class_plant.php');

	$result = sql_query("SELECT plant_image_sets.id, plants.common_name, plants.item_number, plant_image_sets.is_primary, plant_image_sets.title, plant_image_sets.photography_credit, plant_image_sets.expiration_date, plant_image_sets.is_distributable, plant_image_sets.source FROM plant_image_sets INNER JOIN plants ON plants.id = plant_id WHERE CAST(plants.item_number AS UNSIGNED) = $item_number AND plant_image_sets.is_active=1 AND plants.is_active=1 AND (expiration_date>NOW() OR expiration_date='0000-00-00') ORDER BY plant_image_sets.is_primary DESC, plant_image_sets.title ASC;");

	$num_rows = intval(@mysql_numrows($result));
	$ret = '';
	for($i=0;$i<$num_rows;$i++){
		$id = mysql_result($result,$i,'plant_image_sets.id');
		$common_name = unescape_special_characters(mysql_result($result,$i,'plants.common_name'));
		$is_primary = mysql_result($result,$i,'plant_image_sets.is_primary');
		$title = mysql_result($result,$i,'plant_image_sets.title');
		$source = mysql_result($result,$i,'plant_image_sets.source');
		$expiration_date = str_replace('0000-00-00','',mysql_result($result,$i,'plant_image_sets.expiration_date'));
		$credit = mysql_result($result,$i,'plant_image_sets.photography_credit');
		$item_number = mysql_result($result,$i,'plants.item_number');
		$img_path = 'http://www.monrovia.com/img/plants/'.$id.'/og/'.$item_number.'-'.generate_plant_seo_name($title).'.jpg';
		$temp = array('id'=>$id,'common_name'=>$common_name,'item_number'=>$item_number,'is_primary'=>$is_primary,'title'=>$title,'source'=>$source,'path'=>$img_path,'expiration_date'=>$expiration_date,'credit'=>$credit);
		$ret .= '<image_set>'.XML_serialize($temp).'</image_set>';
	}
	$ret = strip_tags($ret,'<id><common_name><is_primary><title><source><path><source><image_set><item_number><expiration_date><credit>');
	sql_disconnect();
	echo('<?xml version="1.0" ?><image_sets>'.$ret.'</image_sets>');
?>