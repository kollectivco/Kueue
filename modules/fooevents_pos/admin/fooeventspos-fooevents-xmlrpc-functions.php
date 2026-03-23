<?php
/**
 * Initialization of FooEvents integration XML-RPC methods as well as their callbacks.
 *
 * @link https://www.fooevents.com
 * @since 1.9.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check FooEvents seating availability.
 *
 * @since 1.9.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_check_unavailable_booking_slots( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$attendee_details = json_decode( $args[2], true );

	$response = fooeventspos_do_check_unavailable_booking_slots( $attendee_details );

	echo wp_json_encode( $response );

	exit;
}

/**
 * Check seat availability before completing checkout.
 *
 * @since 1.9.0
 * @param array $args The arguments received by the XML-RPC request.
 */
function fooeventspos_check_unavailable_seats( $args ) {
	$user = fooeventspos_authorize_xmlrpc_user( $args );

	$attendee_details = json_decode( $args[2], true );
	$product_ids      = json_decode( $args[3], false );

	$response = fooeventspos_do_check_unavailable_seats( $attendee_details, $product_ids );

	echo wp_json_encode( $response );

	exit;
}

/**
 * Create new XML-RPC methods.
 *
 * @since 1.9.0
 * @param array $methods The available XML-RPC methods.
 */
function fooeventspos_fooevents_xmlrpc_methods( $methods ) {
	$methods['fsfwc.check_unavailable_booking_slots'] = 'fooeventspos_check_unavailable_booking_slots';
	$methods['fsfwc.check_unavailable_seats']         = 'fooeventspos_check_unavailable_seats';

	return $methods;
}

add_filter( 'xmlrpc_methods', 'fooeventspos_fooevents_xmlrpc_methods' );
