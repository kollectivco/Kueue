<?php

namespace KueueEvents\Core\Admin;

class AdminController {

    /**
     * Run the admin.
     * Registers hooks for the WordPress admin area.
     */
    public function run() {
        add_action( 'admin_menu', [ $this, 'register_menus' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        
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
     * Enqueue Admin Assets
     */
    public function enqueue_assets( $hook ) {
        // Only load on our plugin pages
        if ( strpos( $hook, 'kq-' ) === false && strpos( $hook, 'kq_event' ) === false ) {
            return;
        }

        wp_enqueue_style( 'kq-design-system', KQ_PLUGIN_URL . 'assets/css/design-system.css', [], KQ_VERSION );
        wp_enqueue_style( 'kq-admin-style', KQ_PLUGIN_URL . 'assets/css/admin.css', ['kq-design-system'], KQ_VERSION );
        
        // FontAwesome for icons
        wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', [], '6.4.0' );
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
            [ $this, 'render_finance' ]
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
        echo '<div style="padding:10px;background:#f0f0f0;border-bottom:1px solid #ccc;font-size:11px;color:#666">DEBUG: Rendering Dashboard</div>';
        $path = KQ_PLUGIN_DIR . 'includes/Modules/Reports/views/dashboard.php';
        if ( file_exists( $path ) ) {
            include_once $path;
        } else {
            echo '<div class="notice notice-error"><p>Dashboard view not found at: ' . esc_html($path) . '</p></div>';
        }
    }

    /**
     * Render Organizers CRUD
     */
    public function render_organizers() {
        echo '<div style="padding:10px;background:#f0f0f0;border-bottom:1px solid #ccc;font-size:11px;color:#666">DEBUG: Rendering Organizers</div>';
        if ( class_exists( '\KueueEvents\Core\Modules\Vendors\OrganizerAdmin' ) ) {
            $module = new \KueueEvents\Core\Modules\Vendors\OrganizerAdmin();
            $module->render_list();
        } else {
            echo '<div class="notice notice-error"><p>OrganizerAdmin class not found.</p></div>';
        }
    }

    /**
     * Render Ticket Types
     */
    public function render_ticket_types() {
        echo '<div style="padding:10px;background:#f0f0f0;border-bottom:1px solid #ccc;font-size:11px;color:#666">DEBUG: Rendering Ticket Types</div>';
        if ( class_exists( '\KueueEvents\Core\Modules\Tickets\TicketTypeAdmin' ) ) {
            $module = new \KueueEvents\Core\Modules\Tickets\TicketTypeAdmin();
            $module->render_list();
        } else {
            echo '<div class="notice notice-error"><p>TicketTypeAdmin class not found.</p></div>';
        }
    }

    /**
     * Render Attendees
     */
    public function render_attendees() {
        echo '<div style="padding:10px;background:#f0f0f0;border-bottom:1px solid #ccc;font-size:11px;color:#666">DEBUG: Rendering Attendees</div>';
        if ( class_exists( '\KueueEvents\Core\Modules\Attendees\AttendeeAdmin' ) ) {
            $module = new \KueueEvents\Core\Modules\Attendees\AttendeeAdmin();
            $module->render_list();
        } else {
            echo '<div class="notice notice-error"><p>AttendeeAdmin class not found.</p></div>';
        }
    }

    /**
     * Render Issued Tickets
     */
    public function render_tickets() {
        echo '<div style="padding:10px;background:#f0f0f0;border-bottom:1px solid #ccc;font-size:11px;color:#666">DEBUG: Rendering Tickets</div>';
        if ( class_exists( '\KueueEvents\Core\Modules\Tickets\TicketAdmin' ) ) {
            $module = new \KueueEvents\Core\Modules\Tickets\TicketAdmin();
            $module->render_list();
        } else {
            echo '<div class="notice notice-error"><p>TicketAdmin class not found.</p></div>';
        }
    }

    /**
     * Render POS / Box Office
     */
    public function render_pos() {
        echo '<div style="padding:10px;background:#f0f0f0;border-bottom:1px solid #ccc;font-size:11px;color:#666">DEBUG: Rendering POS</div>';
        $events = get_posts( [ 'post_type' => 'kq_event', 'numberposts' => -1 ] );
        $path = KQ_PLUGIN_DIR . 'includes/Modules/POS/views/pos-view.php';
        if ( file_exists( $path ) ) {
            include_once $path;
        } else {
            echo '<div class="notice notice-error"><p>POS view not found.</p></div>';
        }
    }

    /**
     * Render Gateway Accounts CRUD
     */
    public function render_gateway_accounts() {
        echo '<div style="padding:10px;background:#f0f0f0;border-bottom:1px solid #ccc;font-size:11px;color:#666">DEBUG: Rendering Gateways</div>';
        if ( class_exists( '\KueueEvents\Core\Modules\Gateways\GatewayAdminController' ) ) {
            $module = new \KueueEvents\Core\Modules\Gateways\GatewayAdminController();
            $module->render_list();
        } else {
            echo '<div class="notice notice-error"><p>GatewayAdminController class not found.</p></div>';
        }
    }

    /**
     * Render Seating Management
     */
    public function render_seating() {
        echo '<div style="padding:10px;background:#f0f0f0;border-bottom:1px solid #ccc;font-size:11px;color:#666">DEBUG: Rendering Seating</div>';
        $path = KQ_PLUGIN_DIR . 'includes/Modules/Seating/views/seating-view.php';
        if ( file_exists( $path ) ) {
            include_once $path;
        } else {
            echo '<div class="notice notice-error"><p>Seating view not found.</p></div>';
        }
    }

    /**
     * Render Bookings Management
     */
    public function render_bookings() {
        echo '<div style="padding:10px;background:#f0f0f0;border-bottom:1px solid #ccc;font-size:11px;color:#666">DEBUG: Rendering Bookings</div>';
        $events = get_posts( [ 'post_type' => 'kq_event', 'numberposts' => -1 ] );
        $path = KQ_PLUGIN_DIR . 'includes/Modules/Bookings/views/bookings-view.php';
        if ( file_exists( $path ) ) {
            include_once $path;
        } else {
            echo '<div class="notice notice-error"><p>Bookings view not found.</p></div>';
        }
    }

    /**
     * Render Reports Dashboard
     */
    public function render_reports() {
        echo '<div style="padding:10px;background:#f0f0f0;border-bottom:1px solid #ccc;font-size:11px;color:#666">DEBUG: Rendering Reports</div>';
        $stats = \KueueEvents\Core\Modules\Reports\ReportsService::get_global_summary();
        $path = KQ_PLUGIN_DIR . 'includes/Modules/Reports/views/reports-view.php';
        if ( file_exists( $path ) ) {
            include_once $path;
        } else {
            echo '<div class="notice notice-error"><p>Reports view not found.</p></div>';
        }
    }

    /**
     * Render Finance Dashboard
     */
    public function render_finance() {
        echo '<div style="padding:10px;background:#f0f0f0;border-bottom:1px solid #ccc;font-size:11px;color:#666">DEBUG: Rendering Finance</div>';
        $stats = \KueueEvents\Core\Modules\Reports\ReportsService::get_global_summary(); // Fallback to global summary if commission repo missing
        if ( class_exists( '\KueueEvents\Core\Modules\Finance\CommissionRepository' ) ) {
            $stats = \KueueEvents\Core\Modules\Finance\CommissionRepository::get_global_stats();
            $commissions = \KueueEvents\Core\Modules\Finance\CommissionRepository::get_paged( 1, 50 );
        }
        $payout_requests = \KueueEvents\Core\Modules\Payouts\PayoutRepository::get_all_pending();
        
        $path = KQ_PLUGIN_DIR . 'includes/Modules/Finance/views/finance-view.php';
        if ( file_exists( $path ) ) {
            include_once $path;
        } else {
            echo '<div class="notice notice-error"><p>Finance view not found.</p></div>';
        }
    }

    /**
     * Render Check-in Logs
     */
    public function render_checkin_logs() {
        echo '<div style="padding:10px;background:#f0f0f0;border-bottom:1px solid #ccc;font-size:11px;color:#666">DEBUG: Rendering Check-in Logs</div>';
        $logs = \KueueEvents\Core\Modules\Checkins\CheckinRepository::get_all( 100 );
        $path = KQ_PLUGIN_DIR . 'includes/Modules/Checkins/views/checkin-log-list.php';
        if ( file_exists( $path ) ) {
            include_once $path;
        } else {
            echo '<div class="notice notice-error"><p>Check-in logs view not found.</p></div>';
        }
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
