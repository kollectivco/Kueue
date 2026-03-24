<?php

namespace KueueEvents\Core\Modules\Attendees;

class AttendeeAdmin {

    public function render_list() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $user_id = get_current_user_id();
        $is_admin = current_user_can( 'manage_options' );
        $organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( $user_id );
        $organizer_id = ! $is_admin && $organizer ? $organizer->id : null;

        if ( 'delete' === $action && isset( $_GET['id'] ) ) {
            check_admin_referer( 'kq_delete_attendee_' . $_GET['id'] );
            $this->handle_delete( $_GET['id'], $organizer_id );
            wp_redirect( admin_url( 'admin.php?page=kq-attendees' ) );
            exit;
        }

        if ( 'edit' === $action || 'add' === $action ) {
            $this->render_form( $action, $organizer_id );
            return;
        }

        $attendees = AttendeeRepository::get_all( $organizer_id );
        include_once KQ_PLUGIN_DIR . 'includes/Modules/Attendees/views/attendee-list.php';
    }

    private function render_form( $action, $organizer_id ) {
        $id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
        $attendee = null;

        if ( $id ) {
            $attendee = AttendeeRepository::get_by_id( $id );
            if ( $organizer_id && (int) $attendee->organizer_id !== (int) $organizer_id ) {
                wp_die( __( 'You do not have permission to edit this attendee.', 'kueue-events-core' ) );
            }
        }

        if ( isset( $_POST['kq_save_attendee'] ) ) {
            check_admin_referer( 'kq_save_attendee_nonce' );
            $this->handle_save( $id, $organizer_id );
            return;
        }

        // Fetch events for dropdown
        $events_args = [ 'post_type' => 'kq_event', 'posts_per_page' => -1 ];
        if ( $organizer_id ) {
            $events_args['meta_query'] = [
                [ 'key' => '_kq_organizer_id', 'value' => $organizer_id, 'compare' => '=' ]
            ];
        }
        $events = get_posts( $events_args );

        include_once KQ_PLUGIN_DIR . 'includes/Modules/Attendees/views/attendee-form.php';
    }

    private function handle_save( $id, $organizer_id ) {
        $event_id = (int) $_POST['event_id'];
        $event_org_id = \KueueEvents\Core\Modules\Events\EventRepository::get_event_organizer_id( $event_id );

        if ( $organizer_id && (int) $event_org_id !== (int) $organizer_id ) {
            wp_die( __( 'You cannot add attendees for an event you do not own.', 'kueue-events-core' ) );
        }

        $attendee_org_id = $organizer_id ?: $event_org_id;

        $data = [
            'event_id'       => $event_id,
            'organizer_id'   => $attendee_org_id,
            'ticket_type_id' => !empty($_POST['ticket_type_id']) ? (int) $_POST['ticket_type_id'] : null,
            'first_name'     => sanitize_text_field( $_POST['first_name'] ),
            'last_name'      => sanitize_text_field( $_POST['last_name'] ),
            'email'          => sanitize_email( $_POST['email'] ),
            'phone'          => sanitize_text_field( $_POST['phone'] ),
            'company'        => sanitize_text_field( $_POST['company'] ),
            'designation'    => sanitize_text_field( $_POST['designation'] ),
            'status'         => sanitize_text_field( $_POST['status'] ),
            'source'         => $id ? null : 'manual', // Only set on creation
        ];
        
        // Remove null values to avoid overwriting or if we're updating
        if ( $id ) {
            unset($data['source']);
        }

        AttendeeRepository::save( $data, $id );

        wp_redirect( admin_url( 'admin.php?page=kq-attendees' ) );
        exit;
    }

    private function handle_delete( $id, $organizer_id ) {
        if ( $organizer_id ) {
            $attendee = AttendeeRepository::get_by_id( $id );
            if ( $attendee && (int) $attendee->organizer_id === (int) $organizer_id ) {
                AttendeeRepository::delete( $id );
            }
        } else {
            AttendeeRepository::delete( $id );
        }
    }
}
