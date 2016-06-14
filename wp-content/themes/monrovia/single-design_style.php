<?php 
	// Page template for the Design style
	// Contains a Slideshow at the top of the page with Thumbnails
	
get_header(); ?>
	
    <div class="content_wrapper clear">
		<section role="main" class="main">
        <?php the_breadcrumb($post->ID); //Print the Breadcrumb ?>
			<h1><?php the_title(); ?></h1>
			<?php if (have_posts()): while (have_posts()) : the_post(); ?>
			<!-- article -->
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
            	<div class="slide-wrap">
            		<div id="cycle-one" class="cycle-slideshow"
            			data-cycle-slides="> img"
    					data-cycle-fx="scrollHorz"
    					data-cycle-pause-on-hover="true"
                        data-cycle-timeout=0
    					data-cycle-speed="1000"
                		data-cycle-swipe=true
                		data-cycle-pager=".cycle-pager"
    					data-cycle-pager-template=""
					>
            		<!-- prev/next links -->
   					<div class="cycle-prev"></div>
    				<div class="cycle-next"></div>
        		<?php $slides = get_group('slides'); 
						foreach($slides as $slide){
							echo "<img src='".$slide['slides_image'][1]['original']."' />";
						} ?>
        	        </div><!-- end cycle-slideshow -->
                    <div id="cycle-two" class="cycle-slideshow"
        			data-cycle-slides="> img"
        			data-cycle-timeout=0
        			data-cycle-fx=carousel
        			data-cycle-carousel-visible=4
        			data-allow-wrap=false
                    data-cycle-carousel-slide-dimension='92px'
        			>
                <?php foreach($slides as $slide){
					echo "<img src='".$slide['slides_image'][1]['thumb']."' />";
					} ?>
                    </div><!-- end cycle-2 -->
                    </div><!-- end slide-wrap -->
				<?php the_content(); ?>
			</article>
			<!-- /article -->
			<?php endwhile; endif; ?>
		</section>
        <?php get_sidebar('right'); ?>
	</div><!-- end content_wrapper -->
    
<?php get_footer(); ?>

<script>
jQuery(document).ready(function($){

// Connect the two slideshow to get the Carousel Effect
var slideshows = $('.cycle-slideshow').on('cycle-next cycle-prev', function(e, opts) {
    // advance the other slideshow
    slideshows.not(this).cycle('goto', opts.currSlide);
});

$('#cycle-two .cycle-slide').click(function(){
    var index = $('#cycle-two').data('cycle.API').getSlideIndex(this);
    slideshows.cycle('goto', index);
});

});

</script>