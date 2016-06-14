// DOM Ready
jQuery(function() {
	
	// SVG fallback
	// toddmotto.com/mastering-svg-use-for-a-retina-web-fallbacks-with-png-script#update
	if (!Modernizr.svg) {
		var imgs = document.getElementsByTagName('img');
		var dotSVG = /.*\.svg$/;
		for (var i = 0; i != imgs.length; ++i) {
			if(imgs[i].src.match(dotSVG)) {
				imgs[i].src = imgs[i].src.slice(0, -3) + "png";
			}
		}
	}
	
});

jQuery(document).ready( function($){
	getScreenWidth();
	//Prevent Global Plant Catalog from opening up the page on mobile
	jQuery('.mobile .prevent > a').on('click', function(e){
		if(jQuery('body').hasClass('mobile')){
			var a = jQuery(this);
			e.preventDefault();
			e.stopPropagation();
			if(a.parent('li').hasClass('menu-back')){
				//Close menu
				a.parents('ul.sub-menu').removeClass('expanded')
			} else {
				//Slide open the child <ul> menu
				a.parent('li').find('ul.sub-menu').addClass('expanded');
			}
		}
	});
	
	/*** Accordianize stuff!! *************/
		jQuery('.accordian > li > a').on('click', function(e){
			e.preventDefault();
			jQuery(this).toggleClass('open').parents('li').find('.accordianTarget').slideToggle(500);
		});
	
	// Function to submit the newsletter sign up form
	// Submit the iContact form via AJAX
		jQuery('#newsletter-signup').on('click', function(e){
			e.preventDefault();
			var email = jQuery('#nemail').val();
			//Only Submit if it appears like a valid email
			if(checkField(jQuery('#nemail')) != 1){
				//Show ajax loader
				jQuery('.newsletter.ajax-loader').show();
				jQuery('.newsletter-msg').html('').hide();
				// Get some Cookies!
				var zip = getCookie('zip_code');
				var coldzone = getCookie('cold_zone');
				// Send to iContact
				subscribeNewsletter(email, zip, coldzone);

			} else {
				//Display error message
				jQuery('.newsletter-msg').addClass('error').html('Sorry, there was an error. Please try again.').show();
				setTimeout( function(){
				jQuery('.newsletter-msg').fadeOut(600);
			}, 2000);
			}

		});

	
	// Add the show effect to the Cold Zone Tool tip
	$('.question').on('click', function(){
		$(this).siblings('.tool-tip').show(400, 'swing');
	});
	
	// Add the show effect to the Cold Zone Tool tip
	/*$('img.icons').on('click', function(e){
		var content = $(this).attr('data-tooltip');
		var img = $(this).attr('src');
		
		var tip = $('.icon-tip');
		// Get Coordinates of Mouse 
		var x = e.pageX + 25; 
		var y = e.pageY - 55;
		// Fill In the Tool Tip Data 
		tip.find('p').html(content);
		tip.find('img').attr('src', img);
		// Position and display the tool tip 
		tip.css({top: y, left: x});
		tip.show(400, 'swing');
		
	});
	*/
	// Close the tool tip
	$('.tip-close').on('click', function(){
		$(this).parents('.tool-tip').hide(400, 'swing');
	});
	
	// Wrap check boxes with <span> tag for custom styling
	$('input[type="checkbox"]').wrap('<div class="checkwrap"></div>').after('<span />');
	
	// Use <span> elements to style <select> inputs
	// Cant use pseudo elements :before and :after because IE doesent support  css pointer-events property
	//$('.select-wrap').prepend('<span class="before">').append('<span class="after">');
	
	/*** Displaying Labels inside of Text boxes and hiding them when focus() *****/	
	// Hide inline labels on Forms
	$('.wpcf7-form input, .wpcf7-form textarea, .widget input, .search input, input#nemail, input.hideLabel').not(':hidden').each( function (){
		
		var label = $(this).parent('div').find('label').html();
		// If already filled in on Page Load, hide label
		if($(this).val() != ''){
			$(this).parents('.form-item').find('label').animate({'opacity': 0.0}, 300);
		}
		//On Focus, hide the label
		$(this).focus( function(){
			$(this).parents('.form-item').find('label').animate({'opacity': 0.0}, 300);
		});
		// Show Label if Value of Field is still empty
		$(this).blur( function(){
				if($(this).val() == ""){
					$(this).parents('.form-item').find('label').show();
					$(this).parents('.form-item').find('label').animate({'opacity': 1.0}, 300);
				}
				else{
					$(this).parents('.form-item').find('label').hide();
				}
					
		});
		$(this).keypress( function(){
			$(this).parents('.form-item').find('label').hide();
		});
	
	});
	
	jQuery( window ).resize(function() {
		getScreenWidth();
	});
	
});
	
