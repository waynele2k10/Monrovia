<?php 
	// Page template for the a Spokesperson post type 
	 // Uncomment these to Turn on Errors for this page
	//ini_set('display_errors','on');
	//error_reporting(E_ALL);
	global $signUp;
	$signUp = "<p>test</p>";
	$insertNewsletter = 8;	
	require_once('includes/classes/class_search_plant.php');

get_header(); ?>	
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
					$entireCollection = $metaInfo[1]['meta_information_entire_collection_url'][1];
					$collectionName = $metaInfo[1]['meta_information_collection_name'][1];
					$signUp = $metaInfo[1]['meta_information_newsletter_signup_copy'][1];
					$viewAll = $metaInfo[1]['meta_information_view_collection_text'][1];
					$column = $metaInfo[1]['meta_information_query_column'][1];
					$number = $metaInfo[1]['meta_information_query_number'][1];
				?>
				<!-- article -->
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
                	<div class="top-content clear">
                    	<div class="left">
                        	<div class="introduction clear">
                            	<img src="<?php echo $intro[1]['introduction_thumbnail'][1]['original']; ?>" />
									<?php echo $intro[1]['introduction_copy'][1]; ?>
                                    <div class="left">
                                    	<strong>MORE: </strong>
                                    	<a href="<?php echo $intro[1]['introduction_more_link'][1]; ?>"><?php echo $intro[1]['introduction_more_link_text'][1];?></a>
                                    </div>
                            </div><!-- end introduction -->
                            <div id="copy-block-1" class="copy-block">
                            	<h2><?php echo $copyBlock[1]['copy_block_title'][1]; ?></h2>
                                <?php echo $copyBlock[1]['copy_block_content'][1]; ?>
                            </div><!-- end copy block -->
                            <div class="more-links">
                            <?php foreach($moreLinks as $link){ ?>
                            	<div>
                                    <strong><?php echo $link['more_info_links_link_label'][1]; ?>: </strong>
                                    <a href="<?php echo $link['more_info_links_url'][1]; ?>" title="<?php echo $link['more_info_links_link_text'][1]; ?>"><?php echo $link['more_info_links_link_text'][1]; ?></a>
                                </div>
                            <?php } ?>
                            </div><!-- end more links -->
                            <div id="copy-block-2" class="copy-block">
                            	<h2><?php echo $copyBlock[2]['copy_block_title'][1]; ?></h2>
                                <?php echo $copyBlock[2]['copy_block_content'][1]; ?>
                            </div><!-- end copy block -->
                        </div>
                        <div class="slideshow-wrapper left">
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
                                                	<a class="plant-name" href="<?php echo $img['image_slide_slide_link'][1]; ?>" title="<?php $img['image_slide_slide_text'][1]?>"><?php echo stripTag($img['image_slide_slide_text'][1]);?></a>
                                                    <a href="<?php echo $entireCollection; ?>" title="<?php echo $viewAll; ?>"><?php echo $viewAll; ?></a>
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
                    </div><!-- end top content -->
                    <div class="mid-content clear">
                    	<iframe id="<?php echo $metaInfo[1]['meta_information_youtube_video_id'][1]; ?>" src="//www.youtube.com/embed/<?php echo $metaInfo[1]['meta_information_youtube_video_id'][1]; ?>?rel=0&enablejsapi=1" height="215" width="360" allowfullscreen="" frameborder="0" class="left"></iframe>
                        <div id="copy-block-3" class="copy-block">
                            <h2><?php echo $copyBlock[3]['copy_block_title'][1]; ?></h2>
                            <?php echo $copyBlock[3]['copy_block_content'][1]; ?>
                        </div><!-- end copy block -->
                    </div><!-- end mid-content -->		
				</article>
				<!-- /article -->

                <?php 
					//If Number of Plants is set to 0, then omit the plant grid, but keep the plant savvy sign up block
					if($number == 0){ ?>
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
						
				<?php } else { ?>
                <div class="collection-plants clearfix">
                	<h2><?php the_title(); ?></h2>
                <?php 
					//Set up Search Parameters
					$search_plants = new search_plant('','id,item_number,common_name,botanical_name,is_new',false);
					$search_plants->order_by = 'common_name ASC';
					$search_plants->results_per_page = $number;
					$search_plants->criteria[$column] = $collectionName;
					$search_plants->search(false);
					$search_plants->output_results_plant_search();
				?>
                </div>
                <!-- end collection plants -->
                <?php } // End else ?>
						
                
				<?php endwhile; endif; ?>
		</section>
        <?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>
