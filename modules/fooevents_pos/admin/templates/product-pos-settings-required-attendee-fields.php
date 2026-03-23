<?php
/**
 * HTML template for the POS settings required attendee fields
 *
 * @link https://www.fooevents.com
 * @since 1.0.2
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin/templates
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="options_group">
	<p class="form-field">
		<label><?php echo esc_html( $fooeventspos_phrases['title_attendee_name'] ); ?></label>
		<select name="WooCommerceEventsPOSAttendeeDetails">
			<option value="" <?php echo ( '' === $fooevents_pos_attendee_details ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_use_event_settings'] ); ?></option>
			<option value="optional" <?php echo ( 'optional' === $fooevents_pos_attendee_details ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_show_optional'] ); ?></option>
			<option value="required" <?php echo ( 'required' === $fooevents_pos_attendee_details ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_show_required'] ); ?></option>
			<option value="hide" <?php echo ( 'hide' === $fooevents_pos_attendee_details ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_hide'] ); ?></option>
		</select>
		<img class="help_tip" data-tip="<?php echo esc_attr( $fooeventspos_phrases['title_attendee_name_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</p>
</div>
<div class="options_group">
	<p class="form-field">
		<label><?php echo esc_html( $fooeventspos_phrases['title_attendee_email'] ); ?></label>
		<select name="WooCommerceEventsPOSAttendeeEmail">
			<option value="" <?php echo ( '' === $fooevents_pos_attendee_email ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_use_event_settings'] ); ?></option>	
			<option value="optional" <?php echo ( 'optional' === $fooevents_pos_attendee_email ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_show_optional'] ); ?></option>
			<option value="required" <?php echo ( 'required' === $fooevents_pos_attendee_email ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_show_required'] ); ?></option>
			<option value="hide" <?php echo ( 'hide' === $fooevents_pos_attendee_email ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_hide'] ); ?></option>
		</select>
		<img class="help_tip" data-tip="<?php echo esc_attr( $fooeventspos_phrases['title_attendee_email_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</p>
</div>
<div class="options_group">
	<p class="form-field">
		<label><?php echo esc_html( $fooeventspos_phrases['title_attendee_telephone'] ); ?></label>
		<select name="WooCommerceEventsPOSAttendeeTelephone">
			<option value="" <?php echo ( '' === $fooevents_pos_attendee_telephone ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_use_event_settings'] ); ?></option>	
			<option value="optional" <?php echo ( 'optional' === $fooevents_pos_attendee_telephone ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_show_optional'] ); ?></option>
			<option value="required" <?php echo ( 'required' === $fooevents_pos_attendee_telephone ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_show_required'] ); ?></option>
			<option value="hide" <?php echo ( 'hide' === $fooevents_pos_attendee_telephone ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_hide'] ); ?></option>
		</select>
		<img class="help_tip" data-tip="<?php echo esc_attr( $fooeventspos_phrases['title_attendee_telephone_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</p>
</div>
<div class="options_group">
	<p class="form-field">
		<label><?php echo esc_html( $fooeventspos_phrases['title_attendee_company'] ); ?></label>
		<select name="WooCommerceEventsPOSAttendeeCompany">
			<option value="" <?php echo ( '' === $fooevents_pos_attendee_company ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_use_event_settings'] ); ?></option>	
			<option value="optional" <?php echo ( 'optional' === $fooevents_pos_attendee_company ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_show_optional'] ); ?></option>
			<option value="required" <?php echo ( 'required' === $fooevents_pos_attendee_company ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_show_required'] ); ?></option>
			<option value="hide" <?php echo ( 'hide' === $fooevents_pos_attendee_company ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_hide'] ); ?></option>
		</select>
		<img class="help_tip" data-tip="<?php echo esc_attr( $fooeventspos_phrases['title_attendee_company_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</p>
</div>
<div class="options_group">
	<p class="form-field">
		<label><?php echo esc_html( $fooeventspos_phrases['title_attendee_designation'] ); ?></label>
		<select name="WooCommerceEventsPOSAttendeeDesignation">
			<option value="" <?php echo ( '' === $fooevents_pos_attendee_designation ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_use_event_settings'] ); ?></option>	
			<option value="optional" <?php echo ( 'optional' === $fooevents_pos_attendee_designation ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_show_optional'] ); ?></option>
			<option value="required" <?php echo ( 'required' === $fooevents_pos_attendee_designation ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_show_required'] ); ?></option>
			<option value="hide" <?php echo ( 'hide' === $fooevents_pos_attendee_designation ) ? 'selected' : ''; ?>><?php echo esc_html( $fooeventspos_phrases['text_hide'] ); ?></option>
		</select>
		<img class="help_tip" data-tip="<?php echo esc_attr( $fooeventspos_phrases['title_attendee_designation_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</p>
</div>
