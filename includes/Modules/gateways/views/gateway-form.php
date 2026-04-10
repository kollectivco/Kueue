<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
use KueueEvents\Core\Helpers\EncryptionHelper;

$available_providers = \KueueEvents\Core\Modules\Gateways\GatewayManager::get_available_providers();

$account_name  = '';
$provider_slug = '';
$config        = [];
$is_enabled    = 1;
$is_default    = 0;
$notes         = '';

if ( isset( $account ) && is_object( $account ) ) {
    $account_name  = $account->account_name ?? '';
    $provider_slug = $account->provider ?? '';
    $config        = (array) ($account->config ?? []);
    $is_enabled    = (int) ($account->is_enabled ?? 1);
    $is_default    = (int) ($account->is_default ?? 0);
    $notes         = $config['notes'] ?? '';
}

$is_edit    = ( $action === 'edit' );
$page_slug  = ( $channel === 'sms' ) ? 'kq-sms-accounts' : 'kq-whatsapp-accounts';
$page_title = $is_edit
    ? ( $channel === 'sms' ? __( 'Edit SMS Account', 'kueue-events-core' ) : __( 'Edit WhatsApp Account', 'kueue-events-core' ) )
    : ( $channel === 'sms' ? __( 'Add New SMS Account', 'kueue-events-core' ) : __( 'Add New WhatsApp Account', 'kueue-events-core' ) );

