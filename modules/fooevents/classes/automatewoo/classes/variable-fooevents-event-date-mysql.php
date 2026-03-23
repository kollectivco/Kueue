<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attendee event date AutomateWoo variable in MySQL format.
 */
class Variable_FooEvents_Event_Date_MySQL extends AutomateWoo\Variable_Abstract_Datetime {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The event date.
	 */
	protected $name = 'fooevents.event_date_mysql';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the event date in MySQL format.', 'woocommerce-events' );

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

		return $this->format_datetime( $ticket['WooCommerceEventsDateMySQLFormat'], $parameters );

	}

}

return 'Variable_FooEvents_Event_Date_MySQL';
