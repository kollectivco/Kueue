<?php
/**
 * Express Check-in day option
 *
 * @link https://www.fooevents.com
 * @package fooevents-multiday-events
 */

?>
<select name="fooevents-express-check-in-day" id="fooevents-express-check-in-day">
	<?php for ( $x = 1; $x <= 30; $x++ ) : ?>
	<option value="<?php echo esc_attr( $x ); ?>"><?php echo esc_attr( $x ); ?></option>
	<?php endfor; ?>
</select>
<?php esc_attr_e( 'Check-in Day', 'fooevents-multiday-events' ); ?>
