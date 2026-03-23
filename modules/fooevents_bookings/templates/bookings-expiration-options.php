<?php
/**
 * Booking expiration options
 *
 * @link https://www.fooevents.com
 * @package fooevents-bookings
 */

?>
<div class="fooevents_booking_options type_bookings">
	<h2><?php esc_attr_e( 'Bookings Expiration', 'woocommerce-events' ); ?></h2>
	<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'Expire bookings', 'woocommerce-events' ); ?></label>
			<input type="checkbox" name="WooCommerceEventsBookingsExpirePassedDate" value="yes" <?php echo ( 'yes' === $woocommerce_events_bookings_expire_passed_date ) ? 'CHECKED' : ''; ?>>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'Hides bookings if the dates have already passed or at a specified amount of time before they are scheduled to start.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
		<p class="form-field">
			<label><?php esc_attr_e( 'Select time before:', 'woocommerce-events' ); ?></label>
			<select name="WooCommerceEventsBookingsExpireValue" id="WooCommerceEventsBookingsExpireValue">
				<?php for ( $x = 1; $x <= 60; $x++ ) : ?>
				<option value="<?php echo esc_attr( $x ); ?>" <?php echo ( (int) $woocommerce_events_bookings_expire_value === $x ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $x ); ?></option>
				<?php endfor; ?>
			</select>
			<select name="WooCommerceEventsBookingsExpireUnit" id="WooCommerceEventsBookingsExpireUnit">
				<option value="minute" <?php echo ( 'minute' === $woocommerce_events_bookings_expire_unit ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Minutes', 'woocommerce-events' ); ?></option>
				<option value="hour" <?php echo ( 'hour' === $woocommerce_events_bookings_expire_unit ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Hours', 'woocommerce-events' ); ?></option>
				<option value="day" <?php echo ( 'day' === $woocommerce_events_bookings_expire_unit ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Days', 'woocommerce-events' ); ?></option>	
				<option value="week" <?php echo ( 'week' === $woocommerce_events_bookings_expire_unit ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Weeks', 'woocommerce-events' ); ?></option>
				<option value="month" <?php echo ( 'month' === $woocommerce_events_bookings_expire_unit ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Months', 'woocommerce-events' ); ?></option>
				<option value="year" <?php echo ( 'year' === $woocommerce_events_bookings_expire_unit ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Years', 'woocommerce-events' ); ?></option>
			</select>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'Select the unit of time to be used to automatically expire bookings.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
</div>