<?
	session_start();
	require_once('../inc/class_monrovia_user.php');
	$monrovia_user = new monrovia_user($_SESSION['monrovia_user_id']);

	$monrovia_user->permission_requirement('cmgt');
	$monrovia_user->permission_requirement('caln');

	if(isset($_POST['submitted'])&&$_POST['submitted']=='1'){

		$event_id = intval($_GET['event_id']);
		if($event_id==0) exit;

		require_once('../inc/class_monrovia_event_image.php');

		$base_target_path = "../img/events/";
        
        //check if directory exists. If not, create it
        if (!file_exists($base_target_path.$event_id) && (!is_dir($base_target_path.$event_id))) {
            mkdir($base_target_path.$event_id);
        }
        
        $target_path = $base_target_path . $event_id . '/thumbnail_orig.jpg';
        $dest_filename = $base_target_path . $event_id . '/thumbnail.jpg';
        
        $file = $_FILES['file_path'];
            
        $success = (move_uploaded_file($file['tmp_name'], $target_path));
        if ($success){
            if (generate_custom_thumbnail(60,60,$target_path,$target_path,$dest_filename)){
                sql_query("UPDATE monrovia_events SET has_thumbnail='1' WHERE id = ".$event_id);
                //we don't need to keep the original image
                unlink($target_path);   
            } 
        }
        
        require_once('../inc/class_monrovia_event.php');
        $event = new monrovia_event($event_id);
        $event->clear_cache();
		
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
					font-size: 9pt;
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
						var file = (form.file_path.value + '').strip();
						
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
					parent.thumbnail_upload_complete();
				}
				
			</script>
			<body>
				<div id="content">
					<div id="upload_form_container">
						<form enctype="multipart/form-data" action="?event_id=<?=$_GET['event_id']?>" method="POST" id="upload_form" target="iframe_upload" onsubmit="return validate_image_upload();">
							Upload (image will be resized to 60px x 60px)
							<div style="padding-top:.75em;">
								<input type="file" name="file_path" accept="image/jpg,image/jpeg" maxlength="100"/> 
								<input type="submit" value="Add Image" style="margin-top:4px;display:block;" />
								<input type="hidden" name="submitted" value="1" />
							</div>
						</form>
					</div>
				</div>
				<iframe name="iframe_upload" id="iframe_upload" onload="upload_complete();"></iframe>
			</body>
		</html>
<?
	}
?>