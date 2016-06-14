<?php /* Template Name: Design Inspiration

	 // Variation on the second-level.php template

 */
 
 // Uncomment these to Turn on Errors for this page
	ini_set('display_errors','on');
	error_reporting(E_ALL);
get_header(); ?>
	
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
        	<div class="tertiary-grid clear">
        	<?php
        	$transient_name = 'inspiration_gallery';
				$inspiration_gallery = monrovia_get_cache( $transient_name );
				if ( false === $inspiration_gallery ) :
				ob_start();	
				
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
				 ?>
                	<div class="children left">
                    	<a href="<?php echo get_permalink(); ?>">
                        	<img src="<?php echo $thumbnail['src']; ?>" alt="<?php echo get_the_title(); ?>" />
                        </a>
                        <h4><?php echo $title; ?></h4>
                        <a href="<?php echo get_permalink(); ?>" title="Read More">Read more »</a>
                    </div><!-- end child page -->
				<?php  if(($i) % 4 == 0) echo "<br style='clear:both' />";
					$i++;
					endwhile; endif; wp_reset_query(); wp_reset_postdata();
					$inspiration_gallery = ob_get_clean();
					monrovia_set_cache( $transient_name, $inspiration_gallery, MONROVIA_TRANSIENT_EXPIRE );
				endif;
				echo $inspiration_gallery;	?>			
        	</div><!-- end tertiary grid -->
        </section>
	<?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>