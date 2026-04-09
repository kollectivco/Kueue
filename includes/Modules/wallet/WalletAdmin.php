<?php

namespace KueueEvents\Core\Modules\Wallet;

class WalletAdmin {

    private $service;

    public function __construct() {
        $this->service = new WalletService();
    }

    public function run() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_init', [ $this, 'handle_manual_adjustment' ] );
    }

    public function register_menu() {
        add_submenu_page(
            'kueue-events',
            __( 'Wallets', 'kueue-events-core' ),
            __( 'Wallets', 'kueue-events-core' ),
            'manage_options',
            'kq-wallets',
            [ $this, 'render_wallets_page' ]
        );

        add_submenu_page(
            'kueue-events',
            __( 'Wallet Transactions', 'kueue-events-core' ),
            __( 'Wallet Transactions', 'kueue-events-core' ),
            'manage_options',
            'kq-wallet-transactions',
            [ $this, 'render_transactions_page' ]
        );
    }

    public function render_wallets_page() {
        global $wpdb;
        $search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
        
        $query = "SELECT w.*, u.user_login, u.user_email FROM {$wpdb->prefix}kq_wallets w 
                  JOIN {$wpdb->users} u ON w.user_id = u.ID";
        
        if ( $search ) {
            $query .= $wpdb->prepare( " WHERE u.user_login LIKE %s OR u.user_email LIKE %s", "%$search%", "%$search%" );
        }
        
        $wallets = $wpdb->get_results( $query );

        include KQ_PLUGIN_DIR . 'includes/Modules/Wallet/views/admin-wallets.php';
    }

    public function render_transactions_page() {
        global $wpdb;
        $transactions = $wpdb->get_results( "SELECT t.*, u.user_login FROM {$wpdb->prefix}kq_wallet_transactions t 
                                            JOIN {$wpdb->users} u ON t.user_id = u.ID 
                                            ORDER BY t.created_at DESC LIMIT 50" );

        include KQ_PLUGIN_DIR . 'includes/Modules/Wallet/views/admin-transactions.php';
    }

    public function handle_manual_adjustment() {
        if ( ! isset( $_POST['kq_wallet_action'] ) || $_POST['kq_wallet_action'] !== 'manual_adjust' ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'kq_wallet_adjust' ) ) {
            return;
        }

        $user_id = intval( $_POST['user_id'] );
        $amount = floatval( $_POST['amount'] );
        $type = sanitize_text_field( $_POST['type'] ); // credit or debit
        $note = sanitize_text_field( $_POST['note'] );

        if ( $amount <= 0 ) {
            return;
        }

        if ( $type === 'credit' ) {
            $this->service->credit_wallet( $user_id, $amount, 'adjustment', 'admin', get_current_user_id(), $note );
        } else {
            $this->service->debit_wallet( $user_id, $amount, 'adjustment', 'admin', get_current_user_id(), $note );
        }

        wp_redirect( admin_url( 'admin.php?page=kq-wallets&message=success' ) );
        exit;
    }
}
