<?php

namespace KueueEvents\Core\Core;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    return;
}

class CliCommands {

    /**
     * Process the delivery queue.
     * 
     * ## OPTIONS
     * 
     * [--limit=<limit>]
     * : Number of jobs to process. Default 50.
     * 
     * ## EXAMPLES
     * 
     *     wp kq process-delivery-queue --limit=100
     */
    public function process_delivery_queue( $args, $assoc_args ) {
        $limit = isset( $assoc_args['limit'] ) ? (int) $assoc_args['limit'] : 50;

        \WP_CLI::line( sprintf( 'Processing up to %d delivery jobs...', $limit ) );

        global $wpdb;
        $table = $wpdb->prefix . 'kq_delivery_queue';
        
        $pending_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status IN ('pending', 'failed') AND retry_count < 3" );
        
        if ( ! $pending_count ) {
            \WP_CLI::success( 'Queue is empty.' );
            return;
        }

        \KueueEvents\Core\Modules\Delivery\QueueProcessor::process( $limit );

        $new_pending = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status IN ('pending', 'failed') AND retry_count < 3" );
        $processed = $pending_count - $new_pending;

        \WP_CLI::success( sprintf( 'Processed %d jobs. %d still pending.', $processed, $new_pending ) );
    }
}

\WP_CLI::add_command( 'kq', new CliCommands() );
