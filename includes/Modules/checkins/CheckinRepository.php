<?php

namespace KueueEvents\Core\Modules\Checkins;

class CheckinRepository {

    /**
     * Log a check-in event.
     */
    public static function log( $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_checkins';

        $wpdb->insert( $table, [
            'ticket_id'          => $data['ticket_id'],
            'event_id'           => $data['event_id'],
            'organizer_id'       => $data['organizer_id'],
            'scanned_by_user_id' => $data['scanned_by_user_id'],
            'scan_type'          => $data['scan_type'],
            'device_info'        => $data['device_info'] ?? '',
            'result_status'      => $data['result_status'],
            'note'               => $data['note'] ?? '',
            'created_at'         => current_time( 'mysql' )
        ] );

        return $wpdb->insert_id;
    }

    /**
     * Get logs for a ticket.
     */
    public static function get_by_ticket( $ticket_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_checkins';
        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE ticket_id = %d ORDER BY created_at DESC", $ticket_id ) );
    }

    /**
     * Get logs for an event.
     */
    public static function get_by_event( $event_id, $limit = 50 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_checkins';
        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE event_id = %d ORDER BY created_at DESC LIMIT %d", $event_id, $limit ) );
    }

    /**
     * Get all logs (admin).
     */
    public static function get_all( $limit = 100, $offset = 0 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_checkins';
        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table ORDER BY created_at DESC LIMIT %d OFFSET %d", $limit, $offset ) );
    }
}
