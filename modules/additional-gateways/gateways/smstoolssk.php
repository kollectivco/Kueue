<?php

namespace SMSGateway;


class SMSToolsSK
{
    // docs at: https://www.smstools.sk/downloads/SMSTOOLS-API-dokumentacia.pdf
    public static function sendSMS($gateway_fields, $mobile, $message, $test_call)
    {
        return self::process_sms($gateway_fields, $mobile, $message, $test_call);
    }

    public static function process_sms($gateway_fields, $mobile, $message, $test_call)
    {

        $api_key = $gateway_fields['api_key'];
        $sender = $gateway_fields['sender'];


        $data = [
            "auth" => [
                "apikey" => $api_key
            ],
            "data" => [
                "message" => $message,
                "sender" => ["text" => $sender],
                "recipients" => [
                    ["phonenr" => $mobile]
                ]
            ]
        ];

        $url = "https://api.smstools.sk/3/send_batch";

        $curl = curl_init();


        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json; charset=UTF-8"));

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_errno($curl);
        curl_close($curl);

        if ($test_call) return $result;

        if ($curl_error !== 0) {
            return false;
        }

        $is_success = 200 <= $code && $code < 300;

        return $is_success;
    }

}
