<?php
/**
 * XMLRPC helper class
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * XMLRPC helper class
 */
class FooEvents_XMLRPC_Helper {

	/**
	 * Configuration object
	 *
	 * @var array $config
	 */
	public $config;

	/**
	 * On class load
	 *
	 * @param array $config configuration values.
	 */
	public function __construct( $config ) {

		$this->config = $config;
		$this->check_xmlrpc_enabled();
	}

	/**
	 * Check if XMLRPC is enabled
	 */
	public function check_xmlrpc_enabled() {

		if ( ! $this->is_xmlrpc_enabled() ) {

			$this->output_notices( array( 'XMLRPC is not enabled.' ) );

		}
	}

	/**
	 * Is XMLRPC enabled
	 */
	public function is_xmlrpc_enabled() {

		$return_bool = false;
		$enabled     = get_option( 'enable_xmlrpc' );

		if ( $enabled ) {

			$return_bool = true;

		} else {

			global $wp_version;
			if ( version_compare( $wp_version, '3.5', '>=' ) ) {

				$return_bool = true;

			} else {

				$return_bool = false;

			}
		}

		return $return_bool;
	}

	/**
	 * Outputs notices to screen.
	 *
	 * @param array $notices notices.
	 */
	private function output_notices( $notices ) {

		foreach ( $notices as $notice ) {

			echo '<div class="updated"><p>' . esc_html( $notice ) . '</p></div>';

		}
	}
}

/**
 * Tests whether or not XMLRPC is accessible
 *
 * @global object $wp_xmlrpc_server
 */
function fooevents_test_access() {

	echo 'FooEvents success';

	exit();
}

/**
 * Checks a users login details
 *
 * @global object $wp_xmlrpc_server
 * @param array $args arguments.
 */
function fooevents_login_status( $args ) {

	global $wp_xmlrpc_server;
	$wp_xmlrpc_server->escape( $args );

	$username = $args[0];
	$password = $args[1];
	$user     = $wp_xmlrpc_server->login( $username, $password );

	if ( false === $user ) {

		$output['message'] = false;

		echo wp_json_encode( $output );

		exit();

	} else {

		$output['message'] = true;
		$output['data']    = json_decode( wp_json_encode( $user->data ), true );

	}

	if ( ! fooevents_checkroles( $user ) ) {
		$output['message']      = false;
		$output['invalid_user'] = '1';

		echo wp_json_encode( $output );

		exit();
	}

	$output = fooevents_append_output_data( $output );

	echo wp_json_encode( $output );

	exit();
}


/**
 * Gets all data for all events for offline mode
 *
 * @global object $wp_xmlrpc_server
 * @param array $args arguments.
 */
function fooevents_get_all_data( $args ) {

	set_time_limit( 0 );

	wp_raise_memory_limit();

	global $wp_xmlrpc_server;

	$wp_xmlrpc_server->escape( $args );

	$username = $args[0];
	$password = $args[1];
	$user     = $wp_xmlrpc_server->login( $username, $password );

	if ( false === $user ) {

		$output['message'] = false;

		echo wp_json_encode( $output );

		exit();

	}

	if ( ! fooevents_checkroles( $user ) ) {
		$output['message']      = false;
		$output['invalid_user'] = '1';

		echo wp_json_encode( $output );

		exit();
	}

	$data_output = get_all_events( $user );

	foreach ( $data_output as &$event ) {

		$event['eventTickets'] = get_event_tickets( $event['WooCommerceEventsProductID'] );

	}

	echo wp_json_encode( $data_output );

	exit();
}

/**
 * Gets all events
 *
 * @global object $wp_xmlrpc_server
 * @param array $args arguments.
 */
function fooevents_get_list_of_events( $args ) {

	set_time_limit( 0 );

	wp_raise_memory_limit();

	global $wp_xmlrpc_server;

	$wp_xmlrpc_server->escape( $args );

	$username = $args[0];
	$password = $args[1];

	$user = $wp_xmlrpc_server->login( $username, $password );

	if ( false === $user ) {

		$output['message'] = false;

		echo wp_json_encode( $output );

		exit();

	}

	if ( ! fooevents_checkroles( $user ) ) {
		$output['message']      = false;
		$output['invalid_user'] = '1';

		echo wp_json_encode( $output );

		exit();
	}

	echo wp_json_encode( get_all_events( $user ) );

	exit();
}

/**
 * Gets a list of tickets belonging to an event
 *
 * @global object $wp_xmlrpc_server
 * @param array $args arguments.
 */
function fooevents_get_tickets_in_event( $args ) {

	set_time_limit( 0 );

	wp_raise_memory_limit();

	global $woocommerce;
	global $wp_xmlrpc_server;

	$wp_xmlrpc_server->escape( $args );

	$username = $args[0];
	$password = $args[1];
	$user     = $wp_xmlrpc_server->login( $username, $password );
	$event_id = $args[2];

	$user = $wp_xmlrpc_server->login( $username, $password );

	if ( false === $user ) {

		$output['message'] = false;

		echo wp_json_encode( $output );

		exit();

	}

	if ( ! fooevents_checkroles( $user ) ) {
		$output['message']      = false;
		$output['invalid_user'] = '1';

		echo wp_json_encode( $output );

		exit();
	}

	echo wp_json_encode( get_event_tickets( $event_id ) );

	exit();
}

/**
 * Gets a list of updated tickets belonging to an event
 *
 * @global object $wp_xmlrpc_server
 * @param array $args arguments.
 */
