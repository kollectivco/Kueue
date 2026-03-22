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
     * Initialize core components
     */
    private function init_core() {
        // Helper classes can be initialized here
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

        // WP-CLI
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            require_once KQ_PLUGIN_DIR . 'includes/core/CliCommands.php';
        }

        // 5) Payments & Checkout
        if ( class_exists( 'WooCommerce' ) ) {
            $wc = new \KueueEvents\Core\Modules\Payments\WooCommerceService();
            $wc->run();
        }

        // 6) Public Frontend
        $frontend = new \KueueEvents\Core\Modules\Frontend\FrontendController();
        $frontend->run();

        // 7) Self-Update System
        $updater = new \KueueEvents\Core\Core\GitHubUpdater();
        $updater->run();
    }
}
