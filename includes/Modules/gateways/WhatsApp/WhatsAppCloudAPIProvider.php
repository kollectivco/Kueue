<?php

namespace KueueEvents\Core\Modules\Gateways\WhatsApp;

use KueueEvents\Core\Modules\Gateways\GatewayProviderInterface;

class WhatsAppCloudAPIProvider implements GatewayProviderInterface {

    protected $config;

    public function __construct( $config ) {
        $this->config = $config;
    }

    public function send_message( $to, $message, $context = [] ) {
        $phone_number_id = $this->config['phone_number_id'] ?? '';
        $access_token = $this->config['access_token'] ?? '';

        if ( ! $phone_number_id || ! $access_token ) {
            error_log( "[WhatsAppCloud] Missing configuration." );
            return false;
        }

        // Clean phone number (remove +, spaces)
        $to = preg_replace( '/[^0-9]/', '', $to );

        $url = "https://graph.facebook.com/v17.0/{$phone_number_id}/messages";

        $body = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'text',
            'text'              => [ 'body' => $message ]
        ];

        $response = wp_remote_post( $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ],
            'body'    => json_encode( $body ),
            'timeout' => 15,
        ]);

        if ( is_wp_error( $response ) ) {
            error_log( "[WhatsAppCloud] API Request Error: " . $response->get_error_message() );
            return false;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body_res = wp_remote_retrieve_body( $response );

        if ( $code >= 200 && $code < 300 ) {
            return true;
        } else {
            error_log( "[WhatsAppCloud] API Failed (Code $code): " . $body_res );
            return false;
        }
    }

    public function test_connection() {
        return !empty($this->config['access_token']) && !empty($this->config['phone_number_id']);
    }

    public function validate_config( $config ) {
        return !empty( $config['phone_number_id'] ) && !empty( $config['access_token'] );
    }
}
