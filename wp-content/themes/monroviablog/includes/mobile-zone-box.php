<!-- Cold Zone Box Widget -->	
    <div class="cold-zone widget tool-tip-container clear">
    <?php
	    if(is_home()){
			echo "<h3>Your USDA Cold Hardiness Zone</h3>";
		} elseif(!is_home()) {
			echo '<h3 class="showMobile">Your USDA Cold Hardiness Zone</h3>';
		}?>
		<div class="zone-box">
			My<br />Zone<br />
			<span></span>
		</div>
		<div class="zipcode">
			Zip Code: <span></span>
		</div>
	<?php 	// If the user is logged in, print 
      		//link to update zipcode in profile,
      		//else present a input box
	
		if( is_user_logged_in() ){ ?>
		<a href="/community/your-profile/" title="Profile" class="update">Update your profile zip code</a>
	<?php } else { ?>
		<form class="search" method="post" action="#" onSubmit="return false;" role="search">
			<div class="form-item">
				<label for="zipcodeM">Update Zip Code</label>
				<input class="zip-input" type="text" name="zipcode" maxlength="5" id="zipcodeM">
				<button class="zip-update" type="submit" role="button">Go</button>
				<div class="ajax-loader"></div>
           </div><!-- end form item -->
		</form>
	<?php } ?> 
		<a class="question"><i class="fa fa-question-circle"></i></a>
    	<div class="tool-tip zone-tip">
    		<?php getZoneTip(); ?>
    	</div><!-- end tool-tip -->
	</div><!-- end cold zone widget -->