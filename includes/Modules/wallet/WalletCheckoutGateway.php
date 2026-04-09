<?php

namespace KueueEvents\Core\Modules\Wallet;

if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
    return;
}

class WalletCheckoutGateway extends \WC_Payment_Gateway {

    private $service;

    public function __construct() {
        $this->id                 = 'kq_wallet';
        $this->icon               = ''; // Could add an icon URL here
        $this->has_fields         = false;
        $this->method_title       = __( 'Kueue Wallet', 'kueue-events-core' );
        $this->method_description = __( 'Pay using your Kueue wallet balance.', 'kueue-events-core' );

        $this->init_form_fields();
        $this->init_settings();

        $this->title       = $this->get_option( 'title', __( 'Wallet Balance', 'kueue-events-core' ) );
        $this->description = $this->get_option( 'description', __( 'Pay with your account balance.', 'kueue-events-core' ) );

        $this->service = new WalletService();

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
    }

    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title'   => __( 'Enable/Disable', 'kueue-events-core' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Wallet Payment', 'kueue-events-core' ),
                'default' => 'yes',
            ],
            'title' => [
                'title'       => __( 'Title', 'kueue-events-core' ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'kueue-events-core' ),
                'default'     => __( 'Pay with Wallet', 'kueue-events-core' ),
                'desc_tip'    => true,
            ],
            'description' => [
                'title'       => __( 'Description', 'kueue-events-core' ),
                'type'        => 'textarea',
                'description' => __( 'This controls the description which the user sees during checkout.', 'kueue-events-core' ),
                'default'     => __( 'Use your available wallet balance to pay for this order.', 'kueue-events-core' ),
            ],
        ];
    }

    public function is_available() {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $user_id = get_current_user_id();
        $total = \WC()->cart ? \WC()->cart->get_total( 'edit' ) : 0;
        
        if ( ! $this->service->can_user_pay_with_wallet( $user_id, $total ) ) {
            return false;
        }

        return parent::is_available();
    }

    public function get_description() {
        $description = parent::get_description();
        if ( is_user_logged_in() ) {
            $balance = $this->service->get_balance( get_current_user_id() );
            $description .= '<br><strong>' . sprintf( __( 'Current Balance: %s', 'kueue-events-core' ), wc_price( $balance ) ) . '</strong>';
        }
        return $description;
    }

    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );
        $user_id = $order->get_user_id();

        if ( ! $user_id ) {
            wc_add_notice( __( 'Wallet payment requires a logged-in account.', 'kueue-events-core' ), 'error' );
            return;
        }

        $total = $order->get_total();

        // Atomic debit
        $success = $this->service->debit_wallet( 
            $user_id, 
            $total, 
            'purchase', 
            'order', 
            $order_id, 
            sprintf( __( 'Purchase of order #%s', 'kueue-events-core' ), $order_id ) 
        );

        if ( ! $success ) {
            wc_add_notice( __( 'Insufficient wallet balance or transaction error.', 'kueue-events-core' ), 'error' );
            return;
        }

        // Mark as paid
        $order->payment_complete();

        // Reduce stock and empty cart
        \WC()->cart->empty_cart();

        // Return thank you redirect
        return [
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order ),
        ];
    }
}
