<?php
/**
 * Mailchimp helper class
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailchimp helper class
 */
class FooEvents_Mailchimp_Helper {

	/**
	 * Configuration object
	 *
	 * @var array $config contains paths and other configurations
	 */
	public $config;

	/**
	 * Mailchimp API Key
	 *
	 * @var string $mailchimp_api_key Mailchimp API Key
	 */
	public $mailchimp_api_key;

	/**
	 * Mailchimp Server Prefix
	 *
	 * @var string $mailchimp_server_prefix Mailchimp Server Prefix
	 */
	public $mailchimp_server_prefix;

	/**
	 * Mailchimp API URL
	 *
	 * @var string $mailchimp_url Mailchimp API URL
	 */
	public $mailchimp_url;

	/**
	 * Mailchimp authentication headers
	 *
	 * @var array $mailchimp_headers Mailchimp authentication headers
	 */
	public $mailchimp_headers;

	/**
	 * On class load
	 *
	 * @param array $config configuration values.
	 */
	public function __construct( $config ) {

		$this->config = $config;

		$this->mailchimp_api_key       = get_option( 'globalWooCommerceEventsMailchimpAPIKey' );
		$this->mailchimp_server_prefix = get_option( 'globalWooCommerceEventsMailchimpServer' );

		if ( ! empty( $this->mailchimp_server_prefix ) ) {

			$this->mailchimp_url = 'https://' . $this->mailchimp_server_prefix . '.api.mailchimp.com/3.0/';

		}

		$this->mailchimp_headers = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Basic ' . base64_encode( 'user:' . $this->mailchimp_api_key ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		);

	}

	/**
	 * Pings the Mailchimp service to test the API credentials
	 *
	 * @return bool
	 */
	public function ping_mailchimp() {

		if ( ! empty( $this->mailchimp_api_key ) && ! empty( $this->mailchimp_server_prefix ) ) {

			$response = wp_remote_get(
				$this->mailchimp_url . 'ping/',
				array(
					'headers' => $this->mailchimp_headers,
				)
			);

			if ( is_array( $response ) && ! is_wp_error( $response ) && 200 === $response['response']['code'] ) {

				return 'yes';

			} else {

				return 'no';

			}
		} else {

			return 'no';

		}

	}

	/**
	 * Gets all lists associated with account
	 */
	public function get_lists() {

		if ( ! empty( $this->mailchimp_api_key ) && ! empty( $this->mailchimp_server_prefix ) ) {

			$response = wp_remote_get(
				$this->mailchimp_url . 'lists/?count=100',
				array(
					'headers' => $this->mailchimp_headers,
				)
			);

			if ( is_array( $response ) && ! is_wp_error( $response ) ) {

				$response = json_decode( wp_remote_retrieve_body( $response ) );

				$return_lists = array();
				if ( ! empty( $response->lists ) ) {

					foreach ( $response->lists as $list ) {

						$return_lists[ $list->id ] = $list->name;

					}
				}

				return $return_lists;

			} else {

				return false;

			}
		} else {

			return false;

		}

	}

	/**
	 * Pushes attendee to all available Mailchimp lists
	 *
	 * @param int    $event_id Event ID.
	 * @param string $purchaser_first_name The purchaser first name.
	 * @param string $purchaser_last_name The purchaser last name.
	 * @param string $purchaser_email_address The purchaser email address.
	 * @param string $attendee_first_name The attendee first name.
	 * @param string $attendee_last_name The attendee last name.
	 * @param string $attendee_email_address The attendee email address.
	 */
	public function push_to_lists( $event_id, $purchaser_first_name, $purchaser_last_name, $purchaser_email_address, $attendee_first_name = '', $attendee_last_name = '', $attendee_email_address = '' ) {

		$lists = $this->get_send_to_list( $event_id );
		$tags  = $this->get_all_tags( $event_id );

		foreach ( $lists as $list ) {

			$attendee = array(
				'email_address' => $attendee_email_address,
				'status'        => 'subscribed',
				'merge_fields'  => array(
					'FNAME' => $attendee_first_name,
					'LNAME' => $attendee_last_name,
				),
			);

			if ( ! empty( $tags ) ) {

				$attendee['tags'] = array_values( $tags );

			}

			$attendee = wp_json_encode( $attendee );

			$subscriber_hash = md5( strtolower( $attendee_email_address ) );

			$response = wp_remote_request(
				$this->mailchimp_url . 'lists/' . $list . '/members/' . $subscriber_hash,
				array(
					'headers' => $this->mailchimp_headers,
					'body'    => $attendee,
					'method'  => 'PUT',
				)
			);

		}

	}

	/**
	 * Returns all lists an attendee should be added to.
	 *
	 * @param int $event_id Event ID.
	 */
	private function get_send_to_list( $event_id ) {

		$return_lists                             = array();
		$global_woocommerce_events_mailchimp_list = get_option( 'globalWooCommerceEventsMailchimpList' );
		$woocommerce_events_mailchimp_list        = get_post_meta( $event_id, 'WooCommerceEventsMailchimpList', true );

		if ( ! empty( $woocommerce_events_mailchimp_list ) ) {

			array_push( $return_lists, $woocommerce_events_mailchimp_list );

		} elseif ( ! empty( $global_woocommerce_events_mailchimp_list ) ) {

			array_push( $return_lists, $global_woocommerce_events_mailchimp_list );

		}

		return $return_lists;

	}

	/**
	 * Returns all tags an attendee should be tagged with.
	 *
	 * @param int $event_id Event ID.
	 */
	private function get_all_tags( $event_id ) {

		$return_tags                              = array();
		$global_woocommerce_events_mailchimp_tags = get_option( 'globalWooCommerceEventsMailchimpTags' );
		$woocommerce_events_mailchimp_tags        = get_post_meta( $event_id, 'WooCommerceEventsMailchimpTags', true );

		if ( ! empty( $woocommerce_events_mailchimp_tags ) ) {

			$return_tags = array_map( 'trim', explode( ',', $woocommerce_events_mailchimp_tags ) );

		} elseif ( ! empty( $global_woocommerce_events_mailchimp_tags ) ) {

			$return_tags = array_map( 'trim', explode( ',', $global_woocommerce_events_mailchimp_tags ) );

		}

		$return_tags = array_filter( $return_tags );

		return $return_tags;

	}

}
