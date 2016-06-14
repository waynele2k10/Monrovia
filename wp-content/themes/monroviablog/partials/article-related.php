<?php // Exit if accessed directly
if (!defined('ABSPATH')) {echo '<h1>Forbidden</h1>'; exit();} ?>

<?php if (get_next_post_link('&laquo; %link', '%title', 1) OR get_previous_post_link('%link &raquo;', '%title', 1)) : ?>

    <div class="col-sm-12 col-md-12 article-related">
        <div class="row">
            <div class="col-xs-6 col-md-6 text-center previous-post-link">
                <h3><?php previous_post_link('<i class="fa fa-caret-left"></i>%link', 'PREV: %title', 1); ?></h3>
            </div>
            <div class="col-xs-6 col-md-6 text-center next-post-link">
                <h3><?php next_post_link('%link<i class="fa fa-caret-right"></i>', 'NEXT: %title', 1); ?></h3>
            </div>
        </div>
    </div>

<?php endif; ?>