<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attendee event end period AutomateWoo variable.
 */
class Variable_FooEvents_Event_End_Period extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The event end period.
	 */
	protected $name = 'fooevents.event_end_period';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the event end period.', 'woocommerce-events' );

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

		return $ticket['WooCommerceEventsEndPeriod'];

	}

}

return 'Variable_FooEvents_Event_End_Period';
