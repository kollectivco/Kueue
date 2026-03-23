<div class="options_group">
	<p class="form-field">
		<label><?php esc_attr_e( 'Display custom attendee details on ticket?', 'woocommerce-events' ); ?></label>
		<input type="checkbox" name="WooCommerceEventsIncludeCustomAttendeeDetails" value="on" <?php echo ( 'on' === $woocommerce_events_include_custom_attendee_details ) ? 'CHECKED' : ''; ?>>
		<img class="help_tip" data-tip="<?php esc_attr_e( 'This will display custom attendee fields on the ticket.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</p>
</div>
