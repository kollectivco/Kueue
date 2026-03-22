<?php

namespace KueueEvents\Core\Modules\Reports;

class ReportsService {

    /**
     * Get event summary report.
     */
    public static function get_event_summary( $event_id ) {
        global $wpdb;
        $tickets_table = $wpdb->prefix . 'kq_tickets';
        $commissions_table = $wpdb->prefix . 'kq_commissions';

        $raw_stats = $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                COUNT(id) as total_tickets,
                SUM(CASE WHEN checkin_status = 'checked_in' THEN 1 ELSE 0 END) as checkins,
                SUM(CASE WHEN ticket_status = 'active' THEN 1 ELSE 0 END) as active_tickets
             FROM $tickets_table 
             WHERE event_id = %d",
            $event_id
        ) );

        $revenue_stats = $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                SUM(gross_amount) as gross_revenue,
                SUM(commission_amount) as total_commission,
                SUM(net_amount) as net_revenue
             FROM $commissions_table 
             WHERE event_id = %d",
            $event_id
        ) );

        return [
            'total_tickets'   => $raw_stats->total_tickets ?? 0,
            'checkins'        => $raw_stats->checkins ?? 0,
            'active_tickets'  => $raw_stats->active_tickets ?? 0,
            'gross_revenue'   => $revenue_stats->gross_revenue ?? 0,
            'total_commission'=> $revenue_stats->total_commission ?? 0,
            'net_revenue'     => $revenue_stats->net_revenue ?? 0,
        ];
    }

    /**
     * Get global summary stats.
     */
    public static function get_global_summary( $organizer_id = null ) {
        global $wpdb;
        $commissions_table = $wpdb->prefix . 'kq_commissions';
        
        $where = "TRUE";
        $params = [];
        if ( $organizer_id ) {
            $where = "organizer_id = %d";
            $params[] = $organizer_id;
        }

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT 
                SUM(gross_amount) as gross,
                SUM(commission_amount) as commission,
                SUM(net_amount) as net
             FROM $commissions_table 
             WHERE $where",
            $params
        ) );
    }
}
