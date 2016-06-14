<?php get_header();
// Reusing code from Old Monrovia site for Collection Driven Pages
// Should redo this to allow crontol over Plant Id numbers from the 
// Admin Area in the future


	
?>
	
    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<!-- article -->
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php the_content();
				$category = get_the_terms($post->ID, 'collection_category');
				foreach($category as $term){
					$slug = $term->slug;
				}

				?>
			</article>
			<!-- /article -->
			<?php endwhile; endif; ?>
            <div class="collection-plants clearfix">
            <?php
			$transient_name = 'single_collection_' . $post->ID;
			$single_collection = monrovia_get_cache( $transient_name );
			if ( false === $single_collection ) :			
				ob_start();
				require_once('includes/classes/class_search_plant.php');
				
				$categories = ',Apples,Berries,Blueberries,Citrus,Exotic,Figs,Grapes,Herbs,Fruit Trees,';

				$plant_item_numbers = array(
					'edibles-apples'=>array('Apples','5896,5898,6109,5895,6105,6104,40267,40266,6103,40742,40275,40272,40274'),
					'edibles-berries'=>array('Berries','6991,7004,7006,7008,7033,7034,5814,6834,6835,6836,7310,6825,6784,6787,7152,8158,8156,8157,7415,7938'),
					'edibles-blueberries'=>array('Blueberries','2173,2174,2175,2176,3637,3769,3797,4875,7374,8036,3795,3448,7340,2152,5623,8170,8171'),
					'edibles-citrus'=>array('Citrus','2462,2468,2473,2474,2475,2479,2481,2482,2495,2505,2512,2560,2574,2578,2587,2600,2605,2610,2626,2660,2665,2675,2682,4307,2625'),
					'edibles-exotic'=>array('Exotic','139,141,146,2072,2074,2281,3082,3100,3105,3112,3570,5017,6193,3608'),
					'edibles-figs'=>array('Figs','3672,3674,3678,3679,3681,6648,7760'),
					'edibles-grapes'=>array('Grapes','7621,7622,7625,7626,7630,7631,7632,7633,6751,6752,6753,6754,6755'),
					'edibles-herbs'=>array('Herbs','2049,2130,7595,4136,5729,7028,7032,6402,6424,6269,7579,6777'),
					'edibles-fruittrees'=>array('Fruit Trees','6745,6733,6149,6801,6167,6168,6144,6143,6743,5996,6741,6795,6116,6128,6146,6152,6163,3682,6141,6798,6142,6735,6799'),
					'succulents'=>null,
					'shrubs-topiaries'=>array('','1383,1390,1382,6715,3770,1395,7028,5820,3520,6222,1122,3540,6304,6700,5923,3780,1370,3480,1220,4493,1440,9275,4675,7875,7061,4530,1389,2232,2834,6223,1398,1388,1378,605,3512,7656,2753,7461,2375,3523,1203,7117,7510,3820,2109,2391,7876,2520,3073,2440,7877,5,4845,4730,5729,8650,6645,9854,9855,2263,2264,7202'),
					'hinkley'=>null,
					'itoh'=>null,
					'camellias'=>array('','1460,1468,1470,1484,1498,1500,1520,1555,1610,1638,1649,1671,1693,1704,1715,1720,1725,1747,1790,1793,1812,1823,1845,1867,1880,1884,1889,1910,1970,1988,2004,2007,2102,2105,2111,2166,2170,2177,2199,2210,2221,2232,2233,1492,8140,1493,7518,7520,7522,1525'),
					'sunparasol'=>array('','4501,5706,5708,6123,7053,5707,5709,7265,7264,9238')
				);

				if(array_key_exists($slug,$plant_item_numbers)){
					$plant_item_numbers_array = explode(',',$plant_item_numbers[$slug][1]);
					$category = $plant_item_numbers[$slug][0];

					if(count($plant_item_numbers_array)>0){
						$search_plants = new search_plant('','id,item_number,common_name,botanical_name,is_new',false);
						$search_plants->order_by = 'common_name ASC';
						$search_plants->results_per_page = 100;

						$collection_name_mapping = array('succulents'=>'Succulents','hinkley'=>'Dan Hinkley','itoh'=>'Itoh Peonies');
						switch($slug){ 

							case 'succulents':
							case 'itoh':
							case 'hinkley':
								$search_plants->criteria['collection_name'] = $collection_name_mapping[$slug];
								break;
							default:
								$search_plants->criteria['item_number'] = $plant_item_numbers_array;
						}
						$search_plants->search(false);
						$search_plants->output_results_plant_search();
					}


				} elseif($slug == 'edibles'){
					// Markup for the tabs
					?>
					<div class="tabs">
						<ul class="tab clear">
					<?php foreach($plant_item_numbers as $key => $plantCat){
						if(strpos($key, 'edibles') !== false){
							echo "<li><a href='#".$key."'>".$plant_item_numbers[$key][0]."</a></li>";
						}
					} ?>
					</ul><!-- end tab -->
					<?php // End Tab Markup
					foreach($plant_item_numbers as $key => $plantCat){
						if(strpos($key, 'edibles') !== false){
							$plant_item_numbers_array = explode(',',$plant_item_numbers[$key][1]);
							echo "<div id='".$key."'>";
							if(count($plant_item_numbers_array)>0){
								$search_plants = new search_plant('','id,item_number,common_name,botanical_name,is_new',false);
								$search_plants->order_by = 'common_name ASC';
								$search_plants->results_per_page = 25;
								$search_plants->criteria['item_number'] = $plant_item_numbers_array;
								$search_plants->search(false);
								$search_plants->output_results_plant_search();
							}
							echo "</div>";
						}
					}
					echo "</div><!-- end tab -->";
				}
				
				$single_collection = ob_get_clean();
				monrovia_set_cache( $transient_name, $single_collection, MONROVIA_PLANT_DATA_TRANSIENT_EXPIRE );
				
			endif;				
			echo $single_collection;	
			?>
    	</div><!-- end collection plants -->
     </section>
		<?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>

<script>
	jQuery(function($) {
		$( ".tabs" ).tabs();
	});
			
</script>