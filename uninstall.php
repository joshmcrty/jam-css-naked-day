<?php
// Remove the saved options when JAM CSS Naked Day is uninstalled
if( defined( WP_UNINSTALL_PLUGIN ) ) {
	delete_option( 'jam_css_naked_day_options' );
}
?>