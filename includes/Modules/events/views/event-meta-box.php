<style>
    .kq-meta-section { margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
    .kq-meta-section:last-child { border-bottom:none; }
    .kq-meta-section h3 { margin-top: 0; font-size: 16px; border-left: 4px solid #ff3131; padding-left: 10px; margin-bottom: 20px; }
    .kq-meta-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .kq-meta-field { margin-bottom: 15px; }
    .kq-meta-field label { display: block; font-weight: 700; margin-bottom: 8px; font-size: 13px; color: #333; }
    .kq-meta-field input[type="text"], .kq-meta-field input[type="number"], .kq-meta-field input[type="date"], .kq-meta-field input[type="time"], .kq-meta-field input[type="datetime-local"], .kq-meta-field select { width: 100%; padding: 8px; border: 1px solid #ccd0d4; border-radius: 4px; box-shadow: inset 0 1px 2px rgba(0,0,0,.07); }
    .kq-meta-field input[type="checkbox"] { margin-right: 8px; vertical-align: middle; }
    .kq-meta-field .description { display: block; font-size: 11px; color: #888; margin-top: 5px; font-style: italic; }
</style>

<div class="kq-meta-container" style="padding: 10px;">
    <!-- General Section -->
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

    <!-- Venue Section -->
    <div class="kq-meta-section">
        <h3><?php _e( 'Venue & Location', 'kueue-events-core' ); ?></h3>
        <div class="kq-meta-field" style="margin-bottom: 20px;">
            <label for="venue_id"><?php _e( 'Linked Venue', 'kueue-events-core' ); ?></label>
            <select name="venue_id" id="venue_id">
                <option value=""><?php _e( '-- Use Custom Venue (or None) --', 'kueue-events-core' ); ?></option>
                <?php foreach ( $venues as $v ) : ?>
                    <option value="<?php echo (int) $v->ID; ?>" <?php selected( $venue_id, $v->ID ); ?>><?php echo esc_html( $v->post_title ); ?></option>
                <?php endforeach; ?>
            </select>
            <span class="description"><?php _e( 'Linking to a native Venue will override the custom address below on the public page.', 'kueue-events-core' ); ?></span>
        </div>

        <div class="kq-meta-grid">
            <div class="kq-meta-field">
                <label for="venue_name"><?php _e( 'Custom Venue Name', 'kueue-events-core' ); ?></label>
                <input type="text" name="venue_name" id="venue_name" value="<?php echo esc_attr( $venue_name ); ?>" placeholder="e.g. Cairo International Stadium">
            </div>
            <div class="kq-meta-field">
                <label for="venue_address"><?php _e( 'Custom Venue Address', 'kueue-events-core' ); ?></label>
                <input type="text" name="venue_address" id="venue_address" value="<?php echo esc_attr( $venue_address ); ?>" placeholder="Full street address">
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

    <!-- Branding Section -->
    <div class="kq-meta-section">
        <h3><?php _e( 'Event Branding', 'kueue-events-core' ); ?></h3>
        <div class="kq-meta-grid">
            <div class="kq-meta-field">
                <label for="event_logo_id"><?php _e( 'Event Logo (Media ID)', 'kueue-events-core' ); ?></label>
                <input type="number" name="event_logo_id" id="event_logo_id" value="<?php echo esc_attr( $event_logo_id ); ?>">
                <span class="description"><?php _e( 'The round/square logo for lists.', 'kueue-events-core' ); ?></span>
            </div>
            <div class="kq-meta-field">
                <label for="cover_image_id"><?php _e( 'Cover Image (Media ID)', 'kueue-events-core' ); ?></label>
                <input type="number" name="cover_image_id" id="cover_image_id" value="<?php echo esc_attr( $cover_image_id ); ?>">
                <span class="description"><?php _e( 'Large banner image for event page.', 'kueue-events-core' ); ?></span>
            </div>
            <div class="kq-meta-field">
                <label for="accent_color"><?php _e( 'Accent Color', 'kueue-events-core' ); ?></label>
                <input type="color" name="accent_color" id="accent_color" value="<?php echo esc_attr( $accent_color ); ?>" style="height: 40px;">
                <span class="description"><?php _e( 'Primary theme color for buttons and highlights.', 'kueue-events-core' ); ?></span>
            </div>
        </div>
    </div>

    <!-- Sales Settings -->
    <div class="kq-meta-section">
        <h3><?php _e( 'Sales & Tickets Settings', 'kueue-events-core' ); ?></h3>
        <div class="kq-meta-grid">
            <div class="kq-meta-field">
                <label><input type="checkbox" name="enable_sales" value="1" <?php checked( $enable_sales, 1 ); ?>> <?php _e( 'Open Sales Publicly', 'kueue-events-core' ); ?></label>
                <span class="description"><?php _e( 'Check this to allow users to buy tickets.', 'kueue-events-core' ); ?></span>
            </div>
            <div class="kq-meta-field">
                <label for="max_tickets_per_order"><?php _e( 'Max Tickets Per Order', 'kueue-events-core' ); ?></label>
                <input type="number" name="max_tickets_per_order" id="max_tickets_per_order" value="<?php echo esc_attr( $max_tickets ); ?>">
            </div>
            <div class="kq-meta-field">
                <label for="sales_start_datetime"><?php _e( 'Sales Start Date/Time', 'kueue-events-core' ); ?></label>
                <input type="datetime-local" name="sales_start_datetime" id="sales_start_datetime" value="<?php echo esc_attr( $sales_start ); ?>">
            </div>
            <div class="kq-meta-field">
                <label for="sales_end_datetime"><?php _e( 'Sales End Date/Time', 'kueue-events-core' ); ?></label>
                <input type="datetime-local" name="sales_end_datetime" id="sales_end_datetime" value="<?php echo esc_attr( $sales_end ); ?>">
            </div>
        </div>
    </div>

    <!-- Communication -->
    <div class="kq-meta-section">
        <h3><?php _e( 'Automatic Delivery Channels', 'kueue-events-core' ); ?></h3>
        <div class="kq-meta-grid">
            <div class="kq-meta-field">
                <label><input type="checkbox" name="enable_email_delivery" value="1" <?php checked( $enable_email, 1 ); ?>> <?php _e( 'Email Tickets', 'kueue-events-core' ); ?></label>
            </div>
            <div class="kq-meta-field">
                <label><input type="checkbox" name="enable_whatsapp_delivery" value="1" <?php checked( $enable_whatsapp, 1 ); ?>> <?php _e( 'WhatsApp Tickets', 'kueue-events-core' ); ?></label>
                <select name="whatsapp_gateway_account_id">
                    <option value=""><?php _e( '-- Select Account --', 'kueue-events-core' ); ?></option>
                    <?php foreach ( $whatsapp_accounts as $acc ) : ?>
                        <option value="<?php echo (int) $acc->id; ?>" <?php selected( $whatsapp_acc_id, $acc->id ); ?>><?php echo esc_html( $acc->account_name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="kq-meta-field">
                <label><input type="checkbox" name="enable_sms_delivery" value="1" <?php checked( $enable_sms, 1 ); ?>> <?php _e( 'SMS Tickets', 'kueue-events-core' ); ?></label>
                <select name="sms_gateway_account_id">
                    <option value=""><?php _e( '-- Select Account --', 'kueue-events-core' ); ?></option>
                    <?php foreach ( $sms_accounts as $acc ) : ?>
                        <option value="<?php echo (int) $acc->id; ?>" <?php selected( $sms_acc_id, $acc->id ); ?>><?php echo esc_html( $acc->account_name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Addons & Advanced -->
    <div class="kq-meta-section">
        <h3><?php _e( 'Advanced Addons & Re-entry', 'kueue-events-core' ); ?></h3>
        <div class="kq-meta-grid">
            <div class="kq-meta-field">
                <label><input type="checkbox" name="enable_bookings" value="1" <?php checked( $enable_bookings, 1 ); ?>> <?php _e( 'Enable Bookings / Slots', 'kueue-events-core' ); ?></label>
                <select name="booking_mode">
                    <option value="slots" <?php selected( $booking_mode, 'slots' ); ?>><?php _e( 'By Time Slots', 'kueue-events-core' ); ?></option>
                    <option value="daily" <?php selected( $booking_mode, 'daily' ); ?>><?php _e( 'Daily Capacity', 'kueue-events-core' ); ?></option>
                </select>
            </div>
            <div class="kq-meta-field">
                <label><input type="checkbox" name="enable_seating" value="1" <?php checked( $enable_seating, 1 ); ?>> <?php _e( 'Enable Seating Map', 'kueue-events-core' ); ?></label>
                <select name="seating_map_id">
                    <option value=""><?php _e( '-- Select Map --', 'kueue-events-core' ); ?></option>
                    <?php foreach ( $seating_maps as $map ) : ?>
                        <option value="<?php echo (int) $map->id; ?>" <?php selected( $seating_map_id, $map->id ); ?>><?php echo esc_html( $map->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="kq-meta-field">
                <label><input type="checkbox" name="enable_wallets" value="1" <?php checked( $enable_wallets, 1 ); ?>> <?php _e( 'Google/Apple Wallets', 'kueue-events-core' ); ?></label>
            </div>
            <div class="kq-meta-field">
                <label><input type="checkbox" name="allow_reentry" value="1" <?php checked( $allow_reentry, 1 ); ?>> <?php _e( 'Allow Scanner Re-entry', 'kueue-events-core' ); ?></label>
            </div>
        </div>
    </div>
</div>
