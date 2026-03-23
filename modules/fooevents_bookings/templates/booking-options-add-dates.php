<?php
/**
 * Booking options add date template
 *
 * @link https://www.fooevents.com
 * @package fooevents-bookings
 */

?>

<div id="fooevents-dialog-add-dates" title="Booking Wizard">
	<div class="fooevents-dialog-add-dates-loading"></div>
	<form>
		<fieldset>
			<input type="text" style="width: 0; height: 0; top: -100px; position: absolute;"/><!-- Disable datepicker auto-open -->
			<div class="fooevents-dialog-add-dates-row">
				<div class="fooevents-dialog-add-dates-column">
					<h4><?php echo esc_attr__( 'Start Date', 'fooevents-bookings' ); ?></h4>
					<input type="text" id="fooevents_wizard_start_date" class="fooevents_bookings_date WooCommerceEventsBookingsSelectDate" value="" /> 
				</div>
				<div class="fooevents-dialog-add-dates-column">
					<h4><?php echo esc_attr__( 'End Date', 'fooevents-bookings' ); ?></h4>
					<input type="text" id="fooevents_wizard_end_date" class="fooevents_bookings_date WooCommerceEventsBookingsSelectDate" value="" /> 
				</div>
				<div class="clear clearfix"></div>
			</div>
			<div class="fooevents-dialog-add-dates-row">
				<h4><?php echo esc_attr__( 'Days of Week', 'fooevents-bookings' ); ?></h4>
				<label for="mon" class="fooevents-dialog-add-dates-weeek-day"><input type="checkbox" name="fooevents_wizard_week_day" id="mon" class="" value="Mon" /> <?php echo esc_attr__( 'Mon', 'fooevents-bookings' ); ?></label>
				<label for="tue" class="fooevents-dialog-add-dates-weeek-day"><input type="checkbox" name="fooevents_wizard_week_day" id="tue" class="" value="Tues" /> <?php echo esc_attr__( 'Tue', 'fooevents-bookings' ); ?></label>
				<label for="wed" class="fooevents-dialog-add-dates-weeek-day"><input type="checkbox" name="fooevents_wizard_week_day" id="wed" class="" value="Weds" /> <?php echo esc_attr__( 'Wed', 'fooevents-bookings' ); ?></label>
				<label for="thur" class="fooevents-dialog-add-dates-weeek-day"><input type="checkbox" name="fooevents_wizard_week_day" id="thur" class="" value="Thurs" /> <?php echo esc_attr__( 'Thur', 'fooevents-bookings' ); ?></label>
				<label for="fri" class="fooevents-dialog-add-dates-weeek-day"><input type="checkbox" name="fooevents_wizard_week_day" id="fri" class="" value="Fri" /> <?php echo esc_attr__( 'Fri', 'fooevents-bookings' ); ?></label>
				<label for="sat" class="fooevents-dialog-add-dates-weeek-day"><input type="checkbox" name="fooevents_wizard_week_day" id="sat" class="" value="Sat" /> <?php echo esc_attr__( 'Sat', 'fooevents-bookings' ); ?></label>
				<label for="sun" class="fooevents-dialog-add-dates-weeek-day"><input type="checkbox" name="fooevents_wizard_week_day" id="sun" class="" value="Sun" /> <?php echo esc_attr__( 'Sun', 'fooevents-bookings' ); ?></label>
			</div>
			<div class="fooevents-dialog-add-dates-row">
				<div class="fooevents-dialog-add-dates-column">
					<h4><?php echo esc_attr__( 'Default Stock Availability', 'fooevents-bookings' ); ?></h4>
					<input id="fooevents_wizard_stock" type="number" min="0"  value="" class="" autocomplete="off" maxlength="10" placeholder="<?php echo esc_attr__( 'Unlimited stock', 'fooevents-bookings' ); ?>" /> 
				</div>
				<div class="clear clearfix"></div>
			</div>
			<!-- Allow form submission with keyboard without duplicating the dialog button -->
			<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
		</fieldset>
	</form>
</div>
