<?php
/**
 * End date options template.
 *
 * @link https://www.fooevents.com
 * @package fooevents-multiday-events
 */

?>
<div class="fooevents_multiday_options type_sequential type_select">
<div class="options_group">
<p class="form-field fooevents-custom-text-inputs">
	<label><?php esc_attr_e( 'Day:', 'fooevents-multiday-events' ); ?></label>
	<input type="text" id="WooCommerceEventsDayOverride" name="WooCommerceEventsDayOverride" value="<?php echo esc_attr( $woocommerce_events_day_override ); ?>"/>
	<input type="text" id="WooCommerceEventsDayOverridePlural" name="WooCommerceEventsDayOverridePlural" value="<?php echo esc_attr( $woocommerce_events_day_override_plural ); ?>"/>
	<img class="help_tip" data-tip="<?php esc_attr_e( "Change 'Day' to your own custom text for this event.", 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
</p>
</div>
</div>