<?php

namespace KueueEvents\Core\Modules\Payments;

class WooCommerceService {

    public function run() {
        // Hook into WooCommerce order status changes
        add_action( 'woocommerce_order_status_completed', [ $this, 'process_tickets_on_completion' ], 10, 1 );
        add_action( 'woocommerce_order_status_processing', [ $this, 'process_tickets_on_completion' ], 10, 1 );
        
        // Handle cancellations/refunds
        add_action( 'woocommerce_order_status_cancelled', [ $this, 'handle_cancellation' ], 10, 1 );
        add_action( 'woocommerce_order_status_refunded', [ $this, 'handle_cancellation' ], 10, 1 );

        // Admin order details
        add_action( 'woocommerce_admin_order_data_after_order_details', [ $this, 'display_tickets_in_admin_order' ], 10, 1 );

        // Display tickets on Thank You page and View Order page
        add_action( 'woocommerce_thankyou', [ $this, 'display_order_tickets' ], 20 );
        add_action( 'woocommerce_view_order', [ $this, 'display_order_tickets' ], 20 );

        // Ticket links in emails
        add_action( 'woocommerce_email_after_order_table', [ $this, 'display_order_tickets_in_email' ], 20, 4 );
    }

    /**
     * Process tickets when WC order is paid/completed.
     */
    public function process_tickets_on_completion( $order_id ) {
        if ( get_post_meta( $order_id, '_kq_tickets_processed', true ) ) {
            return; // Prevent duplicate generation
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) return;

        $tickets_created = 0;

        foreach ( $order->get_items() as $item_id => $item ) {
            $product_id = $item->get_product_id();
            $ticket_type = $this->get_ticket_type_by_product( $product_id );

            if ( ! $ticket_type ) continue;

            $qty = $item->get_quantity();
            $attendee_data = $item->get_meta( '_kq_attendee_data' );

            for ( $i = 0; $i < $qty; $i++ ) {
                $meta = $attendee_data[$i] ?? [];
                
                // 1. Create Attendee
                $attendee_repo = new \KueueEvents\Core\Modules\Attendees\AttendeeRepository();
                $attendee_id = $attendee_repo->create( [
                    'event_id'       => $ticket_type->event_id,
                    'organizer_id'   => \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_organizer_id_by_event($ticket_type->event_id),
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
                if ( $attendee_id ) {
                    \KueueEvents\Core\Modules\Tickets\TicketGenerator::issue_ticket( $attendee_id, $ticket_type->id, [
                        'booking_slot_id' => $meta['booking_slot_id'] ?? null,
                        'seat_id'         => $meta['seat_id'] ?? null,
                    ] );
                    $tickets_created++;
                }
            }

            // 3. Record Commission (Once per line item or total order? Typically per item is better for logic)
            \KueueEvents\Core\Modules\Finance\CommissionService::record_sale( 
                $ticket_type->event_id, 
                \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_organizer_id_by_event($ticket_type->event_id), 
                $item->get_total(),
                $order->get_id()
            );
        }

        if ( $tickets_created > 0 ) {
            update_post_meta( $order_id, '_kq_tickets_processed', '1' );
        }
    }

    /**
     * Handle order cancellation/refund.
     */
    public function handle_cancellation( $order_id ) {
        global $wpdb;
        $tickets_table = $wpdb->prefix . 'kq_tickets';
        
        $tickets = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM $tickets_table WHERE order_id = %d", $order_id ) );
        
        foreach ( $tickets as $t ) {
            \KueueEvents\Core\Modules\Tickets\TicketRepository::cancel_ticket( $t->id );
        }
        
        // Potentially reverse commission as well if status is refunded
    }

    /**
     * Admin Order View: Display linked tickets
     */
    public function display_tickets_in_admin_order( $order ) {
        $order_id = $order->get_id();
        global $wpdb;
        $table = $wpdb->prefix . 'kq_tickets';
        $tickets = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE order_id = %d", $order_id ) );

        if ( empty( $tickets ) ) return;

        echo '<div style="clear:both;"></div>';
        echo '<h3>' . __( 'Kueue Event Tickets', 'kueue-events-core' ) . '</h3>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>' . __('Ticket #', 'kueue-events-core') . '</th><th>' . __('Attendee', 'kueue-events-core') . '</th><th>' . __('Status', 'kueue-events-core') . '</th><th>' . __('Actions', 'kueue-events-core') . '</th></tr></thead>';
        echo '<tbody>';
        foreach ( $tickets as $t ) {
            $view_url = admin_url( 'admin.php?page=kq-tickets&action=view&id=' . $t->id );
            echo '<tr>';
            echo '<td><code>' . esc_html( $t->ticket_number ) . '</code></td>';
            // Note: need to join or fetch attendee name if not in ticket table. 
            // In our schema, we have attendee_id.
            $attendee = $wpdb->get_row($wpdb->prepare("SELECT first_name, last_name FROM {$wpdb->prefix}kq_attendees WHERE id = %d", $t->attendee_id));
            echo '<td>' . esc_html( $attendee->first_name . ' ' . $attendee->last_name ) . '</td>';
            echo '<td><span class="kq-badge kq-badge-'.esc_attr($t->ticket_status).'">' . esc_html( ucfirst($t->ticket_status) ) . '</span></td>';
            echo '<td><a href="' . esc_url( $view_url ) . '" class="button button-small">' . __('View', 'kueue-events-core') . '</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    /**
     * Helper to get ticket type by product ID.
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
     * Display Tickets on Order Pages
     */
    public function display_order_tickets( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) return;

        global $wpdb;
        $table = $wpdb->prefix . 'kq_tickets';
        $tickets = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE order_id = %d", $order_id ) );

        if ( empty( $tickets ) ) return;

        ?>
        <section class="kq-order-tickets" style="margin-top: 40px; border-top: 2px solid #eee; padding-top: 30px;">
            <h2 style="margin-bottom: 20px;"><?php _e( 'Your Event Tickets', 'kueue-events-core' ); ?></h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <?php foreach ( $tickets as $t ) : 
                    $view_url = KQ_PLUGIN_URL . 'includes/Modules/Tickets/views/ticket-web-view.php?id=' . $t->id;
                    $attendee = $wpdb->get_row($wpdb->prepare("SELECT first_name, last_name FROM {$wpdb->prefix}kq_attendees WHERE id = %d", $t->attendee_id));
                ?>
                <div style="background: #fff; border: 1px solid #eee; padding: 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                    <div>
                        <strong style="display: block; font-size: 16px;"><?php echo esc_html( $attendee->first_name . ' ' . $attendee->last_name ); ?></strong>
                        <code style="font-size: 12px; color: #888;"><?php echo esc_html( $t->ticket_number ); ?></code>
                    </div>
                    <a href="<?php echo esc_url( $view_url ); ?>" target="_blank" class="kq-btn kq-btn-primary" style="padding: 6px 14px; font-size: 12px; background: #ff3131; color: #fff; text-decoration: none; border-radius: 6px;">
                        <?php _e( 'View Ticket', 'kueue-events-core' ); ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }

    /**
     * Display Tickets in Order Emails
     */
    public function display_order_tickets_in_email( $order, $sent_to_admin, $plain_text, $email ) {
        if ( $sent_to_admin ) return;

        global $wpdb;
        $table = $wpdb->prefix . 'kq_tickets';
        $tickets = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE order_id = %d", $order->get_id() ) );

        if ( empty( $tickets ) ) return;

        echo '<h2 style="color: #ff3131; font-family: Inter, sans-serif;">' . __( 'Your Event Tickets', 'kueue-events-core' ) . '</h2>';
        echo '<p>' . __( 'You can access your digital tickets via the links below:', 'kueue-events-core' ) . '</p>';
        
        echo '<ul>';
        foreach ( $tickets as $t ) {
            $view_url = KQ_PLUGIN_URL . 'includes/Modules/Tickets/views/ticket-web-view.php?id=' . $t->id;
            $attendee = $wpdb->get_row($wpdb->prepare("SELECT first_name, last_name FROM {$wpdb->prefix}kq_attendees WHERE id = %d", $t->attendee_id));
            echo '<li><strong>' . esc_html( $attendee->first_name . ' ' . $attendee->last_name ) . ':</strong> <a href="' . esc_url( $view_url ) . '">' . __( 'Download Ticket', 'kueue-events-core' ) . '</a></li>';
        }
        echo '</ul>';
    }
}
