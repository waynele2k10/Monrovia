<?php /* Template Name: Retailer FAQ */
get_header(); ?>

		 <script>
			jQuery(function($) {
				$( ".tabs" ).tabs();
			});
		</script>
	
    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
			<?php
			
			$transient_name = 'retailer_faq';
			$retailer_faq = monrovia_get_cache( $transient_name );
			if ( false === $retailer_faq ) :
				ob_start();

			// Set the Get Categories arguments
			$args = array( 'taxonomy' => 'faq_category', 'parent' => 21, 'orderby' => 'ID', 'order' => 'ASC', 'hide_empty' => 0 );
			$categories = get_categories( $args );
			?>
            <div class="accordian-wrap">
            	<ul class="accordian clear">
            <?php 
				$count = 0;
				// Loop throught the category names
				foreach($categories as $category){
					
					if($count == 0){ $style = "block"; $class='open'; } else {$style='none'; $class='';}
					echo "<li><a href='#".$category->slug."'  class='".$class."'>".$category->name."</a>";
				
				//Set the Category Name
				$catSlug = $category->slug;
				//Set up the custom query
				$query =  "posts_per_page=-1&order=DESC&post_type=faq&faq_category=$catSlug";
				$custom_query = new WP_Query();
				$custom_query->query($query); 
				?>
				<div id="<?php echo $category->slug; ?>"  class='accordianTarget' style='display:<?php echo $style;?>;'>
				<?php if($custom_query->have_posts()) : $post = $posts[0]; while ($custom_query->have_posts()) : $custom_query->the_post();  ?>
                    <div class="faq">
                    	<h2><?php the_title(); ?></h2>
                        <?php the_content(); ?>
                    </div><!-- end faq -->
                <?php endwhile; endif; wp_reset_query(); wp_reset_postdata();?>
                </div></li>
             <?php 
			 	$count++;
			 	} //End for each ?>
			</ul></div><!-- end tabs -->
			<?php $retailer_faq = ob_get_clean();
				monrovia_set_cache( $transient_name, $retailer_faq, MONROVIA_TRANSIENT_EXPIRE );
			endif;
			echo $retailer_faq;?>
		</section>
        <?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>
