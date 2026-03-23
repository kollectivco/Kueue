<?php
/**
 * FooEventsPOS_Admin class
 *
 * @link https://www.fooevents.com
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * The admin-specific functionality of the plugin.
 *
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */
class FooEventsPOS_Admin {
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
	 * The FooEvents POS phrases helper.
	 *
	 * @since 1.0.0
	 * @var array $fooeventspos_phrases The current phrases helper array.
	 */
	private $fooeventspos_phrases;

	/**
	 * REST API
	 *
	 * @since 1.0.0
	 * @var FooEventsPOS_REST_API $class_rest_api The current REST API.
	 */
	private $class_rest_api;

	/**
	 * XML-RPC API
	 *
	 * @since 1.0.0
	 * @var FooEventsPOS_XMLRPC $class_xmlrpc The current XML-RPC API.
	 */
	private $class_xmlrpc;

	/**
	 * The Analytics class.
	 *
	 * @since 1.0.0
	 * @var FooEventsPOS_Analytics $class_analytics The current Analytics class.
	 */
	private $class_analytics;

	/**
	 * The Payments class.
	 *
	 * @since 1.8.0
	 * @var FooEventsPOS_Payments $class_payments The current Payments class.
	 */
	private $class_payments;

	/**
	 * FooEvents POS payment method IDs.
	 *
	 * @since 1.0.0
	 * @var array $fooeventspos_payment_method_ids An array of the current FooEvents POS payment method IDs.
	 */
	private $fooeventspos_payment_method_ids;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $fooeventspos The name of this plugin.
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

		// FooEvents POS phrases.
		require plugin_dir_path( __FILE__ ) . 'helpers/fooeventspos-phrases-helper.php';
		$this->fooeventspos_phrases = $fooeventspos_phrases;

		// API helper methods.
		require_once plugin_dir_path( __FILE__ ) . 'helpers/fooeventspos-api-helper.php';

		// FooEventsPOS_REST_API class.
		require_once plugin_dir_path( __FILE__ ) . 'class-fooeventspos-rest-api.php';
		$this->class_rest_api = new FooEventsPOS_REST_API();

		// FooEventsPOS_XMLRPC class.
		require_once plugin_dir_path( __FILE__ ) . 'class-fooeventspos-xmlrpc.php';
		$this->class_xmlrpc = new FooEventsPOS_XMLRPC();

		// FooEventsPOS_XMLRPC functions.
		require_once plugin_dir_path( __FILE__ ) . 'fooeventspos-xmlrpc-functions.php';

		// FooEvents_POS_Update_Helper class.
		require_once plugin_dir_path( __FILE__ ) . 'class-fooevents-pos-update-helper.php';
		new FooEvents_POS_Update_Helper();

		// FooEventsPOS_FooEvents_Integration class.
		require_once plugin_dir_path( __FILE__ ) . 'class-fooeventspos-fooevents-integration.php';
		new FooEventsPOS_FooEvents_Integration();

		// FooEventsPOS_FooEvents_XMLRPC functions.
		require_once plugin_dir_path( __FILE__ ) . 'fooeventspos-fooevents-xmlrpc-functions.php';

		// FooEvents_POS_Integration class.
		require_once plugin_dir_path( __FILE__ ) . 'class-fooevents-pos-integration.php';
		new FooEvents_POS_Integration();

		// FooEvents_POS_Integration functions.
		require_once plugin_dir_path( __FILE__ ) . 'fooevents-pos-integration-functions.php';

		// FooEventsPOS_Analytics class.
		require_once plugin_dir_path( __FILE__ ) . 'apps/wc_analytics/class-fooeventspos-analytics.php';

		// FooEventsPOS_Payments class.
		require_once plugin_dir_path( __FILE__ ) . 'class-fooeventspos-payments.php';

