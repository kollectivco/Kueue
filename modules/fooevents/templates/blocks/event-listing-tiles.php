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
	<div <?php get_block_wrapper_attributes(); ?> id="fooevents-event-listing-tiles" class="fooevents-event-listing-tile-number-<?php echo ( $attr['numberOfTileColumns'] ) ? esc_attr( $attr['numberOfTileColumns'] ) : ''; ?>">
		<?php foreach ( $events as $event ) : ?>
			<?php
			$thumbnail = has_post_thumbnail( $event['post_id'] ) ? get_the_post_thumbnail( $event['post_id'], $attr['imageSize'] ) : '';
			$product   = wc_get_product( $event['post_id'] );
			$stock     = '';
			$in_stock  = '';
			if ( $product ) {
				$stock    = $product->get_stock_quantity();
				$in_stock = $product->is_in_stock();
			}
			$price      = $product->get_price_html();
			$event_type = get_post_meta( $event['post_id'], 'WooCommerceEventsType', true ) ?: '';
			?>
			<div id="fooevents-event-listing-tiles-post-id-<?php echo esc_attr( $event['post_id'] ); ?>" class="fooevents-event-listing-tiles-content fooevents-event-listing-tiles-<?php echo esc_attr( $event_type ); ?>">
				<?php if ( ! empty( $thumbnail ) && $attr['displayImage'] ) : ?>
					<div class="event-thumbnail"><a href="<?php echo esc_attr( $event['url'] ); ?>"><?php echo wp_kses_post( $thumbnail ); ?></a></div>
				<?php endif; ?>					
				<h3><a href="<?php echo esc_attr( $event['url'] ); ?>"><?php echo esc_attr( $event['title'] ); ?></a></h3>		
				<?php if ( $attr['displayLocation'] ) : ?>
					<div class="fooevents-event-listing-tiles-location"><?php echo esc_attr( $event['location'] ); ?></div>
				<?php endif; ?> 
				<?php if ( $attr['displayDate'] || $attr['displayTimes'] ) : ?>
					<p class="fooevents-event-listing-tiles-datetime">
						<?php if ( $attr['displayDate'] && '' != trim( $event['unformated_date'] ) ) : ?>
							<?php if ( $attr['displayIcons'] ) : ?>
								<span class="event-icon event-icon-calendar"></span>
							<?php endif; ?>	
							<span class="event-date"><?php echo esc_attr( $event['unformated_date'] ); ?></span><br />						
						<?php endif; ?> 
						<?php if ( $attr['displayTimes'] && '' != trim( $event['unformated_start_time'] ) ) : ?>
							<?php if ( $attr['displayIcons'] ) : ?>
								<span class="event-icon event-icon-<?php echo esc_attr( $event_type ); ?>"></span>
							<?php endif; ?>	
							<span class="event-time"><?php echo esc_attr( $event['unformated_start_time'] ); ?> <?php echo ! empty( $event['unformated_end_time'] ) ? ' - ' . esc_attr( $event['unformated_end_time'] ) : ''; ?></span>
						<?php endif; ?>	
					</p>
				<?php endif; ?>		 
				<?php if ( get_the_excerpt( $event['post_id'] ) && $attr['displayExcerpt'] ) : ?>
					<p class="fooevents-event-listing-tiles-excerpt"><?php echo wp_kses_post( get_the_excerpt( $event['post_id'] ) ); ?></p>
				<?php endif; ?>     
				<?php if ( ( 'bookings' !== $event_type && $in_stock && $event['stock_num'] && $attr['displayAvailability'] ) || ( 'bookings' === $event_type && $event['stock_num'] !== 0 && $event['stock_num'] !== '' && $attr['displayAvailability'] ) || ( $attr['displayPrice'] ) ) : ?>
					<div class="fooevents-event-listing-tiles-stock">
						<?php if ( $attr['displayPrice'] ) : ?>
							<p class="fooevents-event-listing-tiles-price"><?php echo wp_kses_post( $price ); ?></p>
						<?php endif; ?>	 
						<?php if ( ( 'bookings' !== $event_type && $in_stock && $event['stock_num'] && $attr['displayAvailability'] ) || ( 'bookings' === $event_type && $event['stock_num'] !== 0 && $event['stock_num'] !== '' && $attr['displayAvailability'] ) ) : ?>
							<p class="fooevents-event-listing-tiles-availability"><?php echo esc_attr( $event['stock_num'] ); ?> <?php esc_attr_e( 'Available', 'woocommerce-events' ); ?></p>
						<?php endif; ?> 
						<?php if ( ( 'bookings' !== $event_type && ! $in_stock && $attr['displayAvailability'] ) || ( 'bookings' === $event_type && $event['stock_num'] === 0 && $attr['displayAvailability'] ) ) : ?>
							<p class="fooevents-event-listing-tiles-availability out-of-stock"><?php esc_attr_e( 'Out of stock', 'woocommerce-events' ); ?></p>
						<?php endif; ?> 
					</div>
				<?php endif; ?>
				<?php if ( ( 'bookings' !== $event_type && $in_stock && $attr['displayBookButton'] ) || ( 'bookings' === $event_type && $event['stock_num'] !== 0 && $attr['displayBookButton'] ) ) : ?>
					<p class="fooevents-event-listing-book-now"><a href="<?php echo esc_attr( $event['url'] ); ?>" class="button"><?php echo esc_attr( $event['ticketTerm'] ); ?></a></p>  
				<?php endif; ?> 
			</div>
		<?php endforeach; ?>   
	</div>
<?php else : ?>
	<div class="fooevents-event-listing-no-events"><?php esc_attr_e( 'No events found.', 'woocommerce-events' ); ?></div>
<?php endif; ?>
