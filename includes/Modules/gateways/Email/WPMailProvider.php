<?php

namespace KueueEvents\Core\Modules\Gateways\Email;

use KueueEvents\Core\Modules\Gateways\GatewayProviderInterface;

class WPMailProvider implements GatewayProviderInterface {

    private $config;

    public function __construct( $config = [] ) {
        $this->config = $config;
    }

    /**
     * Send email via wp_mail.
     */
    public function send_message( $to, $message, $context = [] ) {
        $subject = $context['subject'] ?? __( 'Notification from Kueue Events', 'kueue-events-core' );
        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        
        $from_name = get_option( 'kq_email_from_name', get_bloginfo( 'name' ) );
        $from_email = get_option( 'kq_email_from_address', get_bloginfo( 'admin_email' ) );

        if ( $from_name && $from_email ) {
            $headers[] = "From: $from_name <$from_email>";
        }

        return wp_mail( $to, $subject, $message, $headers );
    }

    public function test_connection() {
        return true; // wp_mail doesn't have a connection to test
    }

    public function validate_config( $config ) {
        return true;
    }
}
