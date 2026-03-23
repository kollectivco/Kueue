<?php
/**
 * FooEvents POS integration functions
 *
 * @link https://www.fooevents.com
 * @since 1.0.2
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Deletes FooEvents POS global integration settings
 *
 * @since 1.0.2
 */
function fooeventspos_delete_settings() {

	delete_option( 'globalWooCommerceEventsPOSUseAppSettings' );

	delete_option( 'globalFooEventsPOSUseCheckinsSettings' );
	delete_option( 'globalFooEventsPOSAppTitle' );
	delete_option( 'globalFooEventsPOSAppLogo' );
	delete_option( 'globalFooEventsPOSPrimaryColor' );
	delete_option( 'globalFooEventsPOSPrimaryTextColor' );
	delete_option( 'globalFooEventsPOSAppIcon' );
}

register_uninstall_hook( __FILE__, 'fooeventspos_delete_settings' );
