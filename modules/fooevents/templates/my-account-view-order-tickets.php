<?php
/**
 * My Account View Orders Ticket Templates
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="fooevents-notice"></div>

	<?php foreach ( $woocommerce_events_order_tickets as $event ) : ?>
		<!-- Event Details -->
		<table class="woocommerce-table woocommerce-table--order-details shop_table order_details fooevents-order-ticket fooevents-order-table" cellpadding="0" cellspacing="0"> 
			<tr>
				<td colspan="2">
					<h2><?php echo esc_attr( $event['WooCommerceEventsName'] ); ?></h2>
				</td>
			</tr>
			<?php if ( ! empty( $event['WooCommerceEventsDate'] ) && trim( $event['WooCommerceEventsDate'] ) !== '' && empty( $ticket['WooCommerceEventsBookingDate'] ) ) : ?>
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
		</table>
		<div class="clear"></div>

		<!-- Tickets -->

		<?php foreach ( $event['tickets'] as $ticket ) : ?>
			<table class="woocommerce-table woocommerce-table--order-details shop_table order_details fooevents-order-table" cellpadding="0" cellspacing="0"> 
				<tr>
					<td valign="middle" colspan="2">
						<img src="<?php echo esc_attr( $this->config->barcode_url ) . esc_attr( $ticket['WooCommerceEventsTicketHash'] ) . '-' . esc_attr( $ticket['WooCommerceEventsTicketID'] ); ?>.png" class="fooevents-order-ticket-code" />
						<div class="fooevents-order-ticket-details">
							<?php echo esc_attr( $event['WooCommerceEventsName'] ); ?>
							<?php if ( 'ticketnumberformatted' === $ticket['WooCommerceEventsTicketIdentifierOutput'] ) : ?>
								<h4 class="fooevents-order-ticket-id"><?php echo esc_attr( $ticket['WooCommerceEventsTicketNumberFormatted'] ); ?></h4>
							<?php else: ?>	
								<h4 class="fooevents-order-ticket-id">#<?php echo esc_attr( $ticket['WooCommerceEventsTicketID'] ); ?></h4>
							<?php endif; ?>
						</div>
					</td>
				</tr>
				<tr>
					<td><strong><?php esc_attr_e( 'Ticket Holder:', 'woocommerce-events' ); ?></strong></td>
					<td>
						<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeName'] ) && ! empty( $ticket['WooCommerceEventsAttendeeLastName'] ) ) : ?>
							<?php echo esc_attr( $ticket['WooCommerceEventsAttendeeName'] ) . ' ' . esc_attr( $ticket['WooCommerceEventsAttendeeLastName'] ); ?>
						<?php else : ?>
							<?php echo esc_attr( $ticket['customerFirstName'] ) . ' ' . esc_attr( $ticket['customerLastName'] ); ?>
						<?php endif; ?>	
					</td>
				</tr>				
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
								<?php if ( 'checkbox' === $field['field'][ $key . '_type' ] ) : ?>
									<td><?php echo ( 1 == esc_attr( $field['value'] ) ) ? esc_attr__( 'Yes', 'woocommerce-events' ) : esc_attr__( 'No', 'woocommerce-events' ); ?></td>
								<?php else : ?>	
									<td><?php echo esc_attr( $field['value'] ); ?></td>
								<?php endif; ?>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php endif; ?>
				<?php if ( true === $pdf_plugin_enabled ) : ?>
					<tr>
						<td valign="top" colspan="2">
							<?php echo wp_kses_post( $fooevents_pdf_tickets->display_ticket_download( $ticket['ID'], $this->config->barcode_path, $this->config->event_plugin_url ) ); ?>
						</td>
					</tr>	
				<?php endif; ?>		
			</table>
			<div class="clear"></div>
		<?php endforeach; ?>
	<?php endforeach; ?>
