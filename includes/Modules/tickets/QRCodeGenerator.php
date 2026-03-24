<?php

namespace KueueEvents\Core\Modules\Tickets;

class QRCodeGenerator {

    /**
     * Generate SVG QR code URL via public API (goqr.me).
     */
    public static function generate_svg_url( $token, $size = 200 ) {
        $payload = 'kq:t:' . $token;
        return sprintf(
            'https://api.qrserver.com/v1/create-qr-code/?size=%dx%d&data=%s&format=svg&ecc=H&margin=1',
            $size, $size, urlencode( $payload )
        );
    }

    /**
     * Generate PNG QR code URL.
     */
    public static function generate_png_url( $token, $size = 200 ) {
        $payload = 'kq:t:' . $token;
        return sprintf(
            'https://api.qrserver.com/v1/create-qr-code/?size=%dx%d&data=%s&format=png&ecc=H&margin=1',
            $size, $size, urlencode( $payload )
        );
    }

    /**
     * Generate localized Data URI (Base64) for QR code.
     * Useful for PDF generation without remote dependencies.
     */
    public static function generate_data_uri( $token, $size = 200 ) {
        if ( class_exists( '\KueueEvents\Core\Vendor\SimpleQR' ) ) {
            $svg = \KueueEvents\Core\Vendor\SimpleQR::generate_svg( $token, $size );
            return 'data:image/svg+xml;base64,' . base64_encode( $svg );
        }

        // Fallback to remote PNG if local fails
        return self::generate_png_url( $token, $size );
    }
}
