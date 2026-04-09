<?php

namespace KueueEvents\Core\Core;

class AuditLogger {

    public static function log( $event_type, $object_type = null, $object_id = null, $details = '', $severity = 'info' ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_activity_logs';

        $wpdb->insert( $table, [
            'user_id'     => get_current_user_id() ?: null,
            'event_type'  => $event_type,
            'object_type' => $object_type,
            'object_id'   => $object_id,
            'details'     => is_array($details) ? json_encode($details) : $details,
            'severity'    => $severity,
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at'  => current_time( 'mysql' )
        ] );
    }

    public static function info( $event, $obj_type = null, $obj_id = null, $msg = '' ) {
        self::log( $event, $obj_type, $obj_id, $msg, 'info' );
    }

    public static function warning( $event, $obj_type = null, $obj_id = null, $msg = '' ) {
        self::log( $event, $obj_type, $obj_id, $msg, 'warning' );
    }

    public static function error( $event, $obj_type = null, $obj_id = null, $msg = '' ) {
        self::log( $event, $obj_type, $obj_id, $msg, 'error' );
    }
}
