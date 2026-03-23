<div class="wrap" id="fooevents-bookings-updater">
	<h2><?php esc_attr_e( 'FooEvents Bookings Timestamp Updater', 'fooevents-bookings-timestamp-updater' ); ?></h2>
	<p><?php esc_attr_e( 'Running this updater will add a booking date timestamp to tickets that were generated for bookable events before the timestamp field was implemented.', 'fooevents-bookings-timestamp-updater' ); ?></p>
	<p><strong><u><?php esc_attr_e( 'Please click the "Run Updater" button to update the specified number of tickets. Continue to run the updater until there are no tickets remaining that require the update.', 'fooevents-bookings-timestamp-updater' ); ?></u></strong></p>
	<?php if ( $return_text != '' ) : ?>
	<textarea class="" rows="10"  cols="60" readonly><?php echo $return_text; ?></textarea><br /><br />
	<?php endif; ?>
	<p><?php echo esc_attr( $num_tickets_to_update ); ?> <?php echo esc_attr_e( ' ticket(s) need to be updated.', 'fooevents-bookings-timestamp-updater' ); ?></p>
	<?php if ( $num_tickets_to_update == '0' ) : ?>
			<p style="color:red;"><u><strong><?php esc_attr_e( 'No further updates are required. Please remove the FooEvents Bookings Timestamp Updater plugin.', 'fooevents-bookings-timestamp-updater' ); ?></strong></u></p>
		<?php endif; ?>
	<form action="<?php echo esc_attr( admin_url( 'admin.php' ) ); ?>" method="get">
		<input type="hidden" name="page" value="fooevents-bookings-timestamp-updater" />
		<input type="hidden" name="run_script" value="yes" />
		<p>
			<input name="fooevents-bookings-timestamp-updater-amount" type="text" id="amount" value="<?php echo $get_amount; ?>" class="small-text">
			<label><?php esc_attr_e( 'Number of tickets to update per batch.', 'fooevents-bookings-timestamp-updater' ); ?></label> 
		</p>		
		<input type="submit" value="Run Update" class="button button-primary" id='fooevents-bookings-updater-button'/>
	</form>
<div>
