<?php /* Template Name: Locations */
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
            <article class="clear col-wrapper">
			<?php
			$transient_name = 'locations';
			$locations = monrovia_get_cache( $transient_name );
			if ( false === $locations) :
				ob_start();	
				// Set the Get Categories arguments
				$args = array( 'taxonomy' => 'job_location', 'hide_empty' => 0, 'orderby' => 'ID' );
				$categories = get_categories( $args );
			
				//Loop through the categories and print out posts for each
				foreach($categories as $key => $category){

					?>
                	<div class="two-col left">
                		<img src="<?php echo z_taxonomy_image_url($category->term_id); ?>"/>
                        <p><?php echo get_tax_meta( $category->term_id, 'ba_textarea_locations' ); ?></p>
					</div><!-- end two_col -->
                    <?php if(($key+1) % 2 == 0) echo "<br class='clearfix' />"; ?> 
             <?php } //End for each ?>
             <?php $locations = ob_get_clean();
					monrovia_set_cache( $transient_name, $locations, MONROVIA_TRANSIENT_EXPIRE );
			endif;
			echo $locations;?>
             </article>
		</section>
        <?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>
