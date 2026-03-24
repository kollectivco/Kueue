<?php
/**
 * Plugin Name:       Kueue Events Core
 * Plugin URI:        https://kueue.com/
 * Description:       Full event marketplace system for ticket management, bookings, and multi-vendor support.
 * Version:           1.2.6
 * Author:            Antigravity
 * Author URI:        https://antigravity.ai/
 * Update URI:        https://github.com/kollectivco/Kueue
 * License:           GPL-2.0+
 * Text Domain:       kueue-events-core
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define Plugin Constants
define( 'KQ_VERSION', '1.2.6' );
define( 'KQ_PLUGIN_FILE', __FILE__ );
define( 'KQ_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KQ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'KQ_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Robust PSR-4 Autoloader with Case-Sensitivity Handling (for Linux Support).
 */
spl_autoload_register( function ( $class ) {
    $prefix = 'KueueEvents\\Core\\';
    if ( strpos( $class, $prefix ) !== 0 ) {
        return;
    }

    $relative_class = substr( $class, strlen( $prefix ) );
    $parts = explode( '\\', $relative_class );
    
    // Attempt path construction
    $base = KQ_PLUGIN_DIR . 'includes/';
    $path = $base;
    
    foreach ( $parts as $i => $part ) {
        if ( $i === count( $parts ) - 1 ) {
            $path .= $part . '.php'; // Class name must match case
        } else {
            // Check Capitalized folder, then lowercase folder
            if ( is_dir( $path . $part ) ) {
                $path .= $part . '/';
            } elseif ( is_dir( $path . strtolower( $part ) ) ) {
                $path .= strtolower( $part ) . '/';
            } else {
                $path .= $part . '/'; // Default to original for file_exists check below
            }
        }
    }

    if ( file_exists( $path ) ) {
        require_once $path;
    }
} );

/**
 * Load global helpers with safety checks.
 */
function kq_load_helpers() {
    $potential_paths = [
        KQ_PLUGIN_DIR . 'includes/Helpers/GeneralHelpers.php',
        KQ_PLUGIN_DIR . 'includes/helpers/GeneralHelpers.php'
    ];
    
    foreach ( $potential_paths as $p ) {
        if ( file_exists( $p ) ) {
            require_once $p;
            return true;
        }
    }
    return false;
}

if ( ! kq_load_helpers() ) {
    // Graceful error if helpers missing
    if ( is_admin() ) {
        add_action( 'admin_notices', function() {
            echo '<div class="error"><p>Kueue Events Error: Helpers file not found. Case-sensitivity mismatch?</p></div>';
        });
    }
}

/**
 * Start the plugin
 */
function run_kueue_events_core() {
    if ( class_exists( 'KueueEvents\\Core\\Core\\Main' ) ) {
        $plugin = new \KueueEvents\Core\Core\Main();
        $plugin->run();
    }
}

// Initialize
run_kueue_events_core();

/**
 * Hooks
 */
register_activation_hook( __FILE__, function() {
    if ( class_exists( 'KueueEvents\\Core\\Core\\Activator' ) ) {
        $activator = new \KueueEvents\Core\Core\Activator();
        $activator->activate();
    }
} );

register_deactivation_hook( __FILE__, function() {
    if ( class_exists( 'KueueEvents\\Core\\Core\\Deactivator' ) ) {
        $deactivator = new \KueueEvents\Core\Core\Deactivator();
        $deactivator->deactivate();
    }
} );
