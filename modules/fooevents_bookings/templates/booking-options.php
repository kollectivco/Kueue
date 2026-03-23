<?php
/**
 * Booking options template
 *
 * @link https://www.fooevents.com
 * @package fooevents-bookings
 */

?>
<div id="fooevents_bookings_options" class="panel woocommerce_options_panel" style="display: block;">
	<p><h2><b><?php esc_attr_e( 'Bookings', 'fooevents-bookings' ); ?></b></h2></p>
	<div class="fooevents_bookings_wrap">
		<div id="fooevents_bookings_message"></div>
		<div class="options_group">
			<table id="fooevents_bookings_options_table" cellpadding="0" cellspacing="0" width="100%">
				<thead>
					<tr> 
						<td width="60%" class="fooevents_bookings_top"><a href="#" id="fooevents_bookings_new_field" class='button button-primary'><?php echo sprintf( esc_attr__( '+ New %s', 'fooevents-bookings' ), $slot_label ); ?></a></td>
						<td width="40%" class="fooevents_bookings_top fooevents_bookings_view_options"><em>(<a href="#" class="fooevents_bookings_expand_all"><?php echo esc_attr__( 'Expand', 'fooevents-bookings' ); ?></a> / <a href="#" class="fooevents_bookings_close_all"><?php echo esc_attr__( 'Close', 'fooevents-bookings' ); ?></a>)</em></td>
					</tr>
				</thead>
				<tbody>
					<?php
					$x = 0;
					$y = 1;
					?>
					<?php foreach ( $fooevents_bookings_options as $option_key => $option ) : ?>
						<?php $option_ids = array_keys( $option ); ?>
						<?php $option_values = array_values( $option ); ?>
						<?php
						$num_option_ids    = count( $option_ids );
						$num_option_values = count( $option_values );
						?>
						<?php if ( $num_option_ids === $num_option_values ) : ?>
					<tr id="<?php echo esc_attr( $option_key ); ?>" class="fooevents_bookings_option">
						<td colspan="2" id="<?php echo esc_attr( $option_key ); ?>_row">
							<div class="fooevents_booking_col fooevents_booking_col_1 fooevents_booking_handle_column" id="<?php echo esc_attr( $option_key ); ?>_handle_column">
								<span class="indent"><span class="dashicons dashicons-menu fooevents_bookings_handle"></span></span>
							</div>
							<div class="fooevents_booking_col fooevents_booking_col_2 fooevents_booking_label_column" id="<?php echo esc_attr( $option_key ); ?>_label_column">
								<input type="text" id="<?php echo esc_attr( $option_key ); ?>_label" data-bookings="label" class="fooevents_bookings_label" value="<?php echo esc_attr( $option['label'] ); ?>" autocomplete="off" maxlength="150"/>
							</div>
							<div class="fooevents_booking_col fooevents_booking_col_2 fooevents_booking_col_time fooevents_booking_add_time_column" id="<?php echo esc_attr( $option_key ); ?>_add_time_column">
								<?php if ( isset( $option['add_time'] ) && 'enabled' === $option['add_time'] ) : ?> 
								<select data-bookings="hour" id="<?php echo esc_attr( $option_key ); ?>_hour" class="fooevents_bookings_time_field fooevents_bookings_time_hour">
									<?php for ( $x = 0; $x <= 23; $x++ ) : ?>
										<?php $x = sprintf( '%02d', $x ); ?>
									<option value="<?php echo esc_attr( $x ); ?>" <?php echo( isset( $option['hour'] ) && $option['hour'] === $x ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $x ); ?></option>
									<?php endfor; ?>
								</select>
								<select data-bookings="minute" id="<?php echo esc_attr( $option_key ); ?>_minute" class="fooevents_bookings_time_field fooevents_bookings_time_minute">
									<?php for ( $x = 0; $x <= 59; $x++ ) : ?>
										<?php $x = sprintf( '%02d', $x ); ?>
									<option value="<?php echo esc_attr( $x ); ?>" <?php echo( isset( $option['minute'] ) && $x === $option['minute'] ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $x ); ?></option>
									<?php endfor; ?>
								</select>
								<select data-bookings="period" id="<?php echo esc_attr( $option_key ); ?>_period" class="fooevents_bookings_time_field fooevents_bookings_time_period">
									<option value="">-</option>
									<option value="a.m." <?php echo( isset( $option['period'] ) && 'a.m.' === $option['period'] ) ? 'SELECTED' : ''; ?>>a.m.</option>
									<option value="p.m." <?php echo( isset( $option['period'] ) && 'p.m.' === $option['period'] ) ? 'SELECTED' : ''; ?>>p.m.</option>
								</select>
								<?php endif; ?>
							</div>
							<div class="fooevents_booking_col fooevents_booking_col_3 fooevents_booking_options_column" id="<?php echo esc_attr( $option_key ); ?>_options_column">
								<a href="javascript:void(0);" id="<?php echo esc_attr( $option_key ); ?>_options" class="dashicons-before dashicons-admin-settings fooevents_bookings_settings"></a>
								<a href="javascript:void(0);" id="<?php echo esc_attr( $option_key ); ?>_toggle" class="dashicons-before dashicons-arrow-up fooevents_bookings_toggle"></a>
								<div class="fooevents_booking_col fooevents_booking_col_6 booking_options">
									<label for="<?php echo esc_attr( $option_key ); ?>_zoom_id" class="fooevents_zoom_id_label"><input type="checkbox" id="<?php echo esc_attr( $option_key ); ?>_zoom_id" data-bookings="zoom_id" class="fooevents_bookings_zoom_id" value="enabled" <?php echo( isset( $option['zoom_id'] ) && 'enabled' === $option['zoom_id'] ) ? 'CHECKED' : ''; ?>/> Zoom</label> <label for="<?php echo esc_attr( $option_key ); ?>_add_time" class="fooevents_add_time_label"><input type="checkbox" id="<?php echo esc_attr( $option_key ); ?>_add_time" data-bookings="add_time"  class="fooevents_bookings_add_time" value="enabled" <?php echo( isset( $option['add_time'] ) && 'enabled' === $option['add_time'] ) ? 'CHECKED' : ''; ?> <?php echo( isset( $option['zoom_id'] ) && 'enabled' === $option['zoom_id'] ) ? 'DISABLED' : ''; ?>/> <?php echo esc_attr__( 'Time', 'fooevents-bookings' ); ?></label> <button class="fooevents-add-dates button" id="<?php echo esc_attr( $option_key ); ?>_fooevents-add-dates"><?php echo esc_attr__( 'Add dates', 'fooevents-bookings' ); ?></button><a href="#" id="<?php echo esc_attr( $option_key ); ?>_copy" class="fooevents_bookings_copy dashicons-before dashicons-admin-page"></a><a href="javascript:void(0);" id="<?php echo esc_attr( $option_key ); ?>_remove" name="remove" class="fooevents_bookings_remove dashicons-before dashicons-trash"></a>
								</div>
							</div>
							<table class="fooevents_bookings_add_date_table" cellspacing="0" cellpadding="0" id="<?php echo esc_attr( $option_key ); ?>_holder" width="100%">
								<tbody>
								<?php if ( ! empty( $option['add_date'] ) ) : ?>
									<?php foreach ( $option['add_date'] as $row => $add_date ) : ?>
									<tr>
										<td width="10%" class="fooevents_bookings_handle_container"><span class="dashicons dashicons-menu fooevents_bookings_handle"></span></td>
										<td width="90%"><input type="text" data-bookings="<?php echo esc_attr( $row ); ?>_add_date" class="fooevents_bookings_date WooCommerceEventsBookingsSelectDate" value="<?php echo esc_attr( $add_date['date'] ); ?>" /> 
											<span id="<?php echo esc_attr( $row ); ?>_zoom_id_container" class="fooevents_bookings_zoom_id_container" style="display:<?php echo ( isset( $option['zoom_id'] ) && 'enabled' === $option['zoom_id'] ) ? 'inline' : 'none'; ?>;" data-zoom-id-enabled="<?php echo ( isset( $option['zoom_id'] ) && 'enabled' === $option['zoom_id'] ) ? '1' : '0'; ?>" data-zoom-id="<?php echo ( isset( $add_date['zoom_id'] ) ) ? esc_attr( $add_date['zoom_id'] ) : ''; ?>"></span> 
											<input type="number" min="0" data-bookings="<?php echo esc_attr( $row ); ?>_stock" value="<?php echo esc_attr( $add_date['stock'] ); ?>" class="fooevents_bookings_date_stock" autocomplete="off" maxlength="10" placeholder="<?php echo esc_attr__( 'Unlimited stock', 'fooevents-bookings' ); ?>" /> 
										<span> <a href="javascript:void(0);" id="<?php echo esc_attr( $row ); ?>_remove" class="fooevents_bookings_date_remove">[X]</a></span></td>	
									</tr>
									<?php endforeach; ?>
								<?php endif; ?>
								</tbody>
							</table>    
						</td>
					</tr>
							<?php
							$x++;
							$y++;
							?>
					<?php endif; ?>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php if ( 0 === $x ) : ?>
				<div class="fooevents_bookings_none fooevents_notfound"><?php echo esc_attr__( 'No slots found. Create your first one.', 'fooevents-bookings' ); ?></div>
			<?php endif; ?>
		</div>
	</div>
	<div class="clearfix"></div>
	<div id="fooevents_bookings_info">
		<?php if ( 'auto-draft' !== $post_status ) : ?>
			<a href="javascript:void(0);" id="fooevents_bookings_save" class='button button-primary'><?php echo esc_attr__( 'Save Changes', 'fooevents-bookings' ); ?></a>
		<?php endif; ?>
		<div class="fooevents_bookings_view_options">
			<a href="javascript:void(0);" class="fooevents_bookings_expand_all">Expand</a> / <a href="javascript:void(0);" class="fooevents_bookings_close_all">Close</a>
		</div>   
		<div class="clearfix"></div>     
	</div>
	<input type="hidden" id="fooevents_bookings_options_serialized" name="fooevents_bookings_options_serialized" value='<?php echo esc_attr( $fooevents_bookings_options_serialized ); ?>' autocomplete="off" />
	<input type="hidden" id="fooevents_bookings_post_id" name="fooevents_bookings_post_id" value="<?php echo esc_attr( $post->ID ); ?>" />
	<?php wp_nonce_field( 'fooevents_bookings_options', 'fooevents_bookings_options_nonce' ); ?>
</div>
<div class="fooevents-bookings-loader"></div>
