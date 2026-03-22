<?php

namespace KueueEvents\Core\Admin;

class AdminController {

    /**
     * Run the admin.
     * Registers hooks for the WordPress admin area.
     */
    public function run() {
        add_action( 'admin_menu', [ $this, 'register_menus' ] );
        
        $settings = new \KueueEvents\Core\Admin\SettingsController();
        $settings->run();

        // Check-ins & REST
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
        
        $scanner = new \KueueEvents\Core\Modules\Checkins\ScannerPage();
        $scanner->run();

        // POS & Bookings
        add_action( 'rest_api_init', [ $this, 'register_pos_routes' ] );

        // Seating
        add_action( 'rest_api_init', [ $this, 'register_seating_routes' ] );

        // Simple background trigger for queue processing in admin
        if ( is_admin() ) {
            kq_process_delivery_queue( 5 );
        }
    }

    /**
     * REST Routes
     */
    public function register_rest_routes() {
        $checkin_controller = new \KueueEvents\Core\Modules\Checkins\CheckinController();
        $checkin_controller->register_routes();
    }

    public function register_pos_routes() {
        $pos = new \KueueEvents\Core\Modules\POS\POSController();
        $pos->register_routes();

        // Also Bookings routes
        register_rest_route( 'kq/v1', '/bookings/slots', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_booking_slots' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'add_booking_slot' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
        ] );
    }

    public function permissions_check() {
        return current_user_can( 'manage_kq_events' );
    }

    public function get_booking_slots( $request ) {
        $event_id = $request->get_param( 'event_id' );
        if ( ! $event_id ) return [];
        return \KueueEvents\Core\Modules\Bookings\BookingRepository::get_by_event( $event_id );
    }

    public function add_booking_slot( $request ) {
        $data = $request->get_json_params();
        return \KueueEvents\Core\Modules\Bookings\BookingRepository::create_slot( $data );
    }

    // Seating Routes
    public function register_seating_routes() {
        register_rest_route( 'kq/v1', '/seating/maps', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_seating_maps' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'save_seating_map' ],
                'permission_callback' => [ $this, 'permissions_check' ],
            ],
        ] );
    }

    public function get_seating_maps() {
        return \KueueEvents\Core\Modules\Seating\SeatingRepository::get_all_maps();
    }

    public function save_seating_map( $request ) {
        $data = $request->get_json_params();
        return \KueueEvents\Core\Modules\Seating\SeatingRepository::save_map( $data );
    }

    /**
     * Register Admin Menus
     */
    public function register_menus() {
        // 1) Main Menu
        add_menu_page(
            __( 'Kueue Events', 'kueue-events-core' ),
            __( 'Kueue Events', 'kueue-events-core' ),
            'manage_kq_events',
            'kq-events-dashboard',
            [ $this, 'render_dashboard' ],
            'dashicons-tickets-alt',
            25
        );

        // 2) Dashboard Submenu
        add_submenu_page(
            'kq-events-dashboard',
            __( 'Dashboard', 'kueue-events-core' ),
            __( 'Dashboard', 'kueue-events-core' ),
            'manage_kq_events',
            'kq-events-dashboard',
            [ $this, 'render_dashboard' ]
        );

        // 3) Organizers Submenu
        add_submenu_page(
            'kq-events-dashboard',
            __( 'Organizers', 'kueue-events-core' ),
            __( 'Organizers', 'kueue-events-core' ),
            'manage_options',
            'kq-organizers',
            [ $this, 'render_organizers' ]
        );

        // 4) Events Submenu
        add_submenu_page(
            'kq-events-dashboard',
            __( 'Events', 'kueue-events-core' ),
            __( 'Events', 'kueue-events-core' ),
            'manage_kq_events',
            'edit.php?post_type=kq_event'
        );

        // 5) Ticket Types Submenu
        add_submenu_page(
            'kq-events-dashboard',
            __( 'Ticket Types', 'kueue-events-core' ),
            __( 'Ticket Types', 'kueue-events-core' ),
            'manage_kq_events',
            'kq-ticket-types',
            [ $this, 'render_ticket_types' ]
        );

        // 6) Attendees Submenu
        add_submenu_page(
            'kq-events-dashboard',
            __( 'Attendees', 'kueue-events-core' ),
            __( 'Attendees', 'kueue-events-core' ),
            'manage_kq_events',
            'kq-attendees',
            [ $this, 'render_attendees' ]
        );

        // 7) Tickets Submenu
        add_submenu_page(
            'kq-events-dashboard',
            __( 'Tickets', 'kueue-events-core' ),
            __( 'Tickets', 'kueue-events-core' ),
            'manage_kq_events',
            'kq-tickets',
            [ $this, 'render_tickets' ]
        );

        // 6) Communications (Header and Submenus)
        // Note: For multi-level submenus in WP, we often just list them linearly or use a separator.

        // 7) SMS Accounts
        add_submenu_page(
            'kq-events-dashboard',
            __( 'SMS Accounts', 'kueue-events-core' ),
            __( '— SMS Accounts', 'kueue-events-core' ),
            'manage_options',
            'kq-sms-accounts',
            [ $this, 'render_gateway_accounts' ]
        );

        // 8) WhatsApp Accounts
        add_submenu_page(
            'kq-events-dashboard',
            __( 'WhatsApp Accounts', 'kueue-events-core' ),
            __( '— WhatsApp Accounts', 'kueue-events-core' ),
            'manage_options',
            'kq-whatsapp-accounts',
            [ $this, 'render_gateway_accounts' ]
        );

        // 9) Check-in Logs
        add_submenu_page(
            'kq-events-dashboard',
            __( 'Check-in Logs', 'kueue-events-core' ),
            __( '— Check-in Logs', 'kueue-events-core' ),
            'manage_kq_reports',
            'kq-checkin-logs',
            [ $this, 'render_checkin_logs' ]
        );

        // 10) Bookings
        add_submenu_page(
            'kq-events-dashboard',
            __( 'Bookings', 'kueue-events-core' ),
            __( '— Bookings', 'kueue-events-core' ),
            'manage_kq_events',
            'kq-bookings',
            [ $this, 'render_bookings' ]
        );

        // 10) Seating
        add_submenu_page(
            'kq-events-dashboard',
            __( 'Seating', 'kueue-events-core' ),
            __( '— Seating', 'kueue-events-core' ),
            'manage_kq_events',
            'kq-seating',
            [ $this, 'render_seating' ]
        );

        // 11) Reports
        add_submenu_page(
            'kq-events-dashboard',
            __( 'Reports', 'kueue-events-core' ),
            __( '— Reports', 'kueue-events-core' ),
            'manage_kq_reports',
            'kq-reports',
            [ $this, 'render_reports' ]
        );

        // 12) Finance
        add_submenu_page(
            'kq-events-dashboard',
            __( 'Finance', 'kueue-events-core' ),
            __( '— Finance', 'kueue-events-core' ),
            'manage_kq_reports',
            'kq-finance',
            [ $this, 'render_placeholder' ]
        );

        // 13) POS
        add_submenu_page(
            'kq-events-dashboard',
            __( 'POS / Box Office', 'kueue-events-core' ),
            __( '— POS', 'kueue-events-core' ),
            'manage_kq_tickets',
            'kq-pos',
            [ $this, 'render_pos' ]
        );

        // 14) Settings
        add_submenu_page(
            'kq-events-dashboard',
            __( 'Settings', 'kueue-events-core' ),
            __( 'Settings', 'kueue-events-core' ),
            'manage_options',
            'kq-settings',
            [ new \KueueEvents\Core\Admin\SettingsController(), 'render_settings' ]
        );

        // 15) System Info & Updates
        add_submenu_page(
            'kq-events-dashboard',
            __( 'System Info', 'kueue-events-core' ),
            __( 'System Info', 'kueue-events-core' ),
            'manage_options',
            'kq-system-info',
            [ $this, 'render_system_info' ]
        );
    }

    /**
     * Render Dashboard
     */
    public function render_dashboard() {
        include_once KQ_PLUGIN_DIR . 'includes/Admin/views/dashboard.php';
    }

    /**
     * Render Organizers CRUD
     */
    public function render_organizers() {
        $module = new \KueueEvents\Core\Modules\Vendors\OrganizerAdmin();
        $module->render_list();
    }

    /**
     * Render Ticket Types
     */
    public function render_ticket_types() {
        $module = new \KueueEvents\Core\Modules\Tickets\TicketTypeAdmin();
        $module->render_list();
    }

    /**
     * Render Attendees
     */
    public function render_attendees() {
        $module = new \KueueEvents\Core\Modules\Attendees\AttendeeAdmin();
        $module->render_list();
    }

    /**
     * Render POS / Box Office
     */
    public function render_pos() {
        $events = get_posts( [ 'post_type' => 'kq_event', 'numberposts' => -1 ] );
        include_once KQ_PLUGIN_DIR . 'includes/Admin/views/pos-view.php';
    }

    /**
     * Render Gateway Accounts CRUD
     */
    public function render_gateway_accounts() {
        // This will be handled by the Gateways Module Controller
        $module = new \KueueEvents\Core\Modules\Gateways\GatewayAdminController();
        $module->render_list();
    }

    /**
     * Render Seating Management
     */
    public function render_seating() {
        include_once KQ_PLUGIN_DIR . 'includes/Admin/views/seating-view.php';
    }

    /**
     * Render Bookings Management
     */
    public function render_bookings() {
        $events = get_posts( [ 'post_type' => 'kq_event', 'numberposts' => -1 ] );
        include_once KQ_PLUGIN_DIR . 'includes/Admin/views/bookings-view.php';
    }

    /**
     * Render Reports Dashboard
     */
    public function render_reports() {
        $stats = \KueueEvents\Core\Modules\Reports\ReportsService::get_global_summary();
        include_once KQ_PLUGIN_DIR . 'includes/Admin/views/reports-view.php';
    }

    /**
     * Render Check-in Logs
     */
    public function render_checkin_logs() {
        $logs = \KueueEvents\Core\Modules\Checkins\CheckinRepository::get_all( 100 );
        include_once KQ_PLUGIN_DIR . 'includes/Admin/views/checkin-log-list.php';
    }

    /**
     * Render System Info & Updates
     */
    public function render_system_info() {
        $current_v = \KueueEvents\Core\Core\VersionManager::get_current_version();
        ?>
        <div class="wrap">
            <h1><?php _e( 'Kueue Events System Info', 'kueue-events-core' ); ?></h1>
            <div class="card" style="max-width: 600px;">
                <h2><?php _e( 'Plugin Metadata', 'kueue-events-core' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><?php _e( 'Installed Version', 'kueue-events-core' ); ?></th>
                        <td><strong><?php echo esc_html( $current_v ); ?></strong></td>
                    </tr>
                    <tr>
                        <th><?php _e( 'Update Channel', 'kueue-events-core' ); ?></th>
                        <td><code>Stable (GitHub)</code></td>
                    </tr>
                </table>
                <p>
                    <a href="<?php echo admin_url('update-core.php?force-check=1'); ?>" class="button button-primary">
                        <?php _e( 'Check for Updates Now', 'kueue-events-core' ); ?>
                    </a>
                </p>
                <p class="description">
                    <?php _e( 'Updates are automatically checked by WordPress. Clicking the button above forces a check of all plugins.', 'kueue-events-core' ); ?>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Render Placeholder
     */
    public function render_placeholder() {
        echo '<div class="wrap"><h1>Coming Soon</h1><p>Feature under development.</p></div>';
    }
}
