<?php

namespace KueueEvents\Core\Helpers;

class EncryptionHelper {

    /**
     * Encryption method.
     * Uses wp_salt for key derivation.
     */
    public static function encrypt( $value ) {
        if ( ! $value ) return false;

        $key = wp_salt( 'nonce' );
        $cipher = 'aes-256-cbc';
        $iv_length = openssl_cipher_iv_length( $cipher );
        $iv = openssl_random_pseudo_bytes( $iv_length );

        $encrypted = openssl_encrypt( $value, $cipher, $key, 0, $iv );
        return base64_encode( $iv . $encrypted );
    }

    /**
     * Decryption method.
     */
    public static function decrypt( $value ) {
        if ( ! $value ) return false;

        $key = wp_salt( 'nonce' );
        $cipher = 'aes-256-cbc';
        $iv_length = openssl_cipher_iv_length( $cipher );
        $data = base64_decode( $value );

        $iv = substr( $data, 0, $iv_length );
        $encrypted = substr( $data, $iv_length );

        return openssl_decrypt( $encrypted, $cipher, $key, 0, $iv );
    }

    /**
     * Mask credentials for UI display.
     */
    public static function mask( $value ) {
        if ( ! $value ) return '';
        $len = strlen( $value );
        if ( $len <= 4 ) return '****';
        return substr( $value, 0, 2 ) . str_repeat( '*', $len - 4 ) . substr( $value, -2 );
    }
}
