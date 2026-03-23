<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Attendee last name AutomateWoo variable.
 */
class Variable_FooEvents_Barcode_URL extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The attendee last name.
	 */
	protected $name = 'fooevents.barcode_url';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Outputs the barcode URL.', 'woocommerce-events' );

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

		$barcode_identifier = '';

		if ( 'ticketnumberformatted' == $ticket['WooCommerceEventsTicketIdentifierOutput'] ) {

			$barcode_identifier = '-tn';

		}

		return $ticket['barcodeURL'] . $ticket['WooCommerceEventsTicketHash'] . '-' . $ticket['WooCommerceEventsTicketID'] . $barcode_identifier . '.png';

	}


}

return 'Variable_FooEvents_Barcode_URL';
