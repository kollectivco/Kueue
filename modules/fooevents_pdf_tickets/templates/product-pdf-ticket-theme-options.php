<?php
/**
 * PDF Ticket theme options tempate
 *
 * @link https://www.fooevents.com
 * @package fooevents-pdf-tickets
 */

?>
<div class="options_group">
	<p class="form-field">
	<label><?php esc_attr_e( 'PDF ticket theme:', 'fooevents-pdf-tickets' ); ?></label>
	<select name="WooCommerceEventsPDFTicketTheme" id="WooCommerceEventsPDFTicketTheme">
		<?php foreach ( $themes as $theme => $theme_details ) : ?>
			<option value="<?php echo esc_attr( $theme_details['path'] ); ?>" <?php echo ( $woocommerce_events_pdf_ticket_theme === $theme_details['path'] ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $theme_details['name'] ); ?></option>
		<?php endforeach; ?>
	</select>
	<img class="help_tip" data-tip="<?php esc_attr_e( 'Select the PDF compatible ticket theme that will be used to style the PDF tickets that are attached to ticket emails.', 'fooevents-pdf-tickets' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />   
	</p> 
</div>
