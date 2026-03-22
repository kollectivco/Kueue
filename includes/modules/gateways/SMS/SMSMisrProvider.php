<?php

namespace KueueEvents\Core\Modules\Gateways\SMS;

use KueueEvents\Core\Modules\Gateways\GatewayProviderInterface;

class SMSMisrProvider implements GatewayProviderInterface {

    protected $config;

    public function __construct( $config ) {
        $this->config = $config;
    }

    public function send_message( $to, $message, $context = [] ) {
        // Stub implementation
        error_log( "[SMSMisr] Sending message to $to" );
        return true;
    }

    public function test_connection() {
        return true;
    }

    public function validate_config( $config ) {
        return isset( $config['username'], $config['password'], $config['sender'] );
    }
}
