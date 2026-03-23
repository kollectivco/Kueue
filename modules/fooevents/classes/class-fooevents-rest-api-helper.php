<?php
/**
 * REST API helper class
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * REST API helper class
 */
class FooEvents_REST_API_Helper extends WP_REST_Controller {

	/**
	 * API namespace
	 *
	 * @var array $api_namespace
	 */
	private $api_namespace;

	/**
	 * Base
	 *
	 * @var array $base
	 */
	private $base;

	/**
	 * API version
	 *
	 * @var array $api_version
	 */
	private $api_version;

	/**
	 * On class load
	 */
	public function __construct() {

		$this->api_namespace = 'fooevents/v';
		$this->api_version   = '1';

		$this->init();
	}

	/**
	 * Init on class load
	 */
	public function init() {

		add_action( 'rest_api_init', array( $this, 'fooevents_register_rest_api_routes' ) );
		add_filter( 'rest_pre_serve_request', array( $this, 'fooevents_rest_pre_serve_request' ) );
	}

	/**
	 * Register REST API endpoints with their corresponding callback functions
	 */
	public function fooevents_register_rest_api_routes() {

		$namespace = $this->api_namespace . $this->api_version;

		$rest_api_endpoints = array(
			'login_status',

			'get_all_data',
			'get_list_of_events',
			'get_tickets_in_event',
			'get_updated_tickets_in_event',
			'get_single_ticket',

			'update_ticket_status',
			'update_ticket_status_m',
			'update_ticket_status_multiday',
		);

		foreach ( $rest_api_endpoints as $endpoint ) {
			register_rest_route(
				$namespace,
				'/' . $endpoint,
				array(
					array(
						'methods'             => 'POST',
						'callback'            => array( $this, 'fooevents_callback_' . $endpoint ),
						'permission_callback' => function ( $request ) {
							return $this->fooevents_is_valid_user( $request->get_headers() );
						},
					),
				)
			);
		}
	}

	/**
	 * Add headers to REST pre-serve request.
	 *
	 * @param bool $served Whether the REST request was served or not.
	 *
	 * @return bool
	 */
	public function fooevents_rest_pre_serve_request( $served ) {

		header( 'Access-Control-Allow-Headers: Username, Password, Content-Type' );

		return $served;
	}

	/**
	 * Test if the provided credentials are for a valid user.
	 *
	 * @param array $headers The REST API request headers.
	 *
	 * @return bool
	 */
	public function fooevents_is_valid_user( $headers ) {
		if ( array_key_exists( 'username', $headers ) && array_key_exists( 'password', $headers ) ) {
			$username = trim( $headers['username'][0] );
			$password = trim( $headers['password'][0] );

			if ( empty( $username ) || empty( $password ) ) {
				return false;
			}

			$user = get_user_by( 'login', $username );

			if ( ! $user || is_wp_error( $user ) ) {
				$user = get_user_by( 'email', $username );

				if ( ! $user || is_wp_error( $user ) ) {
					return false;
				}
			}

			return wp_check_password( $password, $user->user_pass, $user->ID );
		}

		return false;
	}

	/**
	 * Test if is valid user with proper user role
	 *
	 * @param array $headers headers.
	 */
	public function fooevents_is_authorized_user( $headers ) {
		if ( array_key_exists( 'username', $headers ) ) {
			$username = trim( $headers['username'][0] );

			$user = get_user_by( 'login', $username );

			if ( ! $user || is_wp_error( $user ) ) {
				$user = get_user_by( 'email', $username );
			}

			remove_all_actions( 'set_current_user' );

			wp_set_current_user( $user->ID, $user->user_login );

			if ( ! ( current_user_can( 'publish_event_magic_tickets' ) || current_user_can( 'app_event_magic_tickets' ) ) ) { // phpcs:ignore WordPress.WP.Capabilities.Unknown
				return array(
					'message'      => false,
					'invalid_user' => '1',
				);
			}

			return $user;
		} else {
			return array(
				'message' => false,
			);
		}
	}

