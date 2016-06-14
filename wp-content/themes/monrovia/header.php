<?php  
// Plant needs access to class_plant before other pages do
if ( !is_page_template('plant.php') ) {
$upload_dir = wp_upload_dir();
$GLOBALS['view_all_max'] = 500;
include('includes/classes/class_plant.php');
}

?>

<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
        <!-- dns prefetch -->
		<link href="//www.google-analytics.com" rel="dns-prefetch">
        <link href="//shop.monrovia.com" rel="dns-prefetch">
         <!-- meta -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta content="width=device-width, maximum-scale=1, user-scalable=1" name="viewport">
        <meta name="p:domain_verify" content="a179bcc297a73a146418ce2d8b4c83b2"/>
        <meta name="google-site-verification" content="EOWZguHWCAu9p7glwLpSUFLcvSEMhhtrDvRIqQKuuFE" />
        <!--<meta http-equiv="Cache-control" content="public">-->
        
	<!-- Force facebook share image here -->
	<meta name="og:image" content="<?php echo get_template_directory_uri(); ?>/img/FB_image.jpg"/>

        <?php if ( is_page_template('plant.php') ) {
			//Use Specialized Meta Tags if its a Plant Detail Page ?>
            
				<?php  //If the plant is in-active, add a no follow tag and redirect to plant search page 
				$allowValues = array(1,2,3,4,6);
				$pStatus = $record->info['release_status_id'];
                   // if(strpos($record->determine_where_active(),',site,')===false){ ?>
                  <?php if(!in_array($pStatus,$allowValues)){ ?>
                    <meta name="robots" content="no-follow, no-index, no-archive" />
                    <script>
                        window.location.href = "/plant-catalog/?msg=notactive";
                    </script>
                    
                <?php } ?>
            	<!-- title -->
            	<title><?php echo $page_title;?></title>
                <!-- Define Canonical -->
       			<link rel="canonical" href="<?php echo $record->info['details_url'];?>/" />
         		<!-- meta -->
                <meta name="description" content="<?php echo $page_meta_description;?>" />
                <meta name="keywords" content="<?php echo $page_meta_keywords;?>"  />
                <meta name="og:type" content="website" />
                <meta name="og:image" content="<?php echo $page_meta_facebook_thumbnail;?>"/>
                <meta name="og:title" content="<?php echo $page_title;?>" />
                <meta name="og:description" content="<?php echo $page_meta_description;?>" />
                <meta name="og:url" content="<?php echo $record->info['details_url'];?>/" />
                <meta itemprop="image" content="<?php echo $page_meta_facebook_thumbnail;?>"/>
			 
		<?php } else {
			// Any other page ?>
				
                <!-- title -->
                <title><?php wp_title(''); ?><?php if(wp_title('', false)) { echo ' :'; } ?> <?php bloginfo('name'); ?></title>
                
        <?php } // End if ?>
		
		<!-- icons -->
		<link href="<?php echo get_template_directory_uri(); ?>/img/icons/favicon.ico" rel="shortcut icon">
		<link href="<?php echo get_template_directory_uri(); ?>/img/icons/touch.png" rel="apple-touch-icon-precomposed">
        
		<!-- css + javascript -->
		<?php wp_head(); ?>
		<script>
		!function(){
			// configure legacy, retina, touch requirements @ conditionizr.com
			conditionizr()
		}()
		</script>
        
         <!-- geoIP2 -->
        <script src="//js.maxmind.com/js/apis/geoip2/v2.0/geoip2.js" type="text/javascript" async></script>
	</head>
	<body <?php body_class(); ?>>
    <!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-S59B"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-S59B');</script>
