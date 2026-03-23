<?php
/**
 * WooCommerce Checkout helper class
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * WooCommerce Checkout helper class
 */
class FooEvents_Checkout_Helper {

	/**
	 * Configuration array
	 *
	 * @var array $config
	 */
	private $config;

	/**
	 * On class load
	 *
	 * @param array $config config.
	 */
	public function __construct( $config ) {

		$this->config = $config;

		$global_woocommerce_events_attendee_fields_pos = get_option( 'globalWooCommerceEventsAttendeeFieldsPos' );
		$theme_name                                    = wp_get_theme();

		$woocommerce_checkout_position = array(
			'default'           => 'woocommerce_after_order_notes',
			'beforeordernotes'  => 'woocommerce_before_order_notes',
			'afterbillingform'  => 'woocommerce_after_checkout_billing_form',
			'aftershippingform' => 'woocommerce_after_checkout_shipping_form',
		);

		if ( empty( $global_woocommerce_events_attendee_fields_pos ) && 'Divi' === $theme_name ) {

			$global_woocommerce_events_attendee_fields_pos = 'afterbillingform';

		}

		if ( empty( $global_woocommerce_events_attendee_fields_pos ) || 1 === (int) $global_woocommerce_events_attendee_fields_pos ) {

			$global_woocommerce_events_attendee_fields_pos = 'default';

		}

		if ( ! array_key_exists( $global_woocommerce_events_attendee_fields_pos, $woocommerce_checkout_position ) ) {

			$global_woocommerce_events_attendee_fields_pos = 'default';

		}

		add_action( $woocommerce_checkout_position[ $global_woocommerce_events_attendee_fields_pos ], array( $this, 'attendee_checkout' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'attendee_checkout_process' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'woocommerce_events_process' ) );
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( $this, 'woocommerce_events_process_api' ) );
	}

	/**
	 * Displays attendee checkout forms on the checkout page
	 *
	 * @param object $checkout checkout.
	 */
	public function attendee_checkout( $checkout ) {

		$products = WC()->cart->get_cart();
		$events   = $this->get_order_events( $products );

		$x = 1;

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		foreach ( $events as $event => $tickets ) {

			$capture_attendees      = $this->check_tickets_for_capture_attendees( $tickets );
			$capture_attendee_other = $this->check_tickets_for_capture_attendee_other( $tickets );
			$seating_chart          = $this->check_tickets_for_seating_chart( $tickets );
			$custom_fields          = $this->check_tickets_for_custom_attendee_fields( $tickets );
			$booking_options        = $this->check_tickets_for_booking_options( $tickets );

			if ( ! is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) && ! is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {
				$seating_chart = false;
			}

			if ( $capture_attendees || $capture_attendee_other || $seating_chart || $custom_fields || $booking_options ) {

				$event_name = get_the_title( $event );
				echo '<h3 class="fooevents-eventname">' . esc_attr( $event_name ) . '</h3>';

				if ( ! is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) && ! is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {
					$seating_chart = false;
				}

				$y = 1;

				foreach ( $tickets as $ticket ) {

					echo '<div id="fooevents-attendee-' . esc_attr( $y ) . '" class="fooevents-attendee">';

					$woocommerce_events_capture_attendee_details         = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeDetails', true );
					$woocommerce_events_capture_attendee_email           = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeEmail', true );
					$woocommerce_events_capture_attendee_first_name      = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeFirstName', true );
					$woocommerce_events_capture_attendee_last_name       = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeLastName', true );
					$woocommerce_events_capture_attendee_email_address   = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeEmailAddress', true );
					$woocommerce_events_capture_attendee_telephone       = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeTelephone', true );
					$woocommerce_events_capture_attendee_company         = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeCompany', true );
					$woocommerce_events_capture_attendee_designation     = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeDesignation', true );
					$attendee_term                                       = get_post_meta( $ticket['product_id'], 'WooCommerceEventsAttendeeOverride', true );
					$woocommerce_events_hide_bookings_stock_availability = get_post_meta( $ticket['product_id'], 'WooCommerceEventsHideBookingsStockAvailability', true );

					if ( empty( $attendee_term ) ) {

						$attendee_term = get_option( 'globalWooCommerceEventsAttendeeOverride', true );

					}

					if ( empty( $attendee_term ) || 1 == $attendee_term ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison

						$attendee_term = __( 'Attendee', 'woocommerce-events' );

					}

					// translators: Placeholder is for the attendee term and ticket number.
					$attendee_heading = sprintf( __( '%1$s %2$d', 'woocommerce-events' ), $attendee_term, $y );
					$attendee_heading = '<h4 class="fooevents-attendee-number">' . $attendee_heading . '</h4>';

					echo wp_kses_post( $attendee_heading );

					if ( 'on' !== $woocommerce_events_hide_bookings_stock_availability ) {

						echo '<div id="fooevents-checkout-attendee-info-' . esc_attr( $x ) . '-' . esc_attr( $y ) . '" class="fooevents-checkout-attendee-info"></div>';

					}

					$ticket_type = '';
					if ( ! empty( $ticket['attribute_ticket-type'] ) ) {

						$ticket_type = ' - ' . $ticket['attribute_ticket-type'];

					}

					if ( ! empty( $ticket['attribute_pa_ticket-type'] ) ) {

						$ticket_type = ' - ' . $ticket['attribute_pa_ticket-type'];

					}

					if ( ! empty( $ticket['variations'] ) ) {

						$variations    = array();
						
						$ticket_vars = array();
						foreach ( $ticket['variations'] as $variation_name => $variation_value ) {

							$variation_name_output = str_replace( 'attribute_', '', $variation_name );
							$variation_name_output = str_replace( 'pa_', '', $variation_name_output );
							$variation_name_output = str_replace( '_', ' ', $variation_name_output );
							$variation_name_output = str_replace( '-', ' ', $variation_name_output );
							$variation_name_output = str_replace( 'Pa_', '', $variation_name_output );
							$variation_name_output = ucwords( $variation_name_output );
							$variation_name_output = urldecode( $variation_name_output );

							$variation_value_output = str_replace( '_', ' ', $variation_value );
							$variation_value_output = str_replace( '-', ' ', $variation_value_output );
							$variation_value_output = ucwords( $variation_value_output );

							$variations[ $variation_name_output ] = $variation_value_output;

						}

						foreach ( $variations as $variation_label => $variation_value ) {

							echo '<div class="fooevents-variation-desc"><p><strong>' . esc_html( trim( $variation_label ) ) . ':</strong> ' . esc_html( trim( $variation_value ) ) . '</p></div>';

						}
					}

					if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

						$woocommerce_events_type = get_post_meta( $ticket['product_id'], 'WooCommerceEventsType', true );

						if ( 'bookings' === $woocommerce_events_type ) {

							$fooevents_bookings = new FooEvents_Bookings();
							$fooevents_bookings->output_booking_fields( $ticket['product_id'], $x, $y, $ticket, $checkout );

						}
					}

					$global_woocommerce_events_add_copy_purchaser_details = get_option( 'globalWooCommerceEventsAddCopyPurchaserDetails', true );
					$global_woocommerce_use_place_holders                 = get_option( 'globalWooCommerceUsePlaceHolders', true );
					$woocommerce_events_copy_override                     = get_option( 'WooCommerceEventsCopyOverride', true );
					$copy_purchaser_details                               = get_option( 'globalWooCommerceEventsAddCopyPurchaserDetails', true );
					$copy_text = '';

					if ( empty( $woocommerce_events_copy_override ) || 1 == $woocommerce_events_copy_override ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison

						$copy_text = __( 'Copy', 'woocommerce-events' );

					} else {

						$copy_text = $woocommerce_events_copy_override;

					}

					if ( 'yes' === $global_woocommerce_events_add_copy_purchaser_details || 'icon' === $global_woocommerce_events_add_copy_purchaser_details ) {

						echo '<p><a href="javascript:void(0)" class="dashicons dashicons-admin-page fooevents-copy-from-purchaser" title="' . esc_attr__( 'Copy purchaser\'s details', 'woocommerce-events' ) . '"></a></p>';

					} elseif ( 'text' === $global_woocommerce_events_add_copy_purchaser_details ) {

						echo '<p><a href="javascript:void(0)" class="fooevents-copy-from-purchaser" title="' . esc_attr__( 'Copy purchaser\'s details', 'woocommerce-events' ) . '">' . esc_attr( $copy_text ) . '</a></p>';

					} elseif ( 'textandicon' === $global_woocommerce_events_add_copy_purchaser_details ) {

						echo '<p><a href="javascript:void(0)" class="dashicons dashicons-admin-page fooevents-copy-from-purchaser" title="' . esc_attr__( 'Copy purchaser\'s details', 'woocommerce-events' ) . '"></a><a href="javascript:void(0)" class="fooevents-copy-from-purchaser" title="' . esc_attr__( 'Copy purchaser\'s details', 'woocommerce-events' ) . '">' . esc_attr( $copy_text ) . '</a></p>';

					}

					if ( 'on' === $woocommerce_events_capture_attendee_details ) {

						$first_name_label = __( 'First Name', 'woocommerce-events' );
						$field_identifier = $ticket['product_id'] . '_attendee_' . $x . '__' . $y;

						$first_name_params = array(
							'type'        => 'text',
							'class'       => array( 'attendee-class form-row-wide fooevents-attendee-first-name' ),
							'label'       => $first_name_label,
							'placeholder' => '',
							'required'    => true,
							'id'          => 'field_' . $field_identifier,
						);

						if ( 'yes' === $global_woocommerce_use_place_holders ) {

							$first_name_params['placeholder'] = $first_name_label;

						}

						woocommerce_form_field( $field_identifier, $first_name_params, $checkout->get_value( $ticket['product_id'] . '_attendee_' . $x . '__' . $y ) );

						$last_name_label  = __( 'Last Name', 'woocommerce-events' );
						$field_identifier = $ticket['product_id'] . '_attendeelastname_' . $x . '__' . $y;

						$last_name_params = array(
							'type'        => 'text',
							'class'       => array( 'attendee-class form-row-wide fooevents-attendee-last-name' ),
							'label'       => $last_name_label,
							'placeholder' => '',
							'required'    => true,
							'id'          => 'field_' . $field_identifier,
						);

						if ( 'yes' === $global_woocommerce_use_place_holders ) {

							$last_name_params['placeholder'] = $last_name_label;

						}

						woocommerce_form_field( $field_identifier, $last_name_params, $checkout->get_value( $ticket['product_id'] . '_attendeelastname_' . $x . '__' . $y ) );

					}

					if ( ( 'on' === $woocommerce_events_capture_attendee_details && '' === $woocommerce_events_capture_attendee_email ) || 'on' === $woocommerce_events_capture_attendee_email ) {

							$email_label      = __( 'Email', 'woocommerce-events' );
							$field_identifier = $ticket['product_id'] . '_attendeeemail_' . $x . '__' . $y;

						if ( 'autocopyhideemail' === $copy_purchaser_details ) {

							$email_label = '';

						}

						$email_params = array(
							'type'        => 'text',
							'class'       => array( 'attendee-class form-row-wide fooevents-attendee-email' ),
							'label'       => $email_label,
							'placeholder' => '',
							'required'    => true,
							'id'          => 'field_' . $field_identifier,
						);

						if ( 'autocopyhideemail' === $copy_purchaser_details ) {

								$email_params['type'] = 'hidden';

						}

						if ( 'yes' === $global_woocommerce_use_place_holders ) {

							$email_params['placeholder'] = $email_label;

						}

						woocommerce_form_field( $field_identifier, $email_params, $checkout->get_value( $ticket['product_id'] . '_attendeeemail_' . $x . '__' . $y ) );

					}

					if ( 'on' === $woocommerce_events_capture_attendee_telephone ) {

						$telephone_label  = __( 'Telephone', 'woocommerce-events' );
						$field_identifier = $ticket['product_id'] . '_attendeetelephone_' . $x . '__' . $y;

						$telephone_params = array(
							'type'        => 'text',
							'class'       => array( 'attendee-class form-row-wide fooevents-attendee-telephone' ),
							'label'       => $telephone_label,
							'placeholder' => '',
							'required'    => true,
							'id'          => 'field_' . $field_identifier,
						);

						if ( 'yes' === $global_woocommerce_use_place_holders ) {

							$telephone_params['placeholder'] = $telephone_label;

						}

						woocommerce_form_field( $field_identifier, $telephone_params, $checkout->get_value( $ticket['product_id'] . '_attendeetelephone_' . $x . '__' . $y ) );

					}

					if ( 'on' === $woocommerce_events_capture_attendee_company ) {

						$company_label    = __( 'Company', 'woocommerce-events' );
						$field_identifier = $ticket['product_id'] . '_attendeecompany_' . $x . '__' . $y;

						$company_params = array(
							'type'        => 'text',
							'class'       => array( 'attendee-class form-row-wide fooevents-attendee-company' ),
							'label'       => $company_label,
							'placeholder' => '',
							'required'    => true,
							'id'          => 'field_' . $field_identifier,
						);

						if ( 'yes' === $global_woocommerce_use_place_holders ) {

							$company_params['placeholder'] = $company_label;

						}

						woocommerce_form_field( $field_identifier, $company_params, $checkout->get_value( $ticket['product_id'] . '_attendeecompany_' . $x . '__' . $y ) );

					}

					if ( 'on' === $woocommerce_events_capture_attendee_designation ) {

						$designation_label = __( 'Designation', 'woocommerce-events' );
						$field_identifier  = $ticket['product_id'] . '_attendeedesignation_' . $x . '__' . $y;

						$designation_params = array(
							'type'        => 'text',
							'class'       => array( 'attendee-class form-row-wide' ),
							'label'       => $designation_label,
							'placeholder' => '',
							'required'    => true,
							'id'          => 'field_' . $field_identifier,
						);

						if ( 'yes' === $global_woocommerce_use_place_holders ) {

							$designation_params['placeholder'] = $designation_label;

						}

						woocommerce_form_field( $field_identifier, $designation_params, $checkout->get_value( $ticket['product_id'] . '_attendeedesignation_' . $x . '__' . $y ) );

					}

					if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

						$fooevents_custom_attendee_fields = new Fooevents_Custom_Attendee_Fields();
						$fooevents_custom_attendee_fields->output_attendee_fields( $ticket['product_id'], $x, $y, $ticket, $checkout );

					}

					if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

						$woocommerce_events_type = get_post_meta( $ticket['product_id'], 'WooCommerceEventsType', true );

						if ( 'seating' === $woocommerce_events_type ) {

							$fooevents_seating = new Fooevents_Seating();
							$fooevents_seating->output_seating_fields( $ticket['product_id'], $x, $y, $ticket, $checkout, $tickets );

						}
					}

					++$y;

					echo '</div>';

				}
			}

			++$x;

		}
	}

	/**
	 * Check if attendee details should be captured
	 *
	 * @param array $tickets tickets.
	 * @return bool
	 */
	public function check_tickets_for_capture_attendees( $tickets ) {

		foreach ( $tickets as $ticket ) {

			$woocommerce_events_capture_attendee_details = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeDetails', true );

			if ( 'on' === $woocommerce_events_capture_attendee_details ) {

				return true;

			}
		}

		return false;
	}

	/**
	 * Check if attendee email should be captured
	 *
	 * @param array $tickets tickets.
	 * @return bool
	 */
	public function check_tickets_for_capture_attendee_other( $tickets ) {

		foreach ( $tickets as $ticket ) {

			$woocommerce_events_capture_attendee_email = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeEmail', true );

			if ( 'on' === $woocommerce_events_capture_attendee_email ) {

				return true;

			}

			$woocommerce_events_capture_attendee_telephone = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeTelephone', true );

			if ( 'on' === $woocommerce_events_capture_attendee_telephone ) {

				return true;

			}

			$woocommerce_events_capture_attendee_company = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeCompany', true );

			if ( 'on' === $woocommerce_events_capture_attendee_company ) {

				return true;

			}

			$woocommerce_events_capture_attendee_designation = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeDesignation', true );

			if ( 'on' === $woocommerce_events_capture_attendee_designation ) {

				return true;

			}
		}

		return false;
	}

	/**
	 * Checks if there are booking options available to event
	 *
	 * @param array $tickets tickets.
	 * @return bool
	 */
	public function check_tickets_for_booking_options( $tickets ) {

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

			foreach ( $tickets as $ticket ) {

				$woocommerce_events_booking_options = get_post_meta( $ticket['product_id'], 'fooevents_bookings_options_serialized', true );
				$woocommerce_events_type            = get_post_meta( $ticket['product_id'], 'WooCommerceEventsType', true );

				if ( '{}' === $woocommerce_events_booking_options ) {

					$woocommerce_events_booking_options = '';

				}

				if ( ! empty( $woocommerce_events_booking_options ) && 'bookings' === $woocommerce_events_type ) {

					return true;

				}
			}
		}

		return false;
	}

	/**
	 * Checks if there is associated seating chart
	 *
	 * @param array $tickets tickets.
	 * @return bool
	 */
	public function check_tickets_for_seating_chart( $tickets ) {

		foreach ( $tickets as $ticket ) {

			$woocommerce_events_seating_chart = get_post_meta( $ticket['product_id'], 'fooevents_seating_options_serialized', true );
			$woocommerce_events_type          = get_post_meta( $ticket['product_id'], 'WooCommerceEventsType', true );

			if ( '{}' === $woocommerce_events_seating_chart ) {

				$woocommerce_events_seating_chart = '';

			}

			if ( ! empty( $woocommerce_events_seating_chart ) && 'seating' === $woocommerce_events_type ) {

				return true;

			}
		}

		return false;
	}

	/**
	 * Checks if there is associated seating chart
	 *
	 * @param array $tickets tickets.
	 * @return bool
	 */
	public function check_tickets_for_custom_attendee_fields( $tickets ) {

		if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

			foreach ( $tickets as $ticket ) {

				$woocommerce_events_custom_fields = get_post_meta( $ticket['product_id'], 'fooevents_custom_attendee_fields_options_serialized', true );

				if ( '{}' === $woocommerce_events_custom_fields ) {

					$woocommerce_events_custom_fields = '';

				}

				if ( ! empty( $woocommerce_events_custom_fields ) ) {

					return true;

				}
			}
		}

		return false;
	}

	/**
	 * Checks if product has custom attendee fields
	 *
	 * @param array $product_id product ID.
	 * @return bool
	 */
	public function check_product_for_custom_attendee_fields( $product_id ) {

		$woocommerce_events_custom_fields = get_post_meta( $product_id, 'fooevents_custom_attendee_fields_options_serialized', true );

		if ( '{}' === $woocommerce_events_custom_fields ) {

			$woocommerce_events_custom_fields = '';

		}

		if ( ! empty( $woocommerce_events_custom_fields ) ) {

			return true;

		}

		return false;
	}

	/**
	 * Processes the attendee details on Checkout
	 */
	public function attendee_checkout_process() {

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		$products = WC()->cart->get_cart();
		$events   = $this->get_order_events( $products );
		$x        = 1;
		foreach ( $events as $event => $tickets ) {

			if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

				$fooevents_bookings = new FooEvents_Bookings();
				$fooevents_bookings->check_availablity_for_all_attendees( $event, $tickets );

			}

			$event_title = get_the_title( $event );

			$unique_emails = array();
			$y             = 1;
			foreach ( $tickets as $ticket ) {

				$woocommerce_events_capture_attendee_details     = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeDetails', true );
				$woocommerce_events_capture_attendee_email       = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeEmail', true );
				$woocommerce_events_capture_attendee_telephone   = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeTelephone', true );
				$woocommerce_events_capture_attendee_company     = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeCompany', true );
				$woocommerce_events_capture_attendee_designation = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeDesignation', true );
				$woocommerce_events_unique_email                 = get_post_meta( $ticket['product_id'], 'WooCommerceEventsUniqueEmail', true );

				if ( 'on' === $woocommerce_events_capture_attendee_details ) {

					if ( ! isset( $_POST[ $ticket['product_id'] . '_attendee_' . $x . '__' . $y ] ) || empty( $_POST[ $ticket['product_id'] . '_attendee_' . $x . '__' . $y ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

						// translators: Placeholder is for the event name and ticket number.
						$notice = sprintf( __( 'Name is required for %1$s attendee %2$d', 'woocommerce-events' ), $event_title, $y );
						wc_add_notice( $notice, 'error' );

					}

					if ( ! isset( $_POST[ $ticket['product_id'] . '_attendeelastname_' . $x . '__' . $y ] ) || empty( $_POST[ $ticket['product_id'] . '_attendeelastname_' . $x . '__' . $y ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

						// translators: Placeholder is for the event name and ticket number.
						$notice = sprintf( __( 'Last name is required for %1$s attendee %2$d', 'woocommerce-events' ), $event_title, $y );
						wc_add_notice( $notice, 'error' );

					}
				}

				if ( ( 'on' === $woocommerce_events_capture_attendee_details && '' === $woocommerce_events_capture_attendee_email ) || 'on' === $woocommerce_events_capture_attendee_email ) {

					$attendee_email = '';
					if ( isset( $_POST[ $ticket['product_id'] . '_attendeeemail_' . $x . '__' . $y ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

						$attendee_email = sanitize_text_field( wp_unslash( $_POST[ $ticket['product_id'] . '_attendeeemail_' . $x . '__' . $y ] ) ); // phpcs:ignore WordPress.Security.NonceVerification

					}

					if ( ! $attendee_email ) {

						// translators: Placeholder is for the event name and ticket number.
						$notice = sprintf( __( 'Email is required for %1$s attendee %2$d', 'woocommerce-events' ), $event_title, $y );
						wc_add_notice( $notice, 'error' );

					} elseif ( 'on' === $woocommerce_events_unique_email ) {

							$unique_emails[] = strtolower( trim( $attendee_email ) );
					}

					if ( ! $this->is_email_valid( $attendee_email ) ) {

						// translators: Placeholder is for the event name and ticket number.
						$notice = sprintf( __( 'Email is not valid for %1$s attendee %2$d', 'woocommerce-events' ), $event_title, $y );
						wc_add_notice( $notice, 'error' );

					}
				}

				if ( 'on' === $woocommerce_events_capture_attendee_telephone ) {
					if ( isset( $_POST[ $ticket['product_id'] . '_attendeetelephone_' . $x . '__' . $y ] ) && empty( $_POST[ $ticket['product_id'] . '_attendeetelephone_' . $x . '__' . $y ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

						// translators: Placeholder is for the event name and ticket number.
						$notice = sprintf( __( 'Telephone is required for %1$s attendee %2$d', 'woocommerce-events' ), $event_title, $y );
						wc_add_notice( $notice, 'error' );

					}
				}

				if ( 'on' === $woocommerce_events_capture_attendee_company ) {
					if ( ! isset( $_POST[ $ticket['product_id'] . '_attendeecompany_' . $x . '__' . $y ] ) || empty( $_POST[ $ticket['product_id'] . '_attendeecompany_' . $x . '__' . $y ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

						// translators: Placeholder is for the uploads path.
						$notice = sprintf( __( 'Company is required for %1$s attendee %2$d', 'woocommerce-events' ), $event_title, $y );
						wc_add_notice( $notice, 'error' );

					}
				}

				if ( 'on' === $woocommerce_events_capture_attendee_designation ) {
					if ( ! isset( $_POST[ $ticket['product_id'] . '_attendeedesignation_' . $x . '__' . $y ] ) || empty( $_POST[ $ticket['product_id'] . '_attendeedesignation_' . $x . '__' . $y ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

						// translators: Placeholder is for the event name and ticket number.
						$notice = sprintf( __( 'Designation is required for %1$s attendee %2$d', 'woocommerce-events' ), $event_title, $y );
						wc_add_notice( $notice, 'error' );

					}
				}

				if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

					require_once ABSPATH . '/wp-admin/includes/plugin.php';

				}

				if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

					$fooevents_custom_attendee_fields = new Fooevents_Custom_Attendee_Fields();
					$fooevents_custom_attendee_fields->validate_custom_fields( $ticket, $event, $x, $y );

				}

				if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

					$woocommerce_events_type = get_post_meta( $ticket['product_id'], 'WooCommerceEventsType', true );

					if ( 'seating' === $woocommerce_events_type ) {

						$fooevents_seating = new Fooevents_Seating();
						$fooevents_seating->check_required_fields( $ticket, $event, $x, $y );
						$fooevents_seating->check_required_field_availability( $ticket['product_id'], $event, $x, $y );

					}
				}

				if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {
					// echo "YES";exit();
					$fooevents_bookings = new FooEvents_Bookings();
					$fooevents_bookings->check_required_fields( $ticket, $event, $x, $y );
					$fooevents_bookings->check_booking_availability( $ticket['product_id'], $event, $x, $y );

				}

				++$y;

			}

			if ( ! empty( $unique_emails ) ) {

				if ( count( $unique_emails ) !== count( array_unique( $unique_emails ) ) ) {

					// translators: Placeholder is for the event name.
					$notice = sprintf( __( 'All attendee email addresses that are used to register for %s must be unique', 'woocommerce-events' ), $event_title );
					wc_add_notice( $notice, 'error' );

				}
			}

			++$x;

		}
	}

	/**
	 * Creates tickets and assigns attendees
	 *
	 * @param int $order_id order ID.
	 */
	public function woocommerce_events_process( $order_id ) {

		set_time_limit( 0 );

		$products = WC()->cart->get_cart();

		$events = $this->get_order_events( $products );

		$order         = wc_get_order( $order_id );
		$total_tickets = array();
		$order_tickets = array();
		$x             = 1;
		foreach ( $events as $event => $tickets ) {

			$y = 1;
			foreach ( $tickets as $ticket ) {

				$woocommerce_events_capture_attendee_details     = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeDetails', true );
				$woocommerce_events_capture_attendee_email       = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeEmail', true );
				$woocommerce_events_capture_attendee_telephone   = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeTelephone', true );
				$woocommerce_events_capture_attendee_company     = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeCompany', true );
				$woocommerce_events_capture_attendee_designation = get_post_meta( $ticket['product_id'], 'WooCommerceEventsCaptureAttendeeDesignation', true );

				$pos_order = 'foosales_app' === $order->get_meta( '_foosales_order_source', true ) || 'fooeventspos_app' === $order->get_meta( '_fooeventspos_order_source', true );

				$woocommerce_events_pos_attendee_details     = get_post_meta( $ticket['product_id'], 'WooCommerceEventsPOSAttendeeDetails', true );
				$woocommerce_events_pos_attendee_email       = get_post_meta( $ticket['product_id'], 'WooCommerceEventsPOSAttendeeEmail', true );
				$woocommerce_events_pos_attendee_telephone   = get_post_meta( $ticket['product_id'], 'WooCommerceEventsPOSAttendeeTelephone', true );
				$woocommerce_events_pos_attendee_company     = get_post_meta( $ticket['product_id'], 'WooCommerceEventsPOSAttendeeCompany', true );
				$woocommerce_events_pos_attendee_designation = get_post_meta( $ticket['product_id'], 'WooCommerceEventsPOSAttendeeDesignation', true );

				$customer = $order->get_user_id();

				$customer_details = array(
					'customerID' => $customer,
				);

				if ( empty( $customer_details['customerID'] ) ) {

					$customer_details['customerID'] = 0;

				}

				if ( empty( $ticket['variations'] ) ) {

					$ticket['variations'] = '';

				}

				$attendee_name        = '';
				$attendee_last_name   = '';
				$attendee_email       = '';
				$attendee_telephone   = '';
				$attendee_company     = '';
				$attendee_designation = '';

				if ( 'on' === $woocommerce_events_capture_attendee_details || ( $pos_order && 'hide' !== $woocommerce_events_pos_attendee_details && '' !== $woocommerce_events_pos_attendee_details ) || 'on' === $woocommerce_events_capture_attendee_email || ( $pos_order && 'hide' !== $woocommerce_events_pos_attendee_email && '' !== $woocommerce_events_pos_attendee_email ) ) {

					if ( 'on' === $woocommerce_events_capture_attendee_details || ( $pos_order && 'hide' !== $woocommerce_events_pos_attendee_details && '' !== $woocommerce_events_pos_attendee_details ) ) {

						if ( isset( $_POST[ $ticket['product_id'] . '_attendee_' . $x . '__' . $y ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

							$attendee_name = sanitize_text_field( wp_unslash( $_POST[ $ticket['product_id'] . '_attendee_' . $x . '__' . $y ] ) ); // phpcs:ignore WordPress.Security.NonceVerification

						}

						if ( isset( $_POST[ $ticket['product_id'] . '_attendeelastname_' . $x . '__' . $y ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

							$attendee_last_name = sanitize_text_field( wp_unslash( $_POST[ $ticket['product_id'] . '_attendeelastname_' . $x . '__' . $y ] ) ); // phpcs:ignore WordPress.Security.NonceVerification

						}
					}

					if ( ( 'on' === $woocommerce_events_capture_attendee_details && '' === $woocommerce_events_capture_attendee_email ) || 'on' === $woocommerce_events_capture_attendee_email || ( $pos_order && 'hide' !== $woocommerce_events_pos_attendee_email && '' !== $woocommerce_events_pos_attendee_email ) ) {

						if ( isset( $_POST[ $ticket['product_id'] . '_attendeeemail_' . $x . '__' . $y ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

							$attendee_email = sanitize_text_field( wp_unslash( $_POST[ $ticket['product_id'] . '_attendeeemail_' . $x . '__' . $y ] ) ); // phpcs:ignore WordPress.Security.NonceVerification

						}
					}
				}

				if ( $pos_order ) {

					if ( 0 === (int) $customer_details['customerID'] && '' === $attendee_name && '' === $attendee_last_name ) {

						$attendee_name = __( 'POS', 'woocommerce-events' );

						$attendee_last_name = get_post_meta( $ticket['product_id'], 'WooCommerceEventsAttendeeOverride', true );

						if ( '' === $attendee_last_name ) {

							$attendee_last_name = get_option( 'globalWooCommerceEventsAttendeeOverride', true );

							if ( '' === $attendee_last_name ) {

								$attendee_last_name = __( 'Attendee', 'woocommerce-events' );

							}
						}
					}
				}

				if ( ( 'on' === $woocommerce_events_capture_attendee_telephone || ( $pos_order && 'hide' !== $woocommerce_events_pos_attendee_telephone && '' !== $woocommerce_events_pos_attendee_telephone ) ) && isset( $_POST[ $ticket['product_id'] . '_attendeetelephone_' . $x . '__' . $y ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

					$attendee_telephone = sanitize_text_field( wp_unslash( $_POST[ $ticket['product_id'] . '_attendeetelephone_' . $x . '__' . $y ] ) ); // phpcs:ignore WordPress.Security.NonceVerification

				}

				if ( ( 'on' === $woocommerce_events_capture_attendee_company || ( $pos_order && 'hide' !== $woocommerce_events_pos_attendee_company && '' !== $woocommerce_events_pos_attendee_company ) ) && isset( $_POST[ $ticket['product_id'] . '_attendeecompany_' . $x . '__' . $y ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

					$attendee_company = sanitize_text_field( wp_unslash( $_POST[ $ticket['product_id'] . '_attendeecompany_' . $x . '__' . $y ] ) ); // phpcs:ignore WordPress.Security.NonceVerification

				}

				if ( ( 'on' === $woocommerce_events_capture_attendee_designation || ( $pos_order && 'hide' !== $woocommerce_events_pos_attendee_designation && '' !== $woocommerce_events_pos_attendee_designation ) ) && isset( $_POST[ $ticket['product_id'] . '_attendeedesignation_' . $x . '__' . $y ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

					$attendee_designation = sanitize_text_field( wp_unslash( $_POST[ $ticket['product_id'] . '_attendeedesignation_' . $x . '__' . $y ] ) ); // phpcs:ignore WordPress.Security.NonceVerification

				}

				if ( empty( $ticket['variation_id'] ) ) {

					$ticket['variation_id'] = '';

				}

				// create ticket.
				$order_tickets[ $x ][ $y ] = $this->create_order_ticket( $customer_details['customerID'], $ticket['product_id'], $order_id, $ticket['attribute_ticket-type'], $ticket['variations'], $ticket['variation_id'], $ticket['price'], $x, $y, $attendee_name, $attendee_last_name, $attendee_email, $attendee_telephone, $attendee_company, $attendee_designation, $ticket );

				if ( empty( $ticket['product_id'] ) ) {

					$total_tickets[ $ticket['product_id'] ] = 1;

				} elseif ( isset( $total_tickets[ $ticket['product_id'] ] ) ) {

						++$total_tickets[ $ticket['product_id'] ];

				} else {

					$total_tickets[ $ticket['product_id'] ] = 1;
				}

				++$y;

			}

			++$x;

		}

		$order->update_meta_data( 'WooCommerceEventsOrderTickets', $order_tickets );
		$order->update_meta_data( 'WooCommerceEventsTicketsPurchased', $total_tickets );
		$order->save();
	}

	/**
	 * Cater for ticket generation on checkout with WooCommerce checkout block
	 *
	 * @param object $order order.
	 */
	public function woocommerce_events_process_api( $order ) {

		$this->woocommerce_events_process( $order->get_id() );
	}

	/**
	 * Checks a string for valid email address
	 *
	 * @param string $email email.
	 * @return bool
	 */
	private function is_email_valid( $email ) {

		return filter_var( $email, FILTER_VALIDATE_EMAIL )
			&& preg_match( '/@.+\./', $email );
	}

	/**
	 * Get's an orders events
	 *
	 * @param object $woocommerce woocommerce object.
	 * @return array
	 */
	private function get_order_events( $products ) {

		$events = array();
		foreach ( $products as $cart_item_key => $product ) {

			for ( $x = 0; $x < $product['quantity']; $x++ ) {

				$woocommerce_events_event = get_post_meta( $product['product_id'], 'WooCommerceEventsEvent', true );

				if ( 'Event' === $woocommerce_events_event ) {

					$product_data = get_post( $product['product_id'] );

					$ticket                          = array();
					$ticket['product_id']            = $product['product_id'];
					$ticket['attribute_ticket-type'] = '';
					$ticket['event_name']            = $product_data->post_title;

					$price_incl_tax  = wc_get_price_including_tax( $product['data'] );
					$ticket['price'] = $price_incl_tax;

					if ( ! empty( $product['fooevents_bookings_slot_date_val'] ) ) {

						$ticket['booking_selection_slot_date'] = $product['fooevents_bookings_slot_date_val'];

					}

					if ( ! empty( $product['fooevents_bookings_date_val'] ) ) {

						$ticket['booking_selection_date'] = $product['fooevents_bookings_date_val'];

					}

					if ( ! empty( $product['fooevents_bookings_slot_val'] ) ) {

						$ticket['booking_selection_slot'] = $product['fooevents_bookings_slot_val'];

					}

					if ( ! empty( $product['fooevents_bookings_method'] ) ) {

						$ticket['fooevents_bookings_method'] = $product['fooevents_bookings_method'];

					}

					if ( ! empty( $product['fooevents_seats'] ) ) {

						$ticket['seats'] = $product['fooevents_seats'];

					}

					if ( ! empty( $product['variation']['attribute_ticket-type'] ) ) {

						$ticket['attribute_ticket-type'] = $product['variation']['attribute_ticket-type'];

					}

					if ( ! empty( $product['variation'] ) ) {

						$ticket['variations']   = $product['variation'];
						$ticket['variation_id'] = $product['variation_id'];

					}

					$events[ $product_data->ID ][] = $ticket;

				}
			}
		}

		return $events;
	}

	/**
	 * Creates a new ticket
	 *
	 * @param int    $customer_id customer ID.
	 * @param int    $product_id product ID.
	 * @param int    $order_id order ID.
	 * @param string $ticket_type ticket type.
	 * @param string $variations variations.
	 * @param int    $variation_id variation ID.
	 * @param double $price price.
	 * @param int    $x event counter.
	 * @param int    $y ticket counter.
	 * @param string $attendee_name attendee name.
	 * @param string $attendee_last_name attendee last name.
	 * @param string $attendee_email attendee email.
	 * @param string $attendee_telephone attendee telephone.
	 * @param string $attendee_company attendee company.
	 * @param string $attendee_designation attendee designation.
	 * @return array
	 */
	public function create_order_ticket( $customer_id, $product_id, $order_id, $ticket_type, $variations, $variation_id, $price, $x, $y, $attendee_name = '', $attendee_last_name = '', $attendee_email = '', $attendee_telephone = '', $attendee_company = '', $attendee_designation = '', $ticket_data = array() ) {

		$order = wc_get_order( $order_id );

		$ticket = array();

		$ticket['WooCommerceEventsProductID']           = sanitize_text_field( $product_id );
		$ticket['WooCommerceEventsOrderID']             = sanitize_text_field( $order_id );
		$ticket['WooCommerceEventsTicketType']          = sanitize_text_field( $ticket_type );
		$ticket['WooCommerceEventsStatus']              = 'Unpaid';
		$ticket['WooCommerceEventsCustomerID']          = sanitize_text_field( $customer_id );
		$ticket['WooCommerceEventsAttendeeName']        = sanitize_text_field( $attendee_name );
		$ticket['WooCommerceEventsAttendeeLastName']    = sanitize_text_field( $attendee_last_name );
		$ticket['WooCommerceEventsAttendeeEmail']       = sanitize_text_field( $attendee_email );
		$ticket['WooCommerceEventsAttendeeTelephone']   = sanitize_text_field( $attendee_telephone );
		$ticket['WooCommerceEventsAttendeeCompany']     = sanitize_text_field( $attendee_company );
		$ticket['WooCommerceEventsAttendeeDesignation'] = sanitize_text_field( $attendee_designation );
		$ticket['WooCommerceEventsVariations']          = $variations;
		$ticket['WooCommerceEventsVariationID']         = $variation_id;
		$ticket['WooCommerceEventsPrice']               = wc_price( $price );

		$woocommerce_events_purchaser_first_name = $order->get_billing_first_name();
		$woocommerce_events_purchaser_last_name  = $order->get_billing_last_name();
		$woocommerce_events_purchaser_email      = $order->get_billing_email();
		$woocommerce_events_purchaser_phone      = $order->get_billing_phone();

		$ticket['WooCommerceEventsPurchaserFirstName'] = $woocommerce_events_purchaser_first_name;
		$ticket['WooCommerceEventsPurchaserLastName']  = $woocommerce_events_purchaser_last_name;
		$ticket['WooCommerceEventsPurchaserEmail']     = $woocommerce_events_purchaser_email;
		$ticket['WooCommerceEventsPurchaserPhone']     = $woocommerce_events_purchaser_phone;

		$woocommerce_events_custom_attendee_fields = '';
		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		$custom_fields = $this->check_product_for_custom_attendee_fields( $product_id );
		if ( $custom_fields && ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) ) {

			$fooevents_custom_attendee_fields          = new Fooevents_Custom_Attendee_Fields();
			$woocommerce_events_custom_attendee_fields = $fooevents_custom_attendee_fields->capture_custom_attendee_options( $product_id, $x, $y );

		}

		$ticket['WooCommerceEventsCustomAttendeeFields'] = $woocommerce_events_custom_attendee_fields;

		$woocommerce_events_seating_fields = '';

		if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) {

			$fooevents_seating                 = new Fooevents_Seating();
			$woocommerce_events_seating_fields = $fooevents_seating->capture_seating_options( $product_id, $x, $y, $ticket_data );

			$row_text = get_post_meta( $product_id, 'WooCommerceEventsSeatingRowOverride', true );

			if ( '' === $row_text ) {
				$row_text = __( 'Row', 'woocommerce-events' );
			}

			$ticket['WooCommerceEventsSeatingRowOverride'] = $row_text;

			$seat_text = get_post_meta( $product_id, 'WooCommerceEventsSeatingSeatOverride', true );

			if ( '' === $seat_text ) {
				$seat_text = __( 'Seat', 'woocommerce-events' );
			}

			$ticket['WooCommerceEventsSeatingSeatOverride'] = $seat_text;

		}

		$ticket['WooCommerceEventsSeatingFields'] = $woocommerce_events_seating_fields;

		$woocommerce_events_booking_options = '';

		if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

			$fooevents_bookings                 = new FooEvents_Bookings();
			$woocommerce_events_booking_options = $fooevents_bookings->capture_booking_options( $product_id, $x, $y, $ticket_data );

		}

		$ticket['WooCommerceEventsBookingOptions'] = $woocommerce_events_booking_options;

		return $ticket;
	}
}
