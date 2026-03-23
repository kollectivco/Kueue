<?php
/**
 * End date options template.
 *
 * @link https://www.fooevents.com
 * @package fooevents-multiday-events
 */

?>
<?php foreach ( $woocommerce_events_multiday_check_in_time as $day => $time ) : ?>
	<b><?php echo esc_attr( $day_term ); ?> <?php echo esc_attr( $day ); ?>: </b><?php echo esc_attr( $time ); ?><br />
<?php endforeach; ?>
