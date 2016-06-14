<?
require( '../wp-load.php' );
include_once( '../wp-config.php');

	$item_number = intval($_POST['item_number']);
	if($item_number==0) exit;
	$id_exclude = $_POST['id_exclude'];

	//require_once('../inc/class_sql.php');

	if($id_exclude!=''){
		$result = mysql_query("SELECT id FROM plants WHERE CAST(item_number AS UNSIGNED) = $item_number AND id <> $id_exclude;");
	}else{
		$result = mysql_query("SELECT id FROM plants WHERE CAST(item_number AS UNSIGNED) = $item_number;");
	}

	$num_rows = intval(mysql_num_rows($result));
	$ret = '';
	for($i=0;$i<$num_rows;$i++){
		$ret .= ','.mysql_result($result,$i,"id");
	}
	echo(trim($ret,','));

?>