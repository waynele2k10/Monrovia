<?php // Exit if accessed directly
if (!defined('ABSPATH')) {echo '<h1>Forbidden</h1>'; exit();} ?>

<ul class="breadcrumb">
    <li><a href="<?php echo get_site_url(); ?>" title="<?php _e('Home','monroviablog'); ?>"><i class="icon-home"></i>&nbsp;<?php _e('Home', 'monroviablog'); ?></a></li>
    <?php if (is_single() OR is_page()) : ?>
        <?php
        // get ancestors
        $parents = array_reverse (get_ancestors(get_the_ID(), 'page'));
        if (!empty($parents)) :
            foreach ($parents as $p) :
                echo '<li><a href="'.get_permalink($p).'">'.get_the_title($p).'</a></li>';
            endforeach;
        endif;
        ?>
    <li><?php the_title(); ?></li>
    <?php elseif (is_author()) : ?>
        <li><?php _e('About the author', 'monroviablog'); ?></li>
    <?php elseif (is_search()) : ?>
        <li><?php _e('Search results', 'monroviablog'); ?></li>
    <?php elseif (is_category()) :
        $category = get_queried_object();
        if ($category->parent)
            echo '<li><a href="'.get_category_link($category->parent).'" title="'.get_cat_name($category->parent).'">'.get_cat_name($category->parent).'</a></li>';
        echo '<li>'.$category->name.'</li>';
        ?>
    <?php elseif (is_tag()) : ?>
        <li>
            <?php _e('Browsing posts tagged:', 'monroviablog'); ?> <?php $tag = get_queried_object(); echo $tag->name; ?>
        </li>
    <?php elseif (is_archive()) : ?>
        <li>
            <?php _e('Browsing archived posts:', 'monroviablog'); ?>
        </li>
    <?php endif; ?>
</ul>