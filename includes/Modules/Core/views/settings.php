<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="kq-admin-section">
    <div class="kq-admin-header">
        <h1><?php _e( 'Settings & Configuration', 'kueue-events-core' ); ?></h1>
    </div>

    <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'saved' ) : ?>
        <div class="updated notice is-dismissible" style="border-left-color: var(--kq-primary);"><p><?php _e( 'Settings saved successfully.', 'kueue-events-core' ); ?></p></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 300px; gap: 30px;">
        <div class="kq-card">
            <form method="post" action="admin-post.php">
                <input type="hidden" name="action" value="kq_save_settings">
                <?php wp_nonce_field( 'kq_save_settings_nonce' ); ?>

                <h3 style="margin-top:0; color: var(--kq-dark);"><?php _e( 'Message Delivery Control', 'kueue-events-core' ); ?></h3>
                <p style="color: #888; font-size: 13px; margin-bottom: 20px;"><?php _e( 'Configure how the system handles SMS and WhatsApp traffic to avoid account bans.', 'kueue-events-core' ); ?></p>

                <div class="kq-form-row" style="margin-bottom: 24px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px;"><?php _e( 'WhatsApp Throttling (seconds)', 'kueue-events-core' ); ?></label>
                    <input type="number" name="kq_throttle_whatsapp" value="<?php echo (int) get_option( 'kq_throttle_whatsapp', 1 ); ?>" class="kq-input" style="max-width: 150px;">
                    <p style="font-size: 12px; color: #999; margin-top: 4px;"><?php _e( 'Minimum delay between WhatsApp messages per account.', 'kueue-events-core' ); ?></p>
                </div>

                <div class="kq-form-row" style="margin-bottom: 40px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 8px;"><?php _e( 'SMS Throttling (seconds)', 'kueue-events-core' ); ?></label>
                    <input type="number" name="kq_throttle_sms" value="<?php echo (int) get_option( 'kq_throttle_sms', 1 ); ?>" class="kq-input" style="max-width: 150px;">
                    <p style="font-size: 12px; color: #999; margin-top: 4px;"><?php _e( 'Minimum delay between SMS messages per account.', 'kueue-events-core' ); ?></p>
                </div>

                <h3 style="border-top: 1px solid #eee; padding-top: 30px; margin-top: 30px; color: var(--kq-dark);"><?php _e( 'Check-in Logic', 'kueue-events-core' ); ?></h3>
                <div class="kq-form-row">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="kq_allow_checkout_after_checkin" value="1" <?php checked( get_option('kq_allow_checkout_after_checkin'), 1 ); ?>>
                        <span style="font-weight: 600;"><?php _e( 'Allow re-entry (Check-out after Check-in)', 'kueue-events-core' ); ?></span>
                    </label>
                    <p style="font-size: 12px; color: #999; margin-top: 4px; padding-left: 25px;">
                        <?php _e( 'Allow scanners to toggle status back to checked_out for re-entry logic.', 'kueue-events-core' ); ?>
                    </p>
                </div>

                <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                    <button type="submit" class="kq-btn kq-btn-primary"><?php _e( 'Save Configuration', 'kueue-events-core' ); ?></button>
                </div>
            </form>
        </div>

        <div class="kq-sidebar-info">
            <div class="kq-card" style="background: var(--kq-dark); color: #fff;">
                <h3 style="color: #fff; margin-top:0;"><?php _e( 'System Health', 'kueue-events-core' ); ?></h3>
                <div style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: #888;">Queue Status</span>
                        <span style="color: #4cd137;">● Online</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #888;">Pending Jobs</span>
                        <span>0</span>
                    </div>
                </div>
                
                <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=kq_manual_queue' ), 'kq_manual_queue_nonce' ); ?>" 
                   class="kq-btn kq-btn-outline" 
                   style="width: 100%; border-color: #333; color: #fff !important; text-align: center;">
                   <?php _e( 'Process Queue Now', 'kueue-events-core' ); ?>
                </a>
            </div>
            
            <div class="kq-card" style="margin-top: 20px; border-left: 4px solid var(--kq-primary);">
                <h4 style="margin-top:0;"><?php _e( 'Need Help?', 'kueue-events-core' ); ?></h4>
                <p style="font-size: 13px; color: #666;"><?php _e( 'If you encounter delivery issues, check individual gateway account logs under the Gateway managers.', 'kueue-events-core' ); ?></p>
            </div>
        </div>
    </div>
</div>
