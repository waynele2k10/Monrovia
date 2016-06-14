<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    echo '<h1>Forbidden</h1>';
    exit();
} get_header();
?>
<?php
$_year = "";
$_month = "";
if (isset($_POST['year']) && $_POST['year'] != "") {
    $_year = $_POST['year'];
    if (isset($_POST['month']) && $_POST['month'] != "") {
        $_month = $_POST['month'];
        //$args = array_merge($args, array('year'=>$_year,'monthnum'=>$_month));
    } else {
        //$args = array_merge($args, array('year'=>$_year));
    }
}
?>
<div class="row">
    <div class="col-md-12">
        <div class="article-header">
            <div class="row">
                <div class="col-md-6">
                    <h1><?php echo __("All Posts", "monroviablog") ?></h1>
                </div>
                <div class="col-md-6">
                    <div class="filterbyday-form clearfix">
                        <!-- search -->
                        <form class="right" method="post" action="" role="search">
                            <ul class="form-item">
                                <li class="col-label-1"><label for="month">Month</label></li>
                                <li class="col-label-2"><select name="month" class="form-control month"><?php echo select_month_html($_month); ?><select></li>
                                <li class="col-label-3"><label for="year">Year</label></li>
                                <li class="col-label-4"><select name="year" class="form-control year"><?php echo select_year_html($_year); ?><select></li>
                                <li class="col-label-5"><button class="search-submit" type="submit" role="button">Go</button></li>
                            </ul><!-- end form item -->
                        </form>
                        <!-- /search -->
                    </div><!-- end mobile search -->
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="content">
            <?php if (have_posts()) : ?>
                <div class="list-post">
                    <ul>
                        <?php while (have_posts()) : the_post(); ?>
                            <?php get_template_part('partials/item'); ?>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    </ul>
                    <div class="clearfix"></div>
                    <button id="pagination" class="bt-wide-all">LOAD MORE POSTS</button>
                    <div class="clearfix"></div>
                </div>
            <?php else: ?>
                <?php get_template_part('partials/nothing-found'); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php get_footer(); ?>
