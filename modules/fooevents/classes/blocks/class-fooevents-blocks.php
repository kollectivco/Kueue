<?php
/**
 * Main plugin class.
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * Main plugin class.
 */
class FooEvents_Blocks {

	/**
	 * Configuration object
	 *
	 * @var array $config contains paths and other configurations.
	 */
	private $config;

	/**
	 * Blocks array
	 *
	 * @var array $blocks contains all the FooEvents block objects.
	 */
	public $blocks;

	/**
	 * On plugin load
	 */
	public function __construct() {

		$this->config = new FooEvents_Config();

		require_once $this->config->class_path . 'blocks/class-fooevents-blocks-event-listing.php';
		$this->blocks['event_listing'] = new FooEvents_Blocks_Event_Listing( $this->config );

		require_once $this->config->class_path . 'blocks/class-fooevents-blocks-event-attendees.php';
		$this->blocks['event_attendees'] = new FooEvents_Blocks_Event_Attendees( $this->config );

		add_action( 'rest_api_init', array( $this, 'register_block_rest_api_endpoints' ) );

	}

	/**
	 * Register REST API enpoints used by blocks.
	 */
	public function register_block_rest_api_endpoints() {

		register_rest_route(
			'fooevents/v1',
			'/events/',
			array(
				'methods'  => 'GET',
				'callback' => array(
					$this,
					'get_events',
				),
				'permission_callback' => '__return_true',
			)
		);

	}


	/**
	 * Returns all event products
	 */
	public function get_events() {

		$events = new WP_Query(
			array(
				'post_type'      => array( 'product' ),
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'   => 'WooCommerceEventsEvent',
						'value' => 'Event',
					),
				),
			)
		);
		$events = $events->get_posts();

		echo wp_json_encode( $events );
	}

}

