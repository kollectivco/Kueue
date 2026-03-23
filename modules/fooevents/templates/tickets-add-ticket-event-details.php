<?php
/**
 * Add ticket event details template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<tr valign="top">
	<td colspan="2">
		<h3><?php echo esc_attr( $event['WooCommerceEventsName'] ); ?></h3> 
	</td>
</tr>
<tr valign="top">
	<td>
		<strong><?php esc_attr_e( 'Date: ', 'woocommerce-events' ); ?></strong>
	</td>
	<td>
		<?php echo esc_attr( $event['WooCommerceEventsDate'] ); ?>
	</td>
</tr>
<tr valign="top">
	<td>
		<strong><?php esc_attr_e( 'Start time: ', 'woocommerce-events' ); ?></strong>
	</td>
	<td>
		<?php echo esc_attr( $event['WooCommerceEventsStartTime'] ); ?> <?php echo( ! empty( $event['WooCommerceEventsPeriod'] ) ) ? esc_attr( $event['WooCommerceEventsPeriod'] ) : ''; ?>
	</td>
</tr>
<tr valign="top">
	<td>
		<strong><?php esc_attr_e( 'End time: ', 'woocommerce-events' ); ?></strong>
	</td>
	<td>
		<?php echo esc_attr( $event['WooCommerceEventsEndTime'] ); ?> <?php echo( ! empty( $event['WooCommerceEventsEndPeriod'] ) ) ? esc_attr( $event['WooCommerceEventsEndPeriod'] ) : ''; ?>
	</td>
</tr>
<tr valign="top">
	<td>
		<strong><?php esc_attr_e( 'Venue: ', 'woocommerce-events' ); ?></strong>
	</td>
	<td>
		<?php echo esc_attr( $event['WooCommerceEventsLocation'] ); ?>
	</td>
</tr>
<tr valign="top">
	<td>
		<strong><?php esc_attr_e( 'GPS Coordinates: ', 'woocommerce-events' ); ?></strong>
	</td>
	<td>
		<?php echo esc_attr( $event['WooCommerceEventsGPS'] ); ?>
	</td>
</tr>
<tr valign="top">
	<td>
		<strong><?php esc_attr_e( 'Phone: ', 'woocommerce-events' ); ?></strong> 
	</td>
	<td>
		<?php echo esc_attr( $event['WooCommerceEventsSupportContact'] ); ?>
	</td>
</tr>
<tr valign="top">
	<td>
		<strong><?php esc_attr_e( 'Email: ', 'woocommerce-events' ); ?></strong> 
	</td>
	<td>
		<a href="mailto:<?php echo esc_attr( $event['WooCommerceEventsEmail'] ); ?>"><?php echo esc_attr( $event['WooCommerceEventsEmail'] ); ?></a>
	</td>
</tr>
<?php if ( ! empty( $event['WooCommerceEventsZoomText'] ) ) : ?>
<tr valign="top">
	<td>
		<strong><?php esc_attr_e( 'Zoom Meetings / Webinars: ', 'woocommerce-events' ); ?></strong> 
	</td>
	<td>
		<?php echo nl2br( wp_kses_post( $event['WooCommerceEventsZoomText'] ) ); ?>
	</td>
</tr>
<?php endif; ?>
<tr>
	<td colspan="2">
		<a href="<?php echo esc_attr( $event['WooCommerceEventsURL'] ); ?>" target="_BLANK" class="button">View</a> <a href="post.php?post=<?php echo esc_attr( $event['WooCommerceEventsProductID'] ); ?>&action=edit" target="_BLANK" class="button"><?php esc_attr_e( 'Edit', 'woocommerce-events' ); ?></a>
	</td>
</tr>
