<?php

/**
 * Event listing block
 *
 * @link    https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php if ( ! empty( $events ) ) : ?> 
	<div <?php get_block_wrapper_attributes(); ?> id="fooevents-event-listing-list">
		<?php
		$previous_month_year = '';
		foreach ( $events as $event ) :
			
			$thumbnail = has_post_thumbnail( $event['post_id'] ) ? get_the_post_thumbnail( $event['post_id'], $attr['imageSize'] ) : '';
			$product   = wc_get_product( $event['post_id'] );
			$stock     = '';
			$in_stock  = '';
			if ( $product ) {
				$stock    = $product->get_stock_quantity();
				$in_stock = $product->is_in_stock();
			}
			$price              = $product->get_price_html();
			$event_type         = get_post_meta( $event['post_id'], 'WooCommerceEventsType', true ) ?: '';
			$event_date         = strtotime( $event['unformated_date'] );

			$current_month_year = date_i18n( 'F Y', $event['timestamp'] );
			if ( $attr['displayMonthSeperators'] ) :
				if ( $previous_month_year !== $current_month_year ) {
					echo '<div class="fooevents-event-listing-list-seperators">';
					echo '<h2>' . esc_html( $current_month_year ) . '</h2>';
					echo '</div>';
					$previous_month_year = $current_month_year;
				}
			endif;
			?>
			<div id="fooevents-event-listing-list-post-id-<?php echo esc_attr( $event['post_id'] ); ?>" class="fooevents-event-listing-list-container  fooevents-event-listing-list-<?php echo esc_attr( $event_type ); ?> image-<?php echo ( $attr['imageAlignment'] ) ? esc_attr( $attr['imageAlignment'] ) : ''; ?> <?php echo ( empty( $thumbnail ) || ! $attr['displayImage'] ) ? 'image-none' : ''; ?>">
				<?php if ( ! empty( $thumbnail ) && $attr['displayImage'] ) : ?>
					<div class="fooevents-event-listing-list-thumbnail">
						<?php echo wp_kses_post( $thumbnail ); ?>
					</div>
				<?php endif; ?>

				<div class="fooevents-event-listing-list-content">
					<h3><?php if ( ! empty( $event['url'] ) ) : ?><a href="<?php echo esc_attr( $event['url'] ); ?>"><?php endif; echo esc_attr( $event['title'] ); ?><?php if ( ! empty( $event['url'] ) ) : ?></a><?php endif; ?></h3>
					<?php if ( $attr['displayLocation'] ) : ?>
						<p class="fooevents-event-listing-list-location"><strong><?php echo esc_attr( $event['location'] ); ?></strong></p>
					<?php endif; ?>
					<?php if ( $attr['displayDate'] || $attr['displayTimes'] ) : ?>
						<p class="fooevents-event-listing-list-datetime">
							<?php if ( $attr['displayDate'] && '' != trim( $event['unformated_date'] ) ) : ?>
								<?php if ( $attr['displayIcons'] ) : ?>
									<span class="event-icon event-icon-calendar"></span>
								<?php endif; ?>
								<span class="event-date"><?php echo esc_attr( $event['unformated_date'] );
								 if (!empty($event['unformated_end_date'])) : echo " - " . esc_attr( $event['unformated_end_date'] ); endif; 
								?></span><br />
							<?php endif; ?>
							<?php if ( $attr['displayTimes'] && '' != trim( $event['unformated_start_time'] ) ) : ?>
								<?php if ( $attr['displayIcons'] ) : ?>
									<span class="event-icon event-icon-<?php echo esc_attr( $event_type ); ?>"></span>
								<?php endif; ?>
								<span class="event-time"><?php echo esc_attr( $event['unformated_start_time'] ); ?> <?php echo ! empty( $event['unformated_end_time'] ) ? ' - ' . esc_attr( $event['unformated_end_time'] ) : '';
								if (!empty($event['timezone'])) : echo " " . esc_attr( $event['timezone'] ); endif; ?></span>
							<?php endif; ?>
						</p>
					<?php endif; ?>
					<?php if ( get_the_excerpt( $event['post_id'] ) && $attr['displayExcerpt'] ) : ?>
						<p class="fooevents-event-listing-list-excerpt"><?php echo wp_kses_post( get_the_excerpt( $event['post_id'] ) ); ?></p>
					<?php endif; ?>
					<?php if ( ( 'bookings' !== $event_type && $in_stock && $event['stock_num'] && $attr['displayAvailability'] ) || ( 'bookings' === $event_type && $event['stock_num'] !== 0 && $event['stock_num'] !== '' && $attr['displayAvailability'] ) || ( $attr['displayPrice'] ) ) : ?>
						<p class="fooevents-event-listing-list-stock">
							<?php if ( $attr['displayPrice'] ) : ?>
								<span class="fooevents-event-listing-list-price"><?php echo wp_kses_post( $price ); ?></span>
							<?php endif; ?>
							<?php if ( ( 'bookings' !== $event_type && $in_stock && $event['stock_num'] && $attr['displayAvailability'] ) || ( 'bookings' === $event_type && $event['stock_num'] !== 0 && $event['stock_num'] !== '' && $attr['displayAvailability'] ) ) : ?>
								<span class="fooevents-event-listing-list-availability"><?php echo esc_attr( $event['stock_num'] ); ?> <?php esc_attr_e( 'Available', 'woocommerce-events' ); ?></span>
							<?php endif; ?>
							<?php if ( ( 'bookings' !== $event_type && ! $in_stock && $attr['displayAvailability'] ) || ( 'bookings' === $event_type && $event['stock_num'] === 0 && $attr['displayAvailability'] ) ) : ?>
								<span class="fooevents-event-listing-list-availability out-of-stock"><?php esc_attr_e( 'Out of stock', 'woocommerce-events' ); ?></span>
							<?php endif; ?>
						</p>
					<?php endif; ?>
					<?php if ( ( 'bookings' !== $event_type && $in_stock && $attr['displayBookButton'] ) || ( 'bookings' === $event_type && $event['stock_num'] !== 0 && $attr['displayBookButton'] ) && ! empty( $event['url'] ) ) : ?>
						<p class="fooevents-event-listing-list-book-now"><a href="<?php echo esc_attr( $event['url'] ); ?>" class="button"><?php echo esc_attr( $event['ticketTerm'] ); ?></a></p>
					<?php endif; ?>
					<div class="fooevents-event-listing-clear"></div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php else : ?>
	<div class="fooevents-event-listing-no-events"><?php esc_attr_e( 'No events found.', 'woocommerce-events' ); ?></div>
<?php endif; ?>
