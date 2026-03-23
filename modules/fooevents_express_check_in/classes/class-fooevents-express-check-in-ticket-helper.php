<?php
/**
 * Ticket Helper class.
 *
 * @link https://www.fooevents.com
 * @package fooevents-express-check-in
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * Main plugin class.
 */
class FooEvents_Express_Check_In_Ticket_Helper {

	/**
	 * Configuration object
	 *
	 * @var object $config contains paths and other configurations
	 */
	private $config;

	/**
	 * On class load
	 *
	 * @param array $config configuration.
	 */
	public function __construct( $config ) {

		$this->config = $config;
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		add_action( 'wp_ajax_fooevents_perform_search', array( $this, 'fooevents_perform_search' ) );
		add_action( 'wp_ajax_change_ticket_status', array( $this, 'change_ticket_status' ) );
		add_action( 'wp_ajax_change_ticket_status_auto_complete', array( $this, 'change_ticket_status_auto_complete' ) );
		add_action( 'wp_ajax_undo_check_in', array( $this, 'undo_check_in' ) );
	}

	/**
	 * Adds Express Check-ins to the Tickets menu.
	 */
	public function add_menu_item() {

		if ( current_user_can( 'publish_event_magic_tickets' ) ) {
			add_submenu_page( 'fooevents', __( 'Express Check-in', 'fooevents-express-check-in' ), __( 'Express Check-in', 'fooevents-express-check-in' ), 'edit_posts', 'fooevents-express-checkin-page', array( $this, 'display_page' ) );
		}
	}

	/**
	 * Displays Express Check-ins admin page
	 */
	public function display_page() {

		$multiday_options = '';

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

			$fooevents_multiday_events = new Fooevents_Multiday_Events();
			$multiday_options          = $fooevents_multiday_events->display_multiday_express_check_in_options();
			$multiday_options          = '<span>'.$multiday_options.'</span>';

		}

