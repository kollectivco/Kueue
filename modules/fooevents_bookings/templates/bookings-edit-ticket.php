<?php
/**
 * Booking edit ticket template
 *
 * @link https://www.fooevents.com
 * @package fooevents-bookings
 */

?>
<h3><?php echo esc_attr( $details_label ); ?></h3>
<span id="fooevents-bookings-info"></span>
<div class="ticket-details-row">
	<label><?php echo esc_attr( $slot_label ); ?></label>
	<select id="WooCommerceEventsBookingSlotID" name="WooCommerceEventsBookingSlotID">
		<option><?php echo esc_attr( sprintf( __( 'Select Booking %s', 'fooevents-bookings' ), $slot_label ) ); ?></option>
		<?php foreach ( $fooevents_bookings_options as $option_key => $option ) : ?>
			<?php
			$slot = '';
			if ( isset( $option['add_time'] ) && 'enabled' === $option['add_time'] ) {

				$slot = $option['label'] . ' ' . $option['formatted_time'];

			} else {

				$slot = $option['label'];

			}
			?>
			<?php if ( isset( $option['add_date'] ) ) : ?>
		<option value="<?php echo esc_attr( $option_key ); ?>" <?php echo ( $woocommerce_events_booking_slot_id === $option_key ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $slot ); ?></option>
		<?php endif; ?>    
		<?php endforeach; ?>
	</select>
</div>
<div class="ticket-details-row">
	<label><?php echo esc_attr( $date_label ); ?></label>
	<select id="WooCommerceEventsBookingDateID" name="WooCommerceEventsBookingDateID" data-placeholder="Select slot">
		<option><?php echo esc_attr( sprintf( __( 'Select Booking %s', 'fooevents-bookings' ), $date_label ) ); ?></option>
		<?php foreach ( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_date'] as $option_key => $option ) : ?>
			<?php if ( '' === $option['stock'] || 0 !== $option['stock'] || $option_key === $woocommerce_events_booking_date_id ) : ?>
		<option value="<?php echo esc_attr( $option_key ); ?>" <?php echo ( $woocommerce_events_booking_date_id === $option_key ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $option['date'] ); ?></option>
		<?php endif; ?>
		<?php endforeach; ?>
	</select>
</div>
<?php wp_nonce_field( 'fooevents_bookings_options_edit_ticket', 'fooevents_bookings_options_edit_ticket_nonce' ); ?>
<div class="clear"></div>
