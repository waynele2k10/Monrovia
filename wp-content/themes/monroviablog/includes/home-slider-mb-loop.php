
<?php 
	$sliders = get_group('home_slide');
	foreach($sliders as $slider) {
?>
<div class="item" >
	<div class="bg-slider">
		<img src="<?php echo $slider['home_slide_background_image'][1]['original']; ?>" width="100%" height="440" class="bg-slider-img" alt="monrovia slider" />
	</div>
	<div class="item-wrap feature-wrap">
		<div class="feature">
			<img src="<?php echo $slider['home_slide_feature_desktop_image'][1]['original']; ?>" width="100%" height="365" class="image" alt="<?php echo $slider['home_slide_feature_title'][1]?>" />
			<div class="content">
				<h2 class="content-title"><?php echo $slider['home_slide_feature_title'][1]?></h2>
				<p class="content-message"><?php echo $slider['home_slide_feature_message'][1]?></p>
				<a class="btn-link" href="<?php echo $slider['home_slide_feature_button_link'][1]?>" ><?php echo $slider['home_slide_feature_button_label'][1]?></a>
			</div>
		</div>
	</div>
</div>
<div class="item" >
	<div class="bg-slider">
		<img src="<?php echo $slider['home_slide_background_image'][1]['original']; ?>" width="100%" height="440" class="bg-slider-img" alt="monrovia slider" />
	</div>
	<div class="item-wrap">
		<div class="feature">
			<img src="<?php echo $slider['home_slide_top_right_desktop_image'][1]['original']; ?>" width="390" height="220" class="image" alt="<?php echo $slider['home_slide_top_right_title'][1]?>" />
			<div class="content">
				<h2 class="content-title"><?php echo $slider['home_slide_top_right_title'][1]?></h2>
				<p class="content-message"><?php echo $slider['home_slide_top_right_message'][1]?></p>
				<a class="btn-link" href="<?php echo $slider['home_slide_top_right_button_link'][1]?>" ><?php echo $slider['home_slide_top_right_button_label'][1]?></a>
			</div>
		</div>
	</div>
</div>
<div class="item" >
	<div class="bg-slider">
		<img src="<?php echo $slider['home_slide_background_image'][1]['original']; ?>" width="100%" height="440" class="bg-slider-img" alt="monrovia slider" />
	</div>
	<div class="item-wrap">
		<div class="feature">
			<img src="<?php echo $slider['home_slide_bottom_right_desktop_image'][1]['original']; ?>" width="390" height="220" class="image" alt="<?php echo $slider['home_slide_bottom_right_title'][1]?>" />
			<div class="content">
				<h2 class="content-title"><?php echo $slider['home_slide_bottom_right_title'][1]?></h2>
				<p class="content-message"><?php echo $slider['home_slide_bottom_right_message'][1]?></p>
				<a class="btn-link" href="<?php echo $slider['home_slide_bottom_right_button_link'][1]?>" ><?php echo $slider['home_slide_bottom_right_button_label'][1]?></a>
			</div>
		</div>
	</div>
</div>
<?php } ?>