<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
    <h1><?php _e( 'Finance & Commissions', 'kueue-events-core' ); ?></h1>
    
    <div class="kq-finance-stats" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 20px;">
        <div class="stat-box" style="background:#fff; padding:20px; border-radius:10px; border:1px solid #ddd;">
            <h3><?php _e( 'Total Gross', 'kueue-events-core' ); ?></h3>
            <div class="value" style="font-size:2em; font-weight:bold; color:#333;"><?php echo kq_price( $stats->total_gross ?? 0 ); ?></div>
        </div>
        <div class="stat-box" style="background:#fff; padding:20px; border-radius:10px; border:1px solid #ddd;">
            <h3><?php _e( 'Total Commissions', 'kueue-events-core' ); ?></h3>
            <div class="value" style="font-size:2em; font-weight:bold; color:#ff3131;"><?php echo kq_price( $stats->total_commission ?? 0 ); ?></div>
        </div>
        <div class="stat-box" style="background:#fff; padding:20px; border-radius:10px; border:1px solid #ddd;">
            <h3><?php _e( 'Total Net (Organizers)', 'kueue-events-core' ); ?></h3>
            <div class="value" style="font-size:2em; font-weight:bold; color:#28a745;"><?php echo kq_price( $stats->total_net ?? 0 ); ?></div>
        </div>
        <div class="stat-box" style="background:#fff; padding:20px; border-radius:10px; border:1px solid #ddd;">
            <h3><?php _e( 'Pending Payouts', 'kueue-events-core' ); ?></h3>
            <div class="value" style="font-size:2em; font-weight:bold; color:#ffc107;"><?php echo kq_price( $stats->pending_payouts ?? 0 ); ?></div>
        </div>
    </div>

    <h2 style="margin-top:40px; color: #ff3131;"><?php _e( 'Pending Payout Requests', 'kueue-events-core' ); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'ID', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Organizer', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Amount', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Method', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Request Date', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Actions', 'kueue-events-core' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $payout_requests ) ) : foreach ( $payout_requests as $p ) : 
                $org = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_id( $p->organizer_id );
            ?>
            <tr>
                <td>#<?php echo esc_html( $p->id ); ?></td>
                <td><?php echo $org ? esc_html( $org->company_name ) : '—'; ?></td>
                <td><strong><?php echo kq_price( $p->amount ); ?></strong></td>
                <td><?php echo esc_html( strtoupper($p->payment_method) ); ?></td>
                <td><?php echo date_i18n( get_option('date_format'), strtotime( $p->created_at ) ); ?></td>
                <td>
                    <button class="button button-primary kq-approve-payout" data-id="<?php echo $p->id; ?>"><?php _e( 'Approve', 'kueue-events-core' ); ?></button>
                    <button class="button kq-reject-payout" data-id="<?php echo $p->id; ?>"><?php _e( 'Reject', 'kueue-events-core' ); ?></button>
                </td>
            </tr>
            <?php endforeach; else : ?>
            <tr>
                <td colspan="6" style="text-align:center; padding:20px;"><?php _e( 'No pending payout requests.', 'kueue-events-core' ); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2 style="margin-top:40px;"><?php _e( 'Recent Commissions', 'kueue-events-core' ); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'Order ID', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Organizer', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Event', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Gross Amount', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Commission', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Net Amount', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Status', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Date', 'kueue-events-core' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $commissions ) ) : foreach ( $commissions as $c ) : 
                $org = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_id( $c->organizer_id );
                $event = get_post( $c->event_id );
            ?>
            <tr>
                <td>#<?php echo esc_html( $c->order_id ); ?></td>
                <td><?php echo $org ? esc_html( $org->company_name ) : '—'; ?></td>
                <td><?php echo $event ? esc_html( $event->post_title ) : '—'; ?></td>
                <td><?php echo kq_price( $c->gross_amount ); ?></td>
                <td><?php echo kq_price( $c->commission_amount ); ?></td>
                <td><strong><?php echo kq_price( $c->net_amount ); ?></strong></td>
                <td>
                    <span class="status-badge" style="background: <?php echo $c->status === 'paid' ? '#4cd137' : '#ffc107'; ?>; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 11px;">
                        <?php echo strtoupper( $c->status ); ?>
                    </span>
                </td>
                <td><?php echo date_i18n( get_option('date_format'), strtotime( $c->created_at ) ); ?></td>
            </tr>
            <?php endforeach; else : ?>
            <tr>
                <td colspan="8" style="text-align:center; padding:20px;"><?php _e( 'No finance data found.', 'kueue-events-core' ); ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
