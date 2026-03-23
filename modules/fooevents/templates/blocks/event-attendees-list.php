<?php
/**
 * Event Attendee block
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
if ( $attr['profilePictureSize'] ) {
	$size = $attr['profilePictureSize'];
	switch ( $size ) {
		case 'small':
			$avatar_size = 80;
			break;
		case 'medium':
			$avatar_size = 100;
			break;
		case 'large':
			$avatar_size = 200;
			break;
		default:
			$avatar_size = 100;
	}
}
?>
<div <?php echo get_block_wrapper_attributes(); ?>>		
	<?php if ( ! empty( $attendees['attendees'] ) ) : ?>
		<div class="fooevents-attendees fooevents-attendees-list woocommerce">
			<?php $counter = 0; ?>
			<table id="fooevents-attendee-list-compact" class="woocommerce-orders-table shop_table" width="100%">
				<?php if ( $attr['enableShowGravatar'] ) : ?>
					<th></td>
				<?php endif; ?>
				<?php if ( $attr['enableShowName'] ) : ?>
					<th><?php echo esc_attr( 'Name', 'woocommerce-events' ); ?></th>
				<?php endif; ?>
				<?php if ( $attr['enableShowCompany'] ) : ?>
					<th><?php echo esc_attr( 'Company', 'woocommerce-events' ); ?></th>
				<?php endif; ?>
				<?php if ( $attr['enableShowDesignation'] ) : ?>
					<th><?php echo esc_attr( 'Designation', 'woocommerce-events' ); ?></th>
				<?php endif; ?>
				<?php if ( $attr['enableShowPhone'] ) : ?>
					<th><?php echo esc_attr( 'Phone', 'woocommerce-events' ); ?></th>
				<?php endif; ?>
				<?php if ( $attr['enableShowEmail'] ) : ?>
					<th><?php echo esc_attr( 'Email', 'woocommerce-events' ); ?></th>
				<?php endif; ?>
			<?php foreach ( $attendees['attendees'] as $attendee ) : ?>
				<tr>
					<?php if ( $attr['enableShowGravatar'] ) : ?>
						<?php $avatar_url = get_avatar_url( $attendee['WooCommerceEventsAttendeeEmail'], array( 'size' => $avatar_size ) ); ?>
						<td class="fooevents-attendee-info-avatar fooevents-attendee-<?php echo $size; ?>"><img src="<?php echo esc_attr( $avatar_url ); ?>" alt="<?php echo esc_attr( $attendee['full_name'] ); ?>" /></td>
					<?php endif; ?>
					<?php if ( $attr['enableShowName'] ) : ?>
						<td class="fooevents-attendee-info-name">
							<strong>
							<?php if ( 'full_name' === $attr['nameFormat'] ) : ?>
								<?php echo esc_attr( $attendee['full_name'] ); ?>
							<?php endif; ?>
							<?php if ( 'nameFormat' === $attr['nameFormat'] ) : ?>
								<?php echo esc_attr( $attr['nameFormat'] ); ?>
							<?php endif; ?>
							<?php if ( 'first_name' === $attr['nameFormat'] ) : ?>
								<?php echo esc_attr( $attendee['first_name'] ); ?>
							<?php endif; ?>
							<?php if ( 'last_name' === $attr['nameFormat'] ) : ?>
								<?php echo esc_attr( $attendee['last_name'] ); ?>
							<?php endif; ?>
							<?php if ( 'first_name_initial' === $attr['nameFormat'] ) : ?>
								<?php echo esc_attr( $attendee['first_name_initial'] ); ?>
							<?php endif; ?>
							<?php if ( 'initial_last_name' === $attr['nameFormat'] ) : ?>
								<?php echo esc_attr( $attendee['initial_last_name'] ); ?>
							<?php endif; ?>
							</strong>
						</td>
					<?php endif; ?>
					<?php if ( $attr['enableShowCompany'] ) : ?>
						<td class="fooevents-attendee-info-company"><?php echo esc_attr( $attendee['WooCommerceEventsAttendeeCompany'] ); ?></td>
					<?php endif; ?>
					<?php if ( $attr['enableShowDesignation'] ) : ?>
						<td class="fooevents-attendee-info-designation"><?php echo esc_attr( $attendee['WooCommerceEventsAttendeeDesignation'] ); ?></td>
					<?php endif; ?>
					<?php if ( $attr['enableShowPhone'] ) : ?>
						<td class="fooevents-attendee-info-telephone"><?php echo esc_attr( $attendee['WooCommerceEventsAttendeeTelephone'] ); ?></td>
					<?php endif; ?>
					<?php if ( $attr['enableShowEmail'] ) : ?>
						<td class="fooevents-attendee-info-email"><a href="mailto:<?php echo esc_attr( $attendee['WooCommerceEventsAttendeeEmail'] ); ?>"><?php echo esc_attr( $attendee['WooCommerceEventsAttendeeEmail'] ); ?></a></td>
					<?php endif; ?>
				<?php ++$counter; ?>
				<tr>
			<?php endforeach; ?>
			</table>			
			<?php if ( $attr['enableTotalAttendees'] ) : ?>
				<p class="fooevents-attendees-total fooevents-attendees-list-total"><?php echo esc_attr( $attendees['total_attendees'] ); ?> <?php echo esc_attr( 'people are attending this event.', 'woocommerce-events' ); ?></p>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<p><?php echo esc_attr( 'No attendees found.', 'woocommerce-events' ); ?></p>
	<?php endif; ?>
</div>

