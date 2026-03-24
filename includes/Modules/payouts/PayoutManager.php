<?php

namespace KueueEvents\Core\Modules\Payouts;

use KueueEvents\Core\Modules\Finance\CommissionService;

class PayoutManager {

    /**
     * Submit a payout request.
     */
    public static function request_payout( $organizer_id, $amount, $method = 'manual', $notes = '' ) {
        // 1. Verify balance
        $balance_info = CommissionService::get_organizer_balance( $organizer_id );
        
        if ( $amount > $balance_info->available_balance ) {
            return new \WP_Error( 'insufficient_balance', __( 'Requested amount exceeds available balance.', 'kueue-events-core' ) );
        }

        if ( $amount <= 0 ) {
            return new \WP_Error( 'invalid_amount', __( 'Amount must be greater than zero.', 'kueue-events-core' ) );
        }

        // 2. Create payout record
        $payout_id = PayoutRepository::create([
            'organizer_id'   => $organizer_id,
            'amount'         => $amount,
            'payment_method' => $method,
            'notes'          => $notes
        ]);

        return $payout_id;
    }

    /**
     * Approve Payout (Mark as Completed).
     */
    public static function approve_payout( $payout_id, $admin_notes = '' ) {
        return PayoutRepository::update_status( $payout_id, 'completed', $admin_notes );
    }

    /**
     * Reject Payout (Mark as Cancelled).
     */
    public static function reject_payout( $payout_id, $reason = '' ) {
        return PayoutRepository::update_status( $payout_id, 'cancelled', $reason );
    }
}
