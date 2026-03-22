<?php

namespace KueueEvents\Core\Modules\Tickets;

class TicketAdmin {

    public function render_list() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $user_id = get_current_user_id();
        $is_admin = current_user_can( 'manage_options' );
        $organizer = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( $user_id );
        $organizer_id = ! $is_admin && $organizer ? $organizer->id : null;

        if ( 'delete' === $action && isset( $_GET['id'] ) ) {
            check_admin_referer( 'kq_delete_ticket_' . $_GET['id'] );
            $this->handle_delete( $_GET['id'], $organizer_id );
            wp_redirect( admin_url( 'admin.php?page=kq-tickets' ) );
            exit;
        }

        // Delivery Actions
        if ( in_array( $action, [ 'send', 'resend_email', 'resend_whatsapp', 'resend_sms' ] ) && isset( $_GET['id'] ) ) {
            check_admin_referer( 'kq_ticket_action_' . $_GET['id'] );
            $this->handle_delivery_action( $action, (int) $_GET['id'], $organizer_id );
            wp_redirect( admin_url( 'admin.php?page=kq-tickets&message=' . $action . '_success' ) );
            exit;
        }

        if ( 'issue' === $action ) {
            $this->render_issue_form( $organizer_id );
            return;
        }

        $tickets = TicketRepository::get_all( $organizer_id );
        include_once KQ_PLUGIN_DIR . 'includes/admin/views/ticket-list.php';
    }

    private function render_issue_form( $organizer_id ) {
        if ( isset( $_POST['kq_issue_manual_ticket'] ) ) {
            check_admin_referer( 'kq_issue_manual_ticket_nonce' );
            $this->handle_manual_issue( $organizer_id );
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

        include_once KQ_PLUGIN_DIR . 'includes/admin/views/ticket-issue-form.php';
    }

    private function handle_manual_issue( $organizer_id ) {
        $event_id = (int) $_POST['event_id'];
        $attendee_id = (int) $_POST['attendee_id'];
        $ticket_type_id = (int) $_POST['ticket_type_id'];

        // Verify event ownership for organizers
        if ( $organizer_id ) {
            $event_org_id = \KueueEvents\Core\Modules\Events\EventRepository::get_event_organizer_id( $event_id );
            if ( (int) $event_org_id !== (int) $organizer_id ) {
                wp_die( __( 'You cannot issue tickets for this event.', 'kueue-events-core' ) );
            }
        }

        $ticket_id = TicketGenerator::issue_ticket( $attendee_id, $ticket_type_id );

        if ( $ticket_id ) {
            // Trigger Automated Delivery
            \KueueEvents\Core\Modules\Delivery\DeliveryService::send_ticket( $ticket_id );
            
            wp_redirect( admin_url( 'admin.php?page=kq-tickets&message=issued' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=kq-tickets&message=failed' ) );
        }
        exit;
    }

    private function handle_delete( $id, $organizer_id ) {
        if ( $organizer_id ) {
            $ticket = TicketRepository::get_by_id( $id );
            if ( $ticket && (int) $ticket->organizer_id === (int) $organizer_id ) {
                TicketRepository::delete( $id );
            }
        } else {
            TicketRepository::delete( $id );
        }
    }

    private function handle_delivery_action( $action, $id, $organizer_id ) {
        $ticket = TicketRepository::get_by_id( $id );
        if ( ! $ticket ) return;

        // Security
        if ( $organizer_id && (int) $ticket->organizer_id !== (int) $organizer_id ) {
            wp_die( __( 'Access denied.', 'kueue-events-core' ) );
        }

        switch ( $action ) {
            case 'send':
                \KueueEvents\Core\Modules\Delivery\DeliveryService::send_ticket( $id, true );
                break;
            case 'resend_email':
                \KueueEvents\Core\Modules\Delivery\DeliveryService::send_email( $ticket );
                break;
            case 'resend_whatsapp':
                \KueueEvents\Core\Modules\Delivery\DeliveryService::send_whatsapp( $ticket );
                break;
            case 'resend_sms':
                \KueueEvents\Core\Modules\Delivery\DeliveryService::send_sms( $ticket );
                break;
        }
    }
}
