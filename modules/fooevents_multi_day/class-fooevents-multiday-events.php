<?php
/**
 * Main plugin class.
 *
 * @link https://www.fooevents.com
 * @package fooevents-multiday-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * Main plugin class.
 */
class Fooevents_Multiday_Events {

	/**
	 * Configuration object
	 *
	 * @var object $config contains paths and other configurations
	 */
	private $config;

	/**
	 * Update helper object
	 *
	 * @var object $update_helper responsible for plugin updates
	 */
	private $update_helper;

	/**
	 * On plugin load
	 */
	public function __construct() {

		$this->plugin_init();

		add_action( 'init', array( $this, 'load_text_domain' ) );
		add_action( 'admin_init', array( $this, 'register_scripts' ) );
	}

	/**
	 * Initializes plugin
	 */
	public function plugin_init() {

		// Main config.
		$this->config = new Fooevents_Multiday_Events_Config();

		// UpdateHelper.
		require_once $this->config->class_path . 'updatehelper.php';
		$this->update_helper = new Fooevents_Multiday_Events_Update_Helper( $this->config );
	}

	/**
	 * Register plugin scripts.
	 */
	public function register_scripts() {

		global $wp_locale;

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'events-multi-day-script', $this->config->scripts_path . 'events-multi-day-admin.js', array( 'jquery-ui-datepicker', 'wp-color-picker' ), $this->config->plugin_data['Version'], true );

