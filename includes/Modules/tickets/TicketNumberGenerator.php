<?php

namespace KueueEvents\Core\Modules\Tickets;

class TicketNumberGenerator {

    /**
     * Generate unique ticket number.
     * Format: KQ-{EVENTID}-{RANDOM_STRING}
     */
    public static function generate_ticket_number( $event_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_tickets';
        
        $prefix = "KQ-" . $event_id . "-";
        
        $is_unique = false;
        $ticket_number = '';
        $max_retries = 10;
        $retries = 0;

        while ( ! $is_unique && $retries < $max_retries ) {
            $random = strtoupper( substr( md5( uniqid( mt_rand(), true ) ), 0, 8 ) );
            $ticket_number = $prefix . $random;

            $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE ticket_number = %s", $ticket_number ) );
            if ( ! $exists ) {
                $is_unique = true;
            }
            $retries++;
        }

        return $ticket_number;
    }

    /**
     * Generate secure cryptographically strong token.
     */
    public static function generate_secure_token() {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_tickets';
        
        $is_unique = false;
        $token = '';
        $max_retries = 10;
        $retries = 0;

        while ( ! $is_unique && $retries < $max_retries ) {
            $token = bin2hex( random_bytes( 20 ) ); // 40 chars

            $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE secure_token = %s", $token ) );
            if ( ! $exists ) {
                $is_unique = true;
            }
            $retries++;
        }

        return $token;
    }
}
