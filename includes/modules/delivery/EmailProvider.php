<?php

namespace KueueEvents\Core\Modules\Delivery;

class EmailProvider implements \KueueEvents\Core\Modules\Gateways\GatewayProviderInterface {

    public function send_message( $to, $message, $context = [] ) {
        $subject = isset( $context['subject'] ) ? $context['subject'] : __( 'Your Ticket', 'kueue-events-core' );
        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        
        $attachments = [];
        if ( isset( $context['attachment_path'] ) && file_exists( $context['attachment_path'] ) ) {
            $attachments[] = $context['attachment_path'];
        }

        return wp_mail( $to, $subject, $message, $headers, $attachments );
    }

    public function test_connection() {
        return true; // No special connection for wp_mail() usually
    }

    public function validate_config( $config ) {
        return true;
    }
}
