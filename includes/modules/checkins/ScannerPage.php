<?php

namespace KueueEvents\Core\Modules\Checkins;

class ScannerPage {

    public function run() {
        add_action( 'init', [ $this, 'register_rewrite_rules' ] );
        add_filter( 'query_vars', [ $this, 'register_query_vars' ] );
        add_action( 'template_include', [ $this, 'handle_scanner_view' ] );
    }

    public function register_rewrite_rules() {
        add_rewrite_rule( 'kq-scanner/?', 'index.php?kq_scanner=1', 'top' );
    }

    public function register_query_vars( $vars ) {
        $vars[] = 'kq_scanner';
        return $vars;
    }

    public function handle_scanner_view( $template ) {
        if ( get_query_var( 'kq_scanner' ) !== '1' ) {
            return $template;
        }

        // Permission Check
        if ( ! is_user_logged_in() || ! ( current_user_can( 'manage_kq_tickets' ) || current_user_can( 'manage_options' ) ) ) {
            auth_redirect();
            exit;
        }

        $this->render_scanner_page();
        exit;
    }

    /**
     * Handle Scanner Session Init
     */
    public function get_scanner_token( $user_id ) {
        $device_id = md5( $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] );
        return ScannerSessionManager::create_session( $user_id, $device_id );
    }

    protected function render_scanner_page() {
        $user_id = get_current_user_id();
        $is_admin = current_user_can( 'manage_options' );

        $scanner_token = $this->get_scanner_token( $user_id );
        $device_id = md5( $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] );
        
        // Fetch events for selector
        if ( $is_admin ) {
            $events = get_posts( [ 'post_type' => 'kq_event', 'numberposts' => -1 ] );
        } else {
            $organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( $user_id );
            $events = $organizer ? get_posts( [
                'post_type'  => 'kq_event',
                'numberposts' => -1,
                'meta_query' => [
                    [ 'key' => '_kq_organizer_id', 'value' => $organizer->id ]
                ]
            ] ) : [];
        }

        include_once KQ_PLUGIN_DIR . 'includes/admin/views/scanner-page-view.php';
    }
}
