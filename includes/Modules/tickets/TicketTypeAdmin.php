<?php

namespace KueueEvents\Core\Modules\Tickets;

class TicketTypeAdmin {

    public function render_list() {
        $action = isset( $_GET['action'] ) ? $_GET['action'] : 'list';
        $organizer_id = ! current_user_can( 'manage_options' ) ? \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( get_current_user_id() )->id : null;

        if ( 'delete' === $action && isset( $_GET['id'] ) ) {
            check_admin_referer( 'kq_delete_ticket_type_' . $_GET['id'] );
            $this->handle_delete( $_GET['id'], $organizer_id );
            wp_redirect( admin_url( 'admin.php?page=kq-ticket-types' ) );
            exit;
        }

        if ( 'edit' === $action || 'add' === $action ) {
            $this->render_form( $action, $organizer_id );
            return;
        }

        $ticket_types = TicketTypeRepository::get_all( $organizer_id );
        include_once KQ_PLUGIN_DIR . 'includes/Modules/Tickets/views/ticket-type-list.php';
    }

    private function render_form( $action, $organizer_id ) {
        $id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
        $ticket_type = null;

        if ( $id ) {
            $ticket_type = TicketTypeRepository::get_by_id( $id );
            // Verify ownership if organizer
            if ( $organizer_id ) {
                $event_org_id = \KueueEvents\Core\Modules\Events\EventRepository::get_event_organizer_id( $ticket_type->event_id );
                if ( (int) $event_org_id !== (int) $organizer_id ) {
                    wp_die( __( 'You do not have permission to edit this ticket type.', 'kueue-events-core' ) );
                }
            }
        }

        if ( isset( $_POST['kq_save_ticket_type'] ) ) {
            check_admin_referer( 'kq_save_ticket_type_nonce' );
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

        include_once KQ_PLUGIN_DIR . 'includes/Modules/Tickets/views/ticket-type-form.php';
    }

    private function handle_save( $id, $organizer_id ) {
        $event_id = (int) $_POST['event_id'];
        
        // Verify event ownership for organizers
        if ( $organizer_id ) {
            $event_org_id = \KueueEvents\Core\Modules\Events\EventRepository::get_event_organizer_id( $event_id );
            if ( (int) $event_org_id !== (int) $organizer_id ) {
                wp_die( __( 'You cannot create ticket types for this event.', 'kueue-events-core' ) );
            }
        }

        $price = (float) $_POST['price'];
        $sale_price = !empty($_POST['sale_price']) ? (float) $_POST['sale_price'] : null;

        if ( $sale_price !== null && $sale_price > $price ) {
            $sale_price = $price; // Clamp
        }

        $data = [
            'event_id'       => $event_id,
            'name'           => sanitize_text_field( $_POST['name'] ),
            'slug'           => sanitize_title( $_POST['name'] ),
            'description'    => sanitize_textarea_field( $_POST['description'] ),
            'price'          => $price,
            'sale_price'     => $sale_price,
            'stock_quantity' => (int) $_POST['stock_quantity'],
            'min_per_order'  => (int) $_POST['min_per_order'],
            'max_per_order'  => (int) $_POST['max_per_order'],
            'status'         => sanitize_text_field( $_POST['status'] ),
            'sort_order'     => (int) $_POST['sort_order'],
        ];

        TicketTypeRepository::save( $data, $id );

        wp_redirect( admin_url( 'admin.php?page=kq-ticket-types' ) );
        exit;
    }

    private function handle_delete( $id, $organizer_id ) {
        if ( $organizer_id ) {
            $ticket_type = TicketTypeRepository::get_by_id( $id );
            if ( $ticket_type ) {
                $event_org_id = \KueueEvents\Core\Modules\Events\EventRepository::get_event_organizer_id( $ticket_type->event_id );
                if ( (int) $event_org_id === (int) $organizer_id ) {
                    TicketTypeRepository::delete( $id );
                }
            }
        } else {
            TicketTypeRepository::delete( $id );
        }
    }
}
