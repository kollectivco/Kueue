<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bookings date term AutomateWoo variable.
 */
class Variable_FooEvents_Bookings_Date_Term extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The bookings date term.
	 */
	protected $name = 'fooevents.bookings_date_term';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the bookings date term.', 'woocommerce-events' );

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

		return $ticket['WooCommerceEventsBookingsDateTerm'];

	}

}

return 'Variable_FooEvents_Bookings_Date_Term';
