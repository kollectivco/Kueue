<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attendee email AutomateWoo variable.
 */
class Variable_FooEvents_Attendee_Email extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The attendee email address.
	 */
	protected $name = 'fooevents.attendee_email';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the attendee email address', 'woocommerce-events' );

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

		return $ticket['WooCommerceEventsAttendeeEmail'];

	}

}

return 'Variable_FooEvents_Attendee_Email';
