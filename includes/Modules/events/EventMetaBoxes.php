<?php

namespace KueueEvents\Core\Modules\Events;

class EventMetaBoxes {

    public function run() {
        add_action( 'add_meta_boxes', [ $this, 'add_events_meta_box' ] );
        add_action( 'save_post_kq_event', [ $this, 'save_events_meta' ], 10, 2 );
    }

    public function add_events_meta_box() {
        add_meta_box(
            'kq_event_settings',
            __( 'Event Settings', 'kueue-events-core' ),
            [ $this, 'render_events_meta_box' ],
            'kq_event',
            'normal',
            'high'
        );
    }

    public function render_events_meta_box( $post ) {
        wp_nonce_field( 'kq_save_event_meta', 'kq_event_meta_nonce' );

        $post_id = $post->ID;
        
        // Fetch All Data (Ensures no undefined variables in view)
        $data = [
            'event_type'        => get_post_meta( $post_id, '_kq_event_type', true ) ?: 'simple_event',
            'organizer_id'      => get_post_meta( $post_id, '_kq_organizer_id', true ),
            'event_status'      => get_post_meta( $post_id, '_kq_event_status', true ) ?: 'draft',
            'visibility'        => get_post_meta( $post_id, '_kq_visibility', true ) ?: 'public',
            
            'start_date'        => get_post_meta( $post_id, '_kq_start_date', true ),
            'end_date'          => get_post_meta( $post_id, '_kq_end_date', true ),
            'start_time'        => get_post_meta( $post_id, '_kq_start_time', true ),
            'end_time'          => get_post_meta( $post_id, '_kq_end_time', true ),
            'timezone'          => get_post_meta( $post_id, '_kq_timezone', true ) ?: 'UTC',
            
            'venue_id'          => get_post_meta( $post_id, '_kq_venue_id', true ),
            'venue_name'        => get_post_meta( $post_id, '_kq_venue_name', true ),
            'venue_address'     => get_post_meta( $post_id, '_kq_venue_address', true ),
            'venue_city'        => get_post_meta( $post_id, '_kq_venue_city', true ),
            'venue_country'     => get_post_meta( $post_id, '_kq_venue_country', true ),
            
            'event_logo_id'     => get_post_meta( $post_id, '_kq_event_logo_id', true ),
            'cover_image_id'    => get_post_meta( $post_id, '_kq_cover_image_id', true ),
            'accent_color'      => get_post_meta( $post_id, '_kq_accent_color', true ) ?: '#ff3131',
            
            'enable_sales'      => get_post_meta( $post_id, '_kq_enable_sales', true ),
            'sales_start'       => get_post_meta( $post_id, '_kq_sales_start_datetime', true ),
            'sales_end'         => get_post_meta( $post_id, '_kq_sales_end_datetime', true ),
            'max_tickets'       => get_post_meta( $post_id, '_kq_max_tickets_per_order', true ) ?: 10,
            
            'enable_email'      => get_post_meta( $post_id, '_kq_enable_email_delivery', true ),
            'enable_whatsapp'   => get_post_meta( $post_id, '_kq_enable_whatsapp_delivery', true ),
            'enable_sms'        => get_post_meta( $post_id, '_kq_enable_sms_delivery', true ),
            'whatsapp_acc_id'   => get_post_meta( $post_id, '_kq_whatsapp_gateway_account_id', true ),
            'sms_acc_id'        => get_post_meta( $post_id, '_kq_sms_gateway_account_id', true ),
            
            'enable_bookings'   => get_post_meta( $post_id, '_kq_enable_bookings', true ),
            'enable_seating'    => get_post_meta( $post_id, '_kq_enable_seating', true ),
            'booking_mode'      => get_post_meta( $post_id, '_kq_booking_mode', true ) ?: 'slots',
            'seating_map_id'    => get_post_meta( $post_id, '_kq_seating_map_id', true ),
            'enable_wallets'    => get_post_meta( $post_id, '_kq_enable_wallets', true ),
            'allow_reentry'     => get_post_meta( $post_id, '_kq_allow_reentry', true ),
        ];

        // Fetch Global Data
        $organizers = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_all() ?: [];
        $sms_accounts = \KueueEvents\Core\Modules\Gateways\GatewayManager::get_accounts( 'sms' ) ?: [];
        $whatsapp_accounts = \KueueEvents\Core\Modules\Gateways\GatewayManager::get_accounts( 'whatsapp' ) ?: [];
        $seating_maps = \KueueEvents\Core\Modules\Seating\SeatingRepository::get_all_maps() ?: [];
        $venues = get_posts([ 'post_type' => 'kq_venue', 'numberposts' => -1 ]) ?: [];

        extract( $data );

        include KQ_PLUGIN_DIR . 'includes/Modules/Events/views/event-meta-box.php';
    }

    public function save_events_meta( $post_id, $post ) {
        if ( ! isset( $_POST['kq_event_meta_nonce'] ) || ! wp_verify_nonce( $_POST['kq_event_meta_nonce'], 'kq_save_event_meta' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $fields = [
            'event_type', 'organizer_id', 'event_status', 'visibility',
            'start_date', 'end_date', 'start_time', 'end_time', 'timezone',
            'venue_id', 'venue_name', 'venue_address', 'venue_city', 'venue_country',
            'event_logo_id', 'cover_image_id', 'accent_color',
            'enable_sales', 'sales_start_datetime', 'sales_end_datetime', 'max_tickets_per_order',
            'enable_email_delivery', 'enable_whatsapp_delivery', 'enable_sms_delivery',
            'whatsapp_gateway_account_id', 'sms_gateway_account_id',
            'enable_bookings', 'enable_seating', 'booking_mode', 'seating_map_id',
            'enable_wallets', 'allow_reentry'
        ];

        // Validation for active events
        if ( isset( $_POST['event_status'] ) && $_POST['event_status'] === 'active' ) {
            if ( empty( $_POST['start_date'] ) ) {
                $_POST['event_status'] = 'draft';
            }
        }

        // Permissions check for organizer_id
        $current_user_organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( get_current_user_id() );
        
        foreach ( $fields as $field ) {
            $key = '_kq_' . $field;
            
            if ( isset( $_POST[$field] ) ) {
                $value = $_POST[$field];
                
                // Security for organizer_id
                if ( $field === 'organizer_id' ) {
                    if ( ! current_user_can( 'manage_options' ) ) {
                        if ( $current_user_organizer ) {
                            $value = $current_user_organizer->id;
                        } else {
                            continue; 
                        }
                    }
                }

                if ( is_array( $value ) ) {
                    update_post_meta( $post_id, $key, array_map( 'sanitize_text_field', $value ) );
                } elseif ( $field === 'accent_color' ) {
                    update_post_meta( $post_id, $key, sanitize_hex_color( $value ) );
                } else {
                    update_post_meta( $post_id, $key, sanitize_text_field( $value ) );
                }
            } else {
                // Handle checkboxes
                $checkboxes = [
                    'enable_sales', 'enable_email_delivery', 'enable_whatsapp_delivery', 
                    'enable_sms_delivery', 'enable_bookings', 'enable_seating', 
                    'enable_wallets', 'allow_reentry'
                ];
                if ( in_array( $field, $checkboxes ) ) {
                   update_post_meta( $post_id, $key, '' );
                }
            }
        }
    }
}
