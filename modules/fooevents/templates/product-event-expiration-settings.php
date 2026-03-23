<?php
/**
 * Event expiration settings template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="fooevents_expiration" class="panel woocommerce_options_panel fooevents-settings fooevents_options_panel">
	<h2><?php esc_attr_e( 'Event Expiration', 'woocommerce-events' ); ?></h2>
	<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'Event expiration date:', 'woocommerce-events' ); ?></label>
				<input type="text" id="WooCommerceEventsExpire" name="WooCommerceEventsExpire" value="<?php echo esc_attr( $woocommerce_events_expire ); ?>"/>
				<img class="help_tip" data-tip="<?php esc_attr_e( 'The date when the event will automatically expire.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</p>
	</div>
	<div class="options_group">
		<p class="form-field form-field-container">
			<label><?php esc_attr_e( 'Expiration message:', 'woocommerce-events' ); ?></label> <img class="help_tip" data-tip="<?php esc_attr_e( 'The message that will be displayed on the product page when the event has expired.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			<div class="form-field-editor"><?php wp_editor( $woocommerce_events_expire_message, 'WooCommerceEventsExpireMessage' ); ?></div>
			
		</p>
	</div>
	<h2><?php esc_attr_e( 'Ticket Expiration', 'woocommerce-events' ); ?></h2>
	<div class="options_group">
		<p class="form-field ">
			<label><?php esc_attr_e( 'Expiration type:', 'woocommerce-events' ); ?></label>
			<input type="radio" name="WooCommerceEventsTicketExpirationType" value="select" <?php echo ( 'time' !== $woocommerce_events_ticket_expiration_type ) ? 'CHECKED' : ''; ?>/> <?php esc_attr_e( 'Fixed date', 'woocommerce-events' ); ?> <img class="help_tip" data-tip="<?php esc_attr_e( 'Select either a fixed date or elapsed time since the ticket was purchased to automatically expire tickets.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" /><br />
			<input type="radio" name="WooCommerceEventsTicketExpirationType" value="time" <?php echo ( 'time' === $woocommerce_events_ticket_expiration_type ) ? 'CHECKED' : ''; ?>/> <?php esc_attr_e( 'Elapsed time', 'woocommerce-events' ); ?> <br />
			
		</p>
	</div>
	<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'Select fixed date:', 'woocommerce-events' ); ?></label>
			<input type="text" id="WooCommerceEventsTicketsExpireSelect" name="WooCommerceEventsTicketsExpireSelect" value="<?php echo esc_attr( $woocommerce_events_tickets_expire_select ); ?>"/>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'Select the fixed ticket expiration date.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'Select elapsed time:', 'woocommerce-events' ); ?></label>
			<select name="WooCommerceEventsTicketsExpireValue" id="WooCommerceEventsTicketsExpireValue">
				<?php for ( $x = 1; $x <= 60; $x++ ) : ?>
				<option value="<?php echo esc_attr( $x ); ?>" <?php echo ( (int) $woocommerce_events_tickets_expire_value === $x ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $x ); ?></option>
				<?php endfor; ?>
			</select>
			<select name="WooCommerceEventsTicketsExpireUnit" id="WooCommerceEventsTicketsExpireUnit">
				<option value="year" <?php echo ( 'year' === $woocommerce_events_tickets_expire_unit ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Years', 'woocommerce-events' ); ?></option>
				<option value="month" <?php echo ( 'month' === $woocommerce_events_tickets_expire_unit ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Months', 'woocommerce-events' ); ?></option>
				<option value="week" <?php echo ( 'week' === $woocommerce_events_tickets_expire_unit ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Weeks', 'woocommerce-events' ); ?></option>
				<option value="day" <?php echo ( 'day' === $woocommerce_events_tickets_expire_unit ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Days', 'woocommerce-events' ); ?></option>
				<option value="hour" <?php echo ( 'hour' === $woocommerce_events_tickets_expire_unit ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Hours', 'woocommerce-events' ); ?></option>
				<option value="minute" <?php echo ( 'minute' === $woocommerce_events_tickets_expire_unit ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Minutes', 'woocommerce-events' ); ?></option>
			</select>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'Select the unit of time to be used for elapsed time.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<?php echo $bookings_expiration_options; ?>
</div>
