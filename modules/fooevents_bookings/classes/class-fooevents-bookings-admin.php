<?php
/**
 * Main plugin class.
 *
 * @link https://www.fooevents.com
 * @package fooevents-custom-attendee-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

}

/**
 * Bookings admin class.
 */
class FooEvents_Bookings_Admin extends WP_List_Table {

	/**
	 * Configuration object
	 *
	 * @var object $config contains paths and other configurations
	 */
	private $config;

	/**
	 * On plugin load
	 *
	 * @param object $config configuration object.
	 */
	public function __construct( $config ) {

		$this->config = $config;

		require_once $this->config->class_path . 'class-bookings-list-table.php';

		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		add_action( 'wp_ajax_fooevents_bookings_admin_get_slots', array( $this, 'get_booking_slots' ) );
		add_action( 'wp_ajax_fooevents_bookings_admin_get_dates', array( $this, 'get_booking_dates' ) );
	}

	/**
	 * Gets a products booking dates.
	 *
	 * @param int $event_id the event product ID.
	 */
	public function get_booking_dates() {

		$event_id = '';
		if ( isset( $_POST['event_id'] ) && ! empty( $_POST['event_id'] ) ) {

			$event_id = esc_attr( wp_unslash( $_POST['event_id'] ) );

		}

		$slot_id = '';
		if ( isset( $_POST['slot_id'] ) && ! empty( $_POST['slot_id'] ) ) {

			$slot_id = esc_attr( wp_unslash( $_POST['slot_id'] ) );

		}

		$fooevents_bookings_options_serialized = get_post_meta( $event_id, 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options_raw        = json_decode( $fooevents_bookings_options_serialized, true );
		$fooevents_bookings_options            = $this->process_booking_options( $fooevents_bookings_options_raw );

		$format = get_option( 'date_format' );

		$return_dates = array();

		foreach ( $fooevents_bookings_options as $sid => $slot ) {

			foreach ( $slot['add_date'] as $date_id => $date ) {

				$return_date = $this->convert_month_to_english( $date['date'] );

				if ( 'd/m/Y' === $format ) {

					$return_date = str_replace( '/', '-', $return_date );

				}

				$return_date = date( 'm-d-Y', strtotime( $return_date ) );

				if ( ! empty( $slot_id ) ) {

					if ( $sid === $slot_id ) {

						array_push( $return_dates, $return_date );

					}
				} else {

					array_push( $return_dates, $return_date );

				}
			}
		}

		echo wp_json_encode( $return_dates );

		exit();
	}


	/**
	 * Gets a products booking slots
	 *
	 * @param int $event_id the event product ID.
	 */
	public function get_booking_slots( $event_id = '' ) {

		if ( isset( $_POST['event_id'] ) && ! empty( $_POST['event_id'] ) ) {

			$event_id = esc_attr( wp_unslash( $_POST['event_id'] ) );

		}

		$fooevents_bookings_options_serialized = get_post_meta( $event_id, 'fooevents_bookings_options_serialized', true );
		$fooevents_bookings_options            = json_decode( $fooevents_bookings_options_serialized, true );

		$return_slots = array();

		foreach ( $fooevents_bookings_options as $slot_id => $slot ) {

			$return_slots[ $slot_id ] = $slot['label'];

		}

		if ( isset( $_POST['event_id'] ) && ! empty( $_POST['event_id'] ) ) {

			echo wp_json_encode( $return_slots );
			exit();

		} else {

			return $return_slots;

		}
	}

	/**
	 * Adds Bookings Admin to the FooEvents menu.
	 */
	public function add_menu_item() {

		if ( current_user_can( 'publish_event_magic_tickets' ) ) {
			add_submenu_page( 'fooevents', __( 'Bookings', 'fooevents-bookings' ), __( 'Bookings', 'fooevents-bookings' ), 'edit_posts', 'fooevents-bookings-admin', array( $this, 'display_page' ) );
		}
	}


	/**
	 * Displays the bookings administration page
	 */
	public function display_page() {

		$event_id = '';
		if ( isset( $_GET['fooevents_bookings_product'] ) && '' !== $_GET['fooevents_bookings_product'] ) {

			$event_id = sanitize_text_field( wp_unslash( $_GET['fooevents_bookings_product'] ) );

		}

		$slot_id = '';
		if ( isset( $_GET['fooevents_bookings_slot'] ) && '' !== $_GET['fooevents_bookings_slot'] ) {

			$slot_id = sanitize_text_field( wp_unslash( $_GET['fooevents_bookings_slot'] ) );

		}

		if ( ! empty( $event_id ) ) {

			$slots = $this->get_booking_slots( $event_id );

		}

		$events = $this->get_booking_events();

		$date = '';
		if ( isset( $_GET['fooevents_bookings_admin_date'] ) && ! empty( $_GET['fooevents_bookings_admin_date'] ) ) {

			$date = sanitize_text_field( wp_unslash( $_GET['fooevents_bookings_admin_date'] ) );

		}

		$FE_Bookings_List_Table = new FE_Bookings_List_Table();
		$FE_Bookings_List_Table->prepare_items();

		include $this->config->template_path . 'bookings-admin-listing.php';
	}

	/**
	 * Process booking options
	 *
	 * @param array $fooevents_bookings_options booking options.
	 * @return array
	 */
	public function process_booking_options( $fooevents_bookings_options ) {

		$processed_fooevents_bookings_options = array();

		if ( ! empty( $fooevents_bookings_options ) ) {

			foreach ( $fooevents_bookings_options as $options_key => $options ) {

				$processed_fooevents_bookings_options[ $options_key ] = array();

				foreach ( $options as $k => $v ) {

					if ( strpos( $k, '_add_date' ) !== false ) {

						$date_id = str_replace( '_add_date', '', $k );
						$processed_fooevents_bookings_options[ $options_key ]['add_date'][ $date_id ]['date'] = $v;

					} elseif ( strpos( $k, '_zoom_id' ) !== false ) {

						$date_id = str_replace( '_zoom_id', '', $k );
						$processed_fooevents_bookings_options[ $options_key ]['add_date'][ $date_id ]['zoom_id'] = $v;

					} elseif ( strpos( $k, '_stock' ) !== false ) {

						$date_id = str_replace( '_stock', '', $k );
						$processed_fooevents_bookings_options[ $options_key ]['add_date'][ $date_id ]['stock'] = $v;

					} elseif ( 'add_time' === $k && 'enabled' === $v && isset( $processed_fooevents_bookings_options[ $options_key ]['hour'] ) && isset( $processed_fooevents_bookings_options[ $options_key ]['minute'] ) ) {

						$formatted_period = '';
						if ( isset( $processed_fooevents_bookings_options[ $options_key ]['period'] ) && ! empty( $processed_fooevents_bookings_options[ $options_key ]['period'] ) ) {

							$formatted_period = ' ' . $processed_fooevents_bookings_options[ $options_key ]['period'];

						}

						$processed_fooevents_bookings_options[ $options_key ]['formatted_time'] = '(' . $processed_fooevents_bookings_options[ $options_key ]['hour'] . ':' . $processed_fooevents_bookings_options[ $options_key ]['minute'] . $formatted_period . ')';

						$processed_fooevents_bookings_options[ $options_key ]['add_time'] = 'enabled';

					} else {

						$processed_fooevents_bookings_options[ $options_key ][ $k ] = $v;

					}
				}
			}
		}

		return $processed_fooevents_bookings_options;
	}

	/**
	 * Get all booking events
	 */
	private function get_booking_events() {

		global $wpdb;

		$booking_events = $wpdb->get_results( 
			"
			SELECT p.*
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE p.post_type = 'product'
			AND pm.meta_key = 'WooCommerceEventsType'
			AND pm.meta_value = 'bookings'
			"
		 );

		return $booking_events;

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
