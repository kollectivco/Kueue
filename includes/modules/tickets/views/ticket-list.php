<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1>
        <?php _e( 'Tickets', 'kueue-events-core' ); ?>
        <a href="<?php echo admin_url( 'admin.php?page=kq-tickets&action=issue' ); ?>" class="page-title-action"><?php _e( 'Issue Ticket', 'kueue-events-core' ); ?></a>
    </h1>
    
    <?php if ( isset( $_GET['message'] ) ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo ( $_GET['message'] === 'issued' ) ? __( 'Ticket issued successfully.', 'kueue-events-core' ) : __( 'Failed to issue ticket.', 'kueue-events-core' ); ?></p>
        </div>
    <?php endif; ?>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-number"><?php _e( 'Ticket Number', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-event"><?php _e( 'Event', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-attendee"><?php _e( 'Attendee', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-type"><?php _e( 'Ticket Type', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-status"><?php _e( 'Status', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-checkin"><?php _e( 'Check-in', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-delivery"><?php _e( 'Delivery', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-issued"><?php _e( 'Issued At', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-actions"><?php _e( 'Actions', 'kueue-events-core' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $tickets ) ) : ?>
                <tr><td colspan="9"><?php _e( 'No tickets found.', 'kueue-events-core' ); ?></td></tr>
            <?php else : ?>
                <?php foreach ( $tickets as $ticket ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( $ticket->ticket_number ); ?></strong></td>
                        <td><?php 
                            $event = get_post( $ticket->event_id );
                            echo $event ? esc_html( $event->post_title ) : '—';
                        ?></td>
                        <td><?php 
                            $att = \KueueEvents\Core\Modules\Attendees\AttendeeRepository::get_by_id( $ticket->attendee_id );
                            echo $att ? esc_html( $att->first_name . ' ' . $att->last_name ) : '—';
                        ?></td>
                        <td><?php 
                            $tt = TicketTypeRepository::get_by_id( $ticket->ticket_type_id );
                            echo $tt ? esc_html( $tt->name ) : '—';
                        ?></td>
                        <td><?php echo esc_html( ucfirst( $ticket->ticket_status ) ); ?></td>
                        <td><?php echo esc_html( ucfirst( str_replace('_', ' ', $ticket->checkin_status ) ) ); ?></td>
                        <td><?php echo esc_html( ucfirst( str_replace('_', ' ', $ticket->delivery_status ) ) ); ?></td>
                        <td><?php echo esc_html( $ticket->issued_at ); ?></td>
                        <td>
                            <a href="<?php echo home_url( '/kq-ticket/' . $ticket->secure_token ); ?>" target="_blank"><?php _e( 'View', 'kueue-events-core' ); ?></a> |
                            <a href="<?php echo home_url( '/kq-ticket/' . $ticket->secure_token . '/pdf' ); ?>"><?php _e( 'PDF', 'kueue-events-core' ); ?></a> |
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=kq-tickets&action=send&id=' . $ticket->id ), 'kq_ticket_action_' . $ticket->id ); ?>"><?php _e( 'Send', 'kueue-events-core' ); ?></a> |
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=kq-tickets&action=resend_email&id=' . $ticket->id ), 'kq_ticket_action_' . $ticket->id ); ?>"><?php _e( 'Email', 'kueue-events-core' ); ?></a> |
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=kq-tickets&action=resend_whatsapp&id=' . $ticket->id ), 'kq_ticket_action_' . $ticket->id ); ?>"><?php _e( 'WA', 'kueue-events-core' ); ?></a> |
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=kq-tickets&action=resend_sms&id=' . $ticket->id ), 'kq_ticket_action_' . $ticket->id ); ?>"><?php _e( 'SMS', 'kueue-events-core' ); ?></a> |
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=kq-tickets&action=delete&id=' . $ticket->id ), 'kq_delete_ticket_' . $ticket->id ); ?>" style="color: red;" onclick="return confirm('<?php _e( 'Are you sure?', 'kueue-events-core' ); ?>')"><?php _e( 'Delete', 'kueue-events-core' ); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
