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
<?php $items_per_row = $attr['numberOfGridItems']; ?>
<?php $column_width = 100 / $items_per_row; ?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>		
	<?php if ( ! empty( $attendees['attendees'] ) ) : ?>
		<div class="fooevents-attendees fooevents-attendees-grid fooevents-attendee-list-grid-<?php echo esc_attr( $items_per_row ); ?>">
		<?php $counter = 0; ?>
			<?php foreach ( $attendees['attendees'] as $attendee ) : ?> 
				<div class="fooevents-attendee fooevents-attendee-list fooevents-attendee-<?php echo esc_attr( $size ); ?>">
					<?php if ( $attr['enableShowGravatar'] && $attendee['WooCommerceEventsAttendeeEmail'] ) : ?>
						<?php $avatar_url = get_avatar_url( $attendee['WooCommerceEventsAttendeeEmail'], array( 'size' => $avatar_size ) ); ?>
						<img src="<?php echo esc_attr( $avatar_url ); ?>" alt="<?php echo esc_attr( $attendee['full_name'] ); ?>" />
					<?php endif; ?>
					<div class="fooevents-attendee-info">
						<?php if ( $attr['enableShowName'] ) : ?>
							<div class="fooevents-attendee-info-name">
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
							</div>
						<?php endif; ?>
						<?php if ( $attr['enableShowDesignation'] ) : ?>
							<div class="fooevents-attendee-info-designation"><?php echo esc_attr( $attendee['WooCommerceEventsAttendeeDesignation'] ); ?></div>
						<?php endif; ?>
						<?php if ( $attr['enableShowCompany'] ) : ?>
							<div class="fooevents-attendee-info-company"><?php echo esc_attr( $attendee['WooCommerceEventsAttendeeCompany'] ); ?></div>
						<?php endif; ?>
						<?php if ( $attr['enableShowPhone'] ) : ?>
							<div class="fooevents-attendee-info-telephone"><?php echo esc_attr( $attendee['WooCommerceEventsAttendeeTelephone'] ); ?></div>
						<?php endif; ?>
						<?php if ( $attr['enableShowEmail'] ) : ?>
							<div class="fooevents-attendee-info-email"><a href="mailto:<?php echo esc_attr( $attendee['WooCommerceEventsAttendeeEmail'] ); ?>"><?php echo esc_attr( $attendee['WooCommerceEventsAttendeeEmail'] ); ?></a></div>
						<?php endif; ?>
					</div>
				</div>
				<?php ++$counter; ?>
			<?php endforeach; ?>
		</div>
		<?php if ( $attr['enableTotalAttendees'] ) : ?>
			<p class="fooevents-attendees-total fooevents-attendees-grid-total"><?php echo esc_attr( $attendees['total_attendees'] ); ?> <?php echo esc_attr__( 'people are attending this event.', 'woocommerce-events' ); ?></p>
		<?php endif; ?>
	<?php else : ?>
		<p><?php echo esc_attr__( 'No attendees found.', 'woocommerce-events' ); ?></p>
	<?php endif; ?>
</div>
