<?php
/**
 * Main plugin class.
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * Main plugin class.
 */
class FooEvents {

	/**
	 * WooCommerce helper object
	 *
	 * @var object $woo_helper WooCommerce helper object
	 */
	private $woo_helper;

	/**
	 * ICS helper object
	 *
	 * @var object $ics_helper ICS helper object
	 */
	private $ics_helper;

	/**
	 * Configuration object
	 *
	 * @var array $config contains paths and other configurations
	 */
	private $config;

	/**
	 * XML-RPC helper object
	 *
	 * @var object $xml_rpc_helper XML-RPC helper object
	 */
	private $xml_rpc_helper;

	/**
	 * Checkout helper object
	 *
	 * @var object $checkout_helper Checkout helper object
	 */
	private $checkout_helper;

	/**
	 * Ticket helper object
	 *
	 * @var object $ticket_helper Ticket helper object
	 */
	private $ticket_helper;

	/**
	 * Barcode helper object
	 *
	 * @var object $barcode_helper Barcode helper object
	 */
	private $barcode_helper;

	/**
	 * Reports helper object
	 *
	 * @var object $ticket_helper Reports helper object
	 */
	private $event_report_helper;

	/**
	 * Update helper object
	 *
	 * @var object $update_helper Update helper object
	 */
	private $update_helper;

	/**
	 * REST helper object
	 *
	 * @var object $rest_api_helper REST helper object
	 */
	private $rest_api_helper;

	/**
	 * Orders helper object
	 *
	 * @var object $orders_helper Orders helper object
	 */
	private $orders_helper;

	/**
	 * Zoom helper object
	 *
	 * @var object $zoom_api_helper Zoom helper object
	 */
	private $zoom_api_helper;

	/**
	 * Follow-up Email helper object
	 *
	 * @var object $follow_up_emails_helper Follow-up Email helper object
	 */
	private $follow_up_emails_helper;

	/**
	 * Mailchimp helper object
	 *
	 * @var object $mailchimp_helper Mailchimp helper object
	 */
	private $mailchimp_helper;

	/**
	 * Salt value
	 *
	 * @var string $salt Salt value
	 */
	private $salt;

	/**
	 * Theme helper object
	 *
	 * @var object $theme_helper Theme helper object
	 */
	private $theme_helper;

	/**
	 * API Key
	 *
	 * @var string $api_key API Key
	 */
	private $api_key;

	/**
	 * Plugin file
	 *
	 * @var string $plugin_file Plugin file
	 */
	private $plugin_file;

	/**
	 * Slug
	 *
	 * @var string $slug Slug
	 */
	private $slug;

