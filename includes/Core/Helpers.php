<?php

if ( ! function_exists( 'kq_price' ) ) {
    function kq_price( $price ) {
        if ( function_exists( 'wc_price' ) ) {
            return wc_price( $price );
        }
        return sprintf( '%0.2f', (float) $price );
    }
}
if ( ! function_exists( 'kq_process_delivery_queue' ) ) {
    function kq_process_delivery_queue( $limit = 5 ) {
        if ( class_exists( '\KueueEvents\Core\Modules\Delivery\DeliveryManager' ) ) {
            \KueueEvents\Core\Modules\Delivery\DeliveryManager::process_queue( $limit );
        }
    }
}
