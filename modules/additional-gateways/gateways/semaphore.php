<?php

namespace SMSGateway;


class Semaphore
{
    public static $chunks = 50;
    public static $supports_bulk = true;
    public static $bulk_type = 'FIXED_MESSAGE';

    public static function sendSMS($gateway_fields, $mobile, $message, $test_call)
    {
        $api_key = $gateway_fields['api_key'];
        $sender = $gateway_fields['sender'];

        return self::process_sms($api_key, $sender, $mobile, $message, $test_call);
    }

    public static function sendBulkSMS($gateway_fields, $messages, $test_call)
    {
        $api_key = $gateway_fields['api_key'];
        $sender = $gateway_fields['sender'];

    }

    public static function process_sms($api_key, $sender, $mobile, $message, $test_call)
    {
        $curl = curl_init();


        $post_params = array(
            'apikey' => $api_key,
            'number' => str_replace('+', '', $mobile),
            'message' => $message,
        );

        if (!empty($sender)) {
            $post_params['sendername'] = $sender;
        }

        curl_setopt($curl, CURLOPT_URL, 'https://api.semaphore.co/api/v4/messages');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_params);
        curl_setopt($curl, CURLOPT_PROXY_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_errno($curl);

        if ($test_call) {
            return $result;
        }

        $is_success = 200 <= $code && $code < 300;

        return $is_success;
    }
}
