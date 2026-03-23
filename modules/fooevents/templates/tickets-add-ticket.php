<?php
/**
 * Add ticket template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="fooevents-add-ticket-container">
	<div id="fooevents-ticket-details-inner">

		<h3><?php esc_attr_e( 'Event Details', 'woocommerce-events' ); ?></h3>
		<div id="fooevents-ticket-details">
			<div class="ticket-details-row">
				<label><?php esc_attr_e( 'Select Event:', 'woocommerce-events' ); ?></label>
				<select name="WooCommerceEventsEvent" id="WooCommerceEventsEvent" class="required fooevents-search-list">
					<option value=""><?php esc_attr_e( 'Please select...', 'woocommerce-events' ); ?></option>
					<?php foreach ( $events as $event ) : ?>
						<option value="<?php echo esc_attr( $event->ID ); ?>"><?php echo esc_attr( $event->post_title ); ?> [<?php echo esc_attr( $event->ID ); ?>]</option>
					<?php endforeach; ?>
				</select> 
			</div>
			<div id="fooevents-event-variation-options"></div>
			<div class="clear"></div>  

			<div class="ticket-details-row">
				<h3><?php esc_attr_e( 'Purchaser Details', 'woocommerce-events' ); ?></h3>

				<div class="ticket-details-group">
					<label><?php esc_attr_e( 'Select Existing User:', 'woocommerce-events' ); ?></label>
					<select name="WooCommerceEventsClientID" id="WooCommerceEventsClientID" class="required fooevents-search-list-add-ticket">
						<option value="0"><?php esc_attr_e( 'Select...', 'woocommerce-events' ); ?></option>
						<?php foreach ( $users as $user ) : ?>
							<option value="<?php echo esc_attr( $user->ID ); ?>"><?php echo esc_attr( $user->display_name ); ?> - <?php echo esc_attr( $user->user_email ); ?> [<?php echo esc_attr( $user->ID ); ?>]</option>
						<?php endforeach; ?>
					</select> 
				</div>

				<div class="ticket-details-group">
					<label><?php esc_attr_e( 'Username:', 'woocommerce-events' ); ?></label>
					<input type="text" name="WooCommerceEventsPurchaserUserName" id="WooCommerceEventsPurchaserUserName" value="" class="required"/>
				</div>

				<div class="ticket-details-group">
					<label><?php esc_attr_e( 'Email:', 'woocommerce-events' ); ?></label>
					<input type="text" name="WooCommerceEventsPurchaserEmail" id="WooCommerceEventsPurchaserEmail" value="" class="required"/>
				</div>
				<div class="ticket-details-group">
					<label><?php esc_attr_e( 'First Name:', 'woocommerce-events' ); ?></label>
					<input type="text" name="WooCommerceEventsPurchaserFirstName" id="WooCommerceEventsPurchaserFirstName" value=""  class="required"/>
				</div>
				<div class="ticket-details-group">
					<label><?php esc_attr_e( 'Last Name:', 'woocommerce-events' ); ?></label>
					<input type="text" name="WooCommerceEventsPurchaserLastName" id="WooCommerceEventsPurchaserLastName" value=""  class="required"/>
				</div>
				<div class="ticket-details-group">
					<label><?php esc_attr_e( 'Display Name:', 'woocommerce-events' ); ?></label>
					<input type="text" name="WooCommerceEventsPurchaserDisplayName" id="WooCommerceEventsPurchaserDisplayName" value=""  class="required"/>
				</div>

			</div>
			<div class="ticket-details-row">
				<h3><?php esc_attr_e( 'Attendee Details', 'woocommerce-events' ); ?></h3>
				<div class="ticket-details-group">
					<label><?php esc_attr_e( 'First Name:', 'woocommerce-events' ); ?></label>
					<input type="text" name="WooCommerceEventsAttendeeName" id="WooCommerceEventsAttendeeName" value="" class="required"/>
				</div>
				<div class="ticket-details-group">
					<label><?php esc_attr_e( 'Last Name:', 'woocommerce-events' ); ?></label>
					<input type="text" name="WooCommerceEventsAttendeeLastName" id="WooCommerceEventsAttendeeLastName" value="" class="required"/>
				</div>
				<div class="ticket-details-group">
					<label><?php esc_attr_e( 'Email:', 'woocommerce-events' ); ?></label>
					<input type="text" name="WooCommerceEventsAttendeeEmail" id="WooCommerceEventsAttendeeEmail" value="" class="required"/>
				</div>
				<div class="ticket-details-group">
					<label><?php esc_attr_e( 'Phone number:', 'woocommerce-events' ); ?></label>
					<input type="text" name="WooCommerceEventsAttendeeTelephone" id="WooCommerceEventsAttendeeTelephone" value="" class=""/>
				</div>
				<div class="ticket-details-group">
					<label><?php esc_attr_e( 'Company name:', 'woocommerce-events' ); ?></label>
					<input type="text" name="WooCommerceEventsAttendeeCompany" id="WooCommerceEventsAttendeeCompany" value="" class=""/>
				</div>
				<div class="ticket-details-group">
					<label><?php esc_attr_e( 'Designation:', 'woocommerce-events' ); ?></label>
					<input type="text" name="WooCommerceEventsAttendeeDesignation" id="WooCommerceEventsAttendeeDesignation" value="" class=""/>
				</div>
			</div>
		</div>

		<div class="clear"></div>  

		<table id="fooevents-add-ticket-bookings-container" width="100%">
			<tbody></tbody>
		</table>

		<div class="clear"></div>  

		<div id="fooevents-add-ticket-attendee-fields-container">
		</div>

		<div id="fooevents-add-ticket-seating-container">
		</div>
		<div class="clear"></div>  
	</div>
	<div class="clear"></div>    
</div>
<div id="fooevents-event-details-container">
	<div id="fooevents-event-details">
		<table class="fooevents-event-details" class="form-table">
			<tbody>
				<tr valign="top">
					<td cosspan="2">
						<h3><?php esc_attr_e( 'Event Overview', 'woocommerce-events' ); ?></h3>
						<i><?php echo wp_kses_post( 'Select an event in the <strong>Event Details</strong> section.', 'woocommerce-events' ); ?></i>
					</td>
				</tr>
			</tbody>
		</table>
		<input type="hidden" name="add_ticket" id="add_ticket" value="true" class="required"/>
		<div class="clear"></div>
	</div>
</div>
<?php wp_nonce_field( 'fooevents_add_ticket_page', 'fooevents_add_ticket_page_nonce' ); ?>
<div class="clear"></div>  
