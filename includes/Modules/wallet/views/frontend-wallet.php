<?php
$service = new \KueueEvents\Core\Modules\Wallet\WalletService();
$trans_repo = new \KueueEvents\Core\Modules\Wallet\WalletTransactionRepository();
$user_id = get_current_user_id();
$balance = $service->get_balance( $user_id );
$transactions = $trans_repo->get_user_transactions( $user_id, 20 );
?>

<div class="kq-wallet-container" style="max-width: 900px; margin: 40px auto; font-family: 'Inter', sans-serif; color: #1a1a1a;">
    <div class="kq-wallet-header" style="background: linear-gradient(135deg, #1e1e1e 0%, #3a3a3a 100%); padding: 40px; border-radius: 20px; color: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.15); margin-bottom: 30px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -20px; right: -20px; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
        
        <h2 style="margin: 0; font-size: 18px; font-weight: 300; opacity: 0.8;"><?php _e( 'Account Balance', 'kueue-events-core' ); ?></h2>
        <div style="font-size: 48px; font-weight: 800; margin: 10px 0;">
            <?php echo wc_price( $balance ); ?>
        </div>
        
        <div style="display: flex; gap: 15px; margin-top: 20px;">
            <button class="kq-btn kq-btn-outline" style="border-color: rgba(255,255,255,0.3); color: #fff;"><?php _e( 'Top Up', 'kueue-events-core' ); ?></button>
            <p style="font-size: 12px; opacity: 0.6; align-self: center;"><?php _e( 'Top-up with credit card coming soon.', 'kueue-events-core' ); ?></p>
        </div>
    </div>

    <div class="kq-wallet-history" style="background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05);">
        <h3 style="margin-top: 0; margin-bottom: 25px; font-size: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;"><?php _e( 'Transaction History', 'kueue-events-core' ); ?></h3>
        
        <?php if ( empty( $transactions ) ) : ?>
            <p style="text-align: center; color: #888; padding: 40px 0;"><?php _e( 'No transactions found.', 'kueue-events-core' ); ?></p>
        <?php else : ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; color: #888; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                            <th style="padding: 12px 0;"><?php _e( 'Details', 'kueue-events-core' ); ?></th>
                            <th style="padding: 12px 0;"><?php _e( 'Reference', 'kueue-events-core' ); ?></th>
                            <th style="padding: 12px 0; text-align: right;"><?php _e( 'Amount', 'kueue-events-core' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $transactions as $t ) : 
                            $is_credit = $t->balance_after > $t->balance_before;
                        ?>
                            <tr style="border-bottom: 1px solid #f9f9f9;">
                                <td style="padding: 15px 0;">
                                    <div style="font-weight: 600;"><?php echo esc_html( ucfirst( $t->type ) ); ?></div>
                                    <div style="font-size: 11px; color: #aaa;"><?php echo date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $t->created_at ) ); ?></div>
                                </td>
                                <td style="padding: 15px 0;">
                                    <div style="font-size: 13px; color: #666;"><?php echo esc_html( $t->note ); ?></div>
                                    <?php if ( $t->reference_id ) : ?>
                                        <div style="font-size: 11px; color: #999;"><?php echo esc_html( strtoupper( $t->reference_type ) ); ?>: #<?php echo esc_html( $t->reference_id ); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 15px 0; text-align: right;">
                                    <span style="font-weight: 700; color: <?php echo $is_credit ? '#27ae60' : '#e74c3c'; ?>;">
                                        <?php echo $is_credit ? '+' : '-'; ?> <?php echo wc_price( $t->amount ); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
