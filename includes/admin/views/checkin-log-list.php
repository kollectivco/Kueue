<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
    <h1><?php _e( 'Check-in Logs', 'kueue-events-core' ); ?></h1>

    <div class="tablenav top">
        <div class="alignleft actions">
            <a href="<?php echo home_url('/kq-scanner'); ?>" target="_blank" class="button button-primary"><?php _e( 'Open Web Scanner', 'kueue-events-core' ); ?></a>
        </div>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'Date', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Ticket', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Event', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Attendee', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Type', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Status', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Scanned By', 'kueue-events-core' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $logs ) ) : ?>
                <tr>
                    <td colspan="7"><?php _e( 'No check-in logs found.', 'kueue-events-core' ); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ( $logs as $log ) : 
                    $ticket = \KueueEvents\Core\Modules\Tickets\TicketRepository::get_by_id($log->ticket_id);
                    $event = get_post($log->event_id);
                    $user = get_userdata($log->scanned_by_user_id);
                    $attendee = $ticket ? \KueueEvents\Core\Modules\Attendees\AttendeeRepository::get_by_id($ticket->attendee_id) : null;
                ?>
                    <tr>
                        <td><?php echo esc_html( $log->created_at ); ?></td>
                        <td><?php echo $ticket ? esc_html( $ticket->ticket_number ) : '—'; ?></td>
                        <td><?php echo $event ? esc_html( $event->post_title ) : '—'; ?></td>
                        <td><?php echo $attendee ? esc_html( $attendee->first_name . ' ' . $attendee->last_name ) : '—'; ?></td>
                        <td>
                            <span class="badge" style="background: <?php echo $log->scan_type === 'checkin' ? '#4caf50' : '#2196f3'; ?>; color: #fff; padding: 2px 6px; border-radius: 4px;">
                                <?php echo esc_html( strtoupper( $log->scan_type ) ); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html( $log->result_status ); ?></td>
                        <td><?php echo $user ? esc_html( $user->display_name ) : 'Unknown'; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
