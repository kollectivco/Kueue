<?php

namespace KueueEvents\Core\Modules\Frontend;

class FrontendController {

    public function run() {
        // Register shortcodes (with Aliases)
        add_shortcode( 'kq_events', [ $this, 'render_events_list' ] );
        add_shortcode( 'kq_event', [ $this, 'render_event_single' ] );
        add_shortcode( 'kq_dashboard', [ $this, 'render_organizer_dashboard' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        // AJAX for ticket selection and checkout
        add_action( 'wp_ajax_kq_add_to_cart', [ $this, 'handle_add_to_cart' ] );
        add_action( 'wp_ajax_nopriv_kq_add_to_cart', [ $this, 'handle_add_to_cart' ] );

        // GDPR Hooks
        add_filter( 'wp_privacy_personal_data_exporters', [ $this, 'register_gdpr_exporter' ], 10 );
        add_filter( 'wp_privacy_personal_data_erasers', [ $this, 'register_gdpr_eraser' ], 10 );

        // AJAX for payout request
        add_action( 'wp_ajax_kq_request_payout', [ $this, 'handle_request_payout' ] );

        // AJAX for fetching slots
        add_action( 'wp_ajax_kq_get_slots', [ $this, 'handle_get_slots' ] );
        add_action( 'wp_ajax_nopriv_kq_get_slots', [ $this, 'handle_get_slots' ] );

        // AJAX for fetching seating data
        add_action( 'wp_ajax_kq_get_seating_data', [ $this, 'handle_get_seating_data' ] );
        add_action( 'wp_ajax_nopriv_kq_get_seating_data', [ $this, 'handle_get_seating_data' ] );

        // AJAX for ticket management
        add_action( 'wp_ajax_kq_resend_ticket', [ $this, 'handle_resend_ticket' ] );
        add_action( 'wp_ajax_kq_cancel_ticket', [ $this, 'handle_cancel_ticket' ] );
    }

    /**
     * Handle Resend Ticket via AJAX
     */
    public function handle_resend_ticket() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Security check failed.' ] );
        }

        $ticket_id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
        if ( ! $ticket_id ) {
            wp_send_json_error( [ 'message' => 'Invalid ticket ID.' ] );
        }

        // Logic to verify if user owns this ticket through organizer profile
        $user_id = get_current_user_id();
        $ticket = \KueueEvents\Core\Modules\Tickets\TicketRepository::get_by_id( $ticket_id );
        $organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( $user_id );

        if ( ! $ticket || ! $organizer || (int) $ticket->organizer_id !== (int) $organizer->id ) {
            wp_send_json_error( [ 'message' => 'Access denied.' ] );
        }

        \KueueEvents\Core\Modules\Tickets\TicketGenerator::queue_ticket_delivery( $ticket_id );
        wp_send_json_success( [ 'message' => 'Ticket delivery queued.' ] );
    }

