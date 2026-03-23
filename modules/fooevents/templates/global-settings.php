<?php
/**
 * Global options
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap" id="fooevents-settings-page">
	<h1 class="wp-heading-inline"><?php esc_attr_e( 'FooEvents Settings', 'woocommerce-events' ); ?></h1>
	<h2 class="nav-tab-wrapper">
		<a href="?page=fooevents-settings&tab=api" class="nav-tab <?php echo 'api' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_attr_e( 'License', 'woocommerce-events' ); ?></a>
		<a href="?page=fooevents-settings&tab=general" class="nav-tab <?php echo 'general' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_attr_e( 'General', 'woocommerce-events' ); ?></a>
		<a href="?page=fooevents-settings&tab=terminology" class="nav-tab <?php echo 'terminology' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Terminology', 'woocommerce-events' ); ?></a>
		<a href="?page=fooevents-settings&tab=ticket_design" class="nav-tab <?php echo 'ticket_design' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Ticket Design', 'woocommerce-events' ); ?></a>
		<?php if ( $pdf_enabled ) : ?>
			<a href="?page=fooevents-settings&tab=pdf" class="nav-tab <?php echo 'pdf' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_attr_e( 'PDF Tickets', 'woocommerce-events' ); ?></a>
		<?php endif; ?>
		<?php if ( $seating_enabled ) : ?>
			<a href="?page=fooevents-settings&tab=seating" class="nav-tab <?php echo 'seating' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Seating', 'woocommerce-events' ); ?></a>
		<?php endif; ?>
		<?php if ( $calendar_enabled ) : ?>
			<a href="?page=fooevents-settings&tab=calendar" class="nav-tab <?php echo 'calendar' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Calendar', 'woocommerce-events' ); ?></a>
		<?php endif; ?>
		<a href="?page=fooevents-settings&tab=checkins_app" class="nav-tab <?php echo 'checkins_app' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Check-ins App', 'woocommerce-events' ); ?></a>
		<a href="?page=fooevents-settings&tab=integration" class="nav-tab <?php echo 'integration' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_attr_e( 'Integration', 'woocommerce-events' ); ?></a>
	</h2>
	<form method="post" action="options.php">
		<table class="form-table fooevents-settings">
			<?php if ( 'api' === $active_tab ) : ?>
				<?php settings_fields( 'fooevents-settings-api' ); ?>
				<?php do_settings_sections( 'fooevents-settings-api' ); ?>
			<tr valign="top">
				<th scope="row"><h2><?php esc_attr_e( 'FooEvents License', 'woocommerce-events' ); ?></h2></th>
				<td></td>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'FooEvents license key', 'woocommerce-events' ); ?></th>
				<td>
					<input type="password" name="globalWooCommerceEventsAPIKey" id="globalWooCommerceEventsAPIKey" value="<?php echo esc_html( $global_woocommerce_events_api_key ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Required for automatic plugin updates. Leave empty if purchase was made on CodeCanyon.net AND no other plugin purchases were made on FooEvents.com. You must paste your license key here if any plugin purchases were made on FooEvents.com.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" /> 
					<a href="https://www.fooevents.com/my-account/license/" target="_blank"><?php esc_attr_e( 'Get license key', 'woocommerce-events' ); ?> &rarr;</a>
				</td>
			</tr>  
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Envato purchase code', 'woocommerce-events' ); ?></th>
				<td>
					<input type="password" name="globalWooCommerceEnvatoAPIKey" id="globalWooCommerceEnvatoAPIKey" value="<?php echo esc_html( $global_woocommerce_envato_api_key ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Required for automatic plugin updates. Leave empty if purchase was made on FooEvents.com AND no purchases were made on CodeCanyon.net. You must paste your Envato purchase code here if the FooEvents for WooCommerce plugin was purchased on CodeCanyon.net.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<?php endif; ?>
			<?php if ( 'general' === $active_tab ) : ?>
				<?php settings_fields( 'fooevents-settings-general' ); ?>
				<?php do_settings_sections( 'fooevents-settings-general' ); ?>
			<tr valign="top">
				<th scope="row"><h2><?php esc_attr_e( 'General', 'woocommerce-events' ); ?></h2></th>
				<td></td>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Change add to cart text', 'woocommerce-events' ); ?></th>
				<td>
					<input type="checkbox" name="globalWooCommerceEventsChangeAddToCart" id="globalWooCommerceEventsChangeAddToCart" value="yes" <?php echo ( 'yes' === $global_woocommerce_events_change_add_to_cart ) ? 'CHECKED' : ''; ?>>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( "Changes 'Add to cart' text to 'Book ticket' for event products.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Enable event sorting options', 'woocommerce-events' ); ?></th>
				<td>
					<input type="checkbox" name="globalWooCommerceEventSorting" id="globalWooCommerceEventSorting" value="yes" <?php echo ( 'yes' === $global_woocommerce_event_sorting ) ? 'CHECKED' : ''; ?>>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Adds sort by date options to the WooCommerce product sorting drop-down list. You can set the default sort option in the WordPress Customizer.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Display event date on product listings', 'woocommerce-events' ); ?></th>
				<td>
					<input type="checkbox" name="globalWooCommerceDisplayEventDate" id="globalWooCommerceDisplayEventDate" value="yes" <?php echo ( 'yes' === $global_woocommerce_display_event_date ) ? 'CHECKED' : ''; ?>>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Adds the event date above the product title on product listing pages.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Hide event details tab', 'woocommerce-events' ); ?></th>
				<td>
					<input type="checkbox" name="globalWooCommerceHideEventDetailsTab" id="globalWooCommerceHideEventDetailsTab" value="yes" <?php echo ( 'yes' === $global_woocommerce_hide_event_details_tab ) ? 'CHECKED' : ''; ?>>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Hides the event details tab on the product page.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Use placeholders on checkout form', 'woocommerce-events' ); ?></th>
				<td>
					<input type="checkbox" name="globalWooCommerceUsePlaceHolders" id="globalWooCommerceUsePlaceHolders" value="yes" <?php echo ( 'yes' === $global_woocommerce_use_place_holders ) ? 'CHECKED' : ''; ?>>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( "Displays placeholders in the checkout form (useful for themes that don't support form labels).", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Hide unpaid tickets', 'woocommerce-events' ); ?></th>
				<td>
					<input type="checkbox" name="globalWooCommerceEventsHideUnpaidTickets" id="globalWooCommerceEventsHideUnpaidTickets" value="yes" <?php echo ( 'yes' === $global_woocommerce_events_hide_unpaid_tickets ) ? 'CHECKED' : ''; ?>>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Hides unpaid tickets in ticket admin.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Disable completed order email notification', 'woocommerce-events' ); ?></th>
				<td>
					<input type="checkbox" name="globalWooCommerceEventsSuppressAdminNotifications" id="globalWooCommerceEventsSuppressAdminNotifications" value="yes" <?php echo ( 'yes' === $global_woocommerce_events_suppress_admin_notifications ) ? 'CHECKED' : ''; ?>>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Prevents the WooCommerce completed order email notification from being sent to customers when tickets are created through the WordPress dashboard.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Use "Total Sales" on reports listing page', 'woocommerce-events' ); ?></th>
				<td>
					<input type="checkbox" name="globalWooCommerceEventsUseTotalSales" id="globalWooCommerceEventsUseTotalSales" value="yes" <?php echo ( 'yes' === $global_woocommerce_events_use_total_sales ) ? 'CHECKED' : ''; ?>>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Solves a performance issue on some servers with large amount of tickets. Tickets sold on report listing page might be less acurate with this setting enabled.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>  
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Email copy of tickets to other recipients', 'woocommerce-events' ); ?></th>
				<td>
					<input type="textbox" name="globalWooCommerceEventsEmailTicketAdmin" id="globalWooCommerceEventsEmailTicketAdmin" value="<?php echo esc_attr( $global_woocommerce_events_email_ticket_admin ); ?>" autocomplete="off">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Sends a copy when a ticket is generated to the specified email address. Use a comma separated list to send to multiple email addresses.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( "Copy ticket purchaser's details", 'woocommerce-events' ); ?></th>
				<td>
					<select name="globalWooCommerceEventsAddCopyPurchaserDetails" id="globalWooCommerceEventsAddCopyPurchaserDetails">
						<option value=""><?php esc_attr_e( 'Disabled', 'woocommerce-events' ); ?></option>
						<option value="icon" <?php echo ( 'yes' === $global_woocommerce_events_add_copy_purchaser_details || 'icon' === $global_woocommerce_events_add_copy_purchaser_details ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Show icon', 'woocommerce-events' ); ?></option>
						<option value="text" <?php echo ( 'text' === $global_woocommerce_events_add_copy_purchaser_details ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Show text', 'woocommerce-events' ); ?></option>
						<option value="textandicon" <?php echo ( 'textandicon' === $global_woocommerce_events_add_copy_purchaser_details ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Show icon AND text', 'woocommerce-events' ); ?></option>
						<option value="autocopy" <?php echo ( 'autocopy' === $global_woocommerce_events_add_copy_purchaser_details ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Automatically copy details', 'woocommerce-events' ); ?></option>
						<option value="autocopyhideemail" <?php echo ( 'autocopyhideemail' === $global_woocommerce_events_add_copy_purchaser_details ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Automatically copy details AND hide email address', 'woocommerce-events' ); ?></option>
					</select>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( "Adds the option to copy across the ticket purchaser's details for the attendee's first name, last name and email address on the checkout page using either an icon, text, or icon and text visual prompt. It can also be set to automatically copy the purchaser's details with the option to hide the email address field.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Event expiration method', 'woocommerce-events' ); ?></th>
				<td>
					<select name="globalWooCommerceEventsExpireOption">
						<option value="disable" <?php echo ( 'disable' === $global_woocommerce_events_expire_option ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Disable purchase', 'woocommerce-events' ); ?></option>
						<option value="hide" <?php echo ( 'hide' === $global_woocommerce_events_expire_option ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Hide product', 'woocommerce-events' ); ?></option>
					</select>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Disable the ability to purchase the ticket/product or hide it in page listings after the event has expired.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Attendee fields position', 'woocommerce-events' ); ?></th>
				<td>
					<select name="globalWooCommerceEventsAttendeeFieldsPos">
						<option value="defualt" <?php echo ( '' === $global_woocommerce_events_attendee_fields_pos ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'After order notes (default)', 'woocommerce-events' ); ?></option>
						<option value="beforeordernotes" <?php echo ( 'beforeordernotes' === $global_woocommerce_events_attendee_fields_pos ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Before order notes', 'woocommerce-events' ); ?></option>
						<option value="afterbillingform" <?php echo ( 'afterbillingform' === $global_woocommerce_events_attendee_fields_pos ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'After billing form', 'woocommerce-events' ); ?></option>
						<option value="aftershippingform" <?php echo ( 'aftershippingform' === $global_woocommerce_events_attendee_fields_pos ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'After shipping form', 'woocommerce-events' ); ?></option>
					</select>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Sets the position of the attendee form fields on the checkout page. Selecting a different position may help to resolve conflicts with certain themes and third-party plugins.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Send on order status', 'woocommerce-events' ); ?></th>
				<td>
				<select name="globalWooCommerceEventsSendOnStatus[]" multiple autocomplete="off">
					<?php foreach ( $order_statuses as $slug => $status_term ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php echo ( in_array( $slug, $global_woocommerce_events_send_on_status ) ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $status_term ); ?></option>
					<?php endforeach; ?>
				</select>
				<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Sets the required order status for ticket creation and sending. "Completed" is the default status but this can be deselected and other statuses can be selected simultaneously using the multi-select field.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Import batch size', 'woocommerce-events' ); ?></th>
				<td>
					<select name="globalWooCommerceEventsImportBatchSize" id="globalWooCommerceEventsImportBatchSize">
						<option value=""><?php esc_attr_e( '100 (Default)', 'woocommerce-events' ); ?></option>
						<option value="200" <?php echo ( 200 === $global_woocommerce_events_import_batch_size ) ? 'SELECTED' : ''; ?>>200</option>
						<option value="500" <?php echo ( 500 === $global_woocommerce_events_import_batch_size ) ? 'SELECTED' : ''; ?>>500</option>
						<option value="1000" <?php echo ( 1000 == $global_woocommerce_events_import_batch_size ) ? 'SELECTED' : ''; ?>>1000</option>
						<option value="2000" <?php echo ( 2000 === $global_woocommerce_events_import_batch_size ) ? 'SELECTED' : ''; ?>>2000</option>
						<option value="3000" <?php echo ( 3000 === $global_woocommerce_events_import_batch_size ) ? 'SELECTED' : ''; ?>>3000</option>
						<option value="4000" <?php echo ( 4000 === $global_woocommerce_events_import_batch_size ) ? 'SELECTED' : ''; ?>>4000</option>
						<option value="5000" <?php echo ( 5000 === $global_woocommerce_events_import_batch_size ) ? 'SELECTED' : ''; ?>>5000</option>
					</select>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Specify the number of tickets to import in each batch.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					<div class="fooevents-settings-warning"><p><?php esc_attr_e( "Depending on your server's configuration and capabilities, setting a large batch size may cause issues due to server timeouts or memory exhaustion. Adjust this value accordingly to avoid performance issues.", 'woocommerce-events' ); ?></p></div>
				</td>
			</tr>	
			<?php endif; ?>
			<?php if ( 'terminology' === $active_tab ) : ?>
				<?php settings_fields( 'fooevents-settings-terminology' ); ?>
				<?php do_settings_sections( 'fooevents-settings-terminology' ); ?>
			<tr valign="top">
				<th scope="row"><h2><?php esc_attr_e( 'Terminology', 'woocommerce-events' ); ?></h2></th>
				<td></td>
				<td width="100%"></td>
			</tr>
			<tr valign="top">
				<th scope="row"></th>
				<td><?php esc_attr_e( 'Singular', 'woocommerce-events' ); ?></td>
				<td><?php esc_attr_e( 'Plural', 'woocommerce-events' ); ?></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Event', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsEventOverride" id="globalWooCommerceEventsEventOverride" value="<?php echo esc_attr( $global_woocommerce_events_event_override ); ?>">
				</td>
				<td>
					<input type="text" name="globalWooCommerceEventsEventOverridePlural" id="globalWooCommerceEventsEventOverridePlural" value="<?php echo esc_attr( $global_woocommerce_events_event_override_plural ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( "Change 'event' to your own custom text.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Attendee', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsAttendeeOverride" id="globalWooCommerceEventsAttendeeOverride" value="<?php echo esc_attr( $global_woocommerce_events_attendee_override ); ?>">
				</td>
				<td>
					<input type="text" name="globalWooCommerceEventsAttendeeOverridePlural" id="globalWooCommerceEventsAttendeeOverridePlural" value="<?php echo esc_attr( $global_woocommerce_events_attendee_override_plural ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( "Change 'attendee' to your own custom text.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Book ticket', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsTicketOverride" id="globalWooCommerceEventsTicketOverride" value="<?php echo esc_attr( $global_woocommerce_events_ticket_override ); ?>">
				</td>
				<td>
					<input type="text" name="globalWooCommerceEventsTicketOverridePlural" id="globalWooCommerceEventsTicketOverridePlural" value="<?php echo esc_attr( $global_woocommerce_events_ticket_override_plural ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( "Change 'Book ticket' to your own custom text.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Day', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="WooCommerceEventsDayOverride" id="WooCommerceEventsDayOverride" value="<?php echo esc_attr( $woocommerce_events_day_override ); ?>">
				</td>
				<td>
					<input type="text" name="WooCommerceEventsDayOverridePlural" id="WooCommerceEventsDayOverridePlural" value="<?php echo esc_attr( $woocommerce_events_day_override_plural ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( "Change 'Day' to your own custom text.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Copy', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="WooCommerceEventsCopyOverride" id="WooCommerceEventsCopyOverride" value="<?php echo esc_attr( $woocommerce_events_copy_override ); ?>">
				</td>
				<td>
					<input type="text" name="WooCommerceEventsCopyOverridePlural" id="WooCommerceEventsCopyOverridePlural" value="<?php echo esc_attr( $woocommerce_events_copy_override_plural ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( "Change 'Copy' to your own custom text.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( 'ticket_design' === $active_tab ) : ?>
				<?php settings_fields( 'fooevents-settings-ticket-design' ); ?>
				<?php do_settings_sections( 'fooevents-settings-ticket-design' ); ?>
			<tr valign="top">
				<th scope="row"><h2><?php esc_attr_e( 'Ticket Design', 'woocommerce-events' ); ?></h2></th>
				<td></td>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Global ticket border', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsTicketBackgroundColor" id="globalWooCommerceEventsTicketBackgroundColor" class="woocommerce-events-color-field" value="<?php echo esc_html( $global_woocommerce_events_ticket_background_color ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Color of the ticket border.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Global ticket button', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsTicketButtonColor" id="globalWooCommerceEventsTicketButtonColor" class="woocommerce-events-color-field" value="<?php echo esc_html( $global_woocommerce_events_ticket_button_color ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Color of the ticket button.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Global ticket button text', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsTicketTextColor" id="globalWooCommerceEventsTicketTextColor" class="woocommerce-events-color-field" value="<?php echo esc_html( $global_woocommerce_events_ticket_text_color ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Color of the text in the ticket button.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>  
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Global ticket logo', 'woocommerce-events' ); ?></th>
				<td>
					<input id="globalWooCommerceEventsTicketLogo" class="text uploadfield" type="text" size="40" name="globalWooCommerceEventsTicketLogo" value="<?php echo esc_attr( $global_woocommerce_events_ticket_logo ); ?>" />                
					<span class="uploadbox">
						<input class="upload_image_button_woocommerce_events  button  " type="button" value="Upload file" />
						<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a>
						<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Full URL that links to the logo that will be used in the ticket.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</span>
				</td>
			</tr>  
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Global ticket header image', 'woocommerce-events' ); ?></th>
				<td>
					<input id="globalWooCommerceEventsTicketHeaderImage" class="text uploadfield" type="text" size="40" name="globalWooCommerceEventsTicketHeaderImage" value="<?php echo esc_attr( $global_woocommerce_events_ticket_header_image ); ?>" />               
					<span class="uploadbox">
						<input class="upload_image_button_woocommerce_events  button  " type="button" value="Upload file" />
						<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a>
						<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Full URL that links to the image that will be used as the ticket header.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Enable QR codes', 'woocommerce-events' ); ?></th>
				<td>
					<input type="hidden" name="globalWooCommerceEventsEnableQRCode" id="globalWooCommerceEventsEnableQRCode" value="no">
					<input type="checkbox" name="globalWooCommerceEventsEnableQRCode" id="globalWooCommerceEventsEnableQRCode" value="yes" <?php echo ( 'yes' === $global_woocommerce_events_enable_qr_code ) ? 'CHECKED' : ''; ?>>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Use QR codes instead of 1D barcodes on tickets.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'QR/Barcode image format', 'woocommerce-events' ); ?></th>
				<td>
					<select name="globalWooCommerceEventsBarcodeOutput">
						<option value="png" <?php echo ( 'png' === $global_woocommerce_events_barcode_output || empty( $global_woocommerce_events_barcode_output ) ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'PNG', 'woocommerce-events' ); ?></option>
						<option value="pngjpg" <?php echo ( 'pngjpg' === $global_woocommerce_events_barcode_output ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'PNG and JPG', 'woocommerce-events' ); ?></option>
					</select>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Select the preferred image format for 1D barcodes and QR codes that are generated for tickets. PNG is the default format.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Remove FooEvents footer link', 'woocommerce-events' ); ?></th>
				<td>
					<input type="checkbox" name="globalWooCommerceEventsDisplayPoweredby" value="off" <?php echo ( 'off' === $global_woocommerce_events_display_poweredby ) ? 'CHECKED' : ''; ?>>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Removes the "Powered by FooEvents.com" link from the ticket email footer.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 			
			<?php endif; ?>

			<?php if ( 'pdf' === $active_tab ) : ?>
				<?php echo $pdf_options; ?>
			<?php endif; ?>
			<?php if ( 'calendar' === $active_tab ) : ?>
				<?php echo $calendar_options; ?>
			<?php endif; ?>
			<?php if ( 'seating' === $active_tab ) : ?>
				<?php echo $seating_options; ?>
			<?php endif; ?>
			<?php if ( 'checkins_app' === $active_tab ) : ?>
				<?php settings_fields( 'fooevents-settings-checkins-app' ); ?>
				<?php do_settings_sections( 'fooevents-settings-checkins-app' ); ?>
			<tr valign="top">
				<th scope="row"><h2><?php esc_attr_e( 'Check-ins App', 'woocommerce-events' ); ?></h2></th>
				<td></td>
				<td></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Hide personal information', 'woocommerce-events' ); ?></th>
				<td>
					<input type="checkbox" name="globalWooCommerceEventsAppHidePersonalInfo" id="globalWooCommerceEventsAppHidePersonalInfo" value="yes" <?php echo ( 'yes' === $global_woocommerce_events_app_hide_personal_info ) ? 'CHECKED' : ''; ?>>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Hides all personal information for attendees and/or ticket purchasers in the app. Only attendee names will be visible for check-in purposes.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Hide unpaid tickets', 'woocommerce-events' ); ?></th>
				<td>
					<input type="checkbox" name="globalWooCommerceHideUnpaidTicketsApp" id="globalWooCommerceHideUnpaidTicketsApp" value="yes" <?php echo ( 'yes' === $global_woocommerce_hide_unpaid_tickets_app ) ? 'CHECKED' : ''; ?>>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Hides all unpaid tickets in the app.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'App title', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsAppTitle" id="globalWooCommerceEventsAppTitle" placeholder="<?php esc_attr_e( 'e.g. Attendee Check-ins', 'woocommerce-events' ); ?>" class="text" size="40" value="<?php echo esc_attr( $global_woocommerce_events_app_title ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'The title that displays on the app sign-in screen beneath the app logo.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'App logo', 'woocommerce-events' ); ?></th>
				<td>
					<input id="globalWooCommerceEventsAppLogo" class="text uploadfield" type="text" size="40" name="globalWooCommerceEventsAppLogo" value="<?php echo esc_attr( $global_woocommerce_events_app_logo ); ?>" />             
					<span class="uploadbox">
						<input class="upload_image_button_woocommerce_events  button  " type="button" value="Upload file" />
						<a href="#" class="upload_reset"><?php esc_attr_e( 'Clear', 'woocommerce-events' ); ?></a>
						<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Full URL that links to the image that will be used as the logo on the sign-in screen (PNG format with transparency and a width of around 940px is recommended).', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					</span>
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Accent color', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsAppColor" id="globalWooCommerceEventsAppColor" class="woocommerce-events-color-field" value="<?php echo esc_attr( $global_woocommerce_events_app_color ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Color of the top navigation bar and sign-in button.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Accent text color', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsAppTextColor" id="globalWooCommerceEventsAppTextColor" class="woocommerce-events-color-field" value="<?php echo esc_attr( $global_woocommerce_events_app_text_color ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Color of the text in the top navigation bar and sign-in button.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Background color', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsAppBackgroundColor" id="globalWooCommerceEventsAppBackgroundColor" class="woocommerce-events-color-field" value="<?php echo esc_attr( $global_woocommerce_events_app_background_color ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Color of the background on the sign-in screen.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				<td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Title text color', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsAppSignInTextColor" id="globalWooCommerceEventsAppSignInTextColor" class="woocommerce-events-color-field" value="<?php echo esc_attr( $global_woocommerce_events_app_sign_in_text_color ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Color of the title text beneath the logo on the sign-in screen.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
				<?php echo $fooevents_pos_color_options; ?>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Event listing options', 'woocommerce-events' ); ?></th>
				<td>
					<label><input type="radio" name="globalWooCommerceEventsAppEvents" id="globalWooCommerceEventsAppEventsAll" value="all" <?php echo ( 'all' === $global_woocommerce_events_app_events || empty( $global_woocommerce_events_app_events ) ) ? 'CHECKED' : ''; ?>> <?php esc_attr_e( 'Show all events', 'woocommerce-events' ); ?></label>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Manage how events are listed in the app. Changes can be made in real-time without the user needing to sign-out.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					<br/><br/>
					<label><input type="radio" name="globalWooCommerceEventsAppEvents" id="globalWooCommerceEventsAppEventsUser" value="user" <?php echo ( 'user' === $global_woocommerce_events_app_events ) ? 'CHECKED' : ''; ?>> <?php esc_attr_e( 'Only show events created by the signed-in user', 'woocommerce-events' ); ?></label>
					<br/><br/>
					<label><input type="radio" name="globalWooCommerceEventsAppEvents" id="globalWooCommerceEventsAppEventsID" value="id" <?php echo ( 'id' === $global_woocommerce_events_app_events ) ? 'CHECKED' : ''; ?>> <?php esc_attr_e( 'Only show the following events:', 'woocommerce-events' ); ?></label>
					<br/><br/>
					<select name="globalWooCommerceEventsAppEventIDs[]" id="globalWooCommerceEventsAppEventIDs" multiple class="fooevents-multiselect" <?php echo ( 'id' !== $global_woocommerce_events_app_events ) ? 'disabled' : ''; ?>>
						<?php
						foreach ( $woocommerce_events_app_events as $woocommerce_events_app_event ) {
							?>
								<option value="<?php echo esc_attr( $woocommerce_events_app_event['id'] ); ?>" <?php echo ! empty( $global_woocommerce_events_app_event_ids ) && in_array( (string) $woocommerce_events_app_event['id'], $global_woocommerce_events_app_event_ids, true ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $woocommerce_events_app_event['title'] ); ?> (ID: <?php echo esc_html( $woocommerce_events_app_event['id'] ); ?>)</option>
							<?php
						}
						?>
					</select>
					<br/><br/>
					<input type="checkbox" name="globalWooCommerceEventsAppShowAllForAdmin" id="globalWooCommerceEventsAppShowAllForAdmin" value="yes" <?php echo ( 'yes' === $global_woocommerce_events_app_show_all_for_admin || 'all' === $global_woocommerce_events_app_events || empty( $global_woocommerce_events_app_events ) ) ? 'CHECKED' : ''; ?> <?php echo ( 'all' === $global_woocommerce_events_app_events || empty( $global_woocommerce_events_app_events ) ) ? 'disabled' : ''; ?>> <label for="globalWooCommerceEventsAppShowAllForAdmin"><?php esc_html_e( 'Show all events for Administrator users', 'woocommerce-events' ); ?></label>
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Tickets to load per request', 'woocommerce-events' ); ?></th>
				<td>
					<select name="globalWooCommerceEventsAppTicketsPerRequest" id="globalWooCommerceEventsAppTicketsPerRequest" class="text">
						<?php
						foreach ( $tickets_per_request_array as $tickets_per_request_key => $tickets_per_request_value ) {
							?>
							<option <?php echo ( ( ! empty( $global_woocommerce_events_app_ticket_limit ) && $global_woocommerce_events_app_ticket_limit === (string) $tickets_per_request_key ) || ( empty( $global_woocommerce_events_app_ticket_limit ) && 'all' === (string) $tickets_per_request_key ) ) ? 'SELECTED' : ''; ?> value="<?php echo esc_attr( $tickets_per_request_key ); ?>"><?php echo esc_html( $tickets_per_request_value ); ?></option>
							<?php
						}
						?>
					</select>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Specify how many event tickets the Check-ins app should load during each request (lower values will result in slower ticket load times but this usually works better for servers with limited resources).', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Show ticket access log', 'woocommerce-events' ); ?></th>
				<td>
					<select name="globalWooCommerceEventsAppTicketAccessLog" id="globalWooCommerceEventsAppTicketAccessLog" class="text">
						<?php
						foreach ( $ticket_access_log_array as $ticket_access_log_key => $ticket_access_log_value ) {
							?>
							<option <?php echo ( ( ! empty( $global_woocommerce_events_app_ticket_access_log ) && $global_woocommerce_events_app_ticket_access_log === (string) $ticket_access_log_key ) || ( empty( $global_woocommerce_events_app_ticket_access_log ) && 'none' === (string) $ticket_access_log_key ) ) ? 'SELECTED' : ''; ?> value="<?php echo esc_attr( $ticket_access_log_key ); ?>"><?php echo esc_html( $ticket_access_log_value ); ?></option>
							<?php
						}
						?>
					</select>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( "Select whether a ticket's check-ins log should be loaded together with the ticket information. Please note that this might cause tickets to take longer to load or require additional server resources.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<?php endif; ?>

			<?php if ( 'integration' === $active_tab ) : ?>
				<?php settings_fields( 'fooevents-settings-integration' ); ?>
				<?php do_settings_sections( 'fooevents-settings-integration' ); ?>
			<tr valign="top">
				<th scope="row" colspan="3"><h3 class="fooevents-settings-section-title"><?php esc_attr_e( 'Google Maps', 'woocommerce-events' ); ?></h3></th>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Maps embed method', 'woocommerce-events' ); ?></th>
				<td>
					<select name="globalWooCommerceEventsGoogleMapsMethod" id="globalWooCommerceEventsGoogleMapsMethod" class="fooevents-search-list">
						<option value=""><?php esc_html_e( 'Iframe (Default - No API key required)', 'woocommerce-events' ); ?></option>
						<option value="API" <?php echo ( 'API' === $global_woocommerce_events_google_maps_method ) ? 'SELECTED' : ''; ?>><?php esc_html_e( 'JavaScript (Google Maps API key required)', 'woocommerce-events' ); ?></option>
					</select>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Add Google Maps to your product pages using iframes (free) or JavaScript (paid; requires a Google Maps API key).', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Google Maps API key', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsGoogleMapsAPIKey" id="globalWooCommerceEventsGoogleMapsAPIKey" value="<?php echo esc_html( $global_woocommerce_events_google_maps_api_key ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Please enter a valid Google Maps API key when using the JavaScript Maps embed method.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					<br/>
					<br/>
					<a href="https://help.fooevents.com/docs/topics/events/google-maps/" target="_blank"><?php esc_attr_e( 'Get help generating Google Maps API keys', 'woocommerce-events' ); ?></a>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" colspan="3"><h3 class="fooevents-settings-section-title"><?php esc_attr_e( 'Zoom Meetings and Webinars', 'woocommerce-events' ); ?></h3></th>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Account ID', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsZoomAccountID" id="globalWooCommerceEventsZoomAccountID" value="<?php echo esc_attr( $global_woocommerce_events_zoom_account_id ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Required to securely connect to your Zoom account in order to register attendees for your meetings/webinars.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Client ID', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsZoomClientID" id="globalWooCommerceEventsZoomClientID" value="<?php echo esc_attr( $global_woocommerce_events_zoom_client_id ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Required to securely connect to your Zoom account in order to register attendees for your meetings/webinars.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Client Secret', 'woocommerce-events' ); ?></th>
				<td>
					<input type="password" name="globalWooCommerceEventsZoomClientSecret" id="globalWooCommerceEventsZoomClientSecret" value="<?php echo esc_attr( $global_woocommerce_events_zoom_client_secret ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Required to securely connect to your Zoom account in order to register attendees for your meetings/webinars.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<tr valign="top">
				<th scope="row"></th>
				<td>
					<input id="fooevents_zoom_test_access" type="button" value="<?php esc_attr_e( 'Test Access', 'woocommerce-events' ); ?>" class="button button-secondary">
					<br/>
					<br/>
					<a href="https://help.fooevents.com/docs/topics/events/zoom-meetings-and-webinars/#generating-server-to-server-oauth-credentials" target="_blank"><?php esc_attr_e( 'Get help generating Zoom API Server-to-Server OAuth credentials', 'woocommerce-events' ); ?></a>
				</td>
			</tr> 
			<tr valign="top" id="globalWooCommerceEventsZoomUsers" style="display:
				<?php
				if ( '' !== $global_woocommerce_events_zoom_account_id && '' !== $global_woocommerce_events_zoom_client_id && '' !== $global_woocommerce_events_zoom_client_secret ) {
					?>
					table-row
					<?php
				} else {
					?>
					none<?php } ?>;">
				<th scope="row"><?php esc_attr_e( 'Users/Hosts', 'woocommerce-events' ); ?></th>
				<td>
					<input id="fooevents_zoom_fetch_users" type="button" value="<?php esc_attr_e( 'Fetch Users', 'woocommerce-events' ); ?>" class="button button-secondary">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Displays meetings/webinars on the Event Integration tab according to which users created them so that they can be linked to specific events. The default setting will only display meetings/webinars for the user that generated the Server-to-Server OAuth credentials. The second option is useful if you have multiple hosts on your Zoom account and you would like to restrict which meetings/webinars are visible (Hint: Shift-Click or Ctrl/Cmd+Click to select multiple hosts).', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					<br/>
					<br/>
					<div id="globalWooCommerceEventsZoomUsersContainer">
						<?php if ( empty( $global_woocommerce_events_zoom_users ) ) : ?>
							<input type="hidden" name="globalWooCommerceEventsZoomUsers" value="[]" />
							<input type="hidden" name="globalWooCommerceEventsZoomSelectedUsers[]" value="me" />
						<?php else : ?>
							<input type="hidden" name="globalWooCommerceEventsZoomUsers" value="<?php echo esc_attr( wp_json_encode( $global_woocommerce_events_zoom_users ) ); ?>" />
							<label><input type="radio" name="globalWooCommerceEventsZoomSelectedUserOption" id="globalWooCommerceEventsZoomSelectedUserOptionMe" value="me" <?php echo ( empty( $global_woocommerce_events_zoom_selected_user_option ) || 'me' === $global_woocommerce_events_zoom_selected_user_option ) ? 'CHECKED' : ''; ?>> <?php esc_attr_e( 'Show only meetings/webinars for the user that generated the Zoom API Server-to-Server OAuth credentials', 'woocommerce-events' ); ?></label>
							<br/><br/>
							<label><input type="radio" name="globalWooCommerceEventsZoomSelectedUserOption" id="globalWooCommerceEventsZoomSelectedUserOptionSelect" value="select" <?php echo ( 'select' === $global_woocommerce_events_zoom_selected_user_option ) ? 'CHECKED' : ''; ?>> <?php esc_attr_e( 'Show all meetings/webinars created by the following users:', 'woocommerce-events' ); ?></label>
							<br/><br/>
							<select name="globalWooCommerceEventsZoomSelectedUsers[]" id="globalWooCommerceEventsZoomSelectedUsers" multiple class="fooevents-multiselect" <?php echo ( 'select' !== $global_woocommerce_events_zoom_selected_user_option ) ? 'disabled' : ''; ?>>
								<?php
								foreach ( $global_woocommerce_events_zoom_users as $user ) {
									?>
										<option value="<?php echo esc_attr( $user['id'] ); ?>" <?php echo ! empty( $global_woocommerce_events_zoom_selected_users ) && in_array( $user['id'], $global_woocommerce_events_zoom_selected_users, true ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $user['first_name'] ) . ' ' . esc_attr( $user['last_name'] ) . ' - ' . esc_attr( $user['email'] ); ?></option>
									<?php
								}
								?>
							</select>
							<p><?php esc_attr_e( 'Please note that meeting/webinar load times will increase as more users are selected.', 'woocommerce-events' ); ?></p>
						<?php endif; ?>
					</div>                    
				</td>
			</tr> 
				<?php echo $eventbrite_options; ?>
			<tr valign="top">
				<th scope="row" colspan="3"><h3 class="fooevents-settings-section-title"><?php esc_attr_e( 'Mailchimp', 'woocommerce-events' ); ?></h3></th>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'API key', 'woocommerce-events' ); ?></th>
				<td>
					<input type="password" name="globalWooCommerceEventsMailchimpAPIKey" id="globalWooCommerceEventsMailchimpAPIKey" value="<?php echo esc_html( $global_woocommerce_events_mailchimp_api_key ); ?>">
					<?php if ( 'yes' === $ping_mailchimp ) : ?>
						<mark class="yes fooevents-malchimp-access-result"><span class="dashicons dashicons-yes"></span><?php esc_attr_e( 'Connected', 'woocommerce-events' ); ?></mark>
					<?php else : ?>
						<mark class="error fooevents-malchimp-access-result"><span class="dashicons dashicons-warning"></span><?php esc_attr_e( 'Not Connected', 'woocommerce-events' ); ?></mark>
					<?php endif; ?>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Enables FooEvents to integrate with your Mailchimp account. Login to Mailchimp and navigate to "Account" > "Extras" > "API keys" to create or reuse an existing API key.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Server prefix', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsMailchimpServer" id="globalWooCommerceEventsMailchimpServer" value="<?php echo esc_html( $global_woocommerce_events_mailchimp_server ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Login to your Mailchimp account and copy the parameter that comes immediately after "https://" in your web browser e.g. the server prefix is "us19" in https://us19.admin.mailchimp.com/', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
				<?php if ( 'yes' === $ping_mailchimp ) : ?>	
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Default audience list', 'woocommerce-events' ); ?></th>
				<td>
					<select name="globalWooCommerceEventsMailchimpList" id="globalWooCommerceEventsMailchimpList" class="fooevents-search-list">
						<option value="">(<?php esc_html_e( 'Not set', 'woocommerce-events' ); ?>)</option>
						<?php foreach ( $mailchimp_lists as $list_id => $list ) : ?>
							<option value="<?php echo esc_attr( (string) $list_id ); ?>" <?php echo( ( ! empty( $global_woocommerce_events_mailchimp_list ) && (string) $global_woocommerce_events_mailchimp_list === (string) $list_id ) ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $list ); ?></option>
						<?php endforeach; ?>		
					</select>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Select a default audience where you would like the attendees of all your events to be automatically added as contacts in Mailchimp. This can be overridden for each product (event) through the Event Integration settings. Note: The audience must first be setup in your Mailchimp account.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Default audience tags', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsMailchimpTags" id="globalWooCommerceEventsMailchimpTags" value="<?php echo esc_html( $global_woocommerce_events_mailchimp_tags ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Specify default audience tags as comma-separated values (,) which you would like to be associated with the attendees of all your events when they are automatically added as contacts in Mailchimp. This can be overridden for each product (event) through the Event Integration settings.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr>					
			<?php endif; ?>
			<tr valign="top">
				<th scope="row"></th>
				<td>
					<a href="https://help.fooevents.com/docs/topics/third-party-integration/mailchimp/" target="_blank"><?php esc_attr_e( 'Get help generating Mailchimp API keys', 'woocommerce-events' ); ?></a>
				</td>
			</tr>
				<?php if ( $subscriptions_enabled ) : ?>
			<tr valign="top">
				<th scope="row" colspan="3"><h3 class="fooevents-settings-section-title"><?php esc_attr_e( 'WooCommerce Subscriptions', 'woocommerce-events' ); ?></h3></th>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'Disable new ticket generation for subscription renewals', 'woocommerce-events' ); ?></th>
				<td>
					<input type="checkbox" name="globalWooCommerceEventsDisableSubTicketGen" id="globalWooCommerceEventsDisableSubTicketGen" value="yes" <?php echo ( 'yes' === $global_woocommerce_events_disable_sub_ticket_gen ) ? 'CHECKED' : ''; ?>>
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Only create tickets on parent order completion. No tickets will be generated on order renewals.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
				</td>
			</tr> 
			<?php endif; ?>
			<tr valign="top">
				<th scope="row" colspan="3"><h3 class="fooevents-settings-section-title"><?php esc_attr_e( 'AddToWallet', 'woocommerce-events' ); ?></h3></th>
			</tr>
			<tr valign="top">
				<th scope="row"><?php esc_attr_e( 'AddToWallet API key', 'woocommerce-events' ); ?></th>
				<td>
					<input type="text" name="globalWooCommerceEventsAddToWalletAPIKey" id="globalWooCommerceEventsAddToWalletAPIKey" value="<?php echo esc_html( $global_woocommerce_events_addtowallet_api_key ); ?>">
					<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Let attendees add their QR codes to their Android or Apple Wallet apps on their phones.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					<p><?php echo $global_woocommerce_events_addtowallet_credits; ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"></th>
				<td>
					<a href="https://help.fooevents.com/docs/topics/third-party-integration/addtowallet/" target="_blank"><?php esc_attr_e( 'Get help generating the AddToWallet API key', 'woocommerce-events' ); ?></a>
				</td>
			</tr>
			<?php endif; ?>
			
			
		</table>
		<?php submit_button(); ?>
	</form>
</div>
