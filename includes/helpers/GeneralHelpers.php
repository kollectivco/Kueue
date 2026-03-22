<?php

use KueueEvents\Core\Modules\Delivery\DeliveryManager;

/**
 * Global helper to log delivery.
 * 
 * @param array $data Log details
 */
if ( ! function_exists( 'log_delivery' ) ) {
    function log_delivery( $data ) {
        DeliveryManager::log_delivery( $data );
    }
}

/**
 * Global helper to add a job to the delivery queue.
 */
if ( ! function_exists( 'kq_add_to_queue' ) ) {
    function kq_add_to_queue( $channel, $gateway_account_id, $payload ) {
        return DeliveryManager::add_to_queue( $channel, $gateway_account_id, $payload );
    }
}

/**
 * Global helper to process the delivery queue.
 */
if ( ! function_exists( 'kq_process_delivery_queue' ) ) {
    function kq_process_delivery_queue( $limit = 10 ) {
        \KueueEvents\Core\Modules\Delivery\QueueProcessor::process( $limit );
    }
}
