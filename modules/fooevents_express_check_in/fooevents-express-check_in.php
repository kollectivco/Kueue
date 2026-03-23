<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;}
/**
 * Plugin Name: FooEvents Express Check-in
 * Description: Ensure fast and effortless attendee check-ins at your event. Search for attendees or connect a barcode scanner to scan tickets instead of typing.
 * Version: 1.8.9
 * Author: FooEvents
 * Plugin URI: https://www.fooevents.com/
 * Author URI: https://www.fooevents.com/
 * Developer: FooEvents
 * Developer URI: https://www.fooevents.com/
 * Text Domain: fooevents-express-check-in
 * WC requires at least: 7.0.0
 * WC tested up to: 8.6.1
 *
 * Copyright: © 2009-2023 FooEvents.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

add_action(
	'before_woocommerce_init',
	function() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

// include config.
require WP_PLUGIN_DIR . '/fooevents_express_check_in/class-fooevents-express-check-in-config.php';
require WP_PLUGIN_DIR . '/fooevents_express_check_in/class-fooevents-express-check-in.php';

$fooevents_express_check_in = new FooEvents_Express_Check_In();

