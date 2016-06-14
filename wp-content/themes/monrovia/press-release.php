<?php /* Template Name: Press Release */
get_header(); ?>

		 <script>
			/*jQuery(function($) {
			//	$( ".tabs" ).tabs();
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
			<?php $transient_name = 'press_release';
			$press_release = monrovia_get_cache( $transient_name );
			if ( false === $press_release ) :
				ob_start();		?>
            <div class="accordian-wrap">
            	<ul class="accordian clear">
            <?php 
				// Get Current Year
			$year = $currentYear = date('Y');
			//Create an array of years for tabs
			for($year;$year>=2005;$year--){
				$years[] = $year;
			}
		
			//Loop through the years and print out Press Release for that year
			$currentYear = date('Y');
			foreach($years as $year){
				
				$block = 'none';
				$class='';
				if($currentYear == $year){
					$block = 'block';
					$class='open';
				}
				echo "<li><a href='#".$year."' class='$class'>".$year."</a>";

				//Set up the custom query
				$query =  "posts_per_page=-1&order=DESC&post_type=press_release&year=$year";
				$custom_query = new WP_Query();
				$custom_query->query($query); 
				?>
				<div id="<?php echo $year ?>" class="accordianTarget" style="display:<?php echo $block; ?>">
                <h2><?php echo $year; ?> Press Releases</h2>
				<?php if($custom_query->have_posts()) : $post = $posts[0]; while ($custom_query->have_posts()) : $custom_query->the_post();  ?>
                    <div class="faq">
                    	<date><?php echo the_date('M j, Y'); ?></date>
                    	<h2><a href="<?php the_permalink(); ?>" ><?php the_title(); ?></a></h2>
                    </div><!-- end faq -->
                <?php endwhile; endif; wp_reset_query(); wp_reset_postdata();?>
                </div></li>
             <?php } //End for each ?>
			</ul></div><!-- end tabs -->
			<?php $press_release = ob_get_clean();
				monrovia_set_cache( $transient_name, $press_release, MONROVIA_TRANSIENT_EXPIRE );
			endif;
			echo $press_release;?>
		</section>
        <?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>
