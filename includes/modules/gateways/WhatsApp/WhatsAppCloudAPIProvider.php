<?php

namespace KueueEvents\Core\Modules\Gateways\WhatsApp;

use KueueEvents\Core\Modules\Gateways\GatewayProviderInterface;

class WhatsAppCloudAPIProvider implements GatewayProviderInterface {

    protected $config;

    public function __construct( $config ) {
        $this->config = $config;
    }

    public function send_message( $to, $message, $context = [] ) {
        // Stub implementation
        error_log( "[WhatsAppCloud] Sending message to $to" );
        return true;
    }

    public function test_connection() {
        return true;
    }

    public function validate_config( $config ) {
        return isset( $config['phone_number_id'], $config['access_token'] );
    }
}
