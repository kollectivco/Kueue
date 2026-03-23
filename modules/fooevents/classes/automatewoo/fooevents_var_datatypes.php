<?php
/**
 * AutomateWoo variables class.
 *
 * @link    https://www.fooevents.com
 * @package woocommerce_events
 */

namespace AutomateWoo\DataTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CustomVar data type class
 */
class Fooevents_Vars extends AbstractDataType {
	/**
	 * @param $item
	 * @return bool
	 */

	public function validate( $item ) {

		return true;

	}

	/**
	 * @param $item
	 * @return mixed
	 */
	public function compress( $item ) {

		return $item['ID'];

	}


	/**
	 * @param $compressed_item
	 * @param $compressed_data_layer
	 * @return mixed decompressed item
	 */
	function decompress( $compressed_item, $compressed_data_layer ) {

			$fooevents = new \FooEvents();
			$ticket    = $fooevents->get_ticket_data( $compressed_item );

			return $ticket;

	}}
