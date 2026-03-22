<?php

namespace KueueEvents\Core\Core;

class AjaxHandler {

    public function run() {
        add_action( 'wp_ajax_kq_get_ticket_types', [ $this, 'get_ticket_types' ] );
        add_action( 'wp_ajax_kq_get_attendees', [ $this, 'get_attendees' ] );
    }

    public function get_ticket_types() {
        check_ajax_referer( 'kq_get_ticket_types_nonce', 'nonce' );
        $event_id = (int) $_POST['event_id'];
        
        $ticket_types = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_by_event_id( $event_id );
        
        wp_send_json_success( $ticket_types );
    }

    public function get_attendees() {
        check_ajax_referer( 'kq_get_attendees_nonce', 'nonce' );
        $event_id = (int) $_POST['event_id'];
        
        $attendees = \KueueEvents\Core\Modules\Attendees\AttendeeRepository::get_by_event_id( $event_id );
        
        wp_send_json_success( $attendees );
    }
}
