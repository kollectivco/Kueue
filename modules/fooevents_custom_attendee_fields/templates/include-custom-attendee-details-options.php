<div class="options_group">
    <p class="form-field">
        <label><?php _e('Display custom attendee details on ticket?', 'fooevents-custom-attendee-fields'); ?></label>
        <input type="checkbox" name="WooCommerceEventsIncludeCustomAttendeeDetails" value="on" <?php echo ($WooCommerceEventsIncludeCustomAttendeeDetails == 'on')? 'CHECKED' : ''; ?>>
        <img class="help_tip" data-tip="<?php _e('This will display custom attendee fields on the ticket.', 'fooevents-custom-attendee-fields'); ?>" src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
    </p>
</div>