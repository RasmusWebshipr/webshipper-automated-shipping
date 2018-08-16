<?php
/**
 * Webshipr for WooCommerce Uninstall
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option('webshipr_options');
