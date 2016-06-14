<?php
$orig_post = $post;
global $post;
$categories = get_the_category($post->ID);
if ($categories) {
    $category_ids = array();
    foreach ($categories as $individual_category)
        $category_ids[] = $individual_category->term_id;
    $args = array(
        'category__in' => $category_ids,
        'post__not_in' => array($post->ID),
        'posts_per_page' => 3, // Number of related posts that will be shown.
        'ignore_sticky_posts' => 1
    );
    $my_query = new wp_query($args);
    if ($my_query->have_posts()) {
        echo '<div id="related_posts" class="clearfix"><h3>Suggested Posts</h3><ul>';
        while ($my_query->have_posts()) {
            $my_query->the_post();
            ?>
            <li class="col-md-4">
                <div class="relatedthumb">
                    <a href="<? the_permalink() ?>" rel="bookmark" title="<?php the_title(); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php echo get_the_post_thumbnail(get_the_ID(), 'monroviablog-thumbnails', array()); ?>
                        <?php else: ?>
                            <?php echo '<img width="236" height="134" class="attachment-monroviablog-list size-monroviablog-list wp-post-image" src="' . get_bloginfo('stylesheet_directory') . '/img/thumbnail-default.jpg" />'; ?>
                        <?php endif; ?>
                    </a>
                </div>
                <div class="relatedcontent">
                    <?php
                    $icon_name = get_post_meta(get_the_ID(), "icon_name", true);
                    $icon_value = get_post_meta(get_the_ID(), "icon_value", true);
                    if (! empty( $icon_name ) && ! empty( $icon_value )) :
                    ?>
                        <span class="icon-label"><?php echo get_icon_label($icon_value);?><?php echo $icon_name ?></span>
                    <?php endif; ?>
                    <h3><a href="<? the_permalink() ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a></h3>
                    <p class="content-post"><?php echo get_the_excerpt(); ?></p>
                    <p class="date-left"><a href="<?php echo get_author_posts_url(get_the_author_meta('ID'), get_the_author_meta('user_nicename')); ?>"><?php the_author(); ?></a> | <a href="<?php echo get_day_link(get_the_time('Y'), get_the_time('m'), get_the_time('d')); ?>" title="date"><?php the_time('F j, Y') ?></a></p>
                </div>
            </li>
            <?
        }
        echo '</ul></div>';
    }
}
$post = $orig_post;
wp_reset_query();
?>