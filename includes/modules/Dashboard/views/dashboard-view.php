<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="kq-frontend-dashboard">
    <h2><?php printf( __( 'Organizer Dashboard: %s', 'kueue-events-core' ), esc_html( $organizer->organizer_name ) ); ?></h2>
    
    <div class="kq-stats-row" style="display:flex; gap:20px; margin-bottom:30px;">
        <div class="kq-stat-card" style="background:#fefefe; padding:20px; border:1px solid #ddd; border-radius:8px; flex:1;">
            <strong><?php _e( 'Revenue', 'kueue-events-core' ); ?></strong>
            <div style="font-size:1.8em;"><?php echo number_format($stats['total_revenue'] ?? 0, 2); ?> EGP</div>
        </div>
        <div class="kq-stat-card" style="background:#fefefe; padding:20px; border:1px solid #ddd; border-radius:8px; flex:1;">
            <strong><?php _e( 'Tickets Sold', 'kueue-events-core' ); ?></strong>
            <div style="font-size:1.8em;"><?php echo number_format($stats['total_tickets'] ?? 0); ?></div>
        </div>
        <div class="kq-stat-card" style="background:#fefefe; padding:20px; border:1px solid #ddd; border-radius:8px; flex:1;">
            <strong><?php _e( 'Payouts', 'kueue-events-core' ); ?></strong>
            <div style="font-size:1.8em;"><?php echo number_format($stats['net_revenue'] ?? 0, 2); ?> EGP</div>
            <a href="#" class="button"><?php _e( 'Request Payout', 'kueue-events-core' ); ?></a>
        </div>
    </div>

    <h3><?php _e( 'My Events', 'kueue-events-core' ); ?></h3>
    <table class="kq-dashboard-table" style="width:100%; border-collapse:collapse;">
        <thead>
            <tr style="background:#f4f4f4;">
                <th style="padding:10px; border-bottom:1px solid #ccc;"><?php _e( 'Event Name', 'kueue-events-core' ); ?></th>
                <th style="padding:10px; border-bottom:1px solid #ccc;"><?php _e( 'Status', 'kueue-events-core' ); ?></th>
                <th style="padding:10px; border-bottom:1px solid #ccc;"><?php _e( 'Sales', 'kueue-events-core' ); ?></th>
                <th style="padding:10px; border-bottom:1px solid #ccc;"><?php _e( 'Action', 'kueue-events-core' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $events as $event ) : ?>
                <tr>
                    <td style="padding:10px; border-bottom:1px solid #eee;"><?php echo esc_html($event->post_title); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #eee;"><?php echo get_post_meta($event->ID, '_kq_event_status', true); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #eee;"><?php echo rand(5, 50); ?></td>
                    <td style="padding:10px; border-bottom:1px solid #eee;"><a href="<?php echo get_permalink($event->ID); ?>"><?php _e( 'View Page', 'kueue-events-core' ); ?></a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
