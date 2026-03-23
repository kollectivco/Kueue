<?php

namespace SMSGateway\wa;

/**
 * Bird WhatsApp gateway – sends template messages via Bird Channels API.
 * Docs: https://docs.bird.com/api/channels-api/supported-channels/programmable-whatsapp/sending-whatsapp-messages
 */
class Whatsapp_Bird
{
    public static function sendWhatsapp($gateway_fields, $mobile, $message, $test_call)
    {
        return self::process_whatsapp($gateway_fields, $mobile, $message, $test_call);
    }

    public static function process_whatsapp($gateway_fields, $mobile, $message, $test_call)
    {
        $accesskey   = $gateway_fields['accesskey'];
        $channel_id  = $gateway_fields['channel_id'];
        $workspace_id = $gateway_fields['workspace_id'];

        $template_ids  = array('template-name', 'namespace', 'language');
        $params_values = array();
        $otp           = null;

        if (defined('DIGITS_OTP')) {
            $otp           = constant('DIGITS_OTP');
            $params_values = digits_get_wa_gateway_templates($message, $otp);
        }

        if (isset($gateway_fields['template-name'])) {
            $template = $gateway_fields;
        } else {
            $whatsapp = wpn_parse_message_template($message, $template_ids);
            if (empty($whatsapp) || !is_array($whatsapp)) {
                untdovr_error_use_template_messages($test_call);
                return false;
            }
            $template      = $whatsapp['template'];
            $params_values = $whatsapp['params'];
        }

        $variables = array();
        if (!empty($params_values)) {
            ksort($params_values);
            $i = 1;
            foreach ($params_values as $params_value) {
                $variables[(string) $i] = (string) $params_value;
                $i++;
            }
        }


        if ($otp !== null && $otp !== '') {
            $variables['otp'] = (string) $otp;
        }

        $template_payload = array(
            'locale'  => isset($template['language']) ? $template['language'] : 'en',
            'version' => 'latest',
        );

        if (!empty($gateway_fields['project_id'])) {
            $template_payload['projectId'] = $gateway_fields['project_id'];
        } else {
            $template_payload['name'] = $template['template-name'];
        }

        if (!empty($variables)) {
            $template_payload['variables'] = $variables;
        }

        $data = array(
            'receiver' => array(
                'contacts' => array(
                    array(
                        'identifierKey'   => 'phonenumber',
                        'identifierValue' => $mobile,
                    ),
                ),
            ),
            'template' => $template_payload,
        );

        $url = "https://api.bird.com/workspaces/{$workspace_id}/channels/{$channel_id}/messages";

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: AccessKey ' . $accesskey,
            ),
        ));

        $result    = curl_exec($curl);
        $code      = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_errno($curl);
        curl_close($curl);

        if ($test_call) {
            return $result;
        }

        if ($curl_error !== 0) {
            return false;
        }

        $is_success = 200 <= $code && $code < 300;

        return $is_success;
    }
}
