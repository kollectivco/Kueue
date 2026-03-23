<?php
/**
 * Edit ticket resent ticket template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table">
	<div id="WooCommerceEventsResendOrderTicketMessage"></div>
	<?php if ( 'yes' === $woocommerce_events_tickets_generated || $imported_order_tickets ) : ?>	
	<tbody>     
			<tr valign="top">  
				<td>
					<input type="text" value="<?php echo esc_attr( $order->get_billing_email() ); ?>" size="12" name="WooCommerceEventsResendOrderTicketEmail" id="WooCommerceEventsResendOrderTicketEmail" />
				</td>
				<td>
					<input type="submit" class="button" value="<?php esc_attr_e( 'Resend', 'woocommerce-events' ); ?>" name="WooCommerceEventsResendOrderTicket" id="WooCommerceEventsResendOrderTicket" />
				</td>
			</tr>
	</tbody>
	<?php else : ?>	
		<div><?php esc_attr_e( 'Tickets have not yet been generated', 'woocommerce-events' ); ?></div>
	<?php endif; ?>	
</table>
