<?php
/**
 * Ticket template for receipt ticket theme
 *
 * @link https://www.fooevents.com
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin/templates/receipt_ticket_theme
 */

?>
<!-- EVENT DETAILS -->
<?php if ( ! empty( $ticket['ticketNumber'] ) && 1 === $ticket['ticketNumber'] ) : ?>
	<!-- EMAIL CONTAINER -->
	<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" style="border-collapse:collapse">
		<tr>
			<td style="padding:0 0 40px;border-bottom: solid 1px #bbb;">

				<!-- EVENT TITLE -->
				<h1 style="text-align: left"><?php echo esc_html( $ticket['name'] ); ?></h1>

				<!-- EVENT DATE / TIME -->
				<?php if ( 'off' !== $ticket['WooCommerceEventsTicketDisplayDateTime'] ) : ?>

					<?php if ( ( isset( $ticket['WooCommerceEventsBookingSlot'] ) || isset( $ticket['WooCommerceEventsBookingDate'] ) ) && 'off' !== $ticket['WooCommerceEventsTicketDisplayBookings'] ) : ?>

						<p>
							<?php echo esc_html( $ticket['WooCommerceEventsBookingDate'] ); ?><br />
							<?php echo esc_html( $ticket['WooCommerceEventsBookingSlot'] ); ?>
						</p>

					<?php else : ?> 

						<p>
							<?php
							if ( ! empty( $ticket['WooCommerceEventsSelectDate'] ) && ! empty( $ticket['WooCommerceEventsSelectDate'][0] ) && 'select' === $ticket['WooCommerceEventsType'] ) :

								$x = 0;
								foreach ( $ticket['WooCommerceEventsSelectDate'] as $select_date ) :
									if ( '' !== $select_date ) :
										if ( $x > 0 ) {
											echo ', ';
										}
										echo esc_html( $select_date );
									endif;
										++$x;
								endforeach;

							elseif ( ! empty( $ticket['WooCommerceEventsDate'] ) ) :

									echo esc_html( $ticket['WooCommerceEventsDate'] );
								if ( ! empty( $ticket['WooCommerceEventsEndDate'] ) ) :
									echo ' - ' . esc_html( $ticket['WooCommerceEventsEndDate'] );
									endif;

							endif;
							?>
						</p>
						<p>
							<?php if ( 'select' !== $ticket['WooCommerceEventsType'] || 'on' === $ticket['WooCommerceEventsSelectGlobalTime'] ) : ?>
								<?php echo ' '; ?>
								<?php echo esc_html( $ticket['WooCommerceEventsHour'] ); ?>:<?php echo esc_html( $ticket['WooCommerceEventsMinutes'] ); ?><?php echo ( ! empty( $ticket['WooCommerceEventsPeriod'] ) ) ? esc_html( $ticket['WooCommerceEventsPeriod'] ) : ''; ?>
								<?php echo ( ! empty( $ticket['WooCommerceEventsTimeZone'] ) ) ? ' ' . esc_html( $ticket['WooCommerceEventsTimeZone'] ) : ''; ?>
								<?php if ( '00' !== $ticket['WooCommerceEventsHourEnd'] ) : ?>
									<?php echo ' - '; ?>
									<?php echo esc_html( $ticket['WooCommerceEventsHourEnd'] ); ?>:<?php echo esc_html( $ticket['WooCommerceEventsMinutesEnd'] ); ?><?php echo ( ! empty( $ticket['WooCommerceEventsEndPeriod'] ) ) ? esc_html( $ticket['WooCommerceEventsEndPeriod'] ) : ''; ?>
									<?php echo ( ! empty( $ticket['WooCommerceEventsTimeZone'] ) ) ? ' ' . esc_html( $ticket['WooCommerceEventsTimeZone'] ) : ''; ?>
								<?php endif; ?>
							<?php endif; ?>
						</p> 

					<?php endif; ?> 

				<?php endif; ?>  			 

				<!-- TICKET TEXT -->
				<?php if ( ! empty( $ticket['WooCommerceEventsTicketText'] ) ) : ?>
					<?php echo nl2br( esc_html( $ticket['WooCommerceEventsTicketText'] ) ); ?> 
				<?php endif; ?>

				<!-- LOCATION -->
				<?php if ( ! empty( $ticket['WooCommerceEventsLocation'] ) ) : ?> 
					<h3><?php esc_html_e( 'Location', 'woocommerce-events' ); ?></h3>
					<p><?php echo esc_html( $ticket['WooCommerceEventsLocation'] ); ?></p>
				<?php endif; ?>

				<!-- DIRECTIONS -->
				<?php if ( ! empty( $ticket['WooCommerceEventsDirections'] ) ) : ?> 
					<h3><?php esc_html_e( 'Directions', 'woocommerce-events' ); ?></h3>
					<p><?php echo esc_html( $ticket['WooCommerceEventsDirections'] ); ?></p>
				<?php endif; ?>

				<!-- CONTACT -->
				<?php if ( ! empty( $ticket['WooCommerceEventsSupportContact'] ) ) : ?>
					<h3><?php esc_html_e( 'Contact us for questions and concerns', 'woocommerce-events' ); ?></h3>
					<p><?php echo esc_html( $ticket['WooCommerceEventsSupportContact'] ); ?></p>
				<?php endif; ?>	

			</td>
		</tr>
	</table>

