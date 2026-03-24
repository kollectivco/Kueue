<?php

namespace KueueEvents\Core\Modules\Tickets;

/**
 * Google Wallet Service - Handles integration with Google Pay API for Passes.
 * This class provides the foundation for JWT signing and object creation.
 * REQUIRED: Google Service Account Key (JSON) and Class ID.
 */
class GoogleWalletService {

    private $issuer_id;
    private $class_id;
    private $service_account_path;

    public function __construct() {
        // These should be loaded from settings in a real production environment
        $this->issuer_id = get_option( 'kq_google_wallet_issuer_id' );
        $this->class_id = get_option( 'kq_google_wallet_class_id' );
        $this->service_account_path = KQ_PLUGIN_DIR . 'assets/certs/google-service-account.json';
    }

    /**
     * Generate a "Save to Google Wallet" URL for a specific ticket.
     */
    public function get_save_url( $ticket ) {
        if ( ! $this->is_configured() ) {
            return '#google-wallet-not-configured';
        }

        // 1. Create the JWT payload (following demo_eventticket.php logic)
        $payload = $this->create_jwt_payload( $ticket );

        // 2. Sign the JWT using the Service Account
        $jwt = $this->sign_jwt( $payload );

        if ( ! $jwt ) {
            return '#google-wallet-signing-failed';
        }

        return 'https://pay.google.com/gp/v/save/' . $jwt;
    }

    /**
     * Check if the service is minimally configured.
     */
    public function is_configured() {
        return ! empty( $this->issuer_id ) && file_exists( $this->service_account_path );
    }

    /**
     * Create the JWT Payload structure for an Event Ticket.
     * This uses the 'savetowallet' type which creates Classes/Objects on-the-fly.
     */
    private function create_jwt_payload( $ticket ) {
        $issuer_email = json_decode( file_get_contents( $this->service_account_path ), true )['client_email'];
        $event_name = get_the_title( $ticket->event_id );
        $class_id = "{$this->issuer_id}.{$this->class_id}";
        $object_id = "{$this->issuer_id}.{$ticket->ticket_number}";

        return [
            'iss' => $issuer_email,
            'aud' => 'google',
            'typ' => 'savetowallet',
            'iat' => time(),
            'payload' => [
                'eventTicketClasses' => [
                    [
                        'id' => $class_id,
                        'issuerName' => get_bloginfo( 'name' ),
                        'reviewStatus' => 'UNDER_REVIEW',
                        'eventName' => [
                            'defaultValue' => [
                                'language' => 'en-US',
                                'value' => $event_name
                            ]
                        ]
                    ]
                ],
                'eventTicketObjects' => [
                    [
                        'id' => $object_id,
                        'classId' => $class_id,
                        'state' => 'ACTIVE',
                        'barcode' => [
                            'type' => 'QR_CODE',
                            'value' => $ticket->secure_token
                        ],
                        'reservationInfo' => [
                            'confirmationCode' => $ticket->ticket_number
                        ],
                        'ticketHolderName' => 'Attendee Name',
                        'ticketNumber' => $ticket->ticket_number
                    ]
                ]
            ]
        ];
    }

    /**
     * Sign the JWT payload.
     * Requires firebase/php-jwt.
     */
    private function sign_jwt( $payload ) {
        if ( ! $this->is_configured() ) {
            return false;
        }

        // Load JWT Library with case-sensitive check
        $jwt_path = KQ_PLUGIN_DIR . 'includes/Vendor/firebase-php-jwt/JWT.php';
        $key_path = KQ_PLUGIN_DIR . 'includes/Vendor/firebase-php-jwt/Key.php';

        if ( file_exists( $jwt_path ) ) {
            require_once $jwt_path;
            require_once $key_path;
        }

        try {
            $key_data = json_decode( file_get_contents( $this->service_account_path ), true );
            if ( ! isset( $key_data['private_key'] ) ) {
                return false;
            }

            return \Firebase\JWT\JWT::encode(
                $payload,
                $key_data['private_key'],
                'RS256'
            );
        } catch ( \Exception $e ) {
            error_log( "Google Wallet JWT Error: " . $e->getMessage() );
            return false;
        }
    }
}
