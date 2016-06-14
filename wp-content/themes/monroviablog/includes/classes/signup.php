<?php 
// Newsletter block for use inside of plant grids

$metaInfo =  get_group('meta_information', $post->ID);
$signUp = $metaInfo[1]['meta_information_newsletter_signup_copy'][1];
?>

<div class="newsletter-signup clear">
    <h3>Connect with Us</h3>
    <img src="<?php echo get_template_directory_uri()?>/img/signup-image.jpg" />
    <?php echo $signUp; ?>
    <p id="signupMessage" class="newsletter-msg message"></p>
    <div class="clear plant-savvy-signup left">
       <div class="form-item left hasLabel">
            <label for="nemail" class="block-label">Enter your email address</label>
            <input type="text" name="nemail" id="nemail" class="checkKeypress checkBlur">	            
       </div><!-- end clear -->
       <button class="search-submit left" role="button" type="submit" name="Submit" value="Submit" id="newsletter-signup">Sign Up</button>
       <span class="newsletter ajax-loader"></span>
   </div><!-- end clear -->
   <div class="left savvy-social right clear">
        <a target="_blank" href="http://www.facebook.com/pages/Monrovia/102411039815423?v=wall&amp;ref=sgm" title="Facebook"></a>
        <a target="_blank" href="http://twitter.com/MonroviaPlants/" title="Twitter"></a>
        <a target="_blank" href="https://www.pinterest.com/monroviaplants/" title="Pinterest"></a>
        <a target="_blank" href="http://instagram.com/MonroviaNursery#" title="Instagram"></a>
        <a target="_blank" href="https://plus.google.com/106439322773521086880/" title="Google+"></a>
   </div><!-- end left -->
</div><!-- end newsletter signup -->