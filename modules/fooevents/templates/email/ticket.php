<?php
/**
 * Ticket Theme Body
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- EVENT DETAILS -->
<?php if ( ( ! empty( $ticket['ticketNumber'] ) && 1 === $ticket['ticketNumber'] ) || ( __( 'Preview Event', 'woocommerce-events' ) === $ticket['name'] ) ) : ?>

	<?php if ( ! empty( $ticket['type'] ) && 'PDF' == $ticket['type'] ) : ?>
		<!-- PDF CONTAINER -->
		<div class="intro" style="padding: 30px; width: 100%; max-width: 640px; margin: 0 auto; font-size:14px; font-family: <?php echo wp_kses_post( $font_family ); ?>; text-align: left">		
	<?php else : ?>
		<!-- EMAIL CONTAINER -->
		<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" style="border-collapse:collapse"><tr><td align="center" style="text-align:center">
		<table border="0" cellpadding="20" cellspacing="0" style="margin: 0 auto; width:100%; max-width: 640px;"><tr><td style="text-align:left">
	<?php endif; ?> 

		<!-- LOGO -->
			<?php if ( ! empty( $ticket['WooCommerceEventsTicketLogo'] ) ) : ?>
				<p>
					<img src="<?php echo esc_attr( $ticket['WooCommerceEventsTicketLogo'] ); ?>" alt="<?php echo esc_attr( $ticket['name'] ); ?>" style="width: auto; max-width:100%"/>
				</p>
			<?php endif; ?> 

		<!-- GRAPHIC -->
			<?php if ( ! empty( $ticket['WooCommerceEventsTicketHeaderImage'] ) ) : ?>
				<p>
					<img src="<?php echo esc_attr( $ticket['WooCommerceEventsTicketHeaderImage'] ); ?>" alt="<?php echo esc_attr( $ticket['name'] ); ?>" width="100%"/>
				</p>
			<?php endif; ?> 

		<!-- EVENT TITLE -->
			<h1 style="text-align: left"><?php echo esc_attr( $ticket['name'] ); ?></h1>			 

		<!-- TICKET TEXT -->
			<?php if ( ! empty( $ticket['WooCommerceEventsTicketText'] ) ) : ?>
				<?php echo nl2br( wp_kses_post( $ticket['WooCommerceEventsTicketText'] ) ); ?> 
			<?php endif; ?>

		<!-- LOCATION -->
			<?php if ( ! empty( $ticket['WooCommerceEventsLocation'] ) ) : ?> 
				<h3><?php esc_attr_e( 'Location', 'woocommerce-events' ); ?></h3>
				<p><?php echo esc_attr( $ticket['WooCommerceEventsLocation'] ); ?></p>
			<?php endif; ?>

		<!-- DIRECTIONS -->
			<?php if ( ! empty( $ticket['WooCommerceEventsDirections'] ) ) : ?> 
				<h3><?php esc_attr_e( 'Directions', 'woocommerce-events' ); ?></h3>
				<p><?php echo esc_attr( $ticket['WooCommerceEventsDirections'] ); ?></p>
			<?php endif; ?>

		<!-- CONTACT -->
			<?php if ( ! empty( $ticket['WooCommerceEventsSupportContact'] ) ) : ?>
				<h3><?php esc_attr_e( 'Contact us for questions and concerns', 'woocommerce-events' ); ?></h3>
				<p><?php echo esc_attr( $ticket['WooCommerceEventsSupportContact'] ); ?></p>
			<?php endif; ?>

		<!-- PDF FOOTER TEXT-->
			<?php if ( ! empty( $ticket['FooEventsTicketFooterText'] ) ) : ?>
				<?php echo esc_attr( $ticket['FooEventsTicketFooterText'] ); ?>
			<?php endif; ?>		

		<!-- POWERED BY TEXT -->
			<?php if ( isset( $ticket['WooCommerceEventsDisplayPoweredby'] ) && 'off' !== $ticket['WooCommerceEventsDisplayPoweredby'] ) : ?>
				<p><?php echo esc_attr_e( 'Powered by', 'woocommerce-events' ); ?> <a href="https://www.fooevents.com/?ref=ticket"><?php echo esc_attr_e( 'FooEvents.com', 'woocommerce-events' ); ?></a></p>
			<?php endif; ?>  

	<?php if ( ! empty( $ticket['type'] ) && 'PDF' == $ticket['type'] ) : ?>
		<!-- PDF CONTAINER -->	 
		</div>
	<?php else : ?> 
		<!-- EMAIL CONTAINER -->
		</td></tr></table>
		</td></tr></table>
	<?php endif; ?>

<?php endif; ?>

<?php if ( 0 !== $ticket['ticketNumber'] % 2 ) : ?>
	<div style="page-break-before: always;"></div>
<?php endif; ?>

<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" style="border-collapse:collapse">
	<tr>
		<td valign="top" style="text-align:center">
			<table border="0" cellpadding="20" cellspacing="0" style=" margin: 0 auto; width:100%; max-width: 640px;">
				<tr>
					<td style="text-align:left">
						<table border="0" cellpadding="15" cellspacing="0" width="100%" class="ticket" style="border: solid 1px #ddd; border-collapse:collapse; font-family: <?php echo wp_kses_post( $font_family ); ?>;">	
							<!-- TICKET BODY  -->
							<tr>
								<td valign="top" align="center" role="presentation" class="wide" style="width:30%; border-right: dashed 1px #ddd; border-bottom: solid 1px #ddd; border-bottom-width: 0;">
																	
									<!-- BARCODE OR QR CODE -->
									<?php if ( 'off' !== $ticket['WooCommerceEventsTicketDisplayBarcode'] ) : ?>
										<img src="<?php echo esc_attr( $barcodeURL ); ?>" style="width:100%; max-width: 184px;" /><br /> <?php // phpcs:ignore -- Variable name is not in valid snake_case format ?>
									<?php endif; ?>

									<!-- ADD TO WALLET -->
									<?php if ( ! empty( $ticket['WooCommerceEventsTicketDisplayAddToWallet'] ) && 'off' != $ticket['WooCommerceEventsTicketDisplayAddToWallet'] ) : ?>
										<span class="no-print">
											<a href="<?php echo esc_attr( site_url() ); ?>/wp-admin/admin-ajax.php?action=fooevents_wallet_pass&ticket=<?php echo $ticket['ID']; ?>" style="background-color:<?php echo $ticket['WooCommerceEventsTicketButtonColor']; ?>; color:<?php echo $ticket['WooCommerceEventsTicketTextColor']; ?>; margin-top:20px; padding:10px 0; width:100%; text-align:center; text-decoration:none; display:inline-block; border-radius:5px; font-weight: bold; font-size:14px; font-family:<?php echo wp_kses_post( $font_family ); ?>"><?php _e( 'Add to wallet', 'woocommerce-events' ); ?></a>
											<br /><br />
										</span>
									<?php endif; ?> 									

									<!-- TICKET NUMBER -->
									#<?php echo esc_attr( $ticket['TicketNumberTicketOutput'] ); ?>																							
								</td>
								<td valign="top" class="wide">

									<!-- TICKET DETAILS -->
									<table cellpadding="2" cellspacing="0" width="100%" style=" border-collapse:collapse; font-family: <?php echo wp_kses_post( $font_family ); ?>">
										
									<!-- TITLE AND DATE -->
										<tr>
											<td valign="top" align="left" colspan="2">
											<h2 style="padding:0; margin:0"><?php echo esc_attr( $ticket['name'] ); ?></h2>
											<!-- EVENT DATE / TIME -->
											<?php if ( 'off' !== $ticket['WooCommerceEventsTicketDisplayDateTime'] ) : ?>	
												<?php if ( 'bookings' !== $ticket['WooCommerceEventsType'] ) : ?>
													
														<?php
														if ( ! empty( $ticket['WooCommerceEventsDate'] ) ) :
															echo esc_attr( $ticket['WooCommerceEventsDate'] );
															if ( ! empty( $ticket['WooCommerceEventsEndDate'] ) ) :
																echo ' - ' . esc_attr( $ticket['WooCommerceEventsEndDate'] );
															endif;
														endif;
														?>
														
														<?php if ( 'select' !== $ticket['WooCommerceEventsType'] || 'on' == esc_attr( $ticket['WooCommerceEventsSelectGlobalTime'] ) ) : ?>
															<?php echo ' '; ?>
															<?php echo esc_attr( $ticket['WooCommerceEventsHour'] ); ?>:<?php echo esc_attr( $ticket['WooCommerceEventsMinutes'] ); ?><?php echo ( ! empty( esc_attr( $ticket['WooCommerceEventsPeriod'] ) ) ) ? esc_attr( $ticket['WooCommerceEventsPeriod'] ) : ''; ?>
															<?php echo ( ! empty( $ticket['WooCommerceEventsTimeZone'] ) ) ? ' ' . esc_attr( $ticket['WooCommerceEventsTimeZone'] ) : ''; ?>
															<?php if ( '00' !== $ticket['WooCommerceEventsHourEnd'] ) : ?>
																<?php echo ' - '; ?>
																<?php echo esc_attr( $ticket['WooCommerceEventsHourEnd'] ); ?>:<?php echo esc_attr( $ticket['WooCommerceEventsMinutesEnd'] ); ?><?php echo ( ! empty( esc_attr( esc_attr( $ticket['WooCommerceEventsEndPeriod'] ) ) ) ) ? esc_attr( $ticket['WooCommerceEventsEndPeriod'] ) : ''; ?>
																<?php echo ( ! empty( $ticket['WooCommerceEventsTimeZone'] ) ) ? ' ' . esc_attr( $ticket['WooCommerceEventsTimeZone'] ) : ''; ?>
															<?php endif; ?>
														<?php endif; ?>		
													
												<?php endif; ?> 
											<?php endif; ?> 	

											<!-- ADD TO CALENDAR BUTTON -->
											<?php if ( 'off' != $ticket['WooCommerceEventsTicketAddCalendar'] ) : ?>
												<span class="no-print">
													<br /><a href="<?php echo esc_attr( site_url() ); ?>/wp-admin/admin-ajax.php?action=fooevents_ics&event=<?php echo esc_attr( $ticket['WooCommerceEventsProductID'] ); ?>&ticket=<?php echo esc_attr( $ticket['ID'] ); ?><?php echo ! empty( esc_attr( $ticket['WooCommerceEventsAttendeeEmail'] ) ) ? '&email=' . urlencode( $ticket['WooCommerceEventsAttendeeEmail'] ) : ''; ?>&ticket=<?php echo esc_attr( $ticket['ID'] ); ?>"><?php esc_attr_e( 'Add to calendar', 'woocommerce-events' ); ?></a><br /><br />
												</span>
											<?php endif; ?> 

											</td>
										</tr>

									<!-- PRICE -->
										<?php if ( 'off' != $ticket['WooCommerceEventsTicketDisplayPrice'] ) : ?>
											<tr>
												<td valign="top">
												<?php esc_attr_e( 'Price:', 'woocommerce-events' ); ?>
												</td>
												<td valign="top">								
													<?php
													if ( ! empty( $ticket['WooCommerceEventsPrice'] ) ) {
														echo wp_kses_post( $ticket['WooCommerceEventsPrice'] );
													} elseif ( ! empty( $ticket['price'] ) ) {
														echo wp_kses_post( $ticket['price'] );}
													?>
												</td>
											</tr>	
										<?php endif; ?>

									<!-- VARIATIONS -->
										<?php if ( ! empty( $ticket['WooCommerceEventsVariationsFromID'] ) ) : ?>
											<?php foreach ( $ticket['WooCommerceEventsVariationsFromID'] as $variationName => $variationValue ) : ?><?php // phpcs:ignore -- Variable name is not in valid snake_case format ?>
												<tr>
													<td valign="top">
														<?php echo esc_attr( $variationName ); ?>:<?php // phpcs:ignore -- Variable name is not in valid snake_case format ?>
													</td>
													<td valign="top">
														<?php echo esc_attr( $variationValue ); ?><br /><?php // phpcs:ignore -- Variable name is not in valid snake_case format ?>
													</td>
												</tr>												
											<?php endforeach; ?>        
										<?php endif; ?>	

									<!-- MULTI-DAY DETAILS -->
										<?php if ( 'on' === $ticket['WooCommerceEventsTicketDisplayMultiDay'] && ( 'select' === $ticket['WooCommerceEventsType'] || 'sequential' === $ticket['WooCommerceEventsType'] ) ) : ?>
											<?php $x = 1; ?>
											<?php $y = 0; ?>    
											<?php foreach ( $ticket['WooCommerceEventsSelectDate'] as $date ) : ?>
												<tr>
													<td valign="top">
													<label>
														<?php
														// translators: %1$s is the day term, %2$d is the day number.
														printf( esc_html__( '%1$s %2$d: ', 'woocommerce-events' ), esc_html( $ticket['dayTerm'] ), esc_html( $x ) );
														?>
														</label>
													</td>
													<td valign="top">
														<?php echo esc_attr( $date ); ?><br />
														<?php if ( 'select' !== $ticket['WooCommerceEventsType'] || 'on' == esc_attr( $ticket['WooCommerceEventsSelectGlobalTime'] ) ) : ?>
															<?php echo ' '; ?>
															<?php echo esc_attr( $ticket['WooCommerceEventsHour'] ); ?>:<?php echo esc_attr( $ticket['WooCommerceEventsMinutes'] ); ?><?php echo ( ! empty( esc_attr( $ticket['WooCommerceEventsPeriod'] ) ) ) ? esc_attr( $ticket['WooCommerceEventsPeriod'] ) : ''; ?>
															<?php echo ( ! empty( $ticket['WooCommerceEventsTimeZone'] ) ) ? ' ' . esc_attr( $ticket['WooCommerceEventsTimeZone'] ) : ''; ?>
															<?php if ( '00' !== $ticket['WooCommerceEventsHourEnd'] ) : ?>
																<?php echo ' - '; ?>
																<?php echo esc_attr( $ticket['WooCommerceEventsHourEnd'] ); ?>:<?php echo esc_attr( $ticket['WooCommerceEventsMinutesEnd'] ); ?><?php echo ( ! empty( esc_attr( esc_attr( $ticket['WooCommerceEventsEndPeriod'] ) ) ) ) ? esc_attr( $ticket['WooCommerceEventsEndPeriod'] ) : ''; ?>
																<?php echo ( ! empty( $ticket['WooCommerceEventsTimeZone'] ) ) ? ' ' . esc_attr( $ticket['WooCommerceEventsTimeZone'] ) : ''; ?>
															<?php endif; ?>
														<?php else : ?>		 
															<?php if ( ! empty( $ticket['WooCommerceEventsSelectDateHour'][ $y ] ) && ! empty( $ticket['WooCommerceEventsSelectDateMinutes'][ $y ] ) ) : ?>
																<?php echo esc_attr( $ticket['WooCommerceEventsSelectDateHour'][ $y ] ) . ':' . esc_attr( $ticket['WooCommerceEventsSelectDateMinutes'][ $y ] ); ?><?php echo( isset( $ticket['WooCommerceEventsSelectDatePeriod'][ $y ] ) ) ? ' ' . esc_attr( $ticket['WooCommerceEventsSelectDatePeriod'][ $y ] ) : ''; ?>
															<?php endif; ?>
															<?php if ( ! empty( $ticket['WooCommerceEventsSelectDateHourEnd'][ $y ] ) && ! empty( $ticket['WooCommerceEventsSelectDateMinutesEnd'][ $y ] ) ) : ?>
																<?php echo ' - ' . esc_attr( $ticket['WooCommerceEventsSelectDateHourEnd'][ $y ] ) . ':' . esc_attr( $ticket['WooCommerceEventsSelectDateMinutesEnd'][ $y ] ); ?><?php echo( isset( $ticket['WooCommerceEventsSelectDatePeriodEnd'][ $y ] ) ) ? ' ' . esc_attr( $ticket['WooCommerceEventsSelectDatePeriodEnd'][ $y ] ) : ''; ?>
															<?php endif; ?>
															<?php echo ( ! empty( $ticket['WooCommerceEventsTimeZone'] ) ) ? ' ' . esc_attr( $ticket['WooCommerceEventsTimeZone'] ) : ''; ?>
														<?php endif; ?>	
													</td>
												</tr>												
												<?php ++$x; ?>
												<?php ++$y; ?>
											<?php endforeach; ?>
										<?php endif; ?>

									<!-- BOOKING DETAILS -->                                      
										<?php if ( isset( $ticket['WooCommerceEventsBookingSlot'] ) || isset( $ticket['WooCommerceEventsBookingDate'] ) ) : ?>					
											<?php if ( 'off' != $ticket['WooCommerceEventsTicketDisplayBookings'] ) : ?>
												<tr>
													<td valign="top">
														<label><?php echo esc_attr( $ticket['WooCommerceEventsBookingsSlotTerm'] ); ?>:</label>
													</td>
													<td valign="top">
														<?php echo esc_attr( $ticket['WooCommerceEventsBookingSlot'] ); ?>
													</td>
												</tr>
												<tr>
													<td valign="top">
														<label><?php echo esc_attr( $ticket['WooCommerceEventsBookingsDateTerm'] ); ?>:</label> 
													</td>
													<td valign="top">
														<?php echo esc_attr( $ticket['WooCommerceEventsBookingDate'] ); ?>
													</td>
												</tr>
											<?php endif; ?> 
										<?php endif; ?> 	

									<!-- SEATING -->                                      
										<?php if ( ! empty( $ticket['fooevents_seating_options_array'] ) ) : ?>
											<tr>
												<td valign="top">
													<label><?php echo esc_attr( $ticket['fooevents_seating_options_array']['row_name_label'] ); ?>:</label>
												</td>
												<td valign="top">
													<?php echo esc_attr( $ticket['fooevents_seating_options_array']['row_name'] ); ?>
												</td>
											</tr>
											<tr>
												<td valign="top">
													<label><?php echo esc_attr( $ticket['fooevents_seating_options_array']['seat_number_label'] ); ?>:</label>
												</td>
												<td valign="top">
													<?php echo esc_attr( $ticket['fooevents_seating_options_array']['seat_number'] ); ?>
												</td>
											</tr>
										<?php endif; ?>                                     
									
									<?php if ( 'off' !== $ticket['WooCommerceEventsTicketPurchaserDetails'] ) : ?>

									<!-- ATTENDEE FIELDS -->                                  
										<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeName'] ) ) : ?>
											<tr>
												<td valign="top">
													<label><?php esc_attr_e( 'Ticket Holder:', 'woocommerce-events' ); ?></label>  
												</td>
												<td valign="top">
													<?php echo esc_attr( $ticket['WooCommerceEventsAttendeeName'] ); ?> <?php echo esc_attr( $ticket['WooCommerceEventsAttendeeLastName'] ); ?>
												</td>
											</tr>
										<?php endif; ?>

										<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeTelephone'] ) ) : ?>
											<tr>
												<td valign="top">
													<label><?php esc_attr_e( 'Telephone Number:', 'woocommerce-events' ); ?></label>  
												</td>
												<td valign="top">
													<?php echo esc_attr( $ticket['WooCommerceEventsAttendeeTelephone'] ); ?>
												</td>
											</tr>
										<?php endif; ?>

										<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeCompany'] ) ) : ?>
											<tr>
												<td valign="top">
													<label><?php esc_attr_e( 'Company:', 'woocommerce-events' ); ?></label>     
												</td>
												<td valign="top">
													<?php echo esc_attr( $ticket['WooCommerceEventsAttendeeCompany'] ); ?>    
												</td>
											</tr>
										<?php endif; ?>

										<?php if ( ! empty( $ticket['WooCommerceEventsAttendeeDesignation'] ) ) : ?>
											<tr>
												<td valign="top">
													<label><?php esc_attr_e( 'Designation:', 'woocommerce-events' ); ?></label>  
												</td>
												<td valign="top">
													<?php echo esc_attr( $ticket['WooCommerceEventsAttendeeDesignation'] ); ?>
												</td>
											</tr>
										<?php endif; ?>

									<?php endif; ?>

									<!-- CUSTOM ATTENDEE FIELDS -->
										<?php if ( ! empty( $ticket['fooevents_custom_attendee_fields_options_array'] ) && ( isset( $ticket['WooCommerceEventsIncludeCustomAttendeeDetails'] ) && 'off' != $ticket['WooCommerceEventsIncludeCustomAttendeeDetails'] ) ) : ?>
											<?php foreach ( $ticket['fooevents_custom_attendee_fields_options_array'] as $custom_attendee_fields ) : ?>
												<tr>
													<td valign="top">
														<label><?php echo esc_attr( $custom_attendee_fields['label'] ); ?>:</label>
													</td>
													<td valign="top">
														<?php echo esc_attr( $custom_attendee_fields['value'] ); ?>
													</td>
												</tr>
											<?php endforeach; ?>
										<?php endif; ?> 

									<!-- ZOOM INFORMATION -->
										<?php if ( ! empty( $ticket['WooCommerceEventsTicketDisplayZoom'] ) && 'off' !== $ticket['WooCommerceEventsTicketDisplayZoom'] && ! empty( $ticket['WooCommerceEventsZoomText'] ) ) : ?>
											<tr>
												<td valign="top">
													<label><?php esc_attr_e( 'Zoom Details', 'woocommerce-events' ); ?>:</label>
												</td>
												<td valign="top">
													<?php echo wp_kses_post( $ticket['WooCommerceEventsZoomText'] ); ?>
												</td>
											</tr>
										<?php endif; ?>
									
									</table>
								</td>
							</tr>	
						</table>
					</td>
				</tr>				
			</table>
		</td>
	</tr>
</table>

