<?php
/**
 * Extend the WP_List_Table for the reports table
 *
 * @link https://www.fooevents.com
 * @package woocommerce-events
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;}

// TODO: Remove order utilitiy when WC 8.0 released.
use Automattic\WooCommerce\Utilities\OrderUtil;

// WP_List_Table is not loaded automatically so we need to load it in our application.
if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

}

/**
 * Create a new table class that will extend the WP_List_Table
 */
class Events_List_Table extends WP_List_Table {

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
	 * Override the parent columns method. Defines the columns to use in your listing table
	 *
	 * @return Array
	 */
	public function get_columns() {

		$columns = array(
			'event_name'        => __( 'Event Name', 'woocommerce-events' ),
			'tickets_sold'      => __( 'Tickets Sold', 'woocommerce-events' ),
			'tickets_available' => __( 'Available Tickets', 'woocommerce-events' ),
			'total_sales'       => __( 'Gross Revenue', 'woocommerce-events' ),
			'settings'          => __( 'Product', 'woocommerce-events' ),
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
		wp_reset_postdata();

		if ( $return_post_count ) {

			$per_page = -1;

		}

		$events_query = new WP_Query(
			array(
				'post_type'      => array( 'product' ),
				'posts_per_page' => $per_page,
				'paged'          => $paged,
				'meta_query'     => array(
					array(
						'key'     => 'WooCommerceEventsEvent',
						'value'   => 'Event',
						'compare' => '=',
					),
				),
			)
		);
		$events       = $events_query->get_posts();

		if ( $return_post_count ) {

			return $events_query->post_count;

		} else {

			return $events;

		}
	}

	/**
	 * Sets the Event Name column as primary so mobile responsive rows collapse correctly.
	 *
	 * @return string
	 */
	protected function get_primary_column_name() {
		return 'event_name';
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  Array  $item Data.
	 * @param  String $column_name Current column name.
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {

			case 'event_name':
				return '<strong><a href="admin.php?page=fooevents-event-report&event=' . $item->ID . '">' . $item->post_title . '</a></strong> <span>#' . $item->ID . '</span>';
			case 'tickets_sold':
				return '<strong><a href="edit.php?post_type=event_magic_tickets&event_id=' . $item->ID . '">' . $this->get_tickets_sold( $item->ID ) . '</a></strong>';
			case 'tickets_available':
				return $this->tickets_available_column( $item->ID );
			case 'total_sales':
				return wc_price( $this->get_gross_revenue( $item->ID ) );
			case 'settings':
				return '<a href="post.php?post=' . $item->ID . '&action=edit">' . __( 'Edit', 'woocommerce-events' ) . '</a>';
			default:
				return '';

		}
	}

	/**
	 * Displays tickets available
	 *
	 * @param int $id product ID.
	 * @return string
	 */
	public function tickets_available_column( $id ) {

		$product = wc_get_product( $id );

		$variations = '';

		if ( get_post_meta( $id, '_manage_stock', true ) === 'yes' ) {

			return round( (int) get_post_meta( $id, '_stock', true ) );

		} elseif ( $product->is_type( 'variable' ) ) {

			$variations = $product->get_available_variations();

			$stock_out_put = 0;
			foreach ( $variations as $variation ) {

				$variation_id  = $variation['variation_id'];
				$variation_obj = new WC_Product_variation( $variation_id );
				$stock         = $variation_obj->get_stock_quantity();

				if ( ! empty( $stock ) ) {

					$stock_out_put = $stock_out_put + $stock;

				}
			}

			if ( empty( $stock_out_put ) ) {

				$stock_out_put = '-';

			}

			return $stock_out_put;

		} else {

			return '-';

		}
	}

	/**
	 * Fetch a product's gross revenue
	 *
	 * @global object $wpdb
	 * @param int $id product ID.
	 * @return int
	 */
	private function get_gross_revenue( $id ) {

		global $wpdb;

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
			",
					$id
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
			",
					$id
				)
			);

		}

		if ( empty( $num ) ) {

			$num = 0;

		}

		return $num;
	}


	/**
	 * Fetch a product's gross revenue
	 *
	 * @global object $wpdb
	 * @param int $id product ID.
	 * @return int
	 */
	private function get_net_revenue( $id ) {

		global $wpdb;

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
			",
					$id
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
			",
					$id
				)
			);

		}

		if ( empty( $num ) ) {

			$num = 0;

		}

		return $num;
	}

	/**
	 * Get a product's tickets sold
	 *
	 * @param int $id product ID.
	 */
	private function get_tickets_sold( $id ) {

		$enable_total_sales = get_option( 'globalWooCommerceEventsUseTotalSales' );

		if ( ! $enable_total_sales ) {

			$query = new WP_Query(
				array(
					'post_type'  => 'event_magic_tickets',
					'meta_key'   => 'WooCommerceEventsProductID',
					'meta_value' => $id,
				)
			);

			return $query->found_posts;

		} else {

			$tickets_sold = get_post_meta( $id, 'total_sales', true );

			return $tickets_sold;

		}
	}
}
