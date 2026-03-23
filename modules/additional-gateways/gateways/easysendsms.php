<?php

namespace SMSGateway;

require_once 'utils.php';

class EasysendSMS
{
    public static $chunks = 50;
    public static $supports_bulk = true;
    public static $bulk_type = 'FIXED_MESSAGE';

    // docs at: https://www.easysendsms.com/http-api
    public static function sendSMS($gateway_fields, $mobile, $message, $test_call)
    {
        return self::process_sms($gateway_fields, $mobile, $message, $test_call);
    }

    public static function sendBulkSMS($gateway_fields, $messages, $test_call)
    {
        $username = $gateway_fields['username'];
        $password = $gateway_fields['password'];
        $sender = $gateway_fields['sender'];

    }

    public static function process_sms($gateway_fields, $mobile, $message, $test_call)
    {
        $username = $gateway_fields['username'];
        $password = $gateway_fields['password'];
        $sender = $gateway_fields['sender'];
        $mobile = str_replace('+', '', $mobile);

        $curl = curl_init();

        $post_params = array(
            'username' => $username,
            'password' => $password,
            'from' => $sender,
            'to' => $mobile,
            'text' => $message,
            'type' => 0
        );

        curl_setopt($curl, CURLOPT_URL, 'https://api.easysendsms.app/bulksms');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_params);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_errno($curl);
        curl_close($curl);


        $is_success = 200 <= $code && $code < 300;

        if ($test_call) return $result;

        return true;
    }
}
