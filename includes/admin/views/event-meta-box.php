<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
$post_id = $post->ID;

// Pre-fetch all meta to avoid multiple calls and lint warnings
$event_type = get_post_meta($post_id, '_kq_event_type', true);
$organizer_id = get_post_meta($post_id, '_kq_organizer_id', true);
$event_status = get_post_meta($post_id, '_kq_event_status', true);
$visibility = get_post_meta($post_id, '_kq_visibility', true);

$start_date = get_post_meta($post_id, '_kq_start_date', true);
$end_date = get_post_meta($post_id, '_kq_end_date', true);
$start_time = get_post_meta($post_id, '_kq_start_time', true);
$end_time = get_post_meta($post_id, '_kq_end_time', true);
$timezone = get_post_meta($post_id, '_kq_timezone', true) ?: 'UTC';

$venue_name = get_post_meta($post_id, '_kq_venue_name', true);
$venue_address = get_post_meta($post_id, '_kq_venue_address', true);
$venue_city = get_post_meta($post_id, '_kq_venue_city', true);
$venue_country = get_post_meta($post_id, '_kq_venue_country', true);

$enable_sales = get_post_meta($post_id, '_kq_enable_sales', true);
$sales_start = get_post_meta($post_id, '_kq_sales_start_datetime', true);
$sales_end = get_post_meta($post_id, '_kq_sales_end_datetime', true);
$max_tickets = get_post_meta($post_id, '_kq_max_tickets_per_order', true);

$enable_email = get_post_meta($post_id, '_kq_enable_email_delivery', true);
$enable_whatsapp = get_post_meta($post_id, '_kq_enable_whatsapp_delivery', true);
$enable_sms = get_post_meta($post_id, '_kq_enable_sms_delivery', true);

$whatsapp_acc_id = get_post_meta($post_id, '_kq_whatsapp_gateway_account_id', true);
$sms_acc_id = get_post_meta($post_id, '_kq_sms_gateway_account_id', true);

?>
<style>
    .kq-meta-section { margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 15px; }
    .kq-meta-section h3 { margin-top: 0; }
    .kq-meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
    .kq-meta-field { margin-bottom: 10px; }
    .kq-meta-field label { display: block; font-weight: bold; margin-bottom: 5px; }
    .kq-meta-field input[type="text"], .kq-meta-field input[type="date"], .kq-meta-field input[type="time"], .kq-meta-field input[type="datetime-local"], .kq-meta-field select { width: 100%; }
</style>

