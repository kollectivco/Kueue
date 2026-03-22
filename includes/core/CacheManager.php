<?php

namespace KueueEvents\Core\Core;

class CacheManager {

    /**
     * Set cache with transient.
     */
    public static function set( $key, $value, $expiration = HOUR_IN_SECONDS ) {
        set_transient( 'kq_cache_' . $key, $value, $expiration );
    }

    /**
     * Get cache from transient.
     */
    public static function get( $key ) {
        return get_transient( 'kq_cache_' . $key );
    }

    /**
     * Delete cache.
     */
    public static function delete( $key ) {
        delete_transient( 'kq_cache_' . $key );
    }

    /**
     * Clear all plugin transients (helper).
     */
    public static function clear_all() {
        global $wpdb;
        $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_kq_cache_%' OR option_name LIKE '_transient_timeout_kq_cache_%'" );
    }
}
