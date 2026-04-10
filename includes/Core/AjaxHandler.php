<?php

namespace KueueEvents\Core\Core;

class AjaxHandler {

    public function run() {
        // Core
        add_action( 'wp_ajax_kq_get_ticket_types', [ $this, 'get_ticket_types' ] );
        add_action( 'wp_ajax_kq_get_attendees', [ $this, 'get_attendees' ] );
        
        // Frontend Actions
        add_action( 'wp_ajax_kq_add_to_cart', [ $this, 'add_to_cart' ] );
        add_action( 'wp_ajax_nopriv_kq_add_to_cart', [ $this, 'add_to_cart' ] );
        
        // Booking / Seating
        add_action( 'wp_ajax_kq_get_slots', [ $this, 'get_slots' ] );
        add_action( 'wp_ajax_nopriv_kq_get_slots', [ $this, 'get_slots' ] );
        add_action( 'wp_ajax_kq_get_seating_data', [ $this, 'get_seating_data' ] );
        add_action( 'wp_ajax_nopriv_kq_get_seating_data', [ $this, 'get_seating_data' ] );

        // Organizer Actions
        add_action( 'wp_ajax_kq_request_payout', [ $this, 'request_payout' ] );
        add_action( 'wp_ajax_kq_resend_ticket', [ $this, 'resend_ticket' ] );
        add_action( 'wp_ajax_kq_cancel_ticket', [ $this, 'cancel_ticket' ] );

        // Admin Search & Quick Create
        add_action( 'wp_ajax_kq_search_venues', [ $this, 'search_venues' ] );
        add_action( 'wp_ajax_kq_search_organizers', [ $this, 'search_organizers' ] );
        add_action( 'wp_ajax_kq_create_venue', [ $this, 'create_venue' ] );
        add_action( 'wp_ajax_kq_create_organizer', [ $this, 'create_organizer' ] );
        add_action( 'wp_ajax_kq_gateway_test_send', [ $this, 'gateway_test_send' ] );
    }

