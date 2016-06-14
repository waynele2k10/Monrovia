<?php 
/* 
Home Page Slideshow
*/
?>  
                <div class="slide-wrap">
            		<div class="cycle-slideshow"
    					data-cycle-fx="scrollHorz"
    					data-cycle-pause-on-hover="true"
    					data-cycle-speed="750"
                        data-cycle-manual-speed="250"
                        data-cycle-timeout="5000"
                		data-cycle-swipe=true
                		data-cycle-pager=".cycle-pager"
                        data-cycle-slides=".item"
    					data-cycle-pager-template="<span></span>"
    				>
            		<!-- prev/next links -->
   					<div class="cycle-prev"></div>
    				<div class="cycle-next"></div>
        			<?php 
						$slides = get_group('home_slide'); 
						foreach($slides as $slide){ 
                        //Display Overlay??
						$overlay = $slide['home_slide_show_plant_name_overlay'][1];	
						?>
							<div class="item">
                            <?php if($overlay == 1){ ?>
                            	<div class="infoOverlay hideMobile">
                                	<a href="<?php echo $slide['home_slide_plant_link'][1]; ?>"><?php echo $slide['home_slide_plant_names'][1]; ?></a>
                                    <a href="<?php echo $slide['home_slide_plant_link_2'][1]; ?>"><?php echo $slide['home_slide_plant_names_2'][1]; ?></a>
                                    <a href="<?php echo $slide['home_slide_plant_link_3'][1]; ?>"><?php echo $slide['home_slide_plant_names_3'][1]; ?></a>
                                </div><!-- end info overlay -->
                            <?php } ?>
                    			<img src="<?php echo $slide['home_slide_image'][1]['original']; ?>" width="980" height="327" class="slide-image hideMobile" alt="<?php echo $slide['home_slide_button_label'][1]?>" />
                                <img src="<?php echo $slide['home_slide_mobile_image'][1]['original']; ?>" width="100%" height="" class="slide-image showMobile" alt="<?php echo $slide['home_slide_button_label'][1]?>" />
                    			<div class="slide-content">
									<h2><?php echo $slide['home_slide_messaging'][1]; ?></h2>
                        			<a href="<?php echo $slide['home_slide_button_link'][1]?>" class='green-btn'><?php echo $slide['home_slide_button_label'][1]?></a>
								</div><!-- end slide content -->	
							</div><!-- end item -->	
					<?php } // End foreach ?>
        			<div class="cycle-pager"></div>
        		</div><!-- end cycle-slideshow -->
            </div><!-- end slide-wrap -->
			