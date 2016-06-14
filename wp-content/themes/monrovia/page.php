<?php get_header(); 

	//ini_set('display_errors','on');	
	//error_reporting(E_ALL);
?>

    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
			<?php 
				//** The Selected Plants module was upgraded to allow adding multiple modules on the same page. Initialize the counter here
				$iSelectedPlantModuleCounter = 1;
				if (have_posts()): while (have_posts()) : the_post(); ?>
            <?php 
				$selectedPlants = get_group('selected_plants');
				$label = $selectedPlants['selected_plants_label'][1];
				$plants = explode(',', $selectedPlants[1]['selected_plants_plant_item_numbers'][1]);
	
			?>
			<!-- article -->
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php the_content(); ?>
			</article>
			<!-- /article -->
            <!-- Add Option to display a Plant grid below content -->
            <?php #print_r($plants); ?>
            <?php if(is_array($plants) && !empty($plants) && $plants[0] != ''){ ?>
            <div class="plants-grid collection-plants clear">
                    
                    <?php $myEvent = get_group('selected_plants'); // use the Custom Group name
						foreach($myEvent as $selectedPlant)
						{ ?>
				<div class="clear">
						<?php echo $selectedPlant['selected_plants_description'][1]; ?>
    				 </div>
      			<div class="clear">
       			<h2><?php echo $selectedPlant['selected_plants_label'][1]; ?></h2>
   					</div>
                    
                    <div class="row clear">
                    <?php 
					//** Since we are now inside the foreach $myEvent loop, we need to reinitialize $plants object and increment the counter
					$plants = explode(',', $selectedPlants[$iSelectedPlantModuleCounter]['selected_plants_plant_item_numbers'][1]);
					$iSelectedPlantModuleCounter = $iSelectedPlantModuleCounter + 1;
                        foreach($plants as $key => $plant){
                        $data = getPlantData('', $plant); 
                        $favorited = getUserWishlist($data['pid']); //Returns True if in Favorites
                        $forsale = isForSale($data['item']);
                        ?>
                        <div class="list-plant left" data-key="<?php echo $key; ?>">
                            <div class="image-wrap">
                            <?php if($data['new']=='1'){ ?>
                                        <div class="flag-new">New Plant</div>
                            <?php } ?>
                                <div class='ajax-loader'></div>
                                <a href="<?php echo site_url().'/plant-catalog/plants/'.$data['pid'].'/'.$data['seo']; ?>">
                                    <img src="<?php echo site_url().'/wp-content/uploads/plants/search_results/'.$data['image-id'].'.jpg'?>"  />
                                </a>
                                <?php if($forsale){ ?>
                                        <a href="<?php echo $forsale; ?>" title="Buy Now" class='for-sale clear' target="_blank"><i class="fa fa-shopping-cart"></i><span>Buy Now</span></a>
                                    <?php } else { //Show favorite actions ?>
                                <a	class="favorite-icon <?php echo ($favorited)?'added':'';?>"
                                    title="<?php echo ($favorited)?'It\'s a Favorite!':'Add this plant to your favorite list and receive availability notifications.';?>"
                                    data-pid="<?php echo $data['pid'];?>" 
                                    data-item="<?php echo $data['item'];?>" 
                                    data-user="<?php echo(is_user_logged_in())?'true':'false'; ?>"
                                    data-action="<?php echo ($favorited)?'remove':'add'?>"
                                >
                               </a>
                               <span class="favorite-text"><?php echo ($favorited)?'Remove from Favorites':'Add to Favorites'?></span>
                               <?php } // end else ?>
                             </div><!-- end image wrap -->
                            <a href="<?php echo site_url().'/plant-catalog/plants/'.$data['pid'].'/'.$data['seo']; ?>" title="<?php echo $data['title']; ?>"><?php echo $data['title']; ?></a><br />
                            <span><?php echo $data['botanical']; ?></span><br />
                            <span>Item #<?php echo $data['item']; ?></span>
                       </div><!-- end list plant -->
                       <?php if((($key+1) % 4 == 0) && ($key+1) != count($plants)) echo "</div><div class='row clear'>"; ?> 
                       <?php if(($key+1) == count($plants)) echo "</div>"; ?>

                    <?php } // End foreach ?>
                     <?php } ?>  <!-- end Custom Group name -->
               
               <div class="clear">
       			<p></p>
   				</div> </div><!-- end plants grid -->
				
			<?php } ?>
            <!-- end plant grid -->
			<?php endwhile; endif; ?>
		</section>
		<?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>