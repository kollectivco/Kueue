<?php
/**
 * Config class.
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin config class
 */
class FooEvents_Config {

	/**
	 * Plugin directory name
	 *
	 * @var string $plugin_directory plugin directory
	 */
	public $plugin_directory;

	/**
	 * Path to plugin
	 *
	 * @var string $path path to plugin
	 */
	public $path;

	/**
	 * Path to classes
	 *
	 * @var string $class_path path to classes
	 */
	public $class_path;

	/**
	 * Path to classes
	 *
	 * @var string $template_path path to templates
	 */
	public $template_path;

	/**
	 * Path to vendor directory
	 *
	 * @var string $vendor_path Path to vendor directory
	 */
	public $vendor_path;

	/**
	 * Path to barcode directory
	 *
	 * @var string $barcodePath Path to barcode directory
	 */
	public $barcode_path;

	/**
	 * Path to PDF ticket directory
	 *
	 * @var string $pdf_ticket_path Path to PDF ticket directory
	 */
	public $pdf_ticket_path;

	/**
	 * PDF ticket URL
	 *
	 * @var string $pdf_ticket_url PDF ticket URL
	 */
	public $pdf_ticket_url;

	/**
	 * ICS Path
	 *
	 * @var string $ics_path PDF ICS Path
	 */
	public $ics_path;

	/**
	 * ICS URL
	 *
	 * @var string $ics_url PDF ICS URL
	 */
	public $ics_url;


	/**
	 * Path to scripts
	 *
	 * @var string $scripts_path path to scripts
	 */
	public $scripts_path;

	/**
	 * Path to styles
	 *
	 * @var string $styles_path path to styles
	 */
	public $styles_path;

	/**
	 * Email template path
	 *
	 * @var string $email_template_path email template path
	 */
	public $email_template_path;

	/**
	 * Plugin URL
	 *
	 * @var string $plugin_url Plugin URL
	 */
	public $plugin_url;

	/**
	 * Plugin URL
	 *
	 * @var string $event_plugin_url Plugin URL
	 */
	public $event_plugin_url;

	/**
	 * Client mode
	 *
	 * @var string $client_mode Client mode
	 */
	public $client_mode;

	/**
	 * FooEvents salt
	 *
	 * @var string $salt FooEvents salt
	 */
	public $salt;

	/**
	 * Plugin data
	 *
	 * @var array $plugin_data Plugin data
	 */
	public $plugin_data;

	/**
	 * Plugin file
	 *
	 * @var array $plugin_file Plugin file
	 */
	public $plugin_file;

	/**
	 * Uploads directory path
	 *
	 * @var array $uploads_dir_path Uploads directory path
	 */
	public $uploads_dir_path;

	/**
	 * Uploads path
	 *
	 * @var array $uploads_path Uploads path
	 */
	public $uploads_path;

	/**
	 * Barcode URL
	 *
	 * @var array $barcode_url Barcode URL
	 */
	public $barcode_url;

	/**
	 * Theme packs path
	 *
	 * @var array $theme_packs_path Theme packs path
	 */
	public $theme_packs_path;

	/**
	 * Theme packs URL
	 *
	 * @var array $theme_packs_url Theme packs URL
	 */
	public $theme_packs_url;

	/**
	 * Email template path theme email
	 *
	 * @var array $email_template_path_theme_email Email template path theme email
	 */
	public $email_template_path_theme_email;

	/**
	 * Email template path theme
	 *
	 * @var array $email_template_path_theme Email template path theme
	 */
	public $email_template_path_theme;

	/**
	 * Initialize configuration variables to be used as object.
	 */
	public function __construct() {

		$upload_dir = wp_upload_dir();
		            $base_dir = trailingslashit( ABSPATH . 'app' );          // /home/USER/public_html/app/
    $base_url = trailingslashit( site_url( 'app' ) );        // https://example.com/app/

		$this->plugin_directory                = 'fooevents';
		$this->path                            = plugin_dir_path( __FILE__ );
		$this->plugin_file                     = $this->path . 'fooevents.php';
		$this->plugin_url                      = plugin_dir_url( __FILE__ );
		$this->class_path                      = plugin_dir_path( __FILE__ ) . 'classes/';
		$this->template_path                   = plugin_dir_path( __FILE__ ) . 'templates/';
		$this->vendor_path                     = plugin_dir_path( __FILE__ ) . 'vendor/';
   $this->uploads_dir_path    = $base_dir;                  // ABSPATH . 'app/'
    $this->uploads_path        = $base_dir;                  // ABSPATH . 'app/'
    $this->barcode_path        = $base_dir . 'qr/';          // ABSPATH . 'app/qr/'
    $this->barcode_url         = $base_url . 'qr/';          // site_url('app/qr/')
		$this->theme_packs_path                = $upload_dir['basedir'] . '/fooevents/themes/';
		$this->theme_packs_url                 = $upload_dir['baseurl'] . '/fooevents/themes/';
    $this->pdf_ticket_path     = $base_dir . 'tickets/';     // ABSPATH . 'app/tickets/'
    $this->pdf_ticket_url      = $base_url . 'tickets/';     // site_url('app/tickets/')
		$this->ics_path                        = $upload_dir['basedir'] . '/fooevents/ics/';
		$this->ics_url                         = $upload_dir['baseurl'] . '/fooevents/ics/';
		$this->email_template_path             = plugin_dir_path( __FILE__ ) . 'templates/email/';
		$this->email_template_path_theme_email = get_stylesheet_directory() . '/' . $this->plugin_directory . '/themes/';
		$this->email_template_path_theme       = get_stylesheet_directory() . '/' . $this->plugin_directory . '/templates/';
		$this->scripts_path                    = plugin_dir_url( __FILE__ ) . 'js/';
		$this->styles_path                     = plugin_dir_url( __FILE__ ) . 'css/';
		$this->event_plugin_url                = plugins_url() . '/' . $this->plugin_directory . '/';
		$this->client_mode                     = false;
		$this->salt                            = get_option( 'woocommerce_events_do_salt' );

		if ( ! function_exists( 'get_plugin_data' ) ) {

			require_once ABSPATH . 'wp-admin/includes/plugin.php';

		}

		$this->plugin_data = get_plugin_data( __DIR__ . '/fooevents.php', false, false );

	}

}
