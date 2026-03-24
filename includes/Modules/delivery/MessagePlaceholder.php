<?php

namespace KueueEvents\Core\Modules\Delivery;

class MessagePlaceholder {

    /**
     * Map of placeholders to values.
     */
    public static function get_placeholders( $ticket_id ) {
        global $wpdb;
        $table_tickets = $wpdb->prefix . 'kq_tickets';
        $table_attendees = $wpdb->prefix . 'kq_attendees';

        $ticket = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_tickets WHERE id = %d", $ticket_id ) );
        if ( ! $ticket ) return [];

        $attendee = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_attendees WHERE id = %d", $ticket->attendee_id ) );
        if ( ! $attendee ) return [];

        $event = get_post( $ticket->event_id );
        $event_name = $event ? $event->post_title : '';
        $ticket_url = home_url( '/kq-ticket/' . $ticket->secure_token );

        return [
            '{first_name}'   => $attendee->first_name,
            '{last_name}'    => $attendee->last_name,
            '{full_name}'    => $attendee->first_name . ' ' . $attendee->last_name,
            '{email}'        => $attendee->email,
            '{phone}'        => $attendee->phone,
            '{event_name}'   => $event_name,
            '{ticket_number}'=> $ticket->ticket_number,
            '{ticket_link}'  => $ticket_url,
            '{order_id}'     => '#' . $ticket->order_id,
        ];
    }

    /**
     * Replace placeholders in a message string.
     */
    public static function process( $message, $ticket_id ) {
        $placeholders = self::get_placeholders( $ticket_id );
        
        // Custom filter for other plugins/modules to add placeholders
        $placeholders = apply_filters( 'kq_message_placeholders', $placeholders, $ticket_id );

        return str_replace( array_keys( $placeholders ), array_values( $placeholders ), $message );
    }
}
