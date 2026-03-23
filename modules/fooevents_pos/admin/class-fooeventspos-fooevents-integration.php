<?php
/**
 * The class that handles the integration functionality for FooEvents
 *
 * @link https://www.fooevents.com
 * @since 1.9.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles the integration functionality for FooEvents.
 *
 * @since 1.9.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */
class FooEventsPOS_FooEvents_Integration {
	/**
	 * REST API
	 *
	 * @since 1.9.0
	 * @var class $class_rest_api The current REST API.
	 */
	private $class_rest_api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.9.0
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'fooeventspos_copy_default_theme' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'fooeventspos_process_meta_box' ) );
		add_action( 'rest_api_init', array( $this, 'fooeventspos_register_rest_api_routes' ) );

		$this->fooeventspos_load_dependencies();
	}

	/**
	 * Load the required dependencies for this class.
	 *
	 * @since 1.9.0
	 */
	private function fooeventspos_load_dependencies() {

		// REST API class.
		require_once plugin_dir_path( __FILE__ ) . 'class-fooeventspos-rest-api.php';

		$this->class_rest_api = new FooEventsPOS_REST_API();
	}

	/**
	 * Copy default POS ticket theme
	 *
	 * @since 1.9.0
	 */
	public function fooeventspos_copy_default_theme() {

		if ( ( is_plugin_active( 'fooevents/fooevents.php' ) || is_plugin_active_for_network( 'fooevents/fooevents.php' ) ) && class_exists( 'FooEvents_Config' ) ) {

			if ( ! function_exists( 'WP_Filesystem' ) ) {

				require_once ABSPATH . '/wp-admin/includes/file.php';

			}

			WP_Filesystem();

			global $wp_filesystem;

			$default_pos_ticket_theme = 'receipt_ticket_theme';

			$fooevents_config = new FooEvents_Config();

			if ( ! file_exists( $fooevents_config->uploads_path . 'themes/' . $default_pos_ticket_theme ) && $wp_filesystem->is_writable( $fooevents_config->uploads_dir_path ) ) {

				$default_pos_ticket_theme_folder = plugin_dir_path( __FILE__ ) . 'templates/' . $default_pos_ticket_theme . '/';

				$this->fooeventspos_xcopy( $default_pos_ticket_theme_folder, $fooevents_config->theme_packs_path . $default_pos_ticket_theme );

			}
		}
	}

	/**
	 * XCOPY function to move templates to new location in uploads directory
	 *
	 * @since 1.9.0
	 * @param string $source source.
	 * @param string $dest destination.
	 * @param int    $permissions file permissions.
	 *
	 * @return bool successful copy.
	 */
	private function fooeventspos_xcopy( $source, $dest, $permissions = 0755 ) {

		if ( ! function_exists( 'WP_Filesystem' ) ) {

			require_once ABSPATH . '/wp-admin/includes/file.php';

		}

		WP_Filesystem();

		global $wp_filesystem;

		if ( is_link( $source ) ) {

			return symlink( readlink( $source ), $dest );

		}

		if ( is_file( $source ) ) {

			return copy( $source, $dest );

		}

		if ( ! is_dir( $dest ) ) {

			$wp_filesystem->mkdir( $dest, $permissions );

		}

		$dir = dir( $source );

		while ( false !== $entry = $dir->read() ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition

			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}

			$this->fooeventspos_xcopy( "$source/$entry", "$dest/$entry", $permissions );
		}

		$dir->close();

		return true;
	}

	/**
	 * Processes the meta box form once the publish / update button is clicked.
	 *
	 * @since 1.9.0
	 * @global object $woocommerce_errors
	 * @param int $product_id The product ID.
	 */
	public function fooeventspos_process_meta_box( $product_id ) {

		global $woocommerce_errors;

		if ( isset( $_POST['_fooeventspos_pos_settings_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_fooeventspos_pos_settings_nonce'] ) ), '_fooeventspos_save_pos_settings_' . $product_id ) ) {

			$wc_product = wc_get_product( $product_id );

			if ( isset( $_POST['WooCommerceEventsPOSTicketTheme'] ) ) {

				$wc_product->update_meta_data( 'WooCommerceEventsPOSTicketTheme', sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPOSTicketTheme'] ) ) );

			}

			if ( isset( $_POST['WooCommerceEventsPOSTicketBarcodeQRWidth'] ) ) {

				$fooevents_ticket_barcode_qr_width = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPOSTicketBarcodeQRWidth'] ) );
				$wc_product->update_meta_data( 'WooCommerceEventsPOSTicketBarcodeQRWidth', $fooevents_ticket_barcode_qr_width );

			} else {

				$wc_product->update_meta_data( 'WooCommerceEventsPOSTicketBarcodeQRWidth', '72' );

			}

			if ( isset( $_POST['WooCommerceEventsPOSEnableTicketEmails'] ) ) {

				$fooevents_enable_ticket_emails = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPOSEnableTicketEmails'] ) );
				$wc_product->update_meta_data( 'WooCommerceEventsPOSEnableTicketEmails', $fooevents_enable_ticket_emails );

			} else {

				$wc_product->update_meta_data( 'WooCommerceEventsPOSEnableTicketEmails', 'off' );

			}

			$wc_product->save();
		}
	}

	/**
	 * Adds ticket data to the order.
	 *
	 * @since 1.9.0
	 * @param array $single_order The single order data.
	 * @param array $order_ticket_ids An array of ticket IDs for the order.
	 */
	public static function fooeventspos_add_order_ticket_data( &$single_order, $order_ticket_ids = array() ) {

		$single_order['otd']  = '';
		$single_order['otda'] = array();

		if ( ( is_plugin_active( 'fooevents/fooevents.php' ) || is_plugin_active_for_network( 'fooevents/fooevents.php' ) ) && class_exists( 'FooEvents_Config' ) && class_exists( 'FooEvents_Mail_Helper' ) && class_exists( 'FooEvents_Ticket_Helper' ) ) {
			if ( ! empty( $order_ticket_ids ) ) {
				$fooevents_config        = new FooEvents_Config();
				$fooevents_mail_helper   = new FooEvents_Mail_Helper( $fooevents_config );
				$fooevents_ticket_helper = new FooEvents_Ticket_Helper( $fooevents_config );

				$sorted_order_tickets = array();

				// Sort tickets into events.
				foreach ( $order_ticket_ids as $order_ticket_id ) {

					$ticket = $fooevents_ticket_helper->get_ticket_data( $order_ticket_id );
					$sorted_order_tickets[ $ticket['WooCommerceEventsProductID'] ][] = $ticket;

				}

				$last_event_id = end( array_keys( $sorted_order_tickets ) );

				foreach ( $sorted_order_tickets as $event_id => $tickets ) {

					$wc_product = wc_get_product( $event_id );

					if ( false === $wc_product ) {
						continue;
					}

					$woocommerce_events_pos_ticket_theme = $wc_product->get_meta( 'WooCommerceEventsPOSTicketTheme', true );

					if ( empty( $woocommerce_events_pos_ticket_theme ) ) {

						$default_pos_ticket_theme = 'receipt_ticket_theme';

						$woocommerce_events_pos_ticket_theme = $fooevents_config->uploads_path . 'themes/' . $default_pos_ticket_theme;

					}

					$header = $fooevents_mail_helper->parse_email_template( $woocommerce_events_pos_ticket_theme . '/header.php', $tickets[0], array() );
					$footer = $fooevents_mail_helper->parse_email_template( $woocommerce_events_pos_ticket_theme . '/footer.php', $tickets[0], array() );

					$fooevents_ticket_barcode_qr_width = $wc_product->get_meta( 'WooCommerceEventsPOSTicketBarcodeQRWidth', true );

					if ( empty( $fooevents_ticket_barcode_qr_width ) ) {

						$fooevents_ticket_barcode_qr_width = '72';

					}

					$ticket_body = '';

					$email_attendee = false;
					$ticket_count   = 1;

					foreach ( $tickets as $ticket ) {

						$ticket['ticketNumber']         = $ticket_count;
						$ticket['ticketTotal']          = count( $order_ticket_ids );
						$ticket['ticketBarcodeQRWidth'] = $fooevents_ticket_barcode_qr_width;

						$body = $fooevents_mail_helper->parse_ticket_template( $woocommerce_events_pos_ticket_theme . '/ticket.php', $ticket );

						$ticket_body .= $body;

						++$ticket_count;

						self::fooeventspos_thermal_receipt_ticket_details( $single_order['otda'], $ticket );
					}

					$ticket_data_output = str_replace( array( "\r", "\n", "\t" ), '', trim( $header . $ticket_body . $footer ) );

					$single_order['otd'] .= $ticket_data_output . ( $event_id !== $last_event_id ? '<div style="page-break-before: always;"></div>' : '' );
				}
			}
		}
	}

	/**
	 * Extract ticket details for use by a thermal receipt printer.
	 *
	 * @param array $order_ticket_data_array The order ticket data array.
	 * @param array $ticket The single ticket array.
	 *
	 * @return array Ticket details array.
	 */
	private static function fooeventspos_thermal_receipt_ticket_details( &$order_ticket_data_array = array(), $ticket = array() ) {
		if ( ! empty( $ticket['ticketNumber'] ) && 1 === (int) $ticket['ticketNumber'] ) {

			// Event title.
			$order_ticket_data_array[] = array(
				'text'   => $ticket['name'],
				'weight' => 'bold',
				'size'   => 'large',
				'align'  => 'left',
			);

			$order_ticket_data_array[] = array(
				'linefeed' => '1',
			);

			// Event date / time.
			if ( 'off' !== $ticket['WooCommerceEventsTicketDisplayDateTime'] ) {
				if ( ( isset( $ticket['WooCommerceEventsBookingSlot'] ) || isset( $ticket['WooCommerceEventsBookingDate'] ) ) && 'off' !== $ticket['WooCommerceEventsTicketDisplayBookings'] ) {
					$order_ticket_data_array[] = array(
						'text'   => $ticket['WooCommerceEventsBookingDate'],
						'weight' => 'normal',
						'size'   => 'normal',
						'align'  => 'left',
					);

					$order_ticket_data_array[] = array(
						'text'   => $ticket['WooCommerceEventsBookingSlot'],
						'weight' => 'normal',
						'size'   => 'normal',
						'align'  => 'left',
					);
				} else {
					$date_text = '';

					if ( ! empty( $ticket['WooCommerceEventsSelectDate'] ) && ! empty( $ticket['WooCommerceEventsSelectDate'][0] ) && 'select' === $ticket['WooCommerceEventsType'] ) {

						$x = 0;

						foreach ( $ticket['WooCommerceEventsSelectDate'] as $select_date ) {
							if ( '' !== $select_date ) {
								if ( $x > 0 ) {
									$date_text .= ', ';
								}

								$date_text .= $select_date;
							}

							++$x;
						}
					} elseif ( ! empty( $ticket['WooCommerceEventsDate'] ) ) {
						$date_text .= $ticket['WooCommerceEventsDate'];

						if ( ! empty( $ticket['WooCommerceEventsEndDate'] ) ) {
							$date_text .= ' - ' . $ticket['WooCommerceEventsEndDate'];
						}
					}

					$order_ticket_data_array[] = array(
						'text'   => $date_text,
						'weight' => 'normal',
						'size'   => 'normal',
						'align'  => 'left',
					);

					$date_text = '';

					if ( 'select' !== $ticket['WooCommerceEventsType'] || 'on' === $ticket['WooCommerceEventsSelectGlobalTime'] ) {
						$date_text .= $ticket['WooCommerceEventsHour'] . ':' . $ticket['WooCommerceEventsMinutes'] . ( ( ! empty( $ticket['WooCommerceEventsPeriod'] ) ) ? $ticket['WooCommerceEventsPeriod'] : '' );
						$date_text .= ( ! empty( $ticket['WooCommerceEventsTimeZone'] ) ) ? ' ' . $ticket['WooCommerceEventsTimeZone'] : '';

						if ( '00' !== $ticket['WooCommerceEventsHourEnd'] ) {
							$date_text .= ' - ' . $ticket['WooCommerceEventsHourEnd'] . ':' . $ticket['WooCommerceEventsMinutesEnd'] . ( ( ! empty( $ticket['WooCommerceEventsEndPeriod'] ) ) ? $ticket['WooCommerceEventsEndPeriod'] : '' );
							$date_text .= ( ! empty( $ticket['WooCommerceEventsTimeZone'] ) ) ? ' ' . $ticket['WooCommerceEventsTimeZone'] : '';
						}
					}

					$order_ticket_data_array[] = array(
						'text'   => $date_text,
						'weight' => 'normal',
						'size'   => 'normal',
						'align'  => 'left',
					);
				}
			}

			// Ticket text.
			if ( ! empty( $ticket['WooCommerceEventsTicketText'] ) ) {
				$ticket_text_lines = explode( "\r\n", $ticket['name'] );

				foreach ( $ticket_text_lines as $ticket_text_line ) {
					$order_ticket_data_array[] = array(
						'text'   => $ticket_text_line,
						'weight' => 'normal',
						'size'   => 'normal',
						'align'  => 'left',
					);
				}

				$order_ticket_data_array[] = array(
					'linefeed' => '1',
				);
			}

			// Location.
			if ( ! empty( $ticket['WooCommerceEventsLocation'] ) ) {
				$order_ticket_data_array[] = array(
					'text'   => __( 'Location', 'woocommerce-events' ),
					'weight' => 'bold',
					'size'   => 'normal',
					'align'  => 'left',
				);

				$order_ticket_data_array[] = array(
					'text'   => $ticket['WooCommerceEventsLocation'],
					'weight' => 'normal',
					'size'   => 'normal',
					'align'  => 'left',
				);

				$order_ticket_data_array[] = array(
					'linefeed' => '1',
				);
			}

			// Directions.
			if ( ! empty( $ticket['WooCommerceEventsDirections'] ) ) {
				$order_ticket_data_array[] = array(
					'text'   => __( 'Directions', 'woocommerce-events' ),
					'weight' => 'bold',
					'size'   => 'normal',
					'align'  => 'left',
				);

				$order_ticket_data_array[] = array(
					'text'   => $ticket['WooCommerceEventsDirections'],
					'weight' => 'normal',
					'size'   => 'normal',
					'align'  => 'left',
				);

				$order_ticket_data_array[] = array(
					'linefeed' => '1',
				);
			}

			// Contact.
			if ( ! empty( $ticket['WooCommerceEventsSupportContact'] ) ) {
				$order_ticket_data_array[] = array(
					'text'   => __( 'Contact us for questions and concerns', 'woocommerce-events' ),
					'weight' => 'bold',
					'size'   => 'normal',
					'align'  => 'left',
				);

				$order_ticket_data_array[] = array(
					'text'   => $ticket['WooCommerceEventsSupportContact'],
					'weight' => 'normal',
					'size'   => 'normal',
					'align'  => 'left',
				);

				$order_ticket_data_array[] = array(
					'linefeed' => '1',
				);
			}

			$order_ticket_data_array[] = array(
				'cut' => '1',
			);
		}

		// Ticket series number.
		$order_ticket_data_array[] = array(
			'text'   => __( 'Ticket', 'woocommerce-events' ) . ' ' . $ticket['ticketNumber'],
			'weight' => 'bold',
			'size'   => 'normal',
			'align'  => 'center',
		);

		$order_ticket_data_array[] = array(
			'linefeed' => '1',
		);

		// Barcode or QR code.
		if ( 'off' !== $ticket['WooCommerceEventsTicketDisplayBarcode'] ) {
			$global_woocommerce_events_enable_qr_code = get_option( 'globalWooCommerceEventsEnableQRCode' );

			$order_ticket_data_array[] = array(
				'code'  => $ticket['WooCommerceEventsTicketID'],
				'type'  => 'yes' === get_option( 'globalWooCommerceEventsEnableQRCode' ) ? 'qr' : 'code128',
				'align' => 'center',
			);
		}

		// Event title.
		$order_ticket_data_array[] = array(
			'text'   => $ticket['name'],
			'weight' => 'bold',
			'size'   => 'large',
			'align'  => 'center',
		);

		// Event date / time.
		if ( 'off' !== $ticket['WooCommerceEventsTicketDisplayDateTime'] ) {
			if ( 'bookings' !== $ticket['WooCommerceEventsType'] ) {

				$ticket_date_time = '';

				if ( ! empty( $ticket['WooCommerceEventsDate'] ) ) {
					$ticket_date_time .= $ticket['WooCommerceEventsDate'];

					if ( ! empty( $ticket['WooCommerceEventsEndDate'] ) ) {
						$ticket_date_time .= ' - ' . $ticket['WooCommerceEventsEndDate'];
					}
				}

				if ( 'select' !== $ticket['WooCommerceEventsType'] || 'on' !== $ticket['WooCommerceEventsSelectGlobalTime'] ) {

					$ticket_date_time .= $ticket['WooCommerceEventsHour'] . ':' . $ticket['WooCommerceEventsMinutes'] . ( ( ! empty( $ticket['WooCommerceEventsPeriod'] ) ) ? $ticket['WooCommerceEventsPeriod'] : '' );
					$ticket_date_time .= ( ! empty( $ticket['WooCommerceEventsTimeZone'] ) ) ? ' ' . $ticket['WooCommerceEventsTimeZone'] : '';

					if ( '00' !== $ticket['WooCommerceEventsHourEnd'] ) {

						$ticket_date_time .= '-' . $ticket['WooCommerceEventsHourEnd'] . ':' . $ticket['WooCommerceEventsMinutesEnd'] . ( ( ! empty( $ticket['WooCommerceEventsEndPeriod'] ) ) ? $ticket['WooCommerceEventsEndPeriod'] : '' );
						$ticket_date_time .= ( ! empty( $ticket['WooCommerceEventsTimeZone'] ) ) ? ' ' . $ticket['WooCommerceEventsTimeZone'] : '';
					}
				}

				$order_ticket_data_array[] = array(
					'text'   => $ticket_date_time,
					'weight' => 'normal',
					'size'   => 'normal',
					'align'  => 'center',
				);

				$order_ticket_data_array[] = array(
					'linefeed' => '1',
				);
			}
		}

		$order_ticket_data_array[] = array(
			'divider' => '1',
		);

		// Ticket number.
		$order_ticket_data_array[] = array(
			'text_left' => __( 'Ticket Number', 'woocommerce-events' ) . ':',
		);

		$order_ticket_data_array[] = array(
			'text_right' => $ticket['WooCommerceEventsTicketID'],
		);

		// Multi-day details.
		if ( ! empty( $ticket['WooCommerceEventsTicketDisplayMultiDay'] ) && 'on' === $ticket['WooCommerceEventsTicketDisplayMultiDay'] ) {
			$x = 1;
			$y = 0;

			foreach ( $ticket['WooCommerceEventsSelectDate'] as $date ) {
				$order_ticket_data_array[] = array(
					'text_left' => $ticket['dayTerm'] . ' ' . $x,
				);

				$date_text = esc_html( $date );

				if ( ! empty( $ticket['WooCommerceEventsSelectDateHour'][ $y ] ) && ! empty( $ticket['WooCommerceEventsSelectDateMinutes'][ $y ] ) ) {
					$date_text .= ' ' . $ticket['WooCommerceEventsSelectDateHour'][ $y ] . ':' . $ticket['WooCommerceEventsSelectDateMinutes'][ $y ] . ( isset( $ticket['WooCommerceEventsSelectDatePeriod'][ $y ] ) ? ' ' . $ticket['WooCommerceEventsSelectDatePeriod'][ $y ] : '' );
				}

				if ( ! empty( $ticket['WooCommerceEventsSelectDateHourEnd'][ $y ] ) && ! empty( $ticket['WooCommerceEventsSelectDateMinutesEnd'][ $y ] ) ) {
					$date_text .= ' - ' . $ticket['WooCommerceEventsSelectDateHourEnd'][ $y ] . ':' . $ticket['WooCommerceEventsSelectDateMinutesEnd'][ $y ] . ( isset( $ticket['WooCommerceEventsSelectDatePeriodEnd'][ $y ] ) ? ' ' . $ticket['WooCommerceEventsSelectDatePeriodEnd'][ $y ] : '' );
				}

				$order_ticket_data_array[] = array(
					'text_right' => $date_text,
				);

				++$x;
				++$y;
			}
		}

		// Booking details.
		if ( isset( $ticket['WooCommerceEventsBookingSlot'] ) || isset( $ticket['WooCommerceEventsBookingDate'] ) ) {
			if ( 'off' !== $ticket['WooCommerceEventsTicketDisplayBookings'] ) {
				$order_ticket_data_array[] = array(
					'text_left' => $ticket['WooCommerceEventsBookingsSlotTerm'],
				);

				$order_ticket_data_array[] = array(
					'text_right' => $ticket['WooCommerceEventsBookingSlot'],
				);

				$order_ticket_data_array[] = array(
					'text_left' => $ticket['WooCommerceEventsBookingsDateTerm'],
				);

				$order_ticket_data_array[] = array(
					'text_right' => $ticket['WooCommerceEventsBookingDate'],
				);
			}
		}

		// Seating.
		if ( ! empty( $ticket['fooevents_seating_options_array'] ) ) {
			$order_ticket_data_array[] = array(
				'text_left' => $ticket['fooevents_seating_options_array']['row_name_label'],
			);

			$order_ticket_data_array[] = array(
				'text_right' => $ticket['fooevents_seating_options_array']['row_name'],
			);

			$order_ticket_data_array[] = array(
				'text_left' => $ticket['fooevents_seating_options_array']['seat_number_label'],
			);

			$order_ticket_data_array[] = array(
				'text_right' => $ticket['fooevents_seating_options_array']['seat_number'],
			);
		}

		// Ticket type.
		if ( ! empty( $ticket['WooCommerceEventsTicketType'] ) ) {
			$order_ticket_data_array[] = array(
				'text_left' => __( 'Ticket Type:', 'woocommerce-events' ),
			);

			$order_ticket_data_array[] = array(
				'text_right' => $ticket['WooCommerceEventsTicketType'],
			);
		}

		// Price.
		if ( 'off' !== $ticket['WooCommerceEventsTicketDisplayPrice'] ) {
			$order_ticket_data_array[] = array(
				'text_left' => __( 'Price:', 'woocommerce-events' ),
			);

			$ticket_price = '';

			if ( ! empty( $ticket['WooCommerceEventsPrice'] ) ) {
				$ticket_price = $ticket['WooCommerceEventsPrice'];
			} elseif ( ! empty( $ticket['price'] ) ) {
				$ticket_price = $ticket['price'];
			}

			$order_ticket_data_array[] = array(
				'text_right' => $ticket_price,
			);
		}

		// Variations.
		if ( ! empty( $ticket['WooCommerceEventsVariations'] ) ) {
			foreach ( $ticket['WooCommerceEventsVariations'] as $variation_name => $variation_value ) {
				if ( 'Ticket Type' !== $variation_name ) {
					$order_ticket_data_array[] = array(
						'text_left' => $variation_name,
					);

					$order_ticket_data_array[] = array(
						'text_right' => $variation_value,
					);
				}
			}
		}

		// Attendee fields.
		if ( 'off' !== $ticket['WooCommerceEventsTicketPurchaserDetails'] ) {

			if ( ! empty( $ticket['WooCommerceEventsAttendeeName'] ) ) {

				$order_ticket_data_array[] = array(
					'text_left' => __( 'Ticket Holder:', 'woocommerce-events' ),
				);

				$order_ticket_data_array[] = array(
					'text_right' => $ticket['WooCommerceEventsAttendeeName'] . ' ' . $ticket['WooCommerceEventsAttendeeLastName'],
				);
			}

			if ( ! empty( $ticket['WooCommerceEventsAttendeeTelephone'] ) ) {
				$order_ticket_data_array[] = array(
					'text_left' => __( 'Telephone Number:', 'woocommerce-events' ),
				);

				$order_ticket_data_array[] = array(
					'text_right' => $ticket['WooCommerceEventsAttendeeTelephone'],
				);
			}

			if ( ! empty( $ticket['WooCommerceEventsAttendeeCompany'] ) ) {
				$order_ticket_data_array[] = array(
					'text_left' => __( 'Company:', 'woocommerce-events' ),
				);

				$order_ticket_data_array[] = array(
					'text_right' => $ticket['WooCommerceEventsAttendeeCompany'],
				);
			}

			if ( ! empty( $ticket['WooCommerceEventsAttendeeDesignation'] ) ) {
				$order_ticket_data_array[] = array(
					'text_left' => __( 'Designation:', 'woocommerce-events' ),
				);

				$order_ticket_data_array[] = array(
					'text_right' => $ticket['WooCommerceEventsAttendeeDesignation'],
				);
			}
		}

		// Custom attendee fields.
		if ( ! empty( $ticket['fooevents_custom_attendee_fields_options_array'] ) && ( isset( $ticket['WooCommerceEventsIncludeCustomAttendeeDetails'] ) && 'off' !== $ticket['WooCommerceEventsIncludeCustomAttendeeDetails'] ) ) {
			foreach ( $ticket['fooevents_custom_attendee_fields_options_array'] as $custom_attendee_fields ) {
				$order_ticket_data_array[] = array(
					'text_left' => $custom_attendee_fields['label'],
				);

				$order_ticket_data_array[] = array(
					'text_right' => $custom_attendee_fields['value'],
				);
			}
		}

		// Zoom information.
		if ( ! empty( $ticket['WooCommerceEventsTicketDisplayZoom'] ) && 'off' !== $ticket['WooCommerceEventsTicketDisplayZoom'] && ! empty( $ticket['WooCommerceEventsZoomText'] ) ) {
			$order_ticket_data_array[] = array(
				'text'   => __( 'Zoom Details', 'woocommerce-events' ),
				'weight' => 'bold',
				'size'   => 'normal',
			);

			$order_ticket_data_array[] = array(
				'text'   => $ticket['WooCommerceEventsZoomText'],
				'weight' => 'normal',
				'size'   => 'normal',
			);
		}

		$order_ticket_data_array[] = array(
			'cut' => '1',
		);

		return $ticket_details;
	}

	/**
	 * Adds ticket stationary data to the order.
	 *
	 * @since 1.9.0
	 * @param array $single_order The single order data.
	 * @param array $order_ticket_ids An array of ticket IDs for the order.
	 */
	public static function fooeventspos_add_order_ticket_stationary_data( &$single_order, $order_ticket_ids = array() ) {

		$single_order['otsd'] = '';

		if ( ( is_plugin_active( 'fooevents/fooevents.php' ) || is_plugin_active_for_network( 'fooevents/fooevents.php' ) ) && class_exists( 'FooEvents_Config' ) && class_exists( 'FooEvents_Woo_Helper' ) && class_exists( 'FooEvents_Ticket_Helper' ) ) {
			if ( ! empty( $order_ticket_ids ) ) {
				$fooevents_config        = new FooEvents_Config();
				$fooevents_woo_helper    = new FooEvents_Woo_Helper( $fooevents_config );
				$fooevents_ticket_helper = new FooEvents_Ticket_Helper( $fooevents_config );

				if ( method_exists( $fooevents_woo_helper, 'woocommerce_events_attendee_badges_params' ) ) {
					$order_event_ids = array();

					foreach ( $order_ticket_ids as $order_ticket_id ) {

						$order_event_ids[] = get_post_meta( $order_ticket_id, 'WooCommerceEventsProductID', true );

					}

					$order_event_ids = array_unique( $order_event_ids );

					foreach ( $order_event_ids as $order_event_id ) {
						ob_start();
						$fooevents_woo_helper->woocommerce_events_attendee_badges_params( $order_event_id, implode( ',', $order_ticket_ids ), $single_order['oid'] );
						$attendee_badge_content = str_replace( array( "\r", "\n", "\t" ), '', trim( ob_get_clean() ) );

						$single_order['otsd'] .= $attendee_badge_content;
					}
				}
			}
		}
	}

	/**
	 * Generate a dropdown of FooEvents ticket themes for use in the printed POS receipt.
	 *
	 * @since 1.9.0
	 * @param WC_Product $wc_product The current product object.
	 *
	 * @return string Ticket theme options.
	 */
	public static function fooeventspos_generate_pos_theme_options( $wc_product ) {

		$fooevents_config = new FooEvents_Config();

		require plugin_dir_path( __FILE__ ) . 'helpers/fooeventspos-phrases-helper.php';

		$woocommerce_events_pos_ticket_theme = $wc_product->get_meta( 'WooCommerceEventsPOSTicketTheme', true );

		if ( empty( $woocommerce_events_pos_ticket_theme ) ) {

			$woocommerce_events_pos_ticket_theme = $fooevents_config->email_template_path;

		}

		$themes = self::fooeventspos_get_pos_ticket_themes();

		ob_start();

		require plugin_dir_path( __FILE__ ) . 'templates/product-fooevents-ticket-theme-options.php';

		$pos_ticket_theme_options = ob_get_clean();

		return $pos_ticket_theme_options;
	}

	/**
	 * Returns an array of valid themes supporting POS tickets
	 *
	 * @since 1.9.0
	 *
	 * @return array Valid themes supporting POS tickets.
	 */
	public static function fooeventspos_get_pos_ticket_themes() {

		$themes = array(
			'pos'  => array(),
			'html' => array(),
		);

		$fooevents_config = new FooEvents_Config();
		$wp_filesystem    = new WP_Filesystem_Direct( true );

		foreach ( new DirectoryIterator( $fooevents_config->theme_packs_path ) as $file ) {

			if ( $file->isDir() && ! $file->isDot() ) {

				$theme_name = $file->getFilename();

				$theme_path = $file->getPath();
				$theme_path = $theme_path . '/' . $theme_name;

				if ( file_exists( $theme_path . '/header.php' ) && file_exists( $theme_path . '/footer.php' ) && file_exists( $theme_path . '/ticket.php' ) && file_exists( $theme_path . '/config.json' ) ) {

					$theme_config_json = $wp_filesystem->get_contents( $theme_path . '/config.json' );
					$theme_config      = json_decode( $theme_config_json, true );

					$temp_theme = array();

					$temp_theme['path'] = $theme_path;
					$theme_url          = $fooevents_config->theme_packs_url . $theme_name;
					$temp_theme['url']  = $theme_url;
					$temp_theme['name'] = $theme_config['name'];

					if ( file_exists( $theme_path . '/preview.png' ) ) {

						$temp_theme['preview'] = $theme_url . '/preview.png';

					} else {

						$temp_theme['preview'] = $fooevents_config->event_plugin_url . 'images/no-preview.png';

					}

					$temp_theme['file_name'] = $file->getFilename();

					if ( isset( $theme_config['supports-pos'] ) && 'true' === $theme_config['supports-pos'] ) {
						$themes['pos'][] = $temp_theme;
					} elseif ( isset( $theme_config['supports-html'] ) && 'true' === $theme_config['supports-html'] ) {
						$themes['html'][] = $temp_theme;
					}
				}
			}
		}

		return $themes;
	}

	/**
	 * Generate ticket options for FooEvents POS.
	 *
	 * @since 1.9.0
	 * @param WC_Product $wc_product The current product object.
	 *
	 * @return string Ticket options.
	 */
	public static function fooeventspos_add_product_pos_tickets_options_tab_options( $wc_product ) {

		require plugin_dir_path( __FILE__ ) . 'helpers/fooeventspos-phrases-helper.php';

		$fooevents_ticket_barcode_qr_width = $wc_product->get_meta( 'WooCommerceEventsPOSTicketBarcodeQRWidth', true );

		$fooevents_enable_ticket_emails = $wc_product->get_meta( 'WooCommerceEventsPOSEnableTicketEmails', true );

		ob_start();

		require plugin_dir_path( __FILE__ ) . 'templates/product-fooevents-ticket-options.php';

		$pos_ticket_options = ob_get_clean();

		return $pos_ticket_options;
	}

	/**
	 * Auto check-in an order's tickets if auto-checkin was enabled in the app.
	 *
	 * @since 1.9.0
	 * @param int  $order_id The order ID.
	 * @param bool $auto_checkin Whether or not the ticket statuses should be set to 'Checked In'.
	 *
	 * @return array Auto-check-in result.
	 */
	public static function fooeventspos_auto_checkin_order_tickets( $order_id, $auto_checkin ) {

		$auto_checkin_result = array( 'message' => 'Not Checked In' );

		if ( true === $auto_checkin ) {
			if ( ( is_plugin_active( 'fooevents/fooevents.php' ) || is_plugin_active_for_network( 'fooevents/fooevents.php' ) ) && class_exists( 'FooEvents_Config' ) ) {

				$wc_order = wc_get_order( $order_id );

				$woocommerce_events_tickets_generated = '' !== $wc_order->get_meta( 'WooCommerceEventsTicketsGenerated', true );

				if ( $woocommerce_events_tickets_generated ) {
					$order_selected_incomplete_statuses = get_option( 'globalFooEventsPOSOrderIncompleteStatuses', array( '' ) );

					if ( empty( $order_selected_incomplete_statuses ) ) {
						$order_selected_incomplete_statuses = array( '' );
					}

					if ( false === in_array( $wc_order->get_status(), $order_selected_incomplete_statuses, true ) ) {
						$fooevents_config = new FooEvents_Config();

						$order_tickets = new WP_Query(
							array(
								'post_type'      => array( 'event_magic_tickets' ),
								'posts_per_page' => -1,
								'fields'         => 'ids',
								'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
									array(
										'key'   => 'WooCommerceEventsOrderID',
										'value' => $order_id,
									),
								),
							)
						);

						$tickets_status = array();

						foreach ( $order_tickets->posts as $order_ticket_id ) {

							$ticket_number = get_post_meta( $order_ticket_id, 'WooCommerceEventsTicketID', true );

							$tickets_status[ (string) $ticket_number ] = 'Checked In';

						}

						$auto_checkin_result = update_ticket_multiple_status( wp_json_encode( $tickets_status ) );
					}
				}
			}
		}

		return $auto_checkin_result;
	}

	/**
	 * Add extra event information such as booking slots or seating information.
	 *
	 * @since 1.9.0
	 * @param int   $event_id The event product post ID.
	 * @param array $event The event array to be amended.
	 */
	public static function fooeventspos_add_event_extra( $event_id, &$event ) {

		$wc_product = wc_get_product( $event_id );

		if ( false === $wc_product ) {
			return;
		}

		$woocommerce_events_type = $wc_product->get_meta( 'WooCommerceEventsType', true );

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

			if ( 'bookings' === $woocommerce_events_type ) {

				$fooevents_bookings = new FooEvents_Bookings();

				$event['ebso'] = $wc_product->get_meta( 'WooCommerceEventsBookingsMethod', true );

				$fooevents_bookings_options_serialized = $wc_product->get_meta( 'fooevents_bookings_options_serialized', true );
				$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
				$fooevents_bookings_options            = $fooevents_bookings->process_booking_options( $fooevents_bookings_options_raw );

				if ( 'dateslot' === $event['ebso'] ) {
					$fooevents_bookings_options = $fooevents_bookings->process_date_slot_bookings_options( $fooevents_bookings_options );
				}

				$event['ebo']        = $fooevents_bookings_options;
				$event['ebo_hashes'] = array_keys( $event['ebo'] );

				foreach ( $event['ebo_hashes'] as $hash ) {
					$event['ebo'][ $hash ]['ebdo_hashes'] = array();

					if ( isset( $event['ebo'][ $hash ]['add_date'] ) ) {
						$event['ebo'][ $hash ]['ebdo_hashes'] = array_keys( $event['ebo'][ $hash ]['add_date'] );
					}
				}
			}
		}

		if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

			if ( 'seating' === $woocommerce_events_type ) {

				$fooevents_seating_options_serialized   = $wc_product->get_meta( 'fooevents_seating_options_serialized', true );
				$fooevents_seats_blocked_serialized     = $wc_product->get_meta( 'fooevents_seats_blocked_serialized', true );
				$fooevents_seats_unavailable_serialized = $wc_product->get_meta( 'fooevents_seats_unavailable_serialized', true );

				$fooevents_seating_options_serialized   = '' === $fooevents_seating_options_serialized ? '{}' : $fooevents_seating_options_serialized;
				$fooevents_seats_blocked_serialized     = '' === $fooevents_seats_blocked_serialized ? '[]' : $fooevents_seats_blocked_serialized;
				$fooevents_seats_unavailable_serialized = '' === $fooevents_seats_unavailable_serialized ? '[]' : $fooevents_seats_unavailable_serialized;

				$event['eso']        = json_decode( $fooevents_seating_options_serialized, true );
				$event['eso_hashes'] = array_keys( $event['eso'] );

				$event['esb'] = json_decode( $fooevents_seats_blocked_serialized, true );
				$event['esu'] = json_decode( $fooevents_seats_unavailable_serialized, true );
			}
		}
	}

	/**
	 * Add extra event-related information to a variation.
	 *
	 * @since 1.9.0
	 * @param int   $variation_id The product variation ID.
	 * @param array $product_variation The product variation array to be amended.
	 */
	public static function fooeventspos_add_variation_extra( $variation_id, &$product_variation ) {

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		$wc_product_variation = wc_get_product( $variation_id );

		if ( false === $wc_product_variation ) {
			return;
		}

		$event_id   = $wc_product_variation->get_parent_id();
		$wc_product = wc_get_product( $event_id );

		if ( false === $wc_product ) {
			return;
		}

		if ( 'Event' === $wc_product->get_meta( 'WooCommerceEventsEvent', true ) ) {
			$event_type = $wc_product->get_meta( 'WooCommerceEventsType', true );

			if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

				if ( 'seating' === $event_type ) {
					$fooevents_variation_seating_required = $wc_product_variation->get_meta( 'fooevents_variation_seating_required', true );

					$product_variation['pvsr'] = 'yes' === $fooevents_variation_seating_required ? '1' : '0';
				}
			}
		}
	}

	/**
	 * Register REST API endpoints with their corresponding callback functions.
	 *
	 * @since 1.9.0
	 */
	public function fooeventspos_register_rest_api_routes() {
		$rest_api_endpoints = array(
			'v' . $this->class_rest_api->current_version => array(
				'check_unavailable_booking_slots' => 'POST',
				'check_unavailable_seats'         => 'POST',
			),
		);

		foreach ( $rest_api_endpoints as $version => $endpoints ) {

			foreach ( $endpoints as $endpoint => $method ) {

				$namespace = $this->class_rest_api->api_namespace . $version;

				$callback = 'fooeventspos_rest_callback_' . $endpoint;

				if ( method_exists( $this, $callback . '_' . $version ) ) {
					$callback .= '_' . $version;

					register_rest_route(
						$namespace,
						'/' . $endpoint,
						array(
							array(
								'methods'             => $method,
								'callback'            => array(
									$this,
									$callback,
								),
								'permission_callback' => function ( $request ) {
									return (bool) $this->class_rest_api->fooeventspos_is_valid_user( $request->get_headers() );
								},
							),
						)
					);
				} else {
					register_rest_route(
						$namespace,
						'/' . $endpoint,
						array(
							array(
								'methods'             => $method,
								'callback'            => array(
									$this,
									$callback,
								),
								'permission_callback' => function ( $request ) {
									return (bool) $this->class_rest_api->fooeventspos_is_valid_user( $request->get_headers() );
								},
							),
						)
					);
				}
			}
		}
	}

	/**
	 * Check bookings availability before completing checkout.
	 *
	 * @since 1.9.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Booking availability output.
	 */
	public function fooeventspos_rest_callback_check_unavailable_booking_slots( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->class_rest_api->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

				require_once ABSPATH . '/wp-admin/includes/plugin.php';

			}

			if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

				$attendee_details = json_decode( stripslashes( $request->get_param( 'param2' ) ), true );

				$output = fooeventspos_do_check_unavailable_booking_slots( $attendee_details );
			}
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Check seat availability before completing checkout.
	 *
	 * @since 1.9.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Seating availability output.
	 */
	public function fooeventspos_rest_callback_check_unavailable_seats( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->class_rest_api->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

				require_once ABSPATH . '/wp-admin/includes/plugin.php';

			}

			if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

				$attendee_details = json_decode( stripslashes( $request->get_param( 'param2' ) ), true );
				$product_ids      = json_decode( stripslashes( $request->get_param( 'param3' ) ), true );

				$output = fooeventspos_do_check_unavailable_seats( $attendee_details, $product_ids );
			}
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Perform a status health check for if the FooEvents plugin is installed and activated.
	 *
	 * @since 1.9.0
	 * @param array $status_outputs The current array of status outputs.
	 * @param int   $issue_count The current amount of issues found.
	 *
	 * @return string Activate plugin link.
	 */
	public static function fooeventspos_check_fooevents_active( &$status_outputs, &$issue_count ) {
		$fooevents_plugin               = 'fooevents/fooevents.php';
		$fooevents_plugin_path          = WP_PLUGIN_DIR . '/' . $fooevents_plugin;
		$fooevents_plugin_exists        = file_exists( $fooevents_plugin_path );
		$fooevents_activate_plugin_link = $fooevents_plugin_exists ? wp_nonce_url( admin_url( 'plugins.php?action=activate&plugin=' . $fooevents_plugin ), 'activate-plugin_' . $fooevents_plugin ) : '';

		if ( false === ( is_plugin_active( $fooevents_plugin ) || is_plugin_active_for_network( $fooevents_plugin ) ) ) {
			require plugin_dir_path( __FILE__ ) . 'helpers/fooeventspos-phrases-helper.php';

			$status_outputs[] = array(
				'type'       => 'notice',
				'title'      => $fooeventspos_phrases['label_notice'],
				'message'    => $fooeventspos_phrases['description_fooevents_plugin'],
				'link_url'   => $fooevents_plugin_exists ? $fooevents_activate_plugin_link : 'https://www.fooevents.com/products/fooevents-for-woocommerce/',
				'link_label' => $fooevents_plugin_exists ? $fooeventspos_phrases['status_activate_link'] : $fooeventspos_phrases['status_check_label_link'],
				'target'     => '_blank',
			);

			++$issue_count;
		}

		return $fooevents_activate_plugin_link;
	}

	/**
	 * Restock booking slot when updating an incomplete order containing bookings events.
	 *
	 * @since 1.9.0
	 * @param int $existing_order_id The ID of the existing incomplete WooCommerce order.
	 */
	public static function fooeventspos_restock_booking_slots( $existing_order_id ) {

		if ( ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) ) {

			$fooevents_bookings = new FooEvents_Bookings();

			$fooevents_bookings->order_cancelled_return_stock( $existing_order_id );

		}
	}
}
