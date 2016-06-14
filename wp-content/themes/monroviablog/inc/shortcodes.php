<?php

/*  TESTIMONIALS
/*====================================================================*/
	
	function mb_slides($atts, $content = null) {

		extract(shortcode_atts(
			array(
			), $atts)
		);

		static $id_counter = 1;

		$output = "";

		if (isset($color) && !empty($color)) {
			$color = 'style="color:'.$color.';"';
		}

		$output .= '<div id="bxslider-'.$id_counter.'" class="bxslider">';
			$output .= '<ul class="slides">';
				$output .= do_shortcode($content);
			$output .= '</ul>';
		$output .= '</div><div id="bx-pager"></div>';

		$id_counter++;

		return $output;
	}
	add_shortcode('mb_slides', 'mb_slides');

	function mb_slide($atts, $content = null) {

		extract(shortcode_atts(
			array(
				'img'      => '',
				'title'  => ''
			), $atts)
		);

		$output = '';

		if (isset($img) && !empty($img)) {
			$img_ats = wp_get_attachment_image_src($img, 'full');
			$img_full     =  $img_ats[0];
			$img_medium_ats = wp_get_attachment_image_src($img, 'monroviablog-list');
			$img_medium     =  $img_medium_ats[0];
			$img_thumbnail_ats = wp_get_attachment_image_src($img, 'thumbnail');
			$img_thumbnail     =  $img_thumbnail_ats[0];
		}

		$output .= '<li class="item" src-thumbnail="'.$img_thumbnail.'">';
			$output .= '<a class="thickbox" href="'.$img_full.'" title="'.$content.'"><img src="'.$img_medium.'" title="'.$content.'"/></a>';
		$output .= '</li>';

		return $output;
	}
	add_shortcode('mb_slide', 'mb_slide');

?>