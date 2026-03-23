<?php
/**
 * HTML template for the global settings FooEvents POS color options
 *
 * @link https://www.fooevents.com
 * @since 1.0.2
 * @package fooevents-pos
 */

defined( 'ABSPATH' ) || exit;

?>
<tr valign="top">
	<th scope="row"><?php echo esc_html( $fooeventspos_phrases['checkbox_use_settings_in_fooevents_pos'] ); ?></th>
	<td>
		<input type="checkbox" name="globalWooCommerceEventsPOSUseAppSettings" id="globalWooCommerceEventsPOSUseAppSettings" value="yes" <?php echo ( 'yes' === $woocommerce_events_pos_use_app_color ) ? 'checked' : ''; ?>>
		<img class="help_tip fooevents-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['checkbox_use_settings_in_fooevents_pos_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</td>
</tr> 
