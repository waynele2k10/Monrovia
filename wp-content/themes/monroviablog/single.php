<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    echo '<h1>Forbidden</h1>';
    exit();
} get_header();
?>

<div class="row">
    <div class="col-sm-12 col-md-12">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
				<?php if (has_post_video( $post->ID )) { ?>
					<?php echo get_the_post_video( $post->ID, array(980,480) ); ?>
                <?php } elseif (get_post_meta($post->ID, 'floorplan_image_attachment_id', true)) { ?>
                    <?php echo do_shortcode('[FLOORPLAN post_id=' . $post->ID . ']'); ?>
					<?php $yellow_value = get_post_meta($post->ID, "yellow_label", true); ?>
					<?php if (! empty( $yellow_value )) : ?>
					<div class="category"><span><?php echo $yellow_value ?></span></div>
					<?php endif; ?>
                    <?php
                } elseif (has_post_thumbnail()) {
                    // Get attached file guid
                    $att = get_post_meta(get_the_ID(), '_thumbnail_id', true);
                    $thumb = get_post($att);
                    if (is_object($thumb)) {
                        $att_ID = $thumb->ID;
                        $att_url = $thumb->guid;
                    } else {
                        $att_ID = $post->ID;
                        $att_url = $post->guid;
                    }
                    $att_title = (!empty(get_post($att_ID)->post_excerpt)) ? get_post($att_ID)->post_excerpt : get_the_title($att_ID);
                    ?>
                    <div class="clearfix text-center">
                        <a class="thickbox" href="<?php echo $att_url; ?>" title="<?php echo $att_title; ?>">
                            <?php echo get_the_post_thumbnail(get_the_ID(), 'large', array()); ?>
							<?php $yellow_value = get_post_meta($post->ID, "yellow_label", true); ?>
							<?php if (! empty( $yellow_value )) : ?>
							<div class="category"><span><?php echo $yellow_value ?></span></div>
							<?php endif; ?>
                        </a>
                    </div>
                <?php }; ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('well'); ?>>
                    <div class="header-container">
                        <div class="row">
                            <div class="col-md-8"><h1><?php the_title(); ?></h1></div>
                            <div class="col-md-4">
                                <?php
                                $pin_url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

                                if (has_post_thumbnail()) {
									$pin_media = wp_get_attachment_url(get_post_thumbnail_id());
                                } else {
                                    $pin_media = 'http://' . $_SERVER['SERVER_NAME'] . '/wp-content/themes/monrovia/img/FB_image.jpg';
                                }
                                ?>
                                <div class="share-this addthis_toolbox clear hideMobile">
                                    <div>Share Post</div>
                                    <a class="addthis_button_facebook"><img src="<?php echo get_template_directory_uri(); ?>/img/spacer.gif" title="Facebook" /></a>
                                    <a class="addthis_button_twitter"><img src="<?php echo get_template_directory_uri(); ?>/img/spacer.gif" title="Twitter" /></a>
                                    <a class="addthis_button_pinterest_share" pi:pinit:url="<?php echo $pin_url ?>" pi:pinit:media="<?php echo $pin_media ?>"><img src="<?php echo get_template_directory_uri(); ?>/img/spacer.gif" title="Pinterest"/></a>
                                    <a class="addthis_button_email"><img src="<?php echo get_template_directory_uri(); ?>/img/spacer.gif" title="Email" /></a>
                                </div><!-- end share-this -->
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <p class="date">
									<?php
									$icon_name = get_post_meta(get_the_ID(), "icon_name", true);
									$icon_value = get_post_meta(get_the_ID(), "icon_value", true);
									if (! empty( $icon_value )) :
									?>
									<span class="icon-label"><?php echo get_icon_label($icon_value, $icon_name);?></span>
									<?php endif; ?>
                                    <a href="<?php echo get_author_posts_url(get_the_author_meta('ID'), get_the_author_meta('user_nicename')); ?>"><?php the_author(); ?></a> | <a href="<?php echo get_day_link(get_the_time('Y'), get_the_time('m'), get_the_time('d')); ?>" title="date"><?php the_time('F j, Y') ?></a></p>
                            </div>
                        </div>
                    </div>
                    <section>
                        <?php the_content(); ?>
                    </section>

					<?php get_template_part('partials/post-related-plant'); ?>
                    <?php get_template_part('partials/article-related'); ?>
                    <?php comments_template('', true); ?>
                </article>
                <?php get_template_part('partials/post-related'); ?>
            <?php endwhile; ?>
        <?php else : ?>
            <?php get_template_part('partials/nothing-found'); ?>
        <?php endif; ?>
    </div>
</div><!-- .row -->

<?php get_footer(); ?>