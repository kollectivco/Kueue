<?php

namespace KueueEvents\Core\Modules\Attendees;

class CheckoutHandler {

    public function run() {
        // Render fields at checkout
        add_action( 'woocommerce_after_checkout_billing_form', [ $this, 'render_attendee_fields' ] );
        
        // Validate attendee data
        add_action( 'woocommerce_checkout_process', [ $this, 'validate_attendee_fields' ] );
        
        // Save attendee data to order item meta
        add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'save_attendee_data_to_order_item' ], 10, 4 );
    }

    /**
     * Render attendee fields for each ticket in cart.
     */
    public function render_attendee_fields() {
        $items = WC()->cart->get_cart();
        $has_tickets = false;

        foreach ( $items as $cart_item_key => $values ) {
            $product_id = $values['product_id'];
            $ticket_type = $this->get_ticket_type_by_product( $product_id );

            if ( $ticket_type ) {
                $has_tickets = true;
                $quantity = $values['quantity'];
                
                echo '<div class="kq-checkout-attendees">';
                echo '<h3>' . sprintf( __( 'Attendee Details: %s', 'kueue-events-core' ), $ticket_type->name ) . '</h3>';
                
                for ( $i = 1; $i <= $quantity; $i++ ) {
                    $saved_attendee = $values['_kq_attendee_data'][$i-1] ?? [];
                    // Fallback to billing for the first attendee if nothing saved
                    if ( $i === 1 && empty($saved_attendee) ) {
                        $saved_attendee = [
                            'first_name' => WC()->checkout->get_value('billing_first_name') ?: '',
                            'last_name'  => WC()->checkout->get_value('billing_last_name') ?: '',
                            'email'      => WC()->checkout->get_value('billing_email') ?: '',
                        ];
                    }

                    $full_name = !empty($saved_attendee['first_name']) ? trim($saved_attendee['first_name'] . ' ' . ($saved_attendee['last_name'] ?? '')) : '';

                    echo '<div class="kq-attendee-group">';
                    echo '<div class="kq-attendee-group-header">';
                    echo '<span class="kq-attendee-number">' . $i . '</span>';
                    echo '<h4 class="kq-attendee-group-title">' . sprintf( __( 'Attendee #%d', 'kueue-events-core' ), $i ) . '</h4>';
                    echo '</div>';
                    
                    echo '<div class="kq-attendee-fields-grid">';
                    
                    // Name
                    woocommerce_form_field( "kq_attendee_{$cart_item_key}_{$i}_name", [
                        'type'        => 'text',
                        'class'       => ['form-row-wide'],
                        'label'       => __( 'Full Name', 'kueue-events-core' ),
                        'placeholder' => __( 'e.g. John Doe', 'kueue-events-core' ),
                        'required'    => true,
                        'default'     => $full_name,
                    ]);
 
                    // Email
                    woocommerce_form_field( "kq_attendee_{$cart_item_key}_{$i}_email", [
                        'type'        => 'email',
                        'class'       => ['form-row-wide'],
                        'label'       => __( 'Email Address', 'kueue-events-core' ),
                        'placeholder' => __( 'e.g. john@example.com', 'kueue-events-core' ),
                        'required'    => true,
                        'default'     => $saved_attendee['email'] ?? '',
                    ]);
 
                    echo '</div>'; // End grid
                    echo '</div>'; // End group
                }
                echo '</div>'; // End kq-checkout-attendees
            }
        }
    }

    /**
     * Validate fields on checkout submit.
     */
    public function validate_attendee_fields() {
        $items = WC()->cart->get_cart();
        foreach ( $items as $cart_item_key => $values ) {
            $product_id = $values['product_id'];
            $ticket_type = $this->get_ticket_type_by_product( $product_id );

            if ( $ticket_type ) {
                $quantity = $values['quantity'];
                for ( $i = 1; $i <= $quantity; $i++ ) {
                    if ( empty( $_POST["kq_attendee_{$cart_item_key}_{$i}_name"] ) ) {
                        wc_add_notice( sprintf( __( 'Attendee #%d Name is required for %s.', 'kueue-events-core' ), $i, $ticket_type->name ), 'error' );
                    }
                    if ( empty( $_POST["kq_attendee_{$cart_item_key}_{$i}_email"] ) ) {
                        wc_add_notice( sprintf( __( 'Attendee #%d Email is required for %s.', 'kueue-events-core' ), $i, $ticket_type->name ), 'error' );
                    }
                }
            }
        }
    }

    /**
     * Save attendee data to order item meta.
     */
    public function save_attendee_data_to_order_item( $item, $cart_item_key, $values, $order ) {
        $product_id = $values['product_id'];
        $ticket_type = $this->get_ticket_type_by_product( $product_id );

        if ( $ticket_type ) {
            $attendee_data = [];
            $quantity = $values['quantity'];

            for ( $i = 1; $i <= $quantity; $i++ ) {
                $name = sanitize_text_field( $_POST["kq_attendee_{$cart_item_key}_{$i}_name"] );
                $email = sanitize_email( $_POST["kq_attendee_{$cart_item_key}_{$i}_email"] );

                // Split name
                $parts = explode( ' ', $name );
                $first_name = $parts[0] ?? '';
                $last_name = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : '';

                $attendee_data[] = [
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                    'email'      => $email,
                ];
            }

            $item->add_meta_data( '_kq_attendee_data', $attendee_data );
        }
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
}
