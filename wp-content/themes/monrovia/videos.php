<?php /* Template Name: Videos */
get_header(); ?>

<!--  Conditional Container Tag: Monrovia (7439) | Videos page (56254) | 2016 Grow Beautifully  (5972) | Expected URL: http://www.monrovia.com/gardening-videos/ --> <script type="text/javascript"> var ftRandom = Math.random()*1000000; 
document.write('<iframe style="position:absolute; visibility:hidden; width:1px; height:1px;" src="http://servedby.flashtalking.com/container/7439;56254;5972;iframe/?spotName=Videos_page&cachebuster='+ftRandom+'"></iframe>'); 
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
			<?php $transient_name = 'videos';
			$videos = monrovia_get_cache( $transient_name );
			if ( false === $videos ) :
				ob_start();	?>
            <div class="video-grid clear">
			<?php
			//Set up the custom query
				$query =  "posts_per_page=-1&order=DESC&post_type=youtube_video";
				$custom_query = new WP_Query();
				$custom_query->query($query); 
				$i=1;

			if($custom_query->have_posts()) : $post = $posts[0]; while ($custom_query->have_posts()) : $custom_query->the_post(); ?>
					<div class="two-col left clear">
                    	<a href="<?php echo the_permalink(); ?>"title="<?php the_title(); ?>" class="left">
                        	<?php //the_post_thumbnail(); ?>
                            <img src="http://img.youtube.com/vi/<?php echo get('youtube_id'); ?>/mqdefault.jpg" />
                        </a>
						<div class="video-info left">
                        	<?php if(strtotime(get_the_date())>strtotime('-6 months')) echo'<div class="flag-new">NEW</div>'; ?>
                        	<a href="<?php echo the_permalink(); ?>" class="video_title"><?php echo the_title(); ?></a> <span class='small'>(<?php echo get('duration'); ?>)</span><br />
							<div class="video_description small"><?php monrovia_wp_excerpt('monrovia_long_excerpt', '...'); ?></div>
						</div><!-- end video_info -->
					</div><!-- end two col -->
				<?php if( $i % 2 == 0) echo "<br class='clearfix' />"; ?> 
			<?php $i++; endwhile; endif; wp_reset_query(); wp_reset_postdata();?>
			</div><!-- end video_grid -->
			<?php $videos = ob_get_clean();
				monrovia_set_cache( $transient_name, $videos, MONROVIA_TRANSIENT_EXPIRE );
			endif;
			echo $videos;	?>
		</section>
        <?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>
