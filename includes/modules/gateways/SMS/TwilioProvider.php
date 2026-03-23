<?php

namespace KueueEvents\Core\Modules\Gateways\SMS;

use KueueEvents\Core\Modules\Gateways\GatewayProviderInterface;

class TwilioProvider implements GatewayProviderInterface {

    private $config;

    public function __construct( $config ) {
        $this->config = $config;
    }

    /**
     * Send message using Twilio's REST API.
     */
    public function send_message( $to, $message, $context = [] ) {
        $sid = $this->config['account_sid'] ?? '';
        $token = $this->config['auth_token'] ?? '';
        $from = $this->config['sender_id'] ?? '';

        if ( empty($sid) || empty($token) || empty($from) ) return false;

        $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
        
        $data = [
            'From' => $from,
            'To'   => $to,
            'Body' => $message
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, $sid . ":" . $token);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ( $status >= 200 && $status < 300 );
    }

    public function test_connection() {
        // Implement simple balance check or similar for testing
        return true;
    }

    public function validate_config( $config ) {
        return !empty($config['account_sid']) && !empty($config['auth_token']) && !empty($config['sender_id']);
    }

    public static function get_config_fields() {
        return [
            'account_sid' => [ 'label' => 'Account SID', 'type' => 'text' ],
            'auth_token'  => [ 'label' => 'Auth Token',  'type' => 'password' ],
            'sender_id'   => [ 'label' => 'From Number', 'type' => 'text' ],
        ];
    }
}
