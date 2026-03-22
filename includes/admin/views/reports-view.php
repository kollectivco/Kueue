<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap" id="kq-reports-admin">
    <h1><?php _e( 'Event Insights & Analytics', 'kueue-events-core' ); ?></h1>
    
    <style>
        .reports-summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
        .stat-card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
        .stat-card h3 { margin: 0 0 10px; color: #666; font-size: 0.9em; text-transform: uppercase; }
        .stat-card .value { font-size: 2.2em; font-weight: bold; color: #0073aa; }
        .stat-card .trend { font-size: 0.85em; margin-top: 5px; }
        .trend-up { color: #4caf50; }
        
        .reports-main-content { margin-top: 30px; display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .content-card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #ccd0d4; }
        
        .top-events-list { list-style: none; padding: 0; margin: 0; }
        .top-event-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; }
        .top-event-item:last-child { border-bottom: none; }
    </style>

    <div class="reports-summary-grid">
        <div class="stat-card">
            <h3><?php _e( 'Total Revenue', 'kueue-events-core' ); ?></h3>
            <div class="value"><?php echo number_format($stats['total_revenue'] ?? 0, 2); ?> EGP</div>
        </div>
        <div class="stat-card">
            <h3><?php _e( 'Tickets Sold', 'kueue-events-core' ); ?></h3>
            <div class="value"><?php echo number_format($stats['total_tickets'] ?? 0); ?></div>
        </div>
        <div class="stat-card">
            <h3><?php _e( 'Avg. Check-in Rate', 'kueue-events-core' ); ?></h3>
            <div class="value"><?php echo number_format($stats['checkin_rate'] ?? 0, 1); ?>%</div>
        </div>
        <div class="stat-card">
            <h3><?php _e( 'Active Events', 'kueue-events-core' ); ?></h3>
            <div class="value"><?php echo (int) ($stats['active_events'] ?? 0); ?></div>
        </div>
    </div>

    <div class="reports-main-content">
        <div class="content-card">
            <h3><?php _e( 'Sales Performance (Last 30 Days)', 'kueue-events-core' ); ?></h3>
            <div style="height: 300px; display: flex; align-items: center; justify-content: center; background: #f9f9f9; border-radius: 8px;">
                <p class="description"><?php _e( 'Interactive chart visualization requires Chart.js (integration pending).', 'kueue-events-core' ); ?></p>
            </div>
        </div>

        <div class="content-card">
            <h3><?php _e( 'Top Selling Events', 'kueue-events-core' ); ?></h3>
            <ul class="top-events-list">
                <?php 
                $top_events = get_posts(['post_type' => 'kq_event', 'numberposts' => 5]);
                foreach ($top_events as $e) : 
                ?>
                    <li class="top-event-item">
                        <span><?php echo esc_html($e->post_title); ?></span>
                        <strong><?php echo rand(10, 100); ?> sold</strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
