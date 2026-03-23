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
class FooEvents_Blocks_Event_Listing {

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
	 * Return post meta value
	 *
	 * @param array  $post the post.
	 * @param string $field_name the field name.
	 */
	public function rest_get_post_field( $post, $field_name ) {

		return get_post_meta( $post['id'], $field_name, true );
	}

	/**
	 * Register FooEvents Blocks
	 */
	public function register_blocks() {

		register_block_type(
			$this->config->path . '/build/fooevents-event-listing',
			array(
				'render_callback' => array( $this, 'event_listing_frontend' ),
			)
		);
	}

	/**
	 * Outputs the frontend of the event listing block
	 *
	 * @param array $attr attributes from block settings.
	 * @return string
	 */
	public function event_listing_frontend( $attr ) {

		$events = $this->get_all_events( $attr );

		$layout = $attr['eventListingLayout'];

		foreach ( $events as $key => $event ) {

			if ( empty( $event ) ) {

				unset( $events[ $key ] );

			}

			$ticket_term = get_post_meta( $event['post_id'], 'WooCommerceEventsTicketOverride', true );

			if ( empty( $ticket_term ) ) {

				$ticket_term = get_option( 'globalWooCommerceEventsTicketOverride', true );

			}

			if ( empty( $ticket_term ) || 1 === (int) $ticket_term ) {

				$ticket_term = __( 'Book ticket', 'woocommerce-events' );

			}

			$events[ $key ]['ticketTerm'] = $ticket_term;

		}

		ob_start();

		if ( 'list' === $layout ) {

			require $this->config->template_path . 'blocks/event-listing.php';

		} elseif ( 'tiles' === $layout ) {

			require $this->config->template_path . 'blocks/event-listing-tiles.php';

		} elseif ( 'compact' === $layout ) {

			require $this->config->template_path . 'blocks/event-listing-compact.php';

		}

		return ob_get_clean();
	}