function fooevents_get_updated_tickets_in_event( $args ) {

	set_time_limit( 0 );

	wp_raise_memory_limit();

	global $woocommerce;
	global $wp_xmlrpc_server;

	$wp_xmlrpc_server->escape( $args );

	$username = $args[0];
	$password = $args[1];
	$event_id = $args[2];
	$since    = $args[3];

	$user = $wp_xmlrpc_server->login( $username, $password );

	if ( false === $user ) {

		$output['message'] = false;

		echo wp_json_encode( $output );

		exit();

	}

	if ( ! fooevents_checkroles( $user ) ) {
		$output['message']      = false;
		$output['invalid_user'] = '1';

		echo wp_json_encode( $output );

		exit();
	}

	echo wp_json_encode( get_event_updated_tickets( $event_id, $since ) );

	exit();
}

/**
 * Get a single ticket if it exists
 *
 * @global object $wp_xmlrpc_server
 * @param array $args arguments.
 */
function fooevents_get_single_ticket( $args ) {

	global $wp_xmlrpc_server;

	$wp_xmlrpc_server->escape( $args );

	$username  = $args[0];
	$password  = $args[1];
	$ticket_id = $args[2];

	$user = $wp_xmlrpc_server->login( $username, $password );

	if ( false === $user ) {

		$output['message'] = false;

		echo wp_json_encode( $output );

		exit();

	}

	if ( ! fooevents_checkroles( $user ) ) {
		$output['message']      = false;
		$output['invalid_user'] = '1';

		echo wp_json_encode( $output );

		exit();
	}

	echo wp_json_encode( get_single_ticket( $ticket_id ) );

	exit();
}

/**
 * Updates a tickets status
 *
 * @param array $args arguments.
 */
function fooevents_update_ticket_status( $args ) {

	global $wp_xmlrpc_server;
	$wp_xmlrpc_server->escape( $args );

	$username  = $args[0];
	$password  = $args[1];
	$ticket_id = $args[2];
	$status    = $args[3];

	$user = $wp_xmlrpc_server->login( $username, $password );

	if ( false === $user ) {

		$output['message'] = false;

		echo wp_json_encode( $output );

		exit();

	}

	$output = array();

	if ( ! fooevents_checkroles( $user ) ) {
		$output['message']      = false;
		$output['invalid_user'] = '1';

		echo wp_json_encode( $output );

		exit();
	}

	$output['message'] = update_ticket_status( $ticket_id, $status );

	echo wp_json_encode( $output );

	exit();
}

/**
 * Updates multiple tickets status
 *
 * @param array $args arguments.
 */
function fooevents_update_ticket_status_m( $args ) {

	set_time_limit( 0 );

	wp_raise_memory_limit();

	global $wp_xmlrpc_server;
	$wp_xmlrpc_server->escape( $args );

	$username       = $args[0];
	$password       = $args[1];
	$tickets_status = stripslashes( ( $args[2] ) );

	$output = array();

	$user = $wp_xmlrpc_server->login( $username, $password );

	if ( false === $user ) {

		$output['message'] = false;

		echo wp_json_encode( $output );

		exit();

	}

	if ( ! fooevents_checkroles( $user ) ) {
		$output['message']      = false;
		$output['invalid_user'] = '1';

		echo wp_json_encode( $output );

		exit();
	}

	$output = update_ticket_multiple_status( $tickets_status );

	echo wp_json_encode( $output );

	exit();
}

/**
 * Updates multiday tickets status
 *
 * @param array $args arguments.
 */
function fooevents_update_ticket_status_multiday( $args ) {

	global $wp_xmlrpc_server;
	$wp_xmlrpc_server->escape( $args );

	$username  = $args[0];
	$password  = $args[1];
	$ticket_id = $args[2];
	$status    = $args[3];
	$day       = $args[4];
	$user      = $wp_xmlrpc_server->login( $username, $password );
	$output    = array();
	if ( false === $user ) {

		$output['message'] = false;

		echo wp_json_encode( $output );

		exit();

	}

	if ( ! empty( $ticket_id ) && ! empty( $status ) && ! empty( $day ) ) {

		$output['message'] = update_ticket_multiday_status( $ticket_id, $status, $day );

	} else {

		$output['message'] = 'All fields are required.';

	}

	echo wp_json_encode( $output );

	exit();
}

/**
 * Register FooEvents methods
 *
 * @param array $methods methods.
 */
function fooevents_new_xmlrpc_methods( $methods ) {

	$methods['fooevents.test_access']  = 'fooevents_test_access';
	$methods['fooevents.login_status'] = 'fooevents_login_status';

	$methods['fooevents.get_all_data']                 = 'fooevents_get_all_data';
	$methods['fooevents.get_list_of_events']           = 'fooevents_get_list_of_events';
	$methods['fooevents.get_tickets_in_event']         = 'fooevents_get_tickets_in_event';
	$methods['fooevents.get_updated_tickets_in_event'] = 'fooevents_get_updated_tickets_in_event';
	$methods['fooevents.get_single_ticket']            = 'fooevents_get_single_ticket';

	$methods['fooevents.update_ticket_status']          = 'fooevents_update_ticket_status';
	$methods['fooevents.update_ticket_status_m']        = 'fooevents_update_ticket_status_m';
	$methods['fooevents.update_ticket_status_multiday'] = 'fooevents_update_ticket_status_multiday';

	return $methods;
}

add_filter( 'xmlrpc_methods', 'fooevents_new_xmlrpc_methods' );

/**
 * FooEvents checkroles
 *
 * @param object $user user.
 */
function fooevents_checkroles( $user ) {

	if ( user_can( $user, 'publish_event_magic_tickets' ) || user_can( $user, 'app_event_magic_tickets' ) ) {

		return true;

	} else {

		return false;

	}
}
