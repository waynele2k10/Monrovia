<?php 
/* 
	Template Name: Find a Professional
	Search For Landscape Professional
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
             <div class="search-form">
                <?php include('includes/landscape-pro-search.php'); ?>
            </div><!-- end search form -->
            <?php endwhile; endif; ?>
		</section>
		<?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>