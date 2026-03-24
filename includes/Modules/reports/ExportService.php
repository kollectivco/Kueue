<?php

namespace KueueEvents\Core\Modules\Reports;

class ExportService {

    /**
     * Export attendees to CSV.
     */
    public static function export_attendees_csv( $event_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_attendees';
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT first_name, last_name, email, phone, status, created_at FROM $table WHERE event_id = %d",
            $event_id
        ), ARRAY_A );

        if ( empty( $results ) ) return;

        $filename = "attendees-event-$event_id-" . date('Y-m-d') . ".csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, array_keys($results[0]));
        foreach ($results as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    /**
     * Export tickets to CSV.
     */
    public static function export_tickets_csv( $event_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_tickets';
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT ticket_number, secure_token, ticket_status, checkin_status, issued_at FROM $table WHERE event_id = %d",
            $event_id
        ), ARRAY_A );

        if ( empty( $results ) ) return;

        $filename = "tickets-event-$event_id-" . date('Y-m-d') . ".csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, array_keys($results[0]));
        foreach ($results as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
}
