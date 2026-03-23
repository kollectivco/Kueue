<?php
/**
 * Order helper class
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Utilities\OrderUtil;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

/**
 * Order helper class
 */
class FooEvents_Orders_Helper {

	/**
	 * Configuration object
	 *
	 * @var array $config contains paths and other configurations
	 */
	public $config;

	/**
	 * Ticket helper object
	 *
	 * @var array $ticket_helper
	 */
	public $ticket_helper;

	/**
	 * On class load
	 *
	 * @param array $config configuration values.
	 */
	public function __construct( $config ) {

		$this->config = $config;

		add_action( 'add_meta_boxes', array( &$this, 'add_orders_meta_boxes' ), 10, 2 );
		add_action( 'woocommerce_email_customer_details', array( $this, 'display_attendee_details_in_new_order_email' ), 10, 4 );

		add_action( 'woocommerce_view_order', array( $this, 'my_account_order_ticket_listing' ) );

		add_action( 'wp_ajax_resend_order_ticket', array( $this, 'resend_order_ticket' ) );
	}

	/**
	 * Adds meta boxes to the WooCommerce orders page.
	 *
	 * @param string $post_type the post type.
	 * @param object $post the order.
	 */
	public function add_orders_meta_boxes( $post_type, $post ) {

		global $wpdb;

		$screens = array( 'shop_orders', 'shop_order' );

		$id = '';
		if ( isset( $_GET['id'] ) ) {

			$id = sanitize_text_field( wp_unslash( $_GET['id'] ) );

		} else {

			$id = $post->ID;

		}

		foreach ( $screens as $screen_name ) {

			if ( ( 'shop_order' === get_post_type( $id ) && isset( $_GET['post'] ) ) || ( isset( $_GET['page'] ) && 'wc-orders' === $_GET['page'] && isset( $id ) && $id > 0 ) ) { // phpcs:ignore WordPress.Security.NonceVerification

				$order = wc_get_order( $id );

				$woocommerce_events_order_tickets          = $order->get_meta( 'WooCommerceEventsOrderTickets', true );
				$woocommerce_events_order_admin_add_ticket = $order->get_meta( 'WooCommerceEventsOrderAdminAddTicket', true );

				$imported_order_tickets = $wpdb->get_results(
					$wpdb->prepare(
						"
						SELECT p.*
						FROM {$wpdb->posts} p
						INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
						WHERE p.post_type = 'event_magic_tickets'
						AND pm.meta_key = 'WooCommerceEventsOrderID'
						AND pm.meta_value = %d
					",
						$id
					)
				);

				if ( ! empty( $woocommerce_events_order_tickets ) || ! empty( $imported_order_tickets ) || 'yes' === $woocommerce_events_order_admin_add_ticket ) {

					$screen = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ? wc_get_page_screen_id( $screen_name ) : $screen_name;

					add_meta_box(
						'woocommerce_events_order_details',
						__( 'Attendee Details', 'woocommerce-events' ),
						array( &$this, 'add_orders_meta_boxes_details' ),
						$screen,
						'normal'
					);

					add_meta_box(
						'woocommerce_events_resend_tickets',
						__( 'Resend Tickets', 'woocommerce-events' ),
						array( &$this, 'add_orders_meta_boxes_resend_tickets' ),
						$screen,
						'side',
						'low'
					);

				}
			}
		}
	}

