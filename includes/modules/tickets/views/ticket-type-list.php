<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1>
        <?php _e( 'Ticket Types', 'kueue-events-core' ); ?>
        <a href="<?php echo admin_url( 'admin.php?page=kq-ticket-types&action=add' ); ?>" class="page-title-action"><?php _e( 'Add New', 'kueue-events-core' ); ?></a>
    </h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-name"><?php _e( 'Ticket Type Name', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-event"><?php _e( 'Event', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-price"><?php _e( 'Price', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-stock"><?php _e( 'Stock', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-status"><?php _e( 'Status', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-actions"><?php _e( 'Actions', 'kueue-events-core' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $ticket_types ) ) : ?>
                <tr><td colspan="6"><?php _e( 'No ticket types found.', 'kueue-events-core' ); ?></td></tr>
            <?php else : ?>
                <?php foreach ( $ticket_types as $tt ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( $tt->name ); ?></strong></td>
                        <td><?php 
                            $event = get_post( $tt->event_id );
                            echo $event ? esc_html( $event->post_title ) : '—';
                        ?></td>
                        <td><?php 
                            echo esc_html($tt->price);
                            if ( $tt->sale_price ) {
                                echo ' <del style="color: grey;">' . esc_html($tt->price) . '</del> ' . esc_html($tt->sale_price);
                            }
                        ?></td>
                        <td><?php echo (int) $tt->sold_quantity . ' / ' . (int) $tt->stock_quantity; ?></td>
                        <td><?php echo esc_html( ucfirst( $tt->status ) ); ?></td>
                        <td>
                            <a href="<?php echo admin_url( 'admin.php?page=kq-ticket-types&action=edit&id=' . $tt->id ); ?>"><?php _e( 'Edit', 'kueue-events-core' ); ?></a> |
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=kq-ticket-types&action=delete&id=' . $tt->id ), 'kq_delete_ticket_type_' . $tt->id ); ?>" style="color: red;" onclick="return confirm('<?php _e( 'Are you sure?', 'kueue-events-core' ); ?>')"><?php _e( 'Delete', 'kueue-events-core' ); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
