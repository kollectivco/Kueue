<?php

namespace KueueEvents\Core\Modules\Frontend;

class FrontendController {

    public function run() {
        // Register shortcodes (with Aliases)
        add_shortcode( 'kq_events', [ $this, 'render_events_list' ] );
        add_shortcode( 'kq_events_list', [ $this, 'render_events_list' ] );

        add_shortcode( 'kq_event', [ $this, 'render_event_single' ] );
        add_shortcode( 'kq_event_page', [ $this, 'render_event_single' ] );

        add_shortcode( 'kq_dashboard', [ $this, 'render_organizer_dashboard' ] );
        add_shortcode( 'kq_organizer_dashboard', [ $this, 'render_organizer_dashboard' ] );

        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        // AJAX for ticket selection and checkout
        add_action( 'wp_ajax_kq_add_to_cart', [ $this, 'handle_add_to_cart' ] );
        add_action( 'wp_ajax_nopriv_kq_add_to_cart', [ $this, 'handle_add_to_cart' ] );

        // GDPR Hooks
        add_filter( 'wp_privacy_personal_data_exporters', [ $this, 'register_gdpr_exporter' ], 10 );
        add_filter( 'wp_privacy_personal_data_erasers', [ $this, 'register_gdpr_eraser' ], 10 );
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
        $events = get_posts( [ 'post_type' => 'kq_event', 'posts_per_page' => -1 ] );
        ob_start();
        include KQ_PLUGIN_DIR . 'includes/Modules/Frontend/views/events-list.php';
        return ob_get_clean();
    }

    /**
     * Render Single Event Page
     */
    public function render_event_single( $atts ) {
        $id = $atts['id'] ?? get_the_ID();
        $event = get_post( $id );
        if ( ! $event || $event->post_type !== 'kq_event' ) return 'Event not found.';

        $ticket_types = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_by_event( $id );
        
        ob_start();
        include KQ_PLUGIN_DIR . 'includes/Modules/Frontend/views/event-single.php';
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
        ];

        try {
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
     * Render Organizer Dashboard
     */
    public function render_organizer_dashboard() {
        if ( ! is_user_logged_in() ) {
            return '<p>Please log in to view dashboard.</p>';
        }

        $user_id = get_current_user_id();
        $organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( $user_id );
        if ( ! $organizer ) {
            return '<p>Organizer profile not found.</p>';
        }

        $stats = \KueueEvents\Core\Modules\Reports\ReportsService::get_global_summary( $organizer->id );
        $payouts = \KueueEvents\Core\Modules\Payouts\PayoutRepository::get_by_organizer($organizer->id);
        
        ob_start();
        include KQ_PLUGIN_DIR . 'includes/Modules/Frontend/views/organizer-dashboard.php';
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