    /**
     * Handle Cancel Ticket via AJAX
     */
    public function handle_cancel_ticket() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Security check failed.' ] );
        }

        $ticket_id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0;
        if ( ! $ticket_id ) {
            wp_send_json_error( [ 'message' => 'Invalid ticket ID.' ] );
        }

        $user_id = get_current_user_id();
        $ticket = \KueueEvents\Core\Modules\Tickets\TicketRepository::get_by_id( $ticket_id );
        $organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( $user_id );

        if ( ! $ticket || ! $organizer || (int) $ticket->organizer_id !== (int) $organizer->id ) {
            wp_send_json_error( [ 'message' => 'Access denied.' ] );
        }

        $result = \KueueEvents\Core\Modules\Tickets\TicketRepository::cancel_ticket( $ticket_id );
        if ( $result ) {
            wp_send_json_success( [ 'message' => 'Ticket cancelled successfully.' ] );
        } else {
            wp_send_json_error( [ 'message' => 'Failed to cancel ticket.' ] );
        }
    }

    /**
     * Handle Get Slots via AJAX
     */
    public function handle_get_slots() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Security check failed.' ] );
        }

        $date_id = isset( $_POST['date_id'] ) ? (int) $_POST['date_id'] : 0;
        if ( ! $date_id ) {
            wp_send_json_error( [ 'message' => 'Invalid date.' ] );
        }

        $slots = \KueueEvents\Core\Modules\Bookings\BookingRepository::get_slots( $date_id );
        
        // Filter to available slots only
        $available_slots = array_filter( $slots, function( $slot ) {
             return $slot->sold_count < $slot->capacity;
        });

        wp_send_json_success( array_values( $available_slots ) );
    }

    /**
     * Handle Get Seating Data via AJAX
     */
    public function handle_get_seating_data() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Security check failed.' ] );
        }

        $event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;
        if ( ! $event_id ) {
            wp_send_json_error( [ 'message' => 'Invalid event.' ] );
        }

        $map = \KueueEvents\Core\Modules\Seating\SeatingRepository::get_map_by_event( $event_id );
        if ( ! $map ) {
            wp_send_json_error( [ 'message' => 'No map found.' ] );
        }

        $sections = \KueueEvents\Core\Modules\Seating\SeatingRepository::get_sections( $map->id );
        foreach ( $sections as &$section ) {
            $section->rows = \KueueEvents\Core\Modules\Seating\SeatingRepository::get_rows( $section->id );
            foreach ( $section->rows as &$row ) {
                $row->seats = \KueueEvents\Core\Modules\Seating\SeatingRepository::get_seats( $row->id );
            }
        }

        wp_send_json_success( [
            'map'      => $map,
            'sections' => $sections
        ] );
    }

    /**
     * Enqueue Frontend Assets
     */
    public function enqueue_assets() {
        // Enqueue only if we have our shortcodes or on relevant pages
        global $post;
        if ( is_a( $post, 'WP_Post' ) && ( has_shortcode( $post->post_content, 'kq_events' ) || has_shortcode( $post->post_content, 'kq_event' ) || has_shortcode( $post->post_content, 'kq_dashboard' ) || $post->post_type === 'kq_event' ) ) {
            wp_enqueue_style( 'kq-design-system', KQ_PLUGIN_URL . 'assets/css/design-system.css', [], KQ_VERSION );
            wp_enqueue_style( 'kq-frontend-style', KQ_PLUGIN_URL . 'assets/css/frontend.css', ['kq-design-system'], KQ_VERSION );
            
            // FontAwesome
            wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', [], '6.4.0' );

            // Add scripts for AJAX
            wp_enqueue_script( 'kq-frontend-js', KQ_PLUGIN_URL . 'assets/js/frontend.js', ['jquery'], KQ_VERSION, true );
            wp_localize_script( 'kq-frontend-js', 'kq_ajax', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'kq-nonce' )
            ]);
        }
    }

    /**
     * Render Events List
     */
    public function render_events_list( $atts ) {
        // Use standard get_posts for safety
        $events = get_posts( [ 'post_type' => 'kq_event', 'posts_per_page' => -1, 'post_status' => 'publish' ] );

        ob_start();
        $view_path = KQ_PLUGIN_DIR . 'includes/Modules/Frontend/views/events-list.php';
        if ( file_exists( $view_path ) ) {
            include $view_path;
        } else {
            echo '<p>Events list view not found.</p>';
        }
        return ob_get_clean();
    }

    /**
     * Render Single Event Page
     */
    public function render_event_single( $atts ) {
        $a = shortcode_atts( [ 'id' => 0 ], $atts );
        $event_id = (int) $a['id'] ?: get_the_ID();
        
        $event = get_post( $event_id );
        if ( ! $event || $event->post_type !== 'kq_event' ) {
            return '<p>' . __( 'Event not found.', 'kueue-events-core' ) . '</p>';
        }

        $ticket_types = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_by_event_id( $event_id );
        
        // Advanced Data
        $enable_bookings = get_post_meta( $event_id, '_kq_enable_bookings', true );
        $enable_seating = get_post_meta( $event_id, '_kq_enable_seating', true );
        
        $booking_dates = $enable_bookings ? \KueueEvents\Core\Modules\Bookings\BookingRepository::get_dates( $event_id ) : [];
        $seating_map = $enable_seating ? \KueueEvents\Core\Modules\Seating\SeatingRepository::get_map_by_event( $event_id ) : null;

        ob_start();
        $view_path = KQ_PLUGIN_DIR . 'includes/Modules/Frontend/views/event-single.php';
        if ( file_exists( $view_path ) ) {
            include $view_path;
        } else {
            echo '<p>Event single view not found.</p>';
        }
        return ob_get_clean();
    }

    /**
     * Handle Add to Cart via AJAX (WC Integration)
     */
    public function handle_add_to_cart() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Security check failed.' ] );
        }

        if ( ! class_exists( 'WooCommerce' ) ) {
            wp_send_json_error( [ 'message' => 'WooCommerce not active.' ] );
        }

        $ticket_type_id = isset( $_POST['ticket_type_id'] ) ? (int) $_POST['ticket_type_id'] : 0;
        $qty = isset( $_POST['qty'] ) ? (int) $_POST['qty'] : 1;
        $attendee_data = isset( $_POST['attendees'] ) ? (array) $_POST['attendees'] : [];
        $booking_slot_id = isset( $_POST['booking_slot_id'] ) ? (int) $_POST['booking_slot_id'] : null;
        $seat_id = isset( $_POST['seat_id'] ) ? (int) $_POST['seat_id'] : null;

        $tt = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_by_id( $ticket_type_id );
        if ( ! $tt || ! $tt->wc_product_id ) {
            wp_send_json_error( [ 'message' => 'Invalid ticket type or no product linked.' ] );
        }

        // Sanitize attendee data
        $sanitized_attendees = [];
        foreach ( $attendee_data as $att ) {
            $sanitized_attendees[] = [
                'first_name' => sanitize_text_field( $att['first_name'] ?? '' ),
                'last_name'  => sanitize_text_field( $att['last_name'] ?? '' ),
                'email'      => sanitize_email( $att['email'] ?? '' ),
            ];
        }

        // Add to WC Cart with meta
        $cart_item_data = [
            '_kq_ticket_type_id' => $tt->id,
            '_kq_attendee_data'  => $sanitized_attendees,
            '_kq_booking_slot_id' => $booking_slot_id,
            '_kq_seat_id'         => $seat_id,
        ];

        try {
            // Clear existing Kueue items to ensure one ticket type / session if needed
            // Or just allow multiple. We'll allow multiple but ensure meta is distinct.
            $cart_id = WC()->cart->add_to_cart( $tt->wc_product_id, $qty, 0, [], $cart_item_data );
            if ( $cart_id ) {
                wp_send_json_success( [ 'redirect_url' => wc_get_checkout_url() ] );
            } else {
                wp_send_json_error( [ 'message' => 'Failed to add to cart.' ] );
            }
        } catch ( \Exception $e ) {
            wp_send_json_error( [ 'message' => $e->getMessage() ] );
        }
    }

    /**
     * Handle Request Payout via AJAX
     */
    public function handle_request_payout() {
        if ( ! check_ajax_referer( 'kq-nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Security check failed.' ] );
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Please log in.' ] );
        }

        $user_id = get_current_user_id();
        $organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( $user_id );
        if ( ! $organizer ) {
            wp_send_json_error( [ 'message' => 'Organizer profile not found.' ] );
        }

        $stats = \KueueEvents\Core\Modules\Reports\ReportsService::get_global_summary( $organizer->id );
        $available_balance = (float) ( $stats->net ?? 0 );

        // Check if there's enough balance (minimal 100 EGP for demo)
        if ( $available_balance < 100 ) {
            wp_send_json_error( [ 'message' => 'Minimum payout amount is 100 EGP.' ] );
        }

        // Check for pending requests
        $existing = \KueueEvents\Core\Modules\Payouts\PayoutRepository::get_by_organizer( $organizer->id );
        foreach ( $existing as $p ) {
            if ( $p->status === 'pending' ) {
                wp_send_json_error( [ 'message' => 'You already have a pending withdrawal request.' ] );
            }
        }

        $result = \KueueEvents\Core\Modules\Payouts\PayoutRepository::create([
            'organizer_id'   => $organizer->id,
            'amount'         => $available_balance,
            'payment_method' => 'manual',
            'notes'          => 'Auto-generated request from dashboard.'
        ]);

        if ( $result ) {
            wp_send_json_success( [ 'message' => 'Payout request submitted successfully.' ] );
        } else {
            wp_send_json_error( [ 'message' => 'Failed to create payout request.' ] );
        }
    }

    /**
     * Render Organizer Dashboard
     */
    public function render_organizer_dashboard() {
        if ( ! is_user_logged_in() ) {
            return '<p>' . __( 'Please log in to view dashboard.', 'kueue-events-core' ) . '</p>';
        }

        $user_id = get_current_user_id();
        $organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( $user_id );
        if ( ! $organizer ) {
            return '<p>' . __( 'Organizer profile not found.', 'kueue-events-core' ) . '</p>';
        }

        $stats = \KueueEvents\Core\Modules\Reports\ReportsService::get_global_summary( $organizer->id );
        $payouts = \KueueEvents\Core\Modules\Payouts\PayoutRepository::get_by_organizer($organizer->id);
        
        // Fetch events for this organizer
        $events = get_posts( [
            'post_type'  => 'kq_event',
            'meta_key'   => '_kq_organizer_id',
            'meta_value' => $organizer->id,
            'numberposts' => -1
        ] );

        ob_start();
        $view_path = KQ_PLUGIN_DIR . 'includes/Modules/Frontend/views/organizer-dashboard.php';
        if ( file_exists( $view_path ) ) {
            include $view_path;
        } else {
            // Check alt path (Dashboard module)
            $alt_path = KQ_PLUGIN_DIR . 'includes/Modules/Dashboard/views/dashboard-view.php';
            if ( file_exists( $alt_path ) ) {
                include $alt_path;
            } else {
                echo '<p>Organizer dashboard view not found.</p>';
            }
        }
        return ob_get_clean();
    }

    /**
     * Register GDPR Exporter
     */
    public function register_gdpr_exporter( $exporters ) {
        $exporters['kq-events'] = [
            'exporter_friendly_name' => __( 'Kueue Events Data', 'kueue-events-core' ),
            'callback'               => [ $this, 'kq_personal_data_exporter' ],
        ];
        return $exporters;
    }

    public function kq_personal_data_exporter( $email_address, $page = 1 ) {
        $attendees = \KueueEvents\Core\Modules\Attendees\AttendeeRepository::get_by_email( $email_address );
        $data = [];
        foreach ( $attendees as $att ) {
            $data[] = [
                'group_id'    => 'kq-events',
                'group_label' => __( 'Event Bookings', 'kueue-events-core' ),
                'item_id'     => 'att-' . $att->id,
                'data'        => [
                    [ 'name' => __( 'First Name', 'kueue-events-core' ), 'value' => $att->first_name ],
                    [ 'name' => __( 'Last Name', 'kueue-events-core' ), 'value' => $att->last_name ],
                    [ 'name' => __( 'Ticket Type', 'kueue-events-core' ), 'value' => $att->ticket_type_id ],
                ],
            ];
        }
        return [ 'data' => $data, 'done' => true ];
    }

    /**
     * Register GDPR Eraser
     */
    public function register_gdpr_eraser( $erasers ) {
        $erasers['kq-events'] = [
            'eraser_friendly_name' => __( 'Kueue Events Data', 'kueue-events-core' ),
            'callback'             => [ $this, 'kq_personal_data_eraser' ],
        ];
        return $erasers;
    }

    public function kq_personal_data_eraser( $email_address, $page = 1 ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_attendees';
        $items_removed = $wpdb->delete( $table, [ 'email' => $email_address ] );
        return [ 'items_removed' => $items_removed, 'items_retained' => 0, 'messages' => [], 'done' => true ];
    }
}
