<?php

namespace KueueEvents\Core\Modules\Payouts;

class PayoutRepository {

    /**
     * Create withdrawal request.
     */
    public static function create( $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_payouts';

        $wpdb->insert( $table, [
            'organizer_id'   => $data['organizer_id'],
            'amount'         => $data['amount'],
            'status'         => 'pending',
            'payment_method' => $data['payment_method'] ?? 'manual',
            'notes'          => $data['notes'] ?? '',
            'created_at'     => current_time( 'mysql' ),
        ] );

        return $wpdb->insert_id;
    }

    /**
     * Get organizer payouts.
     */
    public static function get_by_organizer( $organizer_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_payouts';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table WHERE organizer_id = %d ORDER BY created_at DESC",
            $organizer_id
        ) );
    }

    /**
     * Update payout status.
     */
    public static function update_status( $id, $status, $notes = '' ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_payouts';
        return $wpdb->update( $table, [ 'status' => $status, 'notes' => $notes ], [ 'id' => $id ] );
    }
}
