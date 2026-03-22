<?php
/**
 * Plugin Name:       Kueue Events Core
 * Plugin URI:        https://kueue.com/
 * Description:       Full event marketplace system for ticket management, bookings, and multi-vendor support.
 * Version:           1.1.0
 * Author:            Antigravity
 * Author URI:        https://antigravity.ai/
 * License:           GPL-2.0+
 * Text Domain:       kueue-events-core
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define Plugin Constants
define( 'KQ_VERSION', '1.1.0' );
define( 'KQ_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KQ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'KQ_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * PSR-4 Autoloader for the plugin.
 */
spl_autoload_register( function ( $class ) {
    $prefix = 'KueueEvents\\Core\\';
    if ( strpos( $class, $prefix ) !== 0 ) {
        return;
    }

    $relative_class = substr( $class, strlen( $prefix ) );
    $file = KQ_PLUGIN_DIR . 'includes/' . str_replace( '\\', '/', $relative_class ) . '.php';

    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

// Load global helpers
require_once KQ_PLUGIN_DIR . 'includes/Helpers/GeneralHelpers.php';

/**
 * Initialize the plugin.
 */
function run_kueue_events_core() {
	$plugin = new \KueueEvents\Core\Core\Main();
	$plugin->run();
}

/**
 * Activation Hook
 */
register_activation_hook( __FILE__, function() {
    $activator = new \KueueEvents\Core\Core\Activator();
    $activator->activate();
} );

/**
 * Deactivation Hook
 */
register_deactivation_hook( __FILE__, function() {
    $deactivator = new \KueueEvents\Core\Core\Deactivator();
    $deactivator->deactivate();
} );

// Start the plugin
run_kueue_events_core();
