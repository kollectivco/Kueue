<?php
/**
 * Custom attendee options template.
 *
 * @link https://www.fooevents.com
 * @package fooevents-custom-attendee-fields
 */

?>
<div id="fooevents_custom_attendee_field_options" class="panel woocommerce_options_panel fooevents_options_panel" style="display: block;">
<h2><?php esc_attr_e( 'Custom Attendee Fields', 'woocommerce-events' ); ?></h2>
<?php if ( $is_checkout_block_enabled ) : ?>
	<div class="fooevents-settings-notice">
		<p>
			<?php esc_html_e( 'FooEvents does not currently support capturing custom attendee field details using the new WooCommerce Checkout block. To enable this functionality, please switch to the classic shortcode-based WooCommerce Checkout experience.', 'woocommerce_events' ); ?>
			<a href="<?php echo esc_url( 'https://help.fooevents.com/docs/frequently-asked-questions/installation-setup/does-fooevents-work-with-the-woocommerce-checkout-and-cart-blocks/' ); ?>">
			<?php esc_html_e( 'Learn more', 'woocommerce_events' ); ?> &rarr;
			</a>			
		</p>
	</div>
<?php endif; ?>
	<div class="fooevents_custom_attendee_fields_wrap">
		<div class="options_group">
			<table id="fooevents_custom_attendee_fields_options_table" cellpadding="0" cellspacing="0">
				<thead>
					<tr>
						<th></th>
						<th><?php esc_attr_e( 'Label', 'fooevents-custom-attendee-fields' ); ?></th>
						<th><?php esc_attr_e( 'Type', 'fooevents-custom-attendee-fields' ); ?></th>
						<th><?php esc_attr_e( 'Options', 'fooevents-custom-attendee-fields' ); ?></th>
						<th><?php esc_attr_e( 'Default', 'fooevents-custom-attendee-fields' ); ?></th>
						<th><?php esc_attr_e( 'Required', 'fooevents-custom-attendee-fields' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$x          = 0;
					$y          = 1;
					$has_fields = false;
					?>
					<?php foreach ( $fooevents_custom_attendee_fields_options as $option_key => $option ) : ?>
						<?php $option_ids = array_keys( $option ); ?>
						<?php $option_values = array_values( $option ); ?>
						<?php
						$num_option_ids    = count( $option_ids );
						$num_option_values = count( $option_values );
						?>
						<?php if ( $num_option_ids === $num_option_values ) : ?>
						<tr id="<?php echo esc_attr( $option_key ); ?>">
							<td>
								<span class="dashicons dashicons-menu"></span>
							</td>
							<td>
								<input type="text" id="<?php echo esc_attr( $option_key ); ?>_label" name="<?php echo esc_attr( $option_key ); ?>_label" class="fooevents_custom_attendee_fields_label" value="<?php echo esc_attr( $option[ $option_key . '_label' ] ); ?>" autocomplete="off" maxlength="150"/>
							</td>
							<td>
								<select id="<?php echo esc_attr( $option_key ); ?>_type" name="<?php echo esc_attr( $option_key ); ?>_type" class="fooevents_custom_attendee_fields_type" autocomplete="off">
									<option value="text" <?php echo ( 'text' === $option[ $option_key . '_type' ] ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Text', 'fooevents-custom-attendee-fields' ); ?></option>
									<option value="textarea" <?php echo ( 'textarea' === $option[ $option_key . '_type' ] ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Textarea', 'fooevents-custom-attendee-fields' ); ?></option>
									<option value="select" <?php echo ( 'select' === $option[ $option_key . '_type' ] ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Select', 'fooevents-custom-attendee-fields' ); ?></option>
									<option value="checkbox" <?php echo ( 'checkbox' === $option[ $option_key . '_type' ] ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Checkbox', 'fooevents-custom-attendee-fields' ); ?></option>
									<option value="radio" <?php echo ( 'radio' === $option[ $option_key . '_type' ] ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Radio', 'fooevents-custom-attendee-fields' ); ?></option>
									<option value="country" <?php echo ( 'country' === $option[ $option_key . '_type' ] ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Country', 'fooevents-custom-attendee-fields' ); ?></option>
									<option value="date" <?php echo ( 'date' === $option[ $option_key . '_type' ] ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Date', 'fooevents-custom-attendee-fields' ); ?></option>
									<option value="time" <?php echo ( 'time' === $option[ $option_key . '_type' ] ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Time', 'fooevents-custom-attendee-fields' ); ?></option>
									<option value="email" <?php echo ( 'email' === $option[ $option_key . '_type' ] ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Email', 'fooevents-custom-attendee-fields' ); ?></option>
									<option value="url" <?php echo ( 'url' === $option[ $option_key . '_type' ] ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'URL', 'fooevents-custom-attendee-fields' ); ?></option>
									<option value="numbers" <?php echo ( 'numbers' === $option[ $option_key . '_type' ] ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Numbers', 'fooevents-custom-attendee-fields' ); ?></option>
									<option value="alphabet" <?php echo ( 'alphabet' === $option[ $option_key . '_type' ] ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Alphabet', 'fooevents-custom-attendee-fields' ); ?></option>
									<option value="alphanumeric" <?php echo ( 'alphanumeric' === $option[ $option_key . '_type' ] ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Alphanumeric', 'fooevents-custom-attendee-fields' ); ?></option>
								</select>
							</td>
							<td>
								<input id="<?php echo esc_attr( $option_key ); ?>_options" name="<?php echo esc_attr( $option_key ); ?>_options" class="fooevents_custom_attendee_fields_options" type="text" value="<?php echo esc_attr( $option[ $option_key . '_options' ] ); ?>" <?php echo ( 'select' === $option[ $option_key . '_type' ] || 'radio' === $option[ $option_key . '_type' ] ) ? '' : 'disabled'; ?> autocomplete="off" />    
							</td>
							<td>
								<input id="<?php echo esc_attr( $option_key ); ?>_def" name="<?php echo esc_attr( $option_key ); ?>_def" class="fooevents_custom_attendee_fields_def" type="text" value="<?php echo ( ! empty( $option[ $option_key . '_def' ] ) ) ? esc_attr( $option[ $option_key . '_def' ] ) : ''; ?>" <?php echo ( 'select' === $option[ $option_key . '_type' ] || 'radio' === $option[ $option_key . '_type' ] ) ? '' : 'disabled'; ?> autocomplete="off" />    
							</td>
							<td>
								<select id="<?php echo esc_attr( $option_key ); ?>_req" name="<?php echo esc_attr( $option_key ); ?>_req" class="fooevents_custom_attendee_fields_req" autocomplete="off">
									<option value="true" <?php echo ( 'true' === $option[ $option_key . '_req' ] ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'Yes', 'fooevents-custom-attendee-fields' ); ?></option>
									<option value="false" <?php echo ( 'false' === $option[ $option_key . '_req' ] ) ? 'SELECTED' : ''; ?>><?php esc_attr_e( 'No', 'fooevents-custom-attendee-fields' ); ?></option>
								</select>
							</td>
							<td><a href="#" id="<?php echo esc_attr( $option_key ); ?>_remove" name="<?php echo esc_attr( $option_key ); ?>_remove" class="fooevents_custom_attendee_fields_remove" class="fooevents_custom_attendee_fields_remove">[X]</a></td>
						</tr>
							<?php
							$has_fields = true;
							++$x;
							++$y;
							?>
					<?php endif; ?>
					<?php endforeach; ?>				
				</tbody>
			</table>    
		</div>
	</div>
	<div id="fooevents_custom_attendee_fields_info">
		<?php if ( false === $has_fields ) { ?>
			<p class="fooevents_notfound"><?php esc_attr_e( 'No Custom Attendee Fields found.', 'fooevents-custom-attendee-fields' ); ?></p>
		<?php } ?>	
		<p><a href="#" id="fooevents_custom_attendee_fields_new_field" class='button button-primary'>+ <?php esc_attr_e( 'New field', 'fooevents-custom-attendee-fields' ); ?></a></p>
		<p class="description"><?php echo esc_attr_e( "When using the 'Select' or 'Radio' options, seperate the options using a pipe symbol. Example: Small|Medium|Large.", 'fooevents-custom-attendee-fields' ); ?></p>
	</div>
	<?php wp_nonce_field( 'fooevents_custom_attendee_fields_options', 'fooevents_custom_attendee_fields_nonce' ); ?>
	<input type="hidden" id="fooevents_custom_attendee_fields_options_serialized" name="fooevents_custom_attendee_fields_options_serialized" value="<?php esc_attr_e( $fooevents_custom_attendee_fields_options_serialized ); ?>" autocomplete="off" />
</div>
