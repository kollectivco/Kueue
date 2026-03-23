<?php

namespace SMSGateway;

class YourBulkSMS
{
    public static function sendSMS($gateway_fields, $mobile, $message, $test_call)
    {
        return self::process_sms($gateway_fields, $mobile, $message, $test_call);
    }

    public static function process_sms($gateway_fields, $mobile, $message, $test_call)
    {

        $auth_key = $gateway_fields['auth_key'];
        $sender = $gateway_fields['sender'];

        $curl = curl_init();

        $params = array(
            'authkey' => $auth_key,
            'mobiles'     => str_replace('+', '', $mobile),
            'message'     => $message,
            'sender'      => $sender,
            'route'       => '2',
            'country'     => '0',
        );


        if(isset($gateway_fields['template_id'])){
            $params['DLT_TE_ID'] = $gateway_fields['dlt-template-id'];
        }else{
            $template_ids = array('message', 'template-id');
            $message_obj = wpn_parse_message_template($message, $template_ids);

            if(is_array($message_obj)){
                $template = $message_obj['template'];
                if(isset($template['template-id'])){
                    $params['DLT_TE_ID'] = $template['template-id'];
                    $params['message'] = $template['message'];

                }
            }
        }


        $params['message'] = urlencode($params['message']);




        $encoded_query = http_build_query($params);
        curl_setopt($curl, CURLOPT_URL, 'http://control.yourbulksms.com/api/sendhttp.php?' . $encoded_query);
        curl_setopt($curl, CURLOPT_POST, 1);
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