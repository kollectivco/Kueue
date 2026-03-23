<?php
/**
 * XML-RPC class containing initialization of XML-RPC methods as well as their callbacks.
 *
 * @link https://www.fooevents.com
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * The XML-RPC API-specific functionality of the plugin.
 *
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */
class FooEventsPOS_XMLRPC {
	/**
	 * The FooEvents POS phrases helper.
	 *
	 * @since 1.0.0
	 * @var array $fooeventspos_phrases The current phrases helper array.
	 */
	private $fooeventspos_phrases;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		require plugin_dir_path( __FILE__ ) . 'helpers/fooeventspos-phrases-helper.php';

		$this->fooeventspos_phrases = $fooeventspos_phrases;

		add_action( 'admin_notices', array( $this, 'fooeventspos_check_xmlrpc_enabled' ) );
	}

	/**
	 * Check whether XML-RPC is enabled.
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_check_xmlrpc_enabled() {

		$xmlrpc_enabled = false;
		$enabled        = get_option( 'enable_xmlrpc' );

		if ( $enabled ) {

			$xmlrpc_enabled = true;

		} else {

			global $wp_version;

			if ( version_compare( $wp_version, '3.5', '>=' ) ) {

				$xmlrpc_enabled = true;

			} else {

				$xmlrpc_enabled = false;

			}
		}

		if ( ! $xmlrpc_enabled ) {

			$this->fooeventspos_output_notices( array( esc_html( $this->fooeventspos_phrases['notice_xmlrpc_not_enabled'] ) ) );

		}
	}

	/**
	 * Output admin notices.
	 *
	 * @since 1.0.0
	 * @param array $notices An array of notices to output.
	 */
	private function fooeventspos_output_notices( $notices ) {

		foreach ( $notices as $notice ) {

			echo '<div class="notice notice-error"><p>' . esc_html( $notice ) . '</p></div>';

		}
	}
}
