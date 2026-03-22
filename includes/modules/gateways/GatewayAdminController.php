<?php

namespace KueueEvents\Core\Modules\Gateways;

class GatewayAdminController {

    public function render_list() {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_gateway_accounts';

        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $page = $_GET['page'];
        $channel = ( $page === 'kq-sms-accounts' ) ? 'sms' : 'whatsapp';

        if ( 'delete' === $action && isset( $_GET['id'] ) ) {
            check_admin_referer( 'kq_delete_account_' . $_GET['id'] );
            $wpdb->delete( $table, [ 'id' => $_GET['id'] ] );
            wp_redirect( admin_url( 'admin.php?page=' . $page ) );
            exit;
        }

        if ( 'edit' === $action || 'add' === $action ) {
            $this->render_form( $action, $channel );
            return;
        }

        $accounts = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE channel = %s", $channel ) );
        include_once KQ_PLUGIN_DIR . 'includes/admin/views/gateway-list.php';
    }

    private function render_form( $action, $channel ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_gateway_accounts';
        $id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
        $account = null;

        if ( $id ) {
            $account = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );
            $account->config = json_decode( $account->config_json, true );
        }

        if ( isset( $_POST['kq_save_account'] ) ) {
            check_admin_referer( 'kq_save_account_nonce' );
            $this->handle_save( $id, $channel );
            return;
        }

        include_once KQ_PLUGIN_DIR . 'includes/admin/views/gateway-form.php';
    }

    private function handle_save( $id, $channel ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_gateway_accounts';

        $account_name = sanitize_text_field( $_POST['account_name'] );
        $provider = sanitize_text_field( $_POST['provider'] );
        $is_enabled = isset( $_POST['is_enabled'] ) ? 1 : 0;
        $is_default = isset( $_POST['is_default'] ) ? 1 : 0;
        
        $config = $_POST['config']; // Credentials
        
        // If editing, merge with old config first to preserve existing values if they are masked in UI
        if ( $id ) {
            $old_account = $wpdb->get_row( $wpdb->prepare( "SELECT config_json FROM $table WHERE id = %d", $id ) );
            $old_config = json_decode( $old_account->config_json, true );
            
            foreach ( $config as $key => $val ) {
                // If the submitted value is '********' or equivalent masked, reuse old value (which is already encrypted)
                if ( $val === '********' || strpos( $val, '****' ) !== false ) {
                    $config[$key] = $old_config[$key];
                } else {
                    // It's a new plain value; encrypt it
                    if ( in_array( $key, [ 'username', 'password', 'access_token', 'app_secret' ] ) ) {
                        $config[$key] = \KueueEvents\Core\Helpers\EncryptionHelper::encrypt( $val );
                    }
                }
            }
        } else {
            // New account; encrypt everything sensitive
            foreach ( [ 'username', 'password', 'access_token', 'app_secret' ] as $field ) {
                if ( !empty( $config[$field] ) ) {
                    $config[$field] = \KueueEvents\Core\Helpers\EncryptionHelper::encrypt( $config[$field] );
                }
            }
        }

        $data = [
            'channel'      => $channel,
            'provider'     => $provider,
            'account_name' => $account_name,
            'is_enabled'   => $is_enabled,
            'is_default'   => $is_default,
            'config_json'  => json_encode( $config )
        ];

        if ( $id ) {
            $wpdb->update( $table, $data, [ 'id' => $id ] );
        } else {
            $wpdb->insert( $table, $data );
            $id = $wpdb->insert_id;
        }

        if ( $is_default ) {
            GatewayManager::set_default( $id, $channel );
        }

        $page = ( $channel === 'sms' ) ? 'kq-sms-accounts' : 'kq-whatsapp-accounts';
        wp_redirect( admin_url( 'admin.php?page=' . $page ) );
        exit;
    }
}
