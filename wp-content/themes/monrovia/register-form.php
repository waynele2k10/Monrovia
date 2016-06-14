<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="login" id="theme-my-login<?php $template->the_instance(); ?>">
	<h2>Not a member yet? Sign up today!</h2>
    <p>Monrovia members can create & share wish lists, get expert gardening tips, and more.</p>
	<?php //$template->the_action_template_message( 'register' ); ?>
	<?php $template->the_errors(); ?>
	<form name="registerform" id="registerform<?php $template->the_instance(); ?>" action="<?php $template->the_action_url( 'register' ); ?>" method="post">
		<p>
			<label for="user_login<?php $template->the_instance(); ?>"><span>*</span><?php _e( 'Username' ); ?></label>
			<input type="text" name="user_login" id="user_login<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'user_login' ); ?>" size="20" />
		</p>

		<p>
			<label for="user_email<?php $template->the_instance(); ?>"><span>*</span><?php _e( 'E-mail' ); ?></label>
			<input type="text" name="user_email" id="user_email<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value( 'user_email' ); ?>" size="20" />
		</p>

		<?php do_action( 'register_form' ); ?>

		<p id="reg_passmail<?php $template->the_instance(); ?>"><?php echo apply_filters( 'tml_register_passmail_template_message', __( 'A password will be e-mailed to you.' ) ); ?></p>
		<p class="required"><span class="req">*</span>Required Fields</p>
		<p class="submit">
			<input type="submit" name="wp-submit" id="wp-submit<?php $template->the_instance(); ?>" value="<?php esc_attr_e( 'Register' ); ?>" />
			<input type="hidden" name="redirect_to" value="<?php $template->the_redirect_url( 'register' ); ?>" />
			<input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />
			<input type="hidden" name="action" value="register" />
		</p>
	</form>
	<?php //$template->the_action_links( array( 'register' => false ) ); ?>
</div>

<script>
// No way to add span wrapped * in the Cimy User Fields Admin Interface
// Adding with jQuery here after page load.
// Probably a better way to do this

jQuery(document).ready( function($){
	$("#cimy_uef_wp_p_field_3 label, #cimy_uef_wp_p_field_4 label, #cimy_uef_p_field_2 label, label[for='pass1'], label[for='pass2']").prepend('<span>*</span>');
	
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