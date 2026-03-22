<?php

namespace KueueEvents\Core\Modules\POS;

class POSController {

    private $namespace = 'kq/v1';
    private $rest_base = 'pos';

    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/issue', [
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'issue_ticket' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
        ] );
    }

    /**
     * Permission check for POS operations.
     */
    public function permissions_check( $request ) {
        return current_user_can( 'manage_kq_tickets' );
    }

    /**
     * Quick sell flow.
     */
    public function issue_ticket( $request ) {
        $event_id = $request->get_param( 'event_id' );
        $ticket_type_id = $request->get_param( 'ticket_type_id' );
        $attendee_data = $request->get_param( 'attendee' ); // array with first_name, last_name, email, phone
        $booking_slot_id = $request->get_param( 'booking_slot_id' );
        $seat_id = $request->get_param( 'seat_id' );
        $auto_checkin = $request->get_param( 'auto_checkin' ) === 'yes';

        // 1. Create/Identify Attendee
        $attendee_repo = new \KueueEvents\Core\Modules\Attendees\AttendeeRepository();
        $attendee_id = $attendee_repo->create( [
            'event_id'       => $event_id,
            'organizer_id'   => get_current_user_id(), // simplified, normally fetch organizer from user
            'ticket_type_id' => $ticket_type_id,
            'first_name'     => $attendee_data['first_name'] ?? 'Guest',
            'last_name'      => $attendee_data['last_name'] ?? 'POS',
            'email'          => $attendee_data['email'] ?? 'pos@guest.com',
            'phone'          => $attendee_data['phone'] ?? '',
            'status'         => 'confirmed',
            'source'         => 'pos'
        ] );

        // 2. Issue Ticket
        $ticket_id = \KueueEvents\Core\Modules\Tickets\TicketGenerator::issue_ticket( $attendee_id, $ticket_type_id, [
            'booking_slot_id' => $booking_slot_id,
            'seat_id'         => $seat_id,
        ] );

        if ( ! $ticket_id ) {
            return new \WP_REST_Response( [ 'success' => false, 'message' => 'Ticket issuance failed.' ], 400 );
        }

        // 3. Record Commission
        $ticket_type = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_by_id( $ticket_type_id );
        if ( $ticket_type ) {
             \KueueEvents\Core\Modules\Finance\CommissionService::record_sale( $event_id, get_current_user_id(), $ticket_type->price );
        }

        // 4. Handle Seat/Slot capacity
        if ( $booking_slot_id ) {
             \KueueEvents\Core\Modules\Bookings\BookingRepository::increment_sold_count( $booking_slot_id );
        }
        if ( $seat_id ) {
             \KueueEvents\Core\Modules\Seating\SeatingRepository::mark_seat_sold( $seat_id );
        }

        // 5. Auto Check-in if requested
        if ( $auto_checkin ) {
             $ticket = \KueueEvents\Core\Modules\Tickets\TicketRepository::get_by_id( $ticket_id );
             \KueueEvents\Core\Modules\Checkins\CheckinService::process_scan( $ticket->secure_token, get_current_user_id() );
        }

        return new \WP_REST_Response( [ 'success' => true, 'ticket_id' => $ticket_id ], 200 );
    }
}
