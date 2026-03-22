<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
    <h1><?php _e( 'Kueue Events Settings', 'kueue-events-core' ); ?></h1>

    <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'saved' ) : ?>
        <div class="updated"><p><?php _e( 'Settings saved successfully.', 'kueue-events-core' ); ?></p></div>
    <?php endif; ?>

    <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'queue_processed' ) : ?>
        <div class="updated"><p><?php _e( 'Manual queue processing triggered.', 'kueue-events-core' ); ?></p></div>
    <?php endif; ?>

    <h2 class="nav-tab-wrapper">
        <a href="#tab-delivery" class="nav-tab nav-tab-active"><?php _e( 'Delivery Hardening', 'kueue-events-core' ); ?></a>
    </h2>

    <form method="post" action="admin-post.php">
        <input type="hidden" name="action" value="kq_save_settings">
        <?php wp_nonce_field( 'kq_save_settings_nonce' ); ?>

        <div id="tab-delivery" class="tab-content">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e( 'WhatsApp Throttling (seconds)', 'kueue-events-core' ); ?></th>
                    <td>
                        <input type="number" name="kq_throttle_whatsapp" value="<?php echo (int) get_option( 'kq_throttle_whatsapp', 1 ); ?>" class="small-text">
                        <p class="description"><?php _e( 'Minimum delay between WhatsApp messages per account.', 'kueue-events-core' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'SMS Throttling (seconds)', 'kueue-events-core' ); ?></th>
                    <td>
                        <input type="number" name="kq_throttle_sms" value="<?php echo (int) get_option( 'kq_throttle_sms', 1 ); ?>" class="small-text">
                        <p class="description"><?php _e( 'Minimum delay between SMS messages per account.', 'kueue-events-core' ); ?></p>
                    </td>
                </tr>
            </table>

            <h3><?php _e( 'Check-in Settings', 'kueue-events-core' ); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e( 'Allow Check-out after Check-in', 'kueue-events-core' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="kq_allow_checkout_after_checkin" value="1" <?php checked( get_option('kq_allow_checkout_after_checkin'), 1 ); ?>>
                            <?php _e( 'Allow scanners to toggle status back to checked_out for re-entry logic.', 'kueue-events-core' ); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button(); ?>
    </form>

    <hr>

    <h3><?php _e( 'Queue Management', 'kueue-events-core' ); ?></h3>
    <p><?php _e( 'Process the delivery queue manually now.', 'kueue-events-core' ); ?></p>
    <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=kq_manual_queue' ), 'kq_manual_queue_nonce' ); ?>" class="button button-primary">
        <?php _e( 'Process Queue Now (20 jobs)', 'kueue-events-core' ); ?>
    </a>
</div>
