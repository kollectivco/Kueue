<?php
/**
 * Edit ticket template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="fooevents-ticket-details-container">
	<div id="fooevents-ticket-details-inner">
		<div id="fooevents-ticket-details-head">
			<img src="<?php echo esc_attr( $barcode_url ) . esc_attr( $ticket['WooCommerceEventsTicketHash'] ) . '-' . esc_attr( $ticket['WooCommerceEventsTicketID'] ) . esc_attr( $barcode_identifier ) . '.png'; ?>" class="ticket-code" />
			<h1><?php echo esc_attr( $ticket['WooCommerceEventsAttendeeName'] ) . ' ' . esc_attr( $ticket['WooCommerceEventsAttendeeLastName'] ); ?></h1>
			<h3>#<?php echo esc_attr( $ticket['WooCommerceEventsTicketID'] ); ?></h3>
			<?php if ( 'ticketnumberformatted' === $ticket['WooCommerceEventsTicketIdentifierOutput'] ) : ?>
				<h3><?php echo esc_attr( $ticket['WooCommerceEventsTicketNumberFormatted'] ); ?></h3>
			<?php endif; ?>
			<?php echo wp_kses_post( $pdf_ticket_link ); ?>
			<div class="clear"></div>
		</div>
		<div id="fooevents-ticket-details">
			<div class="ticket-details-row">
				<label><?php esc_attr_e( 'First Name:', 'woocommerce-events' ); ?></label>
				<input type="text" name="WooCommerceEventsAttendeeName" value="<?php echo esc_attr( $ticket['WooCommerceEventsAttendeeName'] ); ?>" />
			</div>
			<div class="ticket-details-row">
				<label><?php esc_attr_e( 'Last Name:', 'woocommerce-events' ); ?></label>
				<input type="text" name="WooCommerceEventsAttendeeLastName" value="<?php echo esc_attr( $ticket['WooCommerceEventsAttendeeLastName'] ); ?>" />
			</div>
			<div class="ticket-details-row">
				<label><?php esc_attr_e( 'Email:', 'woocommerce-events' ); ?></label>
				<input type="text" name="WooCommerceEventsAttendeeEmail" value="<?php echo esc_attr( $ticket['WooCommerceEventsAttendeeEmail'] ); ?>" />
			</div>
			<div class="ticket-details-row">
				<label><?php esc_attr_e( 'Telephone:', 'woocommerce-events' ); ?></label>
				<input type="text" name="WooCommerceEventsAttendeeTelephone" value="<?php echo esc_attr( $ticket['WooCommerceEventsAttendeeTelephone'] ); ?>" />
			</div>
			<div class="ticket-details-row">
				<label><?php esc_attr_e( 'Company:', 'woocommerce-events' ); ?></label>
				<input type="text" name="WooCommerceEventsAttendeeCompany" value="<?php echo esc_attr( $ticket['WooCommerceEventsAttendeeCompany'] ); ?>" />
			</div>
			<div class="ticket-details-row">
				<label><?php esc_attr_e( 'Designation:', 'woocommerce-events' ); ?></label>
				<input type="text" name="WooCommerceEventsAttendeeDesignation" value="<?php echo esc_attr( $ticket['WooCommerceEventsAttendeeDesignation'] ); ?>" />
			</div>
			<div class="clear"></div>
			<?php echo $booking_options; ?>
			<?php echo $custom_attendee_options; ?>
			<?php echo $seating_options; ?>
		</div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
	<input type="hidden" id="fooevents_ticket_raw_id" name="fooevents_ticket_raw_id" value="<?php echo esc_attr( $post->ID ); ?>" />
	<input type="hidden" id="fooevents_event_id" name="fooevents_event_id" value="<?php echo esc_attr( $ticket['WooCommerceEventsProductID'] ); ?>" />
</div>
<div id="fooevents-event-details-container">
	<table id="fooevents-event-details">

		<?php if ( ! empty( $ticket['WooCommerceEventsVariations'] ) ) : ?>
			<tr>
				<td colspan="2">
					<h3><?php esc_attr_e( 'Variation', 'woocommerce-events' ); ?></h3>
				</td>
			</tr>
			<?php foreach ( $ticket['WooCommerceEventsVariations'] as $variation_name_output => $variation_value ) : ?>
			<tr>
				<td>
					<strong><?php echo esc_attr( $variation_name_output ); ?>:</strong>
				</td>
				<td>
					<?php echo esc_attr( $variation_value ); ?>
				</td>
			</tr>        
			<?php endforeach; ?>
		<?php endif; ?>  

		<tr>
			<td colspan="2">
				<h3><?php echo esc_attr( $ticket['WooCommerceEventsName'] ); ?></h3>
			</td>
		</tr>
		<?php if ( ! empty( $ticket['WooCommerceEventsDate'] ) && trim( $ticket['WooCommerceEventsDate'] ) !== '' ) : ?>
		<tr>
			<td>
				<strong><?php esc_attr_e( 'Date: ', 'woocommerce-events' ); ?></strong>
			</td>
			<td>
				<?php echo esc_attr( $ticket['WooCommerceEventsDate'] ); ?>
			</td>
		</tr>
		<?php endif; ?>     
		<?php if ( ! empty( $ticket['WooCommerceEventsStartTime'] ) && trim( $ticket['WooCommerceEventsStartTime'] ) !== '' ) : ?>
			<tr>
				<td><strong><?php esc_attr_e( 'Time:', 'woocommerce-events' ); ?></strong></td>
				<td><?php echo( empty( $ticket['WooCommerceEventsStartTime'] ) ) ? '' : esc_attr( $ticket['WooCommerceEventsStartTime'] ); ?> <?php echo( empty( $ticket['WooCommerceEventsEndTime'] ) ) ? '' : ' - ' . esc_attr( $ticket['WooCommerceEventsEndTime'] ); ?> </td>
			</tr>
		<?php endif; ?>
		<?php if ( ! empty( $ticket['WooCommerceEventsLocation'] ) && trim( $ticket['WooCommerceEventsLocation'] ) !== '' ) : ?>        
		<tr>
			<td>
				<strong><?php esc_attr_e( 'Venue: ', 'woocommerce-events' ); ?></strong>
			</td>
			<td>
				<?php echo esc_attr( $ticket['WooCommerceEventsLocation'] ); ?>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( ! empty( $ticket['WooCommerceEventsZoomText'] ) && trim( $ticket['WooCommerceEventsZoomText'] ) !== '' ) : ?>        
		<tr>
			<td valign="top">
				<strong><?php esc_attr_e( 'Zoom Meetings / Webinars: ', 'woocommerce-events' ); ?></strong>
			</td>
			<td valign="top">
				<?php echo nl2br( wp_kses_post( $ticket['WooCommerceEventsZoomText'] ) ); ?>
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<td colspan="2">
				<a href="<?php echo esc_attr( $ticket['WooCommerceEventsURL'] ); ?>" target="_BLANK" class="button"><?php esc_attr_e( 'View', 'woocommerce-events' ); ?></a> <a href="post.php?post=<?php echo esc_attr( $ticket['WooCommerceEventsProductID'] ); ?>&action=edit" target="_BLANK" class="button"><?php esc_attr_e( 'Edit', 'woocommerce-events' ); ?></a>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<h3><?php esc_attr_e( 'Order Details', 'woocommerce-events' ); ?></h3>
			</td>
		</tr>
		<?php if ( $ticket['WooCommerceEventsOrderID'] ) : ?>
			<?php if ( 'CSV' == $ticket['WooCommerceEventsCreateType'] ) : ?>
				<tr>
					<td>
						<strong><?php esc_attr_e( 'Type: ', 'woocommerce-events' ); ?></strong>
					</td>
					<td>
						<?php esc_html_e( 'Imported ticket', 'woocommerce-events' ); ?>
					</td>
				</tr>
			<?php endif; ?>	
			<tr>
				<td>
					<strong><?php esc_attr_e( 'Order ID: ', 'woocommerce-events' ); ?></strong>
				</td>
				<td>
					<a href="post.php?post=<?php echo esc_attr( $ticket['WooCommerceEventsOrderID'] ); ?>&action=edit" target="_BLANK">#<?php echo esc_attr( $ticket['WooCommerceEventsOrderID'] ); ?></a></td>
			</tr>
			<?php if ( ! empty( $ticket['WooCommerceEventsOrderStatus'] ) && trim( $ticket['WooCommerceEventsOrderStatus'] ) !== '' ) : ?>   
			<tr>
				<td>
					<strong><?php esc_attr_e( 'Order Status: ', 'woocommerce-events' ); ?></strong>
				</td>
				<td>
					<?php echo esc_attr( $ticket['WooCommerceEventsOrderStatus'] ); ?>
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( ! empty( $ticket['WooCommerceEventsOrderTotal'] ) && trim( $ticket['WooCommerceEventsOrderTotal'] ) !== '' ) : ?>   
			<tr>
				<td>
					<strong><?php esc_attr_e( 'Order Amount: ', 'woocommerce-events' ); ?></strong>
				</td>
				<td>
					<?php echo wp_kses_post( $ticket['WooCommerceEventsOrderTotal'] ); ?>
				</td>
			</tr>
			<?php endif; ?>
		<?php else : ?>
			<tr>
				<td>
					<strong><?php esc_attr_e( 'Type: ', 'woocommerce-events' ); ?></strong>
				</td>
				<td>
					<?php esc_html_e( 'Imported ticket', 'woocommerce-events' ); ?>
				</td>
			</tr>		
		<?php endif; ?>	
		<tr>
			<td colspan="2">
				<h3><?php esc_attr_e( 'Purchaser Details', 'woocommerce-events' ); ?></h3>
			</td>
		</tr>
		<?php if ( empty( $ticket['customerID'] ) || (int) trim( $ticket['customerID'] ) === 0 ) : ?> 
		<tr>
			<td colspan="2">
				<?php esc_html_e( 'Guest customer', 'woocommerce-events' ); ?>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( ! empty( $ticket['customerFirstName'] ) && trim( $ticket['customerFirstName'] ) !== '' ) : ?> 
		<tr>
			<td>
				<strong><?php esc_attr_e( 'Name: ', 'woocommerce-events' ); ?></strong>
			</td>
			<td>
				<a href="user-edit.php?user_id=<?php echo esc_attr( $ticket['customerID'] ); ?>" target="_BLANK">
				<?php echo esc_attr( $ticket['customerFirstName'] ); ?> <?php echo esc_attr( $ticket['customerLastName'] ); ?> [#<?php echo esc_attr( $ticket['customerID'] ); ?>]</a>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( ! empty( $ticket['customerPhone'] ) && trim( $ticket['customerPhone'] ) !== '' ) : ?> 
		<tr>
			<td>
				<strong><?php esc_attr_e( 'Phone: ', 'woocommerce-events' ); ?></strong>
			</td>
			<td>
				<?php echo esc_attr( $ticket['customerPhone'] ); ?>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( ! empty( $ticket['customerEmail'] ) && trim( $ticket['customerEmail'] ) !== '' ) : ?> 
		<tr>
			<td>
				<strong><?php esc_attr_e( 'Email: ', 'woocommerce-events' ); ?></strong>
			</td>
			<td>
				<a href="mailto:<?php echo esc_attr( $ticket['customerEmail'] ); ?>"><?php echo esc_attr( $ticket['customerEmail'] ); ?></a>
			</td>
		</tr>
		<?php endif; ?> 
	</table>
	<div class="clear"></div>
</div>
<div class="clear"></div>