<?php endif; ?>

<div style="page-break-before: always;"></div>
<table border="0" cellpadding="5" cellspacing="0" style="margin: 0 auto; width:100%; max-width: 100%; font-size:12px; font-family: <?php echo esc_attr( $font_family ); ?>;">

	<!-- TICKET SERIES NUMBER -->
	<tr>
		<td colspan="2" align="center" style="padding-top:20px">
			<span style="border: solid 2px #000; padding:5px 10px; border-radius:30px;"><strong><?php esc_html_e( 'Ticket', 'woocommerce-events' ); ?> <?php echo esc_html( $ticket['ticketNumber'] ); ?></strong></span>
		</td>
	</tr>

	<!-- TICKET PAGINATION AND BARCODE OR QR CODE-->
	<tr>
		<td colspan="2" align="center" style="padding-bottom:20px;border-bottom: solid 1px #bbb;">

			<!-- BARCODE OR QR CODE -->
			<?php if ( 'off' !== $ticket['WooCommerceEventsTicketDisplayBarcode'] ) : ?>
				<p style="text-align: center"><img src="<?php echo esc_url( $barcodeURL ); ?>" height="<?php echo esc_attr( $ticket['ticketBarcodeQRWidth'] ); ?>px" width="<?php echo esc_attr( $ticket['ticketBarcodeQRWidth'] ); ?>px" /></p> <?php // phpcs:ignore WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase ?>
			<?php endif; ?>			

			<!-- EVENT TITLE -->
			<h2 style="text-align: center; padding:0; margin:0;"><?php echo esc_html( $ticket['name'] ); ?></h2>

			<!-- EVENT DATE / TIME -->
			<?php if ( 'off' !== $ticket['WooCommerceEventsTicketDisplayDateTime'] ) : ?>	
				<?php if ( 'bookings' !== $ticket['WooCommerceEventsType'] ) : ?>
					<p style="text-align: center; padding:0; margin:0;">
						<?php
						if ( ! empty( $ticket['WooCommerceEventsDate'] ) ) :
							echo esc_html( $ticket['WooCommerceEventsDate'] );
							if ( ! empty( $ticket['WooCommerceEventsEndDate'] ) ) :
								echo ' - ' . esc_html( $ticket['WooCommerceEventsEndDate'] );
							endif;
						endif;
						?>

						<?php if ( 'select' !== $ticket['WooCommerceEventsType'] || 'on' === $ticket['WooCommerceEventsSelectGlobalTime'] ) : ?>
							<?php echo ' '; ?>
							<?php echo esc_html( $ticket['WooCommerceEventsHour'] ); ?>:<?php echo esc_html( $ticket['WooCommerceEventsMinutes'] ); ?><?php echo ( ! empty( $ticket['WooCommerceEventsPeriod'] ) ) ? esc_html( $ticket['WooCommerceEventsPeriod'] ) : ''; ?>
							<?php echo ( ! empty( $ticket['WooCommerceEventsTimeZone'] ) ) ? ' ' . esc_html( $ticket['WooCommerceEventsTimeZone'] ) : ''; ?>
							<?php if ( '00' !== $ticket['WooCommerceEventsHourEnd'] ) : ?>
								<?php echo ' - '; ?>
								<?php echo esc_html( $ticket['WooCommerceEventsHourEnd'] ); ?>:<?php echo esc_html( $ticket['WooCommerceEventsMinutesEnd'] ); ?><?php echo ( ! empty( $ticket['WooCommerceEventsEndPeriod'] ) ) ? esc_html( $ticket['WooCommerceEventsEndPeriod'] ) : ''; ?>
								<?php echo ( ! empty( $ticket['WooCommerceEventsTimeZone'] ) ) ? ' ' . esc_html( $ticket['WooCommerceEventsTimeZone'] ) : ''; ?>
							<?php endif; ?>
							<br />
						<?php endif; ?>
					</p>
				<?php endif; ?> 
			<?php endif; ?> 						
		</td>
	</tr> 	

	<!-- TICKET NUMBER  -->
	<tr>
		<td valign="top" align="left" style="padding-top:20px;">
			<?php esc_html_e( 'Ticket Number', 'woocommerce-events' ); ?>:
		</td>
		<td valign="top" align="left" style="padding-top:20px;">
			<!-- TICKET NUMBER -->
			<strong><?php echo esc_html( $ticket['WooCommerceEventsTicketID'] ); ?></strong>
		</td>
	</tr>				

	<!-- MULTI-DAY DETAILS -->
	<?php if ( ! empty( $ticket['WooCommerceEventsTicketDisplayMultiDay'] ) && 'on' === $ticket['WooCommerceEventsTicketDisplayMultiDay'] ) : ?>
		<?php $x = 1; ?>
		<?php $y = 0; ?>    
		<?php foreach ( $ticket['WooCommerceEventsSelectDate'] as $date ) : ?>
			<tr>
				<td align="left">
					<?php printf( esc_html__( '%1$s %2$d: ', 'woocommerce-events' ), esc_html( $ticket['dayTerm'] ), esc_html( $x ) ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment ?>
				</td>
				<td align="left">
					<strong><?php echo esc_attr( $date ); ?></strong><br /> 
					<small>
						<?php if ( ! empty( $ticket['WooCommerceEventsSelectDateHour'][ $y ] ) && ! empty( $ticket['WooCommerceEventsSelectDateMinutes'][ $y ] ) ) : ?>
							<?php echo esc_html( $ticket['WooCommerceEventsSelectDateHour'][ $y ] . ':' . $ticket['WooCommerceEventsSelectDateMinutes'][ $y ] ); ?><?php echo( isset( $ticket['WooCommerceEventsSelectDatePeriod'][ $y ] ) ? ' ' . esc_html( $ticket['WooCommerceEventsSelectDatePeriod'][ $y ] ) : '' ); ?>
						<?php endif; ?>
						<?php if ( ! empty( $ticket['WooCommerceEventsSelectDateHourEnd'][ $y ] ) && ! empty( $ticket['WooCommerceEventsSelectDateMinutesEnd'][ $y ] ) ) : ?>
							<?php echo ' - ' . esc_html( $ticket['WooCommerceEventsSelectDateHourEnd'][ $y ] . ':' . $ticket['WooCommerceEventsSelectDateMinutesEnd'][ $y ] ); ?><?php echo( isset( $ticket['WooCommerceEventsSelectDatePeriodEnd'][ $y ] ) ? ' ' . esc_html( $ticket['WooCommerceEventsSelectDatePeriodEnd'][ $y ] ) : '' ); ?>
						<?php endif; ?>
					</small>								
				</td>
			</tr>												
			<?php ++$x; ?>
			<?php ++$y; ?>
		<?php endforeach; ?>
	<?php endif; ?>

	<!-- BOOKING DETAILS -->                                      
	<?php if ( isset( $ticket['WooCommerceEventsBookingSlot'] ) || isset( $ticket['WooCommerceEventsBookingDate'] ) ) : ?>					
		<?php if ( 'off' !== $ticket['WooCommerceEventsTicketDisplayBookings'] ) : ?>
			<tr>
				<td align="left">
					<?php echo esc_html( $ticket['WooCommerceEventsBookingsSlotTerm'] ); ?>:
				</td>
				<td align="left">
					<strong><?php echo esc_html( $ticket['WooCommerceEventsBookingSlot'] ); ?></strong>
				</td>
			</tr>
			<tr>
				<td align="left">
					<?php echo esc_html( $ticket['WooCommerceEventsBookingsDateTerm'] ); ?>: 
				</td>
				<td align="left">
					<strong><?php echo esc_html( $ticket['WooCommerceEventsBookingDate'] ); ?></strong>
				</td>								
			</tr>
		<?php endif; ?> 
	<?php endif; ?> 	

	<!-- SEATING -->                                      
	<?php if ( ! empty( $ticket['fooevents_seating_options_array'] ) ) : ?>
		<tr>
			<td align="left">
				<?php echo esc_html( $ticket['fooevents_seating_options_array']['row_name_label'] ); ?>:
			</td>
			<td align="left">
				<strong><?php echo esc_html( $ticket['fooevents_seating_options_array']['row_name'] ); ?></strong>
			</td>
		</tr>
		<tr>
			<td align="left">
				<?php echo esc_html( $ticket['fooevents_seating_options_array']['seat_number_label'] ); ?>:
			</td>
			<td align="left">
				<strong><?php echo esc_html( $ticket['fooevents_seating_options_array']['seat_number'] ); ?></strong>
			</td>
		</tr>
	<?php endif; ?>                                     

	<!-- TICKET TYPE -->
	<?php if ( ! empty( $ticket['WooCommerceEventsTicketType'] ) ) : ?>
		<tr>
			<td align="left">
				<?php esc_html_e( 'Ticket Type:', 'woocommerce-events' ); ?>   
			</td>
			<td align="left">
				<strong><?php echo esc_html( $ticket['WooCommerceEventsTicketType'] ); ?></strong>
			</td>
		</tr>
	<?php endif; ?>

	<!-- PRICE -->
	<?php if ( 'off' !== $ticket['WooCommerceEventsTicketDisplayPrice'] ) : ?>
		<tr>
			<td align="left">
				<?php esc_html_e( 'Price:', 'woocommerce-events' ); ?>
			</td>
			<td align="left">
				<strong>
					<?php
					if ( ! empty( $ticket['WooCommerceEventsPrice'] ) ) {
						echo esc_html( wp_strip_all_tags( $ticket['WooCommerceEventsPrice'] ) );
					} elseif ( ! empty( $ticket['price'] ) ) {
						echo esc_html( wp_strip_all_tags( $ticket['price'] ) );
					}
					?>
				</strong>
			</td>
		</tr>
	<?php endif; ?>

	<!-- VARIATIONS -->
	<?php if ( ! empty( $ticket['WooCommerceEventsVariations'] ) ) : ?>
		<?php foreach ( $ticket['WooCommerceEventsVariations'] as $variation_name => $variation_value ) : ?>
			<?php if ( 'Ticket Type' !== $variation_name ) : ?>
			<tr>
				<td align="left">
					<?php echo esc_html( $variation_name ); ?>:
				</td>
				<td align="left">
					<strong><?php echo esc_html( $variation_value ); ?></strong>
				</td>
			</tr>
			<?php endif; ?>
		<?php endforeach; ?>        
	<?php endif; ?>		

	<!-- ATTENDEE FIELDS -->  
	<?php if ( 'off' !== $ticket['WooCommerceEventsTicketPurchaserDetails'] ) : ?>

		<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeName'] ) ) : ?>
			<tr>
				<td align="left">
					<?php esc_html_e( 'Ticket Holder:', 'woocommerce-events' ); ?>
				</td>
				<td align="left">
					<strong><?php echo esc_html( $ticket['WooCommerceEventsAttendeeName'] ); ?> <?php echo esc_html( $ticket['WooCommerceEventsAttendeeLastName'] ); ?></strong>
				</td>
			</tr>
		<?php endif; ?>

		<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeTelephone'] ) ) : ?>
			<tr>
				<td align="left">
					<?php esc_html_e( 'Telephone Number:', 'woocommerce-events' ); ?>
				</td>
				<td align="left">
					<strong><?php echo esc_html( $ticket['WooCommerceEventsAttendeeTelephone'] ); ?></strong>
				</td>
			</tr>
		<?php endif; ?>

		<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeCompany'] ) ) : ?>
			<tr>
				<td align="left">
					<?php esc_html_e( 'Company:', 'woocommerce-events' ); ?>   
				</td>
				<td align="left">
					<strong><?php echo esc_html( $ticket['WooCommerceEventsAttendeeCompany'] ); ?></strong>
				</td>
			</tr>
		<?php endif; ?>

		<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeDesignation'] ) ) : ?>
			<tr>
				<td align="left">
					<?php esc_html_e( 'Designation:', 'woocommerce-events' ); ?>
				</td>
				<td align="left">
					<strong><?php echo esc_html( $ticket['WooCommerceEventsAttendeeDesignation'] ); ?></strong>
				</td>
			</tr>
		<?php endif; ?>

	<?php endif; ?>

	<!-- CUSTOM ATTENDEE FIELDS -->
	<?php if ( ! empty( $ticket['fooevents_custom_attendee_fields_options_array'] ) && ( isset( $ticket['WooCommerceEventsIncludeCustomAttendeeDetails'] ) && 'off' !== $ticket['WooCommerceEventsIncludeCustomAttendeeDetails'] ) ) : ?>
		<?php foreach ( $ticket['fooevents_custom_attendee_fields_options_array'] as $custom_attendee_fields ) : ?>
			<tr>
				<td align="left">
					<?php echo esc_html( $custom_attendee_fields['label'] ); ?>:
				</td>
				<td align="left">
					<strong><?php echo esc_html( $custom_attendee_fields['value'] ); ?></strong>
				</td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?> 

	<!-- ZOOM INFORMATION -->
	<?php if ( ! empty( $ticket['WooCommerceEventsTicketDisplayZoom'] ) && 'off' !== $ticket['WooCommerceEventsTicketDisplayZoom'] && ! empty( $ticket['WooCommerceEventsZoomText'] ) ) : ?>
		<tr>
			<td valign="top" align="left">
				<?php esc_html_e( 'Zoom Details', 'woocommerce-events' ); ?>:
			</td>
			<td valign="top" align="left">
				<strong><?php echo esc_html( $ticket['WooCommerceEventsZoomText'] ); ?></strong>
			</td>
		</tr>
	<?php endif; ?>	
	<tr>
		<td colspan="2" align="center" style="border-bottom: dashed 1px #bbb;"></td>
	</tr>	
</table>
