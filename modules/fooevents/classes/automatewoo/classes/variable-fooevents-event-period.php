<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attendee event period AutomateWoo variable.
 */
class Variable_FooEvents_Event_Period extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The event period.
	 */
	protected $name = 'fooevents.event_period';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the event period.', 'woocommerce-events' );

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

		return $ticket['WooCommerceEventsPeriod'];

	}

}

return 'Variable_FooEvents_Event_Period';
