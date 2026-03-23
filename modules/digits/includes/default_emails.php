<?php

use DigitsFormHandler\EmailHandler;

if (!defined('ABSPATH')) {
    exit;
}

add_filter('wp_new_user_notification_email', 'digits_new_user_notification_email', 10, 3);

function digits_new_user_notification_email($email, $user, $blogname)
{
    $header = 'Content-Type: text/html; charset=UTF-8';
    $headers = array();

    if (!empty($email['headers'])) {
        $headers = $email['headers'];
    }
    if (is_array($headers)) {
        $headers[] = $header;
    } else {
        $headers .= "," . $header;
    }

    $user_login = $user->user_login;
    $user_email = $user->user_email;
    $key = get_password_reset_key($user);


    if (is_wp_error($key)) {
        return false;
    }

    $url = network_site_url( 'wp-login.php?login=' . rawurlencode( $user->user_login ) . "&key=$key&action=rp", 'digits' );

    $args = array();
    $args["{{verify-link}}"] = $url;


    $email_handler = new EmailHandler("new_user_set_password");
    $email_handler->setUser($user);
    $email_handler->parse_placeholders($args);
    $body = $email_handler->getBody();
    $subject = $email_handler->getSubject();


    $email['headers'] = $headers;
    $email['subject'] = $subject;
    $email['message'] = $body;
    return $email;
}