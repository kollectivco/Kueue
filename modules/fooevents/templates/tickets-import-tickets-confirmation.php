<?php
/**
 * Ticket import confirm page
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_attr_e( 'Import Tickets', 'woocommerce-events' ); ?></h1>
</div>
<p class="install-help"></p>
<?php if ( ! empty( $error_message ) ) : ?>
	<div class="notice notice-error">
		<p><?php echo esc_attr( $error_message ); ?></p>
	</div>
<?php endif; ?>	
<div id="fooevents-import-wrapper">
	<ul class="fooevents-import-steps">
	<li><a href="<?php echo esc_attr( admin_url( 'admin.php?page=fooevents-import-tickets', 'https' ) ); ?>"><?php esc_attr_e( 'Upload CSV file', 'woocommerce-events' ); ?></a></li>
		<li><?php esc_attr_e( 'Column Mapping', 'woocommerce-events' ); ?></li>
		<li class='active'><?php esc_attr_e( 'Confirm Import', 'woocommerce-events' ); ?></li>
		<li><?php esc_attr_e( 'Done!', 'woocommerce-events' ); ?></li>
	</ul>	
	<div class="clearfix"></div>
	<div class="fooevents-import-inner">
		<div class="fooevents-import-section fooevents-import-section-header">
			<h2><?php echo esc_html( __( 'Confirm Import', 'woocommerce-events' ) ); ?></h2>	
			<p><?php echo esc_html( __( 'Please check that all data displays in the appropriate fields and that no errors have been identified. If you do come across any errors, please correct the data in your CSV and re-upload the CSV file.', 'woocommerce-events' ) ); ?></p>		</div>
		<form name="fooevents-theme-viewer-upload-form" action="<?php echo esc_attr( admin_url( 'admin.php' ) ); ?>?page=fooevents-import-tickets" method="POST" enctype="multipart/form-data">
			<div class="fooevents-import-section">
				<div class="fooevents-import-section-scroll">
					<input type="hidden" name="step" value="3" />
					<input type="hidden" name="fooevents-import-tickets-run" value="<?php echo esc_attr( $file_run ); ?>" />
					<input type="hidden" name="fooevents-import-tickets-max-run" value="<?php echo esc_attr( $output_num_runs ); ?>" />
					<input type="hidden" name="fooevents-import-tickets-file" value="<?php echo htmlspecialchars( wp_json_encode( $tickets_array ) ); ?>">
					<input type="hidden" name="fooevents-csv-field" value="<?php echo htmlspecialchars( wp_json_encode( $_POST['fooevents-csv-field'] ) ); ?>" />
					<input type="hidden" name="fooevents-csv-field-meta" value="<?php echo htmlspecialchars( wp_json_encode( $field_metas ) ); ?>" />
					<input type="hidden" name="fooevents-csv-custom-attendee" value="<?php echo htmlspecialchars( wp_json_encode( $custom_attendee_fields ) ); ?>" />
					<table class="wp-list-table widefat">
						<thead>
							<tr>
								<th><?php esc_attr_e( 'Event ID', 'woocommerce-events' ); ?></th>
								<?php if ( isset( $field_map['productvariation'] ) ) : ?>
									<th><?php esc_attr_e( 'Variation ID', 'woocommerce-events' ); ?></th>
								<?php endif; ?>
								<?php if ( isset( $field_map['oid'] ) ) : ?>
									<th><?php esc_attr_e( 'Order ID', 'woocommerce-events' ); ?></th>
								<?php endif; ?>	
								<th><?php esc_attr_e( 'Attendee First Name', 'woocommerce-events' ); ?></th>
								<th><?php esc_attr_e( 'Attendee Last Name', 'woocommerce-events' ); ?></th>
								<th><?php esc_attr_e( 'Attendee Email', 'woocommerce-events' ); ?></th>
								<?php if ( isset( $field_map['phone'] ) ) : ?>
									<th><?php esc_attr_e( 'Attendee Telephone', 'woocommerce-events' ); ?></th>
								<?php endif; ?>	
								<?php if ( isset( $field_map['company'] ) ) : ?>
									<th><?php esc_attr_e( 'Attendee Company', 'woocommerce-events' ); ?></th>
								<?php endif; ?>	
								<?php if ( isset( $field_map['designation'] ) ) : ?>
									<th><?php esc_attr_e( 'Attendee Designation', 'woocommerce-events' ); ?></th>
								<?php endif; ?>
								<?php if ( isset( $field_map['bookingdate'] ) && isset( $field_map['bookingslot'] ) ) : ?>
									<th><?php esc_attr_e( 'Booking Date ID', 'woocommerce-events' ); ?></th>
									<th><?php esc_attr_e( 'Booking Slot ID', 'woocommerce-events' ); ?></th>
								<?php endif; ?>		
								<?php if ( ! empty( $field_metas ) ) : ?>
									<?php foreach ( $field_metas as $key => $position ) : ?>
										<th><?php echo esc_attr( $key ); ?></th>
									<?php endforeach; ?>
								<?php endif; ?>
								<?php if ( ! empty( $custom_attendee_fields ) ) : ?>
									<?php foreach ( $custom_attendee_fields as $key => $position ) : ?>
										<th><?php esc_attr_e( 'Custom Attendee Field', 'woocommerce-events' ); ?></th>
									<?php endforeach; ?>
								<?php endif; ?>
							</tr>
						</thead>
						
						<?php foreach ( $tickets_array as $ticket ) : ?>
							<tr>
							<td <?php echo ( false === $this->validate_event( $ticket[ $field_map['pid'] ] ) ) ? 'class="fooevents-csv-error"title="' . esc_attr( 'Invalid event ID', 'woocommerce-events' ) . '"' : ''; ?>><?php echo esc_attr( $ticket[ $field_map['pid'] ] ); ?></td>
							<?php if ( isset( $field_map['productvariation'] ) ) : ?>
								<td><?php echo esc_attr( $ticket[ $field_map['productvariation'] ] ); ?></td>
							<?php endif; ?>
							<?php if ( isset( $field_map['oid'] ) ) : ?>
								<td><?php echo esc_attr( $ticket[ $field_map['oid'] ] ); ?></td>
							<?php endif; ?>
							<td><?php echo esc_attr( $ticket[ $field_map['fname'] ] ); ?></td>
							<td><?php echo esc_attr( $ticket[ $field_map['lname'] ] ); ?></td>
							<td <?php echo ( false === $this->validate_email_address( $ticket[ $field_map['email'] ] ) ) ? 'class="fooevents-csv-error" title="' . esc_attr( 'Invalid email address', 'woocommerce-events' ) . '"' : ''; ?>><?php echo esc_attr( $ticket[ $field_map['email'] ] ); ?></td>
							<?php if ( isset( $field_map['phone'] ) ) : ?>
								<td><?php echo esc_attr( $ticket[ $field_map['phone'] ] ); ?></td>
							<?php endif; ?>
							<?php if ( isset( $field_map['company'] ) ) : ?>
								<td><?php echo esc_attr( $ticket[ $field_map['company'] ] ); ?></td>
							<?php endif; ?>
							<?php if ( isset( $field_map['designation'] ) ) : ?>
								<td <?php echo ( false === $this->validate_alphanumeric( $ticket[ $field_map['designation'] ] ) ) ? 'class="fooevents-csv-error" title="' . esc_attr( 'Designation can only contain alphanumeric chatacters', 'woocommerce-events' ) . '"' : ''; ?>><?php echo esc_attr( $ticket[ $field_map['designation'] ] ); ?></td>
							<?php endif; ?>
							<?php if ( isset( $field_map['bookingdate'] ) && isset( $field_map['bookingslot'] ) ) : ?>
								<td><?php echo esc_attr( $ticket[ $field_map['bookingdate'] ] ); ?></td>
								<td><?php echo esc_attr( $ticket[ $field_map['bookingslot'] ] ); ?></td>
							<?php endif; ?>	
							<?php if ( ! empty( $field_metas ) ) : ?>
								<?php foreach ( $field_metas as $key => $position ) : ?>
									<td><?php echo esc_attr( $ticket[ $position ] ); ?></td>
								<?php endforeach; ?>
							<?php endif; ?>
							<?php if ( ! empty( $custom_attendee_fields ) ) : ?>
								<?php foreach ( $custom_attendee_fields as $key => $position ) : ?>
									<td><?php echo esc_attr( $ticket[ $position ] ); ?></td>
								<?php endforeach; ?>
							<?php endif; ?>		
							</tr> 
						<?php endforeach; ?>
					</table>
				</div>
			</div>
			<div class="fooevents-import-section fooevents-import-section-footer">
				<input type="submit" value="<?php esc_attr_e( 'Confirm Import', 'woocommerce-events' ); ?>" class="button button-primary button-hero" id="fooevents-csv-confirmation-button" />
			</div>			
		</form>
	</div> 
</div> 
