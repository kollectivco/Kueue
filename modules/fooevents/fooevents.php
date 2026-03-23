<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;}
/**
 * Plugin Name: FooEvents for WooCommerce
 * Description: FooEvents adds powerful event, ticketing and booking functionality to your WooCommerce website with no commission or ticket fees.
 * Version: 1.20.24
 * Author: FooEvents
 * Plugin URI: https://www.fooevents.com/
 * Author URI: https://www.fooevents.com/
 * Developer: FooEvents
 * Developer URI: https://www.fooevents.com/
 * Text Domain: woocommerce-events
 * Domain Path: /languages
 * WC requires at least: 7.0.0
 * WC tested up to: 10.5.0
 *
 * Copyright: © 2009-2025 FooEvents.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, false );
		}
	}
);

add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'product_block_editor', __FILE__, false );
		}
	}
);


require WP_PLUGIN_DIR . '/fooevents/class-fooevents-config.php';
require WP_PLUGIN_DIR . '/fooevents/class-fooevents.php';
require WP_PLUGIN_DIR . '/fooevents/classes/blocks/class-fooevents-blocks.php';

$fooevents        = new FooEvents();
$fooevents_blocks = new FooEvents_Blocks();
