<?php
/**
 * Event report helper class
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// TODO: Remove order utilitiy when WC 8.0 released.
use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

}

/**
 * Event report helper class
 */
class FooEvents_Event_Report_Helper extends WP_List_Table {
	/**
	 * Configuration array
	 *
	 * @var array $config
	 */
	public $config;

	/**
	 * On class load
	 *
	 * @param array $config configuration array.
	 */
	public function __construct( $config ) {

		$this->config = $config;

		require_once $this->config->class_path . 'class-events-list-table.php';
		require_once $this->config->class_path . 'class-check-in-list-table.php';

		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		add_action( 'wp_ajax_fetch_tickets_sold', array( $this, 'fetch_tickets_sold' ) );
		add_action( 'wp_ajax_fetch_tickets_revenue', array( $this, 'fetch_tickets_revenue' ) );
		add_action( 'wp_ajax_fetch_tickets_revenue_net', array( $this, 'fetch_tickets_revenue_net' ) );
		add_action( 'wp_ajax_fetch_revenue_formatted', array( $this, 'fetch_revenue_formatted' ) );
		add_action( 'wp_ajax_fetch_check_ins', array( $this, 'fetch_check_ins' ) );
		add_action( 'wp_ajax_fetch_check_ins_today', array( $this, 'fetch_check_ins_today' ) );
		add_action( 'wp_ajax_fetch_check_outs', array( $this, 'fetch_check_outs' ) );
		add_action( 'wp_ajax_fetch_check_outs_today', array( $this, 'fetch_check_outs_today' ) );
	}

	/**
	 * Add admin reports menu item
	 */
	public function add_menu_item() {

		add_submenu_page( 'fooevents', __( 'Reports', 'woocommerce-events' ), __( 'Reports', 'woocommerce-events' ), 'edit_posts', 'fooevents-reports', array( $this, 'display_report_table_page' ) );
		add_submenu_page( 'fooevents_not_exists', __( 'Report', 'woocommerce-events' ), __( 'Report', 'woocommerce-events' ), 'edit_posts', 'fooevents-event-report', array( $this, 'display_report_page' ) );
	}

	/**
	 * Display ticket themes page
	 */
	public function display_report_table_page() {

		$events_list_table = new Events_List_Table();
		$events_list_table->prepare_items();

		include $this->config->template_path . 'reports-event-listing.php';
	}

