<?php

if ( ! function_exists( 'kq_price' ) ) {
    function kq_price( $price ) {
        if ( function_exists( 'wc_price' ) ) {
            return wc_price( $price );
        }
        return sprintf( '%0.2f', (float) $price );
    }
}
