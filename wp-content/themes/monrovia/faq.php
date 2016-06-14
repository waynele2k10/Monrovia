<?php /* Template Name: FAQ */
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
			$transient_name = 'faq';
			$faq = monrovia_get_cache( $transient_name );
			if ( false === $faq ) :
				ob_start();		
			// Get All Children of Retailers Category
			$arg = array( 'taxonomy' => 'faq_category', 'parent' => 21, 'orderby' => 'ID', 'order' => 'ASC', 'hide_empty' => 0 );
			$retailers = get_categories( $arg );
			//Loop through all the Child Terms and save as an array
			foreach($retailers as $child){ $childArray[] = $child->term_id;}
			//Prepend the Parent ID to the array
			array_unshift( $childArray, '21'); 
			//Implode the array to a comma separated string
			$childArray = implode(',', $childArray );
			// Set the Get Categories arguments
			$args = array( 'taxonomy' => 'faq_category', 'hide_empty' => 0, 'orderby' => 'ID', 'exclude' => $childArray );
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
				<div id="<?php echo $category->slug; ?>" class='accordianTarget' style='display: <?php echo $style; ?>'>
				<?php if($custom_query->have_posts()) : $post = $posts[0]; while ($custom_query->have_posts()) : $custom_query->the_post();  ?>
                    <div class="faq">
                    	<h2><?php the_title(); ?></h2>
                        <?php the_content(); ?>
                    </div><!-- end faq -->
                <?php endwhile; endif; wp_reset_query(); wp_reset_postdata();
                echo "</div></li>";
			 	$count++; 
			 	} //End for each ?>
			</ul></div><!-- end tabs -->
			<?php $faq = ob_get_clean();
			      monrovia_set_cache( $transient_name, $faq, MONROVIA_TRANSIENT_EXPIRE );
			endif;
			echo $faq; ?>
		</section>
        <?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>
