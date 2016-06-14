<?php 
/*
	Template Name: Legal Plants
	Use this template to display Patented
	or Trademarked plants
	
*/

get_header();

	require_once('includes/classes/class_search_plant.php');
	
	$search = new search_plant('','id,item_number,common_name,botanical_name,patent,primary_attribute',false);
	if($post->ID == 1326){ $search->criteria['is_monrovia_trademarked'] = '1';}
	else { $search->criteria['is_monrovia_patented'] = '1'; }
	$search->results_per_page = 10;
	
	$start_page = 1;
	if(isset($_GET['start_page'])) $start_page = max(intval($_GET['start_page']),1);
	$search->results_start_page = $start_page;

	if(isset($_GET['view_all'])&&$_GET['view_all']=='1') $search->view_all = 1;
	
	$_SESSION['search'] = $search;
	$search->search(true);
	
	// Showing Results Text
	function getResultsText($search){
	$record_first = (($search->results_start_page-1)*$search->results_per_page)+1;
	$record_last = min($record_first + $search->results_per_page - 1,$search->results_total);
	if($search->results_total>0){
		if($record_first==$record_last){
			$text = (" $record_first of ".$search->results_total);
		}else if($record_first==1&&$record_last==$search->results_total){
			$text = ("Showing all ".$search->results_total. " results");
		}else{
			$text =(" $record_first - $record_last of ".$search->results_total);
		}
			return $text;
		}
	}
?>
	
    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<!-- article -->
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php the_content(); ?>
			</article>
			<!-- /article -->
			<?php endwhile; endif; ?>
        </section>
        <div class="search-meta clear">
        	<div class="showing left">
               <?php echo getResultsText($search); ?>
        	</div><!-- end showing -->
            <div class="paging right">
				<?php echo $search->pagination_html;?>
	  		</div><!-- end paging -->
        </div><!-- end search meta -->
		<!-- Display Search Results -->
      	<div class="plants-grid search-results clear">
			<?php if($post->ID == 1326){ $search->output_results_plant_search('trademarked_plants'); }
            	else{$search->output_results_plant_search('patented_plants');} ?>
      	</div><!-- end plants grid -->
        <div class="search-meta clear">
        	<div class="showing left">
               <?php echo getResultsText($search); ?>
        	</div><!-- end showing -->
            <div class="paging right">
				<?php echo $search->pagination_html;?>
	  		</div><!-- end paging -->
        </div><!-- end search meta -->
	</div><!-- end content_wrapper -->
<?php get_footer(); ?>