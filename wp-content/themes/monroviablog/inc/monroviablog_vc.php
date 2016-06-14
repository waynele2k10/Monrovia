<?php

		/*  TESTIMONIALS                                            (CONTENT)
		/*-----------------------------------------------------------------*/

			vc_map(array(
	    		'name'                    => "Slides Container",
	    		'base'                    => "mb_slides",
	    		"as_parent"               => array('only' => 'mb_slide'),
	    		"content_element"         => true,
	    		'class'                   => 'mb_slides',
	    		'show_settings_on_create' => true,
	    		"js_view"                 => 'VcColumnView',
	    		'category'                => __("Content",'monroviablog'),
	    		'is_container'            => true,
	    		'icon'                    => 'icon-slides',
	    		'description'             => 'Add slides carousel with this element',
	    		'params'                  => array(
	    		)
	    	));

			vc_map(array(
	    		'name'                    => "Slide",
	    		'base'                    => "mb_slide",
	    		"as_child"                => array('only' => 'mb_slides'),
	    		"content_element"         => true,
	    		'params'                  => array(
	    			array(
						"type"       => "attach_image",
						"class"      => "",
						"heading"    => "Upload slide image",
						"param_name" => "img",
						"value"      => ""
					),
					array(
						"type"       => "textarea",
						"param_name" => "content",
						"value"      => 'Title'
					)
	    		)
	    	));

    if ( class_exists( 'WPBakeryShortCodesContainer' ) ) {
	    class WPBakeryShortCode_Mb_Slides extends WPBakeryShortCodesContainer {}
	}

	if ( class_exists( 'WPBakeryShortCode' ) ) {
	    class WPBakeryShortCode_Mb_Slide extends WPBakeryShortCode {}
	}

?>