<?php if (function_exists('get_header')){
	 get_header(); 
} else{
	exit;
}?>

<!--  Conditional Container Tag: Monrovia (7439) | Monroviacom Homepage (56249) | 2016 Grow Beautifully  (5972) | Expected URL: http://www.monrovia.com/ --> 
<script type="text/javascript"> 
var ftRandom = Math.random()*1000000; 
document.write('<iframe style="position:absolute; visibility:hidden; width:1px; height:1px;" src="http://servedby.flashtalking.com/container/7439;56249;5972;iframe/?spotName=Monroviacom_Homepage&cachebuster='+ftRandom+'"></iframe>'); 
</script>

    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php // Define Homepage Custom Query
        $transient_name = 'home_page';
			$home_page = monrovia_get_cache( $transient_name );
			if ( false === $home_page ) :
				ob_start();		
			$query =  array( 'post_type' => 'home_page');
			$custom_query = new WP_Query($query);

			if($custom_query->have_posts()): while($custom_query->have_posts()): $custom_query->the_post(); ?>
			<?php //include('includes/homepage-slideshow.php'); ?>
            <!-- Start Homepage Content Blocks -->
            <div class="home-promo-wrap left">
            	<?php $promos = get_group('home_content_blocks');
					  foreach($promos as $promo){ ?>
						  <div class="promo clear">
                          	<a href="<?php echo $promo['home_content_blocks_title_link'][1]?>">
                          	<img src="<?php echo $promo['home_content_blocks_promo_image'][1]['original']; ?>" class="left" alt="<?php echo $promo['home_content_blocks_block_title'][1]?>" />
                            </a>
                            <div class="promo-inner left">
                          		<a href="<?php echo $promo['home_content_blocks_title_link'][1]?>"><h2><?php echo $promo['home_content_blocks_block_title'][1]?></h2></a>
                                <?php echo $promo['home_content_blocks_block_content'][1] ?>
                            </div><!-- end promo-inner -->
                          </div><!-- end promo -->
						  
				<?php	  } ?>
                		<div class="promo clear featured-plants">
                        <h3>Plants in the spotlight</h3>
                        <?php $plants = get_field('featured_plants_plant_id_number');
							$i=0;
							foreach($plants as $plant){
								if($i<2){  //Limit the homepage to 2 plants
								$data = getPlantData('', $plant);
								$imageID = $data['image-id'];
								$title = $data['title'];
								$url = $data['seo'];
								 ?>
								<div class="featured-plant-home">
                                <a href="<?php echo site_url().'/plant-catalog/plants/'.$data['pid'].'/'.$data['seo']; ?>">
                                	<img src="<?php echo site_url().'/wp-content/uploads/plants/search_results/'.$imageID.'.jpg'?>" alt="<?php echo $title; ?>" />
                                </a>
                                <a href="<?php echo site_url().'/plant-catalog/plants/'.$data['pid'].'/'.$data['seo']; ?>" title="<?php echo $title; ?>"><?php echo $title; ?></a>
                                <?php $i++; } ?>
                                </div><!-- end featured-plant-home -->	 
						<?php	} ?>
                        </div><!-- end promo featured plants -->
            </div><!-- end promo wrap -->
            <!-- end content blocks -->
            
            <?php endwhile; endif; ?><?php wp_reset_query();
			$home_page = ob_get_clean();
				monrovia_set_cache( $transient_name, $home_page, MONROVIA_TRANSIENT_EXPIRE );
			endif;
			echo $home_page;
            ?>
        	<?php get_sidebar('homepage'); ?>
		</section>
	</div><!-- end content_wrapper -->
    <script>
		jQuery(document).ready( function(){
			//Populate hidden fields of MailChimp form
			var cZone = getCookie('cold_zone');
			var zipCode = getCookie('zip_code');
			jQuery('#zipcode').val(zipCode);
			jQuery('#coldzone').val(cZone);
		});
	</script>
    
<?php get_footer(); ?>