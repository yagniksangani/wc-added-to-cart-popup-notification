<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://profiles.wordpress.org/yagniksangani/
 * @since      1.0.0
 *
 * @package    WC_AC_POPUP_NOTIFICATION
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$option_name = 'wcacn_option';
if ( get_option( $option_name ) ) {
	delete_option( $option_name );
}
