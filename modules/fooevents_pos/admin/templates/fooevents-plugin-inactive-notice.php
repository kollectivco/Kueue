<?php
/**
 * HTML template for the notice that shows when the FooEvents plugin is not activated
 *
 * @link https://www.fooevents.com
 * @since 1.9.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin/templates
 */

defined( 'ABSPATH' ) || exit;

?>

<?php if ( PAnD::is_admin_notice_active( 'disable-notice-fooevents-plugin-inactive-60' ) && false === ( is_plugin_active( 'fooevents/fooevents.php' ) || is_plugin_active_for_network( 'fooevents/fooevents.php' ) ) ) { ?>
	<div data-dismissible="disable-notice-fooevents-plugin-inactive-60" class="notice notice-info is-dismissible fooeventspos-notice">
		<p>
			<?php printf( esc_html( $fooeventspos_phrases['status_notice_fooevents_plugin'] ), '<a href="https://www.fooevents.com/products/fooevents-for-woocommerce/" target="_blank">', '</a>' ); ?>
			<?php if ( '' !== $fooevents_activate_plugin_link ) : ?>
				<a href="<?php echo esc_attr( $fooevents_activate_plugin_link ); ?>" target="_blank"><?php echo esc_html( $fooeventspos_phrases['status_activate_link'] ); ?></a>
			<?php endif; ?>
		</p>
	</div>
<?php } ?>
