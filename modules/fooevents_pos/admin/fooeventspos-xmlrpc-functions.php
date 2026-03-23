<?php
/**
 * Initialization of XML-RPC methods as well as their callbacks.
 *
 * @link https://www.fooevents.com
 * @since 1.0.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Test if is valid user with proper user role.
 *
 * @since 1.0.0
 * @global wp_xmlrpc_server $wp_xmlrpc_server
 * @param array $args The arguments received by the XML-RPC request.
 *
 * @return WP_User Authorized user.
 */
function fooeventspos_authorize_xmlrpc_user( $args ) {
	global $wp_xmlrpc_server;

	$wp_xmlrpc_server->escape( $args );

	$username = $args[0];
	$password = $args[1];
	$user     = '';

	$user = $wp_xmlrpc_server->login( $username, $password );

	if ( false === $user ) {
		$output['message'] = false;

		echo wp_json_encode( $output );

		exit;
	} elseif ( ! fooeventspos_checkroles( $user ) ) {
			$output['message']      = false;
			$output['invalid_user'] = '1';

			echo wp_json_encode( $output );

			exit;
	}

	return $user;
}

/**
 * Tests whether or not XML-RPC is accessible.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_test_access( $args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
	echo 'FooEvents POS success';

	exit;
}

/**
 * Checks connection details and if successful, fetches all data.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_connect_data_fetch( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	wp_raise_memory_limit();

	set_time_limit( 0 );

	$output = array( 'message' => true );

	$chunk = $args[2];

	$output['data'] = fooeventspos_fetch_chunk( $user, $chunk );

	echo wp_json_encode( $output );

	exit;
}

/**
 * Update product data.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_update_product( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$response = array( 'status' => 'error' );

	$product_data = json_decode( $args[2], true );

	$result = fooeventspos_do_update_product( $product_data );

	if ( ! empty( $result ) ) {
		$updated_product  = $result['updated_product'];
		$sale_product_ids = $result['sale_product_ids'];

		$response['status']           = 'success';
		$response['product']          = $updated_product;
		$response['sale_product_ids'] = $sale_product_ids;
	}

	echo wp_json_encode( $response );

	exit;
}

/**
 * Create a new order or update an existing order.
 *
 * @since 1.3.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_create_update_order( $args ) {

	$response = array( 'status' => 'error' );

	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$order_date                  = $args[2];
	$payment_method_key          = $args[3];
	$coupons                     = $args[4];
	$order_items                 = $args[5];
	$order_customer              = $args[6];
	$order_note                  = $args[7];
	$order_note_send_to_customer = $args[8];
	$attendee_details            = $args[9];
	$square_order_id             = $args[10];
	$user_id                     = $args[11];
	$stripe_payment_id           = $args[12];
	$analytics                   = $args[13];
	$order_status                = $args[14];
	$existing_order_id           = $args[15];
	$payments                    = $args[16];

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
				$response = $check_stock_result;
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
			)
		);

		$response = array(
			'status' => 'success',
			'order'  => fooeventspos_do_get_single_order( $created_updated_order ),
		);
	}

	echo wp_json_encode( $response );

	exit;
}

/**
 * Check stock availability for provided order items.
 *
 * @since 1.7.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_check_stock( $args ) {

	$response = array( 'status' => 'success' );

	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$order_items = $args[2];

	$check_stock_order_items = array();

	$order_items_array = json_decode( stripslashes( $order_items ), true );

	foreach ( $order_items_array as $order_item ) {
		$check_stock_order_items[ $order_item['pid'] ] = $order_item['oiq'];
	}

	$response = fooeventspos_do_check_stock( $check_stock_order_items );

	echo wp_json_encode( $response );

	exit;
}

/**
 * Sync offline data.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_sync_offline_changes( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$offline_changes = json_decode( stripslashes( $args[2] ), true );

	fooeventspos_do_sync_offline_changes( $offline_changes );
}

/**
 * Cancels an order, refunds the total and restocks if specified.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_cancel_order( $args ) {

	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$response = array( 'status' => 'error' );

	if ( fooeventspos_do_cancel_order( $args[2], (bool) $args[3] ) ) {
		$response['status'] = 'success';
	}

	echo wp_json_encode( $response );

	exit;
}

/**
 * Refunds items of an order and restocks specified quantities.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_refund_order( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$response = array( 'status' => 'error' );

	$order_id       = $args[2];
	$refunded_items = json_decode( stripslashes( $args[3] ), true );

	$refund_result = fooeventspos_do_refund_order( $order_id, $refunded_items );
	$wc_order      = $refund_result['order'];

	$response['status'] = 'success';
	$response['order']  = fooeventspos_do_get_single_order( $wc_order );

	// Square refund.
	if ( ! empty( $refund_result['square_refund'] ) ) {
		$response['square_refund'] = $refund_result['square_refund'];

		if ( ! empty( $output['square_refund_message'] ) ) {
			$output['square_refund_message'] = $refund_result['square_refund_message'];
		}

		if ( ! empty( $refund_result['square_terminal_refund'] ) ) {
			$response['square_terminal_refund'] = $refund_result['square_terminal_refund'];
		}
	}

	// Stripe refund.
	if ( ! empty( $refund_result['stripe_refund'] ) ) {
		$response['stripe_refund'] = $refund_result['stripe_refund'];
		$response['charge_id']     = $refund_result['charge_id'];
		$response['amount']        = $refund_result['amount'];

		if ( ! empty( $refund_result['message'] ) ) {
			$response['message'] = $refund_result['message'];
		}
	}

	echo wp_json_encode( $response );

	exit;
}

/**
 * Update payment.
 *
 * @since 1.8.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_update_payment( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$response = array( 'status' => 'error' );

	$payment_json = $args[2];
	$payment      = json_decode( $payment_json, true );

	$action = $args[3];

	if ( 'refund' === $action ) {
		$refund_result = fooeventspos_do_refund_payment( $payment, $platform );

		$response['status']                = $refund_result['status'];
		$response['payment_update_status'] = $refund_result['payment_update_status'];

		// Square refund.
		if ( ! empty( $refund_result['square_refund'] ) ) {
			$response['square_refund'] = $refund_result['square_refund'];

			if ( ! empty( $refund_result['square_refund_message'] ) ) {
				$response['square_refund_message'] = $refund_result['square_refund_message'];
			}

			if ( ! empty( $refund_result['square_terminal_refund'] ) ) {
				$response['square_terminal_refund'] = $refund_result['square_terminal_refund'];
			}
		}

		// Stripe refund.
		if ( ! empty( $refund_result['stripe_refund'] ) ) {
			$response['stripe_refund'] = $refund_result['stripe_refund'];
			$response['charge_id']     = $refund_result['charge_id'];
			$response['amount']        = $refund_result['amount'];

			if ( ! empty( $refund_result['message'] ) ) {
				$response['message'] = $refund_result['message'];
			}
		}
	} else {
		$update_result = fooeventspos_do_update_payment( $payment );

		$response['status'] = $update_result['status'];
	}

	echo wp_json_encode( $response );

	exit;
}

/**
 * Create or update a customer.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_create_update_customer( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$customer_details = json_decode( stripslashes( $args[2] ), true );

	$response = fooeventspos_do_create_update_customer( $customer_details );

	echo wp_json_encode( $response );

	exit;
}

/**
 * Get the coupon discount.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_get_coupon_code_discounts( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$coupons        = json_decode( stripslashes( $args[2] ), true );
	$order_items    = json_decode( stripslashes( $args[3] ), true );
	$order_customer = json_decode( stripslashes( $args[4] ), true );

	$response = fooeventspos_do_get_coupon_code_discounts( $coupons, $order_items, $order_customer );

	echo wp_json_encode( $response );

	exit;
}

/**
 * Get the data updates.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_get_data_updates( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$last_checked_timestamp = json_decode( stripslashes( $args[2] ), true );
	$order_ids_to_check     = isset( $args[3] ) ? json_decode( stripslashes( $args[3] ), true ) : array();

	$response = fooeventspos_do_get_data_updates( $last_checked_timestamp, $order_ids_to_check );

	echo wp_json_encode( $response );

	exit;
}

/**
 * Generate a Square device code.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_generate_square_device_code( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$square_location = json_decode( stripslashes( $args[2] ), true );

	$response = fooeventspos_do_generate_square_device_code( $square_location );

	echo wp_json_encode( $response );

	exit;
}

/**
 * Create a Square terminal checkout request.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_create_square_terminal_checkout( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$checkout_data = json_decode( stripslashes( $args[2] ), true );

	$response = fooeventspos_do_create_square_terminal_checkout( $checkout_data );

	echo wp_json_encode( $response );

	exit;
}

/**
 * Get a Square terminal checkout status.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_get_square_terminal_checkout_status( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$checkout_id = $args[2];

	$response = fooeventspos_do_get_square_terminal_checkout_status( $checkout_id );

	echo wp_json_encode( $response );

	exit;
}

/**
 * Get the pair status of a Square device.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_get_square_device_pair_status( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$square_device_id = $args[2];

	$response = fooeventspos_do_get_square_device_pair_status( $square_device_id );

	echo wp_json_encode( $response );

	exit;
}

/**
 * Create a Square terminal refund request.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_create_square_terminal_refund( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$refund_data = json_decode( stripslashes( $args[2] ), true );

	$response = fooeventspos_do_create_square_terminal_refund( $refund_data );

	echo wp_json_encode( $response );

	exit;
}

/**
 * Get a Square terminal refund status.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_get_square_terminal_refund_status( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$refund_id = $args[2];

	$response = fooeventspos_do_get_square_terminal_refund_status( $refund_id );

	echo wp_json_encode( $response );

	exit;
}

/**
 * Register a Stripe reader.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_register_stripe_reader( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$reader_data = json_decode( $args[2], true );

	$response = fooeventspos_do_register_stripe_reader( $reader_data );

	echo wp_json_encode( $response );

	exit;
}

/**
 * Create a Stripe connection token.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_create_stripe_connection_token( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$response = fooeventspos_do_create_stripe_connection_token();

	echo wp_json_encode( $response );

	exit;
}

/**
 * Create a Stripe payment intent.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_create_stripe_payment_intent( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$payment_intent_data = json_decode( $args[2], true );
	$payment_method      = $args[3];

	$response = fooeventspos_do_create_stripe_payment_intent( $payment_intent_data, $payment_method );

	echo wp_json_encode( $response );

	exit;
}

/**
 * Capture a processed Stripe payment intent.
 *
 * @since 1.0.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_capture_stripe_payment( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$payment_intent_id = $args[2];

	$response = fooeventspos_do_capture_stripe_payment( $payment_intent_id );

	echo wp_json_encode( $response );

	exit;
}

/**
 * Create new XML-RPC methods.
 *
 * @since 1.0.0
 * @param array $methods The available XML-RPC methods.
 */
