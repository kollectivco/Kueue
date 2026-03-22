<?php

namespace KueueEvents\Core\Modules\Delivery;

class DeliveryService {

    /**
     * Send entire ticket bundle (all enabled channels).
     */
    public static function send_ticket( $ticket_id, $force = false ) {
        $ticket = \KueueEvents\Core\Modules\Tickets\TicketRepository::get_by_id( $ticket_id );
        if ( ! $ticket || $ticket->ticket_status !== 'active' ) {
            return false;
        }

        // Check delivery status to prevent double sending
        if ( ! $force && $ticket->delivery_status === 'sent' ) {
            return false; // Already sent
        }

        $event_id = $ticket->event_id;
        $enable_emailFull = get_post_meta( $event_id, '_kq_enable_email_delivery', true );
        $enable_whatsappFull = get_post_meta( $event_id, '_kq_enable_whatsapp_delivery', true );
        $enable_smsFull = get_post_meta( $event_id, '_kq_enable_sms_delivery', true );

        $enable_email = !empty($enable_emailFull);
        $enable_whatsapp = !empty($enable_whatsappFull);
        $enable_sms = !empty($enable_smsFull);

        $responses = [];

        // 1) Email
        if ( $enable_email ) {
            $responses['email'] = self::send_email( $ticket );
        }

        // 2) WhatsApp
        if ( $enable_whatsapp ) {
            $responses['whatsapp'] = self::send_whatsapp( $ticket );
        }

        // 3) SMS
        if ( $enable_sms ) {
            $responses['sms'] = self::send_sms( $ticket );
        }

        // Update overall delivery status based on results
        $all_success = true;
        foreach ( $responses as $res ) {
            if ( ! $res ) $all_success = false;
        }

        if ( $all_success && !empty($responses) ) {
            self::update_ticket_status( $ticket_id, 'sent' );
        }

        return $responses;
    }

    /**
     * Send Email.
     */
    public static function send_email( $ticket ) {
        $attendee = \KueueEvents\Core\Modules\Attendees\AttendeeRepository::get_by_id( $ticket->attendee_id );
        if ( ! $attendee || ! $attendee->email ) return false;

        $event = get_post( $ticket->event_id );
        $renderer = new \KueueEvents\Core\Modules\Tickets\TemplateRenderer();
        $renderer->set_ticket_data( $ticket );
        
        $template_path = KQ_PLUGIN_DIR . 'includes/Admin/views/ticket-email-template.php';
        if ( ! file_exists( $template_path ) ) {
            $template_path = KQ_PLUGIN_DIR . 'includes/Admin/views/ticket-web-view.php'; // Fallback
        }

        $html = $renderer->render( $template_path );
        $ticket_url = home_url( '/kq-ticket/' . $ticket->secure_token );
        
        // Add ticket URL manually if needed in some cases
        $message = str_replace( '{ticket_link}', $ticket_url, $html );

        $context = [
            'subject' => sprintf( __( 'Your Ticket for %s', 'kueue-events-core' ), $event->post_title ),
        ];

        // Queue it
        return DeliveryManager::add_to_queue( 'email', 0, [
            'to'      => $attendee->email,
            'message' => $message,
            'context' => $context
        ] );
    }

    /**
     * Send WhatsApp.
     */
    public static function send_whatsapp( $ticket ) {
        $attendee = \KueueEvents\Core\Modules\Attendees\AttendeeRepository::get_by_id( $ticket->attendee_id );
        if ( ! $attendee || ! $attendee->phone ) return false;

        $event_id = $ticket->event_id;
        $event = get_post($event_id);
        $gateway_id = get_post_meta( $event_id, '_kq_whatsapp_gateway_id', true );

        if ( ! $gateway_id ) {
            // Get default account if none set
            $default_account = \KueueEvents\Core\Modules\Gateways\GatewayManager::get_default_account( 'whatsapp' );
            $gateway_id = $default_account ? $default_account->id : null;
        }

        if ( ! $gateway_id ) return false;

        $ticket_url = home_url( '/kq-ticket/' . $ticket->secure_token );
        $message = sprintf(
            __( "Hello %s,\nYour ticket for %s is confirmed!\n\nDate: %s\nTicket #: %s\nView Ticket: %s", 'kueue-events-core' ),
            $attendee->first_name,
            $event->post_title,
            get_post_meta($event_id, '_kq_start_date', true),
            $ticket->ticket_number,
            $ticket_url
        );

        return DeliveryManager::add_to_queue( 'whatsapp', $gateway_id, [
            'to'      => $attendee->phone,
            'message' => $message,
        ] );
    }

    /**
     * Send SMS.
     */
    public static function send_sms( $ticket ) {
        $attendee = \KueueEvents\Core\Modules\Attendees\AttendeeRepository::get_by_id( $ticket->attendee_id );
        if ( ! $attendee || ! $attendee->phone ) return false;

        $event_id = $ticket->event_id;
        $event = get_post($event_id);
        $gateway_id = get_post_meta( $event_id, '_kq_sms_gateway_id', true );

        if ( ! $gateway_id ) {
            $default_account = \KueueEvents\Core\Modules\Gateways\GatewayManager::get_default_account( 'sms' );
            $gateway_id = $default_account ? $default_account->id : null;
        }

        if ( ! $gateway_id ) return false;

        $ticket_url = home_url( '/kq-ticket/' . $ticket->secure_token );
        $message = sprintf(
            __( "Your ticket for %s: %s", 'kueue-events-core' ),
            $event->post_title,
            $ticket_url
        );

        return DeliveryManager::add_to_queue( 'sms', $gateway_id, [
            'to'      => $attendee->phone,
            'message' => $message,
        ] );
    }

    /**
     * Update overall ticket delivery status.
     */
    private static function update_ticket_status( $ticket_id, $status ) {
        \KueueEvents\Core\Modules\Tickets\TicketRepository::save( [ 'delivery_status' => $status ], $ticket_id );
    }
}
