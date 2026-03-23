<?php
/**
 * HTML template for the license key setting for the FooEvents POS plugin
 *
 * @link https://www.fooevents.com
 * @since 1.1.0
 * @package fooevents-pos
 */

defined( 'ABSPATH' ) || exit;

?>

<tr>
	<th scope="row" valign="top"><?php echo esc_html( $fooeventspos_phrases['title_fooevents_pos_license_key'] ); ?></th>
	<td valign="top">
		<input type="password" name="globalWooCommerceEventsAPIKey" id="globalWooCommerceEventsAPIKey" value="<?php echo esc_attr( $global_woocommerce_events_api_key ); ?>">
		<input id="fooeventspos_save_license_key_button" type="button" value="<?php echo esc_attr( $fooeventspos_phrases['button_save'] ); ?>" class="button button-secondary">
		<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['title_fooevents_pos_license_key_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</td>
</tr>
