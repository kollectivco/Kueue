<?php
/**
 * Booking add ticket template
 *
 * @link https://www.fooevents.com
 * @package fooevents-bookings
 */

?>
<tr valign="top">
	<td>
		<h3><?php esc_attr_e( 'Booking Details', 'fooevents-bookings' ); ?></h3>
		<span id="fooevents-bookings-info"></span>
	</td>
	<td></td>
</tr>
<tr valign="top">
	<td width="50%">
		<label><?php echo esc_attr( $slot_label ); ?>:</label>
		<select id="WooCommerceEventsBookingSlotID" name="WooCommerceEventsBookingSlotID">
			<option value=""><?php echo esc_attr( $slot_default ); ?></option>
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
	</td>
	<td width="50%">
		<label><?php echo esc_attr( $date_label ); ?>:</label>
		<select id="WooCommerceEventsBookingDateID" name="WooCommerceEventsBookingDateID" data-placeholder="Select slot">
			<option value=""><?php echo esc_attr( $date_default ); ?></option>
			<?php foreach ( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_date'] as $option_key => $option ) : ?>
			<option value="<?php echo esc_attr( $option_key ); ?>" <?php echo ( $woocommerce_events_booking_date_id === $option_key ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $option['date'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</td>
</tr>
<?php wp_nonce_field( 'fooevents_bookings_options_add_ticket', 'fooevents_bookings_options_add_ticket_nonce' ); ?>
