<?php 
get_header(); ?>

    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<!-- article -->
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
            	<?php addVideoView($post->ID); //Adds Video view to wp_postmeta ?>
            	<div class="youtubeWrap">
                <iframe id="<?php the_title(); ?>" src="//www.youtube.com/embed/<?php echo get('youtube_id'); ?>?rel=0&enablejsapi=1" height="385" width="640" allowfullscreen="" frameborder="0"></iframe><br /><br /></div>
				<?php the_content(); ?>
			</article>
            	<?php //Get plant Item Numberss 
						$plants = get_group('plants_in_video'); 
                        if(count($plants)>0){ ?>
						<div class="plants-grid video-related clear">
						<h2>Plants Featured in this Video</h2>
                        	<div class="row clear">
						<?php 
							foreach($plants[1]['plants_in_video_plant_id'] as $key => $plant){
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
											<a href="<?php echo $forsale; ?>" title="Buy Now" class='for-sale clear'><i class="fa fa-shopping-cart"></i><span>Buy Now</span></a>
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
                           <?php if(($key % 4 == 0) && $key != count($plants[1]['plants_in_video_plant_id'])) echo "</div><div class='row clear'>"; ?> 
                           <?php if($key == count($plants[1]['plants_in_video_plant_id'])) echo "</div>"; ?>

                   		<?php } // End foreach ?>
                        </div><!-- end plants grid -->
                <?php } ?>
			<!-- /article -->
			<?php endwhile; endif; ?>
		</section>
        <?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>