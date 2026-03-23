<?php
/**
 * FooEventsPOS_Public class
 *
 * @link https://www.fooevents.com
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/public
 */

defined( 'ABSPATH' ) || exit;

/**
 * The public-facing functionality of the plugin.
 *
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/public
 */
class FooEventsPOS_Public {
	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @var string $fooeventspos The ID of this plugin.
	 */
	private $fooeventspos;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @var string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $fooeventspos The name of the plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $fooeventspos, $version ) {
		$this->fooeventspos   = $fooeventspos;
		$this->version = $version;

		$this->fooeventspos_load_dependencies();
	}

	/**
	 * Load the required dependencies for this class.
	 *
	 * @since 1.1.1
	 */
	private function fooeventspos_load_dependencies() {
		// FooEvents_POS_Integration class.
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-fooevents-pos-integration.php';
	}

	/**
	 * Create custom CRA app page template
	 *
	 * @since 1.0.0
	 * @param string $template The page template used by the React app.
	 */
	public function fooeventspos_pos_page_template( $template ) {
		$fooeventspos_pos_page_post_id = (int) get_option( 'fooeventspos_pos_page', 0 );

		if ( $fooeventspos_pos_page_post_id > 0 && is_page( $fooeventspos_pos_page_post_id ) ) {
			return plugin_dir_path( __FILE__ ) . 'template-fooeventspos-page.php';
		}

		return $template;
	}

	/**
	 * Add rewrite rules for querying React production build assets
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_rewrite() {
		$server_request_uri = '';
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$server_request_uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}

		$pos_page_slug = 'fooeventspos';

		$pos_page_slug = FooEvents_POS_Integration::fooeventspos_get_app_slug();

		if ( substr( $server_request_uri, 0, strlen( '/' . $pos_page_slug ) ) === '/' . $pos_page_slug && strlen( substr( $server_request_uri, strlen( '/' . $pos_page_slug ) ) ) > 1 ) {
			wp_safe_redirect( esc_url( home_url( '/' . $pos_page_slug . '/' ) ) );

			exit;
		}
	}

	/**
	 * Enqueue React scripts and styles
	 *
	 * @since 1.3.0
	 */
	public function fooeventspos_set_fooeventspos_scripts_and_styles() {
		$pos_page_slug = 'fooeventspos';

		$pos_page_slug = FooEvents_POS_Integration::fooeventspos_get_app_slug();

		if ( is_page( $pos_page_slug ) ) {
			if ( in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ), true ) || is_admin() ) {
				return;
			}

			$rand_version = wp_rand( 10, 1000 );

			// Enqueue scripts.
			$js_files     = scandir( __DIR__ . '/build/static/js/' );
			$script_count = 0;

			foreach ( $js_files as $filename ) {
				if ( false !== strpos( $filename, '.js' ) && false === strpos( $filename, '.js.map' ) ) {
					$fullpath = plugin_dir_url( __FILE__ ) . 'build/static/js/' . $filename;
					wp_enqueue_script( 'fooeventspos-scripts' . ( $script_count++ ), $fullpath, array(), $rand_version, true );
				}
			}

			// Enqueue styles.
			$font_awesome_version = '6.7.2';

			wp_enqueue_style( 'fooeventspos-font-awesome', '//use.fontawesome.com/releases/v' . $font_awesome_version . '/css/all.css', array(), $font_awesome_version, 'all' );

			$css_files   = scandir( __DIR__ . '/build/static/css/' );
			$style_count = 0;

			foreach ( $css_files as $filename ) {
				if ( false !== strpos( $filename, '.css' ) && false === strpos( $filename, '.css.map' ) ) {
					$fullpath = plugin_dir_url( __FILE__ ) . 'build/static/css/' . $filename;
					wp_enqueue_style( 'fooeventspos-styles' . ( $style_count++ ), esc_url( $fullpath ), array(), $rand_version, 'all' );
				}
			}
		}
	}

	/**
	 * Start capturing wp_head content
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_start_wp_head_footer_buffer() {
		$pos_page_slug = 'fooeventspos';

		$pos_page_slug = FooEvents_POS_Integration::fooeventspos_get_app_slug();

		if ( is_page( $pos_page_slug ) ) {
			if ( in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ), true ) || is_admin() ) {
				return;
			}

			ob_start();
		}
	}

	/**
	 * End capturing wp_head content to strip out non-FooEvents POS styles and scripts
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_end_wp_head_footer_buffer() {
		$pos_page_slug = 'fooeventspos';

		$pos_page_slug = FooEvents_POS_Integration::fooeventspos_get_app_slug();

		if ( is_page( $pos_page_slug ) ) {
			if ( in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ), true ) || is_admin() ) {
				return;
			}

			$wp_head_footer = ob_get_contents();

			ob_get_clean();

			// Remove content filters.
			remove_all_filters( 'pre_kses' );

			$includes = preg_grep( '/fooeventspos-font-awesome|fooeventspos-styles|fooeventspos-scripts/', explode( PHP_EOL, $wp_head_footer ) );

			foreach ( $includes as $include ) {
				echo wp_kses(
					$include,
					array(
						'link'   => array(
							'rel'   => array(),
							'id'    => array(),
							'href'  => array(),
							'media' => array(),
						),
						'script' => array(
							'src' => array(),
							'id'  => array(),
						),
					)
				);
			}
		}
	}

	/**
	 * Hide admin bar on FooEvents POS page
	 *
	 * @since 1.0.0
	 * @param bool $show_admin_bar Whether or not the admin bar should show.
	 */
	public function fooeventspos_hide_admin_bar( $show_admin_bar ) {
		$pos_page_slug = 'fooeventspos';

		$pos_page_slug = FooEvents_POS_Integration::fooeventspos_get_app_slug();

		if ( is_page( $pos_page_slug ) ) {
			return false;
		} else {
			return $show_admin_bar;
		}
	}
}
