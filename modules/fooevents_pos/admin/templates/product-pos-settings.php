<?php
/**
 * HTML template for event product POS settings
 *
 * @link https://www.fooevents.com
 * @since 1.0.2
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin/templates
 */

defined( 'ABSPATH' ) || exit;

?>
<div id="fooeventspos_pos_settings" class="panel woocommerce_options_panel">
	<h2><?php echo esc_html( $fooeventspos_phrases['title_pos_settings'] ); ?></h2>
	<div class="options_group">
		<p class="form-field">
			<label><?php echo esc_html( $fooeventspos_phrases['checkbox_show_product_in_pos'] ); ?></label>
			<input type="checkbox" name="fooeventspos_product_show_in_pos" value="yes" <?php echo ( empty( $fooeventspos_product_show_in_pos ) || 'yes' === $fooeventspos_product_show_in_pos ) ? 'checked' : ''; ?>>
			<img class="help_tip" data-tip="<?php echo esc_attr( $fooeventspos_phrases['checkbox_show_product_in_pos_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<div class="options_group show_if_variable">
		<p class="form-field">
			<label><?php echo esc_html( $fooeventspos_phrases['title_show_variations_in_pos'] ); ?></label>
			<?php echo ( sprintf( esc_html( $fooeventspos_phrases['description_show_hide_variations'] ), '<a href="javascript:jQuery( \'li.variations_tab a\' ).click();">', '</a>', '<a href="https://help.fooevents.com/docs/frequently-asked-questions/fooevents-pos/how-do-i-show-or-hide-certain-products-or-variations-in-the-pos" target="_blank">', '</a>' ) ); ?>
		</p>
	</div>
	<div class="options_group">
		<p class="form-field">
			<label><?php echo esc_html( $fooeventspos_phrases['checkbox_pin_product_in_pos'] ); ?></label>
			<input type="checkbox" name="fooeventspos_product_pin_in_pos" value="yes" <?php echo ( 'yes' === $fooeventspos_product_pin_in_pos ) ? 'checked' : ''; ?>>
			<img class="help_tip" data-tip="<?php echo esc_attr( $fooeventspos_phrases['checkbox_pin_product_in_pos_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		</p>
	</div>
	<?php
	foreach ( $pos_settings as $section => $options ) {
		if ( ! empty( $options ) ) {
			?>
				<h2><?php echo esc_html( $fooeventspos_phrases[ 'title_' . $section ] ); ?></h2>
			<?php
			foreach ( $options as $option ) {
				echo wp_kses(
					$option,
					array(
						'div'      => array( 'class' => array() ),
						'p'        => array( 'class' => array() ),
						'label'    => array(),
						'select'   => array(
							'name' => array(),
							'id'   => array(),
						),
						'optgroup' => array( 'label' => array() ),
						'option'   => array(
							'value'    => array(),
							'selected' => array(),
						),
						'input'    => array(
							'type'    => array(),
							'name'    => array(),
							'value'   => array(),
							'checked' => array(),
						),
						'img'      => array(
							'class'    => array(),
							'data-tip' => array(),
							'src'      => array(),
							'height'   => array(),
							'width'    => array(),
						),
					)
				);
			}
			?>
			<?php
		}
	}

	wp_nonce_field( '_fooeventspos_save_pos_settings_' . $post->ID, '_fooeventspos_pos_settings_nonce' );
	?>
</div>
