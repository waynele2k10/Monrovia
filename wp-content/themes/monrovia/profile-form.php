<?php
/*
If you would like to edit this file, copy it to your current theme's directory and edit it there.
Theme My Login will always look in your theme's directory first, before using this default template.
*/
?>
<div class="login profile" id="theme-my-login<?php $template->the_instance(); ?>">
	<?php $template->the_action_template_message( 'profile' ); ?>
	<?php $template->the_errors(); ?>
	<form id="your-profile" action="<?php $template->the_action_url( 'profile' ); ?>" method="post">
		<?php wp_nonce_field( 'update-user_' . $current_user->ID ); ?>
		<p>
			<input type="hidden" name="from" value="profile" />
			<input type="hidden" name="checkuser_id" value="<?php echo $current_user->ID; ?>" />
		</p>

		<?php if ( has_action( 'personal_options' ) ) : ?>

		<h3><?php _e( 'Personal Options' ); ?></h3>

		<table class="form-table">
		<?php do_action( 'personal_options', $profileuser ); ?>
		</table>

		<?php endif; ?>

		<?php do_action( 'profile_personal_options', $profileuser ); ?>

		<div class="form-item">
			<label for="user_login"><?php _e( 'Username' ); ?></label>
			<input type="text" name="user_login" id="user_login" value="<?php echo esc_attr( $profileuser->user_login ); ?>" disabled="disabled" class="regular-text" />
        	<span class="description"><?php _e( 'Your username cannot be changed.', 'theme-my-login' ); ?></span>
        </div><!-- end form item -->
		<div class="form-item">
        	<label for="first_name"><?php _e( 'First Name' ); ?></label></th>
			<input type="text" name="first_name" id="first_name" value="<?php echo esc_attr( $profileuser->first_name ); ?>" class="regular-text" />
		</div><!-- end form item -->

		<div class="form-item">
			<label for="last_name"><?php _e( 'Last Name' ); ?></label>
			<input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( $profileuser->last_name ); ?>" class="regular-text" />
		</div><!-- end form item -->

		<div class="form-item">
			<label for="nickname"><span>*</span><?php _e( 'Nickname' ); ?></label>
			<input type="text" name="nickname" id="nickname" value="<?php echo esc_attr( $profileuser->nickname ); ?>" class="regular-text" />
		</div><!-- end form item -->

		<div class="form-item">
			<label for="display_name"><?php _e( 'Display name publicly as' ); ?></label>
			<div class="select-wrap">
            	<select name="display_name" id="display_name">
				<?php
					$public_display = array();
					$public_display['display_nickname']  = $profileuser->nickname;
					$public_display['display_username']  = $profileuser->user_login;

					if ( ! empty( $profileuser->first_name ) )
						$public_display['display_firstname'] = $profileuser->first_name;

					if ( ! empty( $profileuser->last_name ) )
						$public_display['display_lastname'] = $profileuser->last_name;

					if ( ! empty( $profileuser->first_name ) && ! empty( $profileuser->last_name ) ) {
						$public_display['display_firstlast'] = $profileuser->first_name . ' ' . $profileuser->last_name;
						$public_display['display_lastfirst'] = $profileuser->last_name . ' ' . $profileuser->first_name;
					}

					if ( ! in_array( $profileuser->display_name, $public_display ) )// Only add this if it isn't duplicated elsewhere
						$public_display = array( 'display_displayname' => $profileuser->display_name ) + $public_display;

					$public_display = array_map( 'trim', $public_display );
					$public_display = array_unique( $public_display );

					foreach ( $public_display as $id => $item ) {
				?>
					<option <?php selected( $profileuser->display_name, $item ); ?>><?php echo $item; ?></option>
				<?php
					}
				?>
				</select>
            </div><!-- end select wrap -->
		</div><!-- end form item -->

		<div class="form-item">
			<label for="email"><span>*</span><?php _e( 'E-mail' ); ?></label>
			<input type="text" name="email" id="email" value="<?php echo esc_attr( $profileuser->user_email ); ?>" class="regular-text" />
		</div><!-- end form-item -->

		<?php
		$show_password_fields = apply_filters( 'show_password_fields', true, $profileuser );
		if ( $show_password_fields ) :
		?>
		<div class="form-item">
			<label for="pass1"><?php _e( 'New Password' ); ?></label>
			<input type="password" name="pass1" id="pass1" size="16" value="" autocomplete="off" /><span class="description"><?php _e( 'If you would like to change the password, type a new one.' ); ?></span>
        </div><!-- end form-item -->
        <div class="form-item">
			<input type="password" name="pass2" id="pass2" size="16" value="" autocomplete="off" /><span class="description"><?php _e( 'Type your new password again.' ); ?></span>
         </div><!-- end form item -->
         <div class="form-item">
			<div id="pass-strength-result"><?php _e( 'Strength indicator', 'theme-my-login' ); ?></div>
			<p class="description indicator-hint"><?php _e( 'To make it stronger, use upper and lower case letters, numbers and symbols like ! " ? $ % ^ &amp; ).' ); ?></p>
		</div><!-- end form-item -->
		<?php endif; ?>

		<?php do_action( 'show_user_profile', $profileuser ); ?>

		<p class="submit">
			<input type="hidden" name="action" value="profile" />
			<input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />
			<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $current_user->ID ); ?>" />
			<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Update Profile' ); ?>" name="submit" />
		</p>
	</form>
</div>

<script>
// No way to add span wrapped * in the Cimy User Fields Admin Interface
// Adding with jQuery here after page load.
// Probably a better way to do this

jQuery(document).ready( function($){
	$("label[for='cimy_uef_2']").prepend('<span>*</span>').find('.description').remove();
});
</script>

