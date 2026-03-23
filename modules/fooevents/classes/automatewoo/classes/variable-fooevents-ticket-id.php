<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ticket ID AutomateWoo variable.
 */
class Variable_FooEvents_Ticket_Id extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The Ticket ID.
	 */
	protected $name = 'fooevents.ticket_id';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the ticket ID.', 'woocommerce-events' );

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

		return $ticket['WooCommerceEventsTicketID'];

	}

}

return 'Variable_FooEvents_Ticket_Id';
