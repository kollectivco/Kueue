<?php
/**
 * Main plugin class.
 *
 * @link https://www.fooevents.com
 * @package fooevents-bookings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * Main plugin class.
 */
class FooEvents_Bookings {

	/**
	 * Configuration object
	 *
	 * @var object $config contains paths and other configurations
	 */
	private $config;

	/**
	 * Update helper object
	 *
	 * @var object $update_helper responsible for plugin updates
	 */
	private $update_helper;

	/**
	 * Bookings admin object
	 *
	 * @var object $bookings_admin responsible for bookings admin functionality
	 */
	private $bookings_admin;

	/**
	 * On plugin load
	 */
	public function __construct( $suppress_actions = false ) {

		$this->plugin_init();

		add_action( 'admin_init', array( $this, 'register_scripts' ) );
		add_action( 'admin_init', array( $this, 'plugin_notice_dismissed' ) );
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_product_bookings_options_tab' ), 23 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'add_product_bookings_options_tab_options' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_frontend_scripts' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'process_meta_box' ) );
		add_action( 'wp_ajax_nopriv_fetch_fooevents_bookings_date_slot_slots', array( $this, 'fetch_bookings_date_slot_slots' ) );
		add_action( 'wp_ajax_fetch_fooevents_bookings_date_slot_slots', array( $this, 'fetch_bookings_date_slot_slots' ) );
		add_action( 'wp_ajax_nopriv_fetch_fooevents_bookings_dates', array( $this, 'fetch_bookings_dates' ) );
		add_action( 'wp_ajax_fetch_fooevents_admin_bookings_dates', array( $this, 'fetch_bookings_dates_admin' ) );
		add_action( 'wp_ajax_fooevents_fetch_add_ticket_booking_options', array( $this, 'fetch_add_ticket_booking_options' ) );
		add_action( 'wp_ajax_fetch_fooevents_bookings_dates', array( $this, 'fetch_bookings_dates' ) );
		add_action( 'wp_ajax_nopriv_fetch_fooevents_bookings_date_stock', array( $this, 'fetch_fooevents_bookings_date_stock' ) );
		add_action( 'wp_ajax_fetch_fooevents_bookings_date_stock', array( $this, 'fetch_fooevents_bookings_date_stock' ) );
		add_action( 'wp_ajax_nopriv_fetch_fooevents_bookings_slot_date_stock', array( $this, 'fetch_fooevents_bookings_slot_date_stock' ) );
		add_action( 'wp_ajax_fetch_fooevents_bookings_slot_date_stock', array( $this, 'fetch_fooevents_bookings_slot_date_stock' ) );
		add_action( 'wp_ajax_nopriv_fetch_fooevents_bookings_date_slot_stock', array( $this, 'fetch_fooevents_bookings_date_slot_stock' ) );
		add_action( 'wp_ajax_fetch_fooevents_bookings_date_slot_stock', array( $this, 'fetch_fooevents_bookings_date_slot_stock' ) );
		add_action( 'manage_event_magic_tickets_posts_custom_column', array( &$this, 'add_admin_column_content' ) );
		add_action( 'wp_ajax_fooevents_save_booking_options', array( $this, 'save_booking_options' ) );
		add_action( 'wp_ajax_fooevents_delete_zoom', array( $this, 'delete_zoom_meeting' ) );
		add_action( 'woocommerce_add_cart_item_data', array( $this, 'add_slot_date_addtocart' ), 11, 4 );
		add_action( 'woocommerce_add_to_cart_validation', array( $this, 'addtocart_booking_availability' ), 11, 3 );
		add_action( 'save_post', array( &$this, 'save_edit_ticket_meta_boxes' ), 1, 2 );
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'order_cancelled_return_stock' ), 10, 1 );
		add_action( 'woocommerce_order_status_refunded', array( $this, 'order_refunded_return_stock' ) );
		add_action( 'woocommerce_order_partially_refunded', array( $this, 'order_partial_refunded_return_stock' ), 10, 2 );
		add_action( 'woocommerce_order_fully_refunded', array( $this, 'order_partial_refunded_return_stock' ), 10, 2 );
		add_action( 'init', array( $this, 'load_text_domain' ) );
		add_action( 'woocommerce_get_item_data', array( $this, 'add_booking_details_to_cart' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'check_fooevents' ), 10 );
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hide_bookings_meta' ), 99, 1 );
		add_action( 'woocommerce_thankyou', array( $this, 'split_order_items' ), 10, 1 );
		add_action( 'woocommerce_product_is_in_stock', array( $this, 'check_for_no_slots' ), 10, 2 );
		add_filter( 'woocommerce_cart_item_quantity', array( $this, 'wc_cart_item_quantity' ), 10, 3 );

		add_action( 'woocommerce_order_status_changed', array( $this, 'add_order_meta_on_ticket_create' ), 100, 3 );
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( $this, 'unset_specific_order_item_meta_data' ), 10, 2 );

		if ( false === $suppress_actions ) {

			add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'output_booking_fields_product' ) );

		}

		// WPML.
		add_action( 'woocommerce_thankyou', array( $this, 'wpml_sync_bookings_between_translations_new_order' ) );
	}

	/**
	 *  Initialize bookings plugin and helpers.
	 */
	public function plugin_init() {

		// Main config.
		$this->config = new FooEvents_Bookings_Config();

		// Update Helper.
		require_once $this->config->class_path . 'updatehelper.php';
		$this->update_helper = new Fooevents_Bookings_Update_Helper( $this->config );

		// Bookings admin.
		require_once $this->config->class_path . 'class-fooevents-bookings-admin.php';
		$this->bookings_admin = new FooEvents_Bookings_Admin( $this->config );

		if ( ! function_exists( 'get_plugin_data' ) ) {

			require_once ABSPATH . 'wp-admin/includes/plugin.php';

		}
	}

	/**
	 * Checks if FooEvents is installed
	 */
	public function check_fooevents() {

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( ! is_plugin_active( 'fooevents/fooevents.php' ) ) {

				$this->output_notices( array( __( 'The FooEvents Bookings plugin requires FooEvents for WooCommerce to be installed.', 'fooevents-bookings' ) ) );

		}

		if ( isset( $_GET['page'] ) && 'fooevents-bookings-admin' === $_GET['page'] ) {

			$this->output_notice_dismissible( sprintf( __( 'Only tickets that were generated after the <strong>FooEvents Bookings plugin</strong> was updated to <strong>version 1.4.0</strong> can be viewed when filtering down to a date level. In order to filter by date and view tickets that were generated before version 1.4.0, please %1$sfollow these steps%2$s.', 'fooevents-bookings' ), '<a href="https://help.fooevents.com/docs/topics/code-snippets/fooevent-bookings-timestamp-updater/" target="_blank">', '</a>' ), 'fooevents-bookings-admin', 'fooevents-bookings-timestamp' );

		}
	}

	/**
	 * Register plugin scripts.
	 */
	public function register_scripts() {

		global $wp_locale;
		global $woocommerce;

		$woocommerce_currency_symbol = '';
		if ( class_exists( 'WooCommerce' ) ) {

				$woocommerce_currency_symbol = get_woocommerce_currency_symbol();

		}

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ( isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) || ( isset( $_GET['post_type'] ) && 'product' === $_GET['post_type'] ) ) {

			$fooevents_bookings_prod_obj = array(
				'closeText'         => __( 'Done', 'fooevents-bookings' ),
				'currentText'       => __( 'Today', 'fooevents-bookings' ),
				'time'              => __( 'Time', 'fooevents-bookings' ),
				'addDate'           => __( 'Add Date', 'fooevents-bookings' ),
				'unlimitedStock'    => __( 'Unlimited stock', 'fooevents-bookings' ),
				'webinars'          => __( 'Webinars', 'fooevents-bookings' ),
				'meetings'          => __( 'Meetings', 'fooevents-bookings' ),
				'savingChanges'     => __( 'Saving Changes', 'fooevents-bookings' ),
				'saveChanges'       => __( 'Save Changes', 'fooevents-bookings' ),
				'optionsSaved'      => __( 'Your booking options have been saved.', 'fooevents-bookings' ),
				'illegalCharacter'  => __( 'Field cannot contain quotes.', 'fooevents-bookings' ),
				'deleteZoom'        => __( 'Also delete Zoom meeting/webinar?', 'fooevents-bookings' ),
				'zoomDeleteSuccess' => __( 'Successfully deleted Zoom meeting/webinar.', 'fooevents-bookings' ),
				'zoomDeleteError'   => __( 'Unable to delete the Zoom meeting/webinar.', 'fooevents-bookings' ),
				'removeSlot'        => __( 'Are you sure that you want to remove this booking slot?', 'fooevents-bookings' ),
				'startDateRequired' => __( 'Please enter a Start Date.', 'fooevents-bookings' ),
				'endDateRequired'   => __( 'Please enter an End Date. The End Date should be within a maximum of 6 months from the Start Date each time you generate new date slots.', 'fooevents-bookings' ),
				'weekDayRequired'   => __( 'Please select the days of the week.', 'fooevents-bookings' ),
				'sixMonthLimit'     => __( 'The date range cannot exceed 6 months from the Start Date. Please adjust the End Date before proceeding. Note: You can reopen the Booking Wizard after completion to generate additional date slots if required.', 'fooevents-bookings' ),
				'idGenError'        => __( 'An error occured, please try again.', 'fooevents-bookings' ),
				'monthNames'        => $this->strip_array_indices( $wp_locale->month ),
				'monthNamesShort'   => $this->strip_array_indices( $wp_locale->month_abbrev ),
				'monthStatus'       => __( 'Show a different month', 'fooevents-bookings' ),
				'dayNames'          => $this->strip_array_indices( $wp_locale->weekday ),
				'dayNamesShort'     => $this->strip_array_indices( $wp_locale->weekday_abbrev ),
				'dayNamesMin'       => $this->strip_array_indices( $wp_locale->weekday_initial ),
				'dateFormat'        => $this->date_format_php_to_js( get_option( 'date_format' ) ),
				'firstDay'          => get_option( 'start_of_week' ),
				'isRTL'             => $wp_locale->is_rtl(),
				'currencySymbol'    => $woocommerce_currency_symbol,
				'ajaxurl'           => admin_url( 'admin-ajax.php' ),
				'locale'            => get_locale(),
			);

			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-dialog' );

			wp_enqueue_script( 'events-booking-script-product-moment-locales', $this->config->scripts_path . 'moment-with-locales.js', array(), $this->config->plugin_data['Version'], true );
			wp_enqueue_script( 'events-booking-script-product', $this->config->scripts_path . 'events-booking-admin-product.js', array( 'jquery', 'jquery-ui-datepicker', 'wp-color-picker', 'jquery-ui-sortable', 'jquery-ui-dialog', 'events-booking-script-product-moment-locales' ), $this->config->plugin_data['Version'], true );
			wp_localize_script( 'events-booking-script-product', 'FooEventsBookingsProdObj', $fooevents_bookings_prod_obj );

			wp_enqueue_script( 'events-booking-script-ticket', $this->config->scripts_path . 'events-booking-admin-edit-ticket.js', array( 'jquery' ), $this->config->plugin_data['Version'], true );
			wp_localize_script( 'events-booking-script-ticket', 'FooEventsBookingsTicketObj', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		}

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['post_type'] ) && 'event_magic_tickets' === $_GET['post_type'] ) {

			$add_ticket_args = array(
				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
				'adminURL'      => get_admin_url(),
				'eventOverview' => __( 'Event Overview', 'fooevents-bookings' ),
				'selectEvent'   => __( 'Select an event in the <strong>Event Details</strong> section.', 'fooevents-bookings' ),
			);

			wp_enqueue_script( 'events-booking-admin-add-ticket', $this->config->scripts_path . 'events-booking-admin-add-ticket.js', array( 'jquery' ), $this->config->plugin_data['Version'], true );
			wp_localize_script( 'events-booking-admin-add-ticket', 'FooEventsBookingsAddTicketObj', $add_ticket_args );

		}

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) && 'fooevents-bookings-admin' === $_GET['page'] ) {

			$fooevents_bookings_admin_obj = array(
				'closeText'         => __( 'Done', 'fooevents-bookings' ),
				'currentText'       => __( 'Today', 'fooevents-bookings' ),
				'time'              => __( 'Time', 'fooevents-bookings' ),
				'addDate'           => __( 'Add Date', 'fooevents-bookings' ),
				'unlimitedStock'    => __( 'Unlimited stock', 'fooevents-bookings' ),
				'webinars'          => __( 'Webinars', 'fooevents-bookings' ),
				'meetings'          => __( 'Meetings', 'fooevents-bookings' ),
				'savingChanges'     => __( 'Saving Changes', 'fooevents-bookings' ),
				'saveChanges'       => __( 'Save Changes', 'fooevents-bookings' ),
				'optionsSaved'      => __( 'Your booking options have been saved.', 'fooevents-bookings' ),
				'deleteZoom'        => __( 'Also delete Zoom meeting/webinar?', 'fooevents-bookings' ),
				'zoomDeleteSuccess' => __( 'Successfully deleted Zoom meeting/webinar.', 'fooevents-bookings' ),
				'zoomDeleteError'   => __( 'Unable to delete the Zoom meeting/webinar.', 'fooevents-bookings' ),
				'monthNames'        => $this->strip_array_indices( $wp_locale->month ),
				'monthNamesShort'   => $this->strip_array_indices( $wp_locale->month_abbrev ),
				'monthStatus'       => __( 'Show a different month', 'fooevents-bookings' ),
				'dayNames'          => $this->strip_array_indices( $wp_locale->weekday ),
				'dayNamesShort'     => $this->strip_array_indices( $wp_locale->weekday_abbrev ),
				'dayNamesMin'       => $this->strip_array_indices( $wp_locale->weekday_initial ),
				'dateFormat'        => $this->date_format_php_to_js( get_option( 'date_format' ) ),
				'firstDay'          => get_option( 'start_of_week' ),
				'isRTL'             => $wp_locale->is_rtl(),
				'currencySymbol'    => $woocommerce_currency_symbol,
				'ajaxurl'           => admin_url( 'admin-ajax.php' ),
			);

			wp_enqueue_script( 'jquery-ui-datepicker' );

			wp_enqueue_script( 'events-booking-admin-admin', $this->config->scripts_path . 'events-booking-admin-admin.js', array( 'jquery', 'jquery-ui-datepicker' ), '1.0.0', true );
			wp_localize_script( 'events-booking-admin-admin', 'FooEventsBookingsAdminObj', $fooevents_bookings_admin_obj );

		}
	}

	/**
	 * Register plugin frontend scripts.
	 */
	public function register_frontend_scripts() {

		$booking_frontend_args = array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'loading' => __( 'Loading...', 'fooevents-bookings' ),
		);

		wp_enqueue_script( 'events-booking-script-front', $this->config->scripts_path . 'events-booking-frontend.js', array( 'jquery' ), $this->config->plugin_data['Version'], true );
		wp_localize_script( 'events-booking-script-front', 'FooEventsBookingsFrontObj', $booking_frontend_args );
		wp_enqueue_style( 'fooevents-bookings-style', $this->config->styles_path . 'fooevents-bookings-frontend.css', array(), $this->config->plugin_data['Version'] );
	}

	/**
	 * Register plugin styles.
	 */
	public function register_styles() {

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) && 'fooevents-bookings-admin' === $_GET['page'] ) {

			wp_enqueue_style( 'woocommerce-events-admin-jquery', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css', array(), '1.0.0' );

		}

		wp_enqueue_style( 'fooevents-bookings-style', $this->config->styles_path . 'fooevents-bookings.css', array(), $this->config->plugin_data['Version'] );
		wp_enqueue_style( 'fooevents-bookings-timepicker-style', $this->config->styles_path . 'jquery-ui-timepicker-addon.css', array(), $this->config->plugin_data['Version'] );
	}

	/**
	 * Loads text-domain for localization
	 */
	public function load_text_domain() {

		$path   = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$loaded = load_plugin_textdomain( 'fooevents-bookings', false, $path );
	}

	public function plugin_notice_dismissed() {

		$user_id = get_current_user_id();

		if ( isset( $_GET['fooevents_notice'] ) ) {

			$check = sanitize_text_field( $_GET['fooevents_notice'] );
			add_user_meta( $user_id, $check, 'true', true );
		}
	}

	/**
	 * Initializes the WooCommerce meta box
	 */
	public function add_product_bookings_options_tab() {

		echo '<li class="custom_tab_booking_options type_bookings" id="custom_tab_booking_options"><a href="#fooevents_bookings_options">' . esc_attr__( 'Booking Settings', 'fooevents-bookings' ) . '</a></li>';
	}

	public function check_for_no_slots( $stock, $product ) {

		$product_id = $product->get_id();

		$event_type = get_post_meta( $product_id, 'WooCommerceEventsType', true );

		if ( 'bookings' === $event_type ) {

			$bookings = get_post_meta( $product_id, 'fooevents_bookings_options_serialized', true );

			if ( '[]' === $bookings || empty( $bookings ) ) {

				return false;

			}
		}

		return $stock;
	}

	/**
	 * Adds booking options to the WooCommerce options tab
	 */
	public function add_product_bookings_options_tab_options() {

		global $post;

		$post_status = get_post_status( $post );

		$fooevents_bookings_options_serialized = get_post_meta( $post->ID, 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options            = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options );

		$bookings_slot_term = get_post_meta( $post->ID, 'WooCommerceEventsBookingsSlotOverride', true );

		$slot_label = '';
		if ( empty( $bookings_slot_term ) ) {

			$slot_label = __( 'Slot', 'fooevents-bookings' );

		} else {

			$slot_label = $bookings_slot_term;

		}

		if ( empty( $fooevents_bookings_options ) ) {

			$fooevents_bookings_options = array();

		}

		require $this->config->template_path . 'booking-options.php';
		require $this->config->template_path . 'booking-options-add-dates.php';
	}

	/**
	 * Processes the meta box form once the publish / update button is clicked.
	 *
	 * @global object $woocommerce_errors
	 * @param int $post_id the post ID.
	 */
	public function process_meta_box( $post_id ) {

		global $woocommerce_errors;

		$nonce = '';
		if ( isset( $_POST['fooevents_bookings_options_nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_options_nonce'] ) );
		}

		/*
		if ( ! wp_verify_nonce( $nonce, 'fooevents_bookings_options' ) ) {
			die( esc_attr__( 'Security check failed - FooEvents Bookings 0001', 'fooevents-bookings' ) );
		}*/

		if ( isset( $_POST['fooevents_bookings_options_serialized'] ) ) {

			$fooevents_bookings_options_serialized = sanitize_text_field( $_POST['fooevents_bookings_options_serialized'] );

			$this->update_serialized_booking_options( $post_id, $fooevents_bookings_options_serialized );

		}
	}

	/**
	 * Ajax function to save booking options
	 */
	public function save_booking_options() {

		$nonce = '';
		if ( isset( $_POST['nonce_val'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['nonce_val'] ) );
		}

		if ( ! wp_verify_nonce( $nonce, 'fooevents_bookings_options' ) ) {
			die( esc_attr__( 'Security check failed - FooEvents Bookings 0002', 'fooevents-bookings' ) );
		}

		$booking_options = '';
		if ( isset( $_POST['options'] ) ) {

			$booking_options = sanitize_text_field( wp_unslash( $_POST['options'] ) );

		}

		$post_id = '';
		if ( isset( $_POST['post_id'] ) ) {

			$post_id = sanitize_text_field( wp_unslash( $_POST['post_id'] ) );

		}

		// Breaks output so ignore.
		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->update_serialized_booking_options( $post_id, $booking_options );

		exit();
	}

	/**
	 * Update serialized booking options and create/update Zoom meetings if necessary
	 *
	 * @param int    $post_id the post ID.
	 * @param string $booking_options booking options.
	 */
	private function update_serialized_booking_options( $post_id, $booking_options = '{}' ) {

		$fooevents_bookings_options        = stripslashes( $booking_options );
		$fooevents_bookings_options_raw    = json_decode( $fooevents_bookings_options, true );
		$return_fooevents_bookings_options = array();

		$previous_booking_options     = get_post_meta( $post_id, 'fooevents_bookings_options_serialized', true );
		$previous_booking_options_raw = json_decode( $previous_booking_options, true );

		if ( null === $previous_booking_options_raw ) {
			$previous_booking_options_raw = array();
		}

		$post_title = '';
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_POST['post_title'] ) ) {

			$post_title = sanitize_text_field( wp_unslash( $_POST['post_title'] ) );

		}
		$current_post_title = html_entity_decode( apply_filters( 'the_title', $post_title, $post_id ) );

		$woocommerce_events_zoom_topic = '';
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_POST['WooCommerceEventsZoomTopic'] ) ) {

			$woocommerce_events_zoom_topic = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsZoomTopic'] ) );

		}
		$previous_post_title = html_entity_decode( apply_filters( 'the_title', $woocommerce_events_zoom_topic, $post_id ) );

		$zoom_api_helper = null;

		// Check for Zoom updates.
		foreach ( $fooevents_bookings_options_raw as $bookings_slot_id => &$booking_slot_options ) {

			$return_fooevents_bookings_options[ $bookings_slot_id ] = $booking_slot_options;

			if ( isset( $booking_slot_options['zoom_id'] ) ) {

				if ( null === $zoom_api_helper ) {

					$zoom_api_helper = new FooEvents_Zoom_API_Helper( new FooEvents_Config() );

				}
			} else {

				continue;

			}

			$current_date_id = '';
			$date_id         = '';
			$options_array   = array();

			if ( ! isset( $previous_booking_options_raw[ $bookings_slot_id ] ) ) {
				$previous_booking_options_raw[ $bookings_slot_id ] = array();
			}

			$booking_slot_options_diff = array_diff_assoc( $booking_slot_options, $previous_booking_options_raw[ $bookings_slot_id ] );

			$diff_keys = array_keys( $booking_slot_options_diff );

			$zoom_id_changed = false;

			foreach ( $diff_keys as $diff_key ) {

				if ( strpos( $diff_key, '_zoom_id' ) > -1 ) {

					unset( $booking_slot_options_diff[ $diff_key ] );

					$zoom_id_changed = true;

				}
			}

			if ( empty( $previous_booking_options_raw[ $bookings_slot_id ] ) || $zoom_id_changed || $current_post_title !== $previous_post_title ) {

				$options_array = $booking_slot_options;

			} elseif ( ! empty( $booking_slot_options_diff ) ) {

				if ( isset( $booking_slot_options_diff['label'] ) || isset( $booking_slot_options_diff['hour'] ) || isset( $booking_slot_options_diff['minute'] ) || isset( $booking_slot_options_diff['period'] ) ) {

					// Value change, update all Zoom meetings.
					$options_array = $booking_slot_options;

				} else {

					// Only add new Zoom meetings.
					$options_array = $booking_slot_options_diff;

				}
			}

			foreach ( $options_array as $booking_slot_option_key => $booking_slot_option_value ) {

				if ( strpos( $booking_slot_option_key, '_add_date' ) !== false ) {

					$date_id = str_replace( '_add_date', '', $booking_slot_option_key );

				} elseif ( strpos( $booking_slot_option_key, '_zoom_id' ) !== false ) {

					$date_id = str_replace( '_zoom_id', '', $booking_slot_option_key );

				} elseif ( strpos( $booking_slot_option_key, '_stock' ) !== false ) {

					$date_id = str_replace( '_stock', '', $booking_slot_option_key );

				}

				if ( $date_id !== $current_date_id ) {

					$current_date_id = $date_id;

					$zoom_id = &$booking_slot_options[ $current_date_id . '_zoom_id' ];

					$date = $booking_slot_options[ $current_date_id . '_add_date' ];

					$format = get_option( 'date_format' );

					if ( 'd/m/Y' === $format ) {
						$date = str_replace( '/', '-', $date );
					}

					if ( isset( $booking_slot_options['add_time'] ) && isset( $booking_slot_options['hour'] ) && isset( $booking_slot_options['minute'] ) ) {

						$date .= ' ' . $booking_slot_options['hour'] . ':' . $booking_slot_options['minute'] . ( isset( $booking_slot_options['period'] ) ? $booking_slot_options['period'] : '' );

					}

					$date_timestamp = strtotime( $this->convert_month_to_english( $date ) );

					$stock = $booking_slot_options[ $current_date_id . '_stock' ];

					$endpoint = get_post_meta( $post_id, 'WooCommerceEventsZoomType', true );

					if ( '' === $endpoint ) {

						$endpoint = 'meetings';

					}

					$woocommerce_events_zoom_duration_hour   = (int) get_post_meta( $post_id, 'WooCommerceEventsZoomDurationHour', true );
					$woocommerce_events_zoom_duration_minute = (int) get_post_meta( $post_id, 'WooCommerceEventsZoomDurationMinute', true );

					if ( 0 === $woocommerce_events_zoom_duration_hour && 0 === $woocommerce_events_zoom_duration_minute ) {

						$woocommerce_events_zoom_duration_hour = 1;

					}

					$duration = ( $woocommerce_events_zoom_duration_hour * 60 ) + $woocommerce_events_zoom_duration_minute;

					$woocommerce_events_timezone = get_post_meta( $post_id, 'WooCommerceEventsTimeZone', true );

					if ( empty( $woocommerce_events_timezone ) ) {

						$woocommerce_events_timezone = get_option( 'timezone_string' );

					}

					$zoom_options = array(
						'topic'        => ( '' !== $current_post_title ? $current_post_title : html_entity_decode( get_the_title( $post_id ) ) ) . ' - ' . $booking_slot_options['label'] . ' - ' . $date,
						'timezone'     => $woocommerce_events_timezone,
						'start_time'   => date( 'Y-m-d\TH:i:s', $date_timestamp ),
						'type'         => $endpoint,
						'schedule_for' => get_post_meta( $post_id, 'WooCommerceEventsZoomHost', true ),
						'duration'     => $duration,
					);

					$result = array( 'status' => 'error' );

					if ( 'auto' === $zoom_id ) {

						// Create new Zoom meeting.
						$result = $zoom_api_helper->create_zoom_meeting( $zoom_options );

					} elseif ( '' !== $zoom_id ) {

						// Update existing Zoom meeting.
						unset( $zoom_options['type'] );

						$result = $zoom_api_helper->update_zoom_meeting( $zoom_id, $zoom_options );

					}

					if ( 'success' === $result['status'] ) {

						$zoom_id = $result['data']['id'];

						$return_fooevents_bookings_options[ $bookings_slot_id ][ $current_date_id . '_zoom_id' ]    = $result['data']['id'];
						$return_fooevents_bookings_options[ $bookings_slot_id ][ $current_date_id . '_zoom_topic' ] = $result['data']['topic'];

						$return_fooevents_bookings_options['update_zoom'] = true;

					}
				}
			}
		}

		$fooevents_bookings_options = wp_slash( wp_json_encode( $fooevents_bookings_options_raw, JSON_UNESCAPED_UNICODE ) );

		update_post_meta( $post_id, 'fooevents_bookings_options_serialized', $fooevents_bookings_options );

		return wp_json_encode( $return_fooevents_bookings_options );
	}

	/**
	 * Output booking fields on checkout.
	 *
	 * @param int    $product_id product ID.
	 * @param int    $x event counter.
	 * @param int    $y ticket counter.
	 * @param array  $ticket ticket array.
	 * @param object $checkout checkout.
	 * @param string $woocommerce_events_bookings_expire_passed_date expire passed date.
	 * @param string $woocommerce_events_hide_bookings_display_time hide bookings display time.
	 * @param string $woocommerce_events_view_bookings_stock_dropdowns view bookings stock.
	 * @param string $woocommerce_events_view_out_of_stock_bookings view out of stock bookings.
	 * @param string $wordpress_timezone WordPress timezone.
	 * @param string $format date format.
	 * @param string $woocommerce_events_time_zone event timezone.
	 * @param array  $fooevents_bookings_options booking options.
	 * @param bool   $each_slot_one_date each slot one date.
	 * @param bool   $one_slot_multiple_dates one slot multiple dates.
	 * @param string $bookings_date_term date term.
	 * @param string $bookings_slot_term slot term.
	 * @param string $date_label date label.
	 * @param string $slot_label slot label.
	 * @param string $booking_details_label bookings detail label.
	 * @param string $woocommerce_events_bookings_method bookings method.
	 */
	public function output_booking_fields_slot_date( $product_id, $x, $y, $ticket, $checkout, $woocommerce_events_bookings_expire_passed_date, $woocommerce_events_bookings_expire_value, $woocommerce_events_bookings_expire_unit, $woocommerce_events_hide_bookings_display_time, $woocommerce_events_view_bookings_stock_dropdowns, $woocommerce_events_view_out_of_stock_bookings, $wordpress_timezone, $format, $woocommerce_events_time_zone, $fooevents_bookings_options, $each_slot_one_date, $one_slot_multiple_dates, $bookings_date_term, $bookings_slot_term, $date_label, $slot_label, $booking_details_label, $woocommerce_events_bookings_method ) {

		global $woocommerce;

		$today = current_time( 'timestamp' );

		if ( ! $each_slot_one_date && ! $one_slot_multiple_dates ) {

			$bookings_date_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsDateOverride', true );
			$bookings_slot_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsSlotOverride', true );

			if ( empty( $bookings_slot_term ) ) {

				$slot_select_label = __( 'Slot', 'fooevents-bookings' );

			} else {

				$slot_select_label = $bookings_slot_term;

			}

			if ( empty( $bookings_date_term ) ) {

				$date_select_label = __( 'Date', 'fooevents-bookings' );

			} else {

				$date_select_label = $bookings_date_term;

			}

			// translators: Placeholder is for the date label.
			$date_select_placeholder = sprintf( __( 'Select %s', 'fooevents-bookings' ), $date_select_label );

			if ( isset( $ticket['booking_selection_date'] ) && isset( $ticket['fooevents_bookings_method'] ) && $ticket['fooevents_bookings_method'] === $woocommerce_events_bookings_method ) {

				$date_select_label = $ticket['booking_selection_date'];

			}

			// translators: Placeholder is for the date label.
			$bookings_date_term = sprintf( __( 'Select %s', 'fooevents-bookings' ), $date_select_label );
			$select_values_date = array( $date_select_label => $bookings_date_term );

			if ( isset( $ticket['booking_selection_slot'] ) && isset( $ticket['fooevents_bookings_method'] ) && $ticket['fooevents_bookings_method'] === $woocommerce_events_bookings_method ) {

				$slot_select_label = $ticket['booking_selection_slot'];
				// If item is already added to cart.
				$booking_slot = explode( '_', $ticket['booking_selection_slot'] );
				$booking_slot = $booking_slot[0];

				foreach ( $fooevents_bookings_options[ $booking_slot ]['add_date'] as $row => $add_date ) {

					$option = $fooevents_bookings_options[ $booking_slot ];
					$stock  = '';

					if ( empty( $add_date['stock'] ) || $add_date['stock'] < 0 ) {

						$stock = __( 'Unlimited', 'fooevents-bookings' );

					} else {

						$stock = $add_date['stock'];

					}

					if ( 'yes' === $woocommerce_events_bookings_expire_passed_date ) {

						if ( ! empty( $add_date['date'] ) ) {

							$date_to_compare = '';
							if ( isset( $option['hour'] ) && ! empty( $option['hour'] ) && isset( $option['minute'] ) && ! empty( $option['minute'] ) && isset( $option['period'] ) && ! empty( $option['period'] ) ) {

								$date_to_compare = $add_date['date'] . ' ' . $option['hour'] . ':' . $option['minute'] . $option['period'];

							} elseif ( isset( $option['hour'] ) && ! empty( $option['hour'] ) && isset( $option['minute'] ) && ! empty( $option['minute'] ) ) {

								$date_to_compare = $add_date['date'] . ' ' . $option['hour'] . ':' . $option['minute'];

							} else {

								$date_to_compare = $add_date['date'];

							}

							$expire_date = sanitize_text_field( $date_to_compare );

							if ( ! empty( $woocommerce_events_timezone ) && $wordpress_timezone !== $woocommerce_events_timezone ) {

								$date_time = new DateTime( '@' . $today );
								$timezone  = new DateTimeZone( $woocommerce_events_timezone );
								$date_time->setTimezone( $timezone );
								$today = $date_time->format( 'U' );

							}

							if ( 'd/m/Y' === $format ) {

								$expire_date = str_replace( '/', '-', $expire_date );

							}

							$expire_date = str_replace( ',', '', $expire_date );
							$expire_date = $this->convert_month_to_english( $expire_date );

							$timestamp = strtotime( $expire_date );

							if ( ! empty( $woocommerce_events_bookings_expire_value ) && ! empty( $woocommerce_events_bookings_expire_unit ) && ! empty( $timestamp ) ) {

								$timestamp = strtotime( '-' . $woocommerce_events_bookings_expire_value . ' ' . $woocommerce_events_bookings_expire_unit, $timestamp );

							}

							if ( $today > $timestamp ) {

								// Remove expired dates from selectable dates.
								unset( $select_values_date[ $row ] );

								continue;

							}
						}
					}

					if ( strval( $add_date['stock'] ) !== '0' ) {

						$val = $add_date['date'];
						if ( 'on' === $woocommerce_events_view_bookings_stock_dropdowns ) {

							$val .= ' (' . $stock . ')';

						}

						$select_values_date[ $row ] = $val;

					}
				}
			}

			// translators: Placeholder is for the date label.
			$select_values_slot = array( $slot_select_label => sprintf( __( 'Select %s', 'fooevents-bookings' ), $slot_select_label ) );

			$z = 1;

			foreach ( $fooevents_bookings_options as $option_key => $option ) {

				if ( isset( $option['add_date'] ) ) {

					if ( 'yes' === $woocommerce_events_bookings_expire_passed_date ) {

						$upcoming_dates = 0;
						foreach ( $option['add_date'] as $k => $date ) {

							if ( ! empty( $date['date'] ) ) {

								$date_to_compare = '';
								if ( isset( $option['hour'] ) && ! empty( $option['hour'] ) && isset( $option['minute'] ) && ! empty( $option['minute'] ) && isset( $option['period'] ) && ! empty( $option['period'] ) ) {

									$date_to_compare = $date['date'] . ' ' . $option['hour'] . ':' . $option['minute'] . $option['period'];

								} elseif ( isset( $option['hour'] ) && ! empty( $option['hour'] ) && isset( $option['minute'] ) && ! empty( $option['minute'] ) ) {

									$date_to_compare = $date['date'] . ' ' . $option['hour'] . ':' . $option['minute'];

								} else {

									$date_to_compare = $date['date'];

								}

								$expire_date = sanitize_text_field( $date_to_compare );

								if ( ! empty( $woocommerce_events_timezone ) && $wordpress_timezone !== $woocommerce_events_timezone ) {

									$date_time = new DateTime( '@' . $today );
									$timezone  = new DateTimeZone( $woocommerce_events_timezone );
									$date_time->setTimezone( $timezone );
									$today = $date_time->format( 'U' );

								}

								if ( 'd/m/Y' === $format ) {

									$expire_date = str_replace( '/', '-', $expire_date );

								}

								$expire_date = str_replace( ',', '', $expire_date );
								$expire_date = $this->convert_month_to_english( $expire_date );

								$timestamp = strtotime( $expire_date );

								if ( ! empty( $woocommerce_events_bookings_expire_value ) && ! empty( $woocommerce_events_bookings_expire_unit ) && ! empty( $timestamp ) ) {

									$timestamp = strtotime( '-' . $woocommerce_events_bookings_expire_value . ' ' . $woocommerce_events_bookings_expire_unit, $timestamp );

								}

								if ( $today < $timestamp ) {

									++$upcoming_dates;

								}
							}
						}

						// All dates have expired.
						if ( 0 === $upcoming_dates ) {

							continue;

						}
					}

					$val = '';
					if ( isset( $option['add_time'] ) && 'enabled' === $option['add_time'] && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

						$val = $option['label'] . ' ' . $option['formatted_time'];

					} elseif ( isset( $option['label'] ) ) {

						$val = $option['label'];

					}

					$select_values_slot[ $option_key . '_' . $product_id ] = $val;

				}

				$slot_field_params = array(
					'type'              => 'select',
					'class'             => array( 'attendee-class form-row-wide fooevents-bookings-slot' ),
					'label'             => $booking_details_label,
					'placeholder'       => '',
					'options'           => $select_values_slot,
					'default'           => array_key_first( $select_values_slot ),
					'custom_attributes' => array( 'data-value' => array_key_first( $select_values_slot ) ),
					'required'          => true,
				);

				$date_field_params = array(
					'type'              => 'select',
					'class'             => array( 'attendee-class form-row-wide fooevents-bookings-date' ),
					'label'             => '',
					'placeholder'       => $date_select_placeholder,
					'options'           => $select_values_date,
					'default'           => array_key_first( $select_values_date ),
					'custom_attributes' => array( 'data-value' => array_key_first( $select_values_date ) ),
					'required'          => true,
				);

				++$z;

			}

			if ( ! empty( $slot_field_params ) ) {

				woocommerce_form_field( 'fooevents_bookings_slot_' . $x . '__' . $y, $slot_field_params, $checkout->get_value( 'fooevents_bookings_slot_' . $x . '__' . $y ) );
				woocommerce_form_field( 'fooevents_bookings_date_' . $x . '__' . $y, $date_field_params, $checkout->get_value( 'fooevents_bookings_date_' . $x . '__' . $y ) );

			}
		} elseif ( $each_slot_one_date && ! $one_slot_multiple_dates ) {

			$slot_date_select_label = '';

			if ( isset( $ticket['booking_selection_slot_date'] ) ) {

				$slot_date_select_label = $ticket['booking_selection_slot_date'];

			}

			// translators: Placeholder is for the slot label.
			$select_values_slot_date = array( $slot_date_select_label => sprintf( __( 'Select %s', 'fooevents-bookings' ), $slot_label ) );

			foreach ( $fooevents_bookings_options as $option_key => $option ) {

				if ( isset( $option['add_date'] ) ) {

					$display_slot = true;
					if ( 'yes' === $woocommerce_events_bookings_expire_passed_date ) {

						foreach ( $option['add_date'] as $k => $date ) {

							if ( ! empty( $date['date'] ) ) {

								$date_to_compare = '';
								if ( isset( $option['hour'] ) && ! empty( $option['hour'] ) && isset( $option['minute'] ) && ! empty( $option['minute'] ) && isset( $option['period'] ) && ! empty( $option['period'] ) ) {

									$date_to_compare = $date['date'] . ' ' . $option['hour'] . ':' . $option['minute'] . $option['period'];

								} elseif ( isset( $option['hour'] ) && ! empty( $option['hour'] ) && isset( $option['minute'] ) && ! empty( $option['minute'] ) ) {

									$date_to_compare = $date['date'] . ' ' . $option['hour'] . ':' . $option['minute'];

								} else {

									$date_to_compare = $date['date'];

								}

								$expire_date = sanitize_text_field( $date_to_compare );

								if ( ! empty( $woocommerce_events_timezone ) && $wordpress_timezone !== $woocommerce_events_timezone ) {

									$date_time = new DateTime( '@' . $today );
									$timezone  = new DateTimeZone( $woocommerce_events_timezone );
									$date_time->setTimezone( $timezone );
									$today = $date_time->format( 'U' );

								}

								if ( 'd/m/Y' === $format ) {

									$expire_date = str_replace( '/', '-', $expire_date );

								}

								$expire_date = str_replace( ',', '', $expire_date );
								$expire_date = $this->convert_month_to_english( $expire_date );

								$timestamp = strtotime( $expire_date );

								if ( ! empty( $woocommerce_events_bookings_expire_value ) && ! empty( $woocommerce_events_bookings_expire_unit ) && ! empty( $timestamp ) ) {

									$timestamp = strtotime( '-' . $woocommerce_events_bookings_expire_value . ' ' . $woocommerce_events_bookings_expire_unit, $timestamp );

								}

								if ( $today > $timestamp ) {

									$display_slot = false;

								}
							}
						}

						// Date has expired.
						if ( ! $display_slot ) {

							continue;

						}
					}

					$val = $option['label'];
					$select_values_slot[ $option_key . '_' . $product_id ] = $val;

					foreach ( $option['add_date'] as $date_key => $date ) {

						$stock = $date['stock'];

						if ( empty( $date['stock'] ) || $date['stock'] < 0 ) {

							$stock = __( 'Unlimited', 'fooevents-bookings' );

						}

						if ( strval( $date['stock'] ) === '0' ) {

							$stock = 0;

						}

						$val = '';
						if ( isset( $option['add_time'] ) && 'enabled' === $option['add_time'] && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

							$val = $option['label'] . ' ' . $option['formatted_time'];

							if ( ! empty( $date['date'] ) ) {

								if ( ! empty( $val ) ) {

									$val .= ' - ';

								}

								$val .= $date['date'];

							}
						} else {

							$val = $option['label'];

							if ( ! empty( $date['date'] ) ) {

								if ( ! empty( $val ) ) {

									$val .= ' - ';

								}

								$val .= $date['date'];

							}
						}

						if ( 'on' === $woocommerce_events_view_bookings_stock_dropdowns ) {

							$val .= ' (' . $stock . ')';

						}

						if ( ( 'on' === $woocommerce_events_view_out_of_stock_bookings && strval( $date['stock'] ) === '0' ) || strval( $date['stock'] ) !== '0' ) {

							$select_values_slot_date[ $option_key . '_' . $date_key . '_' . $product_id ] = $val;

						}
					}
				}
			}

			$slot_date_field_params = array(
				'type'        => 'select',
				'class'       => array( 'attendee-class form-row-wide fooevents-bookings-slot-date' ),
				'label'       => $booking_details_label,
				'placeholder' => '',
				'options'     => $select_values_slot_date,
				'required'    => true,
			);

			woocommerce_form_field( 'fooevents_bookings_slot_date_' . $x . '__' . $y, $slot_date_field_params, $checkout->get_value( 'fooevents_bookings_slot_date_' . $x . '__' . $y ) );

		} elseif ( ! $each_slot_one_date && $one_slot_multiple_dates ) {

			// translators: Placeholder is for the date label.
			$select_values_date = array( '' => sprintf( __( 'Select %s', 'fooevents-bookings' ), $date_label ) );

			$only_slot = key( $fooevents_bookings_options );
			foreach ( $fooevents_bookings_options[ $only_slot ]['add_date'] as $option_key => $option ) {

				if ( isset( $option['date'] ) ) {

					$display_slot = true;
					if ( 'yes' === $woocommerce_events_bookings_expire_passed_date ) {

						$date_to_compare = '';
						if ( isset( $fooevents_bookings_options[ $only_slot ]['hour'] ) && ! empty( $fooevents_bookings_options[ $only_slot ]['hour'] ) && isset( $fooevents_bookings_options[ $only_slot ]['minute'] ) && ! empty( $fooevents_bookings_options[ $only_slot ]['minute'] ) && isset( $fooevents_bookings_options[ $only_slot ]['period'] ) && ! empty( $fooevents_bookings_options[ $only_slot ]['period'] ) ) {

							$date_to_compare = $option['date'] . ' ' . $fooevents_bookings_options[ $only_slot ]['hour'] . ':' . $fooevents_bookings_options[ $only_slot ]['minute'] . $fooevents_bookings_options[ $only_slot ]['period'];

						} elseif ( isset( $fooevents_bookings_options[ $only_slot ]['hour'] ) && ! empty( $fooevents_bookings_options[ $only_slot ]['hour'] ) && isset( $fooevents_bookings_options[ $only_slot ]['minute'] ) && ! empty( $fooevents_bookings_options[ $only_slot ]['minute'] ) ) {

							$date_to_compare = $option['date'] . ' ' . $fooevents_bookings_options[ $only_slot ]['hour'] . ':' . $fooevents_bookings_options[ $only_slot ]['minute'];

						} else {

							$date_to_compare = $option['date'];

						}

						$expire_date = sanitize_text_field( $date_to_compare );

						if ( ! empty( $woocommerce_events_timezone ) && $wordpress_timezone !== $woocommerce_events_timezone ) {

							$date_time = new DateTime( '@' . $today );
							$timezone  = new DateTimeZone( $woocommerce_events_timezone );
							$date_time->setTimezone( $timezone );
							$today = $date_time->format( 'U' );

						}

						if ( 'd/m/Y' === $format ) {

							$expire_date = str_replace( '/', '-', $expire_date );

						}

						$expire_date = str_replace( ',', '', $expire_date );
						$expire_date = $this->convert_month_to_english( $expire_date );

						$timestamp = strtotime( $expire_date );

						if ( ! empty( $woocommerce_events_bookings_expire_value ) && ! empty( $woocommerce_events_bookings_expire_unit ) && ! empty( $timestamp ) ) {

							$timestamp = strtotime( '-' . $woocommerce_events_bookings_expire_value . ' ' . $woocommerce_events_bookings_expire_unit, $timestamp );

						}

						if ( $today > $timestamp ) {

							$display_slot = false;

						}

						// Date has expired.
						if ( ! $display_slot ) {

							continue;

						}
					}

					$stock = $option['stock'];

					if ( empty( $option['stock'] ) || $option['stock'] < 0 ) {

						$stock = __( 'Unlimited', 'fooevents-bookings' );

					}

					if ( strval( $option['stock'] ) === '0' ) {

						$stock = 0;

					}

					if ( ( 'on' === $woocommerce_events_view_out_of_stock_bookings && strval( $option['stock'] ) === '0' ) || strval( $option['stock'] ) !== '0' ) {

						$val = $option['date'];

						if ( isset( $fooevents_bookings_options[ $only_slot ]['add_time'] ) && 'enabled' === $fooevents_bookings_options[ $only_slot ]['add_time'] && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

							$val .= ' ' . $fooevents_bookings_options[ $only_slot ]['formatted_time'];

						}

						if ( 'on' === $woocommerce_events_view_bookings_stock_dropdowns ) {

							$val .= ' (' . $stock . ')';

						}

						$select_values_date[ $option_key ] = $val;

					}
				}
			}

			$date_field_params = array(
				'type'        => 'select',
				'class'       => array( 'attendee-class form-row-wide fooevents-bookings-date' ),
				'label'       => $booking_details_label,
				'placeholder' => '',
				'options'     => $select_values_date,
				'required'    => true,
			);

			$selected_slot = '';
			if ( isset( $ticket['booking_selection_slot'] ) ) {

				$selected_slot = $ticket['booking_selection_slot'];

			} else {

				$selected_slot = $only_slot . '_' . $product_id;

			}

			$selected_date = '';
			if ( isset( $ticket['booking_selection_date'] ) ) {

				$selected_date = $ticket['booking_selection_date'];

			} else {

				$selected_date = $checkout->get_value( 'fooevents_bookings_date_' . $x . '__' . $y );

			}

			echo '<input type="hidden" id="fooevents_bookings_slot_' . esc_attr( $x ) . '__' . esc_attr( $y ) . '" name="fooevents_bookings_slot_' . esc_attr( $x ) . '__' . esc_attr( $y ) . '" value="' . esc_attr( $selected_slot ) . '">';
			woocommerce_form_field( 'fooevents_bookings_date_' . $x . '__' . $y, $date_field_params, $selected_date );

		}
	}

	/**
	 * Output booking fields on checkout. Date/Slot format.
	 *
	 * @param int    $product_id product ID.
	 * @param int    $x event counter.
	 * @param int    $y ticket counter.
	 * @param array  $ticket ticket array.
	 * @param object $checkout checkout.
	 * @param string $woocommerce_events_bookings_expire_passed_date expire passed date.
	 * @param string $woocommerce_events_hide_bookings_display_time hide bookings display time.
	 * @param string $woocommerce_events_view_bookings_stock_dropdowns view bookings stock.
	 * @param string $woocommerce_events_view_out_of_stock_bookings view out of stock bookings.
	 * @param string $wordpress_timezone WordPress timezone.
	 * @param string $format date format.
	 * @param string $woocommerce_events_time_zone event timezone.
	 * @param array  $fooevents_bookings_options booking options.
	 * @param bool   $each_slot_one_date each slot one date.
	 * @param bool   $one_slot_multiple_dates one slot multiple dates.
	 * @param string $bookings_date_term date term.
	 * @param string $bookings_slot_term slot term.
	 * @param string $date_label date label.
	 * @param string $slot_label slot label.
	 * @param string $booking_details_label bookings detail label.
	 * @param string $woocommerce_events_bookings_method bookings method.
	 */
	public function output_booking_fields_date_slot( $product_id, $x, $y, $ticket, $checkout, $woocommerce_events_bookings_expire_passed_date, $woocommerce_events_bookings_expire_value, $woocommerce_events_bookings_expire_unit, $woocommerce_events_hide_bookings_display_time, $woocommerce_events_view_bookings_stock_dropdowns, $woocommerce_events_view_out_of_stock_bookings, $wordpress_timezone, $format, $woocommerce_events_time_zone, $fooevents_bookings_options, $each_slot_one_date, $one_slot_multiple_dates, $bookings_date_term, $bookings_slot_term, $date_label, $slot_label, $booking_details_label, $woocommerce_events_bookings_method ) {

		global $woocommerce;

		$fooevents_bookings_options_date_slot = $this->process_date_slot_bookings_options( $fooevents_bookings_options );

		$bookings_date_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsDateOverride', true );
		$bookings_slot_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsSlotOverride', true );

		$today = current_time( 'timestamp' );

		if ( empty( $bookings_slot_term ) ) {

			$slot_select_label = __( 'Slot', 'fooevents-bookings' );

		} else {

			$slot_select_label = $bookings_slot_term;

		}

		if ( empty( $bookings_date_term ) ) {

			$date_select_label = __( 'Date', 'fooevents-bookings' );

		} else {

			$date_select_label = $bookings_date_term;

		}

		// translators: Placeholder is for the date label.
		$date_select_placeholder = sprintf( __( 'Select %s', 'fooevents-bookings' ), $date_select_label );
		// translators: Placeholder is for the slot label.
		$slot_select_placeholder = sprintf( __( 'Select %s', 'fooevents-bookings' ), $slot_select_label );

		if ( isset( $ticket['booking_selection_date'] ) && isset( $ticket['fooevents_bookings_method'] ) && $ticket['fooevents_bookings_method'] === $woocommerce_events_bookings_method ) {

			$date_select_label = $ticket['booking_selection_date'];

		}

		// translators: Placeholder is for the date label.
		$bookings_date_term = sprintf( __( 'Select %s', 'fooevents-bookings' ), $date_select_label );

		$select_values_date = array();

		if ( isset( $ticket['booking_selection_date'] ) && isset( $ticket['fooevents_bookings_method'] ) && $ticket['fooevents_bookings_method'] === $woocommerce_events_bookings_method ) {

			$date_trans         = explode( '_', $ticket['booking_selection_date'] );
			$select_values_date = array( $ticket['booking_selection_date'] => $date_trans[0] );

		} else {

			$select_values_date = array( '' => $bookings_date_term );

		}

		// translators: Placeholder is for the slot label.
		$select_values_slot = array( '' => sprintf( __( 'Select %s', 'fooevents-bookings' ), $slot_select_label ) );

		if ( isset( $ticket['booking_selection_slot'] ) && isset( $ticket['fooevents_bookings_method'] ) && $ticket['fooevents_bookings_method'] === $woocommerce_events_bookings_method ) {

			$select_values_slot = array( $ticket['booking_selection_slot'] => $ticket['booking_selection_slot'] );

		}

		$z = 1;

		foreach ( $fooevents_bookings_options_date_slot as $date => $option ) {

			$date_has_expired = false;
			foreach ( $option as $k => $ds ) {

				if ( 'yes' === $woocommerce_events_bookings_expire_passed_date ) {

					$date_to_compare = '';
					if ( isset( $ds['hour'] ) && ! empty( $ds['hour'] ) && isset( $ds['minute'] ) && ! empty( $ds['minute'] ) && isset( $ds['period'] ) && ! empty( $ds['period'] ) ) {

						$date_to_compare = $date . ' ' . $ds['hour'] . ':' . $ds['minute'] . $ds['period'];

					} elseif ( isset( $ds['hour'] ) && ! empty( $ds['hour'] ) && isset( $ds['minute'] ) && ! empty( $ds['minute'] ) ) {

						$date_to_compare = $date . ' ' . $ds['hour'] . ':' . $ds['minute'];

					} else {

						$date_to_compare = $date;

					}

					$date_has_expired = false;
					$expire_date      = sanitize_text_field( $date_to_compare );

					if ( ! empty( $woocommerce_events_timezone ) && $wordpress_timezone !== $woocommerce_events_timezone ) {

						$date_time = new DateTime( '@' . $today );
						$timezone  = new DateTimeZone( $woocommerce_events_timezone );
						$date_time->setTimezone( $timezone );
						$today = $date_time->format( 'U' );

					}

					if ( 'd/m/Y' === $format ) {

						$expire_date = str_replace( '/', '-', $expire_date );

					}

					$expire_date = str_replace( ',', '', $expire_date );
					$expire_date = $this->convert_month_to_english( $expire_date );

					$timestamp = strtotime( $expire_date );

					if ( ! empty( $woocommerce_events_bookings_expire_value ) && ! empty( $woocommerce_events_bookings_expire_unit ) && ! empty( $timestamp ) ) {

						$timestamp = strtotime( '-' . $woocommerce_events_bookings_expire_value . ' ' . $woocommerce_events_bookings_expire_unit, $timestamp );

					}

					if ( $today > $timestamp ) {

						$date_has_expired = true;

					}

					if ( true === $date_has_expired ) {

						continue;

					}
				}

				if ( $ds['stock'] > 0 || '' === $ds['stock'] ) {

					$select_values_date[ $date . '_' . $product_id ] = $date;

				} elseif ( 'on' === $woocommerce_events_view_out_of_stock_bookings && 0 === (int) $ds['stock'] ) {

					$select_values_date[ $date . '_' . $product_id ] = $date;

				}
			}
		}

		$date_field_params = array(
			'type'        => 'select',
			'class'       => array( 'attendee-class form-row-wide fooevents-bookings-date-slot-date' ),
			'label'       => $booking_details_label,
			'placeholder' => $date_select_placeholder,
			'options'     => $select_values_date,
			'required'    => true,
		);

		if ( isset( $ticket['booking_selection_date'] ) && isset( $ticket['booking_selection_slot'] ) && isset( $ticket['fooevents_bookings_method'] ) && $ticket['fooevents_bookings_method'] === $woocommerce_events_bookings_method ) {

			$booking_selection_date = explode( '_', $ticket['booking_selection_date'] );
			$booking_selection_slot = explode( '_', $ticket['booking_selection_slot'] );

			$selected_slot_id = $fooevents_bookings_options_date_slot[ $booking_selection_date[0] ][ $booking_selection_slot[0] ]['slot_id'];

			foreach ( $fooevents_bookings_options_date_slot[ $booking_selection_date[0] ] as $slot_id => $slot ) {

				if ( 'yes' === $woocommerce_events_bookings_expire_passed_date ) {

					$date_to_compare = '';
					if ( isset( $slot['hour'] ) && ! empty( $slot['hour'] ) && isset( $slot['minute'] ) && ! empty( $slot['minute'] ) && isset( $slot['period'] ) && ! empty( $slot['period'] ) ) {

						$date_to_compare = $booking_selection_date[0] . ' ' . $slot['hour'] . ':' . $slot['minute'] . $slot['period'];

					} elseif ( isset( $slot['hour'] ) && ! empty( $slot['hour'] ) && isset( $slot['minute'] ) && ! empty( $slot['minute'] ) ) {

						$date_to_compare = $booking_selection_date[0] . ' ' . $slot['hour'] . ':' . $slot['minute'];

					} else {

						$date_to_compare = $booking_selection_date[0];

					}

					if ( ! empty( $woocommerce_events_timezone ) && $wordpress_timezone !== $woocommerce_events_timezone ) {

						$date_time = new DateTime( '@' . $today );
						$timezone  = new DateTimeZone( $woocommerce_events_timezone );
						$date_time->setTimezone( $timezone );
						$today = $date_time->format( 'U' );

					}

					$expire_date = sanitize_text_field( $date_to_compare );

					if ( 'd/m/Y' === $format ) {

						$expire_date = str_replace( '/', '-', $expire_date );

					}

					$expire_date = str_replace( ',', '', $expire_date );
					$expire_date = $this->convert_month_to_english( $expire_date );

					$timestamp = strtotime( $expire_date );

					if ( ! empty( $woocommerce_events_bookings_expire_value ) && ! empty( $woocommerce_events_bookings_expire_unit ) && ! empty( $timestamp ) ) {

						$timestamp = strtotime( '-' . $woocommerce_events_bookings_expire_value . ' ' . $woocommerce_events_bookings_expire_unit, $timestamp );

					}

					if ( $today > $timestamp ) {

						continue;

					}
				}

				$stock = '';

				if ( empty( $slot['stock'] ) || $slot['stock'] < 0 ) {

					$stock = __( 'Unlimited', 'fooevents-bookings' );

				} else {

					$stock = $slot['stock'];

				}

				$val = $slot['slot_label'];

				if ( ! empty( $slot['slot_time'] ) && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

					$val .= ' ' . $slot['slot_time'];

				}

				if ( strval( $slot['stock'] ) !== '0' ) {

					if ( 'on' === $woocommerce_events_view_bookings_stock_dropdowns ) {

						$val .= ' (' . $stock . ')';

					}
				}

				$select_values_slot[ $slot['slot_id'] . '_' . $slot['date_id'] . '_' . $product_id ] = $val;

			}
		}

		$slot_field_params = array(
			'type'        => 'select',
			'class'       => array( 'attendee-class form-row-wide fooevents-bookings-date-slot-slot' ),
			'label'       => '',
			'placeholder' => $slot_select_placeholder,
			'options'     => $select_values_slot,
			'required'    => true,
		);

		if ( ! empty( $date_field_params ) ) {

			woocommerce_form_field( 'fooevents_bookings_date_slot_date_' . $x . '__' . $y, $date_field_params, $checkout->get_value( 'fooevents_bookings_date_slot_date_' . $x . '__' . $y ) );
			woocommerce_form_field( 'fooevents_bookings_date_slot_slot_' . $x . '__' . $y, $slot_field_params, $checkout->get_value( 'fooevents_bookings_date_slot_slot_' . $x . '__' . $y ) );

		}
	}

	/**
	 * Output hidden booking fields
	 */
	public function output_booking_fields_hidden( $product_id, $x, $y, $ticket, $checkout, $woocommerce_events_bookings_expire_passed_date, $woocommerce_events_bookings_expire_value, $woocommerce_events_bookings_expire_unit, $woocommerce_events_hide_bookings_display_time, $woocommerce_events_view_bookings_stock_dropdowns, $woocommerce_events_view_out_of_stock_bookings, $woocommerce_events_time_zone, $fooevents_bookings_options, $each_slot_one_date, $one_slot_multiple_dates, $bookings_date_term, $bookings_slot_term, $date_label, $slot_label, $booking_details_label, $woocommerce_events_bookings_method ) {

		$bookings_date_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsDateOverride', true );
		$bookings_slot_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsSlotOverride', true );
		$date_label         = '';
		$slot_label         = '';

		if ( empty( $bookings_date_term ) ) {

			$date_label = __( 'Date', 'fooevents-bookings' );

		} else {

			$date_label = $bookings_date_term;

		}

		if ( empty( $bookings_slot_term ) ) {

			$slot_label = __( 'Slot', 'fooevents-bookings' );

		} else {

			$slot_label = $bookings_slot_term;

		}

		if ( ! isset( $ticket['fooevents_bookings_method'] ) ) {

			$ticket['fooevents_bookings_method'] = 'slotdate';

		}

		if ( 'slotdate' === $ticket['fooevents_bookings_method'] ) {

			if ( isset( $ticket['booking_selection_date'] ) && isset( $ticket['booking_selection_date'] ) ) {

				$selected_slot = explode( '_', $ticket['booking_selection_slot'] );

				$slot_output = '';
				if ( isset( $fooevents_bookings_options[ $selected_slot[0] ]['add_time'] ) && 'enabled' === $fooevents_bookings_options[ $selected_slot[0] ]['add_time'] && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

					$slot_output = $fooevents_bookings_options[ $selected_slot[0] ]['label'] . ' ' . $fooevents_bookings_options[ $selected_slot[0] ]['formatted_time'];

				} else {

					$slot_output = $fooevents_bookings_options[ $selected_slot[0] ]['label'];

				}

				echo '<div class="fooevents-variation-desc"><p><strong>' . esc_attr( $slot_label ) . ':</strong> ' . esc_attr( $slot_output ) . '</p></div>';
				echo '<div class="fooevents-variation-desc"><p><strong>' . esc_attr( $date_label ) . ':</strong> ' . esc_attr( $fooevents_bookings_options[ $selected_slot[0] ]['add_date'][ $ticket['booking_selection_date'] ]['date'] ) . '</p></div>';

				echo '<input type="hidden" name="fooevents_bookings_date_' . esc_attr( $x ) . '__' . esc_attr( $y ) . '" value="' . esc_attr( $ticket['booking_selection_date'] ) . '">';
				echo '<input type="hidden" name="fooevents_bookings_slot_' . esc_attr( $x ) . '__' . esc_attr( $y ) . '" value="' . esc_attr( $ticket['booking_selection_slot'] ) . '">';

			} elseif ( isset( $ticket['booking_selection_slot_date'] ) ) {

				$selected_slot_date = explode( '_', $ticket['booking_selection_slot_date'] );
				$slot_output        = '';
				if ( isset( $fooevents_bookings_options[ $selected_slot_date[0] ]['add_time'] ) && 'enabled' === $fooevents_bookings_options[ $selected_slot_date[0] ]['add_time'] && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

					$slot_output = $fooevents_bookings_options[ $selected_slot_date[0] ]['label'] . ' ' . $fooevents_bookings_options[ $selected_slot_date[0] ]['formatted_time'];

				} else {

					$slot_output = $fooevents_bookings_options[ $selected_slot_date[0] ]['label'];

				}

				echo '<div class="fooevents-variation-desc"><p><strong>' . esc_attr( $slot_label ) . ':</strong> ' . esc_attr( $slot_output ) . '</p></div>';
				echo '<div class="fooevents-variation-desc"><p><strong>' . esc_attr( $date_label ) . ':</strong> ' . esc_attr( $fooevents_bookings_options[ $selected_slot_date[0] ]['add_date'][ $selected_slot_date[1] ]['date'] ) . '</p></div>';

				echo '<input type="hidden" name="fooevents_bookings_slot_date_' . esc_attr( $x ) . '__' . esc_attr( $y ) . '" value="' . esc_attr( $ticket['booking_selection_slot_date'] ) . '">';

			} else {

				echo '<div class="fooevents-variation-desc"><p><strong>' . esc_attr( 'The booking slot and date selection are missing. Please remove the item from your cart and select a slot and date on the product page.' ) . '</strong></p></div>';
				echo '<input type="hidden" name="fooevents_bookings_slot_date_' . esc_attr( $x ) . '__' . esc_attr( $y ) . '" value="">';

			}
		} elseif ( 'dateslot' === $ticket['fooevents_bookings_method'] ) {

			$selected_slot = explode( '_', $ticket['booking_selection_slot'] );

			$slot_output = '';
			if ( isset( $fooevents_bookings_options[ $selected_slot[0] ]['add_time'] ) && 'enabled' === $fooevents_bookings_options[ $selected_slot[0] ]['add_time'] && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

				$slot_output = $fooevents_bookings_options[ $selected_slot[0] ]['label'] . ' ' . $fooevents_bookings_options[ $selected_slot[0] ]['formatted_time'];

			} else {

				$slot_output = $fooevents_bookings_options[ $selected_slot[0] ]['label'];

			}

			echo '<div class="fooevents-variation-desc"><p><strong>' . esc_attr( $date_label ) . ':</strong> ' . esc_attr( $fooevents_bookings_options[ $selected_slot[0] ]['add_date'][ $selected_slot[1] ]['date'] ) . '</p></div>';
			echo '<div class="fooevents-variation-desc"><p><strong>' . esc_attr( $slot_label ) . ':</strong> ' . esc_attr( $slot_output ) . '</p></div>';

			echo '<input type="hidden" name="fooevents_bookings_date_slot_date_' . esc_attr( $x ) . '__' . esc_attr( $y ) . '" value="' . esc_attr( $ticket['booking_selection_date'] ) . '">';
			echo '<input type="hidden" name="fooevents_bookings_date_slot_slot_' . esc_attr( $x ) . '__' . esc_attr( $y ) . '" value="' . esc_attr( $ticket['booking_selection_slot'] ) . '">';

		}
	}

	/**
	 * Process bookings options to be suitable for date slot output
	 *
	 * @param array $fooevents_bookings_options bookings options.
	 * @return array
	 */
	public function process_date_slot_bookings_options( $fooevents_bookings_options ) {

		$processed_booking_options = array();

		foreach ( $fooevents_bookings_options as $slot_id => $option ) {

			if ( ! empty( $option['add_date'] ) ) {

				foreach ( $option['add_date'] as $date_id => $date_option ) {

					$date       = $date_option['date'];
					$slot_label = $option['label'];
					$slot_time  = '';
					if ( isset( $option['add_time'] ) && 'enabled' === $option['add_time'] ) {

						$slot_time = $option['formatted_time'];

					}

					$hour = '';
					if ( isset( $option['hour'] ) ) {

						$hour = $option['hour'];

					}

					$minute = '';
					if ( isset( $option['minute'] ) ) {

						$minute = $option['minute'];

					}

					$period = '';
					if ( isset( $option['period'] ) ) {

						$period = $option['period'];

					}

					$processed_booking_options[ $date ][ $slot_id ] = array(
						'slot_label' => $slot_label,
						'slot_id'    => $slot_id,
						'date_id'    => $date_id,
						'stock'      => $date_option['stock'],
						'slot_time'  => $slot_time,
						'hour'       => $hour,
						'minute'     => $minute,
						'period'     => $period,
					);

				}
			}
		}

		uksort( $processed_booking_options, array( $this, 'process_bookings_options_sort_by_date' ) );

		return $processed_booking_options;
	}

	/**
	 * Output booking fields on checkout. Slot/Date format
	 *
	 * @param int    $product_id product ID.
	 * @param int    $x event counter.
	 * @param int    $y ticket counter.
	 * @param array  $ticket ticket data.
	 * @param object $checkout checkout.
	 */
	public function output_booking_fields( $product_id, $x, $y, $ticket, $checkout ) {

		global $woocommerce;

		$woocommerce_events_view_bookings_options         = get_post_meta( $product_id, 'WooCommerceEventsViewBookingsOptions', true );
		$woocommerce_events_bookings_method               = get_post_meta( $product_id, 'WooCommerceEventsBookingsMethod', true );
		$woocommerce_events_bookings_expire_passed_date   = get_post_meta( $product_id, 'WooCommerceEventsBookingsExpirePassedDate', true );
		$woocommerce_events_bookings_expire_value         = get_post_meta( $product_id, 'WooCommerceEventsBookingsExpireValue', true );
		$woocommerce_events_bookings_expire_unit          = get_post_meta( $product_id, 'WooCommerceEventsBookingsExpireUnit', true );
		$woocommerce_events_hide_bookings_display_time    = get_post_meta( $product_id, 'WooCommerceEventsHideBookingsDisplayTime', true );
		$woocommerce_events_timezone                      = get_post_meta( $product_id, 'WooCommerceEventsTimeZone', true );
		$fooevents_bookings_options_serialized            = get_post_meta( $product_id, 'fooevents_bookings_options_serialized', true );
		$woocommerce_events_view_bookings_stock_dropdowns = get_post_meta( $product_id, 'WooCommerceEventsViewBookingsStockDropdowns', true );
		$woocommerce_events_view_out_of_stock_bookings    = get_post_meta( $product_id, 'WooCommerceEventsViewOutOfStockBookings', true );
		$fooevents_bookings_options_raw                   = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options                       = $this->process_booking_options( $fooevents_bookings_options_raw );
		$each_slot_one_date                               = $this->check_if_slot_has_one_date_only( $fooevents_bookings_options );
		$one_slot_multiple_dates                          = $this->check_if_only_one_slot( $fooevents_bookings_options );

		$bookings_date_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsDateOverride', true );
		$bookings_slot_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsSlotOverride', true );
		$date_label         = '';
		$slot_label         = '';

		$format             = get_option( 'date_format' );
		$wordpress_timezone = get_option( 'timezone_string' );

		if ( empty( $bookings_date_term ) ) {

			$date_label = __( 'Date', 'fooevents-bookings' );

		} else {

			$date_label = $bookings_date_term;

		}

		if ( empty( $bookings_slot_term ) ) {

			$slot_label = __( 'Slot', 'fooevents-bookings' );

		} else {

			$slot_label = $bookings_date_term;

		}

		if ( empty( $woocommerce_events_bookings_method ) || 1 === $woocommerce_events_bookings_method ) {

			$woocommerce_events_bookings_method = 'slotdate';

		}

		$bookings_booking_details_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsBookingDetailsOverride', true );
		$booking_details_label         = '';

		if ( empty( $bookings_booking_details_term ) ) {

			$booking_details_label = __( 'Booking details', 'fooevents-bookings' );

		} else {

			$booking_details_label = $bookings_booking_details_term;

		}

		if ( ! empty( $fooevents_bookings_options ) ) {

			$field_params = array(
				'type'        => 'hidden',
				'class'       => array( 'attendee-class form-row-wide fooevents-bookings-date' ),
				'label'       => '',
				'placeholder' => '',
				'options'     => '',
				'required'    => true,
			);

			woocommerce_form_field( 'fooevents_bookings_method', $field_params, $woocommerce_events_bookings_method );

			if ( 'checkout' === $woocommerce_events_view_bookings_options || 'checkoutproduct' === $woocommerce_events_view_bookings_options || '' === $woocommerce_events_view_bookings_options || 'off' === $woocommerce_events_view_bookings_options ) {

				if ( 'slotdate' === $woocommerce_events_bookings_method ) {

					$this->output_booking_fields_slot_date( $product_id, $x, $y, $ticket, $checkout, $woocommerce_events_bookings_expire_passed_date, $woocommerce_events_bookings_expire_value, $woocommerce_events_bookings_expire_unit, $woocommerce_events_hide_bookings_display_time, $woocommerce_events_view_bookings_stock_dropdowns, $woocommerce_events_view_out_of_stock_bookings, $wordpress_timezone, $format, $woocommerce_events_timezone, $fooevents_bookings_options, $each_slot_one_date, $one_slot_multiple_dates, $bookings_date_term, $bookings_slot_term, $date_label, $slot_label, $booking_details_label, $woocommerce_events_bookings_method );

				} elseif ( 'dateslot' === $woocommerce_events_bookings_method ) {

					$this->output_booking_fields_date_slot( $product_id, $x, $y, $ticket, $checkout, $woocommerce_events_bookings_expire_passed_date, $woocommerce_events_bookings_expire_value, $woocommerce_events_bookings_expire_unit, $woocommerce_events_hide_bookings_display_time, $woocommerce_events_view_bookings_stock_dropdowns, $woocommerce_events_view_out_of_stock_bookings, $wordpress_timezone, $format, $woocommerce_events_timezone, $fooevents_bookings_options, $each_slot_one_date, $one_slot_multiple_dates, $bookings_date_term, $bookings_slot_term, $date_label, $slot_label, $booking_details_label, $woocommerce_events_bookings_method );

				}
			} else {

				$this->output_booking_fields_hidden( $product_id, $x, $y, $ticket, $checkout, $woocommerce_events_bookings_expire_passed_date, $woocommerce_events_bookings_expire_value, $woocommerce_events_bookings_expire_unit, $woocommerce_events_hide_bookings_display_time, $woocommerce_events_view_bookings_stock_dropdowns, $woocommerce_events_view_out_of_stock_bookings, $woocommerce_events_timezone, $fooevents_bookings_options, $each_slot_one_date, $one_slot_multiple_dates, $bookings_date_term, $bookings_slot_term, $date_label, $slot_label, $booking_details_label, $woocommerce_events_bookings_method );

			}
		}
	}

	/**
	 * Booking details called by get_ticket_data()
	 *
	 * @param int $ticket_id ticket ID.
	 * @param int $event_id event ID.
	 * @return array
	 */
	public function get_ticket_slot_and_date( $ticket_id, $event_id ) {

		$fooevents_bookings_options_serialized         = get_post_meta( $event_id, 'fooevents_bookings_options_serialized', true );
		$woocommerce_events_hide_bookings_display_time = get_post_meta( $event_id, 'WooCommerceEventsHideBookingsDisplayTime', true );
		$fooevents_bookings_options_raw                = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options                    = $this->process_booking_options( $fooevents_bookings_options_raw );

		$slot_id = get_post_meta( $ticket_id, 'WooCommerceEventsBookingSlotID', true );
		$date_id = get_post_meta( $ticket_id, 'WooCommerceEventsBookingDateID', true );

		$processed_slot_date = array(
			'slot' => '',
			'date' => '',
		);

		if ( isset( $fooevents_bookings_options[ $slot_id ]['label'] ) && isset( $fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['date'] ) ) {

			if ( isset( $fooevents_bookings_options[ $slot_id ]['add_time'] ) && 'enabled' === $fooevents_bookings_options[ $slot_id ]['add_time'] && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

				$processed_slot_date['slot'] = $fooevents_bookings_options[ $slot_id ]['label'] . ' ' . $fooevents_bookings_options[ $slot_id ]['formatted_time'];

			} else {

				$processed_slot_date['slot'] = $fooevents_bookings_options[ $slot_id ]['label'];

			}
			$processed_slot_date['date'] = $fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['date'];

		}

		return $processed_slot_date;
	}

	/**
	 * Outputs booking selection options in admin
	 *
	 * @param int $ticket_post_id ticket ID.
	 * @param int $product_id product ID.
	 * @return string
	 */
	public function ticket_details_booking_fields( $ticket_post_id, $product_id ) {

		$fooevents_bookings_options_serialized = get_post_meta( $product_id, 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );

		$bookings_date_term    = get_post_meta( $product_id, 'WooCommerceEventsBookingsDateOverride', true );
		$bookings_slot_term    = get_post_meta( $product_id, 'WooCommerceEventsBookingsSlotOverride', true );
		$bookings_details_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsBookingDetailsOverridePlural', true );
		$date_label            = '';
		$slot_label            = '';
		$details_label         = '';

		if ( empty( $bookings_details_term ) ) {

			$details_label = __( 'Booking details', 'fooevents-bookings' );

		} else {

			$details_label = $bookings_details_term;

		}

		if ( empty( $bookings_date_term ) ) {

			$date_label = __( 'Date', 'fooevents-bookings' );

		} else {

			$date_label = $bookings_date_term;

		}

		if ( empty( $bookings_slot_term ) ) {

			$slot_label = __( 'Slot', 'fooevents-bookings' );

		} else {

			$slot_label = $bookings_slot_term;

		}

		$post_meta                          = get_post_meta( $ticket_post_id );
		$woocommerce_events_booking_slot_id = get_post_meta( $ticket_post_id, 'WooCommerceEventsBookingSlotID', true );
		$woocommerce_events_booking_date_id = get_post_meta( $ticket_post_id, 'WooCommerceEventsBookingDateID', true );

		ob_start();

		if ( ! empty( $fooevents_bookings_options ) ) {

			require $this->config->template_path . 'bookings-edit-ticket.php';

		}

		$booking_options = ob_get_clean();

		return $booking_options;
	}

	/**
	 * Checks the fields on checkout screen.
	 *
	 * @param array $ticket ticket.
	 * @param int   $event event ID.
	 * @param int   $x event counter.
	 * @param int   $y ticket counter.
	 */
	public function check_required_fields( $ticket, $event, $x, $y ) {

		global $woocommerce;

		$bookings_date_term     = get_post_meta( $event, 'WooCommerceEventsBookingsDateOverride', true );
		$bookings_slot_term     = get_post_meta( $event, 'WooCommerceEventsBookingsSlotOverride', true );
		$bookings_attendee_term = get_post_meta( $event, 'WooCommerceEventsAttendeeOverride', true );
		$events_type            = get_post_meta( $event, 'WooCommerceEventsType', true );
		$event_name             = get_the_title( $event );

		if ( 'bookings' === $events_type ) {

			if ( empty( $bookings_date_term ) ) {

				$date_label = __( 'date', 'fooevents-bookings' );

			} else {

				$date_label = $bookings_date_term;

			}

			if ( empty( $bookings_slot_term ) ) {

				$slot_label = __( 'slot', 'fooevents-bookings' );

			} else {

				$slot_label = $bookings_slot_term;

			}

			if ( empty( $bookings_attendee_term ) ) {

				$attendee_label = __( 'Attendee', 'fooevents-bookings' );

			} else {

				$attendee_label = $bookings_attendee_term;

			}

			$field_id = 'fooevents_bookings_slot_' . $x . '__' . $y;

			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_POST[ $field_id ] ) && empty( $_POST[ $field_id ] ) ) {

				// translators: Placeholder is for the slot label, attendee label, ticket counter, event.
				$notice = sprintf( __( 'Please select the %1$s field for <strong>%2$s %3$d</strong> (%4$s) ', 'fooevents-bookings' ), $slot_label, $attendee_label, $y, $event_name );
				wc_add_notice( $notice, 'error' );

			}

			$field_id = 'fooevents_bookings_date_' . $x . '__' . $y;

			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_POST[ $field_id ] ) && empty( $_POST[ $field_id ] ) && ! isset( $_POST[ 'fooevents_bookings_slot_date_' . $x . '__' . $y ] ) ) {

				// translators: Placeholder is for the date label, attendee label, ticket counter, event.
				$notice = sprintf( __( 'Please select the %1$s field for <strong>%2$s %3$d</strong> (%4$s) ', 'fooevents-bookings' ), $date_label, $attendee_label, $y, $event_name );
				wc_add_notice( $notice, 'error' );

			}

			$field_id = 'fooevents_bookings_slot_date_' . $x . '__' . $y;

			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_POST[ $field_id ] ) && empty( $_POST[ $field_id ] ) ) {

				// translators: Placeholder is for the slot label, attendee label, ticket counter, event.
				$notice = sprintf( __( 'Please select the %1$s field for <strong>%2$s %3$d</strong> (%4$s) ', 'fooevents-bookings' ), $slot_label, $attendee_label, $y, $event_name );
				wc_add_notice( $notice, 'error' );

			}

			$field_id = 'fooevents_bookings_date_slot_date_' . $x . '__' . $y;

			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_POST[ $field_id ] ) && empty( $_POST[ $field_id ] ) ) {

				// translators: Placeholder is for the date label, attendee label, ticket counter, event.
				$notice = sprintf( __( 'Please select the %1$s field for <strong>%2$s %3$d</strong> (%4$s) ', 'fooevents-bookings' ), $date_label, $attendee_label, $y, $event_name );
				wc_add_notice( $notice, 'error' );

			}

			$field_id = 'fooevents_bookings_date_slot_slot_' . $x . '__' . $y;

			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_POST[ $field_id ] ) && empty( $_POST[ $field_id ] ) ) {

				// translators: Placeholder is for the slot label, attendee label, ticket counter, event.
				$notice = sprintf( __( 'Please select the %1$s field for <strong>%2$s %3$d</strong> (%4$s) ', 'fooevents-bookings' ), $slot_label, $attendee_label, $y, $event_name );
				wc_add_notice( $notice, 'error' );

			}

			$field_id_slot      = 'fooevents_bookings_slot_' . $x . '__' . $y;
			$field_id_date      = 'fooevents_bookings_date_' . $x . '__' . $y;
			$field_id_slot_date = 'fooevents_bookings_slot_date_' . $x . '__' . $y;

			if ( isset( $_POST['fooevents_bookings_method'] ) && 'slotdate' === $_POST['fooevents_bookings_method'] && ! isset( $_POST[ $field_id_date ] ) && ! isset( $field_id_slot_date ) ) {

				// translators: Placeholder is for the slot label, attendee label, ticket counter, event.
				$notice = sprintf( __( 'Please select the %1$s and %2$s field for <strong>%3$s %4$d</strong> (%5$s) ', 'fooevents-bookings' ), $slot_label, $date_label, $attendee_label, $y, $event_name );
				wc_add_notice( $notice, 'error' );

			}

			if ( isset( $_POST['fooevents_bookings_method'] ) && 'slotdate' === $_POST['fooevents_bookings_method'] && ! isset( $_POST[ $field_id_slot ] ) && ! isset( $field_id_slot_date ) ) {

				// translators: Placeholder is for the slot label, attendee label, ticket counter, event.
				$notice = sprintf( __( 'Please select the %1$s and %2$s field for <strong>%3$s %4$d</strong> (%5$s) ', 'fooevents-bookings' ), $slot_label, $date_label, $attendee_label, $y, $event_name );
				wc_add_notice( $notice, 'error' );

			}
		}
	}

	/**
	 * Check booking stock
	 *
	 * @param int $event_id event ID.
	 * @param int $event event.
	 * @param int $x event counter.
	 * @param int $y ticket counter.
	 */
	public function check_booking_availability( $event_id, $event, $x, $y ) {

		global $wpdb;

		$bookings_date_term     = get_post_meta( $event_id, 'WooCommerceEventsBookingsDateOverride', true );
		$bookings_slot_term     = get_post_meta( $event_id, 'WooCommerceEventsBookingsSlotOverride', true );
		$bookings_attendee_term = get_post_meta( $event_id, 'WooCommerceEventsAttendeeOverride', true );

		$event = get_the_title( $event );

		if ( empty( $bookings_date_term ) ) {

			$date_label = __( 'date', 'fooevents-bookings' );

		} else {

			$date_label = $bookings_date_term;

		}

		if ( empty( $bookings_slot_term ) ) {

			$slot_label = __( 'slot', 'fooevents-bookings' );

		} else {

			$slot_label = $bookings_slot_term;

		}

		if ( empty( $bookings_attendee_term ) ) {

			$attendee_label = __( 'Attendee', 'fooevents-bookings' );

		} else {

			$attendee_label = $bookings_attendee_term;

		}

		$fooevents_bookings_options_serialized = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = $event_id AND meta_key = 'fooevents_bookings_options_serialized'",
				$event_id
			),
			'ARRAY_A'
		);

		$fooevents_bookings_options_serialized = $fooevents_bookings_options_serialized['meta_value'];

		$fooevents_bookings_options_raw = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options     = $this->process_booking_options( $fooevents_bookings_options_raw );

		$slot_id        = 'fooevents_bookings_slot_' . $x . '__' . $y;
		$date_id        = 'fooevents_bookings_date_' . $x . '__' . $y;
		$slot_date_id   = 'fooevents_bookings_slot_date_' . $x . '__' . $y;
		$date_slot_date = 'fooevents_bookings_date_slot_date_' . $x . '__' . $y;
		$date_slot_slot = 'fooevents_bookings_date_slot_slot_' . $x . '__' . $y;

		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_POST[ $slot_id ] ) && isset( $_POST[ $date_id ] ) ) {

			$booking_slot = explode( '_', sanitize_text_field( wp_unslash( $_POST[ $slot_id ] ) ) );
			$booking_slot = $booking_slot[0];
			$booking_date = sanitize_text_field( wp_unslash( $_POST[ $date_id ] ) );

			$date_stock = $fooevents_bookings_options[ $booking_slot ]['add_date'][ $booking_date ]['stock'];

			if ( empty( $date_stock ) && '0' != $date_stock ) {

				// unlimited.
				$date_stock = 999;

			}

			if ( empty( $fooevents_bookings_options[ $booking_slot ] ) || $date_stock <= 0 ) {

				// translators: Placeholder is for the slot label, date label, attendee label, ticket counter, event.
				$notice = sprintf( __( 'The selected %1$s/%2$s is not available for <strong>%3$s %4$d</strong> (%5$s).', 'fooevents-bookings' ), $slot_label, $date_label, $attendee_label, $y, $event );
				wc_add_notice( $notice, 'error' );

			}
		} elseif ( isset( $_POST[ $slot_date_id ] ) ) {

			$booking_slot_date = explode( '_', sanitize_text_field( wp_unslash( $_POST[ $slot_date_id ] ) ) );
			$slot_date_stock   = $fooevents_bookings_options[ $booking_slot_date[0] ]['add_date'][ $booking_slot_date[1] ]['stock'];

			if ( empty( $slot_date_stock ) && strval( $slot_date_stock ) !== '0' ) {

				// unlimited.
				$slot_date_stock = 999;

			}

			if ( empty( $fooevents_bookings_options[ $booking_slot_date[0] ] ) || $slot_date_stock <= 0 ) {

				// translators: Placeholder is for the slot label, date label, attendee label, ticket counter, event.
				$notice = sprintf( __( 'The selected %1$s/%2$s is not available for <strong>%3$s %4$d</strong> (%5$s).', 'fooevents-bookings' ), $slot_label, $date_label, $attendee_label, $y, $event );
				wc_add_notice( $notice, 'error' );

			}
		} elseif ( isset( $_POST[ $date_slot_date ] ) && isset( $_POST[ $date_slot_slot ] ) ) {

			$fooevents_bookings_options_date_slot = $this->process_date_slot_bookings_options( $fooevents_bookings_options );
			$date_selected                        = sanitize_text_field( wp_unslash( $_POST[ $date_slot_date ] ) );
			$date_selected                        = explode( '_', $date_selected );
			$slot_selected                        = sanitize_text_field( wp_unslash( $_POST[ $date_slot_slot ] ) );
			$slot_selected                        = explode( '_', $slot_selected );

			if ( strval( $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['stock'] ) === '0' ) {

				$passed = false;
				// translators: Placeholder is for the slot label, date label.
				$notice = sprintf( __( '<strong>Availability:</strong> The selected %1$s/%2$s is not available.', 'fooevents-bookings' ), $slot_label, $date_label );

				wc_add_notice( $notice, 'error' );

			}

			if ( 'yes' === $bookings_expire_passed_date ) {

				$date = $date_selected[0];

				if ( isset( $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['hour'] ) && ! empty( $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['hour'] ) && isset( $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['minute'] ) && ! empty( $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['minute'] ) && isset( $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['period'] ) && ! empty( $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['period'] ) ) {

					$date_to_compare = $date . ' ' . $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['hour'] . ':' . $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['minute'] . $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['period'];

				} elseif ( isset( $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['hour'] ) && ! empty( $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['hour'] ) && isset( $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['minute'] ) && ! empty( $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['minute'] ) ) {

					$date_to_compare = $date . ' ' . $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['hour'] . ':' . $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['minute'];

				} else {

					$date_to_compare = $date;

				}

				$expire_date = sanitize_text_field( $date_to_compare );

				if ( ! empty( $woocommerce_events_timezone ) && $wordpress_timezone !== $woocommerce_events_timezone ) {

					$date_time = new DateTime( '@' . $today );
					$timezone  = new DateTimeZone( $woocommerce_events_timezone );
					$date_time->setTimezone( $timezone );
					$today = $date_time->format( 'U' );

				}

				if ( 'd/m/Y' === $format ) {

					$expire_date = str_replace( '/', '-', $expire_date );

				}

				$expire_date = str_replace( ',', '', $expire_date );
				$expire_date = $this->convert_month_to_english( $expire_date );

				$timestamp = strtotime( $expire_date );

				if ( $today > $timestamp ) {

					// translators: Placeholder is for the slot label, date label, attendee label, ticket counter, event.
					$notice = sprintf( __( 'The selected %1$s/%2$s is not available for <strong>%3$s %4$d</strong> (%5$s). Date has expired.', 'fooevents-bookings' ), $slot_label, $date_label, $attendee_label, $y, $event );
					wc_add_notice( $notice, 'error' );

				}
			}
		}
	}

	/**
	 * Checks that there are enough bookings for attendees
	 *
	 * @param string $event event.
	 * @param array  $tickets ticket data.
	 */
	public function check_availablity_for_all_attendees( $event = '', $tickets = array() ) {

		$slots = array();
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		foreach ( $_POST as $key => $value ) {

			if ( strpos( $key, 'fooevents_bookings_slot_' ) === 0 ) {

				$slots[ $key ] = $value;

			} elseif ( strpos( $key, 'fooevents_bookings_date_slot_slot_' ) === 0 ) {

				$slots[ $key ] = $value;

			}
		}

		$bookings_to_process = array();

		if ( ! empty( $slots ) ) {

			$x = 0;
			foreach ( $slots as $input => $slot ) {

				$slot    = explode( '_', $slot );
				$date_id = '';
				$eid     = '';
				$sid     = '';
				$did     = '';

				if ( strpos( $input, 'fooevents_bookings_slot_date_' ) === 0 ) {

					$eid = $slot[2];
					$sid = $slot[0];
					$did = $slot[1];

				} elseif ( strpos( $input, 'fooevents_bookings_date_slot_slot_' ) === 0 ) {

					$sid = $slot[0];
					$did = $slot[1];
					$eid = $slot[2];

				} else {

					$input   = explode( '_', $input );
					$date_id = 'fooevents_bookings_date_' . $input[3] . '__' . $input[5];
					$eid     = $slot[1];
					$sid     = $slot[0];

					if ( isset( $_POST[ $date_id ] ) ) {

						$did = sanitize_text_field( wp_unslash( $_POST[ $date_id ] ) );

					}
				}

				if ( isset( $bookings_to_process[ $eid ][ $sid ][ $did ] ) ) {

					$bookings_to_process[ $eid ][ $sid ][ $did ] = $bookings_to_process[ $eid ][ $sid ][ $did ] + 1;

				} else {

					$bookings_to_process[ $eid ][ $sid ][ $did ] = 1;

				}

				++$x;

			}
		}

		$unavailable_slot_stock = array();

		foreach ( $bookings_to_process as $event_id => $booking ) {

			$fooevents_bookings_options_serialized = get_post_meta( $event_id, 'fooevents_bookings_options_serialized', true );
			$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
			$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );

			foreach ( $booking as $slot => $date ) {

				foreach ( $date as $d => $num ) {

					$stock = $fooevents_bookings_options[ $slot ]['add_date'][ $d ]['stock'];

					if ( ! empty( $stock ) && $stock < $num && $stock > -1 && function_exists( 'wc_add_notice' ) ) {

						$event_title = get_the_title( $event );
						// translators: Placeholder is for the event, stock.
						$notice = sprintf( __( '%1$s only has %2$d bookings available.', 'fooevents-bookings' ), $event_title, $stock );
						wc_add_notice( $notice, 'error' );

					}

					if ( isset( $stock ) && '' !== $stock && (int) $stock < (int) $num && (int) $stock >= 0 ) {
						$unavailable_slot_stock[ $slot . '_' . $d ] = $stock;
					}
				}
			}
		}

		return $unavailable_slot_stock;
	}

	/**
	 * Capture booking options on create order
	 *
	 * @param int $product_id product ID.
	 * @param int $x event counter.
	 * @param int $y ticket counter.
	 * @return string
	 */
	public function capture_booking_options( $product_id, $x, $y, $ticket ) {

		$fooevents_bookings_options_serialized = get_post_meta( $product_id, 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );
		$returned_booking_options              = array();

		$slot_id           = 'fooevents_bookings_slot_' . $x . '__' . $y;
		$date_id           = 'fooevents_bookings_date_' . $x . '__' . $y;
		$slot_date_id      = 'fooevents_bookings_slot_date_' . $x . '__' . $y;
		$date_slot_date_id = 'fooevents_bookings_date_slot_date_' . $x . '__' . $y;
		$date_slot_slot_id = 'fooevents_bookings_date_slot_slot_' . $x . '__' . $y;

		if ( isset( $_POST[ $slot_id ] ) && isset( $_POST[ $date_id ] ) ) {

			$booking_slot             = explode( '_', sanitize_text_field( wp_unslash( $_POST[ $slot_id ] ) ) );
			$booking_slot             = $booking_slot[0];
			$booking_date             = sanitize_text_field( wp_unslash( $_POST[ $date_id ] ) );
			$returned_booking_options = array(
				'slot'       => $booking_slot,
				'slot_label' => $fooevents_bookings_options[ $booking_slot ]['label'],
				'date'       => $booking_date,
				'date_label' => $fooevents_bookings_options[ $booking_slot ]['add_date'][ $booking_date ]['date'],
			);

			$date_stock = $fooevents_bookings_options[ $booking_slot ]['add_date'][ $booking_date ]['stock'];

			if ( ! empty( $date_stock ) ) {

				$fooevents_bookings_options[ $booking_slot ]['add_date'][ $booking_date ]['stock'] = (int) $date_stock - 1;

			}

			$fooevents_bookings_options_serialized_updated = wp_json_encode( $fooevents_bookings_options, JSON_UNESCAPED_UNICODE );

			update_post_meta( $product_id, 'fooevents_bookings_options_serialized', $fooevents_bookings_options_serialized_updated );

			return $returned_booking_options;

		} elseif ( isset( $_POST[ $slot_date_id ] ) ) {

			$booking_slot_date        = explode( '_', sanitize_text_field( wp_unslash( $_POST[ $slot_date_id ] ) ) );
			$returned_booking_options = array(
				'slot'       => $booking_slot_date[0],
				'slot_label' => $fooevents_bookings_options[ $booking_slot_date[0] ]['label'],
				'date'       => $booking_slot_date[1],
				'date_label' => $fooevents_bookings_options[ $booking_slot_date[0] ]['add_date'][ $booking_slot_date[1] ]['date'],
			);

			$slot_date_stock = $fooevents_bookings_options[ $booking_slot_date[0] ]['add_date'][ $booking_slot_date[1] ]['stock'];

			if ( ! empty( $slot_date_stock ) ) {

				$fooevents_bookings_options[ $booking_slot_date[0] ]['add_date'][ $booking_slot_date[1] ]['stock'] = (int) $slot_date_stock - 1;

			}

			$fooevents_bookings_options_serialized_updated = wp_json_encode( $fooevents_bookings_options, JSON_UNESCAPED_UNICODE );

			update_post_meta( $product_id, 'fooevents_bookings_options_serialized', $fooevents_bookings_options_serialized_updated );

			return $returned_booking_options;

		} elseif ( isset( $_POST[ $date_slot_date_id ] ) && isset( $_POST[ $date_slot_slot_id ] ) ) {

			$fooevents_bookings_options_date_slot = $this->process_date_slot_bookings_options( $fooevents_bookings_options );

			$booking_date_slot_date   = explode( '_', sanitize_text_field( wp_unslash( $_POST[ $date_slot_date_id ] ) ) );
			$booking_date_slot_slot   = explode( '_', sanitize_text_field( wp_unslash( $_POST[ $date_slot_slot_id ] ) ) );
			$returned_booking_options = array(
				'slot'       => $booking_date_slot_slot[0],
				'slot_label' => $fooevents_bookings_options[ $booking_date_slot_slot[0] ]['label'],
				'date'       => $booking_date_slot_slot[1],
				'date_label' => $fooevents_bookings_options[ $booking_date_slot_slot[0] ]['add_date'][ $booking_date_slot_slot[1] ]['date'],
			);

			$slot_date_stock = $fooevents_bookings_options[ $booking_date_slot_slot[0] ]['add_date'][ $booking_date_slot_slot[1] ]['stock'];

			if ( ! empty( $slot_date_stock ) ) {

				$fooevents_bookings_options[ $booking_date_slot_slot[0] ]['add_date'][ $booking_date_slot_slot[1] ]['stock'] = (int) $slot_date_stock - 1;

			}

			$fooevents_bookings_options_serialized_updated = wp_json_encode( $fooevents_bookings_options, JSON_UNESCAPED_UNICODE );

			update_post_meta( $product_id, 'fooevents_bookings_options_serialized', $fooevents_bookings_options_serialized_updated );

			return $returned_booking_options;

		} elseif ( isset( $ticket['fooevents_bookings_method'] ) ) {

			/*
			echo '<pre>';
				print_r( $ticket );
			echo '</pre>';*/

			// Cater for WooCommerce checkout block.
			if ( 'slotdate' === $ticket['fooevents_bookings_method'] && isset( $ticket['booking_selection_date'] ) && isset( $ticket['booking_selection_slot'] ) ) {

				$booking_slot = explode( '_', sanitize_text_field( wp_unslash( $ticket['booking_selection_slot'] ) ) );
				$booking_slot = $booking_slot[0];
				$booking_date = sanitize_text_field( wp_unslash( $ticket['booking_selection_date'] ) );

				$returned_booking_options = array(
					'slot'       => $booking_slot,
					'slot_label' => $fooevents_bookings_options[ $booking_slot ]['label'],
					'date'       => $booking_date,
					'date_label' => $fooevents_bookings_options[ $booking_slot ]['add_date'][ $booking_date ]['date'],
				);

				$date_stock = $fooevents_bookings_options[ $booking_slot ]['add_date'][ $booking_date ]['stock'];

				if ( ! empty( $date_stock ) ) {

					$fooevents_bookings_options[ $booking_slot ]['add_date'][ $booking_date ]['stock'] = (int) $date_stock - 1;

				}

				$fooevents_bookings_options_serialized_updated = wp_json_encode( $fooevents_bookings_options, JSON_UNESCAPED_UNICODE );

				update_post_meta( $product_id, 'fooevents_bookings_options_serialized', $fooevents_bookings_options_serialized_updated );

				return $returned_booking_options;

			} elseif ( 'slotdate' === $ticket['fooevents_bookings_method'] && isset( $ticket['booking_selection_slot_date'] ) ) {

				$booking_slot_date        = explode( '_', sanitize_text_field( wp_unslash( $ticket['booking_selection_slot_date'] ) ) );
				$returned_booking_options = array(
					'slot'       => $booking_slot_date[0],
					'slot_label' => $fooevents_bookings_options[ $booking_slot_date[0] ]['label'],
					'date'       => $booking_slot_date[1],
					'date_label' => $fooevents_bookings_options[ $booking_slot_date[0] ]['add_date'][ $booking_slot_date[1] ]['date'],
				);

				$slot_date_stock = $fooevents_bookings_options[ $booking_slot_date[0] ]['add_date'][ $booking_slot_date[1] ]['stock'];

				if ( ! empty( $slot_date_stock ) ) {

					$fooevents_bookings_options[ $booking_slot_date[0] ]['add_date'][ $booking_slot_date[1] ]['stock'] = (int) $slot_date_stock - 1;

				}

				$fooevents_bookings_options_serialized_updated = wp_json_encode( $fooevents_bookings_options, JSON_UNESCAPED_UNICODE );

				update_post_meta( $product_id, 'fooevents_bookings_options_serialized', $fooevents_bookings_options_serialized_updated );

				return $returned_booking_options;

			} elseif ( 'dateslot' === $ticket['fooevents_bookings_method'] && isset( $ticket['booking_selection_slot'] ) ) {

				$fooevents_bookings_options_date_slot = $this->process_date_slot_bookings_options( $fooevents_bookings_options );

				$date_slot                = explode( '_', sanitize_text_field( wp_unslash( $ticket['booking_selection_slot'] ) ) );
				$returned_booking_options = array(
					'slot'       => $date_slot[0],
					'slot_label' => $fooevents_bookings_options[ $date_slot[0] ]['label'],
					'date'       => $date_slot[1],
					'date_label' => $fooevents_bookings_options[ $date_slot[0] ]['add_date'][ $date_slot[1] ]['date'],
				);

				$slot_date_stock = $fooevents_bookings_options[ $date_slot[0] ]['add_date'][ $date_slot[1] ]['stock'];

				if ( ! empty( $slot_date_stock ) ) {

					$fooevents_bookings_options[ $date_slot[0] ]['add_date'][ $date_slot[1] ]['stock'] = (int) $slot_date_stock - 1;

				}

				$fooevents_bookings_options_serialized_updated = wp_json_encode( $fooevents_bookings_options, JSON_UNESCAPED_UNICODE );

				update_post_meta( $product_id, 'fooevents_bookings_options_serialized', $fooevents_bookings_options_serialized_updated );

				/*
				echo "<pre>";
					print_r($returned_booking_options);
				echo "</pre>";*/

				return $returned_booking_options;

			}
		}
	}

	/**
	 * Ajax to return slots based on selected date
	 */
	public function fetch_bookings_date_slot_slots() {

		$result = '';
		if ( isset( $_POST['selected'] ) ) {

			$result = explode( '_', sanitize_text_field( wp_unslash( $_POST['selected'] ) ) );

		}

		$fooevents_bookings_options_serialized            = get_post_meta( $result[1], 'fooevents_bookings_options_serialized', true );
		$woocommerce_events_view_bookings_stock_dropdowns = get_post_meta( $result[1], 'WooCommerceEventsViewBookingsStockDropdowns', true );
		$woocommerce_events_hide_bookings_display_time    = get_post_meta( $result[1], 'WooCommerceEventsHideBookingsDisplayTime', true );
		$woocommerce_events_view_out_of_stock_bookings    = get_post_meta( $result[1], 'WooCommerceEventsViewOutOfStockBookings', true );
		$woocommerce_events_timezone                      = get_post_meta( $result[1], 'WooCommerceEventsTimeZone', true );
		$woocommerce_events_bookings_expire_passed_date   = get_post_meta( $result[1], 'WooCommerceEventsBookingsExpirePassedDate', true );
		$woocommerce_events_bookings_expire_value         = get_post_meta( $result[1], 'WooCommerceEventsBookingsExpireValue', true );
		$woocommerce_events_bookings_expire_unit          = get_post_meta( $result[1], 'WooCommerceEventsBookingsExpireUnit', true );
		$fooevents_bookings_options_raw                   = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options                       = $this->process_booking_options( $fooevents_bookings_options_raw );
		$fooevents_bookings_options_date_slot             = $this->process_date_slot_bookings_options( $fooevents_bookings_options );

		$today              = current_time( 'timestamp' );
		$format             = get_option( 'date_format' );
		$wordpress_timezone = get_option( 'timezone_string' );

		$return_slots = array();

		if ( ! empty( $fooevents_bookings_options_date_slot ) ) {

			foreach ( $fooevents_bookings_options_date_slot[ $result[0] ] as $k => $slot ) {

				$date = $result[0];

				if ( ( 'on' === $woocommerce_events_view_out_of_stock_bookings && '0' == $slot['stock'] ) || $slot['stock'] > 0 || '' === $slot['stock'] ) {

					if ( 'yes' === $woocommerce_events_bookings_expire_passed_date ) {

						$date_to_compare = '';
						if ( isset( $slot['hour'] ) && ! empty( $slot['hour'] ) && isset( $slot['minute'] ) && ! empty( $slot['minute'] ) && isset( $slot['period'] ) && ! empty( $slot['period'] ) ) {

							$date_to_compare = $date . ' ' . $slot['hour'] . ':' . $slot['minute'] . $slot['period'];

						} elseif ( isset( $slot['hour'] ) && ! empty( $slot['hour'] ) && isset( $slot['minute'] ) && ! empty( $slot['minute'] ) ) {

							$date_to_compare = $date . ' ' . $slot['hour'] . ':' . $slot['minute'];

						} else {

							$date_to_compare = $date;

						}

						$expire_date = sanitize_text_field( $date_to_compare );

						if ( ! empty( $woocommerce_events_timezone ) && $wordpress_timezone !== $woocommerce_events_timezone ) {

							$date_time = new DateTime( '@' . $today );
							$timezone  = new DateTimeZone( $woocommerce_events_timezone );
							$date_time->setTimezone( $timezone );
							$today = $date_time->format( 'U' );

						}

						if ( 'd/m/Y' === $format ) {

							$expire_date = str_replace( '/', '-', $expire_date );

						}

						$expire_date = str_replace( ',', '', $expire_date );
						$expire_date = $this->convert_month_to_english( $expire_date );

						$timestamp = strtotime( $expire_date );

						if ( ! empty( $woocommerce_events_bookings_expire_value ) && ! empty( $woocommerce_events_bookings_expire_unit ) && ! empty( $timestamp ) ) {

							$timestamp = strtotime( '-' . $woocommerce_events_bookings_expire_value . ' ' . $woocommerce_events_bookings_expire_unit, $timestamp );

						}

						if ( $today > $timestamp ) {

							continue;

						}
					}

					$stock = '';
					if ( '' === $slot['stock'] ) {

						$stock = __( 'Unlimited', 'fooevents-bookings' );

					} else {

						$stock = $slot['stock'];

					}

					if ( ! empty( $slot['slot_time'] ) && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

						$slot['slot_label'] .= ' ' . $slot['slot_time'];

					}

					if ( 'on' === $woocommerce_events_view_bookings_stock_dropdowns ) {

						$slot['slot_label'] .= ' (' . $stock . ')';

					}

					$slot['product'] = $result[1];

					$return_slots[] = $slot;

				}
			}
		}

		echo wp_json_encode( $return_slots, JSON_UNESCAPED_UNICODE );

		exit();
	}

	/**
	 * Ajax to return a booking slot's booking dates
	 */
	public function fetch_bookings_dates() {

		$result = '';
		if ( isset( $_POST['selected'] ) ) {

			$result = explode( '_', sanitize_text_field( wp_unslash( $_POST['selected'] ) ) );

		}

		$woocommerce_events_bookings_expire_passed_date   = get_post_meta( $result[1], 'WooCommerceEventsBookingsExpirePassedDate', true );
		$woocommerce_events_bookings_expire_value         = get_post_meta( $result[1], 'WooCommerceEventsBookingsExpireValue', true );
		$woocommerce_events_bookings_expire_unit          = get_post_meta( $result[1], 'WooCommerceEventsBookingsExpireUnit', true );
		$fooevents_bookings_options_serialized            = get_post_meta( $result[1], 'fooevents_bookings_options_serialized', true );
		$woocommerce_events_view_bookings_stock_dropdowns = get_post_meta( $result[1], 'WooCommerceEventsViewBookingsStockDropdowns', true );
		$woocommerce_events_view_out_of_stock_bookings    = get_post_meta( $result[1], 'WooCommerceEventsViewOutOfStockBookings', true );
		$woocommerce_events_timezone                      = get_post_meta( $result[1], 'WooCommerceEventsTimeZone', true );
		$fooevents_bookings_options_raw                   = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options                       = $this->process_booking_options( $fooevents_bookings_options_raw );
		$fooevents_bookings_options                       = $fooevents_bookings_options[ $result[0] ];
		$return_dates                                     = array();
		$today              = current_time( 'timestamp' );
		$date_format        = get_option( 'date_format' );
		$wordpress_timezone = get_option( 'timezone_string' );

		if ( ! empty( $fooevents_bookings_options ) ) {

			foreach ( $fooevents_bookings_options['add_date'] as $k => $v ) {

				$expire_hour = '';
				if ( ! empty( $fooevents_bookings_options['hour'] ) ) {

					$expire_hour = $fooevents_bookings_options['hour'];

				}

				$expire_minute = '';
				if ( ! empty( $fooevents_bookings_options['minute'] ) ) {

					$expire_minute = $fooevents_bookings_options['minute'];

				}

				$expire_period = '';
				if ( ! empty( $fooevents_bookings_options['period'] ) ) {

					$expire_period = $fooevents_bookings_options['period'];

				}

				if ( 'yes' === $woocommerce_events_bookings_expire_passed_date && isset( $v['date'] ) ) {

					$expire_date = sanitize_text_field( $v['date'] );

					if ( ! empty( $woocommerce_events_timezone ) && $wordpress_timezone !== $woocommerce_events_timezone ) {

						$date_time = new DateTime( '@' . $today );
						$timezone  = new DateTimeZone( $woocommerce_events_timezone );
						$date_time->setTimezone( $timezone );
						$today = $date_time->format( 'U' );

					}

					if ( 'd/m/Y' === $date_format ) {

						$expire_date = str_replace( '/', '-', $expire_date );

					}

					$expire_date = str_replace( ',', '', $expire_date );
					$expire_date = $this->convert_month_to_english( $expire_date );

					if ( ! empty( $expire_hour ) && ! empty( $expire_hour ) ) {

						$expire_date = $expire_date . ' ' . $expire_hour . ':' . $expire_minute . ' ' . $expire_period;

					}

					$timestamp = strtotime( $expire_date );

					if ( ! empty( $woocommerce_events_bookings_expire_value ) && ! empty( $woocommerce_events_bookings_expire_unit ) && ! empty( $timestamp ) ) {

						$timestamp = strtotime( '-' . $woocommerce_events_bookings_expire_value . ' ' . $woocommerce_events_bookings_expire_unit, $timestamp );

					}
					// echo $today . ' - ' . $timestamp . '  ';
					if ( $today > $timestamp ) {

						continue;

					}
				}

				if ( $v['stock'] > 0 || empty( $v['stock'] ) ) {

					$stock = $v['stock'];

					if ( ( 'on' === $woocommerce_events_view_out_of_stock_bookings && strval( $v['stock'] ) === '0' ) || strval( $v['stock'] ) !== '0' ) {

						if ( empty( $v['stock'] ) ) {
							$stock = __( 'Unlimited', 'fooevents-bookings' );
						}

						if ( strval( $v['stock'] ) === '0' ) {
							$stock = 0;
						}

						if ( 'on' === $woocommerce_events_view_bookings_stock_dropdowns ) {

							$val = $v['date'] . ' (' . $stock . ')';

						} else {

							$val = $v['date'];

						}

						$return_dates[ $k ] = $val;

					}
				}
			}
		}

		uasort( $return_dates, array( $this, 'process_bookings_options_sort_by_date' ) );

		echo wp_json_encode( $return_dates, JSON_UNESCAPED_UNICODE );

		exit();
	}

	/**
	 * Sorts returned dates
	 *
	 * @param string $date1 first date.
	 * @param string $date2 second date.
	 */
	public function process_bookings_options_sort_by_date( $date1, $date2 ) {

		$date_format = get_option( 'date_format' );
		$date1       = sanitize_text_field( $date1 );
		$date1       = preg_replace( '/\([^)]+\)/', '', $date1 );

		if ( 'd/m/Y' === $date_format ) {

			$date1 = str_replace( '/', '-', $date1 );

		}

		$date1 = str_replace( ',', '', $date1 );
		$date1 = $this->convert_month_to_english( $date1 );

		$date1 = strtotime( $date1 );

		$date2 = sanitize_text_field( $date2 );
		$date2 = preg_replace( '/\([^)]+\)/', '', $date2 );

		if ( 'd/m/Y' === $date_format ) {

			$date2 = str_replace( '/', '-', $date2 );

		}

		$date2 = str_replace( ',', '', $date2 );
		$date2 = $this->convert_month_to_english( $date2 );

		$date2 = strtotime( $date2 );

		return $date1 - $date2;
	}

	/**
	 * Admin function to return a slots booking dates in admin
	 */
	public function fetch_bookings_dates_admin() {

		$slot_id = '';
		if ( isset( $_POST['slot_id'] ) ) {

			$slot_id = sanitize_text_field( wp_unslash( $_POST['slot_id'] ) );

		}

		$event_id = '';
		if ( isset( $_POST['event_id'] ) ) {

			$event_id = sanitize_text_field( wp_unslash( $_POST['event_id'] ) );

		}

		$woocommerce_events_bookings_expire_passed_date = get_post_meta( $event_id, 'WooCommerceEventsBookingsExpirePassedDate', true );
		$woocommerce_events_bookings_expire_value       = get_post_meta( $event_id, 'WooCommerceEventsBookingsExpireValue', true );
		$woocommerce_events_bookings_expire_unit        = get_post_meta( $event_id, 'WooCommerceEventsBookingsExpireUnit', true );
		$woocommerce_events_timezone                    = get_post_meta( $event_id, 'WooCommerceEventsTimeZone', true );
		$fooevents_bookings_options_serialized          = get_post_meta( $event_id, 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw                 = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options                     = $this->process_booking_options( $fooevents_bookings_options_raw );
		$fooevents_bookings_options                     = $fooevents_bookings_options[ $slot_id ];
		$return_dates                                   = array();
		$today              = current_time( 'timestamp' );
		$date_format        = get_option( 'date_format' );
		$wordpress_timezone = get_option( 'timezone_string' );

		if ( ! empty( $fooevents_bookings_options ) ) {

			foreach ( $fooevents_bookings_options['add_date'] as $k => $v ) {

				if ( 'yes' === $woocommerce_events_bookings_expire_passed_date && isset( $v['date'] ) ) {

					$expire_date = sanitize_text_field( $v['date'] );

					if ( ! empty( $woocommerce_events_timezone ) && $wordpress_timezone !== $woocommerce_events_timezone ) {

						$date_time = new DateTime( '@' . $today );
						$timezone  = new DateTimeZone( $woocommerce_events_timezone );
						$date_time->setTimezone( $timezone );
						$today = $date_time->format( 'U' );

					}

					if ( 'd/m/Y' === $date_format ) {

						$expire_date = str_replace( '/', '-', $expire_date );

					}

					$expire_date = str_replace( ',', '', $expire_date );
					$expire_date = $this->convert_month_to_english( $expire_date );

					$timestamp = strtotime( $expire_date );

					if ( ! empty( $woocommerce_events_bookings_expire_value ) && ! empty( $woocommerce_events_bookings_expire_unit ) && ! empty( $timestamp ) ) {

						$timestamp = strtotime( '-' . $woocommerce_events_bookings_expire_value . ' ' . $woocommerce_events_bookings_expire_unit, $timestamp );

					}

					if ( $today > $timestamp ) {

						continue;

					}
				}

				if ( $v['stock'] > 0 || empty( $v['stock'] ) ) {

					$stock = $v['stock'];

					if ( strval( $v['stock'] ) !== '0' ) {

						if ( empty( $v['stock'] ) ) {

							$stock = __( 'Unlimited', 'fooevents-bookings' );

						}

						$return_dates[ $k ] = $v['date'];

					}
				}
			}
		}

		echo wp_json_encode( $return_dates, JSON_UNESCAPED_UNICODE );

		exit();
	}

	/**
	 * Fetch booking options for add ticket page
	 */
	public function fetch_add_ticket_booking_options() {

		// TODO: Add nonce check with val from main plugin.
		$event_id = '';
		if ( isset( $_POST['event_id'] ) ) {

			$event_id = sanitize_text_field( wp_unslash( $_POST['event_id'] ) );

		}

		$post = get_post( $event_id );

		$fooevents_bookings_options_serialized = get_post_meta( $event_id, 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );

		$bookings_date_term = get_post_meta( $event_id, 'WooCommerceEventsBookingsDateOverride', true );
		$bookings_slot_term = get_post_meta( $event_id, 'WooCommerceEventsBookingsSlotOverride', true );
		$date_label         = '';
		$slot_label         = '';

		if ( empty( $bookings_slot_term ) ) {

			$slot_label = __( 'Slot', 'fooevents-bookings' );

		} else {

			$slot_label = $bookings_slot_term;

		}

		if ( empty( $bookings_date_term ) ) {

			$date_label = __( 'Date', 'fooevents-bookings' );

		} else {

			$date_label = $bookings_date_term;

		}

		$slot_default = __( 'Select ', 'fooevents-bookings' ) . $slot_label;
		$date_default = __( 'Select ', 'fooevents-bookings' ) . $date_label;

		if ( ! empty( $fooevents_bookings_options ) ) {

			require_once $this->config->template_path . 'bookings-add-ticket.php';

		}

		exit();
	}

	/**
	 * Validate bookings selection data in admin
	 *
	 * @param int $slot_id slot ID.
	 * @param int $date_id date ID.
	 * @param int $ticket_id ticket ID.
	 * @param int $event_id event ID.
	 */
	public function admin_edit_bookings_validate( $slot_id, $date_id, $ticket_id, $event_id ) {

		$ticket_id = sanitize_text_field( $ticket_id );
		$slot_id   = sanitize_text_field( $slot_id );
		$date_id   = sanitize_text_field( $date_id );
		$event_id  = sanitize_text_field( $event_id );

		$fooevents_bookings_options_serialized = get_post_meta( $event_id, 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );
		$fooevents_bookings_options            = $fooevents_bookings_options[ $slot_id ];

		$woocommerce_events_booking_slot_id = get_post_meta( $ticket_id, 'WooCommerceEventsBookingSlotID', true );
		$woocommerce_events_booking_date_id = get_post_meta( $ticket_id, 'WooCommerceEventsBookingDateID', true );

		$stock = $fooevents_bookings_options['add_date'][ $date_id ]['stock'];

		// If booking has changed.
		if ( $slot_id !== $woocommerce_events_booking_slot_id || $date_id !== $woocommerce_events_booking_date_id ) {

			$stock = $fooevents_bookings_options['add_date'][ $date_id ]['stock'];

			if ( '' === $stock ) {

				$stock = __( 'Unlimited', 'fooevents-bookings' );

			} else {

				$stock = (int) $stock;

			}

			if ( 0 === $stock ) {

				$message = __( 'No stock availabe for selected booking', 'fooevents-bookings' );
				return wp_json_encode(
					array(
						'type'    => 'error',
						'message' => $message,
					),
					JSON_UNESCAPED_UNICODE
				);

			}
		} else {

			return wp_json_encode( array( 'type' => 'success' ), JSON_UNESCAPED_UNICODE );

		}

		exit();
	}

	/**
	 * Validate bookings selection on add ticket page
	 *
	 * @param int $event_id event ID.
	 * @param int $slot_id slot ID.
	 * @param int $date_id date ID.
	 */
	public function admin_add_ticket_bookings_validate( $event_id, $slot_id, $date_id ) {

		if ( empty( $slot_id ) ) {

			$message = __( 'Booking slot is required.', 'fooevents-bookings' );
			return wp_json_encode(
				array(
					'type'    => 'error',
					'message' => $message,
				),
				JSON_UNESCAPED_UNICODE
			);

		}

		if ( empty( $date_id ) ) {

			$message = __( 'Booking date is required.', 'fooevents-bookings' );
			return wp_json_encode(
				array(
					'type'    => 'error',
					'message' => $message,
				),
				JSON_UNESCAPED_UNICODE
			);

		}

		if ( empty( $event_id ) ) {

			$message = __( 'Event is required.', 'fooevents-bookings' );
			return wp_json_encode(
				array(
					'type'    => 'error',
					'message' => $message,
				),
				JSON_UNESCAPED_UNICODE
			);

		}

		$fooevents_bookings_options_serialized = get_post_meta( $event_id, 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );
		$fooevents_bookings_options            = $fooevents_bookings_options[ $slot_id ];

		$stock = $fooevents_bookings_options['add_date'][ $date_id ]['stock'];

		if ( 0 === $stock ) {

			$message = __( 'No stock availabe for selected booking', 'fooevents-bookings' );
			return wp_json_encode(
				array(
					'type'    => 'error',
					'message' => $message,
				),
				JSON_UNESCAPED_UNICODE
			);

		} else {

			return wp_json_encode( array( 'type' => 'success' ), JSON_UNESCAPED_UNICODE );

		}

		exit();
	}

	/**
	 * Capture bookings on admin add ticket page
	 *
	 * @param int $post_id post ID.
	 * @param int $event_id event ID.
	 * @param int $slot_id slot ID.
	 * @param int $date_id date ID.
	 */
	public function admin_add_ticket_bookings_capture_booking( $post_id, $event_id, $slot_id, $date_id ) {

		$nonce = '';
		if ( isset( $_POST['fooevents_bookings_options_add_ticket_nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_options_add_ticket_nonce'] ) );
		}

		if ( ! wp_verify_nonce( $nonce, 'fooevents_bookings_options_add_ticket' ) ) {
			die( esc_attr__( 'Security check failed - FooEvents Bookings 0004', 'fooevents-bookings' ) );
		}

		$fooevents_bookings_options_serialized = get_post_meta( $event_id, 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );
		$wp_date_format                        = get_option( 'date_format' );
		$formatted_time                        = '';

		$slot = $fooevents_bookings_options[ $slot_id ]['label'];
		$date = $fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['date'];

		if ( isset( $fooevents_bookings_options[ $slot_id ]['add_time'] ) && 'enabled' === $fooevents_bookings_options[ $slot_id ]['add_time'] ) {

			$formatted_time = $fooevents_bookings_options[ $slot_id ]['hour'] . ':' . $fooevents_bookings_options[ $slot_id ]['minute'] . ' ' . str_replace( '.', '', $fooevents_bookings_options[ $slot_id ]['period'] );

		}

		$bookings_date_time = '';
		if ( 'd/m/Y' === $wp_date_format ) {

			$bookings_date_time = str_replace( '/', '-', $date );

		} else {

			$bookings_date_time = $date;

		}

		$bookings_date_time                        = str_replace( ',', '', $bookings_date_time );
		$bookings_date_time                        = $this->convert_month_to_english( $bookings_date_time );
		$woocommerce_events_booking_date_timestamp = strtotime( $bookings_date_time . ' ' . $formatted_time );

		update_post_meta( $post_id, 'WooCommerceEventsBookingSlot', $slot );
		update_post_meta( $post_id, 'WooCommerceEventsBookingSlotID', $slot_id );
		update_post_meta( $post_id, 'WooCommerceEventsBookingDate', $date );
		update_post_meta( $post_id, 'WooCommerceEventsBookingDateID', $date_id );
		update_post_meta( $post_id, 'WooCommerceEventsBookingDateTimestamp', $woocommerce_events_booking_date_timestamp );

		$date_stock = $fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['stock'];

		if ( ! empty( $date_stock ) ) {

			$fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['stock'] = $date_stock - 1;

			$fooevents_bookings_options_serialized_updated = wp_json_encode( $fooevents_bookings_options, JSON_UNESCAPED_UNICODE );

			update_post_meta( $event_id, 'fooevents_bookings_options_serialized', $fooevents_bookings_options_serialized_updated );

		}

		if ( isset( $fooevents_bookings_options[ $slot_id ]['zoom_id'] ) && 'enabled' === $fooevents_bookings_options[ $slot_id ]['zoom_id'] ) {

			$zoom_meeting_id = $fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['zoom_id'];

			$attendee_email = '';
			if ( isset( $_POST['WooCommerceEventsAttendeeEmail'] ) ) {

				$attendee_email = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeEmail'] ) );

			}

			$attendee_first_name = '';
			if ( isset( $_POST['WooCommerceEventsAttendeeName'] ) ) {

				$attendee_first_name = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeName'] ) );

			}

			$attendee_last_name = '';
			if ( isset( $_POST['WooCommerceEventsAttendeeLastName'] ) ) {

				$attendee_last_name = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeLastName'] ) );

			}

			$zoom_api_helper = new FooEvents_Zoom_API_Helper( new FooEvents_Config() );

			$register_args = array(
				'email'      => ! empty( $attendee_email ) ? $attendee_email : get_post_meta( $post_id, 'WooCommerceEventsPurchaserEmail', true ),
				'first_name' => ! empty( $attendee_first_name ) ? $attendee_first_name : get_post_meta( $post_id, 'WooCommerceEventsPurchaserFirstName', true ),
				'last_name'  => ! empty( $attendee_last_name ) ? $attendee_last_name : get_post_meta( $post_id, 'WooCommerceEventsPurchaserLastName', true ),
			);

			$register_result = $zoom_api_helper->add_update_single_zoom_registrant( $zoom_meeting_id, $register_args );

		}
	}

	/**
	 * Fetches the number of available stock for booking
	 */
	public function fetch_fooevents_bookings_date_stock() {

		$slot_selected = '';
		if ( isset( $_POST['slot_selected'] ) ) {

			$slot_selected = sanitize_text_field( wp_unslash( $_POST['slot_selected'] ) );
			$slot_selected = explode( '_', $slot_selected );

		}

		$date_selected = '';
		if ( isset( $_POST['date_selected'] ) ) {

			$date_selected = sanitize_text_field( wp_unslash( $_POST['date_selected'] ) );

		}

		$fooevents_bookings_options_serialized = get_post_meta( $slot_selected[1], 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );

		$stock = 0;

		if ( '0' === strval( $fooevents_bookings_options[ $slot_selected[0] ]['add_date'][ $date_selected ]['stock'] ) ) {

			$stock = __( '0', 'fooevents-bookings' );

		} elseif ( $fooevents_bookings_options[ $slot_selected[0] ]['add_date'][ $date_selected ]['stock'] > 0 ) {

			$stock = $fooevents_bookings_options[ $slot_selected[0] ]['add_date'][ $date_selected ]['stock'];

		} elseif ( isset( $fooevents_bookings_options[ $slot_selected[0] ]['add_date'][ $date_selected ]['stock'] ) && empty( $fooevents_bookings_options[ $slot_selected[0] ]['add_date'][ $date_selected ]['stock'] ) ) {

			$stock = __( 'Unlimited', 'fooevents-bookings' );

		} else {

			$stock = '';

		}

		if ( isset( $stock ) && ( $stock === '0' || ! empty( $stock ) ) ) {

			$available = '<strong>' . __( 'Availability: ', 'fooevents-bookings' ) . '</strong>' . $stock;

		}

		echo wp_json_encode(
			array(
				'stock'     => $available,
				'stock_val' => $stock,
			),
			JSON_UNESCAPED_UNICODE
		);

		exit();
	}

	/**
	 * Fetches the number of available stock for booking
	 */
	public function fetch_fooevents_bookings_slot_date_stock() {

		$slot_date_selected = '';
		if ( isset( $_POST['slot_date_selected'] ) ) {

			$slot_date_selected = sanitize_text_field( wp_unslash( $_POST['slot_date_selected'] ) );
			$slot_date_selected = explode( '_', $slot_date_selected );

		}

		$fooevents_bookings_options_serialized = get_post_meta( $slot_date_selected[2], 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );

		$stock = 0;

		if ( '0' === strval( $fooevents_bookings_options[ $slot_date_selected[0] ]['add_date'][ $slot_date_selected[1] ]['stock'] ) || (int) $fooevents_bookings_options[ $slot_date_selected[0] ]['add_date'][ $slot_date_selected[1] ]['stock'] < 0 ) {

			$stock = __( '0', 'fooevents-bookings' );

		} elseif ( empty( $fooevents_bookings_options[ $slot_date_selected[0] ]['add_date'][ $slot_date_selected[1] ]['stock'] ) ) {

			$stock = __( 'Unlimited', 'fooevents-bookings' );

		} elseif ( $fooevents_bookings_options[ $slot_date_selected[0] ]['add_date'][ $slot_date_selected[1] ]['stock'] > 0 ) {

			$stock = $fooevents_bookings_options[ $slot_date_selected[0] ]['add_date'][ $slot_date_selected[1] ]['stock'];

		}

		$available = '<strong>' . __( 'Availability: ', 'fooevents-bookings' ) . '</strong>' . $stock;

		echo wp_json_encode(
			array(
				'stock'     => $available,
				'stock_val' => $stock,
			),
			JSON_UNESCAPED_UNICODE
		);

		exit();
	}

	/**
	 * Fetches the number of available stock for booking
	 */
	public function fetch_fooevents_bookings_date_slot_stock() {

		$date_slot_slot_selected = '';
		if ( isset( $_POST['date_slot_slot_selected'] ) ) {

			$date_slot_slot_selected = sanitize_text_field( wp_unslash( $_POST['date_slot_slot_selected'] ) );
			$date_slot_slot_selected = explode( '_', $date_slot_slot_selected );

		}

		$date_slot_date_selected = '';
		if ( isset( $_POST['date_slot_date_selected'] ) ) {

			$date_slot_date_selected = sanitize_text_field( wp_unslash( $_POST['date_slot_date_selected'] ) );
			$date_slot_date_selected = explode( '_', $date_slot_date_selected );

		}

		$fooevents_bookings_options_serialized = get_post_meta( $date_slot_date_selected[1], 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );
		$fooevents_bookings_options_date_slot  = $this->process_date_slot_bookings_options( $fooevents_bookings_options );

		$stock = 0;

		if ( '0' === strval( $fooevents_bookings_options_date_slot[ $date_slot_date_selected[0] ][ $date_slot_slot_selected[0] ]['stock'] ) || (int) $fooevents_bookings_options_date_slot[ $date_slot_date_selected[0] ][ $date_slot_slot_selected[0] ]['stock'] < 0 ) {

			$stock = __( '0', 'fooevents-bookings' );

		} elseif ( empty( $fooevents_bookings_options_date_slot[ $date_slot_date_selected[0] ][ $date_slot_slot_selected[0] ]['stock'] ) ) {

			$stock = __( 'Unlimited', 'fooevents-bookings' );

		} elseif ( $fooevents_bookings_options_date_slot[ $date_slot_date_selected[0] ][ $date_slot_slot_selected[0] ]['stock'] > 0 ) {

			$stock = $fooevents_bookings_options_date_slot[ $date_slot_date_selected[0] ][ $date_slot_slot_selected[0] ]['stock'];

		}

		$available = '<strong>' . __( 'Availability: ', 'fooevents-bookings' ) . '</strong>' . $stock;

		echo wp_json_encode(
			array(
				'stock'     => $available,
				'stock_val' => $stock,
			),
			JSON_UNESCAPED_UNICODE
		);

		exit();
	}

	/**
	 * Processing booking options on order complete
	 *
	 * @param int   $post_id post ID.
	 * @param int   $event event ID.
	 * @param array $woocommerce_events_booking_options bookings options.
	 */
	public function process_capture_booking( $event, $woocommerce_events_booking_options, $post_id = '' ) {

		$fooevents_bookings_options_serialized = get_post_meta( $event, 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );
		$wp_date_format                        = get_option( 'date_format' );

		$woocommerce_events_booking_slot = '';
		$formatted_time                  = '';
		if ( ! empty( $fooevents_bookings_options ) ) {

			if ( isset( $fooevents_bookings_options[ $woocommerce_events_booking_options['slot'] ]['add_time'] ) && 'enabled' === $fooevents_bookings_options[ $woocommerce_events_booking_options['slot'] ]['add_time'] ) {

				$woocommerce_events_booking_slot = $fooevents_bookings_options[ $woocommerce_events_booking_options['slot'] ]['label'] . ' ' . $fooevents_bookings_options[ $woocommerce_events_booking_options['slot'] ]['formatted_time'];
				$formatted_time                  = $fooevents_bookings_options[ $woocommerce_events_booking_options['slot'] ]['hour'] . ':' . $fooevents_bookings_options[ $woocommerce_events_booking_options['slot'] ]['minute'] . ' ' . str_replace( '.', '', $fooevents_bookings_options[ $woocommerce_events_booking_options['slot'] ]['period'] );

			} else {

				$woocommerce_events_booking_slot = $fooevents_bookings_options[ $woocommerce_events_booking_options['slot'] ]['label'];

			}

			$woocommerce_events_booking_date = $fooevents_bookings_options[ $woocommerce_events_booking_options['slot'] ]['add_date'][ $woocommerce_events_booking_options['date'] ]['date'];

			if ( ! empty( $post_id ) ) {

				$bookings_date_time = '';
				if ( 'd/m/Y' === $wp_date_format ) {

					$bookings_date_time = str_replace( '/', '-', $woocommerce_events_booking_date );

				} else {

					$bookings_date_time = $woocommerce_events_booking_date;

				}

				$bookings_date_time                        = str_replace( ',', '', $bookings_date_time );
				$bookings_date_time                        = $this->convert_month_to_english( $bookings_date_time );
				$woocommerce_events_booking_date_timestamp = strtotime( $bookings_date_time . ' ' . $formatted_time );
				$mysql_date                                = date( 'Y-m-d H:i:s', $woocommerce_events_booking_date_timestamp );

				update_post_meta( $post_id, 'WooCommerceEventsBookingSlot', $woocommerce_events_booking_slot );
				update_post_meta( $post_id, 'WooCommerceEventsBookingSlotID', $woocommerce_events_booking_options['slot'] );
				update_post_meta( $post_id, 'WooCommerceEventsBookingDate', $woocommerce_events_booking_date );
				update_post_meta( $post_id, 'WooCommerceEventsBookingDateTimestamp', $woocommerce_events_booking_date_timestamp );
				update_post_meta( $post_id, 'WooCommerceEventsBookingDateMySQLFormat', $mysql_date );
				update_post_meta( $post_id, 'WooCommerceEventsBookingDateID', $woocommerce_events_booking_options['date'] );

				// Hook create booking
				do_action( 'fooevents_create_booking', $post_id );

			} else {

				return array(
					'slot'    => $woocommerce_events_booking_slot,
					'date'    => $woocommerce_events_booking_date,
					'slot_id' => $woocommerce_events_booking_options['slot'],
					'date_id' => $woocommerce_events_booking_options['date'],
				);

			}
		}
	}

	/**
	 * Display booking details on ticket detail page
	 *
	 * @param object $post post.
	 */
	public function display_booking_info( $post ) {

		$woocommerce_events_booking_date = get_post_meta( $post->ID, 'WooCommerceEventsBookingDate', true );
		$woocommerce_events_booking_slot = get_post_meta( $post->ID, 'WooCommerceEventsBookingSlot', true );
		$product_id                      = get_post_meta( $post->ID, 'WooCommerceEventsProductID', true );

		$bookings_date_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsDateOverride', true );
		$bookings_slot_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsSlotOverride', true );

		$date_label = '';
		$slot_label = '';

		if ( empty( $bookings_date_term ) ) {

			$date_label = __( 'Date:', 'fooevents-bookings' );

		} else {

			$date_label = $bookings_date_term;

		}

		if ( empty( $bookings_slot_term ) ) {

			$slot_label = __( 'Slot:', 'fooevents-bookings' );

		} else {

			$slot_label = $bookings_date_term;

		}

		if ( ! empty( $woocommerce_events_booking_date ) ) {

			require $this->config->template_path . 'bookings-ticket-detail.php';

		}
	}

	/**
	 * Displays event booking term options
	 *
	 * @param object $post post.
	 * @param array  $zoom_meetings Zoom meetings.
	 * @param array  $zoom_webinars Zoom webinars.
	 * @return string
	 */
	public function generate_bookings_term_options( $post, $zoom_meetings = array(), $zoom_webinars = array() ) {

		ob_start();

		$woocommerce_events_bookings_date_override                   = get_post_meta( $post->ID, 'WooCommerceEventsBookingsDateOverride', true );
		$woocommerce_events_bookings_date_override_plural            = get_post_meta( $post->ID, 'WooCommerceEventsBookingsDateOverridePlural', true );
		$woocommerce_events_bookings_slot_override                   = get_post_meta( $post->ID, 'WooCommerceEventsBookingsSlotOverride', true );
		$woocommerce_events_bookings_slot_override_plural            = get_post_meta( $post->ID, 'WooCommerceEventsBookingsSlotOverridePlural', true );
		$woocommerce_events_bookings_booking_details_override        = get_post_meta( $post->ID, 'WooCommerceEventsBookingsBookingDetailsOverride', true );
		$woocommerce_events_bookings_booking_details_override_plural = get_post_meta( $post->ID, 'WooCommerceEventsBookingsBookingDetailsOverridePlural', true );

		require $this->config->template_path . 'bookings-term-options.php';

		$bookings_term_options = ob_get_clean();

		return $bookings_term_options;
	}

		/**
		 * Displays event booking expiration options
		 *
		 * @param object $post post.
		 * @return string
		 */
	public function generate_bookings_expiration_options( $post ) {

		ob_start();

		$woocommerce_events_bookings_expire_passed_date = get_post_meta( $post->ID, 'WooCommerceEventsBookingsExpirePassedDate', true );
		$woocommerce_events_bookings_expire_value       = get_post_meta( $post->ID, 'WooCommerceEventsBookingsExpireValue', true );
		$woocommerce_events_bookings_expire_unit        = get_post_meta( $post->ID, 'WooCommerceEventsBookingsExpireUnit', true );
		require $this->config->template_path . 'bookings-expiration-options.php';

		$bookings_expiration_options = ob_get_clean();

		return $bookings_expiration_options;
	}

	/**
	 * Formats bookings fields for CSV
	 *
	 * @param int $ticket_id ticket ID.
	 * @param int $product_id product ID.
	 * @return array
	 */
	public function display_bookings_options_array( $ticket_id, $product_id ) {

		$fooevents_bookings_options_serialized = get_post_meta( $product_id, 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );

		$woocommerce_events_booking_date_id = get_post_meta( $ticket_id, 'WooCommerceEventsBookingDateID', true );
		$woocommerce_events_booking_slot_id = get_post_meta( $ticket_id, 'WooCommerceEventsBookingSlotID', true );

		$woocommerce_events_booking_slot = '';
		if ( isset( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_time'] ) && 'enabled' === $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_time'] ) {

			$woocommerce_events_booking_slot = $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['label'] . ' ' . $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['formatted_time'];

		} else {

			$woocommerce_events_booking_slot = $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['label'];

		}

		$returned_bookings_options = array();

		$returned_bookings_options['Bookings Slot'] = $woocommerce_events_booking_slot;
		$returned_bookings_options['Bookings Date'] = $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ]['date'];

		return $returned_bookings_options;
	}

	/**
	 * Adds column content to the event ticket custom post type.
	 *
	 * @param string $column column.
	 * @global object $post post.
	 * @global object $woocommerce WooCommerce.
	 */
	public function add_admin_column_content( $column ) {

		global $post;
		global $woocommerce;

		if ( 'Bookings' === $column ) {

			$woocommerce_events_booking_date_id = get_post_meta( $post->ID, 'WooCommerceEventsBookingDateID', true );
			$woocommerce_events_booking_slot_id = get_post_meta( $post->ID, 'WooCommerceEventsBookingSlotID', true );
			$product_id                         = get_post_meta( $post->ID, 'WooCommerceEventsProductID', true );

			$fooevents_bookings_options_serialized = get_post_meta( $product_id, 'fooevents_bookings_options_serialized', true );
			$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
			$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );

			$woocommerce_events_booking_slot = '';
			if ( ! empty( $fooevents_bookings_options ) && ! empty( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ] ) && ! empty( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ] ) ) {

				$woocommerce_events_booking_slot = '';

				if ( isset( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_time'] ) && 'enabled' === $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_time'] ) {

					$woocommerce_events_booking_slot = $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['label'] . ' ' . $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['formatted_time'];

				} elseif ( isset( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['label'] ) ) {

					$woocommerce_events_booking_slot = $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['label'];

				}

				$woocommerce_events_booking_date = '';
				if ( isset( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ]['date'] ) ) {

					$woocommerce_events_booking_date = $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ]['date'];

				}
			}

			$bookings_date_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsDateOverride', true );
			$bookings_slot_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsSlotOverride', true );

			$date_label = '';
			$slot_label = '';

			if ( empty( $bookings_date_term ) ) {

				$date_label = __( 'Date:', 'fooevents-bookings' );

			} else {

				$date_label = $bookings_date_term;

			}

			if ( empty( $bookings_slot_term ) ) {

				$slot_label = __( 'Slot:', 'fooevents-bookings' );

			} else {

				$slot_label = $bookings_date_term;

			}

			if ( ! empty( $woocommerce_events_booking_slot ) ) {
				echo '<b>' . esc_attr( $slot_label ) . '</b> ' . esc_attr( $woocommerce_events_booking_slot ) . '<br />';
			}
			if ( ! empty( $woocommerce_events_booking_date ) ) {
				echo '<b>' . esc_attr( $date_label ) . '</b> ' . esc_attr( $woocommerce_events_booking_date );
			}
		}
	}

	/**
	 * Retrieves bookings data for ICS generation
	 *
	 * @param int $product_id product ID.
	 * @param int $ticket_id ticket ID.
	 * @return array
	 */
	public function get_ics_data( $product_id, $ticket_id ) {

		$fooevents_bookings_options_serialized = get_post_meta( $product_id, 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );

		$woocommerce_events_booking_date_id = get_post_meta( $ticket_id, 'WooCommerceEventsBookingDateID', true );
		$woocommerce_events_booking_slot_id = get_post_meta( $ticket_id, 'WooCommerceEventsBookingSlotID', true );

		$processed_ics_data = array(
			'WooCommerceEventsBookingDateID' => $woocommerce_events_booking_date_id,
			'WooCommerceEventsBookingSlotID' => $woocommerce_events_booking_slot_id,
		);

		if ( ! empty( $woocommerce_events_booking_date_id ) && ! empty( $woocommerce_events_booking_slot_id ) && isset( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ] ) ) {

			if ( isset( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['zoom_id'] ) && 'enabled' === $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['zoom_id'] ) {

				$processed_ics_data['OffsetEndHours']   = (int) get_post_meta( $product_id, 'WooCommerceEventsZoomDurationHour', true );
				$processed_ics_data['OffsetEndMinutes'] = (int) get_post_meta( $product_id, 'WooCommerceEventsZoomDurationMinute', true );

			}

			$processed_ics_data['WooCommerceEventsEvent'] = get_post_meta( $product_id, 'WooCommerceEventsEvent', true );

			$processed_ics_data['WooCommerceEventsDate'] = '';

			if ( isset( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ]['date'] ) ) {

				$processed_ics_data['WooCommerceEventsDate'] = $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ]['date'];

			}

			$processed_ics_data['WooCommerceEventsEndDate'] = '';

			$processed_ics_data['WooCommerceEventsHour'] = '';
			if ( isset( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['hour'] ) ) {

				$processed_ics_data['WooCommerceEventsHour']    = $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['hour'];
				$processed_ics_data['WooCommerceEventsHourEnd'] = $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['hour'];

			}

			$processed_ics_data['WooCommerceEventsMinutes'] = '';
			if ( isset( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['minute'] ) ) {

				$processed_ics_data['WooCommerceEventsMinutes']    = $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['minute'];
				$processed_ics_data['WooCommerceEventsMinutesEnd'] = $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['minute'];

			}

			$processed_ics_data['WooCommerceEventsPeriod'] = '';
			if ( isset( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['period'] ) ) {

				$processed_ics_data['WooCommerceEventsPeriod']    = $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['period'];
				$processed_ics_data['WooCommerceEventsEndPeriod'] = $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['period'];

			}

			$processed_ics_data['WooCommerceEventsLocation']                   = get_post_meta( $product_id, 'WooCommerceEventsLocation', true );
			$processed_ics_data['WooCommerceEventsTimeZone']                   = get_post_meta( $product_id, 'WooCommerceEventsTimeZone', true );
			$processed_ics_data['WooCommerceEventsTicketText']                 = get_post_meta( $product_id, 'WooCommerceEventsTicketText', true );
			$processed_ics_data['WooCommerceEventsEmail']                      = get_post_meta( $product_id, 'WooCommerceEventsEmail', true );
			$processed_ics_data['WooCommerceEventsTicketDisplayZoom']          = get_post_meta( $product_id, 'WooCommerceEventsTicketDisplayZoom', true );
			$processed_ics_data['WooCommerceEventsTicketAddCalendarReminders'] = get_post_meta( $product_id, 'WooCommerceEventsTicketAddCalendarReminders', true );

			$processed_ics_data['WooCommerceEventsAttendeeName']     = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeName', true );
			$processed_ics_data['WooCommerceEventsAttendeeLastName'] = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeLastName', true );
			$processed_ics_data['WooCommerceEventsAttendeeEmail']    = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeEmail', true );
			$processed_ics_data['WooCommerceEventsTicketID']         = get_post_meta( $ticket_id, 'WooCommerceEventsTicketID', true );

		}

		return $processed_ics_data;
	}

	/**
	 * Process booking options
	 *
	 * @param array $fooevents_bookings_options booking options.
	 * @return array
	 */
	public function process_booking_options( $fooevents_bookings_options ) {

		$processed_fooevents_bookings_options = array();

		if ( ! empty( $fooevents_bookings_options ) ) {

			foreach ( $fooevents_bookings_options as $options_key => $options ) {

				$processed_fooevents_bookings_options[ $options_key ] = array();

				foreach ( $options as $k => $v ) {

					if ( strpos( $k, '_add_date' ) !== false ) {

						$date_id = str_replace( '_add_date', '', $k );
						$processed_fooevents_bookings_options[ $options_key ]['add_date'][ $date_id ]['date'] = $v;

					} elseif ( strpos( $k, '_zoom_id' ) !== false ) {

						$date_id = str_replace( '_zoom_id', '', $k );
						$processed_fooevents_bookings_options[ $options_key ]['add_date'][ $date_id ]['zoom_id'] = $v;

					} elseif ( strpos( $k, '_stock' ) !== false ) {

						$date_id = str_replace( '_stock', '', $k );
						$processed_fooevents_bookings_options[ $options_key ]['add_date'][ $date_id ]['stock'] = $v;

					} elseif ( 'add_time' === $k && 'enabled' === $v && isset( $processed_fooevents_bookings_options[ $options_key ]['hour'] ) && isset( $processed_fooevents_bookings_options[ $options_key ]['minute'] ) ) {

						$formatted_period = '';
						if ( isset( $processed_fooevents_bookings_options[ $options_key ]['period'] ) && ! empty( $processed_fooevents_bookings_options[ $options_key ]['period'] ) ) {

							$formatted_period = ' ' . $processed_fooevents_bookings_options[ $options_key ]['period'];

						}

						$processed_fooevents_bookings_options[ $options_key ]['formatted_time']   = '(' . $processed_fooevents_bookings_options[ $options_key ]['hour'] . ':' . $processed_fooevents_bookings_options[ $options_key ]['minute'] . $formatted_period . ')';
						$processed_fooevents_bookings_options[ $options_key ]['unformatted_time'] = $processed_fooevents_bookings_options[ $options_key ]['hour'] . ':' . $processed_fooevents_bookings_options[ $options_key ]['minute'] . $formatted_period;

						$processed_fooevents_bookings_options[ $options_key ]['add_time'] = 'enabled';

					} else {

						$processed_fooevents_bookings_options[ $options_key ][ $k ] = $v;

					}
				}
			}
		}

		return $processed_fooevents_bookings_options;
	}

	/**
	 * Checks if all slots have only one date
	 *
	 * @param array $fooevents_bookings_options booking options.
	 * @return bool
	 */
	private function check_if_slot_has_one_date_only( $fooevents_bookings_options ) {

		$return_val = true;
		$num_slots  = count( $fooevents_bookings_options );

		foreach ( $fooevents_bookings_options as $option ) {

			$x = 0;
			if ( ! empty( $option['add_date'] ) ) {

				foreach ( $option['add_date'] as $date ) {

					++$x;

				}
			}

			if ( $x > 1 || 1 === $num_slots ) {

				$return_val = false;

			}
		}

		return $return_val;
	}

	/**
	 * Check if there is only one booking slot
	 *
	 * @param array $fooevents_bookings_options booking options.
	 * @return bool
	 */
	private function check_if_only_one_slot( $fooevents_bookings_options ) {

		$return_val = true;

		$num_slots = count( $fooevents_bookings_options );

		if ( $num_slots > 1 ) {

			$return_val = false;

		}

		return $return_val;
	}

	/**
	 * Output booking fields on product page slot/date format
	 *
	 * @param object $post post.
	 * @param object $product product.
	 * @param string $woocommerce_events_bookings_expire_passed_date expire passed date.
	 * @param string $woocommerce_events_hide_bookings_display_time display time.
	 * @param string $woocommerce_events_hide_bookings_stock_availability stock availability.
	 * @param string $format date format.
	 * @param string $wordpress_timezone WordPress timezone.
	 * @param string $woocommerce_events_timezone events timezone.
	 * @param string $fooevents_bookings_options_serialized bookings options serialized.
	 * @param string $woocommerce_events_view_bookings_stock_dropdowns view bookings stock in dropdowns.
	 * @param string $woocommerce_events_view_out_of_stock_bookings view out of stock booking items in dropdown.
	 * @param string $woocommerce_events_bookings_hide_date_single_dropdown hide date single dropdown.
	 * @param string $fooevents_bookings_options_raw booking options raw.
	 * @param string $fooevents_bookings_options bookings options.
	 * @param bool   $each_slot_one_date each slot one date.
	 * @param bool   $one_slot_multiple_dates one slot multiple dates.
	 * @param int    $selected_slot_id slot ID.
	 * @param int    $selected_slot_id_post_id slot ID post ID.
	 * @param int    $selected_date_id date ID.
	 * @param int    $selected_slot_id_date_id slot ID date ID.
	 */
	public function output_booking_fields_product_slot_date( $post, $product, $woocommerce_events_bookings_expire_passed_date, $woocommerce_events_bookings_expire_value, $woocommerce_events_bookings_expire_unit, $woocommerce_events_hide_bookings_display_time, $woocommerce_events_hide_bookings_stock_availability, $format, $wordpress_timezone, $woocommerce_events_timezone, $fooevents_bookings_options_serialized, $woocommerce_events_view_bookings_stock_dropdowns, $woocommerce_events_view_out_of_stock_bookings, $woocommerce_events_bookings_hide_date_single_dropdown, $fooevents_bookings_options_raw, $fooevents_bookings_options, $each_slot_one_date, $one_slot_multiple_dates, $selected_slot_id, $selected_slot_id_post_id, $selected_date_id, $selected_slot_id_date_id ) {

		$woocommerce_events_type = get_post_meta( $post->ID, 'WooCommerceEventsType', true );
		$bookings_slot_term      = get_post_meta( $post->ID, 'WooCommerceEventsBookingsSlotOverride', true );
		$bookings_slot_term      = get_post_meta( $post->ID, 'WooCommerceEventsBookingsSlotOverride', true );
		$date_label              = '';
		$slot_label              = '';

		$today = current_time( 'timestamp' );

		if ( empty( $bookings_date_term ) ) {

			$date_label = __( 'Date', 'fooevents-bookings' );

		} else {

			$date_label = $bookings_date_term;

		}

		if ( empty( $bookings_slot_term ) ) {

			$slot_label = __( 'Slot', 'fooevents-bookings' );

		} else {

			$slot_label = $bookings_slot_term;

		}

		$bookings_booking_details_term = get_post_meta( $post->ID, 'WooCommerceEventsBookingsBookingDetailsOverride', true );

		if ( empty( $bookings_booking_details_term ) ) {

			$booking_details_label = __( 'Booking details', 'fooevents-bookings' );

		} else {

			$booking_details_label = $bookings_booking_details_term;

		}

		if ( ! empty( $fooevents_bookings_options ) && 'bookings' === $woocommerce_events_type ) {

			echo '<input type="hidden" id="fooevents-bookings-stock" class="fooevents-bookings-stock">';

			if ( ! $each_slot_one_date && ! $one_slot_multiple_dates ) {

				if ( 'on' !== $woocommerce_events_hide_bookings_stock_availability ) {

					echo '<div id="fooevents-checkout-attendee-info-val-trans" class="fooevents-checkout-attendee-info"></div>';

				}

				$bookings_date_term = get_post_meta( $post->ID, 'WooCommerceEventsBookingsDateOverride', true );
				$bookings_slot_term = get_post_meta( $post->ID, 'WooCommerceEventsBookingsSlotOverride', true );

				if ( empty( $bookings_slot_term ) ) {

					$slot_select_label = __( 'Slot', 'fooevents-bookings' );

				} else {

					$slot_select_label = $bookings_slot_term;

				}

				if ( empty( $bookings_date_term ) ) {

					$date_select_label = __( 'Date', 'fooevents-bookings' );

				} else {

					$date_select_label = $bookings_date_term;

				}

				// translators: Placeholder is for the date label.
				$select_values_date = array( '' => sprintf( __( 'Select %s', 'fooevents-bookings' ), $date_select_label ) );
				// translators: Placeholder is for the slot label.
				$select_values_slot = array( '' => sprintf( __( 'Select %s', 'fooevents-bookings' ), $slot_select_label ) );

				$z = 1;
				foreach ( $fooevents_bookings_options as $option_key => $option ) {

					if ( isset( $option['add_date'] ) ) {

						if ( 'yes' === $woocommerce_events_bookings_expire_passed_date ) {

							$upcoming_dates = 0;
							foreach ( $option['add_date'] as $k => $date ) {

								if ( ! empty( $date['date'] ) ) {

									$date_to_compare = '';
									if ( isset( $option['hour'] ) && ! empty( $option['hour'] ) && isset( $option['minute'] ) && ! empty( $option['minute'] ) && isset( $option['period'] ) && ! empty( $option['period'] ) ) {

										$date_to_compare = $date['date'] . ' ' . $option['hour'] . ':' . $option['minute'] . $option['period'];

									} elseif ( isset( $option['hour'] ) && ! empty( $option['hour'] ) && isset( $option['minute'] ) && ! empty( $option['minute'] ) ) {

										$date_to_compare = $date['date'] . ' ' . $option['hour'] . ':' . $option['minute'];

									} else {

										$date_to_compare = $date['date'];

									}

									$expire_date = sanitize_text_field( $date_to_compare );

									if ( ! empty( $woocommerce_events_timezone ) && $wordpress_timezone !== $woocommerce_events_timezone ) {

										$date_time = new DateTime( '@' . $today );
										$timezone  = new DateTimeZone( $woocommerce_events_timezone );
										$date_time->setTimezone( $timezone );
										$today = $date_time->format( 'U' );

									}

									if ( 'd/m/Y' === $format ) {

										$expire_date = str_replace( '/', '-', $expire_date );

									}

									$expire_date = str_replace( ',', '', $expire_date );
									$expire_date = $this->convert_month_to_english( $expire_date );

									$timestamp = strtotime( $expire_date );

									if ( ! empty( $woocommerce_events_bookings_expire_value ) && ! empty( $woocommerce_events_bookings_expire_unit ) && ! empty( $timestamp ) ) {

										$timestamp = strtotime( '-' . $woocommerce_events_bookings_expire_value . ' ' . $woocommerce_events_bookings_expire_unit, $timestamp );

									}

									if ( $today < $timestamp ) {

										++$upcoming_dates;

									}
								}
							}

							// All dates have expired.
							if ( 0 === $upcoming_dates ) {

								continue;

							}
						}

						if ( ! empty( $selected_slot_id ) && $option_key === $selected_slot_id ) {

							foreach ( $option['add_date'] as $k => $date ) {

								$val = $date['date'];

								if ( 'on' === $woocommerce_events_view_bookings_stock_dropdowns ) {

									$stock = $date['stock'];

									if ( '' === $stock ) {

										$stock = __( 'Unlimited', 'fooevents-bookings' );

									}

									$val .= ' (' . $stock . ')';

								}

								$select_values_date[ $k ] = $val;

							}
						}

						if ( isset( $option['add_date'] ) ) {

							$val = '';
							if ( isset( $option['add_time'] ) && 'enabled' === $option['add_time'] && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

								$val = $option['label'] . ' ' . $option['formatted_time'];

							} else {

								$val = $option['label'];

							}

							$select_values_slot[ $option_key . '_' . $post->ID ] = $val;

						}

						$slot_field_params = array(
							'type'         => 'select',
							'class'        => array( 'attendee-class form-row-wide fooevents-bookings-slot' ),
							'label'        => $booking_details_label,
							'placeholder'  => '',
							'options'      => $select_values_slot,
							'autocomplete' => 'off',
							'required'     => true,
						);

						$date_field_params = array(
							'type'         => 'select',
							'class'        => array( 'attendee-class form-row-wide fooevents-bookings-date' ),
							'label'        => '',
							'placeholder'  => '',
							'options'      => $select_values_date,
							'autocomplete' => 'off',
							'required'     => true,
						);

						++$z;

					}
				}

				if ( ! empty( $slot_field_params ) ) {

					woocommerce_form_field( 'fooevents_bookings_slot_val__trans', $slot_field_params, $selected_slot_id_post_id );
					woocommerce_form_field( 'fooevents_bookings_date_val__trans', $date_field_params, $selected_date_id );

				}
			} elseif ( $each_slot_one_date && ! $one_slot_multiple_dates ) {

				if ( 'on' !== $woocommerce_events_hide_bookings_stock_availability ) {

					echo '<div id="fooevents-checkout-attendee-info-val-field" class="fooevents-checkout-attendee-info"></div>';

				}

				// translators: Placeholder is for the slot label.
				$select_values_slot_date = array( '' => sprintf( __( 'Select %s', 'fooevents-bookings' ), $slot_label ) );

				foreach ( $fooevents_bookings_options as $option_key => $option ) {

					if ( isset( $option['add_date'] ) ) {

						$display_slot = true;
						if ( 'yes' === $woocommerce_events_bookings_expire_passed_date ) {

							foreach ( $option['add_date'] as $k => $date ) {

								if ( ! empty( $date['date'] ) ) {

									$date_to_compare = '';
									if ( isset( $option['hour'] ) && ! empty( $option['hour'] ) && isset( $option['minute'] ) && ! empty( $option['minute'] ) && isset( $option['period'] ) && ! empty( $option['period'] ) ) {

										$date_to_compare = $date['date'] . ' ' . $option['hour'] . ':' . $option['minute'] . $option['period'];

									} elseif ( isset( $option['hour'] ) && ! empty( $option['hour'] ) && isset( $option['minute'] ) && ! empty( $option['minute'] ) ) {

										$date_to_compare = $date['date'] . ' ' . $option['hour'] . ':' . $option['minute'];

									} else {

										$date_to_compare = $date['date'];

									}

									$expire_date = sanitize_text_field( $date_to_compare );

									if ( ! empty( $woocommerce_events_timezone ) && $wordpress_timezone !== $woocommerce_events_timezone ) {

										$date_time = new DateTime( '@' . $today );
										$timezone  = new DateTimeZone( $woocommerce_events_timezone );
										$date_time->setTimezone( $timezone );
										$today = $date_time->format( 'U' );

									}

									if ( 'd/m/Y' === $format ) {

										$expire_date = str_replace( '/', '-', $expire_date );

									}

									$expire_date = str_replace( ',', '', $expire_date );
									$expire_date = $this->convert_month_to_english( $expire_date );

									$timestamp = strtotime( $expire_date );

									if ( ! empty( $woocommerce_events_bookings_expire_value ) && ! empty( $woocommerce_events_bookings_expire_unit ) && ! empty( $timestamp ) ) {

										$timestamp = strtotime( '-' . $woocommerce_events_bookings_expire_value . ' ' . $woocommerce_events_bookings_expire_unit, $timestamp );

									}

									if ( $today > $timestamp ) {

										$display_slot = false;

									}
								}
							}

							// Date has expired.
							if ( ! $display_slot ) {

								continue;

							}
						}

						$val = $option['label'];
						$select_values_slot[ $option_key . '_' . $post->ID ] = $val;

						foreach ( $option['add_date'] as $date_key => $date ) {

							$stock = $date['stock'];

							if ( empty( $date['stock'] ) || $date['stock'] < 0 ) {

								$stock = 'Unlimited';

							}

							if ( strval( $date['stock'] ) === '0' ) {

								$stock = 0;

							}

							$val = '';
							if ( isset( $option['add_time'] ) && 'enabled' === $option['add_time'] && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

								$val = $option['label'] . ' ' . $option['formatted_time'];

								if ( ! empty( $date['date'] ) ) {

									if ( ! empty( $val ) && 'on' !== $woocommerce_events_bookings_hide_date_single_dropdown ) {

										$val .= ' - ';

									}

									if ( 'on' !== $woocommerce_events_bookings_hide_date_single_dropdown ) {

										$val .= $date['date'];

									}
								}
							} else {

								$val = $option['label'];

								if ( ! empty( $date['date'] ) ) {

									if ( ! empty( $val ) && 'on' !== $woocommerce_events_bookings_hide_date_single_dropdown ) {

										$val .= ' - ';

									}

									if ( 'on' !== $woocommerce_events_bookings_hide_date_single_dropdown ) {

										$val .= $date['date'];

									}
								}
							}

							if ( 'on' === $woocommerce_events_view_bookings_stock_dropdowns ) {

								$val .= ' (' . $stock . ')';

							}

							if ( ( 'on' === $woocommerce_events_view_out_of_stock_bookings && strval( $date['stock'] ) === '0' ) || strval( $date['stock'] ) !== '0' ) {

								$select_values_slot_date[ $option_key . '_' . $date_key . '_' . $post->ID ] = $val;

							}
						}
					}
				}

				$slot_date_field_params = array(
					'type'        => 'select',
					'class'       => array( 'attendee-class form-row-wide fooevents-bookings-slot-date' ),
					'label'       => $booking_details_label,
					'placeholder' => '',
					'options'     => $select_values_slot_date,
					'required'    => true,
				);

				woocommerce_form_field( 'fooevents_bookings_slot_date_val_trans', $slot_date_field_params, $selected_slot_id_date_id );

			} elseif ( ! $each_slot_one_date && $one_slot_multiple_dates ) {

				if ( 'on' !== $woocommerce_events_hide_bookings_stock_availability ) {

					echo '<div id="fooevents-checkout-attendee-info-val-trans" class="fooevents-checkout-attendee-info"></div>';

				}

				// translators: Placeholder is for the date label.
				$select_values_date = array( '' => sprintf( __( 'Select %s', 'fooevents-bookings' ), $date_label ) );

				$only_slot = key( $fooevents_bookings_options );

				foreach ( $fooevents_bookings_options[ $only_slot ]['add_date'] as $option_key => $option ) {

					if ( isset( $option['date'] ) ) {

						$display_slot = true;
						if ( 'yes' === $woocommerce_events_bookings_expire_passed_date ) {

							$date_to_compare = '';
							if ( isset( $fooevents_bookings_options[ $only_slot ]['hour'] ) && ! empty( $fooevents_bookings_options[ $only_slot ]['hour'] ) && isset( $fooevents_bookings_options[ $only_slot ]['minute'] ) && ! empty( $fooevents_bookings_options[ $only_slot ]['minute'] ) && isset( $fooevents_bookings_options[ $only_slot ]['period'] ) && ! empty( $fooevents_bookings_options[ $only_slot ]['period'] ) ) {

								$date_to_compare = $option['date'] . ' ' . $fooevents_bookings_options[ $only_slot ]['hour'] . ':' . $fooevents_bookings_options[ $only_slot ]['minute'] . $fooevents_bookings_options[ $only_slot ]['period'];

							} elseif ( isset( $fooevents_bookings_options[ $only_slot ]['hour'] ) && ! empty( $fooevents_bookings_options[ $only_slot ]['hour'] ) && isset( $fooevents_bookings_options[ $only_slot ]['minute'] ) && ! empty( $fooevents_bookings_options[ $only_slot ]['minute'] ) ) {

								$date_to_compare = $option['date'] . ' ' . $fooevents_bookings_options[ $only_slot ]['hour'] . ':' . $fooevents_bookings_options[ $only_slot ]['minute'];

							} else {

								$date_to_compare = $option['date'];

							}

							$expire_date = sanitize_text_field( $date_to_compare );

							if ( ! empty( $woocommerce_events_timezone ) && $wordpress_timezone !== $woocommerce_events_timezone ) {

								$date_time = new DateTime( '@' . $today );
								$timezone  = new DateTimeZone( $woocommerce_events_timezone );
								$date_time->setTimezone( $timezone );
								$today = $date_time->format( 'U' );

							}

							if ( 'd/m/Y' === $format ) {

								$expire_date = str_replace( '/', '-', $expire_date );

							}

							$expire_date = str_replace( ',', '', $expire_date );
							$expire_date = $this->convert_month_to_english( $expire_date );

							$timestamp = strtotime( $expire_date );

							if ( ! empty( $woocommerce_events_bookings_expire_value ) && ! empty( $woocommerce_events_bookings_expire_unit ) && ! empty( $timestamp ) ) {

								$timestamp = strtotime( '-' . $woocommerce_events_bookings_expire_value . ' ' . $woocommerce_events_bookings_expire_unit, $timestamp );

							}

							if ( $today > $timestamp ) {

								$display_slot = false;

							}

							// Date has expired.
							if ( ! $display_slot ) {

								continue;

							}
						}

						$stock = $option['stock'];

						if ( empty( $option['stock'] ) || $option['stock'] < 0 ) {

							$stock = __( 'Unlimited', 'fooevents-bookings' );

						}

						if ( strval( $option['stock'] ) === '0' ) {

							$stock = 0;

						}

						if ( ( 'on' === $woocommerce_events_view_out_of_stock_bookings && strval( $option['stock'] ) === '0' ) || strval( $option['stock'] ) !== '0' ) {

							$val = $option['date'];

							if ( isset( $fooevents_bookings_options[ $only_slot ]['add_time'] ) && 'enabled' === $fooevents_bookings_options[ $only_slot ]['add_time'] && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

								$val .= ' ' . $fooevents_bookings_options[ $only_slot ]['formatted_time'];

							}

							if ( 'on' === $woocommerce_events_view_bookings_stock_dropdowns ) {

								$val .= ' (' . $stock . ')';

							}

							$select_values_date[ $option_key ] = $val;

						}
					}
				}

				$date_field_params = array(
					'type'        => 'select',
					'class'       => array( 'attendee-class form-row-wide fooevents-bookings-date' ),
					'label'       => $booking_details_label,
					'placeholder' => '',
					'options'     => $select_values_date,
					'required'    => true,
				);

				echo '<input type="hidden" id="fooevents_bookings_slot_val__trans"  name="fooevents_bookings_slot_val__trans" value="' . esc_attr( $only_slot ) . '_' . esc_attr( $post->ID ) . '">';
				woocommerce_form_field( 'fooevents_bookings_date_val__trans', $date_field_params, $selected_date_id );

			}

			$bookings_options_selected = false;
			if ( isset( $_GET['bookings_sid'] ) && isset( $_GET['bookings_did'] ) ) {

				$bookings_options_selected = true;

			} elseif ( isset( $_POST['fooevents_bookings_slot_val__trans'] ) && ! empty( $_POST['fooevents_bookings_slot_val__trans'] ) && 'slotdate' === $_POST['fooevents_bookings_method'] ) {

				$bookings_options_selected = true;

			} elseif ( isset( $_POST['fooevents_bookings_date_val__trans'] ) && ! empty( $_POST['fooevents_bookings_date_val__trans'] ) && 'slotdate' === $_POST['fooevents_bookings_method'] ) {

				$bookings_options_selected = true;

			} elseif ( isset( $_POST['fooevents_bookings_slot_date_val_trans'] ) && ! empty( $_POST['fooevents_bookings_slot_date_val_trans'] ) ) {

				$bookings_options_selected = true;

			}

			if ( true === $bookings_options_selected ) {

				echo '<input type="hidden" name="fooevents_calendar_selected" class="fooevents_calendar_selected" value="yes" />';

			}
		} else {

			echo '<input type="hidden" id="fooevents_bookings_slot_val__trans"  name="fooevents_bookings_slot_val__trans" value="">';

		}
	}

	/**
	 * Output booking fields on product page date/slot format
	 *
	 * @param object $post post.
	 * @param object $product product.
	 * @param string $woocommerce_events_bookings_expire_passed_date expire passed date.
	 * @param string $woocommerce_events_hide_bookings_display_time hide bookings display time.
	 * @param string $woocommerce_events_hide_bookings_stock_availability hide bookings stock availability.
	 * @param string $format hide bookings date format.
	 * @param string $wordpress_timezone WordPress timezone.
	 * @param string $woocommerce_events_timezone WooCommerce events timezone.
	 * @param string $fooevents_bookings_options_serialized bookings options serialized.
	 * @param string $woocommerce_events_view_bookings_stock_dropdowns view booking stock in dropdowns.
	 * @param string $woocommerce_events_view_out_of_stock_bookings view out of stock bookings.
	 * @param string $woocommerce_events_bookings_hide_date_single_dropdown hide date single drop down.
	 * @param string $fooevents_bookings_options_raw booking options raw.
	 * @param string $fooevents_bookings_options bookings options
	 * @param bool   $each_slot_one_date each slot one date.
	 * @param bool   $one_slot_multiple_dates each slot multiple dates.
	 * @param bool   $selected_slot_id each slot ID.
	 * @param bool   $selected_slot_id_post_id slot ID post ID.
	 * @param bool   $selected_date_id date ID.
	 * @param bool   $selected_slot_id_date_id slot ID date ID.
	 */
	public function output_booking_fields_product_date_slot( $post, $product, $woocommerce_events_bookings_expire_passed_date, $woocommerce_events_bookings_expire_value, $woocommerce_events_bookings_expire_unit, $woocommerce_events_hide_bookings_display_time, $woocommerce_events_hide_bookings_stock_availability, $format, $wordpress_timezone, $woocommerce_events_timezone, $fooevents_bookings_options_serialized, $woocommerce_events_view_bookings_stock_dropdowns, $woocommerce_events_view_out_of_stock_bookings, $woocommerce_events_bookings_hide_date_single_dropdown, $fooevents_bookings_options_raw, $fooevents_bookings_options, $each_slot_one_date, $one_slot_multiple_dates, $selected_slot_id, $selected_slot_id_post_id, $selected_date_id, $selected_slot_id_date_id ) {

		$fooevents_bookings_options_date_slot = $this->process_date_slot_bookings_options( $fooevents_bookings_options );

		$bookings_date_term = get_post_meta( $post->ID, 'WooCommerceEventsBookingsDateOverride', true );
		$bookings_slot_term = get_post_meta( $post->ID, 'WooCommerceEventsBookingsSlotOverride', true );
		$date_label         = '';
		$slot_label         = '';

		$today = current_time( 'timestamp' );

		$selected_date         = '';
		$selected_date_post_id = '';
		if ( isset( $fooevents_bookings_options[ $selected_slot_id ]['add_date'][ $selected_date_id ]['date'] ) && ! empty( $fooevents_bookings_options[ $selected_slot_id ]['add_date'][ $selected_date_id ]['date'] ) ) {

			$selected_date         = $fooevents_bookings_options[ $selected_slot_id ]['add_date'][ $selected_date_id ]['date'];
			$selected_date_post_id = $fooevents_bookings_options[ $selected_slot_id ]['add_date'][ $selected_date_id ]['date'] . '_' . $post->ID;

		}

		if ( empty( $bookings_date_term ) ) {

			$date_label = __( 'Date', 'fooevents-bookings' );

		} else {

			$date_label = $bookings_date_term;

		}

		if ( empty( $bookings_slot_term ) ) {

			$slot_label = __( 'Slot', 'fooevents-bookings' );

		} else {

			$slot_label = $bookings_date_term;

		}

		$bookings_booking_details_term = get_post_meta( $post->ID, 'WooCommerceEventsBookingsBookingDetailsOverride', true );

		if ( empty( $bookings_booking_details_term ) ) {

			$booking_details_label = __( 'Booking details', 'fooevents-bookings' );

		} else {

			$booking_details_label = $bookings_booking_details_term;

		}

		if ( ! empty( $fooevents_bookings_options_date_slot ) ) {

			echo '<input type="hidden" id="fooevents-bookings-stock" class="fooevents-bookings-stock">';

			if ( 'on' !== $woocommerce_events_hide_bookings_stock_availability ) {

				echo '<div id="fooevents-checkout-attendee-info-val-trans" class="fooevents-checkout-attendee-info"></div>';

			}

			$bookings_date_term = get_post_meta( $post->ID, 'WooCommerceEventsBookingsDateOverride', true );
			$bookings_slot_term = get_post_meta( $post->ID, 'WooCommerceEventsBookingsSlotOverride', true );

			if ( empty( $bookings_slot_term ) ) {

				$slot_select_label = __( 'Slot', 'fooevents-bookings' );

			} else {

				$slot_select_label = $bookings_slot_term;

			}

			if ( empty( $bookings_date_term ) ) {

				$date_select_label = __( 'Date', 'fooevents-bookings' );

			} else {

				$date_select_label = $bookings_date_term;

			}

			// translators: Placeholder is for the date label.
			$select_values_date = array( '' => sprintf( __( 'Select %s', 'fooevents-bookings' ), $date_select_label ) );
			// translators: Placeholder is for the slot label.
			$select_values_slot = array( '' => sprintf( __( 'Select %s', 'fooevents-bookings' ), $slot_select_label ) );

			$z = 1;

			foreach ( $fooevents_bookings_options_date_slot as $date => $option ) {

				foreach ( $option as $k => $ds ) {

					if ( $date === $selected_date ) {

						if ( ! empty( $ds['slot_time'] ) && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

							$ds['slot_label'] .= $ds['slot_time'];

						}

						$stock_output = '';
						if ( '' === $ds['stock'] ) {

							$stock_output = __( 'Unlimited', 'fooevents-bookings' );

						} else {

							$stock_output = $ds['stock'];

						}

						if ( 'on' === $woocommerce_events_view_bookings_stock_dropdowns ) {

							$ds['slot_label'] .= ' (' . $stock_output . ')';

						}

						$select_values_slot[ $ds['slot_id'] . '_' . $ds['date_id'] . '_' . $post->ID ] = $ds['slot_label'];

					}

					if ( 'yes' === $woocommerce_events_bookings_expire_passed_date ) {

						$date_to_compare = '';
						if ( isset( $ds['hour'] ) && ! empty( $ds['hour'] ) && isset( $ds['minute'] ) && ! empty( $ds['minute'] ) && isset( $ds['period'] ) && ! empty( $ds['period'] ) ) {

							$date_to_compare = $date . ' ' . $ds['hour'] . ':' . $ds['minute'] . $ds['period'];

						} elseif ( isset( $ds['hour'] ) && ! empty( $ds['hour'] ) && isset( $ds['minute'] ) && ! empty( $ds['minute'] ) ) {

							$date_to_compare = $date . ' ' . $ds['hour'] . ':' . $ds['minute'];

						} else {

							$date_to_compare = $date;

						}

						$date_has_expired = false;
						$expire_date      = sanitize_text_field( $date_to_compare );

						if ( ! empty( $woocommerce_events_timezone ) && $wordpress_timezone !== $woocommerce_events_timezone ) {

							$date_time = new DateTime( '@' . $today );
							$timezone  = new DateTimeZone( $woocommerce_events_timezone );
							$date_time->setTimezone( $timezone );
							$today = $date_time->format( 'U' );

						}

						if ( 'd/m/Y' === $format ) {

							$expire_date = str_replace( '/', '-', $expire_date );

						}

						$expire_date = str_replace( ',', '', $expire_date );
						$expire_date = $this->convert_month_to_english( $expire_date );

						$timestamp = strtotime( $expire_date );

						if ( ! empty( $woocommerce_events_bookings_expire_value ) && ! empty( $woocommerce_events_bookings_expire_unit ) && ! empty( $timestamp ) ) {

							$timestamp = strtotime( '-' . $woocommerce_events_bookings_expire_value . ' ' . $woocommerce_events_bookings_expire_unit, $timestamp );

						}

						if ( $today > $timestamp ) {

							$date_has_expired = true;

						}

						if ( true === $date_has_expired ) {

							continue;

						}
					}

					if ( $ds['stock'] > 0 || '' === $ds['stock'] ) {

						$select_values_date[ $date . '_' . $post->ID ] = $date;

					} elseif ( 'on' === $woocommerce_events_view_out_of_stock_bookings && 0 === (int) $ds['stock'] ) {

						$select_values_date[ $date . '_' . $post->ID ] = $date;

					}
				}
			}

			$date_field_params = array(
				'type'        => 'select',
				'class'       => array( 'attendee-class form-row-wide fooevents-bookings-date-slot-date' ),
				'label'       => $booking_details_label,
				'placeholder' => '',
				'options'     => $select_values_date,
				'required'    => true,
			);

			$slot_field_params = array(
				'type'        => 'select',
				'class'       => array( 'attendee-class form-row-wide fooevents-bookings-date-slot-slot' ),
				'label'       => '',
				'placeholder' => '',
				'options'     => $select_values_slot,
				'required'    => true,
			);

			if ( ! empty( $date_field_params ) ) {

				woocommerce_form_field( 'fooevents_bookings_date_val__trans', $date_field_params, $selected_date_post_id );
				woocommerce_form_field( 'fooevents_bookings_slot_val__trans', $slot_field_params, $selected_slot_id . '_' . $selected_date_id . '_' . $post->ID );

			}

			$bookings_options_selected = false;
			if ( isset( $_GET['bookings_sid'] ) && isset( $_GET['bookings_did'] ) ) {

				$bookings_options_selected = true;

			} elseif ( isset( $_POST['fooevents_bookings_slot_val__trans'] ) && ! empty( $_POST['fooevents_bookings_slot_val__trans'] ) && isset( $_POST['fooevents_bookings_date_val__trans'] ) && ! empty( $_POST['fooevents_bookings_date_val__trans'] ) && 'dateslot' == $_POST['fooevents_bookings_method'] ) {

				$bookings_options_selected = true;

			}

			if ( true === $bookings_options_selected ) {

				echo '<input type="hidden" name="fooevents_calendar_selected" class="fooevents_calendar_selected" value="yes" />';

			}
		} else {

			echo '<input type="hidden" id="fooevents_bookings_slot_val__trans"  name="fooevents_bookings_slot_val__trans" value="">';
			echo '<input type="hidden" id="fooevents_bookings_date_val__trans"  name="fooevents_bookings_date_val__trans" value="">';

		}
	}

	/**
	 * Add booking dropdown option to product page
	 *
	 * @param object $checkout checkout.
	 */
	public function output_booking_fields_product( $checkout ) {

		global $post;

		$woocommerce_events_view_bookings_options = get_post_meta( $post->ID, 'WooCommerceEventsViewBookingsOptions', true );
		$woocommerce_events_bookings_method       = get_post_meta( $post->ID, 'WooCommerceEventsBookingsMethod', true );

		$format             = get_option( 'date_format' );
		$wordpress_timezone = get_option( 'timezone_string' );

		$selected_slot_id         = '';
		$selected_slot_id_post_id = '';
		$selected_date_id         = '';
		$selected_slot_id_date_id = '';

		if ( isset( $_GET['bookings_sid'] ) && ! empty( $_GET['bookings_sid'] ) ) {

			$selected_slot_id         = sanitize_text_field( wp_unslash( $_GET['bookings_sid'] ) );
			$selected_slot_id_post_id = sanitize_text_field( wp_unslash( $_GET['bookings_sid'] . '_' . $post->ID ) );

		}

		if ( isset( $_POST['fooevents_bookings_slot_val__trans'] ) && ! empty( $_POST['fooevents_bookings_slot_val__trans'] ) && 'slotdate' === $_POST['fooevents_bookings_method'] ) {

			$selected_slot_id         = sanitize_text_field( wp_unslash( substr( $_POST['fooevents_bookings_slot_val__trans'], 0, 20 ) ) );
			$selected_slot_id_post_id = sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_slot_val__trans'] ) );

		}

		if ( isset( $_GET['bookings_did'] ) && ! empty( $_GET['bookings_did'] ) ) {

			$selected_date_id = sanitize_text_field( wp_unslash( $_GET['bookings_did'] ) );

		}

		if ( isset( $_POST['fooevents_bookings_date_val__trans'] ) && ! empty( $_POST['fooevents_bookings_date_val__trans'] ) && 'slotdate' === $_POST['fooevents_bookings_method'] ) {

			$selected_date_id = sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_date_val__trans'] ) );

		}

		if ( isset( $_GET['bookings_sid'] ) && ! empty( $_GET['bookings_sid'] ) && isset( $_GET['bookings_did'] ) && ! empty( $_GET['bookings_did'] ) ) {

			$selected_slot_id_date_id = sanitize_text_field( wp_unslash( $_GET['bookings_sid'] ) ) . '_' . sanitize_text_field( wp_unslash( $_GET['bookings_did'] ) ) . '_' . $post->ID;

		}

		if ( isset( $_POST['fooevents_bookings_slot_val__trans'] ) && ! empty( $_POST['fooevents_bookings_slot_val__trans'] ) && isset( $_POST['fooevents_bookings_date_val__trans'] ) && ! empty( $_POST['fooevents_bookings_date_val__trans'] ) && 'slotdate' === $_POST['fooevents_bookings_method'] ) {

			$selected_slot_id_date_id = sanitize_text_field( wp_unslash( substr( $_POST['fooevents_bookings_slot_val__trans'], 0, 20 ) ) ) . '_' . sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_date_val__trans'] ) ) . '_' . $post->ID;

		}

		if ( empty( $woocommerce_events_bookings_method ) || 1 === $woocommerce_events_bookings_method ) {

			$woocommerce_events_bookings_method = 'slotdate';

		}

		if ( isset( $_POST['fooevents_bookings_slot_date_val_trans'] ) && ! empty( $_POST['fooevents_bookings_slot_date_val_trans'] ) ) {

			$selected = explode( '_', sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_slot_date_val_trans'] ) ) );

			$selected_slot_id         = $selected[0];
			$selected_date_id         = $selected[1];
			$selected_slot_id_date_id = sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_slot_date_val_trans'] ) );

		}

		if ( isset( $_POST['fooevents_bookings_slot_val__trans'] ) && ! empty( $_POST['fooevents_bookings_slot_val__trans'] ) && isset( $_POST['fooevents_bookings_date_val__trans'] ) && ! empty( $_POST['fooevents_bookings_date_val__trans'] ) && 'dateslot' == $_POST['fooevents_bookings_method'] ) {

			$slot_data                = explode( '_', sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_slot_val__trans'] ) ) );
			$selected_slot_id         = $slot_data[0];
			$selected_date_id         = $slot_data[1];
			$selected_slot_id_date_id = sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_slot_val__trans'] ) );

		}

		// Option disabled via Event Settings option.
		if ( 'product' === $woocommerce_events_view_bookings_options || 'checkoutproduct' === $woocommerce_events_view_bookings_options || 'on' === $woocommerce_events_view_bookings_options ) {

			// Check for the custom field value.
			$product                                        = wc_get_product( $post->ID );
			$woocommerce_events_bookings_expire_passed_date = get_post_meta( $post->ID, 'WooCommerceEventsBookingsExpirePassedDate', true );
			$woocommerce_events_bookings_expire_value       = get_post_meta( $post->ID, 'WooCommerceEventsBookingsExpireValue', true );
			$woocommerce_events_bookings_expire_unit        = get_post_meta( $post->ID, 'WooCommerceEventsBookingsExpireUnit', true );

			$woocommerce_events_hide_bookings_display_time         = get_post_meta( $post->ID, 'WooCommerceEventsHideBookingsDisplayTime', true );
			$woocommerce_events_hide_bookings_stock_availability   = get_post_meta( $post->ID, 'WooCommerceEventsHideBookingsStockAvailability', true );
			$woocommerce_events_timezone                           = get_post_meta( $post->ID, 'WooCommerceEventsTimeZone', true );
			$fooevents_bookings_options_serialized                 = get_post_meta( $post->ID, 'fooevents_bookings_options_serialized', true );
			$woocommerce_events_view_bookings_stock_dropdowns      = get_post_meta( $post->ID, 'WooCommerceEventsViewBookingsStockDropdowns', true );
			$woocommerce_events_view_out_of_stock_bookings         = get_post_meta( $post->ID, 'WooCommerceEventsViewOutOfStockBookings', true );
			$woocommerce_events_bookings_hide_date_single_dropdown = get_post_meta( $post->ID, 'WooCommerceEventsBookingsHideDateSingleDropDown', true );
			$fooevents_bookings_options_raw                        = json_decode( $fooevents_bookings_options_serialized, true );
			$fooevents_bookings_options                            = $this->process_booking_options( $fooevents_bookings_options_raw );
			$each_slot_one_date                                    = $this->check_if_slot_has_one_date_only( $fooevents_bookings_options );
			$one_slot_multiple_dates                               = $this->check_if_only_one_slot( $fooevents_bookings_options );

			$field_params = array(
				'type'        => 'hidden',
				'class'       => array( 'attendee-class form-row-wide' ),
				'label'       => '',
				'placeholder' => '',
				'options'     => '',
				'required'    => true,
			);

			woocommerce_form_field( 'fooevents_bookings_method', $field_params, $woocommerce_events_bookings_method );

			if ( 'slotdate' === $woocommerce_events_bookings_method ) {

				$this->output_booking_fields_product_slot_date( $post, $product, $woocommerce_events_bookings_expire_passed_date, $woocommerce_events_bookings_expire_value, $woocommerce_events_bookings_expire_unit, $woocommerce_events_hide_bookings_display_time, $woocommerce_events_hide_bookings_stock_availability, $format, $wordpress_timezone, $woocommerce_events_timezone, $fooevents_bookings_options_serialized, $woocommerce_events_view_bookings_stock_dropdowns, $woocommerce_events_view_out_of_stock_bookings, $woocommerce_events_bookings_hide_date_single_dropdown, $fooevents_bookings_options_raw, $fooevents_bookings_options, $each_slot_one_date, $one_slot_multiple_dates, $selected_slot_id, $selected_slot_id_post_id, $selected_date_id, $selected_slot_id_date_id );

			} else {

				$this->output_booking_fields_product_date_slot( $post, $product, $woocommerce_events_bookings_expire_passed_date, $woocommerce_events_bookings_expire_value, $woocommerce_events_bookings_expire_unit, $woocommerce_events_hide_bookings_display_time, $woocommerce_events_hide_bookings_stock_availability, $format, $wordpress_timezone, $woocommerce_events_timezone, $fooevents_bookings_options_serialized, $woocommerce_events_view_bookings_stock_dropdowns, $woocommerce_events_view_out_of_stock_bookings, $woocommerce_events_bookings_hide_date_single_dropdown, $fooevents_bookings_options_raw, $fooevents_bookings_options, $each_slot_one_date, $one_slot_multiple_dates, $selected_slot_id, $selected_slot_id_post_id, $selected_date_id, $selected_slot_id_date_id );

			}
		}
	}

	/**
	 * Add trans fields as item data to the cart object
	 *
	 * @since 1.0.0
	 * @param array $cart_item_data cart item data.
	 * @param int   $product_id product ID.
	 * @param int   $variation_id variation ID.
	 * @param int   $quantity quantity.
	 */
	public function add_slot_date_addtocart( $cart_item_data, $product_id, $variation_id, $quantity ) {

		if ( ! empty( $_POST['fooevents_bookings_slot_date_val_trans'] ) ) {

			$cart_item_data['fooevents_bookings_slot_date_val'] = sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_slot_date_val_trans'] ) );

		}

		if ( ! empty( $_POST['fooevents_bookings_slot_val__trans'] ) ) {

			$cart_item_data['fooevents_bookings_slot_val'] = sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_slot_val__trans'] ) );

		}

		if ( ! empty( $_POST['fooevents_bookings_date_val__trans'] ) ) {

			$cart_item_data['fooevents_bookings_date_val'] = sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_date_val__trans'] ) );

		}

		if ( ! empty( $_POST['fooevents_bookings_method'] ) ) {

			$cart_item_data['fooevents_bookings_method'] = sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_method'] ) );

		}

		return $cart_item_data;
	}

	/**
	 * Check booking availability on add to cart
	 *
	 * @param bool  $passed passed.
	 * @param int   $product_id product ID.
	 * @param int   $quantity quantity.
	 * @param int   $variation_id variation ID.
	 * @param array $variations variaitons.
	 * @return boolean
	 */
	public function addtocart_booking_availability( $passed, $product_id, $quantity, $variation_id = null, $variations = null ) {

		$woocommerce_events_event = get_post_meta( $product_id, 'WooCommerceEventsEvent', true );
		$woocommerce_events_type  = get_post_meta( $product_id, 'WooCommerceEventsType', true );

		if ( ( isset( $_POST['fooevents_bookings_slot_val__trans'] ) || isset( $_POST['fooevents_bookings_date_val__trans'] ) || isset( $_POST['fooevents_bookings_slot_date_val_trans'] ) ) && ( 'Event' === $woocommerce_events_event ) && ( 'bookings' === $woocommerce_events_type ) ) {

			$woocommerce_events_bookings_method = get_post_meta( $product_id, 'WooCommerceEventsBookingsMethod', true );

			if ( empty( $woocommerce_events_bookings_method ) || 1 === $woocommerce_events_bookings_method ) {

				$woocommerce_events_bookings_method = 'slotdate';

			}

			$bookings_date_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsDateOverride', true );
			$bookings_slot_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsSlotOverride', true );

			$date_label    = '';
			$slot_label    = '';
			$slot_selected = '';
			$date_selected = '';
			$passed        = true;

			if ( empty( $bookings_date_term ) ) {

				$date_label = __( 'date', 'fooevents-bookings' );

			} else {

				$date_label = $bookings_date_term;

			}

			if ( empty( $bookings_slot_term ) ) {

				$slot_label = __( 'slot', 'fooevents-bookings' );

			} else {

				$slot_label = $bookings_slot_term;

			}

			if ( isset( $_POST['fooevents_bookings_slot_val__trans'] ) ) {

				$slot_selected = sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_slot_val__trans'] ) );
				$slot_selected = explode( '_', $slot_selected );

			}
			if ( isset( $_POST['fooevents_bookings_date_val__trans'] ) ) {

				$date_selected = sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_date_val__trans'] ) );

			}
			if ( isset( $_POST['fooevents_bookings_slot_date_val_trans'] ) ) {

				$selected      = explode( '_', sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_slot_date_val_trans'] ) ) );
				$slot_selected = $selected;

				if ( isset( $selected[1] ) ) {

					$date_selected = $selected[1];

				}
			}

			$fooevents_bookings_options_serialized = get_post_meta( $product_id, 'fooevents_bookings_options_serialized', true );
			$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
			$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );

			if ( 'slotdate' === $woocommerce_events_bookings_method ) {

				$passed = $this->addtocart_booking_availability_slot_date( $product_id, $quantity, $fooevents_bookings_options, $slot_selected, $date_selected, $slot_label, $date_label );

			} else {

				$passed = $this->addtocart_booking_availability_date_slot( $product_id, $quantity, $fooevents_bookings_options, $slot_selected, $date_selected, $slot_label, $date_label );

			}

			if ( isset( $_POST['fooevents_bookings_slot_val__trans'] ) && empty( $_POST['fooevents_bookings_slot_val__trans'] ) ) {

				// translators: Placeholder is for the slot label.
				$notice = sprintf( __( '<strong>Error:</strong> Please select a %s.', 'fooevents-bookings' ), $slot_label );

				wc_add_notice( $notice, 'error' );

				$passed = false;
			}

			if ( isset( $_POST['fooevents_bookings_method'] ) && 'dateslot ' === $_POST['fooevents_bookings_method'] && isset( $_POST['fooevents_bookings_date_val__trans'] ) && empty( $_POST['fooevents_bookings_date_val__trans'] ) ) {

				// translators: Placeholder is for the date label.
				$notice = sprintf( __( '<strong>Error:</strong> Please select a %s.', 'fooevents-bookings' ), $date_label );

				wc_add_notice( $notice, 'error' );

				$passed = false;
			}

			if ( isset( $_POST['fooevents_bookings_method'] ) && 'slotdate' === $_POST['fooevents_bookings_method'] && empty( $_POST['fooevents_bookings_date_val__trans'] ) && ! isset( $_POST['fooevents_bookings_slot_date_val_trans'] ) ) {

				// translators: Placeholder is for the date label.
				$notice = sprintf( __( '<strong>Error:</strong> Please select a %s.', 'fooevents-bookings' ), $date_label );

				wc_add_notice( $notice, 'error' );

				$passed = false;
			}

			if ( isset( $_POST['fooevents_bookings_slot_date_val_trans'] ) && empty( $_POST['fooevents_bookings_slot_date_val_trans'] ) ) {

				// translators: Placeholder is for the slot label.
				$notice = sprintf( __( '<strong>Error:</strong> Please select a %s.', 'fooevents-bookings' ), $slot_label );

				wc_add_notice( $notice, 'error' );

				$passed = false;
			}

			return $passed;

		} else {

			return true;

		}
	}

	/**
	 * Validate product page add to cart stock for bookings slot date
	 *
	 * @param int    $product_id product ID.
	 * @param int    $quantity quantity.
	 * @param array  $fooevents_bookings_options bookings options.
	 * @param string $slot_selected slot.
	 * @param string $date_selected date.
	 * @param string $slot_label slot label.
	 * @param string $date_label date label.
	 * @return boolean
	 */
	public function addtocart_booking_availability_slot_date( $product_id, $quantity, $fooevents_bookings_options, $slot_selected, $date_selected, $slot_label, $date_label ) {

		$passed = true;

		if ( isset( $_POST['fooevents_bookings_slot_val__trans'] ) && isset( $_POST['fooevents_bookings_date_val__trans'] ) && ! empty( $_POST['fooevents_bookings_slot_val__trans'] ) && ! empty( $_POST['fooevents_bookings_date_val__trans'] ) ) {

			$stock = 0;

			if ( strval( $fooevents_bookings_options[ $slot_selected[0] ]['add_date'][ $date_selected ]['stock'] ) === '0' ) {

				$passed = false;
				// translators: Placeholder is for the slot label, date label.
				$notice = sprintf( __( '<strong>Availability:</strong> The selected %1$s/%2$s is not available.', 'fooevents-bookings' ), $slot_label, $date_label );

				wc_add_notice( $notice, 'error' );

			}
		} elseif ( isset( $_POST['fooevents_bookings_slot_date_val_trans'] ) && ! empty( $_POST['fooevents_bookings_slot_date_val_trans'] ) ) {

			$booking_slot_date = explode( '_', sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_slot_date_val_trans'] ) ) );

			$slot_date_stock = $fooevents_bookings_options[ $booking_slot_date[0] ]['add_date'][ $booking_slot_date[1] ]['stock'];

			if ( empty( $slot_date_stock ) ) {

				// unlimited.
				$slot_date_stock = 999;

			}

			if ( strval( $fooevents_bookings_options[ $booking_slot_date[0] ]['add_date'][ $booking_slot_date[1] ]['stock'] ) === '0' ) {

				// translators: Placeholder is for the slot label, date label.
				$notice = sprintf( __( '<strong>Availability:</strong> The selected %1$s/%2$s is not available.', 'fooevents-bookings' ), $slot_label, $date_label );
				wc_add_notice( $notice, 'error' );

				$passed = false;
			}
		}

		if ( ! WC()->cart->is_empty() ) {

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				if ( $cart_item['fooevents_bookings_slot_date_val'] === $_POST['fooevents_bookings_slot_date_val_trans'] ) {

					if ( $cart_item['product_id'] === $product_id && isset( $_POST['fooevents_bookings_slot_val__trans'] ) && isset( $_POST['fooevents_bookings_date_val__trans'] ) && isset( $cart_item['fooevents_bookings_slot_val'] ) && $cart_item['fooevents_bookings_slot_val'] === $_POST['fooevents_bookings_slot_val__trans'] && $cart_item['fooevents_bookings_date_val'] === $_POST['fooevents_bookings_date_val__trans'] ) {

						$slot_id = explode( '_', $cart_item['fooevents_bookings_slot_val'] );
						$slot_id = $slot_id[0];

						$date_id = $cart_item['fooevents_bookings_date_val'];

						$available_stock = $fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['stock'];
						$requested_stock = $cart_item['quantity'] + $quantity;

						if ( ! empty( $available_stock ) && $available_stock < $requested_stock ) {

							$notice = __( '<strong>Error:</strong> You have exceeded the number of available booking slots. Please reduce the quantity in your cart before continuing with the checkout process.', 'fooevents-bookings' );

							wc_add_notice( $notice, 'error' );

							$passed = false;

						}
					} elseif ( $cart_item['product_id'] === $product_id && isset( $_POST['fooevents_bookings_slot_date_val__trans'] ) ) {

						$slot    = explode( '_', $cart_item['fooevents_bookings_slot_date_val'] );
						$slot_id = $slot[0];

						$date_id = $slot[1];

						$available_stock = $fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['stock'];
						$requested_stock = $cart_item['quantity'] + $quantity;

						if ( ! empty( $available_stock ) && $available_stock < $requested_stock ) {

							$notice = __( '<strong>Error:</strong> You have exceeded the number of available booking slots. Please reduce the quantity in your cart before continuing with the checkout process.', 'fooevents-bookings' );

							wc_add_notice( $notice, 'error' );

							$passed = false;

						}
					} elseif ( $cart_item['product_id'] === $product_id && isset( $_POST['fooevents_bookings_slot_date_val_trans'] ) && ! empty( $_POST['fooevents_bookings_slot_date_val_trans'] ) ) {

						$slot    = explode( '_', $_POST['fooevents_bookings_slot_date_val_trans'] );
						$slot_id = $slot[0];

						$date_id = $slot[1];

						$available_stock = $fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['stock'];
						$requested_stock = $cart_item['quantity'] + $quantity;

						if ( ! empty( $available_stock ) && $available_stock < $requested_stock ) {

							$notice = __( '<strong>Error:</strong> You have exceeded the number of available booking slots. Please reduce the quantity in your cart before continuing with the checkout process.', 'fooevents-bookings' );

							wc_add_notice( $notice, 'error' );

							$passed = false;

						}
					}
				}
			}
		} else {

			$available_stock = '';

			if ( isset( $fooevents_bookings_options[ $slot_selected[0] ]['add_date'][ $date_selected ]['stock'] ) ) {

				$available_stock = $fooevents_bookings_options[ $slot_selected[0] ]['add_date'][ $date_selected ]['stock'];

			}

			$requested_stock = $quantity;

			if ( ! empty( $available_stock ) && $available_stock < $requested_stock ) {

				$notice = __( '<strong>Error:</strong> You have exceeded the number of available booking slots. Please reduce the quantity in your cart before continuing with the checkout process.', 'fooevents-bookings' );

				wc_add_notice( $notice, 'error' );

				$passed = false;

			}
		}

		return $passed;
	}

	/**
	 * Validate product page add to cart stock for bookings slot date
	 *
	 * @param int    $product_id product id.
	 * @param int    $quantity quantity.
	 * @param array  $fooevents_bookings_options bookings options.
	 * @param string $slot_selected slot.
	 * @param string $date_selected date.
	 * @param string $slot_label slot label.
	 * @param string $date_label date label.
	 * @return boolean
	 */
	public function addtocart_booking_availability_date_slot( $product_id, $quantity, $fooevents_bookings_options, $slot_selected, $date_selected, $slot_label, $date_label ) {

		$passed                               = true;
		$fooevents_bookings_options_date_slot = $this->process_date_slot_bookings_options( $fooevents_bookings_options );

		$date_selected = explode( '_', $date_selected );

		if ( isset( $_POST['fooevents_bookings_slot_val__trans'] ) && isset( $_POST['fooevents_bookings_date_val__trans'] ) && ! empty( $_POST['fooevents_bookings_slot_val__trans'] ) && ! empty( $_POST['fooevents_bookings_date_val__trans'] ) ) {

			if ( strval( $fooevents_bookings_options_date_slot[ $date_selected[0] ][ $slot_selected[0] ]['stock'] ) === '0' ) {

				$passed = false;
				// translators: Placeholder is for the slot label, date label.
				$notice = sprintf( __( '<strong>Availability:</strong> The selected %1$s/%2$s is not available.', 'fooevents-bookings' ), $slot_label, $date_label );

				wc_add_notice( $notice, 'error' );

			}
		}

		if ( ! WC()->cart->is_empty() ) {

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				if ( $cart_item['product_id'] === $product_id && $cart_item['fooevents_bookings_slot_val'] === $_POST['fooevents_bookings_slot_val__trans'] && $cart_item['fooevents_bookings_date_val'] === $_POST['fooevents_bookings_date_val__trans'] ) {

					$slot_val = explode( '_', $cart_item['fooevents_bookings_slot_val'] );
					$slot_id  = $slot_val[0];

					$date_id = $slot_val[1];

					$available_stock = $fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['stock'];
					$requested_stock = $cart_item['quantity'] + $quantity;

					if ( ! empty( $available_stock ) && $available_stock < $requested_stock ) {

						$notice = __( '<strong>Error:</strong> You have exceeded the number of available booking slots. Please reduce the quantity in your cart before continuing with the checkout process.', 'fooevents-bookings' );

						wc_add_notice( $notice, 'error' );

						$passed = false;

					}
				}
			}
		} else {

			$available_stock = '';
			if ( isset( $slot_selected[0] ) && isset( $slot_selected[1] ) && isset( $fooevents_bookings_options[ $slot_selected[0] ]['add_date'][ $slot_selected[1] ]['stock'] ) ) {

				$available_stock = $fooevents_bookings_options[ $slot_selected[0] ]['add_date'][ $slot_selected[1] ]['stock'];

			}

			$requested_stock = $quantity;

			if ( ! empty( $available_stock ) && $available_stock < $requested_stock ) {

				$notice = __( '<strong>Error:</strong> You have exceeded the number of available booking slots. Please reduce the quantity in your cart before continuing with the checkout process.', 'fooevents-bookings' );

				wc_add_notice( $notice, 'error' );

				$passed = false;

			}
		}

		return $passed;
	}

	/**
	 * Saves tickets meta box settings
	 *
	 * @param int $post_id post ID.
	 * @global object $post
	 * @global object $woocommerce
	 */
	public function save_edit_ticket_meta_boxes( $post_id ) {

		global $post;
		global $woocommerce;

		if ( is_object( $post ) && isset( $_POST ) && 'event_magic_tickets' === $post->post_type && isset( $_POST['action'] ) && 'editpost' === $_POST['action'] && ! isset( $_POST['add_ticket'] ) ) {

			$nonce = '';
			if ( isset( $_POST['fooevents_bookings_options_edit_ticket_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_options_edit_ticket_nonce'] ) );
			}

			if ( ! wp_verify_nonce( $nonce, 'fooevents_bookings_options_edit_ticket' ) ) {
				// die( esc_attr__( 'Security check failed - FooEvents Bookings 0003', 'fooevents-bookings' ) );
			}

			$ticket_id = '';
			if ( isset( $_POST['fooevents_ticket_raw_id'] ) ) {

				$ticket_id = sanitize_text_field( wp_unslash( $_POST['fooevents_ticket_raw_id'] ) );

			}

			$slot_id = '';
			if ( isset( $_POST['WooCommerceEventsBookingSlotID'] ) ) {

				$slot_id = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBookingSlotID'] ) );

			}

			$date_id = '';
			if ( isset( $_POST['WooCommerceEventsBookingDateID'] ) ) {

				$date_id = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBookingDateID'] ) );

			}

			$event_id = '';
			if ( isset( $_POST['fooevents_event_id'] ) ) {

				$event_id = sanitize_text_field( wp_unslash( $_POST['fooevents_event_id'] ) );

			}

			$fooevents_bookings_options_serialized = get_post_meta( $event_id, 'fooevents_bookings_options_serialized', true );
			$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
			$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );

			$woocommerce_events_booking_slot_id = get_post_meta( $ticket_id, 'WooCommerceEventsBookingSlotID', true );
			$woocommerce_events_booking_date_id = get_post_meta( $ticket_id, 'WooCommerceEventsBookingDateID', true );

			$fooevents_bookings_options_serialized_updated = $fooevents_bookings_options_serialized;

			// If booking has changed.
			if ( $slot_id !== $woocommerce_events_booking_slot_id || $date_id !== $woocommerce_events_booking_date_id ) {

				$stock = $fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['stock'];

				$old_stock = '';
				if ( isset( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ]['stock'] ) ) {

					$old_stock = $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ]['stock'];

				}

				$new_slot_label = $fooevents_bookings_options[ $slot_id ]['label'];
				$new_date_label = $fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['date'];

				if ( '' !== $stock ) {

					$stock = (int) $stock - 1;

					$fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['stock'] = $stock;
					$fooevents_bookings_options_serialized_updated                           = wp_json_encode( $fooevents_bookings_options, JSON_UNESCAPED_UNICODE );

				}

				if ( '' !== $old_stock ) {

					$old_stock = (int) $old_stock + 1;

					$fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ]['stock'] = $old_stock;
					$fooevents_bookings_options_serialized_updated = wp_json_encode( $fooevents_bookings_options, JSON_UNESCAPED_UNICODE );

				}

				$wp_date_format = get_option( 'date_format' );

				$formatted_time = '';
				if ( isset( $fooevents_bookings_options[ $slot_id ]['add_time'] ) && 'enabled' === $fooevents_bookings_options[ $slot_id ]['add_time'] ) {

					$formatted_time = $fooevents_bookings_options[ $slot_id ]['hour'] . ':' . $fooevents_bookings_options[ $slot_id ]['minute'] . ' ' . str_replace( '.', '', $fooevents_bookings_options[ $slot_id ]['period'] );

				}

				$bookings_date_time = '';
				if ( 'd/m/Y' === $wp_date_format ) {

					$bookings_date_time = str_replace( '/', '-', $new_date_label );

				} else {

					$bookings_date_time = $new_date_label;

				}

				$bookings_date_time                        = str_replace( ',', '', $bookings_date_time );
				$bookings_date_time                        = $this->convert_month_to_english( $bookings_date_time );
				$woocommerce_events_booking_date_timestamp = strtotime( $bookings_date_time . ' ' . $formatted_time );

				update_post_meta( $ticket_id, 'WooCommerceEventsBookingSlotID', $slot_id );
				update_post_meta( $ticket_id, 'WooCommerceEventsBookingSlot', $new_slot_label );
				update_post_meta( $ticket_id, 'WooCommerceEventsBookingDateID', $date_id );
				update_post_meta( $ticket_id, 'WooCommerceEventsBookingDate', $new_date_label );
				update_post_meta( $ticket_id, 'WooCommerceEventsBookingDateTimestamp', $woocommerce_events_booking_date_timestamp );
				update_post_meta( $event_id, 'fooevents_bookings_options_serialized', $fooevents_bookings_options_serialized_updated );

				if ( ( isset( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['zoom_id'] ) && 'enabled' === $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['zoom_id'] ) ||
					( isset( $fooevents_bookings_options[ $slot_id ]['zoom_id'] ) && 'enabled' === $fooevents_bookings_options[ $slot_id ]['zoom_id'] )
				) {

					$zoom_api_helper = new FooEvents_Zoom_API_Helper( new FooEvents_Config() );

					if ( isset( $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['zoom_id'] ) && 'enabled' === $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['zoom_id'] ) {

						// Cancel previous Zoom meeting registration.
						$previous_zoom_meeting_id = $fooevents_bookings_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ]['zoom_id'];
						$previous_attendee_email  = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeEmail', true );

						$cancel_args = array(
							'action'      => 'cancel',
							'registrants' => array( array( 'email' => $previous_attendee_email ) ),
						);

						$cancel_result = $zoom_api_helper->fooevents_update_zoom_registration_statuses( $previous_zoom_meeting_id, $cancel_args );

					}

					if ( isset( $fooevents_bookings_options[ $slot_id ]['zoom_id'] ) && 'enabled' === $fooevents_bookings_options[ $slot_id ]['zoom_id'] ) {

						// Register for new Zoom meeting.
						$new_zoom_meeting_id = $fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['zoom_id'];

						$new_attendee_email = '';
						if ( isset( $_POST['WooCommerceEventsAttendeeEmail'] ) ) {

							$new_attendee_email = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeEmail'] ) );

						}

						$new_attendee_first_name = '';
						if ( isset( $_POST['WooCommerceEventsAttendeeName'] ) ) {

							$new_attendee_first_name = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeName'] ) );

						}

						$new_attendee_last_name = '';
						if ( isset( $_POST['WooCommerceEventsAttendeeLastName'] ) ) {

							$new_attendee_last_name = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeLastName'] ) );

						}

						$register_args = array(
							'email'      => ! empty( $new_attendee_email ) ? $new_attendee_email : get_post_meta( $ticket_id, 'WooCommerceEventsPurchaserEmail', true ),
							'first_name' => ! empty( $new_attendee_first_name ) ? $new_attendee_first_name : get_post_meta( $ticket_id, 'WooCommerceEventsPurchaserFirstName', true ),
							'last_name'  => ! empty( $new_attendee_last_name ) ? $new_attendee_last_name : get_post_meta( $ticket_id, 'WooCommerceEventsPurchaserLastName', true ),
						);

						$register_result = $zoom_api_helper->add_update_single_zoom_registrant( $new_zoom_meeting_id, $register_args );

					}
				}
			}
		}
	}

	/**
	 * Fetch bookings dates to display in calendar
	 *
	 * @param array $include_cats categories included.
	 */
	public function get_bookings_for_calendar( $include_cats = array(), $product_ids = array() ) {

		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => 'WooCommerceEventsEvent',
					'value'   => 'Event',
					'compare' => '=',
				),
				array(
					'key'     => 'fooevents_bookings_options_serialized',
					'value'   => '',
					'compare' => '!=',
				),
			),
		);

		if ( ! empty( $include_cats ) ) {

			$args['tax_query'] = array( 'relation' => 'OR' );

			foreach ( $include_cats as $include_cat ) {

				$args['tax_query'][] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => $include_cat,
				);

			}
		}

		if ( ! empty( $product_ids ) ) {

			$args['post__in'] = $product_ids;

		}

		$events_query       = new WP_Query( $args );
		$booking_events     = $events_query->get_posts();
		$format             = get_option( 'date_format' );
		$all_day_events     = get_option( 'globalFooEventsAllDayEvent' );
		$wordpress_timezone = get_option( 'timezone_string' );
		$today              = current_time( 'timestamp' );

		$json_events = array();

		$x = 0;
		foreach ( $booking_events as $event ) {

			$event_background_color                         = get_post_meta( $event->ID, 'WooCommerceEventsBackgroundColor', true );
			$event_type                                     = get_post_meta( $event->ID, 'WooCommerceEventsType', true );
			$event_text_color                               = get_post_meta( $event->ID, 'WooCommerceEventsTextColor', true );
			$fooevents_bookings_options_serialized          = get_post_meta( $event->ID, 'fooevents_bookings_options_serialized', true );
			$fooevents_bookings_options_raw                 = json_decode( $fooevents_bookings_options_serialized, true );
			$fooevents_bookings_options                     = $this->process_booking_options( $fooevents_bookings_options_raw );
			$woocommerce_events_bookings_expire_passed_date = get_post_meta( $event->ID, 'WooCommerceEventsBookingsExpirePassedDate', true );
			$woocommerce_events_bookings_expire_value       = get_post_meta( $event->ID, 'WooCommerceEventsBookingsExpireValue', true );
			$woocommerce_events_bookings_expire_unit        = get_post_meta( $event->ID, 'WooCommerceEventsBookingsExpireUnit', true );
			$woocommerce_events_timezone                    = get_post_meta( $event->ID, 'WooCommerceEventsTimeZone', true );
			$woocommerce_events_view_out_of_stock_bookings  = get_post_meta( $event->ID, 'WooCommerceEventsViewOutOfStockBookings', true );
			$woocommerce_events_hide_bookings_slot_calendar = get_post_meta( $event->ID, 'WooCommerceEventsHideBookingsSlotCalendar', true );
			$woocommerce_events_location                    = get_post_meta( $event->ID, 'WooCommerceEventsLocation', true );
			$events_expire_option                           = get_option( 'globalWooCommerceEventsExpireOption' );
			$post = get_post( $event->ID );

			if ( 'bookings' === $event_type ) {

				foreach ( $fooevents_bookings_options as $slot_id => $slot ) {

					if ( ! empty( $slot['add_date'] ) ) {

						foreach ( $slot['add_date'] as $date_id => $date ) {

							if ( isset( $date['date'] ) ) {

								$stock = '';
								if ( strlen( $date['stock'] ) > 0 ) {

									$stock = (int) $date['stock'];

								}

								$stock_class = '';
								if ( 0 === (int) $stock && '' !== $stock ) {
									$stock_class = 'fooevents_out_of_stock';
								}

								$global_display_stock = get_option( 'globalFooEventsDisplayStock' );
								$stock_display        = '';

								if ( 'yes' === $global_display_stock ) {

									if ( '' !== $stock ) {
										$stock_display = ' (' . $stock . ')';

									} else {
										$stock_display = '';
									}
								} else {
									$stock_display = '';
								}

								if ( 0 === $stock && 'on' !== $woocommerce_events_view_out_of_stock_bookings ) {

									continue;

								}

								$timestamp = '';
								if ( 'yes' === $woocommerce_events_bookings_expire_passed_date && ! empty( $date['date'] ) ) {

									$date_to_compare = '';
									if ( isset( $slot['hour'] ) && ! empty( $slot['hour'] ) && isset( $slot['minute'] ) && ! empty( $slot['minute'] ) && isset( $slot['period'] ) && ! empty( $slot['period'] ) ) {

										$date_to_compare = $date['date'] . ' ' . $slot['hour'] . ':' . $slot['minute'] . $slot['period'];

									} elseif ( isset( $slot['hour'] ) && ! empty( $slot['hour'] ) && isset( $slot['minute'] ) && ! empty( $slot['minute'] ) ) {

										$date_to_compare = $date['date'] . ' ' . $slot['hour'] . ':' . $slot['minute'];

									} else {

										$date_to_compare = $date['date'];

									}

									if ( ! empty( $woocommerce_events_timezone ) && $wordpress_timezone !== $woocommerce_events_timezone ) {

										$date_time = new DateTime( '@' . $today );
										$timezone  = new DateTimeZone( $woocommerce_events_timezone );
										$date_time->setTimezone( $timezone );
										$today = $date_time->format( 'U' );

									}

									$expire_date = sanitize_text_field( $date_to_compare );

									if ( 'd/m/Y' === $format || 'd.m.Y' === $format ) {

										$expire_date = str_replace( '/', '-', $expire_date );

									}

									$expire_date = str_replace( ',', '', $expire_date );
									$expire_date = $this->convert_month_to_english( $expire_date );

									$timestamp = strtotime( $expire_date );

									if ( ! empty( $woocommerce_events_bookings_expire_value ) && ! empty( $woocommerce_events_bookings_expire_unit ) && ! empty( $timestamp ) ) {

										$timestamp = strtotime( '-' . $woocommerce_events_bookings_expire_value . ' ' . $woocommerce_events_bookings_expire_unit, $timestamp );

									}

									if ( $today > $timestamp && 'hide' === $events_expire_option ) {

										continue;

									}
								}

								$booking_date = $date['date'];
								$all_day      = true;

								if ( isset( $slot['add_time'] ) && 'enabled' === $slot['add_time'] && isset( $slot['hour'] ) && isset( $slot['minute'] ) && isset( $slot['period'] ) && 'yes' !== $all_day_events ) {

									$booking_date = $booking_date . ' ' . $slot['hour'] . ':' . $slot['minute'] . $slot['period'];
									$all_day      = false;

								}

								if ( 'd/m/Y' === $format || 'd.m.Y' === $format ) {

									$booking_date = str_replace( '/', '-', $booking_date );

								}

								$booking_date = str_replace( ',', '', $booking_date );
								// $booking_date = str_replace( '.', '', $booking_date );
								$booking_date           = $this->convert_month_to_english( $booking_date );
								$booking_date           = date_i18n( 'Y-m-d H:i:s', strtotime( $booking_date ) );
								$booking_date           = str_replace( ' ', 'T', $booking_date );
								$booking_date_timestamp = strtotime( $booking_date );

								if ( isset( $slot['label'] ) ) {

									$title = '';

									if ( ! empty( $slot['label'] ) && 'on' !== $woocommerce_events_hide_bookings_slot_calendar ) {

										$title .= $slot['label'];

									}

									if ( ! empty( $slot['label'] ) && ! empty( $event->post_title ) && 'on' !== $woocommerce_events_hide_bookings_slot_calendar ) {

										$title .= ' - ';

									}

									if ( ! empty( $event->post_title ) ) {

										$title .= $event->post_title;

									}

									$operator = '?';
									if ( ! get_option( 'permalink_structure' ) ) {

										$operator = '&';

									}

									$lang = '';

									//phpcs:ignore WordPress.Security.NonceVerification.Recommended
									if ( ! empty( $_GET['lang'] ) ) {

										//phpcs:ignore WordPress.Security.NonceVerification.Recommended
										$lang = '&lang=' . sanitize_text_field( wp_unslash( $_GET['lang'] ) );

									}

									$time = '';
									if ( isset( $slot['formatted_time'] ) ) {

										$time = $slot['formatted_time'];

									}

									$booking_url = get_permalink( $event->ID ) . $operator . 'bookings_sid=' . $slot_id . '&bookings_did=' . $date_id . $lang;
									if ( $today > $timestamp && 'yes' === $woocommerce_events_bookings_expire_passed_date && ! empty( $timestamp ) ) {
										$booking_url = '';
									}

									if ( ! isset( $slot['unformatted_time'] ) ) {

										$slot['unformatted_time'] = '';

									}

									$json_events['events'][ $x ] = array(
										'title'           => $title . $stock_display,
										'allDay'          => $all_day,
										'start'           => $booking_date,
										'unformated_date' => $date['date'],
										'url'             => $booking_url,
										'post_id'         => $event->ID,
										'in_stock'        => 'yes',
										'desc'            => $post->post_excerpt,
										'time'            => $time,
										'timestamp'       => $booking_date_timestamp,
										'unformated_start_time' => $slot['unformatted_time'],
										'unformated_end_time' => '',
										'location'        => $woocommerce_events_location,
										'stock_num'       => $stock,
										'className'       => $stock_class,
									);

									if ( ! empty( $event_background_color ) ) {

										$json_events['events'][ $x ]['color'] = $event_background_color;

									}

									if ( $today > $timestamp && 'yes' === $woocommerce_events_bookings_expire_passed_date && ! empty( $timestamp ) ) {
										$json_events['events'][ $x ]['color'] = '#cccccc';
									}

									if ( ! empty( $event_text_color ) ) {

										$json_events['events'][ $x ]['textColor'] = $event_text_color;

									}
								}

								++$x;

							}
						}
					}
				}
			}
		}

		return $json_events;
	}

	/**
	 * Delete a Zoom meeting/webinar when removing a slot date
	 */
	public function delete_zoom_meeting() {

		$nonce = '';
		if ( isset( $_POST['fooevents_bookings_options_nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['fooevents_bookings_options_nonce'] ) );
		}

		if ( ! wp_verify_nonce( $nonce, 'fooevents_bookings_options' ) ) {
			die( esc_attr__( 'Security check failed - FooEvents Bookings 0005', 'fooevents-bookings' ) );
		}

		$zoom_id = '';
		if ( isset( $_POST['zoom_id'] ) ) {

			$zoom_id = sanitize_text_field( wp_unslash( $_POST['zoom_id'] ) );

		}

		$zoom_api_helper = new FooEvents_Zoom_API_Helper( new FooEvents_Config() );
		$result          = $zoom_api_helper->delete_zoom_meeting( $zoom_id );

		echo wp_json_encode( $result );

		exit();
	}

	/**
	 * Return bookings stock on cancelled order
	 *
	 * @param int $order_id order ID.
	 */
	public function order_cancelled_return_stock( $order_id ) {

		$wc_order = wc_get_order( $order_id );

		$woocommerce_events_order_tickets = $wc_order->get_meta( 'WooCommerceEventsOrderTickets', true );

		foreach ( $woocommerce_events_order_tickets as $event => $event_tickets ) {

			foreach ( $event_tickets as $ticket ) {

				$event_type = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsType', true );

				if ( 'bookings' === $event_type ) {

					$fooevents_bookings_options_serialized = get_post_meta( $ticket['WooCommerceEventsProductID'], 'fooevents_bookings_options_serialized', true );
					$fooevents_bookings_options            = json_decode( $fooevents_bookings_options_serialized, true );
					$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options );

					$booking_slot = $ticket['WooCommerceEventsBookingOptions']['slot'];
					$booking_date = $ticket['WooCommerceEventsBookingOptions']['date'];

					$date_stock = $fooevents_bookings_options[ $booking_slot ]['add_date'][ $booking_date ]['stock'];

					if ( '' !== $date_stock && isset( $fooevents_bookings_options[ $booking_slot ]['add_date'][ $booking_date ] ) ) {

						$fooevents_bookings_options[ $booking_slot ]['add_date'][ $booking_date ]['stock'] = $date_stock + 1;

						$fooevents_bookings_options_serialized_updated = wp_json_encode( $fooevents_bookings_options, JSON_UNESCAPED_UNICODE );

						update_post_meta( $ticket['WooCommerceEventsProductID'], 'fooevents_bookings_options_serialized', $fooevents_bookings_options_serialized_updated );

						$this->wpml_sync_bookings_between_translations( $ticket['WooCommerceEventsProductID'] );

					}
				}
			}
		}
	}

	/**
	 * Return bookings stock on refunded order
	 *
	 * @param int $order_id order ID.
	 */
	public function order_refunded_return_stock( $order_id ) {

		$restock_refunded_items = '';
		//phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['restock_refunded_items'] ) ) {

			//phpcs:ignore WordPress.Security.NonceVerification.Missing
			$restock_refunded_items = sanitize_text_field( wp_unslash( $_POST['restock_refunded_items'] ) );

		}

		if ( 'on' === $restock_refunded_items ) {

			$order = wc_get_order( $order_id );

			$woocommerce_events_order_tickets = $order->get_meta( 'WooCommerceEventsOrderTickets', true );

			foreach ( $woocommerce_events_order_tickets as $event => $tickets ) {

				foreach ( $tickets as $ticket ) {

					$fooevents_bookings_options_serialized = get_post_meta( $ticket['WooCommerceEventsProductID'], 'fooevents_bookings_options_serialized', true );
					$fooevents_bookings_options            = json_decode( $fooevents_bookings_options_serialized, true );
					$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options );

					$booking_slot = $ticket['WooCommerceEventsBookingOptions']['slot'];
					$booking_date = $ticket['WooCommerceEventsBookingOptions']['date'];
					$date_stock   = $fooevents_bookings_options[ $booking_slot ]['add_date'][ $booking_date ]['stock'];

					if ( '' !== $date_stock && isset( $fooevents_bookings_options[ $booking_slot ]['add_date'][ $booking_date ] ) ) {

						$fooevents_bookings_options[ $booking_slot ]['add_date'][ $booking_date ]['stock'] = $date_stock + 1;

						$fooevents_bookings_options_serialized_updated = wp_json_encode( $fooevents_bookings_options, JSON_UNESCAPED_UNICODE );

						update_post_meta( $ticket['WooCommerceEventsProductID'], 'fooevents_bookings_options_serialized', $fooevents_bookings_options_serialized_updated );

						$this->wpml_sync_bookings_between_translations( $ticket['WooCommerceEventsProductID'] );

					}
				}
			}
		}
	}

	/**
	 * Return bookings stock on partial refunded order
	 *
	 * @param int $order_id order ID.
	 * @param int $refund_id refund ID.
	 */
	public function order_partial_refunded_return_stock( $order_id, $refund_id ) {

		$restock_items = sanitize_text_field( wp_unslash( $_POST['restock_refunded_items'] ) );

		if ( 'true' === $restock_items ) {

			$order         = wc_get_order( $order_id );
			$order_refunds = $order->get_refunds();

			foreach ( $order_refunds as $refund ) {

				foreach ( $refund->get_items() as $item_id => $item ) {

					$refunded_item_id = $item->get_meta( '_refunded_item_id' );
					$product_id       = $item->get_product_id();

					$booking_slot_id = wc_get_order_item_meta( $refunded_item_id, 'booking_slot_id', true );
					$booking_date_id = wc_get_order_item_meta( $refunded_item_id, 'booking_date_id', true );

					$fooevents_bookings_options_serialized = get_post_meta( $product_id, 'fooevents_bookings_options_serialized', true );
					$fooevents_bookings_options            = json_decode( $fooevents_bookings_options_serialized, true );
					$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options );

					$date_stock = $fooevents_bookings_options[ $booking_slot_id ]['add_date'][ $booking_date_id ]['stock'];

					if ( '' !== $date_stock && isset( $fooevents_bookings_options[ $booking_slot_id ]['add_date'][ $booking_date_id ] ) ) {

						$fooevents_bookings_options[ $booking_slot_id ]['add_date'][ $booking_date_id ]['stock'] = $date_stock + 1;

						$fooevents_bookings_options_serialized_updated = wp_json_encode( $fooevents_bookings_options, JSON_UNESCAPED_UNICODE );

						update_post_meta( $product_id, 'fooevents_bookings_options_serialized', $fooevents_bookings_options_serialized_updated );

						$this->wpml_sync_bookings_between_translations( $product_id );

					}
				}
			}
		}
	}

	/**
	 * Add booking details to cart items.
	 *
	 * @param array $item_data item data.
	 * @param array $cart_item_data cart item data.
	 */
	public function add_booking_details_to_cart( $item_data, $cart_item_data ) {

		if ( is_checkout() ) {

			return $item_data;

		}

		$fooevents_bookings_options                    = array();
		$woocommerce_events_hide_bookings_display_time = '';
		$slot_label                                    = '';
		$date_label                                    = '';
		if ( isset( $cart_item_data['fooevents_bookings_method'] ) ) {

			$fooevents_bookings_options_serialized         = get_post_meta( $cart_item_data['product_id'], 'fooevents_bookings_options_serialized', true );
			$fooevents_bookings_options_raw                = json_decode( $fooevents_bookings_options_serialized, true );
			$fooevents_bookings_options                    = $this->process_booking_options( $fooevents_bookings_options_raw );
			$woocommerce_events_hide_bookings_display_time = get_post_meta( $cart_item_data['product_id'], 'WooCommerceEventsHideBookingsDisplayTime', true );

			$bookings_date_term = get_post_meta( $cart_item_data['product_id'], 'WooCommerceEventsBookingsDateOverride', true );
			$bookings_slot_term = get_post_meta( $cart_item_data['product_id'], 'WooCommerceEventsBookingsSlotOverride', true );

			if ( empty( $bookings_slot_term ) ) {

				$slot_label = __( 'Slot', 'fooevents-bookings' );

			} else {

				$slot_label = $bookings_slot_term;

			}

			if ( empty( $bookings_date_term ) ) {

				$date_label = __( 'Date', 'fooevents-bookings' );

			} else {

				$date_label = $bookings_date_term;

			}
		}

		if ( isset( $cart_item_data['fooevents_bookings_method'] ) && 'slotdate' === $cart_item_data['fooevents_bookings_method'] && isset( $cart_item_data['fooevents_bookings_slot_val'] ) && isset( $cart_item_data['fooevents_bookings_date_val'] ) ) {

			$slot_val = explode( '_', $cart_item_data['fooevents_bookings_slot_val'] );
			$date_val = $cart_item_data['fooevents_bookings_date_val'];

			$slot_display = $fooevents_bookings_options[ $slot_val[0] ]['label'];
			if ( ! empty( $fooevents_bookings_options[ $slot_val[0] ]['formatted_time'] ) && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

				$slot_display .= ' ' . $fooevents_bookings_options[ $slot_val[0] ]['formatted_time'];

			}

			if ( ! empty( $slot_display ) ) {
				$item_data[] = array(
					'key'   => $slot_label,
					'value' => $slot_display,
				);
			}

			$item_data[] = array(
				'key'   => $date_label,
				'value' => $fooevents_bookings_options[ $slot_val[0] ]['add_date'][ $date_val ]['date'],
			);

		} elseif ( isset( $cart_item_data['fooevents_bookings_method'] ) && 'dateslot' === $cart_item_data['fooevents_bookings_method'] && isset( $cart_item_data['fooevents_bookings_slot_val'] ) && isset( $cart_item_data['fooevents_bookings_date_val'] ) ) {

			$slot_data = explode( '_', $cart_item_data['fooevents_bookings_slot_val'] );
			$slot_val  = $slot_data[0];
			$date_val  = $slot_data[1];

			$slot_display = $fooevents_bookings_options[ $slot_val ]['label'];
			if ( ! empty( $fooevents_bookings_options[ $slot_val ]['formatted_time'] ) && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

				$slot_display .= ' ' . $fooevents_bookings_options[ $slot_val ]['formatted_time'];

			}

			$item_data[] = array(
				'key'   => $date_label,
				'value' => $fooevents_bookings_options[ $slot_val ]['add_date'][ $date_val ]['date'],
			);

			if ( ! empty( $slot_display ) ) {
				$item_data[] = array(
					'key'   => $slot_label,
					'value' => $slot_display,
				);
			}
		} elseif ( isset( $cart_item_data['fooevents_bookings_method'] ) && 'slotdate' === $cart_item_data['fooevents_bookings_method'] && isset( $cart_item_data['fooevents_bookings_slot_date_val'] ) ) {

			$slot_data = explode( '_', $cart_item_data['fooevents_bookings_slot_date_val'] );
			$slot_val  = $slot_data[0];
			$date_val  = $slot_data[1];

			$slot_display = $fooevents_bookings_options[ $slot_val ]['label'];
			if ( ! empty( $fooevents_bookings_options[ $slot_val ]['formatted_time'] ) && 'on' !== $woocommerce_events_hide_bookings_display_time ) {

				$slot_display .= ' ' . $fooevents_bookings_options[ $slot_val ]['formatted_time'];

			}

			$item_data[] = array(
				'key'   => $date_label,
				'value' => $fooevents_bookings_options[ $slot_val ]['add_date'][ $date_val ]['date'],
			);

			if ( ! empty( $slot_display ) ) {
				$item_data[] = array(
					'key'   => $slot_label,
					'value' => $slot_display,
				);
			}
		}

		return $item_data;
	}

	/**
	 * On ticket creation add booking meta to order items so partial refund is possible
	 *
	 * @param int    $order_id The order ID.
	 * @param string $old_status The old status.
	 * @param string $new_status The new status.
	 */
	public function add_order_meta_on_ticket_create( $order_id, $old_status, $new_status ) {

		$new_status = 'wc-' . $new_status;

		$global_woocommerce_events_send_on_status = get_option( 'globalWooCommerceEventsSendOnStatus' );

		if ( ! empty( $global_woocommerce_events_send_on_status ) && ! is_array( $global_woocommerce_events_send_on_status ) ) {

			$global_woocommerce_events_send_on_status[] = $global_woocommerce_events_send_on_status;

		} elseif ( empty( $global_woocommerce_events_send_on_status ) ) {

			$global_woocommerce_events_send_on_status   = array();
			$global_woocommerce_events_send_on_status[] = 'wc-completed';

		}

		if ( in_array( $new_status, $global_woocommerce_events_send_on_status ) ) {

			$order                            = wc_get_order( $order_id );
			$woocommerce_events_order_tickets = $order->get_meta( 'WooCommerceEventsOrderTickets', true );

			$bookings_tickets = $this->get_booking_tickets_in_order_tickets( $woocommerce_events_order_tickets );

			$x = 0;
			foreach ( $order->get_items() as $item_id => $item ) {

				$product_id  = $item->get_product_id();
				$events_type = get_post_meta( $product_id, 'WooCommerceEventsType', true );

				if ( 'bookings' === $events_type ) {

					$bookings_date_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsDateOverride', true );
					$bookings_slot_term = get_post_meta( $product_id, 'WooCommerceEventsBookingsSlotOverride', true );

					if ( empty( $bookings_slot_term ) ) {

						$slot_label = __( 'Slot', 'fooevents-bookings' );

					} else {

						$slot_label = $bookings_slot_term;

					}

					if ( empty( $bookings_date_term ) ) {

						$date_label = __( 'Date', 'fooevents-bookings' );

					} else {

						$date_label = $bookings_date_term;

					}

					$item->update_meta_data( 'booking_slot_id', $bookings_tickets[ $x ]['WooCommerceEventsBookingOptions']['slot'] );
					$item->update_meta_data( 'booking_date_id', $bookings_tickets[ $x ]['WooCommerceEventsBookingOptions']['date'] );
					$item->update_meta_data( $slot_label, $this->get_label_from_id( $product_id, $bookings_tickets[ $x ]['WooCommerceEventsBookingOptions']['slot'] ) );
					$item->update_meta_data( $date_label, $this->get_date_from_ids( $product_id, $bookings_tickets[ $x ]['WooCommerceEventsBookingOptions']['slot'], $bookings_tickets[ $x ]['WooCommerceEventsBookingOptions']['date'] ) );
					$item->save();

					++$x;

				}
			}
		}
	}

	/**
	 * Split booking order items to support partial refunds
	 *
	 * @param object $order the order object.
	 */
	public function split_order_items( $order_id ) {

		$order       = wc_get_order( $order_id );
		$order_items = $order->get_items();

		foreach ( $order_items as $item ) {

			if ( $item instanceof WC_Order_Item_Product ) {

				$quantity = $item->get_quantity( 'edit' );

				$product    = $item->get_product();
				$product_id = 0;

				if ( false !== $product ) {

					$product_id = $product->get_id();

				}

				if ( $product instanceof WC_Product_Variation ) {

					$product_id = $product->get_parent_id();

				}

				$events_type = get_post_meta( $product_id, 'WooCommerceEventsType', true );

				if ( 'bookings' === $events_type && $product && 1 < $quantity ) {

					$taxes = $item->get_taxes();

					$order->remove_item( $item->get_id() );

					for ( $i = $quantity; $i > 0; $i-- ) {

						$new_item  = new WC_Order_Item_Product();
						$new_taxes = array();

						if ( ! empty( $taxes ) && is_array( $taxes ) ) {
							foreach ( $taxes as $taxes_key => $tax_values ) {
								$new_tax_values = array();

								if ( ! empty( $tax_values ) && is_array( $tax_values ) ) {

									foreach ( $tax_values as $tax_key => $tax_value ) {
										$new_tax_values[ $tax_key ] = $tax_value / $quantity;
									}
								}

								$new_taxes[ $taxes_key ] = $new_tax_values;
							}
						}

						$new_item->set_product( $product );
						$new_item->set_backorder_meta();
						$new_item->set_props(
							array(
								'quantity'     => 1,
								'subtotal'     => $item->get_subtotal( 'edit' ) / $quantity,
								'total'        => $item->get_total( 'edit' ) / $quantity,
								'subtotal_tax' => $item->get_subtotal_tax( 'edit' ) / $quantity,
								'total_tax'    => $item->get_total_tax( 'edit' ) / $quantity,
								'taxes'        => $new_taxes,
							)
						);
						$order->add_item( $new_item );
						$order->save();

					}
				}
			}
		}
	}

	/**
	 * If WPML is active sync bookings between translations on new order
	 *
	 * @param int $order_id order ID.
	 */
	public function wpml_sync_bookings_between_translations_new_order( $order_id ) {

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) || is_plugin_active_for_network( 'sitepress-multilingual-cms/sitepress.php' ) ) {

			$order = wc_get_order( $order_id );

			foreach ( $order->get_items() as $id => $item ) {

				$product_id = $item->get_product_id();

				$booking_options = get_post_meta( $product_id, 'fooevents_bookings_options_serialized', true );

				if ( ! empty( $booking_options ) ) {

					do_action( 'wpml_sync_custom_field', $product_id, 'fooevents_bookings_options_serialized' );

				}
			}
		}
	}

	/**
	 * If WPML is active sync bookings between translations
	 *
	 * @param int $product_id product ID.
	 */
	public function wpml_sync_bookings_between_translations( $product_id ) {

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) || is_plugin_active_for_network( 'sitepress-multilingual-cms/sitepress.php' ) ) {

			do_action( 'wpml_sync_custom_field', $product_id, 'fooevents_bookings_options_serialized' );

		}
	}

	/**
	 * Hide booking item meta in admin and emails
	 *
	 * @param array $arr the array of meta to hide.
	 * @return array
	 */
	public function hide_bookings_meta( $arr ) {

		$arr[] = 'booking_slot_id';
		$arr[] = 'booking_date_id';

		return $arr;
	}

	/**
	 * Hide booking item meta in emails
	 *
	 * @param array $arr the array of meta to hide.
	 * @return array
	 */
	public function unset_specific_order_item_meta_data( $formatted_meta, $item ) {

		// Only on emails notifications
		if ( is_admin() || is_wc_endpoint_url() ) {
			return $formatted_meta;
		}

		foreach ( $formatted_meta as $key => $meta ) {
			if ( in_array( $meta->key, array( 'booking_slot_id' ) ) || in_array( $meta->key, array( 'booking_date_id' ) ) ) {
				unset( $formatted_meta[ $key ] );
			}
		}
		return $formatted_meta;
	}


	/**
	 * Returns a booking date from the post, slot and date IDs.
	 *
	 * @param int $post_id the post ID.
	 * @param int $slot_id the slot ID.
	 * @param int $date_id the date ID.
	 */
	public function get_date_from_ids( $post_id, $slot_id, $date_id ) {

		$fooevents_bookings_options_serialized = get_post_meta( $post_id, 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );

		if ( ! empty( $fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['date'] ) ) {

			return $fooevents_bookings_options[ $slot_id ]['add_date'][ $date_id ]['date'];

		}

		return;
	}

	/**
	 * Returns a booking slot label from the post and slot ID.
	 *
	 * @param int $post_id the post ID.
	 * @param int $slot_id the slot ID.
	 */
	public function get_label_from_id( $post_id, $slot_id ) {

		$fooevents_bookings_options_serialized = get_post_meta( $post_id, 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );

		if ( ! empty( $fooevents_bookings_options[ $slot_id ]['label'] ) ) {

			return $fooevents_bookings_options[ $slot_id ]['label'];

		}

		return;
	}



	/**
	 * Retrieve booking tickets from order tickets
	 *
	 * @param array $events_order_tickets Array of the orders tickets
	 * @return array
	 */
	private function get_booking_tickets_in_order_tickets( $events_order_tickets ) {

		$return_booking_tickets = array();

		$x = 1;
		foreach ( $events_order_tickets as $event => $tickets ) {

			$y = 1;
			foreach ( $tickets as $ticket ) {

				$events_type = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsType', true );

				if ( 'bookings' === $events_type ) {

					$return_booking_tickets[] = $ticket;

				}

				++$y;

			}

			++$x;

		}

		return $return_booking_tickets;
	}

	/**
	 * Cart quantity
	 *
	 * @param int    $product_quantity product quantity.
	 * @param string $cart_item_key item key.
	 * @param array  $cart_item cart item.
	 */
	public function wc_cart_item_quantity( $product_quantity, $cart_item_key, $cart_item ) {
		if ( is_cart() ) {

			$woocommerce_events_type = get_post_meta( $cart_item['product_id'], 'WooCommerceEventsType', true );

			if ( 'bookings' === $woocommerce_events_type ) {

				$product_quantity = sprintf( '%2$s <input type="hidden" name="cart[%1$s][qty]" value="%2$s" />', $cart_item_key, $cart_item['quantity'] );
			}
		}

		return $product_quantity;
	}

	/**
	 * Array of month names for translation to English
	 *
	 * @param string $event_date event date.
	 * @return string
	 */
	private function convert_month_to_english( $event_date ) {

		$months = array(
			// French.
			'janvier'     => 'January',
			'février'     => 'February',
			'mars'        => 'March',
			'avril'       => 'April',
			'mai'         => 'May',
			'juin'        => 'June',
			'juillet'     => 'July',
			'aout'        => 'August',
			'août'        => 'August',
			'septembre'   => 'September',
			'octobre'     => 'October',

			// German.
			'Januar'      => 'January',
			'Februar'     => 'February',
			'März'        => 'March',
			'Mai'         => 'May',
			'Juni'        => 'June',
			'Juli'        => 'July',
			'Oktober'     => 'October',
			'Dezember'    => 'December',
			'Montag'      => '',
			'Dienstag'    => '',
			'Mittwoch'    => '',
			'Donnerstag'  => '',
			'Freitag'     => '',
			'Samstag'     => '',
			'Sonntag'     => '',

			// Spanish.
			'enero'       => 'January',
			'febrero'     => 'February',
			'marzo'       => 'March',
			'abril'       => 'April',
			'mayo'        => 'May',
			'junio'       => 'June',
			'julio'       => 'July',
			'agosto'      => 'August',
			'septiembre'  => 'September',
			'setiembre'   => 'September',
			'octubre'     => 'October',
			'noviembre'   => 'November',
			'diciembre'   => 'December',
			'novembre'    => 'November',
			'décembre'    => 'December',
			'lunes'       => '',
			'martes'      => '',
			'miércoles'   => '',
			'jueves'      => '',
			'viernes'     => '',
			'sábado'      => '',
			'domingo'     => '',

			// Catalan - Spain.
			'gener'       => 'January',
			'febrer'      => 'February',
			'març'        => 'March',
			'abril'       => 'April',
			'maig'        => 'May',
			'juny'        => 'June',
			'juliol'      => 'July',
			'agost'       => 'August',
			'setembre'    => 'September',
			'octubre'     => 'October',
			'novembre'    => 'November',
			'desembre'    => 'December',

			// Dutch.
			'januari'     => 'January',
			'februari'    => 'February',
			'maart'       => 'March',
			'april'       => 'April',
			'mei'         => 'May',
			'juni'        => 'June',
			'juli'        => 'July',
			'augustus'    => 'August',
			'september'   => 'September',
			'oktober'     => 'October',
			'november'    => 'November',
			'december'    => 'December',
			'maandag'     => '',
			'dinsdag'     => '',
			'woensdag'    => '',
			'donderdag'   => '',
			'vrijdag'     => '',
			'zaterdag'    => '',
			'zondag'      => '',

			// Italian.
			'Gennaio'     => 'January',
			'Febbraio'    => 'February',
			'Marzo'       => 'March',
			'Aprile'      => 'April',
			'Maggio'      => 'May',
			'Giugno'      => 'June',
			'Luglio'      => 'July',
			'Agosto'      => 'August',
			'Settembre'   => 'September',
			'Ottobre'     => 'October',
			'Novembre'    => 'November',
			'Dicembre'    => 'December',

			// Polish.
			'Styczeń'     => 'January',
			'Luty'        => 'February',
			'Marzec'      => 'March',
			'Kwiecień'    => 'April',
			'Maj'         => 'May',
			'Czerwiec'    => 'June',
			'Lipiec'      => 'July',
			'Sierpień'    => 'August',
			'Wrzesień'    => 'September',
			'Październik' => 'October',
			'Listopad'    => 'November',
			'Grudzień'    => 'December',

			// Afrikaans.
			'Januarie'    => 'January',
			'Februarie'   => 'February',
			'Maart'       => 'March',
			'Mei'         => 'May',
			'Junie'       => 'June',
			'Julie'       => 'July',
			'Augustus'    => 'August',
			'Oktober'     => 'October',
			'Desember'    => 'December',

			// Turkish.
			'Ocak'        => 'January',
			'Şubat'       => 'February',
			'Mart'        => 'March',
			'Nisan'       => 'April',
			'Mayıs'       => 'May',
			'Haziran'     => 'June',
			'Temmuz'      => 'July',
			'Ağustos'     => 'August',
			'Eylül'       => 'September',
			'Ekim'        => 'October',
			'Kasım'       => 'November',
			'Aralık'      => 'December',

			// Portuguese.
			'janeiro'     => 'January',
			'fevereiro'   => 'February',
			'março'       => 'March',
			'abril'       => 'April',
			'maio'        => 'May',
			'junho'       => 'June',
			'julho'       => 'July',
			'agosto'      => 'August',
			'setembro'    => 'September',
			'outubro'     => 'October',
			'novembro'    => 'November',
			'dezembro'    => 'December',

			// Swedish.
			'Januari'     => 'January',
			'Februari'    => 'February',
			'Mars'        => 'March',
			'April'       => 'April',
			'Maj'         => 'May',
			'Juni'        => 'June',
			'Juli'        => 'July',
			'Augusti'     => 'August',
			'September'   => 'September',
			'Oktober'     => 'October',
			'November'    => 'November',
			'December'    => 'December',

			// Czech.
			'leden'       => 'January',
			'únor'        => 'February',
			'březen'      => 'March',
			'duben'       => 'April',
			'květen'      => 'May',
			'červen'      => 'June',
			'červenec'    => 'July',
			'srpen'       => 'August',
			'září'        => 'September',
			'říjen'       => 'October',
			'listopad'    => 'November',
			'prosinec'    => 'December',

			// Norwegian.
			'januar'      => 'January',
			'februar'     => 'February',
			'mars'        => 'March',
			'april'       => 'April',
			'mai'         => 'May',
			'juni'        => 'June',
			'juli'        => 'July',
			'august'      => 'August',
			'september'   => 'September',
			'oktober'     => 'October',
			'november'    => 'November',
			'desember'    => 'December',

			// Danish.
			'januar'      => 'January',
			'februar'     => 'February',
			'marts'       => 'March',
			'april'       => 'April',
			'maj'         => 'May',
			'juni'        => 'June',
			'juli'        => 'July',
			'august'      => 'August',
			'september'   => 'September',
			'oktober'     => 'October',
			'november'    => 'November',
			'december'    => 'December',

			// Finnish.
			'tammikuu'    => 'January',
			'helmikuu'    => 'February',
			'maaliskuu'   => 'March',
			'huhtikuu'    => 'April',
			'toukokuu'    => 'May',
			'kesäkuu'     => 'June',
			'heinäkuu'    => 'July',
			'elokuu'      => 'August',
			'syyskuu'     => 'September',
			'lokakuu'     => 'October',
			'marraskuu'   => 'November',
			'joulukuu'    => 'December',

			// Russian.
			'Январь'      => 'January',
			'Февраль'     => 'February',
			'Март'        => 'March',
			'Апрель'      => 'April',
			'Май'         => 'May',
			'Июнь'        => 'June',
			'Июль'        => 'July',
			'Август'      => 'August',
			'Сентябрь'    => 'September',
			'Октябрь'     => 'October',
			'Ноябрь'      => 'November',
			'Декабрь'     => 'December',

			// Icelandic.
			'Janúar'      => 'January',
			'Febrúar'     => 'February',
			'Mars'        => 'March',
			'Apríl'       => 'April',
			'Maí'         => 'May',
			'Júní'        => 'June',
			'Júlí'        => 'July',
			'Ágúst'       => 'August',
			'September'   => 'September',
			'Oktober'     => 'October',
			'Nóvember'    => 'November',
			'Desember'    => 'December',

			// Latvian.
			'janvāris'    => 'January',
			'februāris'   => 'February',
			'marts'       => 'March',
			'aprīlis'     => 'April',
			'maijs'       => 'May',
			'jūnijs'      => 'June',
			'jūlijs'      => 'July',
			'augusts'     => 'August',
			'septembris'  => 'September',
			'oktobris'    => 'October',
			'novembris'   => 'November',
			'decembris'   => 'December',

			// Lithuanian.
			'sausio'      => 'January',
			'vasario'     => 'February',
			'kovo'        => 'March',
			'balandžio'   => 'April',
			'gegužės'     => 'May',
			'birželio'    => 'June',
			'liepos'      => 'July',
			'rugpjūčio'   => 'August',
			'rugsėjo'     => 'September',
			'spalio'      => 'October',
			'lapkričio'   => 'November',
			'gruodžio'    => 'December',

			// Estonian.
			'jaanuar'     => 'January',
			'veebruar'    => 'February',
			'märts'       => 'March',
			'aprill'      => 'April',
			'mai'         => 'May',
			'juuni'       => 'June',
			'juuli'       => 'July',
			'august'      => 'August',
			'september'   => 'September',
			'oktoober'    => 'October',
			'november'    => 'November',
			'detsember'   => 'December',

			// Greek.
			'Ιανουάριος'  => 'January',
			'Φεβρουάριος' => 'February',
			'Μάρτιος'     => 'March',
			'Απρίλιος'    => 'April',
			'Μάιος'       => 'May',
			'Ιούνιος'     => 'June',
			'Ιούλιος'     => 'July',
			'Αύγουστος'   => 'August',
			'Σεπτέμβριος' => 'September',
			'Οκτώβριος'   => 'October',
			'Νοέμβριος'   => 'November',
			'Δεκέμβριος'  => 'December',

			// Slovak - Slovakia.
			'január'      => 'January',
			'február'     => 'February',
			'marec'       => 'March',
			'apríl'       => 'April',
			'máj'         => 'May',
			'jún'         => 'June',
			'júl'         => 'July',
			'august'      => 'August',
			'september'   => 'September',
			'október'     => 'October',
			'november'    => 'November',
			'december'    => 'December',

			// Slovenian - Slovenia.
			'januar'      => 'January',
			'februar'     => 'February',
			'marec'       => 'March',
			'april'       => 'April',
			'maj'         => 'May',
			'junij'       => 'June',
			'julij'       => 'July',
			'avgust'      => 'August',
			'september'   => 'September',
			'oktober'     => 'October',
			'november'    => 'November',
			'december'    => 'December',

			// Romanian - Romania.
			'ianuarie'    => 'January',
			'februarie'   => 'February',
			'martie'      => 'March',
			'aprilie'     => 'April',
			'mai'         => 'May',
			'iunie'       => 'June',
			'iulie'       => 'July',
			'august'      => 'August',
			'septembrie'  => 'September',
			'octombrie'   => 'October',
			'noiembrie'   => 'November',
			'decembrie'   => 'December',

			// Croatian - Croatia.

			'siječanj'    => 'January',
			'veljača'     => 'February',
			'ožujak'      => 'March',
			'travanj'     => 'April',
			'svibanj'     => 'May',
			'lipanj'      => 'June',
			'srpanj'      => 'July',
			'kolovoz'     => 'August',
			'rujan'       => 'September',
			'listopad'    => 'October',
			'studeni'     => 'November',
			'prosinac'    => 'December',

			// Bulgarian - Bulgaria.

			'януари'      => 'January',
			'февруари'    => 'February',
			'март'        => 'March',
			'април'       => 'April',
			'май'         => 'May',
			'юни'         => 'June',
			'юли'         => 'July',
			'август'      => 'August',
			'септември'   => 'September',
			'октомври'    => 'October',
			'ноември'     => 'November',
			'декември'    => 'December',
		);

		$pattern     = array_keys( $months );
		$replacement = array_values( $months );

		foreach ( $pattern as $key => $value ) {
			$pattern[ $key ] = '/\b' . $value . '\b/iu';
		}

		$replaced_event_date = preg_replace( $pattern, $replacement, $event_date );

		$replaced_event_date = str_replace( ' de ', ' ', $replaced_event_date );

		return $replaced_event_date;
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
	 * Outputs notices to screen.
	 *
	 * @param array $notices notices to output.
	 */
	private function output_notices( $notices ) {

		foreach ( $notices as $notice ) {

			echo '<div class="updated"><p>' . esc_attr( $notice ) . '</p></div>';

		}
	}

	/**
	 * Outputs dismissible notices to screen.
	 *
	 * @param array $notices notices to output.
	 */
	private function output_notice_dismissible( $notice, $page, $check_field ) {

		if ( ! empty( $notice ) ) {

			$user_id = get_current_user_id();

			if ( ! get_user_meta( $user_id, $check_field ) ) {

				echo '<div class="notice notice-info"><p>' . wp_kses_post( $notice ) . ' <a href="admin.php?page=' . esc_attr( $page ) . '&fooevents_notice=' . esc_attr( $check_field ) . '">Dismiss</a></p></div>';

			}
		}
	}
}
