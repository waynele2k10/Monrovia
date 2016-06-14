<?php
/*
 * Template Name: Monroviablog - One column BLOG
 *
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {echo '<h1>Forbidden</h1>'; exit();} get_header(); ?>

<?php if ($monroviablog_theme_options['breadcrumb'] == 1) get_template_part('partials/breadcrumb'); ?>

<?php // Blog Posts Query
$args = array('paged'=>get_query_var('paged'),'posts_per_page'=>get_query_var('posts_per_page'),'post_type'=> 'post');
query_posts($args);
?>

<?php if (have_posts()) : ?>

    <?php while (have_posts()) : the_post(); ?>

        <?php get_template_part('partials/article'); ?>

    <?php endwhile; ?>

    <?php if ($wp_query->max_num_pages>1) : ?>

        <?php monroviablog_pagination(); ?>

    <?php endif; ?>

<?php else : ?>

    <?php get_template_part('partials/nothing-found'); ?>

<?php endif; ?>

<?php get_footer(); ?>