<?php

namespace KueueEvents\Core\Core;

class AjaxHandler {

    public function run() {
        add_action( 'wp_ajax_kq_get_ticket_types', [ $this, 'get_ticket_types' ] );
        add_action( 'wp_ajax_kq_get_attendees', [ $this, 'get_attendees' ] );
        add_action( 'wp_ajax_kq_add_to_cart', [ $this, 'add_to_cart' ] );
        add_action( 'wp_ajax_nopriv_kq_add_to_cart', [ $this, 'add_to_cart' ] );
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

    public function add_to_cart() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            wp_send_json_error( [ 'message' => __( 'WooCommerce not active.', 'kueue-events-core' ) ] );
        }

        $event_id = (int) $_POST['event_id'];
        $attendee_data = $_POST['att'] ?? []; // Array of attendee details

        $added_any = false;
        foreach ( $_POST as $key => $val ) {
            if ( strpos( $key, 'qty_' ) === 0 ) {
                $tt_id = (int) str_replace( 'qty_', '', $key );
                $qty = (int) $val;
                if ( $qty > 0 ) {
                    $tt = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_by_id( $tt_id );
                    if ( $tt && $tt->wc_product_id ) {
                        $meta = isset($attendee_data[$tt_id]) ? $attendee_data[$tt_id] : [];
                        WC()->cart->add_to_cart( $tt->wc_product_id, $qty, 0, [], [ '_kq_attendee_data' => $meta ] );
                        $added_any = true;
                    }
                }
            }
        }

        if ( $added_any ) {
            wp_send_json_success( [ 'redirect_url' => wc_get_checkout_url() ] );
        } else {
            wp_send_json_error( [ 'message' => __( 'No tickets selected.', 'kueue-events-core' ) ] );
        }
    }
}
