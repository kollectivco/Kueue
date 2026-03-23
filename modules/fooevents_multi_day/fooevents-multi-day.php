<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;}
/**
 * Plugin Name: FooEvents Multi-Day
 * Description: Sell tickets to events that run over multiple calendar or sequential days and perform separate check-ins for each day of the event.
 * Version: 1.7.9
 * Author: FooEvents
 * Plugin URI: https://www.fooevents.com/
 * Author URI: https://www.fooevents.com/
 * Developer: FooEvents
 * Developer URI: https://www.fooevents.com/
 * Text Domain: fooevents-multiday-events
 * WC requires at least: 7.0.0
 * WC tested up to: 10.1.1
 *
 * Copyright: © 2009-2025 FooEvents.
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

require WP_PLUGIN_DIR . '/fooevents_multi_day/class-fooevents-multiday-events-config.php';
require WP_PLUGIN_DIR . '/fooevents_multi_day/class-fooevents-multiday-events.php';

$fooevents_multiday_events = new Fooevents_Multiday_Events();
