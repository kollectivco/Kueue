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

        register_rest_route( 'kq/v1', '/tickets/types', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_ticket_types' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
        ] );

        register_rest_route( 'kq/v1', '/pos/event-settings', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_event_settings' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
        ] );
    }

    /**
     * Get event settings for POS (bookings/seating).
     */
    public function get_event_settings( $request ) {
        $event_id = $request->get_param( 'event_id' );
        if ( ! $event_id ) return [];

        $enable_bookings = get_post_meta( $event_id, '_kq_enable_bookings', true );
        $enable_seating = get_post_meta( $event_id, '_kq_enable_seating', true );

        $data = [
            'enable_bookings' => (bool) $enable_bookings,
            'enable_seating'  => (bool) $enable_seating,
            'booking_dates'   => $enable_bookings ? \KueueEvents\Core\Modules\Bookings\BookingRepository::get_dates( $event_id ) : [],
            'seating_map'     => $enable_seating ? \KueueEvents\Core\Modules\Seating\SeatingRepository::get_map_by_event( $event_id ) : null
        ];

        return $data;
    }

    /**
     * Get ticket types for an event.
     */
    public function get_ticket_types( $request ) {
        $event_id = $request->get_param( 'event_id' );
        if ( ! $event_id ) return [];

        $types = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_by_event_id( $event_id );
        
        // Add currency for the UI
        foreach ( $types as &$t ) {
            $t->currency = 'EGP'; // Default for Cairo context
        }

        return $types;
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
            'organizer_id'   => \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_organizer_id_by_event($event_id),
            'ticket_type_id' => $ticket_type_id,
            'first_name'     => $attendee_data['first_name'] ?? 'Guest',
            'last_name'      => $attendee_data['last_name'] ?? 'POS',
            'email'          => $attendee_data['email'] ?? 'pos@guest.com',
            'phone'          => $attendee_data['phone'] ?? '',
            'status'         => 'confirmed',
            'source'         => 'pos'
        ] );

        // 2. Issue Ticket (Handles capacity/seats inside)
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
             \KueueEvents\Core\Modules\Finance\CommissionService::record_sale( 
                 $event_id, 
                 \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_organizer_id_by_event($event_id), 
                 $ticket_type->price 
             );
        }

        // 4. Auto Check-in if requested
        if ( $auto_checkin ) {
             $ticket = \KueueEvents\Core\Modules\Tickets\TicketRepository::get_by_id( $ticket_id );
             \KueueEvents\Core\Modules\Checkins\CheckinService::process_scan( $ticket->secure_token, get_current_user_id() );
        }

        return new \WP_REST_Response( [ 'success' => true, 'ticket_id' => $ticket_id ], 200 );
    }
}
