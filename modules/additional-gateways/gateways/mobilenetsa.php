<?php

namespace SMSGateway;

class MobilenetSa
{

    public static function sendSMS($gateway_fields, $recipient, $message, $test_call)
    {
        return self::apiMobile($gateway_fields, $recipient, $message, $test_call);
    }

    public static function apiMobile($gateway_fields, $mobile, $message, $test_call)
    {


        $token = $gateway_fields['username'];
        $sender = $gateway_fields['sender'];

        $mobile = str_replace("+", "", $mobile);

        $array_data = array(
            'number' => $mobile,
            'senderName' => $sender,
            'sendAtOption' => 'now',
            'messageBody' => $message,
            'allow_duplicate' => true
        );


        $curl = curl_init('https://app.mobile.net.sa/api/v1/send');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Authorization: Bearer ' . $token
        ));

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $array_data);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        $results = json_decode($response, true);


        if (curl_errno($curl)) {
            return false;
        }

        curl_close($curl);

        if ($test_call) {
            return $results;
        } else {
            return true;
        }

    }

}
