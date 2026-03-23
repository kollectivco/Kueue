<?php
/**
 * Event integration settings template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="fooevents_integration" class="panel woocommerce_options_panel fooevents-settings fooevents_options_panel">
	<h2><?php esc_attr_e( 'Zoom Meetings and Webinars', 'woocommerce-events' ); ?></h2>

	<?php if ( empty( $global_woocommerce_events_zoom_account_id ) || empty( $global_woocommerce_events_zoom_client_id ) || empty( $global_woocommerce_events_zoom_client_secret ) ) : ?>

		<div class="options_group">
			<p><?php esc_html_e( 'The Zoom API Server-to-Server OAuth credentials are not set.', 'woocommerce-events' ); ?> 
			<br /><a href="admin.php?page=fooevents-settings&tab=integration"><?php esc_html_e( 'Please check the Event Integration settings.', 'woocommerce-events' ); ?></a></p>
		</div>
	
	<?php else : ?>

		<div class="options_group">
			<p class="form-field">
				<label><?php esc_html_e( 'Attendee details', 'woocommerce-events' ); ?></label>
				<?php esc_html_e( 'Note: Meeting and webinar registration requires attendee full name and email to be captured at checkout.', 'woocommerce-events' ); ?>
				<br/>
				<span class="fooevents_enable_attendee_details_note">
					<span class="fooevents_capture_attendee_details_enabled" 
					<?php
					if ( empty( $woocommerce_events_capture_attendee_details ) || 'off' === $woocommerce_events_capture_attendee_details || empty( $woocommerce_events_capture_attendee_email ) || 'off' === $woocommerce_events_capture_attendee_email ) :
						?>
						style="display:none;"<?php endif; ?>>
						<mark class="yes fooevents-zoom-test-access-result" style="padding:0;"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Capture attendee full name and email is currently enabled', 'woocommerce-events' ); ?></mark>
					</span>
					<span class="fooevents_capture_attendee_details_disabled" 
					<?php
					if ( ! empty( $woocommerce_events_capture_attendee_details ) && 'on' === $woocommerce_events_capture_attendee_details && ! empty( $woocommerce_events_capture_attendee_email ) && 'on' === $woocommerce_events_capture_attendee_email ) :
						?>
						style="display:none;"<?php endif; ?>>
						<mark class="error fooevents-zoom-test-access-result" style="padding:0;"><span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'Capture attendee full name and email is currently disabled', 'woocommerce-events' ); ?></mark>
						<br/>
						<a href="javascript:enableCaptureAttendeeDetails();"><?php esc_html_e( 'Enable attendee detail capture option', 'woocommerce-events' ); ?></a>
					</span>
				</span>
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_html_e( 'Automatically generate:', 'woocommerce-events' ); ?></label>
				<span class="fooevents-event-type">
				<label class="fooevents-options-inner-label"><input type="radio" name="WooCommerceEventsZoomType" value="meetings" <?php echo ( empty( $woocommerce_events_zoom_type ) || 'meetings' === $woocommerce_events_zoom_type ) ? 'CHECKED' : ''; ?>> <?php esc_html_e( 'Meetings', 'woocommerce-events' ); ?></label>
				<br/>
				<label class="fooevents-options-inner-label"><input type="radio" name="WooCommerceEventsZoomType" value="webinars" <?php echo ( ! empty( $woocommerce_events_zoom_type ) && 'webinars' === $woocommerce_events_zoom_type ) ? 'CHECKED' : ''; ?>> <?php esc_html_e( 'Webinars', 'woocommerce-events' ); ?></label>
				<a href="https://zoom.us/webinar" target="_BLANK"><img src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" class="help_tip" data-tip="<?php esc_attr_e( 'Requires the Zoom Video Webinars service to be purchased in your Zoom account.', 'woocommerce-events' ); ?>" height="16" width="16" /></a>
				</span>
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_html_e( 'Select meeting/webinar host:', 'woocommerce-events' ); ?></label>
				<span id="globalWooCommerceEventsZoomUsersContainer">
					<select name="WooCommerceEventsZoomHost" id="WooCommerceEventsZoomHost" class="fooevents-search-list">
						<option value="">(<?php esc_html_e( 'Not set', 'woocommerce-events' ); ?>)</option>
						<?php
						if ( ! empty( $global_woocommerce_events_zoom_users ) ) :
							foreach ( $global_woocommerce_events_zoom_users as $user ) {
								?>
								<option value="<?php echo esc_attr( $user['id'] ); ?>" <?php echo ( ! empty( $woocommerce_events_zoom_host ) && $woocommerce_events_zoom_host === $user['id'] ) ? 'SELECTED' : ''; ?>><?php echo esc_html( $user['first_name'] ) . ' ' . esc_html( $user['last_name'] ) . ' - ' . esc_html( $user['email'] ); ?></option>
								<?php
							}
						endif;
						?>
					</select>
				</span>
				<img src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" class="help_tip" data-tip="<?php esc_attr_e( 'Select the host of Zoom meetings/webinars that get generated automatically.', 'woocommerce-events' ); ?>" height="16" width="16" />
				<br/><br/>
				<input id="fooevents_zoom_reload_users" type="button" value="<?php esc_attr_e( 'Fetch Users', 'woocommerce-events' ); ?>" class="button button-secondary fooevents-zoom-integration-button">
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_html_e( 'Current event type:', 'woocommerce-events' ); ?></label>        
				<span id="fooevents_zoom_current_event_type"></span>
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_html_e( 'Zoom integration type:', 'woocommerce-events' ); ?></label>
				<span class="fooevents-zoom-integration-type-container fooevents-event-type">
					<span class="zoom-single zoom-sequential zoom-seating">
						<label class="fooevents-options-inner-label"><input type="radio" name="WooCommerceEventsZoomMultiOption" value="single" <?php echo ( empty( $woocommerce_events_zoom_multi_option ) || 'single' === $woocommerce_events_zoom_multi_option ) ? 'CHECKED' : ''; ?>> <?php esc_html_e( 'Single meeting/webinar for this event', 'woocommerce-events' ); ?></label>
						<img class="help_tip" data-tip="<?php esc_attr_e( 'Select or automatically generate a Zoom meeting/webinar for this event which attendees will automatically be registered for when purchasing an event ticket.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
						<br/>
					</span>
					<span class="zoom-sequential zoom-select">
						<label class="fooevents-options-inner-label"><input type="radio" name="WooCommerceEventsZoomMultiOption" value="multi" <?php echo ( ! empty( $woocommerce_events_zoom_multi_option ) && 'multi' === $woocommerce_events_zoom_multi_option ) ? 'CHECKED' : ''; ?> <?php echo true === $multi_day_enabled ? '' : 'DISABLED'; ?>> <?php esc_html_e( 'Separate meeting/webinar for each day', 'woocommerce-events' ); ?></label>
						<a href="https://www.fooevents.com/products/fooevents-multi-day/" target="_BLANK"><img src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" class="help_tip" data-tip="<?php esc_attr_e( 'Select or automatically generate a Zoom meeting/webinar for each day of this multi-day event which attendees will automatically be registered for when purchasing an event ticket. Note: Requires the FooEvents Multi-day plugin.', 'woocommerce-events' ); ?>" height="16" width="16" /></a>
						<br/>
					</span>
					<span class="zoom-bookings">
						<label class="fooevents-options-inner-label"><input type="radio" name="WooCommerceEventsZoomMultiOption" value="bookings" <?php echo ( ! empty( $woocommerce_events_zoom_multi_option ) && 'bookings' === $woocommerce_events_zoom_multi_option ) ? 'CHECKED' : ''; ?> <?php echo true === $bookings_enabled ? '' : 'DISABLED'; ?>> <?php esc_html_e( 'Separate meeting/webinar for each booking slot', 'woocommerce-events' ); ?></label>
						<a href="https://www.fooevents.com/products/fooevents-bookings/" target="_BLANK"><img src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" class="help_tip" data-tip="<?php esc_attr_e( 'Select or automatically generate a Zoom meeting/webinar for each date/time slot of the bookable event which attendees will automatically be registered for when purchasing an event ticket. Note: Requires the FooEvents Bookings plugin.', 'woocommerce-events' ); ?>" height="16" width="16" /></a>
					</span>
				</span>
			</p>
		</div>
		<div class="options_group">
			<div id ="fooevents_zoom_meeting_single" class="options_group zoom-integration-type-container">
				<p class="form-field">
					<label><?php esc_html_e( 'Link the event to this meeting/webinar:', 'woocommerce-events' ); ?></label>
					<select name="WooCommerceEventsZoomWebinar" id="WooCommerceEventsZoomWebinar" class="WooCommerceEventsZoomSelect fooevents-search-list">
						<option value="">(<?php esc_html_e( 'Not set', 'woocommerce-events' ); ?>)</option>
						<option value="auto">(<?php esc_html_e( 'Auto-generate', 'woocommerce-events' ); ?>)</option>
						<?php if ( 'success' === $zoom_webinars['status'] && ! empty( $zoom_webinars['data']['webinars'] ) ) : ?>
							<optgroup label="<?php esc_attr_e( 'Webinars', 'woocommerce-events' ); ?>">
							<?php foreach ( $zoom_webinars['data']['webinars'] as $zoom_webinar ) : ?>
								<option value="<?php echo esc_attr( $zoom_webinar['id'] ); ?>" data-zoom-type="<?php echo esc_attr( $zoom_webinar['type'] ); ?>" <?php echo ( str_replace( '_webinars', '', $woocommerce_events_zoom_webinar ) === str_replace( '_webinars', '', $zoom_webinar['id'] ) ) ? 'SELECTED' : ''; ?>><?php echo $zoom_webinars['user_count'] > 1 ? esc_html( $zoom_webinar['host']['first_name'] ) . ' ' . esc_html( $zoom_webinar['host']['last_name'] ) . ' - ' : ''; ?><?php echo esc_html( $zoom_webinar['topic'] ); ?> - <?php echo ! empty( $zoom_webinar['start_date_display'] ) && ! empty( $zoom_webinar['start_time_display'] ) ? esc_html( $zoom_webinar['start_date_display'] ) . ' ' . esc_html( $zoom_webinar['start_time_display'] ) : esc_html__( 'No fixed time', 'woocommerce-events' ); ?></option>
							<?php endforeach; ?>
							</optgroup>
						<?php endif; ?>
						<?php if ( 'success' === $zoom_meetings['status'] && ! empty( $zoom_meetings['data']['meetings'] ) ) : ?>
							<optgroup label="<?php esc_attr_e( 'Meetings', 'woocommerce-events' ); ?>">
							<?php foreach ( $zoom_meetings['data']['meetings'] as $zoom_meeting ) : ?>
								<option value="<?php echo esc_attr( $zoom_meeting['id'] ); ?>" data-zoom-type="<?php echo esc_attr( $zoom_meeting['type'] ); ?>" <?php echo ( str_replace( '_meetings', '', $woocommerce_events_zoom_webinar ) === str_replace( '_meetings', '', $zoom_meeting['id'] ) ) ? 'SELECTED' : ''; ?>><?php echo $zoom_meetings['user_count'] > 1 ? esc_html( $zoom_meeting['host']['first_name'] ) . ' ' . esc_html( $zoom_meeting['host']['last_name'] ) . ' - ' : ''; ?><?php echo esc_html( esc_html( $zoom_meeting['topic'] ) ); ?> - <?php echo ! empty( $zoom_meeting['start_date_display'] ) && ! empty( $zoom_meeting['start_time_display'] ) ? esc_html( $zoom_meeting['start_date_display'] ) . ' ' . esc_html( $zoom_meeting['start_time_display'] ) : esc_html__( 'No fixed time', 'woocommerce-events' ); ?></option>
							<?php endforeach; ?>
							</optgroup>
						<?php endif; ?>
					</select>
					<?php if ( 'success' === $zoom_meetings['status'] && 'success' === $zoom_webinars['status'] && empty( $zoom_meetings['data']['meetings'] ) && empty( $zoom_webinars['data']['webinars'] ) ) : ?>
					<br /><br />
						<?php esc_html_e( 'No Zoom meetings/webinars found.', 'woocommerce-events' ); ?>
					<br/>
					<br/>
					<a href="https://zoom.us/meeting" target="_blank"><?php esc_html_e( 'Create a Zoom meeting', 'woocommerce-events' ); ?></a>
					<br/>
					<a href="https://zoom.us/webinar/list" target="_blank"><?php esc_html_e( 'Create a Zoom webinar', 'woocommerce-events' ); ?></a>
					<?php endif; ?>
				</p> 
				<p class="form-field">
					<label><?php esc_html_e( 'Details:', 'woocommerce-events' ); ?></label>
					<span id="WooCommerceEventsZoomWebinarDetails">(<?php esc_html_e( 'Not set', 'woocommerce-events' ); ?>)</span>
				</p>
			</div>
			<?php if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) : ?>
				<div id ="fooevents_zoom_meeting_multi" class="options_group zoom-integration-type-container" data-day-term="<?php echo esc_attr( $day_term ); ?>">
					<?php
					for ( $x = 1; $x <= $num_days_value; $x++ ) {

						$zoom_webinar_multi = 0;

						if ( ! empty( $woocommerce_events_zoom_webinar_multi ) ) {
							$zoom_webinar_multi = $woocommerce_events_zoom_webinar_multi[ $x - 1 ];
						}
						?>
						<p class="form-field">
						<?php if ( 1 === $x ) : ?>
								<label><?php esc_html_e( 'Link the event to these meetings/webinars:', 'woocommerce-events' ); ?></label>
							<?php endif; ?>
							<span class="fooevents-zoom-day-override-title"><?php echo esc_html( $day_term ); ?> <?php echo esc_html( $x ); ?></span>
							<select name="WooCommerceEventsZoomWebinarMulti[]" id="WooCommerceEventsZoomWebinarMulti<?php echo esc_html( $x ); ?>" class="WooCommerceEventsZoomSelect fooevents-search-list">
								<option value="">(<?php esc_html_e( 'Not set', 'woocommerce-events' ); ?>)</option>
								<option value="auto">(<?php esc_html_e( 'Auto-generate', 'woocommerce-events' ); ?>)</option>
							<?php if ( 'success' === $zoom_webinars['status'] && ! empty( $zoom_webinars['data']['webinars'] ) ) : ?>
									<optgroup label="<?php esc_attr_e( 'Webinars', 'woocommerce-events' ); ?>">
									<?php foreach ( $zoom_webinars['data']['webinars'] as $zoom_webinar ) : ?>
										<option value="<?php echo esc_attr( $zoom_webinar['id'] ); ?>" data-zoom-type="<?php echo esc_attr( $zoom_webinar['type'] ); ?>" <?php echo ( str_replace( '_webinars', '', $zoom_webinar_multi ) === str_replace( '_webinars', '', $zoom_webinar['id'] ) ) ? 'SELECTED' : ''; ?>><?php echo $zoom_webinars['user_count'] > 1 ? esc_html( $zoom_webinar['host']['first_name'] ) . ' ' . esc_html( $zoom_webinar['host']['last_name'] ) . ' - ' : ''; ?><?php echo esc_html( $zoom_webinar['topic'] ); ?> - <?php echo ! empty( $zoom_webinar['start_date_display'] ) && ! empty( $zoom_webinar['start_time_display'] ) ? esc_html( $zoom_webinar['start_date_display'] ) . ' ' . esc_html( $zoom_webinar['start_time_display'] ) : esc_html__( 'No fixed time', 'woocommerce-events' ); ?></option>
									<?php endforeach; ?>
									</optgroup>
								<?php endif; ?>
							<?php if ( 'success' === $zoom_meetings['status'] && ! empty( $zoom_meetings['data']['meetings'] ) ) : ?>
									<optgroup label="<?php esc_attr_e( 'Meetings', 'woocommerce-events' ); ?>">
									<?php foreach ( $zoom_meetings['data']['meetings'] as $zoom_meeting ) : ?>
										<option value="<?php echo esc_attr( $zoom_meeting['id'] ); ?>" data-zoom-type="<?php echo esc_attr( $zoom_meeting['type'] ); ?>" <?php echo ( str_replace( '_meetings', '', $zoom_webinar_multi ) === str_replace( '_meetings', '', $zoom_meeting['id'] ) ) ? 'SELECTED' : ''; ?>><?php echo $zoom_meetings['user_count'] > 1 ? esc_html( $zoom_meeting['host']['first_name'] ) . ' ' . esc_html( $zoom_meeting['host']['last_name'] ) . ' - ' : ''; ?><?php echo esc_html( $zoom_meeting['topic'] ); ?> - <?php echo ! empty( $zoom_meeting['start_date_display'] ) && ! empty( $zoom_meeting['start_time_display'] ) ? esc_html( $zoom_meeting['start_date_display'] ) . ' ' . esc_html( $zoom_meeting['start_time_display'] ) : esc_html__( 'No fixed time', 'woocommerce-events' ); ?></option>
									<?php endforeach; ?>
									</optgroup>
								<?php endif; ?>
							</select>
							<a href="#" class="fooevents-zoom-show-hide-meeting-details-link" data-meeting="WooCommerceEventsZoomWebinarMulti<?php echo esc_html( $x ); ?>">
								<span class="toggle-indicator fooevents-zoom-show-hide-meeting-details" aria-hidden="true"></span>
								<span class="fooevents-zoom-show-hide-meeting-details-link-text"><?php esc_html_e( 'Show details', 'woocommerce-events' ); ?></span>
							</a>
						</p>
						<p class="form-field fooevents-zoom-multi-meeting-details">
							<span class="fooevents-zoom-multi-meeting-details-container" id="WooCommerceEventsZoomWebinarMulti<?php echo esc_html( $x ); ?>Details">(<?php esc_html_e( 'Not set', 'woocommerce-events' ); ?>)</span>
						</p>
					<?php } ?>
				</div>
			<?php endif; ?>
			<?php if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) : ?>
				<div id ="fooevents_zoom_meeting_bookings" class="options_group zoom-integration-type-container">
				<p class="form-field">
					<label><?php esc_html_e( 'Meeting/webinar duration', 'woocommerce-events' ); ?></label>
					<select name="WooCommerceEventsZoomDurationHour" id="WooCommerceEventsZoomDurationHour">
						<?php for ( $x = 0; $x <= 23; $x++ ) : ?>
						<option value="<?php echo esc_attr( $x ); ?>" <?php echo( ( '' !== $woocommerce_events_zoom_duration_hour && (int) $woocommerce_events_zoom_duration_hour === $x ) || ( '' === $woocommerce_events_zoom_duration_hour && 1 === $x ) ) ? 'SELECTED' : ''; ?>><?php echo esc_html( $x ); ?></option>
						<?php endfor; ?>
					</select>
					<span class="fooevents-zoom-duration-label"><?php esc_html_e( 'hours', 'woocommerce-events' ); ?></span>
					<select name="WooCommerceEventsZoomDurationMinute" id="WooCommerceEventsZoomDurationMinute">
						<?php for ( $x = 0; $x <= 59; $x++ ) : ?>
						<option value="<?php echo esc_attr( $x ); ?>" <?php echo( '' !== $woocommerce_events_zoom_duration_minute && (int) $woocommerce_events_zoom_duration_minute === $x ) ? 'SELECTED' : ''; ?>><?php echo esc_html( $x ); ?></option>
						<?php endfor; ?>
					</select>
					<span class="fooevents-zoom-duration-label"><?php esc_html_e( 'minutes', 'woocommerce-events' ); ?></span>
					<img src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" class="help_tip" data-tip="<?php esc_attr_e( 'Set the duration of Zoom meetings/webinars that get generated automatically for booking date and time slots.', 'woocommerce-events' ); ?>" height="16" width="16" />
				</p>
				</div>
			<?php endif; ?>
			<input type="hidden" name="WooCommerceEventsZoomTopic" value="<?php echo esc_attr( $woocommerce_events_zoom_topic ); ?>" />
		</div>
	<?php endif; ?>	
	<?php if ( ! empty( $mailchimp_api_key ) && ! empty( $mailchimp_server_prefix ) && ! empty( $mailchimp_lists ) ) : ?>
		<h2><?php esc_attr_e( 'Mailchimp', 'woocommerce-events' ); ?></h2>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_html_e( 'Attendee details:', 'woocommerce-events' ); ?></label>
				<?php esc_html_e( 'Note: In order to add attendee details to a Mailchimp list, you will need to capture attendee details at checkout.', 'woocommerce-events' ); ?><br />
				<span class="fooevents_enable_attendee_details_note">
						<span class="fooevents_capture_attendee_details_enabled" 
						<?php
						if ( empty( $woocommerce_events_capture_attendee_details ) || 'off' === $woocommerce_events_capture_attendee_details ) :
							?>
							style="display:none;"<?php endif; ?>>
							<mark class="yes fooevents-zoom-test-access-result" style="padding:0;"><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Capture attendee full name and email is currently enabled', 'woocommerce-events' ); ?></mark>
						</span>
						<span class="fooevents_capture_attendee_details_disabled" 
						<?php
						if ( ! empty( $woocommerce_events_capture_attendee_details ) && 'on' === $woocommerce_events_capture_attendee_details ) :
							?>
							style="display:none;"<?php endif; ?>>
							<mark class="error fooevents-zoom-test-access-result" style="padding:0;"><span class="dashicons dashicons-warning"></span> <?php esc_html_e( 'Capture attendee full name and email is currently disabled', 'woocommerce-events' ); ?></mark>
							<br/>
							<a href="javascript:enableCaptureAttendeeDetails();"><?php esc_html_e( 'Enable attendee detail capture option', 'woocommerce-events' ); ?></a>
						</span>
				</span>
			</p>
			<p class="form-field">
				<label><?php esc_html_e( 'Audience list', 'woocommerce-events' ); ?></label>
				<select name="WooCommerceEventsMailchimpList" id="WooCommerceEventsMailchimpList" class="fooevents-search-list">
					<option value="">(<?php esc_html_e( 'Not set', 'woocommerce-events' ); ?>)</option>
					<?php foreach ( $mailchimp_lists as $list_id => $list ) : ?>
						<option value="<?php echo esc_attr( (string) $list_id ); ?>" <?php echo( ( ! empty( $woocommerce_events_mailchimp_list ) && (string) $woocommerce_events_mailchimp_list === (string) $list_id ) ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $list ); ?></option>
					<?php endforeach; ?>		
				</select>
				<img src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" class="help_tip" data-tip="<?php esc_attr_e( 'Select an audience where you would like the attendees of this event to be automatically added as contacts in Mailchimp. This takes priority over the default audience list in the FooEvents settings. Note: The audience must first be setup in your Mailchimp account.', 'woocommerce-events' ); ?>" height="16" width="16" />
			</p>
			<p class="form-field">
				<label><?php esc_attr_e( 'Audience tags', 'woocommerce-events' ); ?></label>
				<input type="text" id="WooCommerceEventsMailchimpTags" name="WooCommerceEventsMailchimpTags" value="<?php echo esc_attr( $woocommerce_events_mailchimp_tags ); ?>"/>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Specify tags as comma-separated values (,) which you would like to be associated with all the attendees of this event when they are automatically added as contacts in Mailchimp. This takes priority over the default audience tags in the FooEvents settings.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
	<?php endif; ?>
	<?php if ( ! empty( $addtowallet_api_key ) ) : ?>
		<h2><?php esc_attr_e( 'AddToWallet', 'woocommerce-events' ); ?></h2>
		
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'Display AddToWallet link on ticket?', 'woocommerce-events' ); ?></label>
				<input type="checkbox" name="WooCommerceEventsTicketDisplayAddToWallet" value="on" <?php echo ( 'on' === $woocommerce_events_display_addtowallet ) ? 'CHECKED' : ''; ?>>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Display a link that will let the attendee add their QR code as a pass to their Android or Apple Wallet on their phone. Please note that an account with sufficient credits on the addtowallet.co website and a valid API key in the FooEvents -> Settings -> Integration -> AddToWallet API key field is needed.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'AddToWallet logo:', 'woocommerce-events' ); ?></label>
				<input id="WooCommerceEventsAddToWalletLogo" class="text uploadfield" type="text" size="40" name="WooCommerceEventsAddToWalletLogo" value="<?php echo esc_attr( $woocommerce_events_addtowallet_logo ); ?>" />				
				<span class="uploadbox">
					<input class="upload_image_button_woocommerce_events button" type="button" value="Upload file" />
					<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a>
				</span>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Full URL that links to the image that will be used as the logo on AddToWallet passes. For best results please ensure that this is a square image. (JPG or PNG format).', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'AddToWallet image:', 'woocommerce-events' ); ?></label>
				<input id="WooCommerceEventsAddToWalletImage" class="text uploadfield" type="text" size="40" name="WooCommerceEventsAddToWalletImage" value="<?php echo esc_attr( $woocommerce_events_addtowallet_image ); ?>" />				
				<span class="uploadbox">
					<input class="upload_image_button_woocommerce_events button" type="button" value="Upload file" />
					<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a>
				</span>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Full URL that links to the main image that will be used on AddToWallet passes (JPG or PNG format).', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'AddToWallet pass background color:', 'woocommerce-events' ); ?></label>
				<input type="text" class="woocommerce-events-color-field" id="WooCommerceEventsAddToWalletBackgroundColor" name="WooCommerceEventsAddToWalletBackgroundColor" value="<?php echo esc_html( $woocommerce_events_addtowallet_background_color ); ?>"/>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Color of the AddToWallet pass background.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'AddToWallet card title:', 'woocommerce-events' ); ?></label>
				<input type="text" placeholder="The default is the event name" id="WooCommerceEventsAddToWalletCardTitle" name="WooCommerceEventsAddToWalletCardTitle" value="<?php echo esc_attr( $woocommerce_events_addtowallet_card_title ); ?>"/>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'The title can be your website or event name.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'AddToWallet card header:', 'woocommerce-events' ); ?></label>
				<input type="text" placeholder="The default is the event name, can use {var} for variation" id="WooCommerceEventsAddToWalletCardHeader" name="WooCommerceEventsAddToWalletCardHeader" value="<?php echo esc_attr( $woocommerce_events_addtowallet_card_header ); ?>"/>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'The header can be your event name or variation', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'Display date on AddToWallet pass?', 'woocommerce-events' ); ?></label>
				<input type="checkbox" name="WooCommerceEventsDisplayAddToWalletDate" value="on" <?php echo ( 'on' === $woocommerce_events_display_addtowallet_date ) ? 'CHECKED' : ''; ?>>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Display the date on the AddToWallet pass.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'AddToWallet date label:', 'woocommerce-events' ); ?></label>
				<input type="text" placeholder="The default is the word 'Date'" id="WooCommerceEventsAddToWalletDateLabel" name="WooCommerceEventsAddToWalletDateLabel" value="<?php echo esc_attr( $woocommerce_events_addtowallet_date_label ); ?>"/>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Word or phrase to display instead of the default word Date', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'AddToWallet date value:', 'woocommerce-events' ); ?></label>
				<input type="text" placeholder="The default is the date and time for the event/booking slot" id="WooCommerceEventsAddToWalletDateValue" name="WooCommerceEventsAddToWalletDateValue" value="<?php echo esc_attr( $woocommerce_events_addtowallet_date_value ); ?>"/>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Value to display instead of the default Date', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'Display attendee name on AddToWallet pass?', 'woocommerce-events' ); ?></label>
				<input type="checkbox" name="WooCommerceEventsDisplayAddToWalletAttendee" value="on" <?php echo ( 'on' === $woocommerce_events_display_addtowallet_attendee ) ? 'CHECKED' : ''; ?>>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Display the attendee name on the AddToWallet pass.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'AddToWallet attendee label:', 'woocommerce-events' ); ?></label>
				<input type="text" placeholder="The default is the word 'Attendee'" id="WooCommerceEventsAddToWalletAttendeeLabel" name="WooCommerceEventsAddToWalletAttendeeLabel" value="<?php echo esc_attr( $woocommerce_events_addtowallet_attendee_label ); ?>"/>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Word or phrase to display instead of the default word Attendee', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'Display the venue/location on AddToWallet pass?', 'woocommerce-events' ); ?></label>
				<input type="checkbox" name="WooCommerceEventsDisplayAddToWalletLocation" value="on" <?php echo ( 'on' === $woocommerce_events_display_addtowallet_location ) ? 'CHECKED' : ''; ?>>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Display the location on the AddToWallet pass.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'AddToWallet location label:', 'woocommerce-events' ); ?></label>
				<input type="text" placeholder="The default is the word 'Location'" id="WooCommerceEventsAddToWalletLocationLabel" name="WooCommerceEventsAddToWalletLocationLabel" value="<?php echo esc_attr( $woocommerce_events_addtowallet_location_label ); ?>"/>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Word or phrase to display instead of the default word Location', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		
		<div class="options_group">
			<p><a href="admin.php?page=fooevents-settings&tab=integration" target="_BLANK"><?php esc_html_e( 'Configure AddToWallet API Settings.', 'woocommerce-events' ); ?></a></p>
		</div>
	<?php endif; ?>

	
</div>