jQuery(document).ready( function($){
	
		/*** Get Zipcode from Geo Coordinates **/
	
		//IF there are cookies set, use them for the User Zipcode and Zone
		if(getCookie('zip_code') && getCookie('cold_zone')){
			// Remove letters from already existing cookies
			var cold = getCookie('cold_zone');
			cold = cold.replace('a','');
			cold = cold.replace('b','');
			// Set the html
			jQuery('.zone-box span').html(cold);
			jQuery('.zipcode span').html(getCookie('zip_code'));
		} else{
			if(GetUrlValue('updated') != 'true'){
				// Get zipcode and set Cold Zone
				useMaximind();
				}
			}
			
		// When the User updates the ziopcode, call getZone
		// to get the updated cold zone and populate the HTML
		jQuery('.zip-update').on('click', function(){
			$(this).siblings('.ajax-loader').show();
			// Get the coldZone
			var zip;
			jQuery('.zip-input').each( function(){
				if($(this).val() != ''){
					zip = $(this).val();
				}
			});
			getZone(zip);
		});
		
		
		// Add AJAX calls to the favorite buttons from Grid View ONLY
			$('.favorite-icon').on('click', function(){
				
				//Check to see if the user is logged in
				var user = $(this).attr('data-user');
				
				if(user == 'true'){
					//Show the Loader Icon
					$(this).parents('.image-wrap').find('.ajax-loader').show();
					// Set up the variables
					var process = $(this).attr('data-action');
					var plantID = $(this).attr('data-pid');
					var plantItem = $(this).attr('data-item');
				
					var that = $(this);
				
					// Make the AJAX call to Add or Remove the Favorite
					$.post(ajaxurl, { action: 'updateFavorites', ax: process, pid: plantID, itemNum: plantItem  }, function( data ) {
						data = jQuery.parseJSON(data);
						var result = data.result;
						//Swap the button text and data-action values
						if(data.result == 'Added'){
							that.attr('data-action', 'remove').addClass('added').siblings('.favorite-text').html('Remove from Favorites');
						} else {
							that.attr('data-action', 'add').removeClass('added').siblings('.favorite-text').html('Add to Favorites');
						}
						// Hide the loader
						that.parents('.image-wrap').find('.ajax-loader').hide();
 					});
					// End AJAX call
				} else {
					//Alert User to log in first
					alert("You must be logged in to add Favorites");
				}
				
			
				
			});
			
// Form Field Validation
/* Functions for live feedback on fields */
jQuery('.checkKeypress').on('keypress', function(){
	checkField(jQuery(this));
});

jQuery('.checkBlur').on('blur', function(){
	checkField(jQuery(this));
});
		
});
			
// Get the User Geo Cordinates
// First try from HTML5 geolcation method
// Then use IP Address Mapping if unavailiable
		
/* Updated 5-14-2014:  No longer using HTML5 Geolocation
   Will now use maxmind geoIP2 Javascript API to get Geolocation
   info based on IP address
   @author http://dev.maxmind.com/geoip/geoip2/javascript/
 */

// This does an ajax call to functions.php to get the Cold Zone based
// On Zipcode from monrovia_zipcodes table
// It also sets the Cookies zone and zipcode for use in populating 
// HTML Values for the Users Cold Zone box

function getZone(postalCode){
	 
	jQuery.post(ajaxurl, { action: 'get_cold_zone', zipcode: postalCode }, function( data ) {
			data = jQuery.parseJSON(data);
			var zipcode = data.zipcode;
			var zone = data.cold_zone;
			
			// Update the HTML
			jQuery('.zone-box span').html(zone);
			jQuery('.zipcode span').html(zipcode);
			
			// Set the Cookies
			setCookie('zip_code', zipcode, 365);
			setCookie('cold_zone', zone, 365);
			
			jQuery('.widget .ajax-loader').hide();
		}); 
		

	}
		
