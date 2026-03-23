<?php

namespace SMSGateway;


class Bird
{
    // docs at: https://docs.bird.com/api/channels-api/supported-channels/programmable-sms/sending-sms-messages#sms-messages
    public static function sendSMS($gateway_fields, $mobile, $message, $test_call)
    {
        return self::process_sms($gateway_fields, $mobile, $message, $test_call);
    }

    public static function process_sms($gateway_fields, $mobile, $message, $test_call)
    {

        $accesskey = $gateway_fields['accesskey'];

        $channel_id = $gateway_fields['channel_id'];
        $workspace_id = $gateway_fields['workspace_id'];



       /* if (isset($gateway_fields['template_id'])) {
            $params['tempid'] = $gateway_fields['template_id'];
        } else {
            $template_ids = array('message', 'template-id');
            $message_obj = wpn_parse_message_template($message, $template_ids);

            if (is_array($message_obj)) {
                $template = $message_obj['template'];
                if (isset($template['template-id'])) {
                    $params['tempid'] = $template['template-id'];
                    $params['message'] = $template['message'];

                }
            }
        }*/


        $data = [
            "receiver" => [
                "contacts" => [
                    [
                        "identifierKey" => "phonenumber",
                        "identifierValue" => $mobile
                    ]
                ]
            ],
            "body" => [
                "type" => "text",
                "text" => [
                    "text" => $message
                ]
            ]
        ];

        $url = "https://api.bird.com/workspaces/$workspace_id/channels/$channel_id/messages";

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: AccessKey ' . $accesskey
        ));

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
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