	/**
	 * Outputs ticket details meta box
	 *
	 * @param object $post WordPress post object.
	 */
	public function add_orders_meta_boxes_details( $post ) {

		global $wpdb;

		$id = '';
		if ( isset( $_GET['id'] ) ) {

			$id = sanitize_text_field( wp_unslash( $_GET['id'] ) );

		} else {

			$id = $post->ID;

		}

		$order                                    = wc_get_order( $id );
		$order_status                             = $order->get_status();
		$woocommerce_events_sent_ticket           = $order->get_meta( 'WooCommerceEventsTicketsGenerated', true );
		$global_woocommerce_events_send_on_status = get_option( 'globalWooCommerceEventsSendOnStatus' );
		$order_statuses                           = wc_get_order_statuses();

		$imported_order_tickets = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT p.*
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = 'event_magic_tickets'
				AND pm.meta_key = 'WooCommerceEventsOrderID'
				AND pm.meta_value = %d
			",
				$id
			)
		);

		$woocommerce_events_order_tickets = $order->get_meta( 'WooCommerceEventsOrderTickets', true );

		$status_output = '';
		if ( ! is_array( $global_woocommerce_events_send_on_status ) && ! empty( $global_woocommerce_events_send_on_status ) ) {

			$status_output = $order_statuses[ $global_woocommerce_events_send_on_status ];

		} elseif ( ! empty( $global_woocommerce_events_send_on_status ) ) {

			foreach ( $global_woocommerce_events_send_on_status as $status ) {

				$status_output .= $order_statuses[ $status ] . ', ';

			}

			$status_output = substr( $status_output, 0, strlen( $status_output ) - 2 );
		} else {

			$status_output = 'Completed';

		}

		if ( 'yes' === $woocommerce_events_sent_ticket || $imported_order_tickets ) {

			$tickets_query                    = new WP_Query(
				array(
					'post_type'      => array( 'event_magic_tickets' ),
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => 'WooCommerceEventsOrderID',
							'value'   => $id,
							'compare' => '=',
						),
					),
				)
			);
			$event_tickets                    = $tickets_query->get_posts();
			$woocommerce_events_order_tickets = $this->process_event_tickets_for_display( $event_tickets );

			require $this->config->template_path . 'order-ticket-details-generated-tickets.php';

		} else {

			$woocommerce_events_order_tickets = $order->get_meta( 'WooCommerceEventsOrderTickets', true );
			$woocommerce_events_order_tickets = $this->process_order_tickets_for_display( $woocommerce_events_order_tickets );

			require $this->config->template_path . 'order-ticket-details.php';

		}
	}

	/**
	 * Outputs resend tickets meta box
	 *
	 * @param object $post WordPress post object.
	 */
	public function add_orders_meta_boxes_resend_tickets( $post ) {

		global $wpdb;

		$id = '';
		if ( isset( $_GET['id'] ) ) {

			$id = sanitize_text_field( wp_unslash( $_GET['id'] ) );

		} else {

			$id = $post->ID;

		}

		$imported_order_tickets = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT p.*
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = 'event_magic_tickets'
				AND pm.meta_key = 'WooCommerceEventsOrderID'
				AND pm.meta_value = %d
			",
				$id
			)
		);

		$order                                = wc_get_order( $id );
		$woocommerce_events_tickets_generated = $order->get_meta( 'WooCommerceEventsTicketsGenerated', true );

		require $this->config->template_path . 'order-ticket-resend-ticket.php';
	}

	/**
	 * Processes resend order ticket
	 */
	public function resend_order_ticket() {

		$post_id = '';
		if ( isset( $_POST['postID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$post_id = sanitize_text_field( wp_unslash( $_POST['postID'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

		}

		$to = '';
		if ( isset( $_POST['WooCommerceEventsResendOrderTicketEmail'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$to = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsResendOrderTicketEmail'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

		}

		if ( ! empty( $post_id ) && ! empty( $to ) ) {

			require_once $this->config->class_path . 'class-fooevents-woo-helper.php';
			$woo_helper = new FooEvents_Woo_Helper( $this->config );

			$woo_helper->build_send_tickets( $post_id, true, $to );

			if ( isset( $_POST['postID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

				echo wp_json_encode( array( 'message' => 'Mail has been sent.' ) );
			}
		}

		exit();
	}

	/**
	 * Formats tickets to be displayed
	 *
	 * @param array $event_tickets event tickets.
	 * @return array
	 */
	public function process_event_tickets_for_display( $event_tickets ) {

		require_once $this->config->class_path . 'class-fooevents-ticket-helper.php';
		$ticket_helper           = new FooEvents_Ticket_Helper( $this->config );
		$processed_event_tickets = array();

		require_once $this->config->class_path . 'class-fooevents-zoom-api-helper.php';
		$zoom_api_helper = new FooEvents_Zoom_API_Helper( $this->config );

		$x = 0;
		foreach ( $event_tickets as $ticket_raw ) {

			$ticket = $ticket_helper->get_ticket_data( $ticket_raw->ID, 'admin' );

			$event = get_post( $ticket['WooCommerceEventsProductID'] );

			if ( empty( $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ] ) ) {

				$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ] = array();

				$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsProductID'] = $ticket['WooCommerceEventsProductID'];
				$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsName']      = $event->post_title;
				$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsURL']       = get_permalink( $event->ID );

				$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsDate']      = get_post_meta( $event->ID, 'WooCommerceEventsDate', true );
				$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsStartTime'] = get_post_meta( $event->ID, 'WooCommerceEventsHour', true ) . ':' . get_post_meta( $event->ID, 'WooCommerceEventsMinutes', true ) . ' ' . get_post_meta( $event->ID, 'WooCommerceEventsPeriod', true );
				$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsEndTime']   = get_post_meta( $event->ID, 'WooCommerceEventsHourEnd', true ) . ':' . get_post_meta( $event->ID, 'WooCommerceEventsMinutesEnd', true ) . ' ' . get_post_meta( $event->ID, 'WooCommerceEventsEndPeriod', true );

				$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsLocation']       = get_post_meta( $event->ID, 'WooCommerceEventsLocation', true );
				$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsGPS']            = get_post_meta( $event->ID, 'WooCommerceEventsGPS', true );
				$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsSupportContact'] = get_post_meta( $event->ID, 'WooCommerceEventsSupportContact', true );
				$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsEmail']          = get_post_meta( $event->ID, 'WooCommerceEventsEmail', true );

				if ( empty( $ticket['WooCommerceEventsZoomText'] ) && 'bookings' !== get_post_meta( $event->ID, 'WooCommerceEventsType', true ) ) {

					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsZoomText'] = $zoom_api_helper->get_ticket_text( array( 'WooCommerceEventsProductID' => $event->ID ), 'admin' );

				}
			}

			if ( ! empty( $ticket['WooCommerceEventsVariations'] ) ) {

				$ticket_vars = array();
				foreach ( $ticket['WooCommerceEventsVariations'] as $variation_name => $variation_value ) {

					$variation_name_output = str_replace( 'attribute_', '', $variation_name );
					$variation_name_output = str_replace( 'pa_', '', $variation_name_output );
					$variation_name_output = str_replace( '_', ' ', $variation_name_output );
					$variation_name_output = str_replace( '-', ' ', $variation_name_output );
					$variation_name_output = str_replace( 'Pa_', '', $variation_name_output );
					$variation_name_output = ucwords( $variation_name_output );

					$variation_value_output = str_replace( '_', ' ', $variation_value );
					$variation_value_output = str_replace( '-', ' ', $variation_value_output );
					$variation_value_output = ucwords( $variation_value_output );

					$ticket_vars[ $variation_name_output ] = $variation_value_output;

				}

				$ticket['WooCommerceEventsVariations'] = $ticket_vars;

			}

			$ticket_cust = array();

			if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

				$fooevents_custom_attendee_fields = new Fooevents_Custom_Attendee_Fields();
				$ticket_cust                      = $fooevents_custom_attendee_fields->fetch_attendee_details_for_order_generated( $ticket['WooCommerceEventsProductID'], $ticket_raw->ID );

			}

			$ticket['WooCommerceEventsCustomAttendeeFields'] = $ticket_cust;

			$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ] = $ticket;

			if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

					$fooevents_bookings = new Fooevents_Bookings();

				if ( ! empty( $ticket['WooCommerceEventsBookingOptions'] ) ) {

					$woocommerce_events_booking_fields = $fooevents_bookings->process_capture_booking( $ticket['WooCommerceEventsProductID'], $ticket['WooCommerceEventsBookingOptions'], '' );

				}

				$bookings_date_term = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsBookingsDateOverride', true );
				$bookings_slot_term = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsBookingsSlotOverride', true );

				if ( ! empty( $woocommerce_events_booking_fields ) ) {

					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['slot']                      = $woocommerce_events_booking_fields['slot'];
					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['slot_id']                   = $woocommerce_events_booking_fields['slot_id'];
					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['date']                      = $woocommerce_events_booking_fields['date'];
					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['date_id']                   = $woocommerce_events_booking_fields['date_id'];
					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['WooCommerceEventsZoomText'] = $ticket['WooCommerceEventsZoomText'];

				}
			}

			++$x;
		}

		return $processed_event_tickets;
	}

	/**
	 * Displays FooEvents details in the WooCommerce New Order email
	 *
	 * @param WC_Order $order order object.
	 * @param string   $sent_to_admin send to admin.
	 * @param string   $plain_text plain text.
	 * @param string   $email email.
	 */
	public function display_attendee_details_in_new_order_email( $order, $sent_to_admin, $plain_text, $email ) {

		$woocommerce_events_order_tickets     = $order->get_meta( 'WooCommerceEventsOrderTickets', true );
		$woocommerce_events_tickets_generated = $order->get_meta( 'WooCommerceEventsTicketsGenerated', true );

		if ( ! empty( $woocommerce_events_order_tickets ) ) {

			if ( 'yes' !== $woocommerce_events_tickets_generated ) {

				$woocommerce_events_order_tickets = $this->process_order_tickets_for_display( $woocommerce_events_order_tickets );

			} else {

				$order_id = $order->get_id();

				$tickets_query                    = new WP_Query(
				array(
						'post_type'      => array( 'event_magic_tickets' ),
						'posts_per_page' => -1,
						'meta_query'     => array(
							array(
								'key'     => 'WooCommerceEventsOrderID',
								'value'   => $order_id,
								'compare' => '=',
							),
						),
					)
				);
				$event_tickets                    = $tickets_query->get_posts();
				$woocommerce_events_order_tickets = $this->process_event_tickets_for_display( $event_tickets );

			}

			if ( file_exists( $this->config->email_template_path_theme . 'order-new-order-email-ticket-details.php' ) ) {

				require $this->config->email_template_path_theme . 'order-new-order-email-ticket-details.php';

			} else {

				require $this->config->template_path . 'order-new-order-email-ticket-details.php';

			}
		}
	}

	/**
	 * Displays tickets on the My Account View Order page.
	 *
	 * @param int $order_id the order ID.
	 */
	public function my_account_order_ticket_listing( $order_id ) {

		$order = wc_get_order( $order_id );

		$woocommerce_events_tickets_generated = $order->get_meta( 'WooCommerceEventsTicketsGenerated', true );

		if ( 'yes' === $woocommerce_events_tickets_generated ) {

			$pdf_plugin_enabled    = false;
			$fooevents_pdf_tickets = '';

			if ( is_plugin_active( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) || is_plugin_active_for_network( 'fooevents_pdf_tickets/fooevents-pdf-tickets.php' ) ) {

				$fooevents_pdf_tickets = new FooEvents_PDF_Tickets();
				$pdf_plugin_enabled    = true;

			}

			$tickets_query                    = new WP_Query(
				array(
					'post_type'      => array( 'event_magic_tickets' ),
					'posts_per_page' => -1,
					'meta_query'     => array(
						array(
							'key'     => 'WooCommerceEventsOrderID',
							'value'   => $order_id,
							'compare' => '=',
						),
					),
				)
			);
			$event_tickets                    = $tickets_query->get_posts();
			$woocommerce_events_order_tickets = $this->process_event_tickets_for_display( $event_tickets );

			require $this->config->template_path . 'my-account-view-order-tickets.php';

		}
	}

	/**
	 * Checks if a plugin is active.
	 *
	 * @param string $plugin plugin.
	 * @return boolean
	 */
	private function is_plugin_active( $plugin ) {

		return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true );
	}

	/**
	 * Formats tickets to be displayed
	 *
	 * @param array $woocommerce_events_order_tickets tickets in order.
	 * @return array
	 */
	public function process_order_tickets_for_display( $woocommerce_events_order_tickets ) {

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		require_once $this->config->class_path . 'class-fooevents-zoom-api-helper.php';
		$zoom_api_helper = new FooEvents_Zoom_API_Helper( $this->config );

		$processed_event_tickets = array();

		foreach ( $woocommerce_events_order_tickets as $event_tickets ) {

			$x = 0;
			foreach ( $event_tickets as $ticket ) {

				$event       = get_post( $ticket['WooCommerceEventsProductID'] );
				$event_type  = get_post_meta( $event->ID, 'WooCommerceEventsType', true );
				$global_time = 'off';

				if ( empty( $processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ] ) ) {

					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ] = array();

					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsProductID'] = $ticket['WooCommerceEventsProductID'];
					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsName']      = $event->post_title;
					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsURL']       = get_permalink( $event->ID );
					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsType']      = $event_type;

					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsDate']      = get_post_meta( $event->ID, 'WooCommerceEventsDate', true );
					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsStartTime'] = get_post_meta( $event->ID, 'WooCommerceEventsHour', true ) . ':' . get_post_meta( $event->ID, 'WooCommerceEventsMinutes', true ) . ' ' . get_post_meta( $event->ID, 'WooCommerceEventsPeriod', true );
					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsEndTime']   = get_post_meta( $event->ID, 'WooCommerceEventsHourEnd', true ) . ':' . get_post_meta( $event->ID, 'WooCommerceEventsMinutesEnd', true ) . ' ' . get_post_meta( $event->ID, 'WooCommerceEventsEndPeriod', true );

					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsLocation']       = get_post_meta( $event->ID, 'WooCommerceEventsLocation', true );
					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsGPS']            = get_post_meta( $event->ID, 'WooCommerceEventsGPS', true );
					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsSupportContact'] = get_post_meta( $event->ID, 'WooCommerceEventsSupportContact', true );
					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsEmail']          = get_post_meta( $event->ID, 'WooCommerceEventsEmail', true );

					if ( empty( $ticket['WooCommerceEventsBookingOptions'] ) ) {

						$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsZoomText'] = $zoom_api_helper->get_ticket_text( array( 'WooCommerceEventsProductID' => $event->ID ), 'admin' );

					}
				}

				if ( ! empty( $ticket['WooCommerceEventsVariationID'] ) ) {

					$variation_obj = new WC_Product_variation( $ticket['WooCommerceEventsVariationID'] );
					$variations    = $variation_obj->get_attributes();

					if ( ! empty( $variations ) ) {
						$ticket_variations = array();

						foreach ( $variations as $key => $value ) {
							$ticket_variations[ trim( wc_attribute_label( $key, $variation_obj ) ) ] = trim( $value );
						}

						$ticket['WooCommerceEventsVariations'] = $ticket_variations;

					}
				}

				if ( ! empty( $ticket['WooCommerceEventsCustomAttendeeFields'] ) ) {

					if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

						$fooevents_custom_attendee_fields = new Fooevents_Custom_Attendee_Fields();
						$ticket_cust                      = $fooevents_custom_attendee_fields->fetch_attendee_details_for_order( $ticket['WooCommerceEventsProductID'], $ticket['WooCommerceEventsCustomAttendeeFields'] );

					}

					$ticket['WooCommerceEventsCustomAttendeeFields'] = $ticket_cust;

				}

				$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ] = $ticket;

				if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

						$fooevents_bookings = new Fooevents_Bookings();

					if ( ! empty( $ticket['WooCommerceEventsBookingOptions'] ) ) {

						$woocommerce_events_booking_fields = $fooevents_bookings->process_capture_booking( $ticket['WooCommerceEventsProductID'], $ticket['WooCommerceEventsBookingOptions'], '' );

					}

					$bookings_date_term = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsBookingsDateOverride', true );
					$bookings_slot_term = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsBookingsSlotOverride', true );

					$slot_label = '';
					if ( empty( $bookings_slot_term ) ) {

						$slot_label = __( 'Slot', 'woocommerce-events' );

					} else {

						$slot_label = $bookings_slot_term;

					}

					$date_label = '';
					if ( empty( $bookings_date_term ) ) {

						$date_label = __( 'Date', 'woocommerce-events' );

					} else {

						$date_label = $bookings_date_term;

					}

					if ( ! empty( $woocommerce_events_booking_fields ) ) {

						$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['slot']      = $woocommerce_events_booking_fields['slot'];
						$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['slot_id']   = $woocommerce_events_booking_fields['slot_id'];
						$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['slot_term'] = $slot_label;
						$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['date']      = $woocommerce_events_booking_fields['date'];
						$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['date_id']   = $woocommerce_events_booking_fields['date_id'];
						$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['date_term'] = $date_label;

						$ticket_text_options = array_merge( array( 'WooCommerceEventsProductID' => $event->ID ), $woocommerce_events_booking_fields );

						$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsBookingOptions']['WooCommerceEventsZoomText'] = $zoom_api_helper->get_ticket_text( $ticket_text_options, 'admin' );

					}
				}

				if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsEndDate']    = get_post_meta( $event->ID, 'WooCommerceEventsEndDate', true );
					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsSelectDate'] = get_post_meta( $event->ID, 'WooCommerceEventsSelectDate', true );

					$global_time = get_post_meta( $event->ID, 'WooCommerceEventsSelectGlobalTime', true );
					if ( 'select' == $event_type && 'off' == $global_time ) {

						$start_hours       = get_post_meta( $event->ID, 'WooCommerceEventsSelectDateHour', true );
						$start_minutes     = get_post_meta( $event->ID, 'WooCommerceEventsSelectDateMinutes', true );
						$start_periods     = get_post_meta( $event->ID, 'WooCommerceEventsSelectDatePeriod', true );
						$start_times       = array();
						$end_hours         = get_post_meta( $event->ID, 'WooCommerceEventsSelectDateHourEnd', true );
						$end_minutes       = get_post_meta( $event->ID, 'WooCommerceEventsSelectDateMinutesEnd', true );
						$end_periods       = get_post_meta( $event->ID, 'WooCommerceEventsSelectDatePeriodEnd', true );
						$end_times         = array();
						$time_array_length = count( $start_hours );

						for ( $i = 0; $i < $time_array_length; $i++ ) {

							array_push( $start_times, $start_hours[ $i ] . ':' . $start_minutes[ $i ] . ' ' . $start_periods[ $i ] );
							array_push( $end_times, $end_hours[ $i ] . ':' . $end_minutes[ $i ] . ' ' . $end_periods[ $i ] );

						}

						$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsSelectStartTime'] = $start_times;
						$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['WooCommerceEventsSelectEndTime']   = $end_times;

					}
				}

				if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

					$row_text = get_post_meta( $event->ID, 'WooCommerceEventsSeatingRowOverride', true );

					if ( '' === $row_text ) {
						$row_text = __( 'Row', 'woocommerce-events' );
					}

					$seat_text = get_post_meta( $event->ID, 'WooCommerceEventsSeatingSeatOverride', true );

					if ( '' === $seat_text ) {
						$seat_text = __( 'Seat', 'woocommerce-events' );
					}

					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsSeatingRowOverride']  = $row_text;
					$processed_event_tickets[ $ticket['WooCommerceEventsProductID'] ]['tickets'][ $x ]['WooCommerceEventsSeatingSeatOverride'] = $seat_text;

				}

				++$x;

			}
		}

		return $processed_event_tickets;
	}
}
