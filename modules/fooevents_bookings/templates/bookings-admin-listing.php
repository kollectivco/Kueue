<?php
/**
 * Reports event listing template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

?>
<div class="wrap" id="fooevents-bookings-listing">

	<h2><?php esc_attr_e( 'Bookings', 'woocommerce-events' ); ?></h2>

	<div id="icon-users" class="icon32"></div>
	<form action="<?php echo admin_url( 'admin.php' ); ?>" method="get">
		<div class="fooevents-bookings-listing-nav">
			
			<select id="fooevents-bookings-product" name="fooevents_bookings_product">
				<option value=""><?php esc_attr_e( 'Select Booking Event', 'woocommerce-events' ); ?></option>
				<?php foreach ( $events as $event ) : ?>
					<option value="<?php echo esc_attr( $event->ID ); ?>" <?php echo ( $event->ID == $event_id ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( get_the_title( $event->ID ) ); ?> (<?php echo esc_attr( $event->ID ); ?>)</option>
				<?php endforeach; ?>	
			</select>
			<select id="fooevents-bookings-slot" name="fooevents_bookings_slot">
				<option value=""><?php esc_attr_e( 'All Booking Slots', 'woocommerce-events' ); ?></option>
				<?php if ( ! empty( $slots ) ) : ?>
					<?php foreach ( $slots as $sid => $slot ) : ?>
						<option value="<?php echo esc_attr( $sid ); ?>" <?php echo ( $sid === $slot_id ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $slot ); ?></option>
					<?php endforeach; ?>						<?php endif; ?>			
			</select>
			<input type="text" id="fooevents-bookings-admin-date" class="fooevents_bookings_date fooevents-bookings-admin-date" name="fooevents_bookings_admin_date" value="<?php echo esc_attr( $date ); ?>">
			<input type="submit" id="fooevents-bookings-admin-button" class="button" value="Filter">

			<input type="hidden" name="page" value="fooevents-bookings-admin" />

			<?php $FE_Bookings_List_Table->search_box( 'search', 'search_id' ); ?>

		</div>

		<?php $FE_Bookings_List_Table->display(); ?>

	</form>
</div>
