<?php get_header(); ?>
<div class="row">
	<div class="col-md-12">
		<div class="article-header">
			<div class="row">
				<div class="col-md-6">
					<h1><?php echo __("Posts from ", "monroviablog") . get_the_date('F d, Y'); ?></h1>
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
			<div class="no-post">
				Your search returns no results.
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php get_footer(); ?>