$channel_icon = $channel === 'sms' ? '📱' : '💬';
$channel_label = $channel === 'sms' ? 'SMS' : 'WhatsApp';
?>
<style>
    .kq-gw-wrap { max-width: 900px; }
    .kq-gw-breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #888; margin-bottom: 6px; }
    .kq-gw-breadcrumb a { color: #2271b1; text-decoration: none; }
    .kq-gw-breadcrumb a:hover { text-decoration: underline; }
    .kq-gw-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1px solid #e5e5e5; }
    .kq-gw-title { display: flex; align-items: center; gap: 12px; }
    .kq-gw-title h1 { margin: 0; font-size: 22px; font-weight: 800; color: #1a1a1a; }
    .kq-gw-icon { width: 42px; height: 42px; background: linear-gradient(135deg, #ff3131 0%, #c0392b 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .kq-gw-card { background: #fff; border: 1px solid #e5e5e5; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
    .kq-gw-card-header { padding: 16px 22px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 10px; }
    .kq-gw-card-header h3 { margin: 0; font-size: 14px; font-weight: 700; color: #1a1a1a; }
    .kq-gw-card-header .kq-card-icon { width: 26px; height: 26px; border-radius: 6px; background: #fff3f3; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 1px solid #ffdada; }
    .kq-gw-card-body { padding: 22px; }
    .kq-gw-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .kq-gw-field { margin-bottom: 0; }
    .kq-gw-field label { display: block; font-weight: 600; font-size: 13px; color: #333; margin-bottom: 6px; }
    .kq-gw-field input[type="text"],
    .kq-gw-field input[type="email"],
    .kq-gw-field input[type="password"],
    .kq-gw-field input[type="url"],
    .kq-gw-field select,
    .kq-gw-field textarea { width: 100%; padding: 9px 12px; border: 1px solid #ccd0d4; border-radius: 6px; font-size: 13px; color: #1a1a1a; box-shadow: inset 0 1px 2px rgba(0,0,0,.05); transition: border-color .15s; }
    .kq-gw-field input:focus, .kq-gw-field select:focus, .kq-gw-field textarea:focus { outline: none; border-color: #2271b1; box-shadow: 0 0 0 3px rgba(34,113,177,.1); }
    .kq-gw-field textarea { min-height: 80px; resize: vertical; }
    .kq-gw-field .kq-field-hint { font-size: 11px; color: #888; margin-top: 5px; }
    .kq-gw-toggle-row { display: flex; align-items: center; justify-content: space-between; padding: 14px 0; border-bottom: 1px solid #f5f5f5; }
    .kq-gw-toggle-row:last-child { border-bottom: none; padding-bottom: 0; }
    .kq-gw-toggle-label strong { display: block; font-size: 13px; font-weight: 600; color: #1a1a1a; }
    .kq-gw-toggle-label span { font-size: 11px; color: #888; }
    .kq-toggle { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
    .kq-toggle input { opacity: 0; width: 0; height: 0; }
    .kq-toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #ccc; border-radius: 24px; transition: .2s; }
    .kq-toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: .2s; }
    .kq-toggle input:checked + .kq-toggle-slider { background: #ff3131; }
    .kq-toggle input:checked + .kq-toggle-slider:before { transform: translateX(20px); }
    .kq-gw-footer { display: flex; align-items: center; gap: 12px; padding-top: 10px; }
    .kq-gw-btn-primary { background: #ff3131; color: #fff; border: none; border-radius: 7px; padding: 10px 24px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .15s; }
    .kq-gw-btn-primary:hover { background: #c0392b; color: #fff; }
    .kq-gw-btn-secondary { background: #fff; color: #333; border: 1px solid #ccd0d4; border-radius: 7px; padding: 9px 20px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; transition: border-color .15s; }
    .kq-gw-btn-secondary:hover { border-color: #999; color: #333; }
    .kq-gw-btn-test { background: #f0f6ff; color: #2271b1; border: 1px solid #b8d0ee; border-radius: 7px; padding: 9px 20px; font-size: 13px; font-weight: 600; cursor: pointer; }
    .kq-gw-btn-test:hover { background: #ddeeff; }
    .kq-provider-block { display: none; }
    .kq-provider-block.is-active { display: block; }
    .kq-cred-field { position: relative; }
    .kq-cred-field input { padding-right: 40px; }
    .kq-test-result { margin-top: 12px; padding: 10px 14px; border-radius: 6px; font-size: 13px; display: none; }
    .kq-test-result.success { background: #e8f8ee; border: 1px solid #b2dfcc; color: #1a7a43; }
    .kq-test-result.error { background: #fff0f0; border: 1px solid #f5c6cb; color: #9b2226; }
    .kq-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
    .kq-badge-active { background: #e8f8ee; color: #1a7a43; }
    .kq-badge-inactive { background: #f5f5f5; color: #888; }
    .kq-badge-default { background: #fff3cd; color: #856404; }
</style>

<div class="wrap kq-gw-wrap">

    <!-- Breadcrumb -->
    <div class="kq-gw-breadcrumb">
        <a href="<?php echo admin_url( 'admin.php?page=' . $page_slug ); ?>">
            <?php echo $channel_icon; ?> <?php echo $channel_label; ?> Accounts
        </a>
        <span>/</span>
        <span><?php echo $page_title; ?></span>
    </div>

    <!-- Header -->
    <div class="kq-gw-header">
        <div class="kq-gw-title">
            <div class="kq-gw-icon"><?php echo $channel_icon; ?></div>
            <h1><?php echo esc_html( $page_title ); ?></h1>
        </div>
        <a href="<?php echo admin_url( 'admin.php?page=' . $page_slug ); ?>" class="kq-gw-btn-secondary">
            ← <?php _e( 'Back to List', 'kueue-events-core' ); ?>
        </a>
    </div>

    <form method="post" id="kq-gateway-form">
        <?php wp_nonce_field( 'kq_save_account_nonce' ); ?>

        <!-- ── A. BASIC INFO ─────────────────── -->
        <div class="kq-gw-card">
            <div class="kq-gw-card-header">
                <div class="kq-card-icon">📋</div>
                <h3><?php _e( 'Basic Information', 'kueue-events-core' ); ?></h3>
            </div>
            <div class="kq-gw-card-body">
                <div class="kq-gw-grid">
                    <div class="kq-gw-field">
                        <label for="account_name"><?php _e( 'Account Name', 'kueue-events-core' ); ?> *</label>
                        <input type="text" name="account_name" id="account_name"
                               value="<?php echo esc_attr( $account_name ); ?>"
                               placeholder="<?php echo $channel === 'sms' ? 'e.g. SMSMisr Production' : 'e.g. WhatsApp Business Main'; ?>"
                               required>
                        <p class="kq-field-hint"><?php _e( 'Internal label for this account. Not shown publicly.', 'kueue-events-core' ); ?></p>
                    </div>
                    <div class="kq-gw-field">
                        <label for="provider"><?php _e( 'Provider', 'kueue-events-core' ); ?> *</label>
                        <select name="provider" id="kq-provider-select">
                            <option value=""><?php _e( '-- Select Provider --', 'kueue-events-core' ); ?></option>
                            <?php foreach ( $available_providers as $slug => $prov ) : ?>
                                <?php if ( $prov['channel'] === $channel ) : ?>
                                    <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $provider_slug, $slug ); ?>>
                                        <?php echo esc_html( $prov['name'] ); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <p class="kq-field-hint"><?php _e( 'The API gateway provider for this account.', 'kueue-events-core' ); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── B. CREDENTIALS ─────────────────── -->
        <div class="kq-gw-card">
            <div class="kq-gw-card-header">
                <div class="kq-card-icon">🔑</div>
                <h3><?php _e( 'Provider Credentials', 'kueue-events-core' ); ?></h3>
            </div>
            <div class="kq-gw-card-body">
                <p style="color:#888; font-size:13px; margin-top:0;"><?php _e( 'Credentials are encrypted before being stored. Sensitive fields are masked on edit.', 'kueue-events-core' ); ?></p>

                <?php if ( $channel === 'sms' ) : ?>

                    <!-- SMSMisr Block -->
                    <div class="kq-provider-block <?php echo $provider_slug === 'sms_misr' ? 'is-active' : ''; ?>" data-provider="sms_misr">
                        <div class="kq-gw-grid">
                            <div class="kq-gw-field">
                                <label><?php _e( 'Username', 'kueue-events-core' ); ?></label>
                                <input type="text" name="config[username]"
                                       value="<?php echo !empty($config['username']) ? EncryptionHelper::mask( EncryptionHelper::decrypt($config['username']) ) : ''; ?>">
                                <p class="kq-field-hint"><?php _e( 'Your SMSMisr account username.', 'kueue-events-core' ); ?></p>
                            </div>
                            <div class="kq-gw-field">
                                <label><?php _e( 'Password', 'kueue-events-core' ); ?></label>
                                <input type="password" name="config[password]"
                                       value="<?php echo !empty($config['password']) ? '********' : ''; ?>">
                                <p class="kq-field-hint"><?php _e( 'Leave as-is to keep existing password.', 'kueue-events-core' ); ?></p>
                            </div>
                            <div class="kq-gw-field">
                                <label><?php _e( 'Sender ID / From', 'kueue-events-core' ); ?></label>
                                <input type="text" name="config[sender]"
                                       value="<?php echo esc_attr( $config['sender'] ?? '' ); ?>"
                                       placeholder="e.g. Kueue">
                            </div>
                        </div>
                    </div>

                    <!-- Twilio Block -->
                    <div class="kq-provider-block <?php echo $provider_slug === 'twilio' ? 'is-active' : ''; ?>" data-provider="twilio">
                        <div class="kq-gw-grid">
                            <div class="kq-gw-field">
                                <label><?php _e( 'Account SID', 'kueue-events-core' ); ?></label>
                                <input type="text" name="config[username]"
                                       value="<?php echo !empty($config['username']) ? EncryptionHelper::mask( EncryptionHelper::decrypt($config['username']) ) : ''; ?>">
                            </div>
                            <div class="kq-gw-field">
                                <label><?php _e( 'Auth Token', 'kueue-events-core' ); ?></label>
                                <input type="password" name="config[password]"
                                       value="<?php echo !empty($config['password']) ? '********' : ''; ?>">
                            </div>
                            <div class="kq-gw-field">
                                <label><?php _e( 'From Number', 'kueue-events-core' ); ?></label>
                                <input type="text" name="config[sender]"
                                       value="<?php echo esc_attr( $config['sender'] ?? '' ); ?>"
                                       placeholder="+12025551234">
                            </div>
                        </div>
                    </div>

                    <!-- Nexmo Block -->
                    <div class="kq-provider-block <?php echo $provider_slug === 'nexmo' ? 'is-active' : ''; ?>" data-provider="nexmo">
                        <div class="kq-gw-grid">
                            <div class="kq-gw-field">
                                <label><?php _e( 'API Key', 'kueue-events-core' ); ?></label>
                                <input type="text" name="config[username]"
                                       value="<?php echo !empty($config['username']) ? EncryptionHelper::mask( EncryptionHelper::decrypt($config['username']) ) : ''; ?>">
                            </div>
                            <div class="kq-gw-field">
                                <label><?php _e( 'API Secret', 'kueue-events-core' ); ?></label>
                                <input type="password" name="config[password]"
                                       value="<?php echo !empty($config['password']) ? '********' : ''; ?>">
                            </div>
                            <div class="kq-gw-field">
                                <label><?php _e( 'From / Sender Name', 'kueue-events-core' ); ?></label>
                                <input type="text" name="config[sender]"
                                       value="<?php echo esc_attr( $config['sender'] ?? '' ); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Fallback: no provider selected -->
                    <div class="kq-provider-block <?php echo empty($provider_slug) ? 'is-active' : ''; ?>" data-provider="">
                        <p style="color:#999; text-align:center; padding: 20px 0; margin:0;">
                            <?php _e( 'Select a provider above to see its credential fields.', 'kueue-events-core' ); ?>
                        </p>
                    </div>

                <?php else : // WhatsApp ?>

                    <!-- WhatsApp Cloud API Block -->
                    <div class="kq-provider-block <?php echo ($provider_slug === 'whatsapp_cloud' || empty($provider_slug)) ? 'is-active' : ''; ?>" data-provider="whatsapp_cloud">
                        <div class="kq-gw-grid">
                            <div class="kq-gw-field" style="grid-column: span 2;">
                                <label><?php _e( 'Access Token (Bearer)', 'kueue-events-core' ); ?></label>
                                <input type="password" name="config[access_token]"
                                       value="<?php echo !empty($config['access_token']) ? '********' : ''; ?>"
                                       placeholder="EAAxxxxxxxxxxxxxxx">
                                <p class="kq-field-hint"><?php _e( 'Permanent or temporary access token from Meta Business API. Leave as-is to keep existing token.', 'kueue-events-core' ); ?></p>
                            </div>
                            <div class="kq-gw-field">
                                <label><?php _e( 'Phone Number ID', 'kueue-events-core' ); ?></label>
                                <input type="text" name="config[phone_number_id]"
                                       value="<?php echo esc_attr( $config['phone_number_id'] ?? '' ); ?>"
                                       placeholder="1234567890123">
                                <p class="kq-field-hint"><?php _e( 'Found in Meta Business → WhatsApp → Phone Numbers.', 'kueue-events-core' ); ?></p>
                            </div>
                            <div class="kq-gw-field">
                                <label><?php _e( 'Business Account ID (WABA ID)', 'kueue-events-core' ); ?></label>
                                <input type="text" name="config[business_account_id]"
                                       value="<?php echo esc_attr( $config['business_account_id'] ?? '' ); ?>"
                                       placeholder="Optional">
                                <p class="kq-field-hint"><?php _e( 'WhatsApp Business Account ID if required for your setup.', 'kueue-events-core' ); ?></p>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>
            </div>
        </div>

        <!-- ── C. ACCOUNT STATUS ─────────────── -->
        <div class="kq-gw-card">
            <div class="kq-gw-card-header">
                <div class="kq-card-icon">⚙️</div>
                <h3><?php _e( 'Account Settings', 'kueue-events-core' ); ?></h3>
            </div>
            <div class="kq-gw-card-body" style="padding: 10px 22px;">
                <div class="kq-gw-toggle-row">
                    <div class="kq-gw-toggle-label">
                        <strong><?php _e( 'Enable Account', 'kueue-events-core' ); ?></strong>
                        <span><?php _e( 'Disabled accounts cannot send messages.', 'kueue-events-core' ); ?></span>
                    </div>
                    <label class="kq-toggle">
                        <input type="checkbox" name="is_enabled" value="1" <?php checked( $is_enabled, 1 ); ?>>
                        <span class="kq-toggle-slider"></span>
                    </label>
                </div>
                <div class="kq-gw-toggle-row">
                    <div class="kq-gw-toggle-label">
                        <strong><?php _e( 'Set as Default', 'kueue-events-core' ); ?></strong>
                        <span><?php printf( __( 'Use this account as the primary %s gateway for all events.', 'kueue-events-core' ), $channel_label ); ?></span>
                    </div>
                    <label class="kq-toggle">
                        <input type="checkbox" name="is_default" value="1" <?php checked( $is_default, 1 ); ?>>
                        <span class="kq-toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- ── D. NOTES ───────────────────────── -->
        <div class="kq-gw-card">
            <div class="kq-gw-card-header">
                <div class="kq-card-icon">📝</div>
                <h3><?php _e( 'Notes (Optional)', 'kueue-events-core' ); ?></h3>
            </div>
            <div class="kq-gw-card-body">
                <div class="kq-gw-field">
                    <textarea name="config[notes]" placeholder="<?php _e( 'Internal notes about this account, usage limits, renewal dates, etc.', 'kueue-events-core' ); ?>"><?php echo esc_textarea( $notes ); ?></textarea>
                </div>
            </div>
        </div>

        <!-- ── E. TEST SEND ───────────────────── -->
        <?php if ( $is_edit ) : ?>
        <div class="kq-gw-card">
            <div class="kq-gw-card-header">
                <div class="kq-card-icon">🧪</div>
                <h3><?php _e( 'Test Connection', 'kueue-events-core' ); ?></h3>
            </div>
            <div class="kq-gw-card-body">
                <p style="color:#666; font-size:13px; margin-top:0;"><?php _e( 'Send a real test message to verify that this account is correctly configured. Save first if you made changes.', 'kueue-events-core' ); ?></p>
                <div class="kq-gw-grid">
                    <div class="kq-gw-field">
                        <label><?php $channel === 'sms' ? _e( 'Test Phone Number', 'kueue-events-core' ) : _e( 'Test WhatsApp Number', 'kueue-events-core' ); ?></label>
                        <input type="text" id="kq-test-phone"
                               placeholder="<?php echo $channel === 'sms' ? '+201001234567' : '+201001234567 (incl. country code)'; ?>">
                    </div>
                    <div class="kq-gw-field">
                        <label><?php _e( 'Message Text', 'kueue-events-core' ); ?></label>
                        <input type="text" id="kq-test-message"
                               value="<?php echo $channel === 'sms' ? 'Kueue test SMS ✓' : 'Kueue test WhatsApp message ✓'; ?>">
                    </div>
                </div>
                <button type="button" class="kq-gw-btn-test" id="kq-send-test"
                        data-account-id="<?php echo isset( $account->id ) ? (int) $account->id : 0; ?>"
                        data-channel="<?php echo esc_attr( $channel ); ?>">
                    📤 <?php _e( 'Send Test Message', 'kueue-events-core' ); ?>
                </button>
                <div class="kq-test-result" id="kq-test-result"></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── Footer Actions ─────────────────── -->
        <div class="kq-gw-footer">
            <button type="submit" name="kq_save_account" class="kq-gw-btn-primary">
                💾 <?php echo $is_edit ? __( 'Update Account', 'kueue-events-core' ) : __( 'Create Account', 'kueue-events-core' ); ?>
            </button>
            <a href="<?php echo admin_url( 'admin.php?page=' . $page_slug ); ?>" class="kq-gw-btn-secondary">
                <?php _e( 'Cancel', 'kueue-events-core' ); ?>
            </a>
        </div>

    </form>
</div>

<script>
jQuery(document).ready(function($){
    // Provider switching
    var $select = $('#kq-provider-select');
    function switchProvider( val ) {
        $('.kq-provider-block').removeClass('is-active');
        var $block = $('.kq-provider-block[data-provider="' + val + '"]');
        if ( $block.length ) {
            $block.addClass('is-active');
        } else {
            $('.kq-provider-block[data-provider=""]').addClass('is-active');
        }
    }
    $select.on('change', function(){ switchProvider( $(this).val() ); });
    switchProvider( $select.val() );

    // Test send
    $('#kq-send-test').on('click', function(){
        var $btn = $(this);
        var phone = $('#kq-test-phone').val().trim();
        var msg   = $('#kq-test-message').val().trim();
        var $res  = $('#kq-test-result');

        if ( !phone ) { $res.removeClass('success').addClass('error').text('Please enter a phone number.').show(); return; }

        $btn.prop('disabled', true).text('Sending…');
        $res.hide();

        $.post(ajaxurl, {
            action:     'kq_gateway_test_send',
            nonce:      '<?php echo wp_create_nonce( 'kq-nonce' ); ?>',
            account_id: $btn.data('account-id'),
            channel:    $btn.data('channel'),
            phone:      phone,
            message:    msg
        }, function(res){
            if ( res.success ) {
                $res.removeClass('error').addClass('success').text('✓ ' + ( res.data.message || 'Message sent successfully.')).show();
            } else {
                $res.removeClass('success').addClass('error').text('✗ ' + ( res.data.message || 'Failed to send.')).show();
            }
            $btn.prop('disabled', false).html('📤 <?php _e("Send Test Message","kueue-events-core"); ?>');
        }).fail(function(){
            $res.removeClass('success').addClass('error').text('Request failed — check your internet connection.').show();
            $btn.prop('disabled', false).html('📤 <?php _e("Send Test Message","kueue-events-core"); ?>');
        });
    });
});
</script>
