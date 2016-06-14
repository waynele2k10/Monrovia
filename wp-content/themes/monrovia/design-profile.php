<?php 
/* 
	Template Name: Design Profile Edit
	
*/

require_once('includes/designers.php');

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
            <div class="design-profile">
            	<?php include('includes/design-profile-form.php'); // Include the Design Profile Form ?>
            </div><!-- end design profile -->
			<!-- /article -->
			<?php endwhile; endif; ?>
		</section>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>