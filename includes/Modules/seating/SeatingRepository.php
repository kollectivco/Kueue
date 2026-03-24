<?php

namespace KueueEvents\Core\Modules\Seating;

class SeatingRepository {

    /**
     * Get seating map for an event.
     */
    public static function get_map_by_event( $event_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_seating_maps';
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE event_id = %d",
            $event_id
        ) );
    }

    /**
     * Get sections for a map.
     */
    public static function get_sections( $map_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_seating_sections';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table WHERE map_id = %d ORDER BY sort_order ASC",
            $map_id
        ) );
    }

    /**
     * Get rows for a section.
     */
    public static function get_rows( $section_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_seating_rows';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table WHERE section_id = %d ORDER BY sort_order ASC",
            $section_id
        ) );
    }

    /**
     * Get seats for a row.
     */
    public static function get_seats( $row_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_seating_seats';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table WHERE row_id = %d",
            $row_id
        ) );
    }

    /**
     * Mark seat as sold.
     */
    public static function mark_seat_sold( $seat_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_seating_seats';
        return $wpdb->update( $table, [ 'status' => 'sold' ], [ 'id' => $seat_id, 'status' => 'available' ] );
    }

    /**
     * Get a specific seat.
     */
    public static function get_seat( $seat_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_seating_seats';
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $seat_id
        ) );
    }
}
