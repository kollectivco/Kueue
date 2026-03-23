<?php
/**
 * WooCommerce helper class
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

// TODO: Remove order utilitiy when WC 8.0 released.
use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * WooCommerce helper class
 */
class FooEvents_Woo_Helper {

	/**
	 * Configuration object
	 *
	 * @var array $config
	 */
	public $config;

	/**
	 * Ticket helper object
	 *
	 * @var array $ticket_helper
	 */
	public $ticket_helper;

	/**
	 * Barcode helper object
	 *
	 * @var array $barcode_helper
	 */
	private $barcode_helper;

	/**
	 * Barcode helper object
	 *
	 * @var array $mail_helper
	 */
	public $mail_helper;

	/**
	 * Zoom helper object
	 *
	 * @var array $zoom_api_helper
	 */
	public $zoom_api_helper;

	/**
	 * Mailchimp helper object
	 *
	 * @var object $mailchimp_helper Mailchimp helper object
	 */
	private $mailchimp_helper;

	/**
	 * On class load
	 *
	 * @param array $config configuration values.
	 */
	public function __construct( $config ) {

		$this->check_woocommerce_exists();
		$this->config = $config;

		// TicketHelper.
		require_once $this->config->class_path . 'class-fooevents-ticket-helper.php';
		$this->ticket_helper = new FooEvents_Ticket_Helper( $this->config );

		// BarcodeHelper.
		require_once $this->config->class_path . 'class-fooevents-barcode-helper.php';
		$this->barcode_helper = new FooEvents_Barcode_Helper( $this->config );

		// MailHelper.
		require_once $this->config->class_path . 'class-fooevents-mail-helper.php';
		$this->mail_helper = new FooEvents_Mail_Helper( $this->config );

		// ZoomAPIHelper.
		require_once $this->config->class_path . 'class-fooevents-zoom-api-helper.php';
		$this->zoom_api_helper = new FooEvents_Zoom_API_Helper( $this->config );

		// MailchimpHelper.
		require_once $this->config->class_path . 'class-fooevents-mailchimp-helper.php';
		$this->mailchimp_helper = new FooEvents_Mailchimp_Helper( $this->config );

		add_action( 'woocommerce_product_tabs', array( &$this, 'add_front_end_tab' ), 10, 2 );

		add_action( 'woocommerce_order_status_changed', array( &$this, 'process_order_tickets' ), 99, 3 );
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_product_options_tab' ), 21 );
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_product_ticket_tab' ), 22 );
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_product_terminology_tab' ), 23 );
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_product_exports_tab' ), 26 );
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_product_printing_tab' ), 27 );
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_product_expiration_tab' ), 28 );
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'add_product_integration_tab' ), 29 );

		add_action( 'woocommerce_product_data_panels', array( $this, 'add_product_options_tab_options' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'process_meta_box' ) );
		add_action( 'wp_ajax_woocommerce_events_csv', array( $this, 'woocommerce_events_csv' ) );
		add_action( 'wp_ajax_woocommerce_events_attendee_badges', array( $this, 'woocommerce_events_attendee_badges' ) );
		add_action( 'wp_ajax_fooevents_save_printing_options', array( $this, 'save_printing_options' ) );

		add_action( 'woocommerce_thankyou_order_received_text', array( $this, 'display_thank_you_text' ) );

		add_action( 'woocommerce_order_status_cancelled', array( $this, 'order_status_cancelled' ) );
		add_action( 'woocommerce_order_status_failed', array( $this, 'order_status_failed' ) );
		add_action( 'woocommerce_order_status_completed', array( &$this, 'order_status_completed_cancelled' ), 10, 1 );

		add_filter( 'woocommerce_events_meta_format', 'wptexturize' );
		add_filter( 'woocommerce_events_meta_format', 'convert_smilies' );
		add_filter( 'woocommerce_events_meta_format', 'convert_chars' );
		add_filter( 'woocommerce_events_meta_format', 'wpautop' );
		add_filter( 'woocommerce_events_meta_format', 'shortcode_unautop' );
		add_filter( 'woocommerce_events_meta_format', 'prepend_attachment' );

		add_filter( 'parse_query', array( $this, 'filter_product_results' ) );
		add_filter( 'restrict_manage_posts', array( $this, 'filter_product_options' ) );

		add_filter( 'woocommerce_get_catalog_ordering_args', array( $this, 'add_postmeta_ordering' ) );
		add_filter( 'woocommerce_default_catalog_orderby_options', array( $this, 'add_postmeta_orderby' ) );
		add_filter( 'woocommerce_catalog_orderby', array( $this, 'add_postmeta_orderby' ) );
		add_filter( 'woocommerce_before_shop_loop_item_title', array( $this, 'display_product_date' ), 35 );
	}

	/**
	 * Checks if the WooCommerce plugin exists
	 */
	public function check_woocommerce_exists() {

		if ( ! class_exists( 'WooCommerce' ) ) {

			$this->output_notices( array( __( 'WooCommerce is required for FooEvents. Please install and activate the latest version of WooCommerce.', 'woocommerce-events' ) ) );

		}
	}

	/**
	 * Add options tab to WooCommerce meta box
	 */
	public function add_product_options_tab() {

		echo '<li class="custom_tab_fooevents"><a href="#fooevents_options">' . esc_attr__( 'Event Settings', 'woocommerce-events' ) . '</a></li>';
	}

	/**
	 * Displays the event form
	 *
	 * @global object $post
	 */
	public function add_product_options_tab_options() {

		global $post;

		$woocommerce_events_event = get_post_meta( $post->ID, 'WooCommerceEventsEvent', true );
		$woocommerce_events_type  = get_post_meta( $post->ID, 'WooCommerceEventsType', true );
		// LEGACY: 20201110.
		$woocommerce_events_multi_day_type = get_post_meta( $post->ID, 'WooCommerceEventsMultiDayType', true );

		if ( empty( $woocommerce_events_type ) && ( 'sequential' === $woocommerce_events_multi_day_type || 'select' === $woocommerce_events_multi_day_type ) ) {

			$woocommerce_events_type = $woocommerce_events_multi_day_type;
			// ENDLEGACY: 20201110.
		} elseif ( empty( $woocommerce_events_type ) ) {

			$woocommerce_events_type = 'single';

		}

		$is_checkout_block_enabled = $this->is_checkout_block_enabled();

		$woocommerce_events_date                   = get_post_meta( $post->ID, 'WooCommerceEventsDate', true );
		$woocommerce_events_hour                   = get_post_meta( $post->ID, 'WooCommerceEventsHour', true );
		$woocommerce_events_period                 = get_post_meta( $post->ID, 'WooCommerceEventsPeriod', true );
		$woocommerce_events_minutes                = get_post_meta( $post->ID, 'WooCommerceEventsMinutes', true );
		$woocommerce_events_hour_end               = get_post_meta( $post->ID, 'WooCommerceEventsHourEnd', true );
		$woocommerce_events_minutes_end            = get_post_meta( $post->ID, 'WooCommerceEventsMinutesEnd', true );
		$woocommerce_events_end_period             = get_post_meta( $post->ID, 'WooCommerceEventsEndPeriod', true );
		$woocommerce_events_expire                 = get_post_meta( $post->ID, 'WooCommerceEventsExpire', true );
		$woocommerce_events_expire_message         = get_post_meta( $post->ID, 'WooCommerceEventsExpireMessage', true );
		$woocommerce_events_ticket_expiration_type = get_post_meta( $post->ID, 'WooCommerceEventsTicketExpirationType', true );
		$woocommerce_events_tickets_expire_select  = get_post_meta( $post->ID, 'WooCommerceEventsTicketsExpireSelect', true );
		$woocommerce_events_tickets_expire_value   = get_post_meta( $post->ID, 'WooCommerceEventsTicketsExpireValue', true );
		$woocommerce_events_tickets_expire_unit    = get_post_meta( $post->ID, 'WooCommerceEventsTicketsExpireUnit', true );
		$woocommerce_events_timezone               = get_post_meta( $post->ID, 'WooCommerceEventsTimeZone', true );
		$woocommerce_events_location               = get_post_meta( $post->ID, 'WooCommerceEventsLocation', true );

		$woocommerce_events_print_ticket_logo   = get_post_meta( $post->ID, 'WooCommerceEventsPrintTicketLogo', true );
		$woocommerce_events_print_custom_text   = get_post_meta( $post->ID, 'WooCommerceEventsPrintCustomText', true );
		$woocommerce_events_ticket_logo         = get_post_meta( $post->ID, 'WooCommerceEventsTicketLogo', true );
		$woocommerce_events_ticket_header_image = get_post_meta( $post->ID, 'WooCommerceEventsTicketHeaderImage', true );
		$woocommerce_events_support_contact     = get_post_meta( $post->ID, 'WooCommerceEventsSupportContact', true );
		$woocommerce_events_gps                 = get_post_meta( $post->ID, 'WooCommerceEventsGPS', true );

		$global_woocommerce_events_google_maps_method = get_option( 'globalWooCommerceEventsGoogleMapsMethod', '' );
		$woocommerce_events_google_maps               = get_post_meta( $post->ID, 'WooCommerceEventsGoogleMaps', true );

		$woocommerce_events_directions                    = get_post_meta( $post->ID, 'WooCommerceEventsDirections', true );
		$woocommerce_events_email                         = get_post_meta( $post->ID, 'WooCommerceEventsEmail', true );
		$woocommerce_events_ticket_background_color       = get_post_meta( $post->ID, 'WooCommerceEventsTicketBackgroundColor', true );
		$woocommerce_events_ticket_button_color           = get_post_meta( $post->ID, 'WooCommerceEventsTicketButtonColor', true );
		$woocommerce_events_ticket_text_color             = get_post_meta( $post->ID, 'WooCommerceEventsTicketTextColor', true );
		$woocommerce_events_ticket_purchaser_details      = get_post_meta( $post->ID, 'WooCommerceEventsTicketPurchaserDetails', true );
		$woocommerce_events_ticket_add_calendar           = get_post_meta( $post->ID, 'WooCommerceEventsTicketAddCalendar', true );
		$woocommerce_events_ticket_add_calendar_reminders = get_post_meta( $post->ID, 'WooCommerceEventsTicketAddCalendarReminders', true );
		$woocommerce_events_ticket_attach_ics             = get_post_meta( $post->ID, 'WooCommerceEventsTicketAttachICS', true );

		if ( '' === $woocommerce_events_ticket_add_calendar_reminders ) {

			$woocommerce_events_ticket_add_calendar_reminders = array(
				array(
					'amount' => 1,
					'unit'   => 'weeks',
				),
				array(
					'amount' => 1,
					'unit'   => 'days',
				),
				array(
					'amount' => 1,
					'unit'   => 'hours',
				),
				array(
					'amount' => 15,
					'unit'   => 'minutes',
				),
			);

		}

		$woocommerce_events_ticket_display_date_time     = get_post_meta( $post->ID, 'WooCommerceEventsTicketDisplayDateTime', true );
		$woocommerce_events_ticket_display_barcode       = get_post_meta( $post->ID, 'WooCommerceEventsTicketDisplayBarcode', true );
		$woocommerce_events_ticket_display_price         = get_post_meta( $post->ID, 'WooCommerceEventsTicketDisplayPrice', true );
		$woocommerce_events_ticket_display_bookings      = get_post_meta( $post->ID, 'WooCommerceEventsTicketDisplayBookings', true );
		$woocommerce_events_ticket_display_multi_day     = get_post_meta( $post->ID, 'WooCommerceEventsTicketDisplayMultiDay', true );
		$woocommerce_events_ticket_display_zoom          = get_post_meta( $post->ID, 'WooCommerceEventsTicketDisplayZoom', true );
		$woocommerce_events_addtowallet_logo             = get_post_meta( $post->ID, 'WooCommerceEventsAddToWalletLogo', true );
		$woocommerce_events_addtowallet_image            = get_post_meta( $post->ID, 'WooCommerceEventsAddToWalletImage', true );
		$woocommerce_events_addtowallet_date_label       = get_post_meta( $post->ID, 'WooCommerceEventsAddToWalletDateLabel', true );
		$woocommerce_events_addtowallet_date_value       = get_post_meta( $post->ID, 'WooCommerceEventsAddToWalletDateValue', true );
		$woocommerce_events_addtowallet_attendee_label   = get_post_meta( $post->ID, 'WooCommerceEventsAddToWalletAttendeeLabel', true );
		$woocommerce_events_addtowallet_location_label   = get_post_meta( $post->ID, 'WooCommerceEventsAddToWalletLocationLabel', true );
		$woocommerce_events_addtowallet_card_title       = get_post_meta( $post->ID, 'WooCommerceEventsAddToWalletCardTitle', true );
		$woocommerce_events_addtowallet_card_header      = get_post_meta( $post->ID, 'WooCommerceEventsAddToWalletCardHeader', true );
		$woocommerce_events_display_addtowallet          = get_post_meta( $post->ID, 'WooCommerceEventsTicketDisplayAddToWallet', true );
		$woocommerce_events_display_addtowallet_date     = get_post_meta( $post->ID, 'WooCommerceEventsDisplayAddToWalletDate', true );
		$woocommerce_events_display_addtowallet_attendee = get_post_meta( $post->ID, 'WooCommerceEventsDisplayAddToWalletAttendee', true );
		$woocommerce_events_display_addtowallet_location = get_post_meta( $post->ID, 'WooCommerceEventsDisplayAddToWalletLocation', true );
		$woocommerce_events_addtowallet_background_color = get_post_meta( $post->ID, 'WooCommerceEventsAddToWalletBackgroundColor', true );
		$woocommerce_events_ticket_text                  = get_post_meta( $post->ID, 'WooCommerceEventsTicketText', true );
		$woocommerce_events_thank_you_text               = get_post_meta( $post->ID, 'WooCommerceEventsThankYouText', true );
		$woocommerce_events_event_details_text           = get_post_meta( $post->ID, 'WooCommerceEventsEventDetailsText', true );
		$woocommerce_events_background_color             = get_post_meta( $post->ID, 'WooCommerceEventsBackgroundColor', true );
		$woocommerce_events_text_color                   = get_post_meta( $post->ID, 'WooCommerceEventsTextColor', true );
		$woocommerce_events_capture_attendee_details     = get_post_meta( $post->ID, 'WooCommerceEventsCaptureAttendeeDetails', true );
		$woocommerce_events_capture_attendee_email       = get_post_meta( $post->ID, 'WooCommerceEventsCaptureAttendeeEmail', true );
		$woocommerce_events_email_attendee               = get_post_meta( $post->ID, 'WooCommerceEventsEmailAttendee', true );
		$woocommerce_events_send_email_tickets           = get_post_meta( $post->ID, 'WooCommerceEventsSendEmailTickets', true );
		$woocommerce_events_ticket_identifier_output     = get_post_meta( $post->ID, 'WooCommerceEventsTicketIdentifierOutput', true );
		$woocommerce_events_ticket_number_output         = get_post_meta( $post->ID, 'WooCommerceEventsTicketNumberOutput', true );
		$woocommerce_events_ticket_number_prefix         = get_post_meta( $post->ID, 'WooCommerceEventsTicketNumberPrefix', true );
		$woocommerce_events_ticket_number_suffix         = get_post_meta( $post->ID, 'WooCommerceEventsTicketNumberSuffix', true );
		$woocommerce_events_enable_ticket_identifier     = get_post_meta( $post->ID, 'WooCommerceEventsEnableTicketIdentifier', true );
		$woocommerce_events_capture_attendee_telephone   = get_post_meta( $post->ID, 'WooCommerceEventsCaptureAttendeeTelephone', true );
		$woocommerce_events_capture_attendee_company     = get_post_meta( $post->ID, 'WooCommerceEventsCaptureAttendeeCompany', true );
		$woocommerce_events_capture_attendee_designation = get_post_meta( $post->ID, 'WooCommerceEventsCaptureAttendeeDesignation', true );
		$woocommerce_events_unique_email                 = get_post_meta( $post->ID, 'WooCommerceEventsUniqueEmail', true );

		$woocommerce_events_view_seating_options                = get_post_meta( $post->ID, 'WooCommerceEventsViewSeatingOptions', true );
		$woocommerce_events_view_seating_chart                  = get_post_meta( $post->ID, 'WooCommerceEventsViewSeatingChart', true );
		$woocommerce_events_view_seating_other_variations       = get_post_meta( $post->ID, 'WooCommerceEventsViewSeatingOtherVariations', true );
		$woocommerce_events_view_bookings_options               = get_post_meta( $post->ID, 'WooCommerceEventsViewBookingsOptions', true );
		$woocommerce_events_hide_bookings_display_time          = get_post_meta( $post->ID, 'WooCommerceEventsHideBookingsDisplayTime', true );
		$woocommerce_events_hide_bookings_slot_calendar         = get_post_meta( $post->ID, 'WooCommerceEventsHideBookingsSlotCalendar', true );
		$woocommerce_events_hide_bookings_stock_availability    = get_post_meta( $post->ID, 'WooCommerceEventsHideBookingsStockAvailability', true );
		$woocommerce_events_view_bookings_stock_dropdowns       = get_post_meta( $post->ID, 'WooCommerceEventsViewBookingsStockDropdowns', true );
		$woocommerce_events_view_out_of_stock_bookings          = get_post_meta( $post->ID, 'WooCommerceEventsViewOutOfStockBookings', true );
		$woocommerce_events_bookings_method                     = get_post_meta( $post->ID, 'WooCommerceEventsBookingsMethod', true );
		$woocommerce_events_bookings_hide_date_single_drop_down = get_post_meta( $post->ID, 'WooCommerceEventsBookingsHideDateSingleDropDown', true );

		$woocommerce_events_event_details_new_order    = get_post_meta( $post->ID, 'WooCommerceEventsEventDetailsNewOrder', true );
		$woocommerce_events_display_attendee_new_order = get_post_meta( $post->ID, 'WooCommerceEventsDisplayAttendeeNewOrder', true );
		$woocommerce_events_display_bookings_new_order = get_post_meta( $post->ID, 'WooCommerceEventsDisplayBookingsNewOrder', true );
		$woocommerce_events_display_seatings_new_order = get_post_meta( $post->ID, 'WooCommerceEventsDisplaySeatingsNewOrder', true );
		$woocommerce_events_display_cust_att_new_order = get_post_meta( $post->ID, 'WooCommerceEventsDisplayCustAttNewOrder', true );

		$woocommerce_events_export_unpaid_tickets  = get_post_meta( $post->ID, 'WooCommerceEventsExportUnpaidTickets', true );
		$woocommerce_events_export_billing_details = get_post_meta( $post->ID, 'WooCommerceEventsExportBillingDetails', true );

		$woocommerce_print_ticket_size       = get_post_meta( $post->ID, 'WooCommercePrintTicketSize', true );
		$woocommerce_print_ticket_nr_columns = get_post_meta( $post->ID, 'WooCommercePrintTicketNrColumns', true );
		$woocommerce_print_ticket_nr_rows    = get_post_meta( $post->ID, 'WooCommercePrintTicketNrRows', true );

		$woocommerce_badge_field_top_left      = get_post_meta( $post->ID, 'WooCommerceBadgeFieldTopLeft', true );
		$woocommerce_badge_field_top_middle    = get_post_meta( $post->ID, 'WooCommerceBadgeFieldTopMiddle', true );
		$woocommerce_badge_field_top_right     = get_post_meta( $post->ID, 'WooCommerceBadgeFieldTopRight', true );
		$woocommerce_badge_field_a_4           = get_post_meta( $post->ID, 'WooCommerceBadgeField_a_4', true );
		$woocommerce_badge_field_middle_left   = get_post_meta( $post->ID, 'WooCommerceBadgeFieldMiddleLeft', true );
		$woocommerce_badge_field_middle_middle = get_post_meta( $post->ID, 'WooCommerceBadgeFieldMiddleMiddle', true );
		$woocommerce_badge_field_middle_right  = get_post_meta( $post->ID, 'WooCommerceBadgeFieldMiddleRight', true );
		$woocommerce_badge_field_b_4           = get_post_meta( $post->ID, 'WooCommerceBadgeField_b_4', true );
		$woocommerce_badge_field_bottom_left   = get_post_meta( $post->ID, 'WooCommerceBadgeFieldBottomLeft', true );
		$woocommerce_badge_field_bottom_middle = get_post_meta( $post->ID, 'WooCommerceBadgeFieldBottomMiddle', true );
		$woocommerce_badge_field_bottom_right  = get_post_meta( $post->ID, 'WooCommerceBadgeFieldBottomRight', true );
		$woocommerce_badge_field_c_4           = get_post_meta( $post->ID, 'WooCommerceBadgeField_c_4', true );
		$woocommerce_badge_field_d_1           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_1', true );
		$woocommerce_badge_field_d_2           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_2', true );
		$woocommerce_badge_field_d_3           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_3', true );
		$woocommerce_badge_field_d_4           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_4', true );

		$woocommerce_badge_field_top_left_font      = get_post_meta( $post->ID, 'WooCommerceBadgeFieldTopLeft_font', true );
		$woocommerce_badge_field_top_middle_font    = get_post_meta( $post->ID, 'WooCommerceBadgeFieldTopMiddle_font', true );
		$woocommerce_badge_field_top_right_font     = get_post_meta( $post->ID, 'WooCommerceBadgeFieldTopRight_font', true );
		$woocommerce_badge_field_a_4_font           = get_post_meta( $post->ID, 'WooCommerceBadgeField_a_4_font', true );
		$woocommerce_badge_field_middle_left_font   = get_post_meta( $post->ID, 'WooCommerceBadgeFieldMiddleLeft_font', true );
		$woocommerce_badge_field_middle_middle_font = get_post_meta( $post->ID, 'WooCommerceBadgeFieldMiddleMiddle_font', true );
		$woocommerce_badge_field_middle_right_font  = get_post_meta( $post->ID, 'WooCommerceBadgeFieldMiddleRight_font', true );
		$woocommerce_badge_field_b_4_font           = get_post_meta( $post->ID, 'WooCommerceBadgeField_b_4_font', true );
		$woocommerce_badge_field_bottom_left_font   = get_post_meta( $post->ID, 'WooCommerceBadgeFieldBottomLeft_font', true );
		$woocommerce_badge_field_bottom_middle_font = get_post_meta( $post->ID, 'WooCommerceBadgeFieldBottomMiddle_font', true );
		$woocommerce_badge_field_bottom_right_font  = get_post_meta( $post->ID, 'WooCommerceBadgeFieldBottomRight_font', true );
		$woocommerce_badge_field_c_4_font           = get_post_meta( $post->ID, 'WooCommerceBadgeField_c_4_font', true );
		$woocommerce_badge_field_d_1_font           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_1_font', true );
		$woocommerce_badge_field_d_2_font           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_2_font', true );
		$woocommerce_badge_field_d_3_font           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_3_font', true );
		$woocommerce_badge_field_d_4_font           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_4_font', true );

		$woocommerce_badge_field_top_left_seat_label      = get_post_meta( $post->ID, 'WooCommerceBadgeFieldTopLeft_seat_label', true );
		$woocommerce_badge_field_top_middle_seat_label    = get_post_meta( $post->ID, 'WooCommerceBadgeFieldTopMiddle_seat_label', true );
		$woocommerce_badge_field_top_right_seat_label     = get_post_meta( $post->ID, 'WooCommerceBadgeFieldTopRight_seat_label', true );
		$woocommerce_badge_field_a_4_seat_label           = get_post_meta( $post->ID, 'WooCommerceBadgeField_a_4_seat_label', true );
		$woocommerce_badge_field_middle_left_seat_label   = get_post_meta( $post->ID, 'WooCommerceBadgeFieldMiddleLeft_seat_label', true );
		$woocommerce_badge_field_middle_middle_seat_label = get_post_meta( $post->ID, 'WooCommerceBadgeFieldMiddleMiddle_seat_label', true );
		$woocommerce_badge_field_middle_right_seat_label  = get_post_meta( $post->ID, 'WooCommerceBadgeFieldMiddleRight_seat_label', true );
		$woocommerce_badge_field_b_4_seat_label           = get_post_meta( $post->ID, 'WooCommerceBadgeField_b_4_seat_label', true );
		$woocommerce_badge_field_bottom_left_seat_label   = get_post_meta( $post->ID, 'WooCommerceBadgeFieldBottomLeft_seat_label', true );
		$woocommerce_badge_field_bottom_middle_seat_label = get_post_meta( $post->ID, 'WooCommerceBadgeFieldBottomMiddle_seat_label', true );
		$woocommerce_badge_field_bottom_right_seat_label  = get_post_meta( $post->ID, 'WooCommerceBadgeFieldBottomRight_seat_label', true );
		$woocommerce_badge_field_c_4_seat_label           = get_post_meta( $post->ID, 'WooCommerceBadgeField_c_4_seat_label', true );
		$woocommerce_badge_field_d_1_seat_label           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_1_seat_label', true );
		$woocommerce_badge_field_d_2_seat_label           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_2_seat_label', true );
		$woocommerce_badge_field_d_3_seat_label           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_3_seat_label', true );
		$woocommerce_badge_field_d_4_seat_label           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_4_seat_label', true );

		$woocommerce_badge_field_top_left_logo      = get_post_meta( $post->ID, 'WooCommerceBadgeFieldTopLeft_logo', true );
		$woocommerce_badge_field_top_middle_logo    = get_post_meta( $post->ID, 'WooCommerceBadgeFieldTopMiddle_logo', true );
		$woocommerce_badge_field_top_right_logo     = get_post_meta( $post->ID, 'WooCommerceBadgeFieldTopRight_logo', true );
		$woocommerce_badge_field_a_4_logo           = get_post_meta( $post->ID, 'WooCommerceBadgeField_a_4_logo', true );
		$woocommerce_badge_field_middle_left_logo   = get_post_meta( $post->ID, 'WooCommerceBadgeFieldMiddleLeft_logo', true );
		$woocommerce_badge_field_middle_middle_logo = get_post_meta( $post->ID, 'WooCommerceBadgeFieldMiddleMiddle_logo', true );
		$woocommerce_badge_field_middle_right_logo  = get_post_meta( $post->ID, 'WooCommerceBadgeFieldMiddleRight_logo', true );
		$woocommerce_badge_field_b_4_logo           = get_post_meta( $post->ID, 'WooCommerceBadgeField_b_4_logo', true );
		$woocommerce_badge_field_bottom_left_logo   = get_post_meta( $post->ID, 'WooCommerceBadgeFieldBottomLeft_logo', true );
		$woocommerce_badge_field_bottom_middle_logo = get_post_meta( $post->ID, 'WooCommerceBadgeFieldBottomMiddle_logo', true );
		$woocommerce_badge_field_bottom_right_logo  = get_post_meta( $post->ID, 'WooCommerceBadgeFieldBottomRight_logo', true );
		$woocommerce_badge_field_bottom_c_4_logo    = get_post_meta( $post->ID, 'WooCommerceBadgeField_c_4_logo', true );
		$woocommerce_badge_field_bottom_d_1_logo    = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_1_logo', true );
		$woocommerce_badge_field_bottom_d_2_logo    = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_2_logo', true );
		$woocommerce_badge_field_bottom_d_3_logo    = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_3_logo', true );
		$woocommerce_badge_field_bottom_d_4_logo    = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_4_logo', true );

		$woocommerce_badge_field_top_left_custom      = get_post_meta( $post->ID, 'WooCommerceBadgeFieldTopLeft_custom', true );
		$woocommerce_badge_field_top_middle_custom    = get_post_meta( $post->ID, 'WooCommerceBadgeFieldTopMiddle_custom', true );
		$woocommerce_badge_field_top_right_custom     = get_post_meta( $post->ID, 'WooCommerceBadgeFieldTopRight_custom', true );
		$woocommerce_badge_field_a_4_custom           = get_post_meta( $post->ID, 'WooCommerceBadgeField_a_4_custom', true );
		$woocommerce_badge_field_middle_left_custom   = get_post_meta( $post->ID, 'WooCommerceBadgeFieldMiddleLeft_custom', true );
		$woocommerce_badge_field_middle_middle_custom = get_post_meta( $post->ID, 'WooCommerceBadgeFieldMiddleMiddle_custom', true );
		$woocommerce_badge_field_middle_right_custom  = get_post_meta( $post->ID, 'WooCommerceBadgeFieldMiddleRight_custom', true );
		$woocommerce_badge_field_b_4_custom           = get_post_meta( $post->ID, 'WooCommerceBadgeField_b_4_custom', true );
		$woocommerce_badge_field_bottom_left_custom   = get_post_meta( $post->ID, 'WooCommerceBadgeFieldBottomLeft_custom', true );
		$woocommerce_badge_field_bottom_middle_custom = get_post_meta( $post->ID, 'WooCommerceBadgeFieldBottomMiddle_custom', true );
		$woocommerce_badge_field_bottom_right_custom  = get_post_meta( $post->ID, 'WooCommerceBadgeFieldBottomRight_custom', true );
		$woocommerce_badge_field_c_4_custom           = get_post_meta( $post->ID, 'WooCommerceBadgeField_c_4_custom', true );
		$woocommerce_badge_field_d_1_custom           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_1_custom', true );
		$woocommerce_badge_field_d_2_custom           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_2_custom', true );
		$woocommerce_badge_field_d_3_custom           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_3_custom', true );
		$woocommerce_badge_field_d_4_custom           = get_post_meta( $post->ID, 'WooCommerceBadgeField_d_4_custom', true );

		$woocommerce_print_ticket_sort    = get_post_meta( $post->ID, 'WooCommercePrintTicketSort', true );
		$woocommerce_print_ticket_numbers = get_post_meta( $post->ID, 'WooCommercePrintTicketNumbers', true );
		$woocommerce_print_ticket_orders  = get_post_meta( $post->ID, 'WooCommercePrintTicketOrders', true );

		$woocommerce_events_cut_lines_print_ticket  = get_post_meta( $post->ID, 'WooCommerceEventsCutLinesPrintTicket', true );
		$woocommerce_events_ticket_background_image = get_post_meta( $post->ID, 'WooCommerceEventsTicketBackgroundImage', true );

		$woocommerce_events_email_subject_single = get_post_meta( $post->ID, 'WooCommerceEventsEmailSubjectSingle', true );
		$woocommerce_events_ticket_theme         = get_post_meta( $post->ID, 'WooCommerceEventsTicketTheme', true );

		$woocommerce_events_attendee_override             = get_post_meta( $post->ID, 'WooCommerceEventsAttendeeOverride', true );
		$woocommerce_events_attendee_override_plural      = get_post_meta( $post->ID, 'WooCommerceEventsAttendeeOverridePlural', true );
		$woocommerce_events_event_details_override        = get_post_meta( $post->ID, 'WooCommerceEventsEventDetailsOverride', true );
		$woocommerce_events_event_details_override_plural = get_post_meta( $post->ID, 'WooCommerceEventsEventDetailsOverridePlural', true );
		$woocommerce_events_ticket_override               = get_post_meta( $post->ID, 'WooCommerceEventsTicketOverride', true );
		$woocommerce_events_ticket_override_plural        = get_post_meta( $post->ID, 'WooCommerceEventsTicketOverridePlural', true );

		$woocommerce_events_view_seating_chart            = get_post_meta( $post->ID, 'WooCommerceEventsViewSeatingChart', true );
		$woocommerce_events_view_seating_other_variations = get_post_meta( $post->ID, 'WooCommerceEventsViewSeatingOtherVariations', true );

		$woocommerce_events_hide_bookings_display_time       = get_post_meta( $post->ID, 'WooCommerceEventsHideBookingsDisplayTime', true );
		$woocommerce_events_hide_bookings_stock_availability = get_post_meta( $post->ID, 'WooCommerceEventsHideBookingsStockAvailability', true );
		$woocommerce_events_view_bookings_stock_dropdowns    = get_post_meta( $post->ID, 'WooCommerceEventsViewBookingsStockDropdowns', true );
		$woocommerce_events_view_out_of_stock_bookings       = get_post_meta( $post->ID, 'WooCommerceEventsViewOutOfStockBookings', true );

		$woocommerce_events_zoom_multi_option  = get_post_meta( $post->ID, 'WooCommerceEventsZoomMultiOption', true );
		$woocommerce_events_zoom_webinar       = get_post_meta( $post->ID, 'WooCommerceEventsZoomWebinar', true );
		$woocommerce_events_zoom_webinar_multi = get_post_meta( $post->ID, 'WooCommerceEventsZoomWebinarMulti', true );

		$tzlist = DateTimeZone::listIdentifiers( DateTimeZone::ALL );

		$global_woocommerce_events_google_maps_api_key = get_option( 'globalWooCommerceEventsGoogleMapsAPIKey', true );

		if ( $is_checkout_block_enabled ) {

			$woocommerce_events_view_bookings_options    = 'product';
			$woocommerce_events_view_seating_chart       = 'off';
			$woocommerce_events_capture_attendee_details = '';

		}

		if ( 1 === (int) $global_woocommerce_events_google_maps_api_key ) {

			$global_woocommerce_events_google_maps_api_key = '';

		}

		if ( empty( $woocommerce_events_email_subject_single ) ) {

			$woocommerce_events_email_subject_single = __( '{OrderNumber} Ticket', 'woocommerce-events' );

		}

		$global_woocommerce_events_ticket_background_color = get_option( 'globalWooCommerceEventsTicketBackgroundColor', true );

		if ( 1 === (int) $global_woocommerce_events_ticket_background_color ) {

			$global_woocommerce_events_ticket_background_color = '';

		}

		$global_woocommerce_events_ticket_button_color = get_option( 'globalWooCommerceEventsTicketButtonColor', true );
		if ( 1 === (int) $global_woocommerce_events_ticket_button_color ) {

			$global_woocommerce_events_ticket_button_color = '';

		}

		$global_woocommerce_events_ticket_text_color = get_option( 'globalWooCommerceEventsTicketTextColor', true );
		if ( 1 === (int) $global_woocommerce_events_ticket_text_color ) {

			$global_woocommerce_events_ticket_text_color = '';

		}

		$global_woocommerce_events_ticket_logo = get_option( 'globalWooCommerceEventsTicketLogo', true );

		if ( 1 === (int) $global_woocommerce_events_ticket_logo ) {

			$global_woocommerce_events_ticket_logo = '';

		}

		$global_woocommerce_events_ticket_header_image = get_option( 'globalWooCommerceEventsTicketHeaderImage', true );
		if ( 1 === (int) $global_woocommerce_events_ticket_header_image ) {

			$global_woocommerce_events_ticket_header_image = '';

		}

		$global_woocommerce_events_email_ticket_admin = get_option( 'globalWooCommerceEventsEmailTicketAdmin', true );
		$woocommerce_events_email_ticket_admin        = get_post_meta( $post->ID, 'wooCommerceEventsEmailTicketAdmin', true );

		if ( empty( $woocommerce_events_email_ticket_admin ) || 1 == $woocommerce_events_email_ticket_admin ) {

			if ( 1 == $global_woocommerce_events_email_ticket_admin ) {

				$global_woocommerce_events_email_ticket_admin = '';

			}

			$woocommerce_events_email_ticket_admin = $global_woocommerce_events_email_ticket_admin;

		}

		$end_date      = '';
		$num_days      = '';
		$multiday_type = '';
		$multiday_term = '';

		$day_term = get_post_meta( $post->ID, 'WooCommerceEventsDayOverride', true );

		if ( empty( $day_term ) ) {

			$day_term = get_option( 'WooCommerceEventsDayOverride', true );

		}

		if ( empty( $day_term ) || 1 === $day_term ) {

			$day_term = __( 'Day', 'woocommerce-events' );

		}

		$num_days_value = 1;

		$event_background_colour        = '';
		$event_text_colour              = '';
		$pdf_ticket_themes              = '';
		$pdf_ticket_options             = '';
		$bookings_term_options          = '';
		$seating_term_options           = '';
		$bookings_expiration_options    = '';
		$multiday_select_date_container = '';
		$bookings_enabled               = false;
		$multi_day_enabled              = false;
		$seating_enabled                = false;

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

			$fooevents_multiday_events      = new Fooevents_Multiday_Events();
			$end_date                       = $fooevents_multiday_events->generate_end_date_option( $post );
			$num_days                       = $fooevents_multiday_events->generate_num_days_option( $post );
			$multiday_select_date_container = $fooevents_multiday_events->generate_multiday_select_date_container( $post );
			$multiday_term                  = $fooevents_multiday_events->generate_multiday_term_option( $post );
			$num_days_value                 = (int) get_post_meta( $post->ID, 'WooCommerceEventsNumDays', true );
			$multi_day_enabled              = true;

		}

		$eventbrite_option = '';
		if ( is_plugin_active( 'fooevents-calendar/fooevents-calendar.php' ) || is_plugin_active_for_network( 'fooevents-calendar/fooevents-calendar.php' ) ) {

			$fooevents_calendar = new FooEvents_Calendar();

			$global_fooevents_eventbrite_token = get_option( 'globalFooEventsEventbriteToken' );

			if ( ! empty( $global_fooevents_eventbrite_token ) ) {

				$eventbrite_option = $fooevents_calendar->generate_eventbrite_option( $post );

			}
		}

		$events_include_custom_attendee_fields = '';
		$cf_array                              = array();

		if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

			$fooevents_custom_attendee_fields      = new Fooevents_Custom_Attendee_Fields( $post );
			$events_include_custom_attendee_fields = $fooevents_custom_attendee_fields->generate_include_custom_attendee_options( $post );

			$fooevents_custom_attendee_fields_options = $fooevents_custom_attendee_fields->display_tickets_meta_custom_options_array( $post->ID );

			if ( ! empty( $fooevents_custom_attendee_fields_options['fooevents_custom_attendee_fields_options_serialized'] ) ) {

				$custom_fields = json_decode( $fooevents_custom_attendee_fields_options['fooevents_custom_attendee_fields_options_serialized'], true );

				foreach ( $custom_fields as $key => $value ) {
					foreach ( $value as $key_cf => $value_cf ) {
						if ( strpos( $key_cf, '_label' ) !== false ) {
							$cf_array[ 'fooevents_custom_' . $key ] = $value_cf;
						}
					}
				}
			}
		}

		if ( is_plugin_active( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) || is_plugin_active_for_network( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) ) {

			$fooevents_pdf_tickets = new FooEvents_PDF_Tickets();
			$pdf_ticket_themes     = $fooevents_pdf_tickets->generate_pdf_theme_options( $post );
			$pdf_ticket_options    = $fooevents_pdf_tickets->add_product_pdf_tickets_options_tab_options( $post );

		}

		if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

			$fooevents_bookings          = new FooEvents_Bookings();
			$bookings_term_options       = $fooevents_bookings->generate_bookings_term_options( $post );
			$bookings_expiration_options = $fooevents_bookings->generate_bookings_expiration_options( $post );
			$bookings_enabled            = true;

		}

		if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

			$seating_enabled = true;

			$fooevents_seating = new FooEvents_Seating();

			if ( method_exists( $fooevents_seating, 'generate_seating_term_options' ) ) {
				$seating_term_options = $fooevents_seating->generate_seating_term_options( $post );
			}
		}

		$themes = $this->get_ticket_themes();

		require $this->config->template_path . 'product-event-settings.php';
		require $this->config->template_path . 'product-event-terminology-settings.php';

		wp_enqueue_script( 'woocommerce-events-select', $this->config->scripts_path . 'select2.min.js', array( 'jquery' ), '4.0.12', true );
		wp_enqueue_script( 'woocommerce-events-admin-select', $this->config->scripts_path . 'event-admin-select.js', array( 'jquery', 'woocommerce-events-select' ), '1.0.0', true );
		wp_enqueue_style( 'woocommerce-events-select', $this->config->styles_path . 'select2.min.css', array(), '4.0.12' );
		$woo_helper = $this;
		require $this->config->template_path . 'product-ticket-settings.php';
		require $this->config->template_path . 'product-event-export-settings.php';

		$attendee_text = get_post_meta( $post->ID, 'WooCommerceEventsAttendeeOverride', true );

		if ( '' === $attendee_text ) {
			$attendee_text = __( 'Attendee', 'woocommerce-events' );
		}

		$seat_text = get_post_meta( $post->ID, 'WooCommerceEventsSeatingSeatOverride', true );

		if ( '' === $seat_text ) {
			$seat_text = __( 'Seat', 'woocommerce-events' );
		}

		$attendee_name_text        = str_ireplace( __( 'Attendee', 'woocommerce-events' ), $attendee_text, __( 'Attendee Name', 'woocommerce-events' ) );
		$attendee_email_text       = str_ireplace( __( 'Attendee', 'woocommerce-events' ), $attendee_text, __( 'Attendee Email', 'woocommerce-events' ) );
		$attendee_phone_text       = str_ireplace( __( 'Attendee', 'woocommerce-events' ), $attendee_text, __( 'Attendee Phone', 'woocommerce-events' ) );
		$attendee_company_text     = str_ireplace( __( 'Attendee', 'woocommerce-events' ), $attendee_text, __( 'Attendee Company', 'woocommerce-events' ) );
		$attendee_designation_text = str_ireplace( __( 'Attendee', 'woocommerce-events' ), $attendee_text, __( 'Attendee Designation', 'woocommerce-events' ) );
		$attendee_seat_text        = str_ireplace( __( 'Seat', 'woocommerce-events' ), $seat_text, str_ireplace( __( 'Attendee', 'woocommerce-events' ), $attendee_text, __( 'Attendee Seat', 'woocommerce-events' ) ) );

		require $this->config->template_path . 'product-event-printing-settings.php';

		$global_woocommerce_events_zoom_account_id           = get_option( 'globalWooCommerceEventsZoomAccountID', '' );
		$global_woocommerce_events_zoom_client_id            = get_option( 'globalWooCommerceEventsZoomClientID', '' );
		$global_woocommerce_events_zoom_client_secret        = get_option( 'globalWooCommerceEventsZoomClientSecret', '' );
		$global_woocommerce_events_zoom_users                = json_decode( get_option( 'globalWooCommerceEventsZoomUsers', json_encode( array() ) ), true );
		$global_woocommerce_events_zoom_selected_user_option = get_option( 'globalWooCommerceEventsZoomSelectedUserOption' );
		$global_woocommerce_events_zoom_selected_users       = get_option( 'globalWooCommerceEventsZoomSelectedUsers' );
		$woocommerce_events_zoom_host                        = get_post_meta( $post->ID, 'WooCommerceEventsZoomHost', true );
		$woocommerce_events_zoom_type                        = get_post_meta( $post->ID, 'WooCommerceEventsZoomType', true );
		$woocommerce_events_zoom_duration_hour               = get_post_meta( $post->ID, 'WooCommerceEventsZoomDurationHour', true );
		$woocommerce_events_zoom_duration_minute             = get_post_meta( $post->ID, 'WooCommerceEventsZoomDurationMinute', true );
		$woocommerce_events_zoom_topic                       = get_the_title( $post->ID );
		$zoom_meetings                                       = $this->zoom_api_helper->fooevents_fetch_zoom_meetings();
		$zoom_webinars                                       = $this->zoom_api_helper->fooevents_fetch_zoom_webinars();
		$addtowallet_api_key                                 = get_option( 'globalWooCommerceEventsAddToWalletAPIKey', true );
		$mailchimp_api_key                                   = get_option( 'globalWooCommerceEventsMailchimpAPIKey' );
		$mailchimp_server_prefix                             = get_option( 'globalWooCommerceEventsMailchimpServer' );
		$woocommerce_events_mailchimp_list                   = get_post_meta( $post->ID, 'WooCommerceEventsMailchimpList', true );
		$woocommerce_events_mailchimp_tags                   = get_post_meta( $post->ID, 'WooCommerceEventsMailchimpTags', true );
		$mailchimp_lists                                     = array();

		if ( empty( $woocommerce_events_mailchimp_list ) ) {

			$woocommerce_events_mailchimp_list = get_option( 'globalWooCommerceEventsMailchimpList' );

		}

		if ( empty( $woocommerce_events_mailchimp_tags ) ) {

			$woocommerce_events_mailchimp_tags = get_option( 'globalWooCommerceEventsMailchimpTags' );

		}

		if ( ! empty( $mailchimp_api_key ) && ! empty( $mailchimp_server_prefix ) ) {

			$mailchimp_lists = $this->mailchimp_helper->get_lists();

		}

		require $this->config->template_path . 'product-event-integration-settings.php';

		require $this->config->template_path . 'product-event-expiration-settings.php';

		wp_nonce_field( 'fooevents_metabox_nonce', 'fooevents_metabox_nonce' );
	}

	/**
	 * Add integration tab to WooCommerce meta box
	 */
	public function add_product_integration_tab() {

		echo '<li class="custom_tab_fooevents_integration"><a href="#fooevents_integration">' . esc_attr__( 'Event Integration', 'woocommerce-events' ) . '</a></li>';
	}

	/**
	 * Add expiration tab to WooCommerce meta box
	 */
	public function add_product_expiration_tab() {

		echo '<li class="custom_tab_fooevents_expiration"><a href="#fooevents_expiration">' . esc_attr__( 'Event Expiration', 'woocommerce-events' ) . '</a></li>';
	}

	/**
	 * Add options tab to WooCommerce meta box
	 */
	public function add_product_exports_tab() {

		echo '<li class="custom_tab_fooevents_exports"><a href="#fooevents_exports">' . esc_attr__( 'Event Export', 'woocommerce-events' ) . '</a></li>';
	}

	/**
	 * Add options tab to WooCommerce meta box
	 */
	public function add_product_printing_tab() {

		echo '<li class="custom_tab_fooevents_printing"><a href="#fooevents_printing">' . esc_attr__( 'Stationery Builder', 'woocommerce-events' ) . '</a></li>';
	}

	/**
	 * Add options tab to WooCommerce meta box
	 */
	public function add_product_terminology_tab() {

		echo '<li class="custom_tab_fooevents_terminology"><a href="#fooevents_terminology">' . esc_attr__( 'Event Terminology', 'woocommerce-events' ) . '</a></li>';
	}


	/**
	 * Add options tab to WooCommerce meta box
	 */
	public function add_product_ticket_tab() {

		echo '<li class="custom_tab_fooevents_tickets"><a href="#fooevents_tickets">' . esc_attr__( 'Ticket Settings', 'woocommerce-events' ) . '</a></li>';
	}

	/**
	 * Gets a list of FooEvents Ticket themes
	 */
	public function get_ticket_themes() {

		$valid_themes = array();

		foreach ( new DirectoryIterator( $this->config->theme_packs_path ) as $file ) {

			if ( $file->isDir() && ! $file->isDot() ) {

				$theme_name = $file->getFilename();

				$theme_path = $file->getPath();
				$theme_path = $theme_path . '/' . $theme_name;

				$theme_name_pretty = str_replace( '_', ' ', $theme_name );
				$theme_name_prep   = ucwords( $theme_name_pretty );

				if ( file_exists( $theme_path . '/header.php' ) && file_exists( $theme_path . '/footer.php' ) && file_exists( $theme_path . '/ticket.php' ) ) {

					$theme_config = array();
					if ( file_exists( $theme_path . '/config.json' ) ) {

						$theme_config                             = file_get_contents( $theme_path . '/config.json' );
						$theme_config                             = json_decode( $theme_config, true );
						$valid_themes[ $theme_name_prep ]['name'] = $theme_config['name'];

					} else {

						$valid_themes[ $theme_name_prep ]['name'] = $theme_name_prep;

					}

					$valid_themes[ $theme_name_prep ]['path'] = $theme_path;
					$theme_url                                = $this->config->theme_packs_url . $theme_name;
					$valid_themes[ $theme_name_prep ]['url']  = $theme_url;

					if ( file_exists( $theme_path . '/preview.jpg' ) ) {

						$valid_themes[ $theme_name_prep ]['preview'] = $theme_url . '/preview.jpg';

					} else {

						$valid_themes[ $theme_name_prep ]['preview'] = $this->config->event_plugin_url . 'images/no-preview.jpg';

					}

					$valid_themes[ $theme_name_prep ]['file_name'] = $file->getFilename();

				}
			}
		}

		return $valid_themes;
	}

	/**
	 * Processes the meta box form once the publish / update button is clicked.
	 *
	 * @global object $woocommerce_errors
	 * @param int $post_id post ID.
	 */
	public function process_meta_box( $post_id ) {

		global $woocommerce_errors;
		global $wp_locale;

		/* If any AddToWallet fields were updated then all passes need to be updated. */
		$should_update_passes = false;

		$previous_post_meta       = get_post_meta( $post_id );
		$woocommerce_events_event = '';
		if ( isset( $_POST['WooCommerceEventsEvent'] ) && $_POST['WooCommerceEventsEvent'] == '1' ) {

			$woocommerce_events_event = 'Event';

		} elseif ( isset( $_POST['WooCommerceEventsEvent'] ) ) {

			$woocommerce_events_event = $_POST['WooCommerceEventsEvent'];

		}
		update_post_meta( $post_id, 'WooCommerceEventsEvent', $woocommerce_events_event );

		$format = get_option( 'date_format' );

		$min    = 60 * get_option( 'gmt_offset' );
		$sign   = $min < 0 ? '-' : '+';
		$absmin = abs( $min );

		try {

			$tz = new DateTimeZone( sprintf( '%s%02d%02d', $sign, $absmin / 60, $absmin % 60 ) );

		} catch ( Exception $e ) {

			$server_timezone = date_default_timezone_get();
			$tz              = new DateTimeZone( $server_timezone );

		}

		if ( isset( $_POST['WooCommerceEventsDate'] ) ) {

			$event_date = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDate'] ) );

		}

		if ( isset( $_POST['WooCommerceEventsSelectDate'][0] ) && isset( $_POST['WooCommerceEventsType'] ) && 'select' === $_POST['WooCommerceEventsType'] ) {

			$event_date = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSelectDate'][0] ) );

		}

		$wp_date_format = str_replace( ' \d\e ', ' ', get_option( 'date_format' ) );

		$date_format = $wp_date_format . ' H:i';
		$mysql_date  = '';

		if ( isset( $event_date ) ) {

			$event_date_period_format = ! empty( $_POST['WooCommerceEventsPeriod'] ) ? ' A' : '';
			$event_date_period        = ! empty( $_POST['WooCommerceEventsPeriod'] ) ? ' ' . sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPeriod'] ) ) : '';
			$event_date               = $this->convert_month_to_english( $event_date );
			$event_date_time          = $event_date . ' ' . sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsHour'] ) ) . ':' . sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsMinutes'] ) ) . $event_date_period;

			$timezone = null;

			if ( isset( $_POST['WooCommerceEventsTimeZone'] ) && '' !== $_POST['WooCommerceEventsTimeZone'] ) {

				$timezone = new DateTimeZone( $_POST['WooCommerceEventsTimeZone'] );

			}

			$timestamp_date_time = 0;
			$timestamp           = $timestamp_date_time;

			$event_date_object = DateTime::createFromFormat( $date_format . $event_date_period_format, $event_date_time, $timezone );

			if ( false === $event_date_object ) {

				if ( 'd/m/Y' === $wp_date_format ) {

					$event_date = str_replace( '/', '-', $event_date );

				}
				$event_date = str_replace( ',', '', $event_date );

				$event_date_time = str_replace( '/', '-', $event_date_time );
				$event_date_time = str_replace( ',', '', $event_date_time );

				$timestamp           = strtotime( $event_date );
				$timestamp_date_time = strtotime( $event_date_time );

				if ( false === $timestamp ) {

					$timestamp = 0;

				}

				if ( false === $timestamp_date_time ) {

					$timestamp_date_time = 0;

				}

				try {
					$event_date_object   = new DateTime( '@' . $timestamp, $timezone );
					$timestamp_date_time = $event_date_object->getTimestamp();
					$timestamp           = $timestamp_date_time;
					$mysql_date          = $event_date_object->format( 'Y-m-d H:i:s' );

				} catch ( Exception $e ) {

					$timestamp_date_time = 0;
					$timestamp           = $timestamp_date_time;
					$mysql_date          = 0;

				}
			} else {

				$timestamp_date_time = $event_date_object->getTimestamp();
				$timestamp           = $timestamp_date_time;
				$mysql_date          = $event_date_object->format( 'Y-m-d H:i:s' );

			}

			$woocommerce_events_date = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDate'] ) );

			if ( isset( $_POST['WooCommerceEventsSelectDate'][0] ) && isset( $_POST['WooCommerceEventsType'] ) && 'select' === $_POST['WooCommerceEventsType'] ) {

				$woocommerce_events_date = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSelectDate'][0] ) );

			}

			update_post_meta( $post_id, 'WooCommerceEventsDate', $woocommerce_events_date );
			update_post_meta( $post_id, 'WooCommerceEventsDateMySQLFormat', $mysql_date );
			update_post_meta( $post_id, 'WooCommerceEventsDateTimestamp', $timestamp );
			update_post_meta( $post_id, 'WooCommerceEventsDateTimeTimestamp', $timestamp_date_time );

		}

		$event_end_date = '';

		if ( isset( $_POST['WooCommerceEventsEndDate'] ) && ! empty( $_POST['WooCommerceEventsEndDate'] ) ) {

			$event_end_date = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEndDate'] ) );

		}

		if ( isset( $_POST['WooCommerceEventsSelectDate'] ) && isset( $_POST['WooCommerceEventsType'] ) && 'select' === $_POST['WooCommerceEventsType'] ) {

			$event_end_date = sanitize_text_field( wp_unslash( end( $_POST['WooCommerceEventsSelectDate'] ) ) );

		}

		if ( isset( $event_end_date ) ) {
			$event_end_date_period_format = ! empty( $_POST['WooCommerceEventsEndPeriod'] ) ? ' A' : '';
			$event_end_date_period        = ! empty( $_POST['WooCommerceEventsEndPeriod'] ) ? ' ' . sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEndPeriod'] ) ) : '';
			$event_end_date               = $this->convert_month_to_english( $event_end_date );
			$event_end_date_time          = $event_end_date . ' ' . sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsHourEnd'] ) ) . ':' . sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsMinutesEnd'] ) ) . $event_end_date_period;

			$timezone = null;

			if ( isset( $_POST['WooCommerceEventsTimeZone'] ) && '' !== $_POST['WooCommerceEventsTimeZone'] ) {

				$timezone = new DateTimeZone( sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTimeZone'] ) ) );

			}

			$end_timestamp_date_time = 0;
			$end_timestamp           = $end_timestamp_date_time;

			$event_end_date_object = DateTime::createFromFormat( $date_format . $event_end_date_period_format, $event_end_date_time, $timezone );

			if ( false === $event_end_date_object ) {

				$event_end_date = str_replace( '/', '-', $event_end_date );
				$event_end_date = str_replace( ',', '', $event_end_date );

				$event_end_date_time = str_replace( '/', '-', $event_end_date_time );
				$event_end_date_time = str_replace( ',', '', $event_end_date_time );

				$end_timestamp           = strtotime( $event_end_date );
				$end_timestamp_date_time = strtotime( $event_end_date_time );

				if ( false === $end_timestamp ) {

					$end_timestamp = 0;

				}

				if ( false === $end_timestamp_date_time ) {

					$end_timestamp_date_time = 0;

				}

				try {

					$event_end_date_object   = new DateTime( '@' . $end_timestamp, $timezone );
					$end_timestamp_date_time = $event_end_date_object->getTimestamp();
					$end_timestamp           = $end_timestamp_date_time;

				} catch ( Exception $e ) {

					$end_timestamp_date_time = 0;
					$end_timestamp           = $end_timestamp_date_time;

				}
			} else {

				$end_timestamp_date_time = $event_end_date_object->getTimestamp();
				$end_timestamp           = $end_timestamp_date_time;

			}

			$woocommerce_events_end_date = '';
			if ( isset( $_POST['WooCommerceEventsEndDate'] ) ) {

				$woocommerce_events_end_date = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEndDate'] ) );

			}

			if ( isset( $_POST['WooCommerceEventsSelectDate'] ) && isset( $_POST['WooCommerceEventsType'] ) && 'select' === $_POST['WooCommerceEventsType'] ) {

				$woocommerce_events_end_date = sanitize_text_field( wp_unslash( end( $_POST['WooCommerceEventsSelectDate'] ) ) );

			}

			$mysql_end_date = date( 'Y-m-d H:i:s', $end_timestamp_date_time );

			update_post_meta( $post_id, 'WooCommerceEventsEndDate', $woocommerce_events_end_date );
			update_post_meta( $post_id, 'WooCommerceEventsEndDateMySQLFormat', $mysql_end_date );
			update_post_meta( $post_id, 'WooCommerceEventsEndDateTimestamp', $end_timestamp );
			update_post_meta( $post_id, 'WooCommerceEventsEndDateTimeTimestamp', $end_timestamp_date_time );

		}

		if ( isset( $_POST['WooCommerceEventsType'] ) ) {

			$woocommerce_events_type = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsType'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsType', $woocommerce_events_type );

		}

		if ( isset( $_POST['WooCommerceEventsExpire'] ) ) {

			$expire_date = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsExpire'] ) );

			if ( in_array( $wp_date_format, array( 'd/m/Y', 'd.m.Y' ) ) ) {

				$expire_date = str_replace( '/', '-', $expire_date );

			}

			$expire_date = str_replace( ',', '', $expire_date );
			$expire_date = $this->convert_month_to_english( $expire_date );

			$timestamp = strtotime( $expire_date );

			update_post_meta( $post_id, 'WooCommerceEventsExpire', sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsExpire'] ) ) );
			update_post_meta( $post_id, 'WooCommerceEventsExpireTimestamp', $timestamp );

		}

		if ( isset( $_POST['WooCommerceEventsExpireMessage'] ) ) {

			$woocommerce_events_expire_message = wp_kses_post( wp_unslash( $_POST['WooCommerceEventsExpireMessage'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsExpireMessage', $woocommerce_events_expire_message );

		}

		if ( isset( $_POST['WooCommerceEventsBookingsExpirePassedDate'] ) ) {

			$woocommerce_events_bookings_expire_passed_date = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBookingsExpirePassedDate'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsBookingsExpirePassedDate', $woocommerce_events_bookings_expire_passed_date );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsBookingsExpirePassedDate', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsBookingsExpireValue'] ) ) {

			$woocommerce_events_bookings_expire_value = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBookingsExpireValue'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsBookingsExpireValue', $woocommerce_events_bookings_expire_value );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsBookingsExpireValue', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsBookingsExpireUnit'] ) ) {

			$woocommerce_events_bookings_expire_unit = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBookingsExpireUnit'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsBookingsExpireUnit', $woocommerce_events_bookings_expire_unit );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsBookingsExpireUnit', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsTicketExpirationType'] ) ) {

			$woocommerce_events_ticket_expiration_type = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketExpirationType'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketExpirationType', $woocommerce_events_ticket_expiration_type );

		}

		if ( isset( $_POST['WooCommerceEventsTicketsExpireSelect'] ) ) {

			$expire_date = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketsExpireSelect'] ) );
			$expire_date = str_replace( '/', '-', $expire_date );
			$expire_date = str_replace( ',', '', $expire_date );
			$expire_date = $this->convert_month_to_english( $expire_date );

			$timestamp = strtotime( $expire_date );

			$woocommerce_events_tickets_expire_select = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketsExpireSelect'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketsExpireSelect', $woocommerce_events_tickets_expire_select );
			update_post_meta( $post_id, 'WooCommerceEventsTicketsExpireSelectTimestamp', $timestamp );

		}

		if ( isset( $_POST['WooCommerceEventsTicketsExpireSelect'] ) ) {

			$woocommerce_events_tickets_expire_select = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketsExpireSelect'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketsExpireSelect', $woocommerce_events_tickets_expire_select );

		}

		if ( isset( $_POST['WooCommerceEventsTicketsExpireUnit'] ) ) {

			$woocommerce_events_tickets_expire_unit = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketsExpireUnit'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketsExpireUnit', $woocommerce_events_tickets_expire_unit );

		}

		if ( isset( $_POST['WooCommerceEventsTicketsExpireValue'] ) ) {

			$woocommerce_events_tickets_expire_value = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketsExpireValue'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketsExpireValue', $woocommerce_events_tickets_expire_value );

		}

		if ( isset( $_POST['WooCommerceEventsMultiDayType'] ) ) {

			$woocommerce_events_multi_day_type = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsMultiDayType'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsMultiDayType', $woocommerce_events_multi_day_type );

		}

		// Muti-day select day
		if ( isset( $_POST['WooCommerceEventsSelectDate'] ) ) {

			$woocommerce_events_select_date = $_POST['WooCommerceEventsSelectDate'];
			update_post_meta( $post_id, 'WooCommerceEventsSelectDate', $woocommerce_events_select_date );

		}

		if ( isset( $_POST['WooCommerceEventsSelectDateHour'] ) ) {

			$woocommerce_events_select_date_hour = $_POST['WooCommerceEventsSelectDateHour'];
			update_post_meta( $post_id, 'WooCommerceEventsSelectDateHour', $woocommerce_events_select_date_hour );

		}

		if ( isset( $_POST['WooCommerceEventsSelectDateMinutes'] ) ) {

			$woocommerce_events_select_date_minutes = $_POST['WooCommerceEventsSelectDateMinutes'];
			update_post_meta( $post_id, 'WooCommerceEventsSelectDateMinutes', $woocommerce_events_select_date_minutes );

		}

		if ( isset( $_POST['WooCommerceEventsSelectDatePeriod'] ) ) {

			$woocommerce_events_select_date_period = $_POST['WooCommerceEventsSelectDatePeriod'];
			update_post_meta( $post_id, 'WooCommerceEventsSelectDatePeriod', $woocommerce_events_select_date_period );

		}

		if ( isset( $_POST['WooCommerceEventsSelectDateHourEnd'] ) ) {

			$woocommerce_events_select_date_hour_end = $_POST['WooCommerceEventsSelectDateHourEnd'];
			update_post_meta( $post_id, 'WooCommerceEventsSelectDateHourEnd', $woocommerce_events_select_date_hour_end );

		}

		if ( isset( $_POST['WooCommerceEventsSelectDateMinutesEnd'] ) ) {

			$woocommerce_events_select_date_minutes_end = $_POST['WooCommerceEventsSelectDateMinutesEnd'];
			update_post_meta( $post_id, 'WooCommerceEventsSelectDateMinutesEnd', $woocommerce_events_select_date_minutes_end );

		}

		if ( isset( $_POST['WooCommerceEventsSelectDatePeriodEnd'] ) ) {

			$woocommerce_events_select_date_period_end = $_POST['WooCommerceEventsSelectDatePeriodEnd'];
			update_post_meta( $post_id, 'WooCommerceEventsSelectDatePeriodEnd', $woocommerce_events_select_date_period_end );

		}

		if ( isset( $_POST['WooCommerceEventsSelectDateHourEnd'] ) ) {

			$woocommerce_events_select_date_hour_end = $_POST['WooCommerceEventsSelectDateHourEnd'];
			update_post_meta( $post_id, 'WooCommerceEventsSelectDateHourEnd', $woocommerce_events_select_date_hour_end );

		}

		if ( isset( $_POST['WooCommerceEventsSelectDateMinutesEnd'] ) ) {

			$woocommerce_events_select_date_minutes_end = $_POST['WooCommerceEventsSelectDateMinutesEnd'];
			update_post_meta( $post_id, 'WooCommerceEventsSelectDateMinutesEnd', $woocommerce_events_select_date_minutes_end );

		}

		if ( isset( $_POST['WooCommerceEventsSelectDatePeriodEnd'] ) ) {

			$woocommerce_events_select_date_period_end = $_POST['WooCommerceEventsSelectDatePeriodEnd'];
			update_post_meta( $post_id, 'WooCommerceEventsSelectDatePeriodEnd', $woocommerce_events_select_date_period_end );

		}

		// END Muti-day select day

		if ( isset( $_POST['WooCommerceEventsNumDays'] ) ) {

			$woocommerce_events_num_days = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsNumDays'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsNumDays', $woocommerce_events_num_days );

		}

		if ( isset( $_POST['WooCommerceEventsHour'] ) ) {

			$wooommerce_events_hour = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsHour'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsHour', $wooommerce_events_hour );

		}

		if ( isset( $_POST['WooCommerceEventsMinutes'] ) ) {

			$woocommerce_events_minutes = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsMinutes'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsMinutes', $woocommerce_events_minutes );

		}

		if ( isset( $_POST['WooCommerceEventsPeriod'] ) ) {

			$woocommerce_events_period = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPeriod'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsPeriod', $woocommerce_events_period );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsPeriod', '' );

		}

		if ( isset( $_POST['WooCommerceEventsTimeZone'] ) ) {

			$woocommerce_events_timezone = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTimeZone'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTimeZone', $woocommerce_events_timezone );

		}

		if ( isset( $_POST['WooCommerceEventsLocation'] ) ) {

			$woocommerce_events_location = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsLocation'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsLocation', $woocommerce_events_location );

		}

		if ( isset( $_POST['WooCommerceEventsTicketLogo'] ) ) {

			$woocommerce_events_ticket_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketLogo'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketLogo', $woocommerce_events_ticket_logo );

		}

		if ( isset( $_POST['WooCommerceEventsPrintTicketLogo'] ) ) {

			$woocommerce_events_print_ticket_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPrintTicketLogo'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsPrintTicketLogo', $woocommerce_events_print_ticket_logo );

		}

		if ( isset( $_POST['WooCommerceEventsPrintCustomText'] ) ) {

			$woocommerce_events_print_custom_text = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPrintCustomText'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsPrintCustomText', $woocommerce_events_print_custom_text );

		}

		if ( isset( $_POST['WooCommerceEventsTicketHeaderImage'] ) ) {

			$woocommerce_events_ticket_header_image = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketHeaderImage'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketHeaderImage', $woocommerce_events_ticket_header_image );

		}

		if ( isset( $_POST['WooCommerceEventsAddToWalletLogo'] ) ) {

			$woocommerce_events_addtowallet_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAddToWalletLogo'] ) );
			$result                              = update_post_meta( $post_id, 'WooCommerceEventsAddToWalletLogo', $woocommerce_events_addtowallet_logo );
			if ( true === $result ) {
				$should_update_passes = true;
			}
		}

		if ( isset( $_POST['WooCommerceEventsAddToWalletImage'] ) ) {

			$woocommerce_events_addtowallet_image = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAddToWalletImage'] ) );
			$result                               = update_post_meta( $post_id, 'WooCommerceEventsAddToWalletImage', $woocommerce_events_addtowallet_image );
			if ( true === $result ) {
				$should_update_passes = true;
			}
		}

		if ( isset( $_POST['WooCommerceEventsAddToWalletDateLabel'] ) ) {

			$woocommerce_events_addtowallet_date_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAddToWalletDateLabel'] ) );
			$result                                    = update_post_meta( $post_id, 'WooCommerceEventsAddToWalletDateLabel', $woocommerce_events_addtowallet_date_label );
			if ( true === $result ) {
				$should_update_passes = true;
			}
		}

		if ( isset( $_POST['WooCommerceEventsAddToWalletDateValue'] ) ) {

			$woocommerce_events_addtowallet_date_value = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAddToWalletDateValue'] ) );
			$result                                    = update_post_meta( $post_id, 'WooCommerceEventsAddToWalletDateValue', $woocommerce_events_addtowallet_date_value );
			if ( true === $result ) {
				$should_update_passes = true;
			}
		}

		if ( isset( $_POST['WooCommerceEventsAddToWalletAttendeeLabel'] ) ) {

			$woocommerce_events_addtowallet_attendee_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAddToWalletAttendeeLabel'] ) );
			$result                                        = update_post_meta( $post_id, 'WooCommerceEventsAddToWalletAttendeeLabel', $woocommerce_events_addtowallet_attendee_label );
			if ( true === $result ) {
				$should_update_passes = true;
			}
		}

		if ( isset( $_POST['WooCommerceEventsAddToWalletLocationLabel'] ) ) {

			$woocommerce_events_addtowallet_location_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAddToWalletLocationLabel'] ) );
			$result                                        = update_post_meta( $post_id, 'WooCommerceEventsAddToWalletLocationLabel', $woocommerce_events_addtowallet_location_label );
			if ( true === $result ) {
				$should_update_passes = true;
			}
		}

		if ( isset( $_POST['WooCommerceEventsAddToWalletCardTitle'] ) ) {

			$woocommerce_events_addtowallet_card_title = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAddToWalletCardTitle'] ) );
			$result                                    = update_post_meta( $post_id, 'WooCommerceEventsAddToWalletCardTitle', $woocommerce_events_addtowallet_card_title );
			if ( true === $result ) {
				$should_update_passes = true;
			}
		}

		if ( isset( $_POST['WooCommerceEventsAddToWalletCardHeader'] ) ) {

			$woocommerce_events_addtowallet_card_header = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAddToWalletCardHeader'] ) );
			$result                                     = update_post_meta( $post_id, 'WooCommerceEventsAddToWalletCardHeader', $woocommerce_events_addtowallet_card_header );
			if ( true === $result ) {
				$should_update_passes = true;
			}
		}

		if ( isset( $_POST['WooCommerceEventsTicketText'] ) ) {

			$woocommerce_events_ticket_text = wp_kses_post( wp_unslash( $_POST['WooCommerceEventsTicketText'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketText', $woocommerce_events_ticket_text );

		}

		if ( isset( $_POST['WooCommerceEventsThankYouText'] ) ) {

			$woocommerce_events_thank_you_text = wp_kses_post( wp_unslash( $_POST['WooCommerceEventsThankYouText'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsThankYouText', $woocommerce_events_thank_you_text );

		}

		if ( isset( $_POST['WooCommerceEventsEventDetailsText'] ) ) {

			$woocommerce_events_event_details_text = wp_kses_post( wp_unslash( $_POST['WooCommerceEventsEventDetailsText'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsEventDetailsText', $woocommerce_events_event_details_text );

		}

		if ( isset( $_POST['WooCommerceEventsSupportContact'] ) ) {

			$woocommerce_events_support_contact = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSupportContact'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsSupportContact', $woocommerce_events_support_contact );

		}

		if ( isset( $_POST['WooCommerceEventsHourEnd'] ) ) {

			$woocommerce_events_hour_end = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsHourEnd'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsHourEnd', $woocommerce_events_hour_end );

		}

		if ( isset( $_POST['WooCommerceEventsMinutesEnd'] ) ) {

			$woocommerce_events_minutes_end = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsMinutesEnd'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsMinutesEnd', $woocommerce_events_minutes_end );

		}

		if ( isset( $_POST['WooCommerceEventsEndPeriod'] ) ) {

			$woocommerce_events_end_period = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEndPeriod'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsEndPeriod', $woocommerce_events_end_period );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsEndPeriod', '' );

		}

		if ( isset( $_POST['WooCommerceEventsAddEventbrite'] ) ) {

			if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

				require_once ABSPATH . '/wp-admin/includes/plugin.php';

			}

			if ( is_plugin_active( 'fooevents-calendar/fooevents-calendar.php' ) || is_plugin_active_for_network( 'fooevents-calendar/fooevents-calendar.php' ) ) {

				$fooevents_calendar = new FooEvents_Calendar();
				$fooevents_calendar->process_eventbrite( $post_id );

			}

			$woocommerce_events_add_eventbrite = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAddEventbrite'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsAddEventbrite', $woocommerce_events_add_eventbrite );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsAddEventbrite', '' );

		}

		if ( isset( $_POST['WooCommerceEventsGPS'] ) ) {

			$woocommerce_events_gps = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsGPS'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsGPS', $woocommerce_events_gps );

		}

		if ( isset( $_POST['WooCommerceEventsDirections'] ) ) {

			$woocommerce_events_directions = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDirections'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsDirections', $woocommerce_events_directions );

		}

		if ( isset( $_POST['WooCommerceEventsEmail'] ) ) {

			$woocommerce_events_email = wp_kses_post( wp_unslash( ( $_POST['WooCommerceEventsEmail'] ) ) );
			update_post_meta( $post_id, 'WooCommerceEventsEmail', $woocommerce_events_email );

		}

		if ( isset( $_POST['WooCommerceEventsTicketBackgroundColor'] ) ) {

			$woocommerce_events_ticket_background_color = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketBackgroundColor'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketBackgroundColor', $woocommerce_events_ticket_background_color );

		}

		if ( isset( $_POST['WooCommerceEventsTicketButtonColor'] ) ) {

			$woocommerce_events_ticket_button_color = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketButtonColor'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketButtonColor', $woocommerce_events_ticket_button_color );

		}

		if ( isset( $_POST['WooCommerceEventsTicketTextColor'] ) ) {

			$woocommerce_events_ticket_text_color = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketTextColor'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketTextColor', $woocommerce_events_ticket_text_color );

		}

		if ( isset( $_POST['WooCommerceEventsBackgroundColor'] ) ) {

			$woocommerce_events_background_color = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBackgroundColor'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsBackgroundColor', $woocommerce_events_background_color );

		}

		if ( isset( $_POST['WooCommerceEventsTextColor'] ) ) {

			$woocommerce_events_text_color = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTextColor'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTextColor', $woocommerce_events_text_color );

		}

		if ( isset( $_POST['WooCommerceEventsGoogleMaps'] ) ) {

			$woocommerce_events_google_maps = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsGoogleMaps'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsGoogleMaps', $woocommerce_events_google_maps );

		}

		if ( isset( $_POST['WooCommerceEventsTicketPurchaserDetails'] ) ) {

			$woocommerce_events_ticket_purchaser_details = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketPurchaserDetails'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketPurchaserDetails', $woocommerce_events_ticket_purchaser_details );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsTicketPurchaserDetails', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsTicketAddCalendar'] ) ) {

			$woocommerce_events_ticket_add_calendar = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketAddCalendar'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketAddCalendar', $woocommerce_events_ticket_add_calendar );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsTicketAddCalendar', 'off' );

		}

		$woocommerce_events_ticket_add_calendar_reminders = array();

		if ( isset( $_POST['WooCommerceEventsTicketAddCalendarReminderAmounts'] ) && isset( $_POST['WooCommerceEventsTicketAddCalendarReminderUnits'] ) ) {

			$woocommerce_events_ticket_add_calendar_reminder_amounts = array_map( 'sanitize_text_field', $_POST['WooCommerceEventsTicketAddCalendarReminderAmounts'] );
			$woocommerce_events_ticket_add_calendar_reminder_units   = array_map( 'sanitize_text_field', $_POST['WooCommerceEventsTicketAddCalendarReminderUnits'] );

			for ( $i = 0; $i < count( $woocommerce_events_ticket_add_calendar_reminder_amounts ); $i++ ) {

				$woocommerce_events_ticket_add_calendar_reminders[] = array(
					'amount' => $woocommerce_events_ticket_add_calendar_reminder_amounts[ $i ],
					'unit'   => $woocommerce_events_ticket_add_calendar_reminder_units[ $i ],
				);

			}
		}

		update_post_meta( $post_id, 'WooCommerceEventsTicketAddCalendarReminders', $woocommerce_events_ticket_add_calendar_reminders );

		if ( isset( $_POST['WooCommerceEventsTicketAttachICS'] ) ) {

			$woocommerce_events_ticket_attach_ics = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketAttachICS'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketAttachICS', $woocommerce_events_ticket_attach_ics );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsTicketAttachICS', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsSelectGlobalTime'] ) ) {

			$woocommerce_events_select_global_time = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSelectGlobalTime'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsSelectGlobalTime', $woocommerce_events_select_global_time );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsSelectGlobalTime', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsTicketDisplayDateTime'] ) ) {

			$woocommerce_events_ticket_display_date_time = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketDisplayDateTime'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketDisplayDateTime', $woocommerce_events_ticket_display_date_time );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsTicketDisplayDateTime', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsTicketDisplayBarcode'] ) ) {

			$woocommerce_events_ticket_display_barcode = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketDisplayBarcode'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketDisplayBarcode', $woocommerce_events_ticket_display_barcode );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsTicketDisplayBarcode', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsTicketDisplayPrice'] ) ) {

			$woocommerce_events_ticket_display_price = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketDisplayPrice'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketDisplayPrice', $woocommerce_events_ticket_display_price );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsTicketDisplayPrice', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsTicketDisplayZoom'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$woocommerce_events_ticket_display_zoom = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketDisplayZoom'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_post_meta( $post_id, 'WooCommerceEventsTicketDisplayZoom', $woocommerce_events_ticket_display_zoom );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsTicketDisplayZoom', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsTicketDisplayAddToWallet'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$woocommerce_events_display_addtowallet = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketDisplayAddToWallet'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$result                                 = update_post_meta( $post_id, 'WooCommerceEventsTicketDisplayAddToWallet', $woocommerce_events_display_addtowallet );
			if ( true === $result ) {
				$should_update_passes = true;
			}
		} else {

			update_post_meta( $post_id, 'WooCommerceEventsTicketDisplayAddToWallet', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsDisplayAddToWalletDate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$woocommerce_events_display_addtowallet_date = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDisplayAddToWalletDate'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$result                                      = update_post_meta( $post_id, 'WooCommerceEventsDisplayAddToWalletDate', $woocommerce_events_display_addtowallet_date );
			if ( true === $result ) {
				$should_update_passes = true;
			}
		} else {

			update_post_meta( $post_id, 'WooCommerceEventsDisplayAddToWalletDate', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsDisplayAddToWalletAttendee'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$woocommerce_events_display_addtowallet_attendee = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDisplayAddToWalletAttendee'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$result = update_post_meta( $post_id, 'WooCommerceEventsDisplayAddToWalletAttendee', $woocommerce_events_display_addtowallet_attendee );
			if ( true === $result ) {
				$should_update_passes = true;
			}
		} else {

			update_post_meta( $post_id, 'WooCommerceEventsDisplayAddToWalletAttendee', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsDisplayAddToWalletLocation'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$woocommerce_events_display_addtowallet_location = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDisplayAddToWalletLocation'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$result = update_post_meta( $post_id, 'WooCommerceEventsDisplayAddToWalletLocation', $woocommerce_events_display_addtowallet_location );
			if ( true === $result ) {
				$should_update_passes = true;
			}
		} else {

			update_post_meta( $post_id, 'WooCommerceEventsDisplayAddToWalletLocation', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsAddToWalletBackgroundColor'] ) ) {

			$woocommerce_events_addtowallet_background_color = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAddToWalletBackgroundColor'] ) );
			$result = update_post_meta( $post_id, 'WooCommerceEventsAddToWalletBackgroundColor', $woocommerce_events_addtowallet_background_color );
			if ( true === $result ) {
				$should_update_passes = true;
			}
		}

		if ( isset( $_POST['WooCommerceEventsTicketDisplayBookings'] ) ) {

			$woocommerce_events_ticket_display_bookings = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketDisplayBookings'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketDisplayBookings', $woocommerce_events_ticket_display_bookings );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsTicketDisplayBookings', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsTicketDisplayMultiDay'] ) ) {

			$woocommerce_events_ticket_display_multi_day = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketDisplayMultiDay'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketDisplayMultiDay', $woocommerce_events_ticket_display_multi_day );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsTicketDisplayMultiDay', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsIncludeCustomAttendeeDetails'] ) ) {

			$woocommerce_events_include_custom_attendee_details = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsIncludeCustomAttendeeDetails'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsIncludeCustomAttendeeDetails', $woocommerce_events_include_custom_attendee_details );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsIncludeCustomAttendeeDetails', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsCaptureAttendeeDetails'] ) ) {

			$woocommerce_events_capture_attende_details = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsCaptureAttendeeDetails'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsCaptureAttendeeDetails', $woocommerce_events_capture_attende_details );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsCaptureAttendeeDetails', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsCaptureAttendeeEmail'] ) ) {

			$woocommerce_events_capture_attendee_email = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsCaptureAttendeeEmail'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsCaptureAttendeeEmail', $woocommerce_events_capture_attendee_email );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsCaptureAttendeeEmail', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsEmailAttendee'] ) ) {

			$woocommerce_events_email_attendee = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEmailAttendee'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsEmailAttendee', $woocommerce_events_email_attendee );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsEmailAttendee', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsCaptureAttendeeTelephone'] ) ) {

			$wooommerce_events_capture_attendee_telephone = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsCaptureAttendeeTelephone'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsCaptureAttendeeTelephone', $wooommerce_events_capture_attendee_telephone );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsCaptureAttendeeTelephone', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsCaptureAttendeeCompany'] ) ) {

			$woocommerce_events_capture_attendee_company = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsCaptureAttendeeCompany'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsCaptureAttendeeCompany', $woocommerce_events_capture_attendee_company );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsCaptureAttendeeCompany', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsCaptureAttendeeDesignation'] ) ) {

			$woocommerce_events_capture_attendee_designation = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsCaptureAttendeeDesignation'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsCaptureAttendeeDesignation', $woocommerce_events_capture_attendee_designation );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsCaptureAttendeeDesignation', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsUniqueEmail'] ) ) {

			$woocommerce_events_unique_email = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsUniqueEmail'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsUniqueEmail', $woocommerce_events_unique_email );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsUniqueEmail', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsSendEmailTickets'] ) ) {

			$woocommerce_events_send_email_tickets = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSendEmailTickets'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsSendEmailTickets', $woocommerce_events_send_email_tickets );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsSendEmailTickets', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsTicketIdentifierOutput'] ) ) {

			$woocommerce_events_ticket_identifier_output = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketIdentifierOutput'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketIdentifierOutput', $woocommerce_events_ticket_identifier_output );

		}

		if ( isset( $_POST['WooCommerceEventsTicketNumberOutput'] ) ) {

			$woocommerce_events_ticket_number_output = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketNumberOutput'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketNumberOutput', $woocommerce_events_ticket_number_output );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsTicketNumberOutput', '1' );

		}

		if ( isset( $_POST['WooCommerceEventsTicketNumberPrefix'] ) ) {

			$woocommerce_events_ticket_number_prefix = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketNumberPrefix'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketNumberPrefix', $woocommerce_events_ticket_number_prefix );

		}

		if ( isset( $_POST['WooCommerceEventsEnableTicketIdentifier'] ) ) {

			$woocommerce_events_enable_ticket_identifier = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEnableTicketIdentifier'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsEnableTicketIdentifier', $woocommerce_events_enable_ticket_identifier );

		}

		if ( isset( $_POST['WooCommerceEventsTicketNumberSuffix'] ) ) {

			$woocommerce_events_ticket_number_suffix = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketNumberSuffix'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketNumberSuffix', $woocommerce_events_ticket_number_suffix );

		}

		if ( isset( $_POST['WooCommerceEventsEmailSubjectSingle'] ) ) {

			$woocommerce_events_email_subject_single = htmlentities( sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEmailSubjectSingle'] ) ) );
			update_post_meta( $post_id, 'WooCommerceEventsEmailSubjectSingle', $woocommerce_events_email_subject_single );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsEmailSubjectSingle', '{OrderNumber} Ticket' );

		}

		if ( isset( $_POST['wooCommerceEventsEmailTicketAdmin'] ) ) {

			$woocommerce_events_email_ticket_admin = sanitize_text_field( wp_unslash( $_POST['wooCommerceEventsEmailTicketAdmin'] ) );
			update_post_meta( $post_id, 'wooCommerceEventsEmailTicketAdmin', $woocommerce_events_email_ticket_admin );

		}

		if ( isset( $_POST['WooCommerceEventsExportUnpaidTickets'] ) ) {

			$woocommerce_events_export_unpaid_tickets = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsExportUnpaidTickets'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsExportUnpaidTickets', $woocommerce_events_export_unpaid_tickets );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsExportUnpaidTickets', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsExportBillingDetails'] ) ) {

			$woocommerce_events_export_billing_details = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsExportBillingDetails'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsExportBillingDetails', $woocommerce_events_export_billing_details );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsExportBillingDetails', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsTicketTheme'] ) ) {

			$woocommerce_events_ticket_theme = sanitize_text_field( $_POST['WooCommerceEventsTicketTheme'] );
			update_post_meta( $post_id, 'WooCommerceEventsTicketTheme', $woocommerce_events_ticket_theme );

		}

		if ( isset( $_POST['WooCommerceEventsPDFTicketTheme'] ) ) {

			update_post_meta( $post_id, 'WooCommerceEventsPDFTicketTheme', sanitize_text_field( $_POST['WooCommerceEventsPDFTicketTheme'] ) );

		}

		if ( isset( $_POST['WooCommerceEventsAttendeeOverride'] ) ) {

			$woocommerce_events_attendee_override = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeOverride'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsAttendeeOverride', $woocommerce_events_attendee_override );

		}

		if ( isset( $_POST['WooCommerceEventsAttendeeOverridePlural'] ) ) {

			$woocommerce_events_attendee_override_plural = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsAttendeeOverridePlural'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsAttendeeOverridePlural', $woocommerce_events_attendee_override_plural );

		}

		if ( isset( $_POST['WooCommerceEventsEventDetailsOverride'] ) ) {

			$woocommerce_events_event_details_override = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEventDetailsOverride'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsEventDetailsOverride', $woocommerce_events_event_details_override );

		}

		if ( isset( $_POST['WooCommerceEventsEventDetailsOverride'] ) ) {

			$woocommerce_events_event_details_override = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEventDetailsOverride'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsEventDetailsOverride', $woocommerce_events_event_details_override );

		}

		if ( isset( $_POST['WooCommerceEventsEventDetailsOverridePlural'] ) ) {

			$woocommerce_events_event_details_override_plural = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEventDetailsOverridePlural'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsEventDetailsOverridePlural', $woocommerce_events_event_details_override_plural );

		}

		if ( isset( $_POST['WooCommerceEventsTicketOverride'] ) ) {

			$woocommerce_events_ticket_override = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketOverride'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketOverride', $woocommerce_events_ticket_override );

		}

		if ( isset( $_POST['WooCommerceEventsTicketOverridePlural'] ) ) {

			$woocommerce_events_ticket_override_plural = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketOverridePlural'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsTicketOverridePlural', $woocommerce_events_ticket_override_plural );

		}

		if ( isset( $_POST['WooCommerceEventsDayOverride'] ) ) {

			$woocommerce_events_day_override = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDayOverride'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsDayOverride', $woocommerce_events_day_override );

		}

		if ( isset( $_POST['WooCommerceEventsBookingsDateOverride'] ) ) {

			$woocommerce_events_bookings_date_override = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBookingsDateOverride'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsBookingsDateOverride', $woocommerce_events_bookings_date_override );

		}

		if ( isset( $_POST['WooCommerceEventsBookingsDateOverridePlural'] ) ) {

			$woocommerce_events_bookings_date_override_plural = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBookingsDateOverridePlural'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsBookingsDateOverridePlural', $woocommerce_events_bookings_date_override_plural );

		}

		if ( isset( $_POST['WooCommerceEventsBookingsBookingDetailsOverride'] ) ) {

			$woocommerce_events_bookings_booking_details_override = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBookingsBookingDetailsOverride'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsBookingsBookingDetailsOverride', $woocommerce_events_bookings_booking_details_override );

		}

		if ( isset( $_POST['WooCommerceEventsBookingsBookingDetailsOverridePlural'] ) ) {

			$woocommerce_events_bookings_booking_details_override_plural = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBookingsBookingDetailsOverridePlural'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsBookingsBookingDetailsOverridePlural', $woocommerce_events_bookings_booking_details_override_plural );

		}

		if ( isset( $_POST['WooCommerceEventsBookingsSlotOverride'] ) ) {

			$woocommerce_events_bookings_slot_override = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBookingsSlotOverride'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsBookingsSlotOverride', $woocommerce_events_bookings_slot_override );

		}

		if ( isset( $_POST['WooCommerceEventsBookingsSlotOverridePlural'] ) ) {

			$woocommerce_events_bookings_slot_override_plural = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBookingsSlotOverridePlural'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsBookingsSlotOverridePlural', $woocommerce_events_bookings_slot_override_plural );

		}

		if ( isset( $_POST['WooCommerceEventsSeatingRowOverride'] ) ) {

			$woocommerce_events_seating_row_override = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSeatingRowOverride'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsSeatingRowOverride', $woocommerce_events_seating_row_override );

		}

		if ( isset( $_POST['WooCommerceEventsSeatingRowOverridePlural'] ) ) {

			$woocommerce_events_seating_row_override_plural = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSeatingRowOverridePlural'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsSeatingRowOverridePlural', $woocommerce_events_seating_row_override_plural );

		}

		if ( isset( $_POST['WooCommerceEventsSeatingSeatOverride'] ) ) {

			$woocommerce_events_seating_seat_override = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSeatingSeatOverride'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsSeatingSeatOverride', $woocommerce_events_seating_seat_override );

		}

		if ( isset( $_POST['WooCommerceEventsSeatingSeatOverridePlural'] ) ) {

			$woocommerce_events_seating_seat_override_plural = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSeatingSeatOverridePlural'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsSeatingSeatOverridePlural', $woocommerce_events_seating_seat_override_plural );

		}

		if ( isset( $_POST['WooCommerceEventsSeatingSeatingChartOverride'] ) ) {

			$woocommerce_events_seating_seating_chart_override = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSeatingSeatingChartOverride'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsSeatingSeatingChartOverride', $woocommerce_events_seating_seating_chart_override );

		}

		if ( isset( $_POST['WooCommerceEventsSeatingSeatingChartOverridePlural'] ) ) {

			$woocommerce_events_seating_seating_chart_override_plural = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSeatingSeatingChartOverridePlural'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsSeatingSeatingChartOverridePlural', $woocommerce_events_seating_seating_chart_override_plural );

		}

		if ( isset( $_POST['WooCommerceEventsSeatingFrontOverride'] ) ) {

			$woocommerce_events_seating_front_override = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSeatingFrontOverride'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsSeatingFrontOverride', $woocommerce_events_seating_front_override );

		}

		if ( isset( $_POST['WooCommerceEventsSeatingFrontOverridePlural'] ) ) {

			$woocommerce_events_seating_front_override_plural = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSeatingFrontOverridePlural'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsSeatingFrontOverridePlural', $woocommerce_events_seating_front_override_plural );

		}

		if ( isset( $_POST['WooCommerceEventsDayOverridePlural'] ) ) {

			$woocommerce_events_day_override_plural = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDayOverridePlural'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsDayOverridePlural', $woocommerce_events_day_override_plural );

		}

		if ( isset( $_POST['WooCommerceEventsViewSeatingOptions'] ) ) {

			$woocommerce_events_view_seating_options = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsViewSeatingOptions'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsViewSeatingOptions', $woocommerce_events_view_seating_options );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsViewSeatingOptions', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsViewSeatingChart'] ) ) {

			$woocommerce_events_view_seating_chart = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsViewSeatingChart'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsViewSeatingChart', $woocommerce_events_view_seating_chart );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsViewSeatingChart', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsViewSeatingOtherVariations'] ) ) {

			$woocommerce_events_view_seating_other_variations = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsViewSeatingOtherVariations'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsViewSeatingOtherVariations', $woocommerce_events_view_seating_other_variations );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsViewSeatingOtherVariations', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsViewBookingsOptions'] ) ) {

			$woocommerce_events_view_bookings_options = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsViewBookingsOptions'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsViewBookingsOptions', $woocommerce_events_view_bookings_options );

		} elseif ( $this->is_checkout_block_enabled() ) {

			update_post_meta( $post_id, 'WooCommerceEventsViewBookingsOptions', 'product' );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsViewBookingsOptions', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsEventDetailsNewOrder'] ) ) {

			$woocommerce_events_event_details_new_order = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEventDetailsNewOrder'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsEventDetailsNewOrder', $woocommerce_events_event_details_new_order );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsEventDetailsNewOrder', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsDisplayAttendeeNewOrder'] ) ) {

			$woocommerce_events_display_attendee_new_order = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDisplayAttendeeNewOrder'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsDisplayAttendeeNewOrder', $woocommerce_events_display_attendee_new_order );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsDisplayAttendeeNewOrder', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsDisplayBookingsNewOrder'] ) ) {

			$woocommerce_events_display_bookings_new_order = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDisplayBookingsNewOrder'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsDisplayBookingsNewOrder', $woocommerce_events_display_bookings_new_order );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsDisplayBookingsNewOrder', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsDisplaySeatingsNewOrder'] ) ) {

			$woocommerce_events_display_seatings_new_order = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDisplaySeatingsNewOrder'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsDisplaySeatingsNewOrder', $woocommerce_events_display_seatings_new_order );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsDisplaySeatingsNewOrder', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsDisplayCustAttNewOrder'] ) ) {

			$woocommerce_events_display_cust_att_new_order = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDisplayCustAttNewOrder'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsDisplayCustAttNewOrder', $woocommerce_events_display_cust_att_new_order );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsDisplayCustAttNewOrder', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsHideBookingsDisplayTime'] ) ) {

			$woocommerce_events_hide_bookings_display_time = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsHideBookingsDisplayTime'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsHideBookingsDisplayTime', $woocommerce_events_hide_bookings_display_time );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsHideBookingsDisplayTime', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsHideBookingsSlotCalendar'] ) ) {

			$woocommerce_events_hide_bookings_slot_calendar = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsHideBookingsSlotCalendar'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsHideBookingsSlotCalendar', $woocommerce_events_hide_bookings_slot_calendar );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsHideBookingsSlotCalendar', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsHideBookingsStockAvailability'] ) ) {

			$woocommerce_events_hide_bookings_stock_availability = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsHideBookingsStockAvailability'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsHideBookingsStockAvailability', $woocommerce_events_hide_bookings_stock_availability );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsHideBookingsStockAvailability', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsViewBookingsStockDropdowns'] ) ) {

			$woocommerce_events_view_bookings_stock_dropdowns = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsViewBookingsStockDropdowns'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsViewBookingsStockDropdowns', $woocommerce_events_view_bookings_stock_dropdowns );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsViewBookingsStockDropdowns', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsViewOutOfStockBookings'] ) ) {

			$woocommerce_events_view_out_of_stock_bookings = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsViewOutOfStockBookings'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsViewOutOfStockBookings', $woocommerce_events_view_out_of_stock_bookings );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsViewOutOfStockBookings', 'off' );

		}

		if ( isset( $_POST['WooCommerceEventsBookingsMethod'] ) ) {

			$woocommerce_events_bookings_method = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBookingsMethod'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsBookingsMethod', $woocommerce_events_bookings_method );

		}

		if ( isset( $_POST['WooCommerceEventsBookingsHideDateSingleDropDown'] ) ) {

			$woocommerce_events_bookings_hide_date_single_dropdown = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsBookingsHideDateSingleDropDown'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsBookingsHideDateSingleDropDown', $woocommerce_events_bookings_hide_date_single_dropdown );

		} else {

			update_post_meta( $post_id, 'WooCommerceEventsBookingsHideDateSingleDropDown', 'off' );

		}

		$this->save_printing_options( $post_id, false );

		if ( isset( $_POST['WooCommerceEventsZoomHost'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$woocommerce_events_zoom_host = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsZoomHost'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_post_meta( $post_id, 'WooCommerceEventsZoomHost', $woocommerce_events_zoom_host );

			if ( '' !== $woocommerce_events_zoom_host && ( ! isset( $previous_post_meta['WooCommerceEventsZoomHost'] ) || $previous_post_meta['WooCommerceEventsZoomHost'][0] !== $woocommerce_events_zoom_host ) ) {

				$this->zoom_api_helper->add_zoom_assistant( $woocommerce_events_zoom_host );

			}
		}

		if ( isset( $_POST['globalWooCommerceEventsZoomUsers'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$global_woocommerce_events_zoom_users = sanitize_text_field( wp_unslash( $_POST['globalWooCommerceEventsZoomUsers'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			update_option( 'globalWooCommerceEventsZoomUsers', $global_woocommerce_events_zoom_users );

		}

		if ( isset( $_POST['WooCommerceEventsZoomType'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$woocommerce_events_zoom_type = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsZoomType'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_post_meta( $post_id, 'WooCommerceEventsZoomType', $woocommerce_events_zoom_type );

		}

		if ( isset( $_POST['WooCommerceEventsZoomMultiOption'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$woocommerce_events_zoom_multi_option = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsZoomMultiOption'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			update_post_meta( $post_id, 'WooCommerceEventsZoomMultiOption', $woocommerce_events_zoom_multi_option );

		}

		if ( 'single' === $woocommerce_events_zoom_multi_option ) {

			// Single Zoom meeting.
			$zoom_options = $this->zoom_api_helper->get_product_zoom_options( $post_id );

			if ( isset( $_POST['WooCommerceEventsZoomWebinar'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

				$woocommerce_events_zoom_webinar = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsZoomWebinar'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

				if ( 'auto' === $woocommerce_events_zoom_webinar ) {

					// Create Zoom meeting.
					$response = $this->zoom_api_helper->create_zoom_meeting( $zoom_options );

					if ( 'success' === $response['status'] ) {

						$woocommerce_events_zoom_webinar = $response['data']['id'];

					}
				}

				update_post_meta( $post_id, 'WooCommerceEventsZoomWebinar', $woocommerce_events_zoom_webinar );

			}
		} else {

			// Multi Zoom meetings.
			if ( isset( $_POST['WooCommerceEventsZoomWebinarMulti'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

				$woocommerce_events_zoom_webinar_multi = $_POST['WooCommerceEventsZoomWebinarMulti']; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput

				$multi_count = count( $woocommerce_events_zoom_webinar_multi );

				for ( $day_index = 0; $day_index < $multi_count; $day_index++ ) {

					$zoom_id = &$woocommerce_events_zoom_webinar_multi[ $day_index ];

					if ( 'auto' === $zoom_id ) {

						// Create Zoom meeting.
						$zoom_options = $this->zoom_api_helper->get_product_zoom_options( $post_id, false, array(), $day_index );
						$response     = $this->zoom_api_helper->create_zoom_meeting( $zoom_options );

						if ( 'success' === $response['status'] ) {

							$zoom_id = $response['data']['id'];

						}
					}
				}

				update_post_meta( $post_id, 'WooCommerceEventsZoomWebinarMulti', $woocommerce_events_zoom_webinar_multi );
			}
		}

		$woocommerce_events_zoom_duration_hour   = 1;
		$woocommerce_events_zoom_duration_minute = 0;

		if ( isset( $_POST['WooCommerceEventsZoomDurationHour'] ) && isset( $_POST['WooCommerceEventsZoomDurationMinute'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$hour   = (int) sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsZoomDurationHour'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$minute = (int) sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsZoomDurationMinute'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( $hour > 0 || $minute > 0 ) {

				$woocommerce_events_zoom_duration_hour   = (string) $hour;
				$woocommerce_events_zoom_duration_minute = (string) $minute;

			}
		}

		update_post_meta( $post_id, 'WooCommerceEventsZoomDurationHour', $woocommerce_events_zoom_duration_hour );
		update_post_meta( $post_id, 'WooCommerceEventsZoomDurationMinute', $woocommerce_events_zoom_duration_minute );

		if ( isset( $_POST['WooCommerceEventsMailchimpList'] ) ) {

			$woocommerce_events_mailchimp_list = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsMailchimpList'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsMailchimpList', $woocommerce_events_mailchimp_list );

		}

		if ( isset( $_POST['WooCommerceEventsMailchimpTags'] ) ) {

			$woocommerce_events_mailchimp_tags = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsMailchimpTags'] ) );
			update_post_meta( $post_id, 'WooCommerceEventsMailchimpTags', $woocommerce_events_mailchimp_tags );

		}

		if ( true === $should_update_passes ) {

			wp_schedule_single_event( time() + 5, 'update_fooevents_addtowallet_passes_action', array( $post_id ) );

		}
	}

	/**
	 * Displays the event details on the front end template. Before WooCommerce Displays content.
	 *
	 * @param array $tabs tabs.
	 * @global object $post
	 * @return array $tabs
	 */
	public function add_front_end_tab( $tabs ) {

		global $post;

		$woocommerce_events_event                  = get_post_meta( $post->ID, 'WooCommerceEventsEvent', true );
		$woocommerce_events_google_maps            = get_post_meta( $post->ID, 'WooCommerceEventsGoogleMaps', true );
		$global_woocommerce_hide_event_details_tab = get_option( 'globalWooCommerceHideEventDetailsTab', true );
		$event_details_text                        = get_post_meta( $post->ID, 'WooCommerceEventsEventDetailsOverride', true );

		if ( '' === $event_details_text ) {

			$event_details_text = __( 'Event Details', 'woocommerce-events' );

		}

		if ( 'Event' === $woocommerce_events_event ) {

			if ( 'yes' !== $global_woocommerce_hide_event_details_tab ) {

				$tabs['woocommerce_events'] = array(
					'title'    => $event_details_text,
					'priority' => 30,
					'callback' => 'fooevents_display_event_tab',
				);

			}

			if ( ! empty( $woocommerce_events_google_maps ) ) {

				$tabs['description'] = array(
					'title'    => __( 'Description', 'woocommerce-events' ),
					'priority' => 1,
					'callback' => 'fooevents_display_event_tab_map',
				);

			}
		}

		return $tabs;
	}

	/**
	 * Creates an orders tickets
	 *
	 * @param int $order_id order ID.
	 */
	public function create_tickets( $order_id ) {

		$global_woocommerce_events_disable_sub_ticket_gen = get_option( 'globalWooCommerceEventsDisableSubTicketGen' );

		$wc_order = wc_get_order( $order_id );

		$woocommerce_events_order_tickets = $wc_order->get_meta( 'WooCommerceEventsOrderTickets', true );
		$woocommerce_events_sent_ticket   = $wc_order->get_meta( 'WooCommerceEventsTicketsGenerated', true );
		$woocommerce_events_created_via   = '';

		if ( 'yes' === $global_woocommerce_events_disable_sub_ticket_gen ) {

			$woocommerce_events_created_via = $wc_order->get_meta( '_created_via', true );
		}

		if ( 'yes' !== $woocommerce_events_sent_ticket && ! empty( $woocommerce_events_order_tickets ) && 'subscription' !== $woocommerce_events_created_via ) {

			$mailchimp_api_key       = get_option( 'globalWooCommerceEventsMailchimpAPIKey' );
			$mailchimp_server_prefix = get_option( 'globalWooCommerceEventsMailchimpServer' );

			$x = 1;
			foreach ( $woocommerce_events_order_tickets as $event => $tickets ) {

				$y = 1;
				foreach ( $tickets as $ticket ) {

					/*
					echo "<pre>";
						print_r($ticket);
					echo "</pre>";
					exit();*/

					if ( ! empty( $ticket['WooCommerceEventsOrderID'] ) ) {

						$rand = rand( 111111, 999999 );

						$post = array(

							'post_author'  => $ticket['WooCommerceEventsCustomerID'],
							'post_content' => 'Ticket',
							'post_status'  => 'publish',
							'post_title'   => 'Assigned Ticket ' . $rand,
							'post_type'    => 'event_magic_tickets',

						);

						$post['ID']              = wp_insert_post( $post );
						$ticket_id               = $post['ID'] . $rand;
						$post['post_title']      = '#' . $ticket_id;
						$ticket_post_id          = wp_update_post( $post );
						$ticket_expire_type      = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsTicketExpirationType', true );
						$events_type             = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsType', true );
						$ticket_expire_select    = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsTicketsExpireSelectTimestamp', true );
						$ticket_expire_value     = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsTicketsExpireValue', true );
						$ticket_expire_unit      = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsTicketsExpireUnit', true );
						$ticket_expire_timestamp = '';

						if ( 'select' === $ticket_expire_type && ! empty( $ticket_expire_select ) ) {

							$ticket_expire_timestamp = $ticket_expire_select;

						} elseif ( 'time' === $ticket_expire_type && ! empty( $ticket_expire_value ) && ! empty( $ticket_expire_unit ) ) {

							$ticket_expire_timestamp = strtotime( '+' . $ticket_expire_value . ' ' . $ticket_expire_unit );

						}

						$ticket_hash = $this->generate_random_string( 8 );

						update_post_meta( $ticket_post_id, 'WooCommerceEventsTicketID', $ticket_id );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsTicketHash', $ticket_hash );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsProductID', $ticket['WooCommerceEventsProductID'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsOrderID', $ticket['WooCommerceEventsOrderID'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsTicketType', $ticket['WooCommerceEventsTicketType'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsCustomerID', $ticket['WooCommerceEventsCustomerID'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeName', $ticket['WooCommerceEventsAttendeeName'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeLastName', $ticket['WooCommerceEventsAttendeeLastName'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeEmail', $ticket['WooCommerceEventsAttendeeEmail'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeTelephone', $ticket['WooCommerceEventsAttendeeTelephone'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeCompany', $ticket['WooCommerceEventsAttendeeCompany'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeDesignation', $ticket['WooCommerceEventsAttendeeDesignation'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsVariations', $ticket['WooCommerceEventsVariations'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsVariationID', $ticket['WooCommerceEventsVariationID'] );

						update_post_meta( $ticket_post_id, 'WooCommerceEventsPurchaserFirstName', $ticket['WooCommerceEventsPurchaserFirstName'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsPurchaserLastName', $ticket['WooCommerceEventsPurchaserLastName'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsPurchaserEmail', $ticket['WooCommerceEventsPurchaserEmail'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsPurchaserPhone', $ticket['WooCommerceEventsPurchaserPhone'] );

						update_post_meta( $ticket_post_id, 'WooCommerceEventsPrice', $ticket['WooCommerceEventsPrice'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsTicketExpireTimestamp', $ticket_expire_timestamp );

						if ( false !== $wc_order ) {

							update_post_meta( $ticket_post_id, 'WooCommerceEventsStatus', 'Not Checked In' );

						}

						if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

							require_once ABSPATH . '/wp-admin/includes/plugin.php';

						}

						if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

							$fooevents_custom_attendee_fields          = new Fooevents_Custom_Attendee_Fields();
							$woocommerce_events_custom_attendee_fields = $fooevents_custom_attendee_fields->process_capture_custom_attendee_options( $ticket_post_id, $ticket['WooCommerceEventsCustomAttendeeFields'] );

						}

						if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

							$fooevents_seating                 = new Fooevents_Seating();
							$woocommerce_events_seating_fields = $fooevents_seating->process_capture_seating_options( $ticket_post_id, $ticket['WooCommerceEventsSeatingFields'] );

						}

						if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

							if ( 'bookings' === $events_type ) {

								$fooevents_bookings                = new Fooevents_Bookings();
								$woocommerce_events_booking_fields = $fooevents_bookings->process_capture_booking( $ticket['WooCommerceEventsProductID'], $ticket['WooCommerceEventsBookingOptions'], $ticket_post_id, $ticket );

							}
							/*
							update_post_meta( $ticket_post_id, 'WooCommerceEventsBookingDateID', $ticket['booking_selection_date'] );
							$slot = explode( '_', $ticket['booking_selection_slot'] );
							update_post_meta( $ticket_post_id, 'WooCommerceEventsBookingSlotID', $slot[0] );*/

						}

						$product = get_post( $ticket['WooCommerceEventsProductID'] );

						update_post_meta( $ticket_post_id, 'WooCommerceEventsProductName', $product->post_title );

						// Hook create ticket.
						do_action( 'fooevents_create_ticket', $ticket_post_id );

						if ( ! empty( $mailchimp_api_key ) && ! empty( $mailchimp_server_prefix ) ) {

							$this->mailchimp_helper->push_to_lists( $ticket['WooCommerceEventsProductID'], $ticket['WooCommerceEventsPurchaserFirstName'], $ticket['WooCommerceEventsPurchaserLastName'], $ticket['WooCommerceEventsPurchaserEmail'], $ticket['WooCommerceEventsAttendeeName'], $ticket['WooCommerceEventsAttendeeLastName'], $ticket['WooCommerceEventsAttendeeEmail'] );

						}

						$ticket_number = $this->increment_ticket_number( $ticket['WooCommerceEventsProductID'] );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsTicketNumber', $ticket_number );

						$ticket_formated_number = $this->format_ticket_number( $ticket['WooCommerceEventsProductID'], $ticket_number );
						update_post_meta( $ticket_post_id, 'WooCommerceEventsTicketNumberFormatted', $ticket_formated_number );

						++$y;

					}
				}

				++$x;

			}

			$this->zoom_api_helper->add_update_zoom_registrants( $order_id );

			$wc_order->update_meta_data( 'WooCommerceEventsTicketsGenerated', 'yes' );
			$wc_order->save();

		}
	}

	/**
	 * Increments ticket number
	 *
	 * @param int $product_id the product ID.
	 * @return int
	 */
	public function increment_ticket_number( $product_id ) {

		$woocommerce_events_tickets_generated = get_post_meta( $product_id, '_WooCommerceEventsCounter', true );

		if ( empty( $woocommerce_events_tickets_generated ) ) {

			$woocommerce_events_tickets_generated = 1;

		} else {

			++$woocommerce_events_tickets_generated;

		}

		update_post_meta( $product_id, '_WooCommerceEventsCounter', $woocommerce_events_tickets_generated );

		return $woocommerce_events_tickets_generated;
	}

	/**
	 * Returns formatted ticket number based on event options
	 *
	 * @param int    $product_id the product id.
	 * @param string $ticket_number the ticket number.
	 * @return string
	 */
	public function format_ticket_number( $product_id, $ticket_number ) {

		$padding = (int) get_post_meta( $product_id, 'WooCommerceEventsTicketNumberOutput', true );
		$prefix  = get_post_meta( $product_id, 'WooCommerceEventsTicketNumberPrefix', true );
		$suffix  = get_post_meta( $product_id, 'WooCommerceEventsTicketNumberSuffix', true );

		$identifier = str_pad( $ticket_number, $padding, '0', STR_PAD_LEFT );

		$identifier = $prefix . $identifier . $suffix;

		return $identifier;
	}

	/**
	 * Sends a ticket email once an order is completed.
	 *
	 * @param int $order_id order ID.
	 * @global $woocommerce
	 */
	public function send_ticket_email( $order_id ) {

		$this->create_tickets( $order_id );

		set_time_limit( 0 );

		global $woocommerce;

		$order   = wc_get_order( $order_id );
		$tickets = $order->get_items();

		$woocommerce_events_tickets_purchased = $order->get_meta( 'WooCommerceEventsTicketsPurchased', true );

		$customer = $order->get_meta( '_customer_user', true );
		$usermeta = get_user_meta( $customer );

		$woocommerce_events_sent_ticket = $order->get_meta( 'WooCommerceEventsSentTicket', true );

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

			$fooevents_custom_attendee_fields          = new Fooevents_Custom_Attendee_Fields();
			$woocommerce_events_custom_attendee_fields = $fooevents_custom_attendee_fields->process_capture_custom_attendee_options( $post_id, $ticket['WooCommerceEventsCustomAttendeeFields'] );

		}

		if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

			$fooevents_seating                 = new Fooevents_Seating();
			$woocommerce_events_seating_fields = $fooevents_seating->process_capture_seating_options( $post_id, $ticket['WooCommerceEventsSeatingFields'] );

		}

		$product = get_post( $ticket['WooCommerceEventsProductID'] );

		update_post_meta( $post_id, 'WooCommerceEventsProductName', $product->post_title );

		++$x;
	}

	/**
	 * Sends a ticket email once an order is completed.
	 *
	 * @param int $order_id order ID.
	 * @global $woocommerce
	 */
	public function process_order_tickets( $order_id, $old_status, $new_status ) {

		set_time_limit( 0 );

		$new_status = 'wc-' . $new_status;

		$global_woocommerce_events_send_on_status = get_option( 'globalWooCommerceEventsSendOnStatus' );

		if ( ! empty( $global_woocommerce_events_send_on_status ) && ! is_array( $global_woocommerce_events_send_on_status ) ) {

			$global_woocommerce_events_send_on_status[] = $global_woocommerce_events_send_on_status;

		} elseif ( empty( $global_woocommerce_events_send_on_status ) ) {

			$global_woocommerce_events_send_on_status   = array();
			$global_woocommerce_events_send_on_status[] = 'wc-completed';

		}

		if ( in_array( $new_status, $global_woocommerce_events_send_on_status ) ) {

			$this->create_tickets( $order_id );
			$this->build_send_tickets( $order_id );

		}
	}

	/**
	 * Builds tickets to be emailed
	 *
	 * @param int $order_id order ID.
	 */
	public function build_send_tickets( $order_id, $resend = false, $email_override = '' ) {

		global $wpdb;
		$order = wc_get_order( $order_id );

		$woocommerce_events_sent_ticket   = $order->get_meta( 'WooCommerceEventsTicketsSent', true );
		$woocommerce_events_order_tickets = $order->get_meta( 'WooCommerceEventsOrderTickets', true );

		if ( ( 'yes' !== $woocommerce_events_sent_ticket && ! empty( $woocommerce_events_order_tickets ) ) || true === $resend ) {

			$order_tickets = $wpdb->get_results(
				$wpdb->prepare(
					"
				SELECT p.*
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = 'event_magic_tickets'
				AND pm.meta_key = 'WooCommerceEventsOrderID'
				AND pm.meta_value = %d
				",
					$order_id
				)
			);

			$email_html = '';

			$sorted_order_tickets = array();

			// Sort tickets into events.
			foreach ( $order_tickets as $order_ticket ) {

				$ticket = $this->ticket_helper->get_ticket_data( $order_ticket->ID );
				$sorted_order_tickets[ $ticket['WooCommerceEventsProductID'] ][] = $ticket;

			}

			foreach ( $sorted_order_tickets as $product_id => $tickets ) {

				$woocommerce_events_email_attendee       = get_post_meta( $product_id, 'WooCommerceEventsEmailAttendee', true );
				$woocommerce_events_email_subject_single = get_post_meta( $product_id, 'WooCommerceEventsEmailSubjectSingle', true );

				if ( empty( $woocommerce_events_email_subject_single ) ) {

					$woocommerce_events_email_subject_single = __( '{OrderNumber} Ticket', 'woocommerce-events' );

				}

				$event_location = get_post_meta( $product_id, 'WooCommerceEventsLocation', true );
				$event_date     = get_post_meta( $product_id, 'WooCommerceEventsDate', true );
				$event_hour     = get_post_meta( $product_id, 'WooCommerceEventsHour', true );
				$event_minute   = get_post_meta( $product_id, 'WooCommerceEventsMinutes', true );
				$event_period   = get_post_meta( $product_id, 'WooCommerceEventsPeriod', true );

				$event_end_date   = get_post_meta( $product_id, 'WooCommerceEventsEndDate', true );
				$event_hour_end   = get_post_meta( $product_id, 'WooCommerceEventsHourEnd', true );
				$event_minute_end = get_post_meta( $product_id, 'WooCommerceEventsMinutesEnd', true );
				$event_period_end = get_post_meta( $product_id, 'WooCommerceEventsEndPeriod', true );

				$merge_fields_global = array(
					'{OrderNumber}'       => '[#' . $order_id . ']',
					'{OrderNumberOnly}'   => $order_id,
					'{EventName}'         => get_the_title( $product_id ),
					'{EventVenue}'        => $event_location,
					'{EventDate}'         => $event_date,
					'{EventHour}'         => $event_hour,
					'{EventMinute}'       => $event_minute,
					'{EventPeriod}'       => $event_period,
					'{EventEndDate}'      => $event_end_date,
					'{EventHourEnd}'      => $event_hour_end,
					'{EventMinuteEnd}'    => $event_minute_end,
					'{EventPeriodEnd}'    => $event_period_end,
					'{CustomerFirstName}' => $order->get_billing_first_name(),
					'{CustomerLastName}'  => $order->get_billing_last_name(),
					'{CustomerEmail}'     => $order->get_billing_email(),
				);

				$subject = strtr( $woocommerce_events_email_subject_single, $merge_fields_global );

				$woocommerce_events_ticket_theme = get_post_meta( $product_id, 'WooCommerceEventsTicketTheme', true );
				if ( empty( $woocommerce_events_ticket_theme ) ) {

					$woocommerce_events_ticket_theme = $this->config->email_template_path;

				}

				$header = $this->mail_helper->parse_email_template( $woocommerce_events_ticket_theme . '/header.php', $tickets[0], array() );
				$footer = $this->mail_helper->parse_email_template( $woocommerce_events_ticket_theme . '/footer.php', $tickets[0], array() );

				$ticket_body = '';

				$email_attendee = false;
				$ticket_count   = 1;

				foreach ( $tickets as $ticket ) {

					$merge_fields_global['{AttendeeFName}'] = $ticket['WooCommerceEventsAttendeeName'];
					$merge_fields_global['{AttendeeLName}'] = $ticket['WooCommerceEventsAttendeeLastName'];
					$merge_fields_global['{AttendeeEmail}'] = $ticket['WooCommerceEventsAttendeeEmail'];
					$merge_fields_global['{TicketID}']      = $ticket['WooCommerceEventsTicketID'];

					if ( ! empty( $ticket['WooCommerceEventsBookingSlot'] ) ) {

						$merge_fields_global['{BookingsSlot}'] = $ticket['WooCommerceEventsBookingSlot'];

					} else {

						$merge_fields_global['{BookingsSlot}'] = '';

					}

					if ( ! empty( $ticket['WooCommerceEventsBookingDate'] ) ) {

						$merge_fields_global['{BookingsDate}'] = $ticket['WooCommerceEventsBookingDate'];

					} else {

						$merge_fields_global['{BookingsDate}'] = '';

					}

					if ( ! empty( $ticket['fooevents_seating_options_array']['row_name'] ) ) {

						$merge_fields_global['{SeatingRow}'] = $ticket['fooevents_seating_options_array']['row_name'];

					} else {

						$merge_fields_global['{SeatingRow}'] = '';

					}

					if ( ! empty( $ticket['fooevents_seating_options_array']['seat_number'] ) ) {

						$merge_fields_global['{SeatingSeat}'] = $ticket['fooevents_seating_options_array']['seat_number'];

					} else {

						$merge_fields_global['{SeatingSeat}'] = '';

					}

					if ( 'on' === $woocommerce_events_email_attendee ) {

						$ticket['ticketNumber'] = 1;

					} else {

						$ticket['ticketNumber'] = $ticket_count;

					}
					$ticket['ticketTotal'] = count( $order_tickets );
					$body                  = $this->mail_helper->parse_ticket_template( $woocommerce_events_ticket_theme . '/ticket.php', $ticket );
					$body                  = strtr( $body, $merge_fields_global );
					$ticket_body          .= $body;

					$subject_ticket = strtr( $subject, $merge_fields_global );

					// Send to attendee.
					if ( 'on' === $woocommerce_events_email_attendee && isset( $ticket['WooCommerceEventsAttendeeEmail'] ) && false === $resend ) {

						$attachments = array();
						if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

							require_once ABSPATH . '/wp-admin/includes/plugin.php';

						}

						$pos_enable_ticket_emails = 'on';
						$pos_order                = false;

						if ( is_plugin_active( 'fooevents_pos/fooevents-pos.php' ) || is_plugin_active_for_network( 'fooevents_pos/fooevents-pos.php' ) ) {

							if ( 'fooeventspos_app' === $order->get_meta( '_fooeventspos_order_source', true ) ) {

								$pos_order = true;

								$pos_enable_ticket_emails = $ticket['WooCommerceEventsPOSEnableTicketEmails'];

								if ( '' === $pos_enable_ticket_emails ) {
									$pos_enable_ticket_emails = 'on';
								}
							}
						} elseif ( is_plugin_active( 'foosales/foosales.php' ) || is_plugin_active_for_network( 'foosales/foosales.php' ) || is_plugin_active( 'foosales-for-woocommerce/foosales-for-woocommerce.php' ) || is_plugin_active_for_network( 'foosales-for-woocommerce/foosales-for-woocommerce.php' ) ) {

							if ( 'foosales_app' === $order->get_meta( '_foosales_order_source', true ) ) {

								$pos_order = true;

								$pos_enable_ticket_emails = $ticket['WooCommerceEventsPOSEnableTicketEmails'];

								if ( '' === $pos_enable_ticket_emails ) {
									$pos_enable_ticket_emails = 'on';
								}
							}
						}

						if ( is_plugin_active( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) || is_plugin_active_for_network( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) ) {

							$global_fooevents_pdf_tickets_enable             = get_option( 'globalFooEventsPDFTicketsEnable' );
							$global_fooevents_pdf_tickets_attach_html_ticket = get_option( 'globalFooEventsPDFTicketsAttachHTMLTicket' );

							if ( 'yes' === $global_fooevents_pdf_tickets_enable ) {

								$fooevents_pdf_tickets = new FooEvents_PDF_Tickets();

								$attachments[]                    = $fooevents_pdf_tickets->generate_ticket( $product_id, array( $ticket ), $this->config->barcode_path, $this->config->path, $merge_fields_global );
								$fooevents_pdf_tickets_email_text = nl2br( get_post_meta( $product_id, 'FooEventsPDFTicketsEmailText', true ) );

								if ( ! empty( $fooevents_pdf_tickets_email_text ) ) {

									$fooevents_pdf_tickets_email_text = '<div class="fooevents-pdf-email-text">' . $fooevents_pdf_tickets_email_text . '</div>';

								}

								if ( empty( $global_fooevents_pdf_tickets_attach_html_ticket ) ) {

									$header = $fooevents_pdf_tickets->parse_email_template( 'email-header.php' );
									$footer = $fooevents_pdf_tickets->parse_email_template( 'email-footer.php' );

									$body = $header . $fooevents_pdf_tickets_email_text . $footer;

								}
							}
						}

						// attach ics.
						$woocommerce_events_ticket_attach_ics = get_post_meta( $product_id, 'WooCommerceEventsTicketAttachICS', true );

						if ( ! empty( $woocommerce_events_ticket_attach_ics ) && 'on' === $woocommerce_events_ticket_attach_ics && file_exists( $this->config->ics_path . $ticket['WooCommerceEventsTicketID'] . '.ics' ) ) {

							$attachments[] = $this->config->ics_path . '' . $ticket['WooCommerceEventsTicketID'] . '.ics';

						}

						if ( ( 'on' === $ticket['WooCommerceEventsSendEmailTickets'] && false === $pos_order ) || ( true === $pos_order && 'on' === $pos_enable_ticket_emails ) ) {

							$mail_status = $this->mail_helper->send_ticket( $ticket['WooCommerceEventsAttendeeEmail'], $subject_ticket, $header . $body . $footer, $attachments, $product_id );

						}

						$email_attendee = true;

					}

					++$ticket_count;

				}

				// Send to purchaser.
				if ( ( 'on' !== $woocommerce_events_email_attendee && false === $email_attendee ) || true === $resend ) {

					$attachments = array();

					$merge_fields_global['{AttendeeFName}'] = $order->get_billing_first_name();
					$merge_fields_global['{AttendeeLName}'] = $order->get_billing_last_name();
					$merge_fields_global['{TicketID}']      = '';

					$subject_ticket = strtr( $subject, $merge_fields_global );

					if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

						require_once ABSPATH . '/wp-admin/includes/plugin.php';

					}

					$pos_enable_ticket_emails = 'on';
					$pos_order                = false;

					if ( is_plugin_active( 'fooevents_pos/fooevents-pos.php' ) || is_plugin_active_for_network( 'fooevents_pos/fooevents-pos.php' ) ) {

						if ( 'fooeventspos_app' === $order->get_meta( '_fooeventspos_order_source', true ) ) {

							$pos_order = true;

							$pos_enable_ticket_emails = $ticket['WooCommerceEventsPOSEnableTicketEmails'];

							if ( '' === $pos_enable_ticket_emails ) {
								$pos_enable_ticket_emails = 'on';
							}
						}
					} elseif ( is_plugin_active( 'foosales/foosales.php' ) || is_plugin_active_for_network( 'foosales/foosales.php' ) || is_plugin_active( 'foosales-for-woocommerce/foosales-for-woocommerce.php' ) || is_plugin_active_for_network( 'foosales-for-woocommerce/foosales-for-woocommerce.php' ) ) {

						if ( 'foosales_app' === $order->get_meta( '_foosales_order_source', true ) ) {

							$pos_order = true;

							$pos_enable_ticket_emails = $ticket['WooCommerceEventsPOSEnableTicketEmails'];

							if ( '' === $pos_enable_ticket_emails ) {
								$pos_enable_ticket_emails = 'on';
							}
						}
					}

					if ( is_plugin_active( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) || is_plugin_active_for_network( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) ) {

						$global_fooevents_pdf_tickets_enable             = get_option( 'globalFooEventsPDFTicketsEnable' );
						$global_fooevents_pdf_tickets_layout             = get_option( 'globalFooEventsPDFTicketsLayout' );
						$global_fooevents_pdf_tickets_attach_html_ticket = get_option( 'globalFooEventsPDFTicketsAttachHTMLTicket' );

						if ( empty( $global_fooevents_pdf_tickets_layout ) ) {

							$global_fooevents_pdf_tickets_layout = 'single';

						}

						if ( 'yes' === $global_fooevents_pdf_tickets_enable ) {

							$fooevents_pdf_tickets = new FooEvents_PDF_Tickets();

							$attachments[] = $fooevents_pdf_tickets->generate_ticket( $product_id, $tickets, $this->config->barcode_path, $this->config->path, $merge_fields_global );

							if ( 'yes' === $global_fooevents_pdf_tickets_attach_html_ticket ) {

								$attached_text = nl2br( get_post_meta( $product_id, 'FooEventsPDFTicketsEmailText', true ) );

								if ( ! empty( $attached_text ) ) {

									$attached_text = '<div class="fooevents-pdf-email-text">' . $attached_text . '</div>';

								}

								$header = $header . $attached_text;

							} else {

								$ticket_body = nl2br( get_post_meta( $product_id, 'FooEventsPDFTicketsEmailText', true ) );

								if ( ! empty( $ticket_body ) ) {

									$ticket_body = '<div class="fooevents-pdf-email-text">' . $ticket_body . '</div>';

								}

								$header = $fooevents_pdf_tickets->parse_email_template( 'email-header.php' );
								$footer = $fooevents_pdf_tickets->parse_email_template( 'email-footer.php' );

							}
						}
					}

					// attach ics.
					$woocommerce_events_ticket_attach_ics = get_post_meta( $product_id, 'WooCommerceEventsTicketAttachICS', true );

					if ( ! empty( $woocommerce_events_ticket_attach_ics ) && 'on' === $woocommerce_events_ticket_attach_ics && file_exists( $this->config->ics_path . $ticket['WooCommerceEventsTicketID'] . '.ics' ) ) {

						$attachments[] = $this->config->ics_path . '' . $ticket['WooCommerceEventsTicketID'] . '.ics';

					}

					$send_email_address = $order->get_billing_email();

					if ( true === $resend && ! empty( $email_override ) ) {

						$send_email_address = $email_override;

					}

					if ( ( 'on' === $ticket['WooCommerceEventsSendEmailTickets'] && false === $pos_order ) || ( true === $pos_order && 'on' === $pos_enable_ticket_emails ) ) {

						$mail_status = $this->mail_helper->send_ticket( $send_email_address, $subject_ticket, $header . $ticket_body . $footer, $attachments, $product_id );

					}
				}
			}

			$order->update_meta_data( 'WooCommerceEventsTicketsSent', 'yes' );
			$order->save();

		}
	}

	/**
	 * Displays thank you text on order completion page.
	 *
	 * @param string $thank_you_text thank you text.
	 * @return string
	 */
	public function display_thank_you_text( $thank_you_text ) {

		global $woocommerce;
		global $post;
		global $wp;

		if ( isset( $wp->query_vars['order-received'] ) ) {

			$order_id = absint( $wp->query_vars['order-received'] );
			$order    = wc_get_order( $order_id );

			$items = '';
			if ( $order ) {

				$items = $order->get_items();

			}

			if ( is_array( $items ) ) {

				$products = array();
				foreach ( $items as $item ) {

					$products[ $item['product_id'] ] = $item['product_id'];

				}

				foreach ( $products as $key => $product_id ) {

					$woocommerce_events_thank_you_text = get_post_meta( $product_id, 'WooCommerceEventsThankYouText', true );

					if ( ! empty( $woocommerce_events_thank_you_text ) ) {

						echo wp_kses_post( wpautop( $woocommerce_events_thank_you_text ) ) . '<br/><br/>';

					}
				}
			}
		}

		return $thank_you_text;
	}

	/**
	 * Cancels ticket when order is canceled.
	 *
	 * @param int $order_id order ID.
	 */
	public function order_status_cancelled( $order_id ) {

		$order = wc_get_order( $order_id );

		if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

			$fooevents_seating = new Fooevents_Seating();

		}

		$tickets = new WP_Query(
			array(
				'post_type'      => array( 'event_magic_tickets' ),
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'   => 'WooCommerceEventsOrderID',
						'value' => $order_id,
					),
				),
			)
		);

		$seats_available_order_status = get_option( 'globalWooCommerceEventsSeatingSeatsAvailableOrderStatus', array( 'wc-cancelled' ) );

		if ( '' === $seats_available_order_status ) {
			$seats_available_order_status = array();
		}

		$tickets = $tickets->get_posts();

		foreach ( $tickets as $ticket ) {

			update_post_meta( $ticket->ID, 'WooCommerceEventsStatus', 'Canceled' );

			if ( isset( $fooevents_seating ) && in_array( 'wc-cancelled', $seats_available_order_status ) ) {
				$fooevents_seating->remove_unavailable_seats_from_event( $ticket->ID );
			}
		}

		/*Also make seats available for tickets that haven't been created yet based on the setting selected under FooEvents -> Settings -> Seating -> Make seats available on order status*/
		if ( isset( $fooevents_seating ) ) {

			$this->remove_unavailable_seats_order_status_change( $fooevents_seating, $seats_available_order_status, $order_id, 'wc-cancelled' );

		}

		$this->zoom_api_helper->cancel_zoom_registrations( $tickets );

		$order->update_meta_data( 'WooCommerceEventsOrderCanceled', true );
		$order->save();

		return $order_id;
	}

	/**
	 * Makes seats available again when order status is set to failed
	 *
	 * @param int $order_id order ID.
	 */
	public function order_status_failed( $order_id ) {

		if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

			$fooevents_seating = new Fooevents_Seating();

		}

		$tickets = new WP_Query(
			array(
				'post_type'      => array( 'event_magic_tickets' ),
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'   => 'WooCommerceEventsOrderID',
						'value' => $order_id,
					),
				),
			)
		);

		$seats_available_order_status = get_option( 'globalWooCommerceEventsSeatingSeatsAvailableOrderStatus', array( 'wc-cancelled' ) );
		if ( '' === $seats_available_order_status ) {
			$seats_available_order_status = array();
		}
		$tickets = $tickets->get_posts();

		foreach ( $tickets as $ticket ) {

			if ( isset( $fooevents_seating ) && in_array( 'wc-failed', $seats_available_order_status ) ) {
				$fooevents_seating->remove_unavailable_seats_from_event( $ticket->ID );
			}
		}

		/*Also make seats available for tickets that haven't been created yet based on the setting selected under FooEvents -> Settings -> Seating -> Make seats available on order status*/
		if ( isset( $fooevents_seating ) ) {

			$this->remove_unavailable_seats_order_status_change( $fooevents_seating, $seats_available_order_status, $order_id, 'wc-failed' );

		}
	}

	/**
	 * Remove unavailable seats based on global setting and order status change for tickets that haven't been created yet
	 *
	 * @param Object $fooevents_seating FooEvents Seating Class Object.
	 * @param array  $seats_available_order_status array of order statuses selected in settings.
	 * @param int    $order_id order ID.
	 * @param string $new_order_status Status to which the order changed.
	 */
	public function remove_unavailable_seats_order_status_change( $fooevents_seating, $seats_available_order_status, $order_id, $new_order_status ) {

		if ( in_array( $new_order_status, $seats_available_order_status ) ) {
			$order = wc_get_order( $order_id );

			if ( $order->get_status() !== 'completed' ) {

				$order_tickets_array = $order->get_meta( 'WooCommerceEventsOrderTickets' );
				foreach ( $order_tickets_array as $order_tickets ) {
					foreach ( $order_tickets as $order_ticket ) {

						$event_id = $order_ticket['WooCommerceEventsProductID'];
						if ( ! empty( $order_ticket['WooCommerceEventsSeatingFields'] ) ) {
							$seat_keys              = array_keys( $order_ticket['WooCommerceEventsSeatingFields'] );
							$seat_to_make_available = substr( $seat_keys[0], 24 ) . '_number_seats_' . $order_ticket['WooCommerceEventsSeatingFields'][ $seat_keys[1] ];
							$fooevents_seating->remove_unavailable_seats_from_event( $event_id, $seat_to_make_available );
						}
					}
				}
			}
		}
	}

	/**
	 * Filter WooCommerce products based on event filter selection
	 *
	 * @param array $query query.
	 */
	public function filter_product_results( $query ) {

		global $pagenow;
		global $typenow;

		if ( is_admin() && 'product' === $typenow && isset( $_GET['fooevents_filter'] ) && '' !== $_GET['fooevents_filter'] ) {

			$fooevents_filter = sanitize_text_field( wp_unslash( $_GET['fooevents_filter'] ) );
			$today            = time();
			$metaquery        = array();

			switch ( $fooevents_filter ) {

				case 'events':
					$metaquery = array(
						array(
							'key'     => 'WooCommerceEventsEvent',
							'compare' => '=',
							'value'   => 'Event',
						),
					);
					break;
				case 'single':
					// All Bookings.
					$query->query_vars['meta_key']   = 'WooCommerceEventsType';
					$query->query_vars['meta_value'] = 'single';
					break;
				case 'multi-day-sequential':
					// All Bookings.
					$query->query_vars['meta_key']   = 'WooCommerceEventsType';
					$query->query_vars['meta_value'] = 'sequential';
					break;
				case 'multi-day-select':
					// All Bookings.
					$query->query_vars['meta_key']   = 'WooCommerceEventsType';
					$query->query_vars['meta_value'] = 'select';
					break;
				case 'bookings':
					// All Bookings.
					$query->query_vars['meta_key']   = 'WooCommerceEventsType';
					$query->query_vars['meta_value'] = 'bookings';
					break;
				case 'seating':
					// All Bookings.
					$query->query_vars['meta_key']   = 'WooCommerceEventsType';
					$query->query_vars['meta_value'] = 'seating';
					break;
				case 'non-events':
					$metaquery = array(
						array(
							'key'     => 'WooCommerceEventsEvent',
							'compare' => '!=',
							'value'   => 'Event',
						),
					);
					break;
				case 'expired-events':
					$metaquery = array(
						'relation' => 'AND',
						array(
							'key'     => 'WooCommerceEventsExpireTimestamp',
							'compare' => 'NOT IN',
							'value'   => array( '' ),
						),
						array(
							'key'     => 'WooCommerceEventsExpireTimestamp',
							'value'   => $today,
							'type'    => 'numeric',
							'compare' => '<=',
						),
					);
					break;

			}

			$query->set( 'meta_query', $metaquery );

		}
	}

	/**
	 * Adds FooEvents drop down filter selection to the WooCommerce product listing
	 */
	public function filter_product_options() {

		global $typenow;
		global $wpdb;

		if ( 'product' === $typenow ) {

			$fooevents_filter = '';

			if ( isset( $_GET['fooevents_filter'] ) && '' !== $_GET['fooevents_filter'] ) {

				$fooevents_filter = sanitize_text_field( wp_unslash( $_GET['fooevents_filter'] ) );

			}

			require $this->config->template_path . 'product-listing-filter-options.php';
		}
	}

	/**
	 * Add postmeta ordering arguments
	 *
	 * @param array $sort_args sort arguments.
	 * @return array $sort_args
	 */
	public function add_postmeta_ordering( $sort_args ) {

		$global_woocommerce_event_sorting = get_option( 'globalWooCommerceEventSorting', true );

		if ( 'yes' === $global_woocommerce_event_sorting ) {

			$orderby_value = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
			switch ( $orderby_value ) {

				case 'eventdate-asc':
					$sort_args['orderby']  = 'meta_value';
					$sort_args['order']    = 'asc';
					$sort_args['meta_key'] = 'WooCommerceEventsDateTimestamp';
					break;

				case 'eventdate-desc':
					$sort_args['orderby']  = 'meta_value';
					$sort_args['order']    = 'desc';
					$sort_args['meta_key'] = 'WooCommerceEventsDateTimestamp';
					break;

			}
		}

		return $sort_args;
	}

	/**
	 * Add postmeta order by options to WooCommerce order options
	 *
	 * @param array $sortby sort by.
	 * @return array $sortby
	 */
	public function add_postmeta_orderby( $sortby ) {

		global $post;

		$global_woocommerce_event_sorting = get_option( 'globalWooCommerceEventSorting', true );
		if ( 'yes' === $global_woocommerce_event_sorting ) {

			$sortby['eventdate-asc']  = __( 'Sort by event date: Old to New', 'woocommerce-events' );
			$sortby['eventdate-desc'] = __( 'Sort by event date: New to Old', 'woocommerce-events' );

		}

		return $sortby;
	}

	/**
	 * Add event date to product template
	 */
	public function display_product_date() {

		global $post;

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		$global_woocommerce_display_event_date = get_option( 'globalWooCommerceDisplayEventDate', true );

		if ( 'yes' === $global_woocommerce_display_event_date ) {

			$event_date = get_post_meta( $post->ID, 'WooCommerceEventsDate', true );

			if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

				$fooevents_multiday_events = new Fooevents_Multiday_Events();
				$event_date                = $fooevents_multiday_events->get_multi_day_date_range( $post->ID );

			}

			if ( is_home() || is_front_page() || is_shop() || is_product_category() || is_product_tag() ) {

				echo '<p class="event-date">';
				echo esc_attr( $event_date );
				echo '</p>';

			}
		}
	}

	/**
	 * If order is canceled and then completed again.
	 *
	 * @param int $order_id order ID.
	 */
	public function order_status_completed_cancelled( $order_id ) {

		$order             = wc_get_order( $order_id );
		$tickets_generated = $order->get_meta( 'WooCommerceEventsTicketsGenerated', true );
		$order_canceled    = $order->get_meta( 'WooCommerceEventsOrderCanceled', true );

		if ( $tickets_generated && $order_canceled ) {

			$tickets = new WP_Query(
				array(
					'post_type'      => array( 'event_magic_tickets' ),
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'   => 'WooCommerceEventsOrderID',
							'value' => $order_id,
						),
					),
				)
			);
			$tickets = $tickets->get_posts();

			foreach ( $tickets as $ticket ) {

				$ticket_status = get_post_meta( $ticket->ID, 'WooCommerceEventsStatus', true );

				if ( 'Canceled' === $ticket_status ) {

					update_post_meta( $ticket->ID, 'WooCommerceEventsStatus', 'Not Checked In' );

				}
			}

			$order->update_meta_data( 'WooCommerceEventsOrderCanceled', false );
			$order->save();

		}

		return $order_id;
	}

	/**
	 * Generates attendee CSV export.
	 */
	public function woocommerce_events_csv() {

		if ( ! current_user_can( 'publish_event_magic_tickets' ) ) {

			echo 'User role does not have permission to export attendee details. Please contact site admin.';
			exit();

		}

		global $woocommerce;

		$event = '';
		if ( isset( $_GET['event'] ) ) {

			$event = sanitize_text_field( wp_unslash( $_GET['event'] ) );

		}

		$include_unpaid_tickets = '';
		if ( isset( $_GET['exportunpaidtickets'] ) ) {

			$include_unpaid_tickets = sanitize_text_field( wp_unslash( $_GET['exportunpaidtickets'] ) );

		}

		$exportbillingdetails = '';
		if ( isset( $_GET['exportbillingdetails'] ) ) {

			$exportbillingdetails = sanitize_text_field( wp_unslash( $_GET['exportbillingdetails'] ) );

		}

		$ticket_id_label            = __( 'TicketID', 'woocommerce-events' );
		$ticket_id_numeric_label    = __( 'TicketID Numeric', 'woocommerce-events' );
		$order_id_label             = __( 'OrderID', 'woocommerce-events' );
		$attendee_first_name_label  = __( 'Attendee First Name', 'woocommerce-events' );
		$attendee_last_name_label   = __( 'Attendee Last Name', 'woocommerce-events' );
		$attendee_email_label       = __( 'Attendee Email', 'woocommerce-events' );
		$ticket_status_label        = __( 'Ticket Status', 'woocommerce-events' );
		$ticket_type_label          = __( 'Ticket Type', 'woocommerce-events' );
		$ticket_variation_label     = __( 'Variation', 'woocommerce-events' );
		$attendee_telephone_label   = __( 'Attendee Telephone', 'woocommerce-events' );
		$attendee_company_label     = __( 'Attendee Company', 'woocommerce-events' );
		$attendee_designation_label = __( 'Attendee Designation', 'woocommerce-events' );
		$purchaser_first_name_label = __( 'Purchaser First Name', 'woocommerce-events' );
		$purchaser_last_name_label  = __( 'Purchaser Last Name', 'woocommerce-events' );
		$purchaser_email_label      = __( 'Purchaser Email', 'woocommerce-events' );
		$purchaser_phone_label      = __( 'Purchaser Phone', 'woocommerce-events' );
		$purchaser_company_label    = __( 'Purchaser Company', 'woocommerce-events' );
		$customer_note_label        = __( 'Customer Note', 'woocommerce-events' );
		$order_status_label         = __( 'Order Status', 'woocommerce-events' );
		$payment_method_label       = __( 'Payment Method', 'woocommerce-events' );
		$order_date_label           = __( 'Order Date', 'woocommerce-events' );

		$ticket_price_label      = __( 'Ticket Price', 'woocommerce-events' );
		$ticket_price_paid_label = __( 'Ticket Price Paid', 'woocommerce-events' );

		$billing_address_1_label   = __( 'Billing Address 1', 'woocommerce-events' );
		$billing_address_2_label   = __( 'Billing Address 2', 'woocommerce-events' );
		$billing_city_label        = __( 'Billing City', 'woocommerce-events' );
		$billing_postal_code_label = __( 'Billing Postal Code', 'woocommerce-events' );
		$billing_country_label     = __( 'Billing Country', 'woocommerce-events' );
		$billing_state_label       = __( 'Billing State', 'woocommerce-events' );
		$billing_phone_label       = __( 'Billing Phone', 'woocommerce-events' );

		$csv_blueprint = array(
			$ticket_id_label,
			$ticket_id_numeric_label,
			$order_id_label,
			$attendee_first_name_label,
			$attendee_last_name_label,
			$attendee_email_label,
			$ticket_status_label,
			$ticket_type_label,
			$ticket_variation_label,
			$attendee_telephone_label,
			$attendee_company_label,
			$attendee_designation_label,
			$purchaser_first_name_label,
			$purchaser_last_name_label,
			$purchaser_email_label,
			$purchaser_phone_label,
			$purchaser_company_label,
			$customer_note_label,
			$order_status_label,
			$payment_method_label,
			$order_date_label,
			$ticket_price_label,
			$ticket_price_paid_label,
		);
		$sorted_rows   = array();

		$events_query = new WP_Query(
			array(
				'post_type'      => array( 'event_magic_tickets' ),
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'   => 'WooCommerceEventsProductID',
						'value' => $event,
					),
				),
			)
		);
		$events       = $events_query->get_posts();

		$x = 0;
		foreach ( $events as $event_item ) {

			$id        = $event_item->ID;
			$var_id    = get_post_meta( $id, 'WooCommerceEventsVariationID', true );
			$ticket    = get_post( $id );
			$ticket_id = $ticket->post_title;

			$order_id = get_post_meta( $id, 'WooCommerceEventsOrderID', true );

			/* Get ticket price */

			$order = wc_get_order( $order_id );

			$ticket_id_numeric         = get_post_meta( $id, 'WooCommerceEventsTicketNumberFormatted', true );
			$product_id                = get_post_meta( $id, 'WooCommerceEventsProductID', true );
			$customer_id               = get_post_meta( $id, 'WooCommerceEventsCustomerID', true );
			$woocommerce_events_status = get_post_meta( $id, 'WooCommerceEventsStatus', true );
			$ticket_type               = get_post_meta( $ticket->ID, 'WooCommerceEventsTicketType', true );

			$quantity = '';
			$subtotal = '';
			$total    = '';

			if ( false !== $order ) {

				foreach ( $order->get_items() as $item_id => $item ) {

					$product_id_on_order   = $item->get_product_id();
					$variation_id_on_order = $item->get_variation_id();

					if ( ( $product_id == $product_id_on_order && empty( $var_id ) ) || ( ! empty( $var_id ) && $var_id == $variation_id_on_order ) ) {

						$quantity = $item->get_quantity();
						if ( $quantity > 0 ) {
							$subtotal = ( $item->get_subtotal() / $quantity ) + ( $item->get_subtotal_tax() / $quantity );
							$total    = ( $item->get_total() / $quantity ) + ( $item->get_subtotal_tax() / $quantity );
						} else {
							$subtotal = 0;
							$total    = 0;
						}

						break;
					}
				}
			}

			if ( 'true' !== $include_unpaid_tickets && 'Unpaid' === $woocommerce_events_status ) {

				continue;

			}

			$woocommerce_events_variations = get_post_meta( $id, 'WooCommerceEventsVariations', true );
			if ( ! empty( $woocommerce_events_variations ) && ! is_array( $woocommerce_events_variations ) ) {

				$woocommerce_events_variations = json_decode( $woocommerce_events_variations );

			}

			$variation_output = '';
			$i                = 0;
			if ( ! empty( $woocommerce_events_variations ) ) {
				foreach ( $woocommerce_events_variations as $variation_name => $variation_value ) {

					if ( $i > 0 ) {

						$variation_output .= ' | ';

					}

					$variation_name_output = str_replace( 'attribute_', '', $variation_name );
					$variation_name_output = str_replace( 'pa_', '', $variation_name_output );
					$variation_name_output = str_replace( '_', ' ', $variation_name_output );
					$variation_name_output = str_replace( '-', ' ', $variation_name_output );
					$variation_name_output = str_replace( '', ' ', $variation_name_output );
					$variation_name_output = str_replace( 'Pa_', '', $variation_name_output );
					$variation_name_output = ucwords( $variation_name_output );

					$variation_value_output = str_replace( '_', ' ', $variation_value );
					$variation_value_output = str_replace( '-', ' ', $variation_value_output );

					$variation_value_output = str_replace( ',', '', $variation_value_output );

					$variation_value_output = ucwords( $variation_value_output );

					$variation_output .= $variation_name_output . ': ' . $variation_value_output;

					++$i;
				}
			}

			$order = wc_get_order( $order_id );

			$woocommerce_events_attendee_name = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeName', true );
			if ( empty( $woocommerce_events_attendee_name ) ) {

				if ( false !== $order ) {

					$woocommerce_events_attendee_name = $order->get_billing_first_name();

				} else {

					$woocommerce_events_attendee_name = '';

				}
			}

			$woocommerce_events_attendee_last_name = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeLastName', true );
			if ( empty( $woocommerce_events_attendee_last_name ) ) {

				if ( false !== $order ) {

					$woocommerce_events_attendee_last_name = $order->get_billing_last_name();

				} else {

					$woocommerce_events_attendee_last_name = '';

				}
			}

			$woocommerce_events_attendee_email = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeEmail', true );
			if ( empty( $woocommerce_events_attendee_email ) ) {

				if ( false !== $order ) {

					$woocommerce_events_attendee_email = $order->get_billing_email();

				} else {

					$woocommerce_events_attendee_email = '';

				}
			}

			$woocommerce_events_purchaser_phone = get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserPhone', true );
			if ( empty( $woocommerce_events_purchaser_phone ) ) {

				if ( false !== $order ) {

					$woocommerce_events_purchaser_phone = $order->get_billing_phone();

				} else {

					$woocommerce_events_purchaser_phone = '';

				}
			}

			$order_status = '';
			if ( false !== $order ) {

				$order_status = $order->get_status();

			} else {

				$order_status = '';

			}

			$woocommerce_events_capture_attendee_telephone   = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeTelephone', true );
			$woocommerce_events_capture_attendee_company     = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeCompany', true );
			$woocommerce_events_capture_attendee_designation = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeDesignation', true );
			$woocommerce_events_purchaser_first_name         = get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserFirstName', true );
			$woocommerce_events_purchaser_last_name          = get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserLastName', true );
			$woocommerce_events_purchaser_email              = get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserEmail', true );

			$customer_note = '';
			if ( false !== $order ) {

				$customer_note = $order->get_customer_note();

			}

			$payment_method = '';
			if ( false !== $order ) {

				$payment_method = $order->get_payment_method();

			}

			$order_date = '';
			if ( false !== $order ) {

				$order_date = $order->get_date_created();

			}

			$sorted_rows[ $x ][ $ticket_id_label ]            = $ticket_id;
			$sorted_rows[ $x ][ $ticket_id_numeric_label ]    = $ticket_id_numeric;
			$sorted_rows[ $x ][ $order_id_label ]             = $order_id;
			$sorted_rows[ $x ][ $attendee_first_name_label ]  = $woocommerce_events_attendee_name;
			$sorted_rows[ $x ][ $attendee_last_name_label ]   = $woocommerce_events_attendee_last_name;
			$sorted_rows[ $x ][ $attendee_email_label ]       = $woocommerce_events_attendee_email;
			$sorted_rows[ $x ][ $ticket_status_label ]        = $woocommerce_events_status;
			$sorted_rows[ $x ][ $ticket_type_label ]          = $ticket_type;
			$sorted_rows[ $x ][ $ticket_variation_label ]     = $variation_output;
			$sorted_rows[ $x ][ $attendee_telephone_label ]   = $woocommerce_events_capture_attendee_telephone;
			$sorted_rows[ $x ][ $attendee_company_label ]     = $woocommerce_events_capture_attendee_company;
			$sorted_rows[ $x ][ $attendee_designation_label ] = $woocommerce_events_capture_attendee_designation;
			$sorted_rows[ $x ][ $purchaser_first_name_label ] = $woocommerce_events_purchaser_first_name;
			$sorted_rows[ $x ][ $purchaser_last_name_label ]  = $woocommerce_events_purchaser_last_name;
			$sorted_rows[ $x ][ $purchaser_email_label ]      = $woocommerce_events_purchaser_email;
			$sorted_rows[ $x ][ $purchaser_phone_label ]      = $woocommerce_events_purchaser_phone;
			$sorted_rows[ $x ][ $customer_note_label ]        = $customer_note;
			$sorted_rows[ $x ][ $order_status_label ]         = ucfirst( $order_status );
			$sorted_rows[ $x ][ $payment_method_label ]       = $payment_method;
			$sorted_rows[ $x ][ $order_date_label ]           = $order_date;
			$sorted_rows[ $x ][ $ticket_price_label ]         = $subtotal;
			$sorted_rows[ $x ][ $ticket_price_paid_label ]    = $total;

			if ( false !== $order ) {

				$sorted_rows[ $x ][ $purchaser_company_label ] = $order->get_billing_company();

			} else {

				$sorted_rows[ $x ][ $purchaser_company_label ] = '';

			}

			if ( ! empty( $exportbillingdetails ) ) {

				if ( false !== $order ) {

					$billing_address_1 = $order->get_billing_address_1();

					$billing_fields   = array(
						$billing_address_1_label   => '',
						$billing_address_2_label   => '',
						$billing_city_label        => '',
						$billing_postal_code_label => '',
						$billing_country_label     => '',
						$billing_state_label       => '',
						$billing_phone_label       => '',
					);
					$billing_headings = array_keys( $billing_fields );

					foreach ( $billing_headings as $value ) {

						if ( ! in_array( $value, $csv_blueprint, true ) ) {

							$csv_blueprint[] = $value;

						}
					}

					$sorted_rows[ $x ][ $billing_address_1_label ]   = $order->get_billing_address_1();
					$sorted_rows[ $x ][ $billing_address_2_label ]   = $order->get_billing_address_2();
					$sorted_rows[ $x ][ $billing_city_label ]        = $order->get_billing_city();
					$sorted_rows[ $x ][ $billing_postal_code_label ] = $order->get_billing_postcode();
					$sorted_rows[ $x ][ $billing_country_label ]     = $order->get_billing_country();
					$sorted_rows[ $x ][ $billing_state_label ]       = $order->get_billing_state();
					$sorted_rows[ $x ][ $billing_phone_label ]       = $order->get_billing_phone();

				}
			}

			if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

				require_once ABSPATH . '/wp-admin/includes/plugin.php';

			}

			if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

				$fooevents_custom_attendee_fields         = new Fooevents_Custom_Attendee_Fields();
				$fooevents_custom_attendee_fields_options = $fooevents_custom_attendee_fields->display_tickets_meta_custom_options_array_csv( $id, $product_id );

				$fooevents_custom_attendee_fields_headings = array_keys( $fooevents_custom_attendee_fields_options );

				foreach ( $fooevents_custom_attendee_fields_headings as $value ) {

					if ( ! in_array( $value, $csv_blueprint, true ) ) {

						$csv_blueprint[] = $value;

					}
				}

				foreach ( $fooevents_custom_attendee_fields_options as $key => $value ) {

					$sorted_rows[ $x ][ $key ] = $value;

				}
			}

			if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

				$fooevents_bookings         = new FooEvents_Bookings();
				$fooevents_bookings_options = $fooevents_bookings->display_bookings_options_array( $id, $product_id );

				$fooevents_bookings_options_headings = array_keys( $fooevents_bookings_options );

				foreach ( $fooevents_bookings_options_headings as $value ) {

					if ( ! in_array( $value, $csv_blueprint, true ) ) {

						$csv_blueprint[] = $value;

					}
				}

				foreach ( $fooevents_bookings_options as $key => $value ) {

					$sorted_rows[ $x ][ $key ] = $value;

				}
			}

			if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

				$fooevents_seating         = new Fooevents_Seating();
				$fooevents_seating_options = $fooevents_seating->display_tickets_meta_seat_options_array( $id );

				$fooevents_seating_headings = array_keys( $fooevents_seating_options );

				foreach ( $fooevents_seating_headings as $value ) {

					if ( ! in_array( $value, $csv_blueprint, true ) ) {

						$csv_blueprint[] = $value;

					}
				}

				foreach ( $fooevents_seating_options as $key => $value ) {

					$sorted_rows[ $x ][ $key ] = $value;

				}
			}

			if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

				$woocommerce_events_num_days = get_post_meta( $product_id, 'WooCommerceEventsNumDays', true );
				$fooevents_multiday_events   = new Fooevents_Multiday_Events();
				$fooevents_multiday_statuses = $fooevents_multiday_events->get_array_of_check_ins( $id, $woocommerce_events_num_days );

				for ( $i = 1; $i <= $woocommerce_events_num_days; $i++ ) {

					$value = sprintf( __( 'Day %s', 'woocommerce-events' ), $i );

					if ( ! in_array( $value, $csv_blueprint, true ) ) {

						$csv_blueprint[] = $value;

					}
				}

				$y = 1;
				foreach ( $fooevents_multiday_statuses as $key => $value ) {

					$day_label                       = sprintf( __( 'Day %s', 'woocommerce-events' ), $y );
					$sorted_rows[ $x ][ $day_label ] = $value;

					++$y;

				}
			}

			$checkins = $this->csv_get_checkins( $event, $ticket->ID );

			if ( ! empty( $checkins ) ) {

				$checkins_headings = array_keys( $checkins );

				foreach ( $checkins_headings as $value ) {

					if ( ! in_array( $value, $csv_blueprint, true ) ) {

						$csv_blueprint[] = $value;

					}
				}

				foreach ( $checkins as $key => $value ) {

					$sorted_rows[ $x ][ $key ] = $value;

				}
			}

			++$x;

		}

		// unpaid tickets.
		if ( $include_unpaid_tickets ) {

			$statuses    = array( 'wc-processing', 'wc-on-hold', 'wc-pending' );
			$order_ids   = array();
			$wpml_active = false;

			if ( is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {

				$languages   = apply_filters( 'wpml_active_languages', null, 'orderby=id&order=desc' );
				$wpml_active = true;

				foreach ( $languages as $code => $language ) {

					$trans_id = apply_filters( 'wpml_object_id', $event, 'product', true, $code );

					$order_ids_returned = $this->get_orders_ids_by_product_id( $trans_id, $statuses );

					if ( ! empty( $order_ids_returned ) ) {

						$order_ids_returned = array_unique( $order_ids_returned );
						foreach ( $order_ids_returned as $oid ) {

							array_push( $order_ids, $oid );

						}
					}
				}
			} else {

				$order_ids = $this->get_orders_ids_by_product_id( $event, $statuses );

			}

			$order_ids = array_unique( $order_ids );

			$x              = 0;
			$unpaid_tickets = array();
			foreach ( $order_ids as $order_id ) {

				$unpaid_order = wc_get_order( $order_id );

				$woocommerce_events_order_tickets = $unpaid_order->get_meta( 'WooCommerceEventsOrderTickets', true );

				if ( ! empty( $woocommerce_events_order_tickets ) ) {
					foreach ( $woocommerce_events_order_tickets as $order => $unpaid_order_tickets ) {

						foreach ( $unpaid_order_tickets as $unpaid_order_ticket ) {

							if ( isset( $_GET['event'] ) && $unpaid_order_ticket['WooCommerceEventsProductID'] === $_GET['event'] || true === $wpml_active ) {

								$unpaid_woocommerce_events_attendee_name = $unpaid_order_ticket['WooCommerceEventsAttendeeName'];
								if ( empty( $unpaid_woocommerce_events_attendee_name ) ) {

									$unpaid_woocommerce_events_attendee_name = $unpaid_order_ticket['WooCommerceEventsPurchaserFirstName'];

								}

								$unpaid_woocommerce_events_attendee_last_name = $unpaid_order_ticket['WooCommerceEventsAttendeeLastName'];
								if ( empty( $unpaid_woocommerce_events_attendee_last_name ) ) {

									$unpaid_woocommerce_events_attendee_last_name = $unpaid_order_ticket['WooCommerceEventsPurchaserLastName'];

								}

								$unpaid_woocommerce_events_attendee_email = $unpaid_order_ticket['WooCommerceEventsAttendeeEmail'];
								if ( empty( $unpaid_woocommerce_events_attendee_email ) ) {

									$unpaid_woocommerce_events_attendee_email = $unpaid_order_ticket['WooCommerceEventsPurchaserEmail'];

								}

								$unpaid_order_woocommerce_events_variations = $unpaid_order_ticket['WooCommerceEventsVariations'];
								if ( ! empty( $unpaid_order_woocommerce_events_variations ) && ! is_array( $unpaid_order_woocommerce_events_variations ) ) {

									$unpaid_order_woocommerce_events_variations = json_decode( $unpaid_order_woocommerce_events_variations );

								}

								$unpaid_variation_output = '';
								$i                       = 0;
								if ( ! empty( $unpaid_order_woocommerce_events_variations ) ) {
									foreach ( $unpaid_order_woocommerce_events_variations as $variation_name => $variation_value ) {

										if ( $i > 0 ) {

											$variation_output .= ' | ';

										}

										$variation_name_output = str_replace( 'attribute_', '', $variation_name );
										$variation_name_output = str_replace( 'pa_', '', $variation_name_output );
										$variation_name_output = str_replace( '_', ' ', $variation_name_output );
										$variation_name_output = str_replace( '-', ' ', $variation_name_output );
										$variation_name_output = str_replace( 'Pa_', '', $variation_name_output );
										$variation_name_output = ucwords( $variation_name_output );

										$variation_value_output = str_replace( '_', ' ', $variation_value );
										$variation_value_output = str_replace( '-', ' ', $variation_value_output );
										$variation_value_output = ucwords( $variation_value_output );

										$unpaid_variation_output .= $variation_name_output . ': ' . $variation_value_output;

										++$i;
									}
								}

								$unpaid_tickets[ $x ][ $ticket_id_label ]            = 'NA';
								$unpaid_tickets[ $x ][ $order_id_label ]             = $unpaid_order_ticket['WooCommerceEventsOrderID'];
								$unpaid_tickets[ $x ][ $attendee_first_name_label ]  = $unpaid_woocommerce_events_attendee_name;
								$unpaid_tickets[ $x ][ $attendee_last_name_label ]   = $unpaid_woocommerce_events_attendee_last_name;
								$unpaid_tickets[ $x ][ $attendee_email_label ]       = $unpaid_woocommerce_events_attendee_email;
								$unpaid_tickets[ $x ][ $ticket_status_label ]        = $unpaid_order_ticket['WooCommerceEventsStatus'];
								$unpaid_tickets[ $x ][ $ticket_type_label ]          = $unpaid_order_ticket['WooCommerceEventsTicketType'];
								$unpaid_tickets[ $x ][ $ticket_variation_label ]     = $unpaid_variation_output;
								$unpaid_tickets[ $x ][ $attendee_telephone_label ]   = $unpaid_order_ticket['WooCommerceEventsAttendeeTelephone'];
								$unpaid_tickets[ $x ][ $attendee_company_label ]     = $unpaid_order_ticket['WooCommerceEventsAttendeeCompany'];
								$unpaid_tickets[ $x ][ $attendee_designation_label ] = $unpaid_order_ticket['WooCommerceEventsAttendeeDesignation'];
								$unpaid_tickets[ $x ][ $purchaser_first_name_label ] = $unpaid_order_ticket['WooCommerceEventsPurchaserFirstName'];
								$unpaid_tickets[ $x ][ $purchaser_last_name_label ]  = $unpaid_order_ticket['WooCommerceEventsPurchaserLastName'];
								$unpaid_tickets[ $x ][ $purchaser_email_label ]      = $unpaid_order_ticket['WooCommerceEventsPurchaserEmail'];
								$unpaid_tickets[ $x ][ $purchaser_phone_label ]      = $unpaid_order->get_billing_phone();
								$unpaid_tickets[ $x ][ $purchaser_company_label ]    = $unpaid_order->get_billing_company();
								$unpaid_tickets[ $x ][ $customer_note_label ]        = $unpaid_order->get_customer_note();
								$unpaid_tickets[ $x ][ $order_status_label ]         = $unpaid_order->get_status();
								$unpaid_tickets[ $x ][ $payment_method_label ]       = $unpaid_order->get_payment_method();
								$unpaid_tickets[ $x ][ $order_date_label ]           = $unpaid_order->get_date_created();

								if ( ! empty( $exportbillingdetails ) ) {

									$unpaid_tickets[ $x ][ $billing_address_1_label ]   = $unpaid_order->get_billing_address_1();
									$unpaid_tickets[ $x ][ $billing_address_2_label ]   = $unpaid_order->get_billing_address_2();
									$unpaid_tickets[ $x ][ $billing_city_label ]        = $unpaid_order->get_billing_city();
									$unpaid_tickets[ $x ][ $billing_postal_code_label ] = $unpaid_order->get_billing_postcode();
									$unpaid_tickets[ $x ][ $billing_country_label ]     = $unpaid_order->get_billing_country();
									$unpaid_tickets[ $x ][ $billing_state_label ]       = $unpaid_order->get_billing_state();
									$unpaid_tickets[ $x ][ $billing_phone_label ]       = $unpaid_order->get_billing_phone();

								}

								if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

									$unpaid_tickets[ $x ]['Bookings Slot'] = $unpaid_order_ticket['WooCommerceEventsBookingOptions']['slot_label'];
									$unpaid_tickets[ $x ]['Bookings Date'] = $unpaid_order_ticket['WooCommerceEventsBookingOptions']['date_label'];

								}

								if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

									if ( ! empty( $unpaid_order_ticket['WooCommerceEventsCustomAttendeeFields'] ) ) {

										/*
										foreach($unpaidOrderTicket['WooCommerceEventsCustomAttendeeFields'] as $unpaidCustomField => $unpaidCustomValue) {

											$unpaidCustomField = strtolower($unpaidCustomField);

											if(!in_array($unpaidCustomField, $csv_blueprint)) {

												$csv_blueprint[] = $unpaidCustomField;

											}

											$unpaidTickets[$x][$unpaidCustomField] = $unpaidCustomValue;

										}*/

										$fooevents_custom_attendee_fields                = new Fooevents_Custom_Attendee_Fields();
										$fooevents_custom_attendee_fields_options_unpaid = $fooevents_custom_attendee_fields->display_tickets_meta_custom_options_array_csv_unpaid( $unpaid_order_ticket['WooCommerceEventsCustomAttendeeFields'], $unpaid_order_ticket['WooCommerceEventsProductID'] );

										$fooevents_custom_attendee_fields_headings_unpaid = array_keys( $fooevents_custom_attendee_fields_options_unpaid );

										foreach ( $fooevents_custom_attendee_fields_headings_unpaid as $value ) {

											if ( ! in_array( $value, $csv_blueprint, true ) ) {

												$csv_blueprint[] = $value;

											}
										}

										foreach ( $fooevents_custom_attendee_fields_options_unpaid as $key => $value ) {

											$unpaid_tickets[ $x ][ $key ] = $value;

										}
									}
								}

								++$x;

							}
						}
					}
				}
			}

			$sorted_rows = array_merge( $sorted_rows, $unpaid_tickets );

		}

		$output = array();

		$y = 0;
		foreach ( $sorted_rows as $item ) {

			foreach ( $item as $key => $valuetest ) {

				foreach ( $csv_blueprint as $heading ) {

					if ( $key === $heading ) {

						$output[ $y ][ $heading ] = $valuetest;

					}
				}

				foreach ( $csv_blueprint as $heading ) {

					if ( empty( $output[ $y ][ $heading ] ) ) {

						$output[ $y ][ $heading ] = '';

					}
				}
			}

			++$y;

		}

		header( 'Content-Encoding: UTF-8' );
		header( 'Content-type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . date( 'Ymdhis' ) . '.csv"' );
		echo "\xEF\xBB\xBF";

		$fp = fopen( 'php://output', 'w' );

		if ( empty( $output ) ) {

			$output[] = array( __( 'No tickets found.', 'woocommerce-events' ) );

		} else {

			fputcsv( $fp, $csv_blueprint );

		}

		foreach ( $output as $fields ) {

			fputcsv( $fp, $fields );

		}

		exit();
	}

	/**
	 * Retrieves a tickets check-in times
	 *
	 * @param int $event_id event ID.
	 * @param int $ticket_id ticket ID.
	 */
	public function csv_get_checkins( $event_id, $ticket_id ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'fooevents_check_in';

		$processed_checkins = array();

		$checkins = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT * FROM $table_name
				WHERE tid = %d
				",
				$ticket_id
			)
		);

		$woocommerce_events_num_days = get_post_meta( $event_id, 'WooCommerceEventsNumDays', true );

		if ( empty( $woocommerce_events_num_days ) ) {

			$woocommerce_events_num_days = 1;

		}

		$day_term = get_post_meta( $event_id, 'WooCommerceEventsDayOverride', true );

		if ( empty( $day_term ) ) {

			$day_term = get_option( 'WooCommerceEventsDayOverride', true );

		}

		if ( empty( $day_term ) || 1 == $day_term ) {

			$day_term = __( 'Day', 'woocommerce-events' );

		}

		$checkin_heading   = __( 'Checked-in', 'woocommerce-events' ) . ' ' . $day_term . ' ';
		$checkout_heading  = __( 'Checked-out', 'woocommerce-events' ) . ' ' . $day_term . ' ';
		$logged_heading    = __( 'Checked-in by', 'woocommerce-events' ) . ' ' . $day_term . ' ';
		$loggedout_heading = __( 'Checked-out by', 'woocommerce-events' ) . ' ' . $day_term . ' ';

		for ( $x = 1; $x <= $woocommerce_events_num_days; $x++ ) {

			$processed_checkins[ $checkin_heading . $x ]  = '';
			$processed_checkins[ $checkout_heading . $x ] = '';

		}

		foreach ( $checkins as $checkin ) {

			$user = get_userdata( $checkin->uid );

			if ( 'Checked In' === $checkin->status ) {

				if ( empty( $processed_checkins[ $checkin_heading . $checkin->day ] ) ) {

					$processed_checkins[ $checkin_heading . $checkin->day ] = $checkin->updated;

				} elseif ( strtotime( $processed_checkins[ $checkin_heading . $checkin->day ] ) > strtotime( $checkin->updated ) ) {

						$processed_checkins[ $checkin_heading . $checkin->day ] = $checkin->updated;
				}

				$processed_checkins[ $logged_heading . $checkin->day ] = $user->user_nicename;

			}

			if ( 'Not Checked In' === $checkin->status ) {

				if ( empty( $processed_checkins[ $checkout_heading . $checkin->day ] ) ) {

					$processed_checkins[ $checkout_heading . $checkin->day ] = $checkin->updated;

				} elseif ( strtotime( $processed_checkins[ $checkout_heading . $checkin->day ] ) < strtotime( $checkin->updated ) ) {

						$processed_checkins[ $checkout_heading . $checkin->day ] = $checkin->updated;
				}

				$processed_checkins[ $loggedout_heading . $checkin->day ] = $user->user_nicename;

			}
		}

		return $processed_checkins;
	}

	/**
	 * Generates attendee badges.
	 */
	public function woocommerce_events_attendee_badges() {

		if ( ! current_user_can( 'publish_event_magic_tickets' ) ) {

			echo 'User role does not have permission to export attendee details. Please contact site admin.';
			exit();

		}

		$event = '';

		if ( isset( $_GET['event'] ) ) {

			$event = sanitize_text_field( wp_unslash( $_GET['event'] ) );

		}

		$ticket = '';

		if ( isset( $_GET['ticket'] ) ) {

			$ticket = sanitize_text_field( wp_unslash( $_GET['ticket'] ) );

		}

		$order = '';

		if ( isset( $_GET['order'] ) ) {

			$order = sanitize_text_field( wp_unslash( $_GET['order'] ) );

		}

		$this->woocommerce_events_attendee_badges_params( $event, $ticket, $order );

		exit();
	}

	/**
	 * Generates attendee badges.
	 *
	 * @param string $event The event ID.
	 * @param string $ticket The ticket ID.
	 * @param string $order The order ID.
	 */
	public function woocommerce_events_attendee_badges_params( $event = '', $ticket = '', $order = '' ) {

		global $woocommerce;

		$sort = get_post_meta( $event, 'WooCommercePrintTicketSort', true );

		$ticketnrs = array();

		if ( '' !== $ticket ) {

			$ticketnrs = explode( ',', $ticket );

		} elseif ( get_post_meta( $event, 'WooCommercePrintTicketNumbers', true ) !== '' ) {

			$ticketnrs = explode( ',', get_post_meta( $event, 'WooCommercePrintTicketNumbers', true ) );

		}

		$ordernrs = array();

		if ( '' !== $order ) {

			$ordernrs = explode( ',', $order );

		} elseif ( get_post_meta( $event, 'WooCommercePrintTicketOrders', true ) !== '' ) {

			$ordernrs = explode( ',', get_post_meta( $event, 'WooCommercePrintTicketOrders', true ) );

		}

		$page_size = 'fooevents_letter_10';

		if ( ! empty( get_post_meta( $event, 'WooCommercePrintTicketSize', true ) ) ) {

			$page_size = 'fooevents_' . get_post_meta( $event, 'WooCommercePrintTicketSize', true );

		}

		$cutlines = 'on';

		if ( get_post_meta( $event, 'WooCommerceEventsCutLinesPrintTicket', true ) !== '' ) {

			$cutlines = get_post_meta( $event, 'WooCommerceEventsCutLinesPrintTicket', true );

		}

		$bgimg = '';

		if ( get_post_meta( $event, 'WooCommerceEventsTicketBackgroundImage', true ) !== '' ) {

			$bgimg = get_post_meta( $event, 'WooCommerceEventsTicketBackgroundImage', true );

		}

		$nr_per_page = substr( $page_size, strrpos( $page_size, '_' ) + 1 );
		$nrcol       = get_post_meta( $event, 'WooCommercePrintTicketNrColumns', true );
		$nrrow       = get_post_meta( $event, 'WooCommercePrintTicketNrRows', true );

		$logo1  = get_post_meta( $event, 'WooCommerceBadgeFieldTopLeft_logo', true );
		$logo2  = get_post_meta( $event, 'WooCommerceBadgeFieldMiddleLeft_logo', true );
		$logo3  = get_post_meta( $event, 'WooCommerceBadgeFieldBottomLeft_logo', true );
		$logo4  = get_post_meta( $event, 'WooCommerceBadgeField_d_1_logo', true );
		$logo5  = get_post_meta( $event, 'WooCommerceBadgeFieldTopMiddle_logo', true );
		$logo6  = get_post_meta( $event, 'WooCommerceBadgeFieldMiddleMiddle_logo', true );
		$logo7  = get_post_meta( $event, 'WooCommerceBadgeFieldBottomMiddle_logo', true );
		$logo8  = get_post_meta( $event, 'WooCommerceBadgeField_d_2_logo', true );
		$logo9  = get_post_meta( $event, 'WooCommerceBadgeFieldTopRight_logo', true );
		$logo10 = get_post_meta( $event, 'WooCommerceBadgeFieldMiddleRight_logo', true );
		$logo11 = get_post_meta( $event, 'WooCommerceBadgeFieldBottomRight_logo', true );
		$logo12 = get_post_meta( $event, 'WooCommerceBadgeField_d_3_logo', true );
		$logo13 = get_post_meta( $event, 'WooCommerceBadgeField_a_4_logo', true );
		$logo14 = get_post_meta( $event, 'WooCommerceBadgeField_b_4_logo', true );
		$logo15 = get_post_meta( $event, 'WooCommerceBadgeField_c_4_logo', true );
		$logo16 = get_post_meta( $event, 'WooCommerceBadgeField_d_4_logo', true );

		$font1  = get_post_meta( $event, 'WooCommerceBadgeFieldTopLeft_font', true );
		$font2  = get_post_meta( $event, 'WooCommerceBadgeFieldMiddleLeft_font', true );
		$font3  = get_post_meta( $event, 'WooCommerceBadgeFieldBottomLeft_font', true );
		$font4  = get_post_meta( $event, 'WooCommerceBadgeField_d_1_font', true );
		$font5  = get_post_meta( $event, 'WooCommerceBadgeFieldTopMiddle_font', true );
		$font6  = get_post_meta( $event, 'WooCommerceBadgeFieldMiddleMiddle_font', true );
		$font7  = get_post_meta( $event, 'WooCommerceBadgeFieldBottomMiddle_font', true );
		$font8  = get_post_meta( $event, 'WooCommerceBadgeField_d_2_font', true );
		$font9  = get_post_meta( $event, 'WooCommerceBadgeFieldTopRight_font', true );
		$font10 = get_post_meta( $event, 'WooCommerceBadgeFieldMiddleRight_font', true );
		$font11 = get_post_meta( $event, 'WooCommerceBadgeFieldBottomRight_font', true );
		$font12 = get_post_meta( $event, 'WooCommerceBadgeField_d_3_font', true );
		$font13 = get_post_meta( $event, 'WooCommerceBadgeField_a_4_font', true );
		$font14 = get_post_meta( $event, 'WooCommerceBadgeField_b_4_font', true );
		$font15 = get_post_meta( $event, 'WooCommerceBadgeField_c_4_font', true );
		$font16 = get_post_meta( $event, 'WooCommerceBadgeField_d_4_font', true );

		$seat_label1  = get_post_meta( $event, 'WooCommerceBadgeFieldTopLeft_seat_label', true );
		$seat_label2  = get_post_meta( $event, 'WooCommerceBadgeFieldMiddleLeft_seat_label', true );
		$seat_label3  = get_post_meta( $event, 'WooCommerceBadgeFieldBottomLeft_seat_label', true );
		$seat_label4  = get_post_meta( $event, 'WooCommerceBadgeField_d_1_seat_label', true );
		$seat_label5  = get_post_meta( $event, 'WooCommerceBadgeFieldTopMiddle_seat_label', true );
		$seat_label6  = get_post_meta( $event, 'WooCommerceBadgeFieldMiddleMiddle_seat_label', true );
		$seat_label7  = get_post_meta( $event, 'WooCommerceBadgeFieldBottomMiddle_seat_label', true );
		$seat_label8  = get_post_meta( $event, 'WooCommerceBadgeField_d_2_seat_label', true );
		$seat_label9  = get_post_meta( $event, 'WooCommerceBadgeFieldTopRight_seat_label', true );
		$seat_label10 = get_post_meta( $event, 'WooCommerceBadgeFieldMiddleRight_seat_label', true );
		$seat_label11 = get_post_meta( $event, 'WooCommerceBadgeFieldBottomRight_seat_label', true );
		$seat_label12 = get_post_meta( $event, 'WooCommerceBadgeField_d_3_seat_label', true );
		$seat_label13 = get_post_meta( $event, 'WooCommerceBadgeField_a_4_seat_label', true );
		$seat_label14 = get_post_meta( $event, 'WooCommerceBadgeField_b_4_seat_label', true );
		$seat_label15 = get_post_meta( $event, 'WooCommerceBadgeField_c_4_seat_label', true );
		$seat_label16 = get_post_meta( $event, 'WooCommerceBadgeField_d_4_seat_label', true );

		$ticketfield1  = get_post_meta( $event, 'WooCommerceBadgeFieldTopLeft', true );
		$ticketfield2  = get_post_meta( $event, 'WooCommerceBadgeFieldMiddleLeft', true );
		$ticketfield3  = get_post_meta( $event, 'WooCommerceBadgeFieldBottomLeft', true );
		$ticketfield4  = get_post_meta( $event, 'WooCommerceBadgeField_d_1', true );
		$ticketfield5  = get_post_meta( $event, 'WooCommerceBadgeFieldTopMiddle', true );
		$ticketfield6  = get_post_meta( $event, 'WooCommerceBadgeFieldMiddleMiddle', true );
		$ticketfield7  = get_post_meta( $event, 'WooCommerceBadgeFieldBottomMiddle', true );
		$ticketfield8  = get_post_meta( $event, 'WooCommerceBadgeField_d_2', true );
		$ticketfield9  = get_post_meta( $event, 'WooCommerceBadgeFieldTopRight', true );
		$ticketfield10 = get_post_meta( $event, 'WooCommerceBadgeFieldMiddleRight', true );
		$ticketfield11 = get_post_meta( $event, 'WooCommerceBadgeFieldBottomRight', true );
		$ticketfield12 = get_post_meta( $event, 'WooCommerceBadgeField_d_3', true );
		$ticketfield13 = get_post_meta( $event, 'WooCommerceBadgeField_a_4', true );
		$ticketfield14 = get_post_meta( $event, 'WooCommerceBadgeField_b_4', true );
		$ticketfield15 = get_post_meta( $event, 'WooCommerceBadgeField_c_4', true );
		$ticketfield16 = get_post_meta( $event, 'WooCommerceBadgeField_d_4', true );

		$seat_text = get_post_meta( $event, 'WooCommerceEventsSeatingSeatOverride', true );

		if ( '' === $seat_text ) {
			$seat_text = __( 'Seat', 'woocommerce-events' );
		}

		$row_text = get_post_meta( $event, 'WooCommerceEventsSeatingRowOverride', true );

		if ( '' === $row_text ) {
			$row_text = __( 'Row', 'woocommerce-events' );
		}

		$postids = array();

		/* Get tickets by ticket number */
		if ( ! empty( $ticketnrs ) ) {

			$events_query = new WP_Query(
				array(
					'post_type'      => array( 'event_magic_tickets' ),
					'posts_per_page' => -1,
					'meta_key'       => 'WooCommerceEventsTicketID',
					'meta_value'     => $ticketnrs,
				)
			);
			$ids          = $events_query->get_posts();

			foreach ( $ids as $id ) {

				array_push( $postids, $id->ID );

			}
		}

		/* Get tickets by order number */
		if ( ! empty( $ordernrs ) ) {

			$events_query_orders = new WP_Query(
				array(
					'post_type'      => array( 'event_magic_tickets' ),
					'posts_per_page' => -1,
					'meta_key'       => 'WooCommerceEventsOrderID',
					'meta_value'     => $ordernrs,
				)
			);
			$ids_orders          = $events_query_orders->get_posts();

			foreach ( $ids_orders as $id ) {

				array_push( $postids, $id->ID );

			}
		}

		switch ( $sort ) {
			case 'most_recent':
				$sort = 'DESC';
				break;
			case 'oldest':
				$sort = 'ASC';
				break;
			case 'a_z1':
				$sort = 'WooCommerceEventsAttendeeName';
				break;
			case 'a_z2':
				$sort = 'WooCommerceEventsAttendeeLastName';
				break;
		}

		$sorted_rows = array();

		if ( ! empty( $postids ) ) {

			$events_query = new WP_Query(
				array(
					'post_type'      => array( 'event_magic_tickets' ),
					'post__in'       => $postids,
					'posts_per_page' => -1,
					'meta_key'       => 'WooCommerceEventsProductID',
					'meta_value'     => $event,
				)
			);

		} elseif ( 'WooCommerceEventsAttendeeName' === $sort || 'WooCommerceEventsAttendeeLastName' === $sort ) {

				$events_query = new WP_Query(
					array(
						'post_type'      => array( 'event_magic_tickets' ),
						'posts_per_page' => -1,
						'meta_key'       => $sort,
						'order'          => 'ASC',
						'orderby'        => 'meta_value',
						'meta_query'     =>
						array(
							'key'   => 'WooCommerceEventsProductID',
							'value' => $event,
						),
					)
				);

		} else {

			$events_query = new WP_Query(
				array(
					'post_type'      => array( 'event_magic_tickets' ),
					'posts_per_page' => -1,
					'meta_key'       => 'WooCommerceEventsProductID',
					'meta_value'     => $event,
					'order'          => $sort,
				)
			);
		}

		$events = $events_query->get_posts();

		if ( empty( $events ) ) {

			echo 'There are no attendees/tickets for this event.';

		}

		$x = 0;

		$location_name = get_post_meta( $event, 'WooCommerceEventsLocation', true );

		$current_logo_url = get_post_meta( $event, 'WooCommerceEventsTicketLogo', true );
		$new_logo_url     = get_post_meta( $event, 'WooCommerceEventsPrintTicketLogo', true );

		foreach ( $events as $event_item ) {

			$id               = $event_item->ID;
			$event_id         = get_post_meta( $id, 'WooCommerceEventsProductID', true );
			$ticket           = get_post( $id );
			$ticket_id        = $ticket->post_title;
			$custom_ticket_id = get_post_meta( $id, 'WooCommerceEventsTicketNumberFormatted', true );

			$woocommerce_events_ticket_hash = get_post_meta( $id, 'WooCommerceEventsTicketHash', true );
			$order_id                       = get_post_meta( $id, 'WooCommerceEventsOrderID', true );
			$product_id                     = get_post_meta( $id, 'WooCommerceEventsProductID', true );
			$event_name                     = get_post_meta( $id, 'WooCommerceEventsProductName', true );
			$date                           = get_post_meta( $event_id, 'WooCommerceEventsDate', true );
			$customer_id                    = get_post_meta( $id, 'WooCommerceEventsCustomerID', true );
			$woocommerce_events_status      = get_post_meta( $id, 'WooCommerceEventsStatus', true );
			$ticket_type                    = get_post_meta( $ticket->ID, 'WooCommerceEventsTicketType', true );
			$woocommerce_events_variations  = get_post_meta( $id, 'WooCommerceEventsVariations', true );

			if ( ! empty( $woocommerce_events_variations ) && ! is_array( $woocommerce_events_variations ) ) {

				$woocommerce_events_variations = json_decode( $woocommerce_events_variations );

			}

			$variation_output = '';
			$i                = 0;

			if ( ! empty( $woocommerce_events_variations ) ) {

				foreach ( $woocommerce_events_variations as $variation_name => $variation_value ) {

					if ( $i > 0 ) {

						$variation_output .= ' | ';

					}

					$variation_name_output  = str_replace( 'attribute_', '', $variation_name );
					$variation_name_output  = str_replace( 'pa_', '', $variation_name_output );
					$variation_name_output  = str_replace( '_', ' ', $variation_name_output );
					$variation_name_output  = str_replace( '-', ' ', $variation_name_output );
					$variation_name_output  = str_replace( 'Pa_', '', $variation_name_output );
					$variation_name_output  = ucwords( $variation_name_output );
					$variation_value_output = str_replace( '_', ' ', $variation_value );
					$variation_value_output = str_replace( '-', ' ', $variation_value_output );
					$variation_value_output = ucwords( $variation_value_output );
					$variation_output      .= $variation_name_output . ': ' . $variation_value_output;
					++$i;

				}
			}

			$order = wc_get_order( $order_id );

			$woocommerce_events_attendee_name = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeName', true );

			if ( empty( $woocommerce_events_attendee_name ) && false !== $order ) {

				$woocommerce_events_attendee_name = $order->get_billing_first_name();

			}

			$woocommerce_events_attendee_last_name = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeLastName', true );

			if ( empty( $woocommerce_events_attendee_last_name ) && false !== $order ) {

				$woocommerce_events_attendee_last_name = $order->get_billing_last_name();

			}

			$woocommerce_events_attendee_email = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeEmail', true );

			if ( empty( $woocommerce_events_attendee_email ) && false !== $order ) {

				$woocommerce_events_attendee_email = $order->get_billing_email();

			}

			$woocommerce_events_capture_attendee_telephone   = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeTelephone', true );
			$woocommerce_events_capture_attendee_company     = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeCompany', true );
			$woocommerce_events_capture_attendee_designation = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeDesignation', true );
			$woocommerce_events_purchaser_first_name         = get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserFirstName', true );
			$woocommerce_events_purchaser_last_name          = get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserLastName', true );
			$woocommerce_events_purchaser_email              = get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserEmail', true );
			$woocommerce_events_purchaser_phone              = get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserPhone', true );

			$sorted_rows[ $x ]['TicketHash']            = $woocommerce_events_ticket_hash;
			$sorted_rows[ $x ]['TicketID']              = $ticket_id;
			$sorted_rows[ $x ]['CustomTicketID']        = $custom_ticket_id;
			$sorted_rows[ $x ]['OrderID']               = $order_id;
			$sorted_rows[ $x ]['Event Name']            = $event_name;
			$sorted_rows[ $x ]['Event Date']            = $date;
			$sorted_rows[ $x ]['Location']              = $location_name;
			$sorted_rows[ $x ]['Event Name Variations'] = $event_name . ' (' . $variation_output . ')';
			$sorted_rows[ $x ]['Attendee First Name']   = $woocommerce_events_attendee_name;
			$sorted_rows[ $x ]['Attendee Last Name']    = $woocommerce_events_attendee_last_name;
			$sorted_rows[ $x ]['Attendee Email']        = $woocommerce_events_attendee_email;
			$sorted_rows[ $x ]['Ticket Status']         = $woocommerce_events_status;
			$sorted_rows[ $x ]['Ticket Type']           = $ticket_type;
			$sorted_rows[ $x ]['Variation']             = $variation_output;
			$sorted_rows[ $x ]['Attendee Telephone']    = $woocommerce_events_capture_attendee_telephone;
			$sorted_rows[ $x ]['Attendee Company']      = $woocommerce_events_capture_attendee_company;
			$sorted_rows[ $x ]['Attendee Designation']  = $woocommerce_events_capture_attendee_designation;
			$sorted_rows[ $x ]['Purchaser First Name']  = $woocommerce_events_purchaser_first_name;
			$sorted_rows[ $x ]['Purchaser Last Name']   = $woocommerce_events_purchaser_last_name;
			$sorted_rows[ $x ]['Purchaser Email']       = $woocommerce_events_purchaser_email;
			$sorted_rows[ $x ]['Purchaser Phone']       = $woocommerce_events_purchaser_phone;
			$sorted_rows[ $x ]['Purchaser Company']     = false !== $order ? $order->get_billing_company() : '';

			$custom1  = get_post_meta( $event, 'WooCommerceBadgeFieldTopLeft_custom', true );
			$custom2  = get_post_meta( $event, 'WooCommerceBadgeFieldMiddleLeft_custom', true );
			$custom3  = get_post_meta( $event, 'WooCommerceBadgeFieldBottomLeft_custom', true );
			$custom4  = get_post_meta( $event, 'WooCommerceBadgeField_d_1_custom', true );
			$custom5  = get_post_meta( $event, 'WooCommerceBadgeFieldTopMiddle_custom', true );
			$custom6  = get_post_meta( $event, 'WooCommerceBadgeFieldMiddleMiddle_custom', true );
			$custom7  = get_post_meta( $event, 'WooCommerceBadgeFieldBottomMiddle_custom', true );
			$custom8  = get_post_meta( $event, 'WooCommerceBadgeField_d_2_custom', true );
			$custom9  = get_post_meta( $event, 'WooCommerceBadgeFieldTopRight_custom', true );
			$custom10 = get_post_meta( $event, 'WooCommerceBadgeFieldMiddleRight_custom', true );
			$custom11 = get_post_meta( $event, 'WooCommerceBadgeFieldBottomRight_custom', true );
			$custom12 = get_post_meta( $event, 'WooCommerceBadgeField_d_3_custom', true );
			$custom13 = get_post_meta( $event, 'WooCommerceBadgeField_a_4_custom', true );
			$custom14 = get_post_meta( $event, 'WooCommerceBadgeField_b_4_custom', true );
			$custom15 = get_post_meta( $event, 'WooCommerceBadgeField_c_4_custom', true );
			$custom16 = get_post_meta( $event, 'WooCommerceBadgeField_d_4_custom', true );

			if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

				require_once ABSPATH . '/wp-admin/includes/plugin.php';

			}

			if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

				$fooevents_custom_attendee_fields         = new Fooevents_Custom_Attendee_Fields();
				$fooevents_custom_attendee_fields_options = $fooevents_custom_attendee_fields->display_tickets_meta_custom_options_array( $id );
				$fooevents_custom_attendee_fields_label   = $fooevents_custom_attendee_fields->display_tickets_meta_custom_options_array( $event );
				$custom_fields_label                      = '';

				if ( ! empty( $fooevents_custom_attendee_fields_label['fooevents_custom_attendee_fields_options_serialized'] ) ) {

					$custom_fields_label = json_decode( $fooevents_custom_attendee_fields_label['fooevents_custom_attendee_fields_options_serialized'], true );

				}

				foreach ( $fooevents_custom_attendee_fields_options as $key => $value ) {

					$cf_code = substr( $key, strrpos( $key, '_' ) + 1 );

					$cf_label = '';

					if ( isset( $custom_fields_label[ $cf_code ] ) && isset( $custom_fields_label[ $cf_code ][ $cf_code . '_label' ] ) ) {
						$cf_label = str_replace( ' ', '_', strtolower( $custom_fields_label[ $cf_code ][ $cf_code . '_label' ] ) );
					}

					$custom1  = str_replace( '{' . $cf_code . '}', $value, $custom1 );
					$custom2  = str_replace( '{' . $cf_code . '}', $value, $custom2 );
					$custom3  = str_replace( '{' . $cf_code . '}', $value, $custom3 );
					$custom4  = str_replace( '{' . $cf_code . '}', $value, $custom4 );
					$custom5  = str_replace( '{' . $cf_code . '}', $value, $custom5 );
					$custom6  = str_replace( '{' . $cf_code . '}', $value, $custom6 );
					$custom7  = str_replace( '{' . $cf_code . '}', $value, $custom7 );
					$custom8  = str_replace( '{' . $cf_code . '}', $value, $custom8 );
					$custom9  = str_replace( '{' . $cf_code . '}', $value, $custom9 );
					$custom10 = str_replace( '{' . $cf_code . '}', $value, $custom10 );
					$custom11 = str_replace( '{' . $cf_code . '}', $value, $custom11 );
					$custom12 = str_replace( '{' . $cf_code . '}', $value, $custom12 );
					$custom13 = str_replace( '{' . $cf_code . '}', $value, $custom13 );
					$custom14 = str_replace( '{' . $cf_code . '}', $value, $custom14 );
					$custom15 = str_replace( '{' . $cf_code . '}', $value, $custom15 );
					$custom16 = str_replace( '{' . $cf_code . '}', $value, $custom16 );

					$sorted_rows[ $x ][ $key ] = $cf_label . ':' . $value;

				}
			}

			$custom1  = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom1 );
			$custom2  = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom2 );
			$custom3  = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom3 );
			$custom4  = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom4 );
			$custom5  = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom5 );
			$custom6  = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom6 );
			$custom7  = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom7 );
			$custom8  = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom8 );
			$custom9  = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom9 );
			$custom10 = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom10 );
			$custom11 = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom11 );
			$custom12 = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom12 );
			$custom13 = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom13 );
			$custom14 = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom14 );
			$custom15 = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom15 );
			$custom16 = str_replace( '{attendeeName}', $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name, $custom16 );

			$custom1  = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom1 );
			$custom2  = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom2 );
			$custom3  = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom3 );
			$custom4  = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom4 );
			$custom5  = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom5 );
			$custom6  = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom6 );
			$custom7  = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom7 );
			$custom8  = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom8 );
			$custom9  = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom9 );
			$custom10 = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom10 );
			$custom11 = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom11 );
			$custom12 = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom12 );
			$custom13 = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom13 );
			$custom14 = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom14 );
			$custom15 = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom15 );
			$custom16 = str_replace( '{attendeeFirstName}', $woocommerce_events_attendee_name, $custom16 );

			$custom1  = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom1 );
			$custom2  = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom2 );
			$custom3  = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom3 );
			$custom4  = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom4 );
			$custom5  = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom5 );
			$custom6  = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom6 );
			$custom7  = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom7 );
			$custom8  = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom8 );
			$custom9  = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom9 );
			$custom10 = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom10 );
			$custom11 = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom11 );
			$custom12 = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom12 );
			$custom13 = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom13 );
			$custom14 = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom14 );
			$custom15 = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom15 );
			$custom16 = str_replace( '{attendeeLastName}', $woocommerce_events_attendee_last_name, $custom16 );

			$ticket_nr = get_post_meta( $ticket->ID, 'WooCommerceEventsTicketID', true );

			$custom1  = str_replace( '{ticketID}', $ticket_nr, $custom1 );
			$custom2  = str_replace( '{ticketID}', $ticket_nr, $custom2 );
			$custom3  = str_replace( '{ticketID}', $ticket_nr, $custom3 );
			$custom4  = str_replace( '{ticketID}', $ticket_nr, $custom4 );
			$custom5  = str_replace( '{ticketID}', $ticket_nr, $custom5 );
			$custom6  = str_replace( '{ticketID}', $ticket_nr, $custom6 );
			$custom7  = str_replace( '{ticketID}', $ticket_nr, $custom7 );
			$custom8  = str_replace( '{ticketID}', $ticket_nr, $custom8 );
			$custom9  = str_replace( '{ticketID}', $ticket_nr, $custom9 );
			$custom10 = str_replace( '{ticketID}', $ticket_nr, $custom10 );
			$custom11 = str_replace( '{ticketID}', $ticket_nr, $custom11 );
			$custom12 = str_replace( '{ticketID}', $ticket_nr, $custom12 );
			$custom13 = str_replace( '{ticketID}', $ticket_nr, $custom13 );
			$custom14 = str_replace( '{ticketID}', $ticket_nr, $custom14 );
			$custom15 = str_replace( '{ticketID}', $ticket_nr, $custom15 );
			$custom16 = str_replace( '{ticketID}', $ticket_nr, $custom16 );

			$custom1  = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom1 );
			$custom2  = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom2 );
			$custom3  = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom3 );
			$custom4  = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom4 );
			$custom5  = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom5 );
			$custom6  = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom6 );
			$custom7  = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom7 );
			$custom8  = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom8 );
			$custom9  = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom9 );
			$custom10 = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom10 );
			$custom11 = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom11 );
			$custom12 = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom12 );
			$custom13 = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom13 );
			$custom14 = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom14 );
			$custom15 = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom15 );
			$custom16 = str_replace( '{barcode}', "<img src='" . esc_url( $this->config->barcode_url ) . $woocommerce_events_ticket_hash . '-' . str_replace( '#', '', $ticket_id ) . ".png' />", $custom16 );

			/* Remove any shortcodes that haven't been replaced e.g. if a ticket doesn't have a custom attendee field */
			$custom1  = preg_replace( '/\{[^}]*\}/', '', $custom1 );
			$custom2  = preg_replace( '/\{[^}]*\}/', '', $custom2 );
			$custom3  = preg_replace( '/\{[^}]*\}/', '', $custom3 );
			$custom4  = preg_replace( '/\{[^}]*\}/', '', $custom4 );
			$custom5  = preg_replace( '/\{[^}]*\}/', '', $custom5 );
			$custom6  = preg_replace( '/\{[^}]*\}/', '', $custom6 );
			$custom7  = preg_replace( '/\{[^}]*\}/', '', $custom7 );
			$custom8  = preg_replace( '/\{[^}]*\}/', '', $custom8 );
			$custom9  = preg_replace( '/\{[^}]*\}/', '', $custom9 );
			$custom10 = preg_replace( '/\{[^}]*\}/', '', $custom10 );
			$custom11 = preg_replace( '/\{[^}]*\}/', '', $custom11 );
			$custom12 = preg_replace( '/\{[^}]*\}/', '', $custom12 );
			$custom13 = preg_replace( '/\{[^}]*\}/', '', $custom13 );
			$custom14 = preg_replace( '/\{[^}]*\}/', '', $custom14 );
			$custom15 = preg_replace( '/\{[^}]*\}/', '', $custom15 );
			$custom16 = preg_replace( '/\{[^}]*\}/', '', $custom16 );

			$sorted_rows[ $x ]['Custom1']  = $custom1;
			$sorted_rows[ $x ]['Custom2']  = $custom2;
			$sorted_rows[ $x ]['Custom3']  = $custom3;
			$sorted_rows[ $x ]['Custom4']  = $custom4;
			$sorted_rows[ $x ]['Custom5']  = $custom5;
			$sorted_rows[ $x ]['Custom6']  = $custom6;
			$sorted_rows[ $x ]['Custom7']  = $custom7;
			$sorted_rows[ $x ]['Custom8']  = $custom8;
			$sorted_rows[ $x ]['Custom9']  = $custom9;
			$sorted_rows[ $x ]['Custom10'] = $custom10;
			$sorted_rows[ $x ]['Custom11'] = $custom11;
			$sorted_rows[ $x ]['Custom12'] = $custom12;
			$sorted_rows[ $x ]['Custom13'] = $custom13;
			$sorted_rows[ $x ]['Custom14'] = $custom14;
			$sorted_rows[ $x ]['Custom15'] = $custom15;
			$sorted_rows[ $x ]['Custom16'] = $custom16;

			if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

				$fooevents_seating         = new Fooevents_Seating();
				$fooevents_seating_options = $fooevents_seating->display_tickets_meta_seat_options_output( $id );
				$fooevents_seating_info    = array();

				if ( is_array( $fooevents_seating_options ) ) {

					for ( $i = 0; $i < 16; $i++ ) {

						switch ( ${'seat_label' . $i + 1} ) {
							case 'seat_label_first':
								$fooevents_seating_info[ $i ] = '<b>' . esc_html( $seat_text ) . '</b>' . $fooevents_seating_options['row_name'] . ', ' . $fooevents_seating_options['seat_number'];
								break;
							case 'no_row_seat_label':
								$fooevents_seating_info[ $i ] = $fooevents_seating_options['row_name'] . ' ' . '<b>' . esc_html( $seat_text ) . '</b>' . $fooevents_seating_options['seat_number'];
								break;
							case 'row_label_seat_label':
								$fooevents_seating_info[ $i ] = '<b>' . esc_html( $row_text ) . '</b>' . $fooevents_seating_options['row_name'] . ' <b>' . esc_html( $seat_text ) . '</b>' . $fooevents_seating_options['seat_number'];
								break;
							case 'no_label':
								$fooevents_seating_info[ $i ] = $fooevents_seating_options['row_name'] . ', ' . $fooevents_seating_options['seat_number'];
								break;
							default:
								$fooevents_seating_info[ $i ] = '<b>' . esc_html( $seat_text ) . '</b>' . $fooevents_seating_options['row_name'] . ', ' . $fooevents_seating_options['seat_number'];
								break;
						}
					}
				}

				$sorted_rows[ $x ]['SeatInfo'] = $fooevents_seating_info;

			}

			++$x;

		}

		$output = array();

		$y = 0;

		require $this->config->template_path . 'product-event-printing-attendee-badges.php';
	}

	/**
	 * Returns the widget label for the ticket print designer
	 *
	 * @param string $data_name data name.
	 * @param array  $cf_array cf_array.
	 */
	public function widget_label( $data_name, $cf_array ) {

		global $post;

		$attendee_text = get_post_meta( $post->ID, 'WooCommerceEventsAttendeeOverride', true );

		if ( '' === $attendee_text ) {
			$attendee_text = __( 'Attendee', 'woocommerce-events' );
		}

		$seat_text = get_post_meta( $post->ID, 'WooCommerceEventsSeatingSeatOverride', true );

		if ( '' === $seat_text ) {
			$seat_text = __( 'Seat', 'fooevents-seating' );
		}

		$attendee_name_text        = str_ireplace( __( 'Attendee', 'woocommerce-events' ), $attendee_text, __( 'Attendee Name', 'woocommerce-events' ) );
		$attendee_email_text       = str_ireplace( __( 'Attendee', 'woocommerce-events' ), $attendee_text, __( 'Attendee Email', 'woocommerce-events' ) );
		$attendee_phone_text       = str_ireplace( __( 'Attendee', 'woocommerce-events' ), $attendee_text, __( 'Attendee Phone', 'woocommerce-events' ) );
		$attendee_company_text     = str_ireplace( __( 'Attendee', 'woocommerce-events' ), $attendee_text, __( 'Attendee Company', 'woocommerce-events' ) );
		$attendee_designation_text = str_ireplace( __( 'Attendee', 'woocommerce-events' ), $attendee_text, __( 'Attendee Designation', 'woocommerce-events' ) );
		$attendee_seat_text        = str_ireplace( __( 'Seat', 'woocommerce-events' ), $seat_text, str_ireplace( __( 'Attendee', 'woocommerce-events' ), $attendee_text, __( 'Attendee Seat', 'woocommerce-events' ) ) );

		switch ( $data_name ) {
			case 'barcode':
				return __( 'Barcode', 'woocommerce-events' );
			case 'logo':
				return __( 'Logo/Image', 'woocommerce-events' );
			case 'event':
				return __( 'Event Name Only', 'woocommerce-events' );
			case 'event_var':
				return __( 'Event Name/Variation', 'woocommerce-events' );
			case 'var_only':
				return __( 'Variation Only', 'woocommerce-events' );
			case 'date':
				return __( 'Event Date', 'woocommerce-events' );
			case 'ticketnr':
				return __( 'Ticket Number', 'woocommerce-events' );
			case 'custom_ticketnr':
				return __( 'Custom Ticket Number', 'woocommerce-events' );
			case 'name':
				return $attendee_name_text;
			case 'email':
				return $attendee_email_text;
			case 'phone':
				return $attendee_phone_text;
			case 'company':
				return $attendee_company_text;
			case 'designation':
				return $attendee_designation_text;
			case 'seat':
				return $attendee_seat_text;
			case 'location':
				return __( 'Event Location', 'woocommerce-events' );
			case 'custom':
				return __( 'Custom Text', 'woocommerce-events' );
			case 'spacer':
				return __( 'Empty Spacer', 'woocommerce-events' );
			default:
				foreach ( $cf_array as $key => $value ) {

					if ( $data_name === $key ) {

						return $value;

					}
				}
		}
	}

	/**
	 * Ajax function to save ticket and badge printing options
	 *
	 * @param int  $post_id post ID.
	 * @param bool $ajax_call AJAX.
	 */
	public function save_printing_options( $post_id = '', $ajax_call = true ) {

		$response = array( 'status' => 'error' );

		if ( '' === $post_id && ! empty( $_POST['post_id'] ) ) {

			$post_id = sanitize_text_field( wp_unslash( $_POST['post_id'] ) );

		}

		if ( '' !== $post_id ) {
			if ( isset( $_POST['WooCommerceBadgeFieldTopLeft'] ) ) {

				$woocommerce_badge_field_top_left = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldTopLeft'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopLeft', $woocommerce_badge_field_top_left );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldTopMiddle'] ) ) {

				$woocommerce_badge_field_top_middle = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldTopMiddle'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopMiddle', $woocommerce_badge_field_top_middle );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldTopRight'] ) ) {

				$woocommerce_badge_field_top_right = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldTopRight'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopRight', $woocommerce_badge_field_top_right );

			}

			if ( isset( $_POST['WooCommerceBadgeField_a_4'] ) ) {

				$woocommerce_badge_field_a_4 = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_a_4'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_a_4', $woocommerce_badge_field_a_4 );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldMiddleLeft'] ) ) {

				$woocommerce_badge_field_middle_left = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldMiddleLeft'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleLeft', $woocommerce_badge_field_middle_left );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldMiddleMiddle'] ) ) {

				$woocommerce_badge_field_middle_middle = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldMiddleMiddle'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleMiddle', $woocommerce_badge_field_middle_middle );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldMiddleRight'] ) ) {

				$woocommerce_badge_field_middle_right = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldMiddleRight'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleRight', $woocommerce_badge_field_middle_right );

			}

			if ( isset( $_POST['WooCommerceBadgeField_b_4'] ) ) {

				$woocommerce_badge_field_b_4 = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_b_4'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_b_4', $woocommerce_badge_field_b_4 );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldBottomLeft'] ) ) {

				$woocommerce_badge_field_bottom_left = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldBottomLeft'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomLeft', $woocommerce_badge_field_bottom_left );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldBottomMiddle'] ) ) {

				$woocommerce_badge_field_bottom_middle = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldBottomMiddle'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomMiddle', $woocommerce_badge_field_bottom_middle );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldBottomRight'] ) ) {

				$woocommerce_badge_field_bottom_right = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldBottomRight'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomRight', $woocommerce_badge_field_bottom_right );

			}

			if ( isset( $_POST['WooCommerceBadgeField_c_4'] ) ) {

				$woocommerce_badge_field_c_4 = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_c_4'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_c_4', $woocommerce_badge_field_c_4 );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_1'] ) ) {

				$woocommerce_badge_field_d_1 = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_1'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_1', $woocommerce_badge_field_d_1 );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_2'] ) ) {

				$woocommerce_badge_field_d_2 = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_2'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_2', $woocommerce_badge_field_d_2 );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_3'] ) ) {

				$woocommerce_badge_field_d_3 = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_3'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_3', $woocommerce_badge_field_d_3 );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_4'] ) ) {

				$woocommerce_badge_field_d_4 = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_4'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_4', $woocommerce_badge_field_d_4 );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldTopLeft_font'] ) ) {

				$woocommerce_badge_field_top_left_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldTopLeft_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopLeft_font', $woocommerce_badge_field_top_left_font );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldTopLeft_seat_label'] ) ) {

				$woocommerce_badge_field_top_left_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldTopLeft_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopLeft_seat_label', $woocommerce_badge_field_top_left_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldTopMiddle_font'] ) ) {

				$woocommerce_badge_field_top_middle_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldTopMiddle_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopMiddle_font', $woocommerce_badge_field_top_middle_font );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldTopMiddle_seat_label'] ) ) {

				$woocommerce_badge_field_top_middle_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldTopMiddle_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopMiddle_seat_label', $woocommerce_badge_field_top_middle_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldTopRight_font'] ) ) {

				$woocommerce_badge_field_top_right_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldTopRight_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopRight_font', $woocommerce_badge_field_top_right_font );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldTopRight_seat_label'] ) ) {

				$woocommerce_badge_field_top_right_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldTopRight_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopRight_seat_label', $woocommerce_badge_field_top_right_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeField_a_4_font'] ) ) {

				$woocommerce_badge_field_a_4_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_a_4_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_a_4_font', $woocommerce_badge_field_a_4_font );

			}

			if ( isset( $_POST['WooCommerceBadgeField_a_4_seat_label'] ) ) {

				$woocommerce_badge_field_a_4_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_a_4_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_a_4_seat_label', $woocommerce_badge_field_a_4_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldMiddleLeft_font'] ) ) {

				$woocommerce_badge_field_middle_left_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldMiddleLeft_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleLeft_font', $woocommerce_badge_field_middle_left_font );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldMiddleLeft_seat_label'] ) ) {

				$woocommerce_badge_field_middle_left_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldMiddleLeft_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleLeft_seat_label', $woocommerce_badge_field_middle_left_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldMiddleMiddle_font'] ) ) {

				$woocommerce_badge_field_middle_middle_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldMiddleMiddle_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleMiddle_font', $woocommerce_badge_field_middle_middle_font );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldMiddleMiddle_seat_label'] ) ) {

				$woocommerce_badge_field_middle_middle_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldMiddleMiddle_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleMiddle_seat_label', $woocommerce_badge_field_middle_middle_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldMiddleRight_font'] ) ) {

				$woocommerce_badge_field_middle_right_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldMiddleRight_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleRight_font', $woocommerce_badge_field_middle_right_font );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldMiddleRight_seat_label'] ) ) {

				$woocommerce_badge_field_middle_right_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldMiddleRight_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleRight_seat_label', $woocommerce_badge_field_middle_right_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeField_b_4_font'] ) ) {

				$woocommerce_badge_field_b_4_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_b_4_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_b_4_font', $woocommerce_badge_field_b_4_font );

			}

			if ( isset( $_POST['WooCommerceBadgeField_b_4_seat_label'] ) ) {

				$woocommerce_badge_field_b_4_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_b_4_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_b_4_seat_label', $woocommerce_badge_field_b_4_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldBottomLeft_font'] ) ) {

				$woocommerce_badge_field_bottom_left_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldBottomLeft_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomLeft_font', $woocommerce_badge_field_bottom_left_font );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldBottomLeft_seat_label'] ) ) {

				$woocommerce_badge_field_bottom_left_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldBottomLeft_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomLeft_seat_label', $woocommerce_badge_field_bottom_left_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldBottomMiddle_font'] ) ) {

				$woocommerce_badge_field_bottom_middle_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldBottomMiddle_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomMiddle_font', $woocommerce_badge_field_bottom_middle_font );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldBottomMiddle_seat_label'] ) ) {

				$woocommerce_badge_field_bottom_middle_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldBottomMiddle_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomMiddle_seat_label', $woocommerce_badge_field_bottom_middle_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldBottomRight_font'] ) ) {

				$woocommerce_badge_field_bottom_right_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldBottomRight_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomRight_font', $woocommerce_badge_field_bottom_right_font );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldBottomRight_seat_label'] ) ) {

				$woocommerce_badge_field_bottom_right_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldBottomRight_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomRight_seat_label', $woocommerce_badge_field_bottom_right_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeField_c_4_font'] ) ) {

				$woocommerce_badge_field_c_4_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_c_4_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_c_4_font', $woocommerce_badge_field_c_4_font );

			}

			if ( isset( $_POST['WooCommerceBadgeField_c_4_seat_label'] ) ) {

				$woocommerce_badge_field_c_4_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_c_4_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_c_4_seat_label', $woocommerce_badge_field_c_4_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_1_font'] ) ) {

				$woocommerce_badge_field_d_1_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_1_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_1_font', $woocommerce_badge_field_d_1_font );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_1_seat_label'] ) ) {

				$woocommerce_badge_field_d_1_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_1_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_1_seat_label', $woocommerce_badge_field_d_1_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_2_font'] ) ) {

				$woocommerce_badge_field_d_2_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_2_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_2_font', $woocommerce_badge_field_d_2_font );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_2_seat_label'] ) ) {

				$woocommerce_badge_field_d_2_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_2_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_2_seat_label', $woocommerce_badge_field_d_2_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_3_font'] ) ) {

				$woocommerce_badge_field_d_3_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_3_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_3_font', $woocommerce_badge_field_d_3_font );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_3_seat_label'] ) ) {

				$woocommerce_badge_field_d_3_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_3_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_3_seat_label', $woocommerce_badge_field_d_3_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_4_font'] ) ) {

				$woocommerce_badge_field_d_4_font = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_4_font'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_4_font', $woocommerce_badge_field_d_4_font );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_4_seat_label'] ) ) {

				$woocommerce_badge_field_d_4_seat_label = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_4_seat_label'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_4_seat_label', $woocommerce_badge_field_d_4_seat_label );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldTopLeft_logo'] ) ) {

				$woocommerce_badge_field_top_left_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldTopLeft_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopLeft_logo', $woocommerce_badge_field_top_left_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopLeft_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldTopMiddle_logo'] ) ) {

				$woocommerce_badge_field_top_middle_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldTopMiddle_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopMiddle_logo', $woocommerce_badge_field_top_middle_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopMiddle_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldTopRight_logo'] ) ) {

				$woocommerce_badge_field_top_right_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldTopRight_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopRight_logo', $woocommerce_badge_field_top_right_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopRight_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeField_a_4_logo'] ) ) {

				$woocommerce_badge_field_a_4_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_a_4_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_a_4_logo', $woocommerce_badge_field_a_4_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeField_a_4_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldMiddleLeft_logo'] ) ) {

				$woocommerce_badge_field_middle_left_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldMiddleLeft_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleLeft_logo', $woocommerce_badge_field_middle_left_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleLeft_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldMiddleMiddle_logo'] ) ) {

				$woocommerce_badge_field_middle_middle_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldMiddleMiddle_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleMiddle_logo', $woocommerce_badge_field_middle_middle_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleMiddle_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldMiddleRight_logo'] ) ) {

				$woocommerce_badge_field_middle_right_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldMiddleRight_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleRight_logo', $woocommerce_badge_field_middle_right_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleRight_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeField_b_4_logo'] ) ) {

				$woocommerce_badge_field_b_4_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_b_4_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_b_4_logo', $woocommerce_badge_field_b_4_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeField_b_4_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldBottomLeft_logo'] ) ) {

				$woocommerce_badge_field_bottom_left_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldBottomLeft_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomLeft_logo', $woocommerce_badge_field_bottom_left_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomLeft_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldBottomMiddle_logo'] ) ) {

				$woocommerce_badge_field_bottom_middle_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldBottomMiddle_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomMiddle_logo', $woocommerce_badge_field_bottom_middle_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomMiddle_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldBottomRight_logo'] ) ) {

				$woocommerce_badge_field_bottom_right_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeFieldBottomRight_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomRight_logo', $woocommerce_badge_field_bottom_right_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomRight_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeField_c_4_logo'] ) ) {

				$woocommerce_badge_field_c_4_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_c_4_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_c_4_logo', $woocommerce_badge_field_c_4_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeField_c_4_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_1_logo'] ) ) {

				$woocommerce_badge_field_d_1_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_1_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_1_logo', $woocommerce_badge_field_d_1_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeField_d_1_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_2_logo'] ) ) {

				$woocommerce_badge_field_d_2_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_2_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_2_logo', $woocommerce_badge_field_d_2_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeField_d_2_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_3_logo'] ) ) {

				$woocommerce_badge_field_d_3_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_3_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_3_logo', $woocommerce_badge_field_d_3_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeField_d_3_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_4_logo'] ) ) {

				$woocommerce_badge_field_d_4_logo = sanitize_text_field( wp_unslash( $_POST['WooCommerceBadgeField_d_4_logo'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_4_logo', $woocommerce_badge_field_d_4_logo );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeField_d_4_logo', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldTopLeft_custom'] ) ) {

				$woocommerce_badge_field_top_left_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeFieldTopLeft_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopLeft_custom', $woocommerce_badge_field_top_left_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopLeft_custom', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldTopMiddle_custom'] ) ) {

				$woocommerce_badge_field_top_middle_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeFieldTopMiddle_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopMiddle_custom', $woocommerce_badge_field_top_middle_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopMiddle_custom', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldTopRight_custom'] ) ) {

				$woocommerce_badge_field_top_right_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeFieldTopRight_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopRight_custom', $woocommerce_badge_field_top_right_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldTopRight_custom', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeField_a_4_custom'] ) ) {

				$woocommerce_badge_field_a_4_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeField_a_4_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_a_4_custom', $woocommerce_badge_field_a_4_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeField_a_4_custom', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldMiddleLeft_custom'] ) ) {

				$woocommerce_badge_field_middle_left_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeFieldMiddleLeft_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleLeft_custom', $woocommerce_badge_field_middle_left_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleLeft_custom', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldMiddleMiddle_custom'] ) ) {

				$woocommerce_badge_field_middle_middle_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeFieldMiddleMiddle_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleMiddle_custom', $woocommerce_badge_field_middle_middle_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleMiddle_custom', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldMiddleRight_custom'] ) ) {

				$woocommerce_badge_field_middle_right_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeFieldMiddleRight_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleRight_custom', $woocommerce_badge_field_middle_right_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldMiddleRight_custom', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeField_b_4_custom'] ) ) {

				$woocommerce_badge_field_b_4_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeField_b_4_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_b_4_custom', $woocommerce_badge_field_b_4_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeField_b_4_custom', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldBottomLeft_custom'] ) ) {

				$woocommerce_badge_field_bottom_left_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeFieldBottomLeft_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomLeft_custom', $woocommerce_badge_field_bottom_left_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomLeft_custom', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldBottomMiddle_custom'] ) ) {

				$woocommerce_badge_field_bottom_middle_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeFieldBottomMiddle_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomMiddle_custom', $woocommerce_badge_field_bottom_middle_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomMiddle_custom', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeFieldBottomRight_custom'] ) ) {

				$woocommerce_badge_field_bottom_right_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeFieldBottomRight_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomRight_custom', $woocommerce_badge_field_bottom_right_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeFieldBottomRight_custom', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeField_c_4_custom'] ) ) {

				$woocommerce_badge_field_c_4_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeField_c_4_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_c_4_custom', $woocommerce_badge_field_c_4_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeField_c_4_custom', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_1_custom'] ) ) {

				$woocommerce_badge_field_d_1_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeField_d_1_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_1_custom', $woocommerce_badge_field_d_1_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeField_d_1_custom', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_2_custom'] ) ) {

				$woocommerce_badge_field_d_2_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeField_d_2_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_2_custom', $woocommerce_badge_field_d_2_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeField_d_2_custom', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_3_custom'] ) ) {

				$woocommerce_badge_field_d_3_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeField_d_3_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_3_custom', $woocommerce_badge_field_d_3_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeField_d_3_custom', '' );

			}

			if ( isset( $_POST['WooCommerceBadgeField_d_4_custom'] ) ) {

				$woocommerce_badge_field_d_4_custom = wp_kses_post( wp_unslash( $_POST['WooCommerceBadgeField_d_4_custom'] ) );
				update_post_meta( $post_id, 'WooCommerceBadgeField_d_4_custom', $woocommerce_badge_field_d_4_custom );

			} else {

				update_post_meta( $post_id, 'WooCommerceBadgeField_d_4_custom', '' );

			}

			if ( isset( $_POST['WooCommercePrintTicketSort'] ) ) {

				$woocommerce_print_ticket_sort = sanitize_text_field( wp_unslash( $_POST['WooCommercePrintTicketSort'] ) );
				update_post_meta( $post_id, 'WooCommercePrintTicketSort', $woocommerce_print_ticket_sort );

			}

			if ( isset( $_POST['WooCommercePrintTicketNumbers'] ) ) {

				$woocommerce_print_ticket_numbers = sanitize_text_field( wp_unslash( $_POST['WooCommercePrintTicketNumbers'] ) );
				update_post_meta( $post_id, 'WooCommercePrintTicketNumbers', $woocommerce_print_ticket_numbers );

			} else {

				update_post_meta( $post_id, 'WooCommercePrintTicketNumbers', '' );

			}

			if ( isset( $_POST['WooCommercePrintTicketOrders'] ) ) {

				$woocommerce_print_ticket_orders = sanitize_text_field( wp_unslash( $_POST['WooCommercePrintTicketOrders'] ) );
				update_post_meta( $post_id, 'WooCommercePrintTicketOrders', $woocommerce_print_ticket_orders );

			} else {

				update_post_meta( $post_id, 'WooCommercePrintTicketOrders', '' );

			}

			if ( isset( $_POST['WooCommercePrintTicketSize'] ) ) {

				$woocommerce_print_ticket_size = sanitize_text_field( wp_unslash( $_POST['WooCommercePrintTicketSize'] ) );
				update_post_meta( $post_id, 'WooCommercePrintTicketSize', $woocommerce_print_ticket_size );

			}

			if ( isset( $_POST['WooCommercePrintTicketNrColumns'] ) ) {

				$woocommerce_print_ticket_nr_columns = sanitize_text_field( wp_unslash( $_POST['WooCommercePrintTicketNrColumns'] ) );
				update_post_meta( $post_id, 'WooCommercePrintTicketNrColumns', $woocommerce_print_ticket_nr_columns );

			}

			if ( isset( $_POST['WooCommercePrintTicketNrRows'] ) ) {

				$woocommerce_print_ticket_nr_rows = sanitize_text_field( wp_unslash( $_POST['WooCommercePrintTicketNrRows'] ) );
				update_post_meta( $post_id, 'WooCommercePrintTicketNrRows', $woocommerce_print_ticket_nr_rows );

			}

			if ( isset( $_POST['WooCommerceEventsCutLinesPrintTicket'] ) ) {

				$woocommerce_events_cut_lines_print_ticket = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsCutLinesPrintTicket'] ) );
				update_post_meta( $post_id, 'WooCommerceEventsCutLinesPrintTicket', $woocommerce_events_cut_lines_print_ticket );

			} else {

				update_post_meta( $post_id, 'WooCommerceEventsCutLinesPrintTicket', 'off' );

			}

			if ( isset( $_POST['WooCommerceEventsTicketBackgroundImage'] ) ) {

				$woocommerce_events_ticket_background_image = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTicketBackgroundImage'] ) );
				update_post_meta( $post_id, 'WooCommerceEventsTicketBackgroundImage', $woocommerce_events_ticket_background_image );

			}

			$response['status'] = 'success';
		}

		if ( $ajax_call ) {

			echo wp_json_encode( $response );
			exit();

		}
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
	 * Get's orders that contain a particular order
	 *
	 * @param int    $product_id product ID.
	 * @param string $order_status status.
	 * @global object $wpdb
	 * @return object
	 */
	private function get_orders_ids_by_product_id( $product_id, $order_status = array( 'wc-completed' ) ) {
		global $wpdb;

		// TODO: Remove CPT when WC 8.0 released.
		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {

			// HPOS.

			$results = $wpdb->get_col(
				"
				SELECT order_items.order_id
				FROM {$wpdb->prefix}woocommerce_order_items as order_items
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
				LEFT JOIN {$wpdb->prefix}wc_orders AS wc_orders ON order_items.order_id = wc_orders.id
				WHERE wc_orders.type = 'shop_order'
				AND wc_orders.status IN ( '" . implode( "','", $order_status ) . "' )
				AND order_items.order_item_type = 'line_item'
				AND order_item_meta.meta_key = '_product_id'
				AND order_item_meta.meta_value = '$product_id'
			"
			);

		} else {

			// CPT.

			$results = $wpdb->get_col(
				"
				SELECT order_items.order_id
				FROM {$wpdb->prefix}woocommerce_order_items as order_items
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
				LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
				WHERE posts.post_type = 'shop_order'
				AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
				AND order_items.order_item_type = 'line_item'
				AND order_item_meta.meta_key = '_product_id'
				AND order_item_meta.meta_value = '$product_id'
			"
			);

		}

		return $results;
	}

	/**
	 * Generates random string used for ticket hash
	 *
	 * @param int $length length.
	 * @return string
	 */
	private function generate_random_string( $length = 10 ) {

		return substr( str_shuffle( str_repeat( $x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil( $length / strlen( $x ) ) ) ), 1, $length );
	}

	/**
	 * Outputs notices to screen.
	 *
	 * @param array $notices notices.
	 */
	private function output_notices( $notices ) {

		foreach ( $notices as $notice ) {

				echo '<div class="updated"><p>' . esc_attr( $notice ) . '</p></div>';

		}
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
			'Февра��ь'    => 'February',
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
}
