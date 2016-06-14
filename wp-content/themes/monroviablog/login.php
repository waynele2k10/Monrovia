<?php 
/**
	Template Name: Login
*/
get_header(); ?>
	
    <div class="content_wrapper clear">
		<section role="main" class="main">
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
			<?php 
				$args = array(
					'instance' => 'tml_login',
					'default_action' => 'login',
					'login_template' => 'login-form.php'
				);
				theme_my_login( $args ); 
				$args = array(
					'instance' => 'tml_register',
					'show_title' => false,
					'default_action' => 'register',
					'login_template' => 'ms-signup-user-form.php'
				);
				theme_my_login( $args );
			?>
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