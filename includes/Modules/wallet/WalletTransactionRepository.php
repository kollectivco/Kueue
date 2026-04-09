<?php

namespace KueueEvents\Core\Modules\Wallet;

class WalletTransactionRepository {

    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'kq_wallet_transactions';
    }

    /**
     * Create a transaction record.
     */
    public function create( $data ) {
        global $wpdb;
        $wpdb->insert( $this->table, $data );
        return $wpdb->insert_id;
    }

    /**
     * Get transactions for a user.
     */
    public function get_user_transactions( $user_id, $limit = 20, $offset = 0 ) {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $user_id,
            $limit,
            $offset
        ) );
    }

    /**
     * Get transaction count for a user.
     */
    public function get_user_transaction_count( $user_id ) {
        global $wpdb;
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE user_id = %d",
            $user_id
        ) );
    }
}
