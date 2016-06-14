<?php 
/* 
	Template Name: Design Profile Create
	Create
*/

// Uncomment these to Turn on Errors for this page
	//ini_set('display_errors','on');
	//error_reporting(E_ALL);

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
            <?php if(is_user_logged_in()){ ?>
            <div class="design-profile">
            	<?php include('includes/design-profile-create-form.php'); // Include the Design Profile Form ?>
            </div><!-- end design profile -->
            <?php } else { ?>
            <p>You must be logged in to create a profile.  Please login <a href="<?php echo get_permalink(406);?>?profile=landscape" title="Login">here</a>.</p>
            <?php } ?>
			<!-- /article -->
			<?php endwhile; endif; ?>
		</section>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>