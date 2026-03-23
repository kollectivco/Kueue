<?php
/**
 * Extend the WP_List_Table for the check-ins table
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
class Check_In_List_Table extends WP_List_Table {

	/**
	 * Prepare the items for the table to process
	 */
	public function prepare_items() {

		global $wpdb;

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

		switch ( $column_name ) {

			case 'ticketid':
				return '<a href="' . get_admin_url() . 'post.php?post=' . $item['ID'] . '&action=edit">#' . $item['ticketid'] . '</a>';
			case 'time':
				return $item['time'];
			case 'attendee':
				return $item['attendee'];
			case 'day':
				return $item['day'];
			case 'status':
				return $item['status'];
			case 'purchaser':
				return '<a href="' . get_admin_url() . 'user-edit.php?user_id=' . $item['customerid'] . '">' . $item['purchaser'] . '</a>';
			case 'user':
				return '<a href="' . get_admin_url() . 'user-edit.php?user_id=' . $item['userid'] . ' " target="_BLANK">' . $item['user'] . '</a>';
	

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
			'ticketid'  => 'Ticket ID',
			'purchaser' => 'Purchaser Name',
			'attendee'  => 'Attendee Name',
			'day'       => 'Day',
			'status'    => 'Status',
			'time'      => 'Check-in Time',
			'user'      => 'Logged by',
		);

		return $columns;

	}

	/**
	 * Define the sortable columns
	 *
	 * @return Array
	 */
	public function get_sortable_columns() {

		$sortable_columns = array(
			// 'id' => array('id', true),
			// 'event_name' => array('event_name', true),
		);

		return $sortable_columns;

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

		global $wpdb;
		$table_name = $wpdb->prefix . 'fooevents_check_in';

		wp_reset_postdata();

		$event = '';
		if ( isset( $_GET['event'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$event = intval( $_GET['event'] ); // phpcs:ignore WordPress.Security.NonceVerification

		}

		$date_to = strtotime( 'tomorrow' ) - 1;

		$date_from = strtotime( '-10 days' );

		if ( isset( $_POST['WooCommerceEventsDateFrom'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$date_from = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDateFrom'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$date_from = strtotime( $date_from );

		}

		if ( isset( $_POST['WooCommerceEventsDateTo'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification

			$date_to = sanitize_text_field( wp_unslash( $_POST['WooCommerceEventsDateTo'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$date_to = strtotime( $date_to );

		}

		if ( $return_post_count ) {

			$per_page = -1;

		}

		if ( $return_post_count ) {

			$tickets = $wpdb->get_results(
				$wpdb->prepare(
					"
					SELECT * FROM $table_name
					WHERE eid = %d
					",
					$event
				)
			);

			return $wpdb->num_rows;

		}

		if ( 1 === $paged ) {

			$paged = 0;

		}

		$rows = $per_page * $paged;

		$tickets = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT * FROM $table_name
				WHERE eid = %d
				ORDER BY checkin DESC
				LIMIT %d, %d
				",
				$event,
				$rows,
				$per_page
			)
		);

		$attendees = array();

		$x = 0;
		foreach ( $tickets as $ticket ) {

			if ( ! empty( $ticket ) && 'Checked In' === $ticket->status || 'Not Checked In' === $ticket->status ) {

				$user = get_userdata( $ticket->uid );

				$woocommerce_events_check_in_time_formatted = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) . ' (P)', $ticket->checkin );
				$woocommerce_events_ticket_id               = get_post_meta( $ticket->tid, 'WooCommerceEventsTicketID', true );
				$woocommerce_events_attendee_name           = get_post_meta( $ticket->tid, 'WooCommerceEventsAttendeeName', true );
				$woocommerce_events_attendee_last_name      = get_post_meta( $ticket->tid, 'WooCommerceEventsAttendeeLastName', true );
				$woocommerce_events_purchaser_first_name    = get_post_meta( $ticket->tid, 'WooCommerceEventsPurchaserFirstName', true );
				$woocommerce_events_purchaser_last_name     = get_post_meta( $ticket->tid, 'WooCommerceEventsPurchaserLastName', true );
				$woocommerce_events_purchaser_email         = get_post_meta( $ticket->tid, 'WooCommerceEventsPurchaserEmail', true );
				$customer_id                                = get_post_meta( $ticket->tid, 'WooCommerceEventsCustomerID', true );

				$attendees[ $x ]['ID']         = $ticket->tid;
				$attendees[ $x ]['ticketid']   = $woocommerce_events_ticket_id;
				$attendees[ $x ]['time']       = $woocommerce_events_check_in_time_formatted;
				$attendees[ $x ]['timestamp']  = $ticket->checkin;
				$attendees[ $x ]['attendee']   = $woocommerce_events_attendee_name . ' ' . $woocommerce_events_attendee_last_name;
				$attendees[ $x ]['purchaser']  = $woocommerce_events_purchaser_first_name . ' ' . $woocommerce_events_purchaser_last_name . ' -(' . $woocommerce_events_purchaser_email . ')';
				$attendees[ $x ]['customerid'] = $customer_id;
				$attendees[ $x ]['day']        = $ticket->day;
				$attendees[ $x ]['status']     = $ticket->status;
				$attendees[ $x ]['user']       = $user->user_nicename;
				$attendees[ $x ]['userid']     = $ticket->uid;

				$x++;

			}
		}

		return $attendees;

	}

}
