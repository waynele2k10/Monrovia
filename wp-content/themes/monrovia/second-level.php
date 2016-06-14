<?php /* Template Name: Second Level

	 // Use this Template for creating second level pages that have
	 // teriary pages.  These tertiary pages will be listed as a grid
	 // underneath the main content of the page

 */
 
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
			<?php /* $transient_name = 'second_level';
			$second_level = monrovia_get_cache( $transient_name );
			if ( false === $second_level ) :
				ob_start();	*/ ?>
        	<div class="tertiary-grid clear">
        	<?php
				//Set up the custom query
				$custom_query = new WP_Query();
				$all = $custom_query->query(array('post_type' => 'page', 'orderby' => 'menu_order', 'order' => 'ASC', 'posts_per_page' => -1 ));
				//Get all the Child Pages of current page
				$children = get_page_children($post->ID, $all );
				//Loop through the Children
				foreach($children as $key => $child){ ?>
                <?php $thumbnail = wp_get_attachment(get_post_thumbnail_id($child->ID));
					//If theres an Image  Caption, use that as the title,
					//else default to the title
					$title = get_the_title($child->ID);
					if($thumbnail['caption']) $title = $thumbnail['caption'];
				 ?>
                	<div class="children left">
                    	<a href="<?php echo get_permalink($child->ID); ?>">
                    		<img src="<?php echo $thumbnail['src']; ?>" alt="<?php echo get_the_title($child->ID); ?>" />
                        </a>
                        <h4><?php echo $title; ?></h4>
                        <a href="<?php echo get_permalink($child->ID); ?>" title="Read More">Read more »</a>
                    </div><!-- end child page -->
				<?php  if(($key+1) % 4 == 0) echo "<br style='clear:both' />";
						} ?>
        	</div><!-- end tertiary grid -->
        	<?php /* $second_level = ob_get_clean();
				monrovia_set_cache( $transient_name, $second_level, MONROVIA_TRANSIENT_EXPIRE );
			endif;
			echo $second_level; */	?>
        </section>
	<?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>