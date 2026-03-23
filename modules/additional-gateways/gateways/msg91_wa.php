<?php

namespace SMSGateway;

class MSG91_WhatsApp
{

    public static function sendWhatsapp($gateway_fields, $mobile, $message, $test_call)
    {
        return self::process_whatsapp($gateway_fields, $mobile, $message, $test_call);
    }

    public static function process_whatsapp($gateway_fields, $mobile, $message, $test_call)
    {

        $auth_key = $gateway_fields['auth_key'];
        $from = $gateway_fields['from'];


        $template_ids = array('language', 'template-name','namespace');
        $params_values = array();

        $mobile = str_replace('+', '', $mobile);


        if (defined('DIGITS_OTP')) {
            $otp = constant('DIGITS_OTP');
            $params_values[1] = $otp;
        }

        if (isset($gateway_fields['template-name'])) {
            $template = $gateway_fields;
            $namespace = $gateway_fields['namespace'];
        } else {
            $whatsapp = wpn_parse_message_template($message, $template_ids);

            if (empty($whatsapp) || !is_array($whatsapp)) {
                untdovr_error_use_template_messages($test_call);
                return false;
            }

            $template = $whatsapp['template'];
            $params_values = $whatsapp['params'];
            $namespace = $template['namespace'];
        }

        $params = array();

        if (!empty($params_values)) {
            ksort($params_values);
            foreach ($params_values as $i => $params_value) {
                $key = $i;
                $params['body_' . $key] = array('type' => 'text', 'value' => strval($params_value));
            }
        }

        if (defined('DIGITS_OTP') && !empty($otp)) {
            $params["button_1"] = array(
                "subtype" => "url",
                "type" => "text",
                "value" => strval($otp)
            );
        }


        $template_name = $template['template-name'];

        $message_template = [
            "name" => $template_name,
            "language" => [
                "code" => $template['language'],
                "policy" => "deterministic"
            ],
            "to_and_components" => [
                [
                    "to" => [$mobile],
                    "components" => $params
                ]
            ]
        ];
        if(!empty($namespace)){
            $message_template['namespace'] = $namespace;
        }

        $payload = array(
            "messaging_product" => "whatsapp",
            "type" => "template",
            "template" => $message_template
        );

        $data = array(
            "integrated_number" => $from,
            "content_type" => "template",
            "payload" => $payload
        );

        $url = 'https://api.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'accept: application/json',
            "authkey: " . $auth_key,
            'content-type: application/json'
        ));

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");

        $answer = curl_exec($curl);

        if ($test_call) {
            return $answer;
        }

        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if (empty($answer)) {
            return false;
        }

        return true;

    }

}