/* Get Maxmind Zipcode from IP address
   Use the maximind API to grab the zipcode 
   if one is not currently availiable via a 
   cookie or a $POST Variable
 */
 
function useMaximind(){
	var onSuccess = function(location){
		
	   var string = JSON.stringify(location, undefined, 4);
	   var data = JSON.parse(string);
	   var postalCode = data.postal.code;
	   var city = data.city.names.en;
	  // var state = data.state.names.en;
	   //console.log(state); // TODO: Create a Cookie for the City
	   //setCookie('city', city, 365);
	   //setCookie('state', state, 365);
	   getZone(postalCode);
	   
	}
	
	// IP look up unsucessful
	var onError = function(error){
		// The error
		//console.log(JSON.stringify(error, undefined, 4));
	}
	
	// Call the GeoIP2 API
	geoip2.city(onSuccess, onError);
}


// This does an ajax call to functions.php to subscribe a user
// to iContact Plant Savvy Newsletter
// 
// @variables - email, zip, coldzone

function subscribeNewsletter(email, zip, coldzone){
	 
	jQuery.post(ajaxurl, { action: 'addSubscription', zipcode: zip, newsletter: 'true', email: email, coldzone: coldzone }, function( data ) {
			data = jQuery.parseJSON(data);
			var msg;
			var display = jQuery('#signupMessage');
			
			if(data.result == 'error'){
				msg = '<strong>ERROR:</strong> You cannot be added at this time. Please try again later.';
				display.addClass('error');
			} else {
				msg = '<strong>SUCCESS:</strong> You have been successfully signed up!';
				display.removeClass('error');
			}
			//Hide the spinner
			jQuery('.newsletter.ajax-loader').hide();
			// Update the HTML with a messsage
			display.html(msg).show();

		}); 
	

}
		

	
/*** Javascript Cookies Functions ****/
	// Function to set a Cookie
	function setCookie(c_name,value,exdays)
	{
		var exdate=new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
		var domain = location.host;
		domain = domain.replace(/^www\./, "");
		document.cookie=c_name + "=" + c_value+"; path=/; domain=."+domain;
	}

	// Function to Retrieve a Cookie
	function getCookie(c_name)
	{
		var i,x,y,ARRcookies=document.cookie.split(";");
		for (i=0;i<ARRcookies.length;i++)
		{
  		x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
  		y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
  		x=x.replace(/^\s+|\s+$/g,"");
  			if (x==c_name)
    		{
    			return unescape(y);
    		}
  		}
	} 
	
	function eraseCookie(name) {
    setCookie(name,"",-1);
}

/********  Rettrieve URL GET varibles *****/
function GetUrlValue(VarSearch){
    var SearchString = window.location.search.substring(1);
    var VariableArray = SearchString.split('&');
    for(var i = 0; i < VariableArray.length; i++){
        var KeyValuePair = VariableArray[i].split('=');
        if(KeyValuePair[0] == VarSearch){
            return KeyValuePair[1];
        }
    }
}

/** Function to validate a field

	@paramenter el -jQuery object
	
**/
function checkField(el){
		var error = 0;
		var emailcheck = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/; // Variable for email format
		var value = el.val();
		console.log(value);
		var req = el.attr('data-req');
		var type = jQuery('input[name="type"]').val();
		if(value.length < 1){
			error = 1;
		}
		
		// If email field, check for valid email format
		if(el.is('#email')){
			if(!emailcheck.test(value)){
				error = 1;
			}
		}
		
		// If its a Radio Field with multi options, make sure ONE of
		// the options is selected.
		if(req = 'multi'){
			jQuery('.form-item.'+type).each( function(){
				if(jQuery(this).find('input').val().length < 1){
					//jQuery(this).addClass('error');
					//error = 1;
				} else {
					//jQuery(this).removeClass('error');
				}
			});
		}
		
		
		
		if(error == 1){
			el.parent('.form-item').removeClass('success').addClass('error');
		} else {
			el.parent('.form-item').removeClass('error').addClass('success');
		}
		
		return error;
}

