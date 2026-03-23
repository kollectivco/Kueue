<?php
/**
 * Offline Check-ins Import
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'FooEvents Check-ins Import', 'woocommerce-events' ); ?></h1>
	<?php
	if ( isset( $_FILES['fooevents-checkins-import'] ) && check_admin_referer( 'fooevents-checkins-import' ) ) {
		if ( isset( $_FILES['fooevents-checkins-import']['error'] ) && $_FILES['fooevents-checkins-import']['error'] > 0 ) {
			wp_die( esc_html__( 'An error occurred while importing your offline check-ins. Please try again and re-export the XML file if required.', 'woocommerce-events' ) );
		} else {
			$file_name       = isset( $_FILES['fooevents-checkins-import']['name'] ) ? sanitize_text_field( wp_unslash( $_FILES['fooevents-checkins-import']['name'] ) ) : '';
			$file_name_split = explode( '.', $file_name );
			$file_ext        = strtolower( end( $file_name_split ) );

			if ( ( 'xml' === $file_ext ) ) {
				WP_Filesystem();

				global $wp_filesystem;

				libxml_use_internal_errors( true );

				$xml_data = $wp_filesystem->get_contents( sanitize_text_field( wp_unslash( isset( $_FILES['fooevents-checkins-import']['tmp_name'] ) ? $_FILES['fooevents-checkins-import']['tmp_name'] : '' ) ) );
				$xml      = simplexml_load_string( $xml_data );

				if ( false === $xml || 'fooevents_checkins' !== $xml->getName() ) {
					echo "<div class='notice notice-error is-dismissible'><p>" . esc_html__( 'Unable to read the XML file. Please upload a valid XML file that was exported from the FooEvents Check-ins app.' ) . '</p></div>';
					?>
						<p>
					<?php
					foreach ( libxml_get_errors() as $import_error ) {
						printf(
							'<strong>%s %s, %s %s:</strong> <em>%s</em><br/>',
							esc_html__( 'Line', 'woocommerce-events' ),
							esc_html( $import_error->line ),
							esc_html__( 'Column', 'woocommerce-events' ),
							esc_html( $import_error->column ),
							esc_html( $import_error->message )
						);
					}
					?>
						</p>
					<?php
				} else {
					$total_checkins = count( $xml->children() );

					if ( $total_checkins > 0 ) {
						$fooevents_offline_checkins = array();
						?>
							<h3><?php esc_html_e( 'Importing offline check-ins...', 'woocommerce-events' ); ?></h3>
							<?php
							ob_start();

							printf(
								"%s: %d\n\n",
								esc_html__( 'Offline check-ins imported', 'woocommerce-events' ),
								esc_html( $total_checkins )
							);

							$checkin_count = 1;
							$failure       = false;

							foreach ( $xml->children() as $offline_checkin ) {
								$offline_checkin_ticket_id = $offline_checkin->fooevents_ticket_id->__toString();
								$offline_checkin_status    = $offline_checkin->fooevents_ticket_status->__toString();

								$import_result = '#';

								if ( strpos( $offline_checkin_ticket_id, '_' ) !== false ) {

									$temp_ticket_array = explode( '_', $offline_checkin_ticket_id );

									$ticket_id = $temp_ticket_array[0];
									$day       = $temp_ticket_array[1];

									$import_result .= $ticket_id . ' - ' . esc_html__( 'Day', 'woocommerce-events' ) . ' ' . $day;

									$result = update_ticket_multiday_status( $ticket_id, $offline_checkin_status, $day );

								} else {

									$import_result .= $offline_checkin_ticket_id;

									$result = update_ticket_status( $offline_checkin_ticket_id, wp_strip_all_tags( $offline_checkin_status ) );

								}

								$import_result .= ' - ';

								if ( 'Status is required' === $result ) {
									$import_result .= __( 'Status is required', 'woocommerce-events' );
								} elseif ( 'Status updated' === $result ) {
									$import_result .= __( 'Status updated', 'woocommerce-events' );
								} elseif ( 'Status unchanged' === $result ) {
									$import_result .= __( 'Status unchanged', 'woocommerce-events' );
								} elseif ( 'Status not updated' === $result ) {
									$import_result .= __( 'Status not updated', 'woocommerce-events' );
								}

								if ( 'Checked In' === $offline_checkin_status ) {
									$import_result .= ' (' . __( 'Checked-in', 'woocommerce-events' ) . ')';
								} elseif ( 'Not Checked In' === $offline_checkin_status ) {
									$import_result .= ' (' . __( 'Not checked-in', 'woocommerce-events' ) . ')';
								} elseif ( 'Canceled' === $offline_checkin_status ) {
									$import_result .= ' (' . __( 'Canceled', 'woocommerce-events' ) . ')';
								} elseif ( 'Unpaid' === $offline_checkin_status ) {
									$import_result .= ' (' . __( 'Unpaid', 'woocommerce-events' ) . ')';
								}

								printf(
									"Offline Check-in %d/%d: %s\n",
									esc_html( $checkin_count++ ),
									esc_html( $total_checkins ),
									esc_html( $import_result )
								);
							}

							$import_output = ob_get_contents();

							ob_get_clean();
							?>
						<textarea id="fooevents_import_log" class="widefat" rows="10" readonly><?php echo esc_html( $import_output ); ?></textarea>
						<p style="text-align:right;"><button class="button button-primary" href="javascript:void(0);" id="fooevents_import_log_copy_button"><?php esc_html_e( 'Copy to clipboard', 'woocommerce-events' ); ?></button></p>
						<?php
						if ( $failure ) {
							echo "<div class='notice notice-error is-dismissible'><p>" . esc_html__( 'There was a problem importing one or more offline check-ins.' ) . '</p></div>';
						} else {
							echo "<div class='notice notice-success is-dismissible'><p>" . esc_html__( 'All offline check-ins have been imported successfully.' ) . '</p></div>';
						}
					} else {
						echo "<div class='notice notice-error is-dismissible'><p>" . esc_html__( 'No offline check-ins were found.' ) . '</p></div>';
					}
				}
			} else {
				echo "<div class='notice notice-error is-dismissible'><p>" . esc_html__( 'Invalid file format. Please upload a valid XML file that was exported directly from the FooEvents Check-in app.' ) . '</p></div>';
			}
		}
		?>
			<hr />
		<?php
	}
	?>
	<p><?php esc_html_e( 'The FooEvents Check-ins import tool allows you to import check-ins that were performed while using the Check-ins app in offline mode. This is useful if you performed a large volume of check-ins while in offline mode and are having trouble syncing them.', 'woocommerce-events' ); ?></p>
	<p><?php esc_html_e( 'This custom XML file must be exported from the settings screen in the FooEvents Check-ins app and saved to a location on your computer. The check-ins will be imported in the exact order they were performed.', 'woocommerce-events' ); ?></p>
	<form enctype="multipart/form-data" id="fooevents-checkins-import-upload-form" method="post" class="wp-upload-form" action="">
		<p>
			<?php
				wp_nonce_field( 'fooevents-checkins-import' );

				$bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
				$size  = size_format( $bytes );

				printf(
					'<label for="upload">%s</label> (%s)',
					esc_html__( 'Choose a file from your computer:' ),
					// translators: The maximum size of the file to upload.
					sprintf( esc_html__( 'Maximum size: %s' ), esc_html( $size ) )
				);
				?>
			<input type="file" id="upload" name="fooevents-checkins-import" size="25" accept=".xml" />
		</p>
		<?php submit_button( esc_html__( 'Upload file and import', 'woocommerce-events' ), 'primary' ); ?>
	</form>
</div>
<style type="text/css">
	img#fooevents_importing_spinner {
		vertical-align:text-bottom;
		margin-left:1em;
	}
</style>
<script type="text/javascript">
	jQuery('form#fooevents-checkins-import-upload-form').submit(function(e) {
		jQuery('form#fooevents-checkins-import-upload-form input#submit').attr('value', '<?php esc_attr_e( 'Importing, please wait...', 'woocommerce-events' ); ?>').prop('disabled', true).parent().append('<img src="<?php echo esc_attr( get_admin_url() ); ?>images/loading.gif" id="fooevents_importing_spinner" />');
	});

	jQuery('button#fooevents_import_log_copy_button').click(function() {
		var copyButton = jQuery(this);

		copyButton.prop('disabled', true);

		jQuery('textarea#fooevents_import_log').select();

		document.execCommand('copy');

		copyButton.text('<?php esc_html_e( 'Copied!', 'woocommerce-events' ); ?>');

		setTimeout(function() {
			copyButton.text('<?php esc_html_e( 'Copy to clipboard', 'woocommerce-events' ); ?>');

			copyButton.prop('disabled', false);
		}, 1000);
	});
</script>
