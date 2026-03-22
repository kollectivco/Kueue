<?php

namespace KueueEvents\Core\Modules\Payments;

class WooCommerceService {

    public function run() {
        // Hook into WooCommerce order status changes
        add_action( 'woocommerce_order_status_completed', [ $this, 'process_tickets_on_completion' ], 10, 1 );
        add_action( 'woocommerce_order_status_cancelled', [ $this, 'handle_cancellation' ], 10, 1 );
        add_action( 'woocommerce_order_status_refunded', [ $this, 'handle_cancellation' ], 10, 1 );
    }

    /**
     * Process tickets when WC order is completed.
     */
    public function process_tickets_on_completion( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) return;

        foreach ( $order->get_items() as $item_id => $item ) {
            $product_id = $item->get_product_id();
            
            // Find matched ticket type
            $ticket_type = $this->get_ticket_type_by_product( $product_id );
            if ( ! $ticket_type ) continue;

            $qty = $item->get_quantity();
            $attendee_data = $item->get_meta( '_kq_attendee_data' );

            for ( $i = 0; $i < $qty; $i++ ) {
                $this->create_ticket_from_order_item( $order, $item, $ticket_type, $attendee_data[$i] ?? [] );
            }
        }
    }

    /**
     * Find ticket type linked to WC product.
     */
    private function get_ticket_type_by_product( $product_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_ticket_types';
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE wc_product_id = %d",
            $product_id
        ) );
    }

    /**
     * Create attendee and issue ticket based on WC item.
     */
    private function create_ticket_from_order_item( $order, $item, $ticket_type, $meta = [] ) {
        // 1. Create Attendee
        $attendee_repo = new \KueueEvents\Core\Modules\Attendees\AttendeeRepository();
        $attendee_id = $attendee_repo->create( [
            'event_id'       => $ticket_type->event_id,
            'organizer_id'   => \KueueEvents\Core\Modules\Organizers\OrganizerRepository::get_organizer_id_by_event($ticket_type->event_id),
            'ticket_type_id' => $ticket_type->id,
            'order_id'       => $order->get_id(),
            'order_item_id'  => $item->get_id(),
            'first_name'     => $meta['first_name'] ?? $order->get_billing_first_name(),
            'last_name'      => $meta['last_name'] ?? $order->get_billing_last_name(),
            'email'          => $meta['email'] ?? $order->get_billing_email(),
            'phone'          => $meta['phone'] ?? $order->get_billing_phone(),
            'status'         => 'confirmed',
            'source'         => 'woocommerce'
        ] );

        // 2. Issue Ticket
        \KueueEvents\Core\Modules\Tickets\TicketGenerator::issue_ticket( $attendee_id, $ticket_type->id, [
            'booking_slot_id' => $meta['booking_slot_id'] ?? null,
            'seat_id'         => $meta['seat_id'] ?? null,
        ] );

        // 3. Record Commission
        \KueueEvents\Core\Modules\Finance\CommissionService::record_sale( 
            $ticket_type->event_id, 
            \KueueEvents\Core\Modules\Organizers\OrganizerRepository::get_organizer_id_by_event($ticket_type->event_id), 
            $item->get_total(),
            $order->get_id()
        );
    }

    /**
     * Handle order cancellation/refund.
     */
    public function handle_cancellation( $order_id ) {
        global $wpdb;
        $tickets_table = $wpdb->prefix . 'kq_tickets';
        
        // Find all tickets for this order
        $tickets = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM $tickets_table WHERE order_id = %d", $order_id ) );
        
        foreach ( $tickets as $t ) {
            \KueueEvents\Core\Modules\Tickets\TicketRepository::cancel_ticket( $t->id );
        }
    }

    /**
     * Sync ticket type to WC product.
     */
    public static function sync_to_product( $ticket_type_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_ticket_types';
        $tt = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $ticket_type_id ) );
        if ( ! $tt ) return false;

        $post_id = $tt->wc_product_id;
        
        $post_data = [
            'post_title'   => $tt->name,
            'post_content' => $tt->description ?? '',
            'post_status'  => 'publish',
            'post_type'    => 'product',
        ];

        if ( $post_id && get_post( $post_id ) ) {
            $post_data['ID'] = $post_id;
            wp_update_post( $post_data );
        } else {
            $post_id = wp_insert_post( $post_data );
            $wpdb->update( $table, [ 'wc_product_id' => $post_id ], [ 'id' => $ticket_type_id ] );
        }

        // WC specific meta
        update_post_meta( $post_id, '_regular_price', $tt->price );
        update_post_meta( $post_id, '_price', $tt->price );
        update_post_meta( $post_id, '_virtual', 'yes' );
        update_post_meta( $post_id, '_downloadable', 'no' );
        update_post_meta( $post_id, '_manage_stock', 'yes' );
        update_post_meta( $post_id, '_stock', $tt->stock_quantity - $tt->sold_quantity );

        return $post_id;
    }
}
