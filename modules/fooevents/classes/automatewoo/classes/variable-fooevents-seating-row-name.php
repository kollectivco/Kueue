<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seating row name AutomateWoo variable.
 */
class Variable_FooEvents_Seating_Row_Name extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The seating row name.
	 */
	protected $name = 'fooevents.seating_row_name';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the seating row name.', 'woocommerce-events' );

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

		return $ticket['fooevents_seating_options_array']['row_name'];

	}

}

return 'Variable_FooEvents_Seating_Row_Name';
