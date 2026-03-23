<?php

namespace KueueEvents\Core\Modules\Gateways\SMS;

use KueueEvents\Core\Modules\Gateways\GatewayProviderInterface;

class NexmoProvider implements GatewayProviderInterface {

    private $config;

    public function __construct( $config ) {
        $this->config = $config;
    }

    /**
     * Send message using Nexmo (Vonage) API.
     */
    public function send_message( $to, $message, $context = [] ) {
        $api_key = $this->config['api_key'] ?? '';
        $api_secret = $this->config['api_secret'] ?? '';
        $from = $this->config['sender_id'] ?? '';

        if ( empty($api_key) || empty($api_secret) ) return false;

        $url = "https://rest.nexmo.com/sms/json";
        
        $data = [
            'api_key'    => $api_key,
            'api_secret' => $api_secret,
            'from'       => $from,
            'to'         => preg_replace('/[^0-9]/', '', $to),
            'text'       => $message,
            'type'       => 'unicode'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($response, true);
        return ( $status === 200 && isset($json['messages'][0]['status']) && (int)$json['messages'][0]['status'] === 0 );
    }

    public function test_connection() {
        return true;
    }

    public function validate_config( $config ) {
        return !empty($config['api_key']) && !empty($config['api_secret']);
    }

    public static function get_config_fields() {
        return [
            'api_key'    => [ 'label' => 'API Key',    'type' => 'text' ],
            'api_secret' => [ 'label' => 'API Secret', 'type' => 'password' ],
            'sender_id'  => [ 'label' => 'Sender ID',  'type' => 'text' ],
        ];
    }
}
