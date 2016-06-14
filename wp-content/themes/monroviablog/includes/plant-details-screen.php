        <div id="plant_icons">
				 <?php 
				 	$theme_path = get_template_directory_uri();
                    $icon_groups = array();
					// Only Show below Collections
					$allowArray = array('Edibles','Succulents');
					$icon_alts  = array("/img/catalog/icons/full_sun_32.gif" => "Light needs: Full Sun",
										"/img/catalog/icons/partial_sun_32.gif" => "<strong>Partial Sun / Partial Shade</strong>: These two terms are often used interchangeably to mean 3-6 (or 4-6) hours of sunlight each day. However, there is a difference.
<strong>Partial shade</strong> typically means the plants will appreciate a more gentle exposure such as the weaker morning or early afternoon sun, with the emphasis on providing the minimum needed shade and sheltering from intense late afternoon sun. <strong>Partial sun</strong> typically means the plants <u>need</u> some direct sun, so the emphasis is on meeting the required minimum hours of sunlight, with filtered sunlight or shade the balance of the day.
Both are best with shelter from the harshest late afternoon sun. This shade could be provided by a structure, a wall, larger plants or  tree(s).",
										"/img/catalog/icons/full_shade_32.gif" => "Light needs: Full Shade",
										"/img/catalog/icons/water_high_32.gif" => "Water needs: High",
										"/img/catalog/icons/water_moderate_32.gif" => "Water needs: Moderate",
										"/img/catalog/icons/water_low_32.gif" => "Water needs: Low",
										"/img/catalog/icons/deer_resistant_32.gif" => "Deer Resistant",
										"/img/catalog/icons/collection_danhinkley_32.gif" => "Plant Collection: Dan Hinkley",
										"/img/catalog/icons/collection_distinctivelybetter_32.gif" => "Plant Collection: Distinctively Better",
										"/img/catalog/icons/collection_distinctivelybetterperennials_32.gif" => "Plant Collection: Distinctively Better Perennials",
										"/img/catalog/icons/collection_edibles_32.gif" => "Plant Collection: Edibles",
										"/img/catalog/icons/collection_itohpeonies_32.gif" => "Plant Collection: Itoh Peonies",
										"/img/catalog/icons/collection_provenwinners_32.gif" => "Plant Collection: Proven Winners",
										"/img/catalog/icons/collection_succulents_32.gif" => "Plant Collection: Succulents",
										"/img/catalog/icons/size_32.gif" => "Average Landscape Size",
										"/img/catalog/icons/flower_32.gif" => "Flowering Time",
										"/img/catalog/icons/landscape_32.gif" => "Landscape Uses",
										'/img/catalog/icons/key_32.gif' => "Key Feature"
										);
					
                                       
                        // SUN EXPOSURE ICONS
                        if(isset($record->info['sun_exposures'])){
                            $sun_exposures = '';
                            for($i=0;$i<count($record->info['sun_exposures']);$i++){
                                $sun_exposures .= ',' . $record->info['sun_exposures'][$i]->name;
                            }
                            $sun_exposures = strtolower($sun_exposures . ',');
            				$icons_sun_exposure = array();
							
                            if(strpos($sun_exposures,',full sun,')!==false) $icons_sun_exposure[] = $sunIcon = '/img/catalog/icons/full_sun_32.gif';
                            if(strpos($sun_exposures,',partial shade,')!==false||strpos($sun_exposures,',filtered sun,')!==false||strpos($sun_exposures,',partial sun,')!==false) $icons_sun_exposure[] = $sunIcon = '/img/catalog/icons/partial_sun_32.gif';
                            if(strpos($sun_exposures,',full shade,')!==false) $icons_sun_exposure[] = $sunIcon = '/img/catalog/icons/full_shade_32.gif';
            
                            $icon_groups[] = $icons_sun_exposure;
							
							
                        }
                    
                        // WATER REQUIREMENT ICONS
						// Set Default to prevent errors
						$waterIcon = array('/img/catalog/icons/water_moderate_32.gif');
                        if(isset($record->info['water_requirement'])){
                            if($record->info['water_requirement']=='High') $icon_groups[] = $waterIcon = array('/img/catalog/icons/water_high_32.gif');
                            if($record->info['water_requirement']=='Moderate') $icon_groups[] = $waterIcon = array('/img/catalog/icons/water_moderate_32.gif');
                            if($record->info['water_requirement']=='Low') $icon_groups[]  = $waterIcon =  array('/img/catalog/icons/water_low_32.gif');
							
                        }
						
					// Set default Icon if Special Feature is present
					if(isset($record->info['special_features'])){ $icon_groups[]= $keyIcon = array('/img/catalog/icons/key_32.gif');}
            
                        // DEER RESISTANT ICON
                        	//check problem/solutions
                        	$deerResistIcon = false;
                        	if(isset($record->info["problem_solutions"])){
                        		for ($icon_i=0;$icon_i<count($record->info["problem_solutions"]);$icon_i++){
                        			if($record->info["problem_solutions"][$icon_i]->name == 'Deer Resistant'){
                        				$icon_groups[] = $keyIcon = array('/img/catalog/icons/deer_resistant_32.gif');
                        				$deerResistIcon = true;
                        			}
                        		}
                        	}
                        //check features | if not already
                   if($deerResistIcon == false){
                        if(strpos($record->info['special_features_friendly'],'Deer Resistant')!==false){ $icon_groups[]=$keyIcon = array('/img/catalog/icons/deer_resistant_32.gif');}
				   }

						
            
                        // COLLECTIONS ICONS
						if($record->info['collection_name'] && in_array($record->info['collection_name'],$allowArray)){
							$collection_abbreviation = get_collection_abbreviation($record->info['collection_name']);
							if($collection_abbreviation!=''){
								$icon_groups[] = array('/img/catalog/icons/collection_'.$collection_abbreviation.'_32.gif');
							}
						}
                        
						$img_string = '';
                        for($i_icon_groups=0;$i_icon_groups<count($icon_groups);$i_icon_groups++){
                            if(count($icon_groups[$i_icon_groups])>0){
                                for($i_icons=0;$i_icons<count($icon_groups[$i_icon_groups]);$i_icons++){
                                    
                                     #$img_string .='<img src="'.$theme_path.$icon_groups[$i_icon_groups][$i_icons].'" width="32" height="32" alt="'.$icon_alts[$icon_groups[$i_icon_groups][$i_icons]].'" title="'.$icon_alts[$icon_groups[$i_icon_groups][$i_icons]].'" />';
									 $img_string .='<img src="'.$theme_path.$icon_groups[$i_icon_groups][$i_icons].'" width="32" height="32" class="icons" data-tooltip="'.$icon_alts[$icon_groups[$i_icon_groups][$i_icons]].'" />';
                                }
                            }
                        }
						echo $img_string;
					 ?>
                </div><!-- END Plant Icons -->
                <div class="plant-availability">
                <h3>Availability</h3>
 		<?php
		$_query_count = "SELECT DISTINCT plants.id FROM plants LEFT JOIN shop_plant_availibility ON plants.item_number = shop_plant_availibility.item_number WHERE ((MATCH(common_name,botanical_name,synonym,trademark_name,php_metaphone,keywords) AGAINST ('\"".$plantGenus."\" +".$plantGenus."' IN BOOLEAN MODE) + ((common_name LIKE '%".$plantGenus."%')*2) + ((botanical_name LIKE '%".$plantGenus."%')*2)) ) AND is_active IN ('1') GROUP BY shop_plant_availibility.item_number";
		$_results_count = mysql_query($_query_count);
		if (mysql_num_rows($_results_count) > 1) {
			$availibilityMessage = '<div class="shop-icon">Not Available.<br /><a href="/plant-catalog/search/?query='.$plantGenus.'">See other plants you might like. <i class="fa fa-angle-double-right"></i></a></div>';
		} else {
			$availibilityMessage = '<div class="shop-icon">Not Available.<br /><a href="/plant-catalog/search/?type=10">See other plants you might like. <i class="fa fa-angle-double-right"></i></a></div>';
		}
		/* 
		if(in_array($plantStatus, $statusArray)){
			switch($plantStatus){
				case 2: 
				$availibilityMessage = '<div class="shop-icon disabled hide">Not Available.<br /><a href="/plant-catalog/search/?query='.$plantGenus.'">See others plant you might like. <i class="fa fa-angle-double-right"></i></a></div>';
				break;
				case 3: 
				$availibilityMessage = '<div class="shop-icon disabled hide">Not Available.<br /><a href="/plant-catalog/search/?query='.$plantGenus.'">See others plant you might like. <i class="fa fa-angle-double-right"></i></a></div>';
				break;
				case 4: 
				$availibilityMessage = '<div class="shop-icon disabled hide">Not Available.<br /><a href="/plant-catalog/search/?query='.$plantGenus.'">See others plant you might like. <i class="fa fa-angle-double-right"></i></a></div>';
				break;
				case 6: 
				$availibilityMessage = '<div class="shop-icon disabled hide">Not Available.<br /><a href="/plant-catalog/search/?query='.$plantGenus.'">See others plant you might like. <i class="fa fa-angle-double-right"></i></a></div>';
				break;
			}
		} */
			$isThirdParty = 0;
			if($record->info['special_third_party'] == 'Lowes'){ $isThirdParty = 1; }
			//Has Status of 1
			// If you can purchase the Plant online, then show Shop Link
			// Else, disable it
			if($buyPlant){
            //Set Status
			$status = "true";
			?>
            	<a class="shop-icon" href="<?php echo $buyPlant; ?>">Buy Online Now and Pick-Up at your local Garden Center <i class='fa fa-angle-double-right'></i></a>
            <?php  } else {
				echo $availibilityMessage; 
			} ?>
                <!-- Plant Messaing for Purchasing Options -->
                <!--div class="plant-messaging clearfix" data-item="<?php echo $record->info['item_number'];?>" data-status="<?php echo $status?>">

						<script type="text/javascript"> 

							if(getCookie('zip_code') != '' && getCookie('zip_code') != undefined){

								var postalCode = getCookie('zip_code');

								isPlantLocal('<?php echo $record->info['item_number'];?>','<?php echo $isThirdParty ?>',postalCode);
							} else {
								// Use the Maxmind API to get the zipcode
							var onSuccess = function(location){
				
							   var string = JSON.stringify(location, undefined, 4);
							   var data = JSON.parse(string);
							   var postalCode = data.postal.code;
							   setCookie('zip_code', postalCode, 365 );
							   isPlantLocal('<?php echo $record->info['item_number'];?>','<?php echo $isThirdParty ?>',postalCode);
							   
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
                       <span class='ajax-loader messaging' style="display:block"></span>
                </div--><!-- end plant messaging -->
                
                <!-- Add to Favorites Button -->
            <?php //Disable button if already added to favorites
				if(is_user_logged_in()){
					echo "<span class='ajax-loader fav'></span>";
					if(getUserWishlist($record->info['id']) == true){  ?>
                   	 	<!-- allow to remove plant if already in Favorites -->
						<a class="fav-icon favorite remove" href="javascript:void(0);" data-action="remove">Remove from Favorite <i class='fa fa-angle-double-right'></i></a>
				<?php } else { ?>
                		<!-- use ajax call to add to wishlist, if logged in and not already added -->
                		<a class="fav-icon favorite" href="javascript:void(0);"  data-action="add">Add to Favorites <i class='fa fa-angle-double-right'></i></a>
               <?php } ?>
            <?php } else {  ?>
            		<!-- User is not logged in, so have them log in -->
					<a class="fav-icon favorite" href="/community/login/?notice=wishlist">Add to Favorites <i class='fa fa-angle-double-right'></i></a>
                <?php } ?>
         <?php if(check_user_role("administrator") || check_user_role("highresimage")){
			 ?> <div class="high-res clearfix"> <?php
				if($record->info['has_downloadable_images']){
				$upload_dir = wp_upload_dir();
	 	  ?>
                
					<form action="<?php echo $upload_dir['baseurl'];?>/plants/download.php" target="_blank" method="post" onsubmit="return validate_image_download();" class="clearfix">
						<button class="green-btn right">Download</button>
						<input type="hidden" name="id" value="<?php echo $record->info['id']?>" />
                        <span>Hi-res imagery: Image set(.zip)</span>
					</form>
			<?php }else{ ?>
					<span>Hi-res imagery:Photo(s) not available in hi-res.</span>

				<?php } ?>
			</div><!-- end high res --> 
		<?php	} ?>
        </div><!-- end plant availibility -->       
