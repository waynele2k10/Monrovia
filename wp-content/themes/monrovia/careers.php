<?php /* Template Name: Careers */
get_header(); ?>

		 <script>
		/*	jQuery(document).ready( function($){
				$('.accordian-control').on('click', function(){
					$(this).parents('.accordian-container').find('.accordian').slideToggle(500);
				});
				$( ".tabs" ).tabs();
			}); */
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
			<?php
			/* $transient_name = 'careers';
			$careers = monrovia_get_cache( $transient_name );
			if ( false === $careers ) :
				ob_start();		
			// Set the Get Categories arguments
			$args = array( 'taxonomy' => 'job_location', 'hide_empty' => 0, 'orderby' => 'ID' );
			$categories = get_categories( $args );
			?>
            <div class="tabs">
            	<ul class="tab clear">
            <?php
				//Add an additional Category Object that will act as All locations
				$obj->slug = 'all';
				$obj->name = 'All locations';
				$categories[] = $obj;
				
			 	//Loop throught the category names
				foreach($categories as $category){
					echo "<li><a href='#".$category->slug."'>".$category->name."</a></li>";
				}
			?>
            	</ul>
            <?php
			// Get the number of Categories
			$count = count($categories);
			//Loop through the categories and print out posts for each
			foreach($categories as $key => $category){
				//Set the Category Name
				$catSlug = $category->slug;
				//Set up the custom query
				$query =  "posts_per_page=-1&order=DESC&post_type=job_listing&job_location=$catSlug";
				//If All Locations, dont query by Category
				if($key == ($count-1)){
					$query =  "posts_per_page=-1&order=DESC&post_type=job_listing";
					$catSlug = "all";
				}
				$custom_query = new WP_Query();
				$custom_query->query($query); 
				?>
				<div id="<?php echo $catSlug ?>">
                	<?php if($catSlug != "all"): //Do not display if All Locations?>
                	<div class="location-meta clear">
                    <img src="<?php echo z_taxonomy_image_url($category->term_id); ?>" class="left" width="250px"/>
                    	<p class="left"><?php echo $category->description; ?></p>
                    </div><!--end location meta -->
                    <?php endif; ?>
				<?php if($custom_query->have_posts()) { $post = $posts[0]; while ($custom_query->have_posts()) : $custom_query->the_post();  ?>
                    <div class="accordian-container">
                    	<div class="accordian-top">
                    		<a class="accordian-control left"><strong><?php the_title();?></strong> - <?php $terms = get_the_terms( $post->ID, 'job_location' ); foreach($terms as $term) echo $term->name; ?></a>
                        	<a href="https://rn21.ultipro.com/MON1010/JobBoard/ListJobs.aspx?__VT=ExtCan" class="green-btn small right" target="_blank">apply now</a>
                        </div><!-- end accordian container -->
                        <article class="accordian">
							<?php the_content(); ?>
                           <!-- <a href="https://azlink.monrovia.com/employment/" class="green-btn small left" target="_blank">apply now</a> -->
                        </article><!-- end accordian -->
                    </div><!-- end accordian-container -->
                <?php endwhile; } 
					  else{ ?>
					<div class="accordian-container">
                    	<div class="accordian-top">
						  	There are currently no positions available.
                        </div><!-- end accordian-top -->
                    </div><!-- end accordian container -->
					<?php  }
				 wp_reset_query(); wp_reset_postdata();?>
                </div>
             <?php } //End for each ?>
			</div><!-- end tabs -->
			<?php $careers = ob_get_clean();
				monrovia_set_cache( $transient_name, $careers, MONROVIA_TRANSIENT_EXPIRE );
			endif;
			echo $careers;
			*/
		?> 
		</section>
        <?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>
