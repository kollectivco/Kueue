<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap" id="kq-reports-admin">
    <h1><?php _e( 'Event Insights & Analytics', 'kueue-events-core' ); ?></h1>
    
    <style>
        .reports-summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
        .stat-card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); }
        .stat-card h3 { margin: 0 0 10px; color: #666; font-size: 0.9em; text-transform: uppercase; }
        .stat-card .value { font-size: 2.2em; font-weight: bold; color: #0073aa; }
        
        .reports-main-content { margin-top: 30px; display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .content-card { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #ccd0d4; }
        
        .top-events-list { list-style: none; padding: 0; margin: 0; }
        .top-event-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; }
        .top-event-item:last-child { border-bottom: none; }
    </style>

    <div class="reports-summary-grid">
        <div class="stat-card">
            <h3><?php _e( 'Total Revenue', 'kueue-events-core' ); ?></h3>
            <div class="value"><?php echo kq_price( $stats->gross ?? 0 ); ?></div>
        </div>
        <div class="stat-card">
            <h3><?php _e( 'Tickets Sold', 'kueue-events-core' ); ?></h3>
            <div class="value"><?php 
                global $wpdb;
                echo $wpdb->get_var("SELECT COUNT(id) FROM {$wpdb->prefix}kq_tickets WHERE ticket_status = 'active'");
            ?></div>
        </div>
        <div class="stat-card">
            <h3><?php _e( 'Active Events', 'kueue-events-core' ); ?></h3>
            <div class="value"><?php 
                $active_events = wp_count_posts('kq_event')->publish;
                echo $active_events;
            ?></div>
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
                $top_events = \KueueEvents\Core\Modules\Reports\ReportsService::get_top_selling_events(5);
                if ( ! empty( $top_events ) ) : foreach ($top_events as $e) : 
                    $event_title = get_the_title( $e->event_id );
                ?>
                    <li class="top-event-item">
                        <span><?php echo esc_html($event_title); ?></span>
                        <strong><?php echo $e->total_sold; ?> <?php _e( 'sold', 'kueue-events-core' ); ?></strong>
                    </li>
                <?php endforeach; else : ?>
                    <p class="description"><?php _e( 'No sales data yet.', 'kueue-events-core' ); ?></p>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
