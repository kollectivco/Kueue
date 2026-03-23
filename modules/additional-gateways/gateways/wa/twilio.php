<?php

namespace SMSGateway\wa;

use Exception;
use Twilio\Rest\Client;

class Whatsapp_Twilio
{
    public static function sendWhatsapp(
        $gateway_fields,
        $mobile,
        $message,
        $test_call
    )
    {
        return self::process_whatsapp(
            $gateway_fields,
            $mobile,
            $message,
            $test_call
        );
    }

    public static function process_whatsapp(
        $gateway_fields,
        $mobile,
        $message,
        $test_call
    )
    {
        $whatsappno = $gateway_fields['whatsappnumber'];
        $sid = $gateway_fields['account_sid'];
        $token = $gateway_fields['auth_token'];



        $template_ids = array('message', 'content-sid');
        $params_values = array();


        if (defined('DIGITS_OTP')) {
            $otp = constant('DIGITS_OTP');
            $params_values = digits_get_wa_gateway_templates($message, $otp);
        }

        if (isset($gateway_fields['cid'])) {
            $template = $gateway_fields;
        } else {
            $whatsapp = wpn_parse_message_template($message, $template_ids);
            if (empty($whatsapp) || !is_array($whatsapp)) {
                untdovr_error_use_template_messages($test_call);
                return false;
            }

            $template = $whatsapp['template'];
            $template['cid'] = $template['content-sid'];
            $template['mssid'] = $gateway_fields['mssid'];
            $params_values = $whatsapp['params'];
        }


        $params = array();


        if (!empty($params_values)) {
            ksort($params_values);
            foreach ($params_values as $i => $params_value) {
                $params[$i] = strval($params_value);
            }
        }

        $messagetemplate = str_replace("\r\n", "\n", $message);

        try {
            $client = new Client($sid, $token);
            $result = $client->messages->create(
                "whatsapp:" . $mobile,
                array(
                    'from' => "whatsapp:" . $whatsappno,
                    "contentSid" => $template['cid'],
                    "contentVariables" => json_encode($params),
                    "messagingServiceSid" => $template['mssid']
                )
            );
        } catch (Exception $e) {
            if ($test_call) {
                return $e->getMessage();
            }

            return false;
        }

        if ($test_call) {
            return $result;
        }

        return true;
    }

}