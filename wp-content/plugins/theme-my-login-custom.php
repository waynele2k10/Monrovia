<?php

// Fix for Allowing redirection with TML plugin
function tml_init() {
	remove_filter( 'login_redirect', 'bbp_redirect_login', 2,  3 );
	remove_filter( 'logout_url',     'bbp_logout_url',     2,  2 );
}
add_action( 'bbp_init', 'tml_init' );
?>