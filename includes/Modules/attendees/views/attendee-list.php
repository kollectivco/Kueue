<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kq-admin-section">
    <div class="kq-admin-header">
        <h1><?php _e( 'Attendees Management', 'kueue-events-core' ); ?></h1>
    </div>

    <div class="kq-card" style="padding: 0; overflow: hidden;">
        <table class="wp-list-table widefat fixed striped kq-table" style="border:none; box-shadow:none;">
            <thead>
                <tr>
                    <th scope="col"><?php _e( 'Attendee Name', 'kueue-events-core' ); ?></th>
                    <th scope="col"><?php _e( 'Email', 'kueue-events-core' ); ?></th>
                    <th scope="col"><?php _e( 'Event & Ticket', 'kueue-events-core' ); ?></th>
                    <th scope="col"><?php _e( 'Status', 'kueue-events-core' ); ?></th>
                    <th scope="col" style="text-align: right;"><?php _e( 'Actions', 'kueue-events-core' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $attendees ) ) : ?>
                    <tr><td colspan="5" style="text-align:center; padding: 40px; color: #888;"><?php _e( 'No attendees found.', 'kueue-events-core' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $attendees as $att ) : 
                        $event = get_post( $att->event_id );
                    ?>
                        <tr>
                            <td>
                                <strong style="font-size: 15px; color: var(--kq-dark);"><?php echo esc_html( $att->first_name . ' ' . $att->last_name ); ?></strong>
                                <div style="font-size: 11px; color: #888;">Order ID: #<?php echo $att->order_id; ?></div>
                            </td>
                            <td><?php echo esc_html( $att->email ); ?></td>
                            <td>
                                <div><strong><?php echo $event ? esc_html( $event->post_title ) : 'Unknown Event'; ?></strong></div>
                                <div style="font-size: 11px; color: #888;">Ticket ID: #<?php echo $att->ticket_id; ?></div>
                            </td>
                            <td>
                                <?php 
                                $status_bg = 'rgba(76, 209, 55, 0.1)';
                                $status_color = '#4cd137';
                                if ( $att->status === 'cancelled' ) { $status_bg = 'rgba(255, 49, 49, 0.1)'; $status_color = '#ff3131'; }
                                if ( $att->status === 'checked_in' ) { $status_bg = 'rgba(0, 115, 170, 0.1)'; $status_color = '#0073aa'; }
                                ?>
                                <span style="background: <?php echo $status_bg; ?>; color: <?php echo $status_color; ?>; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                    <?php echo esc_html( $att->status ); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?php echo admin_url( 'admin.php?page=kq-attendees&action=edit&id=' . $att->id ); ?>" class="kq-btn kq-btn-outline" style="padding: 4px 12px; font-size: 12px;"><?php _e( 'Details', 'kueue-events-core' ); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
