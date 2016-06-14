<?php get_header(); ?>
<?php $_post_not_in = array(); ?>
<?php query_posts('orderby=date&order=DESC&meta_key=monrovia_homepage_featured_position&meta_value=main&post_type=post&showposts=1&post_status=publish'); ?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <?php $_post_not_in[] = get_the_ID() ?>
        <div class="row">	
            <div class="col-md-12">
                <div class="featured">
                    <div class="thumbnail-box">
                        <a class="media-object" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php echo get_the_post_thumbnail(get_the_ID(), 'monroviablog-main-feature', array()); ?>
                            <?php else: ?>
                                <?php echo '<img width="644" height="361" class="attachment-monroviablog-list size-monroviablog-list wp-post-image" src="' . get_bloginfo('stylesheet_directory') . '/img/thumbnail-644x361.jpg" />'; ?>
                            <?php endif; ?>
                            <?php $yellow_value = get_post_meta(get_the_ID(), "yellow_label", true); ?>
                            <?php if (!empty($yellow_value)) : ?>
                                <div class="yellow-label"><span><?php echo $yellow_value ?></span></div>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="content-box">
                        <?php
                        $icon_name = get_post_meta(get_the_ID(), "icon_name", true);
                        $icon_value = get_post_meta(get_the_ID(), "icon_value", true);
                        if (!empty($icon_value)) :
                            ?>
                            <span class="icon-label"><?php echo get_icon_label($icon_value,$icon_name);?></span>
                        <?php endif; ?>
                        <h2 class="title"><a href="<?php the_permalink(); ?>" title="outdoor living"><?php the_title(); ?></a></h2>
                        <p class="content"><?php echo get_the_excerpt(); ?></p>
                        <p class="more-info"><a href="<?php echo get_author_posts_url(get_the_author_meta('ID'), get_the_author_meta('user_nicename')); ?>"><?php the_author(); ?></a> | <a href="<?php echo get_day_link(get_the_time('Y'), get_the_time('m'), get_the_time('d')); ?>" title="date"><?php the_time('F j, Y') ?></a></p>
                        <a href="<?php the_permalink(); ?>" class="read-more">Read More</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    endwhile;
endif;
wp_reset_query();
?>
<div class="row">	
    <div class="col-md-12">
        <div class="featured-title">
            <h1 class="title">Featured Posts</h1>
        </div>
    </div>
