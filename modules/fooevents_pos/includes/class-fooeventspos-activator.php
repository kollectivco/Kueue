<?php
/**
 * Fired during plugin activation
 *
 * @link https://www.fooevents.com
 * @since 1.0.0
 *
 * @package fooevents-pos
 * @subpackage fooevents-pos/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/includes
 */
class FooEventsPOS_Activator {
	/**
	 * Set flag to flush rewrite rules upon activation.
	 *
	 * When the plugin activates, a flag is set for the init hook so that the
	 * rewrite rules can be flushed once on init after which the flag is removed
	 * to prevent it from flushing again.
	 *
	 * @since 1.0.0
	 */
	public static function fooeventspos_activate() {
		update_option( 'fooeventspos_flush_rewrite_rules_flag', true );
	}
}
