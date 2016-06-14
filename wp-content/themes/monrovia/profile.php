<?php 
/* 
	Template Name: Profiles
	Viewing an Individual Design Profile
*/

require_once('includes/designers.php');

get_header(); ?>
	
    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
             <div class="design-profile">
            	<!--<a href="<?php //echo add_query_arg( 'profile', 'widner--associates-tony-w-landscape-architect-25298' ); ?>">Click</a> -->
                <?php include('includes/profile-template.php'); ?>
            </div><!-- end design profile -->
		</section>
		<?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>