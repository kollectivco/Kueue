<?php
/**
 * Order ticket details template
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
		<p><em><?php echo sprintf( esc_attr__( "Tickets will only be generated when the order is changed to '%s'", 'woocommerce-events' ), $status_output ); ?></em></p>
	</div>
	<div class="clear"></div>
	<?php foreach ( $woocommerce_events_order_tickets as $event ) : ?>
		<div class="fooevents-orders-ticket-details-container">
			<div class="fooevents-orders-ticket-details-tickets">
				<?php foreach ( $event['tickets'] as $ticket ) : ?>                 
					<div class="fooevents-orders-ticket-details-tickets-inner">  

						<div id="fooevents-ticket-details-head">
							<h1><?php echo( empty( $ticket['WooCommerceEventsAttendeeName'] ) ) ? esc_attr( $ticket['WooCommerceEventsPurchaserFirstName'] ) . ' ' . esc_attr( $ticket['WooCommerceEventsPurchaserLastName'] ) : esc_attr( $ticket['WooCommerceEventsAttendeeName'] ) . ' ' . esc_attr( $ticket['WooCommerceEventsAttendeeLastName'] ); ?></h1>
							<div class="clear">&nbsp;</div>
						</div>                            

						<table id="fooevents-order-attendee-details" cellpadding="0" cellspacing="0"> 
							<?php if ( ! empty( $ticket['WooCommerceEventsBookingOptions']['date'] ) ) : ?>
								<?php if ( ! empty( $ticket['WooCommerceEventsBookingOptions']['slot'] ) ) : ?>
									<tr>
										<td><strong><?php echo sprintf( esc_attr__( 'Booking %s', 'woocommerce-events' ), esc_attr( $ticket['WooCommerceEventsBookingOptions']['slot_term'] ) ); ?>:</strong></td>
										<td><?php echo esc_attr( $ticket['WooCommerceEventsBookingOptions']['slot'] ); ?></td>
									</tr>
								<?php endif; ?>
								<?php if ( ! empty( $ticket['WooCommerceEventsBookingOptions']['date'] ) ) : ?>    
									<tr>
										<td><strong><?php echo sprintf( esc_attr__( 'Booking %s', 'woocommerce-events' ), esc_attr( $ticket['WooCommerceEventsBookingOptions']['date_term'] ) ); ?>:</strong></td>
										<td><?php echo esc_attr( $ticket['WooCommerceEventsBookingOptions']['date'] ); ?></td>
									</tr>
								<?php endif; ?>
								<?php if ( ! empty( $ticket['WooCommerceEventsBookingOptions']['WooCommerceEventsZoomText'] ) ) : ?>
									<tr>
										<td valign="top"><strong><?php echo esc_attr_e( 'Zoom Meeting / Webinar', 'woocommerce-events' ); ?>:</strong></td>
										<td valign="top"><?php echo nl2br( wp_kses_post( $ticket['WooCommerceEventsBookingOptions']['WooCommerceEventsZoomText'] ) ); ?></td>
									</tr>
								<?php endif; ?>
							<?php endif; ?>
							<?php if ( ! empty( $ticket['WooCommerceEventsSeatingFields'] ) ) : ?>
								<?php $woocommerce_events_seating_fields_keys = array_keys( $ticket['WooCommerceEventsSeatingFields'] ); ?>
								<tr>
									<td><strong><?php echo esc_html( $ticket['WooCommerceEventsSeatingRowOverride'] ); ?></strong></td>
									<td><?php echo esc_attr( $ticket['WooCommerceEventsSeatingFields'][ $woocommerce_events_seating_fields_keys[0] ] ); ?></span></td>
								</tr>
								<tr>
									<td><strong><?php echo esc_html( $ticket['WooCommerceEventsSeatingSeatOverride'] ); ?></strong></td>
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
									<td>
										<strong><?php esc_attr_e( 'Telephone:', 'woocommerce-events' ); ?></strong></td>
									<td><?php echo( empty( $ticket['WooCommerceEventsAttendeeTelephone'] ) ) ? esc_attr( $ticket['WooCommerceEventsPurchaserPhone'] ) : esc_attr( $ticket['WooCommerceEventsAttendeeTelephone'] ); ?></td>
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
					<table class="fooevents-order-event-details"> 
						<?php if ( ! empty( $event['WooCommerceEventsDate'] ) && trim( $event['WooCommerceEventsDate'] ) !== '' ) : ?>
							<tr>
								<td><strong><?php esc_attr_e( 'Date:', 'woocommerce-events' ); ?></strong></td>
								<td><?php echo( empty( $event['WooCommerceEventsDate'] ) ) ? '' : esc_attr( $event['WooCommerceEventsDate'] ); ?></td>
							</tr>
						<?php endif; ?>
						<?php if ( ! empty( $event['WooCommerceEventsStartTime'] ) && trim( $event['WooCommerceEventsStartTime'] ) !== '' && empty( $ticket['WooCommerceEventsBookingOptions']['slot'] ) ) : ?>
							<tr>
								<td><strong><?php esc_attr_e( 'Time:', 'woocommerce-events' ); ?></strong></td>
								<td><?php echo( empty( $event['WooCommerceEventsStartTime'] ) ) ? '' : esc_attr( $event['WooCommerceEventsStartTime'] ); ?> <?php echo( empty( $event['WooCommerceEventsEndTime'] ) ) ? '' : ' - ' . esc_attr( $event['WooCommerceEventsEndTime'] ); ?> </td>
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
