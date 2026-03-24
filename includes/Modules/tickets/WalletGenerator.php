<?php

namespace KueueEvents\Core\Modules\Tickets;

class WalletGenerator {

    /**
     * Generate Apple Wallet Pass
     */
    public static function generate_apple_pass( $ticket ) {
        $library_path = KQ_PLUGIN_DIR . 'includes/Vendor/php-pkpass/src/PKPass.php';
        if ( file_exists( $library_path ) ) {
            require_once $library_path;
            $exception_path = KQ_PLUGIN_DIR . 'includes/Vendor/php-pkpass/src/PKPassException.php';
            if ( file_exists( $exception_path ) ) {
                require_once $exception_path;
            }
        } else {
            return false;
        }
        // PKPass also needs its other files, but PKPass.php might not use an autoloader.
        // The Vendor/php-pkpass/src/ folder has them.
        
        try {
            $cert_path = KQ_PLUGIN_DIR . 'assets/certs/pass.p12';
            $wwdr_path = KQ_PLUGIN_DIR . 'includes/Vendor/php-pkpass/src/Certificate/AppleWWDRCA.pem';

            if ( ! file_exists( $cert_path ) ) {
                wp_die( __( 'Apple Wallet certificates not configured. Please contact the administrator.', 'kueue-events-core' ) );
            }

            $pass = new \PKPass\PKPass(); // Correct Namespace from the library

            // Set pass data
            $pass->setCertificatePath( $cert_path );
            $pass->setCertificatePassword( get_option( 'kq_apple_wallet_pass', 'password' ) );
            $pass->setWwdrCertificatePath( $wwdr_path );

            // Add required image files
            $icon = KQ_PLUGIN_DIR . 'assets/images/icon.png';
            $logo = KQ_PLUGIN_DIR . 'assets/images/logo.png';
            
            if ( file_exists( $icon ) ) $pass->addFile( $icon, 'icon.png' );
            if ( file_exists( $logo ) ) $pass->addFile( $logo, 'logo.png' );

            $data = [
                'description' => 'Event Ticket',
                'formatVersion' => 1,
                'organizationName' => get_bloginfo( 'name' ),
                'passTypeIdentifier' => get_option( 'kq_apple_wallet_pass_type_id', 'pass.com.kueue.events' ),
                'serialNumber' => $ticket->ticket_number,
                'teamIdentifier' => get_option( 'kq_apple_wallet_team_id', 'ABC1234567' ),
                'backgroundColor' => 'rgb(255, 49, 49)',
                'foregroundColor' => 'rgb(255, 255, 255)',
                'labelColor' => 'rgba(255, 255, 255, 0.8)',
                'eventTicket' => [
                    'primaryFields' => [
                        [
                            'key' => 'event',
                            'label' => 'EVENT',
                            'value' => get_the_title( $ticket->event_id )
                        ]
                    ],
                    'secondaryFields' => [
                        [
                            'key' => 'attendee',
                            'label' => 'ATTENDEE',
                            'value' => 'Attendee Name' // Should ideally match the ticket's attendee
                        ],
                        [
                            'key' => 'date',
                            'label' => 'DATE',
                            'value' => get_post_meta( $ticket->event_id, '_kq_start_date', true )
                        ]
                    ]
                ],
                'barcode' => [
                    'format' => 'PKBarcodeFormatQR',
                    'message' => $ticket->secure_token,
                    'messageEncoding' => 'iso-8859-1'
                ]
            ];

            $pass->setData( $data );
            $file = $pass->create();
            
            if ( $file ) {
                header( 'Content-Type: application/vnd.apple.pkpass' );
                header( 'Content-Disposition: attachment; filename="ticket.pkpass"' );
                echo $file;
                exit;
            }
        } catch ( \Exception $e ) {
            error_log( "Apple Wallet Error: " . $e->getMessage() );
            return false;
        }

        return false;
    }

    /**
     * Generate Google Wallet Link/Object (Logic placeholder using REST samples)
     */
    public static function generate_google_wallet_save_link( $ticket ) {
        // This usually involves JWT signing and returning a URL
        // Reference: includes/Vendor/google-wallet/php/demo_eventticket.php
        return "https://pay.google.com/gp/v/save/TICKET_JWT_STUB";
    }
}
