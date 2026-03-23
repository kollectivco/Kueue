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
		<div id="WooCommerceEventsResendTicketMessage"></div>
	<tbody>     
			<tr valign="top">  
				<td>
					<input type="text" value="<?php echo esc_attr( $purchaser['customerEmail'] ); ?>" size="12" name="WooCommerceEventsResendTicketEmail" id="WooCommerceEventsResendTicketEmail" />
				</td>
				<td>
					<input type="submit" class="button" value="<?php esc_attr_e( 'Resend', 'woocommerce-events' ); ?>" name="WooCommerceEventsResendTicket" id="WooCommerceEventsResendTicket" />
				</td>
			</tr>
	</tbody>
</table>
