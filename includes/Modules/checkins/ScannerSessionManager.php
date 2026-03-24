<?php

namespace KueueEvents\Core\Modules\Checkins;

class ScannerSessionManager {

    /**
     * Create a new scanner session.
     */
    public static function create_session( $user_id, $device_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_scanner_sessions';

        $token = bin2hex( random_bytes( 32 ) );
        $expires_at = date( 'Y-m-d H:i:s', time() + ( 12 * HOUR_IN_SECONDS ) );

        $wpdb->insert( $table, [
            'user_id'       => $user_id,
            'session_token' => $token,
            'device_id'     => $device_id,
            'expires_at'    => $expires_at,
            'created_at'    => current_time( 'mysql' )
        ] );

        return $token;
    }

    /**
     * Validate a scanner session.
     */
    public static function validate_session( $token, $device_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_scanner_sessions';

        $session = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE session_token = %s",
            $token
        ) );

        if ( ! $session ) return false;

        // Check expiry
        if ( strtotime( $session->expires_at ) < time() ) {
            self::destroy_session( $token );
            return false;
        }

        // Check device binding
        if ( $session->device_id !== $device_id ) {
            return false;
        }

        // Update activity
        $wpdb->update( $table, [ 'last_activity_at' => current_time( 'mysql' ) ], [ 'id' => $session->id ] );

        return $session;
    }

    /**
     * Destroy a session.
     */
    public static function destroy_session( $token ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_scanner_sessions';
        $wpdb->delete( $table, [ 'session_token' => $token ] );
    }

    /**
     * Cleanup expired sessions.
     */
    public static function cleanup() {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_scanner_sessions';
        $wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE expires_at < %s", current_time( 'mysql' ) ) );
    }
}
