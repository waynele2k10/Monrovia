<?php 
	// Page template for the a Single Garden 
	
get_header(); ?>

         <?php
           //Get the category
					$terms= get_the_terms($post->ID, 'garden_style');
					if ( $terms && ! is_wp_error( $terms ) ){ 
						foreach ( $terms as $term ) {
							$taxSlug = $term->slug;
						}
					}
			//Get Parent Garden Style Link
			$parent = getGardenParent($taxSlug);
			?>
	
    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID,'','', $taxSlug); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
            <a href="/design-inspiration/" class="showMobile" id="backHome" title="Design Inspiration">Back to Inspiration Home</a>
            <?php if (have_posts()): while (have_posts()) : the_post(); ?>
             <div class="design-style-tabs">
            	<ul class="tab clear">
                	<li><a href="<?php echo $parent['link'];?>#gardens" class="garden">Gardens</a></li>
                    <li><a href="<?php echo $parent['link'];?>#look" class="look">Get the look</a></li>
                    <li><a href="<?php echo $parent['link'];?>#plants" class="plants">Plants</a></li>
            	</ul>
                
                <?php //Set up previous and next link values
					$links = getGardenLinks($taxSlug, $post->ID);
				?>
                <nav class="prev-next clear">
                	<a class="left" href="<?php echo $links['prev']; ?>">< Previous Garden</a>
                    <a class="right" href="<?php echo $links['next']; ?>">Next Garden ></a>
                </nav>
				<!-- article -->
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
					<?php the_content(); ?>
				</article>
				<!-- /article -->
                <?php //Get plant Item Numberss 
						$plants = get_group('plants_in_garden'); 
                        if(count($plants)>0){ ?>
						<div class="plants-grid video-related clear">
						<h2>Plants Seen in this garden</h2>
                        	<div class="row clear">
						<?php 
							foreach($plants[1]['plants_in_garden_plant_item_number'] as $key => $plant){
							$data = getPlantData('', $plant); 
                            $favorited = getUserWishlist($data['pid']); //Returns True if in Favorites
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
                                    <a	class="favorite-icon <?php echo ($favorited)?'added':'';?>"
                                        title="<?php echo ($favorited)?'It\'s a Favorite!':'Add this plant to your favorite list and receive availability notifications.';?>"
                                        data-pid="<?php echo $data['pid'];?>" 
                                        data-item="<?php echo $data['item'];?>" 
                                        data-user="<?php echo(is_user_logged_in())?'true':'false'; ?>"
                                        data-action="<?php echo ($favorited)?'remove':'add'?>"
                                    >
                                   </a>
                                   <span class="favorite-text"><?php echo ($favorited)?'Remove from Favorites':'Add to Favorites'?></span>
                                 </div><!-- end image wrap -->
                                <a href="<?php echo site_url().'/plant-catalog/plants/'.$data['pid'].'/'.$data['seo']; ?>" title="<?php echo $data['title']; ?>"><?php echo $data['title']; ?></a><br />
                                <span><?php echo $data['botanical']; ?></span><br />
                                <span>Item #<?php echo $data['item']; ?></span>
                           </div><!-- end list plant -->
                           <?php if(($key % 4 == 0) && $key != count($plants[1]['plants_in_video_plant_id'])) echo "</div><div class='row clear'>"; ?> 
                           <?php if($key == count($plants[1]['plants_in_video_plant_id'])) echo "</div>"; ?>

                   		<?php } // End foreach ?>
                        </div><!-- end plants grid -->
                <?php } ?>
                
				<?php endwhile; endif; ?>
            	<nav>
            		<a class="left" href="<?php echo $parent['link'];?>#gardens">< <?php echo $term->name;?> Gardens</a>
            	</nav>
            </div><!-- end design style tabs -->
		</section>
        <?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>
