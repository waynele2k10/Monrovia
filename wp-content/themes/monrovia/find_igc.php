<?php /* Template Name: Find a Garden Center */
	get_header(); 
    
	//Include XML Class from Old Monrovia site
	//include('includes/xml.php'); Including this on functions.php
	//Include Utility Functions from Old Monrovia site
	//include('includes/utility_functions.php');

	//Define Variables
    $query_location = '';
    $latitude = 0;
    $longitude = 0;
    $range = 25;
    $max_results = 100;
    $garden_centers = array();
	$lowes_locations = array();
		
	$mapquest_api_key = 'Fmjtd%7Cluua2hutnd%2C2n%3Do5-96ax9r';
    
    $filter_type = '';
	
	// If its a plant assigned to a Third Party, like Lowe's
	$third_party = '';
    if(isset($_GET['third_party'])) $third_party = $_GET['third_party'];
    
    $item_number = '';
    if(isset($_GET['item_number'])) $item_number = $_GET['item_number'];
    
    $gc_type = '';
	// IF Lowe's type plant, remove Lowes location
	// from gc_type parameter, as it may cause duplication
    if(isset($_GET['gc_type'])) $gc_type = $_GET['gc_type'];
	if($gc_type=="" && $third_party == "lowes"){
		$gc_type = "igc";
	}

    function find_garden_centers($lat,$lon,$zip,$range,$item_number,$filter_type,$gc_type){
        if (($lat && $lon)||$zip){
            set_time_limit(60); // ALLOW UP TO A MINUTE
            
            $url = 'http://azlink.monrovia.com/tpg.php?';
            
            if($zip){
            	$url .= 'zip=' . $zip;
            }else{
            	$url .= 'lat=' . $lat . '&lon=' . $lon;
            }
            
            $url .= '&filter_type='.$filter_type.'&item_number='.$item_number.'&range='.$range.'&gc_type='.$gc_type.'&max_results=' . $GLOBALS['max_results'];
            
            $xml = get_url($url,60);
            $xml_doc = XML_unserialize($xml);
            $garden_centers = $xml_doc['response']['results']['result'];

            //Check if the plant is a lowes plant, if so remove non-lowes results
            /*if ( isset($item_number) ){
                $record = new plant(get_plant_id_by_item_number($item_number));
                if ( $record->info["special_third_party"] == "Lowes"){
                    $modified_garden_centers = array();
                    foreach ($garden_centers as $keyIndex => $garden_center ) {
                        if ( $garden_center["name"] == "Lowe's" ){
                            $modified_garden_centers[] = $garden_center;
                        }
                    }
                    $garden_centers = $modified_garden_centers;
                }
            } */
            
            if(gettype($garden_centers)=='string'&&trim($garden_centers)=='') $garden_centers = array();
            
            // IF ONLY ONE RESULT, WE NEED TO MAKE SURE IT'S IN ARRAY FORM, WHICH XML_unserialize BREAKS
            if(is_array($garden_centers)&&count($garden_centers)&&!isset($garden_centers[0])){
                $garden_centers = array($garden_centers);
            }
        }else {
            $garden_centers = array();
        }
                
        return $garden_centers;
    }
	
	if(isset($_GET['lat'])&&$_GET['lat']&&isset($_GET['lon'])&&$_GET['lon'] ) {
        $latitude = doubleval($_GET['lat']);
        $longitude = doubleval($_GET['lon']);
    }else{
		if(isset($_GET['location'])){
        	$query_location = $_GET['location'];
		}
    }
    
    if(isset($_GET['range'])&&intval($_GET['range'])>0) $range = intval($_GET['range']);
    if($range<10) $range = 10;
    if($range>100) $range = 100;   

    if(isset($_GET['gc_type'])&&($_GET['gc_type']=='lowes'||$_GET['gc_type']=='igc')) $gc_type = $_GET['gc_type'];

    if($query_location!=''){
    	if(is_valid_us_zip(parse_numeric($query_location))){
    		$query_location = parse_numeric($query_location);
    		$latitude = -1;
    		$longitude = -1;
    	}else{
			$geocode_result = do_geocode($query_location);
			if($geocode_result['success']){
				$latitude = $geocode_result['lat_long'][0];
				$longitude = $geocode_result['lat_long'][1];   	
			}       
    	}
    }
		
        
        if($latitude!=0&&$longitude!=0){
    		if($latitude==-1&&$longitude==-1){
    			// SEARCH BY ZIP
	    		$garden_centers = find_garden_centers(null,null,$query_location,$range,$item_number,$filter_type,$gc_type); 
				// If user didnt set IGC only and third party is lowes, then run another api call
				if(isset($_GET['gc_type']) && $_GET['gc_type'] != "igc" && $third_party == "lowes") {
					$lowes_locations = find_garden_centers(null,null,$query_location,$range,'',$filter_type,'lowes'); 
					}
    			}else{
	    		$garden_centers = find_garden_centers($latitude,$longitude,null,$range,$item_number,$filter_type,$gc_type); 
				// If user didnt set IGC only and third party is lowes, then run another api call
				if(isset($_GET['gc_type']) && $_GET['gc_type'] != "igc" && $third_party == "lowes") {
					$lowes_locations = find_garden_centers(null,null,$query_location,$range,'',$filter_type,'lowes'); 
					}
    			}
    		}else{
    		$garden_centers = array();
    		}
			
			// Merge Lowes Results with IGC Results
			if(count($lowes_locations)){
				if(count($garden_centers)){
					$garden_centers = array_merge($lowes_locations, $garden_centers);
				} else {
					$garden_centers = $lowes_locations;
				}
			}
