<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <h1>
        <?php echo ( $channel === 'sms' ) ? __( 'SMS Accounts', 'kueue-events-core' ) : __( 'WhatsApp Accounts', 'kueue-events-core' ); ?>
        <a href="<?php echo admin_url( 'admin.php?page=' . $page . '&action=add' ); ?>" class="page-title-action"><?php _e( 'Add New', 'kueue-events-core' ); ?></a>
    </h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-name"><?php _e( 'Account Name', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-provider"><?php _e( 'Provider', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-status"><?php _e( 'Status', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-default"><?php _e( 'Default', 'kueue-events-core' ); ?></th>
                <th scope="col" class="manage-column column-actions"><?php _e( 'Actions', 'kueue-events-core' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $accounts ) ) : ?>
                <tr><td colspan="5"><?php _e( 'No accounts found.', 'kueue-events-core' ); ?></td></tr>
            <?php else : ?>
                <?php foreach ( $accounts as $account ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( $account->account_name ); ?></strong></td>
                        <td><?php echo esc_html( $account->provider ); ?></td>
                        <td>
                            <?php if ( $account->is_enabled ) : ?>
                                <span class="badge badge-success" style="color: green;">✔ Enabled</span>
                            <?php else : ?>
                                <span class="badge badge-error" style="color: red;">✘ Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $account->is_default ) : ?>
                                <span class="dashicons dashicons-star-filled" style="color: gold;"></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo admin_url( 'admin.php?page=' . $page . '&action=edit&id=' . $account->id ); ?>"><?php _e( 'Edit', 'kueue-events-core' ); ?></a> |
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=' . $page . '&action=delete&id=' . $account->id ), 'kq_delete_account_' . $account->id ); ?>" style="color: red;" onclick="return confirm('<?php _e( 'Are you sure?', 'kueue-events-core' ); ?>')"><?php _e( 'Delete', 'kueue-events-core' ); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
