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

        $meta = get_post_custom( $post->ID );
        $get_meta = function( $key ) use ( $meta ) {
            return isset( $meta[$key][0] ) ? $meta[$key][0] : '';
        };

        $organizer_id = $get_meta( '_kq_organizer_id' );
        $event_status = $get_meta( '_kq_event_status' );
        $visibility = $get_meta( '_kq_visibility' );
        
        $start_date = $get_meta( '_kq_start_date' );
        $end_date = $get_meta( '_kq_end_date' );
        $start_time = $get_meta( '_kq_start_time' );
        $end_time = $get_meta( '_kq_end_time' );
        $timezone = $get_meta( '_kq_timezone' );

        $venue_name = $get_meta( '_kq_venue_name' );
        $venue_address = $get_meta( '_kq_venue_address' );
        $venue_city = $get_meta( '_kq_venue_city' );
        $venue_country = $get_meta( '_kq_venue_country' );

        $enable_sales = $get_meta( '_kq_enable_sales' );
        $max_tickets = $get_meta( '_kq_max_tickets_per_order' );

        $enable_email_delivery = $get_meta( '_kq_enable_email_delivery' );
        $enable_whatsapp_delivery = $get_meta( '_kq_enable_whatsapp_delivery' );
        $enable_sms_delivery = $get_meta( '_kq_enable_sms_delivery' );

        // Fetch organizers
        $organizers = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_all();
        
        // Allowed gateway accounts
        $sms_accounts = \KueueEvents\Core\Modules\Gateways\GatewayManager::get_accounts( 'sms' );
        $whatsapp_accounts = \KueueEvents\Core\Modules\Gateways\GatewayManager::get_accounts( 'whatsapp' );

        include_once KQ_PLUGIN_DIR . 'includes/Modules/Events/views/event-meta-box.php';
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
            'venue_name', 'venue_address', 'venue_city', 'venue_country',
            'enable_sales', 'sales_start_datetime', 'sales_end_datetime', 'max_tickets_per_order',
            'enable_email_delivery', 'enable_whatsapp_delivery', 'enable_sms_delivery',
            'whatsapp_gateway_account_id', 'sms_gateway_account_id'
        ];

        // Validation for active events
        if ( isset( $_POST['event_status'] ) && $_POST['event_status'] === 'active' ) {
            if ( empty( $_POST['start_date'] ) ) {
                // In a real plugin, we'd use translatable error messages or admin notices
                // For now, we revert status to draft if invalid
                $_POST['event_status'] = 'draft';
            }
        }

        // Validation for sales dates
        if ( !empty( $_POST['sales_start_datetime'] ) && !empty( $_POST['sales_end_datetime'] ) ) {
            if ( strtotime( $_POST['sales_end_datetime'] ) <= strtotime( $_POST['sales_start_datetime'] ) ) {
                // Invalid range; clear end date or handle error
                $_POST['sales_end_datetime'] = '';
            }
        }

        // Permissions check for organizer_id
        $current_user_organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( get_current_user_id() );
        
        foreach ( $fields as $field ) {
            $key = '_kq_' . $field;
            if ( isset( $_POST[$field] ) ) {
                $value = $_POST[$field];
                
                // Extra security for organizer_id
                if ( $field === 'organizer_id' ) {
                    if ( ! current_user_can( 'manage_options' ) ) {
                        // Regular organizer can only assign their own ID
                        if ( $current_user_organizer ) {
                            $value = $current_user_organizer->id;
                        } else {
                            // If they have the role but no record? Should not happen if well managed
                            continue; 
                        }
                    }
                }

                if ( is_array( $value ) ) {
                    update_post_meta( $post_id, $key, array_map( 'sanitize_text_field', $value ) );
                } else {
                    update_post_meta( $post_id, $key, sanitize_text_field( $value ) );
                }
            } else {
                // Checkboxes might be empty, so we unset or set to empty
                if ( in_array( $field, [ 'enable_sales', 'enable_email_delivery', 'enable_whatsapp_delivery', 'enable_sms_delivery' ] ) ) {
                   update_post_meta( $post_id, $key, '' );
                }
            }
        }
    }
}
