<?php
/**
 * HTML template for the FooEvents ticket options
 *
 * @link https://www.fooevents.com
 * @since 1.9.0
 * @package fooevents-pos
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="options_group">
	<p class="form-field">
		<label><?php echo esc_html( $fooeventspos_phrases['title_fooevents_ticket_barcode_qr_width'] ); ?></label>
		<input type="number" min="0" step="1" id="WooCommerceEventsPOSTicketBarcodeQRWidth" name="WooCommerceEventsPOSTicketBarcodeQRWidth" value="<?php echo ( empty( $fooevents_ticket_barcode_qr_width ) ) ? '72' : esc_attr( $fooevents_ticket_barcode_qr_width ); ?>" >
		<img class="help_tip" data-tip="<?php echo esc_attr( $fooeventspos_phrases['title_fooevents_ticket_barcode_qr_width_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</p>
</div>
<div class="options_group">
	<p class="form-field">
		<label><?php echo esc_html( $fooeventspos_phrases['checkbox_email_fooevents_tickets'] ); ?></label>
		<input type="checkbox" name="WooCommerceEventsPOSEnableTicketEmails" value="on" <?php echo ( empty( $fooevents_enable_ticket_emails ) || 'on' === $fooevents_enable_ticket_emails ) ? 'checked' : ''; ?>>
		<img class="help_tip" data-tip="<?php echo esc_attr( $fooeventspos_phrases['checkbox_email_fooevents_tickets_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</p>
</div>
