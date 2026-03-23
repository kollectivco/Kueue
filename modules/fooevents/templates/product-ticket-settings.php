<?php
/**
 * Event ticket settings template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="fooevents_tickets" class="panel woocommerce_options_panel fooevents_options_panel">
<h2><?php esc_attr_e( 'Ticket Design', 'woocommerce-events' ); ?></h2>
	<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'HTML ticket theme:', 'woocommerce-events' ); ?></label>
			<select name="WooCommerceEventsTicketTheme" id="WooCommerceEventsTicketTheme">
				<?php foreach ( $themes as $theme => $theme_details ) : ?>
					<option value="<?php echo esc_attr( $theme_details['path'] ); ?>" <?php echo ( $woocommerce_events_ticket_theme === $theme_details['path'] ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $theme_details['name'] ); ?></option>
				<?php endforeach; ?>
		</select>
		<img class="help_tip" data-tip="<?php esc_attr_e( 'Select the ticket theme that will be used to style the embedded HTML tickets within ticket emails.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p> 
	</div>
	<?php echo $pdf_ticket_themes; ?>
	<div class="options_group">
		<?php $woocommerce_events_ticket_logo = ( empty( $woocommerce_events_ticket_logo ) ) ? $global_woocommerce_events_ticket_logo : $woocommerce_events_ticket_logo; ?>
		<p class="form-field">
			<label><?php esc_attr_e( 'Ticket logo:', 'woocommerce-events' ); ?></label>
			<input id="WooCommerceEventsTicketLogo" class="text uploadfield" type="text" size="40" name="WooCommerceEventsTicketLogo" value="<?php echo esc_attr( $woocommerce_events_ticket_logo ); ?>" />				
			<span class="uploadbox">
					<input class="upload_image_button_woocommerce_events  button  " type="button" value="Upload file" />
				<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a>
			</span>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'Full URL that links to the logo that will be used in the ticket (JPG or PNG format).', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<div class="options_group">
		<?php $woocommerce_events_ticket_header_image = ( empty( $woocommerce_events_ticket_header_image ) ) ? $global_woocommerce_events_ticket_header_image : $woocommerce_events_ticket_header_image; ?>
		<p class="form-field">
			<label><?php esc_attr_e( 'Ticket header image:', 'woocommerce-events' ); ?></label>
			<input id="WooCommerceEventsTicketHeaderImage" class="text uploadfield" type="text" size="40" name="WooCommerceEventsTicketHeaderImage" value="<?php echo esc_attr( $woocommerce_events_ticket_header_image ); ?>" />				
			<span class="uploadbox">
				<input class="upload_image_button_woocommerce_events  button  " type="button" value="Upload file" />
				<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a>
			</span>
		<img class="help_tip" data-tip="<?php esc_attr_e( 'Full URL that links to the image that will be used as the ticket header (JPG or PNG format).', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<div class="options_group">
		<?php $global_woocommerce_events_ticket_background_color = ( empty( $global_woocommerce_events_ticket_background_color ) ) ? '' : $global_woocommerce_events_ticket_background_color; ?>
		<?php $woocommerce_events_ticket_background_color = ( empty( $woocommerce_events_ticket_background_color ) ) ? $global_woocommerce_events_ticket_background_color : $woocommerce_events_ticket_background_color; ?>
		<p class="form-field">
			<label><?php esc_attr_e( 'Ticket accent:', 'woocommerce-events' ); ?></label>
			<input class="woocommerce-events-color-field" type="text" name="WooCommerceEventsTicketBackgroundColor" value="<?php echo '' . esc_attr( $woocommerce_events_ticket_background_color ); ?>"/>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'This color is used for the ticket border or background.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<div class="options_group">
		<?php $global_woocommerce_events_ticket_button_color = ( empty( $global_woocommerce_events_ticket_button_color ) ) ? '' : $global_woocommerce_events_ticket_button_color; ?>
		<?php $woocommerce_events_ticket_button_color = ( empty( $woocommerce_events_ticket_button_color ) ) ? $global_woocommerce_events_ticket_button_color : $woocommerce_events_ticket_button_color; ?>
		<p class="form-field">
			<label><?php esc_attr_e( 'Ticket button:', 'woocommerce-events' ); ?></label>
			<input class="woocommerce-events-color-field" type="text" name="WooCommerceEventsTicketButtonColor" value="<?php echo '' . esc_attr( $woocommerce_events_ticket_button_color ); ?>"/>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'Color of the ticket button.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<div class="options_group">
		<?php $global_woocommerce_events_ticket_text_color = ( empty( $global_woocommerce_events_ticket_text_color ) ) ? '' : $global_woocommerce_events_ticket_text_color; ?>
		<?php $woocommerce_events_ticket_text_color = ( empty( $woocommerce_events_ticket_text_color ) ) ? $global_woocommerce_events_ticket_text_color : $woocommerce_events_ticket_text_color; ?>
		<p class="form-field">
			<label><?php esc_attr_e( 'Ticket button text:', 'woocommerce-events' ); ?></label>
			<input class="woocommerce-events-color-field" type="text" name="WooCommerceEventsTicketTextColor" value="<?php echo '' . esc_attr( $woocommerce_events_ticket_text_color ); ?>"/>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'Color of the ticket button text.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>	
	<h2><?php esc_attr_e( 'Email Settings', 'woocommerce-events' ); ?></h2>

	<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'Ticket email subject:', 'woocommerce-events' ); ?></label>
			<input type="text" id="WooCommerceEventsEmailSubjectSingle" name="WooCommerceEventsEmailSubjectSingle" value="<?php echo esc_attr( $woocommerce_events_email_subject_single ); ?>"/>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'The subject line used in ticket emails. Use {OrderNumber} to display the proper order number.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'Email copy of tickets to other recipients:', 'woocommerce-events' ); ?></label>
			<input type="text" id="wooCommerceEventsEmailTicketAdmin" name="wooCommerceEventsEmailTicketAdmin" value="<?php echo esc_attr( $woocommerce_events_email_ticket_admin ); ?>"/>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'Sends a copy when a ticket is generated for this event to the specified email address. Use a comma separated list to send to multiple email addresses. Note: This setting overrides the global recipients if entered in General settings for this event only.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	
	<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'Email tickets?', 'woocommerce-events' ); ?></label>
			<input type="checkbox" name="WooCommerceEventsSendEmailTickets" value="on" <?php echo ( empty( $woocommerce_events_send_email_tickets ) || 'on' === $woocommerce_events_send_email_tickets ) ? 'CHECKED' : ''; ?>>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'This will email the event tickets to the attendee or purchaser once the order has been completed.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'Email ticket to attendee rather than purchaser?', 'woocommerce-events' ); ?></label>
			<input type="checkbox" name="WooCommerceEventsEmailAttendee" value="on" <?php echo ( 'on' === $woocommerce_events_email_attendee ) ? 'CHECKED' : ''; ?>>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'This will email the ticket to the attendee instead of the ticket purchaser.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'Attach calendar ICS file to the ticket email?', 'woocommerce-events' ); ?></label>
			<input type="checkbox" id="WooCommerceEventsTicketAttachICS" name="WooCommerceEventsTicketAttachICS" value="on" <?php echo ( empty( $woocommerce_events_ticket_attach_ics ) || 'on' === $woocommerce_events_ticket_attach_ics ) ? 'CHECKED' : ''; ?>>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'Attach an ICS file to the ticket email so that the event details automatically appear in certain calendar clients.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>	
	<?php echo $events_include_custom_attendee_fields; ?>
		<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'Display "Add to calendar" option on ticket?', 'woocommerce-events' ); ?></label>
			<input type="checkbox" id="WooCommerceEventsTicketAddCalendarMeta" name="WooCommerceEventsTicketAddCalendar" value="on" <?php echo ( empty( $woocommerce_events_ticket_add_calendar ) || 'on' === $woocommerce_events_ticket_add_calendar ) ? 'CHECKED' : ''; ?>>
			<img class="help_tip" data-tip="<?php esc_attr_e( "Display an 'Add to calendar' button on the ticket which will generate an ICS file containing the event details when clicked.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
				
	<div class="options_group">
		<p class="form-field">
				<label><?php esc_attr_e( '"Add to calendar" reminder alerts:', 'woocommerce-events' ); ?></label>
			<span id="fooevents_add_to_calendar_reminders_container">
				<?php
				$woocommerce_events_ticket_add_calendar_reminders_count = is_array( $woocommerce_events_ticket_add_calendar_reminders ) ? count( $woocommerce_events_ticket_add_calendar_reminders ) : 0;

				for ( $i = 0; $i < $woocommerce_events_ticket_add_calendar_reminders_count; $i++ ) {
					$reminder = $woocommerce_events_ticket_add_calendar_reminders[ $i ];
					?>
					<span class="fooevents-add-to-calendar-reminder-row">
					<input type="number" min="0" step="1" name="WooCommerceEventsTicketAddCalendarReminderAmounts[]" value="<?php echo esc_attr( $reminder['amount'] ); ?>" />
					<select name="WooCommerceEventsTicketAddCalendarReminderUnits[]">
					<?php
					$units = array( 'minutes', 'hours', 'days', 'weeks' );
					foreach ( $units as $unit ) {
						?>
								<option value="<?php echo esc_attr( $unit ); ?>" <?php echo $reminder['unit'] === $unit ? 'SELECTED' : ''; ?>><?php echo esc_attr( $unit ); ?></option>
							<?php
					}
					?>
					</select>
					<a href="#" class="fooevents_add_to_calendar_reminders_remove">[X]</a>
					</span>
					<?php
				}
				?>
				</span>
				<a href="#" id="fooevents_add_to_calendar_reminders_new_field" class="button button-primary"><?php esc_attr_e( '+ New reminder', 'woocommerce-events' ); ?></a>
				<img class="help_tip" data-tip="<?php esc_attr_e( "Add calendar alerts at specified intervals to remind attendees about the event. These alerts will automatically appear in the attendee's calendar client after clicking the 'Add to calendar' button on the ticket.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
	</div>

	<div class="options_group">  
		<p class="form-field form-field-container">
			<label><?php esc_attr_e( 'Ticket email body:', 'woocommerce-events' ); ?></label> <img class="help_tip" data-tip="<?php esc_attr_e( 'The copy that will be displayed in the main body of the ticket email.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			<div class="form-field-editor"><?php wp_editor( $woocommerce_events_ticket_text, 'WooCommerceEventsTicketText' ); ?></div>
		</p>
	</div>
	<h2><?php esc_attr_e( 'Display Options', 'woocommerce-events' ); ?></h2>

	<div class="fooevents_options_panel_wide">

		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'Display purchaser or attendee details on ticket?', 'woocommerce-events' ); ?></label>
				<input type="checkbox" name="WooCommerceEventsTicketPurchaserDetails" value="on" <?php echo ( empty( $woocommerce_events_ticket_purchaser_details ) || 'on' === $woocommerce_events_ticket_purchaser_details ) ? 'CHECKED' : ''; ?>>
				<img class="help_tip" data-tip="<?php esc_attr_e( "Display the purchaser/attendee's name and details on the ticket.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'Display date and time on ticket?', 'woocommerce-events' ); ?></label>
				<input type="checkbox" name="WooCommerceEventsTicketDisplayDateTime" value="on" <?php echo ( empty( $woocommerce_events_ticket_display_date_time ) || 'on' === $woocommerce_events_ticket_display_date_time ) ? 'CHECKED' : ''; ?>>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Display the time and date of the event on the ticket.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'Display barcode/QR code on ticket?', 'woocommerce-events' ); ?></label>
				<input type="checkbox" name="WooCommerceEventsTicketDisplayBarcode" value="on" <?php echo ( empty( $woocommerce_events_ticket_display_barcode ) || 'on' === $woocommerce_events_ticket_display_barcode ) ? 'CHECKED' : ''; ?>>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Display a barcode/QR code on the ticket which is used for check-ins.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'Display price on ticket?', 'woocommerce-events' ); ?></label>
				<input type="checkbox" name="WooCommerceEventsTicketDisplayPrice" value="on" <?php echo ( 'on' === $woocommerce_events_ticket_display_price ) ? 'CHECKED' : ''; ?>>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Display the ticket price on the ticket.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field fooevents_booking_options type_bookings">
				<label><?php esc_attr_e( 'Display booking details on ticket?', 'woocommerce-events' ); ?></label>
				<input type="checkbox" name="WooCommerceEventsTicketDisplayBookings" value="on" <?php echo ( 'on' === $woocommerce_events_ticket_display_bookings ) ? 'CHECKED' : ''; ?>>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Display the booking details on the ticket.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'Display Zoom meeting/webinar details on ticket?', 'woocommerce-events' ); ?></label>
				<input type="checkbox" name="WooCommerceEventsTicketDisplayZoom" value="on" <?php echo ( 'on' === $woocommerce_events_ticket_display_zoom ) ? 'CHECKED' : ''; ?>>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Display all the Zoom meeting/webinar details such as the Meeting ID and Join link on the ticket.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<?php if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) : ?>
		<div class="options_group fooevents_multiday_options type_sequential type_select">
			<p class="form-field">
				<label><?php esc_attr_e( 'Display multi-day details on ticket?', 'woocommerce-events' ); ?></label>
				<input type="checkbox" name="WooCommerceEventsTicketDisplayMultiDay" value="on" <?php echo ( 'on' === $woocommerce_events_ticket_display_multi_day ) ? 'CHECKED' : ''; ?>>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'Display multi-day details on the ticket.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
		</div>
		<?php endif; ?>
		<div class="clear clearfix"></div>
	</div>
	<?php echo $pdf_ticket_options; ?>
	<h2><?php esc_attr_e( 'Incrementing Ticket Numbers', 'woocommerce-events' ); ?></h2>
	<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'Ticket identifier:', 'woocommerce-events' ); ?></label> 
			<select name="WooCommerceEventsTicketIdentifierOutput">
				<option value="ticketid" <?php echo ( 'ticketid' === $woocommerce_events_ticket_identifier_output ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Ticket ID', 'woocommerce-events' ); ?></option>
				<option value="ticketnumberformatted" <?php echo ( 'ticketnumberformatted' === $woocommerce_events_ticket_identifier_output ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Incremental ticket number', 'woocommerce-events' ); ?></option>
			</select>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'This option selects whether the traditional ticket ID (default) or auto-incrementing ticket number should be used as the ticket identifier.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'Ticket character number:', 'woocommerce-events' ); ?></label>
			<select name="WooCommerceEventsTicketNumberOutput">
				<option value="1" <?php echo ( '1' === $woocommerce_events_ticket_number_output ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '1 character', 'woocommerce-events' ); ?></option>
				<option value="2" <?php echo ( '2' === $woocommerce_events_ticket_number_output ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '2 characters', 'woocommerce-events' ); ?></option>
				<option value="3" <?php echo ( '3' === $woocommerce_events_ticket_number_output ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '3 characters', 'woocommerce-events' ); ?></option>
				<option value="4" <?php echo ( '4' === $woocommerce_events_ticket_number_output ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '4 characters', 'woocommerce-events' ); ?></option>
				<option value="5" <?php echo ( '5' === $woocommerce_events_ticket_number_output ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '5 characters', 'woocommerce-events' ); ?></option>
				<option value="6" <?php echo ( '6' === $woocommerce_events_ticket_number_output ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '6 characters', 'woocommerce-events' ); ?></option>
				<option value="7" <?php echo ( '7' === $woocommerce_events_ticket_number_output ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '7 characters', 'woocommerce-events' ); ?></option>
				<option value="8" <?php echo ( '8' === $woocommerce_events_ticket_number_output ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '8 characters', 'woocommerce-events' ); ?></option>
				<option value="9" <?php echo ( '9' === $woocommerce_events_ticket_number_output ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '9 characters', 'woocommerce-events' ); ?></option>
				<option value="10" <?php echo ( '10' === $woocommerce_events_ticket_number_output ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( '10 characters', 'woocommerce-events' ); ?></option>
			</select>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'This option selects how many characters the auto-incrementing ticket number should output. Note: This has no effect on the default Ticket ID number, only the incremental ticket number (if selected).', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'Ticket number prefix:', 'woocommerce-events' ); ?></label>
			<input type="text" id="WooCommerceEventsTicketNumberPrefix" name="WooCommerceEventsTicketNumberPrefix" value="<?php echo esc_attr( $woocommerce_events_ticket_number_prefix ); ?>"/>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'This is an alphanumeric prefix displayed before the auto-incrementing ticket number (e.g. AA0001). Note: This has no effect on the default Ticket ID number, only the incremental ticket number (if selected).', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'Ticket number suffix:', 'woocommerce-events' ); ?></label>
			<input type="text" id="WooCommerceEventsTicketNumberSuffix" name="WooCommerceEventsTicketNumberSuffix" value="<?php echo esc_attr( $woocommerce_events_ticket_number_suffix ); ?>"/>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'This is an alphanumeric prefix displayed before the auto-incrementing ticket number (e.g. 0001ZA). Note: This has no effect on the default Ticket ID number, only the incremental ticket number (if selected).', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
</div>

