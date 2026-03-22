<?php

namespace KueueEvents\Core\Modules\Tickets;

class TicketRepository {

    /**
     * Get ticket by ID.
     */
    public static function get_by_id( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_tickets';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );
    }

    /**
     * Get ticket by ticket number.
     */
    public static function get_by_ticket_number( $ticket_number ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_tickets';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE ticket_number = %s", $ticket_number ) );
    }

    /**
     * Get ticket by secure token.
     */
    public static function get_by_secure_token( $token ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_tickets';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE secure_token = %s", $token ) );
    }

    /**
     * Get tickets for an attendee.
     */
    public static function get_by_attendee_id( $attendee_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_tickets';
        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE attendee_id = %d", $attendee_id ) );
    }

    /**
     * Get paginated tickets.
     */
    public static function get_paged( $page = 1, $limit = 20, $organizer_id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_tickets';
        $offset = ( $page - 1 ) * $limit;
        
        $sql = "SELECT * FROM $table";
        $params = [];

        if ( $organizer_id ) {
            $sql .= " WHERE organizer_id = %d";
            $params[] = $organizer_id;
        }

        $sql .= " ORDER BY id DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }

    /**
     * Get all tickets scoped by organizer if provided.
     */
    public static function get_all( $organizer_id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_tickets';
        
        if ( $organizer_id ) {
            return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE organizer_id = %d ORDER BY id DESC", $organizer_id ) );
        }

        return $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC" );
    }

    /**
     * Save ticket.
     */
    public static function save( $data, $id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_tickets';

        if ( $id ) {
            $wpdb->update( $table, $data, [ 'id' => $id ] );
            return $id;
        } else {
            $wpdb->insert( $table, $data );
            return $wpdb->insert_id;
        }
    }

    /**
     * Cancel ticket and release slot/seat.
     */
    public static function cancel_ticket( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_tickets';
        $ticket = self::get_by_id( $id );
        if ( ! $ticket ) return false;

        // Release slot if exists
        if ( $ticket->booking_slot_id ) {
            $slots_table = $wpdb->prefix . 'kq_booking_slots';
            $wpdb->query( $wpdb->prepare( "UPDATE $slots_table SET sold_count = sold_count - 1 WHERE id = %d", $ticket->booking_slot_id ) );
        }

        // Release seat if exists
        if ( $ticket->seat_id ) {
            $seats_table = $wpdb->prefix . 'kq_seating_seats';
            $wpdb->update( $seats_table, [ 'status' => 'available' ], [ 'id' => $ticket->seat_id ] );
        }

        return $wpdb->update( $table, [ 'ticket_status' => 'cancelled' ], [ 'id' => $id ] );
    }

    /**
     * Delete ticket.
     */
    public static function delete( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_tickets';
        return $wpdb->delete( $table, [ 'id' => $id ] );
    }
}