	/**
	 * Builds and displays the event report page
	 */
	public function display_report_page() {

		$check_in_list_table = new Check_In_List_Table();
		$check_in_list_table->prepare_items();

		$id = intval( $_GET['event'] );

		$event = get_post( $id );

		$date_format = get_option( 'date_format' );

		$todays_date                 = date( $date_format );
		$previous_date               = date( $date_format, strtotime( '-10 days' ) );
		$previous_month              = date( $date_format, strtotime( '-30 days' ) );
		$previous_90_days            = date( $date_format, strtotime( '-39 days' ) );
		$previous_year               = date( $date_format, strtotime( '-365 days' ) );
		$woocommerce_events_date     = get_post_meta( $id, 'WooCommerceEventsDate', true );
		$woocommerce_events_location = get_post_meta( $id, 'WooCommerceEventsLocation', true );

		if ( isset( $_POST['WooCommerceEventsDateFrom'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$previous_date = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDateFrom'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

		}

		if ( isset( $_POST['WooCommerceEventsDateTo'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$todays_date = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDateTo'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

		}

		include $this->config->template_path . 'reports-report-page.php';
	}

	/**
	 * Fetches number check-in pre hour of today
	 */
	public function fetch_check_ins_today() {

		$id             = intval( $_POST['eventID'] );
		$wp_date_format = get_option( 'date_format' );

		if ( ! empty( $id ) ) {

			$date_from = sanitize_text_field( wp_unslash( $_POST['dateFrom'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( 'd/m/Y' === $wp_date_format ) {

				$date_from = str_replace( '/', '-', $date_from );

			}

			$date_from = $this->convert_month_to_english( $date_from );

			$date_to = sanitize_text_field( wp_unslash( $_POST['dateTo'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$date_to = str_replace( '/', '-', $date_to );
			$date_to = str_replace( ',', '', $date_to );
			$date_to = $this->convert_month_to_english( $date_to );

			$requested_hours    = $this->fetch_all_hours_for_date( $date_to );
			$check_ins_per_hour = array();

			foreach ( $requested_hours as $hour ) {

				$hour_formatted                        = date( 'H:00', $hour );
				$check_ins_per_hour[ $hour_formatted ] = $this->fetch_check_ins_on_hour( $hour, $id );

			}

			echo wp_json_encode( $check_ins_per_hour );

		}

		exit();
	}

	/**
	 * Fetches number check-outs pre hour of today
	 */
	public function fetch_check_outs_today() {

		$id             = intval( $_POST['eventID'] );
		$wp_date_format = get_option( 'date_format' );

		if ( ! empty( $id ) ) {

			$date_from = sanitize_text_field( wp_unslash( $_POST['dateFrom'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( 'd/m/Y' === $wp_date_format ) {

				$date_from = str_replace( '/', '-', $date_from );

			}

			$date_from = $this->convert_month_to_english( $date_from );

			$date_to = sanitize_text_field( wp_unslash( $_POST['dateTo'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( 'd/m/Y' === $wp_date_format ) {

				$date_to = str_replace( '/', '-', $date_to );

			}

			$date_to = $this->convert_month_to_english( $date_to );

			$requested_hours    = $this->fetch_all_hours_for_date( $date_to );
			$check_ins_per_hour = array();

			foreach ( $requested_hours as $hour ) {

				$hour_formatted                        = date( 'H:00', $hour );
				$check_ins_per_hour[ $hour_formatted ] = $this->fetch_check_outs_on_hour( $hour, $id );

			}

			echo wp_json_encode( $check_ins_per_hour );

		}

		exit();
	}

	/**
	 * Fetches number check-ins per day between two dates
	 */
	public function fetch_check_ins() {

		$id             = intval( $_POST['eventID'] );
		$wp_date_format = get_option( 'date_format' );

		if ( ! empty( $id ) ) {

			$date_from = sanitize_text_field( wp_unslash( $_POST['dateFrom'] ) );

			if ( 'd/m/Y' === $wp_date_format ) {

				$date_from = str_replace( '/', '-', $date_from );

			}

			$date_from = $this->convert_month_to_english( $date_from );

			$date_to = sanitize_text_field( wp_unslash( $_POST['dateTo'] ) );

			if ( 'd/m/Y' === $wp_date_format ) {

				$date_to = str_replace( '/', '-', $date_to );

			}

			$date_to = $this->convert_month_to_english( $date_to );

			$requested_dates   = $this->all_dates_between( $date_from, $date_to );
			$check_ins_per_day = array();

			foreach ( $requested_dates as $day ) {

				$check_ins_per_day[ $day ] = $this->fetch_check_ins_on_day( $day, $id );

			}

			echo wp_json_encode( $check_ins_per_day );

		}

		exit;
	}

	/**
	 * Fetches number check-outs per day between two dates
	 */
	public function fetch_check_outs() {

		$id             = intval( $_POST['eventID'] );
		$wp_date_format = get_option( 'date_format' );

		if ( ! empty( $id ) ) {

			$date_from = sanitize_text_field( wp_unslash( $_POST['dateFrom'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( 'd/m/Y' === $wp_date_format ) {

				$date_from = str_replace( '/', '-', $date_from );

			}

			$date_from = $this->convert_month_to_english( $date_from );

			$date_to = sanitize_text_field( wp_unslash( $_POST['dateTo'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( 'd/m/Y' === $wp_date_format ) {

				$date_to = str_replace( '/', '-', $date_to );

			}

			$date_to = $this->convert_month_to_english( $date_to );

			$requested_dates   = $this->all_dates_between( $date_from, $date_to );
			$check_ins_per_day = array();

			foreach ( $requested_dates as $day ) {

				$check_ins_per_day[ $day ] = $this->fetch_check_outs_on_day( $day, $id );

			}

			echo wp_json_encode( $check_ins_per_day );

		}

		exit;
	}

	/**
	 * Fetches number tickets sold per day between two dates
	 */
	public function fetch_tickets_sold() {

		$id             = intval( $_POST['eventID'] ); // phpcs:ignore WordPress.Security.NonceVerification
		$wp_date_format = get_option( 'date_format' );

		if ( ! empty( $id ) ) {

			$date_from = sanitize_text_field( wp_unslash( $_POST['dateFrom'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( 'd/m/Y' === $wp_date_format ) {

				$date_from = str_replace( '/', '-', $date_from );

			}

			$date_from = $this->convert_month_to_english( $date_from );

			$date_to = sanitize_text_field( wp_unslash( $_POST['dateTo'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( 'd/m/Y' === $wp_date_format ) {

				$date_to = str_replace( '/', '-', $date_to );

			}

			$date_to = $this->convert_month_to_english( $date_to );

			$canceled_tickets = sanitize_text_field( $_POST['canceledTickets'] ); // phpcs:ignore WordPress.Security.NonceVerification

			$filters['canceledTickets'] = $canceled_tickets;

			$requested_dates      = $this->all_dates_between( $date_from, $date_to );
			$tickets_sold_per_day = array();

			foreach ( $requested_dates as $day ) {

				$tickets_sold_per_day[ $day ] = $this->fetch_tickets_sold_on_day( $day, $id, $filters );

			}

			echo wp_json_encode( $tickets_sold_per_day );

		}

		exit;
	}

	/**
	 * Fetches tickets revenue per day between two dates
	 */
	public function fetch_tickets_revenue() {

		$id             = intval( $_POST['eventID'] );
		$wp_date_format = get_option( 'date_format' );

		if ( ! empty( $id ) ) {

			$date_from = sanitize_text_field( wp_unslash( $_POST['dateFrom'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( 'd/m/Y' === $wp_date_format ) {

				$date_from = str_replace( '/', '-', $date_from );

			}

			$date_from = $this->convert_month_to_english( $date_from );

			$date_to = sanitize_text_field( wp_unslash( $_POST['dateTo'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( 'd/m/Y' === $wp_date_format ) {

				$date_to = str_replace( '/', '-', $date_to );

			}

			$date_to = $this->convert_month_to_english( $date_to );

			$requested_dates         = $this->all_dates_between( $date_from, $date_to );
			$tickets_revenue_per_day = array();

			foreach ( $requested_dates as $day ) {

				$tickets_revenue_per_day[ $day ] = (string) $this->fetch_tickets_gross_revenue_on_day( $day, $id );

			}

			echo wp_json_encode( $tickets_revenue_per_day );

		}

		exit();
	}

	/**
	 * Fetches tickets revenue per day between two dates
	 */
	public function fetch_tickets_revenue_net() {

		$id             = intval( $_POST['eventID'] );
		$wp_date_format = get_option( 'date_format' );

		if ( ! empty( $id ) ) {

			$date_from = sanitize_text_field( wp_unslash( $_POST['dateFrom'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			if ( 'd/m/Y' === $wp_date_format ) {

				$date_from = str_replace( '/', '-', $date_from );

			}

			$date_from = $this->convert_month_to_english( $date_from );

			$date_to = sanitize_text_field( wp_unslash( $_POST['dateTo'] ) );

			if ( 'd/m/Y' === $wp_date_format ) {

				$date_from = str_replace( '/', '-', $date_from );

			}

			$date_to = $this->convert_month_to_english( $date_to );

			$requested_dates         = $this->all_dates_between( $date_from, $date_to );
			$tickets_revenue_per_day = array();

			foreach ( $requested_dates as $day ) {

				$tickets_revenue_per_day[ $day ] = (string) $this->fetch_tickets_net_revenue_on_day( $day, $id );

			}

			echo wp_json_encode( $tickets_revenue_per_day );

		}

		exit();
	}

	/**
	 * Formats revenue with store currency
	 */
	public function fetch_revenue_formatted() {

		$total_revenue = sanitize_text_field( wp_unslash( $_POST['total_revenue'] ) );
		echo wp_kses_post( wc_price( $total_revenue ) );

		exit();
	}

	/**
	 * Fetches the dates between two given days
	 *
	 * @param string $previous_date previous date.
	 * @param string $todays_date today's date.
	 * @param string $step increment.
	 * @param string $output_format output date format.
	 * @return array
	 */
	private function all_dates_between( $previous_date, $todays_date, $step = '+1 day', $output_format = 'Y-m-d' ) {

		$dates   = array();
		$current = strtotime( $previous_date );
		$last    = strtotime( $todays_date );

		while ( $current <= $last ) {

			$dates[] = date( $output_format, $current );
			$current = strtotime( $step, $current );

		}

		return $dates;
	}

	/**
	 * Fetches tickets sold on a particular day
	 *
	 * @global object $wpdb
	 * @param string $day day.
	 * @param int    $id event ID.
	 * @param array  $filters query filters.
	 * @return int
	 */
	private function fetch_tickets_sold_on_day( $day, $id, $filters ) {

		$meta_query = array(
			'relation' => 'AND',
			array(
				'key'   => 'WooCommerceEventsProductID',
				'value' => $id,
			),
		);

		if ( 'no' === $filters['canceledTickets'] ) {

			array_push(
				$meta_query,
				array(
					'key'     => 'WooCommerceEventsStatus',
					'value'   => array( 'Canceled', 'Cancelled' ),
					'compare' => 'NOT IN',
				)
			);

		}

		$events_query = new WP_Query(
			array(
				'post_type'      => array( 'event_magic_tickets' ),
				'posts_per_page' => -1,
				'date_query'     => array(
					'column'    => 'post_date',
					'after'     => $day,
					'before'    => $day,
					'inclusive' => true,
				),
				'meta_query'     => $meta_query,
			)
		);

		return $events_query->found_posts;
	}

	/**
	 * Fetches a product's gross revenue for a particular day
	 *
	 * @global object $wpdb
	 * @param string $day day.
	 * @param int    $id event ID.
	 * @return int
	 */
	private function fetch_tickets_gross_revenue_on_day( $day, $id ) {

		global $wpdb;
		$prefix = $wpdb->base_prefix;

		// TODO: Remove CPT when WC 8.0 released.
		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {

			// HPOS.
			$num = (float) $wpdb->get_var(
				$wpdb->prepare(
					"
			SELECT SUM(o.product_gross_revenue) 
			FROM {$wpdb->prefix}wc_order_product_lookup o 
			INNER JOIN {$wpdb->prefix}wc_orders p
				ON o.order_id = p.id
			WHERE p.status = 'wc-completed'
				AND o.product_id = %d
				AND DATE(p.date_created_gmt) = '%s'
			",
					$id,
					$day
				)
			);

		} else {

			// CPT.
			$num = (float) $wpdb->get_var(
				$wpdb->prepare(
					"
			SELECT SUM(o.product_gross_revenue) 
			FROM {$wpdb->prefix}wc_order_product_lookup o 
			INNER JOIN {$wpdb->prefix}posts p
				ON o.order_id = p.ID
			WHERE p.post_status = 'wc-completed'
				AND o.product_id = %d
				AND DATE(p.post_date) = '%s'
			",
					$id,
					$day
				)
			);

		}

		if ( empty( $num ) ) {

			$num = 0;

		}

		return $num;
	}

	/**
	 * Fetches a product's net revenue for a particular day
	 *
	 * @global object $wpdb
	 * @param string $day day.
	 * @param int    $id event ID.
	 * @return int
	 */
	private function fetch_tickets_net_revenue_on_day( $day, $id ) {

		global $wpdb;
		$prefix = $wpdb->base_prefix;

		// TODO: Remove CPT when WC 8.0 released.
		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {

			// HPOS.

			$num = (float) $wpdb->get_var(
				$wpdb->prepare(
					"
			SELECT SUM(o.product_net_revenue) 
			FROM {$wpdb->prefix}wc_order_product_lookup o 
			INNER JOIN {$wpdb->prefix}wc_orders p
				ON o.order_id = p.id
			WHERE p.status = 'wc-completed'
				AND o.product_id = %d
				AND DATE(p.date_created_gmt) = '%s'
			",
					$id,
					$day
				)
			);

		} else {

			// CPT.

			$num = (float) $wpdb->get_var(
				$wpdb->prepare(
					"
			SELECT SUM(o.product_net_revenue) 
			FROM {$wpdb->prefix}wc_order_product_lookup o 
			INNER JOIN {$wpdb->prefix}posts p
				ON o.order_id = p.ID
			WHERE p.post_status = 'wc-completed'
				AND o.product_id = %d
				AND DATE(p.post_date) = '%s'
			",
					$id,
					$day
				)
			);

		}

		if ( empty( $num ) ) {

			$num = 0;

		}

		return $num;
	}

	/**
	 * Fetches check-ins on a particular day
	 *
	 * @param string $day day.
	 * @param int    $event event ID.
	 * @return int
	 */
	private function fetch_check_ins_on_day( $day, $event ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'fooevents_check_in';

		$day_begin = strtotime( $day );
		$day_end   = strtotime( 'tomorrow', $day_begin ) - 1;

		$wpdb->get_results(
			$wpdb->prepare(
				"
					SELECT * FROM {$table_name}
					WHERE checkin BETWEEN %s AND %s
						AND eid = %d
						AND status = %s
				",
				$day_begin,
				$day_end,
				$event,
				'Checked In'
			)
		);

		return $wpdb->num_rows;
	}

	/**
	 * Fetches check-outs on a particular day
	 *
	 * @param string $day day.
	 * @param int    $event event ID.
	 * @return int
	 */
	private function fetch_check_outs_on_day( $day, $event ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'fooevents_check_in';

		$day_begin = strtotime( $day );
		$day_end   = strtotime( 'tomorrow', $day_begin ) - 1;

		$wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT * FROM {$table_name}
				WHERE checkin BETWEEN %s AND %s
					AND eid = %d
					AND status = %s
				",
				$day_begin,
				$day_end,
				$event,
				'Not Checked In'
			)
		);

		return $wpdb->num_rows;
	}

	/**
	 * Fetch check-ins for a particular hour
	 *
	 * @param string $hour hour.
	 * @param int    $event event ID.
	 * @return int
	 */
	private function fetch_check_ins_on_hour( $hour, $event ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'fooevents_check_in';

		$hour_begin = $hour;
		$hour_end   = $hour_begin + 3599;

		$wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT * FROM {$table_name}
				WHERE checkin BETWEEN %s AND %s
					AND eid = %d
					AND status = %s
				",
				$hour_begin,
				$hour_end,
				$event,
				'Checked In'
			)
		);

		return $wpdb->num_rows;
	}

	/**
	 * Fetch check-outs for a particular hour
	 *
	 * @param string $hour hour.
	 * @param int    $event event ID.
	 * @return int
	 */
	private function fetch_check_outs_on_hour( $hour, $event ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'fooevents_check_in';

		$hour_begin = $hour;
		$hour_end   = $hour_begin + 3599;

		$wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT * FROM {$table_name}
				WHERE checkin BETWEEN %s AND %s
					AND eid = %d
					AND status = %s
				",
				$hour_begin,
				$hour_end,
				$event,
				'Not Checked In'
			)
		);

		return $wpdb->num_rows;
	}

	/**
	 * Fetches all hours for a particular day
	 *
	 * @param string $date date.
	 * @return array
	 */
	private function fetch_all_hours_for_date( $date ) {

		$day_begin          = strtotime( $date );
		$hours_return_array = array( 1 => $day_begin );

		for ( $x = 2; $x <= 24; $x++ ) {

			$hours_return_array[ $x ] = $hours_return_array[ $x - 1 ] + 3600;

		}

		return $hours_return_array;
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
