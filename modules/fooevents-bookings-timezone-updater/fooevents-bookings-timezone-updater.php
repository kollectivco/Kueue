<?php
/**
 * Plugin Name: FooEvents Bookings Timestamp Updater
 * Description: Updates booking tickets that do not have a timestamp
 * Version: 1.0.0
 * Author: FooEvents
 * Plugin URI: https://www.fooevents.com/
 * Author URI: https://www.fooevents.com/
 * Developer: FooEvents
 * Developer URI: https://www.fooevents.com/
 * Text Domain: fooevents-bookings-timestamp-updater
 *
 * Copyright: © 2009-2022 FooEvents.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * Plugin config class
 */
class FooEvents_Bookings_Updater {

	/**
	 * On plugin load
	 */
	public function __construct() {

			add_action( 'admin_menu', array( $this, 'add_menu_item' ) );

	}

	/**
	 * Adds the menu item
	 */
	public function add_menu_item() {

		add_menu_page( null, 'FooEvents Bookings Timestamp Updater', 'edit_posts', 'fooevents-bookings-timestamp-updater', array( $this, 'display_page' ), 'dashicons-database', 6 );

	}

	/**
	 * Displays the bookings updater page
	 */
	public function display_page() {

		$return_text = '';

		if ( isset( $_GET['page'] ) && 'fooevents-bookings-timestamp-updater' === $_GET['page'] && isset( $_GET['run_script'] ) && 'yes' === $_GET['run_script'] ) {

			$return_text = $this->bookings_update();

		}

		$num_tickets_to_update = $this->get_num_bookings_to_update();

		if ( isset( $_GET['fooevents-bookings-timestamp-updater-amount'] ) && $_GET['fooevents-bookings-timestamp-updater-amount'] != '' ) {
			$get_amount = sanitize_text_field( $_GET['fooevents-bookings-timestamp-updater-amount'] );
		} else {
			$get_amount = 100;
		}

		require_once plugin_dir_path( __FILE__ ) . 'template.php';

	}

	/**
	 * Counts number of posts to update
	 */
	public function get_num_bookings_to_update() {

		$args = array(
			'post_type'   => array( 'event_magic_tickets' ),
			'post_status' => array( 'publish' ),
			'meta_query'  => array(
				'relation' => 'AND',
				array(
					'relation' => 'OR',
					array(
						'key'     => 'WooCommerceEventsBookingDateTimestamp',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => 'WooCommerceEventsBookingDateTimestamp',
						'value'   => '',
						'compare' => '=',
					),
				),
				array(
					'key'     => 'WooCommerceEventsBookingDateID',
					'value'   => '',
					'compare' => '!=',
				),
			),
		);

		$tickets_query = new WP_Query( $args );
		$tickets_num   = $tickets_query->found_posts;

		return $tickets_num;

	}

	/**
	 * Updates posts
	 */
	public function bookings_update() {

		set_time_limit( 0 );

		$return_text = '';

		$wp_date_format = get_option( 'date_format' );

		if ( isset( $_GET['fooevents-bookings-timestamp-updater-amount'] ) ) {
			$order_amount = sanitize_text_field( $_GET['fooevents-bookings-timestamp-updater-amount'] );
		} else {
			$order_amount = 100;
		}

		$args = array(
			'post_type'      => array( 'event_magic_tickets' ),
			'post_status'    => array( 'publish' ),
			'posts_per_page' => $order_amount,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'relation' => 'OR',
					array(
						'key'     => 'WooCommerceEventsBookingDateTimestamp',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => 'WooCommerceEventsBookingDateTimestamp',
						'value'   => '',
						'compare' => '=',
					),
				),
				array(
					'key'     => 'WooCommerceEventsBookingDateID',
					'value'   => '',
					'compare' => '!=',
				),
			),
		);

		$tickets_query = new WP_Query( $args );
		$tickets       = $tickets_query->get_posts();

		if ( ! empty( $tickets ) ) {

			foreach ( $tickets as $ticket ) {

				$date                         = get_post_meta( $ticket->ID, 'WooCommerceEventsBookingDate', true );
				$woocommerce_events_ticket_id = get_post_meta( $ticket->ID, 'WooCommerceEventsTicketID', true );

				if ( ! empty( $date ) ) {

					$bookings_date_time = '';
					if ( 'd/m/Y' === $wp_date_format ) {

						$bookings_date_time = str_replace( '/', '-', $date );

					} else {

						$bookings_date_time = $date;

					}

					$formatted_time                            = '';
					$bookings_date_time                        = str_replace( ',', '', $bookings_date_time );
					$bookings_date_time                        = $this->convert_month_to_english( $bookings_date_time );
					$woocommerce_events_booking_date_timestamp = strtotime( $bookings_date_time . ' ' . $formatted_time );

					if ( empty( $woocommerce_events_booking_date_timestamp ) ) {

						$woocommerce_events_booking_date_timestamp = 0;

					}

					update_post_meta( $ticket->ID, 'WooCommerceEventsBookingDateTimestamp', $woocommerce_events_booking_date_timestamp );

					$return_text .= 'Updated ticket #' . $woocommerce_events_ticket_id . ' -> ' . $woocommerce_events_booking_date_timestamp . "\n";

				} else {

					update_post_meta( $ticket->ID, 'WooCommerceEventsBookingDateTimestamp', 0 );

				}
			}
		} else {

			$return_text = __( 'No more tickets to update', 'fooevents-bookings-timestamp-updater' );

		}

		return $return_text;

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
			'Sausis'      => 'January',
			'Vasaris'     => 'February',
			'Kovas'       => 'March',
			'Balandis'    => 'April',
			'Gegužė'      => 'May',
			'Birželis'    => 'June',
			'Liepa'       => 'July',
			'Rugpjūtis'   => 'August',
			'Rugsėjis'    => 'September',
			'Spalis'      => 'October',
			'Lapkritis'   => 'November',
			'Gruodis'     => ' December',

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
			'Δεκέμ��ριος' => 'December',

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

$FooEvents_Bookings_Updater = new FooEvents_Bookings_Updater();
