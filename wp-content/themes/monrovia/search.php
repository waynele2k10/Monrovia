<?php 
// Uncomment these to Turn on Errors for this page
	//ini_set('display_errors','on');
	//error_reporting(E_ALL);
   ?>
<?php get_header(); ?>
	
    <!-- Plant Database Results -->
   <?php
    require_once('includes/classes/class_search_plant.php');
	
	// USERS SHOULD NOT BE ALLOWED TO GO STRAIGHT TO THIS PAGE
	$query = $_GET['s'];

	$query = trim(parse_alphanumeric(strtolower(strip_tags(stripslashes($query))),'\'\-\+ '));
	$query = str_replace('+',' ',$query);
	
	if($query==''||is_suspicious(ids_sanitize($query))){
		header('location:/');
		exit;
	}
	// PLANTS
	if($query=='plant select'){
		$search_plants = new search_plant('','id,item_number,common_name,botanical_name,is_new,primary_attribute,release_status_id',false);	
		$search_plants->criteria['is_plant_select'] = '1';
		$view_all_plants_link = '/plant-catalog/search/?is_plant_select=1';
	}//else if(strpos($query,'brazelberr')!==false){
//		$search_plants = new search_plant('','id,item_number,common_name,botanical_name,is_new,primary_attribute,release_status_id',false);	
//		$search_plants->criteria['item_number'] = array('8170','8171','7938');
//		$view_all_plants_link = '/plant-catalog/search/?item_number=' . $search_plants->criteria['item_number'];
//	}
        else{
		$search_plants = new search_plant($query,'id,item_number,common_name,botanical_name,is_new,primary_attribute,release_status_id',false);	
		$view_all_plants_link = '/plant-catalog/search/?query=' . $query;
	}
	$search_plants->results_per_page = 8;
	$search_plants->search(true);
	if($search_plants->results_total>0) { ?>

	<h2 class="subheader">Plants matching <?php echo $query?></h2>
    	<?php if($search_plants->results_total>$search_plants->results_per_page){ ?>
				<div class="search-meta top clear">
        			<div class="paging left">
            			<a href="<?php echo $view_all_plants_link?>">View all <?php echo $search_plants->results_total?> matching plants &raquo;</a>
                    </div><!-- end paging -->
                </div><!-- end search meta -->
		<?php } ?>
		<!-- Display Search Results -->
      	<div class="plants-grid search-results clear">
		<?php $search_plants->output_results_plant_search(); ?>
        </div><!-- end plants grid -->

		<?php if($search_plants->results_total>$search_plants->results_per_page){ ?>
				<div class="search-meta top clear">
        			<div class="paging left">
            			<a href="<?php echo $view_all_plants_link?>">View all <?php echo $search_plants->results_total?> matching plants &raquo;</a>
                    </div><!-- end paging -->
                </div><!-- end search meta -->
		<?php } ?>
    <?php } // End if ?>
    <!-- End Plant Database results -->
    
	<!-- Webdsite Content Results with paging -->
	<section role="main" class="global-search">
		<h2><?php echo sprintf( __( '%s Pages containing ', 'html5blank' ), $wp_query->found_posts ); echo get_search_query(); ?></h2>
        <?php //if (function_exists('relevanssi_didyoumean')) { relevanssi_didyoumean(get_search_query(), "<p>Did you mean: ", "</p>", 5);}?>
		<?php get_template_part('loop'); ?>
        <div class="search-meta bottom clear">
        	<div class="paging right">
				<?php get_template_part('pagination'); ?>
	  		</div><!-- end paging -->
        </div><!-- end search meta -->
	</section>
	<!-- / End Website Content Results-->

<?php get_footer(); ?>
