<?php
/**
 * AutomateWoo helper file
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * FooEvents trigger after create ticket
 */
class Trigger_FooEvents_After_Create_Ticket extends AutomateWoo\Trigger {

	/**
	 * Available variable groups.
	 *
	 * @var string $supplied_data_items available variable groups.
	 */
	public $supplied_data_items = array( 'customer', 'fooevents', 'order' );

	/**
	 * Set up the trigger
	 */
	public function init() {

		$this->title = __( 'After create ticket', 'woocommerce-events' );
		$this->group = __( 'FooEvents', 'woocommerce-events' );

	}

	/**
	 * Add any fields to the trigger (optional)
	 */
	public function load_fields() {}

	/**
	 * Defines when the trigger is run
	 */
	public function register_hooks() {

		add_action( 'fooevents_create_ticket', array( $this, 'catch_hooks' ) );

	}

	/**
	 * Catches the action and calls the maybe_run() method.
	 *
	 * @param int $ticket_id the ticket ID.
	 */
	public function catch_hooks( $ticket_id ) {

		$fooevents = new FooEvents();
		$ticket    = $fooevents->get_ticket_data( $ticket_id );

		$customer = '';
		if ( isset( $ticket['customerID'] ) ) {

			$customer = AutomateWoo\Customer_Factory::get_by_user_id( $ticket['customerID'] );

		}

		$order = wc_get_order( $ticket['WooCommerceEventsOrderID'] );

		$return_data = array(
			'fooevents' => $ticket,
			'order'     => $order,
		);

		if ( ! empty( $customer ) ) {

			$return_data['customer'] = $customer;

		}

		$this->maybe_run(
			$return_data
		);

	}

	/**
	 * Performs any validation if required. If this method returns true the trigger will fire.
	 *
	 * @param object $workflow AutomateWoo\Workflow.
	 * @return bool
	 */
	public function validate_workflow( $workflow ) {

		// Get objects from the data layer
		// $customer = $workflow->data_layer()->get_customer();

		return true;
	}



}

