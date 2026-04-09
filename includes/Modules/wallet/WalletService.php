<?php

namespace KueueEvents\Core\Modules\Wallet;

class WalletService {

    private $wallet_repo;
    private $transaction_repo;

    public function __construct() {
        $this->wallet_repo = new WalletRepository();
        $this->transaction_repo = new WalletTransactionRepository();
    }

    /**
     * Get or create wallet for user.
     */
    public function get_or_create_wallet( $user_id ) {
        return $this->wallet_repo->get_or_create( $user_id );
    }

    /**
     * Get wallet balance for user.
     */
    public function get_balance( $user_id ) {
        $wallet = $this->get_or_create_wallet( $user_id );
        return $wallet ? (float) $wallet->current_balance : 0.00;
    }

    /**
     * Credit wallet (Add money).
     */
    public function credit_wallet( $user_id, $amount, $type = 'credit', $reference_type = null, $reference_id = null, $note = null ) {
        return $this->process_transaction( $user_id, $amount, $type, $reference_type, $reference_id, $note );
    }

    /**
     * Debit wallet (Subtract money).
     */
    public function debit_wallet( $user_id, $amount, $type = 'debit', $reference_type = null, $reference_id = null, $note = null ) {
        return $this->process_transaction( $user_id, -$amount, $type, $reference_type, $reference_id, $note );
    }

    /**
     * Check if user can pay with wallet.
     */
    public function can_user_pay_with_wallet( $user_id, $amount ) {
        $balance = $this->get_balance( $user_id );
        return $balance >= $amount;
    }

    /**
     * Refund to wallet.
     */
    public function refund_to_wallet( $user_id, $amount, $reference_type = 'refund', $reference_id = null, $note = 'Refund credited to wallet' ) {
        return $this->credit_wallet( $user_id, $amount, 'refund', $reference_type, $reference_id, $note );
    }

    /**
     * Internal: Process transaction atomically.
     */
    private function process_transaction( $user_id, $amount, $type, $reference_type = null, $reference_id = null, $note = null ) {
        global $wpdb;

        // Ensure amount is float
        $amount = (float) $amount;
        if ( $amount === 0.0 ) {
            return false;
        }

        // Use transaction for atomicity
        $wpdb->query( 'START TRANSACTION' );

        try {
            // FOR UPDATE to lock the row
            $wallet = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}kq_wallets WHERE user_id = %d FOR UPDATE",
                $user_id
            ) );

            if ( ! $wallet ) {
                // Create wallet if not exists within transaction
                $this->wallet_repo->create( $user_id );
                $wallet = $wpdb->get_row( $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}kq_wallets WHERE user_id = %d FOR UPDATE",
                    $user_id
                ) );
            }

            $current_balance = (float) $wallet->current_balance;
            $new_balance = $current_balance + $amount;

            // Check if debit is possible
            if ( $new_balance < 0 ) {
                $wpdb->query( 'ROLLBACK' );
                return false;
            }

            // Update wallet
            $this->wallet_repo->update_balance( $wallet->id, $new_balance );

            // Record transaction
            $this->transaction_repo->create( [
                'wallet_id'      => $wallet->id,
                'user_id'        => $user_id,
                'type'           => $type,
                'amount'         => abs( $amount ), // Store positive amount in ledger, type dictates direction
                'balance_before' => $current_balance,
                'balance_after'  => $new_balance,
                'reference_type' => $reference_type,
                'reference_id'   => $reference_id,
                'note'           => $note,
                'status'         => 'completed',
            ] );

            $wpdb->query( 'COMMIT' );

            // Hooks for extensibility
            do_action( 'kq_wallet_transaction_processed', $user_id, $amount, $type, $reference_id );
            if ( $amount > 0 ) {
                do_action( 'kq_wallet_credited', $user_id, $amount, $type, $reference_id );
            } else {
                do_action( 'kq_wallet_debited', $user_id, abs( $amount ), $type, $reference_id );
            }

            return true;

        } catch ( \Exception $e ) {
            $wpdb->query( 'ROLLBACK' );
            return false;
        }
    }
}
