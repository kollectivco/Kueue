<?php
/**
 * Edit ticket status template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<table class="form-table">
	<tbody>     
		<tr valign="top">  
			<td>
				<label><?php esc_attr_e( 'Set Status:', 'woocommerce-events' ); ?></label><Br />
			</td>
			<td>
				<select name="WooCommerceEventsStatus" id="WooCommerceEventsStatusMeta">
					<option value="Not Checked In" <?php echo ( 'Not Checked In' === $woocommerce_events_status ) ? 'SELECTED' : ''; ?>>Not Checked In</option>
					<option value="Checked In" <?php echo ( 'Checked In' === $woocommerce_events_status ) ? 'SELECTED' : ''; ?>>Checked In</option>
					<option value="Canceled" <?php echo ( 'Canceled' === $woocommerce_events_status || 'Cancelled' === $woocommerce_events_status ) ? 'SELECTED' : ''; ?>>Canceled</option>
					<option value="Unpaid" <?php echo ( 'Unpaid' === $woocommerce_events_status ) ? 'SELECTED' : ''; ?>>Unpaid</option>
				</select>
				<input type="hidden" value="true" name="ticket_status" />
			</td>
		</tr>
	</tbody>
</table>
<?php if ( ! empty( $woocommerce_events_multiday_status ) ) : ?>
<hr/>
<div id="WooCommerceEventsMultidayStatusMeta">
	<?php echo $woocommerce_events_multiday_status; ?>
</div>
<?php endif; ?>
