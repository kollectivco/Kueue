<?php

namespace KueueEvents\Core\Core;

class Main {

    /**
     * Run the plugin.
     * Hooks into WordPress actions and filters.
     */
    public function run() {
        // Core initialization
        $this->init_core();

        // 1) Load Admin
        if ( is_admin() ) {
            $admin = new \KueueEvents\Core\Admin\AdminController();
            $admin->run();
        }

        // 2) Load API
        add_action( 'rest_api_init', [ $this, 'register_api_routes' ] );

        // 3) Load Modules
        $this->load_modules();

        // 4) Core Hooks
        add_action( 'init', [ $this, 'init_plugin' ] );
    }

    /**
     * Initialize core components with case-sensitive safety for Linux.
     */
    private function init_core() {
        $helpers = [
            KQ_PLUGIN_DIR . 'includes/Core/Helpers.php',
            KQ_PLUGIN_DIR . 'includes/core/Helpers.php',
            KQ_PLUGIN_DIR . 'includes/Core/helpers.php'
        ];

        foreach ( $helpers as $path ) {
            if ( file_exists( $path ) ) {
                require_once $path;
                break;
            }
        }
    }

    /**
     * Main init action
     */
    public function init_plugin() {
        // Global logic
    }

    /**
     * Register REST API routes
     */
    public function register_api_routes() {
        // To be implemented in API module
    }

    /**
     * Load Modules
     */
    private function load_modules() {
        // Gateways
        new \KueueEvents\Core\Modules\Gateways\GatewayManager();

        // Organizers/Vendors
        $organizer_admin = new \KueueEvents\Core\Modules\Vendors\OrganizerAdmin();
        $organizer_admin->run();

        // Events
        $event_cpt = new \KueueEvents\Core\Modules\Events\EventPostType();
        $event_cpt->run();

        $event_meta = new \KueueEvents\Core\Modules\Events\EventMetaBoxes();
        $event_meta->run();

        $event_perms = new \KueueEvents\Core\Modules\Events\EventPermissions();
        $event_perms->run();

        // Tickets Engine
        new \KueueEvents\Core\Modules\Tickets\TicketTypeAdmin();
        new \KueueEvents\Core\Modules\Attendees\AttendeeAdmin();
        new \KueueEvents\Core\Modules\Tickets\TicketAdmin();

        // Public Views
        $ticket_view = new \KueueEvents\Core\Modules\Tickets\TicketViewController();
        $ticket_view->run();

        // Ajax Handlers
        $ajax_handler = new \KueueEvents\Core\Core\AjaxHandler();
        $ajax_handler->run();

        // WP-CLI with case-sensitive check
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            $cli_path = KQ_PLUGIN_DIR . 'includes/Core/CliCommands.php';
            if ( ! file_exists( $cli_path ) ) {
                $cli_path = KQ_PLUGIN_DIR . 'includes/core/CliCommands.php';
            }
            if ( file_exists( $cli_path ) ) {
                require_once $cli_path;
            }
        }

        // 5) Payments & Checkout
        if ( class_exists( 'WooCommerce' ) ) {
            $wc = new \KueueEvents\Core\Modules\Payments\WooCommerceService();
            $wc->run();

            $checkout = new \KueueEvents\Core\Modules\Attendees\CheckoutHandler();
            $checkout->run();
        }

        // 6) Public Frontend (Events & Dashboard)
        $frontend = new \KueueEvents\Core\Modules\Frontend\FrontendController();
        $frontend->run();

        // 8) Self-Update System
        $updater = new \KueueEvents\Core\Core\GitHubUpdater();
        $updater->run();
    }
}
