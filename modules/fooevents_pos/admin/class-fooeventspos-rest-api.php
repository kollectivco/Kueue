<?php
/**
 * REST API class containing initialization of REST API endpoints as well as their callbacks
 *
 * @link https://www.fooevents.com
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * The REST API-specific functionality of the plugin.
 *
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */
class FooEventsPOS_REST_API extends WP_REST_Controller {
	/**
	 * The namespace of the REST API.
	 *
	 * @since 1.0.0
	 * @var string $api_namespace The current namespace of the REST API.
	 */
	public $api_namespace;

	/**
	 * The current version of the REST API.
	 *
	 * @since 1.0.2
	 * @var string $current_version The current version of the REST API.
	 */
	public $current_version;

	/**
	 * The required capability of the REST API.
	 *
	 * @since 1.0.0
	 * @var string $required_capability The current required capability of the REST API.
	 */
	private $required_capability;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->api_namespace       = 'fooeventspos/';
		$this->current_version     = 6;
		$this->required_capability = 'publish_fooeventspos';

		add_action( 'rest_api_init', array( $this, 'fooeventspos_register_rest_api_routes' ) );
		add_filter( 'rest_pre_serve_request', array( $this, 'fooeventspos_rest_pre_serve_request' ) );
	}

	/**
	 * Register REST API endpoints with their corresponding callback functions.
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_register_rest_api_routes() {

		$rest_api_endpoints = array(
			'v' . $this->current_version         => array(
				'validate'                            => 'GET',
				'connect_data_fetch'                  => 'POST',
				'update_product'                      => 'POST',
				'create_update_order'                 => 'POST',
				'check_stock'                         => 'POST',
				'sync_offline_changes'                => 'POST',
				'cancel_order'                        => 'POST',
				'refund_order'                        => 'POST',
				'update_payment'                      => 'POST',
				'create_update_customer'              => 'POST',
				'get_coupon_code_discounts'           => 'POST',
				'get_data_updates'                    => 'POST',
				'create_square_payment'               => 'POST',
				'generate_square_device_code'         => 'POST',
				'get_square_device_pair_status'       => 'POST',
				'create_square_terminal_checkout'     => 'POST',
				'get_square_terminal_checkout_status' => 'POST',
				'create_square_terminal_refund'       => 'POST',
				'get_square_terminal_refund_status'   => 'POST',
				'register_stripe_reader'              => 'POST',
				'create_stripe_connection_token'      => 'POST',
				'create_stripe_payment_intent'        => 'POST',
				'capture_stripe_payment'              => 'POST',
			),
			'v' . ( $this->current_version + 1 ) => array(
				'connect_data_fetch' => 'POST',
			),
		);

		// Add Square webhook to all versions for backwards compatibility.
		$versions            = array_keys( $rest_api_endpoints );
		$last_version        = end( $versions );
		$last_version_number = (int) str_replace( 'v', '', $last_version );

		for ( $v = 0; $v <= $last_version_number; $v++ ) {
			if ( empty( $rest_api_endpoints[ 'v' . $v ] ) ) {
				$rest_api_endpoints[ 'v' . $v ] = array();
			}

			$rest_api_endpoints[ 'v' . $v ]['webhook_square'] = 'POST';
		}

		foreach ( $rest_api_endpoints as $version => $endpoints ) {

			foreach ( $endpoints as $endpoint => $method ) {

				$namespace = $this->api_namespace . $version;

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
									return (bool) $this->fooeventspos_is_valid_user( $request->get_headers() ) || false !== strpos( $request->get_route(), 'webhook_square' );
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
									return (bool) $this->fooeventspos_is_valid_user( $request->get_headers() ) || false !== strpos( $request->get_route(), 'webhook_square' );
								},
							),
						)
					);
				}
			}
		}
	}

	/**
	 * Add headers to REST pre-serve request.
	 *
	 * @since 1.0.0
	 * @param bool $served Whether the REST request was served or not.
	 *
	 * @return bool Whether the REST request was served or not.
	 */
	public function fooeventspos_rest_pre_serve_request( $served ) {

		header( 'Access-Control-Allow-Headers: Username, Password, Content-Type, X-WP-Nonce' );

		return $served;
	}

	/**
	 * Test if the provided credentials are for a valid user.
	 *
	 * @since 1.1.3
	 * @param array $headers The REST API request headers.
	 *
	 * @return bool Valid user.
	 */
	public function fooeventspos_is_valid_user( $headers ) {
		if ( array_key_exists( 'username', $headers ) && array_key_exists( 'password', $headers ) ) {
			$username = trim( $headers['username'][0] );
			$password = trim( $headers['password'][0] );

			if ( empty( $username ) || empty( $password ) ) {
				return false;
			}

			$user = get_user_by( 'login', $username );

			if ( ! $user || is_wp_error( $user ) ) {
				$user = get_user_by( 'email', $username );

				if ( ! $user || is_wp_error( $user ) ) {
					return false;
				}
			}

			return wp_check_password( $password, $user->user_pass, $user->ID );
		} elseif ( array_key_exists( 'x_wp_nonce', $headers ) || array_key_exists( 'x-wp-nonce', $headers ) ) {
			return is_user_logged_in();
		}

		return false;
	}

	/**
	 * Test if the provided credentials are for a user with the required capability.
	 *
	 * @since 1.1.3
	 * @param array $headers The headers received by the REST API request.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_user_has_required_capability( $headers ) {
		if ( array_key_exists( 'username', $headers ) ) {
			$username = trim( $headers['username'][0] );

			$user = get_user_by( 'login', $username );

			if ( ! $user || is_wp_error( $user ) ) {
				$user = get_user_by( 'email', $username );
			}

			if ( ! $user->has_cap( $this->required_capability ) ) {
				return array(
					'message'      => false,
					'invalid_user' => '1',
				);
			}

			return $user;
		} elseif ( array_key_exists( 'x_wp_nonce', $headers ) || array_key_exists( 'x-wp-nonce', $headers ) ) {
			if ( current_user_can( $this->required_capability ) ) {
				return wp_get_current_user();
			} else {
				return array(
					'message'      => false,
					'invalid_user' => '1',
				);
			}
		} else {
			return array(
				'message' => false,
			);
		}
	}

	/**
	 * Validate that FooEvents POS is being accessed via the plugin
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return bool Accessed via the plugin.
	 */
	public function fooeventspos_rest_callback_validate( WP_REST_Request $request ) {
		return true;
	}

	/**
	 * Connect and fetch data.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_connect_data_fetch( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'message' => false );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			wp_raise_memory_limit();

			set_time_limit( 0 );

			$output['message'] = true;

			$user  = $authorize_result;
			$chunk = $request->get_param( 'param2' );

			$output['data'] = fooeventspos_fetch_chunk( $user, $chunk, $platform );

		} else {

			ob_end_clean();

			return $authorize_result;

		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Update product data.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_update_product( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {
			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$result = fooeventspos_do_update_product( json_decode( $request->get_param( 'param2' ), true ), $platform );

			if ( ! empty( $result ) ) {
				$updated_product  = $result['updated_product'];
				$sale_product_ids = $result['sale_product_ids'];

				$output = array(
					'status'           => 'success',
					'product'          => $updated_product,
					'sale_product_ids' => $sale_product_ids,
				);
			}
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Create a new order or update an existing order.
	 *
	 * @since 1.3.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_create_update_order( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {
			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$order_date                  = $request->get_param( 'param2' );
			$payment_method_key          = $request->get_param( 'param3' );
			$coupons                     = $request->get_param( 'param4' );
			$order_items                 = $request->get_param( 'param5' );
			$order_customer              = $request->get_param( 'param6' );
			$order_note                  = $request->get_param( 'param7' );
			$order_note_send_to_customer = $request->get_param( 'param8' );
			$attendee_details            = $request->get_param( 'param9' );
			$square_order_id             = $request->get_param( 'param10' );
			$user_id                     = $request->get_param( 'param11' );
			$stripe_payment_id           = $request->get_param( 'param12' );
			$analytics                   = $request->get_param( 'param13' );
			$order_status                = $request->get_param( 'param14' );
			$existing_order_id           = $request->get_param( 'param15' );
			$payments                    = $request->get_param( 'param16' );

			add_filter( 'woocommerce_variation_is_purchasable', array( $this, 'fooeventspos_variation_is_purchasable' ), PHP_INT_MAX, 2 );

			$proceed_to_submit = true;

			if ( 'yes' === get_option( 'globalFooEventsPOSCheckStockAvailability', '' ) ) {
				$check_stock_order_items = array();

				$order_items_array = json_decode( stripslashes( $order_items ), true );

				if ( ! empty( $order_items_array ) ) {
					foreach ( $order_items_array as $order_item ) {
						$check_stock_order_items[ $order_item['pid'] ] = $order_item['oiq'];
					}

					$check_stock_result = fooeventspos_do_check_stock( $check_stock_order_items );

					$proceed_to_submit = 'success' === $check_stock_result['status'];

					if ( false === $proceed_to_submit ) {
						$output = $check_stock_result;
					}
				}
			}

			if ( $proceed_to_submit ) {
				$created_updated_order = fooeventspos_do_create_update_order(
					array(
						$order_date,
						$payment_method_key,
						$coupons,
						$order_items,
						$order_customer,
						$order_note,
						$order_note_send_to_customer,
						$attendee_details,
						$square_order_id,
						$user_id,
						$stripe_payment_id,
						$analytics,
						$order_status,
						$existing_order_id,
						$payments,
					),
					$platform
				);

				$output['status'] = 'success';
				$output['order']  = fooeventspos_do_get_single_order( $created_updated_order, $platform, true );
			}
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Check stock availability for provided order items.
	 *
	 * @since 1.7.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_check_stock( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'success' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {
			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$order_items = $request->get_param( 'param2' );

			$check_stock_order_items = array();

			$order_items_array = json_decode( stripslashes( $order_items ), true );

			foreach ( $order_items_array as $order_item ) {
				$check_stock_order_items[ $order_item['pid'] ] = $order_item['oiq'];
			}

			$output = fooeventspos_do_check_stock( $check_stock_order_items );
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Synchronize offline changes.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return string Result output.
	 */
	public function fooeventspos_rest_callback_sync_offline_changes( WP_REST_Request $request ) {

		ob_start();

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {
			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$offline_changes = json_decode( $request->get_param( 'param2' ), true );

			fooeventspos_do_sync_offline_changes( $offline_changes, $platform );
		} else {
			echo wp_json_encode( $authorize_result );
		}

		$output = ob_get_contents();

		ob_get_clean();

		if ( false !== strpos( $platform, 'web' ) ) {
			return $output;
		} else {
			echo wp_kses_post( $output );

			exit;
		}
	}

	/**
	 * Cancel order.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_cancel_order( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			if ( fooeventspos_do_cancel_order( $request->get_param( 'param2' ), (bool) $request->get_param( 'param3' ), $platform ) ) {

				$output['status'] = 'success';

			}
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Refund order.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_refund_order( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$order_id       = $request->get_param( 'param2' );
			$refunded_items = json_decode( stripslashes( $request->get_param( 'param3' ) ), true );

			$refund_result = fooeventspos_do_refund_order( $order_id, $refunded_items, $platform );
			$wc_order      = $refund_result['order'];

			$output['status'] = 'success';
			$output['order']  = fooeventspos_do_get_single_order( $wc_order, $platform );

			// Square refund.
			if ( ! empty( $refund_result['square_refund'] ) ) {
				$output['square_refund'] = $refund_result['square_refund'];

				if ( ! empty( $output['square_refund_message'] ) ) {
					$output['square_refund_message'] = $refund_result['square_refund_message'];
				}

				if ( ! empty( $refund_result['square_terminal_refund'] ) ) {
					$output['square_terminal_refund'] = $refund_result['square_terminal_refund'];
				}
			}

			// Stripe refund.
			if ( ! empty( $refund_result['stripe_refund'] ) ) {
				$output['stripe_refund'] = $refund_result['stripe_refund'];
				$output['charge_id']     = $refund_result['charge_id'];
				$output['amount']        = $refund_result['amount'];

				if ( ! empty( $refund_result['message'] ) ) {
					$output['message'] = $refund_result['message'];
				}
			}
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Update payment.
	 *
	 * @since 1.8.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_update_payment( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$payment_json = $request->get_param( 'param2' );
			$payment      = json_decode( $payment_json, true );

			$action = $request->get_param( 'param3' );

			if ( 'refund' === $action ) {
				$refund_result = fooeventspos_do_refund_payment( $payment, $platform );

				$output['status']                = $refund_result['status'];
				$output['payment_update_status'] = $refund_result['payment_update_status'];

				// Square refund.
				if ( ! empty( $refund_result['square_refund'] ) ) {
					$output['square_refund'] = $refund_result['square_refund'];

					if ( ! empty( $refund_result['square_refund_message'] ) ) {
						$output['square_refund_message'] = $refund_result['square_refund_message'];
					}

					if ( ! empty( $refund_result['square_terminal_refund'] ) ) {
						$output['square_terminal_refund'] = $refund_result['square_terminal_refund'];
					}
				}

				// Stripe refund.
				if ( ! empty( $refund_result['stripe_refund'] ) ) {
					$output['stripe_refund'] = $refund_result['stripe_refund'];
					$output['charge_id']     = $refund_result['charge_id'];
					$output['amount']        = $refund_result['amount'];

					if ( ! empty( $refund_result['message'] ) ) {
						$output['message'] = $refund_result['message'];
					}
				}
			} else {
				$update_result = fooeventspos_do_update_payment( $payment );

				$output['status'] = $update_result['status'];
			}
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Create or update customer.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_create_update_customer( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$customer_details = json_decode( stripslashes( $request->get_param( 'param2' ) ), true );

			$output = fooeventspos_do_create_update_customer( $customer_details, $platform );

		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Get coupon code discounts.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_get_coupon_code_discounts( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$coupons        = json_decode( stripslashes( $request->get_param( 'param2' ) ), true );
			$order_items    = json_decode( stripslashes( $request->get_param( 'param3' ) ), true );
			$order_customer = json_decode( stripslashes( $request->get_param( 'param4' ) ), true );

			$output = fooeventspos_do_get_coupon_code_discounts( $coupons, $order_items, $order_customer, $platform );

		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Get data updates.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_get_data_updates( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$last_checked_timestamp = json_decode( stripslashes( $request->get_param( 'param2' ) ), true );
			$order_ids_to_check     = $request->has_param( 'param3' ) ? json_decode( stripslashes( $request->get_param( 'param3' ) ), true ) : array();

			$output = fooeventspos_do_get_data_updates( $last_checked_timestamp, $order_ids_to_check, $platform );

		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Create a Square manual payment.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_create_square_payment( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$payment_data = json_decode( $request->get_param( 'payment_data' ), true );

			$output = fooeventspos_do_create_square_payment( $payment_data, $platform );

		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Webhook for Square to send various device and checkout status updates.
	 *
	 * @since 1.0.0
	 * @global wpdb $wpdb
	 * @param WP_REST_Request $request The REST API request object.
	 */
	public function fooeventspos_rest_callback_webhook_square( WP_REST_Request $request ) {

		global $wpdb;

		$event_type  = $request->get_param( 'type' );
		$square_data = $request->get_param( 'data' )['object'];

		if ( 'device.code.paired' === $event_type ) {

			if ( ! empty( $square_data['device_code'] ) ) {

				$device_code = $square_data['device_code'];
				$table_name  = $wpdb->prefix . 'fooeventspos_square_devices';

				$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$table_name,
					array(
						'device_id' => $device_code['device_id'],
						'status'    => $device_code['status'],
					),
					array(
						'device_code_id' => $device_code['id'],
					)
				);

			}
		} elseif ( 'terminal.checkout.updated' === $event_type ) {

			if ( ! empty( $square_data['checkout'] ) ) {

				$checkout_data = $square_data['checkout'];
				$table_name    = $wpdb->prefix . 'fooeventspos_square_checkouts';

				$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$table_name,
					array(
						'status'     => $checkout_data['status'],
						'updated_at' => $checkout_data['updated_at'],
						'payment_id' => $checkout_data['payment_ids'][0],
					),
					array(
						'checkout_id' => $checkout_data['id'],
					)
				);
			}
		} elseif ( 'terminal.refund.updated' === $event_type ) {

			if ( ! empty( $square_data['refund'] ) ) {

				$refund_data = $square_data['refund'];
				$table_name  = $wpdb->prefix . 'fooeventspos_square_refunds';

				$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$table_name,
					array(
						'status'     => $refund_data['status'],
						'updated_at' => $refund_data['updated_at'],
					),
					array(
						'refund_id' => $refund_data['id'],
					)
				);
			}
		}
	}

	/**
	 * Generate a Square device code.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_generate_square_device_code( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {
			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$square_location = json_decode( $request->get_param( 'param2' ), true );

			$output = fooeventspos_do_generate_square_device_code( $square_location, $platform );
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Get the pair status of a Square device.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_get_square_device_pair_status( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {
			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$square_device_id = $request->get_param( 'param2' );

			$output = fooeventspos_do_get_square_device_pair_status( $square_device_id, $platform );
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Create a Square terminal checkout request.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_create_square_terminal_checkout( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {
			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$checkout_data = json_decode( $request->get_param( 'param2' ), true );

			$output = fooeventspos_do_create_square_terminal_checkout( $checkout_data, $platform );
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Get a Square terminal checkout status.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_get_square_terminal_checkout_status( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {
			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$checkout_id = $request->get_param( 'param2' );

			$output = fooeventspos_do_get_square_terminal_checkout_status( $checkout_id, $platform );
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Create a Square terminal refund request.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_create_square_terminal_refund( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {
			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$refund_data = json_decode( $request->get_param( 'param2' ), true );

			$output = fooeventspos_do_create_square_terminal_refund( $refund_data, $platform );
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Get a Square terminal refund status.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_get_square_terminal_refund_status( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {
			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$refund_id = $request->get_param( 'param2' );

			$output = fooeventspos_do_get_square_terminal_refund_status( $refund_id, $platform );
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Register a Stripe reader.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_register_stripe_reader( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {
			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$reader_data = json_decode( $request->get_param( 'param2' ), true );

			$output = fooeventspos_do_register_stripe_reader( $reader_data, $platform );
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Create a Stripe connection token.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_create_stripe_connection_token( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {
			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$output = fooeventspos_do_create_stripe_connection_token( $platform );
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Create a Stripe payment intent.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_create_stripe_payment_intent( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {
			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$payment_intent_data = json_decode( $request->get_param( 'param2' ), true );
			$payment_method      = $request->get_param( 'param3' );

			$output = fooeventspos_do_create_stripe_payment_intent( $payment_intent_data, $payment_method, $platform );
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Capture a processed Stripe payment intent.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request The REST API request object.
	 *
	 * @return array Result output.
	 */
	public function fooeventspos_rest_callback_capture_stripe_payment( WP_REST_Request $request ) {

		ob_start();

		$output = array( 'status' => 'error' );

		$authorize_result = $this->fooeventspos_user_has_required_capability( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {
			$platform = 'any';

			if ( $request->has_param( 'platform' ) && '' !== trim( $request->get_param( 'platform' ) ) ) {
				$platform = trim( $request->get_param( 'platform' ) );
			}

			$payment_intent_id = $request->get_param( 'param2' );

			$output = fooeventspos_do_capture_stripe_payment( $payment_intent_id, $platform );
		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Filter callback to return whether or not a variation is purchasable based on whether it can be shown in FooEvents POS.
	 *
	 * @since 1.1.0
	 * @param boolean              $purchasable Whether the variation is purchasable.
	 * @param WC_Product_Variation $wc_product_variation The product variation.
	 *
	 * @return boolean Variation is purchasable.
	 */
	public function fooeventspos_variation_is_purchasable( $purchasable, $wc_product_variation ) {
		return true;
	}
}
