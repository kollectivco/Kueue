<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kq-dashboard-container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    
    <!-- Welcome Section -->
    <div style="margin-bottom: 40px;">
        <h2 style="font-size: 32px; font-weight: 800; margin-bottom: 8px;">Welcome, <?php echo esc_html( $organizer->organizer_name ?? 'Organizer' ); ?></h2>
        <p style="color: #888;">Manage your events, sales, and payouts in one central hub.</p>
    </div>

    <!-- Bento Stats Grid -->
    <div class="kq-grid">
        <div class="kq-card">
            <span class="kq-stat-label">Gross Revenue</span>
            <span class="kq-stat-value"><?php echo function_exists('wc_price') ? wc_price( $stats->gross ?? 0 ) : ($stats->gross ?? 0); ?></span>
        </div>
        <div class="kq-card">
            <span class="kq-stat-label">Commission Fee</span>
            <span class="kq-stat-value" style="color: #ff3131;">-<?php echo function_exists('wc_price') ? wc_price( $stats->commission ?? 0 ) : ($stats->commission ?? 0); ?></span>
        </div>
        <div class="kq-card">
            <span class="kq-stat-label">Net Earnings</span>
            <span class="kq-stat-value" style="color: #4cd137;"><?php echo function_exists('wc_price') ? wc_price( $stats->net ?? 0 ) : ($stats->net ?? 0); ?></span>
        </div>
    </div>

    <!-- Dashboard Main Content -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 40px;">
        
        <!-- My Events Section -->
        <div class="kq-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 style="margin:0;">My Events</h3>
                <a href="#" class="kq-btn kq-btn-outline" style="font-size: 13px;">View All</a>
            </div>
            
            <div class="kq-event-table-container">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; background: #fafafa; border-bottom: 1px solid #eee;">
                            <th style="padding: 12px;">Event</th>
                            <th style="padding: 12px;">Date</th>
                            <th style="padding: 12px;">Sold</th>
                            <th style="padding: 12px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $my_events = get_posts([ 'post_type' => 'kq_event', 'author' => $user_id, 'numberposts' => 5 ]);
                        if ( $my_events ) : foreach ( $my_events as $ev ) : 
                            $summary = \KueueEvents\Core\Modules\Reports\ReportsService::get_event_summary( $ev->ID );
                        ?>
                        <tr style="border-bottom: 1px solid #f9f9f9;">
                            <td style="padding: 12px;"><strong><?php echo esc_html( $ev->post_title ); ?></strong></td>
                            <td style="padding: 12px; font-size: 14px;"><?php echo get_post_meta($ev->ID, '_kq_start_date', true); ?></td>
                            <td style="padding: 12px;"><?php echo $summary['active_tickets']; ?></td>
                            <td style="padding: 12px;"><span style="background: rgba(76, 209, 55, 0.1); color: #4cd137; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700;">Active</span></td>
                        </tr>
                        <?php endforeach; else : ?>
                        <tr><td colspan="4" style="padding: 20px; text-align: center; color: #888;">No events created yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payouts Sidebar -->
        <div class="kq-card" style="background: var(--kq-dark); color: #fff;">
            <h3 style="margin-top:0; color: #fff;">Payout Status</h3>
            <p style="color: #666; font-size: 14px; margin-bottom: 24px;">Track your earnings and pending withdrawal requests.</p>
            
            <?php if ( ! empty( $payouts ) ) : foreach ( $payouts as $p ) : ?>
            <div style="background: #1a1a1c; padding: 16px; border-radius: 12px; margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 700;"><?php echo function_exists('wc_price') ? wc_price( $p->amount ) : $p->amount; ?></span>
                    <span style="font-size: 11px; text-transform: uppercase; color: #aaa;"><?php echo esc_html( $p->status ); ?></span>
                </div>
                <div style="font-size: 12px; color: #666; margin-top: 4px;"><?php echo date_i18n( get_option('date_format'), strtotime($p->created_at ?? 'now') ); ?></div>
            </div>
            <?php endforeach; else : ?>
            <div style="text-align: center; padding: 20px; border: 1px dashed #333; border-radius: 12px; color: #666;">
                No payout history.
            </div>
            <?php endif; ?>

            <a href="#" class="kq-btn kq-btn-primary" style="width: 100%; margin-top: 20px;">Request Payout</a>
        </div>
    </div>
</div>