?>
 <script>
    jQuery(window).load( function(){       
        if(jQuery('#map.map-border').width() > 0) //check if there is a map div
        {
                //display map
                var mapOptions = {
                  zoom: 12,
                  minZoom: 4,
                  maxZoom: 17,
                  mapTypeId: google.maps.MapTypeId.ROADMAP
                };
                
                var map = new google.maps.Map(document.getElementById('map'),mapOptions);
                
                var infoWindow = new google.maps.InfoWindow();
                var garden_centers = jQuery('.details');
                var bounds = new google.maps.LatLngBounds();
                var garden_centers_array = [];
                
                //center map at the searhc zipcode if there are no garden centers found
                if (garden_centers.length == 0) {
                    map.setCenter(new google.maps.LatLng(<?php echo $latitude.''?>,<?php echo $longitude.''?>));
                } else {
                    for (var i = 0, len = garden_centers.length; i < len; ++i) {
                        garden_center = new Object();
                        garden_center.name = jQuery(garden_centers[i]).attr('name');
                        garden_center.address = jQuery(garden_centers[i]).attr('address');
                        garden_center.position = new google.maps.LatLng(parseFloat(jQuery(garden_centers[i]).attr('latitude')), parseFloat(jQuery(garden_centers[i]).attr('longitude')));
                        garden_center.directions = jQuery(garden_centers[i]).attr('directions');
                        garden_center.icon = jQuery(garden_centers[i]).attr('icon');
                        garden_center.marker = new google.maps.Marker({position: garden_center.position, map:map});
                        garden_center.marker.set('location', garden_center.position);
                        garden_center.marker.set('text', '<b>'+garden_center.name +'</b><br />' + garden_center.address + '<br /><br /><a style="text-decoration:underline;border:0;" href ="'+ garden_center.directions +'" target="_blank">Get directions</a>');
                        garden_center.marker.set('icon', garden_center.icon);
                        garden_center.marker.set('listing', i);
                        garden_center.infoWindow = jQuery(garden_centers[i]).attr('name');
                        bounds.extend(garden_center.position);
                        
                        google.maps.event.addListener(garden_center.marker, "click", function(event) {
                            infoWindow.setContent(this.text);
                            infoWindow.open(map, this);
                            var child = (this.listing+1);
							var garden = jQuery('#garden_centers').scrollTop();
							var position = jQuery(".result:nth-child("+child+")").position().top;
							console.log(garden+'and'+position);
                            if (jQuery('.highlighted')) jQuery('.highlighted').removeClass('highlighted');
                            jQuery(".result:nth-child("+child+")").addClass('highlighted');
                            jQuery('#garden_centers').scrollTop((garden+position)-389);
                            
                        });
                        
                        garden_centers_array.push(garden_center);
                    }
                    map.fitBounds(bounds);  
                    var zoomChangeBoundsListener =
					    google.maps.event.addListenerOnce(map, 'bounds_changed', function(event) {
					        if (map.getZoom()>15){
					            map.setZoom(15);
					        }
					});

                }

                jQuery('.result').each(function(index){
                    jQuery(this).on('click',function(){
                        if(jQuery('.highlighted')) jQuery('.highlighted').removeClass('highlighted');
                        jQuery(this).addClass('highlighted');
 
                        
                        infoWindow.setContent(garden_centers_array[index].marker.text);
                        infoWindow.open(map,garden_centers_array[index].marker);
                    });
                });
            
            	// IF ONLY ONE RESULT, DEFAULT TO IT
                if(garden_centers.length==1) jQuery('.result')[0].click();
            
            
        } //end of 'if $map'        
        
    }); //end of onload event
	
	function validate_garden_center_search(){
		return !!get_field('location').value;
	}
    
    function validate_input(){
        var form = jQuery('#location_form');
        if (jQuery('#change_location_input').val()) {
            jQuery('#lat').val('');
            jQuery('#lon').val('');
        }
        if (!jQuery('#lat').val()&&!jQuery('#lon').val()&&!jQuery('#change_location_input').val()) return false;
        return true;
    }