		// Library that persists the dismissal of admin notices across pages.
		require plugin_dir_path( __FILE__ ) . 'helpers/vendor/persist-admin-notices-dismissal/persist-admin-notices-dismissal.php';
	}

	/**
	 * Initialize the plugin
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_plugin_init() {

		if ( ! function_exists( 'WP_Filesystem' ) ) {

			require_once ABSPATH . '/wp-admin/includes/file.php';

		}

		WP_Filesystem();

		global $wp_filesystem;

		// FooEvents POS salt.
		if ( false === get_option( 'globalFooEventsPOSSalt' ) ) {
			$salt = fooeventspos_generate_idempotency_string();

			update_option( 'globalFooEventsPOSSalt', $salt );
		}

		// FooEventsPOS_Analytics class.
		if ( 'yes' === get_option( 'globalFooEventsPOSWooCommerceAnalytics', 'yes' ) ) {
			$this->class_analytics = new FooEventsPOS_Analytics();
		}

		$this->fooeventspos_add_fooeventspos_db_tables();

		$wp_upload_dir           = wp_upload_dir();
		$fooeventspos_upload_path = $wp_upload_dir['basedir'] . '/fooeventspos/';

		if ( ! file_exists( $fooeventspos_upload_path ) && $wp_filesystem->is_writable( $wp_upload_dir['basedir'] ) ) {

			if ( $wp_filesystem->mkdir( $fooeventspos_upload_path, 0755 ) ) {

				$assets_path = WP_PLUGIN_DIR . '/fooevents_pos/public/build/';

				$icon_sizes = fooeventspos_get_app_icon_sizes();

				foreach ( $icon_sizes as $icon => $size ) {
					copy( $assets_path . $icon, $fooeventspos_upload_path . $icon );
				}

				copy( $assets_path . 'fooeventspos.webmanifest', $fooeventspos_upload_path . 'fooeventspos.webmanifest' );
			} else {

				$this->fooeventspos_output_notices( array( sprintf( $this->fooeventspos_phrases['notice_create_upload_directory_failed'], $fooeventspos_upload_path ) ) );

			}
		}

		$this->class_payments = new FooEventsPOS_Payments();
		$this->class_payments->fooeventspos_register_fooeventspos_payment_post_type();
	}

	/**
	 * Add additional database tables.
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_add_fooeventspos_db_tables() {

		// Square Terminal Devices database table.
		$fooeventspos_db_square_devices = get_option( 'fooeventspos_db_square_devices', '' );

		if ( (string) $this->version !== $fooeventspos_db_square_devices ) {
			$this->fooeventspos_add_fooeventspos_db_square_devices();
		}

		// Square Terminal Checkouts database table.
		$fooeventspos_db_square_checkouts = get_option( 'fooeventspos_db_square_checkouts', '' );

		if ( (string) $this->version !== $fooeventspos_db_square_checkouts ) {
			$this->fooeventspos_add_fooeventspos_db_square_checkouts();
		}

		// Square Terminal Refunds database table.
		$fooeventspos_db_square_refunds = get_option( 'fooeventspos_db_square_refunds', '' );

		if ( (string) $this->version !== $fooeventspos_db_square_refunds ) {
			$this->fooeventspos_add_fooeventspos_db_square_refunds();
		}
	}

	/**
	 * Add database table for Square devices.
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_add_fooeventspos_db_square_devices() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'fooeventspos_square_devices';

		$sql = "CREATE TABLE $table_name (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            device_code_id VARCHAR(50) NOT NULL,
            device_id VARCHAR(50) NOT NULL,
            code VARCHAR(50) NOT NULL,
            location_id VARCHAR(50) NOT NULL,
            pair_by VARCHAR(50) NOT NULL,
            status VARCHAR(50) NOT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'fooeventspos_db_square_devices', (string) $this->version );
	}

	/**
	 * Add database table for Square Checkouts.
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_add_fooeventspos_db_square_checkouts() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'fooeventspos_square_checkouts';

		$sql = "CREATE TABLE $table_name (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            checkout_id VARCHAR(50) NOT NULL,
            amount VARCHAR(50) NOT NULL,
            currency VARCHAR(10) NOT NULL,
            device_id VARCHAR(50) NOT NULL,
            deadline VARCHAR(50) NOT NULL,
            payment_id VARCHAR(50) NOT NULL DEFAULT '',
            order_id VARCHAR(50) NOT NULL DEFAULT '',
			status VARCHAR(50) NOT NULL,
			created_at VARCHAR(50) NOT NULL,
			updated_at VARCHAR(50) NOT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'fooeventspos_db_square_checkouts', (string) $this->version );
	}

	/**
	 * Add database table for Square Refunds.
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_add_fooeventspos_db_square_refunds() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'fooeventspos_square_refunds';

		$sql = "CREATE TABLE $table_name (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            refund_id VARCHAR(50) NOT NULL,
            amount VARCHAR(50) NOT NULL,
            currency VARCHAR(10) NOT NULL,
            device_id VARCHAR(50) NOT NULL,
            deadline VARCHAR(50) NOT NULL,
            payment_id VARCHAR(50) NOT NULL DEFAULT '',
            order_id VARCHAR(50) NOT NULL DEFAULT '',
			status VARCHAR(50) NOT NULL,
			created_at VARCHAR(50) NOT NULL,
			updated_at VARCHAR(50) NOT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'fooeventspos_db_square_refunds', (string) $this->version );
	}

	/**
	 * Register admin scripts
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_register_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-tooltip' );

		if ( isset( $_GET['page'] ) && 'fooeventspos-settings' === $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_enqueue_media();
			wp_enqueue_script( 'fooeventspos-select', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), '4.0.12', true );
			wp_localize_script(
				'fooeventspos-admin-settings-trials',
				'fooeventsposTrialsVars',
				array(
					'ajaxURL'       => admin_url( 'admin-ajax.php' ),
					'nonce'         => wp_create_nonce( 'fooeventspos_trial_nonce' ),
					'activeLabel'   => $this->fooeventspos_phrases['label_active'],
					'inactiveLabel' => $this->fooeventspos_phrases['label_inactive'],
				)
			);
		}

		wp_enqueue_script( 'fooeventspos-scripts', plugin_dir_url( __FILE__ ) . 'js/fooeventspos-admin.js', array( 'jquery', 'jquery-ui-tooltip' ), $this->version, true );
		wp_localize_script( 'fooeventspos-scripts', 'fooeventsposPhrases', $this->fooeventspos_phrases );

		if ( ! class_exists( Automattic\WooCommerce\Admin\Loader::class ) ) {
			return;
		}

		// WooCommerce analytics dependencies.
		if ( 'yes' === get_option( 'globalFooEventsPOSWooCommerceAnalytics', 'yes' ) ) {
			$wc_analytics_script_asset_path = plugin_dir_path( __FILE__ ) . 'apps/wc_analytics/build/index.asset.php';

			$wc_analytics_script_asset = file_exists( $wc_analytics_script_asset_path )
				? require $wc_analytics_script_asset_path
				: array(
					'dependencies' => array(),
					'version'      => $this->version,
				);

			$wc_analytics_script_url = plugin_dir_url( __FILE__ ) . 'apps/wc_analytics/build/index.js';

			wp_enqueue_script( 'fooeventspos-wc-analytics', $wc_analytics_script_url, $wc_analytics_script_asset['dependencies'], $wc_analytics_script_asset['version'], true );
			wp_localize_script( 'fooeventspos-wc-analytics', 'fooeventsposPhrases', $this->fooeventspos_phrases );
		}
	}

	/**
	 * Register admin styles
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_register_styles() {

		if ( isset( $_GET['page'] ) && 'fooeventspos-settings' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_enqueue_style( 'fooeventspos-select', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), '4.0.12' );
		}

		wp_enqueue_style( 'fooeventspos-styles', plugin_dir_url( __FILE__ ) . 'css/fooeventspos-admin.css', array(), $this->version );
	}

	/**
	 * Register settings
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_register_settings_options() {
		register_setting( 'fooeventspos-settings-users', 'globalFooEventsPOSCustomerUserRole' );
		register_setting( 'fooeventspos-settings-users', 'globalFooEventsPOSDefaultCustomer' );

		register_setting( 'fooeventspos-settings-products', 'globalFooEventsPOSProductsToDisplay' );
		register_setting( 'fooeventspos-settings-products', 'globalFooEventsPOSProductCategories' );
		register_setting( 'fooeventspos-settings-products', 'globalFooEventsPOSProductsStatus' );
		register_setting( 'fooeventspos-settings-products', 'globalFooEventsPOSProductsOnlyInStock' );
		register_setting( 'fooeventspos-settings-products', 'globalFooEventsPOSCheckStockAvailability' );
		register_setting( 'fooeventspos-settings-products', 'globalFooEventsPOSProductsPerPage' );
		register_setting( 'fooeventspos-settings-products', 'globalFooEventsPOSProductsShowAttributeLabels' );
		register_setting( 'fooeventspos-settings-products', 'globalFooEventsPOSProductsLoadImages' );
		register_setting( 'fooeventspos-settings-products', 'globalFooEventsPOSProductsUseDecimalQuantities' );

		register_setting( 'fooeventspos-settings-orders', 'globalFooEventsPOSOnlyLoadPOSOrders' );
		register_setting( 'fooeventspos-settings-orders', 'globalFooEventsPOSOrderLoadStatuses' );
		register_setting( 'fooeventspos-settings-orders', 'globalFooEventsPOSOrdersToLoad' );
		register_setting( 'fooeventspos-settings-orders', 'globalFooEventsPOSFetchOrderNotes' );
		register_setting( 'fooeventspos-settings-orders', 'globalFooEventsPOSOrderSubmitStatuses' );
		register_setting( 'fooeventspos-settings-orders', 'globalFooEventsPOSDefaultOrderStatus' );
		register_setting( 'fooeventspos-settings-orders', 'globalFooEventsPOSOrderIncompleteStatuses' );
		register_setting( 'fooeventspos-settings-orders', 'globalFooEventsPOSDisableNewOrderEmails' );
		register_setting( 'fooeventspos-settings-orders', 'globalFooEventsPOSNewOrderAlertStatuses' );
		register_setting( 'fooeventspos-settings-orders', 'globalFooEventsPOSNewOrderAlertShippingMethods' );

		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSStoreLogoURL' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSStoreName' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSHeaderContent' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSReceiptTitle' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSOrderNumberPrefix' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSProductColumnTitle' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSQuantityColumnTitle' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSPriceColumnTitle' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSSubtotalColumnTitle' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSShowSKU' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSShowGUID' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSInclusiveAbbreviation' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSExclusiveAbbreviation' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSDiscountsTitle' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSRefundsTitle' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSTaxTitle' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSTotalTitle' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSPaymentMethodTitle' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSShowBillingAddress' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSBillingAddressTitle' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSShowShippingAddress' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSShippingAddressTitle' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSFooterContent' );
		register_setting( 'fooeventspos-settings-receipts', 'globalFooEventsPOSReceiptShowLogo' );

		register_setting( 'fooeventspos-settings-integration', 'globalFooEventsPOSSquareApplicationID' );
		register_setting( 'fooeventspos-settings-integration', 'globalFooEventsPOSSquareAccessToken' );
		register_setting( 'fooeventspos-settings-integration', 'globalFooEventsPOSStripePublishableKey' );
		register_setting( 'fooeventspos-settings-integration', 'globalFooEventsPOSStripeSecretKey' );

		register_setting( 'fooeventspos-settings-analytics', 'globalFooEventsPOSWooCommerceAnalytics' );
		register_setting( 'fooeventspos-settings-analytics', 'globalFooEventsPOSAnalyticsOptIn' );
	}

	/**
	 * Assign admin capabilities
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_assign_admin_caps() {
		$role = get_role( 'administrator' );
		$role->add_cap( 'publish_fooeventspos' );

		// Payments.
		$role->add_cap( 'edit_fooeventspos_payment' );
		$role->add_cap( 'read_fooeventspos_payment' );
		$role->add_cap( 'delete_fooeventspos_payment' );

		$role->add_cap( 'edit_fooeventspos_payments' );
		$role->add_cap( 'edit_others_fooeventspos_payments' );
		$role->add_cap( 'delete_fooeventspos_payments' );
		$role->add_cap( 'read_private_fooeventspos_payment' );

		$role->add_cap( 'read_fooeventspos_payment' );
		$role->add_cap( 'delete_private_fooeventspos_payments' );
		$role->add_cap( 'delete_published_fooeventspos_payments' );
		$role->add_cap( 'delete_others_fooeventspos_payments' );
		$role->add_cap( 'edit_private_fooeventspos_payment' );
		$role->add_cap( 'edit_published_fooeventspos_payments' );

		$role = get_role( 'fooeventspos_cashier' );

		if ( null === $role ) {
			update_option( 'fooeventspos_cashier_role_version', '' );

			$this->fooeventspos_add_cashier_role();

			$role = get_role( 'fooeventspos_cashier' );
		}

		$role->add_cap( 'publish_fooeventspos' );
		$role->add_cap( 'read_private_pages' );
	}

	/**
	 * Create custom CRA page if not exists
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_create_pos_page() {
		global $wpdb;

		$fooeventspos_pos_page_post_id = (int) get_option( 'fooeventspos_pos_page', 0 );

		$fooeventspos_pos_page_title = $this->fooeventspos_phrases['title_fooevents_pos'];
		$pos_page_slug           = 'fooeventspos';

		$fooeventspos_pos_page_title = FooEvents_POS_Integration::fooeventspos_get_app_title();
		$pos_page_slug           = FooEvents_POS_Integration::fooeventspos_get_app_slug();

		$fooeventspos_pos_page_exists = false;

		if ( $fooeventspos_pos_page_post_id > 0 ) {
			$fetched_pos_page_post_id = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT ID FROM $wpdb->posts WHERE ID=%s",
					$fooeventspos_pos_page_post_id
				)
			);

			$fooeventspos_pos_page_exists = (int) $fetched_pos_page_post_id === $fooeventspos_pos_page_post_id;
		}

		// POS page args.
		$fooeventspos_pos_page_args = array(
			'post_title'   => $fooeventspos_pos_page_title,
			'post_name'    => $pos_page_slug,
			'post_content' => '',
			'post_status'  => 'private',
			'post_type'    => 'page',
		);

		if ( false === $fooeventspos_pos_page_exists ) {
			$fooeventspos_pos_page_post = get_page_by_path( $pos_page_slug );

			if ( null === $fooeventspos_pos_page_post ) {
				// Insert the POS page post into the database.
				$fooeventspos_pos_page_post_id = wp_insert_post( $fooeventspos_pos_page_args, true, false );
			} else {
				// Use POS page post ID of existing page.
				$fooeventspos_pos_page_post_id = (int) $fooeventspos_pos_page_post->ID;
			}

			if ( is_wp_error( $fooeventspos_pos_page_post_id ) ) {
				$fooeventspos_pos_page_post_id = 0;
			}
		}

		update_option( 'fooeventspos_pos_page', (int) $fooeventspos_pos_page_post_id );
	}

	/**
	 * Redirects users from the 'fooeventspos' page to the settings page
	 * if they don't have a valid connected or trial plan.
	 *
	 * This runs only on the frontend and for users with the proper capability.
	 */
	public function fooeventspos_redirect_pos_slug_to_settings() {
		// Only run on the frontend.
		if ( is_admin() ) {
			return;
		}

		$pos_page_slug = 'fooeventspos';

		$pos_page_slug = FooEvents_POS_Integration::fooeventspos_get_app_slug();

		// Check if current page is $pos_page_slug and if the user has the required capability.
		if ( is_page( $pos_page_slug ) && current_user_can( 'manage_woocommerce' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			// Check if user has access to POS.
			$response        = $this->fooeventspos_check_url_account_status();
			$fooeventspos_status = $response['status'] ?? '';
			// Redirect back to Settings if the domain is not associated with an active plan or trial.
			// Static status indicates the account check API is not accesable from this server, allow POS app to perform secondary check.
			if ( 'connected-plan' !== $fooeventspos_status && 'active-trial' !== $fooeventspos_status && 'static' !== $fooeventspos_status ) {
				wp_safe_redirect( admin_url( 'admin.php?page=fooeventspos-settings&source=redirect' ) );

				exit;
			}
		}
	}

	/**
	 * Adds the FooEvents POS import sub menu page
	 */
	public function fooeventspos_add_fooeventspos_import_page() {

		$fooeventspos_phrases = $this->fooeventspos_phrases;

		$order_statuses = fooeventspos_get_all_order_statuses();

		require_once plugin_dir_path( __FILE__ ) . 'class-fooevents-pos-integration.php';
		require_once plugin_dir_path( __FILE__ ) . 'helpers/fooeventspos-api-helper.php';
		require_once plugin_dir_path( __FILE__ ) . 'templates/fooeventspos-import.php';
	}

	/**
	 * Redirect to the import sub menu page
	 */
	public function fooeventspos_redirect_to_import() {

		wp_safe_redirect( admin_url( 'admin.php?page=fooeventspos-import' ) );

		exit;
	}

	/**
	 * Add submenu to WooCommerce admin menu
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_pos_menu() {
		add_menu_page(
			null,
			$this->fooeventspos_phrases['title_fooeventspos'],
			'manage_options',
			'fooeventspos-settings',
			array( $this, 'fooeventspos_settings_page' ),
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj48c3ZnIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIHZpZXdCb3g9IjAgMCA1NCAzNCIgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWw6c3BhY2U9InByZXNlcnZlIiB4bWxuczpzZXJpZj0iaHR0cDovL3d3dy5zZXJpZi5jb20vIiBzdHlsZT0iZmlsbC1ydWxlOmV2ZW5vZGQ7Y2xpcC1ydWxlOmV2ZW5vZGQ7c3Ryb2tlLWxpbmVqb2luOnJvdW5kO3N0cm9rZS1taXRlcmxpbWl0OjI7Ij48Zz48cGF0aCBkPSJNNDguNjk2LDYuNTAzYzAuMzgzLC0xLjI3MyAxLjcyNywtMS45OTUgMi45OTksLTEuNjEyYzEuMjcyLDAuMzgzIDEuOTk0LDEuNzI3IDEuNjExLDIuOTk5bC01LjAxLDE2LjY1Yy0wLjM4MywxLjI3MiAtMS43MjcsMS45OTQgLTIuOTk5LDEuNjExYy0xLjI3MiwtMC4zODMgLTEuOTk0LC0xLjcyNyAtMS42MTEsLTIuOTk5bDUuMDEsLTE2LjY0OVoiIHN0eWxlPSJmaWxsOiNhMGE1YWE7Ii8+PHBhdGggZD0iTTUzLjQ3MSw3LjAxOGMwLC0xLjMyNSAtMS4wNzcsLTIuNDAyIC0yLjQwMiwtMi40MDJsLTMzLjgzMSwwYy0xLjMyNiwwIC0yLjQwMiwxLjA3NyAtMi40MDIsMi40MDJjMCwxLjMyNiAxLjA3NiwyLjQwMyAyLjQwMiwyLjQwM2wzMy44MzEsMGMxLjMyNSwwIDIuNDAyLC0xLjA3NyAyLjQwMiwtMi40MDNaIiBzdHlsZT0iZmlsbDojYTBhNWFhOyIvPjxwYXRoIGQ9Ik00OC41OTgsMjMuMjk3YzAuMzUsLTEuMjgyIC0xLjg5OSwtMS43NjcgLTMuMjI4LC0xLjc2N2wtMjIuOTMyLDBjLTEuMzI5LDAgLTIuNDY4LDEuMDggLTIuNDA4LDIuNDA4YzAuMDU3LDEuMjQ2IDAuNDk2LDIuMTg4IDIuNDA4LDIuNDA3bDIyLjkzMiwwYzIuNDksLTAuMDA0IDIuNzk5LC0xLjQ3MyAzLjIyOCwtMy4wNDhaIiBzdHlsZT0iZmlsbDojYTBhNWFhOyIvPjxwYXRoIGQ9Ik0xOS41MSw2LjUyNGMtMC4zOTEsLTEuMjk3IC0xLjc2MiwtMi4wMzQgLTMuMDU5LC0xLjY0M2MtMS4yOTgsMC4zOSAtMi4wMzUsMS43NjEgLTEuNjQ0LDMuMDU5bDQuOTYxLDE2LjQ4NWMwLjM5MSwxLjI5OCAxLjc2MSwyLjAzNCAzLjA1OSwxLjY0NGMxLjI5OCwtMC4zOTEgMi4wMzQsLTEuNzYxIDEuNjQ0LC0zLjA1OWwtNC45NjEsLTE2LjQ4NloiIHN0eWxlPSJmaWxsOiNhMGE1YWE7Ii8+PHBhdGggZD0iTTguOTg4LDIuNDA1YzAsLTEuMzI3IDAuMDQ3LC0yLjQyNCAtMS42NSwtMi40MDVsLTYuNjU1LDBjLTAuOTExLDAgLTAuOTExLDQuODExIDAsNC44MTFsNi42NTUsMGMwLjkxMSwwIDEuNjUsLTEuMDc4IDEuNjUsLTIuNDA2WiIgc3R5bGU9ImZpbGw6I2EwYTVhYTsiLz48cGF0aCBkPSJNMTYuMTQ2LDMzLjg0N2MtMC44NzQsLTAuMTUyIC0xLjYzMiwtMC43OCAtMS45MDQsLTEuNjg2bC04LjczOSwtMjkuMDM4Yy0wLjM4NCwtMS4yNzggMC4xOCwtMi41ODkgMS40NTcsLTIuOTc0YzEuMjc4LC0wLjM4NCAyLjc4OCwwLjMwMyAzLjE3MiwxLjU4MWw3LjMwOSwyNC4yODVjMS4wMzMsMy4wMzYgMS41OTUsMy4wNjQgNS43MTksMy4wNjljMC4wMjYsMCAyNC41NjEsMC4wMTEgMjQuNTYxLDAuMDExYzAuOTYzLDAgMC45NjMsNC44NCAwLDQuODRjMCwwIC0zMS4zOTQsLTAuMDMxIC0zMS41NzUsLTAuMDg4WiIgc3R5bGU9ImZpbGw6I2EwYTVhYTsiLz48L2c+PC9zdmc+',
			'55.8'
		);

		add_submenu_page( 'fooeventspos-settings', $this->fooeventspos_phrases['title_fooeventspos_settings'], $this->fooeventspos_phrases['menu_settings'], 'manage_options', 'fooeventspos-settings', array( $this, 'fooeventspos_settings_page' ) );

		$pos_page_slug = 'fooeventspos';

		$pos_page_slug = FooEvents_POS_Integration::fooeventspos_get_app_slug();

		add_submenu_page( 'fooeventspos-settings', $this->fooeventspos_phrases['menu_point_of_sale'], $this->fooeventspos_phrases['menu_point_of_sale'], 'manage_options', $pos_page_slug, array( $this, 'fooeventspos_pos_redirect' ) );
		add_submenu_page( 'fooeventspos-settings', $this->fooeventspos_phrases['title_fooeventspos_import'], $this->fooeventspos_phrases['menu_import'], 'manage_options', 'fooeventspos-import', array( $this, 'fooeventspos_add_fooeventspos_import_page' ) );
		add_submenu_page( 'fooeventspos-settings', $this->fooeventspos_phrases['title_fooeventspos_pos_payments'], $this->fooeventspos_phrases['title_fooeventspos_pos_payments'], 'manage_options', 'edit.php?post_type=fooeventspos_payment', false );
	}

	/**
	 * Redirect to the FooEvents POS Point of Sale page
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_pos_redirect() {

		$pos_page_slug = 'fooeventspos';

		$pos_page_slug = FooEvents_POS_Integration::fooeventspos_get_app_slug();

		wp_safe_redirect( esc_url( home_url( '/' . $pos_page_slug . '/' ) ) );

		exit;
	}

	/**
	 * Add 'online' order meta when completing checkout online.
	 *
	 * @since 1.4.0
	 * @param WC_Order $wc_order The WooCommerce order.
	 * @param array    $data The order data.
	 */
	public function fooeventspos_before_checkout_create_order( $wc_order, $data ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		$this->fooeventspos_do_before_checkout_create_order( $wc_order );
	}

	/**
	 * Add 'online' order meta when completing block checkout online.
	 *
	 * @since 1.7.5
	 * @param WC_Order $wc_order The WooCommerce order.
	 */
	public function fooeventspos_before_block_checkout_create_order( $wc_order ) {
		$this->fooeventspos_do_before_checkout_create_order( $wc_order );
	}

	/**
	 * Add 'online' order meta when completing checkout online.
	 *
	 * @since 1.7.5
	 * @param WC_Order $wc_order The WooCommerce order.
	 */
	public function fooeventspos_do_before_checkout_create_order( $wc_order ) {
		$wc_order->update_meta_data( '_fooeventspos_order_source', 'online' );
		$wc_order->update_meta_data( '_fooeventspos_payment_method', '' );
	}

	/**
	 * Save the cashier selection on the WooCommerce order details screen.
	 *
	 * @since 1.0.0
	 * @param int $order_id The order ID.
	 */
	public function fooeventspos_order_meta_save_cashier( $order_id ) {
		if ( isset( $_POST['_fooeventspos_order_cashier_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_fooeventspos_order_cashier_nonce'] ) ), '_fooeventspos_save_order_cashier_' . $order_id ) ) {
			if ( isset( $_POST['_fooeventspos_user_id'] ) ) {
				$wc_order = wc_get_order( $order_id );
				$wc_order->update_meta_data( '_fooeventspos_user_id', sanitize_text_field( wp_unslash( $_POST['_fooeventspos_user_id'] ) ) );
				$wc_order->save();
			}
		}
	}

	/**
	 * Set the WooCommerce admin submenu target
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_pos_submenu_target() {
		?>
		<script type="text/javascript">
			jQuery(document).ready( function( $) {
				$( '#toplevel_page_fooeventspos-settings ul li:nth-child(3) a' ).attr( 'target', '_blank' );
			} );
		</script>
		<?php
	}

	/**
	 * Adds analytics opt in notification
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_analytics_notice() {

		// Do not show the notice if it has been dismissed.
		if ( ! PAnD::is_admin_notice_active( 'disable-fooeventspos-analytics-notice-60' ) || 'yes' === get_option( 'globalFooEventsPOSAnalyticsOptIn', '' ) ) {
			return;
		}

		if ( isset( $_GET['page'] ) && 'fooeventspos-settings' === $_GET['page'] && ! isset( $_GET['optin'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			echo '<div data-dismissible="disable-fooeventspos-analytics-notice-60" class="notice notice-info is-dismissible fooeventspos-notice"><p>' . sprintf( esc_html( $this->fooeventspos_phrases['notice_order_analytics_optin'] ), '<a href="' . esc_attr( admin_url( 'admin.php?page=fooeventspos-settings&tab=general&optin=yes' ) ) . '">', '</a>' ) . '</p></div>';
		}
	}

	/**
	 * Maybe display notice for minimum FooEvents plugin version when both FooEvents POS and FooEvents are enabled.
	 *
	 * @since 1.5.0
	 */
	public function fooeventspos_fooevents_integration() {
		// Do not show the notice if it has been dismissed.
		if ( ! PAnD::is_admin_notice_active( 'disable-fooevents-version-notice-5' ) ) {
			return;
		}

		if ( ( is_plugin_active( 'fooevents/fooevents.php' ) || is_plugin_active_for_network( 'fooevents/fooevents.php' ) ) && class_exists( 'FooEvents_Config' ) ) {
			$fooevents_config = new FooEvents_Config();

			if ( version_compare( $fooevents_config->plugin_data['Version'], '1.18.14', '<' ) ) {
				echo '<div data-dismissible="disable-fooevents-version-notice-5" class="notice notice-info is-dismissible fooeventspos-notice"><p>' . sprintf( esc_html( $this->fooeventspos_phrases['notice_fooevents_version_11814'] ), '<a href="' . esc_attr( admin_url( 'plugins.php?s=fooevents%20for%20woocommerce&plugin_status=all' ) ) . '">', '</a>' ) . '</p></div>';
			}
		}
	}

	/**
	 * Display FooEvents POS review notice.
	 *
	 * @since 1.9.0
	 */
	public function fooeventspos_review_notice() {
		if ( get_user_meta( get_current_user_id(), 'dismissed_fooeventspos_review_notice', true ) ) {
			return;
		}

		$number_fooeventspos_orders_submitted = 100;

		$args = array(
			'limit'      => $number_fooeventspos_orders_submitted,
			'type'       => 'shop_order',
			'return'     => 'ids',
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'key'   => '_fooeventspos_order_source',
					'value' => 'fooeventspos_app',
				),
			),
		);

		$order_ids = wc_get_orders( $args );

		$args = null;

		unset( $args );

		$number_fooeventspos_orders = count( $order_ids );

		if ( $number_fooeventspos_orders < $number_fooeventspos_orders_submitted ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible fooeventspos-notice" id="fooeventspos-review-notice">
			<p>
				<strong><?php echo esc_html( $this->fooeventspos_phrases['review_notice_title'] ); ?></strong> 
				<?php echo esc_html( $this->fooeventspos_phrases['review_notice_message'] ); ?>
				<a href="<?php echo esc_url( 'https://www.trustpilot.com/evaluate/www.fooevents.com' ); ?>" target="_blank">
					<?php echo esc_html( $this->fooeventspos_phrases['review_link_text'] ); ?>&nbsp;&rarr;
				</a>			
			</p>
		</div>
		<script type="text/javascript">
			jQuery(document).on('click', '#fooeventspos-review-notice .notice-dismiss', function () {
				jQuery.post(ajaxurl, {
					action: 'dismiss_fooeventspos_review_notice',
					security: '<?php echo wp_create_nonce( 'dismiss_fooeventspos_review_notice' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
				});
			});
		</script>
		<?php
	}

	/**
	 * If the user hides the FooEvents POS review notice.
	 *
	 * @since 1.9.0
	 */
	public function fooeventspos_dismiss_review_notice() {
		check_ajax_referer( 'dismiss_fooeventspos_review_notice', 'security' );

		update_user_meta( get_current_user_id(), 'dismissed_fooeventspos_review_notice', true );

		wp_die();
	}

	/**
	 * Check the upload folder.
	 *
	 * @since 1.1.0
	 */
	public function fooeventspos_check_upload_folder() {

		if ( ! function_exists( 'WP_Filesystem' ) ) {

			require_once ABSPATH . '/wp-admin/includes/file.php';

		}

		WP_Filesystem();

		global $wp_filesystem;

		$wp_upload_dir           = wp_upload_dir();
		$fooeventspos_upload_path = $wp_upload_dir['basedir'] . '/fooeventspos/';

		if ( ! $wp_filesystem->is_writable( $fooeventspos_upload_path ) ) {

			$this->fooeventspos_output_notices( array( sprintf( $this->fooeventspos_phrases['notice_upload_directory_not_writable'], $fooeventspos_upload_path ) ) );

		}
	}

	/**
	 * Checks if WooCommerce is active.
	 *
	 * @since 1.7.7
	 */
	public function fooeventspos_check_woocommerce() {

		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->fooeventspos_output_notices( array( $this->fooeventspos_phrases['notice_woocommerce_not_active'] ) );
		}
	}

	/**
	 * Outputs notices to the screen.
	 *
	 * @since 1.1.0
	 * @param array $notices The array of notices to show on the screen.
	 */
	private function fooeventspos_output_notices( $notices ) {

		foreach ( $notices as $notice ) {
			echo '<div class="notice notice-error"><p>' . esc_html( $notice ) . '</p></div>';
		}
	}

	/**
	 * Add action links to plugin listing
	 *
	 * @since 1.0.0
	 * @param array $links The array of action links on the plugin listing.
	 */
	public function fooeventspos_action_links( $links ) {
		$link_settings = '<a href="' . admin_url( 'admin.php?page=fooeventspos-settings' ) . '">' . $this->fooeventspos_phrases['menu_settings'] . '</a>';

		array_unshift( $links, $link_settings );

		return $links;
	}

	/**
	 * Register FooEvents POS importer
	 */
	public function fooeventspos_register_importer() {

		register_importer( 'fooeventspos-import', $this->fooeventspos_phrases['title_fooeventspos_import'], $this->fooeventspos_phrases['description_fooeventspos_import'], array( $this, 'fooeventspos_redirect_to_import' ) );
	}

	/**
	 * Conditionally disable new order emails for orders captured in FooEvents POS
	 *
	 * @since 1.0.0
	 * @param boolean  $whether_enabled Whether or not the sending of the admin new order email is enabled.
	 * @param WC_Order $wc_order The WooCommerce order.
	 */
	public function fooeventspos_conditionally_send_wc_email( $whether_enabled, $wc_order ) {

		if ( ! empty( $wc_order ) && 'fooeventspos_app' === $wc_order->get_meta( '_fooeventspos_order_source', true ) ) {

			$disable_new_order_emails = 'yes' === get_option( 'globalFooEventsPOSDisableNewOrderEmails', '' );

			if ( $disable_new_order_emails ) {

				return false;

			}
		}

		return $whether_enabled;
	}

	/**
	 * Filter callback to output the current plugin version.
	 *
	 * @since 1.0.0
	 * @param string $not_used A string value that is not used to determine the plugin version.
	 */
	public function fooeventspos_current_plugin_version( $not_used ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found

		return (string) $this->version;
	}

	/**
	 * Add the FooEvents POS Settings page
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_settings_page() {
		if ( ! current_user_can( 'publish_fooeventspos' ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			wp_die( esc_html( $this->fooeventspos_phrases['error_insufficient_permissions'] ) );
		}

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		$active_tab = '';

		if ( isset( $_GET['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			$active_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		} else {

			$active_tab = 'general';

		}

		// Enable globalFooEventsPOSAnalyticsOptIn if opt-in link selected.
		if ( isset( $_GET['tab'] ) && 'general' === $_GET['tab'] && isset( $_GET['optin'] ) && 'yes' === $_GET['optin'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			update_option( 'globalFooEventsPOSAnalyticsOptIn', 'yes' );

		}

		// General settings.
		if ( ! isset( $_GET['tab'] ) || ( isset( $_GET['tab'] ) && 'general' === $_GET['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$response        = $this->fooeventspos_check_url_account_status();
			$fooeventspos_status = $response['status'];
			$fooeventspos_extras = $response['extras'];
			$status_message  = $response['status_message'];
		}

		// Users settings.
		if ( isset( $_GET['tab'] ) && 'users' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			// Get all user roles.
			$customer_user_roles_options = get_editable_roles();

			$customer_user_role = get_option( 'globalFooEventsPOSCustomerUserRole', array( 'customer', 'subscriber' ) );

			$default_order_customer         = get_option( 'globalFooEventsPOSDefaultCustomer', '' );
			$default_order_customer_display = $this->fooeventspos_phrases['text_guest'] . ' ' . $this->fooeventspos_phrases['text_customer'];

			if ( '' !== $default_order_customer ) {
				$wc_customer = new WC_Customer( $default_order_customer );

				/* translators: 1: user display name 2: user ID 3: user email */
				$default_order_customer_display = sprintf(
					/* translators: $1: customer name, $2 customer id, $3: customer email */
					esc_html__( '%1$s (#%2$s - %3$s)', 'woocommerce' ),
					$wc_customer->get_first_name() . ' ' . $wc_customer->get_last_name(),
					$wc_customer->get_id(),
					$wc_customer->get_email()
				);
			}
		}

		// Products settings.
		if ( isset( $_GET['tab'] ) && 'products' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			// Get all product categories.
			$taxonomy     = 'product_cat';
			$orderby      = 'name';
			$show_count   = 1; // 1 for yes, 0 for no.
			$pad_counts   = 0; // 1 for yes, 0 for no.
			$hierarchical = 1; // 1 for yes, 0 for no.
			$title        = '';
			$empty        = 0;

			$args = array(
				'taxonomy'     => $taxonomy,
				'orderby'      => $orderby,
				'show_count'   => $show_count,
				'pad_counts'   => $pad_counts,
				'hierarchical' => $hierarchical,
				'title_li'     => $title,
				'hide_empty'   => $empty,
			);

			$all_categories = get_categories( $args );

			$cat_options = array();

			foreach ( $all_categories as $cat ) {

				if ( 0 === $cat->category_parent ) {

					$category_id = $cat->term_id;

					$cat_options[ $cat->term_id ] = $cat->name;

					$args2 = array(
						'taxonomy'     => $taxonomy,
						'child_of'     => 0,
						'parent'       => $category_id,
						'orderby'      => $orderby,
						'show_count'   => $show_count,
						'pad_counts'   => $pad_counts,
						'hierarchical' => $hierarchical,
						'title_li'     => $title,
						'hide_empty'   => $empty,
					);

					$sub_cats = get_categories( $args2 );

					if ( $sub_cats ) {
						foreach ( $sub_cats as $sub_category ) {
							$cat_options[ $sub_category->term_id ] = '   - ' . $sub_category->name;
						}
					}
				}
			}

			$products_to_display = get_option( 'globalFooEventsPOSProductsToDisplay', 'all' );
			$product_categories  = get_option( 'globalFooEventsPOSProductCategories', array() );

			$status_options = array(
				'any'     => $this->fooeventspos_phrases['option_product_status_any'],
				'publish' => $this->fooeventspos_phrases['option_product_status_published'],
				'pending' => $this->fooeventspos_phrases['option_product_status_pending'],
				'draft'   => $this->fooeventspos_phrases['option_product_status_draft'],
				'future'  => $this->fooeventspos_phrases['option_product_status_future'],
				'private' => $this->fooeventspos_phrases['option_product_status_private'],
			);

			$products_status = get_option( 'globalFooEventsPOSProductsStatus', array( 'publish' ) );

			if ( empty( $products_status ) ) {
				$products_status = array( 'publish' );
			}

			$products_only_in_stock = get_option( 'globalFooEventsPOSProductsOnlyInStock', '' );

			$check_stock_availability = get_option( 'globalFooEventsPOSCheckStockAvailability', '' );

			$products_per_page_array = array(
				'10',
				'20',
				'30',
				'40',
				'50',
				'100',
				'200',
				'300',
				'400',
				'500',
			);

			$products_per_page               = get_option( 'globalFooEventsPOSProductsPerPage', '500' );
			$products_show_attribute_labels  = get_option( 'globalFooEventsPOSProductsShowAttributeLabels', '' );
			$products_load_images            = get_option( 'globalFooEventsPOSProductsLoadImages', 'yes' );
			$products_use_decimal_quantities = get_option( 'globalFooEventsPOSProductsUseDecimalQuantities', '' );
		}

		// Orders settings.
		if ( isset( $_GET['tab'] ) && 'orders' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$only_load_pos_orders = get_option( 'globalFooEventsPOSOnlyLoadPOSOrders', '' );

			$order_load_status_values = fooeventspos_get_all_order_statuses( 'load' );

			$order_load_statuses = get_option( 'globalFooEventsPOSOrderLoadStatuses', array( 'completed', 'cancelled', 'refunded' ) );

			if ( empty( $order_load_statuses ) ) {
				$order_load_statuses = array( 'completed', 'cancelled', 'refunded' );
			}

			$order_limit_array = array(
				'all'  => $this->fooeventspos_phrases['option_order_limit_all'],
				'10'   => '10',
				'20'   => '20',
				'30'   => '30',
				'40'   => '40',
				'50'   => '50',
				'100'  => '100',
				'150'  => '150',
				'200'  => '200',
				'250'  => '250',
				'300'  => '300',
				'350'  => '350',
				'400'  => '400',
				'450'  => '450',
				'500'  => '500',
				'1000' => '1000',
			);

			$orders_to_load = get_option( 'globalFooEventsPOSOrdersToLoad', '100' );

			$fetch_order_notes = get_option( 'globalFooEventsPOSFetchOrderNotes', '' );

			$order_submit_status_values = fooeventspos_get_all_order_statuses( 'submit' );
			$order_submit_statuses      = get_option( 'globalFooEventsPOSOrderSubmitStatuses', array( 'completed' ) );

			if ( empty( $order_submit_statuses ) ) {
				$order_submit_statuses = array( 'completed' );
			}

			$default_order_status = get_option( 'globalFooEventsPOSDefaultOrderStatus', 'completed' );

			if ( ! in_array( $default_order_status, $order_submit_statuses, true ) ) {
				$order_submit_statuses[] = $default_order_status;
			}

			$order_incomplete_status_values = fooeventspos_get_all_order_statuses( 'incomplete' );
			$order_incomplete_statuses      = get_option( 'globalFooEventsPOSOrderIncompleteStatuses', array( '' ) );

			if ( empty( $order_incomplete_statuses ) ) {
				$order_incomplete_statuses = array( '' );
			}

			$disable_new_order_emails = get_option( 'globalFooEventsPOSDisableNewOrderEmails', '' );

			$new_order_alert_statuses = get_option( 'globalFooEventsPOSNewOrderAlertStatuses', array() );

			if ( '' === $new_order_alert_statuses ) {
				$new_order_alert_statuses = array();
			}

			$woocommerce_shipping_methods = fooeventspos_get_available_shipping_methods();

			$new_order_alert_shipping_methods = get_option( 'globalFooEventsPOSNewOrderAlertShippingMethods', array() );

			if ( '' === $new_order_alert_shipping_methods || null === $new_order_alert_shipping_methods ) {
				$new_order_alert_shipping_methods = array();
			}
		}

		// Receipts settings.
		if ( isset( $_GET['tab'] ) && 'receipts' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$store_logo_url         = get_option( 'globalFooEventsPOSStoreLogoURL' );
			$store_name             = get_option( 'globalFooEventsPOSStoreName' );
			$header_content         = get_option( 'globalFooEventsPOSHeaderContent' );
			$receipt_title          = get_option( 'globalFooEventsPOSReceiptTitle' );
			$order_number_prefix    = get_option( 'globalFooEventsPOSOrderNumberPrefix' );
			$product_column_title   = get_option( 'globalFooEventsPOSProductColumnTitle' );
			$quantity_column_title  = get_option( 'globalFooEventsPOSQuantityColumnTitle' );
			$price_column_title     = get_option( 'globalFooEventsPOSPriceColumnTitle' );
			$subtotal_column_title  = get_option( 'globalFooEventsPOSSubtotalColumnTitle' );
			$show_sku               = get_option( 'globalFooEventsPOSShowSKU', 'yes' );
			$show_guid              = get_option( 'globalFooEventsPOSShowGUID', 'yes' );
			$inclusive_abbreviation = get_option( 'globalFooEventsPOSInclusiveAbbreviation' );
			$exclusive_abbreviation = get_option( 'globalFooEventsPOSExclusiveAbbreviation' );
			$discounts_title        = get_option( 'globalFooEventsPOSDiscountsTitle' );
			$refunds_title          = get_option( 'globalFooEventsPOSRefundsTitle' );
			$tax_title              = get_option( 'globalFooEventsPOSTaxTitle' );
			$total_title            = get_option( 'globalFooEventsPOSTotalTitle' );
			$payment_method_title   = get_option( 'globalFooEventsPOSPaymentMethodTitle' );
			$show_billing_address   = get_option( 'globalFooEventsPOSShowBillingAddress' );
			$billing_address_title  = get_option( 'globalFooEventsPOSBillingAddressTitle' );
			$show_shipping_address  = get_option( 'globalFooEventsPOSShowShippingAddress' );
			$shipping_address_title = get_option( 'globalFooEventsPOSShippingAddressTitle' );
			$footer_content         = get_option( 'globalFooEventsPOSFooterContent' );
			$receipt_show_logo      = get_option( 'globalFooEventsPOSReceiptShowLogo', 'yes' );
		}

		// Payments settings.
		if ( isset( $_GET['tab'] ) && 'payments' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['update'] ) && 'yes' === $_GET['update'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$wc_order_query_args = array(
					'type'           => 'shop_order',
					'post_type'      => 'shop_order',
					'post_status'    => 'any',
					'limit'          => 100,
					'posts_per_page' => 100,
					'orderby'        => 'date',
					'order'          => 'DESC',
					'return'         => 'ids',
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'relation' => 'AND',
						array(
							'key'   => '_fooeventspos_order_source',
							'value' => 'fooeventspos_app',
						),
						array(
							'key'     => '_fooeventspos_payments',
							'compare' => 'NOT EXISTS',
						),
					),
				);

				$wc_order_ids_without_payments = array();

				if ( class_exists( Automattic\WooCommerce\Utilities\OrderUtil::class ) && Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
					// HPOS usage is enabled.
					$wc_order_ids_without_payments = wc_get_orders( $wc_order_query_args );
				} else {
					// Traditional CPT-based orders are in use.
					$wc_order_query = new WP_Query( $wc_order_query_args );

					$wc_order_ids_without_payments = $wc_order_query->get_posts();
				}

				foreach ( $wc_order_ids_without_payments as $wc_order_id ) {
					$wc_order = wc_get_order( $wc_order_id );

					$payment_args = array(
						'pd'   => $wc_order->get_date_created()->getTimestamp(),
						'oid'  => $wc_order->get_id(),
						'opmk' => $wc_order->get_meta( '_fooeventspos_payment_method', true ),
						'oud'  => $wc_order->get_meta( '_fooeventspos_user_id', true ),
						'tid'  => '' !== $wc_order->get_meta( '_fooeventspos_square_order_id', true ) ? $wc_order->get_meta( '_fooeventspos_square_order_id', true ) : ( '' !== $wc_order->get_meta( '_fooeventspos_stripe_payment_id', true ) ? $wc_order->get_meta( '_fooeventspos_stripe_payment_id', true ) : '' ),
						'pa'   => $wc_order->get_total(),
						'np'   => '1',
						'pn'   => '1',
						'pap'  => '1',
						'par'  => 'refunded' === $wc_order->get_status() ? '1' : '0',
					);

					$payment_post = FooEventsPOS_Payments::fooeventspos_create_update_payment( $payment_args );

					if ( ! is_wp_error( $payment_post ) ) {
						$payment_args['fspid'] = (string) $payment_post['ID'];
						$payment_args['pe']    = get_post_meta( $payment_post['ID'], '_payment_extra', true );
						$payment_args['soar']  = get_post_meta( $payment_post['ID'], '_fooeventspos_square_order_auto_refund', true );
						$payment_args['sfa']   = get_post_meta( $payment_post['ID'], '_fooeventspos_square_fee_amount', true );

						$wc_order->update_meta_data( '_fooeventspos_payments', wp_json_encode( array( $payment_args ) ) );

						ob_start();
						$wc_order->save();
						ob_end_clean();
					}
				}
			}

			$wc_order_query_args = array(
				'type'           => 'shop_order',
				'post_type'      => 'shop_order',
				'post_status'    => 'any',
				'limit'          => -1,
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'return'         => 'ids',
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'AND',
					array(
						'key'   => '_fooeventspos_order_source',
						'value' => 'fooeventspos_app',
					),
					array(
						'key'     => '_fooeventspos_payments',
						'compare' => 'NOT EXISTS',
					),
				),
			);

			$wc_order_ids_without_payments = array();

			if ( class_exists( Automattic\WooCommerce\Utilities\OrderUtil::class ) && Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
				// HPOS usage is enabled.
				$wc_order_ids_without_payments = wc_get_orders( $wc_order_query_args );
			} else {
				// Traditional CPT-based orders are in use.
				$wc_order_query = new WP_Query( $wc_order_query_args );

				$wc_order_ids_without_payments = $wc_order_query->get_posts();
			}

			$wc_order_count_without_payments = count( $wc_order_ids_without_payments );
		}

		// Integration settings.
		if ( isset( $_GET['tab'] ) && 'integration' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$square_application_id  = get_option( 'globalFooEventsPOSSquareApplicationID' );
			$square_access_token    = get_option( 'globalFooEventsPOSSquareAccessToken' );
			$stripe_publishable_key = get_option( 'globalFooEventsPOSStripePublishableKey' );
			$stripe_secret_key      = get_option( 'globalFooEventsPOSStripeSecretKey' );
		}

		// Analytics settings.
		if ( isset( $_GET['tab'] ) && 'analytics' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$fooeventspos_woocommerce_analytics = get_option( 'globalFooEventsPOSWooCommerceAnalytics', 'yes' );
			$fooeventspos_analytics_opt_in      = get_option( 'globalFooEventsPOSAnalyticsOptIn', '' );
		}

		// Status checks.
		$status_outputs = array();

		// Check if any security and CAPTCHA plugins are installed.
		$issue_count = 0;

		$plugin_vendors = array();

		if ( class_exists( 'wfWAF' ) || is_plugin_active( 'wordfence/wordfence.php' ) ) {
			$plugin_vendors[] = 'Wordfence Security';
		}

		if ( class_exists( 'SucuriScan' ) || is_plugin_active( 'secupress/sucuri.php' ) ) {
			$plugin_vendors[] = 'Sucuri Security plugin';
		}

		if ( function_exists( 'secupress_init' ) || is_plugin_active( 'secupress/secupress.php' ) ) {
			$plugin_vendors[] = 'SecuPress Security plugin';
		}

		if ( function_exists( 'loginizer_activation' ) || is_plugin_active( 'loginizer/loginizer.php' ) ) {
			$plugin_vendors[] = 'Loginizer Security';
		}

		if ( function_exists( 'itsec_load_textdomain' ) || is_plugin_active( 'better-wp-security/better-wp-security.php' ) ) {
			$plugin_vendors[] = 'iThemes Security';
		}

		if ( function_exists( 'gglcptch_admin_menu' ) || is_plugin_active( 'google-captcha-pro/google-captcha-pro.php' ) ) {
			$plugin_vendors[] = 'reCaptcha by BestWebSoft';
		}

		if ( class_exists( 'LoginNocaptcha' ) || is_plugin_active( 'login-recaptcha/login-nocaptcha.php' ) ) {
			$plugin_vendors[] = 'Login No Captcha reCAPTCHA';
		}

		if ( class_exists( 'SimpleGoogleRecaptcha' ) || is_plugin_active( 'simple-google-recaptcha/simple-google-recaptcha.php' ) ) {
			$plugin_vendors[] = 'Simple Google reCAPTCHA';
		}

		if ( function_exists( 'uber_recaptcha_run' ) || is_plugin_active( 'uber-nocaptcha-recaptcha/recaptcha.php' ) ) {
			$plugin_vendors[] = 'Uber reCaptcha';
		}

		foreach ( $plugin_vendors as $plugin_vendor ) {
			$status_outputs[] = array(
				'type'       => 'notice',
				'title'      => $this->fooeventspos_phrases['label_notice'],
				'message'    => sprintf( $this->fooeventspos_phrases['description_plugin_vendor'], $plugin_vendor ),
				'link_url'   => 'https://help.fooevents.com/docs/frequently-asked-questions/setup/im-having-trouble-logging-into-fooeventspos/#4-do-you-have-any-security-plugins-installed',
				'link_label' => $this->fooeventspos_phrases['status_check_label_link'],
				'target'     => '_blank',
			);

			++$issue_count;
		}

		// Check if the WooCommerce thumbnail image size is greater than 0 and smaller than 300px.
		if ( (int) get_option( 'thumbnail_size_w' ) >= 300 || (int) get_option( 'thumbnail_size_w' ) === 0 ) {
			$status_outputs[] = array(
				'type'       => 'critical',
				'title'      => $this->fooeventspos_phrases['label_critical'],
				'message'    => sprintf( $this->fooeventspos_phrases['description_thumbnail_size'], get_option( 'thumbnail_size_w' ) ),
				'link_url'   => 'https://help.fooevents.com/docs/frequently-asked-questions/errors/why-does-the-app-crash/#large-thumbnail-images',
				'link_label' => $this->fooeventspos_phrases['status_check_label_link'],
				'target'     => '_blank',
			);

			++$issue_count;

		}

		// Check is the site is running on localhost.
		if ( ( ! empty( $_SERVER['REMOTE_ADDR'] ) && '127.0.0.1' === $_SERVER['REMOTE_ADDR'] ) || ( ! empty( $_SERVER['REMOTE_ADDR'] ) && '::1' === $_SERVER['REMOTE_ADDR'] ) || ( ! empty( $_SERVER['SERVER_NAME'] ) && 'localhost' === $_SERVER['SERVER_NAME'] ) ) {
			$status_outputs[] = array(
				'type'       => 'critical',
				'title'      => $this->fooeventspos_phrases['label_potentially_critical'],
				'message'    => $this->fooeventspos_phrases['description_localhost'],
				'link_url'   => '',
				'link_label' => '',
				'target'     => '',
			);

			++$issue_count;
		}

		// Check if the site uses https.
		if ( empty( $_SERVER['HTTPS'] ) ) {
			$status_outputs[] = array(
				'type'       => 'critical',
				'title'      => $this->fooeventspos_phrases['label_critical'],
				'message'    => $this->fooeventspos_phrases['description_https'],
				'link_url'   => 'https://docs.woocommerce.com/document/ssl-and-https/',
				'link_label' => $this->fooeventspos_phrases['status_check_label_link'],
				'target'     => '_blank',
			);

			++$issue_count;
		}

		// Check if permalinks are enabled (F4W only).
		if ( ! get_option( 'permalink_structure' ) ) {
			$status_outputs[] = array(
				'type'       => 'critical',
				'title'      => $this->fooeventspos_phrases['label_critical'],
				'message'    => $this->fooeventspos_phrases['description_permalinks'],
				'link_url'   => admin_url( 'options-permalink.php' ),
				'link_label' => $this->fooeventspos_phrases['status_check_label_link'],
				'target'     => '',
			);

			++$issue_count;
		}

		// Check if REST API is working.
		$rest_api_test_result = array(
			'label'       => $this->fooeventspos_phrases['label_rest_api_accessible'],
			'status'      => 'none',
			'description' => $this->fooeventspos_phrases['description_rest_api_accessible'],
		);

		$wp_site_health          = new WP_Site_Health();
		$wp_rest_api_test_result = $wp_site_health->get_test_rest_availability();

		if ( 'good' !== $wp_rest_api_test_result['status'] ) {
			$rest_api_test_result['status']      = 'critical';
			$rest_api_test_result['label']       = $this->fooeventspos_phrases['label_critical'];
			$rest_api_test_result['description'] = $this->fooeventspos_phrases['description_rest_api_critical_error'];

			++$issue_count;
		}

		$status_outputs[] = array(
			'type'       => $rest_api_test_result['status'],
			'title'      => $rest_api_test_result['label'],
			'message'    => $rest_api_test_result['description'],
			'link_url'   => admin_url( 'site-health.php' ),
			'link_label' => $this->fooeventspos_phrases['status_check_label_link_see_site_health'],
			'target'     => '',
		);

		// Check if the site is in a sub directory.
		$fooeventspos_url       = site_url();
		$fooeventspos_url_info  = wp_parse_url( $fooeventspos_url );
		$fooeventspos_clean_url = $fooeventspos_url_info['host'];

		$sub_count = explode( '/', $fooeventspos_clean_url );

		if ( count( $sub_count ) > 1 ) {
			$status_outputs[] = array(
				'type'       => 'notice',
				'title'      => $this->fooeventspos_phrases['label_caution'],
				'message'    => $this->fooeventspos_phrases['description_rest_api_caution'],
				'link_url'   => 'https://help.fooevents.com/docs/frequently-asked-questions/setup/im-having-trouble-logging-into-fooeventspos/#7-is-your-site-installed-in-a-subdirectory-of-another-wordpress',
				'link_label' => $this->fooeventspos_phrases['status_check_label_link'],
				'target'     => '_blank',
			);

			++$issue_count;
		}

		// Check if the FooEvents POS plugin is up to date.
		$update_plugins = get_site_transient( 'update_plugins' );

		if ( $update_plugins && ! empty( $update_plugins->response ) ) {
			foreach ( $update_plugins->response as $plugin => $plugin_values ) {
				if ( 'fooevents-pos' === $plugin_values->slug ) {
					$status_outputs[] = array(
						'type'       => 'critical',
						'title'      => $this->fooeventspos_phrases['label_plugin_update'],
						'message'    => $this->fooeventspos_phrases['description_plugin_update'],
						'link_url'   => admin_url( 'plugins.php?s=fooeventspos' ),
						'link_label' => sprintf( $this->fooeventspos_phrases['status_check_label_link_plugin_update'], $plugin_values->new_version ),
						'target'     => '',
					);

					++$issue_count;
				}
			}
		}

		// Check if the "Decimal Product Quantity for WooCommerce" plugin is active.
		if ( is_plugin_active( 'decimal-product-quantity-for-woocommerce/decimal-product-quantity-for-woocommerce.php' ) ) {
			$status_outputs[] = array(
				'type'       => 'notice',
				'title'      => $this->fooeventspos_phrases['label_notice'],
				'message'    => $this->fooeventspos_phrases['description_decimal_product_quantity_plugin'],
				'link_url'   => 'https://help.fooevents.com/docs/topics/products/decimal-quantities/',
				'link_label' => $this->fooeventspos_phrases['status_check_label_link'],
				'target'     => '_blank',
			);

			++$issue_count;
		}

		$fooevents_activate_plugin_link = FooEventsPOS_FooEvents_Integration::fooeventspos_check_fooevents_active( $status_outputs, $issue_count );

		// Check if no issues were detected.
		if ( 0 === $issue_count ) {
			$status_outputs[] = array(
				'type'       => 'none',
				'title'      => $this->fooeventspos_phrases['label_plugin_no_issues'],
				'message'    => $this->fooeventspos_phrases['description_plugin_no_issues'],
				'link_url'   => 'https://help.fooevents.com/contact',
				'link_label' => $this->fooeventspos_phrases['button_settings_contact_us'],
				'target'     => '_blank',
			);
		}

		$fooeventspos_phrases = $this->fooeventspos_phrases;

		$global_woocommerce_events_api_key = get_option( 'globalWooCommerceEventsAPIKey' );

		$fooeventspos_appearance_settings = FooEvents_POS_Integration::fooeventspos_get_appearance_values();

		$pos_page_slug = 'fooeventspos';

		$pos_page_slug = FooEvents_POS_Integration::fooeventspos_get_app_slug();

		$current_user       = wp_get_current_user();
		$current_user_email = $current_user->user_email;

		require_once plugin_dir_path( __FILE__ ) . 'templates/fooeventspos-settings.php';
	}

	/**
	 * Check the URL for its account status
	 *
	 * @since 1.0.2
	 *
	 * @return array Status and extras.
	 */
	private function fooeventspos_check_url_account_status() {
		return array();
		$fooeventspos_url = site_url();

		$data = array(
			'passphrase' => '84671ded20ea778edd234f7e1cb3dcc1',
			'channel'    => 'fooeventspos-plugin',
			'url'        => $fooeventspos_url,
		);

		$result = wp_remote_post(
			'https://www.fooevents.com/wp-json/fooeventspos-accounts/v0/url/',
			array(
				'method'      => 'POST',
				'timeout'     => 30,
				'redirection' => 10,
				'httpversion' => '1.1',
				'body'        => $data,
			)
		);

		$fooeventspos_extras = array();
		$fooeventspos_status = 'static';
		$status_message  = '';

		if ( ! is_wp_error( $result ) ) {
			$obj = json_decode( $result['body'], true );

			if ( isset( $obj['status'] ) ) {
				$fooeventspos_status = $obj['status'];
			}
			if ( isset( $obj['status_message'] ) ) {
				$status_message = $obj['status_message'];
			}

			// Convert pipe-separated extras string into an array.
			if ( isset( $obj['extras'] ) && ! empty( $obj['extras'] ) ) {
				$fooeventspos_extras = explode( '|', $obj['extras'] );
			}
		}

		return array(
			'status'         => $fooeventspos_status,
			'extras'         => $fooeventspos_extras,
			'status_message' => $status_message,
		);
	}

	/**
	 * The AJAX handler function for creating a free trial
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_create_trial() {
		// Security check for valid nonce.
		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'fooeventspos_trial_nonce' ) ) {
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => esc_html( $this->fooeventspos_phrases['error_create_trial_generic'] ),
				)
			);
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => esc_html( $this->fooeventspos_phrases['error_create_trial_permission'] ),
				)
			);
		}

		// Ensure both the email and store fields are provided.
		if ( isset( $_POST['email'] ) && isset( $_POST['store'] ) ) {

			// Sanitize the input.
			$email = sanitize_email( wp_unslash( $_POST['email'] ) );
			$store = sanitize_text_field( wp_unslash( $_POST['store'] ) );

			if ( ! is_email( $email ) ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => esc_html( $this->fooeventspos_phrases['error_create_trial_invalid_email'] ),
					)
				);
			}
			if ( empty( $store ) ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => esc_html( $this->fooeventspos_phrases['error_create_trial_invalid_store'] ),
					)
				);
			}

			// Build the data to be sent to the trial API.
			$data = array(
				'email' => $email,
				'store' => $store,
			);

			// Define the API endpoint URL (adjust this path as needed).
			// DEV URL.
			$api_endpoint = 'https://www.fooevents.com/wp-json/fooeventspos-accounts/v0/free-trial/';

			// Submit the data using wp_remote_post.
			$api_response = wp_remote_post(
				$api_endpoint,
				array(
					'timeout'     => 30,
					'redirection' => 5,
					'httpversion' => '1.1',
					'body'        => $data,
				)
			);

			// Check for a connection error.
			if ( is_wp_error( $api_response ) ) {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => esc_html( $this->fooeventspos_phrases['error_create_trial_connection'] ),
					)
				);
			}

			// Decode the API response.
			$result = json_decode( wp_remote_retrieve_body( $api_response ), true );

			// If a trial already exists, report an error.
			if ( isset( $result['message'] ) && 'trial-exists' === $result['message'] ) {
				$result = array(
					'status'  => 'error',
					'message' => esc_html( $this->fooeventspos_phrases['error_create_trial_exists'] ),
				);
			}

			// If no valid status was returned, report an error.
			if ( ! isset( $result['status'] ) ) {
				$result = array(
					'status'  => 'error',
					'message' => esc_html( $this->fooeventspos_phrases['error_create_trial_generic'] ),
				);
			}

			// Send the API response back to the client.
			wp_send_json( $result );
		} else {
			// Return an error if required fields are missing.
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => esc_html( $this->fooeventspos_phrases['error_create_trial_generic'] ),
				)
			);
		}
	}

	/**
	 * Filter WooCommerce orders listing based on FooEvents POS filter selection.
	 *
	 * @since 1.4.0
	 * @param array $args The query arguments used in the (Custom Order Table-powered) order list.
	 *
	 * @return array Query args.
	 */
	public function fooeventspos_filter_order_results_hpos( $args ) {

		global $pagenow;

		if ( is_admin() && 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'wc-orders' === $_GET['page'] && 'shop_order' === $args['type'] && isset( $_GET['fooeventspos_payment_method_filter'] ) && isset( $_GET['fooeventspos_cashier_filter'] ) && ( '' !== sanitize_text_field( wp_unslash( $_GET['fooeventspos_payment_method_filter'] ) ) || '' !== sanitize_text_field( wp_unslash( $_GET['fooeventspos_cashier_filter'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! empty( $args['meta_query'] ) ) {
				$args['meta_query']['relation'] = 'AND';
			} else {
				$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
					'relation' => 'AND',
				);
			}

			if ( '' !== sanitize_text_field( wp_unslash( $_GET['fooeventspos_payment_method_filter'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$fooeventspos_payment_method_filter = sanitize_text_field( wp_unslash( $_GET['fooeventspos_payment_method_filter'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$payment_method_options         = array(
					'pos-split'                => 'fooeventspos_split',
					'pos-cash'                 => 'fooeventspos_cash',
					'pos-card'                 => 'fooeventspos_card',
					'pos-direct-bank-transfer' => 'fooeventspos_direct_bank_transfer',
					'pos-check-payment'        => 'fooeventspos_check_payment',
					'pos-cash-on-delivery'     => 'fooeventspos_cash_on_delivery',
					'pos-square-manual'        => 'fooeventspos_square_manual',
					'pos-square-terminal'      => 'fooeventspos_square_terminal',
					'pos-square-reader'        => 'fooeventspos_square_reader',
					'pos-stripe-manual'        => 'fooeventspos_stripe_manual',
					'pos-stripe-reader'        => 'fooeventspos_stripe_reader',
					'pos-stripe-chipper'       => 'fooeventspos_stripe_chipper',
					'pos-stripe-wisepad'       => 'fooeventspos_stripe_wisepad',
					'pos-stripe-reader-m2'     => 'fooeventspos_stripe_reader_m2',
					'pos-other'                => 'fooeventspos_other',
				);

				$payment_method_options = FooEvents_POS_Integration::fooeventspos_get_payment_method_options();

				if ( in_array( $fooeventspos_payment_method_filter, array_keys( $payment_method_options ), true ) ) {
					if ( 'pos-square-reader' === $fooeventspos_payment_method_filter ) {
						$args['meta_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
							'relation' => 'OR',
							array(
								'key'   => '_fooeventspos_payment_method',
								'value' => 'fooeventspos_square_reader',
							),
							array(
								'key'   => '_fooeventspos_payment_method',
								'value' => 'fooeventspos_square',
							),
						);
					} else {
						$args['meta_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
							'key'   => '_fooeventspos_payment_method',
							'value' => $payment_method_options[ $fooeventspos_payment_method_filter ],
						);
					}
				} else {
					switch ( $fooeventspos_payment_method_filter ) {
						case 'pos-all':
							// All FooEvents POS payments.
							$args['meta_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
								'key'   => '_fooeventspos_order_source',
								'value' => 'fooeventspos_app',
							);

							break;
						case 'pos-none':
							// Online orders only.
							$args['meta_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
								'relation' => 'OR',
								array(
									'key'     => '_fooeventspos_payment_method',
									'compare' => 'NOT EXISTS',
								),
								array(
									'key'     => '_fooeventspos_payment_method',
									'value'   => '',
									'compare' => '=',
								),
							);

							break;
					}
				}
			}

			if ( '' !== sanitize_text_field( wp_unslash( $_GET['fooeventspos_cashier_filter'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$fooeventspos_cashier_filter = sanitize_text_field( wp_unslash( $_GET['fooeventspos_cashier_filter'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				$args['meta_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
					'key'     => '_fooeventspos_user_id',
					'value'   => $fooeventspos_cashier_filter,
					'compare' => '=',
				);
			}
		}

		return $args;
	}

	/**
	 * Filter WooCommerce orders listing based on FooEvents POS filter selection.
	 *
	 * @since 1.0.0
	 * @param WP_Query $query The WooCommerce order results query.
	 */
	public function fooeventspos_filter_order_results( $query ) {

		global $pagenow;
		global $typenow;

		if ( is_admin() && 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && 'shop_order' === $_GET['post_type'] && 'shop_order' === $typenow && isset( $_GET['fooeventspos_payment_method_filter'] ) && isset( $_GET['fooeventspos_cashier_filter'] ) && ( '' !== sanitize_text_field( wp_unslash( $_GET['fooeventspos_payment_method_filter'] ) ) || '' !== sanitize_text_field( wp_unslash( $_GET['fooeventspos_cashier_filter'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! empty( $query->query_vars['meta_query'] ) ) {
				$query->query_vars['meta_query']['relation'] = 'AND';
			} else {
				$query->query_vars['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'relation' => 'AND',
				);
			}

			if ( '' !== sanitize_text_field( wp_unslash( $_GET['fooeventspos_payment_method_filter'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$fooeventspos_payment_method_filter = sanitize_text_field( wp_unslash( $_GET['fooeventspos_payment_method_filter'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$payment_method_options         = array(
					'pos-split'                => 'fooeventspos_split',
					'pos-cash'                 => 'fooeventspos_cash',
					'pos-card'                 => 'fooeventspos_card',
					'pos-direct-bank-transfer' => 'fooeventspos_direct_bank_transfer',
					'pos-check-payment'        => 'fooeventspos_check_payment',
					'pos-cash-on-delivery'     => 'fooeventspos_cash_on_delivery',
					'pos-square-manual'        => 'fooeventspos_square_manual',
					'pos-square-terminal'      => 'fooeventspos_square_terminal',
					'pos-square-reader'        => 'fooeventspos_square_reader',
					'pos-stripe-manual'        => 'fooeventspos_stripe_manual',
					'pos-stripe-reader'        => 'fooeventspos_stripe_reader',
					'pos-stripe-chipper'       => 'fooeventspos_stripe_chipper',
					'pos-stripe-wisepad'       => 'fooeventspos_stripe_wisepad',
					'pos-stripe-reader-m2'     => 'fooeventspos_stripe_reader_m2',
					'pos-other'                => 'fooeventspos_other',
				);

				$payment_method_options = FooEvents_POS_Integration::fooeventspos_get_payment_method_options();

				if ( in_array( $fooeventspos_payment_method_filter, array_keys( $payment_method_options ), true ) ) {
					if ( 'pos-square-reader' === $fooeventspos_payment_method_filter ) {
						$query->query_vars['meta_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
						'relation' => 'OR',
						array(
							'key'   => '_fooeventspos_payment_method',
							'value' => 'fooeventspos_square_reader',
						),
						array(
							'key'   => '_fooeventspos_payment_method',
							'value' => 'fooeventspos_square',
						),
						);
					} else {
						$query->query_vars['meta_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
						'key'   => '_fooeventspos_payment_method',
						'value' => $payment_method_options[ $fooeventspos_payment_method_filter ],
						);
					}
				} else {
					switch ( $fooeventspos_payment_method_filter ) {
						case 'pos-all':
							// All FooEvents POS payments.
							$query->query_vars['meta_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
							'key'   => '_fooeventspos_order_source',
							'value' => 'fooeventspos_app',
							);

							break;
						case 'pos-none':
							// Online orders only.
							$query->query_vars['meta_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
								'relation' => 'OR',
								array(
									'key'     => '_fooeventspos_payment_method',
									'compare' => 'NOT EXISTS',
								),
								array(
									'key'     => '_fooeventspos_payment_method',
									'value'   => '',
									'compare' => '=',
								),
							);

							break;
					}
				}
			}

			if ( '' !== sanitize_text_field( wp_unslash( $_GET['fooeventspos_cashier_filter'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$fooeventspos_cashier_filter = sanitize_text_field( wp_unslash( $_GET['fooeventspos_cashier_filter'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				$query->query_vars['meta_query'][] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
				'key'     => '_fooeventspos_user_id',
				'value'   => $fooeventspos_cashier_filter,
				'compare' => '=',
				);
			}
		}
	}

	/**
	 * Adds FooEvents POS drop down filter selection to the WooCommerce orders listing.
	 *
	 * @since 1.0.0
	 * @param string $post_type The post type.
	 */
	public function fooeventspos_filter_orders_payment_method( $post_type ) {

		if ( 'shop_order' === $post_type ) {

			$fooeventspos_payment_method_filter = '';

			if ( isset( $_GET['fooeventspos_payment_method_filter'] ) && '' !== $_GET['fooeventspos_payment_method_filter'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$fooeventspos_payment_method_filter = sanitize_text_field( wp_unslash( $_GET['fooeventspos_payment_method_filter'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}

			?>
			<select name="fooeventspos_payment_method_filter">
				<option value=""><?php echo esc_html( $this->fooeventspos_phrases['filter_all_orders'] ); ?></option>

				<option value="pos-all"
				<?php
				if ( 'pos-all' === $fooeventspos_payment_method_filter ) {
					echo 'selected';
				}
				?>
				><?php echo esc_html( $this->fooeventspos_phrases['filter_fooeventspos_pos_only'] ); ?></option>

				<option value="pos-none"
				<?php
				if ( 'pos-none' === $fooeventspos_payment_method_filter ) {
					echo 'selected';
				}
				?>
				><?php echo esc_html( $this->fooeventspos_phrases['filter_online_only'] ); ?></option>

				<?php
					$payment_methods        = fooeventspos_do_get_all_payment_methods( true );
					$payment_method_options = array(
						'pos-split'                => 'fooeventspos_split',
						'pos-cash'                 => 'fooeventspos_cash',
						'pos-card'                 => 'fooeventspos_card',
						'pos-direct-bank-transfer' => 'fooeventspos_direct_bank_transfer',
						'pos-check-payment'        => 'fooeventspos_check_payment',
						'pos-cash-on-delivery'     => 'fooeventspos_cash_on_delivery',
						'pos-square-manual'        => 'fooeventspos_square_manual',
						'pos-square-terminal'      => 'fooeventspos_square_terminal',
						'pos-square-reader'        => 'fooeventspos_square_reader',
						'pos-stripe-manual'        => 'fooeventspos_stripe_manual',
						'pos-stripe-reader'        => 'fooeventspos_stripe_reader',
						'pos-stripe-chipper'       => 'fooeventspos_stripe_chipper',
						'pos-stripe-wisepad'       => 'fooeventspos_stripe_wisepad',
						'pos-stripe-reader-m2'     => 'fooeventspos_stripe_reader_m2',
						'pos-other'                => 'fooeventspos_other',
					);

					$payment_method_options = FooEvents_POS_Integration::fooeventspos_get_payment_method_options();

					foreach ( $payment_method_options as $payment_method_option_value => $payment_method_key ) {
						?>
						<option value="<?php echo esc_attr( $payment_method_option_value ); ?>"
						<?php
						if ( $payment_method_option_value === $fooeventspos_payment_method_filter ) {
							echo 'selected';
						}
						?>
						><?php echo esc_html( ! empty( $payment_methods[ $payment_method_key ] ) ? 'POS ' . $payment_methods[ $payment_method_key ] : $this->fooeventspos_phrases[ 'filter_pos_' . str_replace( 'fooeventspos_', '', $payment_method_key ) ] ); ?></option>
						<?php
					}
					?>
			</select>
			<?php
		}
	}

	/**
	 * Adds FooEvents POS cashier drop down filter to the WooCommerce orders listing.
	 *
	 * @since 1.0.0
	 * @param string $post_type The post type.
	 */
	public function fooeventspos_filter_orders_cashier( $post_type ) {

		global $wp_roles;

		if ( 'shop_order' === $post_type ) {

			$fooeventspos_cashier_filter = '';

			if ( isset( $_GET['fooeventspos_cashier_filter'] ) && '' !== $_GET['fooeventspos_cashier_filter'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$fooeventspos_cashier_filter = sanitize_text_field( wp_unslash( $_GET['fooeventspos_cashier_filter'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}

			$fooeventspos_roles = array();
			$fooeventspos_caps  = array( 'publish_fooeventspos' );

			// Check which WP roles have FooEvents POS capabilities.
			foreach ( $wp_roles->roles as $role => $value ) {
				$role_object = get_role( $role );

				if ( $role_object ) {
					foreach ( $fooeventspos_caps as $fooeventspos_cap ) {
						if ( $role_object->has_cap( $fooeventspos_cap ) ) {
							$fooeventspos_roles[] = $role;
						}
					}
				}
			}

			$fooeventspos_roles = array_unique( $fooeventspos_roles ); // Remove duplicates.

			// Get users who belong to roles that have FooEvents POS capabilities.
			$fooeventspos_users = array();

			foreach ( $fooeventspos_roles as $role ) {
				$users_of_role  = get_users( array( 'role' => $role ) );
				$fooeventspos_users = array_merge( $users_of_role, $fooeventspos_users );
			}
			?>
			<select name="fooeventspos_cashier_filter">
				<option value=""><?php echo esc_html( $this->fooeventspos_phrases['filter_all_cashiers'] ); ?></option>			
				<?php
				foreach ( $fooeventspos_users as $fooeventspos_user ) {
					echo '<option value="' . esc_attr( $fooeventspos_user->ID ) . '" ';

					if ( strval( $fooeventspos_user->ID ) === $fooeventspos_cashier_filter ) {
						echo ' selected ';
					}

					echo ' >' . esc_html( $fooeventspos_user->display_name ) . '</option>';
				}
				?>
			</select>
			<?php
		}
	}

	/**
	 * Add the 'Cashier' user role and add FooEvents POS capability
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_add_cashier_role() {
		$fooeventspos_cashier_role_version = get_option( 'fooeventspos_cashier_role_version', '' );

		if ( (string) $this->version !== $fooeventspos_cashier_role_version ) {
			add_role(
				'fooeventspos_cashier',
				$this->fooeventspos_phrases['label_order_cashier'],
				array(
					'read'    => true,
					'level_0' => true,
				)
			);

			update_option( 'fooeventspos_cashier_role_version', (string) $this->version );
		}
	}

	/**
	 * Add POS column to WooCommerce orders listing.
	 *
	 * @since 1.0.0
	 * @param array $columns The columns that show on the order list.
	 */
	public function fooeventspos_order_column( $columns ) {
		$columns['fooeventspos_column_cashier'] = esc_html( $this->fooeventspos_phrases['label_order_cashier'] );
		$columns['fooeventspos_column_type']    = 'POS';

		return $columns;
	}

	/**
	 * Add payment method indicator to POS column on WooCommerce orders listing.
	 *
	 * @since 1.0.0
	 * @param string $column The column for which content should be added.
	 * @param int    $order_id The ID of the current order.
	 */
	public function fooeventspos_order_column_content( $column, $order_id ) {
		$wc_order = wc_get_order( $order_id );

		if ( 'fooeventspos_column_cashier' === $column ) {

			$fooeventspos_user_id = $wc_order->get_meta( '_fooeventspos_user_id', true );

			if ( '' !== $fooeventspos_user_id ) {
				$fooeventspos_user      = get_userdata( $fooeventspos_user_id );
				$fooeventspos_user_name = $fooeventspos_user->display_name;

				echo '<a href="' . esc_attr( admin_url( 'edit.php?post_type=shop_order&fooeventspos_payment_method_filter&fooeventspos_cashier_filter=' . $fooeventspos_user_id ) ) . '">' . esc_html( $fooeventspos_user_name ) . '</a>';
			} else {
				echo '<em>-</em>';
			}
		} elseif ( 'fooeventspos_column_type' === $column ) {

			$fooeventspos_order_type = $wc_order->get_meta( '_fooeventspos_payment_method', true );

			$payment_methods            = fooeventspos_do_get_all_payment_methods( true );
			$payment_method_key_classes = array(
				'fooeventspos_split'                => 'fooeventspos_type_split',
				'fooeventspos_cash'                 => 'fooeventspos_type_cash',
				'fooeventspos_card'                 => 'fooeventspos_type_card',
				'fooeventspos_direct_bank_transfer' => 'fooeventspos_type_direct_bank_transfer',
				'fooeventspos_check_payment'        => 'fooeventspos_type_check_payment',
				'fooeventspos_cash_on_delivery'     => 'fooeventspos_type_cash_on_delivery',
				'fooeventspos_square_manual'        => 'fooeventspos_type_square',
				'fooeventspos_square_terminal'      => 'fooeventspos_type_square',
				'fooeventspos_square'               => 'fooeventspos_type_square',
				'fooeventspos_square_reader'        => 'fooeventspos_type_square',
				'fooeventspos_stripe_manual'        => 'fooeventspos_type_stripe',
				'fooeventspos_stripe_reader'        => 'fooeventspos_type_stripe',
				'fooeventspos_stripe_chipper'       => 'fooeventspos_type_stripe',
				'fooeventspos_stripe_wisepad'       => 'fooeventspos_type_stripe',
				'fooeventspos_stripe_reader_m2'     => 'fooeventspos_type_stripe',
				'fooeventspos_other'                => 'fooeventspos_type_other',
			);

			$payment_method_mark_label = $this->fooeventspos_phrases['title_payment_method_online'];
			$payment_method_mark_class = 'fooeventspos_type_online';

			if ( in_array( $fooeventspos_order_type, array_keys( $payment_methods ), true ) ) {
				$payment_method_mark_label = $payment_methods[ $fooeventspos_order_type ];
				$payment_method_mark_class = $payment_method_key_classes[ $fooeventspos_order_type ];
			} elseif ( in_array( $fooeventspos_order_type, array_keys( $payment_method_key_classes ), true ) ) {
				$payment_method_mark_label = $this->fooeventspos_phrases[ 'title_payment_method_' . str_replace( 'fooeventspos_', '', $fooeventspos_order_type ) ];
				$payment_method_mark_class = $payment_method_key_classes[ $fooeventspos_order_type ];
			}

			echo '<mark class="order-status ' . esc_attr( $payment_method_mark_class ) . '"><span>';
			echo esc_html( $payment_method_mark_label );
			echo '</span></mark>';
		}
	}

	/**
	 * Add payment method to WooCommerce order details screen.
	 *
	 * @since 1.0.0
	 * @param WC_Order $wc_order The WooCommerce order to which payment method meta should be added.
	 */
	public function fooeventspos_order_meta_general( $wc_order ) {

		$fooeventspos_order_source   = $wc_order->get_meta( '_fooeventspos_order_source', true );
		$fooeventspos_payment_method = $wc_order->get_meta( '_fooeventspos_payment_method', true );
		$fooeventspos_user_id        = $wc_order->get_meta( '_fooeventspos_user_id', true );

		if ( '' !== $fooeventspos_user_id ) {

			$fooeventspos_user      = get_userdata( $fooeventspos_user_id );
			$fooeventspos_user_name = $fooeventspos_user->display_name;

		}

		if ( 'fooeventspos_app' === $fooeventspos_order_source ) {

			global $wp_roles;

			$fooeventspos_roles = array();
			$fooeventspos_caps  = array( 'publish_fooeventspos' );

			// Check which WP roles have FooEvents POS capabilities.
			foreach ( $wp_roles->roles as $role => $value ) {
				$role_object = get_role( $role );

				if ( $role_object ) {
					foreach ( $fooeventspos_caps as $fooeventspos_cap ) {
						if ( $role_object->has_cap( $fooeventspos_cap ) ) {
							$fooeventspos_roles[] = $role;
						}
					}
				}
			}

			$fooeventspos_roles = array_unique( $fooeventspos_roles ); // Remove duplicates.

			// Get users who belong to roles that have FooEvents POS capabilities.
			$fooeventspos_users = array();

			foreach ( $fooeventspos_roles as $role ) {
				$users_of_role  = get_users( array( 'role' => $role ) );
				$fooeventspos_users = array_merge( $users_of_role, $fooeventspos_users );
			}

			echo "<br class='clear' />";

			$cashier_options = array( '' => '(' . $this->fooeventspos_phrases['filter_none'] . ')' );

			foreach ( $fooeventspos_users as $fooeventspos_user ) {
				$cashier_options[ (string) $fooeventspos_user->ID ] = $fooeventspos_user->display_name;
			}

			woocommerce_wp_select(
				array(
					'id'            => '_fooeventspos_user_id',
					'label'         => $this->fooeventspos_phrases['label_order_cashier'] . ':' . ( '' !== $fooeventspos_user_id ? '<a href="' . esc_attr( admin_url( 'edit.php?post_type=shop_order&fooeventspos_payment_method_filter&fooeventspos_cashier_filter=' . $fooeventspos_user_id ) ) . '" class="fooeventspos-right-link" target="_blank">' . esc_html( $this->fooeventspos_phrases['text_view_other_orders'] ) . '&nbsp;&rarr;</a>' : '' ),
					'value'         => (string) $fooeventspos_user_id,
					'options'       => $cashier_options,
					'wrapper_class' => 'form-field-wide',
				)
			);

			wp_nonce_field( '_fooeventspos_save_order_cashier_' . $wc_order->get_id(), '_fooeventspos_order_cashier_nonce' );

			echo "<br class='clear' />";
			echo '<h3>' . esc_html( $this->fooeventspos_phrases['title_payment_details'] ) . '</h3>';
			echo '<p class="form-field form-field-wide">';

			$payment_methods      = fooeventspos_do_get_all_payment_methods( true );
			$found_payment_method = ! empty( $payment_methods[ $fooeventspos_payment_method ] ) ? $payment_methods[ $fooeventspos_payment_method ] : $this->fooeventspos_phrases[ 'title_payment_method_' . str_replace( 'fooeventspos_', '', $fooeventspos_payment_method ) ];

			echo esc_html( $found_payment_method );

			echo ' <em>' . esc_html( $this->fooeventspos_phrases['label_payment_via_fooeventspos_pos'] ) . '</em>';

			$square_fees = $wc_order->get_meta( '_fooeventspos_square_fee_amount', true );

			if ( (float) $square_fees > 0 ) {

				echo '<br/>' . wp_kses_post( $this->fooeventspos_phrases['text_square_fees'] . ': ' . wc_price( $square_fees ) );

			}

			// Square payment.
			if ( in_array(
				$fooeventspos_payment_method,
				array(
					'fooeventspos_square',
					'fooeventspos_square_manual',
					'fooeventspos_square_terminal',
					'fooeventspos_square_reader',
				),
				true
			) ) {

				$square_order_id = $wc_order->get_meta( '_fooeventspos_square_order_id', true );

				if ( '' !== $square_order_id ) {

					$square_auto_refund = $wc_order->get_meta( '_fooeventspos_square_order_auto_refund', true );

					echo '<br/>';

					if ( '' === $square_auto_refund ) {

						echo esc_html( $this->fooeventspos_phrases['description_square_split_tenders'] ) . ' ';

					}

					echo '<a href="https://squareup.com/dashboard/sales/transactions/' . esc_attr( $square_order_id ) . '" target="_blank">' . esc_html( $this->fooeventspos_phrases['button_view_square_transaction'] ) . '</a>';

				}
			}

			// Stripe payment.
			if ( in_array(
				$fooeventspos_payment_method,
				array(
					'fooeventspos_stripe_manual',
					'fooeventspos_stripe_reader',
					'fooeventspos_stripe_chipper',
					'fooeventspos_stripe_wisepad',
					'fooeventspos_stripe_reader_m2',
				),
				true
			) ) {

				$stripe_payment_id = $wc_order->get_meta( '_fooeventspos_stripe_payment_id', true );

				if ( '' !== $stripe_payment_id ) {

					echo '<br/>';
					echo '<a href="https://dashboard.stripe.com/payments/' . esc_attr( $stripe_payment_id ) . '" target="_blank">' . esc_html( $this->fooeventspos_phrases['button_view_stripe_payment'] ) . '</a>';

				}
			}

			echo '</p>';
		} else {
			echo "<br class='clear' />&nbsp;";
		}
	}

	/**
	 * Add the POS payment methods.
	 *
	 * @since 1.0.0
	 * @param array $methods An array of payment methods.
	 */
	public function fooeventspos_add_payment_options_class( $methods ) {

		if ( ! class_exists( 'FooEventsPOS_Payment_Method' ) ) {
			require_once plugin_dir_path( __FILE__ ) . '/class-fooeventspos-payment-method.php';
		}

		$this->fooeventspos_payment_method_ids = array(
			'split',
			'cash',
			'card',
			'direct-bank-transfer',
			'check-payment',
			'cash-on-delivery',
			'square-manual',
			'square-terminal',
			'square-reader',
			'stripe-manual',
			'stripe-reader',
			'stripe-chipper',
			'stripe-wisepad',
			'stripe-reader-m2',
			'other',
		);

		$this->fooeventspos_payment_method_ids = FooEvents_POS_Integration::fooeventspos_get_payment_method_ids();

		foreach ( $this->fooeventspos_payment_method_ids as $fooeventspos_payment_method_id ) {

			$fooeventspos_payment_method_options = array(
				'id'                   => 'fooeventspos-' . $fooeventspos_payment_method_id,
				'title'                => $this->fooeventspos_phrases[ 'title_payment_method_' . str_replace( '-', '_', $fooeventspos_payment_method_id ) ],
				'description'          => $this->fooeventspos_phrases[ 'description_payment_method_' . str_replace( '-', '_', $fooeventspos_payment_method_id ) ],
				'enable_disable_label' => $this->fooeventspos_phrases[ 'label_enable_disable_' . str_replace( '-', '_', $fooeventspos_payment_method_id ) ],
			);

			// Custom descriptions for Square payment methods.
			if ( strpos( $fooeventspos_payment_method_id, 'square' ) === 0 ) {
				$fooeventspos_payment_method_options['description'] = sprintf( $fooeventspos_payment_method_options['description'], '<a href="' . admin_url( 'admin.php?page=fooeventspos-settings&tab=integration' ) . '">', '&nbsp;&rarr;</a>' );
			}

			// Custom descriptions for Stripe payment methods.
			if ( strpos( $fooeventspos_payment_method_id, 'stripe' ) === 0 ) {
				$fooeventspos_payment_method_options['description'] = sprintf( $fooeventspos_payment_method_options['description'], '<a href="' . admin_url( 'admin.php?page=fooeventspos-settings&tab=integration' ) . '">', '&nbsp;&rarr;</a>' );
			}

			$methods[] = FooEventsPOS_Payment_Method::fooeventspos_with_options( $fooeventspos_payment_method_options );

			unset( $fooeventspos_payment_method_options );
		}

		return $methods;
	}

	/**
	 * Remove POS payment methods from checkout
	 *
	 * @since 1.0.0
	 * @param array $available_gateways list of gateways used by WooCommerce.
	 */
	public function fooeventspos_conditional_payment_gateways( $available_gateways ) {

		if ( is_checkout() ) {
			foreach ( $this->fooeventspos_payment_method_ids as $fooeventspos_payment_method_id ) {

				unset( $available_gateways[ 'fooeventspos-' . $fooeventspos_payment_method_id ] );

			}
		}

		return $available_gateways;
	}
	/**
	 * Redirect cashiers to FooEvents POS.
	 *
	 * @since 1.0.0
	 * @param string $redirect_to Redirection location.
	 * @param string $user User details.
	 */
	public function fooeventspos_cashier_redirect( $redirect_to, $user ) {
		if ( wc_user_has_role( $user, 'fooeventspos_cashier' ) ) {
			$pos_page_slug = 'fooeventspos';

			$pos_page_slug = FooEvents_POS_Integration::fooeventspos_get_app_slug();

			$redirect_to = esc_url( home_url( '/' . $pos_page_slug . '/' ) );
		}

		return $redirect_to;
	}

	/**
	 * Allow 'fooeventspos_cashier' User Role to  view the Dashboard.
	 *
	 * @since 1.0.0
	 * @param boolean $prevent_access allow access.
	 */
	public function fooeventspos_cashier_admin_access( $prevent_access ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		if ( current_user_can( 'fooeventspos_cashier' ) && ! is_super_admin() ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
			$pos_page_slug = 'fooeventspos';

			$pos_page_slug = FooEvents_POS_Integration::fooeventspos_get_app_slug();

			wp_safe_redirect( esc_url( home_url( '/' . $pos_page_slug . '/' ) ) );

			exit;
		}
	}

	/**
	 * Store meta to identify the order as an online order.
	 *
	 * @since 1.0.0
	 * @param string $order_id The ID of the order being  created at checkout.
	 */
	public function fooeventspos_update_order_meta( $order_id ) {
		$wc_order = wc_get_order( $order_id );

		$wc_order->update_meta_data( '_fooeventspos_order_source', 'online' );
		$wc_order->update_meta_data( '_fooeventspos_payment_method', '' );

		$wc_order->save();
	}

	/**
	 * Update order and payments' Square fees.
	 *
	 * @since 1.10.2
	 * @param int $order_id The WooCommerce order ID to update.
	 */
	public function fooeventspos_do_update_order_square_fees( $order_id = '' ) {
		if ( '' !== $order_id ) {
			$order_square_payments = new WP_Query(
				array(
					'post_type'      => array( 'fooeventspos_payment' ),
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						'relation' => 'AND',
						array(
							'key'   => '_order_id',
							'value' => $order_id,
						),
						array(
							'key'     => '_payment_method_key',
							'value'   => 'square',
							'compare' => 'LIKE',
						),
						array(
						'key'     => '_transaction_id',
						'value'   => '',
						'compare' => '!=',
						),
					),
				)
			);

			if ( $order_square_payments->have_posts() ) {
				include_once WP_PLUGIN_DIR . '/fooevents-pos/fooeventspos-api-helper.php';

				$order_square_fees = 0.0;

				foreach ( $order_square_payments->posts as $payment_post_id ) {
					$payment_transaction_id = get_post_meta( $payment_post_id, '_transaction_id', true );

					$payment_square_fees = 0.0;
					$square_order_result = fooeventspos_get_square_order( $payment_transaction_id );

					if ( 'success' === $square_order_result['status'] ) {
						$square_order = $square_order_result['order'];

						if ( ! empty( $square_order['tenders'] ) ) {
							foreach ( $square_order['tenders'] as $square_tender ) {
								$payment_square_fees += ( (float) ( $square_tender['processing_fee_money']['amount'] ) / 100.0 );
							}
						}
					}

					update_post_meta( $payment_post_id, '_fooeventspos_square_fee_amount', $payment_square_fees );

					$order_square_fees += $payment_square_fees;
				}

				$wc_order = wc_get_order( $order_id );
				$wc_order->update_meta_data( '_fooeventspos_square_fee_amount', $order_square_fees );
				$wc_order->save();
			}
		}
	}

	/**
	 * Replace the default intval WooCommerce stock amount filter with floatval
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_remove_woocommerce_stock_amount_filters() {
		remove_filter( 'woocommerce_stock_amount', 'intval' );
		add_filter( 'woocommerce_stock_amount', 'floatval' );
	}

	/**
	 * Add additional WooCommerce product inventory settings for decimal quantities
	 *
	 * @since 1.0.0
	 * @param array  $settings The WooCommerce settings array.
	 * @param string $current_section The current WooCommerce settings section.
	 */
	public function fooeventspos_woocommerce_products_decimal_quantity_settings( $settings, $current_section ) {
		if ( 'inventory' === $current_section ) {
			$updated_settings = array();

			foreach ( $settings as $section ) {
				$updated_settings[] = $section;

				if ( isset( $section['type'] ) && 'title' === $section['type'] ) {
					$updated_settings[] = array(
						'name'              => $this->fooeventspos_phrases['setting_title_default_minimum_cart_quantity'],
						'desc_tip'          => $this->fooeventspos_phrases['setting_title_default_minimum_cart_quantity_tooltip'],
						'id'                => 'fooeventspos_products_default_minimum_cart_quantity',
						'type'              => 'text',
						'css'               => 'width:80px;',
						'desc'              => $this->fooeventspos_phrases['description_default_minimum_cart_quantity_step'],
						'default'           => '1',
						'custom_attributes' => array(
							'min'  => '0',
							'step' => '0.0001',

						),
					);

					$updated_settings[] = array(
						'name'              => $this->fooeventspos_phrases['setting_title_default_cart_quantity_step'],
						'desc_tip'          => $this->fooeventspos_phrases['setting_title_default_cart_quantity_step_tooltip'],
						'id'                => 'fooeventspos_products_default_cart_quantity_step',
						'type'              => 'text',
						'css'               => 'width:80px;',
						'desc'              => $this->fooeventspos_phrases['description_default_minimum_cart_quantity_step'],
						'default'           => '1',
						'custom_attributes' => array(
							'min'  => '0.0001',
							'step' => '0.0001',
						),
					);

					$updated_settings[] = array(
						'name'     => $this->fooeventspos_phrases['setting_title_default_cart_quantity_unit'],
						'desc_tip' => $this->fooeventspos_phrases['setting_title_default_cart_quantity_unit_tooltip'],
						'id'       => 'fooeventspos_products_default_cart_quantity_unit',
						'type'     => 'text',
						'css'      => 'width:80px;',
						'desc'     => $this->fooeventspos_phrases['description_default_cart_quantity_unit'],
						'default'  => '',
					);
				}
			}

			return $updated_settings;

		} else {

			return $settings;

		}
	}

	/**
	 * Sanitize the minimum cart quantity and step values to ensure they are numeric.
	 *
	 * @since 1.0.0
	 * @param string $value The value entered in the input field.
	 * @param array  $options The input field options.
	 * @param string $raw_value The raw value entered in the input field.
	 */
	public function fooeventspos_admin_settings_sanitize_option_fooeventspos_products_default_minimum_cart_quantity_step( $value, $options, $raw_value ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		$wc_thousand_separator = wc_get_price_thousand_separator();
		$wc_decimal_separator  = wc_get_price_decimal_separator();

		$fooeventspos_products_default_minimum_cart_quantity_step = preg_replace( '/[^0-9.]/', '', str_replace( $wc_decimal_separator, '.', str_replace( $wc_thousand_separator, '', sanitize_text_field( $value ) ) ) );

		if ( ! $fooeventspos_products_default_minimum_cart_quantity_step || ! is_numeric( $fooeventspos_products_default_minimum_cart_quantity_step ) || $fooeventspos_products_default_minimum_cart_quantity_step < 0 ) {
			$fooeventspos_products_default_minimum_cart_quantity_step = $options['default'];
		}

		return str_replace( '.', $wc_decimal_separator, $fooeventspos_products_default_minimum_cart_quantity_step );
	}

	/**
	 * Add minimum cart quantity and step value fields to the WooCommerce product inventory tab.
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_woocommerce_product_cart_minimum_step_field() {
		global $post;
		global $fooeventspos_products_default_minimum_cart_quantity;
		global $fooeventspos_products_default_cart_quantity_step;
		global $fooeventspos_products_default_cart_quantity_unit;

		$wc_product = wc_get_product( $post->ID );

		echo '<div class="options_group">';

		woocommerce_wp_text_input(
			array(
				'id'                => 'fooeventspos_product_minimum_cart_quantity',
				'label'             => $this->fooeventspos_phrases['setting_title_minimum_cart_quantity'],
				'type'              => 'text',
				'data_type'         => 'decimal',
				'placeholder'       => $fooeventspos_products_default_minimum_cart_quantity,
				'desc_tip'          => true,
				'description'       => sprintf( $this->fooeventspos_phrases['setting_title_minimum_cart_quantity_tooltip'], $fooeventspos_products_default_minimum_cart_quantity ),
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '0.0001',
				),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => 'fooeventspos_product_cart_quantity_step',
				'label'             => $this->fooeventspos_phrases['setting_title_cart_quantity_step'],
				'type'              => 'text',
				'data_type'         => 'decimal',
				'placeholder'       => $fooeventspos_products_default_cart_quantity_step,
				'desc_tip'          => true,
				'description'       => sprintf( $this->fooeventspos_phrases['setting_title_cart_quantity_step_tooltip'], $fooeventspos_products_default_cart_quantity_step ),
				'custom_attributes' => array(
					'min'  => '0.0001',
					'step' => '0.0001',
				),
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'          => 'fooeventspos_product_override_default_cart_quantity_unit',
				'label'       => $this->fooeventspos_phrases['setting_title_override_default_unit'],
				'desc_tip'    => false,
				'description' => sprintf( $this->fooeventspos_phrases['setting_title_override_default_unit_tooltip'], $fooeventspos_products_default_cart_quantity_unit ),
			)
		);

		$fooeventspos_product_override_default_cart_quantity_unit = $wc_product->get_meta( 'fooeventspos_product_override_default_cart_quantity_unit', true );

		$fooeventspos_product_cart_quantity_unit_args = array(
			'id'          => 'fooeventspos_product_cart_quantity_unit',
			'label'       => $this->fooeventspos_phrases['setting_title_cart_quantity_unit'],
			'desc_tip'    => true,
			'description' => sprintf( $this->fooeventspos_phrases['setting_title_cart_quantity_unit_tooltip'], $fooeventspos_products_default_cart_quantity_unit ),
		);

		if ( '' === $fooeventspos_product_override_default_cart_quantity_unit ) {
			$fooeventspos_product_cart_quantity_unit_args['wrapper_class'] = 'fooeventspos-hidden';
		}

		woocommerce_wp_text_input( $fooeventspos_product_cart_quantity_unit_args );

		wp_nonce_field( '_fooeventspos_save_product_decimal_quantity_meta_' . $wc_product->get_id(), '_fooeventspos_product_decimal_quantity_meta_nonce' );

		echo '</div>';
	}

	/**
	 * Save the minimum cart quantity and step value fields for a WooCommerce product.
	 *
	 * @since 1.0.0
	 * @param int $product_id The current product ID.
	 */
	public function fooeventspos_woocommerce_process_product_cart_minimum_step_field( $product_id ) {
		global $fooeventspos_products_default_minimum_cart_quantity;
		global $fooeventspos_products_default_cart_quantity_step;
		global $fooeventspos_products_default_cart_quantity_unit;

		$wc_product = wc_get_product( $product_id );

		$new_minimum_cart_quantity       = $fooeventspos_products_default_minimum_cart_quantity;
		$new_cart_quantity_step          = $fooeventspos_products_default_cart_quantity_step;
		$new_override_cart_quantity_unit = '';
		$new_cart_quantity_unit          = $fooeventspos_products_default_cart_quantity_unit;

		if ( isset( $_POST['_fooeventspos_product_decimal_quantity_meta_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_fooeventspos_product_decimal_quantity_meta_nonce'] ) ), '_fooeventspos_save_product_decimal_quantity_meta_' . $product_id ) ) {
			if ( isset( $_POST['fooeventspos_product_minimum_cart_quantity'] ) ) {
				$new_minimum_cart_quantity = sanitize_text_field( wp_unslash( $_POST['fooeventspos_product_minimum_cart_quantity'] ) );
			}

			if ( isset( $_POST['fooeventspos_product_cart_quantity_step'] ) ) {
				$new_cart_quantity_step = sanitize_text_field( wp_unslash( $_POST['fooeventspos_product_cart_quantity_step'] ) );
			}

			if ( isset( $_POST['fooeventspos_product_override_default_cart_quantity_unit'] ) ) {
				$new_override_cart_quantity_unit = sanitize_text_field( wp_unslash( $_POST['fooeventspos_product_override_default_cart_quantity_unit'] ) );
			}

			if ( isset( $_POST['fooeventspos_product_cart_quantity_unit'] ) ) {
				$new_cart_quantity_unit = sanitize_text_field( wp_unslash( $_POST['fooeventspos_product_cart_quantity_unit'] ) );
			}
		}

		$wc_product->update_meta_data( 'fooeventspos_product_minimum_cart_quantity', $new_minimum_cart_quantity );
		$wc_product->update_meta_data( 'fooeventspos_product_cart_quantity_step', $new_cart_quantity_step );
		$wc_product->update_meta_data( 'fooeventspos_product_override_default_cart_quantity_unit', $new_override_cart_quantity_unit );
		$wc_product->update_meta_data( 'fooeventspos_product_cart_quantity_unit', $new_cart_quantity_unit );
		$wc_product->save();
	}

	/**
	 * Filter for a product's minimum cart quantity amount.
	 *
	 * @since 1.0.0
	 * @param float      $min The current minimum cart quantity amount.
	 * @param WC_Product $wc_product The current WooCommerce cart product.
	 */
	public function fooeventspos_product_quantity_input_min( $min, $wc_product ) {
		global $fooeventspos_products_default_minimum_cart_quantity;

		$wc_thousand_separator = wc_get_price_thousand_separator();
		$wc_decimal_separator  = wc_get_price_decimal_separator();

		if ( false !== $wc_product ) {
			$fooeventspos_product_minimum_cart_quantity = $wc_product->get_meta( 'fooeventspos_product_minimum_cart_quantity', true );

			if ( '' === $fooeventspos_product_minimum_cart_quantity ) {
				$fooeventspos_product_minimum_cart_quantity = $fooeventspos_products_default_minimum_cart_quantity;
			}
		} else {
			$fooeventspos_product_minimum_cart_quantity = $fooeventspos_products_default_minimum_cart_quantity;
		}

		return str_replace( $wc_decimal_separator, '.', str_replace( $wc_thousand_separator, '', $fooeventspos_product_minimum_cart_quantity ) );
	}

	/**
	 * Filter for a product's cart quantity step amount.
	 *
	 * @since 1.0.0
	 * @param float      $step The current cart quantity step amount.
	 * @param WC_Product $wc_product The current WooCommerce cart product.
	 */
	public function fooeventspos_product_quantity_input_step( $step, $wc_product ) {
		global $fooeventspos_products_default_cart_quantity_step;

		$wc_thousand_separator = wc_get_price_thousand_separator();
		$wc_decimal_separator  = wc_get_price_decimal_separator();

		if ( false !== $wc_product ) {
			$fooeventspos_product_cart_quantity_step = $wc_product->get_meta( 'fooeventspos_product_cart_quantity_step', true );

			if ( '' === $fooeventspos_product_cart_quantity_step ) {
				$fooeventspos_product_cart_quantity_step = $fooeventspos_products_default_cart_quantity_step;
			}
		} else {
			$fooeventspos_product_cart_quantity_step = $fooeventspos_products_default_cart_quantity_step;
		}

		return str_replace( $wc_decimal_separator, '.', str_replace( $wc_thousand_separator, '', $fooeventspos_product_cart_quantity_step ) );
	}

	/**
	 * Filter for a product's quantity input arguments.
	 *
	 * @since 1.0.0
	 * @param array      $args The product's input arguments.
	 * @param WC_Product $wc_product The current WooCommerce product.
	 */
	public function fooeventspos_product_quantity_input_args( $args, $wc_product ) {
		global $fooeventspos_products_default_minimum_cart_quantity;
		global $fooeventspos_products_default_cart_quantity_step;

		$wc_thousand_separator = wc_get_price_thousand_separator();
		$wc_decimal_separator  = wc_get_price_decimal_separator();

		$product_id        = $wc_product->get_id();
		$product_parent_id = $wc_product->get_parent_id();

		if ( $product_parent_id > 0 ) {
			$product_id = $product_parent_id;
		}

		$temp_product = wc_get_product( $product_id );

		$fooeventspos_product_minimum_cart_quantity = $temp_product->get_meta( 'fooeventspos_product_minimum_cart_quantity', true );

		if ( '' === $fooeventspos_product_minimum_cart_quantity ) {
			$fooeventspos_product_minimum_cart_quantity = $fooeventspos_products_default_minimum_cart_quantity;
		}

		$args['min_value'] = str_replace( $wc_decimal_separator, '.', str_replace( $wc_thousand_separator, '', $fooeventspos_product_minimum_cart_quantity ) );

		if ( is_cart() ) {
			foreach ( WC()->cart->get_cart() as $key => $item ) {
				if ( $item['product_id'] === $product_id ) {
					$args['input_value'] = $item['quantity'];

					break;
				}
			}
		} else {
			$args['input_value'] = str_replace( $wc_decimal_separator, '.', str_replace( $wc_thousand_separator, '', $fooeventspos_product_minimum_cart_quantity ) );
		}

		$fooeventspos_product_cart_quantity_step = $temp_product->get_meta( 'fooeventspos_product_cart_quantity_step', true );

		if ( '' === $fooeventspos_product_cart_quantity_step ) {
			$fooeventspos_product_cart_quantity_step = $fooeventspos_products_default_cart_quantity_step;
		}

		$args['step']    = str_replace( $wc_decimal_separator, '.', str_replace( $wc_thousand_separator, '', $fooeventspos_product_cart_quantity_step ) );
		$args['pattern'] = '[0-9.]*';

		return $args;
	}

	/**
	 * Filter for the minimum cart quantity for a product variation.
	 *
	 * @since 1.0.0
	 * @param array               $args The product variation's arguments.
	 * @param WC_Product_Variable $wc_product The current WooCommerce product.
	 * @param WC_Product          $wc_product_variation The current WooCommerce product variation.
	 */
	public function fooeventspos_product_available_variation( $args, $wc_product, $wc_product_variation ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		global $fooeventspos_products_default_minimum_cart_quantity;

		$wc_thousand_separator = wc_get_price_thousand_separator();
		$wc_decimal_separator  = wc_get_price_decimal_separator();

		$fooeventspos_product_minimum_cart_quantity = $wc_product->get_meta( 'fooeventspos_product_minimum_cart_quantity', true );

		if ( '' === $fooeventspos_product_minimum_cart_quantity ) {
			$fooeventspos_product_minimum_cart_quantity = $fooeventspos_products_default_minimum_cart_quantity;
		}

		$args['min_qty'] = str_replace( $wc_decimal_separator, '.', str_replace( $wc_thousand_separator, '', $fooeventspos_product_minimum_cart_quantity ) );

		return $args;
	}

	/**
	 * Filter for the add to cart message HTML which caters for decimal quantities.
	 *
	 * @since 1.0.0
	 * @param string $message The add to cart message HTML.
	 * @param array  $products An array of all the products in the cart.
	 * @param bool   $show_qty A flag for whether to show the quantity or not.
	 */
	public function fooeventspos_add_to_cart_message_html( $message, $products, $show_qty ) {
		global $fooeventspos_products_default_minimum_cart_quantity;
		global $fooeventspos_products_default_cart_quantity_step;
		global $fooeventspos_products_default_cart_quantity_unit;

		$titles = array();
		$count  = 0.0;

		if ( ! is_array( $products ) ) {
			$products = array( $products => $fooeventspos_products_default_minimum_cart_quantity );
			$show_qty = false;
		}

		if ( ! $show_qty ) {
			$products = array_fill_keys( array_keys( $products ), $fooeventspos_products_default_minimum_cart_quantity );
		}

		foreach ( $products as $product_id => $qty ) {
			$wc_product = wc_get_product( $product_id );

			$fooeventspos_product_minimum_cart_quantity = $wc_product->get_meta( 'fooeventspos_product_minimum_cart_quantity', true );

			if ( '' === $fooeventspos_product_minimum_cart_quantity ) {
				$fooeventspos_product_minimum_cart_quantity = $fooeventspos_products_default_minimum_cart_quantity;
			}

			$fooeventspos_product_cart_quantity_step = $wc_product->get_meta( 'fooeventspos_product_cart_quantity_step', true );

			if ( '' === $fooeventspos_product_cart_quantity_step ) {
				$fooeventspos_product_cart_quantity_step = $fooeventspos_products_default_cart_quantity_step;
			}

			$fooeventspos_product_override_default_cart_quantity_unit = $wc_product->get_meta( 'fooeventspos_product_override_default_cart_quantity_unit', true );

			if ( 'yes' === $fooeventspos_product_override_default_cart_quantity_unit ) {
				$fooeventspos_product_cart_quantity_unit = $wc_product->get_meta( 'fooeventspos_product_cart_quantity_unit', true );
			} else {
				$fooeventspos_product_cart_quantity_unit = $fooeventspos_products_default_cart_quantity_unit;
			}

			$default_woocommerce_quantity = '' === $fooeventspos_product_minimum_cart_quantity && '' === $fooeventspos_product_cart_quantity_step;

			/* translators: %s: product name */
			$titles[] = apply_filters( 'woocommerce_add_to_cart_qty_html', ( $default_woocommerce_quantity && $qty > 1 ? absint( $qty ) . ' &times; ' : ( ! $default_woocommerce_quantity ? abs( $qty ) . ' ' . $fooeventspos_product_cart_quantity_unit . ' ' : '' ) ), $product_id ) . apply_filters( 'woocommerce_add_to_cart_item_name_in_quotes', sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'woocommerce' ), wp_strip_all_tags( get_the_title( $product_id ) ) ), $product_id );
			$count   += $qty;
		}

		$titles = array_filter( $titles );
		/* translators: %s: product name */
		$added_text = sprintf( _n( '%s has been added to your cart.', '%s have been added to your cart.', $count, 'woocommerce' ), wc_format_list_of_items( $titles ) );

		// Output success messages.
		if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
			$return_to = apply_filters( 'woocommerce_continue_shopping_redirect', wc_get_raw_referer() ? wp_validate_redirect( wc_get_raw_referer(), false ) : wc_get_page_permalink( 'shop' ) );
			$message   = sprintf( '<a href="%s" tabindex="1" class="button wc-forward">%s</a> %s', esc_url( $return_to ), esc_html__( 'Continue shopping', 'woocommerce' ), esc_html( $added_text ) );
		} else {
			$message = sprintf( '<a href="%s" tabindex="1" class="button wc-forward">%s</a> %s', esc_url( wc_get_cart_url() ), esc_html__( 'View cart', 'woocommerce' ), esc_html( $added_text ) );
		}

		return $message;
	}

	/**
	 * Filter for setting the product quantity to at least the minimum cart quantity.
	 *
	 * @since 1.0.0
	 * @param float $quantity The current cart quantity for the product.
	 * @param int   $product_id The ID of the current cart product.
	 */
	public function fooeventspos_add_to_cart_quantity( $quantity, $product_id ) {
		if ( $product_id ) {
			global $fooeventspos_products_default_minimum_cart_quantity;

			$wc_product = wc_get_product( $product_id );

			$fooeventspos_product_minimum_cart_quantity = $wc_product->get_meta( 'fooeventspos_product_minimum_cart_quantity', true );

			if ( ! $fooeventspos_product_minimum_cart_quantity ) {
				$fooeventspos_product_minimum_cart_quantity = $fooeventspos_products_default_minimum_cart_quantity;
			}

			if ( (float) $quantity < (float) $fooeventspos_product_minimum_cart_quantity ) {
				$quantity = $fooeventspos_product_minimum_cart_quantity;
			}
		}

		return $quantity;
	}

	/**
	 * Add product POS Settings tab
	 *
	 * @since 1.0.2
	 */
	public function fooeventspos_add_product_pos_settings_tab() {

		echo '<li class="custom_tab_pos_settings" id="custom_tab_pos_settings"><a href="#fooeventspos_pos_settings">' . esc_html( $this->fooeventspos_phrases['title_pos_settings'] ) . '</a></li>';
	}

	/**
	 * Adds POS settings options to the WooCommerce options tab
	 *
	 * @since 1.0.2
	 */
	public function fooeventspos_add_product_pos_settings_options() {

		global $post;

		$wc_product = wc_get_product( $post->ID );

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		$fooeventspos_phrases = $this->fooeventspos_phrases;

		// POS Settings.
		$fooeventspos_product_show_in_pos = $wc_product->get_meta( 'fooeventspos_product_show_in_pos', true );
		$fooeventspos_product_pin_in_pos  = $wc_product->get_meta( 'fooeventspos_product_pin_in_pos', true );

		$pos_settings = array(
			'event_settings'  => array(),
			'ticket_settings' => array(),
		);

		if ( ( is_plugin_active( 'fooevents/fooevents.php' ) || is_plugin_active_for_network( 'fooevents/fooevents.php' ) ) ) {

			// Event Settings.
			$fooevents_pos_attendee_details     = $wc_product->get_meta( 'WooCommerceEventsPOSAttendeeDetails', true );
			$fooevents_pos_attendee_email       = $wc_product->get_meta( 'WooCommerceEventsPOSAttendeeEmail', true );
			$fooevents_pos_attendee_telephone   = $wc_product->get_meta( 'WooCommerceEventsPOSAttendeeTelephone', true );
			$fooevents_pos_attendee_company     = $wc_product->get_meta( 'WooCommerceEventsPOSAttendeeCompany', true );
			$fooevents_pos_attendee_designation = $wc_product->get_meta( 'WooCommerceEventsPOSAttendeeDesignation', true );

			ob_start();

			require plugin_dir_path( __FILE__ ) . 'templates/product-pos-settings-required-attendee-fields.php';

			$required_attende_fields = ob_get_clean();

			$pos_settings['event_settings'][] = $required_attende_fields;

			// Ticket Settings.
			$pos_settings['ticket_settings'][] = FooEventsPOS_FooEvents_Integration::fooeventspos_generate_pos_theme_options( $wc_product );
			$pos_settings['ticket_settings'][] = FooEventsPOS_FooEvents_Integration::fooeventspos_add_product_pos_tickets_options_tab_options( $wc_product );
		}

		require_once plugin_dir_path( __FILE__ ) . 'templates/product-pos-settings.php';
	}

	/**
	 * Processes the POS settings meta box form once the publish / update button is clicked.
	 *
	 * @global object $woocommerce_errors
	 * @param int $product_id The product ID.
	 */
	public function fooeventspos_process_product_pos_settings_options( $product_id ) {

		global $woocommerce_errors;

		if ( isset( $_POST['_fooeventspos_pos_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_fooeventspos_pos_settings_nonce'] ) ), '_fooeventspos_save_pos_settings_' . $product_id ) ) {

			$wc_product = wc_get_product( $product_id );

			$fooeventspos_product_show_in_pos = isset( $_POST['fooeventspos_product_show_in_pos'] ) ? 'yes' : 'no';

			$wc_product->update_meta_data( 'fooeventspos_product_show_in_pos', $fooeventspos_product_show_in_pos );

			$fooeventspos_product_pin_in_pos = isset( $_POST['fooeventspos_product_pin_in_pos'] ) ? 'yes' : 'no';

			$wc_product->update_meta_data( 'fooeventspos_product_pin_in_pos', $fooeventspos_product_pin_in_pos );

			if ( isset( $_POST['WooCommerceEventsPOSAttendeeDetails'] ) ) {

				$fooevents_pos_attendee_details = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPOSAttendeeDetails'] ) );
				$wc_product->update_meta_data( 'WooCommerceEventsPOSAttendeeDetails', $fooevents_pos_attendee_details );

			}

			if ( isset( $_POST['WooCommerceEventsPOSAttendeeEmail'] ) ) {

				$fooevents_pos_attendee_email = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPOSAttendeeEmail'] ) );
				$wc_product->update_meta_data( 'WooCommerceEventsPOSAttendeeEmail', $fooevents_pos_attendee_email );

			}

			if ( isset( $_POST['WooCommerceEventsPOSAttendeeTelephone'] ) ) {

				$fooevents_pos_attendee_telephone = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPOSAttendeeTelephone'] ) );
				$wc_product->update_meta_data( 'WooCommerceEventsPOSAttendeeTelephone', $fooevents_pos_attendee_telephone );

			}

			if ( isset( $_POST['WooCommerceEventsPOSAttendeeCompany'] ) ) {

				$fooevents_pos_attendee_company = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPOSAttendeeCompany'] ) );
				$wc_product->update_meta_data( 'WooCommerceEventsPOSAttendeeCompany', $fooevents_pos_attendee_company );

			}

			if ( isset( $_POST['WooCommerceEventsPOSAttendeeDesignation'] ) ) {

				$fooevents_pos_attendee_designation = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPOSAttendeeDesignation'] ) );
				$wc_product->update_meta_data( 'WooCommerceEventsPOSAttendeeDesignation', $fooevents_pos_attendee_designation );

			}

			$wc_product->save();
		}
	}

	/**
	 * Add additional options to variations.
	 *
	 * @since 1.1.0
	 * @param string  $loop The WordPress loop.
	 * @param array   $variation_data The variation data.
	 * @param WP_POST $variation The variation post object.
	 */
	public function fooeventspos_add_variation_options( $loop, $variation_data, $variation ) {

		// Show variation in POS.
		$fooeventspos_variation_show_in_pos = get_post_meta( $variation->ID, 'fooeventspos_variation_show_in_pos', true );

		woocommerce_wp_checkbox(
			array(
				'id'          => 'fooeventspos_variation_show_in_pos[' . $loop . ']',
				'class'       => 'fooeventspos_variation_show_in_pos',
				'label'       => $this->fooeventspos_phrases['checkbox_show_variation_in_pos'],
				'desc_tip'    => true,
				'description' => $this->fooeventspos_phrases['checkbox_show_variation_in_pos_tooltip'],
				'value'       => ( '' !== $fooeventspos_variation_show_in_pos ? $fooeventspos_variation_show_in_pos : 'yes' ),
			)
		);

		wp_nonce_field( '_fooeventspos_add_variation_options_' . $variation->ID, '_fooeventspos_add_variation_options_nonce_' . $variation->ID );
	}

	/**
	 * Save additional options added to variations.
	 *
	 * @since 1.1.0
	 * @param int $variation_id The variation ID.
	 * @param int $index The variation index.
	 */
	public function fooeventspos_save_product_variation( $variation_id, $index ) {

		if ( isset( $_POST[ '_fooeventspos_add_variation_options_nonce_' . $variation_id ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ '_fooeventspos_add_variation_options_nonce_' . $variation_id ] ) ), '_fooeventspos_add_variation_options_' . $variation_id ) ) {

			$fooeventspos_variation_show_in_pos = isset( $_POST['fooeventspos_variation_show_in_pos'][ $index ] ) ? 'yes' : 'no';

			$wc_product_variation = wc_get_product( $variation_id );

			$wc_product_variation->update_meta_data( 'fooeventspos_variation_show_in_pos', $fooeventspos_variation_show_in_pos );
			$wc_product_variation->save();
		}
	}

	/**
	 * Get customers based on search input.
	 *
	 * @since 1.6.0
	 */
	public function fooeventspos_get_customers() {
		$customer_roles_array   = get_option( 'globalFooEventsPOSCustomerUserRole', array( 'customer', 'subscriber' ) );
		$default_customer_nonce = '';

		if ( isset( $_GET['customer_roles'] ) ) {
			$customer_roles_param = json_decode( sanitize_text_field( wp_unslash( $_GET['customer_roles'] ) ) );

			if ( null !== $customer_roles_param && ! empty( $customer_roles_param ) ) {
				$customer_roles_array = $customer_roles_param;
			}
		}

		if ( isset( $_GET['default_customer_nonce'] ) ) {
			$default_customer_nonce = sanitize_text_field( wp_unslash( $_GET['default_customer_nonce'] ) );
		}

		if ( ! wp_verify_nonce( $default_customer_nonce, 'fooeventspos_default_customer' ) ) {
			die( esc_attr__( 'Security check failed - FooEvents POS 0001', 'fooevents-pos' ) );
		}

		if ( isset( $_GET['q'] ) && ! empty( $_GET['q'] ) ) {
			$term = sanitize_text_field( wp_unslash( $_GET['q'] ) );

			$returned_users = array();

			if ( ! empty( $term ) ) {

				$query = new WP_User_Query( // phpcs:ignore WordPress.WP.DiscouragedFunctions.wp_user_query_search
					array(
						'search'         => '*' . esc_attr( $term ) . '*',
						'search_columns' => array( 'user_login', 'user_url', 'user_email', 'user_nicename', 'display_name' ),
						'role__in'       => $customer_roles_array,
						'fields'         => 'ids',
					)
				);

				$query2 = new WP_User_Query( // phpcs:ignore WordPress.WP.DiscouragedFunctions.wp_user_query_search
					array(
						'fields'     => 'ids',
						'role__in'   => $customer_roles_array,
						'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
							'relation' => 'OR',
							array(
								'key'     => 'first_name',
								'value'   => $term,
								'compare' => 'LIKE',
							),
							array(
								'key'     => 'last_name',
								'value'   => $term,
								'compare' => 'LIKE',
							),
						),
					)
				);

				$user_ids = wp_parse_id_list( array_merge( (array) $query->get_results(), (array) $query2->get_results() ) );

				foreach ( $user_ids as $user_id ) {
					$customer = new WC_Customer( $user_id );

					/* translators: 1: user display name 2: user ID 3: user email */
					$returned_users[ (string) $user_id ] = sprintf(
						/* translators: $1: customer name, $2 customer id, $3: customer email */
						esc_html__( '%1$s (#%2$s - %3$s)', 'woocommerce' ),
						$customer->get_first_name() . ' ' . $customer->get_last_name(),
						$customer->get_id(),
						$customer->get_email()
					);
				}
			}

			echo wp_json_encode( $returned_users );

		}

		wp_die();
	}

	/**
	 * Add meta boxes to the order edit screen.
	 *
	 * @since 1.8.0
	 * @param string $post_type The post type.
	 * @param object $post The order post object.
	 */
	public function fooeventspos_add_order_meta_boxes( $post_type, $post ) {
		$screens = array( 'shop_orders', 'shop_order' );

		$order_id = '';

		if ( isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order_id = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} else {
			$order_id = $post->ID;
		}

		if ( ( 'shop_order' === get_post_type( $order_id ) && isset( $_GET['post'] ) ) || ( isset( $_GET['page'] ) && 'wc-orders' === $_GET['page'] && '' !== $order_id ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$wc_order = wc_get_order( $order_id );

			if ( 'fooeventspos_app' === $wc_order->get_meta( '_fooeventspos_order_source', true ) ) {
				$fooeventspos_payments_json = $wc_order->get_meta( '_fooeventspos_payments', true );

				if ( '' === $fooeventspos_payments_json ) {
					$order_payments = new WP_Query(
						array(
							'post_type'      => array( 'fooeventspos_payment' ),
							'posts_per_page' => -1,
							'fields'         => 'ids',
							'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
								array(
									'key'   => '_order_id',
									'value' => $order_id,
								),
							),
						)
					);

					if ( $order_payments->have_posts() ) {
						// Regenerate order payments meta from existing payment posts.
						$order_payments_array = array();

						foreach ( $order_payments->posts as $order_payment_id ) {
							$order_payments_array[] = array(
								'pd'    => (string) strtotime( get_post_datetime( $order_payment_id, 'date', 'gmt' )->format( 'Y-m-d H:i:s+00:00' ) ),
								'oid'   => (string) $order_id,
								'opmk'  => get_post_meta( $order_payment_id, '_payment_method_key', true ),
								'oud'   => get_post_meta( $order_payment_id, '_cashier', true ),
								'tid'   => get_post_meta( $order_payment_id, '_transaction_id', true ),
								'pa'    => get_post_meta( $order_payment_id, '_amount', true ),
								'np'    => get_post_meta( $order_payment_id, '_number_of_payments', true ),
								'pn'    => get_post_meta( $order_payment_id, '_payment_number', true ),
								'pap'   => get_post_meta( $order_payment_id, '_payment_paid', true ),
								'par'   => get_post_meta( $order_payment_id, '_payment_refunded', true ),
								'pe'    => get_post_meta( $order_payment_id, '_payment_extra', true ),
								'soar'  => get_post_meta( $order_payment_id, '_fooeventspos_square_order_auto_refund', true ),
								'sfa'   => get_post_meta( $order_payment_id, '_fooeventspos_square_fee_amount', true ),
								'fspid' => (string) $order_payment_id,
							);
						}

						$fooeventspos_payments_json = wp_json_encode( $order_payments_array );
					} else {
						// Create new payment post for order.
						$payment_args = array(
							'pd'   => $wc_order->get_date_created()->getTimestamp(),
							'oid'  => $wc_order->get_id(),
							'opmk' => $wc_order->get_meta( '_fooeventspos_payment_method', true ),
							'oud'  => $wc_order->get_meta( '_fooeventspos_user_id', true ),
							'tid'  => '' !== $wc_order->get_meta( '_fooeventspos_square_order_id', true ) ? $wc_order->get_meta( '_fooeventspos_square_order_id', true ) : ( '' !== $wc_order->get_meta( '_fooeventspos_stripe_payment_id', true ) ? $wc_order->get_meta( '_fooeventspos_stripe_payment_id', true ) : '' ),
							'pa'   => $wc_order->get_total(),
							'np'   => '1',
							'pn'   => '1',
							'pap'  => '1',
							'par'  => 'refunded' === $wc_order->get_status() ? '1' : '0',
						);

						$payment_post = FooEventsPOS_Payments::fooeventspos_create_update_payment( $payment_args );

						if ( ! is_wp_error( $payment_post ) ) {
							$payment_args['fspid'] = (string) $payment_post['ID'];
							$payment_args['pe']    = get_post_meta( $payment_post['ID'], '_payment_extra', true );
							$payment_args['soar']  = get_post_meta( $payment_post['ID'], '_fooeventspos_square_order_auto_refund', true );
							$payment_args['sfa']   = get_post_meta( $payment_post['ID'], '_fooeventspos_square_fee_amount', true );

							$fooeventspos_payments_json = wp_json_encode( array( $payment_args ) );
						}
					}

					$wc_order->update_meta_data( '_fooeventspos_payments', $fooeventspos_payments_json );

					ob_start();
					$wc_order->save();
					ob_end_clean();
				}

				$fooeventspos_payments = json_decode( $fooeventspos_payments_json, true );

				if ( null !== $fooeventspos_payments ) {
					foreach ( $screens as $screen_name ) {
						$screen = class_exists( Automattic\WooCommerce\Utilities\OrderUtil::class ) && Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ? wc_get_page_screen_id( $screen_name ) : $screen_name;

						add_meta_box(
							'fooeventspos_pos_payments',
							$this->fooeventspos_phrases['title_fooeventspos_pos_payments'],
							array( &$this, 'fooeventspos_add_fooeventspos_pos_payments_meta_box_details' ),
							$screen,
							'normal'
						);
					}
				}
			}
		}
	}

	/**
	 * Add POS payments details to meta box.
	 *
	 * @since 1.8.0
	 * @param object $post The WordPress post object.
	 */
	public function fooeventspos_add_fooeventspos_pos_payments_meta_box_details( $post ) {
		$order_id = '';

		if ( isset( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order_id = sanitize_text_field( wp_unslash( $_GET['id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} else {
			$order_id = $post->ID;
		}

		if ( ( 'shop_order' === get_post_type( $order_id ) && isset( $_GET['post'] ) ) || ( isset( $_GET['page'] ) && 'wc-orders' === $_GET['page'] && '' !== $order_id ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$wc_order = wc_get_order( $order_id );

			if ( 'fooeventspos_app' === $wc_order->get_meta( '_fooeventspos_order_source', true ) ) {
				$fooeventspos_payments_json = $wc_order->get_meta( '_fooeventspos_payments', true );

				$fooeventspos_payments_data = json_decode( $fooeventspos_payments_json, true );

				$fooeventspos_payments = array();

				$update_fooeventspos_payments = false;

				foreach ( $fooeventspos_payments_data as &$payment ) {
					$payment_id = $payment['fspid'];

					$payment_post = get_post( $payment_id, 'ARRAY_A' );

					if ( null === $payment_post ) {
						$payment_post = FooEventsPOS_Payments::fooeventspos_create_update_payment( $payment );

						if ( ! is_wp_error( $payment_post ) ) {
							$payment_id = $payment_post['ID'];

							$payment['fspid'] = (string) $payment_id;
							$payment['pe']    = get_post_meta( $payment_id, '_payment_extra', true );
							$payment['soar']  = get_post_meta( $payment_id, '_fooeventspos_square_order_auto_refund', true );
							$payment['sfa']   = get_post_meta( $payment_id, '_fooeventspos_square_fee_amount', true );

							$update_fooeventspos_payments = true;
						}
					}

					$payment_date_timestamp = strtotime( $payment_post['post_date'] );

					$payment_data = get_post_meta( $payment_id );

					$payment_data['_payment_id'] = $payment_id;

					$date_format = get_option( 'date_format' );
					$time_format = get_option( 'time_format' );

					$payment_data['_payment_date'] = date_i18n( $date_format . ' ' . $time_format, $payment_date_timestamp );

					$fooeventspos_payments[] = $payment_data;
				}

				if ( $update_fooeventspos_payments ) {
					$wc_order->update_meta_data( '_fooeventspos_payments', wp_json_encode( $fooeventspos_payments_data ) );

					ob_start();
					$wc_order->save();
					ob_end_clean();
				}

				$fooeventspos_phrases = $this->fooeventspos_phrases;

				require_once plugin_dir_path( __FILE__ ) . 'templates/order-meta-box-pos-payments.php';
			}
		}
	}
}
