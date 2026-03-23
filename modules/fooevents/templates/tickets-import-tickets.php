<?php
/**
 * Ticket import tickets page
 *
 * @link https://www.fooevents.com
 * @package woocommerce_events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_attr_e( 'Import Tickets', 'woocommerce-events' ); ?></h1>
</div>
<p class="install-help"></p>
<?php if ( ! empty( $error_message ) ) : ?>
	<div class="notice notice-error">
		<p><?php echo esc_attr( $error_message ); ?></p>
	</div>
<?php endif; ?>	
<div id="fooevents-import-wrapper">
	<ul class="fooevents-import-steps">
	<li class='active'><a href="<?php echo esc_attr( admin_url( 'admin.php?page=fooevents-import-tickets', 'https' ) ); ?>"><?php esc_attr_e( 'Upload CSV file', 'woocommerce-events' ); ?></a></li>
		<li><?php esc_attr_e( 'Column Mapping', 'woocommerce-events' ); ?></li>
		<li><?php esc_attr_e( 'Confirm Import', 'woocommerce-events' ); ?></li>
		<li><?php esc_attr_e( 'Done!', 'woocommerce-events' ); ?></li>
	</ul>	
	<div class="clearfix"></div>
	<div class="fooevents-import-inner">
		<div class="fooevents-import-section fooevents-import-section-header">
			<h2><?php echo esc_html( __( 'Import tickets from a CSV file', 'woocommerce-events' ) ); ?></h2>	
			<p>
			<?php
				// translators: Placeholders are for opening and closing HTML anchor tags.
				echo wp_kses_post( sprintf( __( 'This tool allows you to import tickets to your store from a CSV file. For more information on how to structure your CSV, please refer to %1$sthis help guide%2$s.', 'woocommerce-events' ), '<a href="https://help.fooevents.com/docs/topics/tickets/import-tickets/" target="_blank">', '</a>' ) );
			?>
			</p>
		</div>
		<form name="fooevents-theme-viewer-upload-form" action="<?php echo esc_attr( admin_url( 'admin.php' ) ); ?>?page=fooevents-import-tickets" method="POST" enctype="multipart/form-data">
			<div class="fooevents-import-section">
			<table class="form-table">
				<tr>
					<td>
						<label><?php echo esc_html( __( 'Choose a CSV file from your computer:', 'woocommerce-events' ) ); ?></label>
					</td>
					<td>
						<input type="file" name="fooevents-import-tickets-file" />
					</td>
				</tr>
			</table>
			</div>
			<div class="fooevents-import-section fooevents-import-section-footer">
				<input type="submit" value="<?php esc_attr_e( 'Continue', 'woocommerce-events' ); ?>" class="button button-primary button-hero" />
				<input type="hidden" name="step" value="1" />
			</div>
		</form>
	</div> 
</div> 
