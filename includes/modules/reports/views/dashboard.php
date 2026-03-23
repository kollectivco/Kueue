<?php if ( ! defined( 'ABSPATH' ) ) exit; 
$stats = \KueueEvents\Core\Modules\Reports\ReportsService::get_global_summary();
$recent_events = get_posts([ 'post_type' => 'kq_event', 'numberposts' => 5 ]);
?>
<div class="kq-admin-section">
    <div class="kq-admin-header">
        <h1><?php _e( 'Kueue Events Performance', 'kueue-events-core' ); ?></h1>
        <div class="kq-admin-actions">
            <a href="<?php echo admin_url( 'post-new.php?post_type=kq_event' ); ?>" class="kq-btn kq-btn-primary">
                <i class="fa fa-plus"></i> <?php _e( 'New Event', 'kueue-events-core' ); ?>
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="kq-grid">
        <div class="kq-card">
            <span class="kq-stat-label"><?php _e( 'Gross Revenue', 'kueue-events-core' ); ?></span>
            <span class="kq-stat-value"><?php echo wc_price( $stats->gross ?? 0 ); ?></span>
            <div style="color: #4cd137; font-size: 13px; margin-top: 8px;">
                <i class="fa fa-arrow-up"></i> 12% vs last month
            </div>
        </div>
        <div class="kq-card">
            <span class="kq-stat-label"><?php _e( 'Net Income', 'kueue-events-core' ); ?></span>
            <span class="kq-stat-value"><?php echo wc_price( $stats->net ?? 0 ); ?></span>
             <div style="color: #4cd137; font-size: 13px; margin-top: 8px;">
                <i class="fa fa-arrow-up"></i> 8% vs last month
            </div>
        </div>
        <div class="kq-card">
            <span class="kq-stat-label"><?php _e( 'Total Commissions', 'kueue-events-core' ); ?></span>
            <span class="kq-stat-value"><?php echo wc_price( $stats->commission ?? 0 ); ?></span>
             <div style="color: #888; font-size: 13px; margin-top: 8px;">
                <i class="fa fa-info-circle"></i> Marketplace fee: 10%
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 40px;">
        <!-- Recent Events -->
        <div class="kq-card">
            <h3 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                <?php _e( 'Recent Events', 'kueue-events-core' ); ?>
            </h3>
            <table class="wp-list-table widefat fixed striped" style="border:none; box-shadow:none;">
                <thead>
                    <tr>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( $recent_events ) : foreach ( $recent_events as $event ) : 
                        $summary = \KueueEvents\Core\Modules\Reports\ReportsService::get_event_summary( $event->ID );
                    ?>
                    <tr>
                        <td><strong><a href="<?php echo get_edit_post_link( $event->ID ); ?>"><?php echo esc_html( $event->post_title ); ?></a></strong></td>
                        <td><?php echo get_post_meta( $event->ID, '_kq_event_start_date', true ); ?></td>
                        <td><?php echo $summary['active_tickets']; ?> / <?php echo $summary['total_tickets']; ?></td>
                        <td><?php echo wc_price( $summary['gross_revenue'] ); ?></td>
                    </tr>
                    <?php endforeach; else : ?>
                        <tr><td colspan="4">No events found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Quick Links -->
        <div class="kq-card" style="background: var(--kq-dark); color: #fff;">
            <h3 style="margin-top:0; color: #fff;"><?php _e( 'Kueue Hub', 'kueue-events-core' ); ?></h3>
            <p style="color: #888;">Quick access to core modules and system tools.</p>
            
            <ul style="list-style: none; padding: 0; margin-top: 20px;">
                <li style="margin-bottom: 12px;">
                    <a href="<?php echo admin_url( 'admin.php?page=kq-organizers' ); ?>" style="color: #fff; text-decoration: none; display: flex; align-items: center; gap: 10px;">
                        <i class="fa fa-users" style="color: var(--kq-primary);"></i> <?php _e( 'Manage Organizers', 'kueue-events-core' ); ?>
                    </a>
                </li>
                <li style="margin-bottom: 12px;">
                    <a href="<?php echo admin_url( 'admin.php?page=kq-tickets' ); ?>" style="color: #fff; text-decoration: none; display: flex; align-items: center; gap: 10px;">
                        <i class="fa fa-ticket" style="color: var(--kq-primary);"></i> <?php _e( 'Review All Tickets', 'kueue-events-core' ); ?>
                    </a>
                </li>
                <li style="margin-bottom: 12px;">
                    <a href="<?php echo admin_url( 'admin.php?page=kq-reports' ); ?>" style="color: #fff; text-decoration: none; display: flex; align-items: center; gap: 10px;">
                        <i class="fa fa-chart-line" style="color: var(--kq-primary);"></i> <?php _e( 'Advanced Analytics', 'kueue-events-core' ); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo admin_url( 'admin.php?page=kq-settings' ); ?>" style="color: #fff; text-decoration: none; display: flex; align-items: center; gap: 10px;">
                        <i class="fa fa-cog" style="color: var(--kq-primary);"></i> <?php _e( 'Settings', 'kueue-events-core' ); ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
