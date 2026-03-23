<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attendee first name AutomateWoo variable.
 */
class Variable_FooEvents_Attendee_First_Name extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The attendee first name.
	 */
	protected $name = 'fooevents.attendee_first_name';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the attendee first name.', 'woocommerce-events' );

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

		return $ticket['WooCommerceEventsAttendeeName'];

	}

}

return 'Variable_FooEvents_Attendee_First_Name';
