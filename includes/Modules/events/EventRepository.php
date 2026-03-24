<?php

namespace KueueEvents\Core\Modules\Events;

class EventRepository {

    /**
     * Get event organizer ID.
     */
    public static function get_event_organizer_id( $post_id ) {
        return (int) get_post_meta( $post_id, '_kq_organizer_id', true );
    }

    /**
     * Check if user owns event.
     */
    public static function check_ownership( $post_id, $user_id ) {
        if ( user_can( $user_id, 'manage_options' ) ) {
            return true;
        }

        $organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( $user_id );
        if ( ! $organizer ) {
            return false;
        }

        $event_org_id = self::get_event_organizer_id( $post_id );
        return (int) $organizer->id === $event_org_id;
    }

    /**
     * Get allowed gateway accounts for organizer/event.
     */
    public static function get_allowed_gateways( $post_id, $channel = 'sms' ) {
        $org_id = self::get_event_organizer_id( $post_id );
        // For now, return all enabled gateways for the channel, 
        // in advanced phases we might filter by organizer_id field in gateway_accounts table.
        return \KueueEvents\Core\Modules\Gateways\GatewayManager::get_accounts( $channel );
    }
}
