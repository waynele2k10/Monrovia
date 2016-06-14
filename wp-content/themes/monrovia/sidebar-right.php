<div id="right-sidebar" class="sidebar">

	<div class="hideMobile">
    	<?php include('includes/zone-box.php'); ?>
	</div>
	<?php
	
		// Set the Variables
		$id =  $wp_query->post->ID;
		$url = 	get_permalink($id);
		?> 
		<?php
		$pin_url='http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		if (isset($wp_query->query_vars['pid'])) {
			$plant_id = $wp_query->query_vars['pid'];
			$record = monrovia_get_plant_record( $plant_id );
			$record->get_images(true);
			$image_set = $record->info['image_primary'];
		}
		
		
		if (isset($record->info['image_primary']) && $image_set != null) {
			$pin_media = $image_set->info["path_detail"];
		} else {
			$pin_media = 'http://'.$_SERVER['SERVER_NAME'].'/wp-content/themes/monrovia/img/FB_image.jpg';
		}
		?>
        <div class="share-this addthis_toolbox clear hideMobile">
        	<div>Share:</div>
            <a class="addthis_button_facebook"><img src="<?php echo get_template_directory_uri(); ?>/img/spacer.gif" title="Facebook" /></a>
            <a class="addthis_button_pinterest_share" pi:pinit:url="<?php echo $pin_url ?>" pi:pinit:media="<?php echo $pin_media ?>"><img src="<?php echo get_template_directory_uri(); ?>/img/spacer.gif" title="Pinterest"/></a>
            <a class="addthis_button_twitter"><img src="<?php echo get_template_directory_uri(); ?>/img/spacer.gif" title="Twitter" /></a>
            <a href="https://plus.google.com/share?url=<?php echo $url ?>"  onclick="javascript:window.open(this.href,'','menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;">
            <img src="<?php echo get_template_directory_uri(); ?>/img/spacer.gif" title="Google+" /></a>		
            <a class="addthis_button_email"><img src="<?php echo get_template_directory_uri(); ?>/img/spacer.gif" title="Email" /></a>
            <a class="addthis_button_print" href="javascript:void(0);" onclick="window.print();"><img src="<?php echo get_template_directory_uri(); ?>/img/spacer.gif" title="Print" /></a>
            <a class="addthis_button_compact"><img src="<?php echo get_template_directory_uri(); ?>/img/spacer.gif" title="Compact" /></a>
        </div><!-- end share-this -->
    
    <div class="sidebar-inner">
             <?php if (function_exists('dynamic_sidebar') && dynamic_sidebar('Right Sidebar')) : else : ?>
        		<!-- All this stuff in here only shows up if you DON'T have any widgets active in this zone -->
			<?php endif; ?>
    </div><!-- sidebar inner -->

</div>