function fooeventspos_new_xmlrpc_methods( $methods ) {
	$methods['fsfwc.test_access']                         = 'fooeventspos_test_access';
	$methods['fsfwc.connect_data_fetch']                  = 'fooeventspos_connect_data_fetch';
	$methods['fsfwc.update_product']                      = 'fooeventspos_update_product';
	$methods['fsfwc.create_update_order']                 = 'fooeventspos_create_update_order';
	$methods['fsfwc.check_stock']                         = 'fooeventspos_check_stock';
	$methods['fsfwc.sync_offline_changes']                = 'fooeventspos_sync_offline_changes';
	$methods['fsfwc.cancel_order']                        = 'fooeventspos_cancel_order';
	$methods['fsfwc.refund_order']                        = 'fooeventspos_refund_order';
	$methods['fsfwc.create_update_customer']              = 'fooeventspos_create_update_customer';
	$methods['fsfwc.get_coupon_code_discounts']           = 'fooeventspos_get_coupon_code_discounts';
	$methods['fsfwc.get_data_updates']                    = 'fooeventspos_get_data_updates';
	$methods['fsfwc.generate_square_device_code']         = 'fooeventspos_generate_square_device_code';
	$methods['fsfwc.get_square_device_pair_status']       = 'fooeventspos_get_square_device_pair_status';
	$methods['fsfwc.create_square_terminal_checkout']     = 'fooeventspos_create_square_terminal_checkout';
	$methods['fsfwc.get_square_terminal_checkout_status'] = 'fooeventspos_get_square_terminal_checkout_status';
	$methods['fsfwc.create_square_terminal_refund']       = 'fooeventspos_create_square_terminal_refund';
	$methods['fsfwc.get_square_terminal_refund_status']   = 'fooeventspos_get_square_terminal_refund_status';
	$methods['fsfwc.register_stripe_reader']              = 'fooeventspos_register_stripe_reader';
	$methods['fsfwc.create_stripe_connection_token']      = 'fooeventspos_create_stripe_connection_token';
	$methods['fsfwc.create_stripe_payment_intent']        = 'fooeventspos_create_stripe_payment_intent';
	$methods['fsfwc.capture_stripe_payment']              = 'fooeventspos_capture_stripe_payment';

	return $methods;
}

add_filter( 'xmlrpc_methods', 'fooeventspos_new_xmlrpc_methods' );
