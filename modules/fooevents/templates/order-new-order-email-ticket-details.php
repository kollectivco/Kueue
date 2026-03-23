<?php
/**
 * New order email ticket details template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php if ( ! empty( $woocommerce_events_order_tickets ) ) : ?>
	<?php $x = 0; ?>
	<?php foreach ( $woocommerce_events_order_tickets as $event ) : ?>
		<?php foreach ( $event['tickets'] as $ticket ) : ?> 
			<?php
			$woocommerce_events_event_details_new_order    = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsEventDetailsNewOrder', true );
			$woocommerce_events_display_attendee_new_order = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsDisplayAttendeeNewOrder', true );
			$woocommerce_events_display_bookings_new_order = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsDisplayBookingsNewOrder', true );
			$woocommerce_events_display_seatings_new_order = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsDisplaySeatingsNewOrder', true );
			$woocommerce_events_display_cust_att_new_order = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsDisplayCustAttNewOrder', true );
			?>
			<?php if ( ( 'on' === $woocommerce_events_event_details_new_order || 'on' === $woocommerce_events_display_attendee_new_order || 'on' === $woocommerce_events_display_bookings_new_order || 'on' === $woocommerce_events_display_seatings_new_order || 'on' === $woocommerce_events_display_cust_att_new_order ) && 0 === $x ) : ?>
		<h3><?php esc_attr_e( 'Ticket Details', 'woocommerce-events' ); ?></h3>
		<?php endif; ?>
			<?php if ( 'on' === $woocommerce_events_event_details_new_order ) : ?>
				<?php if ( ! empty( $event['WooCommerceEventsName'] ) ) : ?>
			<strong><a href="<?php echo esc_attr( $event['WooCommerceEventsURL'] ); ?>"><?php echo esc_attr( $event['WooCommerceEventsName'] ); ?></a></strong><br />
			<?php endif; ?>
				<?php if ( 'single' === $event['WooCommerceEventsType'] || 'seating' === $event['WooCommerceEventsType'] ) : ?>
					<?php if ( ! empty( $event['WooCommerceEventsDate'] ) ) : ?>
					<strong><?php esc_attr_e( 'Date', 'woocommerce-events' ); ?></strong>: <?php echo esc_attr( $event['WooCommerceEventsDate'] ); ?><br />
				<?php endif; ?>
					<?php if ( ! empty( $event['WooCommerceEventsStartTime'] ) ) : ?>
					<strong><?php esc_attr_e( 'Start time', 'woocommerce-events' ); ?></strong>: <?php echo esc_attr( $event['WooCommerceEventsStartTime'] ); ?><br />
				<?php endif; ?>
					<?php if ( ! empty( $event['WooCommerceEventsEndTime'] ) ) : ?>
					<strong><?php esc_attr_e( 'End time', 'woocommerce-events' ); ?></strong>: <?php echo esc_attr( $event['WooCommerceEventsEndTime'] ); ?><br />
				<?php endif; ?>
			<?php endif; ?>
				<?php if ( 'sequential' === $event['WooCommerceEventsType'] ) : ?>
					<?php if ( ! empty( $event['WooCommerceEventsDate'] ) ) : ?>
					<strong><?php esc_attr_e( 'Start date', 'woocommerce-events' ); ?></strong>: <?php echo esc_attr( $event['WooCommerceEventsDate'] ); ?><br />
				<?php endif; ?>
					<?php if ( ! empty( $event['WooCommerceEventsEndDate'] ) ) : ?>
					<strong><?php esc_attr_e( 'End date', 'woocommerce-events' ); ?></strong>: <?php echo esc_attr( $event['WooCommerceEventsEndDate'] ); ?><br />
				<?php endif; ?>    
					<?php if ( ! empty( $event['WooCommerceEventsStartTime'] ) ) : ?>
					<strong><?php esc_attr_e( 'Start time', 'woocommerce-events' ); ?></strong>: <?php echo esc_attr( $event['WooCommerceEventsStartTime'] ); ?><br />
				<?php endif; ?>
					<?php if ( ! empty( $event['WooCommerceEventsEndTime'] ) ) : ?>
					<strong><?php esc_attr_e( 'End time', 'woocommerce-events' ); ?></strong>: <?php echo esc_attr( $event['WooCommerceEventsEndTime'] ); ?><br />
				<?php endif; ?>
			<?php endif; ?>  
				<?php if ( 'select' === $event['WooCommerceEventsType'] ) : ?>
					<?php $y = 1; ?>    
					<?php if ( ! empty( $event['WooCommerceEventsSelectDate'] ) ) : ?>
						<?php foreach ( $event['WooCommerceEventsSelectDate'] as $date ) : ?>
					<strong><?php esc_attr_e( 'Day ', 'woocommerce-events' ); ?><?php echo esc_attr( $y ); ?></strong>: <?php echo esc_attr( $date ); ?><br />
					
							<?php if ( ! empty( $event['WooCommerceEventsSelectStartTime'] ) ) : ?>
							<strong><?php esc_attr_e( 'Start time', 'woocommerce-events' ); ?></strong>: <?php echo esc_attr( $event['WooCommerceEventsSelectStartTime'][ $y - 1 ] ); ?><br />
						<?php endif; ?>
							<?php if ( ! empty( $event['WooCommerceEventsSelectEndTime'] ) ) : ?>
							<strong><?php esc_attr_e( 'End time', 'woocommerce-events' ); ?></strong>: <?php echo esc_attr( $event['WooCommerceEventsSelectEndTime'][ $y - 1 ] ); ?><br />
						<?php endif; ?>
							<?php ++$y; ?>
					<?php endforeach; ?>
						<?php if ( ! empty( $event['WooCommerceEventsStartTime'] ) && empty( $event['WooCommerceEventsSelectStartTime'] ) ) : ?>
						<strong><?php esc_attr_e( 'Start time', 'woocommerce-events' ); ?></strong>: <?php echo esc_attr( $event['WooCommerceEventsStartTime'] ); ?><br />
					<?php endif; ?>
						<?php if ( ! empty( $event['WooCommerceEventsEndTime'] ) && empty( $event['WooCommerceEventsSelectEndTime'] ) ) : ?>
						<strong><?php esc_attr_e( 'End time', 'woocommerce-events' ); ?></strong>: <?php echo esc_attr( $event['WooCommerceEventsEndTime'] ); ?><br />
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>        
		<?php endif; ?>
			<?php if ( 'on' === $woocommerce_events_display_attendee_new_order ) : ?>
			<strong><?php esc_attr_e( 'Name', 'woocommerce-events' ); ?></strong>: <?php echo( empty( $ticket['WooCommerceEventsAttendeeName'] ) ) ? esc_attr( $ticket['WooCommerceEventsPurchaserFirstName'] ) . ' ' . esc_attr( $ticket['WooCommerceEventsPurchaserLastName'] ) : esc_attr( $ticket['WooCommerceEventsAttendeeName'] ) . ' ' . esc_attr( $ticket['WooCommerceEventsAttendeeLastName'] ); ?><br />
			<strong><?php esc_attr_e( 'Email', 'woocommerce-events' ); ?></strong>: <?php echo( empty( $ticket['WooCommerceEventsAttendeeEmail'] ) ) ? esc_attr( $ticket['WooCommerceEventsPurchaserEmail'] ) : esc_attr( $ticket['WooCommerceEventsAttendeeEmail'] ); ?><br />
				<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeTelephone'] ) ) : ?>
				<strong><?php esc_attr_e( 'Telephone', 'woocommerce-events' ); ?></strong>: <?php echo esc_attr( $ticket['WooCommerceEventsAttendeeTelephone'] ); ?><br />
			<?php endif; ?>
				<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeCompany'] ) ) : ?>
				<strong><?php esc_attr_e( 'Company', 'woocommerce-events' ); ?></strong>: <?php echo esc_attr( $ticket['WooCommerceEventsAttendeeCompany'] ); ?><br />
			<?php endif; ?>
				<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeDesignation'] ) ) : ?>
				<strong><?php esc_attr_e( 'Designation', 'woocommerce-events' ); ?></strong>: <?php echo esc_attr( $ticket['WooCommerceEventsAttendeeDesignation'] ); ?><br />
			<?php endif; ?>
		<?php endif; ?>    
			<?php if ( 'on' === $woocommerce_events_display_bookings_new_order ) : ?>
				<?php if ( ! empty( $ticket['WooCommerceEventsBookingOptions']['slot'] ) ) : ?>
				<strong><?php printf( esc_attr__( 'Booking %s', 'woocommerce-events' ), esc_attr( $ticket['WooCommerceEventsBookingOptions']['slot_term'] ) ); ?></strong>: <?php echo esc_attr( $ticket['WooCommerceEventsBookingOptions']['slot'] ); ?><br />
				<?php endif; ?>
				<?php if ( ! empty( $ticket['WooCommerceEventsBookingOptions']['date'] ) ) : ?>
		<strong><?php printf( esc_attr__( 'Booking %s', 'woocommerce-events' ), esc_attr( $ticket['WooCommerceEventsBookingOptions']['date_term'] ) ); ?></strong>: <?php echo esc_attr( $ticket['WooCommerceEventsBookingOptions']['date'] ); ?><br />
		<?php endif; ?>
		<?php endif; ?>
			<?php if ( 'on' === $woocommerce_events_display_seatings_new_order && ! empty( $ticket['WooCommerceEventsSeatingFields'] ) ) : ?>
				<?php $woocommerce_events_seating_fields_keys = array_keys( $ticket['WooCommerceEventsSeatingFields'] ); ?>
			<strong><?php echo esc_html( $ticket['WooCommerceEventsSeatingRowOverride'] ); ?>:</strong> <?php echo esc_attr( $ticket['WooCommerceEventsSeatingFields'][ $woocommerce_events_seating_fields_keys[0] ] ); ?> <br />
			<strong><?php echo esc_html( $ticket['WooCommerceEventsSeatingSeatOverride'] ); ?>:</strong> <?php echo esc_attr( $ticket['WooCommerceEventsSeatingFields'][ $woocommerce_events_seating_fields_keys[1] ] ); ?><br />
		<?php endif; ?>
			<?php if ( 'on' === $woocommerce_events_display_cust_att_new_order && ! empty( $ticket['WooCommerceEventsCustomAttendeeFields'] ) ) : ?>
				<?php foreach ( $ticket['WooCommerceEventsCustomAttendeeFields'] as $key => $field ) : ?>
				<strong><?php echo esc_attr( $field['field'][ $key . '_label' ] ); ?>:</strong> <?php echo esc_attr( $field['value'] ); ?><br />    
			<?php endforeach; ?>
			<br />	
		<?php endif; ?>    
			<?php ++$x; ?>   
	<?php endforeach; ?>
	<?php endforeach; ?>
	<br />
<?php endif; ?>
