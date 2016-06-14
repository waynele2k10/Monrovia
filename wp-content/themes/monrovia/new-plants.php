<?php
	// Template Name: New Plants
	// Code borrowed from Old Monrovia
	
 get_header();

?>
 	
    <div class="content_wrapper clear">
		<section role="main" class="main">
		
			<?php
				$transient_name = 'new_plants';
				$new_plants_content = monrovia_get_cache( $transient_name );
				if ( false === $new_plants_content ) :
					ob_start();					
					require_once('includes/classes/class_search_plant.php');
				?>
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<!-- article -->
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php the_content(); ?>
			</article>
			<!-- /article -->
			<?php endwhile; endif; ?>

	<!-- NEW PLANTS -->
		<h2 class="border-top">Featured for 2015</h2>
					<?php
						$item_numbers = explode(',','4958,7049,6638,9407,7742,6643,4334,8929,7628,9306,7511,7516,8995,8998,5117,7664,6657,5132,8170,8171');
						$item_numbers = explode(',','9957,2394,1390,4676,1725,3073,4674,8582,3303,6418,9275,9265,2109,1907,4813,7993,40989,4943,4947,1658,4675,7664,7576,5514,7240,5132,40976,40997');



						$search_plants = new search_plant('','id,item_number,common_name,botanical_name,is_new',false);
						$search_plants->order_by = 'botanical_name ASC';
						$search_plants->results_per_page = 100;
						$search_plants->criteria['item_number'] = $item_numbers;
						$search_plants->search(false);
						$search_plants->output_results_plant_search();
					?>
	<!-- /NEW PLANTS -->
	<!-- RECENTLY INTRODUCED -->
		<h2 class="border-top">Recently Introduced</h2>
					<?php
						$search_plants = new search_plant('','id,item_number,common_name,botanical_name,is_new',false);
						$search_plants->order_by = 'common_name ASC';
						$search_plants->results_per_page = 100;
						
						$search_plants->criteria['is_new'] = '1';
						$search_plants->criteria['release_status_id'] = array('1','2','3'); // A, NA, NI
						
						$search_plants->search(false);
						$search_plants->output_results_plant_search();
					?>
	<!-- /RECENTLY INTRODUCED -->
<?php //} ?>

	<a href="/plant-catalog/search/?is_new=1&release_status_id=1,2,3&sort_by=common_name">View all new plants &raquo;</a>
	<?php
		$new_plants_content = ob_get_clean();
		monrovia_set_cache( $transient_name, $new_plants_content, MONROVIA_TRANSIENT_EXPIRE );
		endif;
		
		echo $new_plants_content;
	?>
		</section>
		<?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>