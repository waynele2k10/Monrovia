<?php
 /* 
 
 	Template Name: Design Inspiration Landing
	// Updated template for Designs Approved
	// Feb-24-2014


 */
 
 // Uncomment these to Turn on Errors for this page
	ini_set('display_errors','on');
	error_reporting(E_ALL);
get_header(); ?>

<!--  Conditional Container Tag: Monrovia (7439) | Design Inspiration Page (56252) | 2016 Grow Beautifully  (5972) | Expected URL: http://www.monrovia.com/design-inspiration/ --> <script type="text/javascript"> var ftRandom = Math.random()*1000000; 
document.write('<iframe style="position:absolute; visibility:hidden; width:1px; height:1px;" src="http://servedby.flashtalking.com/container/7439;56252;5972;iframe/?spotName=Design_Inspiration_Page&cachebuster='+ftRandom+'"></iframe>'); 
</script>

    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<!-- article -->
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php the_content(); ?>
			</article>
			<!-- /article -->
			<?php endwhile; endif; ?>
        	<div class="inspiration-grid clear">
        	<?php
        	//$transient_name = 'inspiration_gallery';
				//$inspiration_gallery = monrovia_get_cache( $transient_name );
				//if ( false === $inspiration_gallery ) :
				//ob_start();	
				
				$custom_query = new WP_Query();
				$custom_query->query(array('post_type' => 'design_style', 'orderby' => 'menu_order', 'order' => 'ASC', 'posts_per_page' => -1 ));
				//Loop through the Children
				$i=1;
				if($custom_query->have_posts()) : $post = $posts[0]; while ($custom_query->have_posts()) : $custom_query->the_post();  ?>
                <?php $thumbnail = wp_get_attachment(get_post_thumbnail_id($post->ID));
					//If theres an Image  Caption, use that as the title,
					//else default to the title
					$title = get_the_title();
					if($thumbnail['caption']) $title = $thumbnail['caption'];
					
					//Get the category
					$terms= get_the_terms($post->ID, 'garden_style');
					if ( $terms && ! is_wp_error( $terms ) ){ 
						foreach ( $terms as $term ) {
							$taxSlug = $term->slug;
						}
					}
				 ?>
                	<div class="children left<?php if(($i) % 2 == 0) echo" last";?>">
                    	<a href="<?php echo get_permalink(); ?>" class="block">
                        	<img src="<?php echo $thumbnail['src']; ?>" alt="<?php echo get_the_title(); ?>" width="355" />
                        </a>
                        <?php $gardens = getDesignPlants($taxSlug, true); ?>
                        <div class="meta clearfix">
                        	<a href="<?php echo get_permalink(); ?>" title="<?php echo $title; ?>"><?php echo $title; ?></a>
                        	<span class="right" style="display:none;"><?php echo $gardens['gardens']; ?> Gardens&nbsp;|&nbsp;<?php echo $gardens['plants'];?> Plants</span>
                        </div><!-- end meta -->
                    </div><!-- end child page -->
				<?php  if(($i) % 2 == 0) echo "<br style='clear:both' />";
					$i++;
					endwhile; endif; wp_reset_query(); wp_reset_postdata();
					//$inspiration_gallery = ob_get_clean();
					//monrovia_set_cache( $transient_name, $inspiration_gallery, MONROVIA_TRANSIENT_EXPIRE );
				//endif;
				//echo $inspiration_gallery;	?>			
        	</div><!-- end tertiary grid -->
        </section>
	<?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>