/** Function to check a Form for errors on Submit **/
function checkForm(form){
	jQuery('input[data-req="true"]').each( function(){
		 checkField(jQuery(this));
	});
	//If any field has an error class, then return false
	var errors = form.find('.form-item.error');
	
	if(errors.length>0){
		jQuery('html, body').animate({scrollTop : 0},800);
		jQuery('.global-message').addClass('error').html('Please correct the errors below.').show();
		return false;
	} else {
		jQuery('.global-message').removeClass('error').html('').hide();
		return true;
	}
}

/** Function to Load Plants based on Users Cold Zone
	
	@variable coldZone - integer, boolean - set to false if unknown
	@variable zipCode - integer
	@variable query - string -  the search term
	@variable column -  string -  the column in the plant table to run the search on
	@variable number -  integer - amount of results to return
	
**/
	
function loadPlantCollection(coldZone, zipCode, query, column, number, callback ){
	
	jQuery.post(ajaxurl, { action: 'getPlantCollection', coldzone: coldZone, zip: zipCode, query: query, column: column, number: number }, function( data ) {
		data = jQuery.parseJSON(data);
		var zipcode = data.zipcode;
		var zone = data.cold_zone;
		var city = data.city;
		var state = data.state;
		var html = data.html;
		
		// Update the HTML
		jQuery('.zone-box span').html(zone);
		jQuery('.zipcode span').html(zipcode);
		jQuery('#location').html(city+', '+state+' '+zipcode);
		jQuery('#query-results').html(html);
		
		callback();
		// Set the Cookies
		setCookie('zip_code', zipcode, 365);
		setCookie('cold_zone', zone, 365);
		setCookie('city', city, 365);
	    setCookie('state', state, 365);
		
		//jQuery('.widget .ajax-loader').hide();
	}); 	
}

/** Get the screen width, add a class */
function getScreenWidth(){
	var width = jQuery('body').width();
	// Add class mobile to body 
	if(width < 767){
		jQuery('body').addClass('mobile');
	} else {
		jQuery('body').removeClass('mobile');
	}
}

function setHeightSlide() {
	if (jQuery(window).width() < 980) {
		if (jQuery(window).width() > 767) {
			var slider_h = 440;
			jQuery('.home .slide-wrap.home-slider, .home-slider .item .item-wrap').css("height", slider_h + "px");
		} else {
			var feature_h = (jQuery(window).width() * 220) / 390;
			jQuery('.home-slider .item .feature-wrap img').css("height", feature_h+"px");
			jQuery('.home .slide-wrap.home-slider, .home-slider .item .item-wrap').css("height", "auto");
		}
	} else {
		jQuery('.home .slide-wrap.home-slider, .home-slider .item .item-wrap').css("height", "440px");
	}
}

function ftGoalTag56267(){
var ftRand = Math.random() + "";
var num = ftRand * 1000000000000000000;
var ftGoalTagPix56267 = new Image();
ftGoalTagPix56267.src = "http://servedby.flashtalking.com/spot/8/7439;56267;5972/?spotName=_Shop_action&cachebuster="+num;
}
function ftGoalTag56268(){
var ftRand = Math.random() + "";
var num = ftRand * 1000000000000000000;
var ftGoalTagPix56268 = new Image();
ftGoalTagPix56268.src = "http://servedby.flashtalking.com/spot/8/7439;56268;5972/?spotName=Garden_Center_action&cachebuster="+num;
}

function ftGoalTag56265(){
var ftRand = Math.random() + "";
var num = ftRand * 1000000000000000000;
var ftGoalTagPix56265 = new Image();
ftGoalTagPix56265.src = "http://servedby.flashtalking.com/spot/8/7439;56265;5972/?spotName=Search_action&cachebuster="+num;
}
jQuery(document).ready( function($){
	setHeightSlide();
	jQuery( window ).resize(function() {
		setHeightSlide();
	});
	
	// jQuery('li.nav-garden-center a').click(function(event) {
		// ftGoalTag56268();
	// }); 
	
	jQuery('li.nav-shop-menu a').click(function(event){
		ftGoalTag56267();
	});
	
	jQuery('form.search .search-submit').click(function(event){
		ftGoalTag56265();
	});
});

