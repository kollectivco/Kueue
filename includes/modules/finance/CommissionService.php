<?php

namespace KueueEvents\Core\Modules\Finance;

class CommissionService {

    /**
     * Record a commission record for a sale.
     * Uses hierarchy: Event -> Organizer -> Global
     */
    public static function record_sale( $event_id, $organizer_id, $gross_amount, $order_id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_commissions';

        // 1. Get Commission Rate (Hierarchy)
        $rate_info = self::get_commission_rate( $event_id, $organizer_id );
        
        $commission_value = $rate_info['value'];
        $commission_type  = $rate_info['type'];

        // 2. Calculate
        $commission_amount = 0;
        if ( $commission_type === 'percentage' ) {
            $commission_amount = (float) $gross_amount * ( (float) $commission_value / 100 );
        } else {
            // Fixed amount per sale
            $commission_amount = (float) $commission_value;
        }

        $net_amount = (float) $gross_amount - $commission_amount;

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
     * Resolve commission rate using hierarchy.
     */
    public static function get_commission_rate( $event_id, $organizer_id ) {
        // A. Check Event Override
        $event_comm_type = get_post_meta( $event_id, '_kq_commission_type', true );
        $event_comm_val  = get_post_meta( $event_id, '_kq_commission_value', true );

        if ( ! empty( $event_comm_type ) && $event_comm_val !== '' ) {
            return [ 'type' => $event_comm_type, 'value' => $event_comm_val ];
        }

        // B. Check Organizer Override
        $organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_id( $organizer_id );
        if ( $organizer && ! empty( $organizer->commission_type ) && $organizer->commission_value !== '' ) {
            return [ 'type' => $organizer->commission_type, 'value' => $organizer->commission_value ];
        }

        // C. Fallback to Global
        return [
            'type'  => get_option( 'kq_global_commission_type', 'percentage' ),
            'value' => get_option( 'kq_global_commission_value', '10' )
        ];
    }

    /**
     * Get organizer balance summary.
     */
    public static function get_organizer_balance( $organizer_id ) {
        global $wpdb;
        $table_comm = $wpdb->prefix . 'kq_commissions';
        $table_payouts = $wpdb->prefix . 'kq_payouts';

        // Total Net Earnings
        $total_earned = $wpdb->get_var( $wpdb->prepare(
            "SELECT SUM(net_amount) FROM $table_comm WHERE organizer_id = %d",
            $organizer_id
        ) );

        // Total Paid Out (Withdrawals)
        $total_paid = $wpdb->get_var( $wpdb->prepare(
            "SELECT SUM(amount) FROM $table_payouts WHERE organizer_id = %d AND status = 'completed'",
            $organizer_id
        ) );

        // Total Pending (Requested)
        $total_pending = $wpdb->get_var( $wpdb->prepare(
            "SELECT SUM(amount) FROM $table_payouts WHERE organizer_id = %d AND status = 'pending'",
            $organizer_id
        ) );

        $balance = (float) $total_earned - (float) $total_paid;

        return (object) [
            'total_earned'     => (float) $total_earned,
            'total_withdrawn'  => (float) $total_paid,
            'pending_payouts'  => (float) $total_pending,
            'available_balance'=> $balance > 0 ? $balance : 0
        ];
    }
}
