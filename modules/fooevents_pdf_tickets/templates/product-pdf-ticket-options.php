<?php
/**
 * PDF Ticket theme options tempate
 *
 * @link https://www.fooevents.com
 * @package fooevents-pdf-tickets
 */

?>
<h2><?php esc_attr_e( 'PDF Settings', 'woocommerce-events' ); ?></h2>
<div class="options_group">
		<p class="form-field">
			<label><?php esc_attr_e( 'Email text:', 'fooevents-pdf-tickets' ); ?></label>
			<div class="form-field-editor"><?php wp_editor( $fooevents_pdf_tickets_email_text, 'FooEventsPDFTicketsEmailText' ); ?></div>
		</p>
</div>
<div class="options_group">
	<p class="form-field">
		<label><?php esc_attr_e( 'Ticket footer text:', 'fooevents-pdf-tickets' ); ?></label>
		<textarea name="FooEventsTicketFooterText" id="FooEventsTicketFooterText"><?php echo esc_attr( $fooevents_ticket_footer_text ); ?></textarea>
	</p>
</div>
<?php wp_nonce_field( 'fooevents_pdf_tickets_options', 'fooevents_pdf_tickets_options_nonce' ); ?>
