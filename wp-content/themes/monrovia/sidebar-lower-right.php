<div id="lower-right-sidebar" class="sidebar">
    <div class="sidebar-inner">
             <?php if (function_exists('dynamic_sidebar') && dynamic_sidebar('Lower Right Sidebar')) : else : ?>
        		<!-- All this stuff in here only shows up if you DON'T have any widgets active in this zone -->
			<?php endif; ?>
    </div><!-- sidebar inner -->
</div>