<?php 
	// Page template for the a Plant Collection
	 // Uncomment these to Turn on Errors for this page
	//ini_set('display_errors','on');
	//error_reporting(E_ALL);

	$insertNewsletter = 8;	
	require_once('includes/classes/class_search_plant.php');

get_header(); ?>
<script>
jQuery(document).ready( function(){
		
		// FAQ Show/Hide
		jQuery('a.questions').on('click', function(){
			jQuery(this).toggleClass('expanded');
			jQuery(this).siblings('.answer').slideToggle();
		});
		
		// Update the Plant Grid on Zipcode Change
		jQuery('#query-update').on('click', function(){
			// Show loading Screen
			jQuery('#query-grid').addClass('updating');
			var zipCode  = jQuery('#zipcode-two').val();
			var el = jQuery('#query-grid');
			var query = el.data('query');
			var column = el.data('column');
			var number = el.data('number');
			
			loadPlantCollection(false, zipCode, query, column, number, function(){
				jQuery('#query-grid').removeClass('updating');
			});
			
		});
    });

	
</script>


	
    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID,'','',''); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
            <?php if (have_posts()): while (have_posts()) : the_post(); ?>
                <?php
					// Get and define custom field groups
					$intro = get_group('introduction');
					$copyBlock =  get_group('copy_block');
					$images =  get_group('image_slide');
					$moreLinks =  get_group('more_info_links');
					$metaInfo =  get_group('meta_information');
					$selectedPlants = get_group('selected_plants');
					$tabbedPlants = get_group('tabbed_plant_groups');
					$plants = explode(',', $selectedPlants[1]['selected_plants_plant_item_number'][1]);
					$faqs = get_group('faq');
					$entireCollection = $metaInfo[1]['meta_information_entire_collection_url'][1];
					$collectionName = $metaInfo[1]['meta_information_collection_name'];
					$viewAll = $metaInfo[1]['meta_information_view_collection_text'][1];
					$signUp = $metaInfo[1]['meta_information_newsletter_signup_copy'][1];
					$grid = $metaInfo[1]['meta_information_plant_grid_options'][1];
					if($grid != 'Edibles'){
						$variable = $metaInfo[1]['meta_information_query_variable'][1];
						$column = $metaInfo[1]['meta_information_query_column'][1];
						//$number = $metaInfo[1]['meta_information_query_number'][1];meta_information_plant_grid_options
						$number = 8;
					}

				?>
				<!-- article -->
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
                	<div class="top-content clear">
                    	<div class="slideshow-wrapper right">
							<?php
                            // If theres at least one image, then print out the slider		
                            if(!empty($images)){ ?>
                            <div id="cycle-1" class="cycle-slideshow"
                                        data-cycle-slides="> .slide"
                                        data-cycle-fx="scrollHorz"
                                        data-cycle-pause-on-hover="true"
                                        data-cycle-timeout=0
                                        data-cycle-speed="500"
                                        data-cycle-swipe=true
                                        data-cycle-pager=".cycle-pager"
                                        data-cycle-pager-template=""
                                        data-cycle-auto-height="false"
                                    >
                                    <!-- prev/next links -->
                                    <div class="cycle-prev"></div>
                                    <div class="cycle-next"></div>
                                    <?php 
										foreach($images as $img){ ?>
											<div class="slide">
                                            	<img src="<?php echo $img['image_slide_image'][1]['original'];?>" alt="" width="100%" />
                                                <div class="slide-meta">
													<?php echo stripTag($img['image_slide_slide_text'][1]);?>
                                                </div>
                                            </div>
									<?php } ?>
                            </div><!-- end cycle-slideshow -->
                            <div id="cycle-2" class="cycle-slideshow"
                                    data-cycle-slides="> img"
                                    data-cycle-timeout=0
                                    data-cycle-fx="carousel"
                                    data-cycle-carousel-visible=4
                                    data-allow-wrap=false
                                    data-cycle-carousel-slide-dimension='92px'
                                >
                                <?php 
									foreach($images as $img){ ?>
										<img src="<?php echo $img['image_slide_image'][1]['thumb'];?>" alt="" />
								<?php } ?>
                            </div><!-- end cycle-2 -->
                         <?php // Else print nothing, for now
                    } else { echo "<img src='".get_template_directory_uri()."/img/no-image-large.png' width='355' height='274'/>"; } ?>
    	 				</div><!-- end slideshow-wrapper -->
                    	<div class="left">
                            <div id="copy-block-1" class="copy-block">
                                <?php echo $intro[1]['introduction_copy'][1]; ?>
                            </div><!-- end copy block -->
                            <div class="more-links">
                            <?php foreach($moreLinks as $link){ ?>
                            	<div>
                                    <strong><?php echo $link['more_info_links_link_label'][1]; ?>: </strong>
                                    <a href="<?php echo $link['more_info_links_url'][1]; ?>" title="<?php echo $link['more_info_links_link_text'][1]; ?>"><?php echo $link['more_info_links_link_text'][1]; ?></a>
                                </div>
                            <?php } ?>
                            </div><!-- end more links -->
                            <?php if($faqs){//If FAQs are present, print them out ?>
                            <div class="faq-wrap">
                            	<h2>Questions About <?php the_title();?></h2>
                                <?php foreach($faqs as $faq){ ?>
									<div class="faq">
                                    	<a href="javascript:void(0);" class="questions"><?php echo $faq['faq_question'][1]; ?></a>
                                        <div class="answer clear">
                                        	<?php echo stripTag($faq['faq_answer'][1]); ?>
                                            <?php if($faq['faq_read_more_link'][1]) echo '<a href="'.$faq['faq_read_more_link'][1].'">Read More</a>'; ?>
                                        </div><!-- end answer -->
                                    </div><!-- end faq -->
								<?php } ?>
                            </div><!-- end faq wrap -->
                            <?php } // End if ?>
                            <?php if($copyBlock[1]['copy_block_title'][1]){ //If there is a copy block, print it! ?>
                            <div id="copy-block-1" class="copy-block">
                            	<h2><?php echo $copyBlock[1]['copy_block_title'][1]; ?></h2>
                                <?php echo $copyBlock[1]['copy_block_content'][1]; ?>
                            </div><!-- end copy block -->
                            <?php } // End if ?>
                        </div>
                    </div><!-- end top content -->		
				</article><!-- /article -->
                <?php if($grid == 'Edibles'){ //Show Custom Edible Plant Grid 
				 // Set up the Plant Categories and Plants
                // $categories = ',Apples,Berries,Blueberries,Citrus,Exotic,Figs,Grapes,Herbs,Fruit Trees,';
				//Loop through each of the Tabbed Plant Groups
				//print_r($tabbedPlants); 
				$plant_item_numbers = array();
				foreach($tabbedPlants as $key => $tabbedPlant){
					//Convert Label to ID friendly
					$labelID = 'edibles-'.strtolower(str_replace(' ', '', $tabbedPlant['tabbed_plant_groups_tabbed_label'][1]));				 
					$plant_item_numbers[$labelID] = array($tabbedPlant['tabbed_plant_groups_tabbed_label'][1], $tabbedPlant['tabbed_plant_groups_plants'][1]);
				}
				?>

                <div class="plants-grid collection-plants clear edibles-grid">
                    <div class="clear">
                    	<h2 class="left"><?php echo $selectedPlants[1]['selected_plants_label'][1]; ?></h2>
                    	<a href="<?php echo $entireCollection;?>" class="see-all right hideMobile" title="<?php echo $viewAll; ?>"><?php echo $viewAll; ?></a>
                    </div>   
                <div class="accordian-wrap">
						<ul class="accordian clear">
					<?php // End Tab Markup
					$count = 0;
					foreach($plant_item_numbers as $key => $plantCat){
						if(strpos($key, 'edibles') !== false){
							$style='none'; 
							$class='';
							//if($count == 0){ $style = "block"; $class='open'; }
							echo "<li><a href='#".$key."' class='".$class."'>".$plant_item_numbers[$key][0]."</a>";
							$plant_item_numbers_array = explode(',',$plant_item_numbers[$key][1]);
							echo "<div id='".$key."' class='accordianTarget' style='display:$style;'>";
							if(count($plant_item_numbers_array)>0){
								$search_plants = new search_plant('','id,item_number,common_name,botanical_name,is_new',false);
								$search_plants->order_by = 'common_name ASC';
								$search_plants->results_per_page = 25;
								$search_plants->criteria['item_number'] = $plant_item_numbers_array;
								$search_plants->search(false);
								$search_plants->output_results_plant_search();
							}
							echo "</div></li>";
						}
						$count++;
					}
					echo "</ul></div><!-- end tab --></div><!-- end plant-collection -->";
					
				 } else { ?>
                <div class="plants-grid collection-plants clear">
                    <div class="clear">
                    	<h2 class="left"><?php echo $selectedPlants[1]['selected_plants_label'][1]; ?></h2>
                    	<a href="<?php echo $entireCollection;?>" class="see-all right hideMobile" title="<?php echo $viewAll; ?>"><?php echo $viewAll; ?></a>
                    </div>
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
                </div><!-- end plants grid -->

                <div id="query-grid" class="collection-plants clearfix" data-query="<?php echo $variable;?>" data-column="<?php echo $column;?>" data-number="<?php echo $number;?>">  
                	<span class="fa fa-spinner fa-spin" id="query-ajax"></span>
                	<div class="clear">
                        <h2 class="left"><?php the_title(); ?> that will grow in my garden</h2>
                        <div class="right links hideMobile">
                            <a href="http://shop.monrovia.com/catalogsearch/result/?q=<?php echo $variable;?>" target="_blank">Shop for <?php the_title();?></a>
                            <a href="/plant-catalog/search/?query=<?php echo $variable;?>&sort_by=is_new">See New <?php the_title();?></a>
                            <a href="<?php echo $entireCollection;?>">See all <?php the_title();?></a>
                        </div><!-- end links -->
                    </div><!-- end clear -->
                    <div class="cold-zone-box clear">
                        <span id="location" class="left">
                        <script>
                            var city = getCookie('city');
                            var state = getCookie('state');
                            var zip = getCookie('zip_code');
                            
                            document.write(city+', '+state+' '+zip);
                        </script>               
                        </span>
                        <div class="form-item left hasLabel">
                            <label for="zipcode-two">Update Zip Code</label>
                            <input type="text" id="zipcode-two" maxlength="5" name="zipcode-two" class="zip-input hideLabel">
                            <button role="button" type="submit" class="" id="query-update">Go</button>
                            <div class="ajax-loader"></div>
                       </div>
                    </div><!-- end cold zone box -->
                    <div id="query-results" class="clear">
                    <script type="text/javascript"> 

							//Check to see if cold zone cookie is present
							if(getCookie('cold_zone') != '' && getCookie('cold_zone') != undefined){

								var coldzone = getCookie('cold_zone');
								var zip = getCookie('zip_code');

								//Load the Plants
								loadPlantCollection(coldzone, zip, '<?php echo $variable; ?>', '<?php echo $column; ?>', '<?php echo $number; ?>', function(){});
							} else {
								// Use the Maxmind API to get the zipcode
								var onSuccess = function(location){
					
								   var string = JSON.stringify(location, undefined, 4);
								   var data = JSON.parse(string);
								   var postalCode = data.postal.code;
								   setCookie('zip_code', postalCode, 365 );
								   
								   loadPlantCollection(false, postalCode, '<?php echo $variable; ?>', '<?php echo $column; ?>', '<?php echo $number; ?>', function(){});
								   
								}
							
								// IP look up unsucessful
								var onError = function(error){
									// The error
									console.log(JSON.stringify(error, undefined, 4));
								}
							
								// Call the GeoIP2 API
								geoip2.city(onSuccess, onError);
							}
                       </script>
                    </div><!-- ennd query results -->
                </div><!-- end collection plants -->
                <?php } // End else ?>
                <div class="newsletter-signup clear">
                    <h3>Connect with Us</h3>
                    <img src="<?php echo get_template_directory_uri()?>/img/signup-image.jpg" />
                    <?php echo $signUp; ?>
                    <p id="signupMessage" class="newsletter-msg message"></p>
                    <div class="clear plant-savvy-signup left">
                       <div class="form-item left hasLabel">
                            <label for="nemail" class="block-label">Enter your email address</label>
                            <input type="text" name="nemail" id="nemail" class="checkKeypress checkBlur">	            
                       </div><!-- end clear -->
                       <button class="search-submit left" role="button" type="submit" name="Submit" value="Submit" id="newsletter-signup">Sign Up</button>
                       <span class="newsletter ajax-loader"></span>
                   </div><!-- end clear -->
                   <div class="left savvy-social right clear">
                        <a target="_blank" href="http://www.facebook.com/pages/Monrovia/102411039815423?v=wall&amp;ref=sgm" title="Facebook"></a>
                        <a target="_blank" href="http://twitter.com/MonroviaPlants/" title="Twitter"></a>
                        <a target="_blank" href="https://www.pinterest.com/monroviaplants/" title="Pinterest"></a>
                        <a target="_blank" href="http://instagram.com/MonroviaNursery#" title="Instagram"></a>
                        <a target="_blank" href="https://plus.google.com/106439322773521086880/" title="Google+"></a>
                   </div><!-- end left -->
                </div><!-- end newsletter signup -->
						
                
				<?php endwhile; endif; ?>
		</section>
        <?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
<?php get_footer(); ?>
