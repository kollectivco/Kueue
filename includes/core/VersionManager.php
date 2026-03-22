<?php

namespace KueueEvents\Core\Core;

class VersionManager {

    /**
     * Get the current plugin version from the main file.
     */
    public static function get_current_version() {
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }
        $plugin_data = get_plugin_data( KQ_PLUGIN_DIR . 'kueue-events-core.php' );
        return $plugin_data['Version'] ?? KQ_VERSION;
    }

    /**
     * Validate semantic version format (X.Y.Z).
     */
    public static function is_valid_version( $version ) {
        return preg_match( '/^\d+\.\d+\.\d+$/', $version );
    }

    /**
     * Compare two versions.
     * Returns true if $new is greater than $current.
     */
    public static function is_new_version_available( $current, $new ) {
        return version_compare( $current, $new, '<' );
    }
}