<!-- End Google Tag Manager -->

		<!-- header -->
			<header class="header clear" role="banner">
					<!-- logo -->
					<div class="inner clear">
						<a href="<?php echo home_url(); ?>" class="logo left">
							<!-- svg logo - toddmotto.com/mastering-svg-use-for-a-retina-web-fallbacks-with-png-script -->
							<img src="<?php echo get_template_directory_uri(); ?>/img/logo.png" alt="Monrovia - Horticultural Craftsman since 1926" class="logo-img">
						</a>
                        <div class="navigation-secondary right hideMobile">
							<?php include( 'includes/nav-secondary.php' ); ?>
                        </div>
                        <div class="mobile-header showMobile right clear">
                        	<a href="javascript:void(0);" class="showMobile left search-control" onClick="jQuery('body').toggleClass('search-open');"></a>
                            <a href="javascript:void(0);" class="showMobile left nav-control" onClick="jQuery('body').toggleClass('nav-open');"></a>
                        </div><!-- end mobile header -->
					</div>
					<!-- /inner -->
					<!-- nav -->
					<nav class="nav clear" role="navigation">
						<?php monrovia_nav(); ?>
                        <div class="savvy-social clear showMobile">
                            <a target="_blank" href="http://www.facebook.com/pages/Monrovia/102411039815423?v=wall&amp;ref=sgm" title="Facebook"></a>
                            <a target="_blank" href="http://twitter.com/MonroviaPlants/" title="Twitter"></a>
                            <a target="_blank" href="https://www.pinterest.com/monroviaplants/" title="Pinterest"></a>
                            <a target="_blank" href="http://instagram.com/MonroviaNursery#" title="Instagram"></a>
                            <a target="_blank" href="https://plus.google.com/106439322773521086880/" title="Google+"></a>
                        </div><!-- end show mobile -->
					</nav>
					<!-- /nav -->
                    <div class="mobile-search showMobile">
                    	<!-- search -->
                        <form class="search right" method="get" action="<?php echo home_url(); ?>" role="search">
                            <div class="form-item">
                                <label for="sm">Search</label>
                                <input class="search-input" type="text" name="s" id="sm" autocomplete="off">
                                <button class="search-submit" type="submit" role="button">Go</button>
                            </div><!-- end form item -->
                        </form>
                        <!-- /search -->
                    </div><!-- end mobile search -->
            </header>
		    <!-- /header -->
			
			<?php
			if ( is_front_page() && is_home() ) :
			$query =  array( 'post_type' => 'home_page' );
			$custom_query = new WP_Query($query);
			if($custom_query->have_posts()): 
			?>
				<div class="slide-wrap home-slider" style=>
					<div class="cycle-slideshow"
						data-cycle-fx="scrollHorz"
						data-cycle-pause-on-hover="true"
						data-cycle-speed="750"
						data-cycle-manual-speed="250"
						data-cycle-timeout="5000"
						data-cycle-swipe=true
						data-cycle-pager=".cycle-pager-dt"
						data-cycle-slides=".item"
						data-cycle-pager-template="<span></span>"
					>
						<!-- prev/next links 
						<div class="cycle-prev"></div>
						<div class="cycle-next"></div> -->
						<?php while($custom_query->have_posts()): $custom_query->the_post(); ?>
							<?php include('includes/home-slider-loop.php'); ?>
						<?php endwhile; ?>
						<div class="cycle-pager cycle-pager-dt"></div>
					</div>
				</div>
				<div class="slide-wrap home-slider mobile" style=>
					<div class="cycle-slideshow"
						data-cycle-fx="scrollHorz"
						data-cycle-pause-on-hover="true"
						data-cycle-speed="750"
						data-cycle-manual-speed="250"
						data-cycle-timeout="5000"
						data-cycle-swipe=true
						data-cycle-pager=".cycle-pager-mb"
						data-cycle-slides=".item"
						data-cycle-pager-template="<span></span>"
					>
						<?php while($custom_query->have_posts()): $custom_query->the_post(); ?>
							<?php include('includes/home-slider-mb-loop.php'); ?>
						<?php endwhile; ?>
						<div class="cycle-pager cycle-pager-mb"></div>
					</div>
				</div>
			<?php endif; ?>
			<?php endif; ?>
			<!-- wrapper -->
			<div class="wrapper">
            <?php if(!is_front_page() && 'spokesperson' != get_post_type() && 'plant_collection' != get_post_type()){ ?>
                    <!-- Promo Banner -->
                    <?php getPromoBanner(); ?>
            <?php } ?>
            <?php //print_r($wp_query); ?>



            