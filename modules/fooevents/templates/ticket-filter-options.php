<?php
/**
 * Ticket filter options template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<select name="event_id">
	<option value=""><?php esc_attr_e( 'All Products', 'woocommerce-events' ); ?></option>
	<?php foreach ( $events as $event ) : ?>
	<option value="<?php echo esc_attr( $event->ID ); ?>" <?php echo ( $event_id === $event->ID ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $event->post_title ); ?></option>
	<?php endforeach; ?>
</select>
