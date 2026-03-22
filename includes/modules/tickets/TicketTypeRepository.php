<?php

namespace KueueEvents\Core\Modules\Tickets;

class TicketTypeRepository {

    /**
     * Get ticket type by ID.
     */
    public static function get_by_id( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_ticket_types';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );
    }

    /**
     * Get ticket types for an event.
     */
    public static function get_by_event_id( $event_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_ticket_types';
        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE event_id = %d ORDER BY sort_order ASC", $event_id ) );
    }

    /**
     * Get all ticket types scoped by organizer if provided.
     */
    public static function get_all( $organizer_id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_ticket_types';
        
        if ( $organizer_id ) {
            // Need to join with posts to check organizer meta
            return $wpdb->get_results( $wpdb->prepare(
                "SELECT tt.* FROM $table tt 
                 JOIN {$wpdb->postmeta} pm ON tt.event_id = pm.post_id 
                 WHERE pm.meta_key = '_kq_organizer_id' AND pm.meta_value = %d 
                 ORDER BY tt.event_id DESC, tt.sort_order ASC",
                $organizer_id
            ) );
        }

        return $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC" );
    }

    /**
     * Save ticket type.
     */
    public static function save( $data, $id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_ticket_types';

        if ( $id ) {
            $wpdb->update( $table, $data, [ 'id' => $id ] );
            return $id;
        } else {
            $wpdb->insert( $table, $data );
            return $wpdb->insert_id;
        }
    }

    /**
     * Delete ticket type.
     */
    public static function delete( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_ticket_types';
        return $wpdb->delete( $table, [ 'id' => $id ] );
    }
}
