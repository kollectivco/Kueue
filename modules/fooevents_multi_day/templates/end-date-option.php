<?php
/**
 * End date options template.
 *
 * @link https://www.fooevents.com
 * @package fooevents-multiday-events
 */

?>
<div class="options_group type_sequential" id="WooCommerceEventsEndDateContainer">
	<p class="form-field">
		<label><?php esc_attr_e( 'End date:', 'fooevents-multiday-events' ); ?></label>
		<input type="text" id="WooCommerceEventsEndDate" class="WooCommerceEventsSelectDate" name="WooCommerceEventsEndDate" value="<?php echo esc_attr( $woocommerce_events_end_date ); ?>"/>
		<img class="help_tip" data-tip="<?php esc_attr_e( "The date that the event is scheduled to end. This is used as a label on your website and it's also used by the FooEvents Calendar to display a multi-day event.", 'fooevents-multiday-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</p>
</div>