		include $this->config->template_path . 'express-check-in.php';
	}

	/**
	 * Processes search from the Express Check-ins page
	 */
	public function fooevents_perform_search() {

		$attendee_term = get_option( 'globalWooCommerceEventsAttendeeOverride', true );

		if ( empty( $attendee_term ) || 1 == $attendee_term ) {

			$attendee_term = __( 'Attendee', 'fooevents-express-check-in' );

		}

		$nonce = '';
		if ( isset( $_POST['fooevents-express-check-in-search-nonce'] ) ) {
			$nonce = esc_attr( sanitize_text_field( wp_unslash( $_POST['fooevents-express-check-in-search-nonce'] ) ) );
		}

		/*
		if ( ! wp_verify_nonce( $nonce, 'fooevents-express-check-in-search' ) ) {
			die( esc_attr__( 'Security check failed - FooEvents Express Check-ins 0001', 'fooevents-express-check-in' ) );
		}*/

		$value = '';
		if ( isset( $_POST['value'] ) ) {

			$value = sanitize_text_field( wp_unslash( $_POST['value'] ) );

		}

		$args = array(
			'post_type'      => array( 'event_magic_tickets' ),
			'posts_per_page' => -1,
			'meta_query'     => array(
				'relation' => 'OR',
				array(
					'key'     => 'WooCommerceEventsTicketID',
					'value'   => $value,
					'compare' => 'like',
				),
				array(
					'key'     => 'WooCommerceEventsAttendeeName',
					'value'   => $value,
					'compare' => 'like',
				),
				array(
					'key'     => 'WooCommerceEventsAttendeeLastName',
					'value'   => $value,
					'compare' => 'like',
				),
				array(
					'key'     => 'WooCommerceEventsAttendeeEmail',
					'value'   => $value,
					'compare' => 'like',
				),
				array(
					'key'     => 'WooCommerceEventsCustomerID',
					'value'   => $value,
					'compare' => 'like',
				),
				array(
					'key'     => 'WooCommerceEventsPurchaserFirstName',
					'value'   => $value,
					'compare' => 'like',
				),
				array(
					'key'     => 'WooCommerceEventsPurchaserLastName',
					'value'   => $value,
					'compare' => 'like',
				),
				array(
					'key'     => 'WooCommerceEventsPurchaserEmail',
					'value'   => $value,
					'compare' => 'like',
				),
				array(
					'key'     => 'WooCommerceEventsOrderID',
					'value'   => $value,
					'compare' => 'like',
				),
				array(
					'key'     => 'WooCommerceEventsProductName',
					'value'   => $value,
					'compare' => 'like',
				),
				array(
					'key'     => 'WooCommerceEventsTicketNumberFormatted',
					'value'   => $value,
					'compare' => 'like',
				),
			),
		);

		$tickets      = new WP_Query( $args );
		$tickets_data = '';

		$multiday = '';
		if ( isset( $_POST['multiday'] ) ) {

			$multiday = sanitize_text_field( wp_unslash( $_POST['multiday'] ) );

		}

		$day = '';
		if ( isset( $_POST['day'] ) ) {

			$day = sanitize_text_field( wp_unslash( $_POST['day'] ) );

		}

		$bookings_enabled = false;

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

			$bookings_enabled = true;

		}

		foreach ( $tickets->posts as $ticket ) {

			$ticket_status                          = '';
			$woocommerce_events_multiday_status     = '';
			$woocommerce_events_multiday_status_day = '';

			$woocommerce_events_product_id = get_post_meta( $ticket->ID, 'WooCommerceEventsProductID', true );

			if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

				require_once ABSPATH . '/wp-admin/includes/plugin.php';

			}

			if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

				$fooevents_multiday_events              = new Fooevents_Multiday_Events();
				$woocommerce_events_multiday_status     = $fooevents_multiday_events->display_multiday_status_ticket_meta( $ticket->ID, $multiday, $day );
				$woocommerce_events_multiday_status_day = $fooevents_multiday_events->display_multiday_status_ticket_meta_day( $ticket->ID, $multiday, $day );

			}

			if ( empty( $woocommerce_events_multiday_status ) || 'Unpaid' === $ticket_status || 'Canceled' === $ticket_status || 'Cancelled' === $ticket_status ) {

				$ticket_status = get_post_meta( $ticket->ID, 'WooCommerceEventsStatus', true );

			} else {

				$ticket_status = $woocommerce_events_multiday_status;

			}

			$woocommerce_events_variation_id = get_post_meta( $ticket->ID, 'WooCommerceEventsVariationID', true );
			$ticket_variations               = array();

			if ( ! empty( $woocommerce_events_variation_id ) ) {

				$variation_obj = new WC_Product_variation( $woocommerce_events_variation_id );
				$variations    = $variation_obj->get_attribute_summary();
				$variations    = explode( ',', $variations );

				if ( ! empty( $variations ) ) {

					$ticket_variations = array();
					foreach ( $variations as $variation ) {

						$variation                                  = explode( ':', trim( $variation ) );
						$ticket_variations[ trim( $variation[0] ) ] = trim( $variation[1] );

					}
				}
			}

			$bookings_data = array();
			if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

				$bookings_date_term              = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsBookingsDateOverride', true );
				$bookings_slot_term              = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsBookingsSlotOverride', true );
				$bookings_bookingdetails_term    = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsBookingsBookingDetailsOverride', true );
				$woocommerce_events_booking_slot = get_post_meta( $ticket->ID, 'WooCommerceEventsBookingSlot', true );
				$woocommerce_events_booking_date = get_post_meta( $ticket->ID, 'WooCommerceEventsBookingDate', true );

				if ( ! empty( $woocommerce_events_booking_slot ) && ! empty( $woocommerce_events_booking_date ) ) {

					$bookings_data['WooCommerceEventsBookingSlot'] = $woocommerce_events_booking_slot;
					$bookings_data['WooCommerceEventsBookingDate'] = $woocommerce_events_booking_date;

					if ( empty( $bookings_date_term ) ) {

						$bookings_data['WooCommerceEventsBookingsDateTerm'] = __( 'Date', 'fooevents-bookings' );

					} else {

						$bookings_data['WooCommerceEventsBookingsDateTerm'] = $bookings_date_term;

					}

					if ( empty( $bookings_slot_term ) ) {

						$bookings_data['WooCommerceEventsBookingsSlotTerm'] = __( 'Slot', 'fooevents-bookings' );

					} else {

						$bookings_data['WooCommerceEventsBookingsSlotTerm'] = $bookings_slot_term;

					}
				}
			}

			$product_id                        = get_post_meta( $ticket->ID, 'WooCommerceEventsProductID', true );
			$event                             = get_post( $product_id );
			$woocommerce_events_type           = get_post_meta( $product_id, 'WooCommerceEventsType', true );
			$woocommerce_events_date           = get_post_meta( $product_id, 'WooCommerceEventsDate', true );
			$woocommerce_events_start_time     = get_post_meta( $event->ID, 'WooCommerceEventsHour', true ) . ':' . get_post_meta( $event->ID, 'WooCommerceEventsMinutes', true ) . ' ' . get_post_meta( $event->ID, 'WooCommerceEventsPeriod', true );
			$woocommerce_events_start_time_end = get_post_meta( $event->ID, 'WooCommerceEventsHourEnd', true ) . ':' . get_post_meta( $event->ID, 'WooCommerceEventsMinutesEnd', true ) . ' ' . get_post_meta( $event->ID, 'WooCommerceEventsPeriodEnd', true );

			if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

				if ( 'select' === $woocommerce_events_type ) {

					$fooevents_multiday_events = new Fooevents_Multiday_Events();
					$woocommerce_events_date   = $fooevents_multiday_events->get_comma_seperated_select_dates( $product_id );

				}
			}

			$ticket_status_class = '';
			if ( ! empty( $woocommerce_events_multiday_status ) ) {

				$ticket_status_class = preg_replace( '#[ -]+#', '-', strtolower( strip_tags( $woocommerce_events_multiday_status ) ) );
				$ticket_status_class = preg_replace( '/^.*?:-/', '', $ticket_status_class );

			} else {

				$ticket_status_class = preg_replace( '#[ -]+#', '-', strtolower( get_post_meta( $ticket->ID, 'WooCommerceEventsStatus', true ) ) );

			}

			ob_start();

			include $this->config->template_path . 'tickets-data.php';

			$tickets_data .= ob_get_clean();

		}

		include $this->config->template_path . 'tickets.php';

		exit();
	}

	/**
	 * Changes the ticket status from the Express Check-ins page
	 */
	public function change_ticket_status() {

		global $wpdb;
		$table_name = $wpdb->prefix . 'fooevents_check_in';

		/*
		echo '<pre>';
			print_r( $_POST );
		echo '</pre>';
		exit();*/

		$nonce = '';
		if ( isset( $_POST['fooevents-express-check-in-search-nonce'] ) ) {
			$nonce = esc_attr( sanitize_text_field( wp_unslash( $_POST['fooevents-express-check-in-search-nonce'] ) ) );
		}

		if ( ! wp_verify_nonce( $nonce, 'fooevents-express-check-in-search' ) ) {
			die( esc_attr__( 'Security check failed - FooEvents Express Check-ins 0002', 'fooevents-express-check-in' ) );
		}

		$accepted_responses = array( 'reset', 'cancel', 'confirm' );

		$selected = '';
		$value    = '';
		if ( isset( $_POST['value'] ) ) {

			$selected = explode( '-', sanitize_text_field( wp_unslash( $_POST['value'] ) ) );
			$value    = sanitize_text_field( wp_unslash( $_POST['value'] ) );

		}
		$multiday = '';
		if ( isset( $_POST['multiday'] ) ) {

			$multiday = sanitize_text_field( wp_unslash( $_POST['multiday'] ) );

		}

		$day = '';
		if ( isset( $_POST['day'] ) ) {

			$day = sanitize_text_field( wp_unslash( $_POST['day'] ) );

		}

		$timestamp = current_time( 'timestamp' );

		if ( in_array( $selected[4], $accepted_responses, true ) ) {

			$update_value = '';

			if ( 'reset' === $selected[4] ) {

				$update_value = 'Not Checked In';

			} elseif ( 'cancel' === $selected[4] ) {

				$update_value = 'Canceled';

			} elseif ( 'confirm' === $selected[4] ) {

				$update_value = 'Checked In';

			}

			$post_ID = (int) $selected[5];

			if ( ! empty( $update_value ) ) {

				if ( is_numeric( $post_ID ) && $post_ID > 0 ) {

					$ticket_id  = get_post_meta( $post_ID, 'WooCommerceEventsTicketID', true );
					$event_id   = get_post_meta( $post_ID, 'WooCommerceEventsProductID', true );
					$event_type = get_post_meta( $event_id, 'WooCommerceEventsType', true );

					if ( 'true' === $multiday && 'Canceled' !== $update_value && ( 'sequential' === $event_type || 'select' === $event_type ) ) {

						$woocommerce_events_multiday_status = '';

						if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

							require_once ABSPATH . '/wp-admin/includes/plugin.php';

						}

						if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

							$old_multi_day_status = get_post_meta( $post_ID, 'WooCommerceEventsMultidayStatus', true );
							$old_multi_day_status = json_decode( $old_multi_day_status, true );

							if ( empty( $old_multi_day_status ) || $old_multi_day_status[ $day ] !== $update_value ) {

								$fooevents_multiday_events          = new Fooevents_Multiday_Events();
								$woocommerce_events_multiday_status = $fooevents_multiday_events->update_express_check_in_status( $post_ID, $update_value, $multiday, $day );
								update_post_meta( $post_ID, 'WooCommerceEventsStatus', $update_value );

								echo wp_json_encode(
									array(
										'status'   => 'success',
										'ID'       => sanitize_text_field( wp_unslash( $_POST['value'] ) ),
										'ticket'   => $selected[5],
										'message'  => $update_value,
										'ticketID' => $ticket_id,
									)
								);
								exit();

							} else {

								// translators: Placeholder is for ticket ID.
								$error_message = sprintf( __( 'WARNING: #%1$s has already been checked-in for day %2$s.', 'fooevents-express-check-in' ), $ticket_id, $day );
								echo wp_json_encode(
									array(
										'status'         => 'error',
										'status_message' => $error_message,
									)
								);
								exit();

							}
						}
					} else {

						$woocommerce_events_status = get_post_meta( $post_ID, 'WooCommerceEventsStatus', true );

						if ( ( empty( $woocommerce_events_status ) && ! empty( $update_value ) ) || ( ! empty( $woocommerce_events_status ) && $woocommerce_events_status !== $update_value ) ) {

							$wpdb->insert(
								$table_name,
								array(
									'tid'     => $post_ID,
									'eid'     => $event_id,
									'day'     => $day,
									'uid'     => get_current_user_id(),
									'status'  => $update_value,
									'checkin' => $timestamp,
								)
							);

							do_action( 'fooevents_check_in_ticket', array( $post_ID, $update_value, $timestamp ) );
							update_post_meta( $post_ID, 'WooCommerceEventsStatus', $update_value );

							echo wp_json_encode(
								array(
									'status'   => 'success',
									'ID'       => sanitize_text_field( wp_unslash( $_POST['value'] ) ),
									'ticket'   => $selected[5],
									'message'  => $update_value,
									'ticketID' => $ticket_id,
								)
							);
							exit();

						} else {

							// translators: Placeholder is for ticket ID.
							$error_message = sprintf( __( 'WARNING: #%s has already been checked-in.', 'fooevents-express-check-in' ), $ticket_id );
							echo wp_json_encode(
								array(
									'status'         => 'error',
									'status_message' => $error_message,
								)
							);
							exit();

						}
					}
				}
			}
		}

		// translators: Placeholder is for search value.
		$error_message = sprintf( __( 'ERROR: There was an error processing ticket matching %s ', 'fooevents-express-check-in' ), $value );
		echo wp_json_encode(
			array(
				'status'         => 'error',
				'status_message' => $error_message,
			)
		);
		exit();
	}

	/**
	 * Changes the status automatically when auto complete is enabled
	 */
	public function change_ticket_status_auto_complete() {

		global $wpdb;

		$nonce = '';
		if ( isset( $_POST['fooevents-express-check-in-search-nonce'] ) ) {
			$nonce = esc_attr( sanitize_text_field( wp_unslash( $_POST['fooevents-express-check-in-search-nonce'] ) ) );
		}

		if ( ! wp_verify_nonce( $nonce, 'fooevents-express-check-in-search' ) ) {
			die( esc_attr__( 'Security check failed - FooEvents Express Check-ins 0003', 'fooevents-express-check-in' ) );
		}

		$table_name = $wpdb->prefix . 'fooevents_check_in';
		$timestamp  = current_time( 'timestamp' );

		$multiday = '';
		if ( isset( $_POST['multiday'] ) ) {

			$multiday = sanitize_text_field( wp_unslash( $_POST['multiday'] ) );

		}

		$day = '';
		if ( isset( $_POST['day'] ) ) {

			$day = sanitize_text_field( wp_unslash( $_POST['day'] ) );

		}

		$value = '';
		if ( isset( $_POST['value'] ) ) {

			$value = sanitize_text_field( wp_unslash( $_POST['value'] ) );

		}

		$args = array(
			'post_type'      => array( 'event_magic_tickets' ),
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'     => 'WooCommerceEventsTicketID',
					'value'   => $value,
					'compare' => '=',
				),
			),
		);

		$tickets = new WP_Query( $args );
		$count   = (int) $tickets->found_posts;

		if ( 0 === $count ) {

			// translators: Placeholder is for search value.
			$error_message = sprintf( __( 'ERROR: No tickets where found matching #%s for auto check-in', 'fooevents-express-check-in' ), $value );
			echo wp_json_encode(
				array(
					'status'         => 'error',
					'status_message' => $error_message,
				)
			);
			exit();

		} elseif ( $count > 1 ) {

			// translators: Placeholder is for search value.
			$error_message = sprintf( __( 'ERROR: Multiple tickets found matching #%s for auto check-in', 'fooevents-express-check-in' ), $value );
			echo wp_json_encode(
				array(
					'status'         => 'error',
					'status_message' => $error_message,
				)
			);
			exit();

		} elseif ( 1 === $count ) {

			$ticket_final = '';
			foreach ( $tickets->posts as $ticket ) {

				$ticket_final = $ticket;

			}

			$ticket_status = get_post_meta( $ticket_final->ID, 'WooCommerceEventsStatus', true );

			if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

				require_once ABSPATH . '/wp-admin/includes/plugin.php';

			}

			$product_id = get_post_meta( $ticket_final->ID, 'WooCommerceEventsProductID', true );
			$event_type = get_post_meta( $product_id, 'WooCommerceEventsType', true );

			if ( ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) && ( 'sequential' === $event_type || 'select' === $event_type ) ) || ( is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) && ( 'sequential' === $event_type || 'select' === $event_type ) ) ) {

				if ( 'Canceled' === $ticket_status ) {

					// translators: Placeholder is for search value.
					$error_message = sprintf( __( 'ERROR: Unable to check-in #%s ticket has been marked canceled', 'fooevents-express-check-in' ), $value );
					echo wp_json_encode(
						array(
							'status'         => 'error',
							'status_message' => $error_message,
						)
					);
					exit();

				}

				$old_multi_day_status = get_post_meta( $ticket_final->ID, 'WooCommerceEventsMultidayStatus', true );
				$old_multi_day_status = json_decode( $old_multi_day_status, true );

				$update_value = 'Checked In';
				if ( empty( $old_multi_day_status[ $day ] ) || $old_multi_day_status[ $day ] !== $update_value ) {

					$fooevents_multiday_events          = new Fooevents_Multiday_Events();
					$woocommerce_events_multiday_status = $fooevents_multiday_events->update_express_check_in_status( $ticket_final->ID, $update_value, $multiday, $day );
					update_post_meta( $ticket_final->ID, 'WooCommerceEventsStatus', $update_value );

					// translators: Placeholders are for search value and day.
					$status_message = sprintf( __( 'SUCCESS: #%1$s has been checked-in on Day %2$s', 'fooevents-express-check-in' ), $value, $day );
					echo wp_json_encode(
						array(
							'status'         => 'success',
							'ticket'         => $ticket_final->ID,
							'message'        => 'Checked In',
							'status_message' => $status_message . ' <a href="#" class="fooevents-express-check-in-undo" id="fooevents-express-check-in-undo-' . $ticket_final->ID . '">Undo</a>',
						)
					);
					exit();

				} else {

					// translators: Placeholder is for search value.
					$status_message = sprintf( __( 'WARNING: #%s has already been checked-in.', 'fooevents-express-check-in' ), $value );
					echo wp_json_encode(
						array(
							'status'         => 'error',
							'status_message' => $status_message,
						)
					);
					exit();

				}
			}

			if ( 'Checked In' === $ticket_status ) {

				// translators: Placeholder is for search value.
				$status_message = sprintf( __( 'WARNING: #%s has already been checked-in.', 'fooevents-express-check-in' ), $value );
				echo wp_json_encode(
					array(
						'status'         => 'error',
						'status_message' => $status_message,
					)
				);
				exit();

			} elseif ( 'Canceled' === $ticket_status ) {

				// translators: Placeholder is for search value.
				$status_message = sprintf( __( 'ERROR: Unable to check-in #%s ticket has been marked canceled.', 'fooevents-express-check-in' ), $value );
				echo wp_json_encode(
					array(
						'status'         => 'error',
						'status_message' => $status_message,
					)
				);
				exit();

			} elseif ( 'Not Checked In' === $ticket_status ) {

				$event_id = get_post_meta( $ticket_final->ID, 'WooCommerceEventsProductID', true );

				$wpdb->insert(
					$table_name,
					array(
						'tid'     => $ticket_final->ID,
						'eid'     => $event_id,
						'day'     => $day,
						'uid'     => get_current_user_id(),
						'status'  => 'Checked In',
						'checkin' => $timestamp,
					)
				);

				do_action( 'fooevents_check_in_ticket', array( $ticket_final->ID, 'Checked In', $timestamp ) );
				update_post_meta( $ticket_final->ID, 'WooCommerceEventsStatus', 'Checked In' );

				// translators: Placeholder is for search value.
				$status_message = sprintf( __( 'SUCCESS: #%s has been checked-in.', 'fooevents-express-check-in' ), $value );
				echo wp_json_encode(
					array(
						'status'         => 'success',
						'ticket'         => $ticket_final->ID,
						'message'        => 'Checked In',
						'status_message' => $status_message . ' <a href="#" class="fooevents-express-check-in-undo" id="fooevents-express-check-in-undo-' . $ticket_final->ID . '">Undo</a>',
					)
				);
				exit();

			}
		}

		// translators: Placeholder is for search value.
		$status_message = sprintf( __( 'ERROR: Unknown error for #%s.', 'fooevents-express-check-in' ), $value );
		echo wp_json_encode(
			array(
				'status'         => 'error',
				'status_message' => $status_message,
			)
		);
		exit();
	}

	/**
	 * Ticket check-in undo
	 */
	public function undo_check_in() {

		$nonce = '';
		if ( isset( $_POST['fooevents-express-check-in-search-nonce'] ) ) {
			$nonce = esc_attr( sanitize_text_field( wp_unslash( $_POST['fooevents-express-check-in-search-nonce'] ) ) );
		}

		if ( ! wp_verify_nonce( $nonce, 'fooevents-express-check-in-search' ) ) {
			die( esc_attr__( 'Security check failed - FooEvents Express Check-ins 0004', 'fooevents-express-check-in' ) );
		}

		$multiday = '';
		if ( isset( $_POST['multiday'] ) ) {

			$multiday = sanitize_text_field( wp_unslash( $_POST['multiday'] ) );

		}

		$day = '';
		if ( isset( $_POST['day'] ) ) {

			$day = sanitize_text_field( wp_unslash( $_POST['day'] ) );

		}

		$selected = array();
		$value    = '';
		if ( isset( $_POST['value'] ) ) {

			$selected = explode( '-', sanitize_text_field( wp_unslash( $_POST['value'] ) ) );
			$value    = sanitize_text_field( wp_unslash( $_POST['value'] ) );

		}

		$accepted_responses = array( 'undo' );

		if ( in_array( $selected[4], $accepted_responses, true ) ) {

			$post_ID               = (int) $selected[5];
						$ticket_id = get_post_meta( $post_ID, 'WooCommerceEventsTicketID', true );

			if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

				require_once ABSPATH . '/wp-admin/includes/plugin.php';

			}

			if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

				$fooevents_multiday_events = new Fooevents_Multiday_Events();
				$multiday_response         = $fooevents_multiday_events->undo_express_check_in_status_auto_complete( $post_ID, $multiday, $day );

				// translators: Placeholder is for search value.
				$status_message = sprintf( __( 'SUCCESS: #%s checked-in has been undone.', 'fooevents-express-check-in' ), $ticket_id );
				echo wp_json_encode(
					array(
						'status'         => 'success',
						'status_message' => $status_message,
					)
				);
				exit();

			}

			$ticket_status = get_post_meta( $post_ID, 'WooCommerceEventsStatus', true );

			if ( ! empty( $ticket_status ) ) {

				if ( is_numeric( $post_ID ) && $post_ID > 0 ) {

					if ( 'Checked In' === $ticket_status ) {

						$ticket_id = get_post_meta( $post_ID, 'WooCommerceEventsTicketID', true );
						update_post_meta( $post_ID, 'WooCommerceEventsStatus', 'Not Checked In' );

						// translators: Placeholder is for search value.
						$status_message = sprintf( __( 'SUCCESS: #%s checked-in has been undone.', 'fooevents-express-check-in' ), $value );
						echo wp_json_encode(
							array(
								'status'         => 'success',
								'status_message' => $status_message,
							)
						);
						exit();

					}
				}
			}
		}

		exit();
	}
}
