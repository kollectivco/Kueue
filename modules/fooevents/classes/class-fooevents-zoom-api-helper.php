<?php
/**
 * Zoom helper class
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * Zoom helper class
 */
class FooEvents_Zoom_API_Helper {

	/**
	 * Configuration object
	 *
	 * @var array $config contains paths and other configurations
	 */
	public $config;

	/**
	 * On class load
	 *
	 * @param FooEvents_Config $config configuration.
	 */
	public function __construct( $config ) {

		$this->config = $config;

		add_action( 'wp_ajax_fooevents_zoom_test_access', array( $this, 'fooevents_zoom_test_access' ) );
		add_action( 'wp_ajax_fooevents_zoom_fetch_users', array( $this, 'fooevents_zoom_fetch_users' ) );
		add_action( 'wp_ajax_fooevents_fetch_zoom_meeting', array( $this, 'fooevents_fetch_zoom_meeting' ) );
		add_action( 'wp_ajax_fooevents_update_zoom_registration', array( $this, 'fooevents_update_zoom_registration' ) );
	}

	/**
	 * Obtain a Zoom API OAuth access token using the provided Account ID, Client ID and Client Secret
	 *
	 * @param string $account_id Account ID.
	 * @param string $client_id Client ID.
	 * @param string $client_secret Client Secret.
	 *
	 * @return array
	 */
	private function fooevents_zoom_get_access_token( $account_id, $client_id, $client_secret ) {

		if ( '' === trim( $account_id ) || '' === trim( $client_id ) || '' === trim( $client_secret ) ) {
			return '';
		}

		$result = array(
			'error'  => 'error',
			'reason' => __( 'Error obtaining access token using the provided Zoom API credentials.', 'woocommerce-events' ),
		);

		$wp_remote_request_args = array(
			'method'      => 'POST',
			'timeout'     => 30,
			'redirection' => 10,
			'httpversion' => '1.1',
			'headers'     => array(
				'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $client_secret ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'Content-type'  => 'application/json',
			),
		);

		$response = wp_remote_request(
			'https://zoom.us/oauth/token?grant_type=account_credentials&account_id=' . $account_id,
			$wp_remote_request_args
		);

		if ( ! is_wp_error( $response ) ) {
			$response_array = json_decode( $response['body'], true );

			if ( $response_array ) {
				$result = $response_array;

				if ( ! empty( $response_array['access_token'] ) && ! empty( $response_array['expires_in'] ) ) {
					update_option( 'globalWooCommerceEventsZoomAccessToken', $response_array['access_token'] );
					update_option( 'globalWooCommerceEventsZoomAccessTokenExpiry', time() + (int) $response_array['expires_in'] );
				}
			}
		}

		return $result;
	}

	/**
	 * Generate a Zoom API token from the saved credentials
	 *
	 * @return array
	 */
	private function fooevents_zoom_token() {

		$account_id    = (string) get_option( 'globalWooCommerceEventsZoomAccountID', '' );
		$client_id     = (string) get_option( 'globalWooCommerceEventsZoomClientID', '' );
		$client_secret = (string) get_option( 'globalWooCommerceEventsZoomClientSecret', '' );

		$access_token = (string) get_option( 'globalWooCommerceEventsZoomAccessToken', '' );
		$token_expiry = (int) get_option( 'globalWooCommerceEventsZoomAccessTokenExpiry', 0 );

		if ( '' === $access_token || $token_expiry <= time() ) {
			return $this->fooevents_zoom_get_access_token( $account_id, $client_id, $client_secret );
		}

		return array( 'access_token' => $access_token );
	}

	/**
	 * Perform a Zoom API request and return the result
	 *
	 * @param string $method method.
	 * @param array  $args arguments.
	 * @param string $access_token access token.
	 * @param string $request_type request type.
	 *
	 * @return array
	 */
	private function fooevents_zoom_request( $method = '', $args = array(), $access_token = '', $request_type = 'GET' ) {

		$result = array( 'status' => 'error' );

		if ( '' === $access_token ) {

			$token = $this->fooevents_zoom_token();

			if ( ! empty( $token ) ) {
				if ( ! empty( $token['error'] ) ) {
					$result['message'] = $token['reason'];

					return $result;
				} else {
					$access_token = $token['access_token'];
				}
			}
		}

		if ( empty( $access_token ) ) {

			$result['message'] = __( 'Error obtaining access token using the provided Zoom API credentials.', 'woocommerce-events' );

			return $result;

		}

		$wp_remote_request_args = array(
			'method'      => $request_type,
			'timeout'     => 30,
			'redirection' => 10,
			'httpversion' => '1.1',
			'headers'     => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-type'  => 'application/json',
			),
		);

		if ( 'POST' === $request_type || 'PUT' === $request_type || 'PATCH' === $request_type ) {

			$wp_remote_request_args['body'] = wp_json_encode( $args );

		}

		$response = wp_remote_request(
			'https://api.zoom.us/v2/' . $method . ( 'GET' === $request_type ? '?' . http_build_query( $args ) : '' ),
			$wp_remote_request_args
		);

		if ( is_wp_error( $response ) ) {

			$result['message'] = __( 'Unable to connect to your Zoom account', 'woocommerce-events' );

		} else {

			$result['status'] = 'success';

			$response_array = json_decode( $response['body'], true );

			if ( $response_array ) {

				if ( ! empty( $response_array['code'] ) && ! empty( $response_array['message'] ) ) {

					$result = array(
						'status'  => 'error',
						'message' => $response_array['message'],
					);

				} else {

					$result = array(
						'status' => 'success',
						'data'   => $response_array,
					);

				}
			}
		}

