<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attendee event bookings date datetime AutomateWoo variable.
 */
class Variable_FooEvents_Event_Bookings_Date_Datetime extends AutomateWoo\Variable_Abstract_Datetime {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The event date datetime.
	 */
	protected $name = 'fooevents.bookings_date_datetime';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the event bookings date datetime.', 'woocommerce-events' );

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

		$friendly_date = '';
		if ( ! empty( $ticket['WooCommerceEventsBookingDateTimestamp'] ) ) {

			$friendly_date = $this->format_datetime( $ticket['WooCommerceEventsBookingDateTimestamp'], $parameters );

		}

		return $friendly_date;

	}

}

return 'Variable_FooEvents_Event_Bookings_Date_Datetime';
