<?php
/**
 * Plugin Name: FooEvents POS
 * Plugin URI: https://www.fooevents.com
 * Description: FooEvents POS is a web-based point of sale plugin for WooCommerce that runs on your own server and enables you to sell and print tickets in-person (this functionality requires the FooEvents for WooCommerce plugin to be installed and activated).
 * Version: 1.10.8
 * Author: FooEvents
 * Author URI: https://www.fooevents.com
 * Developer: FooEvents
 * Developer URI: https://www.fooevents.com
 * Text Domain: fooevents-pos
 * Domain Path: /languages
 * Requires at least: 6.2
 * Requires PHP: 7.3
 * WC requires at least: 8.0.0
 * WC tested up to: 10.0.4
 *
 * Requires Plugins: woocommerce
 *
 * Copyright: © 2009-2025 FooEvents POS
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package fooevents-pos
 */

// If this file is called directly, abort.
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

// Define global variables.
$fooeventspos_products_default_minimum_cart_quantity = get_option( 'fooeventspos_products_default_minimum_cart_quantity', '1' );
$fooeventspos_products_default_cart_quantity_step    = get_option( 'fooeventspos_products_default_cart_quantity_step', '1' );
$fooeventspos_products_default_cart_quantity_unit    = get_option( 'fooeventspos_products_default_cart_quantity_unit', '' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-fooeventspos-activator.php
 */
function fooeventspos_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fooeventspos-activator.php';

	FooEventsPOS_Activator::fooeventspos_activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-fooeventspos-deactivator.php
 */
function fooeventspos_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fooeventspos-deactivator.php';

	FooEventsPOS_Deactivator::fooeventspos_deactivate();
}

register_activation_hook( __FILE__, 'fooeventspos_activate' );
register_deactivation_hook( __FILE__, 'fooeventspos_deactivate' );

add_action( 'admin_init', 'fooeventspos_activation_redirect' );
add_action( 'activated_plugin', 'fooeventspos_plugin_activated' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-fooeventspos.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function fooeventspos_run() {
	if ( ! function_exists( 'get_plugin_data' ) ) {

		require_once ABSPATH . 'wp-admin/includes/plugin.php';

	}

	$version = get_plugin_data( __DIR__ . '/fooevents-pos.php', false, false )['Version'];

	$plugin = new FooEventsPOS( $version );
	$plugin->fooeventspos_run();
}

/**
 * Redirect to the FooEvents POS General settings screen after activating the plugin.
 *
 * @since 1.7.7
 */
function fooeventspos_activation_redirect() {
	$option = get_option( 'fooeventspos_do_activation_redirect', false );

	if ( $option ) {
		delete_option( 'fooeventspos_do_activation_redirect' );

		if ( ! isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_safe_redirect( admin_url( 'admin.php?page=fooeventspos-settings' ) );

			exit;
		}
	}
}

/**
 * Update options and user role capabilities on plugin activation.
 *
 * @since 1.7.7
 * @param string $plugin A reference to this plugin.
 */
function fooeventspos_plugin_activated( $plugin ) {

	if ( plugin_basename( __FILE__ ) === $plugin ) {

		$role = get_role( 'administrator' );

		$role->add_cap( 'publish_fooeventspos' );

		if ( ! ( get_option( 'globalFooEventsPOSAnalyticsOptIn' ) ) ) {

			update_option( 'globalFooEventsPOSAnalyticsOptIn', 'yes' );

		}

		if ( ! ( get_option( 'globalFooEventsPOSWooCommerceAnalytics' ) ) ) {

			update_option( 'globalFooEventsPOSWooCommerceAnalytics', 'yes' );

		}

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		if ( ! ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) ) {
			return;
		}

		add_option( 'fooeventspos_do_activation_redirect', true );
	}
}

add_action( 'init', 'fooeventspos_run', 0 );

/**
 * Remove admin capabilities.
 *
 * @since 1.0.0
 */
function fooeventspos_remove_admin_caps() {
	$delete_caps = array(
		'publish_fooeventspos',
		'edit_fooeventspos_payment',
		'read_fooeventspos_payment',
		'delete_fooeventspos_payment',
		'edit_fooeventspos_payments',
		'edit_others_fooeventspos_payments',
		'delete_fooeventspos_payments',
		'read_private_fooeventspos_payment',
		'read_fooeventspos_payment',
		'delete_private_fooeventspos_payments',
		'delete_published_fooeventspos_payments',
		'delete_others_fooeventspos_payments',
		'edit_private_fooeventspos_payment',
		'edit_published_fooeventspos_payments',
	);

	global $wp_roles;

	foreach ( $delete_caps as $cap ) {

		foreach ( array_keys( $wp_roles->roles ) as $role ) {

			$wp_roles->remove_cap( $role, $cap );
		}
	}
}

register_deactivation_hook( __FILE__, 'fooeventspos_remove_admin_caps' );
