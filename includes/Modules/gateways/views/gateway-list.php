<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<style>
    .kq-gw-list-wrap { max-width: 1000px; }
    .kq-gw-list-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 18px; border-bottom: 1px solid #e5e5e5; }
    .kq-gw-list-title { display: flex; align-items: center; gap: 12px; }
    .kq-gw-list-title h1 { margin: 0; font-size: 22px; font-weight: 800; color: #1a1a1a; }
    .kq-gw-icon { width: 42px; height: 42px; background: linear-gradient(135deg, #ff3131 0%, #c0392b 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .kq-gw-add-btn { background: #ff3131; color: #fff !important; border: none; border-radius: 7px; padding: 10px 20px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .kq-gw-add-btn:hover { background: #c0392b; }
    .kq-list-table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e5e5e5; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
    .kq-list-table thead th { background: #f8f8f8; padding: 12px 18px; text-align: left; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #666; border-bottom: 1px solid #e5e5e5; }
    .kq-list-table tbody td { padding: 14px 18px; border-bottom: 1px solid #f0f0f0; font-size: 13px; color: #333; vertical-align: middle; }
    .kq-list-table tbody tr:last-child td { border-bottom: none; }
    .kq-list-table tbody tr:hover { background: #fafafa; }
    .kq-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .kq-badge-active { background: #e8f8ee; color: #1a7a43; }
    .kq-badge-inactive { background: #f5f5f5; color: #888; }
    .kq-badge-default { background: #fff3cd; color: #856404; }
    .kq-list-actions { display: flex; gap: 8px; align-items: center; }
    .kq-list-actions a { font-size: 12px; font-weight: 600; text-decoration: none; padding: 5px 12px; border-radius: 5px; border: 1px solid transparent; }
    .kq-action-edit { color: #2271b1; border-color: #b8d0ee; background: #f0f6ff; }
    .kq-action-edit:hover { background: #ddeeff; }
    .kq-action-delete { color: #9b2226; border-color: #f5c6cb; background: #fff0f0; }
    .kq-action-delete:hover { background: #ffe0e0; }
    .kq-empty-state { text-align: center; padding: 60px 20px; color: #888; }
    .kq-empty-state .kq-empty-icon { font-size: 48px; margin-bottom: 12px; }
    .kq-empty-state h3 { font-size: 16px; color: #555; margin: 0 0 8px; }
</style>

<div class="wrap kq-gw-list-wrap">
    <div class="kq-gw-list-header">
        <div class="kq-gw-list-title">
            <div class="kq-gw-icon"><?php echo $channel === 'sms' ? '📱' : '💬'; ?></div>
            <h1><?php echo $channel === 'sms' ? __( 'SMS Accounts', 'kueue-events-core' ) : __( 'WhatsApp Accounts', 'kueue-events-core' ); ?></h1>
        </div>
        <a href="<?php echo admin_url( 'admin.php?page=' . $page . '&action=add' ); ?>" class="kq-gw-add-btn">
            + <?php $channel === 'sms' ? _e( 'Add SMS Account', 'kueue-events-core' ) : _e( 'Add WhatsApp Account', 'kueue-events-core' ); ?>
        </a>
    </div>

    <table class="kq-list-table">
        <thead>
            <tr>
                <th><?php _e( 'Account Name', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Provider', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Status', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Default', 'kueue-events-core' ); ?></th>
                <th><?php _e( 'Actions', 'kueue-events-core' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $accounts ) ) : ?>
                <tr>
                    <td colspan="5">
                        <div class="kq-empty-state">
                            <div class="kq-empty-icon"><?php echo $channel === 'sms' ? '📱' : '💬'; ?></div>
                            <h3><?php $channel === 'sms' ? _e( 'No SMS accounts yet', 'kueue-events-core' ) : _e( 'No WhatsApp accounts yet', 'kueue-events-core' ); ?></h3>
                            <p><?php _e( 'Add your first account to start sending messages.', 'kueue-events-core' ); ?></p>
                            <a href="<?php echo admin_url( 'admin.php?page=' . $page . '&action=add' ); ?>" class="kq-gw-add-btn" style="display:inline-flex;">
                                + <?php $channel === 'sms' ? _e( 'Add SMS Account', 'kueue-events-core' ) : _e( 'Add WhatsApp Account', 'kueue-events-core' ); ?>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $accounts as $account ) : ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html( $account->account_name ); ?></strong>
                        </td>
                        <td>
                            <span style="text-transform: capitalize; color: #555;"><?php echo esc_html( str_replace( ['_', 'sms', 'whatsapp'], [' ', 'SMS', 'WhatsApp'], $account->provider ) ); ?></span>
                        </td>
                        <td>
                            <?php if ( $account->is_enabled ) : ?>
                                <span class="kq-badge kq-badge-active">✓ Active</span>
                            <?php else : ?>
                                <span class="kq-badge kq-badge-inactive">✗ Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $account->is_default ) : ?>
                                <span class="kq-badge kq-badge-default">★ Default</span>
                            <?php else : ?>
                                <span style="color: #ccc;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="kq-list-actions">
                                <a href="<?php echo admin_url( 'admin.php?page=' . $page . '&action=edit&id=' . $account->id ); ?>" class="kq-action-edit">
                                    ✏️ Edit
                                </a>
                                <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=' . $page . '&action=delete&id=' . $account->id ), 'kq_delete_account_' . $account->id ); ?>"
                                   class="kq-action-delete"
                                   onclick="return confirm('<?php _e( 'Delete this account? This cannot be undone.', 'kueue-events-core' ); ?>')">
                                    🗑 Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
