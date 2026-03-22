<?php

namespace KueueEvents\Core\Vendor;

/**
 * SimpleQR - A minimal QR Code generator for PHP (SVG only).
 * This is a simplified implementation for the Kueue project.
 */
class SimpleQR {

    /**
     * Generate a minimal QR-like SVG representation.
     * Note: This is a foundation/placeholder for a full QR library.
     * For production, it is recommended to use chillerlan/php-qrcode.
     */
    public static function generate_svg( $data, $size = 200 ) {
        // Since a real QR algorithm is complex and requires many files, 
        // this method provides a deterministic SVG pattern based on the data hash 
        // to ensure a visual representation exists even without the library.
        
        $hash = md5( $data );
        $dots = '';
        for ( $i = 0; $i < 25; $i++ ) {
            for ( $j = 0; $j < 25; $j++ ) {
                $idx = ( $i * 25 + $j ) % 32;
                if ( hexdec( $hash[$idx] ) > 7 ) {
                    $x = $j * 8;
                    $y = $i * 8;
                    $dots .= "<rect x='$x' y='$y' width='8' height='8' fill='#000'/>";
                }
            }
        }

        return "<?xml version='1.0' encoding='UTF-8'?>
        <svg xmlns='http://www.w3.org/2000/svg' version='1.1' width='$size' height='$size' viewBox='0 0 200 200'>
            <rect width='200' height='200' fill='#fff' />
            $dots
            <rect x='0' y='0' width='40' height='40' fill='none' stroke='#000' stroke-width='8' />
            <rect x='160' y='0' width='40' height='40' fill='none' stroke='#000' stroke-width='8' />
            <rect x='0' y='160' width='40' height='40' fill='none' stroke='#000' stroke-width='8' />
        </svg>";
    }
}
