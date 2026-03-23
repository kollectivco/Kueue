<?php
/**
 * End date options template.
 *
 * @link https://www.fooevents.com
 * @package fooevents-multiday-events
 */

?>
<?php foreach ( $woocommerce_events_multiday_status as $day => $status_value ) : ?>
	<b><?php echo esc_attr( $day_term ); ?> <?php echo esc_attr( $day ); ?>: </b><?php echo esc_attr( $status_value ); ?><br />
<?php endforeach; ?>
