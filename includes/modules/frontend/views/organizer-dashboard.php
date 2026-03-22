<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="kq-organizer-dashboard">
    <style>
        .kq-dashboard-header { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #eee; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .kq-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .kq-stat-card { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #eee; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .kq-stat-card .label { color: #666; font-size: 0.9em; margin-bottom: 5px; display: block; }
        .kq-stat-card .value { font-size: 1.8em; font-weight: bold; color: #0073aa; }
        
        .kq-dashboard-section { background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #eee; margin-bottom: 25px; }
        .kq-data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .kq-data-table th, .kq-data-table td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
        .kq-data-table th { color: #888; font-weight: 600; font-size: 0.85em; text-transform: uppercase; }
        
        .kq-btn { background: #0073aa; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; display: inline-block; transition: background 0.2s; }
        .kq-btn:hover { background: #005a87; }
        .kq-btn-outline { background: transparent; border: 1px solid #0073aa; color: #0073aa; }
    </style>

    <div class="kq-dashboard-header">
        <div>
            <h1><?php printf( __( 'Welcome, %s', 'kueue-events-core' ), $organizer->name ); ?></h1>
            <p><?php _e( 'Overview of your events and earnings.', 'kueue-events-core' ); ?></p>
        </div>
        <a href="<?php echo esc_url( add_query_arg( 'action', 'new_event' ) ); ?>" class="kq-btn"><?php _e( '+ Create New Event', 'kueue-events-core' ); ?></a>
    </div>

    <div class="kq-stats-grid">
        <div class="kq-stat-card">
            <span class="label"><?php _e( 'Total Sales', 'kueue-events-core' ); ?></span>
            <span class="value"><?php echo number_format( $stats['total_revenue'] ?? 0, 2 ); ?> EGP</span>
        </div>
        <div class="kq-stat-card">
            <span class="label"><?php _e( 'Tickets Issued', 'kueue-events-core' ); ?></span>
            <span class="value"><?php echo (int) ( $stats['total_tickets'] ?? 0 ); ?></span>
        </div>
        <div class="kq-stat-card">
            <span class="label"><?php _e( 'Net Earnings', 'kueue-events-core' ); ?></span>
            <span class="value"><?php echo number_format( $stats['net_earnings'] ?? 0, 2 ); ?> EGP</span>
        </div>
    </div>

    <div class="kq-dashboard-section">
        <h3><?php _e( 'Recent Payout Requests', 'kueue-events-core' ); ?></h3>
        <?php if ( empty( $payouts ) ) : ?>
            <p><?php _e( 'No payout requests found.', 'kueue-events-core' ); ?></p>
        <?php else : ?>
            <table class="kq-data-table">
                <thead>
                    <tr>
                        <th><?php _e( 'Date', 'kueue-events-core' ); ?></th>
                        <th><?php _e( 'Amount', 'kueue-events-core' ); ?></th>
                        <th><?php _e( 'Status', 'kueue-events-core' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $payouts as $p ) : ?>
                        <tr>
                            <td><?php echo date( 'M d, Y', strtotime( $p->created_at ) ); ?></td>
                            <td><?php echo number_format( $p->amount, 2 ); ?> EGP</td>
                            <td><span class="status-badge <?php echo esc_attr( $p->status ); ?>"><?php echo ucfirst( $p->status ); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <div style="margin-top: 20px;">
            <a href="#" class="kq-btn kq-btn-outline"><?php _e( 'Request Payout', 'kueue-events-core' ); ?></a>
        </div>
    </div>
</div>
