<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link https://www.fooevents.com
 * @since 1.0.0
 *
 * @package fooevents-pos
 * @subpackage fooevents-pos/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/includes
 */
class FooEventsPOS_I18n {
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_load_plugin_textdomain() {
		$path   = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$loaded = load_plugin_textdomain( 'fooevents-pos', false, $path );
	}
}
