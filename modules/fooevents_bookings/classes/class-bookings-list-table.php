<?php
/**
 * Extend the WP_List_Table for the reports table
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

// WP_List_Table is not loaded automatically so we need to load it in our application.
if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class FE_Bookings_List_Table extends WP_List_Table {

	/**
	 * Prepare the items for the table to process
	 */
	public function prepare_items() {

		global $wpdb;

		$this->process_bulk_action();

		$paged = $this->get_pagenum();

		$per_page = 20;

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$data     = $this->table_data( $per_page, $paged );

		$total_items = $this->table_data( '', '', true );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;

	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  array  $item item.
	 * @param  String $column_name Current column name.
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		switch ( $column_name ) {

			case 'ticketid':
				return '<strong><a href="' . get_admin_url() . 'post.php?post=' . $item['ID'] . '&action=edit">#' . $item['ticketid'] . '</a></strong>';
			case 'time':
				return $item['time'];
			case 'attendee':
				return $item['attendee'];
			case 'order':
				return '<a href="' . get_admin_url() . 'post.php?post=' . $item['order'] . '&action=edit">' . $item['order'] . '</a>';
			case 'event':
				return '<a href="' . get_admin_url() . 'post.php?post=' . $item['eventid'] . '&action=edit">' . $item['eventname'] . '</a>';
			case 'purchaser':
				return '<a href="' . get_admin_url() . 'user-edit.php?user_id=' . $item['customerid'] . '">' . $item['purchaser'] . '</a>';
			case 'purchasedate':
				return $item['purchasedate'];
			case 'status':
				return '<span class="check-in-status ' . preg_replace( '/[\s_]/', '-', strtolower( $item['status'] ) ) . '"><span></span>' . $item['status'] . '</span>';
			case 'bookingtimestamp':
				return $item['bookingtimestamp'];
			case 'bookingdate':
				return '<a href="' . get_admin_url() . 'admin.php?page=fooevents-bookings-admin&fooevents_bookings_product=' . $item['eventid'] . '&fooevents_bookings_admin_date=' . $item['bookingdateformatted'] . '">' . $item['bookingdate'] . '</a>';
			case 'bookingslot':
				return '<a href="' . get_admin_url() . 'admin.php?page=fooevents-bookings-admin&fooevents_bookings_product=' . $item['eventid'] . '&fooevents_bookings_slot=' . $item['bookingslotid'] . '">' . $item['bookingslot'] . '</a>';

			default:
				return 'Error';

		}

	}


	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return Array
	 */
	public function get_columns() {

		$columns = array(
			'cb'           => '<input type="checkbox" />',
			'ticketid'     => __( 'Title', 'fooevents-bookings' ),
			'purchaser'    => __( 'Purchaser', 'fooevents-bookings' ),
			'attendee'     => __( 'Attendee', 'fooevents-bookings' ),
			'order'        => __( 'Order', 'fooevents-bookings' ),
			'event'        => __( 'Event', 'fooevents-bookings' ),
			'purchasedate' => __( 'Purchase Date', 'fooevents-bookings' ),
			'bookingdate'  => __( 'Booking Date', 'fooevents-bookings' ),
			'bookingslot'  => __( 'Booking Slot', 'fooevents-bookings' ),
			'status'       => __( 'Status', 'fooevents-bookings' ),
		);

		return $columns;

	}

	/**
	 * Get the table data
	 *
	 * @param int  $per_page per page.
	 * @param int  $paged pagination.
	 * @param bool $return_post_count post count.
	 * @return Array
	 */
	private function table_data( $per_page, $paged, $return_post_count = false ) {

		if ( $return_post_count ) {

			$per_page = '';

		}

		$args = array(
			'post_type'      => array( 'event_magic_tickets' ),
			'post_status'    => array( 'publish' ),
			'posts_per_page' => $per_page,
			'paged'          => $paged,
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'     => 'WooCommerceEventsBookingDateID',
					'value'   => '',
					'compare' => '!=',
				),
			),
		);

		if ( isset( $_GET['fooevents_bookings_product'] ) && ! empty( $_GET['fooevents_bookings_product'] ) ) {

			$event_id = esc_attr( wp_unslash( $_GET['fooevents_bookings_product'] ) );

			$event_args = array(
				array(
					'key'     => 'WooCommerceEventsProductID',
					'value'   => $event_id,
					'compare' => '=',

				),
			);

			array_push( $args['meta_query'], $event_args );

		}

		if ( isset( $_GET['fooevents_bookings_admin_date'] ) && ! empty( $_GET['fooevents_bookings_admin_date'] ) && isset( $_GET['fooevents_bookings_product'] ) && ! empty( $_GET['fooevents_bookings_product'] ) ) {

			$booking_date = strtotime( sanitize_text_field( wp_unslash( $_GET['fooevents_bookings_admin_date'] ) ) );

			$date_start_args = array(
				'key'     => 'WooCommerceEventsBookingDateTimestamp',
				'value'   => $booking_date,
				'compare' => '>=',

			);

			$date_end_args = array(
				'key'     => 'WooCommerceEventsBookingDateTimestamp',
				'value'   => $booking_date + 86399,
				'compare' => '<=',

			);

			array_push( $args['meta_query'], $date_start_args, $date_end_args );

		}

		if ( isset( $_GET['fooevents_bookings_slot'] ) && ! empty( $_GET['fooevents_bookings_slot'] ) ) {

			$slot_id = sanitize_text_field( wp_unslash( $_GET['fooevents_bookings_slot'] ) );

			$slot_args = array(
				array(
					'key'     => 'WooCommerceEventsBookingSlotID',
					'value'   => $slot_id,
					'compare' => '=',

				),
			);

			array_push( $args['meta_query'], $slot_args );

		}

		if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {

			$search_term = sanitize_text_field( wp_unslash( $_GET['s'] ) );

			$search_args = array(
				array(
					'key'     => 'WooCommerceEventsTicketID',
					'value'   => $search_term,
					'compare' => '=',

				),
			);

			$args['meta_query'] = $search_args;

		}

		$tickets_query = new WP_Query( $args );

		if ( $return_post_count ) {

			return $tickets_query->found_posts;

		}

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$tickets   = $tickets_query->get_posts();
		$attendees = array();

		$x = 0;
		foreach ( $tickets as $ticket ) {

			$woocommerce_events_check_in_time_formatted = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) . ' (P)', $ticket->checkin );
			$woocommerce_events_ticket_id               = get_post_meta( $ticket->ID, 'WooCommerceEventsTicketID', true );
			$woocommerce_events_attendee_name           = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeName', true );
			$woocommerce_events_attendee_last_name      = get_post_meta( $ticket->ID, 'WooCommerceEventsAttendeeLastName', true );
			$woocommerce_events_purchaser_first_name    = get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserFirstName', true );
			$woocommerce_events_purchaser_last_name     = get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserLastName', true );
			$woocommerce_events_purchaser_email         = get_post_meta( $ticket->ID, 'WooCommerceEventsPurchaserEmail', true );
			$customer_id                                = get_post_meta( $ticket->ID, 'WooCommerceEventsCustomerID', true );
			$order_id                                   = get_post_meta( $ticket->ID, 'WooCommerceEventsOrderID', true );
			$product_id                                 = get_post_meta( $ticket->ID, 'WooCommerceEventsProductID', true );
			$woocommerce_events_booking_date_timestamp  = get_post_meta( $ticket->ID, 'WooCommerceEventsBookingDateTimestamp', true );
			$ticket_status                              = get_post_meta( $ticket->ID, 'WooCommerceEventsStatus', true );
			$booking_date                               = get_post_meta( $ticket->ID, 'WooCommerceEventsBookingDate', true );
			$booking_slot                               = get_post_meta( $ticket->ID, 'WooCommerceEventsBookingSlot', true );
			$booking_slot_id                            = get_post_meta( $ticket->ID, 'WooCommerceEventsBookingSlotID', true );

			$booking_date_formated = '';
			if ( ! empty( $woocommerce_events_booking_date_timestamp ) ) {

				$booking_date_formated = date( 'm-d-Y', $woocommerce_events_booking_date_timestamp );

			}

			$attendees[ $x ]['ID']                   = $ticket->ID;
			$attendees[ $x ]['ticketid']             = $woocommerce_events_ticket_id;
			$attendees[ $x ]['attendee']             = $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name;
			$attendees[ $x ]['purchaser']            = $woocommerce_events_purchaser_first_name . ' ' . $woocommerce_events_purchaser_last_name . ' -(' . $woocommerce_events_purchaser_email . ')';
			$attendees[ $x ]['customerid']           = $customer_id;
			$attendees[ $x ]['order']                = $order_id;
			$attendees[ $x ]['eventid']              = $product_id;
			$attendees[ $x ]['eventname']            = get_the_title( $product_id );
			$attendees[ $x ]['purchasedate']         = get_the_time( $date_format . ' ' . $time_format, $ticket->ID );
			$attendees[ $x ]['bookingtimestamp']     = $woocommerce_events_booking_date_timestamp;
			$attendees[ $x ]['bookingdate']          = $booking_date;
			$attendees[ $x ]['bookingdateformatted'] = $booking_date_formated;
			$attendees[ $x ]['bookingslot']          = $booking_slot;
			$attendees[ $x ]['bookingslotid']        = $booking_slot_id;

			$attendees[ $x ]['status'] = $ticket_status;

			$x++;

		}

		return $attendees;
	}

	/**
	 * Get bulk action options
	 */
	public function get_bulk_actions() {

		$actions = array(
			'delete' => 'Delete',
		);

		return $actions;

	}

	/**
	 * Add checkboxes to table listing
	 *
	 * @param array $item array of items.
	 */
	public function column_cb( $item ) {

		return sprintf(
			'<input type="checkbox" name="ticket[]" value="%s" />',
			$item['ID']
		);
	}

	/**
	 * Process delete bookings.
	 */
	public function process_bulk_action() {

		if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] ) {

			$ticket_ids = $_GET['ticket'];
			if ( ! empty( $ticket_ids ) ) {

				foreach ( $ticket_ids as $ticket_id ) {

					$ticket_id = esc_attr( $ticket_id );
					wp_trash_post( $ticket_id );

				}
			}
		}

	}

	/**
	 * Generates the table navigation above or bellow the table and removes the
	 * _wp_http_referrer and _wpnonce because it generates a error about URL too large
	 *
	 * @param string $which
	 * @return void
	 */
	public function display_tablenav( $which ) {

		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<div class="alignleft actions">
				<?php $this->bulk_actions(); ?>
			</div>
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>
			<br class="clear" />
		</div>
		<?php

	}

}
