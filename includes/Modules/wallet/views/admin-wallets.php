<div class="wrap">
    <h1><?php _e( 'User Wallets', 'kueue-events-core' ); ?></h1>

    <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'success' ) : ?>
        <div class="updated notice is-dismissible"><p><?php _e( 'Wallet updated successfully.', 'kueue-events-core' ); ?></p></div>
    <?php endif; ?>

    <form method="get">
        <input type="hidden" name="page" value="kq-wallets">
        <p class="search-box">
            <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>">
            <input type="submit" class="button" value="<?php _e( 'Search Users', 'kueue-events-core' ); ?>">
        </p>
    </form>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e( 'User', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Email', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Balance', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Currency', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Actions', 'kueue-events-core' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $wallets as $wallet ) : ?>
                <tr>
                    <td><strong><?php echo esc_html( $wallet->user_login ); ?></strong></td>
                    <td><?php echo esc_html( $wallet->user_email ); ?></td>
                    <td><span class="wallet-balance" style="font-weight: bold; color: #2ecc71;"><?php echo number_format( $wallet->current_balance, 2 ); ?></span></td>
                    <td><?php echo esc_html( $wallet->currency ); ?></td>
                    <td>
                        <button type="button" class="button action-adjust" 
                                data-user-id="<?php echo $wallet->user_id; ?>" 
                                data-user-name="<?php echo esc_attr( $wallet->user_login ); ?>">
                            <?php _e( 'Adjust Balance', 'kueue-events-core' ); ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Adjustment Modal -->
    <div id="kq-wallet-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999;">
        <div style="background:#fff; width:400px; margin:100px auto; padding:20px; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,0.2);">
            <h3><?php _e( 'Adjust Wallet Balance', 'kueue-events-core' ); ?>: <span id="modal-user-name"></span></h3>
            <form method="post">
                <?php wp_nonce_field( 'kq_wallet_adjust' ); ?>
                <input type="hidden" name="kq_wallet_action" value="manual_adjust">
                <input type="hidden" name="user_id" id="modal-user-id">
                
                <p>
                    <label><?php _e( 'Action', 'kueue-events-core' ); ?></label><br>
                    <select name="type" style="width:100%;">
                        <option value="credit"><?php _e( 'Credit (Add)', 'kueue-events-core' ); ?></option>
                        <option value="debit"><?php _e( 'Debit (Subtract)', 'kueue-events-core' ); ?></option>
                    </select>
                </p>
                <p>
                    <label><?php _e( 'Amount', 'kueue-events-core' ); ?></label><br>
                    <input type="number" name="amount" step="0.01" min="0.01" style="width:100%;" required>
                </p>
                <p>
                    <label><?php _e( 'Note', 'kueue-events-core' ); ?></label><br>
                    <textarea name="note" style="width:100%;" rows="3"></textarea>
                </p>
                <p style="text-align:right;">
                    <button type="button" class="button" id="modal-close"><?php _e( 'Cancel', 'kueue-events-core' ); ?></button>
                    <button type="submit" class="button button-primary"><?php _e( 'Update Wallet', 'kueue-events-core' ); ?></button>
                </p>
            </form>
        </div>
    </div>

    <script>
        jQuery(document).ready(function($) {
            $('.action-adjust').on('click', function() {
                $('#modal-user-id').val($(this).data('user-id'));
                $('#modal-user-name').text($(this).data('user-name'));
                $('#kq-wallet-modal').show();
            });
            $('#modal-close').on('click', function() {
                $('#kq-wallet-modal').hide();
            });
        });
    </script>
</div>
