<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Zoom URL AutomateWoo variable.
 */
class Variable_FooEvents_Zoom_URL extends AutomateWoo\Variable {

	/**
	 * AutomateWoo Zoom URL
	 *
	 * @var string $name The zoom URL.
	 */
	protected $name = 'fooevents.zoom_url';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Outputs the Zoom URL.', 'woocommerce-events' );

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

		return $ticket['WooCommerceEventsZoomJoinURL'];

	}


}

return 'Variable_FooEvents_Zoom_URL';
