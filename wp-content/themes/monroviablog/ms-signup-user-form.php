<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<h2>Not a member yet? Sign up today!</h2>
<p>Monrovia members can create & share wish lists, get expert gardening tips, and more.</p>
<form id="registerform" method="post" action="<?php $template->the_action_url( 'register' ); ?>">
	<input type="hidden" name="action" value="register" />
	<input type="hidden" name="stage" value="validate-user-signup" />
	<?php do_action( 'signup_hidden_fields' ); ?>
	<p>
	<label for="user_name<?php $template->the_instance(); ?>"><span>*</span><?php _e( 'Username', 'theme-my-login' ); ?></label>
	<?php if ( $errmsg = $errors->get_error_message( 'user_name' ) ) { ?>
		<p class="error"><?php echo $errmsg; ?></p>
	<?php } ?>

	<input name="user_name" type="text" id="user_name<?php $template->the_instance(); ?>" class="input" value="<?php echo esc_attr( $user_name ); ?>" size="20" /><br />
	</p>
	<p>
	<label for="user_email<?php $template->the_instance(); ?>"><span>*</span><?php _e( 'E-mail', 'theme-my-login' ); ?></label>
	<?php if ( $errmsg = $errors->get_error_message( 'user_email' ) ) { ?>
		<p class="error"><?php echo $errmsg; ?></p>
	<?php } ?>

	<input name="user_email" type="text" id="user_email<?php $template->the_instance(); ?>" class="input" value="<?php echo esc_attr( $user_email ); ?>" maxlength="256" size="20" /><br />
	</p>
	<?php if ( $errmsg = $errors->get_error_message( 'generic' ) ) { ?>
		<p class="error"><?php echo $errmsg; ?></p>
	<?php } ?>

	<?php do_action( 'signup_extra_fields', $errors ); ?>
	<p id="reg_passmail<?php $template->the_instance(); ?>"><?php echo apply_filters( 'tml_register_passmail_template_message', __( 'A password will be e-mailed to you.' ) ); ?></p>
	<p class="required"><span class="req">*</span>Required Fields</p>
	<p>
	<?php if ( $active_signup == 'blog' ) { ?>
		<input id="signupblog<?php $template->the_instance(); ?>" type="hidden" name="signup_for" value="blog" />
	<?php } elseif ( $active_signup == 'user' ) { ?>
		<input id="signupblog<?php $template->the_instance(); ?>" type="hidden" name="signup_for" value="user" />
	<?php } else { ?>
		<input id="signupblog<?php $template->the_instance(); ?>" type="radio" name="signup_for" value="blog" <?php if ( ! isset( $_POST['signup_for'] ) || $_POST['signup_for'] == 'blog' ) { ?>checked="checked"<?php } ?> />
		<label class="checkbox" for="signupblog"><?php _e( 'Gimme a site!', 'theme-my-login' ); ?></label>
		<br />
		<input id="signupuser<?php $template->the_instance(); ?>" type="radio" name="signup_for" value="user" <?php if ( isset( $_POST['signup_for'] ) && $_POST['signup_for'] == 'user' ) { ?>checked="checked"<?php } ?> />
		<label class="checkbox" for="signupuser"><?php _e( 'Just a username, please.', 'theme-my-login' ); ?></label>
	<?php } ?>
	</p>

	<p class="submit"><input type="submit" name="submit" class="submit" value="<?php esc_attr_e( 'Register', 'theme-my-login' ); ?>" /></p>
</form>
<?php //$template->the_action_links( array( 'register' => false ) ); ?>
<script>
// No way to add span wrapped * in the Cimy User Fields Admin Interface
// Adding with jQuery here after page load.
// Probably a better way to do this

jQuery(document).ready( function($){
	$("#cimy_uef_wp_p_field_3 label, #cimy_uef_wp_p_field_4 label, #cimy_uef_p_field_2 label, label[for='pass1tml_login'], label[for='pass2tml_login']").prepend('<span>*</span>');
	
	var postalCode = '';
	//Grab the zipcode field
	jQuery('#cimy_uef_2').on('blur', function(){
		postalCode = jQuery(this).val();
		jQuery.post(ajaxurl, { action: 'get_cold_zone', zipcode: postalCode }, function( data ) {
			data = jQuery.parseJSON(data);
			jQuery('#zipcode').val(data.zipcode);
			jQuery('#coldzone').val(data.cold_zone);
		});
		
	});
});
</script>