<div class="kq-meta-container">
    <div class="kq-meta-section">
        <h3><?php _e( 'General Settings', 'kueue-events-core' ); ?></h3>
        <div class="kq-meta-grid">
            <div class="kq-meta-field">
                <label for="event_type"><?php _e( 'Event Type', 'kueue-events-core' ); ?></label>
                <select name="event_type" id="event_type">
                    <option value="simple_event" <?php selected( $event_type, 'simple_event' ); ?>><?php _e( 'Simple Event', 'kueue-events-core' ); ?></option>
                </select>
            </div>
            <div class="kq-meta-field">
                <label><?php _e( 'Organizer', 'kueue-events-core' ); ?></label>
                <?php if ( current_user_can( 'manage_options' ) ) : ?>
                    <select name="organizer_id">
                        <option value=""><?php _e( '-- Select Organizer --', 'kueue-events-core' ); ?></option>
                        <?php foreach ( $organizers as $org ) : ?>
                            <option value="<?php echo (int) $org->id; ?>" <?php selected( $organizer_id, $org->id ); ?>><?php echo esc_html( $org->organizer_name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else : ?>
                    <?php 
                        $org_rec = \KueueEvents\Core\Modules\Vendors\OrganizerRepository::get_by_user_id( get_current_user_id() );
                        echo $org_rec ? '<strong>' . esc_html( $org_rec->organizer_name ) . '</strong>' : '—';
                        if ( $org_rec ) : ?>
                            <input type="hidden" name="organizer_id" value="<?php echo (int) $org_rec->id; ?>">
                        <?php endif; 
                    ?>
                <?php endif; ?>
            </div>
            <div class="kq-meta-field">
                <label for="event_status"><?php _e( 'Event Status', 'kueue-events-core' ); ?></label>
                <select name="event_status" id="event_status">
                    <option value="draft" <?php selected( $event_status, 'draft' ); ?>><?php _e( 'Draft', 'kueue-events-core' ); ?></option>
                    <option value="active" <?php selected( $event_status, 'active' ); ?>><?php _e( 'Active', 'kueue-events-core' ); ?></option>
                    <option value="sold_out" <?php selected( $event_status, 'sold_out' ); ?>><?php _e( 'Sold Out', 'kueue-events-core' ); ?></option>
                    <option value="expired" <?php selected( $event_status, 'expired' ); ?>><?php _e( 'Expired', 'kueue-events-core' ); ?></option>
                </select>
            </div>
            <div class="kq-meta-field">
                <label for="visibility"><?php _e( 'Visibility', 'kueue-events-core' ); ?></label>
                <select name="visibility" id="visibility">
                    <option value="public" <?php selected( $visibility, 'public' ); ?>><?php _e( 'Public', 'kueue-events-core' ); ?></option>
                    <option value="private" <?php selected( $visibility, 'private' ); ?>><?php _e( 'Private', 'kueue-events-core' ); ?></option>
                    <option value="invitation_only" <?php selected( $visibility, 'invitation_only' ); ?>><?php _e( 'Invitation Only', 'kueue-events-core' ); ?></option>
                </select>
            </div>
        </div>
    </div>

    <div class="kq-meta-section">
        <h3><?php _e( 'Schedule', 'kueue-events-core' ); ?></h3>
        <div class="kq-meta-grid">
            <div class="kq-meta-field">
                <label for="start_date"><?php _e( 'Start Date', 'kueue-events-core' ); ?></label>
                <input type="date" name="start_date" id="start_date" value="<?php echo esc_attr( $start_date ); ?>">
            </div>
            <div class="kq-meta-field">
                <label for="end_date"><?php _e( 'End Date', 'kueue-events-core' ); ?></label>
                <input type="date" name="end_date" id="end_date" value="<?php echo esc_attr( $end_date ); ?>">
            </div>
            <div class="kq-meta-field">
                <label for="start_time"><?php _e( 'Start Time', 'kueue-events-core' ); ?></label>
                <input type="time" name="start_time" id="start_time" value="<?php echo esc_attr( $start_time ); ?>">
            </div>
            <div class="kq-meta-field">
                <label for="end_time"><?php _e( 'End Time', 'kueue-events-core' ); ?></label>
                <input type="time" name="end_time" id="end_time" value="<?php echo esc_attr( $end_time ); ?>">
            </div>
            <div class="kq-meta-field">
                <label for="timezone"><?php _e( 'Timezone', 'kueue-events-core' ); ?></label>
                <input type="text" name="timezone" id="timezone" value="<?php echo esc_attr( $timezone ); ?>">
            </div>
        </div>
    </div>

    <div class="kq-meta-section">
        <h3><?php _e( 'Venue', 'kueue-events-core' ); ?></h3>
        <div class="kq-meta-grid">
            <div class="kq-meta-field">
                <label for="venue_name"><?php _e( 'Venue Name', 'kueue-events-core' ); ?></label>
                <input type="text" name="venue_name" id="venue_name" value="<?php echo esc_attr( $venue_name ); ?>">
            </div>
            <div class="kq-meta-field">
                <label for="venue_address"><?php _e( 'Venue Address', 'kueue-events-core' ); ?></label>
                <input type="text" name="venue_address" id="venue_address" value="<?php echo esc_attr( $venue_address ); ?>">
            </div>
            <div class="kq-meta-field">
                <label for="venue_city"><?php _e( 'City', 'kueue-events-core' ); ?></label>
                <input type="text" name="venue_city" id="venue_city" value="<?php echo esc_attr( $venue_city ); ?>">
            </div>
            <div class="kq-meta-field">
                <label for="venue_country"><?php _e( 'Country', 'kueue-events-core' ); ?></label>
                <input type="text" name="venue_country" id="venue_country" value="<?php echo esc_attr( $venue_country ); ?>">
            </div>
        </div>
    </div>

    <div class="kq-meta-section">
        <h3><?php _e( 'Sales Settings', 'kueue-events-core' ); ?></h3>
        <div class="kq-meta-grid">
            <div class="kq-meta-field">
                <label><input type="checkbox" name="enable_sales" value="1" <?php checked( $enable_sales, 1 ); ?>> <?php _e( 'Enable Sales', 'kueue-events-core' ); ?></label>
            </div>
            <div class="kq-meta-field">
                <label for="sales_start_datetime"><?php _e( 'Sales Start', 'kueue-events-core' ); ?></label>
                <input type="datetime-local" name="sales_start_datetime" id="sales_start_datetime" value="<?php echo esc_attr( $sales_start ); ?>">
            </div>
            <div class="kq-meta-field">
                <label for="sales_end_datetime"><?php _e( 'Sales End', 'kueue-events-core' ); ?></label>
                <input type="datetime-local" name="sales_end_datetime" id="sales_end_datetime" value="<?php echo esc_attr( $sales_end ); ?>">
            </div>
            <div class="kq-meta-field">
                <label for="max_tickets_per_order"><?php _e( 'Max Tickets Per Order', 'kueue-events-core' ); ?></label>
                <input type="number" name="max_tickets_per_order" id="max_tickets_per_order" value="<?php echo esc_attr( $max_tickets ); ?>">
            </div>
        </div>
    </div>

    <div class="kq-meta-section">
        <h3><?php _e( 'Communication', 'kueue-events-core' ); ?></h3>
        <div class="kq-meta-field">
            <label><input type="checkbox" name="enable_email_delivery" value="1" <?php checked( $enable_email, 1 ); ?>> <?php _e( 'Enable Email Delivery', 'kueue-events-core' ); ?></label>
        </div>
        <div class="kq-meta-field">
            <label><input type="checkbox" name="enable_whatsapp_delivery" value="1" <?php checked( $enable_whatsapp, 1 ); ?>> <?php _e( 'Enable WhatsApp Delivery', 'kueue-events-core' ); ?></label>
            <select name="whatsapp_gateway_account_id">
                <option value=""><?php _e( '-- Select WhatsApp Account --', 'kueue-events-core' ); ?></option>
                <?php foreach ( $whatsapp_accounts as $acc ) : ?>
                    <option value="<?php echo (int) $acc->id; ?>" <?php selected( $whatsapp_acc_id, $acc->id ); ?>><?php echo esc_html( $acc->account_name ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="kq-meta-field">
            <label><input type="checkbox" name="enable_sms_delivery" value="1" <?php checked( $enable_sms, 1 ); ?>> <?php _e( 'Enable SMS Delivery', 'kueue-events-core' ); ?></label>
            <select name="sms_gateway_account_id">
                <option value=""><?php _e( '-- Select SMS Account --', 'kueue-events-core' ); ?></option>
                <?php foreach ( $sms_accounts as $acc ) : ?>
                    <option value="<?php echo (int) $acc->id; ?>" <?php selected( $sms_acc_id, $acc->id ); ?>><?php echo esc_html( $acc->account_name ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>
