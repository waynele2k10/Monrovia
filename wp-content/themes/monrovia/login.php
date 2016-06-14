<?php 
/**
	Template Name: Login
*/

get_header(); ?>
	
    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<!-- article -->
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php if(isset($_GET['notice']) && $_GET['notice'] != ''){ ?>
            		<?php if($_GET['notice'] == 'newsletter'){ ?>
            			<p class="message">To sign up for our newsletter, please first register for an account.</p>
                    <?php } elseif($_GET['notice'] == 'catalog') { ?>
                    	<p class="message">You must be signed in to create and edit custom catalogs.</p>
                    <?php } else { ?>
                    	<p class="message">You must be signed in to add items to your Favorites list.</p>
                    <?php } ?>
				<?php  }  ?>
				<?php theme_my_login( array( 'default_action' => 'login' ) ); ?>
				<?php theme_my_login( array( 'default_action' => 'register' ) ); ?>
				<?php //the_content(); ?>
             </article>
			<!-- /article -->
			<?php endwhile; endif; ?>
		</section>
	</div><!-- end content_wrapper -->
<script>
jQuery(document).ready( function($){

	$('article br').remove();
});
</script>
<?php get_footer(); ?>