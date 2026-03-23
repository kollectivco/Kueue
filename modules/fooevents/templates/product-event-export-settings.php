<?php
/**
 * Event export settings template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="fooevents_exports" class="panel woocommerce_options_panel fooevents_options_panel">
	<h2><?php esc_attr_e( 'Event Export', 'woocommerce-events' ); ?></h2>

	
	<?php if ( ! empty( $post->ID ) ) : ?>
		<div class="options_group">
			<p class="form-field">
				<label><?php esc_attr_e( 'Include unpaid tickets:', 'woocommerce-events' ); ?></label><input type="checkbox" id="WooCommerceEventsExportUnpaidTicketsExport" name="WooCommerceEventsExportUnpaidTickets" value="on" <?php echo ( 'on' === $woocommerce_events_export_unpaid_tickets ) ? 'CHECKED' : ''; ?>> <img class="help_tip" data-tip="<?php esc_attr_e( 'Include unpaid tickets in exported file.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" /><span id="WooCommerceEventsExportMessage"></span><br />
				<label><?php esc_attr_e( 'Include billing details:', 'woocommerce-events' ); ?></label><input type="checkbox" id="WooCommerceEventsExportBillingDetailsExport" name="WooCommerceEventsExportBillingDetails" value="on" <?php echo ( 'on' === $woocommerce_events_export_billing_details ) ? 'CHECKED' : ''; ?>> <img class="help_tip" data-tip="<?php esc_attr_e( 'Include billing details in exported file.', 'woocommerce-events' ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" /><br /><br />				
				<a href="<?php echo esc_attr( site_url() ); ?>/wp-admin/admin-ajax.php?action=woocommerce_events_csv&event=<?php echo esc_attr( $post->ID ); ?><?php echo ( 'on' === $woocommerce_events_export_unpaid_tickets ) ? '&exportunpaidtickets=true' : ''; ?><?php echo ( 'on' === $woocommerce_events_export_billing_details ) ? '&exportbillingdetails=true' : ''; ?>" class="button" target="_BLANK"><?php esc_attr_e( 'Download CSV of attendees', 'woocommerce-events' ); ?></a>
			</p>
		</div>
		<?php endif; ?>
</div>
