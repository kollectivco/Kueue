<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bookings slot term AutomateWoo variable.
 */
class Variable_FooEvents_Bookings_Slot_Term extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The bookings slot term.
	 */
	protected $name = 'fooevents.bookings_slot_term';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the bookings slot term.', 'woocommerce-events' );

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

		return $ticket['WooCommerceEventsBookingsSlotTerm'];

	}

}

return 'Variable_FooEvents_Bookings_Slot_Term';
