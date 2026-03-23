<?php
/**
 * End date options template.
 *
 * @link https://www.fooevents.com
 * @package fooevents-multiday-events
 */

?>
<div class="options_group type_select" id ="WooCommerceEventsSelectDateContainer">
	<?php if ( ! empty( $woocommerce_events_select_date ) ) : ?>
		<?php $x = 1; ?>
		<?php foreach ( $woocommerce_events_select_date as $event_date ) : ?>
	<div class="WooCommerceEventsSelectDateDay">
		<p class="form-field">
			<label><?php echo esc_attr( $day_term ); ?> <?php echo esc_attr( $x ); ?></label>
			<input type="text" class="WooCommerceEventsSelectDate" name="WooCommerceEventsSelectDate[]" value="<?php echo esc_attr( $event_date ); ?>"/>
		</p>
		<p class="form-field WooCommerceEventsSelectDateTimeContainer">
			<label><?php esc_attr_e( 'Start time:', 'woocommerce-events' ); ?></label>
			<select name="WooCommerceEventsSelectDateHour[]" class="WooCommerceEventsSelectDateHour" id="WooCommerceEventsSelectDateHour-<?php echo esc_attr( $x ); ?>">
				<?php for ( $y = 0; $y <= 23; $y++ ) : ?>
					<?php $y = sprintf( '%02d', $y ); ?>
				<option value="<?php echo esc_attr( $y ); ?>" <?php echo ( ( ! empty( $woocommerce_events_select_date_hour ) && $y === $woocommerce_events_select_date_hour[ $x - 1 ] ) || ( empty( $woocommerce_events_select_date_hour ) && $y === $woocommerce_events_hour ) ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $y ); ?></option>
				<?php endfor; ?>
			</select>
			<select name="WooCommerceEventsSelectDateMinutes[]" class="WooCommerceEventsSelectDateMinutes" id="WooCommerceEventsSelectDateMinutes-<?php echo esc_attr( $x ); ?>">
				<?php for ( $y = 0; $y <= 59; $y++ ) : ?>
					<?php $y = sprintf( '%02d', $y ); ?>
				<option value="<?php echo esc_attr( $y ); ?>"<?php echo ( ! empty( $woocommerce_events_select_date_minutes ) && $y === $woocommerce_events_select_date_minutes[ $x - 1 ] || ( empty( $woocommerce_events_select_date_hour ) && $y === $woocommerce_events_minutes ) ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $y ); ?></option>
				<?php endfor; ?>
			</select>
			<select name="WooCommerceEventsSelectDatePeriod[]" class="WooCommerceEventsSelectDatePeriod" id="WooCommerceEventsSelectDatePeriod-<?php echo esc_attr( $x ); ?>">
				<option value="">-</option>
				<option value="a.m." <?php echo ( ! empty( $woocommerce_events_select_date_period ) && isset( $woocommerce_events_select_date_period[ $x - 1 ] ) && 'a.m.' === $woocommerce_events_select_date_period[ $x - 1 ] || ( empty( $woocommerce_events_select_date_period ) && 'a.m.' === $woocommerce_events_period ) ) ? 'SELECTED' : ''; ?>>a.m.</option>
				<option value="p.m." <?php echo ( ! empty( $woocommerce_events_select_date_period ) && isset( $woocommerce_events_select_date_period[ $x - 1 ] ) && 'p.m.' === $woocommerce_events_select_date_period[ $x - 1 ] || ( empty( $woocommerce_events_select_date_period ) && 'p.m.' === $woocommerce_events_period ) ) ? 'SELECTED' : ''; ?>>p.m.</option>
			</select>
		</p>
		<p class="form-field WooCommerceEventsSelectDateTimeContainer">
			<label><?php esc_attr_e( 'End time:', 'woocommerce-events' ); ?></label>
			<select name="WooCommerceEventsSelectDateHourEnd[]" class="WooCommerceEventsSelectDateHourEnd" id="WooCommerceEventsSelectDateHourEnd-<?php echo esc_attr( $x ); ?>">
				<?php for ( $y = 0; $y <= 23; $y++ ) : ?>
					<?php $y = sprintf( '%02d', $y ); ?>
				<option value="<?php echo esc_attr( $y ); ?>" <?php echo ( ! empty( $woocommerce_events_select_date_hour_end ) && $y === $woocommerce_events_select_date_hour_end[ $x - 1 ] || ( empty( $woocommerce_events_select_date_hour_end ) && $y === $woocommerce_events_hour_end ) ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $y ); ?></option>
				<?php endfor; ?>
			</select>
			<select name="WooCommerceEventsSelectDateMinutesEnd[]" class="WooCommerceEventsSelectDateMinutesEnd" id="WooCommerceEventsSelectDateMinutesEnd-<?php echo esc_attr( $x ); ?>">
				<?php for ( $y = 0; $y <= 59; $y++ ) : ?>
					<?php $y = sprintf( '%02d', $y ); ?>
				<option value="<?php echo esc_attr( $y ); ?>" <?php echo ( ! empty( $woocommerce_events_select_date_minutes_end ) && $y === $woocommerce_events_select_date_minutes_end[ $x - 1 ] || ( empty( $woocommerce_events_select_date_minutes_end ) && $y === $woocommerce_events_minutes_end ) ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $y ); ?></option>
				<?php endfor; ?>
			</select>
			<select name="WooCommerceEventsSelectDatePeriodEnd[]" class="WooCommerceEventsSelectDatePeriodEnd" id="WooCommerceEventsSelectDatePeriodEnd-<?php echo esc_attr( $x ); ?>">
				<option value="">-</option>
				<option value="a.m." <?php echo ( ! empty( $woocommerce_events_select_date_period_end ) && isset( $woocommerce_events_select_date_period_end[ $x - 1 ] ) && 'a.m.' === $woocommerce_events_select_date_period_end[ $x - 1 ] || ( empty( $woocommerce_events_select_date_period_end ) && 'a.m.' === $woocommerce_events_end_period ) ) ? 'SELECTED' : ''; ?>>a.m.</option>
				<option value="p.m." <?php echo ( ! empty( $woocommerce_events_select_date_period_end ) && isset( $woocommerce_events_select_date_period_end[ $x - 1 ] ) && 'p.m.' === $woocommerce_events_select_date_period_end[ $x - 1 ] || ( empty( $woocommerce_events_select_date_period_end ) && 'p.m.' === $woocommerce_events_end_period ) ) ? 'SELECTED' : ''; ?>>p.m.</option>
			</select>
		</p>
	</div>
			<?php ++$x; ?>
	<?php endforeach; ?>
	<?php endif; ?>
</div>
<div class="options_group type_select" id="WooCommerceEventsSelectGlobalTimeContainer">
	<p class="form-field">
		<label><?php esc_attr_e( 'Set start/end times globally?', 'fooevents-multiday-events' ); ?></label>
		<input type="checkbox" name="WooCommerceEventsSelectGlobalTime" id="WooCommerceEventsSelectGlobalTime" value="on" <?php echo( 'on' === $woocommerce_events_select_global_time ) ? 'CHECKED' : ''; ?>>
		<img class="help_tip" data-tip="<?php esc_attr_e( 'Enable this option to use the same start and end times for each day of a multi-day event.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</p>
</div>
