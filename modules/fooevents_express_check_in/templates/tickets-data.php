<?php
/**
 * Ticket data template.
 *
 * @link https://www.fooevents.com
 * @package fooevents-express-check-in
 */

?>
<tr>
	<td><strong><a class="row-title" href="<?php echo esc_url( admin_url() ); ?>post.php?post=<?php echo esc_attr( $ticket->ID ); ?>&action=edit" aria-label="<?php echo esc_attr( $ticket->post_title ); ?> (Edit)" target="_BLANK"><?php echo esc_attr( $ticket->post_title ); ?></a></strong></td>
	<td><strong><a class="row-title" href="<?php echo esc_url( admin_url() ); ?>post.php?post=<?php echo esc_attr( get_post_meta( $ticket->ID, 'WooCommerceEventsOrderID', true ) ); ?>&action=edit" aria-label="<?php echo esc_attr( $ticket->post_title ); ?> (Edit)" target="_BLANK"><?php echo esc_attr( get_post_meta( $ticket->ID, 'WooCommerceEventsOrderID', true ) ); ?></a></strong></td>
	<td><?php echo esc_attr( get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserFirstName', true ) ); ?> <?php echo esc_attr( get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserLastName', true ) ); ?> </td>
	<td><?php echo esc_attr( get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeName', true ) ); ?> <?php echo esc_attr( get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeLastName', true ) ); ?></td>
	<td><span class="fooevents-express-check-in-event-name"><?php echo( ! empty( $event->post_title ) ) ? esc_attr( $event->post_title ) : ''; ?></span></td>
	<td>
		<?php if ( is_array( $ticket_variations ) ) : ?>
			<?php foreach ( $ticket_variations as $variation_name => $variation_value ) : ?>
				<div><?php echo esc_attr( $variation_name ); ?>:<br /> <?php echo esc_attr( $variation_value ); ?></div>
			<?php endforeach; ?>
		<?php endif; ?>
	</td>
	<td>
		<span class="fooevents-express-check-in-event-date">
		<?php if ( ! empty( $woocommerce_events_date ) ) : ?>	
		<?php echo esc_attr( $woocommerce_events_date ); ?><br />
		<?php endif; ?>
		<?php if ( ! empty( $woocommerce_events_start_time ) && ! empty( $woocommerce_events_date ) ) : ?>	
		<?php echo esc_attr( $woocommerce_events_start_time ); ?> - <?php echo esc_attr( $woocommerce_events_start_time_end ); ?>
		<?php endif; ?>
		</span>
	</td>
	<?php if ( $bookings_enabled ) : ?>
	<td>
		<?php if ( ! empty( $bookings_data ) ) : ?>
			<?php echo esc_attr( $bookings_data['WooCommerceEventsBookingSlot'] ); ?> <br />
			<?php echo esc_attr( $bookings_data['WooCommerceEventsBookingDate'] ); ?> 
		<?php endif; ?>
	</td>
	<?php endif; ?>
	<td class="fooevents-express-check-in-status-column">
		<span id="fooevents-express-check-in-status-<?php echo esc_attr( $ticket->ID ); ?>" class="fooevents-express-check-in-status fooevents-express-check-in-status-<?php echo esc_attr( $ticket_status_class ); ?>">
			<?php echo wp_kses_post( $ticket_status ); ?>
		</span>
	</td>	
	<td class="fooevents-express-check-in-button-td">
		<button class="button fooevents-express-check-in-show-actions"><?php echo esc_attr__( 'Options', 'fooevents-express-check-in' ); ?></button>
		<button id="fooevents-express-check-in-confirm-<?php echo esc_attr( $ticket->ID ); ?>" class="button <?php echo ( 'Checked In' !== $ticket_status ) ? 'button-primary' : ''; ?>  fooevents-express-check-in-control fooevents-express-check-in-confirm"><?php echo esc_attr__( 'Check-in', 'fooevents-express-check-in' ); ?></button>
		<div class="fooevents-express-check-in-actions-group">
			<button id="fooevents-express-check-in-cancel-<?php echo esc_attr( $ticket->ID ); ?>" class="button button-secondary fooevents-express-check-in-control fooevents-express-check-in-cancel"><?php echo esc_attr__( 'Cancel', 'fooevents-express-check-in' ); ?></button> 
			<button id="fooevents-express-check-in-reset-<?php echo esc_attr( $ticket->ID ); ?>" class="button fooevents-express-check-in-control fooevents-express-check-in-reset"><?php esc_attr_e( 'Reset', 'fooevents-express-check-in' ); ?></button>
			<a target="_blank" href="<?php echo esc_url( admin_url() ); ?>admin-ajax.php?action=woocommerce_events_attendee_badges&attendee_show=tickets&event=<?php echo esc_attr( $event->ID ); ?>&ticket=<?php echo esc_attr( substr( $ticket->post_title, 1 ) ); ?>" id="fooevents-express-check-in-print-<?php echo esc_attr( $ticket->ID ); ?>" class="button fooevents-express-check-in-control fooevents-express-check-in-print"><?php esc_attr_e( 'Print', 'fooevents-express-check-in' ); ?></a>
		</div>
	</td>
</tr>
