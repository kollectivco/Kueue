<?php

namespace KueueEvents\Core\Modules\Finance;

class CommissionRepository {

    /**
     * Get commissions for a given organizer or all.
     */
    public static function get_paged( $page = 1, $limit = 20, $organizer_id = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_commissions';
        $offset = ( $page - 1 ) * $limit;

        $sql = "SELECT * FROM $table";
        $params = [];

        if ( $organizer_id ) {
            $sql .= " WHERE organizer_id = %d";
            $params[] = $organizer_id;
        }

        $sql .= " ORDER BY id DESC LIMIT %d OFFSET %d";
        $params[] = $limit;
        $params[] = $offset;

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }

    /**
     * Get global financial stats.
     */
    public static function get_global_stats() {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_commissions';

        return $wpdb->get_row( "SELECT 
            SUM(gross_amount) as total_gross,
            SUM(commission_amount) as total_commission,
            SUM(net_amount) as total_net,
            SUM(CASE WHEN status = 'unpaid' THEN net_amount ELSE 0 END) as pending_payouts
            FROM $table" );
    }
}
