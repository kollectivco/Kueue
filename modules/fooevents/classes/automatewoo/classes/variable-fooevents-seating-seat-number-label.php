<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seating seat number label AutomateWoo variable.
 */
class Variable_FooEvents_Seating_Seating_Number_Label extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The seating seat number label.
	 */
	protected $name = 'fooevents.seating_seat_number_label';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the seating seat number label.', 'woocommerce-events' );

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

		return $ticket['fooevents_seating_options_array']['seat_number_label'];

	}

}

return 'Variable_FooEvents_Seating_Seating_Number_Label';
