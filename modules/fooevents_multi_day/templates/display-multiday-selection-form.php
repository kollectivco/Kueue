<?php
/**
 * Multi-day selection form
 *
 * @link https://www.fooevents.com
 * @package fooevents-multiday-events
 */

?>
<table class="form-table">
	<tbody>
			<?php for ( $x = 1; $x <= $woocommerce_events_num_days; $x++ ) : ?>
			<tr valign="top">  
				<td>
					<label><?php echo esc_attr( $day_term ); ?>: <?php echo esc_attr( $x ); ?></label><Br />
				</td>
				<td>
					<select name="WooCommerceEventsStatusMultidayEvent[<?php echo esc_attr( $x ); ?>]">
						<option value="Not Checked In" <?php echo ( 'Not Checked In' === $woocommerce_events_multiday_status[ $x ] ) ? 'SELECTED' : ''; ?>>Not Checked In</option>
						<option value="Checked In" <?php echo ( 'Checked In' === $woocommerce_events_multiday_status[ $x ] ) ? 'SELECTED' : ''; ?>>Checked In</option>
						<option value="Canceled" <?php echo ( 'Canceled' === $woocommerce_events_multiday_status[ $x ] || 'Cancelled' === $woocommerce_events_multiday_status[ $x ] ) ? 'SELECTED' : ''; ?>>Canceled</option>
						<option value="Unpaid" <?php echo ( 'Unpaid' === $woocommerce_events_multiday_status[ $x ] ) ? 'SELECTED' : ''; ?>>Unpaid</option>
					</select>
					<input type="hidden" value="true" name="ticket_status" />
					<?php wp_nonce_field( 'fooevents_multi_day_status_update', 'fooevents_multi_day_status_update_nonce' ); ?>
				</td>
			</tr>
			<?php endfor; ?>
	</tbody>
</table>