	/**
	 * On plugin load
	 */
	public function __construct() {

		$plugin = plugin_basename( __FILE__ );

		$this->api_key     = get_option( 'globalWooCommerceEventsAPIKey', true );
		$this->plugin_file = __FILE__;

		add_action( 'init', array( $this, 'plugin_init' ) );
		add_action( 'init', array( $this, 'load_text_domain' ) );
		add_action( 'admin_init', array( $this, 'register_scripts' ) );
		add_action( 'admin_notices', array( $this, 'check_woocommerce_events' ) );
		add_action( 'admin_notices', array( $this, 'check_fooevents_errors' ) );
		add_action( 'admin_notices', array( $this, 'reviews_notice' ) );
		add_action( 'admin_notices', array( $this, 'classic_checkout_express_notice' ) );
		add_action( 'wp_ajax_dismiss_fooevents_review_notice', array( $this, 'dismiss_reviews_notice' ) );
		add_action( 'wp_ajax_dismiss_fooevents_express_notice', array( $this, 'dismiss_express_checkout_notice' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts_frontend' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles_frontend' ) );
		add_action( 'admin_init', array( $this, 'register_styles' ) );
		add_action( 'admin_init', array( $this, 'fooevents_checkins_register_importer' ) );
		add_action( 'admin_menu', array( $this, 'add_woocommerce_submenu' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 10 );
		add_action( 'wp_ajax_fooevents_ics', array( $this, 'fooevents_ics' ) );
		add_action( 'wp_ajax_nopriv_fooevents_ics', array( $this, 'fooevents_ics' ) );
		add_action( 'wp_ajax_fooevents_wallet_pass', array( $this, 'fooevents_wallet_pass' ) );
		add_action( 'wp_ajax_nopriv_fooevents_wallet_pass', array( $this, 'fooevents_wallet_pass' ) );

		add_action( 'pre_get_posts', array( $this, 'remove_expired_posts' ) );
		add_action( 'woocommerce_product_related_posts_query', array( $this, 'remove_expired_posts_related_products' ) );
		add_filter( 'woocommerce_shortcode_products_query', array( $this, 'remove_expired_posts_woocommerce_shortcode' ) );

		add_filter( 'woocommerce_variation_is_purchasable', array( $this, 'remove_add_to_cart_expired' ), 10, 2 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'remove_add_to_cart_expired_text' ) );
		add_filter( 'woocommerce_is_purchasable', array( $this, 'remove_add_to_cart_expired' ), 10, 2 );

		add_action( 'wpml_loaded', array( $this, 'fooevents_wpml_loaded' ) );

		add_action( 'wp_ajax_woocommerce_events_cancel', array( $this, 'woocommerce_events_cancel' ) );
		add_action( 'wp_ajax_nopriv_woocommerce_events_cancel', array( $this, 'woocommerce_events_cancel' ) );

		add_filter( 'plugin_action_links_fooevents/fooevents.php', array( $this, 'add_plugin_links' ) );
		add_filter( 'add_to_cart_text', array( $this, 'woo_custom_cart_button_text' ) );
		add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'woo_custom_cart_button_text' ) );
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'woo_custom_cart_button_text' ) );

		add_filter( 'parse_query', array( $this, 'fooevents_filter_ticket_results' ) );

		add_action( 'admin_init', array( &$this, 'assign_admin_caps' ) );
		register_activation_hook( __FILE__, array( $this, 'create_fooevents_table' ) );
		register_deactivation_hook( __FILE__, array( &$this, 'remove_event_user_caps' ) );

		add_action( 'admin_init', array( $this, 'register_settings_options' ) );
		add_filter( 'custom_menu_order', array( $this, 'fooevents_menu_order' ) );
		add_action( 'update_fooevents_addtowallet_passes_action', array( $this, 'do_update_fooevents_addtowallet_passes' ), 10, 1 );
	}

	/**
	 * Basic checks to see if FooEvents will run correctly.
	 */
	public function check_woocommerce_events() {

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( is_plugin_active( 'woocommerce_events/woocommerce-events.php' ) ) {

				$this->output_notices( array( __( 'WooCommerce Events has re-branded to FooEvents. Please disable and remove the older WooCommerce Events plugin.', 'woocommerce-events' ) ) );

		}

		if ( ! is_writable( $this->config->uploads_path ) ) {

			// translators: Placeholder is for uploads path.
			$this->output_notices( array( sprintf( __( 'Directory %s is not writeable', 'woocommerce-events' ), $this->config->uploads_path ) ) );

			if ( ! is_writable( $this->config->barcode_path ) ) {

				// translators: Placeholder is for the barcode path.
				$this->output_notices( array( sprintf( __( 'Directory %s is not writeable', 'woocommerce-events' ), $this->config->barcode_path ) ) );

			}

			if ( ! is_writable( $this->config->theme_packs_path ) ) {

				// translators: Placeholder is for the themes path.
				$this->output_notices( array( sprintf( __( 'Directory %s is not writeable', 'woocommerce-events' ), $this->config->theme_packs_path ) ) );

			}

			if ( ! is_writable( $this->config->theme_packs_path . 'default_ticket_theme' ) ) {

				// translators: Placeholder is for the default ticket theme path.
				$this->output_notices( array( sprintf( __( 'Directory %s is not writeable', 'woocommerce-events' ), $this->config->theme_packs_path . 'default_ticket_theme' ) ) );

			}

			if ( ! is_writable( $this->config->ics_path ) ) {

				// translators: Placeholder is for the ICS path.
				$this->output_notices( array( sprintf( __( 'Directory %s is not writeable', 'woocommerce-events' ), $this->config->ics_path ) ) );

			}
		}

		if ( file_exists( $this->config->email_template_path_theme . 'email/header.php' ) || file_exists( $this->config->email_template_path_theme . 'email/footer.php' ) || file_exists( $this->config->email_template_path_theme . 'email/ticket.php' ) || file_exists( $this->config->email_template_path_theme . 'email/tickets.php' ) ) {

			$this->output_notices( array( sprintf( __( 'We have detected that you have overridden FooEvents ticket template files in your Wordpress theme. Please move these to an overridden ticket theme directory. Please consult the FooEvents documentation on how to do this.', 'woocommerce-events' ), $this->config->theme_packs_path . 'default_ticket_theme' ) ) );

		}

		$global_woocommerce_events_enable_qr_code = get_option( 'globalWooCommerceEventsEnableQRCode' );

		if ( $global_woocommerce_events_enable_qr_code && ! extension_loaded( 'gd' ) ) {

			$this->output_notices( array( sprintf( __( 'PHP GD library is a requirement for FooEvents to generate QR codes. Please contact your web host to enable PHP GD libraries.', 'woocommerce-events' ), $this->config->theme_packs_path . 'default_ticket_theme' ) ) );

		}
	}

	/**
	 * Checks for and displays FooEvents errors.
	 */
	public function check_fooevents_errors() {

		$error_codes = array(
			'1' => __( 'Purchaser username already used. Ticket was not created.', 'woocommerce-events' ),
			'2' => __( 'An error occured. Ticket was not created.', 'woocommerce-events' ),
			'3' => __( 'Purchaser email address already used. Ticket was not created.', 'woocommerce-events' ),
		);

		if ( ! empty( $_GET['fooevents_error'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification

			$error_code = sanitize_text_field( wp_unslash( $_GET['fooevents_error'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$this->output_notices( array( $error_codes[ $error_code ] ) );

		}
	}

	/**
	 * Display review notice
	 */
	public function reviews_notice() {

		if ( get_user_meta( get_current_user_id(), 'dismissed_fooevents_review_notice', true ) ) {

			return;
		}

		$global_woocommerce_events_api_key = get_option( 'globalWooCommerceEventsAPIKey' );
		$global_woocommerce_envato_api_key = get_option( 'globalWooCommerceEnvatoAPIKey' );
		$number_tickets                    = wp_count_posts( 'event_magic_tickets' );
		$number_tickets_sold               = 100;

		if ( $number_tickets->publish < $number_tickets_sold || ( empty( $global_woocommerce_events_api_key ) && empty( $global_woocommerce_envato_api_key ) ) ) {

			return;
		}

		?>
		<div class="notice notice-info is-dismissible" id="fooevents-review-notice">
			<p>
				<strong><?php esc_html_e( 'Thank you for using FooEvents!', 'woocommerce_events' ); ?></strong> 
				<?php esc_html_e( 'Please take a moment to share your experience on Trustpilot—your feedback helps us to improve, grow and provide an even better product!', 'woocommerce_events' ); ?>
				<a href="<?php echo esc_url( 'https://www.trustpilot.com/evaluate/www.fooevents.com' ); ?>">
					<?php esc_html_e( 'Submit a Review', 'woocommerce_events' ); ?>
				</a>			
			</p>
		</div>
		<script>
			jQuery(document).on('click', '#fooevents-review-notice .notice-dismiss', function () {
				jQuery.post(ajaxurl, {
					action: 'dismiss_fooevents_review_notice',
					security: '<?php echo esc_attr( wp_create_nonce( 'dismiss_fooevents_review_notice' ) ); ?>'
				});
			});
		</script>
		<?php
	}

	/**
	 * If user hides FooEvents reviews notice.
	 */
	public function dismiss_reviews_notice() {

		check_ajax_referer( 'dismiss_fooevents_review_notice', 'security' );
		update_user_meta( get_current_user_id(), 'dismissed_fooevents_review_notice', true );
		wp_die();
	}

	/**
	 * Displays admin notice if Classic Checkout AND Apple Pay / Google Pay Stripe checkout is enabled.
	 */
	public function classic_checkout_express_notice() {

		// Don’t show if user has dismissed.
		if ( get_user_meta( get_current_user_id(), 'dismissed_fooevents_express_notice', true ) ) {
			return;
		}

		$is_checkout_block_enabled = $this->is_checkout_block_enabled();
		$stripe_settings           = get_option( 'woocommerce_stripe_settings', array() );

		$express_checkout_enabled = ( isset( $stripe_settings['payment_request'] ) && 'yes' === $stripe_settings['payment_request'] );

		if ( $express_checkout_enabled && ! $is_checkout_block_enabled ) {
			?>
		<div class="notice notice-error is-dismissible" id="fooevents-express-checkout-notice">
			<p>
					<strong><?php esc_html_e( 'Important: ', 'woocommerce_events' ); ?></strong> 
					<?php esc_html_e( "Your site appears to have Stripe's Apple Pay and Google Pay Express Checkout payment options enabled. Please note, the WooCommerce Stripe Payment Gateway plugin recently switched to the new WooCommerce Store API and is no longer compatible with FooEvents when using the classic shortcode-based WooCommerce Checkout experience. Please switch to the new WooCommerce Checkout block to continue using Apple Pay with FooEvents.", 'woocommerce_events' ); ?> 
					<a href="<?php echo esc_url( 'https://help.fooevents.com/docs/frequently-asked-questions/installation-setup/fooevents-stripe-apple-pay-google-pay-support/' ); ?>">
					<?php esc_html_e( 'Learn more', 'woocommerce_events' ); ?>
					</a>			
				</p>
		</div>
		<script>
			jQuery(document).on('click', '#fooevents-express-checkout-notice .notice-dismiss', function () {
				jQuery.post(ajaxurl, {
					action: 'dismiss_fooevents_express_notice',
					security: '<?php echo esc_attr( wp_create_nonce( 'dismiss_fooevents_express_notice' ) ); ?>'
				});
			});
		</script>
			<?php
		}
	}

	/**
	 * Dsmiss Express Checkout notice if Classic Checkout AND Apple Pay / Google Pay Stripe checkout is enabled.
	 */
	public function dismiss_express_checkout_notice() {

		check_ajax_referer( 'dismiss_fooevents_express_notice', 'security' );
		update_user_meta( get_current_user_id(), 'dismissed_fooevents_express_notice', true );
		wp_die();
	}

	/**
	 *  Initialize events plugin and helpers.
	 */
	public function plugin_init() {

		// Main config.
		$this->config = new FooEvents_Config();

		$fooevents_db = get_option( 'fooevents_db', false );

		if ( ! $fooevents_db ) {

			$this->create_fooevents_table();

		} else {

			$this->update_fooevents_table();

		}

		// WooHelper.
		require_once $this->config->class_path . 'class-fooevents-woo-helper.php';
		$this->woo_helper = new FooEvents_Woo_Helper( $this->config );

		// ICSHelper.
		require_once $this->config->class_path . 'class-fooevents-ics-helper.php';
		$this->ics_helper = new FooEvents_ICS_Helper( $this->config );

		// CheckoutHelper.
		require_once $this->config->class_path . 'class-fooevents-checkout-helper.php';
		$this->checkout_helper = new FooEvents_Checkout_Helper( $this->config );

		// ThemeHelper.
		require_once $this->config->class_path . 'class-fooevents-theme-helper.php';
		$this->theme_helper = new FooEvents_Theme_Helper( $this->config );

		// EventReportHelper.
		require_once $this->config->class_path . 'class-fooevents-event-report-helper.php';
		$this->event_report_helper = new FooEvents_Event_Report_Helper( $this->config );

		// BarcodeHelper.
		require_once $this->config->class_path . 'class-fooevents-barcode-helper.php';
		$this->barcode_helper = new FooEvents_Barcode_Helper( $this->config );

		// UpdateHelper.
		require_once $this->config->class_path . 'updatehelper.php';
		$this->update_helper = new FooEvents_Update_Helper( $this->config );

		// API helper methods.
		require_once $this->config->class_path . 'apihelper.php';

		// XMLRPCHelper.
		require_once $this->config->class_path . 'class-fooevents-xmlrpc-helper.php';
		$this->xml_rpc_helper = new FooEvents_XMLRPC_Helper( $this->config );

		// RESTAPIHelper.
		require_once $this->config->class_path . 'class-fooevents-rest-api-helper.php';
		$this->rest_api_helper = new FooEvents_REST_API_Helper();

		// OrdersHelper.
		require_once $this->config->class_path . 'class-fooevents-orders-helper.php';
		$this->orders_helper = new FooEvents_Orders_Helper( $this->config );

		// ZoomAPIHelper.
		require_once $this->config->class_path . 'class-fooevents-zoom-api-helper.php';
		$this->zoom_api_helper = new FooEvents_Zoom_API_Helper( $this->config );

		if ( is_plugin_active( 'woocommerce-follow-up-emails/woocommerce-follow-up-emails.php' ) || is_plugin_active_for_network( 'woocommerce-follow-up-emails/woocommerce-follow-up-emails.php' ) ) {

			// follow_up_emails_helper.
			require_once $this->config->class_path . 'class-fooevents-follow-up-emails-helper.php';
			$this->follow_up_emails_helper = new FooEvents_Follow_Up_Emails_Helper( $this->config );

		}

		// MailchimpHelper.
		require_once $this->config->class_path . 'class-fooevents-mailchimp-helper.php';
		$this->mailchimp_helper = new FooEvents_Mailchimp_Helper( $this->config );

		$this->salt = $this->config->salt;

		if ( empty( $this->salt ) ) {

			$salt = wp_rand( 111111, 999999 );
			update_option( 'woocommerce_events_do_salt', $salt );
			$this->salt         = $salt;
			$this->config->salt = $salt;

		}

		if ( ! file_exists( $this->config->uploads_path ) && is_writable( $this->config->uploads_dir_path ) ) {

			if ( ! mkdir( $this->config->uploads_path, 0755, true ) ) {

				// translators: Placeholder is for the uploads path.
				$this->output_notices( array( sprintf( __( 'FooEvents failed to create the directory %s please manually create this directory on your server.', 'woocommerce-events' ), $this->config->uploads_path ) ) );

			}

			if ( ! file_exists( $this->config->barcode_path ) ) {

				if ( ! mkdir( $this->config->barcode_path, 0755, true ) ) {

					// translators: Placeholder is for the barcode path.
					$this->output_notices( array( sprintf( __( 'FooEvents failed to create the directory %s please manually create this directory on your server.', 'woocommerce-events' ), $this->config->barcode_path ) ) );

				}
			}

			if ( ! file_exists( $this->config->theme_packs_path ) ) {

				if ( ! mkdir( $this->config->theme_packs_path, 0755, true ) ) {

					// translators: Placeholder is for the theme path.
					$this->output_notices( array( sprintf( __( 'FooEvents failed to create the directory %s please manually create this directory on your server.', 'woocommerce-events' ), $this->config->theme_packs_path ) ) );

				}
			}

			if ( ! file_exists( $this->config->pdf_ticket_path ) ) {

				if ( ! mkdir( $this->config->pdf_ticket_path, 0755, true ) ) {

					// translators: Placeholder is for the PDF ticket path.
					$this->output_notices( array( sprintf( __( 'FooEvents failed to create the directory %s please manually create this directory on your server.', 'woocommerce-events' ), $this->config->pdf_ticket_path ) ) );

				}
			}
		}

		if ( file_exists( $this->config->pdf_ticket_path ) && ! file_exists( $this->config->pdf_ticket_path . 'index.php' ) ) {

			if ( ! copy( $this->config->template_path . 'index.php', $this->config->pdf_ticket_path . 'index.php' ) ) {

				// translators: Placeholder is for the PDF ticket path.
				$this->output_notices( array( sprintf( __( 'FooEvents failed to create the file %s please manually create this file on your server.', 'woocommerce-events' ), $this->config->pdf_ticket_path . 'index.php' ) ) );

			}
		}

		if ( file_exists( $this->config->barcode_path ) && ! file_exists( $this->config->barcode_path . 'barcode-barcode.png' ) ) {

			$this->barcode_helper->generate_barcode( 'barcode', 'barcode' );

		}

		if ( ! file_exists( $this->config->uploads_path . 'themes/default_ticket_theme' ) && is_writable( $this->config->uploads_dir_path ) ) {

			$this->xcopy( $this->config->email_template_path, $this->config->theme_packs_path . 'default_ticket_theme' );

		}

		if ( ! file_exists( $this->config->ics_path ) ) {

			if ( ! mkdir( $this->config->ics_path, 0755, true ) ) {

				// translators: Placeholder is for the ICS path.
				$this->output_notices( array( sprintf( __( 'FooEvents failed to create the directory %s please manually create this directory on your server.', 'woocommerce-events' ), $this->config->ics_path ) ) );

			}
		}

		// Add ability to change an event's owner.
		add_post_type_support( 'product', 'author' );
	}

	/**
	 * When WPML is loaded
	 */
	public function fooevents_wpml_loaded() {

		add_action( 'pre_get_posts', array( $this, 'fooevents_wpml_compatibility' ) );
	}

	/**
	 * WPML compatibility for events within app
	 *
	 * @param object $wp_query WP Query.
	 */
	public function fooevents_wpml_compatibility( $wp_query ) {

		$q = $wp_query->query_vars;

		if ( isset( $q['meta_query'] ) && isset( $q['post_type'] ) && in_array( 'event_magic_tickets', (array) $q['post_type'], true ) ) {

			foreach ( (array) $q['meta_query'] as $i => $meta_query ) {

				if ( isset( $meta_query['key'] ) && 'WooCommerceEventsProductID' === $meta_query['key'] && is_numeric( $meta_query['value'] ) ) {

						$trid                           = apply_filters( 'wpml_element_trid', null, $meta_query['value'], 'post_event_magic_tickets' );
						$values                         = apply_filters( 'wpml_get_element_translations', null, $trid, 'post_event_magic_tickets' );
						$q['meta_query'][ $i ]['value'] = wp_list_pluck( $values, 'element_id' );

						$wp_query->query_vars = $q;

				}
			}
		}
	}

	/**
	 * Create FooEvents tables
	 */
	public function create_fooevents_table() {

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'fooevents_check_in';

		$sql = "CREATE TABLE $table_name (
		id bigint(20) NOT NULL AUTO_INCREMENT,
                tid BIGINT(20) UNSIGNED NOT NULL,
                eid BIGINT(20) UNSIGNED NOT NULL,
                day int(3),
                checkin int(10),
		updated datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'fooevents_db', true );
	}

	/**
	 * Update FooEvents tables
	 */
	public function update_fooevents_table() {

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'fooevents_check_in';

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$current_database_version = get_option( 'fooevents_database_version', '1.0' );

		if ( version_compare( $current_database_version, '1.2' ) === -1 ) {

			$sql = "ALTER TABLE {$table_name}
                    ADD COLUMN status char(20)
                    AFTER day";

			$result = $wpdb->query( $sql );

			$sql = "ALTER TABLE {$table_name}
                    ADD COLUMN uid bigint(20)
                    AFTER day";

			$result = $wpdb->query( $sql );

			update_option( 'fooevents_database_version', '1.2' );

		}
	}

	/**
	 * Register plugin scripts.
	 */
	public function register_scripts() {

		global $wp_locale;
		global $woocommerce;
		global $post;

		$woocommerce_currency_symbol = '';
		if ( class_exists( 'WooCommerce' ) ) {

			$woocommerce_currency_symbol = get_woocommerce_currency_symbol();

		}

		$fooevents_obj = array(
			'closeText'       => __( 'Done', 'woocommerce-events' ),
			'currentText'     => __( 'Today', 'woocommerce-events' ),
			'monthNames'      => $this->strip_array_indices( $wp_locale->month ),
			'monthNamesShort' => $this->strip_array_indices( $wp_locale->month_abbrev ),
			'monthStatus'     => __( 'Show a different month', 'woocommerce-events' ),
			'dayNames'        => $this->strip_array_indices( $wp_locale->weekday ),
			'dayNamesShort'   => $this->strip_array_indices( $wp_locale->weekday_abbrev ),
			'dayNamesMin'     => $this->strip_array_indices( $wp_locale->weekday_initial ),
			'dateFormat'      => $this->date_format_php_to_js( get_option( 'date_format' ) ),
			'firstDay'        => get_option( 'start_of_week' ),
			'isRTL'           => $wp_locale->is_rtl(),
			'currencySymbol'  => $woocommerce_currency_symbol,
		);

		$local_reminders_args = array(
			'minutesValue' => __( 'minutes', 'woocommerce-events' ),
			'hoursValue'   => __( 'hours', 'woocommerce-events' ),
			'daysValue'    => __( 'days', 'woocommerce-events' ),
			'weeksValue'   => __( 'weeks', 'woocommerce-events' ),
		);

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_script( 'woocommerce-events-timepicker-script', $this->config->scripts_path . 'jquery-ui-timepicker-addon.js', array( 'jquery', 'jquery-ui-datepicker' ), $this->config->plugin_data['Version'], true );
		wp_enqueue_script( 'woocommerce-events-admin-script', $this->config->scripts_path . 'events-admin.js', array( 'jquery', 'jquery-ui-datepicker', 'woocommerce-events-timepicker-script', 'wp-color-picker' ), $this->config->plugin_data['Version'], true );
		wp_localize_script( 'woocommerce-events-admin-script', 'FooEventsObj', $fooevents_obj );

		wp_localize_script( 'woocommerce-events-admin-script', 'localRemindersObj', $local_reminders_args );

		$local_args_print = array(
			'ajaxurl'         => admin_url( 'admin-ajax.php' ),
			'ajaxSaveSuccess' => __( 'Your stationery settings have been saved.', 'woocommerce-events' ),
			'ajaxSaveError'   => __( 'An error occurred while saving your stationery settings.', 'woocommerce-events' ),
		);

		wp_enqueue_script( 'woocommerce-events-printing-admin-script', $this->config->scripts_path . 'events-printing-admin.js', array( 'jquery' ), $this->config->plugin_data['Version'], true );
		wp_localize_script( 'woocommerce-events-printing-admin-script', 'localObjPrint', $local_args_print );

		if ( isset( $_GET['page'] ) && 'fooevents-settings' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification

			wp_enqueue_media();

		}

		if ( isset( $_GET['page'] ) && 'fooevents-event-report' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification

			wp_enqueue_script( 'woocommerce-events-chartist', $this->config->scripts_path . 'chartist.min.js', array( 'jquery' ), '0.11.3', true );
			wp_enqueue_script( 'woocommerce-events-chartist-tooltip', $this->config->scripts_path . 'chartist-plugin-tooltip.min.js', array( 'jquery', 'woocommerce-events-chartist' ), '0.0.20', true );

			wp_enqueue_script( 'woocommerce-events-report', $this->config->scripts_path . 'events-reports.js', array( 'jquery', 'woocommerce-events-chartist' ), $this->config->plugin_data['Version'], true );
			wp_localize_script( 'woocommerce-events-report', 'FooEventsReportsObj', $fooevents_obj );

		}

		if ( isset( $_GET['post_type'] ) && 'event_magic_tickets' === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification

			$add_ticket_args = array(
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'adminURL'      => get_admin_url(),
				'eventOverview' => __( 'Event Overview', 'woocommerce-events' ),
				'selectEvent'   => __( 'Select an event in the <strong>Event Details</strong> section.', 'woocommerce-events' ),
			);

			wp_enqueue_script( 'woocommerce-events-select', $this->config->scripts_path . 'select2.min.js', array( 'jquery' ), '4.0.12', true );
			wp_enqueue_script( 'woocommerce-events-admin-select', $this->config->scripts_path . 'event-admin-select.js', array( 'jquery', 'woocommerce-events-select' ), $this->config->plugin_data['Version'], true );

			wp_enqueue_script( 'woocommerce-events-admin-add-ticket', $this->config->scripts_path . 'events-admin-add-ticket.js', array( 'jquery', 'jquery-ui-datepicker', 'wp-color-picker' ), $this->config->plugin_data['Version'], true );
			wp_localize_script( 'woocommerce-events-admin-add-ticket', 'FooEventsBookingsAddTicketObj', $add_ticket_args );

		}

		if ( isset( $_GET['post'] ) && isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification

			wp_enqueue_script( 'woocommerce-events-admin-edit-ticket', $this->config->scripts_path . 'events-admin-edit-ticket.js', array( 'jquery', 'jquery-ui-datepicker', 'wp-color-picker' ), $this->config->plugin_data['Version'], true );
			wp_localize_script( 'woocommerce-events-admin-edit-ticket', 'FooEventsBookingsEditTicketObj', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		}

		$zoom_args = array(
			'testAccess'                            => __( 'Test Access', 'woocommerce-events' ),
			'testingAccess'                         => __( 'Testing Access...', 'woocommerce-events' ),
			'successFullyConnectedZoomAccount'      => __( 'Successfully connected to your Zoom account', 'woocommerce-events' ),
			'fetchUsers'                            => __( 'Fetch Users', 'woocommerce-events' ),
			'fetchingUsers'                         => __( 'Fetching Users...', 'woocommerce-events' ),
			'userOptionMe'                          => __( 'Show only meetings/webinars for the user that generated the Zoom API Server-to-Server OAuth credentials', 'woocommerce-events' ),
			'userOptionSelect'                      => __( 'Show all meetings/webinars created by the following users:', 'woocommerce-events' ),
			'userLoadTimes'                         => __( 'Please note that meeting/webinar load times will increase as more users are selected.', 'woocommerce-events' ),
			'adminURL'                              => get_admin_url(),
			'pluginsURL'                            => plugins_url(),
			'notSet'                                => __( 'Not set', 'woocommerce-events' ),
			'autoGenerate'                          => __( 'Auto-generate', 'woocommerce-events' ),
			'topic'                                 => __( 'Topic', 'woocommerce-events' ),
			'description'                           => __( 'Description', 'woocommerce-events' ),
			'date'                                  => __( 'Date', 'woocommerce-events' ),
			'startDate'                             => __( 'Start date', 'woocommerce-events' ),
			'endDate'                               => __( 'End date', 'woocommerce-events' ),
			'startTime'                             => __( 'Start time', 'woocommerce-events' ),
			'endTime'                               => __( 'End time', 'woocommerce-events' ),
			'duration'                              => __( 'Duration', 'woocommerce-events' ),
			'recurrence'                            => __( 'Recurrence', 'woocommerce-events' ),
			'upcomingOccurrences'                   => __( 'Upcoming occurrences', 'woocommerce-events' ),
			'occurrences'                           => __( 'Occurrences', 'woocommerce-events' ),
			'noOccurrences'                         => __( 'No upcoming occurrences', 'woocommerce-events' ),
			'unableToFetchMeeting'                  => __( 'Unable to fetch meeting details', 'woocommerce-events' ),
			'unableToFetchWebinar'                  => __( 'Unable to fetch webinar details', 'woocommerce-events' ),
			'registrationRequired'                  => __( 'Note: Automatic attendee registration is required.', 'woocommerce-events' ),
			'registrationRequiredForAllOccurrences' => __( 'Note: Automatic attendee registration is required for all occurrences.', 'woocommerce-events' ),
			'automaticRegistration'                 => __( 'Note: Attendees will be registered automatically.', 'woocommerce-events' ),
			'automaticRegistrationAllOccurrences'   => __( 'Note: Attendees will be registered automatically for all occurrences.', 'woocommerce-events' ),
			'meetingRegistrationCurrentlyEnabled'   => __( 'Automatic attendee registration is currently enabled for this meeting', 'woocommerce-events' ),
			'webinarRegistrationCurrentlyEnabled'   => __( 'Automatic attendee registration is currently enabled for this webinar', 'woocommerce-events' ),
			'meetingRegistrationCurrentlyDisabled'  => __( 'Automatic attendee registration is currently disabled for this meeting', 'woocommerce-events' ),
			'webinarRegistrationCurrentlyDisabled'  => __( 'Automatic attendee registration is currently disabled for this webinar', 'woocommerce-events' ),
			'enableMeetingRegistration'             => __( 'Enable automatic attendee registration for this meeting', 'woocommerce-events' ),
			'enableWebinarRegistration'             => __( 'Enable automatic attendee registration for this webinar', 'woocommerce-events' ),
			'registrationAllOccurrencesEnabled'     => __( 'Automatic attendee registration is currently enabled for all occurrences', 'woocommerce-events' ),
			'registrationAllOccurrencesDisabled'    => __( 'Automatic attendee registration is not currently enabled for all occurrences', 'woocommerce-events' ),
			'enableRegistrationForAllOccurrences'   => __( 'Enable automatic attendee registration for all occurrences', 'woocommerce-events' ),
			'registrations'                         => __( 'Registrations', 'woocommerce-events' ),
			'linkMultiMeetingsWebinars'             => __( 'Link the event to these meetings/webinars:', 'woocommerce-events' ),
			'showDetails'                           => __( 'Show details', 'woocommerce-events' ),
			'hideDetails'                           => __( 'Hide details', 'woocommerce-events' ),
			'notRecurringMeeting'                   => __( 'This is not a recurring meeting', 'woocommerce-events' ),
			'notRecurringWebinar'                   => __( 'This is not a recurring webinar', 'woocommerce-events' ),
			'noFixedTimeMeeting'                    => __( "This meeting's recurrence is currently set to 'No Fixed Time' which does not allow attendees to pre-register in advance. Please change the setting for this meeting to have a fixed recurrence (daily/weekly/monthly) in your Zoom account before proceeding.", 'woocommerce-events' ),
			'noFixedTimeWebinar'                    => __( "This webinar's recurrence is currently set to 'No Fixed Time' which does not allow attendees to pre-register in advance. Please change the setting for this webinar to have a fixed recurrence (daily/weekly/monthly) in your Zoom account before proceeding.", 'woocommerce-events' ),
			'editMeeting'                           => __( 'Edit meeting', 'woocommerce-events' ),
			'editWebinar'                           => __( 'Edit webinar', 'woocommerce-events' ),
			'singleEventType'                       => __( 'Single', 'woocommerce-events' ),
			'sequentialEventType'                   => __( 'Sequential days', 'woocommerce-events' ),
			'selectEventType'                       => __( 'Select days', 'woocommerce-events' ),
			'bookingsEventType'                     => __( 'Bookable', 'woocommerce-events' ),
			'seatingEventType'                      => __( 'Seating', 'woocommerce-events' ),
			'singleEventTypeDescription'            => __( 'Standard one-day events.', 'woocommerce-events' ),
			'sequentialEventTypeDescription'        => __( 'Events that occur over multiple days and repeat for a set number of sequential days.', 'woocommerce-events' ),
			'selectEventTypeDescription'            => __( 'Events that repeat over multiple calendar days.', 'woocommerce-events' ),
			'bookingsEventTypeDescription'          => __( 'Events that require customers to select from available date and time slots (bookings and repeat events).', 'woocommerce-events' ),
			'seatingEventTypeDescription'           => __( 'Events that include the ability for customers to select row and seat numbers from a seating chart.', 'woocommerce-events' ),
			'refreshExampleInfo'                    => __( 'Refresh Example Info', 'woocommerce-events' ),
			'hours'                                 => __( 'hours', 'woocommerce-events' ),
			'hour'                                  => __( 'hour', 'woocommerce-events' ),
			'minutes'                               => __( 'minutes', 'woocommerce-events' ),
			'minute'                                => __( 'minute', 'woocommerce-events' ),
			'daily'                                 => __( 'Daily', 'woocommerce-events' ),
			'dateFormat'                            => $this->date_format_php_to_js( get_option( 'date_format' ) ),
		);

		wp_enqueue_script( 'woocommerce-events-zoom-admin-script', $this->config->scripts_path . 'events-zoom-admin.js', array( 'jquery' ), $this->config->plugin_data['Version'], true );
		wp_localize_script( 'woocommerce-events-zoom-admin-script', 'zoomObj', $zoom_args );
	}

	/**
	 * Registers scripts on the WordPress frontend.
	 */
	public function register_scripts_frontend() {

		$events_add_copy_purchaser_details = get_option( 'globalWooCommerceEventsAddCopyPurchaserDetails' );

		$front_end_args = array(
			'copyFromPurchaser' => $events_add_copy_purchaser_details,
		);

		wp_enqueue_script( 'woocommerce-events-front-script', $this->config->scripts_path . 'events-frontend.js', array( 'jquery' ), '1.0.0', true );
		wp_localize_script( 'woocommerce-events-front-script', 'frontObj', $front_end_args );
	}

	/**
	 * Registers styles on the WordPress frontend.
	 */
	public function register_styles_frontend() {

		global $post;

		$is_event = '';

		if ( isset( $post->ID ) && ! empty( $post->ID ) ) {

			$is_event = get_post_meta( $post->ID, 'WooCommerceEventsEvent', true );

		}

		if ( 'Event' === $is_event ) {

			wp_enqueue_style( 'dashicons' );
			wp_enqueue_style( 'woocommerce-events-front-style', $this->config->styles_path . 'events-frontend.css', array(), $this->config->plugin_data['Version'] );

			wp_enqueue_style( 'woocommerce-events-zoom-frontend-style', $this->config->styles_path . 'events-zoom-frontend.css', array(), $this->config->plugin_data['Version'] );

		}
	}

	/**
	 * Register plugin styles.
	 */
	public function register_styles() {

		wp_enqueue_style( 'woocommerce-events-admin-script', $this->config->styles_path . 'events-admin.css', array(), $this->config->plugin_data['Version'] );
		wp_enqueue_style( 'woocommerce-events-admin-timepicker', $this->config->styles_path . 'jquery-ui-timepicker-addon.css', array(), '1.2.1' );

		if ( ( isset( $_GET['post'] ) && isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) || ( isset( $_GET['page'] ) && 'fooevents-event-report' === $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			wp_enqueue_style( 'woocommerce-events-admin-jquery', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css', array(), '1.0.0' );

		}

		wp_enqueue_style( 'wp-color-picker' );

		if ( isset( $_GET['page'] ) && 'fooevents-event-report' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification

			wp_enqueue_style( 'woocommerce-events-chartist', $this->config->styles_path . 'chartist.min.css', array(), '0.11.3' );
			wp_enqueue_style( 'woocommerce-events-chartist-tooltip', $this->config->styles_path . 'chartist-plugin-tooltip.css', array(), '0.0.18' );

		}

		if ( isset( $_GET['post_type'] ) && 'event_magic_tickets' === $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification

			wp_enqueue_style( 'woocommerce-events-select', $this->config->styles_path . 'select2.min.css', array(), '4.0.12' );

		}

		wp_enqueue_style( 'woocommerce-events-select', $this->config->styles_path . 'select2.min.css', array(), '4.0.12' );

		wp_enqueue_style( 'woocommerce-events-zoom-admin-style', $this->config->styles_path . 'events-zoom-admin.css', array(), $this->config->plugin_data['Version'] );
	}

	/**
	 * Assign FooEvents user permissions to the admin user
	 */
	public function assign_admin_caps() {

		$role = get_role( 'administrator' );

		$role->add_cap( 'publish_event_magic_tickets' );
		$role->add_cap( 'edit_event_magic_tickets' );
		$role->add_cap( 'edit_published_event_magic_tickets' );
		$role->add_cap( 'edit_others_event_magic_tickets' );
		$role->add_cap( 'delete_event_magic_tickets' );
		$role->add_cap( 'delete_others_event_magic_tickets' );
		$role->add_cap( 'read_private_event_magic_tickets' );
		$role->add_cap( 'edit_event_magic_ticket' );
		$role->add_cap( 'delete_event_magic_ticket' );
		$role->add_cap( 'read_event_magic_ticket' );
		$role->add_cap( 'edit_published_event_magic_ticket' );
		$role->add_cap( 'publish_event_magic_ticket' );
		$role->add_cap( 'delete_others_event_magic_ticket' );
		$role->add_cap( 'delete_published_event_magic_ticket' );
		$role->add_cap( 'delete_published_event_magic_tickets' );
		$role->add_cap( 'app_event_magic_tickets' );
	}

	/**
	 * Removes FooEvents user permissions when plugin is disabled
	 */
	public function remove_event_user_caps() {

		$delete_caps = array(
			'publish_event_magic_tickets',
			'edit_event_magic_tickets',
			'edit_published_event_magic_tickets',
			'edit_others_event_magic_tickets',
			'delete_event_magic_tickets',
			'delete_others_event_magic_tickets',
			'read_private_event_magic_tickets',
			'edit_event_magic_ticket',
			'delete_event_magic_ticket',
			'read_event_magic_ticket',
			'edit_published_event_magic_ticket',
			'publish_event_magic_ticket',
			'delete_others_event_magic_ticket',
			'delete_published_event_magic_ticket',
			'delete_published_event_magic_tickets',
			'app_event_magic_tickets',
		);

		global $wp_roles;
		foreach ( $delete_caps as $cap ) {

			foreach ( array_keys( $wp_roles->roles ) as $role ) {

					$wp_roles->remove_cap( $role, $cap );

			}
		}
	}

	/**
	 * Outputs notices to screen.
	 *
	 * @param array $notices notices.
	 */
	private function output_notices( $notices ) {

		foreach ( $notices as $notice ) {

				echo '<div class="notice notice-error"><p>' . esc_attr( $notice ) . '</p></div>';

		}
	}

	/**
	 * XCOPY function to move templates to new location in uploads directory
	 *
	 * @param string $source source.
	 * @param string $dest destination.
	 * @param int    $permissions file permissions.
	 */
	private function xcopy( $source, $dest, $permissions = 0755 ) {
		if ( is_link( $source ) ) {

			return symlink( readlink( $source ), $dest );

		}

		if ( is_file( $source ) ) {

			return copy( $source, $dest );

		}

		if ( ! is_dir( $dest ) ) {

			mkdir( $dest, $permissions );

		}

		$dir = dir( $source );
		while ( false !== $entry = $dir->read() ) {

			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}

			$this->xcopy( "$source/$entry", "$dest/$entry", $permissions );
		}

		$dir->close();
		return true;
	}

	/**
	 * Adds the FooEvents menu item
	 */
	public function add_admin_menu() {

		add_menu_page(
			null,
			__( 'FooEvents', 'woocommerce-events' ),
			'edit_posts',
			'fooevents',
			array( $this, 'redirect_to_tickets' ),
			'dashicons-tickets-alt',
			'55.9'
		);

		add_submenu_page( 'fooevents', __( 'Settings', 'woocommerce-events' ), __( 'Settings', 'woocommerce-events' ), 'edit_posts', 'fooevents-settings', array( $this, 'display_settings_page' ) );
		add_submenu_page( 'fooevents', __( 'Getting Started', 'woocommerce-events' ), __( 'Getting Started', 'woocommerce-events' ), 'edit_posts', 'fooevents-introduction', array( $this, 'add_woocommerce_submenu_page' ) );
		add_submenu_page( 'fooevents', __( 'Import Check-ins', 'woocommerce-events' ), __( 'Import Check-ins', 'woocommerce-events' ), 'edit_posts', 'fooevents-checkins-import', array( $this, 'add_fooevents_checkins_import_page' ) );
	}

	/**
	 * Reorder the FooEvents menu items
	 *
	 * @param array $menu_ord menu order.
	 * @return array $menu_ord menu order.
	 */
	public function fooevents_menu_order( $menu_ord ) {

		if ( ! is_network_admin() ) {

			global $submenu;

			$menu = array();

			if ( isset( $submenu['fooevents'] ) ) {

				foreach ( $submenu['fooevents'] as $item => $menu_item ) {

					if ( 'edit.php?post_type=event_magic_tickets' === $menu_item[2] ) {

						$menu[1] = $menu_item;

					}

					if ( 'fooevents-bookings-admin' === $menu_item[2] ) {

						$menu[2] = $menu_item;

					}

					if ( 'fooevents-ticket-themes' === $menu_item[2] ) {

						$menu[3] = $menu_item;

					}

					if ( 'fooevents-settings' === $menu_item[2] ) {

						$menu[4] = $menu_item;

					}

					if ( 'fooevents-reports' === $menu_item[2] ) {

						$menu[5] = $menu_item;

					}

					if ( 'fooevents-checkins-import' === $menu_item[2] ) {

						$menu[6] = $menu_item;

					}

					if ( 'fooevents-import-tickets' === $menu_item[2] ) {

						$menu[7] = $menu_item;

					}

					if ( 'fooevents-express-checkin-page' === $menu_item[2] ) {

						$menu[8] = $menu_item;

					}

					if ( 'fooevents-introduction' === $menu_item[2] ) {

						$menu[9] = $menu_item;

					}
				}

				ksort( $menu );

				$submenu['fooevents'] = $menu;

			}
		}

		return $menu_ord;
	}

	/**
	 * Redirects to tickets listing
	 */
	public function redirect_to_tickets() {

		wp_safe_redirect( 'edit.php?post_type=event_magic_tickets' );

		exit;
	}

	/**
	 * Redirects to FooEvents settings
	 */
	public function redirect_to_settings() {

		wp_safe_redirect( 'admin.php?page=fooevents-settings&tab=api' );

		exit;
	}

	/**
	 * Register FooEvents options
	 */
	public function register_settings_options() {

		register_setting( 'fooevents-settings-api', 'globalWooCommerceEventsAPIKey' );
		register_setting( 'fooevents-settings-api', 'globalWooCommerceEnvatoAPIKey' );

		register_setting( 'fooevents-settings-general', 'globalWooCommerceEventsChangeAddToCart' );
		register_setting( 'fooevents-settings-general', 'globalWooCommerceEventSorting' );
		register_setting( 'fooevents-settings-general', 'globalWooCommerceDisplayEventDate' );
		register_setting( 'fooevents-settings-general', 'globalWooCommerceHideEventDetailsTab' );
		register_setting( 'fooevents-settings-general', 'globalWooCommerceUsePlaceHolders' );
		register_setting( 'fooevents-settings-general', 'globalWooCommerceEventsHideUnpaidTickets' );
		register_setting( 'fooevents-settings-general', 'globalWooCommerceEventsSuppressAdminNotifications' );
		register_setting( 'fooevents-settings-general', 'globalWooCommerceEventsUseTotalSales' );
		register_setting( 'fooevents-settings-general', 'globalWooCommerceEventsEmailTicketAdmin' );
		register_setting( 'fooevents-settings-general', 'globalWooCommerceEventsAddCopyPurchaserDetails' );
		register_setting( 'fooevents-settings-general', 'globalWooCommerceEventsExpireOption' );
		register_setting( 'fooevents-settings-general', 'globalWooCommerceEventsAttendeeFieldsPos' );
		register_setting( 'fooevents-settings-general', 'globalWooCommerceEventsSendOnStatus' );
		register_setting( 'fooevents-settings-general', 'globalWooCommerceEventsImportBatchSize' );

		register_setting( 'fooevents-settings-terminology', 'globalWooCommerceEventsEventOverride' );
		register_setting( 'fooevents-settings-terminology', 'globalWooCommerceEventsEventOverridePlural' );
		register_setting( 'fooevents-settings-terminology', 'globalWooCommerceEventsAttendeeOverride' );
		register_setting( 'fooevents-settings-terminology', 'globalWooCommerceEventsAttendeeOverridePlural' );
		register_setting( 'fooevents-settings-terminology', 'globalWooCommerceEventsTicketOverride' );
		register_setting( 'fooevents-settings-terminology', 'globalWooCommerceEventsTicketOverridePlural' );
		register_setting( 'fooevents-settings-terminology', 'WooCommerceEventsDayOverride' );
		register_setting( 'fooevents-settings-terminology', 'WooCommerceEventsDayOverridePlural' );
		register_setting( 'fooevents-settings-terminology', 'WooCommerceEventsCopyOverride' );
		register_setting( 'fooevents-settings-terminology', 'WooCommerceEventsCopyOverridePlural' );

		register_setting( 'fooevents-settings-ticket-design', 'globalWooCommerceEventsTicketBackgroundColor' );
		register_setting( 'fooevents-settings-ticket-design', 'globalWooCommerceEventsTicketButtonColor' );
		register_setting( 'fooevents-settings-ticket-design', 'globalWooCommerceEventsTicketTextColor' );
		register_setting( 'fooevents-settings-ticket-design', 'globalWooCommerceEventsTicketLogo' );
		register_setting( 'fooevents-settings-ticket-design', 'globalWooCommerceEventsTicketHeaderImage' );
		register_setting( 'fooevents-settings-ticket-design', 'globalWooCommerceEventsEnableQRCode' );
		register_setting( 'fooevents-settings-ticket-design', 'globalWooCommerceEventsBarcodeOutput' );
		register_setting( 'fooevents-settings-ticket-design', 'globalWooCommerceEventsDisplayPoweredby' );

		register_setting( 'fooevents-settings-checkins-app', 'globalWooCommerceHideUnpaidTicketsApp' );
		register_setting( 'fooevents-settings-checkins-app', 'globalWooCommerceEventsAppTitle' );
		register_setting( 'fooevents-settings-checkins-app', 'globalWooCommerceEventsAppLogo' );
		register_setting( 'fooevents-settings-checkins-app', 'globalWooCommerceEventsAppColor' );
		register_setting( 'fooevents-settings-checkins-app', 'globalWooCommerceEventsAppTextColor' );
		register_setting( 'fooevents-settings-checkins-app', 'globalWooCommerceEventsAppBackgroundColor' );
		register_setting( 'fooevents-settings-checkins-app', 'globalWooCommerceEventsAppSignInTextColor' );
		register_setting( 'fooevents-settings-checkins-app', 'globalWooCommerceEventsAppEvents' );
		register_setting( 'fooevents-settings-checkins-app', 'globalWooCommerceEventsAppEventIDs' );
		register_setting( 'fooevents-settings-checkins-app', 'globalWooCommerceEventsAppShowAllForAdmin' );
		register_setting( 'fooevents-settings-checkins-app', 'globalWooCommerceEventsAppHidePersonalInfo' );
		register_setting( 'fooevents-settings-checkins-app', 'globalWooCommerceEventsAppTicketsPerRequest' );
		register_setting( 'fooevents-settings-checkins-app', 'globalWooCommerceEventsAppTicketAccessLog' );

		register_setting( 'fooevents-settings-integration', 'globalWooCommerceEventsGoogleMapsMethod' );
		register_setting( 'fooevents-settings-integration', 'globalWooCommerceEventsGoogleMapsAPIKey' );
		register_setting( 'fooevents-settings-integration', 'globalWooCommerceEventsAddToWalletAPIKey' );
		register_setting( 'fooevents-settings-integration', 'globalWooCommerceEventsZoomAccountID' );
		register_setting( 'fooevents-settings-integration', 'globalWooCommerceEventsZoomClientID' );
		register_setting( 'fooevents-settings-integration', 'globalWooCommerceEventsZoomClientSecret' );
		register_setting( 'fooevents-settings-integration', 'globalWooCommerceEventsZoomUsers' );
		register_setting( 'fooevents-settings-integration', 'globalWooCommerceEventsZoomSelectedUserOption' );
		register_setting( 'fooevents-settings-integration', 'globalWooCommerceEventsZoomSelectedUsers' );
		register_setting( 'fooevents-settings-integration', 'globalWooCommerceEventsDisableSubTicketGen' );
		register_setting( 'fooevents-settings-integration', 'globalWooCommerceEventsMailchimpAPIKey' );
		register_setting( 'fooevents-settings-integration', 'globalWooCommerceEventsMailchimpServer' );
		register_setting( 'fooevents-settings-integration', 'globalWooCommerceEventsMailchimpList' );
		register_setting( 'fooevents-settings-integration', 'globalWooCommerceEventsMailchimpTags' );
	}

	/**
	 * Display and processes the FooEvents Settings page
	 */
	public function display_settings_page() {

		$this->display_settings_page_notices();

		if ( ! current_user_can( 'publish_event_magic_tickets' ) ) {
			wp_die( esc_attr( __( 'You do not have sufficient permissions to access this page.' ) ) );
		}

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		$active_tab = '';
		if ( isset( $_GET['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$active_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

		} else {

			$active_tab = 'api';

		}

		$bookings_enabled = false;
		if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

			$bookings_enabled = true;

		}

		$pdf_enabled = false;
		if ( is_plugin_active( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) || is_plugin_active_for_network( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) ) {

			$pdf_enabled = true;

		}

		$calendar_enabled = false;
		if ( is_plugin_active( 'fooevents-calendar/fooevents-calendar.php' ) || is_plugin_active_for_network( 'fooevents-calendar/fooevents-calendar.php' ) ) {

			$calendar_enabled = true;

		}

		$seating_enabled = false;
		if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

			$seating_enabled = true;

		}

		$subscriptions_enabled = false;
		if ( is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) || is_plugin_active_for_network( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ) {

			$subscriptions_enabled = true;

		}

		$global_woocommerce_events_api_key                      = get_option( 'globalWooCommerceEventsAPIKey' );
		$global_woocommerce_envato_api_key                      = get_option( 'globalWooCommerceEnvatoAPIKey' );
		$global_woocommerce_events_google_maps_method           = get_option( 'globalWooCommerceEventsGoogleMapsMethod' );
		$global_woocommerce_events_google_maps_api_key          = get_option( 'globalWooCommerceEventsGoogleMapsAPIKey' );
		$global_woocommerce_events_addtowallet_api_key          = get_option( 'globalWooCommerceEventsAddToWalletAPIKey' );
		$global_woocommerce_events_addtowallet_credits          = $this->fooevents_addtowallet_credits();
		$global_woocommerce_events_mailchimp_api_key            = get_option( 'globalWooCommerceEventsMailchimpAPIKey' );
		$global_woocommerce_events_mailchimp_server             = get_option( 'globalWooCommerceEventsMailchimpServer' );
		$global_woocommerce_events_mailchimp_list               = get_option( 'globalWooCommerceEventsMailchimpList' );
		$global_woocommerce_events_mailchimp_tags               = get_option( 'globalWooCommerceEventsMailchimpTags' );
		$global_woocommerce_events_ticket_background_color      = get_option( 'globalWooCommerceEventsTicketBackgroundColor' );
		$global_woocommerce_events_ticket_button_color          = get_option( 'globalWooCommerceEventsTicketButtonColor' );
		$global_woocommerce_events_ticket_text_color            = get_option( 'globalWooCommerceEventsTicketTextColor' );
		$global_woocommerce_events_ticket_logo                  = get_option( 'globalWooCommerceEventsTicketLogo' );
		$global_woocommerce_events_ticket_header_image          = get_option( 'globalWooCommerceEventsTicketHeaderImage' );
		$global_woocommerce_events_enable_qr_code               = get_option( 'globalWooCommerceEventsEnableQRCode' );
		$global_woocommerce_events_barcode_output               = get_option( 'globalWooCommerceEventsBarcodeOutput' );
		$global_woocommerce_events_display_poweredby            = get_option( 'globalWooCommerceEventsDisplayPoweredby' );
		$global_woocommerce_events_change_add_to_cart           = get_option( 'globalWooCommerceEventsChangeAddToCart' );
		$global_woocommerce_event_sorting                       = get_option( 'globalWooCommerceEventSorting' );
		$global_woocommerce_display_event_date                  = get_option( 'globalWooCommerceDisplayEventDate' );
		$global_woocommerce_hide_event_details_tab              = get_option( 'globalWooCommerceHideEventDetailsTab' );
		$global_woocommerce_use_place_holders                   = get_option( 'globalWooCommerceUsePlaceHolders' );
		$global_woocommerce_hide_unpaid_tickets_app             = get_option( 'globalWooCommerceHideUnpaidTicketsApp' );
		$global_woocommerce_events_hide_unpaid_tickets          = get_option( 'globalWooCommerceEventsHideUnpaidTickets' );
		$global_woocommerce_events_suppress_admin_notifications = get_option( 'globalWooCommerceEventsSuppressAdminNotifications' );
		$global_woocommerce_events_use_total_sales              = get_option( 'globalWooCommerceEventsUseTotalSales' );
		$global_woocommerce_events_email_ticket_admin           = get_option( 'globalWooCommerceEventsEmailTicketAdmin' );
		$global_woocommerce_events_add_copy_purchaser_details   = get_option( 'globalWooCommerceEventsAddCopyPurchaserDetails' );
		$global_woocommerce_events_expire_option                = get_option( 'globalWooCommerceEventsExpireOption' );
		$global_woocommerce_events_attendee_fields_pos          = get_option( 'globalWooCommerceEventsAttendeeFieldsPos' );
		$global_woocommerce_events_import_batch_size            = (int) get_option( 'globalWooCommerceEventsImportBatchSize' );

		// Check-ins App settings.
		if ( isset( $_GET['tab'] ) && 'checkins_app' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$global_woocommerce_events_app_title              = get_option( 'globalWooCommerceEventsAppTitle' );
			$global_woocommerce_events_app_logo               = get_option( 'globalWooCommerceEventsAppLogo' );
			$global_woocommerce_events_app_color              = get_option( 'globalWooCommerceEventsAppColor' );
			$global_woocommerce_events_app_text_color         = get_option( 'globalWooCommerceEventsAppTextColor' );
			$global_woocommerce_events_app_background_color   = get_option( 'globalWooCommerceEventsAppBackgroundColor' );
			$global_woocommerce_events_app_sign_in_text_color = get_option( 'globalWooCommerceEventsAppSignInTextColor' );
			$global_woocommerce_events_app_events             = get_option( 'globalWooCommerceEventsAppEvents' );
			$global_woocommerce_events_app_event_ids          = get_option( 'globalWooCommerceEventsAppEventIDs' );
			$global_woocommerce_events_app_show_all_for_admin = get_option( 'globalWooCommerceEventsAppShowAllForAdmin' );
			$global_woocommerce_events_app_hide_personal_info = get_option( 'globalWooCommerceEventsAppHidePersonalInfo' );

			$tickets_per_request_array = array(
				'all'  => __( 'Load all tickets', 'woocommerce-events' ),
				'25'   => '25',
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
				'1500' => '1500',
				'2000' => '2000',
				'2500' => '2500',
				'3000' => '3000',
				'3500' => '3500',
				'4000' => '4000',
				'4500' => '4500',
				'5000' => '5000',
			);

			$global_woocommerce_events_app_ticket_limit = get_option( 'globalWooCommerceEventsAppTicketsPerRequest', 'all' );

			$woocommerce_events_app_events_args = array(
				'post_type'        => 'product',
				'order'            => 'ASC',
				'orderby'          => 'title',
				'fields'           => 'ids',
				'suppress_filters' => true,
				'posts_per_page'   => -1,
				'post_status'      => array( 'publish', 'future' ),
				'meta_query'       => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'WooCommerceEventsEvent',
						'value'   => 'Event',
						'compare' => '=',
					),
				),
			);

			$woocommerce_events_app_events_query = new WP_Query( $woocommerce_events_app_events_args );

			$woocommerce_events_app_events = array();

			foreach ( $woocommerce_events_app_events_query->posts as $event_product_id ) {
				$woocommerce_events_app_events[] = array(
					'id'    => $event_product_id,
					'title' => get_the_title( $event_product_id ),
				);
			}

			$ticket_access_log_array = array(
				'none'   => __( 'None', 'woocommerce-events' ),
				'latest' => __( 'Only the latest access update', 'woocommerce-events' ),
				'all'    => __( 'All access updates', 'woocommerce-events' ),
			);

			$global_woocommerce_events_app_ticket_access_log = get_option( 'globalWooCommerceEventsAppTicketAccessLog', 'none' );
		}

		$global_woocommerce_events_disable_sub_ticket_gen = get_option( 'globalWooCommerceEventsDisableSubTicketGen' );
		$global_woocommerce_events_send_on_status         = get_option( 'globalWooCommerceEventsSendOnStatus' );
		$ping_mailchimp                                   = '';
		$mailchimp_lists                                  = array();

		$global_woocommerce_events_event_override            = get_option( 'globalWooCommerceEventsEventOverride' );
		$global_woocommerce_events_event_override_plural     = get_option( 'globalWooCommerceEventsEventOverridePlural' );
		$global_woocommerce_events_attendee_override         = get_option( 'globalWooCommerceEventsAttendeeOverride' );
		$global_woocommerce_events_attendee_override_plural  = get_option( 'globalWooCommerceEventsAttendeeOverridePlural' );
		$global_woocommerce_events_ticket_override           = get_option( 'globalWooCommerceEventsTicketOverride' );
		$global_woocommerce_events_ticket_override_plural    = get_option( 'globalWooCommerceEventsTicketOverridePlural' );
		$woocommerce_events_day_override                     = get_option( 'WooCommerceEventsDayOverride' );
		$woocommerce_events_day_override_plural              = get_option( 'WooCommerceEventsDayOverridePlural' );
		$woocommerce_events_copy_override                    = get_option( 'WooCommerceEventsCopyOverride' );
		$woocommerce_events_copy_override_plural             = get_option( 'WooCommerceEventsCopyOverridePlural' );
		$global_woocommerce_events_zoom_account_id           = get_option( 'globalWooCommerceEventsZoomAccountID' );
		$global_woocommerce_events_zoom_client_id            = get_option( 'globalWooCommerceEventsZoomClientID' );
		$global_woocommerce_events_zoom_client_secret        = get_option( 'globalWooCommerceEventsZoomClientSecret' );
		$global_woocommerce_events_zoom_users                = json_decode( get_option( 'globalWooCommerceEventsZoomUsers', wp_json_encode( array() ) ), true );
		$global_woocommerce_events_zoom_selected_user_option = get_option( 'globalWooCommerceEventsZoomSelectedUserOption' );
		$global_woocommerce_events_zoom_selected_users       = get_option( 'globalWooCommerceEventsZoomSelectedUsers' );

		if ( 'yes' === $global_woocommerce_events_email_ticket_admin ) {

			$global_woocommerce_events_email_ticket_admin = get_option( 'admin_email', true );

		}

		$pdf_options = '';
		if ( is_plugin_active( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) || is_plugin_active_for_network( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) ) {

			$fooevents_pdf_tickets = new FooEvents_PDF_Tickets();
			$pdf_options           = $fooevents_pdf_tickets->get_pdf_options();

		}

		$calendar_options   = '';
		$eventbrite_options = '';
		if ( is_plugin_active( 'fooevents-calendar/fooevents-calendar.php' ) || is_plugin_active_for_network( 'fooevents-calendar/fooevents-calendar.php' ) ) {

			$fooevents_calendar = new FooEvents_Calendar();
			$calendar_options   = $fooevents_calendar->get_calendar_options();
			$eventbrite_options = $fooevents_calendar->get_eventbrite_options();

		}

		$seating_options = '';
		if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

			$fooevents_seating = new FooEvents_Seating();
			$seating_options   = $fooevents_seating->get_seating_options();

		}

		if ( 'integration' === $active_tab && ! empty( $global_woocommerce_events_mailchimp_api_key ) && ! empty( $global_woocommerce_events_mailchimp_server ) ) {

			$ping_mailchimp = $this->mailchimp_helper->ping_mailchimp();

		}

		if ( 'yes' === $ping_mailchimp ) {

			$mailchimp_lists = $this->mailchimp_helper->get_lists();

		}

		$order_statuses = wc_get_order_statuses();

		if ( ! empty( $global_woocommerce_events_send_on_status ) && ! is_array( $global_woocommerce_events_send_on_status ) ) {

			$global_woocommerce_events_send_on_status_string = $global_woocommerce_events_send_on_status;
			$global_woocommerce_events_send_on_status        = array();
			$global_woocommerce_events_send_on_status[]      = $global_woocommerce_events_send_on_status_string;

		} elseif ( empty( $global_woocommerce_events_send_on_status ) ) {

			$global_woocommerce_events_send_on_status   = array();
			$global_woocommerce_events_send_on_status[] = 'wc-completed';

		}

		$fooevents_pos_color_options = '';
		if ( is_plugin_active( 'fooevents_pos/fooevents-pos.php' ) || is_plugin_active_for_network( 'fooevents_pos/fooevents-pos.php' ) ) {

			$fooevents_pos_color_options = FooEvents_POS_Integration::fooeventspos_get_color_options();

		}

		require $this->config->template_path . 'global-settings.php';
	}

	/**
	 * Display notices on FooEvents settings page.
	 */
	public function display_settings_page_notices() {

		// Check if WooCommerce checkout block is enabled.
		if ( is_admin() && function_exists( 'has_block' ) && function_exists( 'wc_get_page_id' ) ) {

			// Get the post ID of the WooCommerce checkout page.
			$checkout_page_id = wc_get_page_id( 'checkout' );

			// Check if the checkout page contains the WooCommerce Checkout block.
			if ( $checkout_page_id && has_block( 'woocommerce/checkout', $checkout_page_id ) ) {
				// Add an action to admin_notices to display a dashboard message.
					echo '<div class="notice notice-error"><p>';
					echo '<strong>';
					esc_html_e( 'Important: ', 'woocommerce_events' );
					echo '</strong>';
					esc_html_e( 'Your site is currently using the new WooCommerce Checkout block to process payments. While FooEvents offers partial support for the sale of single-day, multi-day, booking, and seating tickets using the WooCommerce Checkout block, the capturing of custom attendee fields is not yet supported. If you need to capture custom attendee information, please switch to the classic shortcode-based WooCommerce Checkout experience.', 'woocommerce_events' );
					echo ' <a href="https://help.fooevents.com/docs/frequently-asked-questions/installation-setup/does-fooevents-work-with-the-woocommerce-checkout-and-cart-blocks/">';
					esc_html_e( 'Learn more', 'woocommerce_events' );
					echo ' &rarr;</a>';
					echo '</div>';
			}
		}
	}

	/**
	 * Adds the WooCommerce sub menu
	 */
	public function add_woocommerce_submenu() {

		add_submenu_page( 'null', __( 'FooEvents Introduction', 'woocommerce-events' ), __( 'FooEvents Introduction', 'woocommerce-events' ), 'manage_options', 'woocommerce-events-help', array( $this, 'add_woocommerce_submenu_page' ) );
	}

	/**
	 * Adds the WooCommerce sub menu page
	 */
	public function add_woocommerce_submenu_page() {

		require $this->config->template_path . 'plugin-introduction.php';
	}


	/**
	 * Adds the FooEvents check-ins import sub menu page
	 */
	public function add_fooevents_checkins_import_page() {

		require $this->config->template_path . 'fooevents-checkins-import.php';
	}

	/**
	 * Redirect to the FooEvents check-ins import sub menu page
	 */
	public function redirect_to_fooevents_checkins_import() {

		wp_safe_redirect( admin_url( 'admin.php?page=fooevents-checkins-import' ) );

		exit;
	}

	/**
	 * Register FooEvents check-ins importer
	 */
	public function fooevents_checkins_register_importer() {

		register_importer( 'fooevents-checkins-import', __( 'FooEvents Check-ins Import', 'woocommerce-events' ), __( 'Import check-ins that were performed while using the FooEvents Check-ins app in offline mode.' ), array( $this, 'redirect_to_fooevents_checkins_import' ) );
	}

	/**
	 * Adds plugin links to the plugins page
	 *
	 * @param array $links links to display.
	 * @return array $links
	 */
	public function add_plugin_links( $links ) {

		$link_settings = '<a href="admin.php?page=fooevents-settings&tab=api">' . __( 'Settings', 'woocommerce-events' ) . '</a>';
		array_push(
			$links,
			$link_settings
		);

		$link_introduction = '<a href="admin.php?page=woocommerce-events-help">' . __( 'Getting Started', 'woocommerce-events' ) . '</a>';
		array_push(
			$links,
			$link_introduction
		);

		return $links;
	}

	/**
	 * Builds the calendar ICS file
	 */
	public function fooevents_ics() {

		$event = '';
		if ( isset( $_GET['event'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$event = sanitize_text_field( wp_unslash( $_GET['event'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

		}

		$ticket_id = '';
		if ( isset( $_GET['ticket'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$ticket_id = sanitize_text_field( wp_unslash( $_GET['ticket'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

		}

		$registrant_email = '';
		if ( isset( $_GET['email'] ) ) {// phpcs:ignore WordPress.Security.NonceVerification

			$registrant_email = isset( $_GET['email'] ) ? sanitize_text_field( wp_unslash( $_GET['email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		}

		$this->ics_helper->generate_ics( $event, $ticket_id, $registrant_email );

		$this->ics_helper->show();

		exit();
	}

	/**
	 * Get AddToWallet credits
	 */
	public function fooevents_addtowallet_credits() {
		$addtowallet_api_key = get_option( 'globalWooCommerceEventsAddToWalletAPIKey', true );

		if ( ! empty( $addtowallet_api_key ) ) {

				$wp_remote_request_args = array(
					'method'  => 'GET',
					'timeout' => 30,
					'headers' => array(
						'apikey' => $addtowallet_api_key,
					),
				);

				$response = wp_remote_post(
					'https://app.addtowallet.co/api/getCredits',
					$wp_remote_request_args
				);

				$response = json_decode( $response['body'], true );

				$response = 'Credits left: <b>' . $response['premiumCredits'] . '</b> premium and <b>' . $response['freeCredits'] . '</b> free credits.';

				return $response;
		}

		return false;
	}



	/**
	 * Create iOS and Android wallet passes using addtowallet.co
	 */
	public function fooevents_wallet_pass( $ticket_id = '', $ticket_action = '' ) {

		$ticket_update = true;

		if ( empty( $ticket_id ) && isset( $_GET['ticket'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$ticket_id     = sanitize_text_field( wp_unslash( $_GET['ticket'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$ticket_update = false;

		}

		$date               = '';
		$date_label         = '';
		$time               = '';
		$attendee_label     = '';
		$attendee_name      = '';
		$attendee_last_name = '';
		$location_label     = '';
		$location           = '';
		$variation          = '';
		$text_modules       = array();

		$event_id             = get_post_meta( $ticket_id, 'WooCommerceEventsProductID', true );
		$logo                 = get_post_meta( $event_id, 'WooCommerceEventsAddToWalletLogo', true );
		$background_color     = get_post_meta( $event_id, 'WooCommerceEventsAddToWalletBackgroundColor', true );
		$apple_font_color     = $this->get_contrasting_font_color( $background_color );
		$card_title           = get_post_meta( $event_id, 'WooCommerceEventsAddToWalletCardTitle', true );
		$card_header          = get_post_meta( $event_id, 'WooCommerceEventsAddToWalletCardHeader', true );
		$header_image         = get_post_meta( $event_id, 'WooCommerceEventsAddToWalletImage', true );
		$event_name           = get_post_meta( $ticket_id, 'WooCommerceEventsProductName', true );
		$display_attendee     = get_post_meta( $event_id, 'WooCommerceEventsDisplayAddToWalletAttendee', true );
		$display_location     = get_post_meta( $event_id, 'WooCommerceEventsDisplayAddToWalletLocation', true );
		$ticket_number        = get_post_meta( $ticket_id, 'WooCommerceEventsTicketID', true );
		$organizer_phone      = get_post_meta( $event_id, 'WooCommerceEventsSupportContact', true );
		$organizer_email      = get_post_meta( $event_id, 'WooCommerceEventsEmail', true );
		$display_date         = get_post_meta( $event_id, 'WooCommerceEventsDisplayAddToWalletDate', true );
		$display_booking_slot = get_post_meta( $event_id, 'WooCommerceEventsTicketDisplayBookings', true );
		$date_value           = get_post_meta( $event_id, 'WooCommerceEventsAddToWalletDateValue', true );
		$addtowallet_card     = get_post_meta( $ticket_id, 'WooCommerceEventsAddToWalletCard', true );
		$organizer_website    = get_site_url();
		$addtowallet_api_key  = get_option( 'globalWooCommerceEventsAddToWalletAPIKey', true );

		if ( str_contains( $card_header, '{var}' ) ) {
			$var_id    = get_post_meta( $ticket_id, 'WooCommerceEventsVariationID', true );
			$variation = wc_get_product( $var_id );
			if ( $variation && $variation->is_type( 'variation' ) ) {
				$card_header = str_replace( '{var}', wc_get_formatted_variation( $variation, true, false, false ), $card_header );
			}
		}
		if ( empty( $card_title ) ) {
			$card_title = $event_name;
		}

		if ( empty( $card_header ) ) {
			$card_header = $event_name;
		}

		if ( 'on' === $display_attendee ) {
			$attendee_label = get_post_meta( $event_id, 'WooCommerceEventsAddToWalletAttendeeLabel', true );
			if ( empty( $attendee_label ) ) {
				$attendee_label = esc_attr( 'Attendee', 'woocommerce-events' );
			}

			$attendee_name      = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeName', true );
			$attendee_last_name = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeLastName', true );

			if ( empty( $attendee_name ) && empty( $attendee_last_name ) ) {
				$attendee_name      = get_post_meta( $ticket_id, 'WooCommerceEventsPurchaserFirstName', true );
				$attendee_last_name = get_post_meta( $ticket_id, 'WooCommerceEventsPurchaserLastName', true );
			}
		}

		if ( 'on' === $display_date ) {
			$date_label = get_post_meta( $event_id, 'WooCommerceEventsAddToWalletDateLabel', true );
			if ( 'on' === $display_booking_slot ) {
				if ( empty( $date_label ) ) {
					$date_label = esc_attr( 'Booking Slot', 'woocommerce-events' );
				}

				if ( empty( $date_value ) ) {
					$date = get_post_meta( $ticket_id, 'WooCommerceEventsBookingDate', true ) . ' ' . get_post_meta( $ticket_id, 'WooCommerceEventsBookingSlot', true );
				} else {
					$date = $date_value;
				}
			} else {

				if ( empty( $date_label ) ) {
					$date_label = esc_attr( 'Date', 'woocommerce-events' );
				}

				if ( empty( $date_value ) ) {
					$date = get_post_meta( $event_id, 'WooCommerceEventsDate', true );

					if ( ! empty( get_post_meta( $event_id, 'WooCommerceEventsEndDate', true ) ) ) :
						$date .= ' - ' . get_post_meta( $event_id, 'WooCommerceEventsEndDate', true );
					endif;

					$time = get_post_meta( $event_id, 'WooCommerceEventsHour', true ) . ':' . get_post_meta( $event_id, 'WooCommerceEventsMinutes', true ) . ' ' . get_post_meta( $event_id, 'WooCommerceEventsPeriod', true );
					if ( ! empty( get_post_meta( $event_id, 'WooCommerceEventsHourEnd', true ) ) ) {
						$time .= ' - ' . get_post_meta( $event_id, 'WooCommerceEventsHourEnd', true ) . ':' . get_post_meta( $event_id, 'WooCommerceEventsMinutesEnd', true ) . ' ' . get_post_meta( $event_id, 'WooCommerceEventsEndPeriod', true );
					}

					$date .= "\n" . $time;
				} else {
					$date = $date_value;
				}
			}
		}

		if ( 'on' === $display_location ) {
			$location_label = get_post_meta( $event_id, 'WooCommerceEventsAddToWalletLocationLabel', true );
			if ( empty( $location_label ) ) {
				$location_label = esc_attr( 'Location', 'woocommerce-events' );
			}
			$location = get_post_meta( $event_id, 'WooCommerceEventsLocation', true );

		}

		$body_args = array(

			'cardTitle'          => $card_title,
			'header'             => $card_header,
			'textModulesData'    => array(
				array(
					'id'     => 'r1start',
					'header' => $attendee_label,
					'body'   => $attendee_name . ' ' . $attendee_last_name,
				),
				array(
					'id'     => 'r1end',
					'header' => $date_label,
					'body'   => $date,
				),
				array(
					'id'     => 'r2start',
					'header' => $location_label,
					'body'   => $location,
				),
			),
			'linksModuleData'    => array(
				array(
					'id'          => 'r1',
					'description' => 'Call Us',
					'uri'         => 'tel:' . $organizer_phone,
				),
				array(
					'id'          => 'r2',
					'description' => 'Email Us',
					'uri'         => 'mailto:' . $organizer_email,
				),
				array(
					'id'          => 'r3',
					'description' => 'Visit our website',
					'uri'         => $organizer_website,
				),
				array(
					'id'          => 'br1',
					'description' => 'Wallet Pass',
					'uri'         => 'text:Created with AddToWallet.co',
				),
				array(
					'id'            => 'r100',
					'description'   => '',
					'uri'           => 'Pass Updated',
					'changeMessage' => '%@',
				),
			),
			'barcodeType'        => 'QR_CODE',
			'barcodeValue'       => $ticket_number,
			'barcodeAltText'     => $ticket_number,
			'hexBackgroundColor' => $background_color,
			'appleFontColor'     => $apple_font_color,
			'heroImage'          => $header_image,
			'herorawurl'         => $header_image,
			'stateType'          => 'ACTIVE',
			'googleHeroImage'    => $header_image,
			'googleHeroRawUrl'   => $header_image,
			'appleHeroImage'     => $header_image,
			'appleHeroRawUrl'    => $header_image,
			'logoUrl'            => $logo,
			'cardType'           => 'Business Card',
			'premiumData'        => true,
		);

		if ( ! empty( $addtowallet_api_key ) ) {

			if ( empty( $addtowallet_card ) && 'delete' !== $ticket_action ) {

				$wp_remote_request_args = array(
					'method'  => 'POST',
					'timeout' => 30,
					'headers' => array(
						'apikey' => $addtowallet_api_key,
					),
					'body'    => $body_args,
				);

				/* The pass doesn't exist yet so needs to be created */
				$response = wp_remote_post(
					'https://app.addtowallet.co/api/card/create',
					$wp_remote_request_args
				);

				$response = json_decode( $response['body'], true );

				$msg = $response['msg'];

				if ( 'Card added successfully!' === $msg ) {

					update_post_meta( $ticket_id, 'WooCommerceEventsAddToWalletCard', $response['cardId'] );
					if ( ! $ticket_update ) {
						wp_redirect( 'https://app.addtowallet.co/card/' . $response['cardId'] );
						exit;
					}
				} else {
					var_dump( $response );
					echo 'There was a problem with generating the pass. Please contact the event organizer for assistance.';
				}
			} else {

				/* The pass exists so needs to be updated or deleted */

				if ( 'delete' === $ticket_action ) {

					$wp_remote_request_args = array(
						'method'  => 'DELETE',
						'timeout' => 30,
						'headers' => array(
							'apikey' => $addtowallet_api_key,
						),
					);

					$response = wp_remote_post(
						'https://app.addtowallet.co/api/card/delete/' . $addtowallet_card,
						$wp_remote_request_args
					);

					$response = json_decode( $response['body'], true );

					if ( 'Card deleted successfully!' === $response['msg'] ) {

						delete_post_meta( $ticket_id, 'WooCommerceEventsAddToWalletCard' );
						wp_redirect( home_url() );
						exit;

					} else {
						var_dump( $response );
						echo 'There was a problem with deleting the pass. Please contact the event organizer for assistance.';
					}
				} else {

					$wp_remote_request_args = array(
						'method'  => 'PUT',
						'timeout' => 30,
						'headers' => array(
							'apikey' => $addtowallet_api_key,
						),
						'body'    => $body_args,
					);

					$response = wp_remote_post(
						'https://app.addtowallet.co/api/card/edit/' . $addtowallet_card,
						$wp_remote_request_args
					);

					$response = json_decode( $response['body'], true );

					$msg = $response['msg'];

					if ( 'card updated successfully' === $msg ) {

						if ( 'fooevents_wallet_pass' === $_GET['action'] ) {
							wp_redirect( 'https://app.addtowallet.co/card/' . $addtowallet_card );
							exit;
						}
					} else {
						var_dump( $response );
						echo 'There was a problem with updating the pass. Please contact the event organizer for assistance.';
					}
				}
			}
		} else {
			echo 'There is no API key set for AddToWallet. Please contact the event organizer for assistance.';
		}
		if ( ! $ticket_update ) {
			exit;
		}
	}

	/**
	 * Updates all AddToWallet passes for an event only if at least one of the AddToWallet fields was udpated.
	 *
	 * @param int $event_id event ID.
	 */
	public function do_update_fooevents_addtowallet_passes( $event_id = '' ) {

		if ( '' === $event_id ) {
			return;
		}

		$tickets = new WP_Query(
			array(
				'post_type'      => array( 'event_magic_tickets' ),
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'   => 'WooCommerceEventsProductID',
						'value' => $event_id,
					),
					array(
						'relation' => 'AND',
						array(
							'key'     => 'WooCommerceEventsAddToWalletCard',
							'value'   => '',
							'compare' => '!=',
						),
						array(
							'key'     => 'WooCommerceEventsAddToWalletCard',
							'compare' => 'EXISTS',
						),
					),
				),
			)
		);

		$tickets = $tickets->get_posts();

		foreach ( $tickets as $ticket ) {

			$content = file_get_contents( get_site_url() . '/wp-admin/admin-ajax.php?action=fooevents_wallet_pass&ticket=' . $ticket->ID );
		}
	}


	/**
	 * Gets the contrasting font color based on the provided hex background colour. This is used specifically for the AddToWallet functionality, where the iOS font colour is always white
	 *
	 * @param string $bg_colour Colour set for the AddToWallet Pass background.
	 */
	public function get_contrasting_font_color( $bg_colour ) {
		// Remove '#' if present
		$bg_colour = ltrim( $bg_colour, '#' );

		// Convert 3-digit hex to 6-digit
		if ( strlen( $bg_colour ) === 3 ) {
			$bg_colour = $bg_colour[0] . $bg_colour[0] .
						$bg_colour[1] . $bg_colour[1] .
						$bg_colour[2] . $bg_colour[2];
		}

		// Convert hex to RGB
		$r = hexdec( substr( $bg_colour, 0, 2 ) );
		$g = hexdec( substr( $bg_colour, 2, 2 ) );
		$b = hexdec( substr( $bg_colour, 4, 2 ) );

		// Calculate the brightness (YIQ formula)
		$brightness = ( ( $r * 299 ) + ( $g * 587 ) + ( $b * 114 ) ) / 1000;

		// Return black for bright backgrounds, white for dark backgrounds
		return ( $brightness > 150 ) ? '#000000' : '#ffffff';
	}



	/**
	 * Changes the WooCommerce 'Add to cart' text
	 *
	 * @param string $text Add to cart text.
	 */
	public function woo_custom_cart_button_text( $text ) {

		global $post;
		global $product;

		$woocommerce_events_event                     = get_post_meta( $post->ID, 'WooCommerceEventsEvent', true );
		$global_woocommerce_events_change_add_to_cart = get_option( 'globalWooCommerceEventsChangeAddToCart', true );
		$ticket_term                                  = get_post_meta( $post->ID, 'WooCommerceEventsTicketOverride', true );

		if ( empty( $ticket_term ) ) {

			$ticket_term = get_option( 'globalWooCommerceEventsTicketOverride', true );

		}

		if ( empty( $ticket_term ) || true === $ticket_term ) {

			$ticket_term = __( 'Book ticket', 'woocommerce-events' );

		}

		if ( 'Event' === $woocommerce_events_event && 'yes' === $global_woocommerce_events_change_add_to_cart ) {

			return $ticket_term;

		} else {

			return $text;

		}
	}

	/**
	 * External access to ticket data
	 *
	 * @param int $ticket_id the ticket ID.
	 * @return array
	 */
	public function get_ticket_data( $ticket_id ) {

		// Main config.
		$this->config = new FooEvents_Config();

		// TicketHelper.
		require_once $this->config->class_path . 'class-fooevents-ticket-helper.php';
		$this->ticket_helper = new FooEvents_Ticket_Helper( $this->config );

		$ticket_data = $this->ticket_helper->get_ticket_data( $ticket_id );

		return $ticket_data;
	}

	/**
	 * Returns the plugin path
	 *
	 * @return string
	 */
	public function get_plugin_path() {

		return $this->config->path;
	}

	/**
	 * Returns the plugin URL
	 *
	 * @return string
	 */
	public function get_plugin_url() {

		return $this->config->event_plugin_url;
	}

	/**
	 * Returns the barcode path
	 *
	 * @return string
	 */
	public function get_barcode_path() {

		return $this->config->barcode_path;
	}

	/**
	 * Loads text-domain for localization
	 */
	public function load_text_domain() {

		$path   = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$loaded = load_plugin_textdomain( 'woocommerce-events', false, $path );
	}

	/**
	 * Remove expired events from shop listing
	 *
	 * @param object $query query.
	 * @return object
	 */
	public function remove_expired_posts( $query ) {

		if ( is_admin() ) {

			return;

		}

		$global_woocommerce_events_expire_option = get_option( 'globalWooCommerceEventsExpireOption' );

		if ( 'hide' === $global_woocommerce_events_expire_option && $query->is_main_query() ) {

			$today = current_time( 'timestamp' );

			$metaquery = array(
				'relation' => 'OR',
				array(
					'key'     => 'WooCommerceEventsExpireTimestamp',
					'compare' => 'NOT EXISTS',
					'value'   => '',
				),
				array(
					'key'     => 'WooCommerceEventsExpireTimestamp',
					'compare' => '=',
					'value'   => '',
				),
				array(
					'key'     => 'WooCommerceEventsExpireTimestamp',
					'value'   => $today,
					'type'    => 'numeric',
					'compare' => '>=',
				),
			);
			$query->set( 'meta_query', $metaquery );
		}

		return $query;
	}

		/**
		 * Remove expired events from related products
		 *
		 * @param array $query query.
		 * @return array
		 */
	public function remove_expired_posts_related_products( $query ) {

		if ( is_admin() ) {

			return;

		}

		global $wpdb;

		$global_woocommerce_events_expire_option = get_option( 'globalWooCommerceEventsExpireOption' );

		if ( 'hide' === $global_woocommerce_events_expire_option ) {

			$today = current_time( 'timestamp' );

			$query['join']  .= " INNER JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id ";
			$query['where'] .= " AND (pm.meta_key = 'WooCommerceEventsExpireTimestamp' AND meta_value = '' OR pm.meta_key = 'WooCommerceEventsExpireTimestamp' AND meta_value >= '" . $today . "')";

		}

		return $query;
	}

	/**
	 * Remove expired events from WooCommerce product shortcode
	 *
	 * @param array $query query.
	 */
	public function remove_expired_posts_woocommerce_shortcode( $query ) {

		if ( is_admin() ) {

			return $query;

		}

		$global_woocommerce_events_expire_option = get_option( 'globalWooCommerceEventsExpireOption' );

		if ( 'hide' === $global_woocommerce_events_expire_option ) {

			$today = current_time( 'timestamp' );

			$metaquery = array(
				'relation' => 'OR',
				array(
					'key'     => 'WooCommerceEventsExpireTimestamp',
					'compare' => 'NOT EXISTS',
					'value'   => '',
				),
				array(
					'key'     => 'WooCommerceEventsExpireTimestamp',
					'compare' => '=',
					'value'   => '',
				),
				array(
					'key'     => 'WooCommerceEventsExpireTimestamp',
					'value'   => $today,
					'type'    => 'numeric',
					'compare' => '>=',
				),
			);

			if ( ! empty( $metaquery ) ) {

				$query['meta_query'] = $metaquery;

			}
		}

		return $query;
	}

	/**
	 * If event has expired make not purchasable
	 *
	 * @param boolean $purchasable is purchaseable.
	 * @param object  $product product.
	 * @return boolean
	 */
	public function remove_add_to_cart_expired( $purchasable, $product ) {

		$id = $product->get_id();

		$timestamp                = get_post_meta( $id, 'WooCommerceEventsExpireTimestamp', true );
		$woocommerce_events_event = get_post_meta( $id, 'WooCommerceEventsEvent', true );
		$today                    = current_time( 'timestamp' );

		if ( $purchasable && $product->is_type( 'variation' ) ) {

			$woocommerce_events_event = get_post_meta( $product->get_parent_id(), 'WooCommerceEventsEvent', true );
			$timestamp                = get_post_meta( $product->get_parent_id(), 'WooCommerceEventsExpireTimestamp', true );

		}

		if ( ! empty( $timestamp ) && 'Event' === $woocommerce_events_event && $today > $timestamp ) {

			$purchasable = false;

		}

		return $purchasable;
	}

	/**
	 * Display message on expired products
	 *
	 * @global object $product
	 */
	public function remove_add_to_cart_expired_text() {

		global $product;
		$id                                = $product->get_id();
		$timestamp                         = get_post_meta( $id, 'WooCommerceEventsExpireTimestamp', true );
		$woocommerce_events_event          = get_post_meta( $id, 'WooCommerceEventsEvent', true );
		$woocommerce_events_expire_message = get_post_meta( $id, 'WooCommerceEventsExpireMessage', true );
		$today                             = current_time( 'timestamp' );

		if ( ! empty( $timestamp ) && ! empty( $woocommerce_events_expire_message ) && 'Event' === $woocommerce_events_event && $today > $timestamp && ! $product->is_purchasable() ) {

			echo '<div class="fooevents-expired-message">' . wp_kses_post( wpautop( $woocommerce_events_expire_message ) ) . '</div>';

		}
	}

	/**
	 * Format array for the datepicker
	 * WordPress stores the locale information in an array with a alphanumeric index, and
	 * the datepicker wants a numerical index. This function replaces the index with a number
	 *
	 * @param array $array_to_strip array to strip.
	 * @return array
	 */
	private function strip_array_indices( $array_to_strip ) {

		foreach ( $array_to_strip as $item ) {

			$new_array[] = $item;

		}

		return( $new_array );
	}

	/**
	 * Test if WooCommerce checkout block is enabled.
	 */
	private function is_checkout_block_enabled() {

		$checkout_page_id = wc_get_page_id( 'checkout' );
		$checkout_content = get_post_field( 'post_content', $checkout_page_id );

		$checkout_block_enabled = false;
		if ( has_block( 'woocommerce/checkout', $checkout_content ) ) {

			$checkout_block_enabled = true;

		}

		return $checkout_block_enabled;
	}

	/**
	 * Convert the php date format string to a js date format
	 *
	 * @param string $format format.
	 */
	private function date_format_php_to_js( $format ) {

		$return_format = $format;
		switch ( $format ) {
			// Predefined WP date formats.
			case 'D d-m-y':
				$return_format = 'D dd-mm-yy';
				break;

			case 'D d-m-Y':
				$return_format = 'D dd-mm-yy';
				break;

			case 'l d-m-Y':
				$return_format = 'DD dd-mm-yy';
				break;

			case 'jS F Y':
				$return_format = 'd MM yy';
				break;

			case 'F j, Y':
				$return_format = 'MM d, yy';
				break;

			case 'F j Y':
				$return_format = 'MM d yy';
				break;

			case 'j F, Y':
				$return_format = 'd MM, yy';
				break;

			case 'M. j, Y':
				$return_format = 'M. d, yy';
				break;

			case 'M. d, Y':
				$return_format = 'M. dd, yy';
				break;

			case 'mm/dd/yyyy':
				$return_format = 'mm/dd/yy';
				break;

			case 'j F Y':
				$return_format = 'd MM yy';
				break;

			case 'Y/m/d':
				$return_format = 'yy/mm/dd';
				break;

			case 'm/d/Y':
				$return_format = 'mm/dd/yy';
				break;

			case 'd/m/Y':
				$return_format = 'dd/mm/yy';
				break;

			case 'd.m.Y':
				$return_format = 'dd.mm.yy';
				break;

			case 'Y-m-d':
				$return_format = 'yy-mm-dd';
				break;

			case 'm-d-Y':
				$return_format = 'mm-dd-yy';
				break;

			case 'd-m-Y':
				$return_format = 'dd-mm-yy';
				break;

			case 'j. FY':
				$return_format = 'd. MMyy';
				break;

			case 'j. F Y':
				$return_format = 'd. MM yy';
				break;

			case 'j. F, Y':
				$return_format = 'd. MM, yy';
				break;

			case 'j.m.Y':
				$return_format = 'd.mm.yy';
				break;

			case 'j.n.Y':
				$return_format = 'd.m.yy';
				break;

			case 'j. n. Y':
				$return_format = 'd. m. yy';
				break;

			case 'j.n. Y':
				$return_format = 'd.m. yy';
				break;

			case 'j \d\e F \d\e Y':
				$return_format = 'd MM yy';
				break;

			case 'D j M Y':
				$return_format = 'D d M yy';
				break;

			case 'D F j':
				$return_format = 'D MM d';
				break;

			case 'l j F Y':
				$return_format = 'DD d MM yy';
				break;

			case 'l, j M Y':
				$return_format = 'DD, d M yy';
				break;

			case 'M d / Y':
				$return_format = 'M dd / yy';
				break;

			case 'M j / Y':
				$return_format = 'M d / yy';
				break;

			default:
				$return_format = 'yy-mm-dd';
		}

		return $return_format;
	}

	/**
	 * Does nothing, contains translation terms for other FooEvents plugins.
	 */
	private function do_nothing_translations() {

		esc_attr_e( 'These are your tickets for:', 'woocommerce-events' );
		esc_attr_e( 'Ticket details:', 'woocommerce-events' );
		esc_attr_e( 'Ticket Number:', 'woocommerce-events' );
		esc_attr_e( 'Location:', 'woocommerce-events' );
		esc_attr_e( 'Directions:', 'woocommerce-events' );
		esc_attr_e( 'Ticket Holder:', 'woocommerce-events' );
		esc_attr_e( 'Telephone Number:', 'woocommerce-events' );
		esc_attr_e( 'Company:', 'woocommerce-events' );
		esc_attr_e( 'Designation:', 'woocommerce-events' );
		esc_attr_e( 'Price:', 'woocommerce-events' );
	}

	/**
	 * Filter tickets listing based on event filter selection
	 *
	 * @param object $query query.
	 */
	public function fooevents_filter_ticket_results( $query ) {

		global $pagenow;
		$fooevents_filter = '';

		if ( is_admin() && 'edit.php' === $pagenow && isset( $_GET['event_id'] ) && '' !== $_GET['event_id'] ) { // phpcs:ignore WordPress.Security.NonceVerification

			$fooevents_filter                = sanitize_text_field( wp_unslash( $_GET['event_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$query->query_vars['meta_key']   = 'WooCommerceEventsProductID';
			$query->query_vars['meta_value'] = $fooevents_filter;

		}
	}
}

/**
 * Display event tab
 */
function fooevents_display_event_tab() {

	global $post;
	$config = new FooEvents_Config();

	$woocommerce_events_event_details_text      = get_post_meta( $post->ID, 'WooCommerceEventsEventDetailsText', true );
	$woocommerce_events_background_color        = get_post_meta( $post->ID, 'WooCommerceEventsBackgroundColor', true );
	$woocommerce_events_text_color              = get_post_meta( $post->ID, 'WooCommerceEventsTextColor', true );
	$woocommerce_events_event                   = get_post_meta( $post->ID, 'WooCommerceEventsEvent', true );
	$woocommerce_events_date                    = get_post_meta( $post->ID, 'WooCommerceEventsDate', true );
	$woocommerce_events_date_mysql              = get_post_meta( $post->ID, 'WooCommerceEventsDateMySQLFormat', true );
	$woocommerce_events_end_date                = get_post_meta( $post->ID, 'WooCommerceEventsEndDate', true );
	$woocommerce_events_hour                    = get_post_meta( $post->ID, 'WooCommerceEventsHour', true );
	$woocommerce_events_minutes                 = get_post_meta( $post->ID, 'WooCommerceEventsMinutes', true );
	$woocommerce_events_period                  = get_post_meta( $post->ID, 'WooCommerceEventsPeriod', true );
	$woocommerce_events_hour_end                = get_post_meta( $post->ID, 'WooCommerceEventsHourEnd', true );
	$woocommerce_events_minutes_end             = get_post_meta( $post->ID, 'WooCommerceEventsMinutesEnd', true );
	$woocommerce_events_end_period              = get_post_meta( $post->ID, 'WooCommerceEventsEndPeriod', true );
	$woocommerce_events_timezone                = get_post_meta( $post->ID, 'WooCommerceEventsTimeZone', true );
	$woocommerce_events_location                = get_post_meta( $post->ID, 'WooCommerceEventsLocation', true );
	$woocommerce_events_ticket_logo             = get_post_meta( $post->ID, 'WooCommerceEventsTicketLogo', true );
	$woocommerce_events_support_contact         = get_post_meta( $post->ID, 'WooCommerceEventsSupportContact', true );
	$woocommerce_events_gps                     = get_post_meta( $post->ID, 'WooCommerceEventsGPS', true );
	$woocommerce_events_directions              = get_post_meta( $post->ID, 'WooCommerceEventsDirections', true );
	$woocommerce_events_email                   = get_post_meta( $post->ID, 'WooCommerceEventsEmail', true );
	$woocommerce_events_multi_day_type          = get_post_meta( $post->ID, 'WooCommerceEventsMultiDayType', true );
	$woocommerce_events_select_date             = get_post_meta( $post->ID, 'WooCommerceEventsSelectDate', true );
	$woocommerce_events_select_date_hour        = get_post_meta( $post->ID, 'WooCommerceEventsSelectDateHour', true );
	$woocommerce_events_select_date_minutes     = get_post_meta( $post->ID, 'WooCommerceEventsSelectDateMinutes', true );
	$woocommerce_events_select_date_period      = get_post_meta( $post->ID, 'WooCommerceEventsSelectDatePeriod', true );
	$woocommerce_events_select_date_hour_end    = get_post_meta( $post->ID, 'WooCommerceEventsSelectDateHourEnd', true );
	$woocommerce_events_select_date_minutes_end = get_post_meta( $post->ID, 'WooCommerceEventsSelectDateMinutesEnd', true );
	$woocommerce_events_select_date_period_end  = get_post_meta( $post->ID, 'WooCommerceEventsSelectDatePeriodEnd', true );
	$woocommerce_events_type                    = get_post_meta( $post->ID, 'WooCommerceEventsType', true );
	$woocommerce_events_select_global_time      = get_post_meta( $post->ID, 'WooCommerceEventsSelectGlobalTime', true );

	$day_term = get_post_meta( $post->ID, 'WooCommerceEventsDayOverride', true );

	if ( empty( $day_term ) ) {

		$day_term = get_option( 'WooCommerceEventsDayOverride', true );

	}

	if ( empty( $day_term ) || 1 === (int) $day_term ) {

		$day_term = __( 'Day', 'woocommerce-events' );

	}

	$multi_day_event = false;

	if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

		require_once ABSPATH . '/wp-admin/includes/plugin.php';

	}

	if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

		$multi_day_event = true;

	}

	

	if ( '' !== $woocommerce_events_timezone ) {

		$timezone_date = new DateTime($woocommerce_events_date_mysql);
		
		try {

			$tz = new DateTimeZone( $woocommerce_events_timezone );

		} catch ( Exception $e ) {

			$server_timezone = date_default_timezone_get();
			$tz              = new DateTimeZone( $server_timezone );

		}

		$timezone_date->setTimeZone( $tz );
		$timezone = $timezone_date->format( 'T' );

		if ( (int) $timezone > 0 ) {
			$timezone = 'UTC' . $timezone;
		}
		
		/* Get timezone for each Select date entry */
		$x = 0;
		foreach ( $woocommerce_events_select_date as $date ) {

			$select_date_timezone[$x]["date"] = $date;
			$timezone_multi_date = new DateTime($date);
			$timezone_multi_date->setTimeZone( $tz );
			$timezone_multi = $timezone_multi_date->format( 'T' );
			if ( (int) $timezone_multi > 0 ) {
				$timezone_multi = 'UTC' . $timezone_multi;
			}
			
			$select_date_timezone[$x]["timezone"] = $timezone_multi;
			$x++;

		}

	} else {

		$timezone = '';

	}

	if ( file_exists( $config->email_template_path_theme . 'event-tab.php' ) ) {

		require $config->email_template_path_theme . 'event-tab.php';

	} else {

		require $config->template_path . 'event-tab.php';

	}
}

/**
 * Display Google Map in event tab
 */
function fooevents_display_event_tab_map() {

	global $post;
	$config = new FooEvents_Config();

	$woocommerce_events_google_maps                = get_post_meta( $post->ID, 'WooCommerceEventsGoogleMaps', true );
	$global_woocommerce_events_google_maps_api_key = get_option( 'globalWooCommerceEventsGoogleMapsAPIKey', true );
	$global_woocommerce_events_google_maps_method  = get_option( 'globalWooCommerceEventsGoogleMapsMethod', '' );

	if ( 1 == $global_woocommerce_events_google_maps_api_key ) {

		$global_woocommerce_events_google_maps_api_key = '';

	}

	$event_content = $post->post_content;

	$event_content = apply_filters( 'the_content', $event_content );

	if ( file_exists( $config->email_template_path_theme . 'event-tab-map.php' ) ) {

		require $config->email_template_path_theme . 'event-tab-map.php';

	} else {

		require $config->template_path . 'event-tab-map.php';

	}
}

add_action( 'wp_dashboard_setup', 'fooevents_dashboard_widget' );

/**
 * Register dashboard widget
 */
function fooevents_dashboard_widget() {

	wp_add_dashboard_widget(
		'fooevents_widget',
		'FooEvents',
		'fooevents_widget_display'
	);
}

/**
 * Display dashboard widget
 */
function fooevents_widget_display() {

	$fooevents   = get_plugin_data( WP_PLUGIN_DIR . '/fooevents/fooevents.php' );
	$woocommerce = get_plugin_data( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );

	echo "<p><a href='" . esc_attr( $fooevents['PluginURI'] ) . "' target='_BLANK'>FooEvents</a> " . esc_attr( $fooevents['Version'] ) . " running on <a href='" . esc_attr( $woocommerce['PluginURI'] ) . "' target='_BLANK'>WooCommerce</a> " . esc_attr( $woocommerce['Version'] ) . '</p>';

	if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

		require_once ABSPATH . '/wp-admin/includes/plugin.php';

	}

	$fooevents_pdf_tickets_active = 'No';
	$fooevents_pdf_tickets        = array( 'Version' => '' );
	if ( is_plugin_active( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) || is_plugin_active_for_network( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) ) {

		$fooevents_pdf_tickets_active = 'Yes';
		$fooevents_pdf_tickets        = get_plugin_data( WP_PLUGIN_DIR . '/fooevents_pdf_tickets/fooevents-pdf-tickets.php' );

	}

	echo "<div class='fooevents_widget_item'><a href='https://www.fooevents.com/fooevents-pdf-tickets/' target='_BLANK'>FooEvents PDF tickets</a>: " . '<b>' . esc_attr( $fooevents_pdf_tickets_active ) . '</b> ' . esc_attr( $fooevents_pdf_tickets['Version'] ) . '</div>';

	$fooevents_express_check_in_active = 'No';
	$fooevents_express_check_in        = array( 'Version' => '' );
	if ( is_plugin_active( 'fooevents_express_check_in/fooevents-express-check_in.php' ) || is_plugin_active_for_network( 'fooevents_express_check_in/fooevents-express-check_in.php' ) ) {

		$fooevents_express_check_in_active = 'Yes';
		$fooevents_express_check_in        = get_plugin_data( WP_PLUGIN_DIR . '/fooevents_express_check_in/fooevents-express-check_in.php' );

	}

	echo "<div class='fooevents_widget_item'><a href='https://www.fooevents.com/fooevents-express-check-in/' target='_BLANK'>FooEvents Express Check-in</a>: " . '<b>' . esc_attr( $fooevents_express_check_in_active ) . '</b> ' . esc_attr( $fooevents_express_check_in['Version'] ) . '</div>';

	$fooevents_calendar_active = 'No';
	$fooevents_calendar        = array( 'Version' => '' );
	if ( is_plugin_active( 'fooevents-calendar/fooevents-calendar.php' ) || is_plugin_active_for_network( 'fooevents-calendar/fooevents-calendar.php' ) ) {

		$fooevents_calendar_active = 'Yes';
		$fooevents_calendar        = get_plugin_data( WP_PLUGIN_DIR . '/fooevents-calendar/fooevents-calendar.php' );

	}

	echo "<div class='fooevents_widget_item'><a href='https://www.fooevents.com/fooevents-calendar/' target='_BLANK'>FooEvents Calendar</a>: " . '<b>' . esc_attr( $fooevents_calendar_active ) . '</b> ' . esc_attr( $fooevents_calendar['Version'] ) . '</div>';

	$fooevents_custom_attendee_fields_active = 'No';
	$fooevents_custom_attendee_fields        = array( 'Version' => '' );
	if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

		$fooevents_custom_attendee_fields_active = 'Yes';
		$fooevents_custom_attendee_fields        = get_plugin_data( WP_PLUGIN_DIR . '/fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' );

	}

	echo "<div class='fooevents_widget_item'><a href='https://www.fooevents.com/fooevents-custom-attendee-fields/' target='_BLANK'>FooEvents Custom Attendee Fields</a>: " . '<b>' . esc_attr( $fooevents_custom_attendee_fields_active ) . '</b> ' . esc_attr( $fooevents_custom_attendee_fields['Version'] ) . '</div>';

	$fooevents_multi_day_active = 'No';
	$fooevents_multi_day        = array( 'Version' => '' );
	if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

		$fooevents_multi_day_active = 'Yes';
		$fooevents_multi_day        = get_plugin_data( WP_PLUGIN_DIR . '/fooevents_multi_day/fooevents-multi-day.php' );

	}

	echo "<div class='fooevents_widget_item'><a href='https://www.fooevents.com/fooevents-multi-day/' target='_BLANK'>FooEvents Multi-Day</a>: " . '<b>' . esc_attr( $fooevents_multi_day_active ) . '</b> ' . esc_attr( $fooevents_multi_day['Version'] ) . '</div>';

	$fooevents_seating_active = 'No';
	$fooevents_seating        = array( 'Version' => '' );
	if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

		$fooevents_seating_active = 'Yes';
		$fooevents_seating        = get_plugin_data( WP_PLUGIN_DIR . '/fooevents_seating/fooevents-seating.php' );

	}

	echo "<div class='fooevents_widget_item'><a href='https://www.fooevents.com/fooevents-seating/' target='_BLANK'>FooEvents Seating</a>: " . '<b>' . esc_attr( $fooevents_seating_active ) . '</b> ' . esc_attr( $fooevents_seating['Version'] ) . '</div>';
}

/**
 * On plugin uninstall
 */
function uninstall_fooevents() {

	delete_option( 'globalWooCommerceEventsAPIKey' );
	delete_option( 'globalWooCommerceEnvatoAPIKey' );
	delete_option( 'globalWooCommerceEventsGoogleMapsMethod' );
	delete_option( 'globalWooCommerceEventsGoogleMapsAPIKey' );
	delete_option( 'globalWooCommerceEventsAddToWalletAPIKey' );
	delete_option( 'globalWooCommerceEventsMailchimpAPIKey' );
	delete_option( 'globalWooCommerceEventsMailchimpServer' );
	delete_option( 'globalWooCommerceEventsMailchimpList' );
	delete_option( 'globalWooCommerceEventsMailchimpTags' );
	delete_option( 'globalWooCommerceEventsTicketBackgroundColor' );
	delete_option( 'globalWooCommerceEventsTicketButtonColor' );
	delete_option( 'globalWooCommerceEventsTicketTextColor' );
	delete_option( 'globalWooCommerceEventsTicketLogo' );
	delete_option( 'globalWooCommerceEventsTicketHeaderImage' );
	delete_option( 'globalWooCommerceEventsChangeAddToCart' );
	delete_option( 'globalWooCommerceEventSorting' );
	delete_option( 'globalWooCommerceDisplayEventDate' );
	delete_option( 'globalWooCommerceHideEventDetailsTab' );
	delete_option( 'globalWooCommerceUsePlaceHolders' );
	delete_option( 'globalWooCommerceHideUnpaidTicketsApp' );
	delete_option( 'globalWooCommerceEventsHideUnpaidTickets' );
	delete_option( 'globalWooCommerceEventsSuppressAdminNotifications' );
	delete_option( 'globalWooCommerceEventsUseTotalSales' );
	delete_option( 'globalWooCommerceEventsEmailTicketAdmin' );
	delete_option( 'globalWooCommerceEventsAddCopyPurchaserDetails' );
	delete_option( 'globalWooCommerceEventsAppTitle' );
	delete_option( 'globalWooCommerceEventsAppLogo' );
	delete_option( 'globalWooCommerceEventsAppColor' );
	delete_option( 'globalWooCommerceEventsAppTextColor' );
	delete_option( 'globalWooCommerceEventsAppBackgroundColor' );
	delete_option( 'globalWooCommerceEventsAppSignInTextColor' );
	delete_option( 'globalWooCommerceEventsAppEvents' );
	delete_option( 'globalWooCommerceEventsAppEventIDs' );
	delete_option( 'globalWooCommerceEventsAppShowAllForAdmin' );
	delete_option( 'globalWooCommerceEventsAppHidePersonalInfo' );
	delete_option( 'globalWooCommerceEventsAppTicketsPerRequest' );
	delete_option( 'globalWooCommerceEventsAppTicketAccessLog' );
	delete_option( 'globalWooCommerceEventsEventOverride' );
	delete_option( 'globalWooCommerceEventsEventOverridePlural' );
	delete_option( 'globalWooCommerceEventsAttendeeOverride' );
	delete_option( 'globalWooCommerceEventsAttendeeOverridePlural' );
	delete_option( 'globalWooCommerceEventsTicketOverride' );
	delete_option( 'globalWooCommerceEventsTicketOverridePlural' );
	delete_option( 'WooCommerceEventsDayOverride' );
	delete_option( 'WooCommerceEventsDayOverridePlural' );
	delete_option( 'WooCommerceEventsCopyOverride' );
	delete_option( 'WooCommerceEventsCopyOverridePlural' );
	delete_option( 'globalWooCommerceEventsZoomAccountID' );
	delete_option( 'globalWooCommerceEventsZoomClientID' );
	delete_option( 'globalWooCommerceEventsZoomClientSecret' );
	delete_option( 'globalWooCommerceEventsZoomUsers' );
	delete_option( 'globalWooCommerceEventsZoomSelectedUserOption' );
	delete_option( 'globalWooCommerceEventsZoomSelectedUsers' );
	delete_option( 'globalWooCommerceEventsDisableSubTicketGen' );
	delete_option( 'globalWooCommerceEventsBarcodeOutput' );
	delete_option( 'globalWooCommerceEventsDisplayPoweredby' );
}
register_uninstall_hook( __FILE__, 'uninstall_fooevents' );

add_action( 'admin_init', 'fooevents_redirect' );
add_action( 'activated_plugin', 'fooevents_activate' );

/**
 *  Add option to redirect on plugin activation.
 *
 * @param string $plugin the plugin.
 */
function fooevents_activate( $plugin ) {

	if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

		require_once ABSPATH . '/wp-admin/includes/plugin.php';

	}

	if ( 'fooevents/fooevents.php' === $plugin ) {

		$salt = wp_rand( 111111, 999999 );
		update_option( 'woocommerce_events_do_salt', $salt );

		add_option( 'fooevents_do_activation_redirect', true );

	}
}

/**
 *  Do activation redirect if required.
 */
function fooevents_redirect() {

	if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

		require_once ABSPATH . '/wp-admin/includes/plugin.php';

	}

	$option = get_option( 'fooevents_do_activation_redirect', false );

	if ( $option ) {

		delete_option( 'fooevents_do_activation_redirect' );

		if ( ! isset( $_GET['activate-multi'] ) ) {

			wp_redirect( 'admin.php?page=woocommerce-events-help' );

		}
	}
}

if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) || ! function_exists( 'get_plugin_data' ) ) {

	require_once ABSPATH . '/wp-admin/includes/plugin.php';

}

if ( is_plugin_active( 'automatewoo/automatewoo.php' ) || is_plugin_active_for_network( 'automatewoo/automatewoo.php' ) ) {

	require_once __DIR__ . '/classes/automatewoo/fooevents-automatewoo.php';

}
