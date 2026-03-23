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
$counter = 0;
?>
<div <?php echo get_block_wrapper_attributes(); ?>>		
	<?php if ( ! empty( $attendees['attendees'] ) ) : ?>
		<div class="fooevents-attendees fooevents-attendees-pictures">
			<?php if ( $attr['enableTotalAttendees'] ) : ?>
				<p class="fooevents-attendees-total"><strong><?php echo esc_attr( $attendees['total_attendees'] ); ?> <?php echo esc_attr( 'people are attending this event', 'woocommerce-events' ); ?></strong></p>
			<?php endif; ?>
			<?php if ( $attr['enableShowGravatar'] ) : ?>
				<?php foreach ( $attendees['attendees'] as $attendee ) : ?>
					<div class="fooevents-attendee fooevents-attendee fooevents-attendee-<?php echo $counter; ?> fooevents-attendee-size-<?php echo $size; ?>">
					<?php if ( $attendee['WooCommerceEventsAttendeeEmail'] ) : ?>
							<?php $avatar_url = get_avatar_url( $attendee['WooCommerceEventsAttendeeEmail'], array( 'size' => $avatar_size ) ); ?>
							<img src="<?php echo esc_attr( $avatar_url ); ?>" alt="<?php echo esc_attr( $attendee['full_name'] ); ?>" /><br />
						<?php endif; ?>
					</div>
					<?php $counter ++; ?>
				<?php endforeach; ?>
				<div class="fooevents-attendee-list-clear"></div>
			<?php endif; ?>
		</div> 
	<?php else : ?>
		<p><?php echo esc_attr( 'No attendees found.', 'woocommerce-events' ); ?></p>
	<?php endif; ?>
</div>