<?php
/**
 * ICS generation class
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}


/**
 * ICS generation class
 */
class FooEvents_ICS_Helper {

	/**
	 * ICS data
	 *
	 * @var string $data
	 */
	public $data;

	/**
	 * Event name
	 *
	 * @var string $name
	 */
	public $name;

	/**
	 * Configuration object
	 *
	 * @var array $config
	 */
	public $config;

	/**
	 * Zoom API Helper object
	 *
	 * @var object $zoom_api_helper
	 */
	private $zoom_api_helper;

	/**
	 * On plugin load
	 *
	 * @param array $config configuration values.
	 */
	public function __construct( $config ) {

		$this->config = $config;

		// ZoomAPIHelper.
		require_once $this->config->class_path . 'class-fooevents-zoom-api-helper.php';
		$this->zoom_api_helper = new FooEvents_Zoom_API_Helper( $this->config );
	}

	/**
	 * Get bookings ICS data
	 *
	 * @param int $event event ID.
	 * @param int $ticket_id ticket ID.
	 * @return array
	 */
	public function get_ics_data_bookings( $event, $ticket_id ) {

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		$ics_data = array();
		if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) {

			$fooevents_bookings = new Fooevents_Bookings();
			$ics_data           = $fooevents_bookings->get_ics_data( $event, $ticket_id );

			$ics_data['WooCommerceEventsTicketAddCalendarReminders'] = get_post_meta( $event, 'WooCommerceEventsTicketAddCalendarReminders', true );
		}

