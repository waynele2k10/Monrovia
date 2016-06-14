<?
	//session_start();
	//require_once('../inc/class_monrovia_user.php');
	require_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/uploads/plants/connect.php');

	if(isset($_POST['submitted'])&&$_POST['submitted']=='1'){
		require_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/monrovia/includes/utility_functions.php');
		//require_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/monrovia/includes/classes/class_plant_image_set.php');
		require_once('../inc/class_plant_image_set.php');
		
		$base_target_path = $_SERVER['DOCUMENT_ROOT']."/wp-content/uploads/plants/originals/";
		$file = $_FILES['file_path'];
		$title = $_POST['title'];
		$credit = $_POST['credit'];
		$expiration_date = $_POST['expiration_date'];
		$source = $_POST['source'];

		$is_active = '0';
		if(isset($_POST['is_active'])) $is_active = checkbox_to_tinyint($_POST['is_active']);
		
		$is_primary = '0';
		if(isset($_POST['is_primary'])) $is_primary = checkbox_to_tinyint($_POST['is_primary']);
		
		$is_distributable = '0';
		if(isset($_POST['is_distributable'])) $is_distributable = checkbox_to_tinyint($_POST['is_distributable']);

		$plant_id = $_GET['plant_id'];

		$plant_image_set = new plant_image_set();
		$plant_image_set->info['plant_id'] = $plant_id;
		$plant_image_set->info['is_active'] = $is_active;
		$plant_image_set->info['is_primary'] = $is_primary;
		$plant_image_set->info['is_distributable'] = $is_distributable;
		$plant_image_set->info['title'] = $title;
		$plant_image_set->info['photography_credit'] = $credit;
		$plant_image_set->info['expiration_date'] = $expiration_date;
		$plant_image_set->info['source'] = $source;

		$plant_image_set->save();

	    $target_path = $base_target_path . $plant_image_set->info['id'] . '.jpg';

		$success = (move_uploaded_file($file['tmp_name'], $target_path));


		$plant_image_set->generate_thumbnails();
		// TODO: if first image, set primary
	?>
	<script>
		var action = 'upload';
		var result = '<?=$success?>';
	</script>
	<?
	}else{
	?>
	<html>
		<head>
			<script src="../js/prototype.js"></script>
			<script src="../js/prototype_extensions.js" type="text/javascript"></script>
			<script src="../js/general.js"></script>

			<link rel="stylesheet" type="text/css" href="../inc/packer.php?path=/monrovia_admin/css/general.css" />

			<style>
				body {
					margin:0px;
					padding:0px;
					overflow:hidden;
					/*height:100%;*/
				}
				#content {
					background:#FFFFFF url(img/loading.gif) center no-repeat;
				}
				.title {
					font-weight:bold;
					font-size:9pt;
				}
				.field_label {
					text-align:left;
					padding-top:6px;
					padding-bottom:2px;
				}
				.text_field {
					width:235px;
				}
				iframe {
					visibility:hidden;
					position:absolute;
				}
				#upload_image_status, #upload_image_primary {
					/*position:absolute;
					#margin-top:5px;*/
				}
				form {
					margin:0px;
					padding:0px;
					background-color:#fff;
				}
				.checkbox_group input {
					margin:0px 4px 0px 0px;
				}
			</style>
			<script>
				function validate_image_upload(){
					try {
						var form = $('upload_form');
						var title = (form.title.value + '').strip();
						var file = (form.file_path.value + '').strip();
						var expiration_date = (form.expiration_date.value + '').strip();
						var source = (form.source.value + '').strip();
						var is_active = form.is_active.checked;
						var is_primary = form.is_primary.checked;
						var is_distributable = form.is_distributable.checked;
						//var plant_item_number = parent.get_field('plant[item_number]').value;
						//var plant_common_name = parent.get_field('plant[common_name]').value;

						if(!title){ alert('Please specify a title for this image.'); return false; }
						if(!file){ alert('Please specify an image.'); return false; }
						if(file.substr(file.length-4,4).toLowerCase()!='.jpg'){ alert('Only JPEGs (.jpg) may be used.'); return false; }
						if(is_primary&&!is_active){ alert('The primary image cannot be inactive.'); return false; }
						if(expiration_date&&!is_valid_mysql_date(expiration_date)){ alert('The expiration date must be in this format: yyyy-mm-dd.'); return false; }
						$('upload_form_container').style.visibility='hidden';
						//form.base_name.value = parent.generate_image_base_name(plant_item_number,plant_common_name,title);// + '_' + random_integer(6);
						return true;
					}catch(err){
						alert('An error has occurred: '+err);
						return false;
					}
				}
				function upload_complete(){
					parent.upload_complete();
				}
				Event.observe(window,'load',function(){
					try{
						$('upload_form').title.focus();
					}catch(err){}
					//$('upload_form').plant_id.value = parent.record_id;
				});
			</script>
			<body>
				<div class="title">Add A New Image</div>
				<div id="content">
					<div id="upload_form_container">
						<form enctype="multipart/form-data" action="upload_image.php?plant_id=<?=$_GET['plant_id']?>" method="POST" id="upload_form" target="iframe_upload" onSubmit="return validate_image_upload();">
							<div class="field_label">Title:</div><input class="text_field" name="title" maxlength="40" />
							<div class="field_label">File:</div><input class="text_field" type="file" size="27" name="file_path" accept="image/jpg,image/jpeg" maxlength="100" />
							<div style="margin-top:6px;" class="checkbox_group">
								<div style="float:left;"><input name="is_active" id="upload_image_status" type="checkbox" checked /><label class="field_label" for="upload_image_status">Active</label></div>
								<div style="padding-left:75px;"><input name="is_primary" id="upload_image_primary" type="checkbox" /><label class="field_label" for="upload_image_primary">Primary Image</label></div>
								<div style="height:4px;overflow:hidden;"></div>
								<div style=""><input name="is_distributable" id="upload_image_distributable" type="checkbox" /><label class="field_label" for="upload_image_distributable">Distributable (allow hi-res downloads)</label></div>
							</div>
							<div class="field_label">Photography Credit:</div><input class="text_field" maxlength="40" name="credit" />
							<div class="field_label">Expiration Date:</div><input class="text_field" maxlength="10" name="expiration_date" />
							<div class="field_label">Source:</div><input class="text_field" maxlength="40" name="source" />

							<input type="submit" value="Add Image" style="margin-top:4px;display:block;" />
							<!--<input type="hidden" name="plant_id" />-->
							<input type="hidden" name="submitted" value="1" />
						</form>
					</div>
				</div>
				<iframe name="iframe_upload" id="iframe_upload" onload="upload_complete();"></iframe>
			</body>
		</html>
<?
	}
?>