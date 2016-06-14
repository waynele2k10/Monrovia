<?php /* Template Name: Newsletter */
get_header(); ?>
<script>
    /*jQuery(function($) {
       $( ".tabs" ).tabs();
    }); */
    
</script>
<script>
    jQuery(document).ready( function(){
	
	var api;
    jQuery(".faq a").overlay({
 
        mask: '#000',
		effect: 'default',
		fixed: false,
		left: 'center',
		top: '50px',
		target: '#overlay',
 
        onBeforeLoad: function() {
 
            // grab wrapper element inside content
            var wrap = this.getOverlay().find(".contentWrap");
 
            // load the page specified in the trigger
            wrap.load(this.getTrigger().attr("href")+'?load=true');
        },
		onBeforeClose: function(){
			//Remoev the markup so extraneous styles dont remain
			this.getOverlay().find(".contentWrap").html('');
			jQuery('body').css('height','auto');
		}
 
    });
	
	jQuery(window).on('hashchange', function() {
		// Need to dig into the overlay API to get the overlay to 
		// stay up. Not sure if its worth it.
  		deepLink(true);
	});
	
	deepLink(false);
	
	// Deeplink to a Newsletter of any year
	function deepLink(closeIt){
		//Close it first?
		if(closeIt){
			//api.close();
		} else {
			//Check for a hastag
			var hashTag = window.location.hash.substr(1);
			if(hashTag != 'undefined' && hashTag != ''){
				// Find the link by the title
				jQuery('a[title="'+hashTag+'"]').trigger('click');	
			}
		}
	}
	
	jQuery( window ).resize(function() {
		setHeightBody();
	});
	
	function setHeightBody() {
		if (jQuery('#overlay').css('display') == "block") {
			var overlay_h = jQuery('#overlay').outerHeight(true) + 60;
			var body_h = jQuery(document).outerHeight(true);
			console.log(overlay_h + ' : ' + body_h);
			jQuery('body').css('height', overlay_h+'px');
		}
	}

 });
</script>

    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
            <!-- article -->
            <?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php the_content(); ?>
                <!--<p id="signupMessage" class="message"></p>
               	<div class="clear plant-savvy-signup">
                   <span class="left">Sign up for our newsletter today!</span>
                   <div class="form-item left hasLabel">
                        <label for="nemail" class="block-label">Enter your email address</label>
                        <input type="text" name="nemail" id="nemail" class="checkKeypress checkBlur">	            
                   </div>
                   <button class="search-submit left" role="button" type="submit" name="Submit" value="Submit" id="newsletter-signup">Sign Up</button>
                   <span class="newsletter ajax-loader"></span>
               </div><!-- end clear -->
			</article>
            <?php endwhile; endif; ?>
			<!-- /article -->
			<?php $transient_name = 'newsletter';
				$newsletter = monrovia_get_cache( $transient_name );
				if ( false === $newsletter ) :
				ob_start();	?>
            <div class="accordian-wrap">
            	<ul class="accordian clear">
            <?php 
				// Get Current Year
			$year = $currentYear = date('Y');
			//Create an array of years for tabs
			for($year;$year>=2010;$year--){
				$years[] = $year;
			}
		
			//Loop through the years and print out Press Release for that year
			$currentYear = date('Y');
			foreach($years as $year){
				
				$block = 'none';
				$class='';
				if($currentYear == $year){
					$block = 'block';
					$class='open';
				}
				echo "<li><a href='#".$year."' class='$class'>".$year."</a>";

				//Set up the custom query
				$query =  "posts_per_page=-1&order=DESC&post_type=newsletter&year=$year&post_status=publish";
				$custom_query = new WP_Query();
				$custom_query->query($query); 
				?>
				<div id="<?php echo $year ?>" class="accordianTarget" style="display:<?php echo $block; ?>">
                <h2><?php echo $year; ?> Newsletters</h2>
                <?php if($custom_query->found_posts < 1) echo "<p>Coming Soon!</p>"; ?>
				<?php if($custom_query->have_posts()) : $post = $posts[0]; while ($custom_query->have_posts()) : $custom_query->the_post();  ?>
                    <div class="faq">
                    	<?php the_content(); ?>
                    </div><!-- end faq -->
                <?php endwhile; endif; wp_reset_query(); wp_reset_postdata();?>
                </div></li>
             <?php } //End foreach ?>
				</ul>
            </div><!-- end tabs -->
			 <?php 
				 $newsletter = ob_get_clean();
				 monrovia_set_cache( $transient_name, $newsletter, MONROVIA_TRANSIENT_EXPIRE );
			 endif;
             echo $newsletter; ?>
		</section>
        <?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    <div id="overlay" class="apple_overlay">
    	<a class="close"><i class="fa fa-times-circle"></i></a>
    	<div class="contentWrap"></div><!-- end contentWrap -->
    </div><!-- end overlay -->
    
        <script>
		jQuery(document).ready( function(){
			//Populate hidden fields of MailChimp form
			var cZone = getCookie('cold_zone');
			var zipCode = getCookie('zip_code');
			jQuery('#zipcode').val(zipCode);
			jQuery('#coldzone').val(cZone);
		});
	</script>
<?php get_footer(); ?>