		return $ics_data;
	}

	/**
	 * Get bookings ICS data
	 *
	 * @param int $event event ID.
	 * @return array
	 */
	public function get_ics_data( $event, $ticket_id ) {

		$processed_ics_data = array();

		$processed_ics_data['WooCommerceEventsEvent']                      = get_post_meta( $event, 'WooCommerceEventsEvent', true );
		$processed_ics_data['WooCommerceEventsDate']                       = get_post_meta( $event, 'WooCommerceEventsDate', true );
		$processed_ics_data['WooCommerceEventsEndDate']                    = get_post_meta( $event, 'WooCommerceEventsEndDate', true );
		$processed_ics_data['WooCommerceEventsHour']                       = get_post_meta( $event, 'WooCommerceEventsHour', true );
		$processed_ics_data['WooCommerceEventsMinutes']                    = get_post_meta( $event, 'WooCommerceEventsMinutes', true );
		$processed_ics_data['WooCommerceEventsPeriod']                     = get_post_meta( $event, 'WooCommerceEventsPeriod', true );
		$processed_ics_data['WooCommerceEventsHourEnd']                    = get_post_meta( $event, 'WooCommerceEventsHourEnd', true );
		$processed_ics_data['WooCommerceEventsMinutesEnd']                 = get_post_meta( $event, 'WooCommerceEventsMinutesEnd', true );
		$processed_ics_data['WooCommerceEventsLocation']                   = get_post_meta( $event, 'WooCommerceEventsLocation', true );
		$processed_ics_data['WooCommerceEventsEndPeriod']                  = get_post_meta( $event, 'WooCommerceEventsEndPeriod', true );
		$processed_ics_data['WooCommerceEventsTimeZone']                   = get_post_meta( $event, 'WooCommerceEventsTimeZone', true );
		$processed_ics_data['WooCommerceEventsTicketText']                 = get_post_meta( $event, 'WooCommerceEventsTicketText', true );
		$processed_ics_data['WooCommerceEventsEmail']                      = get_post_meta( $event, 'WooCommerceEventsEmail', true );
		$processed_ics_data['WooCommerceEventsTicketDisplayZoom']          = get_post_meta( $event, 'WooCommerceEventsTicketDisplayZoom', true );
		$processed_ics_data['WooCommerceEventsTicketAddCalendarReminders'] = get_post_meta( $event, 'WooCommerceEventsTicketAddCalendarReminders', true );

		$processed_ics_data['WooCommerceEventsAttendeeName']     = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeName', true );
		$processed_ics_data['WooCommerceEventsAttendeeLastName'] = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeLastName', true );
		$processed_ics_data['WooCommerceEventsAttendeeEmail']    = get_post_meta( $ticket_id, 'WooCommerceEventsAttendeeEmail', true );
		$processed_ics_data['WooCommerceEventsTicketID']         = get_post_meta( $ticket_id, 'WooCommerceEventsTicketID', true );

		return $processed_ics_data;
	}

	/**
	 * Generate variables needed to build .ics file
	 *
	 * @param int    $event event ID.
	 * @param int    $ticket_id ticket ID.
	 * @param string $registrant_email email address.
	 */
	public function generate_ics( $event, $ticket_id, $registrant_email = '' ) {

		$this->data = '';

		$woocommerce_events_type = get_post_meta( $event, 'WooCommerceEventsType', true );

		$ics_data = array();
		if ( 'bookings' === $woocommerce_events_type && ! empty( $ticket_id ) ) {

			$ics_data = $this->get_ics_data_bookings( $event, $ticket_id, $registrant_email );

		} else {

			$ics_data = $this->get_ics_data( $event, $ticket_id );

		}

		$post = get_post( $event );

		$woocommerce_events_hour_end    = '00';
		$woocommerce_events_minutes_end = '00';
		$woocommerce_events_end_period  = '';

		if ( ! empty( $ics_data['WooCommerceEventsHourEnd'] ) ) {

			$woocommerce_events_hour_end = $ics_data['WooCommerceEventsHourEnd'];

		}

		if ( ! empty( $ics_data['WooCommerceEventsMinutesEnd'] ) ) {

			$woocommerce_events_minutes_end = $ics_data['WooCommerceEventsMinutesEnd'];

		}

		if ( ! empty( $ics_data['WooCommerceEventsEndPeriod'] ) ) {

			$woocommerce_events_end_period = $ics_data['WooCommerceEventsEndPeriod'];

		}

		$woocommerce_events_event = '';
		if ( ! empty( $ics_data['WooCommerceEventsEvent'] ) ) {

			$woocommerce_events_event = $ics_data['WooCommerceEventsEvent'];

		}

		$woocommerce_events_date = '';
		if ( ! empty( $ics_data['WooCommerceEventsDate'] ) ) {

			$woocommerce_events_date = $ics_data['WooCommerceEventsDate'];

		}

		$woocommerce_events_end_date = '';
		if ( ! empty( $ics_data['WooCommerceEventsDate'] ) ) {

			$woocommerce_events_end_date = $ics_data['WooCommerceEventsEndDate'];

		}

		$woocommerce_events_hour = '00';

		if ( ! empty( $ics_data['WooCommerceEventsHour'] ) ) {

			$woocommerce_events_hour = $ics_data['WooCommerceEventsHour'];

		}

		$woocommerce_events_minutes = '00';

		if ( ! empty( $ics_data['WooCommerceEventsMinutes'] ) ) {

			$woocommerce_events_minutes = $ics_data['WooCommerceEventsMinutes'];

		}

		$woocommerce_events_period = '';
		if ( ! empty( $ics_data['WooCommerceEventsPeriod'] ) ) {

			$woocommerce_events_period = $ics_data['WooCommerceEventsPeriod'];
		}

		$woocommerce_events_location = '';
		if ( ! empty( $ics_data['WooCommerceEventsLocation'] ) ) {

			$woocommerce_events_location = $ics_data['WooCommerceEventsLocation'];

		}

		$woocommerce_events_timezone = '';
		if ( ! empty( $ics_data['WooCommerceEventsTimeZone'] ) ) {

			$woocommerce_events_timezone = $ics_data['WooCommerceEventsTimeZone'];

		}

		$woocommerce_events_ticket_text = '';
		if ( ! empty( $ics_data['WooCommerceEventsTicketText'] ) ) {

			$woocommerce_events_ticket_text = $ics_data['WooCommerceEventsTicketText'];
			$woocommerce_events_ticket_text = $this->replace_merge_fields( $woocommerce_events_ticket_text, $ics_data );

		}

		$woocommerce_events_email = '';
		if ( ! empty( $ics_data['WooCommerceEventsEmail'] ) ) {

			$woocommerce_events_email = $ics_data['WooCommerceEventsEmail'];

		}

		$woocommerce_events_ticket_display_zoom = '';
		if ( ! empty( $ics_data['WooCommerceEventsTicketDisplayZoom'] ) ) {

			$woocommerce_events_ticket_display_zoom = $ics_data['WooCommerceEventsTicketDisplayZoom'];

		}

		$woocommerce_events_ticket_add_calendar_reminders = array();
		if ( '' !== $ics_data['WooCommerceEventsTicketAddCalendarReminders'] ) {

			$woocommerce_events_ticket_add_calendar_reminders = $ics_data['WooCommerceEventsTicketAddCalendarReminders'];

		}

		$woocommerce_events_timezone = str_replace( 'UTC', 'GMT', $woocommerce_events_timezone );

		if ( '' === $woocommerce_events_end_date ) {

			$woocommerce_events_end_date = $woocommerce_events_date;

		}

		if ( '' === $woocommerce_events_end_period ) {

			$woocommerce_events_end_period = $woocommerce_events_period;

		}

		$woocommerce_events_period     = $woocommerce_events_hour > 12 ? '' : strtoupper( str_replace( '.', '', $woocommerce_events_period ) );
		$woocommerce_events_end_period = $woocommerce_events_hour_end > 12 ? '' : strtoupper( str_replace( '.', '', $woocommerce_events_end_period ) );

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		$multi_day_type = '';

		if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

			$fooevents_multiday_events = new Fooevents_Multiday_Events();
			$multi_day_type            = $fooevents_multiday_events->get_multi_day_type( $event );

		}

		$description = get_bloginfo( 'name' );

		if ( ! empty( $woocommerce_events_ticket_text ) ) {

			$description .= '\n\n' . wp_strip_all_tags( str_replace( "\r\n", '\n', str_replace( '<br />', '\n', str_replace( '<br/>', '\n', $woocommerce_events_ticket_text ) ) ) );

		}

		if ( ! empty( $woocommerce_events_ticket_display_zoom ) && 'off' !== $woocommerce_events_ticket_display_zoom ) {

			$calendar_text_options = array(
				'WooCommerceEventsProductID' => $event,
				'registrant_email'           => $registrant_email,
			);

			if ( 'bookings' === $woocommerce_events_type && ! empty( $ticket_id ) ) {

				$calendar_text_options['slot_id'] = $ics_data['WooCommerceEventsBookingSlotID'];
				$calendar_text_options['date_id'] = $ics_data['WooCommerceEventsBookingDateID'];

			}

			$description .= $this->zoom_api_helper->get_calendar_text( $calendar_text_options );

		}

		$date_format = str_replace( ' \d\e ', ' ', get_option( 'date_format' ) ) . ' H:i';

		if ( 'select' === $multi_day_type ) {

			$multi_day_dates                            = $fooevents_multiday_events->get_multi_day_selected_dates( $event );
			$woocommerce_events_select_date             = get_post_meta( $event, 'WooCommerceEventsSelectDate', true );
			$woocommerce_events_select_date_hour        = get_post_meta( $event, 'WooCommerceEventsSelectDateHour', true );
			$woocommerce_events_select_date_minutes     = get_post_meta( $event, 'WooCommerceEventsSelectDateMinutes', true );
			$woocommerce_events_select_date_period      = get_post_meta( $event, 'WooCommerceEventsSelectDatePeriod', true );
			$woocommerce_events_select_date_hour_end    = get_post_meta( $event, 'WooCommerceEventsSelectDateHourEnd', true );
			$woocommerce_events_select_date_minutes_end = get_post_meta( $event, 'WooCommerceEventsSelectDateMinutesEnd', true );
			$woocommerce_events_select_date_period_end  = get_post_meta( $event, 'WooCommerceEventsSelectDatePeriodEnd', true );
			$woocommerce_events_select_global_time      = get_post_meta( $event, 'WooCommerceEventsSelectGlobalTime', true );
			$woocommerce_events_hour                    = get_post_meta( $event, 'WooCommerceEventsHour', true );
			$woocommerce_event_minutes                  = get_post_meta( $event, 'WooCommerceEventsMinutes', true );
			$woocommerce_events_period                  = get_post_meta( $event, 'WooCommerceEventsPeriod', true );
			$woocommerce_events_hour_end                = get_post_meta( $event, 'WooCommerceEventsHourEnd', true );
			$woocommerce_events_minutes_end             = get_post_meta( $event, 'WooCommerceEventsMinutesEnd', true );
			$woocommerce_events_end_period              = get_post_meta( $event, 'WooCommerceEventsEndPeriod', true );

			$x = 0;
			foreach ( $woocommerce_events_select_date as $day_date ) {

				$start_period_format = '' !== $woocommerce_events_period ? ' A' : '';
				$start_period        = '' !== $woocommerce_events_period ? ' ' . $woocommerce_events_period : '';

				$end_period_format = '' !== $woocommerce_events_end_period ? ' A' : '';
				$end_period        = '' !== $woocommerce_events_end_period ? ' ' . $woocommerce_events_end_period : '';

				$day_date = $this->convert_month_to_english( $day_date );

				$start_time = '';
				if ( 'on' === $woocommerce_events_select_global_time ) {

					$start_time = $woocommerce_events_hour . ':' . $woocommerce_event_minutes . $woocommerce_events_period;

				} else {

					$start_time = $woocommerce_events_select_date_hour[ $x ] . ':' . $woocommerce_events_select_date_minutes[ $x ] . $woocommerce_events_select_date_period[ $x ];

				}

				$end_time = '';
				if ( 'on' === $woocommerce_events_select_global_time ) {

					$end_time = $woocommerce_events_hour_end . ':' . $woocommerce_events_minutes_end . $woocommerce_events_end_period;

				} else {

					$end_time = $woocommerce_events_select_date_hour_end[ $x ] . ':' . $woocommerce_events_select_date_minutes_end[ $x ] . $woocommerce_events_select_date_period_end[ $x ];

				}

				$temp_start_date = DateTime::createFromFormat( $date_format . $start_period_format, $day_date . ' ' . $start_time );
				$temp_end_date   = DateTime::createFromFormat( $date_format . $end_period_format, $day_date . ' ' . $end_time );

				if ( $temp_start_date ) {

					$start_date = date( 'Y-m-d H:i:s', $temp_start_date->getTimestamp() );

				} else {

					$woocommerce_events_date = str_replace( '/', '-', $day_date );
					$woocommerce_events_date = str_replace( ',', '', $woocommerce_events_date );

					$start_date = date( 'Y-m-d H:i:s', strtotime( $woocommerce_events_date . ' ' . $woocommerce_events_select_date_hour[ $x ] . ':' . $woocommerce_events_select_date_minutes[ $x ] . ' ' . $woocommerce_events_select_date_period[ $x ] ) );

				}

				if ( $temp_end_date ) {

					$end_date = date( 'Y-m-d H:i:s', $temp_end_date->getTimestamp() );

				} else {

					$woocommerce_events_date = str_replace( '/', '-', $day_date );
					$woocommerce_events_date = str_replace( ',', '', $woocommerce_events_date );

					$end_date = date( 'Y-m-d H:i:s', strtotime( $woocommerce_events_date . ' ' . $woocommerce_events_select_date_hour_end[ $x ] . ':' . $woocommerce_events_select_date_minutes_end[ $x ] . ' ' . $woocommerce_events_select_date_period_end[ $x ] ) );

				}

				++$x;

				$this->build_ics( $start_date, $end_date, $woocommerce_events_timezone, $post->post_title, $description, $woocommerce_events_location, $woocommerce_events_ticket_add_calendar_reminders, $woocommerce_events_email );

			}
		} else {

			$start_period_format = '' !== $woocommerce_events_period ? ' A' : '';
			$start_period        = '' !== $woocommerce_events_period ? ' ' . $woocommerce_events_period : '';

			$end_period_format = '' !== $woocommerce_events_end_period ? ' A' : '';
			$end_period        = '' !== $woocommerce_events_end_period ? ' ' . $woocommerce_events_end_period : '';

			$woocommerce_events_date     = $this->convert_month_to_english( $woocommerce_events_date );
			$woocommerce_events_end_date = $this->convert_month_to_english( $woocommerce_events_end_date );

			$temp_start_date = DateTime::createFromFormat( $date_format . $start_period_format, $woocommerce_events_date . ' ' . $woocommerce_events_hour . ':' . $woocommerce_events_minutes . $start_period );
			$temp_end_date   = DateTime::createFromFormat( $date_format . $end_period_format, $woocommerce_events_end_date . ' ' . $woocommerce_events_hour_end . ':' . $woocommerce_events_minutes_end . $end_period_format );

			if ( $temp_start_date ) {

				$start_date = date( 'Y-m-d H:i:s', $temp_start_date->getTimestamp() );

			} else {

				$woocommerce_events_date = str_replace( '/', '-', $woocommerce_events_date );
				$woocommerce_events_date = str_replace( ',', '', $woocommerce_events_date );

				$start_date = date( 'Y-m-d H:i:s', strtotime( $woocommerce_events_date . ' ' . $woocommerce_events_hour . ':' . $woocommerce_events_minutes . ' ' . $woocommerce_events_period ) );

			}

			if ( $temp_end_date ) {

				$end_date = date( 'Y-m-d H:i:s', $temp_end_date->getTimestamp() );

			} else {

				$woocommerce_events_end_date = str_replace( '/', '-', $woocommerce_events_end_date );
				$woocommerce_events_end_date = str_replace( ',', '', $woocommerce_events_end_date );

				$end_date = date( 'Y-m-d H:i:s', strtotime( $woocommerce_events_end_date . ' ' . $woocommerce_events_hour_end . ':' . $woocommerce_events_minutes_end . ' ' . $woocommerce_events_end_period ) );

			}

			if ( isset( $ics_data['OffsetEndHours'] ) && ! empty( $ics_data['OffsetEndHours'] ) ) {

				$end_date = date( 'Y-m-d H:i:s', strtotime( '+' . $ics_data['OffsetEndHours'] . ' hours +' . $ics_data['OffsetEndMinutes'] . ' minutes', strtotime( $end_date ) ) );

			}

			$this->build_ics( $start_date, $end_date, $woocommerce_events_timezone, $post->post_title, $description, $woocommerce_events_location, $woocommerce_events_ticket_add_calendar_reminders, $woocommerce_events_email );

		}
	}

	/**
	 * Builds add to calendar .ics file
	 *
	 * @param string $start start.
	 * @param string $end end.
	 * @param string $timezone timezone.
	 * @param string $name name.
	 * @param string $description description.
	 * @param string $location location.
	 * @param string $reminders reminders.
	 * @param string $organizer_email organizer email.
	 */
	public function build_ics( $start, $end, $timezone, $name, $description, $location = '', $reminders = array(), $organizer_email = '' ) {

		$this->name = $name;

		if ( empty( $this->name ) ) {

			$this->name = 'Event';

		}

		$start = (string) date( 'Ymd\THis', strtotime( $start ) );
		$end   = (string) date( 'Ymd\THis', strtotime( $end ) );

		$domain = wp_parse_url( get_site_url() )['host'];

		$random = wp_rand( 111111, 999999 );

		$tzid_start = '';
		$tzid_end   = '';
		$vtimezone  = '';

		if ( trim( $timezone ) !== '' ) {

			$tzid_start = ';TZID=' . $timezone;
			$tzid_end   = ';TZID=' . $timezone;

		}

		$is_daylight_savings = $this->get_is_daylight_saving( $timezone );

		$daylight_savings = '';
		if ( $is_daylight_savings ) {

			$daylight_savings  = "BEGIN:DAYLIGHT\r\nTZOFFSETFROM:+0000\r\nTZOFFSETTO:+0100\r\nDTSTART:19700329T010000\r\nEND:DAYLIGHT\r\n";
			$daylight_savings .= "BEGIN:STANDARD\r\nTZOFFSETFROM:+0100\r\nTZOFFSETTO:+0000\r\nDTSTART:19701025T020000\r\nEND:STANDARD";

		}

		if ( trim( $timezone ) !== '' ) {

			$offset = $this->get_timezone_utc_offset( $timezone );

			$vtimezone = <<<TEXT
			BEGIN:VTIMEZONE
			TZID:$timezone
			TZID:tzone://Microsoft/$offset
			X-LIC-LOCATION:$timezone
			$daylight_savings
			END:VTIMEZONE
			TEXT;

		}

		$organizer = '' !== $organizer_email ? 'ORGANIZER:mailto:' . $organizer_email . "\r\n" : '';

		$this->data .= $vtimezone . "\r\nBEGIN:VEVENT\r\nDTSTART" . $tzid_start . ':' . $start . "\r\nDTEND" . $tzid_end . ':' . $end . "\r\n" . $organizer . 'LOCATION:' . str_replace( ',', '\,', $location ) . "\r\nTRANSP:OPAQUE\r\nSEQUENCE:0\r\nUID:" . $start . $random . '-fooevents@' . $domain . "\r\nDTSTAMP:" . $start . "\r\nSUMMARY:" . $name . "\r\nDESCRIPTION:" . $description . "\r\nPRIORITY:1\r\nCLASS:PUBLIC\r\n";

		if ( ! empty( $reminders ) ) {
			foreach ( $reminders as $reminder ) {

				$minutes = 0;

				switch ( $reminder['unit'] ) {

					case 'minutes':
						$minutes = (int) $reminder['amount'];
						break;

					case 'hours':
						$minutes = (int) $reminder['amount'] * 60;
						break;

					case 'days':
						$minutes = (int) $reminder['amount'] * 1440;
						break;

					case 'weeks':
						$minutes = (int) $reminder['amount'] * 10080;
						break;

				}

				$this->data .= "BEGIN:VALARM\r\nTRIGGER:-PT" . $minutes . "M\r\nACTION:DISPLAY\r\nDESCRIPTION:Reminder\r\nEND:VALARM\r\n";

			}
		} else {
			$this->data .= "BEGIN:VALARM\r\nACTION:NONE\r\nEND:VALARM\r\n";
		}

		$this->data .= "END:VEVENT\r\n";
	}

	/**
	 * Saves ICS file.
	 *
	 * @param int $ticket_id ticket ID.
	 */
	public function save( $ticket_id ) {

		$data = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nMETHOD:PUBLISH\r\n" . $this->data . "END:VCALENDAR\r\n";

		file_put_contents( $this->config->ics_path . $ticket_id . '.ics', $data );
	}

	/**
	 * Download the ICS file.
	 */
	public function show() {

		$data = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nMETHOD:PUBLISH\r\n" . $this->data . "END:VCALENDAR\r\n";

		header( 'Content-type:text/calendar' );
		header( 'Content-Disposition: attachment; filename="' . $this->name . '.ics"' );
		Header( 'Content-Length: ' . strlen( $data ) );
		Header( 'Connection: close' );
		echo $data;
	}

	/**
	 * Determines if timezone is in daylight savings
	 *
	 * @param string $timezone_string timezone string.
	 * @return bool
	 */
	private function get_is_daylight_saving( $timezone_string ) {

		if ( ! empty( $timezone_string ) ) {

			$date = new DateTime( 'now', new DateTimeZone( $timezone_string ) );
			return (bool) $date->format( 'I' );

		} else {

			return false;

		}
	}

	/**
	 * Replace ticket text ics merge fields
	 *
	 * @param string $text the text.
	 * @param array  $data the data.
	 *
	 * @return string
	 */
	private function replace_merge_fields( $text, $data ) {

		$merge_fields['{AttendeeFName}'] = $data['WooCommerceEventsAttendeeName'];
		$merge_fields['{AttendeeLName}'] = $data['WooCommerceEventsAttendeeLastName'];
		$merge_fields['{AttendeeEmail}'] = $data['WooCommerceEventsAttendeeEmail'];
		$merge_fields['{TicketID}']      = $data['WooCommerceEventsTicketID'];
		$merge_fields['{EventDate}']     = $data['WooCommerceEventsDate'];
		$merge_fields['{EventEndDate}']  = $data['WooCommerceEventsEndDate'];
		$merge_fields['{EventVenue}']    = $data['WooCommerceEventsLocation'];

		$text = strtr( $text, $merge_fields );

		return $text;
	}

	/**
	 * Get the UCT offset of the event timezone
	 *
	 * @param string $timezone the timezone.
	 */
	private function get_timezone_utc_offset( $timezone ) {

		$current        = timezone_open( $timezone );
		$utc_time       = new \DateTime( 'now', new \DateTimeZone( 'UTC' ) );
		$offset_in_secs = $current->getOffset( $utc_time );
		$hours_and_sec  = gmdate( 'H:i', abs( $offset_in_secs ) );

		return stripos( $offset_in_secs, '-' ) === false ? "+{$hours_and_sec}" : "-{$hours_and_sec}";
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
