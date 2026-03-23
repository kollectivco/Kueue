<?php
/**
 * Ticket import log page
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
	<li><a href="<?php echo esc_attr( admin_url( 'admin.php?page=fooevents-import-tickets', 'https' ) ); ?>"><?php esc_attr_e( 'Upload CSV file', 'woocommerce-events' ); ?></a></li>
		<li><?php esc_attr_e( 'Column Mapping', 'woocommerce-events' ); ?></li>
		<li><?php esc_attr_e( 'Confirm Import', 'woocommerce-events' ); ?></li>
		<li class='active'><?php esc_attr_e( 'Done!', 'woocommerce-events' ); ?></li>
	</ul>	
	<div class="clearfix"></div>
	<div class="fooevents-import-inner">
		<div class="fooevents-import-section fooevents-import-section-header">
			<h2><?php printf( esc_attr__( 'Import %1$s of %2$s Complete', 'woocommerce-events' ), esc_attr( $file_run ), esc_attr( $output_num_runs ) ); ?></h2>	<!-- Display number of imports -->
			<p><?php esc_attr_e( 'The following tickets have been successfully imported.', 'woocommerce-events' ); ?></p>
		</div>		
		<div class="fooevents-import-section">
		<div class="fooevents-import-section-scroll">
				<div class="fooevents-import-section-log">
				<ul>
				<?php $import_count = 0; ?>
					<?php foreach ( $created_tickets['created_tickets'] as $ticket ) : ?>
						<li><?php esc_attr_e( 'Imported', 'woocommerce-events' ); ?> - <a href="<?php echo esc_attr( admin_url( 'post.php?post=' . $ticket['post_id'] . '&action=edit', 'https' ) ); ?>"><?php echo esc_attr( '#' . $ticket['ticket_id'] ); ?></a></li>
						<?php ++$import_count; ?>
					<?php endforeach; ?>
				</ul>
				</div>
			</div>
		</div>
		<div class="fooevents-import-section">
			<p><strong><?php printf( esc_attr__( '%1$s tickets have been imported.', 'woocommerce-events' ), esc_attr( $import_count ) ); ?></strong></p>
			<?php if ( $file_run < $output_num_runs ) : ?>
			<p><u><?php esc_attr_e( 'Please repeat the import process until all tickets have been imported.', 'woocommerce-events' ); ?></u></p><!-- Display if file_run < output_num_runs -->
			<?php endif; ?>
		</div>		
		<div class="fooevents-import-section fooevents-import-section-footer">
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=fooevents-import-tickets', 'https' ) ); ?>" class="button button-primary button-hero"><?php esc_attr_e( 'Import', 'woocommerce-events' ); ?></a> 
			<a href="<?php echo esc_attr( admin_url( 'edit.php?post_type=event_magic_tickets', 'https' ) ); ?>" class="button button-secondary button-hero"><?php esc_attr_e( 'View Tickets', 'woocommerce-events' ); ?></a>
		</div>				
	</div> 
</div> 
