<?php
/**
 * HTML template for displaying the appearance tab settings
 *
 * @link https://www.fooevents.com
 * @since 1.1.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin/templates
 */

defined( 'ABSPATH' ) || exit;

?>

<?php settings_fields( 'fooeventspos-settings-general' ); ?>
<?php do_settings_sections( 'fooeventspos-settings-general' ); ?>
<?php if ( array_key_exists( 'fooeventspos_use_checkins_settings', $fooeventspos_appearance_settings ) ) : ?>
	<tr valign="top">
		<th scope="row"><?php echo esc_html( $fooeventspos_phrases['checkbox_fooevents_pos_use_checkins_app_settings'] ); ?></th>
		<td>
			<input type="checkbox" name="globalFooEventsPOSUseCheckinsSettings" id="globalFooEventsPOSUseCheckinsSettings" value="yes" <?php echo ( 'yes' === $fooeventspos_appearance_settings['fooeventspos_use_checkins_settings'] ) ? 'checked' : ''; ?> />
			<img class="help_tip fooevents-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['checkbox_fooevents_pos_use_checkins_app_settings_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=fooevents-settings&tab=checkins_app' ) ); ?>" target="_blank"><?php echo esc_html( $fooeventspos_phrases['text_review_checkins_app_settings'] ); ?></a>
		</td>
	</tr>
<?php endif; ?>
<tr class="fooeventspos-hideable-row" style="display:<?php echo ( 'yes' === $fooeventspos_appearance_settings['fooeventspos_use_checkins_settings'] ) ? 'none' : 'table-row'; ?>;">
	<th scope="row"><?php echo esc_html( $fooeventspos_phrases['title_fooevents_pos_app_title'] ); ?></th>
	<td>
		<input id="globalFooEventsPOSAppTitle" class="text" type="text" size="40" name="globalFooEventsPOSAppTitle" value="<?php echo esc_attr( $fooeventspos_appearance_settings['fooeventspos_app_title'] ); ?>" />
		<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['title_fooevents_pos_app_title_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		<p><?php printf( esc_html( $fooeventspos_phrases['description_fooevents_pos_app_title'] ), esc_html( $fooeventspos_phrases['title_fooevents_pos'] ) ); ?></p>
	</td>
</tr>
<tr class="fooeventspos-hideable-row" style="display:<?php echo ( 'yes' === $fooeventspos_appearance_settings['fooeventspos_use_checkins_settings'] ) ? 'none' : 'table-row'; ?>;">
	<th scope="row"><?php echo esc_html( $fooeventspos_phrases['title_fooevents_pos_app_logo'] ); ?></th>
	<td>
	<div class="fooeventspos-image-container">
		<?php
		if ( '' !== $fooeventspos_appearance_settings['fooeventspos_app_logo_url'] && false !== $fooeventspos_appearance_settings['fooeventspos_app_logo_url'] ) :
			?>
			<img src="<?php echo esc_attr( $fooeventspos_appearance_settings['fooeventspos_app_logo_url'] ); ?>?<?php echo esc_attr( time() ); ?>" class="fooeventspos-uploaded-image" />
			<?php
		endif;
		?>
		</div>
		<input id="globalFooEventsPOSAppLogo" type="hidden" class="fooeventspos-hidden-image-input" name="globalFooEventsPOSAppLogo" value="<?php echo esc_attr( $fooeventspos_appearance_settings['fooeventspos_app_logo_id'] ); ?>" />
		<span class="uploadbox"><input class="upload_image_button_fooeventspos button fooeventspos-show-image" type="button" value="<?php echo esc_attr( $fooeventspos_phrases['button_upload_store_logo'] ); ?>" /><a href="javascript:void(0);" class="upload_reset_fooeventspos fooeventspos-show-image"><?php echo esc_html( $fooeventspos_phrases['button_clear_store_logo'] ); ?></a></span>
		<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['title_fooevents_pos_app_logo_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</td>
</tr>
<tr class="fooeventspos-hideable-row" style="display:<?php echo ( 'yes' === $fooeventspos_appearance_settings['fooeventspos_use_checkins_settings'] ) ? 'none' : 'table-row'; ?>;">
	<th scope="row"><?php echo esc_html( $fooeventspos_phrases['title_fooevents_pos_primary_color'] ); ?></th>
	<td>
		<input type="text" name="globalFooEventsPOSPrimaryColor" id="globalFooEventsPOSPrimaryColor" class="woocommerce-events-color-field" value="<?php echo esc_attr( $fooeventspos_appearance_settings['fooeventspos_primary_color'] ); ?>" />
		<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['title_fooevents_pos_primary_color_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</td>
</tr>
<tr class="fooeventspos-hideable-row" style="display:<?php echo ( 'yes' === $fooeventspos_appearance_settings['fooeventspos_use_checkins_settings'] ) ? 'none' : 'table-row'; ?>;">
	<th scope="row"><?php echo esc_html( $fooeventspos_phrases['title_fooevents_pos_primary_text_color'] ); ?></th>
	<td>
		<input type="text" name="globalFooEventsPOSPrimaryTextColor" id="globalFooEventsPOSPrimaryTextColor" class="woocommerce-events-color-field" value="<?php echo esc_attr( $fooeventspos_appearance_settings['fooeventspos_primary_text_color'] ); ?>" />
		<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['title_fooevents_pos_primary_text_color_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</td>
</tr>
<tr>
	<th scope="row"><?php echo esc_html( $fooeventspos_phrases['title_fooevents_pos_app_icon'] ); ?></th>
	<td>
		<div class="fooeventspos-image-container">
		<?php
		if ( '' !== $fooeventspos_appearance_settings['fooeventspos_app_icon_url'] && false !== $fooeventspos_appearance_settings['fooeventspos_app_icon_url'] ) :
			?>
			<img src="<?php echo esc_attr( $fooeventspos_appearance_settings['fooeventspos_app_icon_url'] ); ?>?<?php echo esc_attr( time() ); ?>" class="fooeventspos-uploaded-image" />
			<?php
		endif;
		?>
		</div>
		<input id="globalFooEventsPOSAppIcon" type="hidden" class="fooeventspos-hidden-image-input" name="globalFooEventsPOSAppIcon" value="<?php echo esc_attr( $fooeventspos_appearance_settings['fooeventspos_app_icon_id'] ); ?>" />
		<span class="uploadbox"><input class="upload_image_button_fooeventspos button fooeventspos-show-image" type="button" value="<?php echo esc_attr( $fooeventspos_phrases['button_upload_store_icon'] ); ?>" /><a href="javascript:void(0);" class="upload_reset_fooeventspos fooeventspos-show-image"><?php echo esc_html( $fooeventspos_phrases['button_clear_store_logo'] ); ?></a></span>
		<img class="help_tip fooeventspos-tooltip" title="<?php echo esc_attr( $fooeventspos_phrases['title_fooevents_pos_app_icon_tooltip'] ); ?>" src="<?php echo esc_attr( plugins_url() ); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	</td>
</tr>
