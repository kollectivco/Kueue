<?php

namespace KueueEvents\Core\Modules\Attendees;

class AttendeeRepository {

    /**
     * Get attendee by ID.
     */
    public static function get_by_id( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_attendees';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );
    }

    /**
     * Get attendees for an event.
     */
    public static function get_by_event_id( $event_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_attendees';
        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE event_id = %d ORDER BY id DESC", $event_id ) );
    }

    /**
     * Get all attendees scoped by organizer if provided.
     */
    public static function get_all( $organizer_id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_attendees';
        
        if ( $organizer_id ) {
            return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE organizer_id = %d ORDER BY id DESC", $organizer_id ) );
        }

        return $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC" );
    }

    /**
     * Save attendee.
     */
    public static function save( $data, $id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_attendees';

        if ( $id ) {
            $wpdb->update( $table, $data, [ 'id' => $id ] );
            return $id;
        } else {
            $wpdb->insert( $table, $data );
            return $wpdb->insert_id;
        }
    }

    /**
     * Delete attendee.
     */
    public static function delete( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_attendees';
        return $wpdb->delete( $table, [ 'id' => $id ] );
    }
}
