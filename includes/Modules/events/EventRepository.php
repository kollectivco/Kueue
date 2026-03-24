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
     * Get all active events.
     */
    public static function get_all_active() {
        return get_posts( [
            'post_type'   => 'kq_event',
            'post_status' => 'publish',
            'numberposts' => -1,
            'meta_query'  => [
                [
                    'key'     => '_kq_event_status',
                    'value'   => 'active',
                    'compare' => '='
                ]
            ]
        ] );
    }
}
