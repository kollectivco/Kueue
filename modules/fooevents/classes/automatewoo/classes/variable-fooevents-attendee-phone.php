<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attendee phone AutomateWoo variable.
 */
class Variable_FooEvents_Attendee_Phone extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The attendee phone number.
	 */
	protected $name = 'fooevents.attendee_phone';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the attendee phone number', 'woocommerce-events' );

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

		return $ticket['WooCommerceEventsAttendeeTelephone'];
	}
}

return 'Variable_FooEvents_Attendee_Phone';
