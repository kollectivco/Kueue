<?php

namespace KueueEvents\Core\Admin;

class SettingsController {

    public function run() {
        add_action( 'admin_post_kq_save_settings', [ $this, 'save_settings' ] );
        add_action( 'admin_post_kq_manual_queue', [ $this, 'manual_queue' ] );
    }

    /**
     * Save settings.
     */
    public function save_settings() {
        check_admin_referer( 'kq_save_settings_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'No permission.' );

        $opts = [ 'kq_throttle_whatsapp', 'kq_throttle_sms' ];
        foreach ( $opts as $opt ) {
            if ( isset( $_POST[$opt] ) ) {
                update_option( $opt, (int) $_POST[$opt] );
            }
        }

        update_option( 'kq_allow_checkout_after_checkin', isset( $_POST['kq_allow_checkout_after_checkin'] ) ? 1 : 0 );

        wp_redirect( admin_url( 'admin.php?page=kq-settings&message=saved' ) );
        exit;
    }

    /**
     * Manual queue process.
     */
    public function manual_queue() {
        check_admin_referer( 'kq_manual_queue_nonce' );
        if ( ! current_user_can( 'manage_kq_reports' ) ) wp_die( 'No permission.' );

        \KueueEvents\Core\Modules\Delivery\QueueProcessor::process( 20 );

        wp_redirect( admin_url( 'admin.php?page=kq-settings&message=queue_processed' ) );
        exit;
    }

    /**
     * Render the settings page.
     */
    public function render_settings() {
        include_once KQ_PLUGIN_DIR . 'includes/Admin/views/settings.php';
    }
}
