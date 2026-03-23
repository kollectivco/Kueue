<?php
/**
 * Fired during plugin deactivation
 *
 * @link https://www.fooevents.com
 * @since 1.0.0
 *
 * @package fooevents-pos
 * @subpackage fooevents-pos/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/includes
 */
class FooEventsPOS_Deactivator {
	/**
	 * Main function called when deactivating the plugin
	 *
	 * @since 1.0.0
	 */
	public static function fooeventspos_deactivate() {
		self::fooeventspos_remove_pos_page();
	}

	/**
	 * Flush rewrite rules upon deactivation.
	 *
	 * When the plugin deactivates, the rewrite rules that were created when the plugin
	 * was activated must be cleared so that the normal WordPress rewrites take over again.
	 *
	 * @since 1.1.0
	 */
	public static function fooeventspos_remove_pos_page() {

		$fooeventspos_pos_page_post_id = (int) get_option( 'fooeventspos_pos_page', 0 );

		if ( $fooeventspos_pos_page_post_id > 0 ) {
			// Remove post/page object.
			wp_delete_post( $fooeventspos_pos_page_post_id, true );

			delete_option( 'fooeventspos_pos_page' );

			flush_rewrite_rules();
		}
	}
}
