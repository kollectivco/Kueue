<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
    <h1><?php _e( 'Delivery Logs', 'kueue-events-core' ); ?></h1>
    <p class="description"><?php _e( 'Showing the last 100 delivery attempts.', 'kueue-events-core' ); ?></p>

    <div class="kq-card" style="margin-top: 20px; padding: 0; overflow: hidden;">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 80px;"><?php _e( 'ID', 'kueue-events-core' ); ?></th>
                    <th style="width: 100px;"><?php _e( 'Channel', 'kueue-events-core' ); ?></th>
                    <th><?php _e( 'Recipient', 'kueue-events-core' ); ?></th>
                    <th><?php _e( 'Status', 'kueue-events-core' ); ?></th>
                    <th><?php _e( 'Response', 'kueue-events-core' ); ?></th>
                    <th style="width: 160px;"><?php _e( 'Time', 'kueue-events-core' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $logs ) ) : foreach ( $logs as $log ) : ?>
                <tr>
                    <td>#<?php echo esc_html( $log->id ); ?></td>
                    <td>
                        <span class="status-badge" style="text-transform: uppercase; font-size: 10px; font-weight: bold; background: #eee; padding: 2px 6px; border-radius: 4px;">
                            <?php echo esc_html( $log->channel ); ?>
                        </span>
                    </td>
                    <td><code><?php echo esc_html( $log->recipient ); ?></code></td>
                    <td>
                        <?php 
                        $status_color = '#888';
                        if ( $log->status === 'success' || $log->status === 'delivered' ) $status_color = '#4cd137';
                        if ( $log->status === 'failed' || $log->status === 'error' ) $status_color = '#ff3131';
                        ?>
                        <span style="color: <?php echo $status_color; ?>; font-weight: bold;">
                            <?php echo strtoupper( esc_html( $log->status ) ); ?>
                        </span>
                    </td>
                    <td>
                        <div style="max-height: 50px; overflow-y: auto; font-size: 11px; color: #666;">
                            <?php echo esc_html( $log->response_data ); ?>
                        </div>
                    </td>
                    <td><?php echo date_i18n( get_option('date_format') . ' ' . get_option('time_format'), strtotime( $log->created_at ) ); ?></td>
                </tr>
                <?php endforeach; else : ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 40px; color: #888;">
                        <?php _e( 'No delivery logs found.', 'kueue-events-core' ); ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
