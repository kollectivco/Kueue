<?php
/**
 * Add ticket template.
 *
 * @link https://www.fooevents.com
 * @package fooevents-custom-attendee-fields
 */

?>
<?php if ( ! empty( $custom_values ) ) : ?>
	<h3><?php esc_attr_e( 'Custom Attendee Fields', 'fooevents-custom-attendee-fields' ); ?></h3>
	<?php foreach ( $custom_values as $key => $field ) : ?>
		<?php $options = explode( '|', $field['field'][ $key . '_options' ] ); ?>
	<div class="ticket-details-row">
		<label><?php echo esc_attr( $field['field'][ $key . '_label' ] ); ?>:</label>
		<?php if ( 'radio' === $field['field'][ $key . '_type' ] ) : ?> 
			<?php
			foreach ( $options as $option ) :
				$x ++;
				?>
			<label for="<?php echo esc_attr( $key . '_' . $x ); ?>">
				<input type="radio" id="<?php echo esc_attr( $key . '_' . $x ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" value="<?php echo esc_attr( $option ); ?>" >
				<?php echo esc_attr( $option ); ?>
			</label>
			<?php endforeach; ?>
		<?php elseif ( 'select' === $field['field'][ $key . '_type' ] ) : ?> 
			<select name="<?php echo esc_attr( $field['name'] ); ?>">
				<?php foreach ( $options as $option ) : ?>
				<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_attr( $option ); ?></option>
				<?php endforeach; ?>
			</select>
		<?php elseif ( 'checkbox' === $field['field'][ $key . '_type' ] ) : ?> 
		<input type="hidden" id="<?php echo esc_attr( $key . '_' . $x ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" value="0">
		<input type="checkbox" id="<?php echo esc_attr( $key . '_' . $x ); ?>" name="<?php echo esc_attr( $field['name'] ); ?>" value="1">
		<?php else : ?> 
			<input type="text" name="<?php echo esc_attr( $field['name'] ); ?>" value="" />
		<?php endif; ?>
	</div>
	<?php endforeach ?>
<?php endif; ?>
<div class="clear"></div>
