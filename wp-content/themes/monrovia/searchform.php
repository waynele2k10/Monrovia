<!-- search -->
<form class="search right" method="get" action="<?php echo home_url(); ?>" role="search">
	<div class="form-item">
  		<label for="s">Search</label>
		<input class="search-input" type="text" name="s" id="s" autocomplete="off">
		<button onclick="ga('send', 'event', 'Main Search', 'Text search performed');" class="search-submit" type="submit" role="button">Go</button>
    </div><!-- end form item -->
</form>
<!-- /search -->

