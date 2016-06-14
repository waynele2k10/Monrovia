<?php
class admin_floorplan {
	function __construct() {
		function load_backend_scripts_image_mapper() {
			wp_enqueue_media ();                        
			wp_enqueue_script ( 'jquery-ui-droppable' );
                        
            //SET FANCYBOX
            wp_enqueue_script( 'jquery_fancybox', plugins_url( '/image-mapper/fancybox/jquery.fancybox-1.3.5.js'));
            wp_enqueue_script( 'jquery_fancyboxgallery', plugins_url( '/image-mapper/js/gallery_without_noConflict.js'));
            wp_enqueue_style( 'fancybox_css', plugins_url('/image-mapper/fancybox/jquery.fancybox-1.3.5.pack.css') );
                        
			wp_register_script ( 'uploader-js', plugins_url ( '/image-mapper/js/uploader.js' ) );
			wp_enqueue_script ( 'uploader-js' );
                        
            wp_register_script ( 'floorplan_custom-js', plugins_url ( '/image-mapper/js/floorplan_custom.js' ), array( 'jquery'));
			wp_enqueue_script ( 'floorplan_custom-js' );
                          
			wp_register_style ( 'floorplan-css', plugins_url ( '/image-mapper/css/floorplan.css' ) );
			wp_enqueue_style ( 'floorplan-css' );

			if (get_post_type() == 'post')
				{
	          	  wp_register_style ( 'iso.css', plugins_url ( '/image-mapper/css/bootstrap/iso.css' ) );
				  wp_enqueue_style ( 'iso.css' );
				  wp_register_style ( 'custom.css', plugins_url ( '/image-mapper/css/custom.css' ) );
				  wp_enqueue_style ( 'custom.css' );
				}
			
            wp_register_script ( 'bootstrap-js', plugins_url ( '/image-mapper/js/bootstrap/bootstrap.js' ) );
			wp_enqueue_script ( 'bootstrap-js' );
		}
		
		add_action ( 'admin_enqueue_scripts', 'load_backend_scripts_image_mapper' );
		
		function admin_init_image_mapper() {
			add_meta_box ( "image_map-meta", "Feature Image Plant Mapping", "image_map_admin", "post", "normal", "high" );
		}
		add_action ( "admin_init", "admin_init_image_mapper" );
		
		function image_map_admin() {
		?>

			<label for="upload_image" class="bootstrap-iso">
				<input id="postid" type="hidden" name="postid" value="<?php echo $_REQUEST ['post']; ?>" />
				<?php $mark_count = get_post_meta ( $_REQUEST ['post'], 'mark_count', true ); ?>
				<input id="mark_count" type="hidden" name="mark_count" value="<?php echo $mark_count; ?>" />
				<?php $image_id = get_post_meta ( $_REQUEST ['post'], 'floorplan_image_attachment_id', true ); ?>
				<input id="image_id" type="hidden" name="floorplan_image_id" value="<?php echo $image_id ?>" /> 
				<input id="upload_image_button" class="button" type="button" value="Upload Image" /> 
				<?php if (! empty($image_id)) : ?>
				<input type="button" value="Delete Image and Mark" style="padding: 3px 12px;" onclick="deleteImage();" class="btn btn-danger" />
				<?php endif; ?>
				<?php $mark_image_child =  get_post_meta ( $_REQUEST ['post'], '_mark_image_1', true ); ?>
				<?php if (! empty($mark_count)) : ?>
				<input type="button" value="Clear All Mark" style="padding: 3px 12px;" onclick="clearAllCameras();" class="btn btn-danger" />
				<?php endif; ?>
				<br /> <br />
			</label>
			<div class="clear"></div>
			<div style="clear: both;"></div>
			<?php
			//TODO: check if post is saved == if there is PostID
	
			// CHECK IF THERE IS AN IMAGE MAP
			if (isset ( $_REQUEST ['post'] )) {
				$attachment_id = get_post_meta ( $_REQUEST ['post'], 'floorplan_image_attachment_id', true );
				
				if ($attachment_id == true) {
					$imgurl = wp_get_attachment_url ( $attachment_id );
				}
				?>
		<div id="droppable" style="position: relative;">
			<img class="floorplan_image" src="<?php if(isset($imgurl)){echo $imgurl;}?>">
				  <?php
				
				$mark_count = get_post_meta($_REQUEST ['post'],'mark_count','true');
				for($i = 1; $i <= $mark_count; $i ++) {
                  	$mark_image =  get_post_meta ( $_REQUEST ['post'], '_mark_image_' . $i, true );
                    $position  = maybe_unserialize(get_post_meta ( $_REQUEST ['post'], '_mark_image_' . $i.'_position', true ));
                                        
					$mark_image_left = $position["left"];
					$mark_image_top =  $position["top"];
					
					
					if (! empty ( $mark_image )) {
						?>
						
						<a id="<?php echo $_REQUEST['post'],'_mark_image_'.$i;?>" class="mark-feature" onclick="editMarkerData('<?php echo $_REQUEST['post'],'_mark_image_'.$i;?>')"  style="position: absolute; top:<?php echo $mark_image_top; ?>; left:<?php echo $mark_image_left; ?>; "  data-toggle="modal" role="button" href="#myModal" >
								<!--img src="<?php echo plugins_url('/image-mapper/images/camera-button.png');?>" onclick="addToFancyboxWrap(this)" /-->
								<!--<div onclick="editMarkerData(<?php echo $_REQUEST['post'],'_mark_image_'.$i;?>)"  style="display:block; width: 61px;height: 61px;"></div>-->
						</a>
	                     <?php
					}
				}
				?> 
        </div>
			<?php } ?>
<div>
	<a href="javascript:void(0);" class="box mark-feature" role="button" data-toggle="modal" id="source-btn" style="<?php echo get_icon_mapper_html(); ?>"> 
		<div id="draggableimg" class="ui-widget-header" style="display:none; width: 25px;height: 25px;background: red;-moz-border-radius: 50px;-webkit-border-radius: 50px;border-radius: 50px;color: #fff;"></div>
	</a><br >
	<div id="fp-instruction-msg">Please drag the circle to your image</div>
</div><br/><br/>

<div>
<p style="color: #E64A31; font-weight: bold;">This function will override the feature image within the detail post only. Otherwise, please leave blank for default featured image.</p>
</div>

<div class="clear"></div>

<!-- Modal -->
<div class="bootstrap-iso">
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">X</button>
					<h4 class="modal-title" id="myModalLabel">Please Upload File</h4>
				</div>
				<div class="modal-body clearfix">
					<form id="markupload_file" method="post" action="" enctype="multipart/form-data">
						<p id="err-msg"></p>
						<p>
							<label for="upload_img"> </label> 
							<a id="upload_img" name="upload_img" size="30">Image </a> 
							<input type="hidden" id="top_val" name="top_val" value="" />
							<input type="hidden" id="left_val" name="left_val" value="" /> 
							<input type="hidden" id="marker_img_id" name="marker_img_id" value="" /> 
							<input type="hidden" id="post_id" name="post_id" value="<?php echo $_REQUEST ['post'];?>" />
						</p>
						<div style="float: left; width: 225px;">
							<label>Title</label>
							<input type="text" id="img-title" name="title" value="" /> 
							<label>Description </label>
							<input type="text" id="description" name="description" value="">
							<label>Link</label>
							<input type="text" id="link" name="link" value="">
						</div>
						<div style="float: left; margin-left: 50px; width: 200px;">
							<img style="width:200px" id="existing-floor-image" />
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<input type="button" name="upload_image_dummy" id="upload_image_dummy" value="Save" class="btn btn-primary" onclick="add_edit_room_details();" />
				</div>
			</div>
		</div>
	</div>
</div>
<div id="loadingDiv"></div>

<?php
		}
	}
}
?>


	



