<?php

if (!defined('ABSPATH')) {
    exit;
}


function digits_getEmailGateWayArray()
{
    $site_url = home_url();
    $site_url = str_replace("http://", "", $site_url);
    $site_url = str_replace("https://", "", $site_url);
    $default_from_email = 'no-reply@' . $site_url;

    $gateways = array(
        'wp_mail' => array(
            'value' => 2,
            'label' => 'WP Mail',
            'inputs' => array(
                'From Email' => array('text' => true, 'name' => 'from', 'default_value' => $default_from_email),
            ),
        ),
        'sendgrid' => array(
            'value' => 3,
            'label' => 'SendGrid',
            'inputs' => array(
                'API Key' => array('text' => true, 'name' => 'api_key'),
                'From Email' => array('text' => true, 'name' => 'from', 'default_value' => $default_from_email),
            ),
        ),
        'mailgun' => array(
            'value' => 4,
            'label' => 'Mailgun',
            'inputs' => array(
                'API Key' => array('text' => true, 'name' => 'api_key'),
                'Domain' => array('text' => true, 'name' => 'domain'),
                'From Email' => array('text' => true, 'name' => 'from', 'default_value' => $default_from_email),
            ),
        ),
    );

    $gateways = apply_filters('digits_email_gateways', $gateways);
    return $gateways;
}



