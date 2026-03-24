<?php

namespace KueueEvents\Core\Modules\Delivery;

class QueueProcessor {

    /**
     * Process the delivery queue.
     */
    public static function process( $limit = 20 ) {
        global $wpdb;
        $table_queue = $wpdb->prefix . 'kq_delivery_queue';

        $now = current_time( 'mysql' );
        $jobs = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_queue WHERE status IN ('pending', 'failed') AND retry_count < 3 AND scheduled_at <= %s ORDER BY scheduled_at ASC LIMIT %d",
            $now, $limit
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

        // 1) Deduplication Lock / Idempotency
        $lock_key = 'kq_delivery_lock_' . $job->id;
        if ( ! set_transient( $lock_key, 'locked', 60 ) ) {
            return; // Already being processed by another worker
        }

        // 2) Throttling Check
        if ( ! self::check_throttle( $job->channel, $job->gateway_account_id ) ) {
            delete_transient( $lock_key );
            return; 
        }

        // Mark as processing
        $affected = $wpdb->update( $table_queue, [ 'status' => 'processing' ], [ 'id' => $job->id, 'status' => $job->status ] );
        if ( ! $affected ) {
            delete_transient( $lock_key );
            return;
        }

        $payload = json_decode( $job->payload_json, true );
        $provider = null;

        if ( 'email' === $job->channel ) {
            $provider = new EmailProvider();
        } else {
            $provider = \KueueEvents\Core\Modules\Gateways\GatewayManager::get_provider( $job->gateway_account_id );
        }

        if ( ! $provider ) {
            $wpdb->update( $table_queue, [
                'status'      => 'failed',
                'retry_count' => $job->retry_count + 1
            ], [ 'id' => $job->id ] );
            return;
        }

        try {
            $result = $provider->send_message( $payload['to'], $payload['message'] ?? '', $payload['context'] ?? [] );

            if ( $result ) {
                $wpdb->update( $table_queue, [
                    'status'       => 'done',
                    'processed_at' => current_time( 'mysql' )
                ], [ 'id' => $job->id ] );

                // Log delivery
                DeliveryManager::log_delivery([
                    'channel'            => $job->channel,
                    'gateway_account_id' => $job->gateway_account_id,
                    'recipient'          => $payload['to'],
                    'payload_summary'    => substr( $payload['message'] ?? '', 0, 100 ),
                    'status'             => 'success',
                    'response_code'      => '200',
                    'response_body'      => 'OK'
                ]);

                // Update last sent time for throttling
                self::update_throttle( $job->channel, $job->gateway_account_id );
            } else {
                throw new \Exception( 'Provider returned false' );
            }
        } catch ( \Exception $e ) {
            $retry_count = $job->retry_count + 1;
            $next_attempt = self::calculate_backoff( $retry_count );

            $wpdb->update( $table_queue, [
                'status'       => 'failed',
                'retry_count'  => $retry_count,
                'scheduled_at' => $next_attempt
            ], [ 'id' => $job->id ] );

            // Log error
            DeliveryManager::log_delivery([
                'channel'            => $job->channel,
                'gateway_account_id' => $job->gateway_account_id,
                'recipient'          => $payload['to'],
                'payload_summary'    => substr( $payload['message'] ?? '', 0, 100 ),
                'status'             => 'failed',
                'response_code'      => '500',
                'response_body'      => $e->getMessage()
            ]);
        } finally {
            delete_transient( $lock_key );
        }
    }

    /**
     * Calculate next attempt with backoff.
     */
    private static function calculate_backoff( $retry_count ) {
        $minutes = 5;
        if ( $retry_count === 2 ) $minutes = 15;
        if ( $retry_count === 3 ) $minutes = 60;

        return date( 'Y-m-d H:i:s', current_time( 'timestamp' ) + ( $minutes * 60 ) );
    }

    /**
     * Check if channel is throttled.
     */
    private static function check_throttle( $channel, $gateway_id ) {
        if ( $channel === 'email' ) return true; // Email usually handled by SMTP/Host limits

        $limit_seconds = (int) get_option( 'kq_throttle_' . $channel, 1 ); // Default 1 second
        $last_sent = (int) get_option( 'kq_last_sent_' . $channel . '_' . $gateway_id, 0 );
        
        if ( ( time() - $last_sent ) < $limit_seconds ) {
            return false;
        }
        return true;
    }

    /**
     * Update last sent time.
     */
    private static function update_throttle( $channel, $gateway_id ) {
        update_option( 'kq_last_sent_' . $channel . '_' . $gateway_id, time() );
    }
}
