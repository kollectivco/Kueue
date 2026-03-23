<?php
/**
 * Order tickets generated template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="fooevents-orders-ticket-details">
	<div class="fooevents-notice">
		<p><em><?php esc_attr_e( 'The following ticket/s have been generated for this order.', 'woocommerce-events' ); ?></em></p>
	</div>
	<div class="clear"></div>
	<?php foreach ( $woocommerce_events_order_tickets as $event ) : ?>
		<div class="fooevents-orders-ticket-details-container">

			<div class="fooevents-orders-ticket-details-tickets">
				<?php foreach ( $event['tickets'] as $ticket ) : ?>
					<div class="fooevents-orders-ticket-details-tickets-inner"> 

						<div id="fooevents-ticket-details-head">
							<img src="<?php echo esc_attr( $this->config->barcode_url ) . esc_attr( $ticket['WooCommerceEventsTicketHash'] ) . '-' . esc_attr( $ticket['WooCommerceEventsTicketID'] ); ?>.png" class="ticket-code" />
							<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeName'] ) && ! empty( $ticket['WooCommerceEventsAttendeeLastName'] ) ) : ?>
							<h1><?php echo esc_attr( $ticket['WooCommerceEventsAttendeeName'] ) . ' ' . esc_attr( $ticket['WooCommerceEventsAttendeeLastName'] ); ?></h1>  
							<?php else : ?>
							<h1><?php echo esc_attr( $ticket['customerFirstName'] ) . ' ' . esc_attr( $ticket['customerLastName'] ); ?></h1>  
							<?php endif; ?>
							<h3><a href="post.php?post=<?php echo esc_attr( $ticket['ID'] ); ?>&action=edit" target="_BLANK">#<?php echo esc_attr( $ticket['WooCommerceEventsTicketID'] ); ?></a></h3>
							<?php if ( 'ticketnumberformatted' === $ticket['WooCommerceEventsTicketIdentifierOutput'] ) : ?>
								<h3><?php echo esc_attr( $ticket['WooCommerceEventsTicketNumberFormatted'] ); ?></h3>
							<?php endif; ?>
							<div class="clear"></div>
						</div>
						<table id="fooevents-order-attendee-details" cellpadding="0" cellspacing="0"> 
							<?php if ( ! empty( $ticket['WooCommerceEventsBookingDate'] ) ) : ?> 
								<?php if ( ! empty( $ticket['WooCommerceEventsBookingSlot'] ) ) : ?> 
									<tr>
										<td><strong><?php echo esc_attr( $ticket['WooCommerceEventsBookingsSlotTerm'] ); ?>:</strong></td>
										<td><?php echo esc_attr( $ticket['WooCommerceEventsBookingSlot'] ); ?></td>
									</tr>
								<?php endif; ?>
								<?php if ( ! empty( $ticket['WooCommerceEventsBookingDate'] ) ) : ?> 
									<tr>
										<td><strong><?php echo esc_attr( $ticket['WooCommerceEventsBookingsDateTerm'] ); ?>:</strong></td>
										<td><?php echo esc_attr( $ticket['WooCommerceEventsBookingDate'] ); ?></td>
									</tr>
								<?php endif; ?>
								<?php if ( ! empty( $ticket['WooCommerceEventsZoomText'] ) ) : ?>
									<tr>
										<td valign="top"><strong><?php esc_attr_e( 'Zoom Meeting / Webinar', 'woocommerce-events' ); ?>:</strong></td>
										<td valign="top"><?php echo nl2br( wp_kses_post( $ticket['WooCommerceEventsZoomText'] ) ); ?></td>
									</tr>
								<?php endif; ?>
							<?php endif; ?>
							<?php if ( ! empty( $ticket['WooCommerceEventsSeatingFields'] ) ) : ?>
								<?php $woocommerce_events_seating_fields_keys = array_keys( $ticket['WooCommerceEventsSeatingFields'] ); ?>
								<tr>
									<td><strong><?php echo esc_html( $ticket['WooCommerceEventsSeatingRowOverride'] ); ?>:</strong></td>
									<td><?php echo esc_attr( $ticket['WooCommerceEventsSeatingFields'][ $woocommerce_events_seating_fields_keys[0] ] ); ?></span></td>
								</tr>
								<tr>
									<td><strong><?php echo esc_html( $ticket['WooCommerceEventsSeatingSeatOverride'] ); ?>:</strong></td>
									<td><?php echo esc_attr( $ticket['WooCommerceEventsSeatingFields'][ $woocommerce_events_seating_fields_keys[1] ] ); ?></td>
								</tr>
							<?php endif; ?>
							<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeEmail'] ) ) : ?>
								<tr>
									<td><strong><?php esc_attr_e( 'Email:', 'woocommerce-events' ); ?></strong></td>
									<td><a href="mailto:<?php echo esc_attr( $ticket['WooCommerceEventsAttendeeEmail'] ); ?>"><?php echo esc_attr( $ticket['WooCommerceEventsAttendeeEmail'] ); ?></a></td>
								</tr>
							<?php endif; ?>
							<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeTelephone'] ) ) : ?>
								<tr>
									<td><strong><?php esc_attr_e( 'Telephone:', 'woocommerce-events' ); ?></strong></td>
									<td><?php echo( empty( $ticket['WooCommerceEventsAttendeeTelephone'] ) ) ? '' : esc_attr( $ticket['WooCommerceEventsAttendeeTelephone'] ); ?></td>
								</tr>
							<?php endif; ?>
							<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeCompany'] ) ) : ?>
							<tr>
								<td><strong><?php esc_attr_e( 'Company:', 'woocommerce-events' ); ?></strong></td>
								<td><?php echo( empty( $ticket['WooCommerceEventsAttendeeCompany'] ) ) ? '' : esc_attr( $ticket['WooCommerceEventsAttendeeCompany'] ); ?></td>
							</tr>
							<?php endif; ?>
							<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeDesignation'] ) ) : ?>
							<tr>
								<td><strong><?php esc_attr_e( 'Designation:', 'woocommerce-events' ); ?></strong></td>
								<td><?php echo( empty( $ticket['WooCommerceEventsAttendeeDesignation'] ) ) ? '' : esc_attr( $ticket['WooCommerceEventsAttendeeDesignation'] ); ?></td>
							</tr>
							<?php endif; ?>
							<?php if ( ! empty( $ticket['WooCommerceEventsVariations'] ) || ! empty( $ticket['WooCommerceEventsCustomAttendeeFields'] ) ) : ?>
								<?php if ( ! empty( $ticket['WooCommerceEventsVariations'] ) ) : ?>
									<?php foreach ( $ticket['WooCommerceEventsVariations'] as $variation_name => $variation_val ) : ?>
							<tr>
								<td><strong><?php echo esc_attr( $variation_name ); ?>:</strong></td>
								<td><?php echo esc_attr( $variation_val ); ?></td>
							</tr>
							<?php endforeach; ?>
							<?php endif; ?>
								<?php if ( ! empty( $ticket['WooCommerceEventsCustomAttendeeFields'] ) ) : ?>
									<?php foreach ( $ticket['WooCommerceEventsCustomAttendeeFields'] as $key => $field ) : ?>
							<tr>
								<td><strong><?php echo esc_attr( $field['field'][ $key . '_label' ] ); ?>:</strong></td>
								<td><?php echo esc_attr( $field['value'] ); ?></td>
							</tr>
							<?php endforeach; ?>
							<?php endif; ?>
							<?php endif; ?>
						</table>
						<div class="clear"></div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="fooevents-orders-ticket-details-events">
				<div class="fooevents-orders-ticket-details-events-inner">
					<h3><?php echo esc_attr( $event['WooCommerceEventsName'] ); ?></h3>
						<table id="fooevents-order-attendee-details" cellpadding="0" cellspacing="0"> 
						<?php if ( ! empty( $event['WooCommerceEventsDate'] ) && trim( $event['WooCommerceEventsDate'] ) !== '' ) : ?>
							<tr>
								<td><strong><?php esc_attr_e( 'Date:', 'woocommerce-events' ); ?></strong></td>
								<td><?php echo( empty( $event['WooCommerceEventsDate'] ) ) ? '' : esc_attr( $event['WooCommerceEventsDate'] ); ?></td>
							</tr>
						<?php endif; ?>
						<?php if ( ! empty( $event['WooCommerceEventsStartTime'] ) && trim( $event['WooCommerceEventsStartTime'] ) !== '' && empty( $ticket['WooCommerceEventsBookingSlot'] ) ) : ?>
							<tr>
								<td><strong><?php esc_attr_e( 'Time:', 'woocommerce-events' ); ?></strong></td>
								<td><?php echo( empty( $event['WooCommerceEventsStartTime'] ) ) ? '' : esc_attr( $event['WooCommerceEventsStartTime'] ); ?> <?php echo( empty( $event['WooCommerceEventsEndTime'] ) ) ? '' : ' - ' . $event['WooCommerceEventsEndTime']; ?> </td>
							</tr>
						<?php endif; ?>
						<?php if ( ! empty( $event['WooCommerceEventsLocation'] ) && trim( $event['WooCommerceEventsLocation'] ) !== '' ) : ?>
							<tr>
								<td><strong><?php esc_attr_e( 'Venue:', 'woocommerce-events' ); ?></strong></td>
								<td><?php echo( empty( $event['WooCommerceEventsLocation'] ) ) ? '' : esc_attr( $event['WooCommerceEventsLocation'] ); ?></td>
							</tr> 
						<?php endif; ?>
						<?php if ( ! empty( $event['WooCommerceEventsZoomText'] ) && trim( $event['WooCommerceEventsZoomText'] ) !== '' ) : ?>        
							<tr>
								<td valign="top"><strong><?php esc_attr_e( 'Zoom Meetings / Webinars: ', 'woocommerce-events' ); ?></strong></td>
								<td valign="top"><?php echo nl2br( wp_kses_post( $event['WooCommerceEventsZoomText'] ) ); ?></td>
							</tr>
						<?php endif; ?>
						<tr>
							<td colspan="2">
								<a href="<?php echo esc_attr( $event['WooCommerceEventsURL'] ); ?>" target="_BLANK" class="button">View</a>
								<a href="post.php?post=<?php echo esc_attr( $event['WooCommerceEventsProductID'] ); ?>&action=edit" target="_BLANK" class="button">Edit</a>
							</td>
						</tr>
					</table>
					<div class="clear"></div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
	<?php endforeach; ?>
</div>
