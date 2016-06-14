/* Plant Specific JS */
	
jQuery(document).ready(function(){
	
	
	//Intiate the tabs only if the screen size is over 979
	if(!jQuery('body').hasClass('mobile')){
		jQuery( ".tabs" ).tabs();
	}
	
	//Set the URL values on Garden Center Links
	var userZip = getCookie('zip_code');
	//jQuery('#gc').attr('href', '/find-a-garden-center/?location='+userZip);
	
	// Connect the two slideshow to get the Carousel Effect
	var slideshows = jQuery('.cycle-slideshow').on('cycle-next cycle-prev', function(e, opts) {
		// advance the other slideshow
		slideshows.not(this).cycle('goto', opts.currSlide);
		/*if(opts.currSlide == 0){
			jQuery('.cycle-carousel-wrap').animate({left: '0px'}, 500, function(){});
		}*/
	});

	//Connect the second slideshow to change the first slideshow
	jQuery('#cycle-2 .cycle-slide').click(function(){
		var index = jQuery('#cycle-2').data('cycle.API').getSlideIndex(this);
		slideshows.cycle('goto', index);
	});
	
	function validate_garden_center_search(){
		return !!get_field('location').value;
	}

	// Add AJAX calls to the favorite buttons
	jQuery('.favorite').on('click', function(){
		//Show the Loader Icon
		jQuery('.ajax-loader.fav').show();
		// Set up the variables
		var process = jQuery(this).attr('data-action');
		var plantID = monrovia_plant_record.plant_id;//<?php echo $record->info['id'];?>;
		var plantItem = monrovia_plant_record.plant_item;//<?php echo $record->info['item_number']?>;

		// Make the AJAX call to Add or Remove the Favorite
		jQuery.post(ajaxurl, { action: 'updateFavorites', ax: process, pid: plantID, itemNum: plantItem  }, function( data ) {
			data = jQuery.parseJSON(data);
			var result = data.result;
			//Swap the button text and data-action values
			if(data.result == 'Added'){
				jQuery('a.favorite').attr('data-action', 'remove').addClass('remove').html('Remove from Favorites <i class="fa fa-angle-double-right"></i>');
			} else {
				jQuery('a.favorite').attr('data-action', 'add').removeClass('remove').html('Add to Favorites  <i class="fa fa-angle-double-right"></i>');
			}
			// Hide the loader
			jQuery('.ajax-loader.fav').hide();
		}); 

	});	
	
});

/*  Use an AJAX call to check if a particular plant has
		been invoiced at an IGC center in the last 30 days within 100
		miles of the provided zipcode
		
		@string plantItem - the plant item number
		@string zipcode - the US zipcode of the current user
	*/
	function isPlantLocal(plantItem,thirdParty,userZip){
		
		//Show the Loader Icon
		jQuery('.ajax-loader.messaging').show();
		
		// Set up the variables
		var messaging = ''

		// Make the AJAX
		jQuery.post(ajaxurl, { action: 'checkLocally', zip: userZip, itemNum: plantItem  }, function( data ) {
			data = jQuery.parseJSON(data);
			var result = data.result;
			// If the plant is availiable Locally
			if(data.result == 'true'){
					messaging = "<a href='/find-a-garden-center/?location="+userZip+"&item_number="+plantItem+"&range=100' class='igc-icon'>Check here for selected retailers who have ordered<i class='fa fa-angle-double-right'></i></a>";
			} else { // Not availiable Locally
				//Is it Status 7?
				if(thirdParty == 1){
					messaging = "<a href='/find-a-garden-center/?location="+userZip+"&range=25' class='igc-icon'>Check here for selected retailers who have ordered<i class='fa fa-angle-double-right'></i></a>";
				} else {
					//Show not Availibale div
					jQuery('.shop-icon.hide').show();
				}
			}
			// Hide the loader
			jQuery('.ajax-loader.messaging').hide();
			
			// Display the messaging
			jQuery('.plant-messaging').html(messaging);
		});
		
	} // End isPlantLocal