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
class FooEvents_Blocks_Event_Attendees {

	/**
	 * Configuration object
	 *
	 * @var array $config contains paths and other configurations
	 */
	private $config;

	/**
	 * On plugin load
	 *
	 * @param array $config the configuration array.
	 */
	public function __construct( $config ) {

		$this->config = $config;

		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Register FooEvents Blocks
	 */
	public function register_blocks() {

		register_block_type(
			$this->config->path . '/build/fooevents-event-attendees',
			array(
				'render_callback' => array( $this, 'display_event_attendees' ),
			)
		);
	}

	/**
	 * Block output
	 *
	 * @param array $attr attributes from block settings.
	 * @return string
	 */
	public function display_event_attendees( $attr ) {

		$attendees = $this->get_attendees( $attr );

		$layout = $attr['layout'];

		ob_start();

		require $this->config->template_path . 'blocks/event-attendees-js.php';

		if ( 'pictures' === $layout ) {

			require $this->config->template_path . 'blocks/event-attendees-pictures.php';

		} elseif ( 'list' === $layout ) {

			require $this->config->template_path . 'blocks/event-attendees-list.php';

		} else {

			require $this->config->template_path . 'blocks/event-attendees-grid.php';

		}

		return ob_get_clean();
	}

	/**
	 * Get all attendees based on Event IDs
	 *
	 * @param array $attr the attributes.
	 * @return array
	 */
	public function get_attendees( $attr ) {

		$product_ids = $attr['productIDs'];

		$limit = 0;

		if ( $attr['limitNumAttendees'] && ! empty( $attr['numberOfAttendees'] ) ) {

			$limit = $attr['numberOfAttendees'];

		}

		$attendees          = array();
		$returned_attendees = array();
		$x                  = 0;

		if ( is_product() ) {

			$product_ids[] = get_the_ID();

		}

		if ( ! empty( $product_ids ) ) {

			foreach ( $product_ids as $product_id ) {

				$query = new WP_Query(
					array(
						'post_type'      => 'event_magic_tickets',
						'meta_key'       => 'WooCommerceEventsProductID', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'meta_value'     => $product_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
						'posts_per_page' => -1,
					)
				);

				$tickets = $query->get_posts();

				foreach ( $tickets as $ticket ) {

					++$x;

					$attendees['attendees'][ $x ]['WooCommerceEventsAttendeeName']        = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeName', true );
					$attendees['attendees'][ $x ]['WooCommerceEventsAttendeeLastName']    = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeLastName', true );
					$attendees['attendees'][ $x ]['WooCommerceEventsAttendeeEmail']       = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeEmail', true );
					$attendees['attendees'][ $x ]['WooCommerceEventsAttendeeTelephone']   = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeTelephone', true );
					$attendees['attendees'][ $x ]['WooCommerceEventsAttendeeCompany']     = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeCompany', true );
					$attendees['attendees'][ $x ]['WooCommerceEventsAttendeeDesignation'] = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeDesignation', true );
					$attendees['attendees'][ $x ]['WooCommerceEventsTicketID']            = get_post_meta( $ticket->ID, 'WooCommerceEventsTicketID', true );
					$attendees['attendees'][ $x ]['WooCommerceEventsOrderID']             = get_post_meta( $ticket->ID, 'WooCommerceEventsOrderID', true );
					$attendees['attendees'][ $x ]['full_name']                            = $attendees['attendees'][ $x ]['WooCommerceEventsAttendeeName'] . ' ' . $attendees['attendees'][ $x ]['WooCommerceEventsAttendeeLastName'];
					$attendees['attendees'][ $x ]['first_name']                           = $attendees['attendees'][ $x ]['WooCommerceEventsAttendeeName'];
					$attendees['attendees'][ $x ]['last_name']                            = $attendees['attendees'][ $x ]['WooCommerceEventsAttendeeLastName'];
					$attendees['attendees'][ $x ]['first_name_initial']                   = $attendees['attendees'][ $x ]['WooCommerceEventsAttendeeName'] . ' ' . strtoupper( $attendees['attendees'][ $x ]['WooCommerceEventsAttendeeLastName'][0] ) . '.';
					$attendees['attendees'][ $x ]['initial_last_name']                    = strtoupper( $attendees['attendees'][ $x ]['WooCommerceEventsAttendeeName'][0] ) . '. ' . $attendees['attendees'][ $x ]['WooCommerceEventsAttendeeLastName'];
					$attendees['attendees'][ $x ]['create_date']                          = $ticket->post_date;

					if ( empty( $attendees['attendees'][ $x ]['WooCommerceEventsAttendeeName'] ) || empty( $attendees['attendees'][ $x ]['WooCommerceEventsAttendeeLastName'] ) || empty( $attendees['attendees'][ $x ]['WooCommerceEventsAttendeeEmail'] ) ) {

						$order = wc_get_order( $attendees['attendees'][ $x ]['WooCommerceEventsOrderID'] );

						$attendees['attendees'][ $x ]['WooCommerceEventsAttendeeName']      = $order->get_billing_first_name();
						$attendees['attendees'][ $x ]['WooCommerceEventsAttendeeLastName']  = $order->get_billing_last_name();
						$attendees['attendees'][ $x ]['WooCommerceEventsAttendeeEmail']     = $order->get_billing_email();
						$attendees['attendees'][ $x ]['WooCommerceEventsAttendeeTelephone'] = $order->get_billing_phone();
						$attendees['attendees'][ $x ]['WooCommerceEventsAttendeeCompany']   = $order->get_billing_company();

					}
				}
			}

			if ( ! empty( $attendees ) ) {

				if ( $attr['uniqueAttendees'] ) {

					$attendees['attendees'] = $this->unique_attendee_array( $attendees['attendees'], 'full_name' );

				}

				if ( 'firstname' === $attr['sortAttendeeBy'] && 'asc' === $attr['sortAttendeeOrder'] ) {

					usort( $attendees['attendees'], array( $this, 'sort_by_first_name' ) );

				} elseif ( 'firstname' === $attr['sortAttendeeBy'] && 'desc' === $attr['sortAttendeeOrder'] ) {

					usort( $attendees['attendees'], array( $this, 'sort_by_first_name_desc' ) );

				} elseif ( 'lastname' === $attr['sortAttendeeBy'] && 'asc' === $attr['sortAttendeeOrder'] ) {

					usort( $attendees['attendees'], array( $this, 'sort_by_last_name' ) );

				} elseif ( 'lastname' === $attr['sortAttendeeBy'] && 'desc' === $attr['sortAttendeeOrder'] ) {

					usort( $attendees['attendees'], array( $this, 'sort_by_last_name_desc' ) );

				} elseif ( 'createdate' === $attr['sortAttendeeBy'] && 'desc' === $attr['sortAttendeeOrder'] ) {

					usort( $attendees['attendees'], array( $this, 'sort_by_create_date_desc' ) );

				} elseif ( 'createdate' === $attr['sortAttendeeBy'] && 'asc' === $attr['sortAttendeeOrder'] ) {

					usort( $attendees['attendees'], array( $this, 'sort_by_create_date' ) );

				} elseif ( 'rand' === $attr['sortAttendeeBy'] ) {

					shuffle( $attendees['attendees'] );

				}

				if ( 0 !== $limit && count( $attendees['attendees'] ) > $limit ) {

					for ( $i = 0; $i < $limit; $i++ ) {

						$returned_attendees['attendees'][ $i ] = $attendees['attendees'][ $i ];

					}
				} else {

					$returned_attendees['attendees'] = $attendees['attendees'];

				}
			}
		}

		if ( ! empty( $attendees ) ) {

			$returned_attendees['total_attendees']          = count( $attendees['attendees'] );
			$returned_attendees['total_returned_attendees'] = count( $returned_attendees['attendees'] );

		}

		return $returned_attendees;
	}

	/**
	 * Return unique multi-dimensional array based on provided key value.
	 *
	 * @param array  $array the multi-dimensional array to be unique.
	 * @param string $key the key to use for unique comparisson.
	 * @return array
	 */
	private function unique_attendee_array( $array, $key ) {

		$temp_array = array();
		$i          = 0;
		$key_array  = array();

		foreach ( $array as $val ) {
			if ( ! in_array( $val[ $key ], $key_array ) ) {
				$key_array[ $i ]  = $val[ $key ];
				$temp_array[ $i ] = $val;
			}
			++$i;
		}

		return $temp_array;
	}

	/**
	 * Sort by first name
	 *
	 * @param string $a The first value.
	 * @param string $b The second value.
	 */
	private function sort_by_first_name( $a, $b ) {

		return strcmp( $a['WooCommerceEventsAttendeeName'], $b['WooCommerceEventsAttendeeName'] );
	}

	/**
	 * Sort by first name descending
	 *
	 * @param string $a The first value.
	 * @param string $b The second value.
	 */
	private function sort_by_first_name_desc( $a, $b ) {

		return strcmp( $b['WooCommerceEventsAttendeeName'], $a['WooCommerceEventsAttendeeName'] );
	}

	/**
	 * Sort by last name
	 *
	 * @param string $a The first value.
	 * @param string $b The second value.
	 */
	private function sort_by_last_name( $a, $b ) {

		return strcmp( $a['WooCommerceEventsAttendeeLastName'], $b['WooCommerceEventsAttendeeLastName'] );
	}

	/**
	 * Sort by last name descending
	 *
	 * @param string $a The first value.
	 * @param string $b The second value.
	 */
	private function sort_by_last_name_desc( $a, $b ) {

		return strcmp( $b['WooCommerceEventsAttendeeLastName'], $a['WooCommerceEventsAttendeeLastName'] );
	}

	/**
	 * Sort by create date
	 *
	 * @param string $a The first value.
	 * @param string $b The second value.
	 */
	private function sort_by_create_date( $a, $b ) {

		$ts1 = strtotime( $a['create_date'] );
		$ts2 = strtotime( $b['create_date'] );
		return $ts1 - $ts2;
	}

	/**
	 * Sort by create date descending
	 *
	 * @param string $a The first value.
	 * @param string $b The second value.
	 */
	private function sort_by_create_date_desc( $a, $b ) {

		$ts1 = strtotime( $a['create_date'] );
		$ts2 = strtotime( $b['create_date'] );
		return $ts2 - $ts1;
	}
}
