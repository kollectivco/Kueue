<?php
/**
 * Ticket listing template.
 *
 * @link https://www.fooevents.com
 * @package fooevents-express-check-in
 */

?>
<?php if ( ! empty( $tickets->posts ) ) : ?>
<table class="fooevents-express-check-in-wrapper wp-list-table widefat fixed striped posts">
	<tbody id="the-list">
	<thead>
		<tr>
			<th width="100px"><?php echo esc_attr__( 'Ticket ID', 'fooevents-express-check-in' ); ?></th> 
			<th width="50px"><?php echo esc_attr__( 'Order', 'fooevents-express-check-in' ); ?></th> 
			<th width="80px" class="manage-column"><?php echo esc_attr__( 'Purchaser', 'fooevents-express-check-in' ); ?></th> 
			<th width="80px" class="manage-column"><?php echo esc_attr( $attendee_term ); ?></th> 
			<th width="80px" class="manage-column"><?php echo esc_attr__( 'Event', 'fooevents-express-check-in' ); ?></th>
			<th width="80px" class="manage-column"><?php echo esc_attr__( 'Variations', 'fooevents-express-check-in' ); ?></th> 
			<th width="130px" class="manage-column"><?php echo esc_attr__( 'Date', 'fooevents-express-check-in' ); ?></th>
			<?php if ( $bookings_enabled ) : ?>
			<th width="130px" class="manage-column"><?php echo esc_attr__( 'Bookings', 'fooevents-express-check-in' ); ?></th>
			<?php endif; ?>
			<th width="130px" class="manage-column fooevents-express-check-in-status-column"><?php echo esc_attr__( 'Check-in Status', 'fooevents-express-check-in' ); ?></th> 
			<th width="170px" class="manage-column fooevents-express-check-in-status-column"><?php echo esc_attr__( 'Actions', 'fooevents-express-check-in' ); ?></th> 
		</tr>
	</thead>
	<?php echo wp_kses_post( $tickets_data ); ?>
	</tbody>
</table>
<?php else : ?>
	<div class="fooevents-express-check-in-notickets">
		<h2><?php echo esc_attr__( 'No tickets found, please try again', 'fooevents-express-check-in' ); ?></h2>
	</div>
<?php endif; ?>