</div>
<div class="row">	
    <div class="col-md-12">
        <div class="featured-item col-md-12">
            <ul class="row">
                <li class="item">
                    <?php query_posts('orderby=date&order=DESC&meta_key=monrovia_homepage_featured_position&meta_value=left&post_type=post&showposts=1&post_status=publish'); ?>
                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                            <?php $_post_not_in[] = get_the_ID() ?>
                            <a class="media-object" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php echo get_the_post_thumbnail(get_the_ID(), 'monroviablog-child-feature', array()); ?>
                                <?php else: ?>
                                    <?php echo '<img width="309" height="175" class="attachment-monroviablog-list size-monroviablog-list wp-post-image" src="' . get_bloginfo('stylesheet_directory') . '/img/thumbnail-309x175.jpg" />'; ?>
                                <?php endif; ?>
                                <?php $yellow_value = get_post_meta(get_the_ID(), "yellow_label", true); ?>
                                <?php if (!empty($yellow_value)) : ?>
                                    <div class="yellow-label"><span><?php echo $yellow_value ?></span></div>
                                <?php endif; ?>
                            </a>
                            <div class="content-box">
                                <?php
                                $icon_name = get_post_meta(get_the_ID(), "icon_name", true);
                                $icon_value = get_post_meta(get_the_ID(), "icon_value", true);
                                if (!empty($icon_value)) :
                                    ?>
                                    <span class="icon-label"><?php echo get_icon_label($icon_value,$icon_name);?></span>
                                <?php endif; ?>
                                <h2 class="title"><a href="<?php the_permalink(); ?>" title="outdoor living"><?php the_title(); ?></a></h2>
                                <p class="content"><?php echo get_the_excerpt(); ?></p>
                                <p class="more-info"><a href="<?php echo get_author_posts_url(get_the_author_meta('ID'), get_the_author_meta('user_nicename')); ?>"><?php the_author(); ?></a> | <a href="<?php echo get_day_link(get_the_time('Y'), get_the_time('m'), get_the_time('d')); ?>" title="date"><?php the_time('F j, Y') ?></a></p>
                                <a href="<?php the_permalink(); ?>" class="read-more">Read More</a>
                            </div>
                            <?php
                        endwhile;
                    endif;
                    wp_reset_query();
                    ?>
                </li>
                <li class="item">
                    <?php query_posts('orderby=date&order=DESC&meta_key=monrovia_homepage_featured_position&meta_value=center&post_type=post&showposts=1&post_status=publish'); ?>
                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                            <?php $_post_not_in[] = get_the_ID() ?>
                            <a class="media-object" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php echo get_the_post_thumbnail(get_the_ID(), 'monroviablog-child-feature', array()); ?>
                                <?php else: ?>
                                    <?php echo '<img width="309" height="175" class="attachment-monroviablog-list size-monroviablog-list wp-post-image" src="' . get_bloginfo('stylesheet_directory') . '/img/thumbnail-309x175.jpg" />'; ?>
                                <?php endif; ?>
                                <?php $yellow_value = get_post_meta(get_the_ID(), "yellow_label", true); ?>
                                <?php if (!empty($yellow_value)) : ?>
                                    <div class="yellow-label"><span><?php echo $yellow_value ?></span></div>
                                <?php endif; ?>
                            </a>
                            <div class="content-box">
                                <?php
                                $icon_name = get_post_meta(get_the_ID(), "icon_name", true);
                                $icon_value = get_post_meta(get_the_ID(), "icon_value", true);
                                if (!empty($icon_value)) :
                                    ?>
                                    <span class="icon-label"><?php echo get_icon_label($icon_value,$icon_name);?></span>
                                <?php endif; ?>
                                <h2 class="title"><a href="<?php the_permalink(); ?>" title="outdoor living"><?php the_title(); ?></a></h2>
                                <p class="content"><?php echo get_the_excerpt(); ?></p>
                                <p class="more-info"><a href="<?php echo get_author_posts_url(get_the_author_meta('ID'), get_the_author_meta('user_nicename')); ?>"><?php the_author(); ?></a> | <a href="<?php echo get_day_link(get_the_time('Y'), get_the_time('m'), get_the_time('d')); ?>" title="date"><?php the_time('F j, Y') ?></a></p>
                                <a href="<?php the_permalink(); ?>" class="read-more">Read More</a>
                            </div>
                            <?php
                        endwhile;
                    endif;
                    wp_reset_query();
                    ?>
                </li>
                <li class="item">
                    <?php query_posts('orderby=date&order=DESC&meta_key=monrovia_homepage_featured_position&meta_value=right&post_type=post&showposts=1&post_status=publish'); ?>
                    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                            <?php $_post_not_in[] = get_the_ID() ?>
                            <a class="media-object" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php echo get_the_post_thumbnail(get_the_ID(), 'monroviablog-child-feature', array()); ?>
                                <?php else: ?>
                                    <?php echo '<img width="309" height="175" class="attachment-monroviablog-list size-monroviablog-list wp-post-image" src="' . get_bloginfo('stylesheet_directory') . '/img/thumbnail-309x175.jpg" />'; ?>
                                <?php endif; ?>
                                <?php $yellow_value = get_post_meta(get_the_ID(), "yellow_label", true); ?>
                                <?php if (!empty($yellow_value)) : ?>
                                    <div class="yellow-label"><span><?php echo $yellow_value ?></span></div>
                                <?php endif; ?>
                            </a>
                            <div class="content-box">
                                <?php
                                $icon_name = get_post_meta(get_the_ID(), "icon_name", true);
                                $icon_value = get_post_meta(get_the_ID(), "icon_value", true);
                                if (!empty($icon_value)) :
                                    ?>
                                    <span class="icon-label"><?php echo get_icon_label($icon_value,$icon_name);?></span>
                                <?php endif; ?>
                                <h2 class="title"><a href="<?php the_permalink(); ?>" title="outdoor living"><?php the_title(); ?></a></h2>
                                <p class="content"><?php echo get_the_excerpt(); ?></p>
                                <p class="more-info"><a href="<?php echo get_author_posts_url(get_the_author_meta('ID'), get_the_author_meta('user_nicename')); ?>"><?php the_author(); ?></a> | <a href="<?php echo get_day_link(get_the_time('Y'), get_the_time('m'), get_the_time('d')); ?>" title="date"><?php the_time('F j, Y') ?></a></p>
                                <a href="<?php the_permalink(); ?>" class="read-more">Read More</a>
                            </div>
                            <?php
                        endwhile;
                    endif;
                    wp_reset_query();
                    ?>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php $query = new WP_Query(array('post__not_in' => $_post_not_in, 'posts_per_page' => 3)); ?>
