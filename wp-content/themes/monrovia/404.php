<?php get_header(); ?>
<?php $four = get_post(1322); ?>
	<!-- section -->
	<section role="main">
		<!-- article -->
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h1><?php echo $four->post_title; ?></h1>
            <?php echo $four->post_content; ?>			
		</article>
		<!-- /article -->
	</section>
	<!-- /section -->
<?php get_footer(); ?>