<?php
/**
 * Booking term option template
 *
 * @link https://www.fooevents.com
 * @package fooevents-bookings
 */

?>
<div class="fooevents_booking_options type_bookings">
<div class="options_group">
<p class="form-field fooevents-custom-text-inputs">
	<label><?php esc_attr_e( 'Slot:', 'fooevents-bookings' ); ?></label>
	<input type="text" id="WooCommerceEventsBookingsSlotOverride" name="WooCommerceEventsBookingsSlotOverride" value="<?php echo esc_attr( $woocommerce_events_bookings_slot_override ); ?>"/>
	<input type="text" id="WooCommerceEventsBookingsSlotOverridePlural" name="WooCommerceEventsBookingsSlotOverridePlural" value="<?php echo esc_attr( $woocommerce_events_bookings_slot_override_plural ); ?>"/>
	<img class="help_tip" data-tip="<?php esc_attr_e( "Change 'Slot' to your own custom text for this event.", 'fooevents-bookings' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
</p>
</div>
<div class="options_group">
<p class="form-field fooevents-custom-text-inputs">
	<label><?php esc_attr_e( 'Date:', 'fooevents-bookings' ); ?></label>
	<input type="text" id="WooCommerceEventsBookingsDateOverride" name="WooCommerceEventsBookingsDateOverride" value="<?php echo esc_attr( $woocommerce_events_bookings_date_override ); ?>"/>
	<input type="text" id="WooCommerceEventsBookingsDateOverridePlural" name="WooCommerceEventsBookingsDateOverridePlural" value="<?php echo esc_attr( $woocommerce_events_bookings_date_override_plural ); ?>"/>
	<img class="help_tip" data-tip="<?php esc_attr_e( "Change 'Date' to your own custom text for this event.", 'fooevents-bookings' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
</p>
</div>
<div class="options_group">
<p class="form-field fooevents-custom-text-inputs">
	<label><?php esc_attr_e( 'Booking details:', 'fooevents-bookings' ); ?></label>
	<input type="text" id="WooCommerceEventsBookingsBookingDetailsOverride" name="WooCommerceEventsBookingsBookingDetailsOverride" value="<?php echo esc_attr( $woocommerce_events_bookings_booking_details_override ); ?>"/>
	<input type="text" id="WooCommerceEventsBookingsBookingDetailsOverridePlural" name="WooCommerceEventsBookingsBookingDetailsOverridePlural" value="<?php echo esc_attr( $woocommerce_events_bookings_booking_details_override_plural ); ?>"/>
	<img class="help_tip" data-tip="<?php esc_attr_e( "Change the 'Booking details' label to your own custom text for this event.", 'fooevents-bookings' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
</p>
</div>
</div>