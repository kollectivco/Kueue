<?php

namespace KueueEvents\Core\Modules\Bookings;

class BookingRepository {

    /**
     * Get booking dates for an event.
     */
    public static function get_dates( $event_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_booking_dates';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table WHERE event_id = %d ORDER BY event_date ASC",
            $event_id
        ) );
    }

    /**
     * Get slots for a specific booking date.
     */
    public static function get_slots( $date_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_booking_slots';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table WHERE booking_date_id = %d ORDER BY start_time ASC",
            $date_id
        ) );
    }

    /**
     * Get a specific slot by ID.
     */
    public static function get_slot( $slot_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_booking_slots';
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $slot_id
        ) );
    }

    /**
     * Increment sold count for a slot.
     */
    public static function increment_sold_count( $slot_id, $count = 1 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_booking_slots';
        return $wpdb->query( $wpdb->prepare(
            "UPDATE $table SET sold_count = sold_count + %d WHERE id = %d AND (sold_count + %d) <= capacity",
            $count, $slot_id, $count
        ) );
    }
}
