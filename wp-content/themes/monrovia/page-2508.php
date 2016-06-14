<?php get_header(); ?>
	
    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<!-- article -->
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php the_content(); ?>
<div class="savvy-social clear" style="margin-left: 0;"><a title="Facebook" href="http://www.facebook.com/pages/Monrovia/102411039815423?v=wall&amp;ref=sgm" target="_blank"></a><a title="Twitter" href="http://twitter.com/MonroviaPlants/" target="_blank"></a><a title="Pinterest" href="https://www.pinterest.com/monroviaplants/" target="_blank"></a><a title="Instagram" href="http://instagram.com/MonroviaNursery#" target="_blank"></a><a title="Google+" href="https://plus.google.com/106439322773521086880/" target="_blank"></a></div>
			</article>
			<!-- /article -->
			<?php endwhile; endif; ?>
		</section>
		<?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>