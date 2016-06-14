<?php get_header(); ?>
<div class="row">
	<div class="col-md-12">
		<div class="article-header">
			<div class="row">
				<div class="col-md-6">
					<h1><?php esc_html_e( sprintf( __( 'Search Results for: %s', 'monroviablog' ), get_search_query() ) ); ?></h1>
				</div>
				<div class="col-md-6">
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="content">
			<?php if (have_posts()) : ?>
			<div class="list-post">
				<ul>
				<?php while (have_posts()) : the_post(); ?>
					<?php get_template_part('partials/item'); ?>
				<?php endwhile; ?>
				</ul>
				<div class="clearfix"></div>
				<button id="pagination" class="bt-wide-all">LOAD MORE POSTS</button>
				<div class="clearfix"></div>
			</div>
			<?php else: ?>
				<?php get_template_part('partials/nothing-found'); ?>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php get_footer(); ?>