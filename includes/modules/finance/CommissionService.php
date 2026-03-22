<?php

namespace KueueEvents\Core\Modules\Finance;

class CommissionService {

    /**
     * Record a commission record for a sale.
     */
    public static function record_sale( $event_id, $organizer_id, $gross_amount, $order_id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_commissions';

        // Fetch organizer commission settings
        $organizer = \KueueEvents\Core\Modules\Organizers\OrganizerRepository::get_by_id( $organizer_id );
        if ( ! $organizer ) return false;

        $commission_amount = 0;
        if ( $organizer->commission_type === 'percentage' ) {
            $commission_amount = $gross_amount * ( $organizer->commission_value / 100 );
        } else {
            $commission_amount = $organizer->commission_value;
        }

        $net_amount = $gross_amount - $commission_amount;

        return $wpdb->insert( $table, [
            'organizer_id'       => $organizer_id,
            'event_id'           => $event_id,
            'order_id'           => $order_id,
            'gross_amount'       => $gross_amount,
            'commission_amount'  => $commission_amount,
            'net_amount'         => $net_amount,
            'status'             => 'unpaid',
            'created_at'         => current_time( 'mysql' ),
        ] );
    }

    /**
     * Get organizer balance.
     */
    public static function get_organizer_balance( $organizer_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_commissions';
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                SUM(net_amount) as total_earned,
                SUM(CASE WHEN status = 'paid' THEN net_amount ELSE 0 END) as total_paid,
                SUM(CASE WHEN status = 'unpaid' THEN net_amount ELSE 0 END) as pending_payout
             FROM $table 
             WHERE organizer_id = %d",
            $organizer_id
        ) );
    }
}
