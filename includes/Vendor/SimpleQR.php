<?php

namespace KueueEvents\Core\Vendor;

/**
 * SimpleQR - A minimal QR Code generator for PHP (SVG only).
 * This is a simplified implementation for the Kueue project.
 */
class SimpleQR {

    public static function generate_svg( $data, $size = 200 ) {
        $lib_path = KQ_PLUGIN_DIR . 'includes/Vendor/phpqrcode/qrlib.php';
        if ( ! file_exists( $lib_path ) ) {
            return '<svg>Library Missing</svg>';
        }

        require_once $lib_path;

        ob_start();
        \QRcode::svg( $data, false, \QR_ECLEVEL_L, 3 );
        $svg = ob_get_clean();

        // Adjust size if needed
        return str_replace( '<svg ', "<svg width='$size' height='$size' ", $svg );
    }
}
