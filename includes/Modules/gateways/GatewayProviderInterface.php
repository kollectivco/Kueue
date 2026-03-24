<?php

namespace KueueEvents\Core\Modules\Gateways;

interface GatewayProviderInterface {

    /**
     * Send message.
     * 
     * @param string $to Recipient
     * @param string $message Content
     * @param array $context Additional data
     */
    public function send_message( $to, $message, $context = [] );

    /**
     * Test connection.
     */
    public function test_connection();

    /**
     * Validate config.
     */
    public function validate_config( $config );

}
