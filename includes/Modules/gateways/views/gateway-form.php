<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
use KueueEvents\Core\Helpers\EncryptionHelper;
$available_providers = \KueueEvents\Core\Modules\Gateways\GatewayManager::get_available_providers();

// Safe property extraction
$account_name = '';
$provider_slug = '';
$config = [];
$is_enabled = 1;
$is_default = 0;

if ( isset( $account ) && is_object( $account ) ) {
    $account_name = isset( $account->account_name ) ? $account->account_name : '';
    $provider_slug = isset( $account->provider ) ? $account->provider : '';
    $config = isset( $account->config ) ? (array) $account->config : [];
    $is_enabled = isset( $account->is_enabled ) ? (int) $account->is_enabled : 0;
    $is_default = isset( $account->is_default ) ? (int) $account->is_default : 0;
}

$is_edit = ( $action === 'edit' );
?>
<div class="wrap">
    <h1><?php echo $is_edit ? __( 'Edit Gateway Account', 'kueue-events-core' ) : __( 'Add New Gateway Account', 'kueue-events-core' ); ?></h1>
    
    <form method="post">
        <?php wp_nonce_field( 'kq_save_account_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="account_name"><?php _e( 'Account Name', 'kueue-events-core' ); ?></label></th>
                <td><input name="account_name" type="text" id="account_name" value="<?php echo esc_attr( $account_name ); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="provider"><?php _e( 'Provider', 'kueue-events-core' ); ?></label></th>
                <td>
                    <select name="provider" id="provider" class="regular-text">
                        <?php foreach ( $available_providers as $slug => $prov ) : ?>
                            <?php if ( $prov['channel'] === $channel ) : ?>
                                <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $provider_slug, $slug ); ?>><?php echo esc_html( $prov['name'] ); ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            
            <?php if ( $channel === 'sms' ) : ?>
                <tr>
                    <th scope="row"><?php _e( 'SMS Credentials', 'kueue-events-core' ); ?></th>
                    <td>
                        <p><label><?php _e( 'Username', 'kueue-events-core' ); ?></label><br>
                        <input name="config[username]" type="text" value="<?php echo ( !empty($config['username']) ) ? EncryptionHelper::mask( EncryptionHelper::decrypt($config['username']) ) : ''; ?>" class="regular-text"></p>
                        
                        <p><label><?php _e( 'Password', 'kueue-events-core' ); ?></label><br>
                        <input name="config[password]" type="password" value="<?php echo ( !empty($config['password']) ) ? '********' : ''; ?>" class="regular-text"></p>
                        
                        <p><label><?php _e( 'Sender ID', 'kueue-events-core' ); ?></label><br>
                        <input name="config[sender]" type="text" value="<?php echo isset($config['sender']) ? esc_attr( $config['sender'] ) : ''; ?>" class="regular-text"></p>
                    </td>
                </tr>
            <?php else : ?>
                <tr>
                    <th scope="row"><?php _e( 'WhatsApp Credentials', 'kueue-events-core' ); ?></th>
                    <td>
                        <p><label><?php _e( 'Phone Number ID', 'kueue-events-core' ); ?></label><br>
                        <input name="config[phone_number_id]" type="text" value="<?php echo isset($config['phone_number_id']) ? esc_attr( $config['phone_number_id'] ) : ''; ?>" class="regular-text"></p>
                        
                        <p><label><?php _e( 'Access Token', 'kueue-events-core' ); ?></label><br>
                        <input name="config[access_token]" type="password" value="<?php echo ( !empty($config['access_token']) ) ? '********' : ''; ?>" class="regular-text"></p>
                    </td>
                </tr>
            <?php endif; ?>

            <tr>
                <th scope="row"><label for="is_enabled"><?php _e( 'Enabled', 'kueue-events-core' ); ?></label></th>
                <td><input name="is_enabled" type="checkbox" id="is_enabled" <?php checked( $is_enabled, 1 ); ?>></td>
            </tr>
            <tr>
                <th scope="row"><label for="is_default"><?php _e( 'Default for Channel', 'kueue-events-core' ); ?></label></th>
                <td><input name="is_default" type="checkbox" id="is_default" <?php checked( $is_default, 1 ); ?>></td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="kq_save_account" id="submit" class="button button-primary" value="<?php _e( 'Save Account', 'kueue-events-core' ); ?>">
        </p>
    </form>
</div>
