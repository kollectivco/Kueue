<?php

namespace KueueEvents\Core\Modules\Wallet;

class WalletRepository {

    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'kq_wallets';
    }

    /**
     * Get wallet by user ID.
     */
    public function get_by_user_id( $user_id ) {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE user_id = %d",
            $user_id
        ) );
    }

    /**
     * Create wallet for user.
     */
    public function create( $user_id, $currency = 'EGP' ) {
        global $wpdb;
        $wpdb->insert(
            $this->table,
            [
                'user_id'         => $user_id,
                'current_balance' => 0.00,
                'currency'        => $currency,
            ]
        );
        return $wpdb->insert_id;
    }

    /**
     * Update wallet balance.
     */
    public function update_balance( $wallet_id, $new_balance ) {
        global $wpdb;
        return $wpdb->update(
            $this->table,
            [ 'current_balance' => $new_balance ],
            [ 'id' => $wallet_id ]
        );
    }

    /**
     * Get or create wallet.
     */
    public function get_or_create( $user_id ) {
        $wallet = $this->get_by_user_id( $user_id );
        if ( ! $wallet ) {
            $this->create( $user_id );
            $wallet = $this->get_by_user_id( $user_id );
        }
        return $wallet;
    }
}
