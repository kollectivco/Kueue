<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attendee event end date AutomateWoo variable in MySQL format.
 */
class Variable_FooEvents_Event_End_Date_MySQL extends AutomateWoo\Variable_Abstract_Datetime {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The event end date.
	 */
	protected $name = 'fooevents.event_event_date';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the event end date in MySQL format.', 'woocommerce-events' );

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

		return $this->format_datetime( $ticket['WooCommerceEventsEndDateMySQLFormat'], $parameters );

	}

}

return 'Variable_FooEvents_Event_End_Date_MySQL';