	/**
	 * Test login status
	 *
	 * @param WP_REST_Request $request request.
	 */
	public function fooevents_callback_login_status( WP_REST_Request $request ) {
		ob_start();

		$output = array( 'message' => false );

		$authorize_result = $this->fooevents_is_authorized_user( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			$output = array(
				'message' => true,
				'data'    => json_decode( wp_json_encode( $authorize_result->data ), true ),
			);

			$output = fooevents_append_output_data( $output );

		} else {

			$output = $authorize_result;

		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Fetch all data
	 *
	 * @param WP_REST_Request $request request.
	 */
	public function fooevents_callback_get_all_data( WP_REST_Request $request ) {

		ob_start();

		$output = array();

		$authorize_result = $this->fooevents_is_authorized_user( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			set_time_limit( 0 );

			wp_raise_memory_limit();

			$data_output = get_all_events( $authorize_result );

			foreach ( $data_output as &$event ) {

				$event['eventTickets'] = get_event_tickets( $event['WooCommerceEventsProductID'], true );

			}

			$output = $data_output;

		} else {

			$output = $authorize_result;

		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Fetch list of all events
	 *
	 * @param WP_REST_Request $request requestion.
	 */
	public function fooevents_callback_get_list_of_events( WP_REST_Request $request ) {

		ob_start();

		$output = array();

		$authorize_result = $this->fooevents_is_authorized_user( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			set_time_limit( 0 );

			wp_raise_memory_limit();

			$output = get_all_events( $authorize_result );

		} else {

			$output = $authorize_result;

		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Fetch all tickets of selected event
	 *
	 * @param WP_REST_Request $request request.
	 */
	public function fooevents_callback_get_tickets_in_event( WP_REST_Request $request ) {

		ob_start();

		$output = array();

		$authorize_result = $this->fooevents_is_authorized_user( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			set_time_limit( 0 );

			wp_raise_memory_limit();

			$event_id = $request->get_param( 'param2' );

			$output = get_event_tickets( $event_id );

		} else {

			$output = $authorize_result;

		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Fetch all updated tickets of selected event
	 *
	 * @param WP_REST_Request $request request.
	 */
	public function fooevents_callback_get_updated_tickets_in_event( WP_REST_Request $request ) {

		ob_start();

		$output = array();

		$authorize_result = $this->fooevents_is_authorized_user( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			set_time_limit( 0 );

			wp_raise_memory_limit();

			$event_id = $request->get_param( 'param2' );
			$since    = $request->get_param( 'param3' );

			$output = get_event_updated_tickets( $event_id, $since );

		} else {

			$output = $authorize_result;

		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Fetch a single ticket if it exists
	 *
	 * @param WP_REST_Request $request request.
	 */
	public function fooevents_callback_get_single_ticket( WP_REST_Request $request ) {

		ob_start();

		$output = array();

		$authorize_result = $this->fooevents_is_authorized_user( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			$ticket_id = $request->get_param( 'param2' );

			$output = get_single_ticket( $ticket_id );

		} else {

			$output = $authorize_result;

		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Update ticket status
	 *
	 * @param WP_REST_Request $request request.
	 */
	public function fooevents_callback_update_ticket_status( WP_REST_Request $request ) {

		ob_start();

		$output = array();

		$authorize_result = $this->fooevents_is_authorized_user( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			$ticket_id = $request->get_param( 'param2' );
			$status    = $request->get_param( 'param3' );

			$output['message'] = update_ticket_status( $ticket_id, $status );

		} else {

			$output = $authorize_result;

		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Update multiple ticket statuses
	 *
	 * @param WP_REST_Request $request request.
	 */
	public function fooevents_callback_update_ticket_status_m( WP_REST_Request $request ) {

		ob_start();

		$output = array();

		$authorize_result = $this->fooevents_is_authorized_user( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			set_time_limit( 0 );

			wp_raise_memory_limit();

			$tickets_status = $request->get_param( 'param2' );

			$output = update_ticket_multiple_status( $tickets_status );

		} else {

			$output = $authorize_result;

		}

		ob_end_clean();

		return $output;
	}

	/**
	 * Update multiday ticket status
	 *
	 * @param WP_REST_Request $request request.
	 */
	public function fooevents_callback_update_ticket_status_multiday( WP_REST_Request $request ) {

		ob_start();

		$output = array();

		$authorize_result = $this->fooevents_is_authorized_user( $request->get_headers() );

		if ( $authorize_result && is_object( $authorize_result ) && is_a( $authorize_result, 'WP_User' ) ) {

			$ticket_id = $request->get_param( 'param2' );
			$status    = $request->get_param( 'param3' );
			$day       = $request->get_param( 'param4' );

			$output = array();

			if ( ! empty( $ticket_id ) && ! empty( $status ) && ! empty( $day ) ) {

				$output['message'] = update_ticket_multiday_status( $ticket_id, $status, $day );

			} else {

				$output['message'] = 'All fields are required.';

			}
		} else {

			$output = $authorize_result;

		}

		ob_end_clean();

		return $output;
	}
}
