<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seating row name label AutomateWoo variable.
 */
class Variable_FooEvents_Seating_Row_Name_Label extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The seating row name label.
	 */
	protected $name = 'fooevents.seating_row_name_label';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the seating row name label.', 'woocommerce-events' );

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

		return $ticket['fooevents_seating_options_array']['row_name_label'];

	}

}

return 'Variable_FooEvents_Seating_Row_Name_Label';
