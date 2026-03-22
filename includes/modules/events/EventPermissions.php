<?php

namespace KueueEvents\Core\Modules\Events;

class EventPermissions {

    public function run() {
        add_filter( 'pre_get_posts', [ $this, 'filter_events_by_organizer' ] );
        add_action( 'load-post.php', [ $this, 'restrict_event_editing' ] );
        add_action( 'load-post-new.php', [ $this, 'restrict_event_creation' ] );
    }

    /**
     * Filter event list in admin for organizer users.
     */
    public function filter_events_by_organizer( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'kq_event' ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            $user_id = get_current_user_id();
            $organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( $user_id );

            if ( $organizer ) {
                $query->set( 'meta_query', [
                    [
                        'key'     => '_kq_organizer_id',
                        'value'   => $organizer->id,
                        'compare' => '='
                    ]
                ] );
            } else {
                // If they have the role but no organizer record, show nothing
                $query->set( 'post__in', [0] );
            }
        }
    }

    /**
     * Restrict editing if the user doesn't own the event.
     */
    public function restrict_event_editing() {
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }

        $post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
        if ( ! $post_id ) {
            return;
        }

        if ( get_post_type( $post_id ) !== 'kq_event' ) {
            return;
        }

        if ( ! $this->check_event_ownership( $post_id ) ) {
            wp_die( __( 'You do not have permission to edit this event.', 'kueue-events-core' ) );
        }
    }

    /**
     * Restrict creation if the user is not a valid organizer.
     */
    public function restrict_event_creation() {
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }

        $screen = get_current_screen();
        if ( $screen && $screen->post_type === 'kq_event' ) {
            $organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( get_current_user_id() );
            if ( ! $organizer ) {
                wp_die( __( 'You must be linked to an organizer account to create events.', 'kueue-events-core' ) );
            }
        }
    }

    /**
     * Helper to check ownership.
     */
    public function check_event_ownership( $post_id ) {
        $organizer_id = get_post_meta( $post_id, '_kq_organizer_id', true );
        $user_organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( get_current_user_id() );

        if ( $user_organizer && (int) $user_organizer->id === (int) $organizer_id ) {
            return true;
        }

        return false;
    }
}
