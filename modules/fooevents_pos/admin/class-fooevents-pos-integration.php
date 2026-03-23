<?php
/**
 * The class that handles the integration functionality for FooEvents POS
 *
 * @link https://www.fooevents.com
 * @since 1.0.2
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles the integration functionality for FooEvents POS.
 *
 * @since 1.0.2
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */
class FooEvents_POS_Integration {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.2
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'fooeventspos_register_settings' ) );
		add_action( 'admin_init', array( $this, 'fooeventspos_register_scripts' ) );
		add_action( 'wp_ajax_fooeventspos_save_license_key', array( $this, 'fooeventspos_save_license_key' ) );
		add_action( 'updated_option', array( $this, 'fooeventspos_updated_option' ), 10, 3 );
	}

	/**
	 * Register FooEvents POS global integration settings
	 *
	 * @since 1.0.2
	 */
	public function fooeventspos_register_settings() {

		register_setting( 'fooevents-settings-checkins-app', 'globalWooCommerceEventsPOSUseAppSettings' );

		register_setting( 'fooeventspos-settings-general', 'globalFooEventsPOSUseCheckinsSettings' );
		register_setting( 'fooeventspos-settings-general', 'globalFooEventsPOSAppTitle' );
		register_setting( 'fooeventspos-settings-general', 'globalFooEventsPOSAppLogo' );
		register_setting( 'fooeventspos-settings-general', 'globalFooEventsPOSPrimaryColor' );
		register_setting( 'fooeventspos-settings-general', 'globalFooEventsPOSPrimaryTextColor' );
		register_setting( 'fooeventspos-settings-general', 'globalFooEventsPOSAppIcon' );
	}

	/**
	 * Register admin scripts
	 *
	 * @since 1.0.2
	 */
	public function fooeventspos_register_scripts() {

		$script                 = '';
		$script_url             = '';
		$current_plugin_version = '';

		require plugin_dir_path( __FILE__ ) . 'helpers/fooeventspos-phrases-helper.php';

		$script                 = 'fooeventspos';
		$script_url             = plugin_dir_url( __FILE__ ) . 'js/';
		$current_plugin_version = apply_filters( 'fooeventspos_current_plugin_version', '' );

		$fooeventspos_script_args = array(
			'adminURL'     => get_admin_url(),
			'buttonSave'   => $fooeventspos_phrases['button_save'],
			'buttonSaving' => $fooeventspos_phrases['button_saving'],
			'buttonSaved'  => $fooeventspos_phrases['button_saved'],
		);

		wp_enqueue_script( $script . '-admin-settings-script', $script_url . $script . '-admin-settings.js', array( 'jquery' ), $current_plugin_version, true );
		wp_localize_script( $script . '-admin-settings-script', 'fooeventsposScriptObj', $fooeventspos_script_args );
	}

	/**
	 * Save the FooEvents license key
	 */
	public function fooeventspos_save_license_key() {

		$license_key = isset( $_POST['globalWooCommerceEventsAPIKey'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['globalWooCommerceEventsAPIKey'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		update_option( 'globalWooCommerceEventsAPIKey', $license_key );

		wp_die();
	}

	/**
	 * Generate color options for use in the FooEvents plugin settings Check-ins app tab.
	 *
	 * @since 1.0.2
	 *
	 * @return string Color options.
	 */
	public static function fooeventspos_get_color_options() {

		require plugin_dir_path( __FILE__ ) . 'helpers/fooeventspos-phrases-helper.php';

		$woocommerce_events_pos_use_app_color = 'yes' === get_option( 'globalFooEventsPOSUseCheckinsSettings', '' ) || 'yes' === get_option( 'globalWooCommerceEventsPOSUseAppSettings', '' ) ? 'yes' : 'no';

		ob_start();

		require plugin_dir_path( __FILE__ ) . 'templates/fooevents-pos-global-settings-color-options.php';

		$fooevents_pos_color_options = ob_get_clean();

		return $fooevents_pos_color_options;
	}

	/**
	 * Get the available payment method IDs.
	 *
	 * @since 1.0.2
	 *
	 * @return array Available payment method IDs.
	 */
	public static function fooeventspos_get_payment_method_ids() {
		return array(
			'split',
			'cash',
			'card',
			'direct-bank-transfer',
			'check-payment',
			'cash-on-delivery',
			'square-manual',
			'square-terminal',
			'stripe-manual',
			'stripe-reader',
			'other',
		);
	}

	/**
	 * Get the available payment method options.
	 *
	 * @since 1.0.2
	 *
	 * @return array Available payment method options.
	 */
	public static function fooeventspos_get_payment_method_options() {
		return array(
			'pos-split'                => 'fooeventspos_split',
			'pos-cash'                 => 'fooeventspos_cash',
			'pos-card'                 => 'fooeventspos_card',
			'pos-direct-bank-transfer' => 'fooeventspos_direct_bank_transfer',
			'pos-check-payment'        => 'fooeventspos_check_payment',
			'pos-cash-on-delivery'     => 'fooeventspos_cash_on_delivery',
			'pos-square-manual'        => 'fooeventspos_square_manual',
			'pos-square-terminal'      => 'fooeventspos_square_terminal',
			'pos-stripe-manual'        => 'fooeventspos_stripe_manual',
			'pos-stripe-reader'        => 'fooeventspos_stripe_reader',
			'pos-other'                => 'fooeventspos_other',
		);
	}

	/**
	 * Get the values for the appearance settings tab.
	 *
	 * @since 1.1.0
	 *
	 * @return array Appearance settings.
	 */
	public static function fooeventspos_get_appearance_values() {
		$fooeventspos_appearance_settings = array(
			'fooeventspos_app_title'          => get_option( 'globalFooEventsPOSAppTitle', '' ),
			'fooeventspos_app_logo_url'       => wp_get_attachment_image_url( get_option( 'globalFooEventsPOSAppLogo', '' ), 'full' ),
			'fooeventspos_app_logo_id'        => get_option( 'globalFooEventsPOSAppLogo', '' ),
			'fooeventspos_primary_color'      => get_option( 'globalFooEventsPOSPrimaryColor', '' ),
			'fooeventspos_primary_text_color' => get_option( 'globalFooEventsPOSPrimaryTextColor', '' ),
			'fooeventspos_app_icon_url'       => wp_get_attachment_image_url( get_option( 'globalFooEventsPOSAppIcon', '' ), 'full' ),
			'fooeventspos_app_icon_id'        => get_option( 'globalFooEventsPOSAppIcon', '' ),
		);

		if ( is_plugin_active( 'fooevents/fooevents.php' ) || is_plugin_active_for_network( 'fooevents/fooevents.php' ) ) {

			$fooeventspos_appearance_settings['fooeventspos_use_checkins_settings'] = 'yes' === get_option( 'globalFooEventsPOSUseCheckinsSettings', '' ) || 'yes' === get_option( 'globalWooCommerceEventsPOSUseAppSettings', '' ) ? 'yes' : 'no';

		}

		return $fooeventspos_appearance_settings;
	}

	/**
	 * Add the appearance values to the store settings data.
	 *
	 * @since 1.1.0
	 * @param array $data The data array containing all the store settings values.
	 */
	public static function fooeventspos_add_appearance_values( &$data ) {

		if ( ( is_plugin_active( 'fooevents/fooevents.php' ) || is_plugin_active_for_network( 'fooevents/fooevents.php' ) ) &&
			( 'yes' === get_option( 'globalFooEventsPOSUseCheckinsSettings', '' ) || 'yes' === get_option( 'globalWooCommerceEventsPOSUseAppSettings', '' ) )
			) {
			$data['fooeventspos_app_title']          = (string) get_option( 'globalWooCommerceEventsAppTitle', '' );
			$data['fooeventspos_app_logo']           = (string) get_option( 'globalWooCommerceEventsAppLogo', '' );
			$data['fooeventspos_primary_color']      = (string) get_option( 'globalWooCommerceEventsAppColor', '' );
			$data['fooeventspos_primary_text_color'] = (string) get_option( 'globalWooCommerceEventsAppSignInTextColor', '' );
		} else {
			$data['fooeventspos_app_title']          = (string) get_option( 'globalFooEventsPOSAppTitle', '' );
			$data['fooeventspos_app_logo']           = wp_get_attachment_image_url( get_option( 'globalFooEventsPOSAppLogo', '' ), 'full' );
			$data['fooeventspos_primary_color']      = (string) get_option( 'globalFooEventsPOSPrimaryColor', '' );
			$data['fooeventspos_primary_text_color'] = (string) get_option( 'globalFooEventsPOSPrimaryTextColor', '' );
		}

		$data['fooeventspos_app_logo'] = preg_replace_callback(
			'/[^\x20-\x7f]/',
			function ( $app_logo_matches ) {
				return rawurlencode( $app_logo_matches[0] );
			},
			$data['fooeventspos_app_logo']
		);

		if ( strpos( $data['fooeventspos_primary_color'], '#' ) === false ) {
			$data['fooeventspos_primary_color'] = '';
		}

		if ( strpos( $data['fooeventspos_primary_text_color'], '#' ) === false ) {
			$data['fooeventspos_primary_text_color'] = '';
		}

		$data['fooeventspos_app_icon'] = preg_replace_callback(
			'/[^\x20-\x7f]/',
			function ( $app_icon_matches ) {
				return rawurlencode( $app_icon_matches[0] );
			},
			wp_get_attachment_image_url( get_option( 'globalFooEventsPOSAppIcon', '' ), 'full' )
		);
	}

	/**
	 * Hook for when options are updated.
	 *
	 * @since 1.1.0
	 * @param string $option_name The name of the option that was updated.
	 * @param string $old_value The old value of the option.
	 * @param string $value The new value of the option.
	 */
	public function fooeventspos_updated_option( $option_name, $old_value, $value ) {

		if ( 'globalFooEventsPOSUseCheckinsSettings' === $option_name ) {

			update_option( 'globalWooCommerceEventsPOSUseAppSettings', $value );

			FooEventsPOS_Deactivator::fooeventspos_remove_pos_page();
		} elseif ( 'globalWooCommerceEventsPOSUseAppSettings' === $option_name ) {

			update_option( 'globalFooEventsPOSUseCheckinsSettings', $value );

			FooEventsPOS_Deactivator::fooeventspos_remove_pos_page();
		} elseif ( 'globalFooEventsPOSAppTitle' === $option_name ) {

			if ( 'yes' !== get_option( 'globalFooEventsPOSUseCheckinsSettings', '' ) && 'yes' !== get_option( 'globalWooCommerceEventsPOSUseAppSettings', '' ) ) {
				FooEventsPOS_Deactivator::fooeventspos_remove_pos_page();
			}
		} elseif ( 'globalFooEventsPOSAppIcon' === $option_name ) {
			$wp_upload_dir           = wp_upload_dir();
			$fooeventspos_upload_path = $wp_upload_dir['basedir'] . '/fooeventspos/';

			$icon_sizes = fooeventspos_get_app_icon_sizes();

			if ( '' !== $value ) {
				if ( ! is_wp_error( $original_icon ) ) {
					foreach ( $icon_sizes as $icon => $size ) {
						$original_icon = wp_get_image_editor( wp_get_original_image_path( $value ) );
						$original_icon->resize( $size, $size, true );
						$original_icon->save( $fooeventspos_upload_path . $icon );
					}
				}
			} else {
				$assets_path = WP_PLUGIN_DIR . '/fooevents_pos/public/build/';

				foreach ( $icon_sizes as $icon => $size ) {
					copy( $assets_path . $icon, $fooeventspos_upload_path . $icon );
				}
			}
		}

		$fooeventspos_appearance_options = array(
			'globalWooCommerceEventsPOSUseAppSettings',
			'globalWooCommerceEventsAppTitle',
			'globalWooCommerceEventsAppLogo',
			'globalWooCommerceEventsAppColor',
			'globalWooCommerceEventsAppSignInTextColor',
			'globalFooEventsPOSUseCheckinsSettings',
			'globalFooEventsPOSAppTitle',
			'globalFooEventsPOSAppLogo',
			'globalFooEventsPOSPrimaryColor',
			'globalFooEventsPOSPrimaryTextColor',
			'globalFooEventsPOSAppIcon',
		);

		if ( in_array( $option_name, $fooeventspos_appearance_options, true ) ) {
			self::fooeventspos_generate_webmanifest();
		}
	}

	/**
	 * Generate webmanifest file.
	 *
	 * @since 1.1.0
	 */
	private static function fooeventspos_generate_webmanifest() {

		if ( ! function_exists( 'WP_Filesystem' ) ) {

			require_once ABSPATH . '/wp-admin/includes/file.php';

		}

		WP_Filesystem();

		global $wp_filesystem;

		$app_title         = self::fooeventspos_get_app_title();
		$app_primary_color = self::fooeventspos_get_app_primary_color();

		$manifest = array(
			'name'             => $app_title,
			'short_name'       => $app_title,
			'icons'            => array(
				array(
					'src'   => 'android-chrome-192x192.png',
					'sizes' => '192x192',
					'type'  => 'image/png',
				),
				array(
					'src'   => 'android-chrome-512x512.png',
					'sizes' => '512x512',
					'type'  => 'image/png',
				),
			),
			'theme_color'      => $app_primary_color,
			'background_color' => $app_primary_color,
			'display'          => 'standalone',
			'start_url'        => esc_url( home_url( '/' . self::fooeventspos_get_app_slug() ) ),
		);

		$wp_upload_dir           = wp_upload_dir();
		$fooeventspos_upload_path = $wp_upload_dir['basedir'] . '/fooeventspos/';
		$manifest_file           = $fooeventspos_upload_path . 'fooeventspos.webmanifest';

		$manifest_json = wp_json_encode( $manifest );

		$wp_filesystem->put_contents( $manifest_file, $manifest_json );
	}

	/**
	 * Get the app title for use as the POS page name.
	 *
	 * @since 1.1.0
	 *
	 * @return string App title.
	 */
	public static function fooeventspos_get_app_title() {

		require plugin_dir_path( __FILE__ ) . 'helpers/fooeventspos-phrases-helper.php';

		$default_page_name = $fooeventspos_phrases['title_fooevents_pos'];

		$app_title = trim( get_option( 'globalFooEventsPOSAppTitle', $default_page_name ) );

		if ( 'yes' === get_option( 'globalFooEventsPOSUseCheckinsSettings', '' ) || 'yes' === get_option( 'globalWooCommerceEventsPOSUseAppSettings', '' ) ) {
			$app_title = trim( get_option( 'globalWooCommerceEventsAppTitle', $default_page_name ) );
		}

		if ( '' === $app_title ) {
			$app_title = $default_page_name;
		}

		return $app_title;
	}

	/**
	 * Get the app title as a slug for use as the POS page slug.
	 *
	 * @since 1.1.0
	 *
	 * @return string App slug.
	 */
	public static function fooeventspos_get_app_slug() {

		$pos_page_slug = 'fooeventspos';

		$app_slug = sanitize_title( trim( get_option( 'globalFooEventsPOSAppTitle', $pos_page_slug ) ) );

		if ( 'yes' === get_option( 'globalFooEventsPOSUseCheckinsSettings', '' ) || 'yes' === get_option( 'globalWooCommerceEventsPOSUseAppSettings', '' ) ) {
			$app_slug = sanitize_title( trim( get_option( 'globalWooCommerceEventsAppTitle', $pos_page_slug ) ) );
		}

		if ( '' === $app_slug ) {
			$app_slug = $pos_page_slug;
		}

		return $app_slug;
	}

	/**
	 * Get the app primary color.
	 *
	 * @since 1.1.0
	 *
	 * @return string App primary color.
	 */
	public static function fooeventspos_get_app_primary_color() {

		$default_primary_color = '#b4458d';

		$fooeventspos_appearance_settings = array();

		self::fooeventspos_add_appearance_values( $fooeventspos_appearance_settings );

		$app_primary_color = $fooeventspos_appearance_settings['fooeventspos_primary_color'];

		if ( '' === $app_primary_color ) {
			$app_primary_color = $default_primary_color;
		}

		return $app_primary_color;
	}
}
