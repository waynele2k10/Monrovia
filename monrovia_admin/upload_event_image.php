<?
	session_start();
	require_once('../inc/class_monrovia_user.php');
	$monrovia_user = new monrovia_user($_SESSION['monrovia_user_id']);

	$monrovia_user->permission_requirement('cmgt');
	$monrovia_user->permission_requirement('caln');

	if(isset($_POST['submitted'])&&$_POST['submitted']=='1'){
		require_once('../inc/class_monrovia_event_image.php');

		$base_target_path = "../img/events/";
		$file = $_FILES['file_path'];
		$title = $_POST['title'];
		$is_active = '0';
		if(isset($_POST['is_active'])) $is_active = checkbox_to_tinyint($_POST['is_active']);
		
		$event_id = intval($_GET['event_id']);
		
		$event_image = new monrovia_event_image();
		$event_image->info['monrovia_event_id'] = $event_id;
		$event_image->info['is_active'] = $is_active;
		$event_image->info['title'] = $title;
        $event_image->info['ordinal'] = $event_image->determine_order();
		
        $event_image->save();
        
		$target_path = $base_target_path . $event_image->info['monrovia_event_id'] . '/'.$event_image->info['id'] .'_original.jpg';
        
        //check if directory exists. If not, create it
        if (!file_exists($base_target_path . $event_image->info['monrovia_event_id']) && !is_dir($base_target_path . $event_image->info['monrovia_event_id'])) {
            mkdir($base_target_path . $event_image->info['monrovia_event_id']);
        }
        

		$success = (move_uploaded_file($file['tmp_name'], $target_path));
        if ($event_image->generate_thumbnails()){
            //we don't need to keep the original image
            unlink($target_path);    
        }
		
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
			<script src="/js/prototype.js"></script>
			<script src="/js/prototype_extensions.js" type="text/javascript"></script>
			<script src="/js/general.js"></script>

			<link rel="stylesheet" type="text/css" href="/inc/packer.php?path=/monrovia_admin/css/general.css" />

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
						var is_active = form.is_active.checked;
						
						if(!title){ alert('Please specify a title for this image.'); return false; }
						if(!file){ alert('Please specify an image.'); return false; }
						if(file.substr(file.length-4,4).toLowerCase()!='.jpg'){ alert('Only JPEGs (.jpg) may be used.'); return false; }
						
						$('upload_form_container').style.visibility='hidden';
						
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
						<form enctype="multipart/form-data" action="upload_event_image.php?event_id=<?=$_GET['event_id']?>" method="POST" id="upload_form" target="iframe_upload" onsubmit="return validate_image_upload();">
							<div class="field_label">Title:</div><input class="text_field" name="title" maxlength="40" />
							<div class="field_label">File:</div><input class="text_field" type="file" size="27" name="file_path" accept="image/jpg,image/jpeg" maxlength="100" />
							<div style="margin-top:6px;" class="checkbox_group">
								<input name="is_active" id="upload_image_status" type="checkbox" checked /><label class="field_label" for="upload_image_status">Active</label>
							</div>
							

							<input type="submit" value="Add Image" style="margin-top:4px;margin-bottom:4px;display:block;" />
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