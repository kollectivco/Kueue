<?php
/**
 * Ticket import  field mapping page
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
		<li class='active'><?php esc_attr_e( 'Column Mapping', 'woocommerce-events' ); ?></li>
		<li><?php esc_attr_e( 'Confirm Import', 'woocommerce-events' ); ?></li>
		<li><?php esc_attr_e( 'Done!', 'woocommerce-events' ); ?></li>
	</ul>	
	<div class="clearfix"></div>
	<div class="fooevents-import-inner fooevents-import-selection">
		<div class="fooevents-import-section fooevents-import-section-header">
			<h2><?php echo esc_html( __( 'Map CSV fields to ticket fields', 'woocommerce-events' ) ); ?></h2>	
			<p><?php echo esc_html( __( 'Select fields from your CSV file to map against ticket fields.', 'woocommerce-events' ) ); ?></p>
		</div>
		<div class="fooevents-import-section fooevents-import-section-header">
			<p><strong><?php echo sprintf( esc_attr__( 'CSV Import (%1$s):  %2$s of %3$s', 'woocommerce-events' ), esc_attr( $file_name ), esc_attr( $file_run ), esc_attr( $output_num_runs ) ); ?></strong></p>
			<?php if ( 1 < $output_num_runs ) : ?>
				<br /><p><?php echo sprintf( esc_attr__( 'Your CSV file contains more than %1$s tickets. The FooEvents Ticket Importer processes tickets in batches of tickets in order to ensure reliable results. Once you have completed importing the current batch, please repeat the process until all tickets have been imported.', 'woocommerce-events' ), esc_attr( $batch_size ) ); ?></p>
			<?php endif; ?>		
		</div>
		<div class="fooevents-import-section">
			<form name="fooevents-theme-viewer-upload-form" action="<?php echo esc_attr( admin_url( 'admin.php' ) ); ?>?page=fooevents-import-tickets" method="POST" enctype="multipart/form-data">				
				<input type="hidden" name="step" value="2" />
				<input type="hidden" name="fooevents-import-tickets-run" value="<?php echo esc_attr( $file_run ); ?>" />
				<input type="hidden" name="fooevents-import-tickets-max-run" value="<?php echo esc_attr( $output_num_runs ); ?>" />
				<input type="hidden" name="fooevents-import-tickets-file" value="<?php echo htmlspecialchars( wp_json_encode( $tickets_array ) ); ?>">
				<table class="form-table">
					<tr>
						<th>
							<?php echo esc_html( __( 'CSV Column name', 'woocommerce-events' ) ); ?>
						</th>
						<th>
							<?php echo esc_html( __( 'Map to ticket field', 'woocommerce-events' ) ); ?>
						</th>
					</tr>
					<?php $x = 0; ?>
					<?php foreach ( $tickets_array[0] as $key => $field_name ) : ?>
						<tr>
							<td>
								<label><?php echo esc_attr( $field_name ); ?></label>
							</td>
							<td>
								<select name="fooevents-csv-field[]">
									<option value="meta-<?php echo esc_attr( $this->process_clean_csv_heading( $field_name ) ); ?>"><?php esc_attr_e( 'Meta field', 'woocommerce-events' ); ?></option>
									<option value="custom-<?php echo esc_attr( $this->process_clean_csv_heading( $field_name ) ); ?>"<?php echo ( true === $this->is_custom_attendee_field( $field_name ) ) ? ' SELECTED' : ''; ?>><?php esc_attr_e( 'Custom Attendee Field', 'woocommerce-events' ); ?></option>
									<option value="fname" <?php echo ( ( empty( $error_message ) && 'Attendee First Name' === trim( $field_name ) ) || ( isset( $_POST['fooevents-csv-field'][ $x ] ) && 'fname' === $_POST['fooevents-csv-field'][ $x ] ) ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Attendee First Name', 'woocommerce-events' ); ?></option>
									<option value="lname" <?php echo ( empty( $error_message ) && 'Attendee Last Name' === trim( $field_name ) || ( isset( $_POST['fooevents-csv-field'][ $x ] ) && 'lname' === $_POST['fooevents-csv-field'][ $x ] ) ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Attendee Last Name', 'woocommerce-events' ); ?></option>
									<option value="email" <?php echo ( empty( $error_message ) && 'Attendee Email' === trim( $field_name ) || ( isset( $_POST['fooevents-csv-field'][ $x ] ) && 'email' === $_POST['fooevents-csv-field'][ $x ] ) ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Attendee Email', 'woocommerce-events' ); ?></option>
									<option value="phone" <?php echo ( empty( $error_message ) && 'Attendee Telephone' === trim( $field_name ) || ( isset( $_POST['fooevents-csv-field'][ $x ] ) && 'phone' === $_POST['fooevents-csv-field'][ $x ] ) ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Attendee Telephone', 'woocommerce-events' ); ?></option>
									<option value="company" <?php echo ( empty( $error_message ) && 'Attendee Company' === trim( $field_name ) || ( isset( $_POST['fooevents-csv-field'][ $x ] ) && 'company' === $_POST['fooevents-csv-field'][ $x ] ) ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Attendee Company', 'woocommerce-events' ); ?></option>
									<option value="designation" <?php echo ( empty( $error_message ) && 'Attendee Designation' === trim( $field_name ) || ( isset( $_POST['fooevents-csv-field'][ $x ] ) && 'designation' === $_POST['fooevents-csv-field'][ $x ] ) ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Attendee Designation', 'woocommerce-events' ); ?></option>
									<option value="pid" <?php echo ( empty( $error_message ) && 'Event ID' === trim( $field_name ) || ( isset( $_POST['fooevents-csv-field'][ $x ] ) && 'pid' === $_POST['fooevents-csv-field'][ $x ] ) ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Event ID', 'woocommerce-events' ); ?></option>
									<option value="bookingdate" <?php echo ( empty( $error_message ) && 'Booking Date ID' === trim( $field_name ) || ( isset( $_POST['fooevents-csv-field'][ $x ] ) && 'bookingdate' === $_POST['fooevents-csv-field'][ $x ] ) ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Booking Date ID', 'woocommerce-events' ); ?></option>
									<option value="bookingslot" <?php echo ( empty( $error_message ) && 'Booking Slot ID' === trim( $field_name ) || ( isset( $_POST['fooevents-csv-field'][ $x ] ) && 'bookingslot' === $_POST['fooevents-csv-field'][ $x ] ) ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Booking Slot ID', 'woocommerce-events' ); ?></option>
									<option value="productvariation" <?php echo ( empty( $error_message ) && 'Variation ID' === trim( $field_name ) || ( isset( $_POST['fooevents-csv-field'][ $x ] ) && 'productvariation' === $_POST['fooevents-csv-field'][ $x ] ) ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Variation ID', 'woocommerce-events' ); ?></option>
									<option value="oid" <?php echo ( empty( $error_message ) && 'Order ID' === trim( $field_name ) || ( isset( $_POST['fooevents-csv-field'][ $x ] ) && 'productvariation' === $_POST['fooevents-csv-field'][ $x ] ) ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Order ID', 'woocommerce-events' ); ?></option>
								</select>
							</td>
						</tr>   
						<?php $x++; ?>	
					<?php endforeach; ?>
				</table>
			</div>
			<div class="fooevents-import-section fooevents-import-section-footer">
				<input type="submit" value="<?php esc_attr_e( 'Run the importer', 'woocommerce-events' ); ?>" class="button button-primary button-hero" />
			</div>
		</form>
	</div> 
</div> 