</script>
		<?php //Add a load class to wrapper
			$load = "has-results";
			$section = ' expanded-list'; 
			if(!isset($_GET['location'])){ $load = 'load empty';$section=''; } 
		?>
	    <div class="content_wrapper clear <?php echo $load; ?>">
		<section role="main" class="main clear<?php echo $section;?>">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
            <!-- article -->
            <?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            	<!-- Mobile Controls for Map Functions -->
            	<div class="clear showMobile">
                	<a href="javascript:void(0);" class="left" onclick="jQuery('.change-location').slideToggle(600);">Enter Location</a> 
                    <a href="javascript:void(0);" id="list" class="left green-btn" onclick="jQuery('section').toggleClass('expanded-list');">LIST</a>
                    <a href="javascript:void(0);" id="mapa" class="left green-btn" onclick="jQuery('section').removeClass('expanded-list');">MAP</a>
                </div><!-- end showMobile -->
                <div class="map-key hideMobile">
            		<div class="icons_description">
                		<img src="<?php echo get_template_directory_uri(); ?>/img/icons/icon_garden_center.png" width="26" height="38" alt="Garden Center" class="left" />
                		<p>Garden Center</p>
            		</div><!-- end icons_description -->
            		<div class="icons_description">
                		<img src="<?php echo get_template_directory_uri(); ?>/img/icons/icon_lowes_center.png" width="26" height="38" alt="Lowe's Retail Store" class="left"/>
                		<p>Lowe's Retail Store</p>
            		</div><!-- end icon_description -->
            	</div><!-- end key -->
                <div class="igc-disclaimer hideMobile">
                	<i class="fa fa-truck"></i>
                	<?php the_content(); ?>
                </div><!-- end disclaimer -->
            </article>
			<!-- /article -->
            <?php endwhile; endif; ?>
            <?php
				$value = "enter your location";
			 	if(isset($_GET['location'])){ $value = $_GET['location']; } ?>
            <div class="search-results left">
            	<div class="change-location">
                	<strong>Enter your Location</strong><br />
            		<form action="?" method="get" onsubmit="return validate_input();" id="location_form">
        				<input name="location" class="location" value="<?php echo $value ?>"maxlength="128" type="text" onfocus="if(this.value=='enter your location') this.value=''" onblur="if(!this.value) this.value='enter your location';"/>
                        <div class="select-wrap">
        				<select class="field_select" name="range"  id="change_location_input">
              				<option value="10"<?php echo  $range == '10'? ' selected="selected"' : '' ?>>within 10 miles</option>
                            <option value="25"<?php echo  $range == '25'? ' selected="selected"' : '' ?>>within 25 miles</option>
                            <option value="50"<?php echo  $range == '50'? ' selected="selected"' : '' ?>>within 50 miles</option>
                            <option value="75"<?php echo  $range == '75'? ' selected="selected"' : '' ?>>within 75 miles</option>
                            <option value="100"<?php echo  $range == '100'? ' selected="selected"' : '' ?>>within 100 miles</option>   
        				</select>
                        </div><!-- end select wrap -->
                        <div class="select-wrap">
                        <?php // Define $type for search
							$type = '';
						    if(isset($_GET['gc_type'])) $type = $_GET['gc_type'];
							?>
						<select class="field_select" name="gc_type" >
							<option value="igc"<?php echo  $type == 'igc'? ' selected="selected"' : '' ?>>local garden centers only</option>
                            <option value="lowes"<?php echo  $type == 'lowes'? ' selected="selected"' : '' ?>>local Lowe's stores only</option>
                            <option value=""<?php echo  $type != 'igc' && $type!='lowes'? ' selected="selected"' : '' ?>>local garden centers & Lowe's </option>
						</select>
                        </div><!-- end select wrap -->
        				<input onclick="ga('send', 'event', 'Garden Center Search', 'Location search performed');" type="submit" title="search" class="green-btn right small" value="search" /> 
                        <input type="hidden" name="item_number" value="<?php echo $item_number?>" />
                        <input type="hidden" name="lat" id="lat" value="<?php echo $latitude.''?>" />
                        <input type="hidden" name="lon" id="lon" value="<?php echo $longitude.''?>" />
                        <input type="hidden" name="filter_type" value="<?php echo $filter_type?>" />
                        <input type="hidden" name="third_party" value="<?php echo $third_party?>" />   
    			</form>
            </div><!-- end change-form -->
            
  <?php if(isset($_GET['item_number'])){
        $plant = new plant();
        $plant->load_by_item_number($_GET['item_number']);
        if($plant->info['id']!=''){
            $plant->get_images();
        ?>
			<?php if(count($garden_centers)){ ?>
				<div class="plant-item-search clear">
				<?php if(isset($plant->info['image_primary'])){ ?>
					<a href="<?php echo $plant->info['details_url']?>"><img src="<?php echo $plant->info['image_primary']->info['path_detail_thumbnail']?>" class="left" /></a>
				<?php } ?>
					<a href="<?php echo $plant->info['details_url']?>"><?php echo $plant->info['common_name']?></a> may have shipped to the following garden centers. Please feel free to contact the garden centers to be sure this plant is still in stock.
				</div><!-- end plant-item-search -->
			<?php } ?>

        <?php
        }
    } ?>
        	
            <?php if(count($garden_centers) == 0){$class = ' no-results';} ?>
            <div id="garden_centers" class="left<?php echo $class; ?>">
                <?php if(!count($garden_centers)){ ?>
                	<?php if($latitude==0||$longitude==0){ ?>
						<h3 style="margin-top:15px;">Your location could not be determined.</h3>
						<p><br />Tip: Please make sure you entered your location correctly.</p>             	
                	<?php }else{ ?>
                		<?php if(isset($plant)&&$plant->info['id']!=''){ ?>
							<h3 style="margin-top:15px;">We couldn't find a garden center near you that carries the <a href="<?php echo $plant->info['details_url']?>"><?php echo $plant->info['common_name']?></a>.</h3>
							<br /><br />
							
							<?php
								$url = '?';
								
								if($query_location!=''){
									$url .= 'location='.$query_location;
								}else{
									$url .= 'lat=' . $latitude . '&lon=' . $longitude;
								}
								
								$url .= '&range=' . $range;							
							?>
							
							Click <a href="<?php echo $url?>">here</a> to search for all garden centers in your area.
                		<?php }else{ ?>
							<h3 style="margin-top:15px;">No garden centers found.</h3>
							<?php if($range<100){ ?>
								<p><br />Tip: Try increasing the search radius to see more results.</p>
							<?php } ?>
                		<?php } ?>                	
                	<?php } ?>
                <?php }else{ 
                $index = 0;
                foreach($garden_centers as $garden_center){
                    $index++;
                    ?>
                <div class="result" <?php if($index==1) echo('style="border-top:0px;"'); ?>>
                    <table>
                        <tr>
                           <td>
				<!-- No more boutique locations so change all boutique icons to regular garden center icons for now -->
                              <div class="icon">
                              <?php if($garden_center['is_boutique']=='true') {?>
                              	<img src="<?php echo get_template_directory_uri(); ?>/img/icons/icon_garden_center.png" width="26" height="38" alt="Garden Center with Monrovia Boutique" />
                             <?php } elseif($garden_center['name'] == "Lowe's") { ?>
                              	<img src="<?php echo get_template_directory_uri(); ?>/img/icons/icon_lowes_center.png" width="26" height="38" alt="Lowe's Retail Store" />
                             <?php } else { ?>
                              	<img src="<?php echo get_template_directory_uri(); ?>/img/icons/icon_garden_center.png" width="26" height="38" alt="Garden Center" />
                             <?php } ?>         
                             </div><!-- end icon -->
                              <div class="details" latitude="<?php echo $garden_center['latitude']?>" longitude="<?php echo $garden_center['longitude']?>" name="<?php echo $garden_center['name']?>" address="<?php echo $garden_center['address']?>,<br /><?php echo $garden_center['city']?>, <?php echo $garden_center['state']?> <?php echo $garden_center['zip']?>" icon="<?php if($garden_center['name']=="Lowe's") echo get_template_directory_uri().'/img/icons/icon_lowes_center.png'; else echo get_template_directory_uri().'/img/icons/icon_garden_center.png';?>" directions="http://maps.google.com/maps?saddr=&daddr=<?php echo $garden_center['address']?>, <?php echo $garden_center['city']?>, <?php echo $garden_center['state']?> <?php echo $garden_center['zip']?>">
                                    
                                    <p><strong><?php echo ($garden_center['name'] == "Stein Gardens & Gifts")?"Stein's Garden & Home":$garden_center['name']; ?></strong><br />
                                    <?php echo $garden_center['address']?>, <br />
                                    <?php echo $garden_center['city']?>, <?php echo $garden_center['state']?> <?php echo $garden_center['zip']?><br />
                                    <?php echo $garden_center['phone']?>
                                    	<?php if(isset($garden_center['url'])&&$garden_center['url']!=''){ ?>
                                        	<br /><a href="<?php echo $garden_center['url']?>" class="underline" target="_blank" onclick="pageTracker._trackEvent('Garden Center','Click - Website','<?php echo parse_alphanumeric($garden_center['name'],' ')?> <?php echo parse_alphanumeric($garden_center['zip'],' ')?>');">Visit website</a>
                                    	<?php } ?>
                                    </p>
                                </div><!-- end details -->   
                            </td>
                        </tr>
                    </table>
                </div><!--end of result-->
                    <?php
                } //end of foreach
             } // End Else
            ?>
                
            </div><!--end of garden_centers -->
        </div><!-- end search results -->
        <?php if(count($garden_centers)>0){ ?>
	    <div id="map" class="map-border"></div><!-- end map -->     
	    <?php }else{ ?>
	    <div id="map">
        	<?php the_post_thumbnail('full'); ?>
        </div><!-- end map -->
	        <?php } ?>
		</section>
	</div><!-- end content_wrapper -->
<script type="text/javascript"> 
var ftRandom = Math.random()*1000000000000000000; 
document.write('<iframe style="position:absolute; visibility:hidden; width:1px; height:1px;" src="http://servedby.flashtalking.com/spot/8/7439;56268;5972/?spotName=Garden_Center_action&cachebuster='+ftRandom+'"></iframe>'); 
</script>
<?php get_footer(); ?>