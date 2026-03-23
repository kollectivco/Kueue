<?php
/**
 * PDF global options
 *
 * @link https://www.fooevents.com
 * @package fooevents-pdf-tickets
 */

?>
<?php settings_fields( 'fooevents-settings-pdf' ); ?>
<?php do_settings_sections( 'fooevents-settings-pdf' ); ?>
<tr valign="top">
	<th scope="row"><h2><?php esc_attr_e( 'PDF Tickets', 'woocommerce-events' ); ?></h2></th>
	<td></td>
	<td></td>
</tr>
<tr valign="top">
	<th scope="row"><?php esc_attr_e( 'Enable PDF tickets', 'fooevents-pdf-tickets' ); ?></th>
	<td>
		<input type="checkbox" name="globalFooEventsPDFTicketsEnable" id="globalFooEventsPDFTicketsEnable" value="yes" <?php echo ( 'yes' === $global_fooevents_pdf_tickets_enable ) ? 'CHECKED' : ''; ?>>
		<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Adds PDF ticket attachments to ticket emails.', 'fooevents-pdf-tickets' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</td>
</tr> 
<tr valign="top">
	<th scope="row"><?php esc_attr_e( 'Enable PDF ticket downloads', 'fooevents-pdf-tickets' ); ?></th>
	<td>
		<input type="checkbox" name="globalFooEventsPDFTicketsDownloads" id="globalFooEventsPDFTicketsDownloads" value="yes" <?php echo ( 'yes' === $global_fooevents_pdf_tickets_downloads ) ? 'CHECKED' : ''; ?>>
		<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Allows purchasers to download a copy of their PDF tickets from the My Account page.', 'fooevents-pdf-tickets' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</td>
</tr> 
<tr valign="top">
	<th scope="row"><?php esc_attr_e( 'Attach PDF ticket to HTML ticket email', 'fooevents-pdf-tickets' ); ?></th>
	<td>
		<input type="checkbox" name="globalFooEventsPDFTicketsAttachHTMLTicket" id="globalFooEventsPDFTicketsAttachHTMLTicket" value="yes" <?php echo ( 'yes' === $global_fooevents_pdf_tickets_attach_html_ticket ) ? 'CHECKED' : ''; ?>>
		<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Attaches the PDF ticket to the HTML ticket email when sent.', 'fooevents-pdf-tickets' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</td>
</tr>
<tr valign="top">
	<th scope="row"><?php esc_attr_e( 'Enable RTL character support', 'fooevents-pdf-tickets' ); ?></th>
	<td>
		<input type="checkbox" name="globalFooEventsPDFTicketsArabicSupport" id="globalFooEventsPDFTicketsArabicSupport" value="yes" <?php echo ( 'yes' === $global_fooevents_pdf_tickets_arabic_support ) ? 'CHECKED' : ''; ?>>
		<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'Displays righ-to-left characters (e.g. Arabic, Hebrew) in PDF tickets', 'fooevents-pdf-tickets' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</td>
</tr>
<tr valign="top">
	<th scope="row"><?php esc_attr_e( 'Font', 'fooevents-pdf-tickets' ); ?></th>
	<td>
		<select name="globalFooEventsPDFTicketsFont" id="globalFooEventsPDFTicketsFont">
			<option value="DejaVu Sans" <?php echo 'DejaVu Sans' === $global_fooevents_pdf_tickets_font ? 'Selected' : ''; ?>>DejaVu Sans</option>
			<option value="Firefly Sung" <?php echo 'Firefly Sung' === $global_fooevents_pdf_tickets_font ? 'Selected' : ''; ?>>Firefly Sung</option>
		</select>
		<img class="help_tip fooevents-tooltip" title="<?php esc_attr_e( 'DejaVu Sans is the default PDF font. Firefly Sung supports CJK (Chinese, Japanese, Korean) characters. Noto Sans JP supports Japanese characters.', 'fooevents-pdf-tickets' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</td>
</tr>
