<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seating seat number AutomateWoo variable.
 */
class Variable_FooEvents_Seating_Seating_Number extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The seating seat number.
	 */
	protected $name = 'fooevents.seating_seat_number';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the seating seat number.', 'woocommerce-events' );

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

		return $ticket['fooevents_seating_options_array']['seating_seat_number'];

	}

}

return 'Variable_FooEvents_Seating_Seating_Number';
