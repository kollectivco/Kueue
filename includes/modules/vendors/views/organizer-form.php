<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
$is_edit = ( $action === 'edit' );
$org_data = [
    'organizer_name'   => '',
    'user_id'          => 0,
    'email'            => '',
    'phone'            => '',
    'status'           => 'active',
    'commission_type'  => 'percentage',
    'commission_value' => 0,
];

if ( $is_edit && isset( $organizer ) && is_object( $organizer ) ) {
    $org_data['organizer_name']   = isset( $organizer->organizer_name ) ? $organizer->organizer_name : '';
    $org_data['user_id']          = isset( $organizer->user_id ) ? (int) $organizer->user_id : 0;
    $org_data['email']            = isset( $organizer->email ) ? $organizer->email : '';
    $org_data['phone']            = isset( $organizer->phone ) ? $organizer->phone : '';
    $org_data['status']           = isset( $organizer->status ) ? $organizer->status : 'active';
    $org_data['commission_type']  = isset( $organizer->commission_type ) ? $organizer->commission_type : 'percentage';
    $org_data['commission_value'] = isset( $organizer->commission_value ) ? $organizer->commission_value : 0;
}
?>
<div class="wrap">
    <h1><?php echo $is_edit ? __( 'Edit Organizer', 'kueue-events-core' ) : __( 'Add New Organizer', 'kueue-events-core' ); ?></h1>
    
    <form method="post">
        <?php wp_nonce_field( 'kq_save_organizer_nonce' ); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="organizer_name"><?php _e( 'Organizer Name', 'kueue-events-core' ); ?></label></th>
                <td><input name="organizer_name" type="text" id="organizer_name" value="<?php echo esc_attr( $org_data['organizer_name'] ); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="user_id"><?php _e( 'Linked WordPress User', 'kueue-events-core' ); ?></label></th>
                <td>
                    <select name="user_id" id="user_id" class="regular-text">
                        <option value="0"><?php _e( '-- Select User --', 'kueue-events-core' ); ?></option>
                        <?php foreach ( $all_users as $user ) : ?>
                            <option value="<?php echo (int) $user->ID; ?>" <?php selected( $org_data['user_id'], $user->ID ); ?>><?php echo esc_html( $user->user_login ); ?> (<?php echo esc_html( $user->display_name ); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="email"><?php _e( 'Email', 'kueue-events-core' ); ?></label></th>
                <td><input name="email" type="email" id="email" value="<?php echo esc_attr( $org_data['email'] ); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="phone"><?php _e( 'Phone', 'kueue-events-core' ); ?></label></th>
                <td><input name="phone" type="text" id="phone" value="<?php echo esc_attr( $org_data['phone'] ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="status"><?php _e( 'Status', 'kueue-events-core' ); ?></label></th>
                <td>
                    <select name="status" id="status" class="regular-text">
                        <option value="active" <?php selected( $org_data['status'], 'active' ); ?>><?php _e( 'Active', 'kueue-events-core' ); ?></option>
                        <option value="pending" <?php selected( $org_data['status'], 'pending' ); ?>><?php _e( 'Pending', 'kueue-events-core' ); ?></option>
                        <option value="suspended" <?php selected( $org_data['status'], 'suspended' ); ?>><?php _e( 'Suspended', 'kueue-events-core' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="commission_type"><?php _e( 'Commission Type', 'kueue-events-core' ); ?></label></th>
                <td>
                    <select name="commission_type" id="commission_type" class="regular-text">
                        <option value="fixed" <?php selected( $org_data['commission_type'], 'fixed' ); ?>><?php _e( 'Fixed', 'kueue-events-core' ); ?></option>
                        <option value="percentage" <?php selected( $org_data['commission_type'], 'percentage' ); ?>><?php _e( 'Percentage', 'kueue-events-core' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="commission_value"><?php _e( 'Commission Value', 'kueue-events-core' ); ?></label></th>
                <td><input name="commission_value" type="number" step="0.01" id="commission_value" value="<?php echo esc_attr( $org_data['commission_value'] ); ?>" class="regular-text"></td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="kq_save_organizer" id="submit" class="button button-primary" value="<?php _e( 'Save Organizer', 'kueue-events-core' ); ?>">
        </p>
    </form>
</div>
