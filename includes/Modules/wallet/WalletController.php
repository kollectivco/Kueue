<?php

namespace KueueEvents\Core\Modules\Wallet;

class WalletController {

    public function run() {
        // Admin
        if ( is_admin() ) {
            $admin = new WalletAdmin();
            $admin->run();
        }

        // Register WooCommerce Gateway
        add_filter( 'woocommerce_payment_gateways', [ $this, 'register_gateway' ] );

        // Add Refund to Wallet option in Admin order items
        add_action( 'woocommerce_order_item_add_action_buttons', [ $this, 'add_refund_to_wallet_button' ], 10, 1 );
    }

    /**
     * Register the Wallet Gateway in WooCommerce.
     */
    public function register_gateway( $gateways ) {
        $gateways[] = 'KueueEvents\Core\Modules\Wallet\WalletCheckoutGateway';
        return $gateways;
    }

    /**
     * Optional: Add hook for refund to wallet button.
     * This is a placeholder for future implementation if deep WC integration is needed.
     */
    public function add_refund_to_wallet_button( $order ) {
        // Implementation for custom refund UI in admin if needed.
    }
}