		return $result;
	}

	/**
	 * Tests whether or not the Zoom API Server-to-Server OAuth credentials have been set up correctly
	 */
	public function fooevents_zoom_test_access() {

		$result = array( 'status' => 'error' );

		$account_id    = isset( $_POST['account_id'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['account_id'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$client_id     = isset( $_POST['client_id'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['client_id'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$client_secret = isset( $_POST['client_secret'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['client_secret'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( '' === trim( $account_id ) || '' === trim( $client_id ) || '' === trim( $client_secret ) ) {

			$result['message'] = __( 'Please enter your Zoom API Account ID, Client ID and Client Secret', 'woocommerce-events' );

			echo wp_json_encode( $result );

			exit();

		}

		$token = $this->fooevents_zoom_get_access_token( $account_id, $client_id, $client_secret );

		if ( ! empty( $token['error'] ) ) {
			$result['message'] = $token['reason'];
		} else {
			$access_token = $token['access_token'];

			$result = $this->fooevents_zoom_request( 'users/me', array(), $access_token );

			if ( 'success' === $result['status'] ) {
				update_option( 'globalWooCommerceEventsZoomAdminID', $result['data']['id'] );
			}
		}

		echo wp_json_encode( $result );

		exit();
	}

	/**
	 * Fetches all users on the account to allow selecting of specific users' meetings to display
	 */
	public function fooevents_zoom_fetch_users() {

		$account_id    = '';
		$client_id     = '';
		$client_secret = '';

		if ( ! empty( $_POST['account_id'] ) && '' !== trim( sanitize_text_field( wp_unslash( $_POST['account_id'] ) ) ) && ! empty( $_POST['client_id'] ) && '' !== trim( sanitize_text_field( wp_unslash( $_POST['client_id'] ) ) ) && ! empty( $_POST['client_secret'] ) && '' !== trim( sanitize_text_field( wp_unslash( $_POST['client_secret'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$account_id    = trim( sanitize_text_field( wp_unslash( $_POST['account_id'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$client_id     = trim( sanitize_text_field( wp_unslash( $_POST['client_id'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$client_secret = trim( sanitize_text_field( wp_unslash( $_POST['client_secret'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		}

		if ( '' === trim( $account_id ) || '' === trim( $client_id ) || '' === trim( $client_secret ) ) {

			// Check for saved credentials.
			$account_id    = get_option( 'globalWooCommerceEventsZoomAccountID', '' );
			$client_id     = get_option( 'globalWooCommerceEventsZoomClientID', '' );
			$client_secret = get_option( 'globalWooCommerceEventsZoomClientSecret', '' );

		}

		if ( '' === trim( $account_id ) || '' === trim( $client_id ) || '' === trim( $client_secret ) ) {
				$result = array(
					'status'  => 'error',
					'message' => __( 'Please enter your Zoom API Account ID, Client ID and Client Secret', 'woocommerce-events' ),
				);

				echo wp_json_encode( $result );

				exit();
		}

		$token = array(
			'error'  => 'error',
			'reason' => __( 'Please enter your Zoom API Account ID, Client ID and Client Secret', 'woocommerce-events' ),
		);

		$token = $this->fooevents_zoom_get_access_token( $account_id, $client_id, $client_secret );

		$zoom_users = array();

		if ( ! empty( $token['error'] ) ) {
			$result['message'] = $token['reason'];
		} else {
			$access_token = $token['access_token'];

			$loaded_all_users = false;
			$page             = 1;
			$user_count       = 0;

			while ( ! $loaded_all_users ) {

				$response = $this->fooevents_zoom_request(
					'users',
					array(
						'page_size'   => 300,
						'page_number' => $page,
						'status'      => 'active',
					),
					$access_token
				);

				if ( 'success' === $response['status'] ) {

					$users = array();

					foreach ( $response['data']['users'] as $user ) {

						++$user_count;

						if ( 1 === $user['type'] ) {
							continue;
						}

						$users[ $user['id'] ] = array(
							'id'         => $user['id'],
							'first_name' => ucwords( $user['first_name'] ),
							'last_name'  => ucwords( $user['last_name'] ),
							'email'      => $user['email'],
						);

					}

					if ( empty( $zoom_users ) ) {

						$zoom_users = $response;

						$zoom_users['data']['users'] = $users;

					} else {

						$zoom_users['data']['users'] = array_merge( $zoom_users['data']['users'], $users );

					}

					if ( $zoom_users['data']['total_records'] > $user_count ) {

						++$page;

					} else {

						$loaded_all_users = true;

					}
				} else {

					echo wp_json_encode( $response );

					exit();

				}
			}

			uasort(
				$zoom_users['data']['users'],
				function ( $a, $b ) {
					return $a['first_name'] <=> $b['first_name'];
				}
			);
		}

		echo wp_json_encode( $zoom_users );

		exit();
	}

	/**
	 * Fetch available Zoom meetings
	 *
	 * @return array
	 */
	public function fooevents_fetch_zoom_meetings() {

		return $this->fooevents_fetch_zoom( 'meetings' );
	}

	/**
	 * Fetch available Zoom webinars
	 *
	 * @return array
	 */
	public function fooevents_fetch_zoom_webinars() {

		return $this->fooevents_fetch_zoom( 'webinars' );
	}

	/**
	 * Fetch available Zoom meetings/webinars
	 *
	 * @param string $endpoint endpoint.
	 * @return array
	 */
	public function fooevents_fetch_zoom( $endpoint = 'webinars' ) {

		if ( ! $this->fooevents_uses_zoom() ) {
			return array();
		}

		$zoom_meetings = array();

		$date_format                          = get_option( 'date_format' );
		$time_format                          = get_option( 'time_format' );
		$global_woocommerce_events_zoom_users = json_decode( get_option( 'globalWooCommerceEventsZoomUsers', wp_json_encode( array() ) ), true );
		$global_woocommerce_events_zoom_selected_user_option = get_option( 'globalWooCommerceEventsZoomSelectedUserOption' );
		$global_woocommerce_events_zoom_selected_users       = get_option( 'globalWooCommerceEventsZoomSelectedUsers' );

		if ( empty( $global_woocommerce_events_zoom_selected_user_option ) || ( ! empty( $global_woocommerce_events_zoom_selected_user_option ) && 'me' === $global_woocommerce_events_zoom_selected_user_option ) || ( ! empty( $global_woocommerce_events_zoom_selected_user_option ) && 'select' === $global_woocommerce_events_zoom_selected_user_option && empty( $global_woocommerce_events_zoom_selected_users ) ) ) {

			$global_woocommerce_events_zoom_selected_users = array( 'me' );

		}

		foreach ( $global_woocommerce_events_zoom_selected_users as $user_id ) {

			$user = array(
				'id'         => 'me',
				'first_name' => '',
				'last_name'  => '',
				'email'      => 'me',
			);

			if ( 'me' !== $user_id ) {

				$user = $global_woocommerce_events_zoom_users[ $user_id ];

			}

			$loaded_all_meetings = false;
			$page                = 1;

			while ( ! $loaded_all_meetings ) {
				$response = $this->fooevents_zoom_request(
					'users/' . $user['email'] . '/' . $endpoint,
					array(
						'page_size'   => 300,
						'page_number' => $page,
					)
				);

				if ( 'success' === $response['status'] ) {

					$meetings = array();

					foreach ( $response['data'][ $endpoint ] as &$zoom_meeting ) {

						if ( 1 === $zoom_meeting['type'] ) {
							continue;
						}

						$zoom_meeting['id'] = $zoom_meeting['id'] . '_' . $endpoint;

						if ( 3 !== $zoom_meeting['type'] && 6 !== $zoom_meeting['type'] ) {

							$start_timestamp = strtotime( $zoom_meeting['start_time'] );

							$start_date = new DateTime( '@' . $start_timestamp );

							try {

								$start_date_timezone = new DateTimeZone( $zoom_meeting['timezone'] );

							} catch ( Exception $e ) {

								$server_timezone     = date_default_timezone_get();
								$start_date_timezone = new DateTimeZone( $server_timezone );

							}

							$start_date->setTimezone( $start_date_timezone );

							$zoom_meeting['start_date_display'] = $start_date->format( $date_format );
							$zoom_meeting['start_time_display'] = $start_date->format( $time_format . ' T' );

						}

						$zoom_meeting['host'] = $user;

						$meetings[] = $zoom_meeting;

					}

					if ( empty( $zoom_meetings ) ) {
						$zoom_meetings = $response;

						$zoom_meetings['data'][ $endpoint ] = $meetings;
					} else {
						$zoom_meetings['data'][ $endpoint ] = array_merge( $zoom_meetings['data'][ $endpoint ], $meetings );
					}

					if ( $response['data']['total_records'] > count( $response['data'][ $endpoint ] ) ) {
						++$page;
					} else {
						$loaded_all_meetings = true;
					}
				} else {
					break;
				}
			}
		}

		if ( empty( $zoom_meetings ) ) {
			$zoom_meetings['status'] = 'error';
		}

		$zoom_meetings['user_count'] = count( $global_woocommerce_events_zoom_selected_users );

		return $zoom_meetings;
	}

	/**
	 * Fetch individual Zoom meeting AJAX call
	 */
	public function fooevents_fetch_zoom_meeting() {

		$zoom_meeting_id = isset( $_POST['zoomMeetingID'] ) ? sanitize_text_field( wp_unslash( $_POST['zoomMeetingID'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$result = $this->do_fooevents_fetch_zoom_meeting( $zoom_meeting_id );

		echo wp_json_encode( $result );

		exit();
	}

	/**
	 * Fetch individual Zoom meeting
	 *
	 * @param string $zoom_meeting_id Zoom meeting ID.
	 */
	public function do_fooevents_fetch_zoom_meeting( $zoom_meeting_id ) {

		$zoom_id_parts = explode( '_', $zoom_meeting_id );
		$zoom_id       = $zoom_id_parts[0];
		$endpoint      = ! empty( $zoom_id_parts[1] ) ? $zoom_id_parts[1] : 'webinars';

		$result = $this->fooevents_zoom_request( $endpoint . '/' . $zoom_id );

		if ( 'success' === $result['status'] && ! empty( $result['data'] ) ) {

			$zoom_meeting = &$result['data'];

			$start_timestamp = 0;

			if ( 5 !== $zoom_meeting['type'] && 2 !== $zoom_meeting['type'] ) {

				// Recurrence type.
				switch ( $zoom_meeting['recurrence']['type'] ) {
					case 1:
						$zoom_meeting['recurrence']['type_display'] = 1 === $zoom_meeting['recurrence']['repeat_interval'] ? __( 'Daily', 'woocommerce-events' ) : __( 'Every', 'woocommerce-events' ) . ' ' . $zoom_meeting['recurrence']['repeat_interval'] . ' ' . __( 'days', 'woocommerce-events' );
						break;

					case 2:
						$zoom_meeting['recurrence']['type_display'] = 1 === $zoom_meeting['recurrence']['repeat_interval'] ? __( 'Weekly', 'woocommerce-events' ) : __( 'Every', 'woocommerce-events' ) . ' ' . $zoom_meeting['recurrence']['repeat_interval'] . ' ' . __( 'weeks', 'woocommerce-events' );
						break;

					case 3:
						$zoom_meeting['recurrence']['type_display'] = 1 === $zoom_meeting['recurrence']['repeat_interval'] ? __( 'Monthly', 'woocommerce-events' ) : __( 'Every', 'woocommerce-events' ) . ' ' . $zoom_meeting['recurrence']['repeat_interval'] . ' ' . __( 'months', 'woocommerce-events' );
						break;

				}

				// Weekly days.
				if ( isset( $zoom_meeting['recurrence']['weekly_days'] ) ) {

					$weekly_days = explode( ',', $zoom_meeting['recurrence']['weekly_days'] );

					$zoom_meeting['recurrence']['type_display'] .= __( ' on ', 'woocommerce-events' );

					$last_weekly_day = end( $weekly_days );

					foreach ( $weekly_days as $weekly_day ) {

						switch ( $weekly_day ) {
							case 1:
								$zoom_meeting['recurrence']['type_display'] .= __( 'Sunday', 'woocommerce-events' );
								break;

							case 2:
								$zoom_meeting['recurrence']['type_display'] .= __( 'Monday', 'woocommerce-events' );
								break;

							case 3:
								$zoom_meeting['recurrence']['type_display'] .= __( 'Tuesday', 'woocommerce-events' );
								break;

							case 4:
								$zoom_meeting['recurrence']['type_display'] .= __( 'Wednesday', 'woocommerce-events' );
								break;

							case 5:
								$zoom_meeting['recurrence']['type_display'] .= __( 'Thursday', 'woocommerce-events' );
								break;

							case 6:
								$zoom_meeting['recurrence']['type_display'] .= __( 'Friday', 'woocommerce-events' );
								break;

							case 7:
								$zoom_meeting['recurrence']['type_display'] .= __( 'Saturday', 'woocommerce-events' );
								break;

						}

						if ( $weekly_day !== $last_weekly_day ) {

							$zoom_meeting['recurrence']['type_display'] .= ', ';

						}
					}
				}

				// Monthly day.
				if ( isset( $zoom_meeting['recurrence']['monthly_day'] ) ) {

					$zoom_meeting['recurrence']['type_display'] .= __( ' on the ', 'woocommerce-events' );
					$zoom_meeting['recurrence']['type_display'] .= $zoom_meeting['recurrence']['monthly_day'];
					$zoom_meeting['recurrence']['type_display'] .= __( ' of the month', 'woocommerce-events' );

				}

				// Monthly week.
				if ( isset( $zoom_meeting['recurrence']['monthly_week'] ) ) {

					switch ( $zoom_meeting['recurrence']['monthly_week'] ) {
						case -1:
							$zoom_meeting['recurrence']['type_display'] .= ' ' . __( 'on the last', 'woocommerce-events' );
							break;

						case 1:
							$zoom_meeting['recurrence']['type_display'] .= ' ' . __( 'on the first', 'woocommerce-events' );
							break;

						case 2:
							$zoom_meeting['recurrence']['type_display'] .= ' ' . __( 'on the second', 'woocommerce-events' );
							break;

						case 3:
							$zoom_meeting['recurrence']['type_display'] .= ' ' . __( 'on the third', 'woocommerce-events' );
							break;

						case 4:
							$zoom_meeting['recurrence']['type_display'] .= ' ' . __( 'on the fourth', 'woocommerce-events' );
							break;

					}

					// Monthly week day.
					if ( isset( $zoom_meeting['recurrence']['monthly_week_day'] ) ) {

						switch ( $zoom_meeting['recurrence']['monthly_week_day'] ) {
							case 1:
								$zoom_meeting['recurrence']['type_display'] .= ' ' . __( 'Sunday', 'woocommerce-events' );
								break;

							case 2:
								$zoom_meeting['recurrence']['type_display'] .= ' ' . __( 'Monday', 'woocommerce-events' );
								break;

							case 3:
								$zoom_meeting['recurrence']['type_display'] .= ' ' . __( 'Tuesday', 'woocommerce-events' );
								break;

							case 4:
								$zoom_meeting['recurrence']['type_display'] .= ' ' . __( 'Wednesday', 'woocommerce-events' );
								break;

							case 5:
								$zoom_meeting['recurrence']['type_display'] .= ' ' . __( 'Thursday', 'woocommerce-events' );
								break;

							case 6:
								$zoom_meeting['recurrence']['type_display'] .= ' ' . __( 'Friday', 'woocommerce-events' );
								break;

							case 7:
								$zoom_meeting['recurrence']['type_display'] .= ' ' . __( 'Saturday', 'woocommerce-events' );
								break;

						}
					}
				}

				if ( ! empty( $zoom_meeting['recurrence']['end_date_time'] ) ) {

					$end_timestamp = strtotime( $zoom_meeting['recurrence']['end_date_time'] );

					$end_date = new DateTime( '@' . $end_timestamp );

					try {

						$end_date_timezone = new DateTimeZone( $zoom_meeting['timezone'] );

					} catch ( Exception $e ) {

						$server_timezone   = date_default_timezone_get();
						$end_date_timezone = new DateTimeZone( $server_timezone );

					}

					$end_date->setTimezone( $end_date_timezone );

					$zoom_meeting['recurrence']['type_display'] .= ' ' . __( 'until', 'woocommerce-events' ) . ' ' . $end_date->format( get_option( 'date_format' ) );

				} elseif ( ! empty( $zoom_meeting['recurrence']['end_times'] ) ) {

					$occurrences = (int) $zoom_meeting['recurrence']['end_times'];

					$zoom_meeting['recurrence']['type_display'] .= ', ' . $occurrences . ' ' . ( 1 === $occurrences ? __( 'occurrence', 'woocommerce-events' ) : __( 'occurrences', 'woocommerce-events' ) );

				}

				if ( ! empty( $zoom_meeting['occurrences'] ) ) {

					foreach ( $zoom_meeting['occurrences'] as &$occurrence ) {

						$start_timestamp = strtotime( $occurrence['start_time'] );
						$start_date      = new DateTime( '@' . $start_timestamp );

						try {

							$start_date_timezone = new DateTimeZone( $zoom_meeting['timezone'] );

						} catch ( Exception $e ) {

							$server_timezone     = date_default_timezone_get();
							$start_date_timezone = new DateTimeZone( $server_timezone );

						}

						$start_date->setTimezone( $start_date_timezone );

						$occurrence['start_date_display'] = $start_date->format( get_option( 'date_format' ) );
						$occurrence['start_time_display'] = $start_date->format( get_option( 'time_format' ) . ' T' );

						$end_timestamp = $start_timestamp + ( (int) $occurrence['duration'] * 60 );
						$end_date      = new DateTime( '@' . $end_timestamp );

						try {

							$end_date_timezone = new DateTimeZone( $zoom_meeting['timezone'] );

						} catch ( Exception $e ) {

							$server_timezone   = date_default_timezone_get();
							$end_date_timezone = new DateTimeZone( $server_timezone );

						}

						$end_date->setTimezone( $end_date_timezone );

						$occurrence['end_time_display'] = $end_date->format( get_option( 'time_format' ) . ' T' );

						$occurrence['duration_display'] = $this->fooevents_format_minutes( (int) $occurrence['duration'] );

					}

					$zoom_meeting['start_date_display'] = $zoom_meeting['occurrences'][0]['start_date_display'];
					$zoom_meeting['start_time_display'] = $zoom_meeting['occurrences'][0]['start_time_display'];
					$zoom_meeting['end_time_display']   = $zoom_meeting['occurrences'][0]['end_time_display'];
					$zoom_meeting['duration_display']   = $zoom_meeting['occurrences'][0]['duration_display'];

				}
			} elseif ( 3 !== $zoom_meeting['type'] && 6 !== $zoom_meeting['type'] ) {

					$start_timestamp = strtotime( $zoom_meeting['start_time'] );
					$start_date      = new DateTime( '@' . $start_timestamp );

				try {

					$start_date_timezone = new DateTimeZone( $zoom_meeting['timezone'] );

				} catch ( Exception $e ) {

					$server_timezone     = date_default_timezone_get();
					$start_date_timezone = new DateTimeZone( $server_timezone );

				}

					$start_date->setTimezone( $start_date_timezone );

					$zoom_meeting['start_date_display'] = $start_date->format( get_option( 'date_format' ) );
					$zoom_meeting['start_time_display'] = $start_date->format( get_option( 'time_format' ) . ' T' );

					$end_timestamp = $start_timestamp + ( (int) $zoom_meeting['duration'] * 60 );
					$end_date      = new DateTime( '@' . $end_timestamp );

				try {

					$end_date_timezone = new DateTimeZone( $zoom_meeting['timezone'] );

				} catch ( Exception $e ) {

					$server_timezone   = date_default_timezone_get();
					$end_date_timezone = new DateTimeZone( $server_timezone );

				}

					$end_date->setTimezone( $end_date_timezone );

					$zoom_meeting['end_time_display'] = $end_date->format( get_option( 'time_format' ) . ' T' );

					$zoom_meeting['duration_display'] = $this->fooevents_format_minutes( (int) $zoom_meeting['duration'] );
			}

			$zoom_meeting['meeting_capacity'] = ( ! empty( $zoom_meeting['settings']['registrants_restrict_number'] ) && (int) $zoom_meeting['settings']['registrants_restrict_number'] > 0 ) ? $zoom_meeting['settings']['registrants_restrict_number'] : $this->fooevents_zoom_user_meeting_capacity( $zoom_meeting['host_id'], in_array( (int) $zoom_meeting['type'], array( 2, 3, 8 ), true ) );

			$registrants = $this->fooevents_get_zoom_meeting_registrants( $zoom_meeting_id );

			$zoom_meeting['registrants'] = array(
				'total_records' => 0,
				'registrants'   => array(),
			);

			if ( 'success' === $registrants['status'] ) {

				$zoom_meeting['registrants'] = $registrants['data'];

			}
		}

		return $result;
	}

	/**
	 * Format number of minutes into a presentable string of hours and minutes
	 *
	 * @param int $minutes minutes.
	 *
	 * @return string
	 */
	private function fooevents_format_minutes( $minutes ) {

		$formatted_minutes = '';

		if ( $minutes >= 60 ) {

			$hours             = floor( $minutes / 60 );
			$remaining_minutes = $minutes % 60;

			$formatted_minutes = $hours . ' ' . ( 1 === $hours ? __( 'hour', 'woocommerce-events' ) : __( 'hours', 'woocommerce-events' ) ) . ( $remaining_minutes > 0 ? ' ' . $remaining_minutes . ' ' . __( 'minutes', 'woocommerce-events' ) : '' );

		} else {

			$formatted_minutes = $minutes . ' ' . __( 'minutes', 'woocommerce-events' );

		}

		return $formatted_minutes;
	}

	/**
	 * Register attendee for a Zoom meeting
	 *
	 * @param int   $zoom_meeting_id Zoom meeting ID.
	 * @param array $args arguments.
	 *
	 * @return array
	 */
	public function fooevents_register_zoom_attendee( $zoom_meeting_id, $args ) {

		$zoom_id_parts = explode( '_', $zoom_meeting_id );
		$zoom_id       = $zoom_id_parts[0];
		$endpoint      = ! empty( $zoom_id_parts[1] ) ? $zoom_id_parts[1] : 'webinars';

		$result = $this->fooevents_zoom_request( $endpoint . '/' . $zoom_id . '/registrants', $args, '', 'POST' );

		return $result;
	}

	/**
	 * Updates attendee registration statuses for a Zoom meeting
	 *
	 * @param int   $zoom_meeting_id Zoom meeting ID.
	 * @param array $args arguments.
	 *
	 * @return array
	 */
	public function fooevents_update_zoom_registration_statuses( $zoom_meeting_id, $args ) {

		$zoom_id_parts = explode( '_', $zoom_meeting_id );
		$zoom_id       = $zoom_id_parts[0];
		$endpoint      = ! empty( $zoom_id_parts[1] ) ? $zoom_id_parts[1] : 'webinars';

		$result = $this->fooevents_zoom_request( $endpoint . '/' . $zoom_id . '/registrants/status', $args, '', 'PUT' );

		return $result;
	}

	/**
	 * Update Zoom registration and approval type
	 */
	public function fooevents_update_zoom_registration() {

		$zoom_meeting_id   = isset( $_POST['zoomMeetingID'] ) ? sanitize_text_field( wp_unslash( $_POST['zoomMeetingID'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$recurring_meeting = isset( $_POST['recurringMeeting'] ) ? (bool) sanitize_text_field( wp_unslash( $_POST['recurringMeeting'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$args = array(
			'settings' => array(
				'approval_type' => 0,
			),
		);

		if ( $recurring_meeting ) {

			$args['settings']['registration_type'] = 1;

		}

		$zoom_id_parts = explode( '_', $zoom_meeting_id );
		$zoom_id       = $zoom_id_parts[0];
		$endpoint      = ! empty( $zoom_id_parts[1] ) ? $zoom_id_parts[1] : 'webinars';

		$result = $this->fooevents_zoom_request( $endpoint . '/' . $zoom_id, $args, '', 'PATCH' );

		echo wp_json_encode( $result );

		exit();
	}

	/**
	 * Fetch the Zoom user's maximum meeting capacity
	 *
	 * @param string $host_id host ID.
	 * @param bool   $is_meeting Is meeting as opposed to webinar.
	 *
	 * @return int
	 */
	public function fooevents_zoom_user_meeting_capacity( $host_id = 'me', $is_meeting = true ) {

		$capacity = 0;
		$result   = $this->fooevents_zoom_request( 'users/' . $host_id . '/settings' );

		if ( 'success' === $result['status'] ) {

			$capacity_key = $is_meeting ? 'meeting_capacity' : 'webinar_capacity';
			$capacity     = $result['data']['feature'][ $capacity_key ];

		}

		return $capacity;
	}

	/**
	 * Get the Zoom meeting registrants
	 *
	 * @param int $zoom_meeting_id Zoom meeting ID.
	 *
	 * @return array
	 */
	private function fooevents_get_zoom_meeting_registrants( $zoom_meeting_id ) {

		$zoom_id_parts = explode( '_', $zoom_meeting_id );
		$zoom_id       = $zoom_id_parts[0];
		$endpoint      = ! empty( $zoom_id_parts[1] ) ? $zoom_id_parts[1] : 'webinars';

		$loaded_all_registrants = false;
		$page                   = 1;

		$meeting_registrants = array();

		while ( ! $loaded_all_registrants ) {

			$response = $this->fooevents_zoom_request(
				$endpoint . '/' . $zoom_id . '/registrants',
				array(
					'page_size'   => 300,
					'page_number' => $page,
				)
			);

			if ( 'success' === $response['status'] ) {

				if ( empty( $meeting_registrants ) ) {

					$meeting_registrants = $response;

				} else {

					$meeting_registrants['data']['registrants'] = array_merge( $meeting_registrants['data']['registrants'], $response['data']['registrants'] );

				}

				if ( $meeting_registrants['data']['total_records'] > count( $meeting_registrants['data']['registrants'] ) ) {

					++$page;

				} else {

					$loaded_all_registrants = true;

				}
			} else {

				return $response;

			}
		}

		return $meeting_registrants;
	}

	/**
	 * Adds attendees as registrants or updates registrant statuses for the Zoom meeting
	 *
	 * @param int $order_id order ID.
	 */
	public function add_update_zoom_registrants( $order_id ) {

		if ( ! $this->fooevents_uses_zoom() ) {
			return;
		}

		global $wpdb;

		$order_tickets = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"
			SELECT p.*
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_type = 'event_magic_tickets'
			AND pm.meta_key = 'WooCommerceEventsOrderID'
			AND pm.meta_value = %d
			",
				$order_id
			)
		);

		wp_raise_memory_limit();

		set_time_limit( 0 );

		foreach ( $order_tickets as $ticket ) {

			$ticket_meta = get_post_meta( $ticket->ID );

			$args = array(
				'fooevents_ticket_id' => $ticket->ID,
				'email'               => $ticket_meta['WooCommerceEventsAttendeeEmail'][0],
				'first_name'          => $ticket_meta['WooCommerceEventsAttendeeName'][0],
				'last_name'           => $ticket_meta['WooCommerceEventsAttendeeLastName'][0],
				'phone'               => ! empty( $ticket_meta['WooCommerceEventsAttendeeTelephone'] ) ? $ticket_meta['WooCommerceEventsAttendeeTelephone'][0] : '',
				'org'                 => ! empty( $ticket_meta['WooCommerceEventsAttendeeCompany'] ) ? $ticket_meta['WooCommerceEventsAttendeeCompany'][0] : '',
				'job_title'           => ! empty( $ticket_meta['WooCommerceEventsAttendeeDesignation'] ) ? $ticket_meta['WooCommerceEventsAttendeeDesignation'][0] : '',
			);

			$woocommerce_events_product_id        = $ticket_meta['WooCommerceEventsProductID'][0];
			$woocommerce_events_type              = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsType', true );
			$woocommerce_events_zoom_multi_option = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsZoomMultiOption', true );

			if ( empty( $woocommerce_events_zoom_multi_option ) || 'single' === $woocommerce_events_zoom_multi_option ) {

				// Single meeting.
				$zoom_meeting_id = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsZoomWebinar', true );

				$this->add_update_single_zoom_registrant( $zoom_meeting_id, $args );

			} elseif ( 'multi' === $woocommerce_events_zoom_multi_option ) {

				// Multiple meetings.
				$woocommerce_events_zoom_webinar_multi = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsZoomWebinarMulti', true );

				foreach ( $woocommerce_events_zoom_webinar_multi as $zoom_meeting_id ) {

					$this->add_update_single_zoom_registrant( $zoom_meeting_id, $args );

				}
			} elseif ( 'bookings' === $woocommerce_events_zoom_multi_option ) {

				// Bookable slot meeting.
				$woocommerce_events_booking_options_json = get_post_meta( $woocommerce_events_product_id, 'fooevents_bookings_options_serialized', true );
				$woocommerce_events_booking_options      = json_decode( ! empty( $woocommerce_events_booking_options_json ) ? $woocommerce_events_booking_options_json : '{}', true );
				$woocommerce_events_booking_slot_id      = $ticket_meta['WooCommerceEventsBookingSlotID'][0];
				$woocommerce_events_booking_date_id      = $ticket_meta['WooCommerceEventsBookingDateID'][0];

				if ( isset( $woocommerce_events_booking_options[ $woocommerce_events_booking_slot_id ]['zoom_id'] ) ) {

					$zoom_meeting_id = $woocommerce_events_booking_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ]['zoom_id'];

					$this->add_update_single_zoom_registrant( $zoom_meeting_id, $args );

				}
			}
		}
	}

	/**
	 * Adds a single attendee as registrant or updates registrant status for the provided Zoom meeting
	 *
	 * @param int   $zoom_meeting_id Zoom meeting ID.
	 * @param array $args arguments.
	 */
	public function add_update_single_zoom_registrant( $zoom_meeting_id, $args ) {

		if ( '' !== $zoom_meeting_id ) {

			if ( '' !== $args['email'] && '' !== $args['first_name'] && '' !== $args['last_name'] ) {

				$result = $this->fooevents_register_zoom_attendee( $zoom_meeting_id, $args );

				if ( 'error' === $result['status'] ) {

					// Possibly already exists, try updating to approved.
					$update_args = array(
						'action'      => 'approve',
						'registrants' => array(
							array( 'email' => $args['email'] ),
						),
					);

					$result = $this->fooevents_update_zoom_registration_statuses( $zoom_meeting_id, $update_args );

				} elseif ( isset( $result['data'] ) && isset( $result['data']['join_url'] ) && '' !== $result['data']['join_url'] && isset( $args['fooevents_ticket_id'] ) && '' !== $args['fooevents_ticket_id'] ) {
					update_post_meta( $args['fooevents_ticket_id'], 'WooCommerceEventsZoomJoinURL', $result['data']['join_url'] );
				}
			}
		}
	}

	/**
	 * Cancel registrations for all provided tickets
	 *
	 * @param array $tickets tickets.
	 */
	public function cancel_zoom_registrations( $tickets ) {

		$zoom_registrants = array();

		foreach ( $tickets as $ticket ) {

			$woocommerce_events_product_id        = get_post_meta( $ticket->ID, 'WooCommerceEventsProductID', true );
			$woocommerce_events_type              = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsType', true );
			$woocommerce_events_zoom_multi_option = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsZoomMultiOption', true );
			$woocommerce_events_status            = get_post_meta( $ticket->ID, 'WooCommerceEventsStatus', true );

			$email = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeEmail', true );

			if ( '' === $email ) {

				$email = get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserEmail', true );

			}

			if ( empty( $woocommerce_events_zoom_multi_option ) || 'single' === $woocommerce_events_zoom_multi_option ) {

				// Single meeting.
				$zoom_meeting_id = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsZoomWebinar', true );

				if ( '' !== $zoom_meeting_id ) {

					if ( empty( $zoom_registrants[ (string) $zoom_meeting_id ] ) ) {

						$zoom_registrants[ (string) $zoom_meeting_id ] = array();

					}

					$zoom_registrants[ (string) $zoom_meeting_id ][] = array( 'email' => $email );

				}
			} elseif ( 'multi' === $woocommerce_events_zoom_multi_option ) {

				// Multiple meetings.
				$woocommerce_events_zoom_webinar_multi = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsZoomWebinarMulti', true );

				$multi_count = count( $woocommerce_events_zoom_webinar_multi );

				for ( $i = 1; $i <= $multi_count; $i++ ) {

					$zoom_meeting_id = $woocommerce_events_zoom_webinar_multi[ $i - 1 ];

					if ( '' !== $zoom_meeting_id ) {

						if ( empty( $zoom_registrants[ (string) $zoom_meeting_id ] ) ) {

							$zoom_registrants[ (string) $zoom_meeting_id ] = array();

						}

						$zoom_registrants[ (string) $zoom_meeting_id ][] = array( 'email' => $email );

					}
				}
			} elseif ( 'bookings' === $woocommerce_events_zoom_multi_option ) {

				// Bookable slot meeting.
				$woocommerce_events_booking_options_json = get_post_meta( $woocommerce_events_product_id, 'fooevents_bookings_options_serialized', true );
				$woocommerce_events_booking_options      = json_decode( ! empty( $woocommerce_events_booking_options_json ) ? $woocommerce_events_booking_options_json : '{}', true );
				$woocommerce_events_booking_slot_id      = get_post_meta( $ticket->ID, 'WooCommerceEventsBookingSlotID', true );
				$woocommerce_events_booking_date_id      = get_post_meta( $ticket->ID, 'WooCommerceEventsBookingDateID', true );

				if ( isset( $woocommerce_events_booking_options[ $woocommerce_events_booking_slot_id ]['zoom_id'] ) ) {

					$zoom_meeting_id = $woocommerce_events_booking_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ]['zoom_id'];

					$zoom_registrants[ (string) $zoom_meeting_id ][] = array( 'email' => $email );

				}
			}
		}

		if ( ! empty( $zoom_registrants ) ) {

			foreach ( $zoom_registrants as $zoom_meeting_id => $registrants ) {

				$args = array(
					'action'      => 'cancel',
					'registrants' => $registrants,
				);

				$result = $this->fooevents_update_zoom_registration_statuses( $zoom_meeting_id, $args );

			}
		}
	}

	/**
	 * Register an attendee for a meeting when manually saving a ticket
	 *
	 * @param int $ticket_id ticket ID.
	 */
	public function register_ticket_attendee( $ticket_id ) {

		$ticket_meta = get_post_meta( $ticket_id );

		$args = array(
			'fooevents_ticket_id' => $ticket_id,
			'email'               => ! empty( $ticket_meta['WooCommerceEventsAttendeeEmail'][0] ) ? $ticket_meta['WooCommerceEventsAttendeeEmail'][0] : $ticket_meta['WooCommerceEventsPurchaserEmail'][0],
			'first_name'          => ! empty( $ticket_meta['WooCommerceEventsAttendeeName'][0] ) ? $ticket_meta['WooCommerceEventsAttendeeName'][0] : $ticket_meta['WooCommerceEventsPurchaserFirstName'][0],
			'last_name'           => ! empty( $ticket_meta['WooCommerceEventsAttendeeLastName'][0] ) ? $ticket_meta['WooCommerceEventsAttendeeLastName'][0] : $ticket_meta['WooCommerceEventsPurchaserLastName'][0],
		);

		$woocommerce_events_type              = get_post_meta( $ticket_meta['WooCommerceEventsProductID'][0], 'WooCommerceEventsType', true );
		$woocommerce_events_zoom_multi_option = get_post_meta( $ticket_meta['WooCommerceEventsProductID'][0], 'WooCommerceEventsZoomMultiOption', true );

		if ( empty( $woocommerce_events_zoom_multi_option ) || 'single' === $woocommerce_events_zoom_multi_option ) {

			// Single meeting.
			if ( 'Canceled' !== $ticket_meta['WooCommerceEventsStatus'][0] ) {

				$zoom_meeting_id = get_post_meta( $ticket_meta['WooCommerceEventsProductID'][0], 'WooCommerceEventsZoomWebinar', true );

				if ( '' !== $zoom_meeting_id ) {

					$this->add_update_single_zoom_registrant( $zoom_meeting_id, $args );

				}
			}
		} elseif ( 'multi' === $woocommerce_events_zoom_multi_option ) {

			// Multiple meetings.
			$woocommerce_events_zoom_webinar_multi = get_post_meta( $ticket_meta['WooCommerceEventsProductID'][0], 'WooCommerceEventsZoomWebinarMulti', true );
			$woocommerce_events_multiday_status    = json_decode( $ticket_meta['WooCommerceEventsMultidayStatus'][0], true );

			$multi_count = count( $woocommerce_events_zoom_webinar_multi );

			for ( $i = 1; $i <= $multi_count; $i++ ) {

				$zoom_meeting_id = $woocommerce_events_zoom_webinar_multi[ $i - 1 ];

				if ( '' !== $zoom_meeting_id ) {

					if ( empty( $woocommerce_events_multiday_status ) || ( ! empty( $woocommerce_events_multiday_status ) && 'Canceled' !== $woocommerce_events_multiday_status[ (string) $i ] ) ) {

						$this->add_update_single_zoom_registrant( $zoom_meeting_id, $args );

					}
				}
			}
		} elseif ( 'bookings' === $woocommerce_events_zoom_multi_option ) {

			// Bookable slot meeting.
			$woocommerce_events_booking_options_json = get_post_meta( $ticket_meta['WooCommerceEventsProductID'][0], 'fooevents_bookings_options_serialized', true );
			$woocommerce_events_booking_options      = json_decode( ! empty( $woocommerce_events_booking_options_json ) ? $woocommerce_events_booking_options_json : '{}', true );
			$woocommerce_events_booking_slot_id      = $ticket_meta['WooCommerceEventsBookingSlotID'][0];
			$woocommerce_events_booking_date_id      = $ticket_meta['WooCommerceEventsBookingDateID'][0];

			if ( isset( $woocommerce_events_booking_options[ $woocommerce_events_booking_slot_id ]['zoom_id'] ) ) {

				$zoom_meeting_id = $woocommerce_events_booking_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ]['zoom_id'];

				$this->add_update_single_zoom_registrant( $zoom_meeting_id, $args );

			}
		}
	}

	/**
	 * Get Zoom ID for given ticket.
	 *
	 * @param int $ticket_id ticket ID.
	 */
	public function get_zoom_id_for_ticket( $ticket_id ) {
		$zoom_meeting_id = '';

		$ticket_meta = get_post_meta( $ticket_id );

		$woocommerce_events_zoom_multi_option = get_post_meta( $ticket_meta['WooCommerceEventsProductID'][0], 'WooCommerceEventsZoomMultiOption', true );

		if ( empty( $woocommerce_events_zoom_multi_option ) || 'single' === $woocommerce_events_zoom_multi_option ) {

			// Single meeting.
			if ( 'Canceled' !== $ticket_meta['WooCommerceEventsStatus'][0] ) {
				$zoom_meeting_id = get_post_meta( $ticket_meta['WooCommerceEventsProductID'][0], 'WooCommerceEventsZoomWebinar', true );
			}
		} elseif ( 'multi' === $woocommerce_events_zoom_multi_option ) {

			// Multiple meetings.
			$woocommerce_events_zoom_webinar_multi = get_post_meta( $ticket_meta['WooCommerceEventsProductID'][0], 'WooCommerceEventsZoomWebinarMulti', true );
			$woocommerce_events_multiday_status    = json_decode( $ticket_meta['WooCommerceEventsMultidayStatus'][0], true );

			$multi_count = count( $woocommerce_events_zoom_webinar_multi );

			for ( $i = 1; $i <= $multi_count; $i++ ) {

				$temp_zoom_meeting_id = $woocommerce_events_zoom_webinar_multi[ $i - 1 ];

				if ( '' !== $temp_zoom_meeting_id ) {

					if ( empty( $woocommerce_events_multiday_status ) || ( ! empty( $woocommerce_events_multiday_status ) && 'Canceled' !== $woocommerce_events_multiday_status[ (string) $i ] ) ) {
						$zoom_meeting_id .= $temp_zoom_meeting_id . ',';
					}
				}
			}
		} elseif ( 'bookings' === $woocommerce_events_zoom_multi_option ) {

			// Bookable slot meeting.
			$woocommerce_events_booking_options_json = get_post_meta( $ticket_meta['WooCommerceEventsProductID'][0], 'fooevents_bookings_options_serialized', true );
			$woocommerce_events_booking_options      = json_decode( ! empty( $woocommerce_events_booking_options_json ) ? $woocommerce_events_booking_options_json : '{}', true );
			$woocommerce_events_booking_slot_id      = $ticket_meta['WooCommerceEventsBookingSlotID'][0];
			$woocommerce_events_booking_date_id      = $ticket_meta['WooCommerceEventsBookingDateID'][0];

			if ( isset( $woocommerce_events_booking_options[ $woocommerce_events_booking_slot_id ]['zoom_id'] ) ) {
				$zoom_meeting_id = $woocommerce_events_booking_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ]['zoom_id'];
			}
		}

		return $zoom_meeting_id;
	}

	/**
	 * Generate text to display on the attendee's ticket
	 *
	 * @param array  $options options.
	 * @param string $display display.
	 *
	 * @return string
	 */
	public function get_ticket_text( $options = array(), $display = '' ) {

		if ( ! $this->fooevents_uses_zoom() ) {
			return '';
		}

		$woocommerce_events_product_id = $options['WooCommerceEventsProductID'];
		$registrant_email              = ! empty( $options['registrant_email'] ) ? $options['registrant_email'] : '';
		$zoom_ticket_text              = '';

		$woocommerce_events_type              = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsType', true );
		$woocommerce_events_zoom_multi_option = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsZoomMultiOption', true );

		if ( empty( $woocommerce_events_zoom_multi_option ) || 'single' === $woocommerce_events_zoom_multi_option || 'bookings' === $woocommerce_events_zoom_multi_option ) {

			$zoom_meeting_id = '';

			if ( 'bookings' === $woocommerce_events_zoom_multi_option ) {

				// Bookable slot meeting.
				$woocommerce_events_booking_options_json = get_post_meta( $woocommerce_events_product_id, 'fooevents_bookings_options_serialized', true );
				$woocommerce_events_booking_options      = json_decode( ! empty( $woocommerce_events_booking_options_json ) ? $woocommerce_events_booking_options_json : '{}', true );

				if ( isset( $options['slot_id'] ) && isset( $options['date_id'] ) ) {
					$woocommerce_events_booking_slot_id = $options['slot_id'];
					$woocommerce_events_booking_date_id = $options['date_id'];

					if ( isset( $woocommerce_events_booking_options[ $woocommerce_events_booking_slot_id ]['zoom_id'] ) ) {

						if ( isset( $woocommerce_events_booking_options[ $woocommerce_events_booking_slot_id ]['add_date'] ) ) {

							$zoom_meeting_id = $woocommerce_events_booking_options[ $woocommerce_events_booking_slot_id ]['add_date'][ $woocommerce_events_booking_date_id ]['zoom_id'];

						} else {

							$zoom_meeting_id = $woocommerce_events_booking_options[ $woocommerce_events_booking_slot_id ][ $woocommerce_events_booking_date_id . '_zoom_id' ];

						}
					}
				}
			} else {

				// Single meeting.
				$zoom_meeting_id = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsZoomWebinar', true );

			}

			if ( '' !== $zoom_meeting_id ) {

				$result = $this->do_fooevents_fetch_zoom_meeting( $zoom_meeting_id );

				if ( ! empty( $result['status'] ) && 'success' === $result['status'] ) {

					$zoom_meeting = $result['data'];
					$is_meeting   = in_array( (int) $zoom_meeting['type'], array( 2, 3, 8 ), true );

					if ( 'admin' !== $display ) {

						$zoom_ticket_text .= '<br/><br/>';

						if ( $is_meeting ) {

							$zoom_ticket_text .= '<strong>' . __( 'Zoom Meeting', 'woocommerce-events' ) . '</strong><br/>';

						} else {

							$zoom_ticket_text .= '<strong>' . __( 'Zoom Webinar', 'woocommerce-events' ) . '</strong><br/>';

						}
					}

					$zoom_ticket_text .= __( 'Topic', 'woocommerce-events' ) . ': ' . $zoom_meeting['topic'] . '<br/>';
					$zoom_ticket_text .= ( ( 5 === $zoom_meeting['type'] || 2 === $zoom_meeting['type'] ) ? __( 'Date', 'woocommerce-events' ) : __( 'Start date', 'woocommerce-events' ) ) . ': ' . $zoom_meeting['start_date_display'] . '<br/>';
					$zoom_ticket_text .= __( 'Start time', 'woocommerce-events' ) . ': ' . $zoom_meeting['start_time_display'] . '<br/>';
					$zoom_ticket_text .= __( 'End time', 'woocommerce-events' ) . ': ' . $zoom_meeting['end_time_display'] . '<br/>';
					$zoom_ticket_text .= __( 'Duration', 'woocommerce-events' ) . ': ' . $zoom_meeting['duration_display'] . '<br/>';

					if ( 5 !== $zoom_meeting['type'] && 2 !== $zoom_meeting['type'] ) {

						$zoom_ticket_text .= __( 'Recurrence', 'woocommerce-events' ) . ': ' . $zoom_meeting['recurrence']['type_display'] . '<br/>';

					}

					if ( $is_meeting ) {

						$zoom_ticket_text .= __( 'Meeting ID', 'woocommerce-events' ) . ': ' . $this->format_zoom_id( $zoom_meeting['id'] ) . '<br/>';

						if ( ! empty( $zoom_meeting['password'] ) ) {

							$zoom_ticket_text .= __( 'Meeting password', 'woocommerce-events' ) . ': ' . $zoom_meeting['password'] . '<br/>';

						}
					} else {

						$zoom_ticket_text .= __( 'Webinar ID', 'woocommerce-events' ) . ': ' . $this->format_zoom_id( $zoom_meeting['id'] ) . '<br/>';

						if ( ! empty( $zoom_meeting['password'] ) ) {

							$zoom_ticket_text .= __( 'Webinar password', 'woocommerce-events' ) . ': ' . $zoom_meeting['password'] . '<br/>';

						}
					}

					$join_url = $zoom_meeting['join_url'];

					if ( '' !== $registrant_email ) {

						foreach ( $zoom_meeting['registrants']['registrants'] as $registrant ) {

							if ( $registrant['email'] === $registrant_email ) {

								$join_url = $registrant['join_url'];

								break;

							}
						}
					}

					if ( 'calendar' === $display ) {

						$zoom_ticket_text .= __( 'Join link', 'woocommerce-events' ) . ':<br/><a href="' . $join_url . '">' . $join_url . '</a><br/>';

					} elseif ( 'admin' !== $display ) {

						$zoom_ticket_text .= '<a href="' . $join_url . '">' . ( $is_meeting ? __( 'Join this meeting', 'woocommerce-events' ) : __( 'Join this webinar', 'woocommerce-events' ) ) . '</a><br/>';

					}
				}
			}
		} elseif ( 'multi' === $woocommerce_events_zoom_multi_option ) {

			// Multiple meetings.
			$day_term = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsDayOverride', true );

			if ( empty( $day_term ) ) {

				$day_term = get_option( 'WooCommerceEventsDayOverride', true );

			}

			if ( empty( $day_term ) || 1 === $day_term ) {

				$day_term = __( 'Day', 'woocommerce-events' );

			}

			$woocommerce_events_zoom_webinar_multi = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsZoomWebinarMulti', true );

			if ( ! empty( $woocommerce_events_zoom_webinar_multi ) ) {

				if ( 'admin' !== $display ) {
					$zoom_ticket_text .= '<br/><br/>';
					$zoom_ticket_text .= '<strong>' . __( 'Zoom Meetings and Webinars', 'woocommerce-events' ) . '</strong>';
					$zoom_ticket_text .= '<br/>';
				}

				$multi_count = count( $woocommerce_events_zoom_webinar_multi );

				for ( $i = 1; $i <= $multi_count; $i++ ) {

					$zoom_meeting_id = $woocommerce_events_zoom_webinar_multi[ $i - 1 ];

					if ( '' !== $zoom_meeting_id ) {

						$result = $this->do_fooevents_fetch_zoom_meeting( $zoom_meeting_id );

						if ( ! empty( $result['status'] ) && 'success' === $result['status'] ) {

							$zoom_meeting = $result['data'];

							$is_meeting = in_array( (int) $zoom_meeting['type'], array( 2, 3, 8 ), true );

							if ( $i > 1 ) {
								$zoom_ticket_text .= '<br/>';
							}

							$zoom_ticket_text .= '<strong>' . $day_term . ' ' . $i . ':</strong><br/>';
							$zoom_ticket_text .= __( 'Topic', 'woocommerce-events' ) . ': ' . $zoom_meeting['topic'] . '<br/>';

							$zoom_ticket_text .= ! empty( $zoom_meeting['start_date_display'] ) ? ( ( 5 === $zoom_meeting['type'] || 2 === $zoom_meeting['type'] ) ? __( 'Date', 'woocommerce-events' ) : __( 'Start date', 'woocommerce-events' ) ) . ': ' . $zoom_meeting['start_date_display'] . '<br/>' : '';

							$zoom_ticket_text .= ! empty( $zoom_meeting['start_time_display'] ) ? __( 'Start time', 'woocommerce-events' ) . ': ' . $zoom_meeting['start_time_display'] . '<br/>' : '';
							$zoom_ticket_text .= ! empty( $zoom_meeting['end_time_display'] ) ? __( 'End time', 'woocommerce-events' ) . ': ' . $zoom_meeting['end_time_display'] . '<br/>' : '';
							$zoom_ticket_text .= ! empty( $zoom_meeting['duration_display'] ) ? __( 'Duration', 'woocommerce-events' ) . ': ' . $zoom_meeting['duration_display'] . '<br/>' : '';

							if ( 5 !== $zoom_meeting['type'] && 2 !== $zoom_meeting['type'] ) {

								$zoom_ticket_text .= __( 'Recurrence', 'woocommerce-events' ) . ': ' . $zoom_meeting['recurrence']['type_display'] . '<br/>';

							}

							if ( $is_meeting ) {

								$zoom_ticket_text .= __( 'Meeting ID', 'woocommerce-events' ) . ': ' . $this->format_zoom_id( $zoom_meeting['id'] ) . '<br/>';

								if ( ! empty( $zoom_meeting['password'] ) ) {

									$zoom_ticket_text .= __( 'Meeting password', 'woocommerce-events' ) . ': ' . $zoom_meeting['password'] . '<br/>';

								}
							} else {

								$zoom_ticket_text .= __( 'Webinar ID', 'woocommerce-events' ) . ': ' . $this->format_zoom_id( $zoom_meeting['id'] ) . '<br/>';

								if ( ! empty( $zoom_meeting['password'] ) ) {
									$zoom_ticket_text .= __( 'Webinar password', 'woocommerce-events' ) . ': ' . $zoom_meeting['password'] . '<br/>';
								}
							}

							$join_url = $zoom_meeting['join_url'];

							if ( '' !== $registrant_email ) {

								foreach ( $zoom_meeting['registrants']['registrants'] as $registrant ) {

									if ( $registrant['email'] === $registrant_email ) {

										$join_url = $registrant['join_url'];

										break;

									}
								}
							}

							if ( 'calendar' === $display ) {

								$zoom_ticket_text .= __( 'Join link', 'woocommerce-events' ) . ':<br/><a href="' . $join_url . '">' . $join_url . '</a><br/>';

							} elseif ( 'admin' !== $display ) {

								$zoom_ticket_text .= '<a href="' . $join_url . '">' . ( $is_meeting ? __( 'Join this meeting', 'woocommerce-events' ) : __( 'Join this webinar', 'woocommerce-events' ) ) . '</a><br/>';

							}
						}
					}
				}
			}
		}

		return $zoom_ticket_text;
	}

	/**
	 * Generate text to display in the calendar event's description
	 *
	 * @param array $options options.
	 *
	 * @return string
	 */
	public function get_calendar_text( $options = array() ) {

		$ticket_text = $this->get_ticket_text( $options, 'calendar' );

		$ticket_text = wp_strip_all_tags( str_replace( '<br/>', '\n', $ticket_text ) );

		return $ticket_text;
	}

	/**
	 * Format Zoom meeting/webinar ID
	 *
	 * @param int $zoom_id Zoom ID.
	 *
	 * @return string
	 */
	private function format_zoom_id( $zoom_id ) {

		$return_val = '';

		switch ( strlen( $zoom_id ) ) {

			case 9:
			case 10:
				$return_val = substr( $zoom_id, 0, 3 ) . '-' . substr( $zoom_id, 3, 3 ) . '-' . substr( $zoom_id, 6 );
				break;

			case 11:
				$return_val = substr( $zoom_id, 0, 3 ) . '-' . substr( $zoom_id, 3, 4 ) . '-' . substr( $zoom_id, 7 );
				break;

		}

		return $return_val;
	}

	/**
	 * Create a new Zoom meeting/webinar
	 *
	 * @param array $options options.
	 *
	 * @return string
	 */
	public function create_zoom_meeting( $options = array() ) {

		$result = array( 'status' => 'error' );

		if ( ! empty( $options ) ) {

			$args = array_merge(
				$options,
				array(
					'password' => $this->generate_zoom_passcode(),
					'settings' => array(
						'approval_type'                  => 0,
						'registration_type'              => 1,
						'close_registration'             => true,
						'registrants_email_notification' => true,
						'waiting_room'                   => false,
					),
				)
			);

			$endpoint = $options['type'];
			$type     = 'meetings' === $endpoint ? 2 : 5;

			if ( (int) $options['type'] > 0 ) {

				$type = (int) $options['type'];

				$endpoint = in_array( $type, array( 2, 8 ), true ) ? 'meetings' : 'webinars';

			}

			$args['type'] = $type;

			$result = $this->fooevents_zoom_request( 'users/me/' . $endpoint, $args, '', 'POST' );

			if ( 'success' === $result['status'] && ! empty( $result['data']['id'] ) ) {

				$result['data']['id'] .= '_' . $endpoint;

			}
		}

		return $result;
	}

	/**
	 * Generate a random passcode.
	 *
	 * @since 1.12.17
	 *
	 * @return string
	 */
	public function generate_zoom_passcode() {

		$characters        = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@-_*';
		$characters_length = strlen( $characters );
		$zoom_passcode     = '';

		for ( $i = 0; $i < 10; $i++ ) {

			$zoom_passcode .= $characters[ wp_rand( 0, $characters_length - 1 ) ];

		}

		return $zoom_passcode;
	}

	/**
	 * Update an existing Zoom meeting/webinar
	 *
	 * @param int   $zoom_id Zoom ID.
	 * @param array $options options.
	 *
	 * @return string
	 */
	public function update_zoom_meeting( $zoom_id, $options = array() ) {

		$result = array( 'status' => 'error' );

		if ( ! empty( $options ) ) {

			$args = array_merge(
				$options,
				array(
					'settings' => array(
						'approval_type'     => 0,
						'registration_type' => 1,
					),
				)
			);

			$zoom_id_parts = explode( '_', $zoom_id );
			$temp_zoom_id  = $zoom_id_parts[0];
			$endpoint      = ! empty( $zoom_id_parts[1] ) ? $zoom_id_parts[1] : 'webinars';

			$result = $this->fooevents_zoom_request( $endpoint . '/' . $temp_zoom_id, $args, '', 'PATCH' );

			if ( 'success' === $result['status'] ) {

				$result['data'] = array(
					'id'    => $zoom_id,
					'topic' => $options['topic'],
				);

			}
		}

		return $result;
	}

	/**
	 * Delete an existing Zoom meeting/webinar
	 *
	 * @param int $zoom_id Zoom ID.
	 */
	public function delete_zoom_meeting( $zoom_id = '' ) {

		$result = array( 'status' => 'error' );

		if ( ! empty( $zoom_id ) ) {

			$zoom_id_parts = explode( '_', $zoom_id );
			$zoom_id       = $zoom_id_parts[0];
			$endpoint      = ! empty( $zoom_id_parts[1] ) ? $zoom_id_parts[1] : 'webinars';

			$result = $this->fooevents_zoom_request( $endpoint . '/' . $zoom_id, array(), '', 'DELETE' );

		}

		return $result;
	}

	/**
	 * Add a user as an assistant (scheduler) to the main Zoom account holder
	 *
	 * @param int $user_id user ID.
	 */
	public function add_zoom_assistant( $user_id = '' ) {

		$result = array( 'status' => 'error' );

		if ( ! empty( $user_id ) ) {

			$zoom_admin_user_id = get_option( 'globalWooCommerceEventsZoomAdminID' );

			if ( empty( $zoom_admin_user_id ) ) {
				$result = $this->fooevents_zoom_request( 'users/me' );

				if ( 'success' === $result['status'] ) {
					$zoom_admin_user_id = $result['data']['id'];

					update_option( 'globalWooCommerceEventsZoomAdminID', $zoom_admin_user_id );
				}
			}

			if ( ! empty( $zoom_admin_user_id ) ) {
				$result = $this->fooevents_zoom_request(
					'users/' . $user_id . '/assistants',
					array(
						'assistants' => array(
							array( 'id' => $zoom_admin_user_id ),
						),
					),
					'',
					'POST'
				);
			}
		}

		return $result;
	}

	/**
	 * Get the current or previous Zoom options for a single or multiday Zoom meeting integration from submitted product page save
	 *
	 * @param int     $post_id post ID.
	 * @param boolean $previous previous.
	 * @param array   $previous_post_meta previous post meta.
	 * @param int     $day_index day index.
	 *
	 * @return array
	 */
	public function get_product_zoom_options( $post_id, $previous = false, $previous_post_meta = array(), $day_index = -1 ) {

		$woocommerce_events_type              = $previous ? $previous_post_meta['WooCommerceEventsType'][0] : ( isset( $_POST['WooCommerceEventsType'] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsType'] ) ) : 'single' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$woocommerce_events_zoom_multi_option = $previous ? $previous_post_meta['WooCommerceEventsZoomMultiOption'][0] : ( isset( $_POST['WooCommerceEventsZoomMultiOption'] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsZoomMultiOption'] ) ) : 'single' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( $previous && empty( $previous_post_meta['WooCommerceEventsZoomType'] ) ) {

			return array();

		}

		$woocommerce_events_zoom_host = $previous ? $previous_post_meta['WooCommerceEventsZoomHost'][0] : ( isset( $_POST['WooCommerceEventsZoomHost'] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsZoomHost'] ) ) : 'me' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$topic = html_entity_decode( $previous ? apply_filters( 'the_title', sanitize_text_field( wp_unslash( isset( $_POST['WooCommerceEventsZoomTopic'] ) ? $_POST['WooCommerceEventsZoomTopic'] : '' ) ), $post_id ) : apply_filters( 'the_title', isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '', $post_id ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$start_time_hour    = $previous ? $previous_post_meta['WooCommerceEventsHour'][0] : ( isset( $_POST['WooCommerceEventsHour'] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsHour'] ) ) : '00' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$start_time_minutes = $previous ? $previous_post_meta['WooCommerceEventsMinutes'][0] : ( isset( $_POST['WooCommerceEventsMinutes'] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsMinutes'] ) ) : '00' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$start_time_period  = $previous ? $previous_post_meta['WooCommerceEventsPeriod'][0] : ( isset( $_POST['WooCommerceEventsPeriod'] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsPeriod'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$end_time_hour      = $previous ? $previous_post_meta['WooCommerceEventsHourEnd'][0] : ( isset( $_POST['WooCommerceEventsHourEnd'] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsHourEnd'] ) ) : '01' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$end_time_minutes   = $previous ? $previous_post_meta['WooCommerceEventsMinutesEnd'][0] : ( isset( $_POST['WooCommerceEventsMinutesEnd'] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsMinutesEnd'] ) ) : '00' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$end_time_period    = $previous ? $previous_post_meta['WooCommerceEventsEndPeriod'][0] : ( isset( $_POST['WooCommerceEventsEndPeriod'] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsEndPeriod'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( $day_index > -1 ) {

			if ( 'sequential' === $woocommerce_events_type ) {

				$temp_start_date = $previous ? $previous_post_meta['WooCommerceEventsDate'][0] : ( isset( $_POST['WooCommerceEventsDate'] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDate'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

				if ( 'd/m/Y' === get_option( 'date_format' ) ) {
					$temp_start_date = str_replace( '/', '-', $temp_start_date );
				}

				$temp_start_date = str_replace( ',', '', $temp_start_date );

				$start_date_timestamp = strtotime( $this->convert_month_to_english( $temp_start_date ) . ' + ' . $day_index . ' days' );
				$start_date           = date( get_option( 'date_format' ), $start_date_timestamp ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

			} elseif ( 'select' === $woocommerce_events_type ) {

				$start_date = $previous ? $previous_post_meta['WooCommerceEventsSelectDate'][0][ $day_index ] : ( isset( $_POST['WooCommerceEventsSelectDate'][ $day_index ] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSelectDate'][ $day_index ] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

				if ( ! isset( $_POST['WooCommerceEventsSelectGlobalTime'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$start_time_hour    = $previous ? $previous_post_meta['WooCommerceEventsSelectDateHour'][0][ $day_index ] : ( isset( $_POST['WooCommerceEventsSelectDateHour'][ $day_index ] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSelectDateHour'][ $day_index ] ) ) : '00' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$start_time_minutes = $previous ? $previous_post_meta['WooCommerceEventsSelectDateMinutes'][0][ $day_index ] : ( isset( $_POST['WooCommerceEventsSelectDateMinutes'][ $day_index ] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSelectDateMinutes'][ $day_index ] ) ) : '00' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$start_time_period  = $previous ? $previous_post_meta['WooCommerceEventsSelectDatePeriod'][0][ $day_index ] : ( isset( $_POST['WooCommerceEventsSelectDatePeriod'][ $day_index ] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSelectDatePeriod'][ $day_index ] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$end_time_hour      = $previous ? $previous_post_meta['WooCommerceEventsSelectDateHourEnd'][0][ $day_index ] : ( isset( $_POST['WooCommerceEventsSelectDateHourEnd'][ $day_index ] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSelectDateHourEnd'][ $day_index ] ) ) : '01' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$end_time_minutes   = $previous ? $previous_post_meta['WooCommerceEventsSelectDateMinutesEnd'][0][ $day_index ] : ( isset( $_POST['WooCommerceEventsSelectDateMinutesEnd'][ $day_index ] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSelectDateMinutesEnd'][ $day_index ] ) ) : '00' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$end_time_period    = $previous ? $previous_post_meta['WooCommerceEventsSelectDatePeriodEnd'][0][ $day_index ] : ( isset( $_POST['WooCommerceEventsSelectDatePeriodEnd'][ $day_index ] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsSelectDatePeriodEnd'][ $day_index ] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				}
			}
		} else {

			$start_date = $previous ? $previous_post_meta['WooCommerceEventsDate'][0] : ( isset( $_POST['WooCommerceEventsDate'] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDate'] ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		}

		$duration = 60;

		if ( ( '' === $start_time_period && '' === $end_time_period ) || $start_time_period === $end_time_period ) {

			$duration = ( ( (int) $end_time_hour * 60 ) + (int) $end_time_minutes ) - ( ( (int) $start_time_hour * 60 ) + (int) $start_time_minutes );

		} else {

			$duration = ( ( ( (int) $end_time_hour + ( 'p.m.' === $end_time_period && (int) $end_time_hour < 12 ? 12 : 0 ) ) * 60 ) + (int) $end_time_minutes ) - ( ( (int) $start_time_hour * 60 ) + (int) $start_time_minutes );
		}

		$endpoint = $previous ? $previous_post_meta['WooCommerceEventsZoomType'][0] : ( isset( $_POST['WooCommerceEventsZoomType'] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsZoomType'] ) ) : 'meetings' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$type     = 'meetings' === $endpoint ? ( 'sequential' === $woocommerce_events_type && 'single' === $woocommerce_events_zoom_multi_option ? 8 : 2 ) : ( 'sequential' === $woocommerce_events_type && 'single' === $woocommerce_events_zoom_multi_option ? 9 : 5 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( 'd/m/Y' === get_option( 'date_format' ) ) {
			$start_date = str_replace( '/', '-', $start_date );
		}

		$start_date = str_replace( ',', '', $start_date );

		$start_time_display = $start_date . ' ' . $start_time_hour . ':' . $start_time_minutes . ( '' !== $start_time_period ? ' ' . $start_time_period : '' );
		$start_timestamp    = strtotime( $this->convert_month_to_english( $start_time_display ) );

		$woocommerce_events_timezone = $previous ? $previous_post_meta['WooCommerceEventsTimeZone'][0] : ( isset( $_POST['WooCommerceEventsTimeZone'] ) ? sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsTimeZone'] ) ) : get_option( 'timezone_string' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$zoom_options = array(
			'topic'        => $topic . ( $day_index > -1 ? ' - ' . $start_time_display : '' ),
			'timezone'     => $woocommerce_events_timezone,
			'schedule_for' => $woocommerce_events_zoom_host,
			'duration'     => $duration,
			'type'         => $type,
			'start_time'   => date( 'Y-m-d\TH:i:s', $start_timestamp ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		);

		if ( 'sequential' === $woocommerce_events_type && 'single' === $woocommerce_events_zoom_multi_option ) {

			$num_days = $previous ? (int) $previous_post_meta['WooCommerceEventsNumDays'][0] : ( isset( $_POST['WooCommerceEventsNumDays'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsNumDays'] ) ) : 1 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$zoom_options['recurrence'] = array(
				'type'            => 1,
				'repeat_interval' => 1,
				'end_times'       => $num_days,
			);
		}

		return $zoom_options;
	}

	/**
	 * A quick check to see if Zoom is being used on the site
	 *
	 * @return boolean
	 */
	public function fooevents_uses_zoom() {
		$global_woocommerce_events_zoom_account_id    = get_option( 'globalWooCommerceEventsZoomAccountID', '' );
		$global_woocommerce_events_zoom_client_id     = get_option( 'globalWooCommerceEventsZoomClientID', '' );
		$global_woocommerce_events_zoom_client_secret = get_option( 'globalWooCommerceEventsZoomClientSecret', '' );

		return '' !== $global_woocommerce_events_zoom_account_id && '' !== $global_woocommerce_events_zoom_client_id && '' !== $global_woocommerce_events_zoom_client_secret;
	}

	/**
	 * Array of month names for translation to English
	 *
	 * @param string $event_date event date.
	 * @return string
	 */
	private function convert_month_to_english( $event_date ) {

		$months = array(
			// French.
			'janvier'     => 'January',
			'février'     => 'February',
			'mars'        => 'March',
			'avril'       => 'April',
			'mai'         => 'May',
			'juin'        => 'June',
			'juillet'     => 'July',
			'aout'        => 'August',
			'août'        => 'August',
			'septembre'   => 'September',
			'octobre'     => 'October',

			// German.
			'Januar'      => 'January',
			'Februar'     => 'February',
			'März'        => 'March',
			'Mai'         => 'May',
			'Juni'        => 'June',
			'Juli'        => 'July',
			'Oktober'     => 'October',
			'Dezember'    => 'December',
			'Montag'      => '',
			'Dienstag'    => '',
			'Mittwoch'    => '',
			'Donnerstag'  => '',
			'Freitag'     => '',
			'Samstag'     => '',
			'Sonntag'     => '',

			// Spanish.
			'enero'       => 'January',
			'febrero'     => 'February',
			'marzo'       => 'March',
			'abril'       => 'April',
			'mayo'        => 'May',
			'junio'       => 'June',
			'julio'       => 'July',
			'agosto'      => 'August',
			'septiembre'  => 'September',
			'setiembre'   => 'September',
			'octubre'     => 'October',
			'noviembre'   => 'November',
			'diciembre'   => 'December',
			'novembre'    => 'November',
			'décembre'    => 'December',
			'lunes'       => '',
			'martes'      => '',
			'miércoles'   => '',
			'jueves'      => '',
			'viernes'     => '',
			'sábado'      => '',
			'domingo'     => '',

			// Catalan - Spain.
			'gener'       => 'January',
			'febrer'      => 'February',
			'març'        => 'March',
			'abril'       => 'April',
			'maig'        => 'May',
			'juny'        => 'June',
			'juliol'      => 'July',
			'agost'       => 'August',
			'setembre'    => 'September',
			'octubre'     => 'October',
			'novembre'    => 'November',
			'desembre'    => 'December',

			// Dutch.
			'januari'     => 'January',
			'februari'    => 'February',
			'maart'       => 'March',
			'april'       => 'April',
			'mei'         => 'May',
			'juni'        => 'June',
			'juli'        => 'July',
			'augustus'    => 'August',
			'september'   => 'September',
			'oktober'     => 'October',
			'november'    => 'November',
			'december'    => 'December',
			'maandag'     => '',
			'dinsdag'     => '',
			'woensdag'    => '',
			'donderdag'   => '',
			'vrijdag'     => '',
			'zaterdag'    => '',
			'zondag'      => '',

			// Italian.
			'Gennaio'     => 'January',
			'Febbraio'    => 'February',
			'Marzo'       => 'March',
			'Aprile'      => 'April',
			'Maggio'      => 'May',
			'Giugno'      => 'June',
			'Luglio'      => 'July',
			'Agosto'      => 'August',
			'Settembre'   => 'September',
			'Ottobre'     => 'October',
			'Novembre'    => 'November',
			'Dicembre'    => 'December',

			// Polish.
			'Styczeń'     => 'January',
			'Luty'        => 'February',
			'Marzec'      => 'March',
			'Kwiecień'    => 'April',
			'Maj'         => 'May',
			'Czerwiec'    => 'June',
			'Lipiec'      => 'July',
			'Sierpień'    => 'August',
			'Wrzesień'    => 'September',
			'Październik' => 'October',
			'Listopad'    => 'November',
			'Grudzień'    => 'December',

			// Afrikaans.
			'Januarie'    => 'January',
			'Februarie'   => 'February',
			'Maart'       => 'March',
			'Mei'         => 'May',
			'Junie'       => 'June',
			'Julie'       => 'July',
			'Augustus'    => 'August',
			'Oktober'     => 'October',
			'Desember'    => 'December',

			// Turkish.
			'Ocak'        => 'January',
			'Şubat'       => 'February',
			'Mart'        => 'March',
			'Nisan'       => 'April',
			'Mayıs'       => 'May',
			'Haziran'     => 'June',
			'Temmuz'      => 'July',
			'Ağustos'     => 'August',
			'Eylül'       => 'September',
			'Ekim'        => 'October',
			'Kasım'       => 'November',
			'Aralık'      => 'December',

			// Portuguese.
			'janeiro'     => 'January',
			'fevereiro'   => 'February',
			'março'       => 'March',
			'abril'       => 'April',
			'maio'        => 'May',
			'junho'       => 'June',
			'julho'       => 'July',
			'agosto'      => 'August',
			'setembro'    => 'September',
			'outubro'     => 'October',
			'novembro'    => 'November',
			'dezembro'    => 'December',

			// Swedish.
			'Januari'     => 'January',
			'Februari'    => 'February',
			'Mars'        => 'March',
			'April'       => 'April',
			'Maj'         => 'May',
			'Juni'        => 'June',
			'Juli'        => 'July',
			'Augusti'     => 'August',
			'September'   => 'September',
			'Oktober'     => 'October',
			'November'    => 'November',
			'December'    => 'December',

			// Czech.
			'leden'       => 'January',
			'únor'        => 'February',
			'březen'      => 'March',
			'duben'       => 'April',
			'květen'      => 'May',
			'červen'      => 'June',
			'červenec'    => 'July',
			'srpen'       => 'August',
			'září'        => 'September',
			'říjen'       => 'October',
			'listopad'    => 'November',
			'prosinec'    => 'December',

			// Norwegian.
			'januar'      => 'January',
			'februar'     => 'February',
			'mars'        => 'March',
			'april'       => 'April',
			'mai'         => 'May',
			'juni'        => 'June',
			'juli'        => 'July',
			'august'      => 'August',
			'september'   => 'September',
			'oktober'     => 'October',
			'november'    => 'November',
			'desember'    => 'December',

			// Danish.
			'januar'      => 'January',
			'februar'     => 'February',
			'marts'       => 'March',
			'april'       => 'April',
			'maj'         => 'May',
			'juni'        => 'June',
			'juli'        => 'July',
			'august'      => 'August',
			'september'   => 'September',
			'oktober'     => 'October',
			'november'    => 'November',
			'december'    => 'December',

			// Finnish.
			'tammikuu'    => 'January',
			'helmikuu'    => 'February',
			'maaliskuu'   => 'March',
			'huhtikuu'    => 'April',
			'toukokuu'    => 'May',
			'kesäkuu'     => 'June',
			'heinäkuu'    => 'July',
			'elokuu'      => 'August',
			'syyskuu'     => 'September',
			'lokakuu'     => 'October',
			'marraskuu'   => 'November',
			'joulukuu'    => 'December',

			// Russian.
			'Январь'      => 'January',
			'Февраль'     => 'February',
			'Март'        => 'March',
			'Апрель'      => 'April',
			'Май'         => 'May',
			'Июнь'        => 'June',
			'Июль'        => 'July',
			'Август'      => 'August',
			'Сентябрь'    => 'September',
			'Октябрь'     => 'October',
			'Ноябрь'      => 'November',
			'Декабрь'     => 'December',

			// Icelandic.
			'Janúar'      => 'January',
			'Febrúar'     => 'February',
			'Mars'        => 'March',
			'Apríl'       => 'April',
			'Maí'         => 'May',
			'Júní'        => 'June',
			'Júlí'        => 'July',
			'Ágúst'       => 'August',
			'September'   => 'September',
			'Oktober'     => 'October',
			'Nóvember'    => 'November',
			'Desember'    => 'December',

			// Latvian.
			'janvāris'    => 'January',
			'februāris'   => 'February',
			'marts'       => 'March',
			'aprīlis'     => 'April',
			'maijs'       => 'May',
			'jūnijs'      => 'June',
			'jūlijs'      => 'July',
			'augusts'     => 'August',
			'septembris'  => 'September',
			'oktobris'    => 'October',
			'novembris'   => 'November',
			'decembris'   => 'December',

			// Lithuanian.
			'sausio'      => 'January',
			'vasario'     => 'February',
			'kovo'        => 'March',
			'balandžio'   => 'April',
			'gegužės'     => 'May',
			'birželio'    => 'June',
			'liepos'      => 'July',
			'rugpjūčio'   => 'August',
			'rugsėjo'     => 'September',
			'spalio'      => 'October',
			'lapkričio'   => 'November',
			'gruodžio'    => 'December',

			// Estonian.
			'jaanuar'     => 'January',
			'veebruar'    => 'February',
			'märts'       => 'March',
			'aprill'      => 'April',
			'mai'         => 'May',
			'juuni'       => 'June',
			'juuli'       => 'July',
			'august'      => 'August',
			'september'   => 'September',
			'oktoober'    => 'October',
			'november'    => 'November',
			'detsember'   => 'December',

			// Greek.
			'Ιανουάριος'  => 'January',
			'Φεβρουάριος' => 'February',
			'Μάρτιος'     => 'March',
			'Απρίλιος'    => 'April',
			'Μάιος'       => 'May',
			'Ιούνιος'     => 'June',
			'Ιούλιος'     => 'July',
			'Αύγουστος'   => 'August',
			'Σεπτέμβριος' => 'September',
			'Οκτώβριος'   => 'October',
			'Νοέμβριος'   => 'November',
			'Δεκέμβριος'  => 'December',

			// Slovak - Slovakia.
			'január'      => 'January',
			'február'     => 'February',
			'marec'       => 'March',
			'apríl'       => 'April',
			'máj'         => 'May',
			'jún'         => 'June',
			'júl'         => 'July',
			'august'      => 'August',
			'september'   => 'September',
			'október'     => 'October',
			'november'    => 'November',
			'december'    => 'December',

			// Slovenian - Slovenia.
			'januar'      => 'January',
			'februar'     => 'February',
			'marec'       => 'March',
			'april'       => 'April',
			'maj'         => 'May',
			'junij'       => 'June',
			'julij'       => 'July',
			'avgust'      => 'August',
			'september'   => 'September',
			'oktober'     => 'October',
			'november'    => 'November',
			'december'    => 'December',

			// Romanian - Romania.
			'ianuarie'    => 'January',
			'februarie'   => 'February',
			'martie'      => 'March',
			'aprilie'     => 'April',
			'mai'         => 'May',
			'iunie'       => 'June',
			'iulie'       => 'July',
			'august'      => 'August',
			'septembrie'  => 'September',
			'octombrie'   => 'October',
			'noiembrie'   => 'November',
			'decembrie'   => 'December',

			// Croatian - Croatia.

			'siječanj'    => 'January',
			'veljača'     => 'February',
			'ožujak'      => 'March',
			'travanj'     => 'April',
			'svibanj'     => 'May',
			'lipanj'      => 'June',
			'srpanj'      => 'July',
			'kolovoz'     => 'August',
			'rujan'       => 'September',
			'listopad'    => 'October',
			'studeni'     => 'November',
			'prosinac'    => 'December',

			// Bulgarian - Bulgaria.

			'януари'      => 'January',
			'февруари'    => 'February',
			'март'        => 'March',
			'април'       => 'April',
			'май'         => 'May',
			'юни'         => 'June',
			'юли'         => 'July',
			'август'      => 'August',
			'септември'   => 'September',
			'октомври'    => 'October',
			'ноември'     => 'November',
			'декември'    => 'December',
		);

		$pattern     = array_keys( $months );
		$replacement = array_values( $months );

		foreach ( $pattern as $key => $value ) {
			$pattern[ $key ] = '/\b' . $value . '\b/iu';
		}

		$replaced_event_date = preg_replace( $pattern, $replacement, $event_date );

		$replaced_event_date = str_replace( ' de ', ' ', $replaced_event_date );

		return $replaced_event_date;
	}
}
