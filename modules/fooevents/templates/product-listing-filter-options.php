<?php
/**
 * Product listing filter options template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<select name="fooevents_filter">
	<option value=""><?php esc_attr_e( 'All Products Types', 'woocommerce-events' ); ?></option>
	<option value="events" 
	<?php
	if ( 'events' === $fooevents_filter ) {
		echo 'selected';}
	?>
	><?php esc_attr_e( 'All Events', 'woocommerce-events' ); ?></option>

	<option value="single" 
	<?php
	if ( 'single' === $fooevents_filter ) {
		echo 'selected';}
	?>
	><?php esc_attr_e( 'Single Day Events', 'woocommerce-events' ); ?></option>

	<option value="multi-day-sequential" 
	<?php
	if ( 'multi-day-sequential' === $fooevents_filter ) {
		echo 'selected';}
	?>
	><?php esc_attr_e( 'Multi-day Events (Sequential)', 'woocommerce-events' ); ?></option>

	<option value="multi-day-select" 
	<?php
	if ( 'multi-day-select' === $fooevents_filter ) {
		echo 'selected';}
	?>
	><?php esc_attr_e( 'Multi-day Events (Selected Days)', 'woocommerce-events' ); ?></option>

	<option value="bookings" 
	<?php
	if ( 'bookings' === $fooevents_filter ) {
		echo 'selected';}
	?>
	><?php esc_attr_e( 'Bookable Events', 'woocommerce-events' ); ?></option>

	<option value="seating" 
	<?php
	if ( 'seating' === $fooevents_filter ) {
		echo 'selected';}
	?>
	><?php esc_attr_e( 'Seating Events', 'woocommerce-events' ); ?></option>

	<option value="non-events" 
	<?php
	if ( 'non-events' === $fooevents_filter ) {
		echo 'selected';}
	?>
	><?php esc_attr_e( 'Non-events', 'woocommerce-events' ); ?></option>
</select>