		$day_term = '';
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['post'] ) ) {

			//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$post_id  = sanitize_text_field( wp_unslash( $_GET['post'] ) );
			$day_term = get_post_meta( $post_id, 'WooCommerceEventsDayOverride', true );

		}

		if ( empty( $day_term ) ) {

			$day_term = get_option( 'WooCommerceEventsDayOverride', true );

		}

		if ( empty( $day_term ) || 1 == $day_term ) {

			$day_term = __( 'Day', 'fooevents-multiday-events' );

		}

			$local_args = array(
				'closeText'       => __( 'Done', 'woocommerce-events' ),
				'currentText'     => __( 'Today', 'woocommerce-events' ),
				'monthNames'      => $this->strip_array_indices( $wp_locale->month ),
				'monthNamesShort' => $this->strip_array_indices( $wp_locale->month_abbrev ),
				'monthStatus'     => __( 'Show a different month', 'woocommerce-events' ),
				'dayNames'        => $this->strip_array_indices( $wp_locale->weekday ),
				'dayNamesShort'   => $this->strip_array_indices( $wp_locale->weekday_abbrev ),
				'dayNamesMin'     => $this->strip_array_indices( $wp_locale->weekday_initial ),
				'dateFormat'      => $this->date_format_php_to_js( get_option( 'date_format' ) ),
				'firstDay'        => get_option( 'start_of_week' ),
				'isRTL'           => $wp_locale->is_rtl(),
				'dayTerm'         => esc_attr( $day_term ),
				'startTimeTerm'   => __( 'Start time', 'fooevents-multiday-events' ),
				'endTimeTerm'     => __( 'End time', 'fooevents-multiday-events' ),
			);

			wp_localize_script( 'events-multi-day-script', 'localObjMultiDay', $local_args );
	}

	/**
	 * Loads text-domain for localization
	 */
	public function load_text_domain() {

		$path   = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		$loaded = load_plugin_textdomain( 'fooevents-multiday-events', false, $path );
	}

	/**
	 * Displays event end date option
	 *
	 * @param object $post post.
	 * @return string
	 */
	public function generate_end_date_option( $post ) {

		ob_start();

		$woocommerce_events_end_date = get_post_meta( $post->ID, 'WooCommerceEventsEndDate', true );

		require $this->config->template_path . 'end-date-option.php';

		$end_date_option = ob_get_clean();

		return $end_date_option;
	}

	/**
	 * Displays event number of days option
	 *
	 * @param object $post post.
	 * @return string
	 */
	public function generate_num_days_option( $post ) {

		ob_start();

		$woocommerce_events_num_days = (int) get_post_meta( $post->ID, 'WooCommerceEventsNumDays', true );

		require $this->config->template_path . 'num-days-option.php';

		$num_days_option = ob_get_clean();

		return $num_days_option;
	}

	/**
	 * Returns a string of event dates seperated by a comma
	 *
	 * @param int $product_id the product ID.
	 * @return string
	 */
	public function get_comma_seperated_select_dates( $product_id ) {

		$woocommerce_events_type        = get_post_meta( $product_id, 'WooCommerceEventsType', true );
		$woocommerce_events_select_date = get_post_meta( $product_id, 'WooCommerceEventsSelectDate', true );

		$returned_dates = '';

		if ( 'select' === $woocommerce_events_type ) {

			$number_dates = count( $woocommerce_events_select_date );

			if ( ! empty( $number_dates ) ) {

				$x = 1;
				foreach ( $woocommerce_events_select_date as $date ) {

					$returned_dates .= $date;
					if ( $x < $number_dates ) {

						$returned_dates .= ', ';

					}

					++$x;

				}
			}
		}

		return $returned_dates;
	}

	/**
	 * Displays event multiday type option
	 *
	 * @param object $post post.
	 * @return string
	 */
	public function generate_multiday_select_date_container( $post ) {

		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		$bookings_enabled = false;

		if ( $this->is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

			$bookings_enabled = true;

		}

		ob_start();

		$woocommerce_events_type                    = get_post_meta( $post->ID, 'WooCommerceEventsType', true );
		$woocommerce_events_select_date             = get_post_meta( $post->ID, 'WooCommerceEventsSelectDate', true );
		$woocommerce_events_select_date_hour        = get_post_meta( $post->ID, 'WooCommerceEventsSelectDateHour', true );
		$woocommerce_events_select_date_minutes     = get_post_meta( $post->ID, 'WooCommerceEventsSelectDateMinutes', true );
		$woocommerce_events_select_date_period      = get_post_meta( $post->ID, 'WooCommerceEventsSelectDatePeriod', true );
		$woocommerce_events_select_date_hour_end    = get_post_meta( $post->ID, 'WooCommerceEventsSelectDateHourEnd', true );
		$woocommerce_events_select_date_minutes_end = get_post_meta( $post->ID, 'WooCommerceEventsSelectDateMinutesEnd', true );
		$woocommerce_events_select_date_period_end  = get_post_meta( $post->ID, 'WooCommerceEventsSelectDatePeriodEnd', true );
		$woocommerce_events_select_global_time      = get_post_meta( $post->ID, 'WooCommerceEventsSelectGlobalTime', true );

		$woocommerce_events_hour        = get_post_meta( $post->ID, 'WooCommerceEventsHour', true );
		$woocommerce_events_minutes     = get_post_meta( $post->ID, 'WooCommerceEventsMinutes', true );
		$woocommerce_events_period      = get_post_meta( $post->ID, 'WooCommerceEventsPeriod', true );
		$woocommerce_events_hour_end    = get_post_meta( $post->ID, 'WooCommerceEventsHourEnd', true );
		$woocommerce_events_minutes_end = get_post_meta( $post->ID, 'WooCommerceEventsMinutesEnd', true );
		$woocommerce_events_end_period  = get_post_meta( $post->ID, 'WooCommerceEventsEndPeriod', true );

		$day_term = get_post_meta( $post->ID, 'WooCommerceEventsDayOverride', true );

		if ( empty( $day_term ) ) {

			$day_term = get_option( 'WooCommerceEventsDayOverride', true );

		}

		if ( empty( $day_term ) || 1 == $day_term ) {

			$day_term = __( 'Day', 'fooevents-multiday-events' );

		}

		require $this->config->template_path . 'multiday-select-date-container.php';

		$multiday_type_option = ob_get_clean();

		return $multiday_type_option;
	}

	/**
	 * Displays event multiday term options
	 *
	 * @param object $post post.
	 * @return string
	 */
	public function generate_multiday_term_option( $post ) {

		ob_start();

		$woocommerce_events_day_override        = get_post_meta( $post->ID, 'WooCommerceEventsDayOverride', true );
		$woocommerce_events_day_override_plural = get_post_meta( $post->ID, 'WooCommerceEventsDayOverridePlural', true );

		require $this->config->template_path . 'multiday-term-option.php';

		$multiday_term_option = ob_get_clean();

		return $multiday_term_option;
	}

	/**
	 * Get event date range for display
	 *
	 * @param int $id event id.
	 * @return string
	 */
	public function get_multi_day_date_range( $id ) {

		$woocommerce_events_type = get_post_meta( $id, 'WooCommerceEventsType', true );
		$event_date              = '';

		if ( 'sequential' === $woocommerce_events_type ) {

			$event_date     = get_post_meta( $id, 'WooCommerceEventsDate', true );
			$end_event_date = get_post_meta( $id, 'WooCommerceEventsEndDate', true );

			if ( ! empty( $end_event_date ) ) {

				$event_date = $event_date . ' - ' . $end_event_date;

			}
		} elseif ( 'select' === $woocommerce_events_type ) {

			$woocommerce_events_select_date = get_post_meta( $id, 'WooCommerceEventsSelectDate', true );

			$event_date     = $woocommerce_events_select_date[0];
			$end_event_date = $woocommerce_events_select_date[ count( $woocommerce_events_select_date ) - 1 ];

			if ( ! empty( $end_event_date ) ) {

				$event_date = $event_date . ' - ' . $end_event_date;

			}
		}

		if ( empty( $event_date ) ) {

			$event_date = get_post_meta( $id, 'WooCommerceEventsDate', true );

		}

		return $event_date;
	}

	/**
	 * Gets the multiday status for an event
	 *
	 * @param int $id event id.
	 * @return array
	 */
	public function get_multiday_status( $id ) {

		$woocommerce_events_multiday_status = get_post_meta( $id, 'WooCommerceEventsMultidayStatus', true );

		if ( ! empty( $woocommerce_events_multiday_status ) && ! is_array( $woocommerce_events_multiday_status ) ) {

			$woocommerce_events_multiday_status = json_decode( $woocommerce_events_multiday_status, true );

		} else {

			$woocommerce_events_multiday_status = array();

		}

		return $woocommerce_events_multiday_status;
	}

	/**
	 * Get the multiday checkins for day
	 *
	 * @param int $id event id.
	 * @return array
	 */
	public function get_multiday_check_in_times( $id ) {

		$woocommerce_events_multiday_check_in_time        = get_post_meta( $id, 'WooCommerceEventsMultidayCheckInTime', true );
		$woocommerce_events_multiday_check_in_time_return = array();

		if ( ! empty( $woocommerce_events_multiday_check_in_time ) ) {

			$woocommerce_events_multiday_check_in_time = json_decode( $woocommerce_events_multiday_check_in_time, true );

			foreach ( $woocommerce_events_multiday_check_in_time as $day => $time ) {

				$woocommerce_events_multiday_check_in_time_return[ $day ] = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) . ' (P)', $time );

			}
		}

		return $woocommerce_events_multiday_check_in_time_return;
	}

	/**
	 * Displays the multi day meta on the ticket detail screen
	 *
	 * @param type $id event id.
	 * @return string
	 */
	public function display_multiday_status_ticket_meta_all( $id ) {

		ob_start();

		$woocommerce_events_multiday_status = $this->get_multiday_status( $id );

		$woocommerce_events_product_id = get_post_meta( $id, 'WooCommerceEventsProductID', true );
		$woocommerce_events_status     = get_post_meta( $id, 'WooCommerceEventsStatus', true );
		$woocommerce_events_num_days   = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsNumDays', true );

		if ( empty( $woocommerce_events_num_days ) ) {

			$woocommerce_events_num_days = 1;

		}

		for ( $x = 1; $x <= $woocommerce_events_num_days; $x++ ) {

			if ( empty( $woocommerce_events_multiday_status[ $x ] ) ) {

				$woocommerce_events_multiday_status[ $x ] = 'Not Checked In';

			}
		}

		ksort( $woocommerce_events_multiday_status );

		$day_term = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsDayOverride', true );

		if ( empty( $day_term ) ) {

			$day_term = get_option( 'WooCommerceEventsDayOverride', true );

		}

		if ( empty( $day_term ) || 1 == $day_term ) {

			$day_term = __( 'Day', 'fooevents-multiday-events' );

		}

		if ( ! empty( $woocommerce_events_status ) && 'Unpaid' !== $woocommerce_events_status && 'Canceled' !== $woocommerce_events_status && 'Cancelled' !== $woocommerce_events_status ) {

			require $this->config->template_path . 'display-multiday-status-ticket-meta-all.php';

		}

		$multiday_status = ob_get_clean();

		return $multiday_status;
	}

	/**
	 * Output multi-day checking times
	 *
	 * @param int $id event id.
	 * @return string
	 */
	public function display_multiday_check_in_times( $id ) {

		ob_start();

		$woocommerce_events_multiday_check_in_time = $this->get_multiday_check_in_times( $id );
		$woocommerce_events_product_id             = get_post_meta( $id, 'WooCommerceEventsProductID', true );
		$woocommerce_events_num_days               = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsNumDays', true );

		if ( empty( $woocommerce_events_num_days ) ) {

			$woocommerce_events_num_days = 1;

		}

		for ( $x = 1; $x <= $woocommerce_events_num_days; $x++ ) {

			if ( empty( $woocommerce_events_multiday_check_in_time[ $x ] ) ) {

				$woocommerce_events_multiday_check_in_time[ $x ] = '';

			}
		}

		ksort( $woocommerce_events_multiday_check_in_time );

		$day_term = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsDayOverride', true );

		if ( empty( $day_term ) ) {

			$day_term = get_option( 'WooCommerceEventsDayOverride', true );

		}

		if ( empty( $day_term ) ) {

			$day_term = __( 'Day', 'fooevents-multiday-events' );

		}

		require $this->config->template_path . 'display-multiday-check-in-times.php';

		$multiday_times = ob_get_clean();

		return $multiday_times;
	}

	/**
	 * Gets an array of check-ins, used in the CSV export
	 *
	 * @param int $id id.
	 * @param int $woocommerce_events_num_days event num days.
	 */
	public function get_array_of_check_ins( $id, $woocommerce_events_num_days ) {

		if ( empty( $woocommerce_events_num_days ) ) {

			$woocommerce_events_num_days = 1;

		}

		$woocommerce_events_multiday_status = $this->get_multiday_status( $id );

		if ( empty( $woocommerce_events_multiday_status ) ) {

			for ( $x = 1; $x <= $woocommerce_events_num_days; $x++ ) {

				$woocommerce_events_multiday_status[ $x ] = 'Not Checked In';

			}
		}

		for ( $x = 1; $x <= $woocommerce_events_num_days; $x++ ) {

			if ( empty( $woocommerce_events_multiday_status[ $x ] ) ) {

				$woocommerce_events_multiday_status[ $x ] = 'Not Checked In';

			}
		}

		$woocommerce_events_multiday_status_processed = array();

		foreach ( $woocommerce_events_multiday_status as $day => $value ) {

			if ( $day <= $woocommerce_events_num_days ) {

				$woocommerce_events_multiday_status_processed[ sprintf( __( 'Day %s', 'woocommerce-events' ), $day ) ] = $value;

			}
		}

		return $woocommerce_events_multiday_status_processed;
	}

	/**
	 * Processes muli-day functionality with express check-ins
	 *
	 * @param int    $id id.
	 * @param string $multiday multiday.
	 * @param string $day day.
	 * @return string
	 */
	public function display_multiday_status_ticket_meta( $id, $multiday, $day ) {

		ob_start();

		$woocommerce_events_multiday_status = $this->get_multiday_status( $id );

		$woocommerce_events_product_id = get_post_meta( $id, 'WooCommerceEventsProductID', true );
		$woocommerce_events_status     = get_post_meta( $id, 'WooCommerceEventsStatus', true );
		$woocommerce_events_num_days   = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsNumDays', true );

		if ( empty( $woocommerce_events_num_days ) ) {

			$woocommerce_events_num_days = 1;

		}

		if ( empty( $woocommerce_events_multiday_status[ $day ] ) ) {

			// $woocommerce_events_multiday_status[$day] = 'Not Checked In';
			return;

		}

		ksort( $woocommerce_events_multiday_status );

		if ( ! empty( $woocommerce_events_multiday_status ) && 'Unpaid' !== $woocommerce_events_status && 'Canceled' !== $woocommerce_events_status && 'Cancelled' !== $woocommerce_events_status ) {

			$day_term = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsDayOverride', true );

			if ( empty( $day_term ) ) {

				$day_term = get_option( 'WooCommerceEventsDayOverride', true );

			}

			if ( empty( $day_term ) || 1 === $day_term ) {

				$day_term = __( 'Day', 'fooevents-multiday-events' );

			}

			require $this->config->template_path . 'display-multiday-status-ticket-meta.php';

		}

		$multiday_status = ob_get_clean();

		return $multiday_status;
	}

	/**
	 * Processes muli-day functionality with express check-ins
	 *
	 * @param int    $id id.
	 * @param string $multiday multiday.
	 * @param string $day day.
	 * @return string
	 */
	public function display_multiday_status_ticket_meta_day( $id, $multiday, $day ) {

		$woocommerce_events_multiday_status = $this->get_multiday_status( $id );
		$woocommerce_events_product_id      = get_post_meta( $id, 'WooCommerceEventsProductID', true );
		$woocommerce_events_status          = get_post_meta( $id, 'WooCommerceEventsStatus', true );
		$woocommerce_events_num_days        = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsNumDays', true );

		if ( empty( $woocommerce_events_num_days ) ) {

			$woocommerce_events_num_days = 1;

		}

		if ( empty( $woocommerce_events_multiday_status[ $day ] ) ) {

			$woocommerce_events_multiday_status[ $day ] = 'Not Checked In';

		}

		return $woocommerce_events_multiday_status[ $day ];
	}

	/**
	 * Undoes autocompleted check-ins in the express check-in plugin
	 *
	 * @param int    $id id.
	 * @param string $multiday multiday.
	 * @param string $day day.
	 */
	public function undo_express_check_in_status_auto_complete( $id, $multiday, $day ) {

		$day                                = (int) $id;
		$woocommerce_events_multiday_status = get_post_meta( $id, 'WooCommerceEventsMultidayStatus', true );
		$woocommerce_events_multiday_status = json_decode( $woocommerce_events_multiday_status, true );

		if ( isset( $woocommerce_events_multiday_status[ $day ] ) ) {

			$woocommerce_events_multiday_status[ $day ] = 'Not Checked In';

			$woocommerce_events_multiday_status = wp_json_encode( $woocommerce_events_multiday_status );
			update_post_meta( $id, 'WooCommerceEventsMultidayStatus', $woocommerce_events_multiday_status );

		}
	}

	/**
	 * Capture multi-day status
	 *
	 * @param int $post_ID the post id.
	 */
	public function capture_multiday_status_ticket_meta( $post_ID ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'fooevents_check_in';

		$nonce = '';
		if ( isset( $_POST['fooevents_multi_day_status_update_nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_POST['fooevents_multi_day_status_update_nonce'] ) );
		}

		if ( ! wp_verify_nonce( $nonce, 'fooevents_multi_day_status_update' ) ) {
			// die( esc_attr__( 'Security check failed - FooEvents Multi-Day 0001', 'fooevents-multiday-events' ) );
		}

		if ( isset( $_POST ) && isset( $_POST['ticket_status'] ) && 'true' === $_POST['ticket_status'] && isset( $_POST['WooCommerceEventsStatusMultidayEvent'] ) ) {

			$woocommerce_events_multiday_status_original = get_post_meta( $post_ID, 'WooCommerceEventsMultidayStatus', true );
			$woocommerce_events_product_id               = get_post_meta( $post_ID, 'WooCommerceEventsProductID', true );
			$woocommerce_events_multiday_status_original = json_decode( $woocommerce_events_multiday_status_original, true );
			$woocommerce_events_multiday_status          = array_map( 'sanitize_text_field', wp_unslash( $_POST['WooCommerceEventsStatusMultidayEvent'] ) );
			$wooocmmerce_events_multiday_status_encoded  = wp_json_encode( $woocommerce_events_multiday_status );

			$dates_to_checkin = array();
			if ( ! empty( $woocommerce_events_multiday_status ) ) {

				foreach ( $woocommerce_events_multiday_status as $day => $status ) {

					if ( empty( $woocommerce_events_multiday_status_original ) && 'Checked In' === $status ) {

						$timestamp = current_time( 'timestamp' );
						$wpdb->insert(
							$table_name,
							array(
								'tid'     => $post_ID,
								'eid'     => $woocommerce_events_product_id,
								'day'     => $day,
								'uid'     => get_current_user_id(),
								'status'  => $status,
								'checkin' => $timestamp,
							)
						);

						do_action( 'fooevents_check_in_ticket', array( $post_ID, $status, $timestamp ) );

					} elseif ( ! empty( $woocommerce_events_multiday_status_original ) && $woocommerce_events_multiday_status_original[ $day ] !== $status ) {

						$timestamp = current_time( 'timestamp' );
						$wpdb->insert(
							$table_name,
							array(
								'tid'     => $post_ID,
								'eid'     => $woocommerce_events_product_id,
								'day'     => $day,
								'uid'     => get_current_user_id(),
								'status'  => $status,
								'checkin' => $timestamp,
							)
						);

						do_action( 'fooevents_check_in_ticket', array( $post_ID, $status, $timestamp ) );

					}
				}
			}

			update_post_meta( $post_ID, 'WooCommerceEventsMultidayStatus', $wooocmmerce_events_multiday_status_encoded );

		}
	}

	/**
	 * Displays multi-day settings in the express check-in plugin
	 *
	 * @return string
	 */
	public function display_multiday_express_check_in_options() {

		ob_start();

		require $this->config->template_path . 'display-multiday-express-check-in-options.php';

		$multiday_options = ob_get_clean();

		return $multiday_options;
	}

	/**
	 * Processes checkings the express check-in plugin
	 *
	 * @param int    $id id.
	 * @param string $update_value update value.
	 * @param string $multiday multiday.
	 * @param string $day day.
	 */
	public function update_express_check_in_status( $id, $update_value, $multiday, $day ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'fooevents_check_in';

		if ( $multiday ) {

			$timestamp                          = current_time( 'timestamp' );
			$day                                = sanitize_text_field( $day );
			$woocommerce_events_multiday_status = get_post_meta( $id, 'WooCommerceEventsMultidayStatus', true );
			$event_id                           = get_post_meta( $id, 'WooCommerceEventsProductID', true );
			$woocommerce_events_multiday_status = json_decode( $woocommerce_events_multiday_status, true );

			if ( ! is_array( $woocommerce_events_multiday_status ) ) {

				$woocommerce_events_multiday_status = array();

			}

			if ( ! empty( $update_value ) ) {

				$woocommerce_events_multiday_check_in_time[ $day ] = $timestamp;

				$wpdb->insert(
					$table_name,
					array(
						'tid'     => $id,
						'eid'     => $event_id,
						'day'     => $day,
						'uid'     => get_current_user_id(),
						'status'  => $update_value,
						'checkin' => $timestamp,
					)
				);

			}

			$woocommerce_events_multiday_status[ $day ] = sanitize_text_field( $update_value );
			$woocommerce_events_multiday_status         = wp_json_encode( $woocommerce_events_multiday_status );
			update_post_meta( $id, 'WooCommerceEventsMultidayStatus', $woocommerce_events_multiday_status );

			return true;

		}
	}

	/**
	 * Displays the multi-day form in the ticket status meta box
	 *
	 * @param int $id id.
	 * @return string
	 */
	public function display_multiday_status_ticket_form_meta( $id ) {

		ob_start();

		$woocommerce_events_multiday_status = $this->get_multiday_status( $id );

		$woocommerce_events_product_id = get_post_meta( $id, 'WooCommerceEventsProductID', true );
		$woocommerce_events_num_days   = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsNumDays', true );
		$woocommerce_events_status     = '';

		if ( empty( $woocommerce_events_num_days ) ) {

			$woocommerce_events_num_days = 1;

		}

		for ( $x = 1; $x <= $woocommerce_events_num_days; $x++ ) {

			if ( empty( $woocommerce_events_multiday_status[ $x ] ) ) {

				$woocommerce_events_multiday_status[ $x ] = 'Not Checked In';

			}
		}

		ksort( $woocommerce_events_multiday_status );

		$day_term = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsDayOverride', true );

		if ( empty( $day_term ) ) {

			$day_term = get_option( 'WooCommerceEventsDayOverride', true );

		}

		if ( empty( $day_term ) || 1 == $day_term ) {

			$day_term = __( 'Day', 'woocommerce-events' );

		}

		require $this->config->template_path . 'display-multiday-selection-form.php';

		$display_multiday_selection_form = ob_get_clean();

		return $display_multiday_selection_form;
	}

	/**
	 * Returns the event end date
	 *
	 * @param in $id id.
	 * @return string
	 */
	public function get_end_date( $id ) {

		return get_post_meta( $id, 'WooCommerceEventsEndDate', true );
	}


	/**
	 * Returns the multi-day type
	 *
	 * @param type $id id.
	 */
	public function get_multi_day_type( $id ) {

		return get_post_meta( $id, 'WooCommerceEventsType', true );
	}

	/**
	 * Returns the multi-day status
	 *
	 * @param type $id id.
	 * @return type
	 */
	public function get_multi_day_selected_dates( $id ) {

		return get_post_meta( $id, 'WooCommerceEventsSelectDate', true );
	}

	/**
	 * Formats the end date for calendar display
	 *
	 * @param int  $id id.
	 * @param bool $end_date end date.
	 * @param bool $list_view list view.
	 * @return string
	 */
	public function format_end_date( $id, $end_date = true, $list_view = false ) {

		$event_end_date = '';
		if ( $end_date ) {

			$event_end_date = get_post_meta( $id, 'WooCommerceEventsEndDate', true );

		} else {

			$event_end_date = get_post_meta( $id, 'WooCommerceEventsDate', true );

		}

		$event_end_date = $this->convert_month_to_english( $event_end_date );
		$event_hour     = get_post_meta( $id, 'WooCommerceEventsHourEnd', true );
		$event_minutes  = get_post_meta( $id, 'WooCommerceEventsMinutesEnd', true );
		$event_period   = get_post_meta( $id, 'WooCommerceEventsEndPeriod', true );
		$format         = get_option( 'date_format' );

		if ( 'calendar' === $list_view ) {

			$list_view = false;

		}

		if ( false === $list_view ) {

			$event_end_date = $event_end_date . ' 23:00';

		} else {

			$event_end_date = $event_end_date . ' ' . $event_hour . ':' . $event_minutes . $event_period;

		}

		if ( 'd/m/Y' === $format ) {

			$event_end_date = str_replace( '/', '-', $event_end_date );

		}

		$event_end_date = str_replace( ',', '', $event_end_date );
		$event_end_date = date( 'Y-m-d H:i:s', strtotime( $event_end_date ) );

		$global_fooevents_all_day_event = get_option( 'globalFooEventsAllDayEvent' );
		if ( 'yes' === $global_fooevents_all_day_event ) {

			$event_end_date = date( 'Y-m-d H:i:s', strtotime( $event_end_date . ' +1 day' ) );

		}

		$event_end_date = str_replace( ' ', 'T', $event_end_date );

		return $event_end_date;
	}

	/**
	 * Process events for display on calendar
	 *
	 * @param array $events events.
	 * @param array $attributes attributes.
	 * @return array
	 */
	public function process_events_calendar( $events, $attributes = array() ) {

		$processed_events = array();

		$x = 0;

		if ( ! empty( $events['events'] ) ) {

			foreach ( $events['events'] as $key => $event ) {

				if ( isset( $event['multi_day'] ) && 'selected' === $event['multi_day'] ) {
					$processed_events['events'][ $x ] = $event;
					++$x;
					continue;

				}

				if ( ! empty( $event['unformated_date'] ) && ! empty( $event['unformated_end_date'] ) ) {

					$processed_events['events'][ $x ] = $event;

					if ( $event['unformated_date'] !== $event['unformated_end_date'] ) {

						if ( isset( $attributes['defaultView'] ) && 'listWeek' === $attributes['defaultView'] ) {

							$end                                     = $this->format_end_date( $event['post_id'], false, true );
							$processed_events['events'][ $x ]['end'] = $end;
							$num_days                                = get_post_meta( $event['post_id'], 'WooCommerceEventsNumDays', true );

							for ( $i = 1; $i < $num_days; $i++ ) {

								++$x;
								$date_time_start = new DateTime( $event['start'] );
								$date_time_start->modify( '+' . $i . ' day' );
								$processed_start = $date_time_start->format( 'Y-m-d H:i:s' );

								$date_time_end = new DateTime( $end );
								$date_time_end->modify( '+' . $i . ' day' );
								$processed_end = $date_time_end->format( 'Y-m-d H:i:s' );

								$processed_events['events'][ $x ]          = $event;
								$processed_events['events'][ $x ]['start'] = $processed_start;

								$processed_events['events'][ $x ]['end'] = $processed_end;

							}
						}
					}
				} else {

					$processed_events['events'][ $x ] = $event;

				}

				++$x;

			}
		}

		return $processed_events;
	}

	/**
	 * Format array for the datepicker
	 *
	 * WordPress stores the locale information in an array with a alphanumeric index, and
	 * the datepicker wants a numerical index. This function replaces the index with a number
	 *
	 * @param array $array_to_strip array to strip.
	 * @return array
	 */
	private function strip_array_indices( $array_to_strip ) {

		foreach ( $array_to_strip as $obj_array_item ) {

			$new_array[] = $obj_array_item;

		}

		return( $new_array );
	}

	/**
	 * Convert the php date format string to a js date format
	 *
	 * @param string $sformat format.
	 */
	private function date_format_php_to_js( $format ) {

		$return_format = $format;
		switch ( $format ) {
			// Predefined WP date formats.
			case 'D d-m-y':
				$return_format = 'D dd-mm-yy';
				break;

			case 'D d-m-Y':
				$return_format = 'D dd-mm-yy';
				break;

			case 'l d-m-Y':
				$return_format = 'DD dd-mm-yy';
				break;

			case 'jS F Y':
				$return_format = 'd MM yy';
				break;

			case 'F j, Y':
				$return_format = 'MM d, yy';
				break;

			case 'F j Y':
				$return_format = 'MM d yy';
				break;

			case 'j F, Y':
				$return_format = 'd MM, yy';
				break;

			case 'M. j, Y':
				$return_format = 'M. d, yy';
				break;

			case 'M. d, Y':
				$return_format = 'M. dd, yy';
				break;

			case 'mm/dd/yyyy':
				$return_format = 'mm/dd/yy';
				break;

			case 'j F Y':
				$return_format = 'd MM yy';
				break;

			case 'Y/m/d':
				$return_format = 'yy/mm/dd';
				break;

			case 'm/d/Y':
				$return_format = 'mm/dd/yy';
				break;

			case 'd/m/Y':
				$return_format = 'dd/mm/yy';
				break;

			case 'd.m.Y':
				$return_format = 'dd.mm.yy';
				break;

			case 'Y-m-d':
				$return_format = 'yy-mm-dd';
				break;

			case 'm-d-Y':
				$return_format = 'mm-dd-yy';
				break;

			case 'd-m-Y':
				$return_format = 'dd-mm-yy';
				break;

			case 'j. FY':
				$return_format = 'd. MMyy';
				break;

			case 'j. F Y':
				$return_format = 'd. MM yy';
				break;

			case 'j. F, Y':
				$return_format = 'd. MM, yy';
				break;

			case 'j.m.Y':
				$return_format = 'd.mm.yy';
				break;

			case 'j.n.Y':
				$return_format = 'd.m.yy';
				break;

			case 'j. n. Y':
				$return_format = 'd. m. yy';
				break;

			case 'j.n. Y':
				$return_format = 'd.m. yy';
				break;

			case 'j \d\e F \d\e Y':
				$return_format = 'd MM yy';
				break;

			case 'D j M Y':
				$return_format = 'D d M yy';
				break;

			case 'D F j':
				$return_format = 'D MM d';
				break;

			case 'l j F Y':
				$return_format = 'DD d MM yy';
				break;

			case 'l, j M Y':
				$return_format = 'DD, d M yy';
				break;

			case 'M d / Y':
				$return_format = 'M dd / yy';
				break;

			case 'M j / Y':
				$return_format = 'M d / yy';
				break;

			default:
				$return_format = 'yy-mm-dd';
		}

		return $return_format;
	}

	/**
	 * Calculate the day number between two dates
	 *
	 * @param string $start start.
	 * @param string $end end.
	 */
	private function num_days_between_two_dates( $start, $end ) {

		$start = $this->convert_month_to_english( $start );
		$end   = $this->convert_month_to_english( $end );

		$start = strtotime( $start );
		$end   = strtotime( $end );

		$datediff = $end - $start;

		return round( $datediff / ( 60 * 60 * 24 ) );
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

	/**
	 * Checks if a plugin is active.
	 *
	 * @param string $plugin plugin.
	 * @return boolean
	 */
	private function is_plugin_active( $plugin ) {

		return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true );
	}
}
