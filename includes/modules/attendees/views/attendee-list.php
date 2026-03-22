<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1>
        <?php _e( 'Attendees', 'kueue-events-core' ); ?>
        <a href="<?php echo admin_url( 'admin.php?page=kq-attendees&action=add' ); ?>" class="page-title-action"><?php _e( 'Add New', 'kueue-events-core' ); ?></a>
    </h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-name"><?php _e( 'Name', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-email"><?php _e( 'Email', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-event"><?php _e( 'Event', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-type"><?php _e( 'Ticket Type', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-status"><?php _e( 'Status', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-source"><?php _e( 'Source', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-actions"><?php _e( 'Actions', 'kueue-events-core' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $attendees ) ) : ?>
                <tr><td colspan="7"><?php _e( 'No attendees found.', 'kueue-events-core' ); ?></td></tr>
            <?php else : ?>
                <?php foreach ( $attendees as $att ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( $att->first_name . ' ' . $att->last_name ); ?></strong></td>
                        <td><?php echo esc_html( $att->email ); ?></td>
                        <td><?php 
                            $event = get_post( $att->event_id );
                            echo $event ? esc_html( $event->post_title ) : '—';
                        ?></td>
                        <td><?php 
                            if ( $att->ticket_type_id ) {
                                $tt = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_by_id( $att->ticket_type_id );
                                echo $tt ? esc_html( $tt->name ) : '—';
                            } else {
                                echo '—';
                            }
                        ?></td>
                        <td><?php echo esc_html( ucfirst( $att->status ) ); ?></td>
                        <td><?php echo esc_html( ucfirst( $att->source ) ); ?></td>
                        <td>
                            <a href="<?php echo admin_url( 'admin.php?page=kq-attendees&action=edit&id=' . $att->id ); ?>"><?php _e( 'Edit', 'kueue-events-core' ); ?></a> |
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=kq-attendees&action=delete&id=' . $att->id ), 'kq_delete_attendee_' . $att->id ); ?>" style="color: red;" onclick="return confirm('<?php _e( 'Are you sure?', 'kueue-events-core' ); ?>')"><?php _e( 'Delete', 'kueue-events-core' ); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
