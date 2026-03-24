<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kq-admin-section">
    <div class="kq-admin-header">
        <h1><?php _e( 'Issued Tickets', 'kueue-events-core' ); ?></h1>
        <div class="kq-admin-actions">
            <a href="<?php echo admin_url( 'admin.php?page=kq-pos' ); ?>" class="kq-btn kq-btn-primary">
                <i class="fa fa-ticket"></i> <?php _e( 'Issue Manually (POS)', 'kueue-events-core' ); ?>
            </a>
        </div>
    </div>

    <div class="kq-card" style="padding: 0; overflow: hidden;">
        <table class="wp-list-table widefat fixed striped kq-table" style="border:none; box-shadow:none;">
            <thead>
                <tr>
                    <th scope="col" style="width: 50px;">#</th>
                    <th scope="col"><?php _e( 'Ticket Number', 'kueue-events-core' ); ?></th>
                    <th scope="col"><?php _e( 'Event & Type', 'kueue-events-core' ); ?></th>
                    <th scope="col"><?php _e( 'Owner', 'kueue-events-core' ); ?></th>
                    <th scope="col"><?php _e( 'Status', 'kueue-events-core' ); ?></th>
                    <th scope="col" style="text-align: right;"><?php _e( 'Actions', 'kueue-events-core' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $tickets ) ) : ?>
                    <tr><td colspan="6" style="text-align:center; padding: 40px; color: #888;"><?php _e( 'No tickets found.', 'kueue-events-core' ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $tickets as $t ) : 
                        $event = get_post( $t->event_id );
                        $tt = \KueueEvents\Core\Modules\Tickets\TicketTypeRepository::get_by_id($t->ticket_type_id);
                    ?>
                        <tr>
                            <td>#<?php echo $t->id; ?></td>
                            <td>
                                <strong style="font-family: monospace; font-size: 14px; background: #f0f0f0; padding: 4px 8px; border-radius: 4px;"><?php echo esc_html( $t->ticket_number ); ?></strong>
                            </td>
                            <td>
                                <div><strong><?php echo $event ? esc_html( $event->post_title ) : 'Unknown Event'; ?></strong></div>
                                <div style="font-size: 11px; color: #888;"><?php echo $tt ? esc_html($tt->name) : 'Unknown Type'; ?></div>
                            </td>
                            <td>
                                <div><?php echo esc_html($t->attendee_first_name . ' ' . $t->attendee_last_name); ?></div>
                                <div style="font-size: 11px; color: #888;"><?php echo esc_html($t->attendee_email); ?></div>
                            </td>
                            <td>
                                <?php 
                                $status_bg = 'rgba(76, 209, 55, 0.1)';
                                $status_color = '#4cd137';
                                if ( $t->ticket_status === 'cancelled' ) { $status_bg = 'rgba(255, 49, 49, 0.1)'; $status_color = '#ff3131'; }
                                if ( $t->checkin_status === 'checked_in' ) { $status_bg = 'rgba(0, 115, 170, 0.1)'; $status_color = '#0073aa'; }
                                ?>
                                <span style="background: <?php echo $status_bg; ?>; color: <?php echo $status_color; ?>; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                                    <?php echo esc_html( $t->ticket_status ); ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?php echo KQ_PLUGIN_URL . 'includes/Modules/Tickets/views/ticket-web-view.php?id=' . $t->id; ?>" target="_blank" class="kq-btn kq-btn-outline" style="padding: 4px 12px; font-size: 12px;"><?php _e( 'View', 'kueue-events-core' ); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
