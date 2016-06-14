<?php if (have_posts()): while (have_posts()) : the_post(); ?>
	
	<!-- article -->
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>		
		<!-- post title -->
        <?php $link = get_permalink();
			//If its an archived plant savvy, reconfigure the URL
			if(get_post_type( $post ) == 'newsletter_archive'){
				$base = get_permalink(16);
				$hash = '#'.strtolower(get_the_date('M-Y'));
				$link = $base.$hash;	
			}
		?>
		<h4><a href="<?php echo $link; ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a></h4>
        <div class="search-meta-info">
        	<strong><?php echo convertPostType( get_post_type( $post ), $post->ID); ?></strong>&nbsp|&nbsp&nbsp
        	<span class="meta-date"><?php the_time('F j, Y'); ?></span>
            
        </div><!-- end search meta -->
		<!-- /post title -->
		<?php the_excerpt();?>	
	</article>
	<!-- /article -->	
<?php endwhile; ?>

<?php else: ?>

	<!-- article -->
	<article>
		<h2>Sorry, your search returned no results :(</h2>
	</article>
	<!-- /article -->

<?php endif; ?>