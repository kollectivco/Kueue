<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
    <h1><?php _e( 'Finance & Commissions', 'kueue-events-core' ); ?></h1>
    
    <div class="kq-finance-stats" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px;">
        <div class="stat-box" style="background:#fff; padding:20px; border-radius:10px; border:1px solid #ddd;">
            <h3><?php _e( 'Total Net Earnings', 'kueue-events-core' ); ?></h3>
            <div class="value" style="font-size:2em; font-weight:bold; color:#28a745;">0.00 EGP</div>
        </div>
        <div class="stat-box" style="background:#fff; padding:20px; border-radius:10px; border:1px solid #ddd;">
            <h3><?php _e( 'Total Commissions', 'kueue-events-core' ); ?></h3>
            <div class="value" style="font-size:2em; font-weight:bold; color:#0073aa;">0.00 EGP</div>
        </div>
        <div class="stat-box" style="background:#fff; padding:20px; border-radius:10px; border:1px solid #ddd;">
            <h3><?php _e( 'Pending Payouts', 'kueue-events-core' ); ?></h3>
            <div class="value" style="font-size:2em; font-weight:bold; color:#ffc107;">0.00 EGP</div>
        </div>
    </div>

    <h2 style="margin-top:40px;"><?php _e( 'Recent Commissions', 'kueue-events-core' ); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'Order ID', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Organizer', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Gross', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Commission', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Net', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Status', 'kueue-events-core' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" style="text-align:center; padding:20px;"><?php _e( 'No finance data found.', 'kueue-events-core' ); ?></td>
            </tr>
        </tbody>
    </table>
</div>
