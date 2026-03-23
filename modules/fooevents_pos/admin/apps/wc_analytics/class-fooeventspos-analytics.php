<?php
/**
 * Analytics class containing the queries used to filter order analytics based on shop channels.
 *
 * @link    https://www.fooevents.com
 * @since   1.29.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin/apps/wc_analytics
 */

defined( 'ABSPATH' ) || exit;

/**
 * FooEvents POS Analytics queries and channel filters.
 *
 * @since   1.29.0
 * @package fooevents-pos
 * @subpackage fooevents-pos/admin/apps/wc_analytics
 */
class FooEventsPOS_Analytics {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Initialize the class and set its properties.
		$this->fooeventspos_add_sales_channel_settings();

		add_filter( 'woocommerce_analytics_orders_query_args', array( $this, 'fooeventspos_analytics_apply_sales_channel_arg' ) );
		add_filter( 'woocommerce_analytics_orders_stats_query_args', array( $this, 'fooeventspos_analytics_apply_sales_channel_arg' ) );

		add_filter( 'woocommerce_analytics_clauses_join_orders_subquery', array( $this, 'fooeventspos_analytics_add_join_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_join_orders_stats_total', array( $this, 'fooeventspos_analytics_add_join_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_join_orders_stats_interval', array( $this, 'fooeventspos_analytics_add_join_subquery' ) );

		add_filter( 'woocommerce_analytics_clauses_where_orders_subquery', array( $this, 'fooeventspos_analytics_add_where_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_where_orders_stats_total', array( $this, 'fooeventspos_analytics_add_where_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_where_orders_stats_interval', array( $this, 'fooeventspos_analytics_add_where_subquery' ) );

		add_filter( 'woocommerce_analytics_clauses_select_orders_subquery', array( $this, 'fooeventspos_analytics_add_select_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_select_orders_stats_total', array( $this, 'fooeventspos_analytics_add_select_subquery' ) );
		add_filter( 'woocommerce_analytics_clauses_select_orders_stats_interval', array( $this, 'fooeventspos_analytics_add_select_subquery' ) );
	}

	/**
	 * Register sales channel settings.
	 *
	 * @since 1.0.0
	 */
	public function fooeventspos_add_sales_channel_settings() {

		require plugin_dir_path( __FILE__ ) . '../../helpers/fooeventspos-phrases-helper.php';

		$fooeventspos_sales_channel = array(
			array(
				'label' => $fooeventspos_phrases['filter_all_orders'],
				'value' => 'all',
			),
			array(
				'label' => $fooeventspos_phrases['filter_online_only'],
				'value' => 'online',
			),
			array(
				'label' => $fooeventspos_phrases['filter_fooeventspos_pos_only'],
				'value' => 'fooeventspos_pos',
			),
		);

		if ( class_exists( Automattic\WooCommerce\Blocks\Package::class ) && class_exists( Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::class ) ) {
			$data_registry = Automattic\WooCommerce\Blocks\Package::container()->get(
				Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::class
			);

			$data_registry->add( 'fooeventsposSalesChannel', $fooeventspos_sales_channel );
		}
	}

	/**
	 * Applies the selected sales channel filter.
	 *
	 * @since 1.0.0
	 * @param array $args An array of arguments used to set the sales channel (fooeventspos_sales_channel).
	 */
	public function fooeventspos_analytics_apply_sales_channel_arg( $args ) {
		$fooeventspos_sales_channel = 'all';

		if ( isset( $_GET['fooeventspos_sales_channel'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$fooeventspos_sales_channel = sanitize_text_field( wp_unslash( $_GET['fooeventspos_sales_channel'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( 'all' === $fooeventspos_sales_channel ) {
			return $args;
		}

		$args['fooeventspos_sales_channel'] = $fooeventspos_sales_channel;

		return $args;
	}

	/**
	 * Adds the 'JOIN' subquery to the Orders query.
	 *
	 * @since 1.0.0
	 * @param array $clauses An array of query clauses.
	 */
	public function fooeventspos_analytics_add_join_subquery( $clauses ) {
		global $wpdb;

		$fooeventspos_sales_channel = 'all';

		if ( isset( $_GET['fooeventspos_sales_channel'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$fooeventspos_sales_channel = sanitize_text_field( wp_unslash( $_GET['fooeventspos_sales_channel'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( 'all' === $fooeventspos_sales_channel ) {
			return $clauses;
		}

		if ( 'fooeventspos_pos' === $fooeventspos_sales_channel || 'online' === $fooeventspos_sales_channel ) {
			if ( class_exists( Automattic\WooCommerce\Utilities\OrderUtil::class ) && Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
				// HPOS usage is enabled.
				$clauses[] = "JOIN {$wpdb->prefix}wc_orders_meta sales_channel_postmeta ON {$wpdb->prefix}wc_order_stats.order_id = sales_channel_postmeta.order_id";
			} else {
				// Traditional CPT-based orders are in use.
				$clauses[] = "JOIN {$wpdb->postmeta} sales_channel_postmeta ON {$wpdb->prefix}wc_order_stats.order_id = sales_channel_postmeta.post_id";
			}
		}

		return $clauses;
	}

	/**
	 * Adds the 'WHERE' subquery to the Orders query.
	 *
	 * @since 1.0.0
	 * @param array $clauses An array of query clauses.
	 */
	public function fooeventspos_analytics_add_where_subquery( $clauses ) {
		global $wpdb;

		$fooeventspos_sales_channel = 'all';

		if ( isset( $_GET['fooeventspos_sales_channel'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$fooeventspos_sales_channel = sanitize_text_field( wp_unslash( $_GET['fooeventspos_sales_channel'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( 'all' === $fooeventspos_sales_channel ) {
			return $clauses;
		}

		if ( 'fooeventspos_pos' === $fooeventspos_sales_channel ) {
			$clauses[] = "AND sales_channel_postmeta.meta_key = '_fooeventspos_order_source' AND sales_channel_postmeta.meta_value = 'fooeventspos_app'";
		} elseif ( 'online' === $fooeventspos_sales_channel ) {
			$clauses[] = "AND sales_channel_postmeta.meta_key = '_fooeventspos_order_source' AND sales_channel_postmeta.meta_value = 'online'";
		}

		return $clauses;
	}

	/**
	 * Adds the 'SELECT' subquery to the Orders query.
	 *
	 * @since 1.0.0
	 * @param array $clauses An array of query clauses.
	 */
	public function fooeventspos_analytics_add_select_subquery( $clauses ) {
		if ( false !== strpos( implode( $clauses ), '_fooeventspos_order_source' ) ) {
			$clauses[] = ', sales_channel_postmeta.meta_value AS sales_channel';
		}

		return $clauses;
	}
}
