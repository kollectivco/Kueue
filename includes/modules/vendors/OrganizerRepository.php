<?php

namespace KueueEvents\Core\Modules\Vendors;

class OrganizerRepository {

    /**
     * Get organizer by ID.
     */
    public static function get_by_id( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_organizers';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );
    }

    /**
     * Get organizer by User ID.
     */
    public static function get_by_user_id( $user_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_organizers';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE user_id = %d", $user_id ) );
    }

    /**
     * Get organizer ID for a given event.
     */
    public static function get_organizer_id_by_event( $event_id ) {
        return (int) get_post_meta( $event_id, '_kq_organizer_id', true );
    }

    /**
     * Get all organizers.
     */
    public static function get_all() {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_organizers';
        return $wpdb->get_results( "SELECT * FROM $table ORDER BY organizer_name ASC" );
    }

    /**
     * Create or update organizer.
     */
    public static function save( $data, $id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_organizers';

        if ( $id ) {
            $wpdb->update( $table, $data, [ 'id' => $id ] );
            return $id;
        } else {
            $wpdb->insert( $table, $data );
            return $wpdb->insert_id;
        }
    }

    /**
     * Delete organizer.
     */
    public static function delete( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_organizers';
        return $wpdb->delete( $table, [ 'id' => $id ] );
    }
}
