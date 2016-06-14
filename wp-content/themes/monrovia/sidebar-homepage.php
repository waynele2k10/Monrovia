  <?php // Homepage Right Widget Area ?>
  <div id="homepage" class="sidebar right">
  	<div class="hideMobile"><?php include('includes/zone-box.php'); ?></div>
    <?php if (function_exists('dynamic_sidebar') && dynamic_sidebar('Homepage')) : else : ?>
    <?php endif; ?>
  </div><!-- end homepage -->
