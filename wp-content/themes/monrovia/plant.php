<?php 
/** 
	Template Name: Plant Detail
	Used for displaying the Plant Details
*/

?>
<?php 
$upload_dir = wp_upload_dir();
$GLOBALS['view_all_max'] = 500;
    
include('includes/classes/class_plant.php');

	//Get Plant ID from URL
	//$plant_id = $_GET['pid'];
	$plant_id = $wp_query->query_vars['pid'];
	
	//If plant id =  NULL, redirect
	if($plant_id == ""){
		//header("Location:/plant-catalog/");
	}
	//Get the Plant Record
	$record = monrovia_get_plant_record( $plant_id );
	
	//If no record, redirect
	if(!$record){
		//header("Location:/plant-catalog/");
	}
	
	$plant_names = html_sanitize($record->info['common_name']);
	$page_title = $plant_names . ' - Monrovia - ' . $plant_names;
	$page_meta_description = 'Monrovia\'s '.$plant_names.' details and information. Learn more about Monrovia plants and best practices for best possible plant performance.';
	$page_meta_keywords = $plant_names . ', monrovia '.$plant_names.', '.$plant_names.' information, '.$plant_names.' requirements, '.$plant_names.' design ideas';

	$page_meta_facebook_thumbnail = site_url().'/wp-content/uploads/plants/primary.php?id=' . $record->info['id'];

