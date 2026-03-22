<?php

namespace KueueEvents\Core\Modules\Delivery;

class DeliveryManager {

    /**
     * Add a message to the delivery queue.
     */
    public static function add_to_queue( $channel, $gateway_account_id, $payload ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_delivery_queue';

        return $wpdb->insert( $table, [
            'channel'            => $channel,
            'gateway_account_id' => $gateway_account_id,
            'payload_json'       => json_encode( $payload ),
            'status'             => 'pending',
            'retry_count'        => 0,
            'scheduled_at'       => current_time( 'mysql' )
        ] );
    }

    /**
     * Process the queue.
     * Manual trigger or cron job.
     */
    public static function process_queue( $limit = 10 ) {
        global $wpdb;
        $table_queue = $wpdb->prefix . 'kq_delivery_queue';

        $jobs = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_queue WHERE status IN ('pending', 'failed') AND retry_count < 3 ORDER BY scheduled_at ASC LIMIT %d",
            $limit
        ) );

        foreach ( $jobs as $job ) {
            self::process_job( $job );
        }
    }

    /**
     * Process a single job.
     */
    private static function process_job( $job ) {
        global $wpdb;
        $table_queue = $wpdb->prefix . 'kq_delivery_queue';

        // Mark as processing
        $wpdb->update( $table_queue, [ 'status' => 'processing' ], [ 'id' => $job->id ] );

        $payload = json_decode( $job->payload_json, true );
        $provider = \KueueEvents\Core\Modules\Gateways\GatewayManager::get_provider( $job->gateway_account_id );

        if ( ! $provider ) {
            $wpdb->update( $table_queue, [
                'status'      => 'failed',
                'retry_count' => $job->retry_count + 1
            ], [ 'id' => $job->id ] );
            return;
        }

        $result = $provider->send_message( $payload['to'], $payload['message'], $payload['context'] ?? [] );

        if ( $result ) {
            $wpdb->update( $table_queue, [
                'status'       => 'done',
                'processed_at' => current_time( 'mysql' )
            ], [ 'id' => $job->id ] );
            
            // Log delivery
            self::log_delivery([
                'channel'            => $job->channel,
                'gateway_account_id' => $job->gateway_account_id,
                'recipient'          => $payload['to'],
                'payload_summary'    => substr( $payload['message'], 0, 100 ),
                'status'             => 'success',
                'response_code'      => '200',
                'response_body'      => 'OK'
            ]);
        } else {
            $wpdb->update( $table_queue, [
                'status'      => 'failed',
                'retry_count' => $job->retry_count + 1
            ], [ 'id' => $job->id ] );
        }
    }

    /**
     * Log delivery details.
     */
    public static function log_delivery( $data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_delivery_logs';

        $wpdb->insert( $table, [
            'channel'            => $data['channel'],
            'gateway_account_id' => $data['gateway_account_id'],
            'recipient'          => $data['recipient'],
            'payload_summary'    => $data['payload_summary'],
            'status'             => $data['status'],
            'response_code'      => $data['response_code'] ?? '',
            'response_body'      => $data['response_body'] ?? '',
            'created_at'         => current_time( 'mysql' )
        ] );
    }
}
