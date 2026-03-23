<?php
/**
 * Ticket themes viewer template
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_attr_e( 'Ticket Themes', 'woocommerce-events' ); ?></h1>
</div>
<?php add_thickbox(); ?> 

<div id="fooevents-theme-viewer-upload-wrapper">
		<p class="install-help"></p>
	<div class="fooevents-theme-viewer-upload-inner">
		<form name="fooevents-theme-viewer-upload-form" action="admin.php?page=fooevents-ticket-themes" method="POST" enctype="multipart/form-data">
			<div>
			<h3><?php esc_attr_e( 'Upload your Ticket Theme ZIP file:', 'woocommerce-events' ); ?></h3>
			<input type="file" name="fooevents-theme-viewer-upload-file" /><br /> 
			<input type="submit" value="<?php esc_attr_e( 'Upload Ticket Theme', 'woocommerce-events' ); ?>" class="button button-primary" />
			<?php wp_nonce_field( 'fooevents_upload_theme' ); ?>
			</div>
		</form>
	</div> 
</div> 

<div id="fooevents-theme-viewer-wrapper">
<?php foreach ( $themes as $theme_name => $theme ) : ?>
	<div class="fooevents-theme-viewer-theme">
	<div class="fooevents-theme-viewer-theme-inner">
		<div id="fooevents-theme-viewer-preview-options-<?php echo esc_attr( $theme['file_name'] ); ?>" style="display:none;" class="fooevents-theme-viewer-preview-popup" >
			<div class="fooevents-theme-viewer-preview-controls">
				<form name="fooevents-theme-viewer-preview-form-<?php echo esc_attr( $theme['file_name'] ); ?>" action="admin.php?page=fooevents-ticket-themes" method="POST">
				<h3><?php esc_attr_e( 'Send preview to:', 'woocommerce-events' ); ?></h3> 
				<input type="text" value="<?php echo esc_attr( $user_email ); ?>" name="fooevents-theme-viewer-preview-input" class="regular-text" />
				<input type="hidden" value="<?php echo esc_attr( $theme['path'] ); ?>" name="fooevents-theme-viewer-preview-path" />
				<input type="hidden" value="<?php echo esc_attr( $theme_name ); ?>" name="fooevents-theme-viewer-preview-theme-name" />
				<input type="submit" value="<?php esc_attr_e( 'Send Ticket Preview', 'woocommerce-events' ); ?>" name="fooevents-theme-viewer-preview-submit" class="button button-primary" />
				</form>
			</div>
			<img src="<?php echo esc_attr( $theme['preview'] ); ?>" class="fooevents-theme-viewer-image-preview" />
		</div>
		<div class="fooevents-theme-viewer-preview-wrapper">
			<a href="#TB_inline?width=800&height=550&inlineId=fooevents-theme-viewer-preview-options-<?php echo esc_attr( $theme['file_name'] ); ?>" class="thickbox">
			<img src="<?php echo esc_attr( $theme['preview'] ); ?>" class="fooevents-theme-viewer-image">
			</a>
		</div>
		<div class="fooevents-theme-viewer-name">
			<h3><a href="#TB_inline?width=800&height=550&inlineId=fooevents-theme-viewer-preview-options-<?php echo esc_attr( $theme['file_name'] ); ?>" class="thickbox"><?php echo esc_attr( $theme['name'] ); ?></a></h3>
		</div>
	</div>
	</div>
<?php endforeach; ?>
</div>
