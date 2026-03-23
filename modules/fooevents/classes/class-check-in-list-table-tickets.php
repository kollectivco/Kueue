<?php
/**
 * Extend the WP_List_Table for the tickets table
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
class Check_In_List_Table_Tickets extends WP_List_Table {

	/**
	 * Prepare the items for the table to process
	 */
	public function prepare_items() {

		global $wpdb;

		$paged    = $this->get_pagenum();
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
	 * Fix nounce bug that stops save_post from working
	 *
	 * @param object $which which.
	 */
	public function display_tablenav( $which ) {

		// Blank.
	}

	/**
	 * Gets the current page number.
	 *
	 * @since 3.1.0
	 *
	 * @return int
	 */
	public function get_pagenum() {

		$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

		if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] ) {

			$pagenum = $this->_pagination_args['total_pages'];

		}

		return max( 1, $pagenum );
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return Array
	 */
	public function get_columns() {

		global $post;

		$woocommerce_events_product_id = get_post_meta( $post->ID, 'WooCommerceEventsProductID', true );
		$woocommerce_events_type       = get_post_meta( $woocommerce_events_product_id, 'WooCommerceEventsType', true );

		$columns = array();

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		if ( in_array( $woocommerce_events_type, array( 'sequential', 'select' ), true ) && is_plugin_active( 'fooevents_multi_day/fooevents-multi-day.php' ) || is_plugin_active_for_network( 'fooevents_multi_day/fooevents-multi-day.php' ) ) {

			$columns['day'] = 'Day';

		}

		$columns['status'] = 'Status';
		$columns['time']   = 'Time';
		$columns['user']   = 'Logged by';

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

		$ticket = sanitize_text_field( wp_unslash( $_GET['post'] ) ); // phpcs:ignore WordPress.Security.NonceVerification

		if ( $return_post_count ) {

			$per_page = -1;

		}

		if ( $return_post_count ) {

			$sql = $wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE tid = %d",
				$ticket
			);

			$tickets = $wpdb->get_results( $sql );

			return $wpdb->num_rows;

		}

		if ( 1 === $paged ) {

			$paged = 0;

		}

		$rows = $per_page * $paged;

		$sql = $wpdb->prepare(
			'
                    SELECT * FROM ' . $table_name . '
                    WHERE tid = %d
                    ORDER BY checkin desc    
                    LIMIT %d, %d 
                ',
			$ticket,
			$rows,
			$per_page
		);

		$check_ins        = $wpdb->get_results( $sql );
		$check_ins_return = array();

		$x = 0;
		foreach ( $check_ins as $ch ) {

			$user = get_userdata( $ch->uid );

			$woocommerce_events_check_in_time_formatted = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) . ' (P)', $ch->checkin );
			$check_ins_return[ $x ]['ID']               = $ch->tid;
			$check_ins_return[ $x ]['timestamp']        = $ch->checkin;
			$check_ins_return[ $x ]['time']             = $woocommerce_events_check_in_time_formatted;
			$check_ins_return[ $x ]['day']              = $ch->day;
			$check_ins_return[ $x ]['status']           = $ch->status;
			$check_ins_return[ $x ]['user']             = $user->user_nicename;
			$check_ins_return[ $x ]['userid']           = $ch->uid;

			++$x;

		}

		return $check_ins_return;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  array  $item item.
	 * @param  string $column_name Current column name.
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {

		if ( ! function_exists( 'is_plugin_active' ) || ! function_exists( 'is_plugin_active_for_network' ) ) {

			require_once ABSPATH . '/wp-admin/includes/plugin.php';

		}

		$ticket_status_class = '';
		if ( 'Checked In' === $item['status'] ) {

			$ticket_status_class = 'fooevents-check-in-status-checked-in';

		} elseif ( 'Not Checked In' === $item['status'] ) {

			$ticket_status_class = 'fooevents-check-in-status-not-checked-in';

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
				return '<span class="fooevents-check-in-status ' . $ticket_status_class . ' ">' . $item['status'] . '</span>';
			case 'purchaser':
				return '<a href="' . get_admin_url() . 'user-edit.php?user_id=' . $item['customerid'] . '">' . $item['purchaser'] . '</a>';
			case 'user':
				return '<a href="' . get_admin_url() . 'user-edit.php?user_id=' . $item['userid'] . ' " target="_BLANK">' . $item['user'] . '</a>';

			default:
				return '';

		}
	}
}
