<?php
/**
 * Event terminology settings template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="fooevents_terminology" class="panel woocommerce_options_panel fooevents_options_panel">
	<h2><?php esc_attr_e( 'Event Terminology', 'woocommerce-events' ); ?></h2>
	<div class="options_group">
		<p class="form-field fooevents-custom-text-inputs">
			<span><strong><?php esc_attr_e( 'Singular', 'woocommerce-events' ); ?></strong></span>
			<span><strong><?php esc_attr_e( 'Plural', 'woocommerce-events' ); ?></strong></span>
		</p>
</div>
<div class="options_group">
		<p class="form-field fooevents-custom-text-inputs">
			<label><?php esc_attr_e( 'Attendee:', 'woocommerce-events' ); ?></label>
			<input type="text" id="WooCommerceEventsAttendeeOverride" name="WooCommerceEventsAttendeeOverride" value="<?php echo esc_attr( $woocommerce_events_attendee_override ); ?>"/>
			<input type="text" id="WooCommerceEventsAttendeeOverridePlural" name="WooCommerceEventsAttendeeOverridePlural" value="<?php echo esc_attr( $woocommerce_events_attendee_override_plural ); ?>"/>
			<img class="help_tip" data-tip="<?php esc_attr_e( "Change 'Attendee' to your own custom text for this event.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
</div>
<div class="options_group">
		<p class="form-field fooevents-custom-text-inputs">
			<label><?php esc_attr_e( 'Event Details:', 'woocommerce-events' ); ?></label>
			<input type="text" id="WooCommerceEventsEventDetailsOverride" name="WooCommerceEventsEventDetailsOverride" value="<?php echo esc_attr( $woocommerce_events_event_details_override ); ?>"/>
			<img class="help_tip" data-tip="<?php esc_attr_e( "Change 'Event Details' to your own custom text for this event.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
</div>
<div class="options_group">
		<p class="form-field fooevents-custom-text-inputs">
			<label><?php esc_attr_e( 'Book ticket:', 'woocommerce-events' ); ?></label>
			<input type="text" id="WooCommerceEventsTicketOverride" name="WooCommerceEventsTicketOverride" value="<?php echo esc_attr( $woocommerce_events_ticket_override ); ?>"/>
			<input type="text" id="WooCommerceEventsTicketOverridePlural" name="WooCommerceEventsTicketOverridePlural" value="<?php echo esc_attr( $woocommerce_events_ticket_override_plural ); ?>"/>
			<img class="help_tip" data-tip="<?php esc_attr_e( "Change 'Book ticket' to your own custom text for this event.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
</div>
<div class="options_group">
		<?php echo $multiday_term; ?>
		<?php echo $bookings_term_options; ?>
		<?php echo $seating_term_options; ?>
	</div>
</div>
