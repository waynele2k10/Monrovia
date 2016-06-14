<?php
if( isset( $_GET['tab'] ) ) {
	$active_tab = ( 'wp2magento' == $_GET['tab'] ) ? 'wp2magento' : 'magento2wp';
}
else {
	$active_tab = 'wp2magento';
}

$settings = VM_WP_Magento_Bridge::get_instance()->get_settings_object();
?>
<div class="wrap">
	<?php screen_icon(); ?>
 	<h2><?php _e( 'WP to Magento Bridge Settings', VM_WP_Magento_Bridge::$textdomain ); ?></h2>
	
	<p><?php _e( 'Settings for interconnecting WordPress with a Magento site', VM_WP_Magento_Bridge::$textdomain );?></p>
	<?php /* settings_errors();*/ ?>

	<h2 class="nav-tab-wrapper">
		<?php $class = ( 'wp2magento' == $active_tab ) ? 'nav-tab-active' : ''; ?>
		<a href="?page=wp-magento-bridge&tab=wp2magento" class="nav-tab <?php echo $class;?>"><?php _e( 'WP &rarr; Magento Settings', VM_WP_Magento_Bridge::$textdomain ); ?></a>
		<?php $class = ( 'magento2wp' == $active_tab ) ? 'nav-tab-active' : ''; ?>
		<a href="?page=wp-magento-bridge&tab=magento2wp" class="nav-tab <?php echo $class;?>"><?php _e( 'Magento &rarr; WP Settings', VM_WP_Magento_Bridge::$textdomain ); ?></a>
	</h2>

	<form method="post" action="options.php">
	<?php
		$sync_error = get_option( 'wp_magento_bridge_error' );
		if ( ! empty( $sync_error ) ) {
			printf( '<h3 class="sync-error">Last user sync failed! Reason: %s</h3>', esc_html( $sync_error ) );
		}

		if ( 'wp2magento' == $active_tab ) {
			settings_fields( 'vmwpmb_wp2magento_group' );
		//	do_settings_fields( 'vmwpmb_wp2magento_group', 'vmwpmbr_wp2magento-section' );

			do_settings_sections( 'wp2magento_settings' );
		}
		else {
			settings_fields( 'vmwpmb_magento2wp_group' );
			//do_settings_fields( 'vmwpmb_magento2wp_group', 'vmwpmbr_magento2wp-section' );

			do_settings_sections( 'magento2wp_settings' );
		}
 
	//	do_settings_sections( 'wp-magento-bridge' );
 
		submit_button();
	?>
	</form>
	
	<form id="" method="POST">
	</form>
</div>
<style>
.sync-error {
	color: #f00;
}
</style>
