<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * Config class.
 *
 * @link https://www.fooevents.com
 * @package fooevents-multiday-events
 */
class Fooevents_Multiday_Events_Config {

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
	 * Path to templates
	 *
	 * @var string $template_path path to templates
	 */
	public $template_path;

	/**
	 * Path to templates override in theme
	 *
	 * @var string $template_path_theme path template overrides in theme
	 */
	public $template_path_theme;

	/**
	 * Path to plugin directory
	 *
	 * @var string $plugin_directory path to main plugin file
	 */
	public $plugin_directory;

	/**
	 * Path to classes
	 *
	 * @var string $class_path path to classes
	 */
	public $class_path;

	/**
	 * Path to plugin
	 *
	 * @var string $path path to plugin
	 */
	public $path;

	/**
	 * Path to plugin file
	 *
	 * @var string $plugin_file path to main plugin file
	 */
	public $plugin_file;

	/**
	 * Plugin Data
	 *
	 * @var object $plugin_data plugin data
	 */
	public $plugin_data;

	/**
	 * Initialize configuration variables to be used as object.
	 */
	public function __construct() {

		$this->plugin_directory    = 'fooevents_multiday_events';
		$this->scripts_path        = plugin_dir_url( __FILE__ ) . 'js/';
		$this->styles_path         = plugin_dir_url( __FILE__ ) . 'css/';
		$this->template_path       = plugin_dir_path( __FILE__ ) . 'templates/';
		$this->template_path_theme = get_stylesheet_directory() . '/' . $this->plugin_directory . '/templates/';
		$this->class_path          = plugin_dir_path( __FILE__ ) . 'classes/';
		$this->path                = plugin_dir_path( __FILE__ );
		$this->plugin_file         = $this->path . 'fooevents-multi-day.php';

		if ( ! function_exists( 'get_plugin_data' ) ) {

			require_once ABSPATH . 'wp-admin/includes/plugin.php';

		}

		$this->plugin_data = get_plugin_data( __DIR__ . '/fooevents-multi-day.php', false, false );

	}


}
