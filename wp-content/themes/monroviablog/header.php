<?php // Exit if accessed directly
if (!defined('ABSPATH')) {
    echo '<h1>Forbidden</h1>';
    exit();
}

$first_cookie1 = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
if (!Theme_My_Login::is_tml_page()) {
    setcookie( 'first_url1', $first_cookie1, time() + (86400 * 30), COOKIEPATH, COOKIE_DOMAIN );
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>" />
        <meta name="author" content="<?php bloginfo('name'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php wp_title('-', true, 'right'); ?></title>
        <link rel="shortcut icon" href="<?php echo get_favicon(); ?>" />
        <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
        <?php
// Enqueue comment-reply script if comments_open and singular
        if (is_singular() && comments_open())
            wp_enqueue_script('comment-reply');
// WordPress Head
        wp_head();
        ?>
    </head>
    <body id="monroviablog" <?php body_class(); ?>>
        <?php
        // Navbar
        get_template_part('partials/navbar');
        ?>
        <div id="primary-menu">
            <div class="container">
                <?php
                $args = array(
                    'menu' => 'primary',
                    'theme_location' => 'primary',
                    'depth' => 1,
                    'container' => 'div',
                    'container_class' => 'primary-container collapse navbar-collapse',
                    'menu_class' => 'nav navbar-nav navbar-left',
                    'fallback_cb' => 'wp_bootstrap_navwalker::fallback',
                    'walker' => new wp_bootstrap_navwalker()
                );
                wp_nav_menu($args);
                ?>
                <div class="search-form showMobile">
                    <!-- search -->
                    <form class="search right" method="get" action="<?php echo home_url(); ?>" role="search">
                        <div class="form-item">
                            <label for="sm">Search</label><!--
                            --><input class="search-input" type="text" name="s" id="sm" autocomplete="off" placeholder="Search for..."><!--
                            --><button class="search-submit" type="submit" role="button">Go</button>
                        </div><!-- end form item -->
                    </form>
                    <!-- /search -->
                </div><!-- end mobile search -->
            </div>
        </div>

        <div class="container">