    public function get_slots() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) wp_send_json_error( [ 'message' => 'Security check failed.' ] );
        $date_id = (int) $_POST['date_id'];
        $slots = \KueueEvents\Core\Modules\Bookings\BookingRepository::get_slots( $date_id );
        $available_slots = array_filter( $slots, function( $slot ) { return $slot->sold_count < $slot->capacity; });
        wp_send_json_success( array_values( $available_slots ) );
    }

    public function get_seating_data() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) wp_send_json_error( [ 'message' => 'Security check failed.' ] );
        $event_id = (int) $_POST['event_id'];
        $map = \KueueEvents\Core\Modules\Seating\SeatingRepository::get_map_by_event( $event_id );
        if ( ! $map ) wp_send_json_error( [ 'message' => 'No map found.' ] );
        $sections = \KueueEvents\Core\Modules\Seating\SeatingRepository::get_sections( $map->id );
        foreach ( $sections as &$section ) {
            $section->rows = \KueueEvents\Core\Modules\Seating\SeatingRepository::get_rows( $section->id );
            foreach ( $section->rows as &$row ) {
                $row->seats = \KueueEvents\Core\Modules\Seating\SeatingRepository::get_seats( $row->id );
            }
        }
        wp_send_json_success( [ 'map' => $map, 'sections' => $sections ] );
    }

    public function add_to_cart() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) wp_send_json_error( [ 'message' => 'Security check failed.' ] );
        if ( ! class_exists( 'WooCommerce' ) ) wp_send_json_error( [ 'message' => 'WooCommerce not active.' ] );

        $ticket_type_id = (int) $_POST['ticket_type_id'];
        $qty = (int) $_POST['qty'] ?: 1;
        $attendee_data = (array) ($_POST['attendees'] ?? []);
        $booking_slot_id = !empty($_POST['booking_slot_id']) ? (int) $_POST['booking_slot_id'] : null;
        $seat_id = !empty($_POST['seat_id']) ? (int) $_POST['seat_id'] : null;

        $tt = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_by_id( $ticket_type_id );
        if ( ! $tt || ! $tt->wc_product_id ) wp_send_json_error( [ 'message' => 'Invalid ticket type.' ] );

        // 1. Availability Checks
        if ( $booking_slot_id ) {
            $slot = \KueueEvents\Core\Modules\Bookings\BookingRepository::get_slot( $booking_slot_id );
            if ( ! $slot || ( $slot->sold_count + $qty ) > $slot->capacity ) {
                wp_send_json_error( [ 'message' => 'This time slot is full.' ] );
            }
        }

        if ( $seat_id ) {
            $seat = \KueueEvents\Core\Modules\Seating\SeatingRepository::get_seat( $seat_id );
            if ( ! $seat || $seat->status !== 'available' ) {
                wp_send_json_error( [ 'message' => 'This seat is no longer available.' ] );
            }
        }

        // 2. Add to Cart
        $cart_item_data = [
            '_kq_ticket_type_id' => $tt->id,
            '_kq_attendee_data'  => $attendee_data,
            '_kq_booking_slot_id' => $booking_slot_id,
            '_kq_seat_id'         => $seat_id,
        ];

        $cart_id = WC()->cart->add_to_cart( $tt->wc_product_id, $qty, 0, [], $cart_item_data );

        if ( $cart_id ) {
            wp_send_json_success( [ 'redirect_url' => wc_get_checkout_url() ] );
        } else {
            wp_send_json_error( [ 'message' => 'Failed to add to cart.' ] );
        }
    }

    public function request_payout() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) wp_send_json_error( [ 'message' => 'Security check.' ] );
        $user_id = get_current_user_id();
        $org = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( $user_id );
        if ( ! $org ) wp_send_json_error( [ 'message' => 'Organizer not found.' ] );
        
        $stats = \KueueEvents\Core\Modules\Reports\ReportsService::get_global_summary( $org->id );
        if ( (float)($stats->net ?? 0) < 100 ) wp_send_json_error( [ 'message' => 'Insufficient balance.' ] );

        \KueueEvents\Core\Modules\Payouts\PayoutRepository::create([
            'organizer_id' => $org->id,
            'amount' => $stats->net,
            'payment_method' => 'manual'
        ]);
        wp_send_json_success( [ 'message' => 'Request sent.' ] );
    }

    public function resend_ticket() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) wp_send_json_error( [ 'message' => 'Security check.' ] );
        $ticket_id = (int) $_POST['id'];
        // Perm check
        $user_id = get_current_user_id();
        $ticket = \KueueEvents\Core\Modules\Tickets\TicketRepository::get_by_id( $ticket_id );
        $org = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( $user_id );
        if ( ! $ticket || ! $org || (int)$ticket->organizer_id !== (int)$org->id ) wp_send_json_error( [ 'message' => 'Denied.' ] );

        \KueueEvents\Core\Modules\Tickets\TicketGenerator::queue_ticket_delivery( $ticket_id );
        wp_send_json_success( [ 'message' => 'Ticket resent.' ] );
    }

    public function cancel_ticket() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) wp_send_json_error( [ 'message' => 'Security check.' ] );
        $ticket_id = (int) $_POST['id'];
        // Perm check
        $user_id = get_current_user_id();
        $ticket = \KueueEvents\Core\Modules\Tickets\TicketRepository::get_by_id( $ticket_id );
        $org = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( $user_id );
        if ( ! $ticket || ! $org || (int)$ticket->organizer_id !== (int)$org->id ) wp_send_json_error( [ 'message' => 'Denied.' ] );

        \KueueEvents\Core\Modules\Tickets\TicketRepository::cancel_ticket( $ticket_id );
        wp_send_json_success( [ 'message' => 'Ticket cancelled.' ] );
    }

    public function get_ticket_types() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) wp_send_json_error( [ 'message' => 'Security check.' ] );
        if ( ! current_user_can( 'manage_kq_tickets' ) ) wp_send_json_error( [ 'message' => 'Unauthorized.' ] );

        $event_id = (int) $_POST['event_id'];
        $ticket_types = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_by_event_id( $event_id );
        wp_send_json_success( $ticket_types );
    }

    public function get_attendees() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) wp_send_json_error( [ 'message' => 'Security check.' ] );
        if ( ! current_user_can( 'manage_kq_tickets' ) ) wp_send_json_error( [ 'message' => 'Unauthorized.' ] );

        $event_id = (int) $_POST['event_id'];
        $attendees = \KueueEvents\Core\Modules\Attendees\AttendeeRepository::get_by_event_id( $event_id );
        wp_send_json_success( $attendees );
    }

    /**
     * Search Venues (CPT kq_venue)
     */
    public function search_venues() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) wp_send_json_error();
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error();

        $term = sanitize_text_field( $_POST['term'] ?? '' );
        
        $args = [
            'post_type'      => 'kq_venue',
            's'              => $term,
            'posts_per_page' => 20,
            'post_status'    => 'publish'
        ];

        $venues = get_posts( $args );
        $results = [];

        foreach ( $venues as $v ) {
            $results[] = [
                'id'   => $v->ID,
                'text' => $v->post_title . ' (' . get_post_meta( $v->ID, '_kq_venue_city', true ) . ')'
            ];
        }

        wp_send_json_success( $results );
    }

    /**
     * Search Organizers (Custom Table)
     */
    public function search_organizers() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) wp_send_json_error();
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error();

        $term = sanitize_text_field( $_POST['term'] ?? '' );
        $results = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::search( $term );
        
        $formatted = [];
        foreach ( $results as $r ) {
            $formatted[] = [
                'id'   => $r->id,
                'text' => $r->organizer_name . ' (' . $r->email . ')'
            ];
        }

        wp_send_json_success( $formatted );
    }

    /**
     * Quick Create Venue
     */
    public function create_venue() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) wp_send_json_error();
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error();

        $name = sanitize_text_field( $_POST['name'] ?? '' );
        if ( empty( $name ) ) wp_send_json_error( [ 'message' => 'Name required.' ] );

        $post_id = wp_insert_post([
            'post_type'    => 'kq_venue',
            'post_title'   => $name,
            'post_status'  => 'publish',
            'post_content' => sanitize_textarea_field( $_POST['description'] ?? '' )
        ]);

        if ( is_wp_error( $post_id ) ) wp_send_json_error( [ 'message' => $post_id->get_error_message() ] );

        update_post_meta( $post_id, '_kq_venue_address', sanitize_text_field( $_POST['address'] ?? '' ) );
        update_post_meta( $post_id, '_kq_venue_city', sanitize_text_field( $_POST['city'] ?? '' ) );
        update_post_meta( $post_id, '_kq_venue_country', sanitize_text_field( $_POST['country'] ?? '' ) );
        update_post_meta( $post_id, '_kq_venue_google_maps_url', esc_url_raw( $_POST['maps_url'] ?? '' ) );
        update_post_meta( $post_id, '_kq_venue_lat', sanitize_text_field( $_POST['lat'] ?? '' ) );
        update_post_meta( $post_id, '_kq_venue_lng', sanitize_text_field( $_POST['lng'] ?? '' ) );

        wp_send_json_success([
            'id'   => $post_id,
            'text' => $name
        ]);
    }

    /**
     * Quick Create Organizer
     */
    public function create_organizer() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) wp_send_json_error();
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error();

        $name = sanitize_text_field( $_POST['name'] ?? '' );
        $email = sanitize_email( $_POST['email'] ?? '' );
        if ( empty( $name ) || empty( $email ) ) wp_send_json_error( [ 'message' => 'Name and email required.' ] );

        $data = [
            'organizer_name'   => $name,
            'organizer_slug'   => sanitize_title( $name ),
            'email'            => $email,
            'phone'            => sanitize_text_field( $_POST['phone'] ?? '' ),
            'status'           => sanitize_text_field( $_POST['status'] ?? 'active' ),
            'commission_type'  => sanitize_text_field( $_POST['commission_type'] ?? 'percentage' ),
            'commission_value' => (float) ($_POST['commission_value'] ?? 0),
            'user_id'          => !empty($_POST['user_id']) ? (int) $_POST['user_id'] : null
        ];

        $id = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::save( $data );
        if ( ! $id ) wp_send_json_error( [ 'message' => 'Failed to save.' ] );

        wp_send_json_success([
            'id'   => $id,
            'text' => $name
        ]);
    }

    /**
     * Gateway Test Send (SMS / WhatsApp)
     */
    public function gateway_test_send() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Security check failed.' ] );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Unauthorized.' ] );
        }

        $account_id = (int) ( $_POST['account_id'] ?? 0 );
        $channel    = sanitize_text_field( $_POST['channel'] ?? '' );
        $phone      = sanitize_text_field( $_POST['phone'] ?? '' );
        $message    = sanitize_text_field( $_POST['message'] ?? 'Kueue test message.' );

        if ( ! $account_id || ! $phone ) {
            wp_send_json_error( [ 'message' => 'Account ID and phone number are required.' ] );
        }

        $provider = \KueueEvents\Core\Modules\Gateways\GatewayManager::get_provider( $account_id );

        if ( ! $provider ) {
            wp_send_json_error( [ 'message' => 'Provider could not be loaded. Check account credentials are saved.' ] );
        }

        try {
            if ( $channel === 'sms' ) {
                $result = $provider->send_sms( $phone, $message );
            } elseif ( $channel === 'whatsapp' ) {
                $result = $provider->send_message( $phone, $message );
            } else {
                wp_send_json_error( [ 'message' => 'Unknown channel.' ] );
            }

            if ( $result ) {
                wp_send_json_success( [ 'message' => 'Test message sent successfully.' ] );
            } else {
                wp_send_json_error( [ 'message' => 'Provider returned a failure. Check your credentials.' ] );
            }
        } catch ( \Exception $e ) {
            wp_send_json_error( [ 'message' => $e->getMessage() ] );
        }
    }
}
