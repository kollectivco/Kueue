<?php if ( ! empty( $custom_values ) || ! empty( $custom_values_legacy ) ) : ?>
	<h3><?php esc_attr_e( 'Custom Attendee Fields', 'fooevents-custom-attendee-fields' ); ?></h3>
<?php endif; ?>
<?php if ( ! empty( $custom_values ) ) : ?>
	<?php foreach ( $custom_values as $key => $field ) : ?>
		<?php $options = explode( '|', $field['field'][ $key . '_options' ] ); ?>
	<div class="ticket-details-row">
		<label><?php echo esc_attr( $field['field'][ $key . '_label' ] ); ?>:</label>
		<?php $x = 0; ?>
		<?php if ( 'radio' === $field['field'][ $key . '_type' ] ) : ?> 
			<?php $x = 0; ?>
			<?php
			foreach ( $options as $option ) :
				$x ++;
				?>
			<label for="<?php echo esc_attr( $key . '_' . $x ); ?>">
				<input type="radio" id="<?php echo esc_attr( $key . '_' . $x ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" value="<?php echo esc_attr( $option ); ?>" <?php echo ( $field['value'] === $option ) ? 'CHECKED' : ''; ?> >
				<?php echo esc_attr( $option ); ?>
			</label>
			<?php endforeach; ?>
		<?php elseif ( 'select' === $field['field'][ $key . '_type' ] ) : ?> 
			<select name="<?php echo esc_attr( $field['name'] ); ?>">
				<?php foreach ( $options as $option ) : ?>
				<option value="<?php echo esc_attr( $option ); ?>" <?php echo ( esc_attr( $field['value'] ) === esc_attr( $option ) ) ? 'SELECTED' : ''; ?>><?php echo esc_attr( $option ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php elseif ( 'checkbox' === $field['field'][ $key . '_type' ] ) : ?> 
		<input type="hidden" id="<?php echo esc_attr( $key . '_' . $x ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" value="0">
		<input type="checkbox" id="<?php echo esc_attr( $key . '_' . $x ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" value="1" <?php echo ( 1 === (int) $field['value'] ) ? 'CHECKED' : ''; ?> >
		<?php else : ?> 
			<input type="text" name="<?php echo esc_attr( $field['name'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" />
		<?php endif; ?>
	</div>
	<?php endforeach ?>
<?php endif; ?>
<!-- LEGACY: 20201125 -->   
<?php if ( ! empty( $custom_values_legacy ) ) : ?>
	<?php foreach ( $custom_values_legacy as $key => $field ) : ?>
	<div class="ticket-details-row">
		<label><?php echo esc_attr( $key ); ?>:</label>
		<input type="text" name="" value="<?php echo esc_attr( $field ); ?>" readonly />
	</div>       
	<?php endforeach; ?>
<?php endif; ?>
<!-- ENDLEGACY: 20201125 -->   
<div class="clear"></div>