?>
<?php include('header.php'); ?>

    <div class="content_wrapper clear">
    <?php
		$transient_name = 'plant_' . $plant_id;
	?>
	
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID, NULL, $record->info['common_name'] ); //Print the Breadcrumb 
		//Set up some purchase variables
			$status = "false";
			$magento_url = get_option( 'vmwpmb_magento_site_url', '' );

			//Check Plant Availiability via Database
			$buyPlant = isForSale($record->info['item_number']); 
			$plantStatus = $record->info['release_status_id'];
			$plantGenus = $record->info['botanical_genus'];
			$statusArray = array(2, 3, 4, 6);
			
			// Set Special Messaging if apllicable
			if(isset($record->info['release_status_id'])){
			$release_status_messages = array(
			'3'=>'Coming Soon!', // NI
			'4'=>'Provided for consumer information&mdash;Monrovia is not currently growing this plant.', // II
			'6'=>'Provided for consumer information&mdash;Monrovia is not currently growing this plant.' // F
		);
		
		/*$msg_release_status = '';
		if(isset($release_status_messages[$record->info['release_status_id']])) $msg_release_status = $release_status_messages[$record->info['release_status_id']];
		if($msg_release_status!=''){
			echo "<p class='message'>$msg_release_status</p>";
		} */
	}  ?>
            
	<!-- Main Plant Details -->
    <div class="plant-main print_hide clear">
    	<h1 class="showMobile"><?php echo html_sanitize($record->info['common_name']);?></h1>
    	<div class="slideshow-wrapper left">
        	<!-- <div class="flag-new">New Plant</div> -->
			<?php
			// If theres at least one image, then print out the slider
			$data = getPlantData($plant_id);		
    		if($data['image-id'] != 'no-image'){ ?>
        	<div id="cycle-1" class="cycle-slideshow"
            			data-cycle-slides="> a.slide-a"
    					data-cycle-fx="scrollHorz"
    					data-cycle-pause-on-hover="true"
                        data-cycle-timeout=0
    					data-cycle-speed="500"
                		data-cycle-swipe=true
                		data-cycle-pager=".cycle-pager"
    					data-cycle-pager-template=""
                        data-cycle-auto-height="false"
					>
            		<!-- prev/next links -->
   					<div class="cycle-prev"></div>
    				<div class="cycle-next"></div>
                    <?php if($buyPlant): ?>
                    <a class="for-sale slide-show-icon" target="_blank" title="Buy Now" href="<?php echo $buyPlant; ?>"><i class="fa fa-shopping-cart"></i></a>
                    <?php endif; ?>
        			<?php $record->website_output_details(); ?>
        	</div><!-- end cycle-slideshow -->
			<div id="cycle-2" class="cycle-slideshow"
        			data-cycle-slides="> img"
        			data-cycle-timeout=0
        			data-cycle-fx="carousel"
        			data-cycle-carousel-visible=4
        			data-allow-wrap=false
                    data-cycle-carousel-slide-dimension='92px'
        		>
 				<?php $record->website_output_details_thumbnails(); ?>
            </div><!-- end cycle-2 -->
			<script>
				jQuery( '#cycle-1' ).on( 'cycle-after', function( event, optionHash, outgoingSlideEl, incomingSlideEl, forwardFlag ) {
					var next_index = optionHash.nextSlide;
					var curr_index = optionHash.currSlide;
					jQuery('.pin_'+next_index).css('display','inline-block');
					jQuery('.pin_'+curr_index).css('display','none');
					
				});
			</script>
         <?php // Else print nothing, for now
	} else { echo "<img src='".get_template_directory_uri()."/img/no-image-large.png' width='355' height='274'/>"; } ?>
    	 </div><!-- end slideshow-wrapper -->
         <div class="plant-main-right">
         <?php // If its an admin, print out edit plant button
		if(check_user_role('administrator')){ ?>
			<input type="button" value="Edit this plant"onclick="window.open('/monrovia_admin/plant_edit.php?id=<?php echo $plant_id?>');" style="display:block;" class="print_hide green-btn right"/>
        <?php }  ?>
         	<h1 class="hideMobile"><?php echo html_sanitize($record->info['common_name']);?></h1>
			<h2><?php echo html_sanitize($record->info['botanical_name']); ?></h2>
            <span class="item-number">Item #<?php echo html_sanitize($record->info['item_number'])?></span>
            <span>USDA Hardiness Zone: 
			<?php if($record->info['cold_zone_low']!='0'&&$record->info['cold_zone_high']!='0'){ ?>
						<?php if($record->info['cold_zone_low']==$record->info['cold_zone_high']){ ?>
							<a href="/plant-catalog/search/?cold_zone=<?php echo html_sanitize($record->info['cold_zone_low'])?>"><?php echo html_sanitize($record->info['cold_zone_low'])?></a>
						<?php }else{ ?>
							<a href="/plant-catalog/search/?cold_zone=<?php echo html_sanitize($record->info['cold_zone_low'])?>%2C<?php echo html_sanitize($record->info['cold_zone_high'])?>"><?php echo html_sanitize($record->info['cold_zone_low'])?> - <?php echo html_sanitize($record->info['cold_zone_high'])?></a>
						<?php } ?>
					 <?php }else{
						 $cold_zone = intval($record->info['cold_zone_low'])+intval($record->info['cold_zone_high']);
						?>
						<a href="/plant-catalog/search/?cold_zone=<?php echo $cold_zone?>"><?php echo $cold_zone?></a>
					 <?php } ?>
                     </span>
              <!-- End Hardiness Zone -->
         <?php include('includes/plant-details-screen.php'); ?>
         </div><!-- end plant-main-right -->
         <div class="plant-description clearfix">
         	<h3>Plant Description</h3>
         	<p><?php echo $record->info['description_benefits']; ?></p>
         </div><!-- end plant description -->
	</div><!-- end plant-main -->
    <div class="plant-tabs">
    <?php
			$transient_name = 'plant_details_tabs_' . $plant_id;
			$plant_details_tabs = monrovia_get_cache( $transient_name );
			if ( false === $plant_details_tabs ) :
				ob_start();		
				include( 'includes/plant-detail-tabs.php' );
				$plant_details_tabs = ob_get_clean();
				monrovia_set_cache( $transient_name, $plant_details_tabs, MONROVIA_TRANSIENT_EXPIRE );
			endif;
			echo $plant_details_tabs;				
		?>
     </div><!-- end plant tabs -->
     <!-- Check to see if there are videos -->
			<?php
			$transient_name = 'plant_details_videos_' . $plant_id;
			$plant_details_videos = monrovia_get_cache( $transient_name );
			if ( false === $plant_details_videos ) :
				ob_start();
		
			//Set up the custom query
				$query =  "posts_per_page=-1&order=DESC&post_type=youtube_video&meta_value=".$data['item']."&meta_key=plants_in_video_plant_id";
				$custom_query = new WP_Query();
				$custom_query->query($query); 
				$count = $custom_query->post_count;
				$i=1;
			if($count>0){ ?>
				<div class="video-grid clear">
                <h3>Videos</h3>
			<?php if($custom_query->have_posts()) : $post = $posts[0]; while ($custom_query->have_posts()) : $custom_query->the_post(); ?>
					<div class="two-col left clear">
                    	<a href="<?php echo the_permalink(); ?>" title="<?php the_title(); ?>" class="left">
                        	<?php //the_post_thumbnail(); ?>
                            <img src="http://img.youtube.com/vi/<?php echo get('youtube_id'); ?>/mqdefault.jpg" alt="YouTube Video" />
                        </a>
						<div class="video-info left">
                        	<?php if(strtotime(get_the_date())>strtotime('-6 months')) echo'<div class="flag-new">NEW</div>'; ?>
                        	<a href="<?php echo the_permalink(); ?>" class="video_title"><?php echo the_title(); ?></a> <span class='small'>(<?php echo get('duration'); ?>)</span><br />
							<div class="video_description small"><?php monrovia_wp_excerpt('monrovia_long_excerpt', '...'); ?></div>
						</div><!-- end video_info -->
					</div><!-- end two col -->
				<?php if( $i % 2 == 0) echo "<br class='clearfix' />"; ?> 
			<?php $i++; endwhile; endif; wp_reset_query(); wp_reset_postdata();?>
            <a href="<?php echo get_permalink(39); ?>" class="right clearfix more">More Videos ></a>
			</div><!-- end video_grid -->
        <?php } ?>
        
			<?php
				$plant_details_videos = ob_get_clean();
				monrovia_set_cache( $transient_name, $plant_details_videos, MONROVIA_TRANSIENT_EXPIRE );
			endif;
			echo $plant_details_videos;
			?>
			
		</section>
		<?php get_sidebar('right'); ?>
        <?php get_sidebar('lower-right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>

    <!-- Begin Icon Tool Tip -->
    <div class="tool-tip icon-tip">
        <div class="tip-content">
            <span class="tip-close"><i class="fa fa-times-circle"></i></span>
            <h4></h4>
            <img class="alignleft" src="">
            <p></p>
            <a title="Learn More" href="/plant-catalog/icons-at-a-glance/">Learn More &gt;</a>
        </div>
    </div><!-- end tool-tip -->
    <!-- end tool tip -->