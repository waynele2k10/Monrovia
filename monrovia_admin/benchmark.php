<? include('inc/header.php'); ?>

<?


/*






TODO: completely ajaxify image upload form








*/
	$record_id = 1;
	if(isset($_GET['id'])) $record_id = $_GET['id'];
	$record = new plant($record_id);
	?>
<? include('inc/footer.php'); ?>
