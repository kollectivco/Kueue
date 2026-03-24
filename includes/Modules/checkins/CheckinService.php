<?php

namespace KueueEvents\Core\Modules\Checkins;

use KueueEvents\Core\Modules\Tickets\TicketRepository;
use KueueEvents\Core\Modules\Vendors\OrganizerRepository;

class CheckinService {

    /**
     * Validate and process a ticket scan.
     */
    public static function process_scan( $token, $current_user_id, $options = [] ) {
        // 1) Sanitize token
        if ( strpos( $token, 'kq:t:' ) === 0 ) {
            $token = substr( $token, 5 );
        }
        
        $ticket = TicketRepository::get_by_secure_token( $token );
        if ( ! $ticket ) {
            return [ 'status' => 'invalid', 'message' => __( 'Ticket not found.', 'kueue-events-core' ) ];
        }

        // 2) Permission Check
        $organizer_id = self::get_user_organizer_id( $current_user_id );
        if ( $organizer_id && (int) $ticket->organizer_id !== (int) $organizer_id ) {
            return [ 'status' => 'forbidden', 'message' => __( 'Unauthorized: This ticket belongs to another organizer.', 'kueue-events-core' ) ];
        }

        // 3) Basic Ticket Validation
        if ( $ticket->ticket_status !== 'active' ) {
            return [ 'status' => 'inactive', 'message' => __( 'This ticket is inactive or cancelled.', 'kueue-events-core' ) ];
        }

        // 4) Event Lock Check
        if ( !empty( $options['event_id'] ) && (int) $ticket->event_id !== (int) $options['event_id'] ) {
            return [ 'status' => 'wrong_event', 'message' => __( 'Ticket belongs to a different event.', 'kueue-events-core' ) ];
        }

        // 5) Logic based on scan mode
        $mode = $options['mode'] ?? 'auto'; // auto, checkin, checkout

        $attendee = \KueueEvents\Core\Modules\Attendees\AttendeeRepository::get_by_id( $ticket->attendee_id );
        $event = get_post( $ticket->event_id );
        $ticket_type = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_by_id( $ticket->ticket_type_id );

        $response_data = [
            'attendee_name'  => $attendee ? $attendee->first_name . ' ' . $attendee->last_name : 'Unknown',
            'event_name'     => $event ? $event->post_title : 'Unknown',
            'ticket_type'    => $ticket_type ? $ticket_type->name : 'Unknown',
            'ticket_number'  => $ticket->ticket_number,
            'checkin_status' => $ticket->checkin_status,
        ];

        switch ( $mode ) {
            case 'checkout':
                return self::handle_checkout( $ticket, $current_user_id, $response_data );
            case 'checkin':
                return self::handle_checkin( $ticket, $current_user_id, $response_data );
            case 'auto':
            default:
                if ( $ticket->checkin_status === 'checked_in' ) {
                    // Check if checkout is allowed after checkin via global settings
                    $allow_checkout = get_option( 'kq_allow_checkout_after_checkin', false );
                    if ( $allow_checkout ) {
                        return self::handle_checkout( $ticket, $current_user_id, $response_data );
                    } else {
                        return array_merge( $response_data, [ 'status' => 'already_used', 'message' => __( 'Already checked in.', 'kueue-events-core' ) ] );
                    }
                } else {
                    return self::handle_checkin( $ticket, $current_user_id, $response_data );
                }
        }
    }

    private static function handle_checkin( $ticket, $user_id, $data ) {
        if ( $ticket->checkin_status === 'checked_in' ) {
            return array_merge( $data, [ 'status' => 'already_used', 'message' => __( 'Already checked in.', 'kueue-events-core' ) ] );
        }

        $now = current_time( 'mysql' );
        TicketRepository::save( [
            'checkin_status'  => 'checked_in',
            'last_checkin_at' => $now
        ], $ticket->id );

        CheckinRepository::log([
            'ticket_id'          => $ticket->id,
            'event_id'           => $ticket->event_id,
            'organizer_id'       => $ticket->organizer_id,
            'scanned_by_user_id' => $user_id,
            'scan_type'          => 'checkin',
            'result_status'      => 'success',
        ]);

        return array_merge( $data, [ 
            'status'         => 'valid', 
            'message'        => __( 'Check-in successful!', 'kueue-events-core' ),
            'checkin_status' => 'checked_in'
        ]);
    }

    private static function handle_checkout( $ticket, $user_id, $data ) {
        if ( $ticket->checkin_status === 'not_checked_in' || $ticket->checkin_status === 'checked_out' ) {
             return array_merge( $data, [ 'status' => 'invalid', 'message' => __( 'Cannot check-out: Ticket not checked in.', 'kueue-events-core' ) ] );
        }

        TicketRepository::save( [
            'checkin_status'  => 'checked_out',
        ], $ticket->id );

        CheckinRepository::log([
            'ticket_id'          => $ticket->id,
            'event_id'           => $ticket->event_id,
            'organizer_id'       => $ticket->organizer_id,
            'scanned_by_user_id' => $user_id,
            'scan_type'          => 'checkout',
            'result_status'      => 'success',
        ]);

        return array_merge( $data, [ 
            'status'         => 'valid', 
            'message'        => __( 'Check-out successful!', 'kueue-events-core' ),
            'checkin_status' => 'checked_out'
        ]);
    }

    private static function get_user_organizer_id( $user_id ) {
        if ( user_can( $user_id, 'manage_options' ) ) return null;
        $org = OrganizerRepository::get_by_user_id( $user_id );
        return $org ? $org->id : -1; // -1 to block if user has no organizer account but has the role
    }
}
