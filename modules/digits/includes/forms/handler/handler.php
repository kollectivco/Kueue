<?php


use DigitsFormHandler\Handler;

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__FILE__) . '/process.php';
require_once dirname(__FILE__) . '/functions.php';
require_once dirname(__FILE__) . '/DigitsFormHandler.php';
require_once dirname(__FILE__) . '/UserRegistration.php';
require_once dirname(__FILE__) . '/notice_exception.php';
require_once dirname(__FILE__) . '/DigitsSignUpException.php';
require_once dirname(__FILE__) . '/firebase_exception.php';
require_once dirname(__FILE__) . '/rate_limit_exception.php';
require_once dirname(__FILE__) . '/UserActionHandler.php';
require_once dirname(__FILE__) . '/user.php';
require_once dirname(__FILE__) . '/redirection.php';
require_once dirname(__FILE__) . '/flow.php';

add_action('wp_ajax_nopriv_digits_forms_ajax', 'digits_forms_ajax');
add_action('wp_ajax_digits_forms_ajax', 'digits_forms_ajax');
function digits_forms_ajax()
{
    if (empty($_REQUEST['type'])) {
        return;
    }
    if (is_user_logged_in()) {
        wp_send_json_error(array('reload' => true));
    }
    $csrf = $_REQUEST['digits_form'];

    if (!wp_verify_nonce($csrf, 'digits_login_form') || empty($_REQUEST['digits_form'])) {
        wp_send_json_error(array('message' => __('Error, Your session expired for security reasons. Please refresh the page and try again!', 'digits')));
        die();
    }
    $type = $_REQUEST['type'];

    digits_form_check_disable($type);


    $handler = Handler::instance();
    $handler->setType($type);
    $handler->setData($_REQUEST);
    $handler->process();
}

function digits_form_check_disable($type)
{
    $users_can_register = get_option('dig_enable_registration', 1);
    $digforgotpass = get_option('digforgotpass', 1);
    if ($users_can_register == 0 && $type == 'register') {
        wp_send_json_error(array('message' => __('Registration is disabled!', 'digits')));
        die();
    }

    if ($digforgotpass == 0 && $type == 'forgot') {
        wp_send_json_error(array('message' => __('Forgot Password is disabled!', 'digits')));
        die();
    }
}


function digits_verify_recaptcha()
{
    $recaptcha_secret_key = get_option('digits_recaptcha_secret_key', '');

    $recaptcha_response = '';
    if (isset($_REQUEST['g-recaptcha-response'])) {
        $recaptcha_response = sanitize_text_field(wp_unslash($_REQUEST['g-recaptcha-response']));
    }

    if (empty($recaptcha_secret_key) || empty($recaptcha_response)) {
        return false;
    }

    $data = array('secret' => $recaptcha_secret_key, 'response' => $recaptcha_response);

    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($data)
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents('https://www.recaptcha.net/recaptcha/api/siteverify', false, $context);
    $check = json_decode($result);

    if (!empty($check->success)) {
        return true;
    } else {
        return false;
    }
}

function digits_get_captcha_provider()
{
    $provider = get_option('digits_captcha_provider', 'recaptcha');
    $provider = strtolower(trim((string)$provider));
    if (empty($provider)) {
        $provider = 'recaptcha';
    }
    return $provider;
}

function digits_verify_turnstile()
{
    $turnstile_secret_key = get_option('digits_turnstile_secret_key', '');
    if (empty($turnstile_secret_key)) {
        return false;
    }

    $token = '';
    if (isset($_REQUEST['cf-turnstile-response'])) {
        $token = sanitize_text_field(wp_unslash($_REQUEST['cf-turnstile-response']));
    } elseif (isset($_REQUEST['g-recaptcha-response'])) {
        // Fallback: support custom response-field-name setups.
        $token = sanitize_text_field(wp_unslash($_REQUEST['g-recaptcha-response']));
    }

    if (empty($token)) {
        return false;
    }

    $data = array(
        'secret' => $turnstile_secret_key,
        'response' => $token,
    );

    if (!empty($_SERVER['REMOTE_ADDR'])) {
        $data['remoteip'] = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
    }

    $response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
        'timeout' => 10,
        'body' => $data,
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $check = json_decode($body);

    return !empty($check->success);
}

function digits_verify_hcaptcha()
{
    $hcaptcha_secret_key = get_option('digits_hcaptcha_secret_key', '');
    if (empty($hcaptcha_secret_key)) {
        return false;
    }

    $token = '';
    if (isset($_REQUEST['h-captcha-response'])) {
        $token = sanitize_text_field(wp_unslash($_REQUEST['h-captcha-response']));
    }

    if (empty($token)) {
        return false;
    }

    $data = array(
        'secret' => $hcaptcha_secret_key,
        'response' => $token,
    );

    if (!empty($_SERVER['REMOTE_ADDR'])) {
        $data['remoteip'] = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
    }

    $response = wp_remote_post('https://hcaptcha.com/siteverify', array(
        'timeout' => 10,
        'body' => $data,
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $check = json_decode($body);

    return !empty($check->success);
}

function digits_verify_captcha()
{

    $provider = digits_get_captcha_provider();
    if ($provider === 'turnstile') {
        return digits_verify_turnstile();
    }
    if ($provider === 'hcaptcha') {
        return digits_verify_hcaptcha();
    }
    return digits_verify_recaptcha();
}