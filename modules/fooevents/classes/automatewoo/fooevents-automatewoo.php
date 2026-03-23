<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * FooEvents AutomateWoo
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */


	add_filter( 'automatewoo/triggers', 'fooevents_add_triggers' );
	add_filter( 'automatewoo/variables', 'fooevents_add_variables' );
	add_filter( 'automatewoo_validate_data_item', 'validate_custom_variables', 10, 3 );

	add_filter(
		'automatewoo/data_types/includes',
		function ( $datatypes ) {
			include_once __DIR__ . '/fooevents_var_datatypes.php';
			$datatypes['fooevents'] = new \AutomateWoo\DataTypes\Fooevents_Vars();
			return $datatypes;
		}
	);

	/**
	 * Create the FooEvents supported AutomateWoo triggers
	 *
	 * @param array $triggers All the triggers.
	 */
	function fooevents_add_triggers( $triggers ) {

		require_once __DIR__ . '/classes/trigger-fooevents-after-create-ticket.php';
		require_once __DIR__ . '/classes/trigger-fooevents-after-check-in-ticket.php';

		$triggers['fooevents_after_create_ticket'] = 'Trigger_FooEvents_After_Create_Ticket';
		$triggers['fooevents_check_in_ticket']     = 'Trigger_FooEvents_After_Check_In_Ticket';

		return $triggers;
	}

	/**
	 * Add Fooevents variables to AutomateWoo workflows
	 *
	 * @param array $variables available AutomateWoo variables.
	 */
	function fooevents_add_variables( $variables ) {

		$variables['fooevents']['attendee_first_name']      = require_once __DIR__ . '/classes/variable-fooevents-attendee-first-name.php';
		$variables['fooevents']['attendee_last_name']       = require_once __DIR__ . '/classes/variable-fooevents-attendee-last-name.php';
		$variables['fooevents']['attendee_email']           = require_once __DIR__ . '/classes/variable-fooevents-attendee-email.php';
		$variables['fooevents']['attendee_phone']           = require_once __DIR__ . '/classes/variable-fooevents-attendee-phone.php';
		$variables['fooevents']['ticket_id']                = require_once __DIR__ . '/classes/variable-fooevents-ticket-id.php';
		$variables['fooevents']['event_name']               = require_once __DIR__ . '/classes/variable-fooevents-event-name.php';
		$variables['fooevents']['event_venue']              = require_once __DIR__ . '/classes/variable-fooevents-event-venue.php';
		$variables['fooevents']['event_date']               = require_once __DIR__ . '/classes/variable-fooevents-event-date.php';
		$variables['fooevents']['event_date_datetime']      = require_once __DIR__ . '/classes/variable-fooevents-event-date-datetime.php';
		$variables['fooevents']['event_date_mysql']         = require_once __DIR__ . '/classes/variable-fooevents-event-date-mysql.php';
		$variables['fooevents']['event_date_plaintext']     = require_once __DIR__ . '/classes/variable-fooevents-event-date-plaintext.php';
		$variables['fooevents']['event_hour']               = require_once __DIR__ . '/classes/variable-fooevents-event-hour.php';
		$variables['fooevents']['event_minutes']            = require_once __DIR__ . '/classes/variable-fooevents-event-minutes.php';
		$variables['fooevents']['event_period']             = require_once __DIR__ . '/classes/variable-fooevents-event-period.php';
		$variables['fooevents']['event_end_date']           = require_once __DIR__ . '/classes/variable-fooevents-event-end-date.php';
		$variables['fooevents']['event_end_date_datetime']  = require_once __DIR__ . '/classes/variable-fooevents-event-end-date-datetime.php';
		$variables['fooevents']['event_end_date_mysql']     = require_once __DIR__ . '/classes/variable-fooevents-event-end-date-mysql.php';
		$variables['fooevents']['event_end_date_plaintext'] = require_once __DIR__ . '/classes/variable-fooevents-event-end-date-plaintext.php';
		$variables['fooevents']['event_end_hour']           = require_once __DIR__ . '/classes/variable-fooevents-event-end-hour.php';
		$variables['fooevents']['event_end_minutes']        = require_once __DIR__ . '/classes/variable-fooevents-event-end-minutes.php';
		$variables['fooevents']['event_end_period']         = require_once __DIR__ . '/classes/variable-fooevents-event-end-period.php';
		$variables['fooevents']['barcode_url']              = require_once __DIR__ . '/classes/variable-fooevents-barcode-url.php';
		$variables['fooevents']['zoom_url']                 = require_once __DIR__ . '/classes/variable-fooevents-zoom-url.php';

		if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) && 'bookings' === $variables['fooevents_event_type'] ) {

			$variables['fooevents']['bookings_date']          = require_once __DIR__ . '/classes/variable-fooevents-bookings-date.php';
			$variables['fooevents']['bookings_mysql']         = require_once __DIR__ . '/classes/variable-fooevents-bookings-date-mysql.php';
			$variables['fooevents']['bookings_date_term']     = require_once __DIR__ . '/classes/variable-fooevents-bookings-date-term.php';
			$variables['fooevents']['bookings_date_datetime'] = require_once __DIR__ . '/classes/variable-fooevents-bookings-date-datetime.php';
			$variables['fooevents']['bookings_slot']          = require_once __DIR__ . '/classes/variable-fooevents-bookings-slot.php';
			$variables['fooevents']['bookings_slot_term']     = require_once __DIR__ . '/classes/variable-fooevents-bookings-slot-term.php';

		}

		if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) && 'seating' === $variables['fooevents_event_type'] ) {

			$variables['fooevents']['seating_row_name']          = require_once __DIR__ . '/classes/variable-fooevents-seating-row-name.php';
			$variables['fooevents']['seating_row_name_label']    = require_once __DIR__ . '/classes/variable-fooevents-seating-row-name-label.php';
			$variables['fooevents']['seating_seat_number']       = require_once __DIR__ . '/classes/variable-fooevents-seating-seat-number.php';
			$variables['fooevents']['seating_seat_number_label'] = require_once __DIR__ . '/classes/variable-fooevents-seating-seat-number-label.php';

		}

		return $variables;
	}

	/**
	 * Allow for FooEvents variables
	 *
	 * @param bool   $valid valid.
	 * @param string $type type.
	 * @param string $item item.
	 */
	function validate_custom_variables( $valid, $type, $item ) {

		if ( 'fooevents' === $type ) {

			return true;

		}

		return $valid;
	}
