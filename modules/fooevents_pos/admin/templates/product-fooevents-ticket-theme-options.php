<?php
/**
 * HTML template for the FooEvents ticket theme options
 *
 * @link https://www.fooevents.com
 * @since 1.9.0
 * @package fooevents-pos
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="options_group">
	<p class="form-field">
		<label><?php echo esc_html( $fooeventspos_phrases['title_fooevents_ticket_theme'] ); ?></label>
		<select name="WooCommerceEventsPOSTicketTheme" id="WooCommerceEventsPOSTicketTheme">
			<?php foreach ( $themes as $theme_group => $theme_group_themes ) : ?>
				<?php if ( ! empty( $theme_group_themes ) ) : ?>
					<optgroup label="<?php echo esc_attr( $fooeventspos_phrases[ 'fooevents_ticket_theme_optgroup_' . $theme_group ] ); ?>">
						<?php foreach ( $theme_group_themes as $theme_details ) : ?>
							<option value="<?php echo esc_attr( $theme_details['path'] ); ?>" <?php echo ( $woocommerce_events_pos_ticket_theme === $theme_details['path'] ) ? 'selected' : ''; ?>><?php echo esc_html( $theme_details['name'] ); ?></option>
						<?php endforeach; ?>
					</optgroup>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
		<img class="help_tip" data-tip="<?php echo esc_attr( $fooeventspos_phrases['title_fooevents_ticket_theme_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</p>
</div>
