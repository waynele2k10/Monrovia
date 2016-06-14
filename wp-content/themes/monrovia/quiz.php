<?php /* Template Name: Style Quiz */

/*  If Form Was Submitted, Calculate results and redirect */
if(isset($_POST['submitted'])&&$_POST['submitted']=='1'){

		$gardens = array();
		$gardens['cottage'] = 0;
		$gardens['zen'] = 0;
		$gardens['classic'] = 0;
		$gardens['contemporary'] = 0;
		$gardens['tropical'] = 0;
		$gardens['eco-friendly'] = 0;

		for($i=1;$i<11;$i++){
			$question_response = $_POST['question'.$i];
			if($question_response!='') $gardens[$_POST['question'.$i]]++;
		}

		arsort($gardens,SORT_NUMERIC);
		$response_rankings = array_keys($gardens);

		$user_garden = $response_rankings[0];

		// IF NOTHING CHOSEN, DEFAULT TO ECO-FRIENDLY
		if($gardens[$response_rankings[0]]==0) $user_garden = 'eco-friendly';

		if($user_garden=='eco-friendly'){
			$style_url = '/design-inspiration/eco/';
		}else{
			$style_url = '/design-inspiration/' . $user_garden . '/';
		}

		// REDIRECT TO APPROPRIATE GARDEN STYLE PAGE
		header('location:'.$style_url);
		exit;
	}

get_header(); ?>
	
       <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_post_thumbnail('full'); ?></h1>
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<!-- article -->
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php the_content(); ?>
			</article>
			<!-- /article -->
			<?php endwhile; endif; ?>
		</section>
		<?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>