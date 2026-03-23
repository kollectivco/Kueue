<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link https://www.fooevents.com
 * @since 1.0.0
 *
 * @package fooevents-pos
 * @subpackage fooevents-pos/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/includes
 */
class FooEventsPOS {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since 1.0.0
	 * @var FooEventsPOS_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since 1.0.0
	 * @var string $fooeventspos The string used to uniquely identify this plugin.
	 */
	protected $fooeventspos;

	/**
	 * The current version of the plugin.
	 *
	 * @since 1.0.0
	 * @var string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 1.0.0
	 * @param string $version The current version of the plugin.
	 */
	public function __construct( $version = '1.0.0' ) {

		$this->version = $version;

		$this->fooeventspos = 'fooevents-pos';

		$this->fooeventspos_load_dependencies();
		$this->fooeventspos_set_locale();
		$this->fooeventspos_define_admin_hooks();
		$this->fooeventspos_define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - FooEventsPOS_Loader. Orchestrates the hooks of the plugin.
	 * - FooEventsPOS_i18n. Defines internationalization functionality.
	 * - FooEventsPOS_Admin. Defines all hooks for the admin area.
	 * - FooEventsPOS_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since 1.0.0
	 */
	private function fooeventspos_load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-fooeventspos-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-fooeventspos-i18n.php';

		/**
		 * The class responsible for deactivating various aspects of the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-fooeventspos-deactivator.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-fooeventspos-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'public/class-fooeventspos-public.php';

		$this->loader = new FooEventsPOS_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the FooEventsPOS_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since 1.0.0
	 */
	private function fooeventspos_set_locale() {

		$plugin_i18n = new FooEventsPOS_I18n();

		$this->loader->fooeventspos_add_action( 'init', $plugin_i18n, 'fooeventspos_load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since 1.0.0
	 */
	private function fooeventspos_define_admin_hooks() {
		$plugin_admin = new FooEventsPOS_Admin( $this->fooeventspos_get_fooeventspos(), $this->fooeventspos_get_version() );

		$this->loader->fooeventspos_add_action( 'init', $plugin_admin, 'fooeventspos_plugin_init', 1 );
		$this->loader->fooeventspos_add_action( 'admin_notices', $plugin_admin, 'fooeventspos_check_woocommerce', 1 );

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		if ( ! ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) ) {
			return;
		}

		$this->loader->fooeventspos_add_filter( 'plugin_action_links_fooevents_pos/fooevents-pos.php', $plugin_admin, 'fooeventspos_action_links' );
		$this->loader->fooeventspos_add_action( 'admin_init', $plugin_admin, 'fooeventspos_register_scripts' );
		$this->loader->fooeventspos_add_action( 'admin_init', $plugin_admin, 'fooeventspos_register_styles' );
		$this->loader->fooeventspos_add_action( 'admin_init', $plugin_admin, 'fooeventspos_register_settings_options' );
		$this->loader->fooeventspos_add_action( 'admin_init', $plugin_admin, 'fooeventspos_add_cashier_role' );
		$this->loader->fooeventspos_add_action( 'admin_init', $plugin_admin, 'fooeventspos_assign_admin_caps' );
		$this->loader->fooeventspos_add_action( 'admin_init', $plugin_admin, 'fooeventspos_register_importer' );
		$this->loader->fooeventspos_add_action( 'init', $plugin_admin, 'fooeventspos_create_pos_page' );
		$this->loader->fooeventspos_add_action( 'admin_menu', $plugin_admin, 'fooeventspos_pos_menu', 1001 );
		$this->loader->fooeventspos_add_action( 'admin_head', $plugin_admin, 'fooeventspos_pos_submenu_target', 1001 );
		$this->loader->fooeventspos_add_action( 'admin_notices', $plugin_admin, 'fooeventspos_analytics_notice' );
		$this->loader->fooeventspos_add_action( 'admin_notices', $plugin_admin, 'fooeventspos_check_upload_folder' );
		$this->loader->fooeventspos_add_action( 'admin_notices', $plugin_admin, 'fooeventspos_fooevents_integration' );
		$this->loader->fooeventspos_add_action( 'admin_notices', $plugin_admin, 'fooeventspos_review_notice' );
		$this->loader->fooeventspos_add_action( 'wp_ajax_dismiss_fooeventspos_review_notice', $plugin_admin, 'fooeventspos_dismiss_review_notice' );
		$this->loader->fooeventspos_add_action( 'woocommerce_product_after_variable_attributes', $plugin_admin, 'fooeventspos_add_variation_options', 10, 3 );
		$this->loader->fooeventspos_add_action( 'woocommerce_save_product_variation', $plugin_admin, 'fooeventspos_save_product_variation', 10, 2 );
		$this->loader->fooeventspos_add_action( 'woocommerce_product_write_panel_tabs', $plugin_admin, 'fooeventspos_add_product_pos_settings_tab', 30 );
		$this->loader->fooeventspos_add_action( 'woocommerce_product_data_panels', $plugin_admin, 'fooeventspos_add_product_pos_settings_options' );
		$this->loader->fooeventspos_add_action( 'woocommerce_process_product_meta', $plugin_admin, 'fooeventspos_process_product_pos_settings_options' );
		$this->loader->fooeventspos_add_action( 'wp_ajax_fooeventspos_get_customers', $plugin_admin, 'fooeventspos_get_customers' );
		$this->loader->fooeventspos_add_action( 'add_meta_boxes', $plugin_admin, 'fooeventspos_add_order_meta_boxes', 10, 2 );


		$this->loader->fooeventspos_add_filter( 'fooeventspos_current_plugin_version', $plugin_admin, 'fooeventspos_current_plugin_version' );

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$add_fooeventspos_admin_actions_filters = true;

		if ( $add_fooeventspos_admin_actions_filters ) {
			$this->loader->fooeventspos_add_action( 'woocommerce_email_enabled_new_order', $plugin_admin, 'fooeventspos_conditionally_send_wc_email', 10, 2 );

			// WooCommerce HPOS.
			$this->loader->fooeventspos_add_filter( 'woocommerce_order_list_table_prepare_items_query_args', $plugin_admin, 'fooeventspos_filter_order_results_hpos' );
			$this->loader->fooeventspos_add_filter( 'manage_woocommerce_page_wc-orders_columns', $plugin_admin, 'fooeventspos_order_column', 20 );
			$this->loader->fooeventspos_add_action( 'woocommerce_order_list_table_restrict_manage_orders', $plugin_admin, 'fooeventspos_filter_orders_payment_method', 9999 );
			$this->loader->fooeventspos_add_action( 'woocommerce_order_list_table_restrict_manage_orders', $plugin_admin, 'fooeventspos_filter_orders_cashier', 9999 );
			$this->loader->fooeventspos_add_action( 'manage_woocommerce_page_wc-orders_custom_column', $plugin_admin, 'fooeventspos_order_column_content', 20, 2 );

			// Traditional CPT-based orders.
			$this->loader->fooeventspos_add_filter( 'parse_query', $plugin_admin, 'fooeventspos_filter_order_results' );
			$this->loader->fooeventspos_add_filter( 'manage_edit-shop_order_columns', $plugin_admin, 'fooeventspos_order_column', 20 );
			$this->loader->fooeventspos_add_action( 'restrict_manage_posts', $plugin_admin, 'fooeventspos_filter_orders_payment_method', 9999 );
			$this->loader->fooeventspos_add_action( 'restrict_manage_posts', $plugin_admin, 'fooeventspos_filter_orders_cashier', 9999 );
			$this->loader->fooeventspos_add_action( 'manage_shop_order_posts_custom_column', $plugin_admin, 'fooeventspos_order_column_content', 20, 2 );

			$this->loader->fooeventspos_add_action( 'woocommerce_admin_order_data_after_order_details', $plugin_admin, 'fooeventspos_order_meta_general' );
			$this->loader->fooeventspos_add_action( 'woocommerce_process_shop_order_meta', $plugin_admin, 'fooeventspos_order_meta_save_cashier' );
			$this->loader->fooeventspos_add_action( 'woocommerce_checkout_create_order', $plugin_admin, 'fooeventspos_before_checkout_create_order', 20, 2 );
			$this->loader->fooeventspos_add_action( 'woocommerce_blocks_checkout_order_processed', $plugin_admin, 'fooeventspos_before_block_checkout_create_order', 20 );
			$this->loader->fooeventspos_add_action( 'woocommerce_store_api_checkout_order_processed', $plugin_admin, 'fooeventspos_before_block_checkout_create_order', 20 );
			$this->loader->fooeventspos_add_filter( 'woocommerce_available_payment_gateways', $plugin_admin, 'fooeventspos_conditional_payment_gateways', 10, 1 );
			$this->loader->fooeventspos_add_filter( 'woocommerce_payment_gateways', $plugin_admin, 'fooeventspos_add_payment_options_class' );
			$this->loader->fooeventspos_add_filter( 'woocommerce_prevent_admin_access', $plugin_admin, 'fooeventspos_cashier_admin_access', 20, 1 );
			$this->loader->fooeventspos_add_filter( 'woocommerce_login_redirect', $plugin_admin, 'fooeventspos_cashier_redirect', 9999, 2 );
			$this->loader->fooeventspos_add_action( 'woocommerce_checkout_update_order_meta', $plugin_admin, 'fooeventspos_update_order_meta' );
			$this->loader->fooeventspos_add_action( 'fooeventspos_update_order_square_fees', $plugin_admin, 'fooeventspos_do_update_order_square_fees', 10, 1 );

			// Vendors.
			$this->loader->fooeventspos_add_action( 'admin_init', 'PAnD', 'init' );

			// Decimal quantities.
			if ( 'yes' === get_option( 'globalFooEventsPOSProductsUseDecimalQuantities', '' ) ) {
				$this->loader->fooeventspos_add_action( 'init', $plugin_admin, 'fooeventspos_remove_woocommerce_stock_amount_filters', PHP_INT_MAX );

				$this->loader->fooeventspos_add_filter( 'woocommerce_get_settings_products', $plugin_admin, 'fooeventspos_woocommerce_products_decimal_quantity_settings', 10, 2 );
				$this->loader->fooeventspos_add_filter( 'woocommerce_admin_settings_sanitize_option_fooeventspos_products_default_minimum_cart_quantity', $plugin_admin, 'fooeventspos_admin_settings_sanitize_option_fooeventspos_products_default_minimum_cart_quantity_step', 10, 3 );
				$this->loader->fooeventspos_add_filter( 'woocommerce_admin_settings_sanitize_option_fooeventspos_products_default_cart_quantity_step', $plugin_admin, 'fooeventspos_admin_settings_sanitize_option_fooeventspos_products_default_minimum_cart_quantity_step', 10, 3 );
				$this->loader->fooeventspos_add_filter( 'woocommerce_quantity_input_min', $plugin_admin, 'fooeventspos_product_quantity_input_min', 10, 2 );
				$this->loader->fooeventspos_add_filter( 'woocommerce_quantity_input_step', $plugin_admin, 'fooeventspos_product_quantity_input_step', 10, 2 );
				$this->loader->fooeventspos_add_filter( 'woocommerce_quantity_input_args', $plugin_admin, 'fooeventspos_product_quantity_input_args', 10, 2 );
				$this->loader->fooeventspos_add_filter( 'woocommerce_available_variation', $plugin_admin, 'fooeventspos_product_available_variation', 10, 3 );
				$this->loader->fooeventspos_add_filter( 'wc_add_to_cart_message_html', $plugin_admin, 'fooeventspos_add_to_cart_message_html', 10, 3 );
				$this->loader->fooeventspos_add_filter( 'woocommerce_add_to_cart_quantity', $plugin_admin, 'fooeventspos_add_to_cart_quantity', 10, 2 );

				$this->loader->fooeventspos_add_action( 'woocommerce_product_options_inventory_product_data', $plugin_admin, 'fooeventspos_woocommerce_product_cart_minimum_step_field' );
				$this->loader->fooeventspos_add_action( 'woocommerce_process_product_meta', $plugin_admin, 'fooeventspos_woocommerce_process_product_cart_minimum_step_field' );
			}
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since 1.0.0
	 */
	private function fooeventspos_define_public_hooks() {
		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		if ( ! ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) ) {
			return;
		}

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugin_public = new FooEventsPOS_Public( $this->fooeventspos_get_fooeventspos(), $this->fooeventspos_get_version() );

		$this->loader->fooeventspos_add_filter( 'template_include', $plugin_public, 'fooeventspos_pos_page_template', 1001 );

		$this->loader->fooeventspos_add_action( 'init', $plugin_public, 'fooeventspos_rewrite', 1001 );

		if ( is_plugin_active( 'oxygen/functions.php' ) || is_plugin_active_for_network( 'oxygen/functions.php' ) ) {
			$this->loader->fooeventspos_add_action( 'oxygen_enqueue_frontend_scripts', $plugin_public, 'fooeventspos_set_fooeventspos_scripts_and_styles', 1001 );
		} else {
			$this->loader->fooeventspos_add_action( 'wp_enqueue_scripts', $plugin_public, 'fooeventspos_set_fooeventspos_scripts_and_styles', 1001 );
		}

		$this->loader->fooeventspos_add_action( 'wp_head', $plugin_public, 'fooeventspos_start_wp_head_footer_buffer', 0 );
		$this->loader->fooeventspos_add_action( 'wp_head', $plugin_public, 'fooeventspos_end_wp_head_footer_buffer', PHP_INT_MAX );
		$this->loader->fooeventspos_add_action( 'wp_footer', $plugin_public, 'fooeventspos_start_wp_head_footer_buffer', 0 );
		$this->loader->fooeventspos_add_action( 'wp_footer', $plugin_public, 'fooeventspos_end_wp_head_footer_buffer', PHP_INT_MAX );

		$this->loader->fooeventspos_add_filter( 'show_admin_bar', $plugin_public, 'fooeventspos_hide_admin_bar' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_run() {
		$this->loader->fooeventspos_run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since 1.0.0
	 * @return string The name of the plugin.
	 */
	public function fooeventspos_get_fooeventspos() {
		return $this->fooeventspos;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since 1.0.0
	 * @return FooEventsPOS_Loader Orchestrates the hooks of the plugin.
	 */
	public function fooeventspos_get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since 1.0.0
	 * @return string The version number of the plugin.
	 */
	public function fooeventspos_get_version() {
		return $this->version;
	}
}