<?php if ($query->have_posts()) : ?>
    <div class="row">
        <div class="col-md-12">
            <div class="list-post">
                <ul>
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <?php $categories = get_the_category(); ?>
                        <li class="item">
                            <a class="thumbnail-box" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" <?php if (has_post_thumbnail()) { 
	echo 'style="background-position: 50% center;
	background-size: cover; 
	background-image: url(' . wp_get_attachment_image_src(get_post_thumbnail_id( get_the_ID() ), 'large' )[0] . ');"'; 
}else{
	echo 'style="background-position: 50% center;
	background-size: cover; 
	background-image: url(' . get_bloginfo('stylesheet_directory') . '/img/thumbnail-default.jpg);"'; 
	}?>>
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php echo get_the_post_thumbnail(get_the_ID(), 'monroviablog-list', array()); ?>
                                <?php else: ?>
                                    <?php echo '<img width="490" height="275" class="attachment-monroviablog-list size-monroviablog-list wp-post-image" src="' . get_bloginfo('stylesheet_directory') . '/img/thumbnail-default.jpg" />'; ?>
                                <?php endif; ?>
                                <?php $yellow_value = get_post_meta(get_the_ID(), "yellow_label", true); ?>
                                <?php if (!empty($yellow_value)) : ?>
                                    <div class="yellow-label"><span><?php echo $yellow_value ?></span></div>
                                <?php endif; ?>
                            </a>
                            <div class="content-box" style="<?php if (!empty($categories)) : ?>background-image: url('<?php echo z_taxonomy_image_url($categories[0]->term_id); ?>');<?php endif; ?> background-size: 50%;">
                                <?php
                                $icon_name = get_post_meta(get_the_ID(), "icon_name", true);
                                $icon_value = get_post_meta(get_the_ID(), "icon_value", true);
                                if (!empty($icon_value)) :
                                    ?>
                                    <span class="icon-label"><?php echo get_icon_label($icon_value,$icon_name);?></span>
                                <?php endif; ?>
                                <h2 class="title"><a href="<?php the_permalink(); ?>" title="outdoor living"><?php the_title(); ?></a></h2>
                                <p class="content"><?php echo get_the_excerpt(); ?></p>
                                <p class="more-info"><a href="<?php echo get_author_posts_url(get_the_author_meta('ID'), get_the_author_meta('user_nicename')); ?>"><?php the_author(); ?></a> | <a href="<?php echo get_day_link(get_the_time('Y'), get_the_time('m'), get_the_time('d')); ?>" title="<?php the_time('F j, Y') ?>"><?php the_time('F j, Y') ?></a></p>
                                <a href="<?php the_permalink(); ?>" class="read-more">Read More</a>
                            </div>
                        </li>
                    <?php endwhile; ?>
                    <div class="clearfix"></div>
                    <a href="<?php echo get_permalink(get_page_by_path('blog')->ID); ?>" class="bt-wide-all home-page">See All</a>
                    <div class="clearfix"></div>
                </ul>
            </div>
        </div>
    </div>
<?php endif;
wp_reset_postdata(); ?>
</div>
<div class="container-fluid">
    <?php
    $query = array('post_type' => 'homepage');
    $custom_query = new WP_Query($query);
    if ($custom_query->have_posts()):
        while ($custom_query->have_posts()): $custom_query->the_post();
            $quotes = get_group('quote_section');
            ?>
            <div class="quotes-wrapper" style="background-image: url('<?php echo $quotes[1]["quote_section_upload"][1]['original'] ?>'); background-repeat: no-repeat; background-position: center center; background-attachment: fixed; background-size: cover; ">
                <div class="container">
                    <div class="quotes-section row">
                        <div class="col-md-12 quote-content">
                            <p class="quote-text">&ldquo; <?php echo $quotes[1]["quote_section_quote"][1] ?> &rdquo;</p>
                            <p class="quote-author">&ndash; <?php echo $quotes[1]["quote_section_author"][1] ?></p>
                        </div>
                    </div>
                </div>
            </div>
    <?php endwhile;
endif;
wp_reset_postdata(); ?>
    <div class="share-joy-wrapper">
        <div class="container">
            <div class="row share-joy">
                <div class="title-posts">
                    <h1 class="home-wide-title">Share your joy</h1>
                </div>
                <div class="line-wide"></div>
                <img src="/wp-content/themes/monroviablog/assets/imgs/share-your-joy.gif" style="display: block;margin: auto;"/>
                <div class="clearfix"></div>
                <br/>
                <br/>
                <a class="bt-wide-all homepage">See All</a>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>
<div class="container">

<?php get_footer(); ?>