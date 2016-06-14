<?php
## ADDS A FUNCTION TO SHOW THE IMAGE MAPPER AT THE FRONT END
########################

class floorplan_frontend {

    function __construct() {

        function load_frontend_scripts_image_mapper() {
            // wp_enqueue_script( 'fancybox', plugins_url( '/image-mapper/fancybox/jquery.fancybox-1.3.5.js'), array( 'jquery' ));
            // wp_enqueue_script( 'fancyboxgallery', plugins_url( '/image-mapper/js/gallery_with_noConflict.js'), array( 'jquery' ));
            // wp_enqueue_style( 'css_fancybox', plugins_url('/image-mapper/fancybox/jquery.fancybox-1.3.5.pack.css'));
            // wp_enqueue_style ( 'css.bootstrap-css', plugins_url ( '/image-mapper/css/bootstrap/css.bootstrap.css' ) );
            wp_enqueue_style('custom.css', plugins_url('/image-mapper/css/custom.css'));
        }

        add_action('wp_enqueue_scripts', 'load_frontend_scripts_image_mapper');

        function display_floorplan_frontend($atts) {
            if ($atts != '') {
                $post_id = $atts['post_id'];
            } elseif (get_post_type() == 'post') {
                $post_id = get_the_ID();
            }
            $attachment_id = get_post_meta($post_id, 'floorplan_image_attachment_id', true);

            if ($attachment_id == true) {
                $imgurl = wp_get_attachment_url($attachment_id);
            }

            $mark_count = get_post_meta($post_id, 'mark_count', true);
            ?>
            <style type="text/css">
                #floorplan_div {
                    width:750px;
                    position: relative;
                }
            </style>

            <div style="clear: both;"></div>

            <div id="floorplan_div" style="position: relative;width:auto;">
                <img class="floorplan_image" src="<?php if (isset($imgurl)) {
                echo $imgurl;
            } ?>">	
            <?php
            for ($i = 1; $i <= $mark_count; $i++) {
                $mark_image = get_post_meta($post_id, '_mark_image_' . $i, true);
                $position = maybe_unserialize(get_post_meta($post_id, '_mark_image_' . $i . '_position', true));
                $title = maybe_unserialize(get_post_meta($post_id, '_mark_image_' . $i . '_title', true));
                $des = maybe_unserialize(get_post_meta($post_id, '_mark_image_' . $i . '_description', true));
                $link = maybe_unserialize(get_post_meta($post_id, '_mark_image_' . $i . '_link', true));

                $mark_image_left = $position["left"];
                $mark_image_top = $position["top"];
                $num = (int) $mark_image_left;

                $floorplan_camera_options = get_post_meta($post_id, 'floorplan_camera_options', true);

                if (!empty($mark_image)) {
                    ?>      

                        <div id="<?php echo $post_id, '_mark_image_' . $i; ?>" class="mark-feature" rel="map_gallery" style="<?php echo get_icon_mapper_html(); ?>;  <?php if ($floorplan_camera_options == 'off') { ?>opacity: 0; width:60px; height:33px;<?php } ?> position: absolute; top:<?php echo $mark_image_top; ?>; left:<?php echo $mark_image_left; ?>; " >
                        <?php
                        if ($num > 50) {
                            $css = "style='position:absolute;top:50%;right:100%;'";
                        } else {
                            $css = "style='position:absolute;top:50%;left:100%'";
                        }
                        ?>
                            <div id="info-img" class="info-img" <?php echo $css ?>;>
                                <img src="<?php echo site_url() . $mark_image; ?>" />
                                <div class="wrap-content">
                                    <h4><?php echo $title; ?></h4>
                                    <p><?php echo $des; ?></p>
                                    <a target="_blank" href="<?php echo $link; ?>">More info</a>
                                </div>
                            </div>
                        </div>

                    <?php
                }
            }
            ?>
            </div>
                <?php
            }

            add_shortcode('FLOORPLAN', 'display_floorplan_frontend');
        }

    }
    