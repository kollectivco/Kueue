<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bookings Date AutomateWoo variable.
 */
class Variable_FooEvents_Bookings_Date extends AutomateWoo\Variable_Abstract_Datetime {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The bookings date.
	 */
	protected $name = 'fooevents.bookings_date';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the bookings date.', 'woocommerce-events' );

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

		return $this->format_datetime( $ticket['WooCommerceEventsBookingDate'], $parameters );

	}

}

return 'Variable_FooEvents_Bookings_Date';