	/**
	 * Get all events to be used by events listing block.
	 *
	 * @param array $attr the attributes.
	 * @return array
	 */
	public function get_all_events( $attr ) {

		$events = array();

		if ( in_array( $attr['eventTypes'], array( 'all', 'single-multi' ), true ) ) {

			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'WooCommerceEventsEvent',
						'value'   => 'Event',
						'compare' => '=',
					),
				),
			);

			if ( ! empty( $attr['hashtags'] ) ) {

				$args['tax_query'] = array( 'relation' => 'OR' ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query

				foreach ( $attr['hashtags'] as $include_cat ) {

					$args['tax_query'][] = array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => $include_cat,
					);

				}
			}

			if ( ! empty( $attr['productIDs'] ) ) {

				$args['post__in'] = $attr['productIDs'];

			}

			$events = get_posts( $args );

			$events = $this->fetch_events( $events, 'events_list', true );

		}

		if ( in_array( $attr['eventTypes'], array( 'all', 'bookings' ), true ) ) {

			if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

				$fooevents_bookings = new FooEvents_Bookings();
				$booking_events     = $fooevents_bookings->get_bookings_for_calendar( $attr['hashtags'], $attr['productIDs'] );

				$events = array_merge_recursive( $events, $booking_events );

			}
		}

		$events = $this->sort_events_by_date( $events, $attr['orderEventBy'] );

		$events = array_slice( $events, 0, $attr['numberOfItems'] );

		return $events;
	}

	/**
	 * Process fetched events
	 *
	 * @param array  $events events.
	 * @param string $display_type output display type.
	 * @param bool   $include_desc include description.
	 * @return array
	 */
	public function fetch_events( $events, $display_type, $include_desc = true ) {

		$json_events    = array();
		$wp_date_format = get_option( 'date_format' );

		$x = 0;
		foreach ( $events as $event ) {

			$fooevents_multiday_events = '';
			$multi_day_type            = '';

			if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

				require_once ABSPATH . '/wp-admin/includes/plugin.php';

			}

			if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

				$fooevents_multiday_events = new Fooevents_Multiday_Events();
				$multi_day_type            = $fooevents_multiday_events->get_multi_day_type( $event->ID );

			}

			$product  = wc_get_product( $event->ID );
			$stock    = '';
			$in_stock = '';

			if ( $product ) {
				$stock    = $product->get_stock_quantity();
				$in_stock = $product->is_in_stock();
			}

			$event_date_unformated  = get_post_meta( $event->ID, 'WooCommerceEventsDate', true );
			$event_type             = get_post_meta( $event->ID, 'WooCommerceEventsType', true );
			$event_hour             = get_post_meta( $event->ID, 'WooCommerceEventsHour', true );
			$event_hour_end         = get_post_meta( $event->ID, 'WooCommerceEventsHourEnd', true );
			$event_minutes          = get_post_meta( $event->ID, 'WooCommerceEventsMinutes', true );
			$event_minutes_end      = get_post_meta( $event->ID, 'WooCommerceEventsMinutesEnd', true );
			$event_timestamp        = get_post_meta( $event->ID, 'WooCommerceEventsDateTimestamp', true );
			$event_period           = get_post_meta( $event->ID, 'WooCommerceEventsPeriod', true );
			$event_end_period       = get_post_meta( $event->ID, 'WooCommerceEventsEndPeriod', true );
			$event_timezone         = get_post_meta( $event->ID, 'WooCommerceEventsTimeZone', true );
			$event_background_color = get_post_meta( $event->ID, 'WooCommerceEventsBackgroundColor', true );
			$event_text_color       = get_post_meta( $event->ID, 'WooCommerceEventsTextColor', true );
			$event_expire           = get_post_meta( $event->ID, 'WooCommerceEventsExpireTimestamp', true );
			$location               = get_post_meta( $event->ID, 'WooCommerceEventsLocation', true );
			$events_expire_option   = get_option( 'globalWooCommerceEventsExpireOption' );
			$today                  = current_time( 'timestamp' );
			$event_start_time       = $event_hour . ':' . $event_minutes . ' ' . $event_period;
			$event_end_time         = $event_hour_end . ':' . $event_minutes_end . ' ' . $event_end_period;

			// Check if event has expired.
			if ( 'hide' === $events_expire_option && ! empty( $event_expire ) && $today >= $event_expire ) {

				continue;

			}

			if ( empty( $event_date_unformated ) ) {

				if ( 'select' !== $multi_day_type ) {

					continue;

				}
			}

			$event_date = $event_date_unformated . ' ' . $event_hour . ':' . $event_minutes . $event_period;
			$event_date = $this->convert_month_to_english( $event_date );
			$format     = get_option( 'date_format' );
			$event_date = str_replace( ',', '', $event_date );

			if ( 'd/m/Y' === $format ) {

				$event_date = str_replace( '/', '-', $event_date );

			}

			$event_date = date_i18n( 'Y-m-d H:i:s', strtotime( $event_date ) );
			$event_date = str_replace( ' ', 'T', $event_date );

			$all_day_event        = false;
			$global_all_day_event = get_option( 'globalFooEventsAllDayEvent' );

			if ( 'yes' === $global_all_day_event ) {

				$all_day_event = true;

			}

			if ( '' !== $event_timezone ) {

				$timezone_date = new DateTime();

				try {

					$tz = new DateTimeZone( $event_timezone );

				} catch ( Exception $e ) {

					$server_timezone = date_default_timezone_get();
					$tz              = new DateTimeZone( $server_timezone );

				}

				$timezone_date->setTimeZone( $tz );
				$timezone = $timezone_date->format( 'T' );
				if ( (int) $timezone > 0 ) {
					$timezone = 'UTC' . $timezone;
				}
			} else {

				$timezone = '';

			}

			if ( 'bookings' !== $event_type ) {

				$json_events['events'][ $x ] = array(
					'title'                 => $event->post_title,
					'allDay'                => $all_day_event,
					'start'                 => $event_date,
					'unformated_date'       => $event_date_unformated,
					'unformated_start_time' => $event_start_time,
					'unformated_end_time'   => $event_end_time,
					'timestamp'             => strtotime( $event_date ),
					'timezone'              => $timezone,
					'url'                   => get_permalink( $event->ID ),
					'location'              => $location,
					'post_id'               => $event->ID,
					'stock_num'             => $stock,
				);

			}

			if ( ! empty( $event_background_color ) ) {

				$json_events['events'][ $x ]['color'] = $event_background_color;

			}

			if ( ! empty( $event_text_color ) ) {

				$json_events['events'][ $x ]['textColor'] = $event_text_color;

			}

			if ( $include_desc ) {

				$json_events['events'][ $x ]['desc'] = $event->post_excerpt;

			}

			if ( 'select' === $multi_day_type ) {

				unset( $json_events['events'][ $x ] );
				--$x;

			}

			if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

				require_once ABSPATH . '/wp-admin/includes/plugin.php';

			}

			if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

				$event_end_date  = $fooevents_multiday_events->get_end_date( $event->ID );
				$event_start_day = get_option( 'globalFooEventsStartDay' );

				$multi_day_dates = array();

				if ( 'select' === $multi_day_type ) {

					$multi_day_dates       = get_post_meta( $event->ID, 'WooCommerceEventsSelectDate', true );
					$multi_day_hours       = get_post_meta( $event->ID, 'WooCommerceEventsSelectDateHour', true );
					$multi_day_hours_end   = get_post_meta( $event->ID, 'WooCommerceEventsSelectDateHourEnd', true );
					$multi_day_minutes     = get_post_meta( $event->ID, 'WooCommerceEventsSelectDateMinutes', true );
					$multi_day_minutes_end = get_post_meta( $event->ID, 'WooCommerceEventsSelectDateMinutesEnd', true );
					$multi_day_period      = get_post_meta( $event->ID, 'WooCommerceEventsSelectDatePeriod', true );
					$multi_day_period_end  = get_post_meta( $event->ID, 'WooCommerceEventsSelectDatePeriodEnd', true );

					if ( 'events_list' === $display_type ) {

						// $multi_day_dates = array($multi_day_dates[0]);

					}

					if ( ! empty( $multi_day_dates ) ) {

						$y = 0;
						$z = 1;
						foreach ( $multi_day_dates as $date ) {

							if ( ( 'eventlist' === $event_start_day || 'both' === $event_start_day ) && 'events_list' === $display_type && $y > 0 ) {

									continue;

							}

							if ( ( 'calendar' === $event_start_day || 'both' === $event_start_day ) && 'events_list' !== $display_type && $y > 0 ) {

									continue;

							}

							++$x;

							$event_date       = '';
							$event_start_time = '';
							$event_end_time   = '';
							if ( isset( $multi_day_hours[ $y ] ) && isset( $multi_day_minutes[ $y ] ) ) {

								$event_date       = $date . ' ' . $multi_day_hours[ $y ] . ':' . $multi_day_minutes[ $y ] . $multi_day_period[ $y ];
								$event_start_time = $multi_day_hours[ $y ] . ':' . $multi_day_minutes[ $y ] . $multi_day_period[ $y ];

							} else {

								$event_date       = $date . ' ' . $event_hour . ':' . $event_minutes . $event_period;
								$event_start_time = $event_hour . ':' . $event_minutes . $event_period;

							}

							if ( isset( $multi_day_hours_end[ $y ] ) && isset( $multi_day_minutes_end[ $y ] ) ) {

								$event_end_time = $multi_day_hours_end[ $y ] . ':' . $multi_day_minutes_end[ $y ] . $multi_day_period_end[ $y ];

							} else {

								$event_end_time = $event_hour . ':' . $event_minutes . $event_period;

							}

							$event_date = $this->convert_month_to_english( $event_date );
							$event_date = str_replace( ',', '', $event_date );

							if ( 'd/m/Y' === $format ) {

								$event_date = str_replace( '/', '-', $event_date );

							}

							$event_date = date( 'Y-m-d H:i:s', strtotime( $event_date ) );
							$event_date = str_replace( ' ', 'T', $event_date );

							$json_events['events'][ $x ] = array(
								'title'                 => $event->post_title,
								'allDay'                => $all_day_event,
								'start'                 => $event_date,
								'unformated_date'       => $date,
								'unformated_start_time' => $event_start_time,
								'timestamp'             => $event_timestamp,
								'unformated_end_time'   => $event_end_time,
								'url'                   => get_permalink( $event->ID ),
								'location'              => $location,
								'post_id'               => $event->ID,
								'multi_day'             => 'selected',
								'stock_num'             => $stock,
							);

							if ( $include_desc ) {

								$json_events['events'][ $x ]['desc'] = $event->post_excerpt;

							}

							if ( ! empty( $event_background_color ) ) {

								$json_events['events'][ $x ]['color'] = $event_background_color;

							}

							if ( ! empty( $event_text_color ) ) {

								$json_events['events'][ $x ]['textColor'] = $event_text_color;

							}

							$product = '';

							if ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {

								$product = wc_get_product( $event->ID );

							}

							if ( ! empty( $product ) ) {

								if ( $product->is_in_stock() ) {

									$json_events['events'][ $x ]['in_stock'] = 'yes';

								} else {

									$json_events['events'][ $x ]['in_stock'] = 'no';

								}
							} else {

								// Not a product so make in stock.
								$json_events['events'][ $x ]['in_stock'] = 'yes';

							}

							++$y;
							++$z;

						}
					} elseif ( ! empty( $event_end_date ) ) {

							$event_end_date_formatted = $fooevents_multiday_events->format_end_date( $event->ID, '', $display_type );

						if ( 'yes' !== $event_start_day ) {

							$json_events['events'][ $x ]['end']                 = $event_end_date_formatted;
							$json_events['events'][ $x ]['unformated_end_date'] = $event_end_date;

						}
					}
				} elseif ( ! empty( $event_end_date ) ) {

						$event_end_date_formatted = $fooevents_multiday_events->format_end_date( $event->ID, true, $display_type );

					if ( ( 'calendar' !== $event_start_day && 'both' !== $event_start_day ) ) {

							$json_events['events'][ $x ]['end']             = $event_end_date_formatted;
						$json_events['events'][ $x ]['unformated_end_date'] = $event_end_date;

					}
				}
			}

			$product = '';

			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {

				$product = wc_get_product( $event->ID );

			}

			if ( ! empty( $product ) ) {

				if ( $product->is_in_stock() ) {

					$json_events['events'][ $x ]['in_stock'] = 'yes';

				} else {

					$json_events['events'][ $x ]['in_stock'] = 'no';

				}
			} else {

				// Not a product so make in stock.
				$json_events['events'][ $x ]['in_stock'] = 'yes';

			}

			$timestamp   = get_post_meta( $event->ID, 'WooCommerceEventsExpireTimestamp', true );
			$event_event = get_post_meta( $event->ID, 'WooCommerceEventsEvent', true );
			$today       = time();

			if ( ! empty( $timestamp ) && 'Event' === $event_event && $today > $timestamp ) {

				$json_events['events'][ $x ]['className'] = 'fooevents-expired-event-calendar';

			}

			++$x;

		}

		return $json_events;
	}

	/**
	 * Sorts events either ascending or descending
	 *
	 * @param array  $events events.
	 * @param string $sort asc/desc.
	 * @return array
	 */
	public function sort_events_by_date( $events, $sort ) {

		if ( ! empty( $events ) ) {

			$events = $events['events'];

			if ( 'asc' === strtolower( $sort ) ) {

				usort( $events, array( $this, 'event_date_compare_asc' ) );

			} else {

				usort( $events, array( $this, 'event_date_compare_desc' ) );

			}

			foreach ( $events as $key => $event ) {

				if ( empty( $event['title'] ) ) {

					unset( $events[ $key ] );

				}
			}
		}
		return $events;
	}

	/**
	 * Compares two dates in ascending order
	 *
	 * @param array $a first date.
	 * @param array $b second date.
	 * @return array
	 */
	public function event_date_compare_asc( $a, $b ) {

		if ( empty( $a ) ) {

			$a = array( 'start' => '' );

		}

		if ( empty( $a['start'] ) ) {

			$a = array( 'start' => '' );

		}

		if ( empty( $b ) ) {

			$b = array( 'start' => '' );

		}

		if ( empty( $b['start'] ) ) {

			$b = array( 'start' => '' );

		}

		$t1 = strtotime( $a['start'] );
		$t2 = strtotime( $b['start'] );

		return $t1 - $t2;
	}

	/**
	 * Compares two dates in descending order
	 *
	 * @param array $a first date.
	 * @param array $b second date.
	 * @return array
	 */
	public function event_date_compare_desc( $a, $b ) {

		if ( empty( $a ) ) {

			$a = array( 'start' => '' );

		}

		if ( empty( $a['start'] ) ) {

			$a = array( 'start' => '' );

		}

		if ( empty( $b ) ) {

			$b = array( 'start' => '' );

		}

		if ( empty( $b['start'] ) ) {

			$b = array( 'start' => '' );

		}

		$t2 = strtotime( $a['start'] );
		$t1 = strtotime( $b['start'] );

		return $t1 - $t2;
	}

	/**
	 * Array of month names for translation to English
	 *
	 * @param string $event_date unprocessed date.
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
