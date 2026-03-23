<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Date Plaintext AutomateWoo variable.
 */
class Variable_FooEvents_Date_Plaintext extends AutomateWoo\Variable {

	/**
	 * AutomateWoo variable name
	 *
	 * @var string $name The date.
	 */
	protected $name = 'fooevents.event_date_plaintext';

	/**
	 * Load admin details
	 */
	public function load_admin_details() {

		$this->description = __( 'Displays the date in plain text.', 'woocommerce-events' );

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

		$event_type = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsType', true );

		if ( 'select' === $event_type ) {

			$woocommerce_events_select_date         = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsSelectDate', true );
			$woocommerce_events_select_date_hour    = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsSelectDateHour', true );
			$woocommerce_events_select_date_minutes = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsSelectDateMinutes', true );
			$woocommerce_events_select_date_period  = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsSelectDatePeriod', true );

			$woocommerce_events_select_date_hour_end    = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsSelectDateHourEnd', true );
			$woocommerce_events_select_date_minutes_end = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsSelectDateMinutesEnd', true );
			$woocommerce_events_select_date_period_end  = get_post_meta( $ticket['WooCommerceEventsProductID'], 'WooCommerceEventsSelectDatePeriodEnd', true );

			$event_date = '';
			if ( ! empty( $woocommerce_events_select_date ) ) {

				$x                                 = 1;
				$variables['fooevents_event_date'] = '<div id="fooevents-event-date-select">';

				foreach ( $woocommerce_events_select_date as $date ) {

					$event_date                        .= '<b>' . $day_term . ' ' . $x . ':</b> ' . $date . '<br />';
					$variables['fooevents_event_date'] .= '<b>' . esc_attr__( 'Start time:', 'woocommerce-events' ) . '</b> ';
					$variables['fooevents_event_date'] .= esc_attr( $woocommerce_events_select_date_hour[ $x - 1 ] ) . ':' . esc_attr( $woocommerce_events_select_date_minutes[ $x - 1 ] );
					$variables['fooevents_event_date'] .= ( isset( $woocommerce_events_select_date_period[ $x - 1 ] ) ) ? ' ' . esc_attr( $woocommerce_events_select_date_period[ $x - 1 ] ) : '';
					$variables['fooevents_event_date'] .= '<br />';

					$variables['fooevents_event_date'] .= '<b>' . esc_attr__( 'End time:', 'woocommerce-events' ) . '</b> ';
					$variables['fooevents_event_date'] .= esc_attr( $woocommerce_events_select_date_hour_end[ $x - 1 ] ) . ':' . esc_attr( $woocommerce_events_select_date_minutes_end[ $x - 1 ] );
					$variables['fooevents_event_date'] .= ( isset( $woocommerce_events_select_date_period_end[ $x - 1 ] ) ) ? ' ' . esc_attr( $woocommerce_events_select_date_period_end[ $x - 1 ] ) : '';
					$variables['fooevents_event_date'] .= '<br /><br />';

					$x++;

				}

				$variables['fooevents_event_date'] .= '</div>';

				return $event_date;

			}
		} else {

			return $ticket['WooCommerceEventsDate'];

		}

	}

}

return 'Variable_FooEvents_Date_Plaintext';
