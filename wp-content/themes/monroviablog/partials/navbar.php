<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    echo '<h1>Forbidden</h1>';
    exit();
}
?>

<nav class="navbar navbar-inverse navbar-static-top">
    <div class="container">
        <div class="row">
            <?php
            global $monroviablog_theme_options;
            if ($monroviablog_theme_options['logo_main'] != "") :
                ?>
                <div class="head-col-logo">
                    <?php if (is_front_page()) : ?>
                        <h1 class="head-logo" title="<?php echo esc_attr(get_bloginfo('name')); ?>">
                            <a href="<?php echo site_url(); ?>" title="<?php echo esc_attr(get_bloginfo('name')); ?>">
                            <img class="logo" src="<?php echo esc_attr($monroviablog_theme_options['logo_main']) ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>"/>
                            </a>
                        </h1>
                    <?php else: ?>
                        <a class="head-logo" href="<?php echo site_url(); ?>" title="<?php echo esc_attr(get_bloginfo('name')); ?>">
                            <img class="logo" src="<?php echo esc_attr($monroviablog_theme_options['logo_main']) ?>"/>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="head-control">
                <a class="control-item home-icon" href="<?php echo get_home_url(); ?>">
                    <i class="fa fa-home" aria-hidden="true"></i>
                </a>
                <button class="control-item search-toggle" type="button">
                    <i class="fa fa-search" aria-hidden="true"></i>
                </button>
                <button class="control-item navbar-toggle" type="button" data-toggle="collapse" data-target=".menu-mobile">
                    <span class="sr-only"><?php _e('Toggle Navigation', 'monroviablog'); ?></span>
                    <i class="fa fa-bars" aria-hidden="true"></i>
                </button>
                <?php
                // Wordpress wp_nav_menu
                $args = array(
                    'menu' => 'mobile',
                    'theme_location' => 'mobile',
                    'depth' => 2,
                    'container' => 'div',
                    'container_class' => 'top-menu collapse navbar-collapse menu-mobile',
                    'menu_class' => 'nav navbar-nav navbar-right',
                    'fallback_cb' => 'wp_bootstrap_navwalker::fallback',
                    'walker' => new wp_bootstrap_navwalker()
                );
                wp_nav_menu($args);
                ?>
            </div>
            <div class="head-col-topmenu">
                <?php
                // Wordpress wp_nav_menu
                $args = array(
                    'menu' => 'header',
                    'theme_location' => 'header',
                    'depth' => 1,
                    'container' => 'div',
                    'container_class' => 'top-menu collapse navbar-collapse',
                    'menu_class' => 'nav navbar-nav navbar-right',
                    'fallback_cb' => 'wp_bootstrap_navwalker::fallback',
                    'walker' => new wp_bootstrap_navwalker()
                );
                wp_nav_menu($args);
                ?>
            </div>
        </div>
    </div>
</nav>