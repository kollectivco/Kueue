<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attendee event name AutomateWoo variable.
 */
class Variable_FooEvents_Event_Name extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The event name.
	 */
	protected $name = 'fooevents.event_name';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the event name.', 'woocommerce-events' );

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

		return get_the_title( $ticket['WooCommerceEventsProductID'] );

	}

}

return 'Variable_FooEvents_Event_Name';
