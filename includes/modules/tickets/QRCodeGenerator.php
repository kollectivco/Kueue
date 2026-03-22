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
     * Get Local QR Path (SVG).
     */
    public static function get_svg_image( $token ) {
        $url = self::generate_svg_url( $token );
        return '<img src="' . esc_attr( $url ) . '" alt="QR Code" />';
    }
}
