<?php
/**
 * Event settings template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="fooevents_options" class="panel woocommerce_options_panel fooevents_options_panel">
<h2><?php esc_attr_e( 'Event Details', 'woocommerce-events' ); ?></h2>
<div class="options_group">
	<p class="form-field">
		<label for="WooCommerceEventsProductIsEvent"><?php esc_attr_e( 'Is this product an event?', 'woocommerce-events' ); ?></label>
		<input type="checkbox" name="WooCommerceEventsEvent" id="WooCommerceEventsProductIsEvent" value="1" <?php checked( $woocommerce_events_event, 'Event' ); ?> />
		<img class="help_tip" data-tip="<?php esc_attr_e( 'This option enables event and ticketing functionality.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</p>
</div>
	<div id="WooCommerceEventsForm">
	<div class="options_group" id ="WooCommerceEventsTypeHolder">
		<p class="form-field">
			<label><?php esc_attr_e( 'Event type:', 'woocommerce-events' ); ?></label>
			<span class="fooevents-event-type">
			<label for="WooCommerceEventsTypeSingle" class="fooevents-options-inner-label"><input type="radio" id="WooCommerceEventsTypeSingle" name="WooCommerceEventsType" value="single" <?php echo ( 'single' === $woocommerce_events_type ) ? 'CHECKED' : ''; ?>> <?php esc_attr_e( 'Single', 'woocommerce-events' ); ?></label><img class="help_tip" data-tip="<?php esc_attr_e( 'Standard one-day events.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" /><br>
			<label for="WooCommerceEventsTypeSequential" class="fooevents-options-inner-label"><input type="radio" id="WooCommerceEventsTypeSequential" name="WooCommerceEventsType" value="sequential" <?php echo ( 'sequential' === $woocommerce_events_type ) ? 'CHECKED' : ''; ?> <?php echo( true === $multi_day_enabled ) ? '' : 'DISABLED'; ?>> <?php esc_attr_e( 'Sequential days', 'woocommerce-events' ); ?></label><a href="https://www.fooevents.com/products/fooevents-multi-day/"><img src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" class="help_tip" data-tip="<?php esc_attr_e( 'Events that occur over multiple days and repeat for a set number of sequential days. Note: Requires the FooEvents Multi-day plugin.', 'woocommerce-events' ); ?>" height="16" width="16" /></a><br> 
			<label for="WooCommerceEventsTypeSelect" class="fooevents-options-inner-label"><input type="radio" id="WooCommerceEventsTypeSelect" name="WooCommerceEventsType" value="select" <?php echo ( 'select' === $woocommerce_events_type ) ? 'CHECKED' : ''; ?> <?php echo( true === $multi_day_enabled ) ? '' : 'DISABLED'; ?>> <?php esc_attr_e( 'Specific days', 'woocommerce-events' ); ?></label><a href="https://www.fooevents.com/products/fooevents-multi-day/"><img src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" class="help_tip" data-tip="<?php esc_attr_e( 'Events that repeat over multiple calendar days. Note: Requires the FooEvents Multi-day plugin.', 'woocommerce-events' ); ?>" height="16" width="16" /></a><br> 
			<label for="WooCommerceEventsTypeBookings" class="fooevents-options-inner-label"><input type="radio" id="WooCommerceEventsTypeBookings" name="WooCommerceEventsType" value="bookings" <?php echo ( 'bookings' === $woocommerce_events_type ) ? 'CHECKED' : ''; ?> <?php echo( true === $bookings_enabled ) ? '' : 'DISABLED'; ?>> <?php esc_attr_e( 'Bookable', 'woocommerce-events' ); ?></label><a href="https://www.fooevents.com/fooevents-bookings/"><img src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" class="help_tip" data-tip="<?php esc_attr_e( 'Events that require customers to select from available date and time slots (bookings and repeat events). Note: Requires the FooEvents Bookings plugin.', 'woocommerce-events' ); ?>" height="16" width="16" /></a><br> 
			<label for="WooCommerceEventsTypeSeating" class="fooevents-options-inner-label"><input type="radio" id="WooCommerceEventsTypeSeating" name="WooCommerceEventsType" value="seating" <?php echo ( 'seating' === $woocommerce_events_type ) ? 'CHECKED' : ''; ?> <?php echo( true === $seating_enabled ) ? '' : 'DISABLED'; ?>> <?php esc_attr_e( 'Seating', 'woocommerce-events' ); ?></label><a href="https://www.fooevents.com/fooevents-seating/"><img src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" class="help_tip" data-tip="<?php esc_attr_e( 'Events that include the ability for customers to select row and seat numbers from a seating chart. Note: Requires the FooEvents Seating plugin.', 'woocommerce-events' ); ?>" height="16" width="16" /></a><br> 
			</span>
		</p>
	</div>	
		<?php echo $num_days; ?>
		<?php echo $multiday_select_date_container; ?>
		<div class="options_group type_single type_seating type_sequential" id="WooCommerceEventsDateContainer">
		<p class="form-field">
			<label><?php esc_attr_e( 'Start date:', 'woocommerce-events' ); ?></label>
			<input type="text" id="WooCommerceEventsDate" name="WooCommerceEventsDate" value="<?php echo esc_attr( $woocommerce_events_date ); ?>"/>
			<img class="help_tip" data-tip="<?php esc_attr_e( "The date that the event is scheduled to take place. This is used as a label on your website and it's also used by the FooEvents Calendar to display the event.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
		</div>
		<?php echo $end_date; ?>
		<div class="options_group type_single type_seating type_sequential" id="WooCommerceEventsTimeContainer">
				<p class="form-field">
						<label><?php esc_attr_e( 'Start time:', 'woocommerce-events' ); ?></label>
						<select name="WooCommerceEventsHour" id="WooCommerceEventsHour">
							<?php for ( $x = 0; $x <= 23; $x++ ) : ?>
								<?php $x = sprintf( '%02d', $x ); ?>
							<option value="<?php echo esc_attr( $x ); ?>" <?php echo ( $woocommerce_events_hour === $x ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $x ); ?></option>
							<?php endfor; ?>
						</select>
						<select name="WooCommerceEventsMinutes" id="WooCommerceEventsMinutes">
							<?php for ( $x = 0; $x <= 59; $x++ ) : ?>
								<?php $x = sprintf( '%02d', $x ); ?>
							<option value="<?php echo esc_attr( $x ); ?>" <?php echo ( $woocommerce_events_minutes === $x ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $x ); ?></option>
							<?php endfor; ?>
						</select>
						<select name="WooCommerceEventsPeriod" id="WooCommerceEventsPeriod" <?php echo ( $woocommerce_events_hour > 12 || $woocommerce_events_hour_end > 12 ) ? 'disabled' : ''; ?>>
							<option value="">-</option>
							<option value="a.m." <?php echo ( $woocommerce_events_hour <= 12 && $woocommerce_events_hour_end <= 12 && 'a.m.' === $woocommerce_events_period ) ? 'SELECTED' : ''; ?>>a.m.</option>
							<option value="p.m." <?php echo ( $woocommerce_events_hour <= 12 && $woocommerce_events_hour_end <= 12 && 'p.m.' === $woocommerce_events_period ) ? 'SELECTED' : ''; ?>>p.m.</option>
						</select>
						<img class="help_tip" data-tip="<?php esc_attr_e( 'The time that the event is scheduled to start.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
		</div>
		<div class="options_group type_single type_seating type_sequential" id="WooCommerceEventsEndTimeContainer">
				<p class="form-field">
						<label><?php esc_attr_e( 'End time:', 'woocommerce-events' ); ?></label>
						<select name="WooCommerceEventsHourEnd" id="WooCommerceEventsHourEnd">
							<?php for ( $x = 0; $x <= 23; $x++ ) : ?>
								<?php $x = sprintf( '%02d', $x ); ?>
							<option value="<?php echo esc_attr( $x ); ?>" <?php echo ( $woocommerce_events_hour_end === $x ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $x ); ?></option>
							<?php endfor; ?>
						</select>
						<select name="WooCommerceEventsMinutesEnd" id="WooCommerceEventsMinutesEnd">
							<?php for ( $x = 0; $x <= 59; $x++ ) : ?>
								<?php $x = sprintf( '%02d', $x ); ?>
							<option value="<?php echo esc_attr( $x ); ?>" <?php echo ( $woocommerce_events_minutes_end === $x ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $x ); ?></option>
							<?php endfor; ?>
						</select>
						<select name="WooCommerceEventsEndPeriod" id="WooCommerceEventsEndPeriod" <?php echo ( $woocommerce_events_hour > 12 || $woocommerce_events_hour_end > 12 ) ? 'disabled' : ''; ?>>
							<option value="">-</option>
							<option value="a.m." <?php echo ( $woocommerce_events_hour <= 12 && $woocommerce_events_hour_end <= 12 && 'a.m.' === $woocommerce_events_end_period ) ? 'SELECTED' : ''; ?>>a.m.</option>
							<option value="p.m." <?php echo ( $woocommerce_events_hour <= 12 && $woocommerce_events_hour_end <= 12 && 'p.m.' === $woocommerce_events_end_period ) ? 'SELECTED' : ''; ?>>p.m.</option>
						</select>
						<img class="help_tip" data-tip="<?php esc_attr_e( 'The time that the event is scheduled to end.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
		</div>
		<div class="options_group type_single type_seating type_bookings type_sequential type_select" id="WooCommerceEventsTimezoneContainer">
				<p class="form-field">
						<label><?php esc_attr_e( 'Time zone:', 'woocommerce-events' ); ?></label>
						<select name="WooCommerceEventsTimeZone" id="WooCommerceEventsTimeZone">
							<option value="" <?php echo ( '' === $woocommerce_events_timezone ) ? 'SELECTED' : ''; ?>>(Not set)</option>
						<?php foreach ( $tzlist as $tz ) : ?>
							<option value="<?php echo esc_attr( $tz ); ?>" <?php echo ( $woocommerce_events_timezone === $tz ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( str_replace( '_', ' ', str_replace( '/', ' / ', $tz ) ) ); ?></option>
						<?php endforeach; ?>     
						</select>
						<img class="help_tip" data-tip="<?php esc_attr_e( "The time zone where the event is taking place. If no time zone is set then the attendee's local time zone will be used for the 'Add to Calendar' functionality in the ticket email.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
		</div>
	
		<?php echo $eventbrite_option; ?>
	
	<h2><?php esc_attr_e( 'Venue Details', 'woocommerce-events' ); ?></h2>

	<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'Venue:', 'woocommerce-events' ); ?></label>
			<input type="text" id="WooCommerceEventsLocation" name="WooCommerceEventsLocation" value="<?php echo esc_attr( $woocommerce_events_location ); ?>"/>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'The venue where the event will be hosted.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'GPS coordinates:', 'woocommerce-events' ); ?></label>
			<input type="text" id="WooCommerceEventsGPS" name="WooCommerceEventsGPS" value="<?php echo esc_attr( $woocommerce_events_gps ); ?>"/>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'GPS coordinates for the venue.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'Google coordinates:', 'woocommerce-events' ); ?></label>
			<input type="text" id="WooCommerceEventsGoogleMaps" name="WooCommerceEventsGoogleMaps" value="<?php echo esc_attr( $woocommerce_events_google_maps ); ?>"/>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'GPS coordinates that are used to calculate the pin position for Google Maps on the event page.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			<?php if ( empty( $global_woocommerce_events_google_maps_api_key ) && 'API' === $global_woocommerce_events_google_maps_method ) : ?>
				<br /><br /><?php esc_attr_e( 'Google Maps API key not set.', 'woocommerce-events' ); ?> <br /><a href="admin.php?page=fooevents-settings&tab=integration"><?php esc_attr_e( 'Please check the Event Integration settings.', 'woocommerce-events' ); ?></a>
			<?php endif; ?>
		</p>
	</div>
	<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'Directions:', 'woocommerce-events' ); ?></label>
			<textarea name="WooCommerceEventsDirections" id="WooCommerceEventsDirections"><?php echo esc_attr( $woocommerce_events_directions ); ?></textarea>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'Directions to the venue.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div> 
	<h2><?php esc_attr_e( 'Organizer Contact Details', 'woocommerce-events' ); ?></h2>
		<div class="options_group">
				<p class="form-field">
					<label><?php esc_attr_e( 'Phone number:', 'woocommerce-events' ); ?></label>
					<input type="text" id="WooCommerceEventsSupportContact" name="WooCommerceEventsSupportContact" value="<?php echo esc_attr( $woocommerce_events_support_contact ); ?>"/>
					<img class="help_tip" data-tip="<?php esc_attr_e( "Event organizer's contact number.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
		</div>
		<div class="options_group">
				<p class="form-field">
					<label><?php esc_attr_e( 'Email address:', 'woocommerce-events' ); ?></label>
					<input type="text" id="WooCommerceEventsEmail" name="WooCommerceEventsEmail" value="<?php echo esc_attr( $woocommerce_events_email ); ?>"/>
					<img class="help_tip" data-tip="<?php esc_attr_e( "Event organizer's email address.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
		</div>
	<h2><?php esc_attr_e( 'Custom Messages', 'woocommerce-events' ); ?></h2>
	
		<div class="options_group"> 
				<p class="form-field form-field-container">
					<label><?php esc_attr_e( 'Thank-you page text:', 'woocommerce-events' ); ?></label> <img class="help_tip" data-tip="<?php esc_attr_e( 'The copy that will be displayed on the thank-you page after ticket purchase.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					<div class="form-field-editor"><?php wp_editor( $woocommerce_events_thank_you_text, 'WooCommerceEventsThankYouText' ); ?></div>
				</p> 
		</div>
		<div class="options_group">
				<p class="form-field form-field-container">
				<label><?php esc_attr_e( 'Event details tab text:', 'woocommerce-events' ); ?></label> <img class="help_tip" data-tip="<?php esc_attr_e( 'The copy that will be displayed in the event details tab.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				<div class="form-field-editor"><?php wp_editor( $woocommerce_events_event_details_text, 'WooCommerceEventsEventDetailsText' ); ?></div>
			</p>
		</div>
		<h2><?php esc_attr_e( 'Calendar Colors', 'woocommerce-events' ); ?></h2>
		<div class="fooevents_options_panel_wide">
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'Calendar background color:', 'woocommerce-events' ); ?></label>
				<input type="text" class="woocommerce-events-color-field" id="WooCommerceEventsBackgroundColor" name="WooCommerceEventsBackgroundColor" value="<?php echo esc_html( $woocommerce_events_background_color ); ?>"/>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Color of the calendar background for the event. Also changes the background color of the date icon in the FooEvents Check-ins app.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'Calendar text color:', 'woocommerce-events' ); ?></label>
				<input type="text" class="woocommerce-events-color-field" id="WooCommerceEventsTextColor" name="WooCommerceEventsTextColor" value="<?php echo esc_html( $woocommerce_events_text_color ); ?>"/>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Color of the calendar text for the event. Also changes the font color of the date icon in the FooEvents Check-ins app.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		</div>
		<h2><?php esc_attr_e( 'Attendee Details', 'woocommerce-events' ); ?></h2>

		<?php if ( $is_checkout_block_enabled ) : ?>
			<div class="fooevents-settings-notice">
				<p>
					<?php esc_html_e( 'FooEvents does not currently support capturing individual attendee details using the new WooCommerce Checkout block. Billing details will be used for all attendees instead. To capture separate attendee information, please use the classic shortcode-based WooCommerce Checkout experience.', 'woocommerce_events' ); ?>
					<a href="<?php echo esc_url( 'https://help.fooevents.com/docs/frequently-asked-questions/installation-setup/does-fooevents-work-with-the-woocommerce-checkout-and-cart-blocks/' ); ?>">
						<?php esc_html_e( 'Learn more', 'woocommerce_events' ); ?> &rarr;
					</a>			
				</p>
			</div>
		<?php endif; ?>
		<div class="fooevents_options_panel_wide">
			<div class="options_group">
					<p class="form-field">
						<label><?php esc_attr_e( 'Capture attendee full name? ', 'woocommerce-events' ); ?></label>
						<input type="checkbox" name="WooCommerceEventsCaptureAttendeeDetails" value="on" <?php echo ( 'on' === $woocommerce_events_capture_attendee_details ) ? 'CHECKED' : ''; ?> <?php echo ( $is_checkout_block_enabled ) ? 'disabled="disabled"' : ''; ?>>
						<img class="help_tip" data-tip="<?php esc_attr_e( 'This will add an attendee full name field to the attendee capture fields on the checkout screen.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</p>
			</div>
			<div class="options_group">
					<p class="form-field">
						<label><?php esc_attr_e( 'Capture attendee email address? ', 'woocommerce-events' ); ?></label>
						<input type="checkbox" name="WooCommerceEventsCaptureAttendeeEmail" value="on" <?php echo ( ( 'on' === $woocommerce_events_capture_attendee_details && '' === $woocommerce_events_capture_attendee_email ) || 'on' === $woocommerce_events_capture_attendee_email ) ? 'CHECKED' : ''; ?> <?php echo ( $is_checkout_block_enabled ) ? 'disabled="disabled"' : ''; ?>>
						<img class="help_tip" data-tip="<?php esc_attr_e( 'This will add an attendee email field to the attendee capture fields on the checkout screen.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</p>
			</div>
			<div class="options_group">
				<p class="form-field">
					<label><?php esc_attr_e( 'Capture attendee phone number?', 'woocommerce-events' ); ?></label>
					<input type="checkbox" name="WooCommerceEventsCaptureAttendeeTelephone" value="on" <?php echo ( 'on' === $woocommerce_events_capture_attendee_telephone ) ? 'CHECKED' : ''; ?> <?php echo ( $is_checkout_block_enabled ) ? 'disabled="disabled"' : ''; ?>>
					<img class="help_tip" data-tip="<?php esc_attr_e( 'This will add a telephone number field to the attendee capture fields on the checkout screen.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
			<div class="options_group">
				<p class="form-field">
					<label><?php esc_attr_e( 'Capture attendee company name?', 'woocommerce-events' ); ?></label>
					<input type="checkbox" name="WooCommerceEventsCaptureAttendeeCompany" value="on" <?php echo ( 'on' === $woocommerce_events_capture_attendee_company ) ? 'CHECKED' : ''; ?> <?php echo ( $is_checkout_block_enabled ) ? 'disabled="disabled"' : ''; ?>>
					<img class="help_tip" data-tip="<?php esc_attr_e( 'This will add a company field to the attendee capture fields on the checkout screen.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
			<div class="options_group">
				<p class="form-field">
					<label><?php esc_attr_e( 'Capture attendee designation?', 'woocommerce-events' ); ?></label>
					<input type="checkbox" name="WooCommerceEventsCaptureAttendeeDesignation" value="on" <?php echo ( 'on' === $woocommerce_events_capture_attendee_designation ) ? 'CHECKED' : ''; ?> <?php echo ( $is_checkout_block_enabled ) ? 'disabled="disabled"' : ''; ?>>
					<img class="help_tip" data-tip="<?php esc_attr_e( 'This will add a designation field to the attendee capture fields on the checkout screen.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
			<div class="options_group">
				<p class="form-field">
					<label><?php esc_attr_e( 'Validate unique attendee email addresses at checkout?', 'woocommerce-events' ); ?></label>
					<input type="checkbox" name="WooCommerceEventsUniqueEmail" value="on" <?php echo ( 'on' === $woocommerce_events_unique_email ) ? 'CHECKED' : ''; ?> <?php echo ( $is_checkout_block_enabled ) ? 'disabled="disabled"' : ''; ?>>
					<img class="help_tip" data-tip="<?php esc_attr_e( 'Enable this option to require a unique email address for each attendee in a WooCommerce order before checkout. Note: This setting applies per event product, allowing the same email address for tickets to different events within the same order.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
		</div>
		<?php if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) : ?>
		<div class="fooevents_seating_options type_seating">
		<h2><?php esc_attr_e( 'Seating Display', 'woocommerce-events' ); ?></h2>
			<?php if ( $is_checkout_block_enabled ) : ?>
			<div class="fooevents-settings-notice">
				<p>
				<?php esc_html_e( 'FooEvents does not currently support modifying seating selections using the new WooCommerce Checkout block. When using the Checkout block, seating can only be selected directly and modified on the event/product pages.', 'woocommerce_events' ); ?>
				<a href="<?php echo esc_url( 'https://help.fooevents.com/docs/frequently-asked-questions/installation-setup/does-fooevents-work-with-the-woocommerce-checkout-and-cart-blocks/' ); ?>">
				<?php esc_html_e( 'Learn more', 'woocommerce_events' ); ?> &rarr;
				</a>			
			</p>
		</div>
		<?php endif; ?>
		<div class="fooevents_options_panel_wide">
			<div class="options_group">
				<p class="form-field">
					<label><?php esc_attr_e( 'Display seating options on product pages?', 'woocommerce-events' ); ?></label>
					<input type="checkbox" name="WooCommerceEventsViewSeatingOptions" value="on" <?php echo ( empty( $woocommerce_events_view_seating_options ) || 'off' === $woocommerce_events_view_seating_options ) ? '' : 'CHECKED'; ?>>
					<img class="help_tip" data-tip="<?php esc_attr_e( 'This will display the seating options on the product page and make it required to select seats before proceeding to check out. Before enabling this option, please ensure that you have setup a seating chart on the Event Seating tab.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
			<div class="options_group">
				<p class="form-field">
					<label><?php esc_attr_e( 'Display seating chart on checkout page?', 'woocommerce-events' ); ?></label>
					<input type="checkbox" name="WooCommerceEventsViewSeatingChart" value="on" <?php echo ( empty( $woocommerce_events_view_seating_chart ) || 'on' === $woocommerce_events_view_seating_chart ) ? 'CHECKED' : ''; ?> <?php echo ( $is_checkout_block_enabled ) ? 'disabled="disabled"' : ''; ?>>
					<img class="help_tip" data-tip="<?php esc_attr_e( "This will display a 'View seating chart' link on the checkout page. Before enabling this option, please ensure that you have setup a seating chart on the Event Seating tab.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
			<div class="options_group">
				<p class="form-field">
					<label><?php esc_attr_e( 'Display seats that are mapped to other variations?', 'woocommerce-events' ); ?></label>
					<input type="checkbox" name="WooCommerceEventsViewSeatingOtherVariations" value="on" <?php echo ( empty( $woocommerce_events_view_seating_other_variations ) || 'on' === $woocommerce_events_view_seating_other_variations ) ? 'CHECKED' : ''; ?>>
					<img class="help_tip" data-tip="<?php esc_attr_e( 'This will display all seats, even if the seat row is not mapped to the current variation selected. Enabling this setting might give a more accurate representation of the venue layout.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
		</div>
		<div class="clear clearfix"></div>
		</div>
		<?php endif; ?>
		<?php if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) : ?>
		<div class="fooevents_booking_options type_bookings">
		<h2><?php esc_attr_e( 'Booking Display', 'woocommerce-events' ); ?></h2>
			<?php if ( $is_checkout_block_enabled ) : ?>
			<div class="fooevents-settings-notice">
				<p>
					<?php esc_html_e( 'FooEvents does not currently support capturing or modifying bookings through the new WooCommerce Checkout block. When using the Checkout block, bookings can only be selected directly on the event/product pages.', 'woocommerce_events' ); ?>
					<a href="<?php echo esc_url( 'https://help.fooevents.com/docs/frequently-asked-questions/installation-setup/does-fooevents-work-with-the-woocommerce-checkout-and-cart-blocks/' ); ?>">
					<?php esc_html_e( 'Learn more', 'woocommerce_events' ); ?> &rarr;
					</a>			
				</p>
			</div>
		<?php endif; ?>
		<div class="fooevents_options_panel_wide">
			<div class="options_group">
				<p class="form-field">
						<label><?php esc_attr_e( 'Hide booking time in slot drop-down?', 'woocommerce-events' ); ?></label>
						<input type="checkbox" name="WooCommerceEventsHideBookingsDisplayTime" value="on" <?php echo ( empty( $woocommerce_events_hide_bookings_display_time ) || 'off' === $woocommerce_events_hide_bookings_display_time ) ? '' : 'CHECKED'; ?>>
						<img class="help_tip" data-tip="<?php esc_attr_e( 'This will hide the time of the booking in the selected slot drop-down.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
			<?php if ( is_plugin_active( 'fooevents-calendar/fooevents-calendar.php' ) || is_plugin_active_for_network( 'fooevents-calendar/ffooevents-calendar.php' ) ) : ?>
				<div class="options_group">
				<p class="form-field">
						<label><?php esc_attr_e( 'Hide booking slot in calendar?', 'woocommerce-events' ); ?></label>
						<input type="checkbox" name="WooCommerceEventsHideBookingsSlotCalendar" value="on" <?php echo ( empty( $woocommerce_events_hide_bookings_slot_calendar ) || 'off' === $woocommerce_events_hide_bookings_slot_calendar ) ? '' : 'CHECKED'; ?>>
						<img class="help_tip" data-tip="<?php esc_attr_e( 'This will hide the booking slot in the FooEvents Calendar.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
			<?php endif; ?>	
			<div class="options_group">
				<p class="form-field">
						<label><?php esc_attr_e( 'Hide stock availability notice?', 'woocommerce-events' ); ?></label>
						<input type="checkbox" name="WooCommerceEventsHideBookingsStockAvailability" value="on" <?php echo ( empty( $woocommerce_events_hide_bookings_stock_availability ) || 'off' === $woocommerce_events_hide_bookings_stock_availability ) ? '' : 'CHECKED'; ?>>
						<img class="help_tip" data-tip="<?php esc_attr_e( 'Hide the stock availability notice. This is typically used when offering unlimited stock.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
			<div class="options_group">
				<p class="form-field">
						<label><?php esc_attr_e( 'Show stock availability in the date and slot drop-downs?', 'woocommerce-events' ); ?></label>
						<input type="checkbox" name="WooCommerceEventsViewBookingsStockDropdowns" value="on" <?php echo ( empty( $woocommerce_events_view_bookings_stock_dropdowns ) || 'off' === $woocommerce_events_view_bookings_stock_dropdowns ) ? '' : 'CHECKED'; ?>>
						<img class="help_tip" data-tip="<?php esc_attr_e( 'This will include the amount of remaining stock for a specific date and slot combination within the drop-down selectors.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
			<div class="options_group">
				<p class="form-field">
						<label><?php esc_attr_e( 'Show out of stock booking dates?', 'woocommerce-events' ); ?></label>
						<input type="checkbox" name="WooCommerceEventsViewOutOfStockBookings" value="on" <?php echo ( empty( $woocommerce_events_view_out_of_stock_bookings ) || 'off' === $woocommerce_events_view_out_of_stock_bookings ) ? '' : 'CHECKED'; ?>>
						<img class="help_tip" data-tip="<?php esc_attr_e( 'Show dates that have no stock.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
			<div class="options_group">
				<p class="form-field">
						<label><?php esc_attr_e( 'Hide the booking date in combined drop-down?', 'woocommerce-events' ); ?></label>
						<input type="checkbox" name="WooCommerceEventsBookingsHideDateSingleDropDown" value="on" <?php echo ( 'on' === $woocommerce_events_bookings_hide_date_single_drop_down ) ? 'CHECKED' : ''; ?>>
						<img class="help_tip" data-tip="<?php esc_attr_e( 'This will hide the date of the booking and only display the slot label in the combined date/slot drop-down. Please note that the combined drop-down options will only display when multiple slots are set for the same date OR a single slot is set that has multiple dates.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
			<div class="options_group">
				<p class="form-field">
					<label><?php esc_attr_e( 'Bookings selection order', 'fooevents-bookings' ); ?></label>
					<select name="WooCommerceEventsBookingsMethod" id="WooCommerceEventsBookingsMethod">
						<option value="slotdate" <?php echo ( 'slotdate' === $woocommerce_events_bookings_method || '' === $woocommerce_events_bookings_method ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Slot -> Date', 'fooevents-bookings' ); ?></option>
						<option value="dateslot" <?php echo ( 'dateslot' === $woocommerce_events_bookings_method ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Date -> Slot', 'fooevents-bookings' ); ?></option>
					</select>
					<img class="help_tip" data-tip="<?php esc_attr_e( 'Displays the order of bookings drop-down selectors as either Slot then Date OR Date then Slot.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
			<div class="options_group">
				<p class="form-field">
					<label><?php esc_attr_e( 'Display booking slots and dates on:', 'fooevents-bookings' ); ?></label>
					<select name="WooCommerceEventsViewBookingsOptions" id="WooCommerceEventsViewBookingsOptions" <?php echo ( $is_checkout_block_enabled ) ? 'disabled="disabled"' : ''; ?>>
						<option value="checkout" <?php echo ( 'checkout' === $woocommerce_events_view_bookings_options || '' === $woocommerce_events_view_bookings_options ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Checkout Page', 'fooevents-bookings' ); ?></option>
						<option value="product" <?php echo ( 'product' === $woocommerce_events_view_bookings_options ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Product Page', 'fooevents-bookings' ); ?></option>
						<option value="checkoutproduct" <?php echo ( 'checkoutproduct' === $woocommerce_events_view_bookings_options || 'on' === $woocommerce_events_view_bookings_options ) ? 'SELECTED' : ''; ?> ><?php esc_attr_e( 'Checkout Page and Product Page', 'fooevents-bookings' ); ?></option>
					</select>
					<img class="help_tip" data-tip="<?php esc_attr_e( 'This will display the booking date and slot drop-down options on the checkout, product page or checkout and product page.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div> 
			</div>
			</div>
		<?php endif; ?>
		<h2><?php esc_attr_e( 'Order Email', 'woocommerce-events' ); ?></h2>
		<div class="fooevents_options_panel_wide">
			<div class="options_group">
				<p class="form-field">
					<label><?php esc_attr_e( 'Display event details in WooCommerce Order emails?', 'woocommerce-events' ); ?></label>
					<input type="checkbox" name="WooCommerceEventsEventDetailsNewOrder" value="on" <?php echo ( empty( $woocommerce_events_event_details_new_order ) || 'off' === $woocommerce_events_event_details_new_order ) ? '' : 'CHECKED'; ?>>
					<img class="help_tip" data-tip="<?php esc_attr_e( 'This will display the event details in the WooCommerce Order transactional emails which WooCommerce sends to the store admin as well as the customer.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
			<div class="options_group">
				<p class="form-field">
					<label><?php esc_attr_e( 'Display attendee details in WooCommerce Order emails?', 'woocommerce-events' ); ?></label>
					<input type="checkbox" name="WooCommerceEventsDisplayAttendeeNewOrder" value="on" <?php echo ( empty( $woocommerce_events_display_attendee_new_order ) || 'off' === $woocommerce_events_display_attendee_new_order ) ? '' : 'CHECKED'; ?>>
					<img class="help_tip" data-tip="<?php esc_attr_e( 'This will display the attendee details in the WooCommerce Order transactional emails which WooCommerce sends to the store admin as well as the customer.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
			<div class="fooevents_booking_options type_bookings">
			<div class="options_group">
				<p class="form-field">
					<label><?php esc_attr_e( 'Display booking details in WooCommerce Order emails?', 'woocommerce-events' ); ?></label>
					<input type="checkbox" name="WooCommerceEventsDisplayBookingsNewOrder" value="on" <?php echo ( empty( $woocommerce_events_display_bookings_new_order ) || 'off' === $woocommerce_events_display_bookings_new_order ) ? '' : 'CHECKED'; ?>>
					<img class="help_tip" data-tip="<?php esc_attr_e( 'This will display the booking details in the WooCommerce Order transactional emails which WooCommerce sends to the store admin as well as the customer.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
			</div>
			<div class="fooevents_seating_options type_seating">
				<div class="options_group">
					<p class="form-field">
						<label><?php esc_attr_e( 'Display seating details in WooCommerce Order emails?', 'woocommerce-events' ); ?></label>
						<input type="checkbox" name="WooCommerceEventsDisplaySeatingsNewOrder" value="on" <?php echo ( empty( $woocommerce_events_display_seatings_new_order ) || 'off' === $woocommerce_events_display_seatings_new_order ) ? '' : 'CHECKED'; ?>>
						<img class="help_tip" data-tip="<?php esc_attr_e( 'This will display the seating details in the WooCommerce Order transactional emails which WooCommerce sends to the store admin as well as the customer.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</p>
				</div>
			</div>
			<div class="options_group">
				<p class="form-field">
					<label><?php esc_attr_e( 'Display custom attendee details in WooCommerce Order emails?', 'woocommerce-events' ); ?></label>
					<input type="checkbox" name="WooCommerceEventsDisplayCustAttNewOrder" value="on" <?php echo ( empty( $woocommerce_events_display_cust_att_new_order ) || 'off' === $woocommerce_events_display_cust_att_new_order ) ? '' : 'CHECKED'; ?>>
					<img class="help_tip" data-tip="<?php esc_attr_e( 'This will display the custom attendee details in the WooCommerce Order transactional emails which WooCommerce sends to the store admin as well as the customer.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</p>
			</div>
		</div>
	</div>
</div>
