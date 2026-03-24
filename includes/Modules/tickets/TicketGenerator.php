<?php

namespace KueueEvents\Core\Modules\Tickets;

use KueueEvents\Core\Modules\Delivery\DeliveryManager;

class TicketGenerator {

    /**
     * Issue a single ticket.
     */
    public static function issue_ticket( $attendee_id, $ticket_type_id, $meta = [] ) {
        $attendee = \KueueEvents\Core\Modules\Attendees\AttendeeRepository::get_by_id( $attendee_id );
        if ( ! $attendee ) {
            return false;
        }

        $ticket_type = TicketTypeRepository::get_by_id( $ticket_type_id );
        if ( ! $ticket_type ) {
            return false;
        }

        // Generate ticket number and token
        $ticket_number = TicketNumberGenerator::generate_ticket_number( $attendee->event_id );
        $secure_token = TicketNumberGenerator::generate_secure_token();

        $data = [
            'event_id'       => $attendee->event_id,
            'organizer_id'   => $attendee->organizer_id,
            'attendee_id'    => $attendee->id,
            'ticket_type_id' => $ticket_type->id,
            'ticket_number'  => $ticket_number,
            'secure_token'   => $secure_token,
            'ticket_status'  => 'active',
            'delivery_status'=> 'not_sent',
            'booking_date_id'=> $meta['booking_date_id'] ?? null,
            'booking_slot_id'=> $meta['booking_slot_id'] ?? null,
            'seating_map_id' => $meta['seating_map_id'] ?? null,
            'section_id'     => $meta['section_id'] ?? null,
            'row_id'         => $meta['row_id'] ?? null,
            'seat_id'        => $meta['seat_id'] ?? null,
            'seat_label'     => $meta['seat_label'] ?? null,
            'issued_at'      => current_time( 'mysql' ),
        ];

        $ticket_id = TicketRepository::save( $data );

        if ( $ticket_id ) {
            // Update sold quantity for ticket type
            self::update_sold_quantity( $ticket_type->id );
            
            // Queue Delivery
            self::queue_ticket_delivery( $ticket_id );
            
            return $ticket_id;
        }

        return false;
    }

    /**
     * Update sold quantity.
     */
    public static function update_sold_quantity( $ticket_type_id ) {
        global $wpdb;
        $table_tt = $wpdb->prefix . 'kq_ticket_types';
        $table_tickets = $wpdb->prefix . 'kq_tickets';
        
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(id) FROM $table_tickets WHERE ticket_type_id = %d AND ticket_status != 'cancelled'",
            $ticket_type_id
        ) );

        $wpdb->update( $table_tt, [ 'sold_quantity' => $count ], [ 'id' => $ticket_type_id ] );
    }

    /**
     * Queue ticket delivery based on event settings.
     */
    public static function queue_ticket_delivery( $ticket_id ) {
        $ticket = TicketRepository::get_by_id( $ticket_id );
        if ( ! $ticket ) return;

        $event_id = $ticket->event_id;
        $attendee = \KueueEvents\Core\Modules\Attendees\AttendeeRepository::get_by_id( $ticket->attendee_id );
        if ( ! $attendee ) return;

        $enable_whatsapp = get_post_meta( $event_id, '_kq_enable_whatsapp_delivery', true );
        $enable_sms = get_post_meta( $event_id, '_kq_enable_sms_delivery', true );
        $enable_email = get_post_meta( $event_id, '_kq_enable_email_delivery', true );

        $wa_acc_id = get_post_meta( $event_id, '_kq_whatsapp_gateway_account_id', true );
        $sms_acc_id = get_post_meta( $event_id, '_kq_sms_gateway_account_id', true );

        $ticket_url = KQ_PLUGIN_URL . 'includes/Modules/Tickets/views/ticket-web-view.php?id=' . $ticket_id;
        $event_name = get_the_title( $event_id );

        $message = sprintf( 
            __( "Hi %s, your ticket for %s is ready! View it here: %s", 'kueue-events-core' ),
            $attendee->first_name,
            $event_name,
            $ticket_url
        );

        // 1. Queue WhatsApp
        if ( $enable_whatsapp && $wa_acc_id ) {
            DeliveryManager::add_to_queue( 'whatsapp', $wa_acc_id, [
                'to'      => $attendee->phone,
                'message' => $message,
                'context' => [ 'ticket_id' => $ticket_id ]
            ] );
        }

        // 2. Queue SMS
        if ( $enable_sms && $sms_acc_id ) {
            DeliveryManager::add_to_queue( 'sms', $sms_acc_id, [
                'to'      => $attendee->phone,
                'message' => $message,
                'context' => [ 'ticket_id' => $ticket_id ]
            ] );
        }

        // 3. Send Email (Synchronous or also queue? Usually email is safe to send now unless bulk)
        if ( $enable_email ) {
            $subject = sprintf( __( "Your Ticket: %s", 'kueue-events-core' ), $event_name );
            wp_mail( $attendee->email, $subject, $message );
        }
    }
}
