<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kq-admin-section">
    <div class="kq-admin-header">
        <h1><?php _e( 'Ticket Types', 'kueue-events-core' ); ?></h1>
        <div class="kq-admin-actions">
            <a href="<?php echo admin_url( 'admin.php?page=kq-ticket-types&action=add' ); ?>" class="kq-btn kq-btn-primary">
                <i class="fa fa-plus"></i> <?php _e( 'Add Ticket Type', 'kueue-events-core' ); ?>
            </a>
        </div>
    </div>

    <div class="kq-card" style="padding: 0; overflow: hidden;">
        <table class="wp-list-table widefat fixed striped kq-table" style="border:none; box-shadow:none;">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-name"><?php _e( 'Name', 'kueue-events-core' ); ?></th>
                    <th scope="col" class="manage-column column-event"><?php _e( 'Event', 'kueue-events-core' ); ?></th>
                    <th scope="col" class="manage-column column-price"><?php _e( 'Price', 'kueue-events-core' ); ?></th>
                    <th scope="col" class="manage-column column-stock"><?php _e( 'Inventory', 'kueue-events-core' ); ?></th>
                    <th scope="col" class="manage-column column-status"><?php _e( 'Status', 'kueue-events-core' ); ?></th>
                    <th scope="col" class="manage-column column-actions" style="text-align: right;"><?php _e( 'Actions', 'kueue-events-core' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $ticket_types ) ) : ?>
                    <tr><td colspan="6" style="text-align:center; padding: 40px; color: #888;"><?php _e( 'No ticket types created yet.', 'kueue-events-core' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $ticket_types as $tt ) : 
                        $event = get_post( $tt->event_id );
                    ?>
                        <tr>
                            <td>
                                <strong style="font-size: 15px; color: var(--kq-dark);"><?php echo esc_html( $tt->name ); ?></strong>
                                <div style="font-size: 11px; color: #888;">ID: #<?php echo $tt->id; ?> | WC: #<?php echo $tt->wc_product_id; ?></div>
                            </td>
                            <td>
                                <?php if ( $event ) : ?>
                                    <a href="<?php echo get_edit_post_link( $event->ID ); ?>" style="text-decoration:none; color: inherit;">
                                        <strong><?php echo esc_html( $event->post_title ); ?></strong>
                                    </a>
                                <?php else : ?>
                                    <span style="color: #ccc;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 800; color: var(--kq-primary);"><?php echo wc_price( $tt->price ); ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?php echo (int) $tt->stock_limit; ?></div>
                                <div style="font-size: 11px; color: #888;">Maximum Capacity</div>
                            </td>
                            <td>
                                <?php 
                                $status_bg = 'rgba(76, 209, 55, 0.1)';
                                $status_color = '#4cd137';
                                if ( $tt->status === 'inactive' ) { $status_bg = 'rgba(136, 136, 136, 0.1)'; $status_color = '#888'; }
                                ?>
                                <span style="background: <?php echo $status_bg; ?>; color: <?php echo $status_color; ?>; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                    <?php echo esc_html( $tt->status ); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?php echo admin_url( 'admin.php?page=kq-ticket-types&action=edit&id=' . $tt->id ); ?>" class="kq-btn kq-btn-outline" style="padding: 4px 12px; font-size: 12px;"><?php _e( 'Edit', 'kueue-events-core' ); ?></a>
                                <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=kq-ticket-types&action=delete&id=' . $tt->id ), 'kq_delete_tt_' . $tt->id ); ?>" 
                                   class="kq-btn" 
                                   style="padding: 4px 12px; font-size: 12px; color: #ff3131;" 
                                   onclick="return confirm('<?php _e( 'Are you sure?', 'kueue-events-core' ); ?>')"><?php _e( 'Delete', 'kueue-events-core' ); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
