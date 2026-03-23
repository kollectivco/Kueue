<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bookings slot AutomateWoo variable.
 */
class Variable_FooEvents_Bookings_Slot extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The bookings slot.
	 */
	protected $name = 'fooevents.bookings_slot';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the bookings slot.', 'woocommerce-events' );

		parent::load_admin_details();

	}


	/**
	 * Get and return variable value
	 *
	 * @param array $ticket FooEvents ticket array.
	 * @param array $parameters AutomateWoo parameters.
	 * @param array $workflow AutomateWoo Workflow.
	 * @return string
	 */
	public function get_value( $ticket, $parameters, $workflow ) {

		return $ticket['WooCommerceEventsBookingSlot'];

	}

}

return 'Variable_FooEvents_Bookings_Slot';
