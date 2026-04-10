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

    /* Searchable Select Stylings */
    .kq-search-wrap { position: relative; display: flex; gap: 10px; }
    .kq-search-input { flex-grow: 1; }
    .kq-search-results { position: absolute; top: 100%; left: 0; right: 50px; background: #fff; border: 1px solid #ccd0d4; z-index: 100; max-height: 200px; overflow-y: auto; display: none; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .kq-search-item { padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #eee; }
    .kq-search-item:hover { background: #f0f0f1; }
    .kq-selected-value { margin-top: 8px; font-weight: 700; color: #2271b1; display: flex; align-items: center; gap: 10px; }
    .kq-clear-selection { cursor: pointer; color: #ff3131; font-size: 11px; }

    /* Modal Styling */
    .kq-modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 99999; display: none; align-items: center; justify-content: center; }
    .kq-modal-content { background: #fff; width: 500px; max-width: 90%; max-height: 90vh; overflow-y: auto; border-radius: 8px; padding: 30px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
    .kq-modal-content h2 { margin-top: 0; border-bottom: 2px solid #ff3131; padding-bottom: 15px; margin-bottom: 20px; font-size: 20px; }
    .kq-modal-close { position: absolute; top: 15px; right: 20px; font-size: 24px; cursor: pointer; color: #888; }
    .kq-modal-footer { margin-top: 25px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee; padding-top: 20px; }

    /* Layout Picker */
    .kq-layout-picker { display: flex; gap: 16px; margin-top: 4px; }
    .kq-layout-card { flex: 1; border: 2px solid #ddd; border-radius: 10px; padding: 0; cursor: pointer; transition: border-color .15s, box-shadow .15s; overflow: hidden; background: #fff; }
    .kq-layout-card:hover { border-color: #aaa; }
    .kq-layout-card.is-selected { border-color: #ff3131; box-shadow: 0 0 0 3px rgba(255,49,49,0.12); }
    .kq-layout-card__preview { width: 100%; height: 90px; display: block; background: #f5f5f5; overflow: hidden; }
    .kq-layout-card__preview svg { width: 100%; height: 100%; }
    .kq-layout-card__body { padding: 10px 12px 12px; }
    .kq-layout-card__badge { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #888; margin-bottom: 3px; }
    .kq-layout-card.is-selected .kq-layout-card__badge { color: #ff3131; }
    .kq-layout-card__title { font-size: 13px; font-weight: 700; color: #1a1a1a; margin-bottom: 3px; }
    .kq-layout-card__desc { font-size: 11px; color: #888; line-height: 1.5; }
    .kq-layout-card__check { display: none; width: 16px; height: 16px; border-radius: 50%; background: #ff3131; color: #fff; font-size: 10px; align-items: center; justify-content: center; }
    .kq-layout-card.is-selected .kq-layout-card__check { display: inline-flex; }
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
                    <div class="kq-search-wrap" data-type="organizer">
                        <input type="text" class="kq-search-trigger" placeholder="<?php _e( 'Search organizers...', 'kueue-events-core' ); ?>">
                        <button type="button" class="button kq-modal-trigger" data-modal="modal-organizer"><?php _e( 'Add New', 'kueue-events-core' ); ?></button>
                        <div class="kq-search-results"></div>
                        <input type="hidden" name="organizer_id" class="kq-id-input" value="<?php echo (int) $organizer_id; ?>">
                    </div>
                    <div class="kq-selected-value" id="selected-organizer">
                        <?php if ($organizer_id) : ?>
                            <span><?php echo esc_html($organizer_name_display); ?></span>
                            <span class="kq-clear-selection" data-target="organizer">&times; <?php _e( 'Clear', 'kueue-events-core' ); ?></span>
                        <?php endif; ?>
                    </div>
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
            <div class="kq-meta-field" style="grid-column: span 2;">
                <label><?php _e( 'Event Layout', 'kueue-events-core' ); ?></label>
                <input type="hidden" name="event_layout" id="event_layout" value="<?php echo esc_attr( $event_layout ?: 'layout_1' ); ?>">

                <div class="kq-layout-picker">

                    <!-- Layout 1: Classic -->
                    <div class="kq-layout-card <?php echo ( ($event_layout ?: 'layout_1') === 'layout_1' ) ? 'is-selected' : ''; ?>" data-layout="layout_1">
                        <div class="kq-layout-card__preview">
                            <svg viewBox="0 0 240 90" xmlns="http://www.w3.org/2000/svg">
                                <!-- Hero bar -->
                                <rect x="0" y="0" width="240" height="36" fill="#2a2a2c" rx="0"/>
                                <rect x="12" y="10" width="80" height="8" fill="#ff3131" rx="2" opacity=".7"/>
                                <rect x="12" y="22" width="120" height="6" fill="#fff" rx="2" opacity=".4"/>
                                <!-- Two-column body -->
                                <rect x="0" y="40" width="155" height="50" fill="#f5f5f5"/>
                                <rect x="8" y="48" width="90" height="5" fill="#ddd" rx="2"/>
                                <rect x="8" y="57" width="130" height="4" fill="#eee" rx="2"/>
                                <rect x="8" y="65" width="110" height="4" fill="#eee" rx="2"/>
                                <!-- Sidebar -->
                                <rect x="160" y="40" width="80" height="50" fill="#fff" rx="0"/>
                                <rect x="165" y="46" width="70" height="4" fill="#ff3131" rx="2" opacity=".5"/>
                                <rect x="165" y="54" width="50" height="3" fill="#ddd" rx="2"/>
                                <rect x="165" y="61" width="50" height="3" fill="#ddd" rx="2"/>
                                <rect x="165" y="72" width="70" height="10" fill="#2a2a2c" rx="3"/>
                            </svg>
                        </div>
                        <div class="kq-layout-card__body">
                            <div class="kq-layout-card__badge">
                                <span class="kq-layout-card__check">✓</span>
                                Layout 1
                            </div>
                            <div class="kq-layout-card__title"><?php _e( 'Classic Hero + Sidebar', 'kueue-events-core' ); ?></div>
                            <div class="kq-layout-card__desc"><?php _e( 'Large cover image, info on the left, sticky ticket box on the right.', 'kueue-events-core' ); ?></div>
                        </div>
                    </div>

                    <!-- Layout 2: Modern -->
                    <div class="kq-layout-card <?php echo ( ($event_layout ?: 'layout_1') === 'layout_2' ) ? 'is-selected' : ''; ?>" data-layout="layout_2">
                        <div class="kq-layout-card__preview">
                            <svg viewBox="0 0 240 90" xmlns="http://www.w3.org/2000/svg">
                                <!-- Centered header -->
                                <rect x="0" y="0" width="240" height="28" fill="#f8f8f8"/>
                                <rect x="70" y="6" width="100" height="8" fill="#1a1a1a" rx="2" opacity=".7"/>
                                <rect x="90" y="18" width="60" height="4" fill="#ddd" rx="2"/>
                                <!-- Full-width image -->
                                <rect x="8" y="32" width="224" height="26" fill="#2a2a2c" rx="4"/>
                                <rect x="20" y="39" width="200" height="4" fill="#666" rx="2" opacity=".5"/>
                                <!-- Dark tickets bar -->
                                <rect x="0" y="62" width="240" height="28" fill="#1a1a1c"/>
                                <rect x="8" y="68" width="40" height="4" fill="#ff3131" rx="2" opacity=".7"/>
                                <rect x="8" y="76" width="60" height="6" fill="#333" rx="2"/>
                                <rect x="160" y="66" width="72" height="18" fill="#2a2a2c" rx="3"/>
                                <rect x="178" y="72" width="36" height="6" fill="#ff3131" rx="2" opacity=".7"/>
                            </svg>
                        </div>
                        <div class="kq-layout-card__body">
                            <div class="kq-layout-card__badge">
                                <span class="kq-layout-card__check">✓</span>
                                Layout 2
                            </div>
                            <div class="kq-layout-card__title"><?php _e( 'Modern Editorial', 'kueue-events-core' ); ?></div>
                            <div class="kq-layout-card__desc"><?php _e( 'Centered title, full-width image, dark premium ticket zone below.', 'kueue-events-core' ); ?></div>
                        </div>
                    </div>

                </div>
                <span class="description" style="margin-top: 10px; display:block;"><?php _e( 'Choose how this event appears on the public page. Saved automatically on publish.', 'kueue-events-core' ); ?></span>

                <script>
                jQuery(document).ready(function($){
                    $('.kq-layout-card').on('click', function(){
                        $('.kq-layout-card').removeClass('is-selected');
                        $(this).addClass('is-selected');
                        $('#event_layout').val($(this).data('layout'));
                    });
                });
                </script>
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
            <div class="kq-search-wrap" data-type="venue">
                <input type="text" class="kq-search-trigger" placeholder="<?php _e( 'Search venues...', 'kueue-events-core' ); ?>">
                <button type="button" class="button kq-modal-trigger" data-modal="modal-venue"><?php _e( 'Add New', 'kueue-events-core' ); ?></button>
                <div class="kq-search-results"></div>
                <input type="hidden" name="venue_id" class="kq-id-input" value="<?php echo (int) $venue_id; ?>">
            </div>
            <div class="kq-selected-value" id="selected-venue">
                <?php if ($venue_id) : ?>
                    <span><?php echo esc_html($venue_name_display); ?></span>
                    <span class="kq-clear-selection" data-target="venue">&times; <?php _e( 'Clear', 'kueue-events-core' ); ?></span>
                <?php endif; ?>
            </div>
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
                <label for="event_logo_id"><?php _e( 'Event Logo', 'kueue-events-core' ); ?></label>
                <div class="kq-image-field">
                    <input type="hidden" name="event_logo_id" id="event_logo_id" value="<?php echo esc_attr( $event_logo_id ); ?>">
                    <div class="kq-image-preview" style="margin-bottom: 10px; max-width: 150px;">
                        <?php if ( $logo_url ) : ?>
                            <img src="<?php echo esc_url( $logo_url ); ?>" style="max-width: 100%; border: 1px solid #ddd; border-radius: 4px;">
                        <?php endif; ?>
                    </div>
                    <button type="button" class="button kq-upload-btn" data-target="event_logo_id"><?php _e( 'Select Logo', 'kueue-events-core' ); ?></button>
                    <button type="button" class="button kq-remove-btn" <?php echo !$logo_url ? 'style="display:none;"' : ''; ?>><?php _e( 'Remove', 'kueue-events-core' ); ?></button>
                </div>
                <span class="description"><?php _e( 'The square logo for list views.', 'kueue-events-core' ); ?></span>
            </div>
            <div class="kq-meta-field">
                <label for="cover_image_id"><?php _e( 'Cover Image', 'kueue-events-core' ); ?></label>
                <div class="kq-image-field">
                    <input type="hidden" name="cover_image_id" id="cover_image_id" value="<?php echo esc_attr( $cover_image_id ); ?>">
                    <div class="kq-image-preview" style="margin-bottom: 10px; max-width: 300px;">
                        <?php if ( $cover_url ) : ?>
                            <img src="<?php echo esc_url( $cover_url ); ?>" style="max-width: 100%; border: 1px solid #ddd; border-radius: 4px;">
                        <?php endif; ?>
                    </div>
                    <button type="button" class="button kq-upload-btn" data-target="cover_image_id"><?php _e( 'Select Cover', 'kueue-events-core' ); ?></button>
                    <button type="button" class="button kq-remove-btn" <?php echo !$cover_url ? 'style="display:none;"' : ''; ?>><?php _e( 'Remove', 'kueue-events-core' ); ?></button>
                </div>
                <span class="description"><?php _e( 'Large banner image for the event page.', 'kueue-events-core' ); ?></span>
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

<!-- Modal: Add New Venue -->
<div class="kq-modal-overlay" id="modal-venue">
    <div class="kq-modal-content">
        <span class="kq-modal-close">&times;</span>
        <h2><?php _e( 'Quick Create Venue', 'kueue-events-core' ); ?></h2>
        <div class="kq-meta-field">
            <label><?php _e( 'Venue Name', 'kueue-events-core' ); ?> *</label>
            <input type="text" id="nv-name">
        </div>
        <div class="kq-meta-field">
            <label><?php _e( 'Address', 'kueue-events-core' ); ?></label>
            <input type="text" id="nv-address">
        </div>
        <div class="kq-meta-grid">
            <div class="kq-meta-field">
                <label><?php _e( 'City', 'kueue-events-core' ); ?></label>
                <input type="text" id="nv-city">
            </div>
            <div class="kq-meta-field">
                <label><?php _e( 'Country', 'kueue-events-core' ); ?></label>
                <input type="text" id="nv-country">
            </div>
        </div>
        <div class="kq-meta-field">
            <label><?php _e( 'Google Maps URL', 'kueue-events-core' ); ?></label>
            <input type="text" id="nv-maps">
        </div>
        <div class="kq-meta-field">
            <label><?php _e( 'Brief Description', 'kueue-events-core' ); ?></label>
            <textarea id="nv-description" style="width:100%; border:1px solid #ccd0d4; border-radius:4px;" rows="3"></textarea>
        </div>
        <div class="kq-modal-footer">
            <button type="button" class="button" id="nv-cancel"><?php _e( 'Cancel', 'kueue-events-core' ); ?></button>
            <button type="button" class="button button-primary" id="nv-save"><?php _e( 'Create Venue', 'kueue-events-core' ); ?></button>
        </div>
    </div>
</div>

<!-- Modal: Add New Organizer -->
<div class="kq-modal-overlay" id="modal-organizer">
    <div class="kq-modal-content">
        <span class="kq-modal-close">&times;</span>
        <h2><?php _e( 'Quick Create Organizer', 'kueue-events-core' ); ?></h2>
        <div class="kq-meta-field">
            <label><?php _e( 'Organizer Name', 'kueue-events-core' ); ?> *</label>
            <input type="text" id="no-name">
        </div>
        <div class="kq-meta-field">
            <label><?php _e( 'Email Address', 'kueue-events-core' ); ?> *</label>
            <input type="email" id="no-email">
        </div>
        <div class="kq-meta-field">
            <label><?php _e( 'Phone Number', 'kueue-events-core' ); ?></label>
            <input type="text" id="no-phone">
        </div>
        <div class="kq-meta-grid">
            <div class="kq-meta-field">
                <label><?php _e( 'Commission Type', 'kueue-events-core' ); ?></label>
                <select id="no-comm-type">
                    <option value="percentage">Percentage (%)</option>
                    <option value="fixed">Fixed (EGP)</option>
                </select>
            </div>
            <div class="kq-meta-field">
                <label><?php _e( 'Value', 'kueue-events-core' ); ?></label>
                <input type="number" id="no-comm-value" value="0">
            </div>
        </div>
        <div class="kq-modal-footer">
            <button type="button" class="button" id="no-cancel"><?php _e( 'Cancel', 'kueue-events-core' ); ?></button>
            <button type="button" class="button button-primary" id="no-save"><?php _e( 'Create Organizer', 'kueue-events-core' ); ?></button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($){
    // 1. Image Uploader Logic (Keep existing)
    let mediaFrame;
    $('.kq-upload-btn').on('click', function(e){
        e.preventDefault();
        const $btn = $(this);
        const targetId = $btn.data('target');
        const $targetInput = $('#' + targetId);
        const $preview = $btn.closest('.kq-image-field').find('.kq-image-preview');
        const $removeBtn = $btn.closest('.kq-image-field').find('.kq-remove-btn');
        mediaFrame = wp.media({ title: 'Select Image', button: { text: 'Use Image' }, multiple: false });
        mediaFrame.on('select', function() {
            const attachment = mediaFrame.state().get('selection').first().toJSON();
            $targetInput.val(attachment.id);
            $preview.html('<img src="' + attachment.url + '" style="max-width: 100%; border: 1px solid #ddd; border-radius: 4px;">');
            $removeBtn.show();
        });
        mediaFrame.open();
    });
    $('.kq-remove-btn').on('click', function(e){
        e.preventDefault();
        const $btn = $(this);
        const $wrapper = $btn.closest('.kq-image-field');
        $wrapper.find('input[type="hidden"]').val('');
        $wrapper.find('.kq-image-preview').html('');
        $btn.hide();
    });

    // 2. Search Logic
    let searchTimer;
    $('.kq-search-trigger').on('keyup', function(){
        const $input = $(this);
        const $wrap = $input.closest('.kq-search-wrap');
        const $results = $wrap.find('.kq-search-results');
        const type = $wrap.data('type');
        const term = $input.val();

        clearTimeout(searchTimer);
        if (term.length < 2) {
            $results.hide();
            return;
        }

        searchTimer = setTimeout(function(){
            $.post(ajaxurl, {
                action: 'kq_search_' + type + 's',
                nonce: '<?php echo wp_create_nonce("kq-nonce"); ?>',
                term: term
            }, function(res){
                if (res.success && res.data.length > 0) {
                    let html = '';
                    res.data.forEach(function(item){
                        html += '<div class="kq-search-item" data-id="'+item.id+'" data-text="'+item.text+'">'+item.text+'</div>';
                    });
                    $results.html(html).show();
                } else {
                    $results.html('<div class="kq-search-item">No results found</div>').show();
                }
            });
        }, 300);
    });

    $(document).on('click', '.kq-search-item', function(){
        const $item = $(this);
        if (!$item.data('id')) return;
        
        const $wrap = $item.closest('.kq-search-wrap');
        const type = $wrap.data('type');
        const id = $item.data('id');
        const text = $item.data('text');

        $wrap.find('.kq-id-input').val(id);
        $('#selected-' + type).html('<span>'+text+'</span><span class="kq-clear-selection" data-target="'+type+'">&times; Clear</span>');
        $wrap.find('.kq-search-results').hide();
        $wrap.find('.kq-search-trigger').val('');
    });

    $(document).on('click', '.kq-clear-selection', function(){
        const type = $(this).data('target');
        const $wrap = $('.kq-search-wrap[data-type="'+type+'"]');
        $wrap.find('.kq-id-input').val('');
        $('#selected-' + type).html('');
    });

    // Close results on outside click
    $(document).on('click', function(e){
        if (!$(e.target).closest('.kq-search-wrap').length) {
            $('.kq-search-results').hide();
        }
    });

    // 3. Modal Logic
    $('.kq-modal-trigger').on('click', function(){
        const modalId = $(this).data('modal');
        $('#' + modalId).css('display', 'flex');
    });

    $('.kq-modal-close, #nv-cancel, #no-cancel').on('click', function(){
        $('.kq-modal-overlay').hide();
    });

    // Quick Save Venue
    $('#nv-save').on('click', function(){
        const $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');

        $.post(ajaxurl, {
            action: 'kq_create_venue',
            nonce: '<?php echo wp_create_nonce("kq-nonce"); ?>',
            name: $('#nv-name').val(),
            address: $('#nv-address').val(),
            city: $('#nv-city').val(),
            country: $('#nv-country').val(),
            maps_url: $('#nv-maps').val(),
            description: $('#nv-description').val()
        }, function(res){
            if (res.success) {
                $('.kq-search-wrap[data-type="venue"] .kq-id-input').val(res.data.id);
                $('#selected-venue').html('<span>'+res.data.text+'</span><span class="kq-clear-selection" data-target="venue">&times; Clear</span>');
                $('.kq-modal-overlay').hide();
                // Reset form
                $('#modal-venue input, #modal-venue textarea').val('');
            } else {
                alert(res.data.message || 'Error saving venue');
            }
            $btn.prop('disabled', false).text('Create Venue');
        });
    });

    // Quick Save Organizer
    $('#no-save').on('click', function(){
        const $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');

        $.post(ajaxurl, {
            action: 'kq_create_organizer',
            nonce: '<?php echo wp_create_nonce("kq-nonce"); ?>',
            name: $('#no-name').val(),
            email: $('#no-email').val(),
            phone: $('#no-phone').val(),
            commission_type: $('#no-comm-type').val(),
            commission_value: $('#no-comm-value').val()
        }, function(res){
            if (res.success) {
                $('.kq-search-wrap[data-type="organizer"] .kq-id-input').val(res.data.id);
                $('#selected-organizer').html('<span>'+res.data.text+'</span><span class="kq-clear-selection" data-target="organizer">&times; Clear</span>');
                $('.kq-modal-overlay').hide();
                // Reset form
                $('#modal-organizer input').val('');
            } else {
                alert(res.data.message || 'Error saving organizer');
            }
            $btn.prop('disabled', false).text('Create Organizer');
        });
    });
});
</script>
