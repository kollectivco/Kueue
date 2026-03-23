<?php
/**
 * End date options template.
 *
 * @link https://www.fooevents.com
 * @package fooevents-multiday-events
 */

?>
<div class="options_group type_sequential type_select" id="WooCommerceEventsNumDaysContainer">
	<p class="form-field">
			<label><?php esc_attr_e( 'Number of days:', 'fooevents-multiday-events' ); ?></label>
			<select name="WooCommerceEventsNumDays" id="WooCommerceEventsNumDays">
				<?php for ( $x = 1; $x <= 45; $x++ ) : ?>
				<option value="<?php echo esc_attr( $x ); ?>" <?php echo ( $x === $woocommerce_events_num_days ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $x ); ?></option>
				<?php endfor; ?>
			</select>
			<img class="help_tip" data-tip="<?php esc_attr_e( 'Select the number of days for multi-day events. This setting is used by the Event Check-ins apps to manage daily check-ins.', 'fooevents-multiday-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</p>
</div>
