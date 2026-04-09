<div class="wrap">
    <h1><?php _e( 'Wallet Transactions', 'kueue-events-core' ); ?></h1>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'Date', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'User', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Type', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Amount', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Balance Before', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Balance After', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Ref / Note', 'kueue-events-core' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $transactions as $t ) : ?>
                <tr>
                    <td><?php echo esc_html( $t->created_at ); ?></td>
                    <td><strong><?php echo esc_html( $t->user_login ); ?></strong></td>
                    <td>
                        <?php 
                        $badge_color = '#7f8c8d';
                        if ( in_array( $t->type, [ 'credit', 'refill', 'adjustment', 'refund' ] ) && $t->balance_after > $t->balance_before ) {
                            $badge_color = '#27ae60';
                        } elseif ( $t->balance_after < $t->balance_before ) {
                            $badge_color = '#c0392b';
                        }
                        ?>
                        <span style="background:<?php echo $badge_color; ?>; color:#fff; padding:2px 8px; border-radius:4px; font-size:11px; text-transform:uppercase;">
                            <?php echo esc_html( $t->type ); ?>
                        </span>
                    </td>
                    <td><strong><?php echo number_format( $t->amount, 2 ); ?></strong></td>
                    <td><?php echo number_format( $t->balance_before, 2 ); ?></td>
                    <td><strong><?php echo number_format( $t->balance_after, 2 ); ?></strong></td>
                    <td>
                        <small>
                            <?php if ( $t->reference_type ) : ?>
                                <strong><?php echo esc_html( strtoupper( $t->reference_type ) ); ?>:</strong> <?php echo esc_html( $t->reference_id ); ?><br>
                            <?php endif; ?>
                            <?php echo esc_html( $t->note ); ?>
                        </small>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
