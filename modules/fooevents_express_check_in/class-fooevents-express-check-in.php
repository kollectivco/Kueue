<?php

/**
 * Main plugin class.
 *
 * @link https://www.fooevents.com
 * @package fooevents-express-check-in
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * Main plugin class.
 */
class FooEvents_Express_Check_In {

	/**
	 * Configuration object
	 *
	 * @var object $config contains paths and other configurations
	 */
	private $config;

	/**
	 * Ticket helper object
	 *
	 * @var object $update_helper responsible for plugin updates
	 */
	private $ticket_helper;

	/**
	 * Update helper object
	 *
	 * @var object $update_helper responsible for plugin updates
	 */
	private $update_helper;

	/**
	 * On plugin load
	 */
	public function __construct() {

		add_action( 'admin_notices', array( $this, 'check_fooevents' ) );
		add_action( 'init', array( $this, 'load_text_domain' ) );
		add_action( 'init', array( $this, 'plugin_init' ) );
		add_action( 'admin_init', array( $this, 'register_scripts_and_styles' ) );

	}

	/**
	 * Checks if FooEvents is installed
	 */
	public function check_fooevents() {

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( ! is_plugin_active( 'fooevents/fooevents.php' ) ) {

				$this->output_notices( array( __( 'The FooEvents Express Check-in plugin requires FooEvents for WooCommerce to be installed.', 'fooevents-express-check-in' ) ) );

		}

	}

	/**
	 * Loads text domain and readies translations
	 */
	public function load_text_domain() {

		$path   = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$loaded = load_plugin_textdomain( 'fooevents-express-check-in', false, $path );

	}

	/**
	 * Initializes plugin
	 */
	public function plugin_init() {

		// Main config.
		$this->config = new FooEvents_Express_Check_In_Config();

		// TicketHelper.
		require_once $this->config->class_path . 'class-fooevents-express-check-in-ticket-helper.php';
		$this->ticket_helper = new FooEvents_Express_Check_In_Ticket_Helper( $this->config );

		// UpdateHelper.
		require_once $this->config->class_path . 'updatehelper.php';
		$this->update_helper = new FooEvents_Express_Check_In_Update_Helper( $this->config );

	}

	/**
	 * Register JavaScript and CSS file in WordPress admin
	 */
	public function register_scripts_and_styles() {

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['page'] ) ) {

			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( 'fooevents-express-checkin-page' === $_GET['page'] ) {

				$global_woocommerce_events_express_sounds = get_option( 'globalWooCommerceEventsExpressSounds' );

				$fooevents_obj = array(
					'successTicketText'  => __( 'SUCCESS: Ticket', 'fooevents-express-check-in' ),
					'hasBeenUpdatedText' => __( ' has been updated.', 'fooevents-express-check-in' ),
					'soundsURL'          => $this->config->plugin_url . 'sounds/',
					'soundsEnable'       => $global_woocommerce_events_express_sounds,
				);

				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'fooevents-express-check-in-admin-script', $this->config->scripts_path . 'check-in-admin.js', array( 'jquery' ), $this->config->plugin_data['Version'], true );
				wp_localize_script( 'fooevents-express-check-in-admin-script', 'FooEventsExpressObj', $fooevents_obj );

				wp_enqueue_style( 'fooevents-express-check-in-admin-style', $this->config->styles_path . 'check-in-admin.css', array(), $this->config->plugin_data['Version'] );

			}
		}

	}

	/**
	 * Outputs notices to screen.
	 *
	 * @param array $notices notice.
	 */
	private function output_notices( $notices ) {

		foreach ( $notices as $notice ) {

			echo '<div class="updated"><p>' . esc_attr( $notice ) . '</p></div>';

		}

	}

}
