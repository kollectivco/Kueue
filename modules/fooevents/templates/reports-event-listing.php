<?php
/**
 * Reports event listing template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<div id="icon-users" class="icon32"></div>
	<h2><?php esc_attr_e( 'Event Reports', 'woocommerce-events' ); ?></h2>
	<?php $events_list_table->display(); ?>
</div>
