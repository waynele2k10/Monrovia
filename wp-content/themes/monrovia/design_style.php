<?php 
/*
	Template Name: Design Style
	Page template for the Design style
	Contains Tabs at the top for navigation
*/
	
get_header(); ?>
<script>
	jQuery(function($) {
		//	$( ".design-style-tabs" ).tabs();
			
	});
	jQuery(document).ready(function($){
			
		//Enable <a> links to control the tabs
			$('.tab-controller').on( 'click', function(){
				var index = $(this).attr('data-index');
				$( ".design-style-tabs" ).tabs( "option", "active", index );
		});
			
	});
</script>
	
    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
            <a href="/design-inspiration/" class="showMobile" id="backHome" title="Design Inspiration">Back to Inspiration Home</a>
            <div class="design-style-tabs">
            	<ul class="tab clear" style="display:none">
                	<li><a href="#gardens" class="garden">Gardens</a></li>
                    <li><a href="#look" class="look">Get the look</a></li>
                    <li><a href="#plants" class="plants">Plants</a></li>
            	</ul>
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
            	<div id="gardens" style="display:none;">
                	<div class="intro"><?php echo get('introduction'); ?></div>
                    <?php
					
					//Get the category
					$terms= get_the_terms($post->ID, 'garden_style');
					if ( $terms && ! is_wp_error( $terms ) ){ 
						foreach ( $terms as $term ) {
							$taxSlug = $term->slug;
						}
					}
					
					//Query all the Gardens of this Particular Style
						$args = array('post_per_page' => -1, 'post_type' => 'garden', 'garden_style' => "$taxSlug", 'orderby' => 'menu_order', 'order' => 'ASC' );
						$query = get_posts( $args );
						$i=1;
						foreach ( $query as $post ) {
  							setup_postdata( $post );
							echo "<div class='children left";
							if(($i) % 2 == 0) echo" last";
							echo "'>";
							echo "<a href='".get_permalink($post->ID)."' class='block'>".get_the_post_thumbnail($post->ID, 'full')."</a>";
							echo "<div class='excerpt'>";
							the_excerpt();
							echo "</div>";
							echo "<a href='".get_permalink($post->ID)."' class='block'>Expore this Garden</a></div>";
							if(($i) % 2 == 0) echo "<br style='clear:both' />";
							$i++;
						} // End foreach
						wp_reset_postdata();
					?>
                    <nav class="clear clearfix">
                    	<a class="left tab-controller" href="#plants" data-index="2">< <?php echo $term->name; ?> Plants</a>
                        <a class="right tab-controller" href="#look" data-index="1">Get the look ></a>
                    </nav>
                </div><!-- end gardens -->
            	<div id="look">
					<!-- article -->
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
						<?php the_content(); ?>
					</article>
					<!-- /article -->
               		<nav class="clear" style="display:none;">
                    	<a class="left tab-controller" href="#gardens" data-index="0">< <?php echo $term->name; ?> Gardens</a>
                    	<a class="right tab-controller" href="#plants" data-index="2"><?php echo $term->name; ?> Plants ></a>
                    </nav>
               </div><!-- end look -->
               <div id="plants" style="display:none">
               	<h2>Plants for the <?php echo $term->name; ?> garden</h2>
               <?php
			   			//Get all the Plansts for this Garden Style
						$plants = getDesignPlants($taxSlug, false);
                        if(count($plants)>0){ ?>
						<div class="plants-grid clear">
                        	<div class="row clear">
						<?php 
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
                           <?php if((($key+1) % 4 == 0) && ($key+1) != count($plants)) echo "</div><div class='row clear'>"; ?> 
                           <?php if(($key+1) == count($plants)) echo "</div>"; ?>

                   		<?php } // End foreach ?>
                        </div><!-- end plants grid -->
                <?php } ?> 
                    <nav class="clear">
                    	<a class="left tab-controller" href="#look" data-index="1">< Get the look</a>
                    	<a class="right tab-controller" href="#gardens" data-index="0"><?php echo $term->name; ?> Gardens ></a>
                    </nav>
               </div><!-- end plants -->
            </div><!-- end tabs -->
			<?php endwhile; endif; ?>
		</section>
        <?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>
