<?php

namespace KueueEvents\Core\Modules\Tickets;

class TemplateRenderer {

    protected $placeholders = [];

    /**
     * Set placeholders from ticket and event data.
     */
    public function set_ticket_data( $ticket ) {
        $event = get_post( $ticket->event_id );
        $attendee = \KueueEvents\Core\Modules\Attendees\AttendeeRepository::get_by_id( $ticket->attendee_id );
        $ticket_type = TicketTypeRepository::get_by_id( $ticket->ticket_type_id );

        $this->placeholders = [
            '{event_name}'    => $event ? esc_html( $event->post_title ) : '',
            '{attendee_name}' => $attendee ? esc_html( $attendee->first_name . ' ' . $attendee->last_name ) : '',
            '{ticket_type}'   => $ticket_type ? esc_html( $ticket_type->name ) : '',
            '{ticket_number}' => esc_html( $ticket->ticket_number ),
            '{event_date}'    => get_post_meta( $ticket->event_id, '_kq_start_date', true ),
            '{event_time}'    => get_post_meta( $ticket->event_id, '_kq_start_time', true ),
            '{venue_name}'    => get_post_meta( $ticket->event_id, '_kq_venue_name', true ),
            '{qr_code}'       => QRCodeGenerator::generate_data_uri( $ticket->secure_token ),
            '{google_wallet_url}' => (new GoogleWalletService())->get_save_url( $ticket ),
        ];
    }

    /**
     * Render the template.
     */
    public function render( $template_path ) {
        if ( ! file_exists( $template_path ) ) {
            return '';
        }

        ob_start();
        include $template_path;
        $content = ob_get_clean();

        foreach ( $this->placeholders as $placeholder => $value ) {
            $content = str_replace( $placeholder, $value, $content );
        }

        return $content;
    }
}
