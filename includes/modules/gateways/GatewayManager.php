<?php

namespace KueueEvents\Core\Modules\Gateways;

class GatewayManager {

    /**
     * Get list of accounts by channel.
     */
    public static function get_accounts( $channel = null ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_gateway_accounts';
        
        $query = "SELECT * FROM $table";
        if ( $channel ) {
            $query .= $wpdb->prepare( " WHERE channel = %s", $channel );
        }
        
        return $wpdb->get_results( $query );
    }

    /**
     * Get default account by channel.
     */
    public static function get_default_account( $channel ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_gateway_accounts';
        
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE channel = %s AND is_default = 1 LIMIT 1",
            $channel
        ) );
    }

    /**
     * Set account as default.
     */
    public static function set_default( $id, $channel ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_gateway_accounts';
        
        // Unset old defaults for this channel
        $wpdb->update( $table, [ 'is_default' => 0 ], [ 'channel' => $channel ] );
        
        // Set new default
        $wpdb->update( $table, [ 'is_default' => 1 ], [ 'id' => $id ] );
    }

    /**
     * Get instance of provider for given account.
     */
    public static function get_provider( $account_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'kq_gateway_accounts';
        
        $account = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $account_id ) );
        if ( ! $account ) return null;

        $config = json_decode( $account->config_json, true );
        $provider_class = self::get_provider_class( $account->provider );

        if ( class_exists( $provider_class ) ) {
            return new $provider_class( $config );
        }
        return null;
    }

    /**
     * List of available providers.
     */
    public static function get_available_providers() {
        return [
            'sms_misr' => [
                'name'    => 'SMSMisr',
                'channel' => 'sms',
                'class'   => '\\KueueEvents\\Core\\Modules\\Gateways\\SMS\\SMSMisrProvider'
            ],
            'whatsapp_cloud' => [
                'name'    => 'WhatsApp Cloud API',
                'channel' => 'whatsapp',
                'class'   => '\\KueueEvents\\Core\\Modules\\Gateways\\WhatsApp\\WhatsAppCloudAPIProvider'
            ]
        ];
    }

    /**
     * Helper to map provider slug to class.
     */
    private static function get_provider_class( $slug ) {
        $providers = self::get_available_providers();
        return isset( $providers[$slug] ) ? $providers[$slug]['class'] : '';
    }
}
