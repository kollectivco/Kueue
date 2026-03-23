<?php
/**
 * Follow up emails helper file
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

/**
 * Follow up emails helper
 */
class FooEvents_Follow_Up_Emails_Helper {

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

			add_filter( 'fue_email_types', array( $this, 'register_email_type' ) );
			add_filter( 'fue_email_form_trigger_fields', array( $this, 'add_product_selector' ) );

			add_action( 'fooevents_create_ticket', array( $this, 'after_create_ticket' ) );
			add_action( 'fooevents_create_ticket_admin', array( $this, 'after_create_ticket' ) );

			add_action( 'fooevents_create_ticket', array( $this, 'before_after_event_date' ) );
			add_action( 'fooevents_create_ticket_admin', array( $this, 'before_after_event_date' ) );

			add_action( 'fooevents_create_ticket', array( $this, 'before_after_booking_date' ) );
			add_action( 'fooevents_create_ticket_admin', array( $this, 'before_after_booking_date' ) );

			add_action( 'fooevents_check_in_ticket', array( $this, 'after_check_in_ticket' ) );

			add_action( 'fue_email_variables_list', array( $this, 'email_variables_list' ) );
			add_action( 'fue_before_variable_replacements', array( $this, 'register_variable_replacements' ), 99, 4 );

	}

	/**
	 * Register custom email type
	 *
	 * @param array $types Email types.
	 * @return array
	 */
	public function register_email_type( $types ) {

			$triggers = array(
				'after_fooevents_ticket_purchased' => __( 'After Ticket Issued', 'woocommerce_events' ),
				'after_ticket_check_in'            => __( 'After Ticket Check-in', 'woocommerce_events' ),
				'after_fooevents_event'            => __( 'After Event Date', 'woocommerce_events' ),
				'before_fooevents_event'           => __( 'Before Event Date', 'woocommerce_events' ),
				'after_fooevents_booking_event'    => __( 'After Booking Date', 'woocommerce_events' ),
				'before_fooevents_booking_event'   => __( 'Before Booking Date', 'woocommerce_events' ),
			);

			$props = array(
				'label'             => __( 'FooEvents', 'woocommerce-events' ),
				'singular_label'    => __( 'FooEvents', 'woocommerce-events' ),
				'triggers'          => $triggers,
				'durations'         => Follow_Up_Emails::$durations,
				'long_description'  => __( 'Send follow-up emails to customers that book appointments, services or rentals.', 'woocommerce-events' ),
				'short_description' => __( 'Send follow-up emails to customers that book appointments, services or rentals.', 'woocommerce-events' ),
			);

			$types[] = new FUE_Email_Type( 'fooevents', $props );

			return $types;

	}

	/**
	 * Add product selector.
	 *
	 * @param object $email email object.
	 */
	public function add_product_selector( $email ) {

		if ( in_array( $email->type, array( 'fooevents' ), false ) ) {

			// load the categories.
			$categories     = get_terms(
				'product_cat',
				array(
					'order_by' => 'name',
					'order'    => 'ASC',
				)
			);
			$has_variations = ( ! empty( $email->product_id ) && FUE_Addon_Woocommerce::product_has_children( $email->product_id ) ) ? true : false;
			$storewide_type = ( ! empty( $email->meta['storewide_type'] ) ) ? $email->meta['storewide_type'] : 'all';

			include FUE_TEMPLATES_DIR . '/email-form/woocommerce/email-form.php';

		}

	}

	/**
	 * Schedule follow up email after ticket is created.
	 *
	 * @param int $ticket_post_id Ticket Post ID.
	 */
	public function after_create_ticket( $ticket_post_id ) {

		$triggers = array( 'after_fooevents_ticket_purchased' );

		$emails = fue_get_emails(
			'any',
			'',
			array(
				'meta_query' => array(
					array(
						'key'     => '_interval_type',
						'value'   => $triggers,
						'compare' => 'IN',
					),
				),
			)
		);

		foreach ( $emails as $email ) {

			if ( 'fue-active' !== $email->status ) {
					continue;
			}

			$send_on          = '';
			$attendee_email   = get_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeEmail', true );
			$product_id       = get_post_meta( $ticket_post_id, 'WooCommerceEventsProductID', true );
			$order_id         = get_post_meta( $ticket_post_id, 'WooCommerceEventsOrderID', true );
			$ticket_id        = get_post_meta( $ticket_post_id, 'WooCommerceEventsTicketID', true );
			$product_cats_ids = wc_get_product_term_ids( $product_id, 'product_cat' );

			$order = wc_get_order( $order_id );

			$send = false;
			if ( ! empty( $attendee_email ) && ( ( ! empty( $email->product_id ) && empty( $email->category_id ) && $product_id === $email->product_id ) || ( ! empty( $email->category_id ) && empty( $email->product_id ) && in_array( $email->category_id, $product_cats_ids, false ) ) || ( ! empty( $email->product_id ) && $product_id === $email->product_id ) && ( ! empty( $email->category_id ) && in_array( $email->category_id, $product_cats_ids, false ) ) ) ) {

				$send = true;

			} elseif ( empty( $email->product_id ) && empty( $email->category_id ) && ! empty( $attendee_email ) ) {

				$send = true;

			}

			if ( true === $send ) {

				if ( 'date' === $email->duration ) {

						$email->interval_type = 'date';
						$send_on              = $email->get_send_timestamp();

				}

				$insert = array(
					'user_email' => $attendee_email,
					'send_on'    => $send_on,
					'email_id'   => $email->id,
					'product_id' => $product_id,
					'meta'       => array(
						'ticket_post_id' => $ticket_post_id,
						'ticket_id'      => $ticket_id,
					),
				);

				if ( ! is_wp_error( FUE_Sending_Scheduler::queue_email( $insert, $email ) ) ) {

					if ( ! defined( 'FUE_ORDER_CREATED' ) ) {
						define( 'FUE_ORDER_CREATED', true );
					}

					if ( false !== $order ) {

						if ( empty( $insert['send_on'] ) ) {
							$insert['send_on'] = $email->get_send_timestamp();
						}

						$email_trigger = apply_filters( 'fue_interval_str', $email->get_trigger_string(), $email );
						$send_date     = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $insert['send_on'] );

						$note = sprintf(
							// translators: Placeholders are for attendee email, name, send date and trigger.
							__( 'FooEvents Email queued: %1$s %2$s scheduled on %3$s<br/>Trigger: %4$s', 'woocommerce-events' ),
							$attendee_email,
							$email->name,
							$send_date,
							$email_trigger
						);

						$order->add_order_note( $note );

					}
				}
			}
		}

	}

	/**
	 * Schedule follow up email before/after event date.
	 *
	 * @param int $ticket_post_id Ticket Post ID.
	 */
	public function before_after_event_date( $ticket_post_id ) {

		$triggers = array( 'before_fooevents_event', 'after_fooevents_event' );

		$emails = fue_get_emails(
			'any',
			'',
			array(
				'meta_query' => array(
					array(
						'key'     => '_interval_type',
						'value'   => $triggers,
						'compare' => 'IN',
					),
				),
			)
		);

		foreach ( $emails as $email ) {

			if ( 'fue-active' !== $email->status ) {
					continue;
			}

			$send_on                  = '';
			$attendee_email           = get_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeEmail', true );
			$product_id               = get_post_meta( $ticket_post_id, 'WooCommerceEventsProductID', true );
			$order_id                 = get_post_meta( $ticket_post_id, 'WooCommerceEventsOrderID', true );
			$ticket_id                = get_post_meta( $ticket_post_id, 'WooCommerceEventsTicketID', true );
			$event_type               = get_post_meta( $product_id, 'WooCommerceEventsType', true );
			$event_date_timestamp     = get_post_meta( $product_id, 'WooCommerceEventsDateTimestamp', true );
			$event_end_date_timestamp = get_post_meta( $product_id, 'WooCommerceEventsEndDateTimeTimestamp', true );
			$product_cats_ids         = wc_get_product_term_ids( $product_id, 'product_cat' );

			$order = wc_get_order( $order_id );

			$send = false;
			if ( ! empty( $attendee_email ) && in_array( $event_type, array( 'single', 'sequential', 'select' ), false ) && ! empty( $event_date_timestamp ) && ( ( ! empty( $email->product_id ) && empty( $email->category_id ) && $product_id === $email->product_id ) || ( ! empty( $email->category_id ) && empty( $email->product_id ) && in_array( $email->category_id, $product_cats_ids, false ) ) || ( ! empty( $email->product_id ) && $product_id === $email->product_id ) && ( ! empty( $email->category_id ) && in_array( $email->category_id, $product_cats_ids, false ) ) ) ) {

				$send = true;

			} elseif ( empty( $email->product_id ) && empty( $email->category_id ) && ! empty( $attendee_email ) && in_array( $event_type, array( 'single', 'sequential', 'select' ), false ) && ! empty( $event_date_timestamp ) ) {

				$send = true;

			}

			if ( true === $send ) {

				$time    = FUE_Sending_Scheduler::get_time_to_add( $email->interval_num, $email->interval_duration );
				$send_on = '';

				if ( 'before_fooevents_event' === $email->trigger ) {

					$send_on = $event_date_timestamp - $time;

				} else {

					if ( in_array( $event_type, array( 'sequential', 'select' ), false ) ) {

						$event_date_timestamp = $event_end_date_timestamp;

					}

					$send_on = $event_date_timestamp + $time;

				}

				$insert = array(
					'user_email' => $attendee_email,
					'send_on'    => $send_on,
					'email_id'   => $email->id,
					'product_id' => $product_id,
					'meta'       => array(
						'ticket_post_id' => $ticket_post_id,
						'ticket_id'      => $ticket_id,
					),
				);

				if ( ! is_wp_error( FUE_Sending_Scheduler::queue_email( $insert, $email ) ) ) {

					if ( ! defined( 'FUE_ORDER_CREATED' ) ) {
						define( 'FUE_ORDER_CREATED', true );
					}

					if ( false !== $order ) {

						if ( empty( $insert['send_on'] ) ) {
							$insert['send_on'] = $email->get_send_timestamp();
						}

						$email_trigger = apply_filters( 'fue_interval_str', $email->get_trigger_string(), $email );
						$send_date     = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $insert['send_on'] );

						$note = sprintf(
							// translators: Placeholders are for attendee email, name, send date and trigger.
							__( 'FooEvents Email queued: %1$s %2$s scheduled on %3$s<br/>Trigger: %4$s', 'woocommerce-events' ),
							$attendee_email,
							$email->name,
							$send_date,
							$email_trigger
						);

						$order->add_order_note( $note );

					}
				}
			}
		}

	}

	/**
	 * Schedule follow up email before/after booking date.
	 *
	 * @param int $ticket_post_id Ticket Post ID.
	 */
	public function before_after_booking_date( $ticket_post_id ) {

		$triggers = array( 'after_fooevents_booking_event', 'before_fooevents_booking_event' );

		$emails = fue_get_emails(
			'any',
			'',
			array(
				'meta_query' => array(
					array(
						'key'     => '_interval_type',
						'value'   => $triggers,
						'compare' => 'IN',
					),
				),
			)
		);

		foreach ( $emails as $email ) {

			if ( 'fue-active' !== (string) $email->status ) {
					continue;
			}

			$send_on                                   = '';
			$attendee_email                            = get_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeEmail', true );
			$product_id                                = get_post_meta( $ticket_post_id, 'WooCommerceEventsProductID', true );
			$order_id                                  = get_post_meta( $ticket_post_id, 'WooCommerceEventsOrderID', true );
			$ticket_id                                 = get_post_meta( $ticket_post_id, 'WooCommerceEventsTicketID', true );
			$event_type                                = (string) get_post_meta( $product_id, 'WooCommerceEventsType', true );
			$woocommerce_events_booking_date_timestamp = get_post_meta( $ticket_post_id, 'WooCommerceEventsBookingDateTimestamp', true );
			$product_cats_ids                          = wc_get_product_term_ids( $product_id, 'product_cat' );

			$order = wc_get_order( $order_id );

			$send = false;
			if ( ! empty( $attendee_email ) && 'bookings' === $event_type && ! empty( $woocommerce_events_booking_date_timestamp ) && ( ( ! empty( $email->product_id ) && empty( $email->category_id ) && $product_id === $email->product_id ) || ( ! empty( $email->category_id ) && empty( $email->product_id ) && in_array( $email->category_id, $product_cats_ids, false ) ) || ( ! empty( $email->product_id ) && $product_id === $email->product_id ) && ( ! empty( $email->category_id ) && in_array( $email->category_id, $product_cats_ids, false ) ) ) ) {

				$send = true;

			} elseif ( empty( $email->product_id ) && empty( $email->category_id ) && ! empty( $attendee_email ) && 'bookings' === $event_type && ! empty( $woocommerce_events_booking_date_timestamp ) ) {

				$send = true;

			}

			if ( true === $send ) {

				$time    = FUE_Sending_Scheduler::get_time_to_add( $email->interval_num, $email->interval_duration );
				$send_on = '';

				if ( 'before_fooevents_booking_event' === $email->trigger ) {

					$send_on = $woocommerce_events_booking_date_timestamp - $time;

				} else {

					$send_on = $woocommerce_events_booking_date_timestamp + $time;

				}

				$insert = array(
					'user_email' => $attendee_email,
					'send_on'    => $send_on,
					'email_id'   => $email->id,
					'product_id' => $product_id,
					'meta'       => array(
						'ticket_post_id' => $ticket_post_id,
						'ticket_id'      => $ticket_id,
					),
				);

				if ( ! is_wp_error( FUE_Sending_Scheduler::queue_email( $insert, $email ) ) ) {

					if ( ! defined( 'FUE_ORDER_CREATED' ) ) {
						define( 'FUE_ORDER_CREATED', true );
					}

					if ( false !== $order ) {

						if ( empty( $insert['send_on'] ) ) {
							$insert['send_on'] = $email->get_send_timestamp();
						}

						$email_trigger = apply_filters( 'fue_interval_str', $email->get_trigger_string(), $email );
						$send_date     = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $insert['send_on'] );

						$note = sprintf(
							// translators: Placeholders are for attendee email, name, send date and trigger.
							__( 'FooEvents Email queued: %1$s %2$s scheduled on %3$s<br/>Trigger: %4$s', 'woocommerce-events' ),
							$attendee_email,
							$email->name,
							$send_date,
							$email_trigger
						);

						$order->add_order_note( $note );

					}
				}
			}
		}

	}

	/**
	 * Schedule follow up email after ticket check-in.
	 *
	 * @param array $args ticket ID, status, timestamp.
	 */
	public function after_check_in_ticket( $args ) {

		if ( count( $args ) !== 3 ) {

			return false;

		}

		$triggers = array( 'after_ticket_check_in' );

		$emails = fue_get_emails(
			'any',
			'',
			array(
				'meta_query' => array(
					array(
						'key'     => '_interval_type',
						'value'   => $triggers,
						'compare' => 'IN',
					),
				),
			)
		);

		foreach ( $emails as $email ) {

			$ticket_post_id = $args[0];
			$status         = $args[1];
			$timestamp      = $args[2];

			$attendee_email   = get_post_meta( $ticket_post_id, 'WooCommerceEventsAttendeeEmail', true );
			$product_id       = get_post_meta( $ticket_post_id, 'WooCommerceEventsProductID', true );
			$order_id         = get_post_meta( $ticket_post_id, 'WooCommerceEventsOrderID', true );
			$ticket_id        = get_post_meta( $ticket_post_id, 'WooCommerceEventsTicketID', true );
			$product_cats_ids = wc_get_product_term_ids( $product_id, 'product_cat' );

			$order = wc_get_order( $order_id );

			$send = false;
			if ( ! empty( $attendee_email ) && ! empty( $timestamp ) && in_array( $status, array( 'Checked In' ), false ) && ( ( ! empty( $email->product_id ) && empty( $email->category_id ) && $product_id === $email->product_id ) || ( ! empty( $email->category_id ) && empty( $email->product_id ) && in_array( $email->category_id, $product_cats_ids, false ) ) || ( ! empty( $email->product_id ) && $product_id === $email->product_id ) && ( ! empty( $email->category_id ) && in_array( $email->category_id, $product_cats_ids, false ) ) ) ) {

				$send = true;

			} elseif ( empty( $email->product_id ) && empty( $email->category_id ) && ! empty( $attendee_email ) && ! empty( $timestamp ) && in_array( $status, array( 'Checked In' ), false ) ) {

				$send = true;

			}

			if ( true === $send ) {

				$time    = FUE_Sending_Scheduler::get_time_to_add( $email->interval_num, $email->interval_duration );
				$send_on = $timestamp + $time;

				$insert = array(
					'user_email' => $attendee_email,
					'send_on'    => $send_on,
					'email_id'   => $email->id,
					'product_id' => $product_id,
					'meta'       => array(
						'ticket_post_id' => $ticket_post_id,
						'ticket_id'      => $ticket_id,
					),
				);

				if ( ! is_wp_error( FUE_Sending_Scheduler::queue_email( $insert, $email ) ) ) {

					if ( ! defined( 'FUE_ORDER_CREATED' ) ) {
						define( 'FUE_ORDER_CREATED', true );
					}

					if ( false !== $order ) {

						if ( empty( $insert['send_on'] ) ) {
							$insert['send_on'] = $email->get_send_timestamp();
						}

						$email_trigger = apply_filters( 'fue_interval_str', $email->get_trigger_string(), $email );
						$send_date     = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $insert['send_on'] );

						$note = sprintf(
							// translators: Placeholders are for attendee email, name, send date and trigger.
							__( 'FooEvents Email queued: %1$s %2$s scheduled on %3$s<br/>Trigger: %4$s', 'woocommerce-events' ),
							$attendee_email,
							$email->name,
							$send_date,
							$email_trigger
						);

						$order->add_order_note( $note );

					}
				}
			}
		}

	}

	/**
	 * Display available FooEvents variables for use in follow ups.
	 *
	 * @param object $email email object.
	 */
	public function email_variables_list( $email ) {

		global $woocommerce;

		if ( 'fooevents' === (string) $email->type ) : ?>
			<li class="var hideable var_fooevents"><strong>{fooevents_download_url}</strong>  <img class="help_tip" title="<?php esc_attr_e( 'The URL of the downloadable file.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_download_filename}</strong>  <img class="help_tip" title="<?php esc_attr_e( 'The name of the downloadable file.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_attendee_first_name}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The attendee’s first name.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_attendee_last_name}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The attendee’s last name.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_ticket_number}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The ticket number.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_event_name}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The event name.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_event_date}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The event date.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_event_dates_multi_day}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The multi-day event dates seperated by commas.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_event_directions}</strong> <img class="help_tip" title="<?php esc_attr_e( 'Directions to the event venue.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_event_type}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The event type as configured in Event Settings.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_event_start_hour}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The hour of the day when the event starts.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_event_start_minutes}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The minute portion of the hour when the event starts.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_event_start_period}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The period of the day when the event starts (a.m. or p.m.).', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_event_end_hour}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The hour of the day when the event ends.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_event_end_minutes}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The minute portion of the hour when the event ends.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_event_end_period}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The period of the day when the event ends (a.m. or p.m.).', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<li class="var hideable var_fooevents"><strong>{fooevents_event_timezone}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The event timezone.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>
			<?php if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) ) : ?>
				<li class="var hideable var_fooevents"><strong>{fooevents_bookings_slot}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The booked slot.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>	
				<li class="var hideable var_fooevents"><strong>{fooevents_bookings_slot_term}</strong> <img class="help_tip" title="<?php esc_attr_e( 'Custom terminology used for the booking slot.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>	
				<li class="var hideable var_fooevents"><strong>{fooevents_bookings_date}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The date of the bookable event.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>	
				<li class="var hideable var_fooevents"><strong>{fooevents_bookings_date_term}</strong> <img class="help_tip" title="<?php esc_attr_e( 'Custom terminology used for the booking date.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>	
			<?php endif; ?>
			<?php if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) ) : ?>
				<li class="var hideable var_fooevents"><strong>{fooevents_seating_row_name_label}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The custom label used for the seat row name.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>	
				<li class="var hideable var_fooevents"><strong>{fooevents_seating_row_name}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The seat row name of the purchased ticket.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>	
				<li class="var hideable var_fooevents"><strong>{fooevents_seating_seat_number_label}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The custom label used for the seat.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>	
				<li class="var hideable var_fooevents"><strong>{fooevents_seating_seat_number}</strong> <img class="help_tip" title="<?php esc_attr_e( 'The seat number of the purchased ticket.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>	
			<?php endif; ?>	
			<?php if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) : ?>
				<li class="var hideable var_fooevents"><strong>{fooevents_custom_attendee_fields}</strong> <img class="help_tip" title="<?php esc_attr_e( 'Custom attendee field values for the event.', 'woocommerce-events' ); ?>" src="<?php echo esc_url( FUE_TEMPLATES_URL ); ?>/images/help.png" width="16" height="16" /></li>	
			<?php endif; ?>	
		<?php endif; ?>	
		<?php
	}

	/**
	 * Register FooEvents variables to be processed.
	 *
	 * @param array  $var all email variables.
	 * @param object $email_data email data.
	 * @param object $email email object.
	 * @param object $queue_item queue item.
	 */
	public function register_variable_replacements( $var, $email_data, $email, $queue_item ) {

		if ( 'fooevents' !== $email->type ) {

			return;

		}

		$variables = array(
			'fooevents_download_url'              => '',
			'download_filename'                   => '',
			'fooevents_attendee_first_name'       => '',
			'fooevents_attendee_last_name'        => '',
			'fooevents_ticket_number'             => '',
			'fooevents_event_name'                => '',
			'fooevents_event_date'                => '',
			'fooevents_event_dates_multi_day'     => '',
			'fooevents_event_start_hour'          => '',
			'fooevents_event_start_minutes'       => '',
			'fooevents_event_start_period'        => '',
			'fooevents_event_end_hour'            => '',
			'fooevents_event_end_minutes'         => '',
			'fooevents_event_end_period'          => '',
			'fooevents_event_timezone'            => '',
			'fooevents_event_directions'          => '',
			'fooevents_event_type'                => '',
			'fooevents_bookings_slot'             => '',
			'fooevents_bookings_slot_term'        => '',
			'fooevents_bookings_date'             => '',
			'fooevents_bookings_date_term'        => '',
			'fooevents_seating_row_name_label'    => '',
			'fooevents_seating_row_name'          => '',
			'fooevents_seating_seat_number_label' => '',
			'fooevents_seating_seat_number'       => '',
			'fooevents_custom_attendee_fields'    => '',
		);

		$variables = $this->add_variable_replacements( $variables, $email_data, $queue_item, $email );

		$var->register( $variables );
	}

	/**
	 * Process follow-up variables with output.
	 *
	 * @param array  $variables all email variables.
	 * @param object $email_data email data.
	 * @param object $queue_item queue item.
	 * @param object $email email object.
	 * @return array
	 */
	public function add_variable_replacements( $variables, $email_data, $queue_item, $email ) {

		if ( empty( $queue_item->meta['ticket_post_id'] ) ) {

			return $variables;

		}

		$woocommerce_events_product_id = get_post_meta( $queue_item->meta['ticket_post_id'], 'WooCommerceEventsProductID', true );
		$woocommerce_events_order_id   = get_post_meta( $queue_item->meta['ticket_post_id'], 'WooCommerceEventsOrderID', true );

		$product = wc_get_product( $woocommerce_events_product_id );
		$order   = WC_FUE_Compatibility::wc_get_order( $woocommerce_events_order_id );
		$user    = $order->get_user();
		$file    = $product->get_file( $email->meta['downloadable_file'] );

		$variables['fooevents_download_url']      = fue_replacement_url_var(
			add_query_arg(
				array(
					'download_file' => $woocommerce_events_product_id,
					'order'         => $order->get_order_key(),
					'email'         => rawurlencode( $order->get_billing_email() ),
					'key'           => $email->meta['downloadable_file'],
				),
				trailingslashit( home_url() )
			)
		);
		$variables['fooevents_download_filename'] = $file['name'];

		$variables['fooevents_attendee_first_name'] = get_post_meta( $queue_item->meta['ticket_post_id'], 'WooCommerceEventsAttendeeName', true );
		$variables['fooevents_attendee_last_name']  = get_post_meta( $queue_item->meta['ticket_post_id'], 'WooCommerceEventsAttendeeLastName', true );
		$variables['fooevents_ticket_number']       = get_post_meta( $queue_item->meta['ticket_post_id'], 'WooCommerceEventsTicketID', true );

		$variables['fooevents_event_name']          = $product->get_title();
		$variables['fooevents_event_type']          = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsType', true );
		$variables['fooevents_event_directions']    = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsDirections', true );
		$variables['fooevents_event_date']          = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsDate', true );
		$variables['fooevents_event_start_hour']    = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsHour', true );
		$variables['fooevents_event_start_minutes'] = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsMinutes', true );
		$variables['fooevents_event_start_period']  = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsPeriod', true );
		$variables['fooevents_event_end_hour']      = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsHourEnd', true );
		$variables['fooevents_event_end_minutes']   = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsMinutesEnd', true );
		$variables['fooevents_event_end_period']    = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsEndPeriod', true );
		$variables['fooevents_event_timezone']      = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsTimeZone', true );

		$day_term = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsDayOverride', true );

		if ( empty( $day_term ) ) {

			$day_term = get_option( 'WooCommerceEventsDayOverride', true );

		}

		if ( empty( $day_term ) || 1 === (int) $day_term ) {

			$day_term = __( 'Day', 'woocommerce-events' );

		}

		if ( 'select' === $variables['fooevents_event_type'] ) {

			$woocommerce_events_select_date         = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSelectDate', true );
			$woocommerce_events_select_date_hour    = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSelectDateHour', true );
			$woocommerce_events_select_date_minutes = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSelectDateMinutes', true );
			$woocommerce_events_select_date_period  = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSelectDatePeriod', true );

			$woocommerce_events_select_date_hour_end    = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSelectDateHourEnd', true );
			$woocommerce_events_select_date_minutes_end = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSelectDateMinutesEnd', true );
			$woocommerce_events_select_date_period_end  = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsSelectDatePeriodEnd', true );

			if ( ! empty( $woocommerce_events_select_date ) ) {

				$x                                 = 1;
				$variables['fooevents_event_date'] = '<div id="fooevents-event-date-select">';

				foreach ( $woocommerce_events_select_date as $date ) {

					$variables['fooevents_event_date'] .= '<b>' . $day_term . ' ' . $x . ':</b> ' . $date . '<br />';
					$variables['fooevents_event_date'] .= '<b>' . esc_attr__( 'Start time:', 'woocommerce-events' ) . '</b> ';
					$variables['fooevents_event_date'] .= esc_attr( $woocommerce_events_select_date_hour[ $x - 1 ] ) . ':' . esc_attr( $woocommerce_events_select_date_minutes[ $x - 1 ] );
					$variables['fooevents_event_date'] .= ( isset( $woocommerce_events_select_date_period[ $x - 1 ] ) ) ? ' ' . esc_attr( $woocommerce_events_select_date_period[ $x - 1 ] ) : '';
					$variables['fooevents_event_date'] .= '<br />';

					$variables['fooevents_event_date'] .= '<b>' . esc_attr__( 'End time:', 'woocommerce-events' ) . '</b> ';
					$variables['fooevents_event_date'] .= esc_attr( $woocommerce_events_select_date_hour_end[ $x - 1 ] ) . ':' . esc_attr( $woocommerce_events_select_date_minutes_end[ $x - 1 ] );
					$variables['fooevents_event_date'] .= ( isset( $woocommerce_events_select_date_period_end[ $x - 1 ] ) ) ? ' ' . esc_attr( $woocommerce_events_select_date_period_end[ $x - 1 ] ) : '';
					$variables['fooevents_event_date'] .= '<br /><br />';

					$x++;

				}

				$variables['fooevents_event_date'] .= '</div>';

			}
		}

		if ( is_plugin_active( 'fooevents_bookings/fooevents-bookings.php' ) || is_plugin_active_for_network( 'fooevents_bookings/fooevents-bookings.php' ) && 'bookings' === $variables['fooevents_event_type'] ) {

			$fooevents_bookings = new FooEvents_Bookings();
			$bookings_data      = $fooevents_bookings->get_ticket_slot_and_date( $queue_item->meta['ticket_post_id'], $woocommerce_events_product_id );

			$bookings_date_term = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsBookingsDateOverride', true );
			$bookings_slot_term = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsBookingsSlotOverride', true );

			$variables['fooevents_bookings_slot'] = $bookings_data['slot'];
			$variables['fooevents_bookings_date'] = $bookings_data['date'];

			if ( empty( $bookings_date_term ) ) {

				$variables['fooevents_bookings_date_term'] = __( 'Date', 'woocommerce-events' );

			} else {

				$variables['fooevents_bookings_date_term'] = $bookings_date_term;

			}

			if ( empty( $bookings_slot_term ) ) {

				$variables['fooevents_bookings_slot_term'] = __( 'Slot', 'woocommerce-events' );

			} else {

				$variables['fooevents_bookings_slot_term'] = $bookings_slot_term;

			}
		}

		if ( is_plugin_active( 'fooevents_seating/fooevents-seating.php' ) || is_plugin_active_for_network( 'fooevents_seating/fooevents-seating.php' ) && 'seating' === $variables['fooevents_event_type'] ) {

			$fooevents_seating                         = new Fooevents_Seating();
			$ticket['fooevents_seating_options_array'] = $fooevents_seating->display_tickets_meta_seat_options_output( $queue_item->meta['ticket_post_id'] );

			$variables['fooevents_seating_row_name_label']    = $ticket['fooevents_seating_options_array']['row_name_label'];
			$variables['fooevents_seating_row_name']          = $ticket['fooevents_seating_options_array']['row_name'];
			$variables['fooevents_seating_seat_number_label'] = $ticket['fooevents_seating_options_array']['seat_number_label'];
			$variables['fooevents_seating_seat_number']       = $ticket['fooevents_seating_options_array']['seat_number'];

		}

		if ( is_plugin_active( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) || is_plugin_active_for_network( 'fooevents_custom_attendee_fields/fooevents-custom-attendee-fields.php' ) ) {

			$fooevents_custom_attendee_fields = new Fooevents_Custom_Attendee_Fields();
			$custom_fields                    = $fooevents_custom_attendee_fields->display_tickets_meta_custom_options_output( $queue_item->meta['ticket_post_id'], $woocommerce_events_product_id );

			if ( ! empty( $custom_fields ) ) {

				$variables['fooevents_custom_attendee_fields'] = '<div id="fooevents-custom-attendee-fields">';

				foreach ( $custom_fields as $field_id => $field ) {

					$variables['fooevents_custom_attendee_fields'] .= '<span>' . $field['label'] . ':</span> ' . $field['value'] . '<br />';

				}

				$variables['fooevents_custom_attendee_fields'] .= '</div>';

			}
		}

		if ( is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

			$fooevents_multiday_events                    = new Fooevents_Multiday_Events();
			$variables['fooevents_event_dates_multi_day'] = $fooevents_multiday_events->get_comma_seperated_select_dates( $woocommerce_events_product_id );

		}

		// Cater for WooCommerce fields.
		$variables['customer_first_name']    = $order->get_billing_first_name();
		$variables['customer_username']      = $user->user_login;
		$variables['customer_name']          = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
		$variables['customer_email']         = $order->get_billing_email();
		$variables['order_number']           = $woocommerce_events_order_id;
		$variables['order_date']             = $order->get_date_created();
		$variables['order_datetime']         = $order->get_date_created();
		$variables['order_subtotal']         = $order->get_total();
		$variables['order_tax']              = $order->get_total_tax();
		$variables['order_pay_method']       = $order->get_payment_method();
		$variables['order_pay_url']          = $order->get_checkout_payment_url();
		$variables['order_billing_address']  = $order->get_billing_address_1() . ' ' . $order->get_billing_address_2() . ' ' . $order->get_billing_state() . ' ' . $order->get_billing_country() . $order->get_billing_postcode();
		$variables['order_shipping_address'] = $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2() . ' ' . $order->get_shipping_state() . ' ' . $order->get_shipping_country() . $order->get_shipping_postcode();
		$variables['order_billing_phone']    = $order->get_billing_phone();
		$variables['order_shipping_phone']   = $order->get_shipping_phone();
		$variables['store_url']              = home_url();
		$variables['store_url_secure']       = home_url( null, 'https' );
		$variables['store_name']             = get_bloginfo( 'name' );

		// End cater for WooCommerce fields.

		return $variables;

	}

}

