<?php
/**
 * Event listing block
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php if ( ! empty( $events ) ) : ?>
	<table <?php get_block_wrapper_attributes(); ?> id="fooevents-event-listing-compact">
		<?php
		$previous_month_year = '';
		foreach ( $events as $event ) :
			$event_date = strtotime( $event['unformated_date'] );
			if ( $attr['displayMonthSeperators'] ) :
				$current_month_year = wp_date( 'F Y', $event['timestamp'] );
				if ( $previous_month_year !== $current_month_year ) {
					echo '<tr><th colspan="3">' . esc_html( $current_month_year ) . '</th></tr>';
					$previous_month_year = $current_month_year;
				}
			endif;
			$thumbnail = has_post_thumbnail( $event['post_id'] ) ? get_the_post_thumbnail( $event['post_id'], $attr['imageSize'] ) : '';
			$product   = wc_get_product( $event['post_id'] );
			$stock     = '';
			$in_stock  = '';
			if ( $product ) {
				$stock    = $product->get_stock_quantity();
				$in_stock = $product->is_in_stock();
			}
			$price      = $product->get_price_html();
			$event_type = get_post_meta( $event['post_id'], 'WooCommerceEventsType', true );
			?>
			<tr id="fooevents-event-listing-compact-post-id-<?php echo esc_attr( $event['post_id'] ); ?>" class="fooevents-event-listing-<?php echo esc_attr( $event_type ); ?> fooevents-event-listing-compact-<?php echo esc_attr( $event_type ); ?>">
				<?php if ( $attr['displayDate'] || $attr['displayTimes'] ) : ?>
					<td class="date" valign="top" width="10%">
						<?php if ( $attr['displayDate'] && '' !== trim( $event['unformated_date'] ) ) : ?>
							<div class="fooevents-event-listing-date-month"><?php echo esc_attr( wp_date( 'M', $event['timestamp'] ) ); ?></div>
							<div class="fooevents-event-listing-date-day"><?php echo esc_attr( wp_date( 'd', $event['timestamp'] ) ); ?></div>
						<?php endif; ?>	
					</td>
				<?php endif; ?>
				<td valign="top" width="70%" class="fooevents-event-listing-compact-details">
					<h3><a href="<?php echo esc_attr( $event['url'] ); ?>"><?php echo esc_attr( $event['title'] ); ?></a></h3>     
					<?php if ( $attr['displayLocation'] ) : ?>
						<p class="fooevents-event-listing-compact-location"><strong><?php echo esc_attr( $event['location'] ); ?></strong></p>
					<?php endif; ?>  
					<?php if ( $attr['displayTimes'] && '' !== trim( $event['unformated_start_time'] ) ) : ?>
						<p class="fooevents-event-listing-compact-datetime">
						<?php if ( $attr['displayIcons'] ) : ?>
							<span class="event-icon event-icon-<?php echo esc_attr( $event_type ); ?>"></span><?php endif; ?><span class="event-time"><?php echo esc_attr( $event['unformated_start_time'] ); ?> <?php echo ! empty( $event['unformated_end_time'] ) ? ' - ' . esc_attr( $event['unformated_end_time'] ) : ''; ?></span>
						</p>
					<?php endif; ?>		               
					<?php if ( get_the_excerpt( $event['post_id'] ) && $attr['displayExcerpt'] ) : ?>
						<div class="fooevents-event-listing-compact-excerpt"><?php echo wp_kses_post( get_the_excerpt( $event['post_id'] ) ); ?></div>
					<?php endif; ?>                      
				</td>
				<?php if ( $attr['displayBookButton'] || $attr['displayAvailability'] || $attr['displayPrice'] || $attr['displayPrice'] ) : ?>
				<td valign="top" class="fooevents-event-listing-compact-details">
					<?php if ( ( 'bookings' !== $event_type && $in_stock && $attr['displayBookButton'] ) || ( 'bookings' === $event_type && 0 !== $event['stock_num'] && $attr['displayBookButton'] ) ) : ?>
						<p class="fooevents-event-listing-compact-book-now"><a href="<?php echo esc_attr( $event['url'] ); ?>" class="button"><?php echo esc_attr( $event['ticketTerm'] ); ?></a></p>  
					<?php endif; ?>  
					<?php if ( ( 'bookings' !== $event_type && $in_stock && $event['stock_num'] && $attr['displayAvailability'] ) || ( 'bookings' === $event_type && 0 !== $event['stock_num'] && '' !== $event['stock_num'] && $attr['displayAvailability'] ) || ( $attr['displayPrice'] ) ) : ?>
						<div class="fooevents-event-listing-compact-stock">
							<?php if ( $attr['displayPrice'] ) : ?>
								<p class="fooevents-event-listing-compact-price"><?php echo wp_kses_post( $price ); ?></p>
							<?php endif; ?>	 
							<?php if ( ( 'bookings' !== $event_type && $in_stock && $event['stock_num'] && $attr['displayAvailability'] ) || ( 'bookings' === $event_type && 0 !== $event['stock_num'] && '' !== $event['stock_num'] && $attr['displayAvailability'] ) ) : ?>
								<p class="fooevents-event-listing-compact-availability"><?php echo esc_attr( $event['stock_num'] ); ?> <?php esc_attr_e( 'Available', 'woocommerce-events' ); ?></p>
							<?php endif; ?> 
							<?php if ( ( 'bookings' !== $event_type && ! $in_stock && $attr['displayAvailability'] ) || ( 'bookings' === $event_type && $event['stock_num'] === 0 && $attr['displayAvailability'] ) ) : ?>
								<p class="fooevents-event-listing-compact-availability fooevents-out-of-stock"><?php esc_attr_e( 'Out of stock', 'woocommerce-events' ); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</td>
				<?php endif; ?> 
			</tr>
		<?php endforeach; ?>   
	</table>
<?php else : ?>
	<div class="fooevents-event-listing-no-events"><?php esc_attr_e( 'No events found.', 'woocommerce-events' ); ?></div></td>
<?php endif; ?>
